<?php
// --------------------------------------------------------
// - ����˵�� : ý�������������޸�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-03 14:48
// --------------------------------------------------------
require "lib/set_env.php";
$table = "media";

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

	if ($r["name"] == "����" || $r["name"] == "�绰") {
		exit("�����硱�͡��绰��Ϊϵͳ����ý����Դ������Ҫ�ڴ���ӡ�");
	}

	$r["hospital_id"] = intval($_POST["media_type"]);

	$r["is_often"] = $_POST["is_often"] ? 1 : 0;

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
$title = $mode == "edit" ? "�޸�ý����Դ" : "����µ���Դ";

$_hid = $mode == "edit" ? $line["hospital_id"] : intval($_GET["hid"]);

// ����ѡ��:
$shid = $_hid ? $_hid : $hid;
$h_name = $db->query("select name from hospital where id=$shid limit 1", 1, "name");

$media_type_arr = array("0" => "ȫ��ý����Դ", $shid => "��".$h_name."��˽��ý����Դ");

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
		alert("�����롰���ơ���"); oForm.name.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>
<div class="description">
	<div class="d_title">ע�⣺�����硱�͡��绰��Ϊϵͳ����ý����Դ������Ҫ�ڴ���ӡ�</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">ý����������</td>
	</tr>
	<tr>
		<td class="left">���ͣ�</td>
		<td class="right">
			<select name="media_type" class="combo">
				<?php echo list_option($media_type_arr, "_key_", "_value_", $mode == "edit" ? $line["hospital_id"] : $_GET["hid"]); ?>
			</select>
			<span class="intro">ý�����ͱ�����д</span>
		</td>
	</tr>
	<tr>
		<td class="left">���ƣ�</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">���Ʊ�����д</span></td>
	</tr>
	<tr>
		<td class="left">�Ƿ��ã�</td>
		<td class="right"><input type="checkbox" name="is_often" value="1" id="is_often" <?php echo $line["is_often"] ? "checked" : ""; ?>><label for="is_often">�Ƿ���ý����Դ������ý�彫�����г���</label></td>
	</tr>
	<tr>
		<td class="left">����</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">Խ��Խ��ǰ������Ϊ��ֵ�������</span></td>
	</tr>
</table>

<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>