<?php
// --------------------------------------------------------
// - 功能说明 : 新增，修改
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2010-10-13 11:40
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_type";

$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";

if ($id > 0) {
	$line = $db->query("select * from $table where id='$id' limit 1", 1);
}

if ($_POST) {
	$type_id = $id;

	$name = trim($_POST["name"]);
	$kefu_list = implode(",", $_POST["kefu"]);
	$db->query("update $table set name='$name', kefu='$kefu_list' where id=$id limit 1");

	echo '<script> parent.msg_box("更新成功", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;
}

?>
<html>
<head>
<title>设置统计子项</title>
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
		<td class="left"><font color="red">*</font> 子项名称：</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" style="width:250px"></td>
	</tr>

	<tr>
		<td class="left" valign="top" style="padding-top:6px;"><font color="red">*</font> 客服名单：</td>
		<td class="right new_kefu">
<?php
$cur_hid = $id > 0 ? $line["hid"] : $_GET["hid"];
$kefu_names = explode(",", trim($line["kefu"]));

// 在整个医院范围内查询组员 (防止遗漏)
$sname = $db->query("select sname from hospital where id=$cur_hid", 1, "sname");
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

<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="hid" value="<?php echo $cur_hid; ?>">
<input type="hidden" name="type" value="web">

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>