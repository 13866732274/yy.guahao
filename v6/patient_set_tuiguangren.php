<?php
// --------------------------------------------------------
// - ����˵�� : �����ƹ���
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2015-8-21
// --------------------------------------------------------
require "lib/set_env.php";

if ($debug_mode) {
	$config["allow_edit_tuiguangren"] = 1;
}

if ($hid <= 0) exit("û��ѡ�����...");

$table = "patient_".$hid;
$hline = $db->query("select * from hospital where id=$hid limit 1", 1);
$cur_hname = $hline["name"];


// ��ǰҪת�Ļ�����Ϣ:
$patient_id = intval($_REQUEST["patient_id"]);
if ($patient_id <= 0) {
	exit("��������");
}
$patient_info = $db->query("select * from $table where id=$patient_id limit 1", 1);



if ($patient_info["tuiguangren"] != '') {
	if (!$config["allow_edit_tuiguangren"]) {
		exit("<title>��ֹ�޸�</title>��ǰ�ƹ�������Ϊ��".$patient_info["tuiguangren"]."����(��û��Ȩ���޸�)");
	}
}


// ִ�в���:
if ($_POST["op"] == "submit") {
	$tuiguangren = trim($_POST["tuiguangren"]);
	$tuiguangren = str_replace(" ", "", $tuiguangren);
	$tuiguangren = str_replace("\n", "", $tuiguangren);
	$tuiguangren = str_replace("\r", "", $tuiguangren);
	$tuiguangren = str_replace("'", "", $tuiguangren);
	$tuiguangren = str_replace('"', "", $tuiguangren);

	if ($tuiguangren != $patient_info["tuiguangren"]) {
		$log = date("Y-m-d H:i:s ").$realname." ���ƹ����ɡ�".$patient_info["tuiguangren"]."���޸�Ϊ��".$tuiguangren."��";
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
<title>�����ƹ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
* {font-family:"΢���ź�" !important; }
select {font-family:"����" !important; }
.l {text-align:right; border-bottom:0px solid #D8D8D8; padding:6px 20px 6px 0px; width:200px; }
.r {text-align:left; border-bottom:0px solid #D8D8D8; padding:6px 6px; }
</style>
<script language="javascript">
function check_data(f) {
	if (f.tuiguangren.value == '') {
		alert("�Բ�������д�ƹ����ٱ��档");
		return false;
	}
	if (f.tuiguangren.value.length > 3) {
		alert("�Բ����ƹ�������̫���ˣ����ֻ����3������ ��ֻ����һ���ƹ��ˣ�");
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
		<td class="l">�ƹ��ˣ�</td>
		<td class="r">
			<input type="input" name="tuiguangren" value="<?php echo $patient_info["tuiguangren"]; ?>">��(ֻ����һ������)
		</td>
	</tr>

</table>

<div class="button_line">
	<input type="submit" class="submit" value="����">
</div>

<input type="hidden" name="patient_id" id="patient_id" value="<?php echo $patient_id; ?>">
<input type="hidden" name="op" value="submit">
</form>

</body>
</html>