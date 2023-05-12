<?php
/*
// 说明: 设置咨询员备注
// 作者: 幽兰 (weelia@126.com)
// 时间: 2016-07-14
*/
require "lib/set_env.php";

$table = "count_chengben_memo";

if ($hid <= 0) exit("请先选择医院科室");

if ($_GET["j_kefu"]) {
	$kefu = trim(mb_convert_encoding($_GET["j_kefu"], "gbk", "UTF-8"));
} else {
	$kefu = trim($_POST["kefu"]);
}

$line = $db->query("select * from $table where hid=$hid and kefu='$kefu' limit 1", 1);

if ($_POST) {
	$memo = trim(strip_tags($_POST["memo"]));
	//$memo = str_replace("\r", "", $memo);
	//$memo = str_replace("\n", "　", $memo);

	if ($line["id"] > 0) {
		if ($memo == "") {
			$db->query("delete from $table where id=".$line["id"]." limit 1");
		} else {
			$db->query("update $table set memo='$memo' where id=".$line["id"]." limit 1");
		}
	} else {
		$time = time();
		if ($memo != "") {
			$db->query("insert into $table set hid=$hid, kefu='$kefu', memo='$memo', addtime=$time, author='$realname'");
		}
	}

	echo '<script> parent.update_content(); </script>';
	echo '<script> parent.msg_box("备注设置成功", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;
}


?>
<html>
<head>
<title>设置备注</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
* {font-family:"Tahoma","微软雅黑"; }
</style>
</head>

<body style="padding:20px; text-align:center;">

<form name="mainform" action="" method="POST">
	<div><b>为“<?php echo $kefu; ?>”设置备注：</b></div>
	<div style="margin-top:10px;"><textarea name="memo"class="input"  style="width:80%; height:80px;"><?php echo $line["memo"]; ?></textarea></div>
	<div style="margin-top:10px;">(限100个汉字以内)</div>
	<div style="margin-top:20px;"><center><input type="submit" class="submit" value="提交资料"></center></div>
	<input type="hidden" name="kefu" value="<?php echo $kefu; ?>">
</form>

</body>
</html>