<?php
// --------------------------------------------------------
// - ����˵�� : �������
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-11-02
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
	echo '<script> parent.msg_box("����ɹ�", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;
}

?>
<html>
<head>
<title>������Ӽ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
</head>

<body style="padding:20px;">
<div class="space"></div>
<form name="mainform" action="" method="POST">
<center>
	�������ƣ�<font color="gray">(ÿ����һ��)</font><br>
	<textarea name="disease_names" class="input" style="width:300px; height:300px; margin-top:10px;"></textarea></td>

	<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</center>
</form>
</body>
</html>