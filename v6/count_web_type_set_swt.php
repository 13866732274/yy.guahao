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

	$local_area = trim($_POST["local_area"]);

	$swt_name_arr = array();
	foreach ($_POST["swt"] as $name => $swt_name) {
		if ($swt_name != "") {
			$swt_name_arr[] = $name."=".trim($swt_name);
		}
	}

	$swt_names = implode("\n", $swt_name_arr);
	$db->query("update $table set local_area='$local_area', swt_names='$swt_names' where id=$id limit 1");

	echo '<script> parent.msg_box("更新成功", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;
}

?>
<html>
<head>
<title>设置商务通名单</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>

<style>
div {padding:2px;  }
nobr {display:inline-block; width:100px; text-align:right;  }
</style>

</head>

<body style="padding:30px 30px;">

<center>
<form name="mainform" action="" method="POST">
<div style="padding:15px; ">本地判断关键词：<input name="local_area" value="<?php echo $line["local_area"]; ?>" class="input">　(如 苏州市)</div>
<?php
$kefu_names = explode(",", trim($line["kefu"]));
$swt_names = str_replace("\r", "", trim($line["swt_names"]));
$swt_arr = explode("\n", $swt_names);
$kefu_swt_arr = array();
foreach ($swt_arr as $v) {
	list($a, $b) = explode("=", $v, 2);
	if ($a != '' && $b != '') {
		$kefu_swt_arr[$a] = $b;
	}
}

foreach ($kefu_names as $kf) {
	echo '<div><nobr>'.$kf.'</nobr> = <input name="swt['.$kf.']" value="'.$kefu_swt_arr[$kf].'" class="input" style="width:100px;"></div>';
}
?>


<input type="hidden" name="id" value="<?php echo $id; ?>">

<div class="button_line">
	<input type="submit" class="submit" value="提交资料">
</div>
</form>
</center>

</body>
</html>