<?php
// --------------------------------------------------------
// - ����˵�� : ҽԺ�������޸�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-01 00:40
// --------------------------------------------------------
require "lib/set_env.php";
$table = "disease";

$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";

if ($mode == "edit") {
	check_power("e", $pinfo, $pagepower) or exit("�Բ�����û���޸�Ȩ��...");
} else {
	check_power("i", $pinfo, $pagepower) or exit("�Բ�����û������Ȩ��...");
}

if ($_POST) {
	$r = array();
	$r["name"] = $_POST["name"];

	// �����������ո��滻Ϊһ��:
	for ($i=1; $i<=5; $i++) {
		$_POST["disease_2"] = str_replace("  ", " ", $_POST["disease_2"]);
		$_POST["xiangmu"] = str_replace("  ", " ", $_POST["xiangmu"]);
	}

	$r["xiangmu"] = $_POST["xiangmu"];
	$r["disease_2"] = $_POST["disease_2"];
	$r["intro"] = $_POST["intro"];
	$r["sort"] = $_POST["sort"];


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
$title = ($mode == "edit") ? "�޸ļ�������" : "����µļ���";

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
		alert("�����롰�������ơ���"); oForm.name.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>
<div class="description">
	<div class="d_title">��ʾ��</div>
	<div class="d_item">1.���뼲�����Ƽ���飨���ɲ����룩������ύ����</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">��������</td>
	</tr>
	<tr>
		<td class="left">�������ƣ�</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <font color="gray">���Ʊ�����д</font></td>
	</tr>
	<tr>
		<td class="left">����������</td>
		<td class="right"><textarea name="disease_2" class="input" style="width:500px; height:80px; overflow:visible; vertical-align:middle;"><?php echo $line["disease_2"]; ?></textarea> <font color="gray">����ÿո����</font></td>
	</tr>
	<tr>
		<td class="left">������Ŀ��</td>
		<td class="right"><textarea name="xiangmu" class="input" style="width:500px; height:80px; overflow:visible; vertical-align:middle;"><?php echo $line["xiangmu"]; ?></textarea> <font color="gray">����ÿո����</font></td>
	</tr>
	<tr>
		<td class="left">������飺</td>
		<td class="right"><textarea name="intro"class="input"  style="width:500px; height:80px; overflow:visible; vertical-align:middle;"><?php echo $line["intro"]; ?></textarea> <font color="gray">ѡ��</font></td>
	</tr>
	<tr>
		<td class="left">���ȶȣ�</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" size="10" style="width:200px"> <font color="gray">Խ��Խ���ȣ���Ϊ����������ĩβ</font></td>
	</tr>
</table>
<?php if ($mode == "edit") { ?>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<?php } ?>
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>