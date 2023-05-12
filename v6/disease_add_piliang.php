<?php
// --------------------------------------------------------
// - 功能说明 : 批量添加
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-11-02
// --------------------------------------------------------
require "lib/set_env.php";
$table = "disease";

if ($_POST) {
	$r = array();
	$names = trim($_POST["disease_names"]);
	$names = str_replace("\r", "", $names);
	$name_arr = explode("\n", $names);
	foreach ($name_arr as $name) {
		$name = trim($name);
		if ($name != "") {
			$r = array();
			$r["name"] = trim($name);
			$r["hospital_id"] = $hid;
			$r["addtime"] = time();
			$r["author"] = $username;
			$db->insert($table, $r);
		}
	}

	echo '<script> parent.update_content(); </script>';
	echo '<script> parent.msg_box("保存成功", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;
}

?>
<html>
<head>
<title>批量添加疾病</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
</head>

<body style="padding:20px;">
<div class="space"></div>
<form name="mainform" action="" method="POST">
<center>
	疾病名称：<font color="gray">(每行填一个)</font><br>
	<textarea name="disease_names" class="input" style="width:300px; height:300px; margin-top:10px;"></textarea></td>

	<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</center>
</form>
</body>
</html>