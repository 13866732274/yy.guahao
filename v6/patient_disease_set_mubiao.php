<?php
/*
// ˵��: ���ñ�������
// ����: ���� (weelia@126.com)
// ʱ��: 2015-11-14
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
	echo '<script> parent.msg_box("�����������óɹ�", 2); </script>';
	echo '<script> parent.load_src(0); </script>';

	exit;
}


?>
<html>
<head>
<title>��������</title>
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

<center>��������<input name="mubiao" value="<?php echo $old["mubiao"]; ?>" class="input" style="width:100px">����(�����¼��־���������޸�)</center>

<input type="hidden" name="month" value="<?php echo $month; ?>">
<input type="hidden" name="did" value="<?php echo $did; ?>">
<div class="button_line mt20">
	<input id="submit_button" type="submit" class="submit" value="�ύ����">
</div>

</form>

</body>
</html>