<?php
/*
// 作者: 幽兰 (weelia@126.com)
*/
require "lib/set_env.php";
$table = "sys_character";

$id = intval($_REQUEST["id"]);
if ($id <= 0) {
	exit("对不起， 参数id错误...");
}


$line = $db->query("select * from $table where id='$id' limit 1", 1);

$config = @unserialize($line["config"]);

$part_arr = $db->query("select id,name from sys_part", "id", "name");
$character_arr = $db->query("select id,name from sys_character order by sort desc, id asc", "id", "name");


if ($_POST) {

	if ($line["addtime"] != $_POST["crc"]) {
		exit("crc校验失败...");
	}

	ob_start();
	$set_arr = $_POST["config"];
	$set_str = serialize($set_arr);

	$db->query("update $table set `config`='$set_str' where id='$id' limit 1");
	$err = ob_get_clean();

	if ($err) {
		echo "提交失败，请稍后再试:<br>".$err;
	} else {
		echo '<script> parent.msg_box("资料提交成功", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	}
	exit;

}

?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title>设置权限</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.wee_new_edit {border:1px solid #c6c6c6; }
.wee_new_edit td {border-top:1px dotted #c6c6c6; }
.wee_new_edit .left {width:18%; text-align:right; padding:5px; background-color:#f3f3f3; }
.wee_new_edit .right {text-align:left; padding:5px; }
</style>
<script type="text/javascript">
function check_data(o) {
	return true;
}
</script>
</head>

<body>

<form method="POST" onsubmit="return check_data(this);">

<table width="100%" class="wee_new_edit">

	<tr>
		<td class="left">企划数据：</td>
		<td class="right">
			<input type="checkbox" name="config[show_list_all]" value="1" <?php if ($config["show_list_all"]) echo "checked"; ?> id="show_list_all"><label for="show_list_all">显示汇总的列表</label>&nbsp;
			<input type="checkbox" name="config[duokeshi_huizong]" value="1" <?php if ($config["duokeshi_huizong"]) echo "checked"; ?> id="duokeshi_huizong"><label for="duokeshi_huizong">多科室汇总</label>&nbsp;
		</td>
	</tr>

	<tr>
		<td class="left">显示消费额：</td>
		<td class="right">
			<select name="config[show_xiaofei]" class="combo">
				<option value="0" style="color:silver;">-不显示-</option>
<?php
$_arr = array(
	"1" => "显示其自挂病人的消费额",
	"2" => "显示所有病人的消费额",
);

echo list_option($_arr, "_key_", "_value_", $config["show_xiaofei"]);
?>
			</select> <span class='intro'>请酌情选择，设置不当可能导致消费数据泄漏。</span>
		</td>
	</tr>


	<tr>
		<td class="left">登录手机版：</td>
		<td class="right">
			<input type="checkbox" name="config[allow_mobile_login]" value="1" id="allow_mobile_login" <?php echo $config["allow_mobile_login"] ? "checked" : ""; ?>><label for="allow_mobile_login">勾选则允许登录手机版 (默认情况为不允许)</label>
		</td>
	</tr>

	<tr>
		<td class="left">搜索来源：</td>
		<td class="right">
			<input type="checkbox" name="config[show_engine]" value="1" id="show_engine" <?php echo $config["show_engine"] ? "checked" : ""; ?>><label for="show_engine">勾选则有“搜索渠道”和“网站来源”显示权限 (默认没有)</label>
		</td>
	</tr>

	<tr>
		<td class="left">资料库：</td>
		<td class="right">
			<input type="checkbox" name="config[show_ziliaoku]" value="1" id="show_ziliaoku" <?php echo $config["show_ziliaoku"] ? "checked" : ""; ?>><label for="show_ziliaoku">勾选开通“资料库”功能</label>&nbsp;&nbsp;
			<input type="checkbox" name="config[show_all_remind]" value="1" id="show_all_remind" <?php echo $config["show_all_remind"] ? "checked" : ""; ?>><label for="show_all_remind">查看所有回访提醒(适用组长)</label>
		</td>
	</tr>

	<tr>
		<td class="left">轨迹数据：</td>
		<td class="right"><input type="checkbox" name="config[show_guiji]" value="1" <?php if ($config["show_guiji"]) echo "checked"; ?> id="show_guiji"><label for="show_guiji">显示轨迹数据</label></td>
	</tr>

	<tr>
		<td class="left">情况备注：</td>
		<td class="right"><input type="checkbox" name="config[zixun_memo]" value="1" <?php if ($config["zixun_memo"]) echo "checked"; ?> id="zixun_memo"><label for="zixun_memo">查看和修改情况备注</label></td>
	</tr>


	<tr>
		<td class="left">网站显示：</td>
		<td class="right">
			<input type="checkbox" name="config[show_site]" value="1" <?php if ($config["show_site"]) echo "checked"; ?> id="show_site"><label for="show_site">显示和修改项目网站</label>&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="config[show_site_out_date]" value="1" <?php if ($config["show_site_out_date"]) echo "checked"; ?> id="show_site_out_date"><label for="show_site_out_date">在首页提醒即将过期的域名</label>  &nbsp; &nbsp;
		</td>
	</tr>

	<tr>
		<td class="left">已到患者：</td>
		<td class="right">
			<input type="checkbox" name="config[show_come_tel]" value="1" <?php if ($config["show_come_tel"]) echo "checked"; ?> id="show_come_tel"><label for="show_come_tel">显示其号码(否则一律隐藏)</label>&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="config[show_come_doctor]" value="1" <?php if ($config["show_come_doctor"]) echo "checked"; ?> id="show_come_doctor"><label for="show_come_doctor">显示接诊医生(否则一律隐藏)</label>
		</td>
	</tr>

	<tr>
		<td class="left">回访提醒：</td>
		<td class="right">
			<input type="checkbox" name="config[show_remind_all]" value="1" <?php if ($config["show_remind_all"]) echo "checked"; ?> id="show_remind_all"><label for="show_remind_all">显示回访提醒列表</label>&nbsp;&nbsp;&nbsp;
			<!-- <input type="checkbox" name="config[show_remind_index]" value="1" <?php if ($config["show_remind_index"]) echo "checked"; ?> id="show_remind_index"><label for="show_remind_index">在首页显示回访汇总</label> -->
		</td>
	</tr>

	<tr>
		<td class="left">对比曲线：</td>
		<td class="right">
			<input type="checkbox" name="config[show_jiuzhenlv_compare]" value="1" <?php if ($config["show_jiuzhenlv_compare"]) echo "checked"; ?> id="show_jiuzhenlv_compare"><label for="show_jiuzhenlv_compare">开通咨询就诊率对比功能</label>&nbsp;&nbsp;&nbsp;
		</td>
	</tr>

	<tr>
		<td class="left">推广人：</td>
		<td class="right">
			<input type="checkbox" name="config[allow_edit_tuiguangren]" value="1" <?php if ($config["allow_edit_tuiguangren"]) echo "checked"; ?> id="allow_edit_tuiguangren"><label for="allow_edit_tuiguangren">允许修改</label>&nbsp;&nbsp;&nbsp;
		</td>
	</tr>

</table>

<div class="space"></div>

<div class="button_line">
	<input type="submit" class="submit" value="提交修改">
</div>

<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="crc" value="<?php echo $line["addtime"]; ?>">

</form>

</body>
</html>