<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();

if (!$zbp->CheckPlugin('Alipay_EasyLogin')) {
  $zbp->ShowError(48);
  die();
}

$objAlipay = Alipay_EasyLogin_Init();

$alipay_user_id = $objAlipay->GetOauthToken();

if ($objAlipay->errCode === 0) {
  $objAlipay->SetCookie('alipay_user_id', $alipay_user_id);
  foreach ($GLOBALS["hooks"]["Filter_Plugin_Alipay_EasyLogin_LoginSuccess"] as $fpname => &$fpsignal) {
    $fpname($alipay_user_id, $objAlipay);
  }
  $ref_url = $objAlipay->GetCookie('ref_url');
  if ($ref_url) {
    Redirect($ref_url);
  }
  echo $alipay_user_id;

  // // debug
  // // ob_clean();
  // echo __FILE__ . "丨" . __LINE__ . ":<br>\n";
  // var_dump($objAlipay->GetData());
  // echo "<br><br>\n\n";
  // // die();
  // // debug

  // $objAlipay->access_token 可以用来获取用户信息

  // array(7) {
  //   ["access_token"]=>
  //   string(40) "authusrB014b883f7d78458ba08a0518e7890B34"
  //   ["alipay_user_id"]=>
  //   string(16) "2088622957119341"
  //   ["expires_in"]=>
  //   int(600)
  //   ["re_expires_in"]=>
  //   int(660)
  //   ["refresh_token"]=>
  //   string(40) "authusrBb7ac0e83f2da4513a951248c05725F34"
  //   ["user_id"]=>
  //   string(16) "2088622957119341"
  //   ["sign"]=>
  //   string(344) "RbM2dMhdkwWs01DBlTzZFGZNSgZaCmLNSu1SzruvLindDOHet"
  // }
} else {
  echo $objAlipay->errMsg;
}
