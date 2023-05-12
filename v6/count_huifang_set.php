<?php
// --------------------------------------------------------
// - 功能说明 : 设置客服
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-12-02
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_huifang_set";
include "count_huifang.inc.php";

$hid = intval($_REQUEST["hid"]);
$line = $db->query("select * from $table where hid=$hid limit 1", 1);

if ($_POST) {
	$kefu_list = implode(",", $_POST["kefu"]);
	if ($line["hid"] > 0) {
		$db->query("update $table set kefu_names='$kefu_list' where hid=$hid limit 1");
	} else {
		$db->query("insert into $table set hid=$hid, kefu_names='$kefu_list'");
	}

	echo '<script> parent.msg_box("更新成功", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;
}

?>
<html>
<head>
<title>设置客服 - <?php echo $sys_yiyuan_arr[$hid]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>

<style>
.new_kefu nobr {padding:2px; width:100px; float:left; display:block; overflow:hidden; }
.new_edit .left {width:15% !important; }
.new_edit .right {width:85% !important; }
</style>

</head>

<body>

<div class="space"></div>
<form name="mainform" action="" method="POST">
<table width="100%" class="new_edit">
	<tr>
		<td class="left" valign="top" style="padding-top:6px;"><font color="red">*</font> 请选择回访客服：</td>
		<td class="right new_kefu">
<?php
$cur_hid = $id > 0 ? $line["hid"] : $_GET["hid"];
$kefu_names = explode(",", trim($line["kefu_names"]));

// 在整个医院范围内查询组员 (防止遗漏)
$sname = $db->query("select sname from hospital where id=$hid", 1, "sname");
$sid_arr = $db->query("select id from hospital where ishide=0 and sname='$sname'", "", "id");
foreach ($sid_arr as $_hid) {
	$my_where[] = "concat(',',hospitals,',') like '%,{$_hid},%'";
}
$sql_where = "(".implode(" or ", $my_where).")";
$cur_hospital_kefu_arr = $db->query("select realname from sys_admin where part_id in (2,3,12) and character_id not in (4,5,19,20,32,15,16,17) and isshow=1 and $sql_where order by part_id asc, realname asc", "", "realname");

foreach ($cur_hospital_kefu_arr as $kf) {
	$check = in_array($kf, $kefu_names) ? " checked" : "";
	$name_color = in_array($kf, $kefu_names) ? "red" : "";
	echo '<nobr><input type="checkbox" name="kefu[]" value="'.$kf.'"'.$check.'><span style="color:'.$name_color.'">'.$kf.'</span></nobr> ';
}
?>

		</td>
	</tr>

</table>

<input type="hidden" name="hid" value="<?php echo $hid; ?>">

<div class="button_line">
	<input type="submit" class="submit" value="提交资料">
</div>

</form>
</body>
</html>