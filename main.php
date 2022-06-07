<?php
require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';
$zbp->Load();
$action = 'root';
if (!$zbp->CheckRights($action)) {
  $zbp->ShowError(6);
  die();
}
if (!$zbp->CheckPlugin('Alipay_EasyLogin')) {
  $zbp->ShowError(48);
  die();
}

$act = GetVars('act', 'GET');
$suc = GetVars('suc', 'GET');
if ($act == 'save') {
  CheckIsRefererValid();
  foreach ($_POST as $key => $val) {
    if (substr($key, 0, 5) == 'read_') {
      continue;
    }
    $zbp->Config('Alipay_EasyLogin')->$key = trim($val);
  }
  $zbp->SaveConfig('Alipay_EasyLogin');
  $zbp->BuildTemplate();
  $zbp->SetHint('good');
  Redirect('./main.php' . ($suc == null ? '' : "?act={$suc}"));
}

Alipay_EasyLogin_Init();

$blogtitle = '支付宝登录基础封装';
require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
?>
<div id="divMain">
  <div class="divHeader"><?php echo $blogtitle; ?></div>
  <div class="SubMenu">
  </div>
  <div id="divMain2">
    <form action="<?php echo BuildSafeURL("main.php?act=save"); ?>" method="post">
      <table width="100%" class="tableBorder">
        <tr>
          <th width="10%">项目</th>
          <th>内容</th>
          <th width="45%">说明</th>
        </tr>
        <tr>
          <td>启用登录</td>
          <td><?php zbpform::zbradio("on", $zbp->Config("Alipay_EasyLogin")->on); ?></td>
          <td></td>
        </tr>
        <tr>
          <td>APPID</td>
          <td><?php zbpform::text("appid", $zbp->Config("Alipay_EasyLogin")->appid, "90%"); ?></td>
          <td>
            <p><a href="https://developers.alipay.com/dev/workspace" target="_blank">https://developers.alipay.com/dev/workspace</a> → 网页&移动应用</p>
          </td>
        </tr>
        <tr>
          <td>private_key</td>
          <td><?php zbpform::textarea("private_key", htmlspecialchars($zbp->Config("Alipay_EasyLogin")->private_key), "90%"); ?></td>
          <td>
            <p>验签选「公钥模式」，然后将私钥填写在这里；</p>
            <p>生成密钥 - 支付宝文档中心</p>
            <p><a href="https://opendocs.alipay.com/common/02kipl" target="_blank" title="生成密钥 - 支付宝文档中心">https://opendocs.alipay.com/common/02kipl</a></p>
          </td>
        </tr>
        <tr>
          <td>回调地址</td>
          <td><?php zbpform::text("read_callback", $zbp->Config("Alipay_EasyLogin")->read_callback, "90%"); ?></td>
          <td>
            <p>
              <a href="https://opendocs.alipay.com/common/02qjlq" target="_blank" title="授权回调地址 - 支付宝文档中心">https://opendocs.alipay.com/common/02qjlq</a> ← 授权回调地址
            </p>
          </td>
        </tr>
        <tr>
          <td></td>
          <td colspan="2"><input type="submit" value="提交" /></td>
        </tr>
      </table>
    </form>
  </div>
</div>

<?php
require $blogpath . 'zb_system/admin/admin_footer.php';
RunTime();
?>
