<?php
/*
// 说明: 设置本月任务
// 作者: 幽兰 (weelia@126.com)
// 时间: 2015-11-14
*/
require "lib/set_env.php";

$month = intval($_REQUEST["month"]);
$did = intval($_REQUEST["did"]);

$old = $db->query("select * from disease_mubiao where dis_id=$did and month='$month' limit 1", 1);
if ($old["id"] > 0) {
	$mode = "edit";
} else {
	$mode = "add";
}

if ($_POST) {

	$mubiao = trim($_POST["mubiao"]);

	if ($mode == "add") {
		$db->query("insert into disease_mubiao set dis_id=$did, month='$month', mubiao='$mubiao', author='$realname'");
	} else {
		$db->query("update disease_mubiao set mubiao='$mubiao', author='$realname' where id=".$old["id"]." limit 1");
	}


	echo '<script> parent.update_content(); </script>';
	echo '<script> parent.msg_box("本月任务设置成功", 2); </script>';
	echo '<script> parent.load_src(0); </script>';

	exit;
}


?>
<html>
<head>
<title>设置任务</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script type="text/javascript">
function check_data() {
	return true;
}
</script>
</head>

<body>
<form name="mainform" action="" method="POST" onsubmit="return check_data()" style="margin-top:30px;">

<center>本月任务：<input name="mubiao" value="<?php echo $old["mubiao"]; ?>" class="input" style="width:100px">　　(将会记录日志，请慎重修改)</center>

<input type="hidden" name="month" value="<?php echo $month; ?>">
<input type="hidden" name="did" value="<?php echo $did; ?>">
<div class="button_line mt20">
	<input id="submit_button" type="submit" class="submit" value="提交数据">
</div>

</form>

</body>
</html>