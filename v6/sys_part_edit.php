<?php
// --------------------------------------------------------
// - ����˵�� : �����������޸�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-03-30 13:30
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_part";

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
	$r["sort"] = intval($_POST["sort"]);

	if ($mode == "add") {
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
		// �������ڵĴ���ʽ:
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

?>
<html>
<head>
<title><?php echo $id ? "�޸Ĳ���" : "��Ӳ���"; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function Check() {
	var oForm = document.mainform;
	if (oForm.name.value == "") {
		alert("�����롰�������ơ���");
		oForm.name.focus();
		return false;
	}
	return true;
}
</script>
</head>

<body>

<div class="space"></div>
<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">��������</td>
	</tr>
	<tr>
		<td class="left" style="width:25%"><font color="red">*</font> �������ƣ�</td>
		<td class="right" style="width:75%"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">���Ʊ�����д</span></td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> ���ȶȣ�</td>
		<td class="right">
			<input name="sort" value="<?php echo $line["sort"]; ?>" class="input" size="30" style="width:200px">
			<span class="intro">�������� Ĭ��Ϊ0 ��ֵ����ĩβ</span>
		</td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>