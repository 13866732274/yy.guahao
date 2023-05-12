<?php
// --------------------------------------------------------
// - 功能说明 : 设置自动统计的调用范围
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-07-15
// --------------------------------------------------------
require "lib/set_env.php";

$type_id = intval($_REQUEST["id"]);
if ($type_id == 0) exit("参数错误~");

$type_info = $db->query("select * from count_type where id=$type_id limit 1", 1);
$hid = $type_info["hid"];
$hospital_name = $db->query("select sname from hospital where id=$hid limit 1", 1, "sname");
$hospital_id_name = $db->query("select id, name from hospital where ishide=0 and sname='$hospital_name' order by name asc", "id", "name");
$hospital_ids = count($hospital_id_name) ? implode(",", array_keys($hospital_id_name)) : "0";

$hospital_id_name[-1] = "**本院全部科室**";


if ($_POST) {
	$hids = trim(@implode(",", $_POST["hids"]), ",");
	$db->query("update count_type set data_hids='$hids' where id=$type_id limit 1");

	echo '<script> parent.msg_box("设置保存成功", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;

}



$cur_hids = $type_info["data_hids"] != "" ? explode(",", $type_info["data_hids"]) : array();


?>
<html>
<head>
<title>设置自动统计的调用范围</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
* {font-family:"Tahoma","微软雅黑";}
.hid_line {margin-top:8px; margin-left:150px; }
</style>
</head>

<body>
<form name="mainform" action="" method="POST">


<?php foreach ($hospital_id_name as $_hid => $_hname) { ?>
<div class="hid_line"><input type="checkbox" name="hids[]" value="<?php echo $_hid; ?>" id="h_<?php echo $_hid; ?>" <?php echo in_array($_hid, $cur_hids) ? "checked" : ""; ?>><label for="h_<?php echo $_hid; ?>"><?php echo $_hname; ?></label></div>
<?php } ?>

<center>
	<br>
	(注：全部留空不选，则仅调用其自身科室的数据)
	<br>
	<br>
	<input type="submit" class="submit" value="确定">
</center>
	<br>

</form>
</body>
</html>