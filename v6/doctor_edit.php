<?php
// --------------------------------------------------------
// - ����˵�� : ҽ���������޸�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-02 16:44
// --------------------------------------------------------
require "lib/set_env.php";
$table = "doctor";

$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";

if ($mode == "edit") {
	check_power("e", $pinfo, $pagepower) or exit("�Բ�����û���޸�Ȩ��...");
} else {
	check_power("i", $pinfo, $pagepower) or exit("�Բ�����û������Ȩ��...");
}

if ($_POST) {
	$r = array();
	$r["doctor_num"] = $_POST["doctor_num"];
	$r["name"] = $_POST["name"];
	$r["intro"] = $_POST["intro"];

	// 2013-11-18
	$r["max_weekday"] = intval($_POST["max_weekday"]);
	$r["max_weekend"] = intval($_POST["max_weekend"]);

	if ($mode == "add") {
		$r["hospital_id"] = $user_hospital_id;
		$r["addtime"] = time();
		$r["author"] = $username;
	}

	$sqldata = $db->sqljoin($r);
	if ($mode == "edit") {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	} else {
		$sql = "insert into $table set $sqldata";
	}

	if ($db->query($sql)) {
		echo '<script> parent.update_content(); </script>';
		echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "�ύʧ�ܣ����Ժ����ԣ�";
	}
	exit;
}

if ($mode == "edit") {
	$line = $db->query_first("select * from $table where id='$id' limit 1");
}
$title = $mode == "edit" ? "�޸�ҽ������" : "���ҽ��";

$hospital_list = $db->query("select id,name from hospital");
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function Check() {
	var oForm = document.mainform;
	if (oForm.name.value == "") {
		alert("�����롰ҽ�����֡���"); oForm.name.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>
<div class="description">
	<div class="d_title">��ʾ��</div>
	<div class="d_item">1.����ҽ�����ּ���飨���ɲ����룩������ύ����</div>
</div>

<div class="space"></div>
<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">ҽ������</td>
	</tr>
	<tr>
		<td class="left">ҽ�����֣�</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">* ҽ�����ֱ�����д</span></td>
	</tr>
	<tr>
		<td class="left">ҽ����ţ�</td>
		<td class="right"><input name="doctor_num" value="<?php echo $line["doctor_num"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">ѡ��</span></td>
	</tr>
	<tr>
		<td class="left">ҽ����飺</td>
		<td class="right"><textarea name="intro" class="input" style="width:80%; height:80px; overflow:visible; vertical-align:middle;"><?php echo $line["intro"]; ?></textarea> <span class="intro">ѡ��</span></td>
	</tr>
	<tr>
		<td class="left">��������������</td>
		<td class="right">
			������ <input name="max_weekday" value="<?php echo $line["max_weekday"]; ?>" class="input" style="width:80px"> &nbsp;&nbsp;
			��ĩ <input name="max_weekend" value="<?php echo $line["max_weekend"]; ?>" class="input" style="width:80px"> &nbsp;&nbsp;
			<span class="intro">0Ϊ����</span>
		</td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>