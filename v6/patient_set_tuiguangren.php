<?php
// --------------------------------------------------------
// - 功能说明 : 设置推广人
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2015-8-21
// --------------------------------------------------------
require "lib/set_env.php";

if ($debug_mode) {
	$config["allow_edit_tuiguangren"] = 1;
}

if ($hid <= 0) exit("没有选择科室...");

$table = "patient_".$hid;
$hline = $db->query("select * from hospital where id=$hid limit 1", 1);
$cur_hname = $hline["name"];


// 当前要转的患者信息:
$patient_id = intval($_REQUEST["patient_id"]);
if ($patient_id <= 0) {
	exit("参数错误");
}
$patient_info = $db->query("select * from $table where id=$patient_id limit 1", 1);



if ($patient_info["tuiguangren"] != '') {
	if (!$config["allow_edit_tuiguangren"]) {
		exit("<title>禁止修改</title>当前推广人设置为：".$patient_info["tuiguangren"]."　　(您没有权限修改)");
	}
}


// 执行操作:
if ($_POST["op"] == "submit") {
	$tuiguangren = trim($_POST["tuiguangren"]);
	$tuiguangren = str_replace(" ", "", $tuiguangren);
	$tuiguangren = str_replace("\n", "", $tuiguangren);
	$tuiguangren = str_replace("\r", "", $tuiguangren);
	$tuiguangren = str_replace("'", "", $tuiguangren);
	$tuiguangren = str_replace('"', "", $tuiguangren);

	if ($tuiguangren != $patient_info["tuiguangren"]) {
		$log = date("Y-m-d H:i:s ").$realname." 将推广人由【".$patient_info["tuiguangren"]."】修改为【".$tuiguangren."】";
		$update_log = ltrim(rtrim($patient_info["edit_log"])."\r\n".$log);
		$db->query("update $table set tuiguangren='$tuiguangren', edit_log='$update_log' where id=$patient_id limit 1");

		echo "<script> parent.load_box(0); parent.update_content(); </script>";
	} else {
		echo "<script> parent.load_box(0); </script>";
	}
	exit;
}





?>
<html>
<head>
<title>设置推广人</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
* {font-family:"微软雅黑" !important; }
select {font-family:"宋体" !important; }
.l {text-align:right; border-bottom:0px solid #D8D8D8; padding:6px 20px 6px 0px; width:200px; }
.r {text-align:left; border-bottom:0px solid #D8D8D8; padding:6px 6px; }
</style>
<script language="javascript">
function check_data(f) {
	if (f.tuiguangren.value == '') {
		alert("对不起，请填写推广人再保存。");
		return false;
	}
	if (f.tuiguangren.value.length > 3) {
		alert("对不起，推广人姓名太长了，最多只能填3个汉字 （只能填一个推广人）");
		return false;
	}
	return true;
}
</script>
</head>

<body>

<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" style="margin-top:30px;">

	<tr>
		<td class="l">推广人：</td>
		<td class="r">
			<input type="input" name="tuiguangren" value="<?php echo $patient_info["tuiguangren"]; ?>">　(只能填一个名字)
		</td>
	</tr>

</table>

<div class="button_line">
	<input type="submit" class="submit" value="保存">
</div>

<input type="hidden" name="patient_id" id="patient_id" value="<?php echo $patient_id; ?>">
<input type="hidden" name="op" value="submit">
</form>

</body>
</html>