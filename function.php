<?php
// 登录封装示例
function Alipay_EasyLogin_Login($ref_url = null)
{
  global $zbp;
  if ($zbp->Config('Alipay_EasyLogin')->on != '1') {
    return;
  }
  $objAlipay = Alipay_EasyLogin_Init();
  // 授权完成后默认返回的页面
  if ($ref_url) {
    $objAlipay->SetCookie('ref_url', $ref_url);
  }
  // 根据 cookie 记录的 alipay_user_id 判断是否已经登录，酌情使用
  // if ($objAlipay->isLogin()) {
  //   return;
  // }
  if ($objAlipay->errCode === 0) {
    $AuthorizeUrl = $objAlipay->GetAuthorizeUrl();
    Redirect($AuthorizeUrl);
  } else {
    $zbp->ShowError($objAlipay->ErrMsg);
  }
}

// 初始化支付宝 SDK
function Alipay_EasyLogin_Init()
{
  global $zbp;
  // 回调地址
  $zbp->Config("Alipay_EasyLogin")->read_callback = Alipay_EasyLogin_Path("callback", "host");
  $appid = $zbp->Config('Alipay_EasyLogin')->appid;
  $private_key = $zbp->Config('Alipay_EasyLogin')->private_key;
  $read_callback = $zbp->Config('Alipay_EasyLogin')->read_callback;
  $objAlipay = new Alipay_EasyLogin($appid, $private_key, $read_callback);
  return $objAlipay;
}

// 判断 UA 是否为支付宝内
function Alipay_EasyLogin_CheckUA()
{
  $ua = $_SERVER['HTTP_USER_AGENT'];
  if (strpos($ua, 'Alipay') === false) {
    return false;
  }
  return true;
}
