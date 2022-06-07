<?php
class Alipay_EasyLogin
{
  private $appid;
  private $private_key;
  private $params = array();
  private $redirect_uri = '';
  private $cke_prefix = 'Alipay_EasyLogin_';
  // 错误信息定义
  private $err_maps = array(
    0 => '',
    101 => 'miss auth code',
    102 => 'http error',
    103 => 'Invalid Arguments',
    201 => 'private key error',
  );
  public $errCode = 0;
  public $errMsg = '';
  public $usrData = array();

  public function __construct($appid, $private_key, $redirect_uri)
  {
    $this->appid = $appid;
    $this->private_key = $private_key;
    $this->redirect_uri = $redirect_uri;
    $this->SetCookie("redirect_uri", $redirect_uri);
    $this->load_err();
  }

  public function __get($key)
  {
    if (empty($this->usrData)) {
      return null;
    }
    $usrData = $this->usrData;
    if (isset($usrData[$key])) {
      return $usrData[$key];
    }
  }

  public function GetData()
  {
    return $this->usrData;
  }

  public function isLogin()
  {
    $alipay_user_id = $this->GetCookie('alipay_user_id');
    if ($alipay_user_id) {
      return true;
    }
    return false;
  }

  public function GetAuthorizeUrl()
  {
    $params = array(
      'app_id' => $this->appid,
      'scope' => 'auth_user',
      'redirect_uri' => $this->redirect_uri,
      'state' => 'init',
    );
    $baseUrl = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm';
    // $baseUrl = 'https://openauth.alipaydev.com/oauth2/publicAppAuthorize.htm';
    return $baseUrl . '?' . http_build_query($params);
  }

  public function GetAuthCode()
  {
    // ?state=init&app_id=2021000118640552&source=alipay_wallet&userOutputs=auth_user&scope=auth_user&alipay_token=&auth_code=4b553487ea3e49b2802e8e8cb68dRX34
    if (isset($_GET['auth_code'])) {
      $this->SetCookie("auth_code", $_GET['auth_code']);
      return $_GET['auth_code'];
    }
    return null;
  }

  public function GetOauthToken()
  {
    $auth_code = $this->GetAuthCode();
    if ($auth_code === null) {
      $this->set_err(101);
      return null;
    }
    $this->build_params(array(
      'code' => $auth_code,
    ));
    $baseUrl = 'https://openapi.alipay.com/gateway.do';
    // $baseUrl = 'https://openapi.alipaydev.com/gateway.do';
    if ($this->errCode === 0) {
      $data = $this->http($baseUrl, $this->params);
      $this->parse_data($data, true);
      return $this->alipay_user_id;
    }
  }

  public function SetCookie($key, $value, $expire = 0)
  {
    if ($expire > 0) {
      $expire = time() + $expire;
    }
    setcookie($this->cke_prefix . $key, $value, $expire, '/');
  }

  public function GetCookie($key, $def = null)
  {
    $key = $this->cke_prefix . $key;
    if (isset($_COOKIE[$key])) {
      return $_COOKIE[$key];
    }
    return $def;
  }

  private function parse_data($data)
  {
    $data = json_decode($data, true);
    if (!is_array($data)) {
      $this->set_err(103);
      return;
    }
    if (isset($data["alipay_system_oauth_token_response"])) {
      $this->usrData = $data["alipay_system_oauth_token_response"];
    }
    if (isset($data["sign"])) {
      $this->usrData["sign"] = $data["sign"];
    }
  }

  private function set_err($err_code, $ext_msg = "")
  {
    $this->errCode = $err_code;
    $this->errMsg = $this->err_maps[$err_code];
    if ($ext_msg !== "") {
      $this->errMsg .= ": " . $ext_msg;
    }
    if ($err_code > 200) {
      $this->SetCookie('err_code', $err_code);
    }
  }

  private function load_err()
  {
    $this->errCode = $this->GetCookie('err_code', 0);
    $this->errMsg = $this->err_maps[$this->errCode];
  }

  private function build_params($append = array())
  {
    $params = array(
      'app_id' => $this->appid,
      'method' => 'alipay.system.oauth.token',
      "format" => "JSON",
      'charset' => 'utf-8',
      'sign_type' => 'RSA2',
      'timestamp' => date('Y-m-d H:i:s'),
      'version' => '1.0',
      'grant_type' => 'authorization_code',
    );
    $params = array_merge($params, $append);
    $params['sign'] = $this->get_sign($params);
    $this->params = $params;
  }

  private function get_sign($params)
  {
    ksort($params);
    // $query = http_build_query($params);
    $query = array();
    foreach ($params as $k => $v) {
      $query[] = $k . '=' . $v;
    }
    $textQuery = implode('&', $query);
    $private_key = $this->private_key;
    $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n{$private_key}\n-----END RSA PRIVATE KEY-----";
    // $key = openssl_get_privatekey($privateKey);
    $key = openssl_pkey_get_private($privateKey);
    $sign = '';
    if ($key !== false) {
      openssl_sign($textQuery, $sign, $key, OPENSSL_ALGO_SHA256);
      $sign = base64_encode($sign);
    } else {
      $this->set_err(201);
    }
    return $sign;
  }

  private function http($url, $param = '')
  {
    $http = Network::Create();
    $http->open('POST', $url);
    // $http->enableGzip();
    // $http->setTimeOuts(30, 30, 0, 0);
    $http->send($param);
    if ($http->status === "200") {
      return $http->responseText;
    } else {
      $this->set_err(102, $http->status);
      return null;
    }
  }
}
