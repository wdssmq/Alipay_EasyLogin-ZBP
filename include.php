<?php
require dirname(__FILE__) . '/class/login_sdk.php';
require dirname(__FILE__) . '/function.php';
# 注册插件
RegisterPlugin("Alipay_EasyLogin", "ActivePlugin_Alipay_EasyLogin");
DefinePluginFilter('Filter_Plugin_Alipay_EasyLogin_LoginSuccess');

function ActivePlugin_Alipay_EasyLogin()
{
  Add_Filter_Plugin('Filter_Plugin_Index_Begin', 'Alipay_EasyLogin_AutoLogin');
}

function Alipay_EasyLogin_AutoLogin()
{
  global $zbp;
  if ($zbp->user->ID > 0) {
    return;
  }
  Alipay_EasyLogin_Login();
}

function Alipay_EasyLogin_Path($file, $t = 'path')
{
  global $zbp;
  $result = $zbp->$t . 'zb_users/plugin/Alipay_EasyLogin/';
  switch ($file) {
    case 'main':
      return $result . 'main.php';
      break;
    case 'callback':
      return $result . 'callback.php';
      break;
    default:
      return $result . $file;
  }
}

function InstallPlugin_Alipay_EasyLogin()
{
  global $zbp;
  if (!$zbp->HasConfig('Alipay_EasyLogin')) {
    $zbp->Config('Alipay_EasyLogin')->version = 1;
    $zbp->Config('Alipay_EasyLogin')->appid = "";
    $zbp->Config('Alipay_EasyLogin')->private_key = "";
    $zbp->SaveConfig('Alipay_EasyLogin');
  }
}

function UninstallPlugin_Alipay_EasyLogin()
{
}
