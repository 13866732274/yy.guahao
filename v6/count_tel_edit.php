<?php
// --------------------------------------------------------
// - ����˵�� : ��ӡ��޸�����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2010-10-08 13:31
// --------------------------------------------------------

$date = $_REQUEST["date"];
if (!$date) {
	exit("��������");
}

$kefu = $_GET["kefu"];
if (!$kefu) {
	exit("��������");
}

if ($_POST) {
	$r = array();

	// �ж��Ƿ��Ѿ����:
	$mode = "add";
	$s_date = date("Ymd", strtotime($date." 0:0:0"));
	$kefu = $_POST["kefu"];
	$cur_id = $db->query("select id from $table where type_id=$cur_type and kefu='$kefu' and date='$s_date' limit 1", 1, "id");
	if ($cur_id > 0) {
		$mode = "edit";
		$id = $cur_id;
	}

	if ($mode == "add") {
		$r["type_id"] = $cur_type;
		$r["type_name"] = $db->query("select name from count_type where id=".$r["type_id"]." limit 1", 1, "name");
		$r["date"] = $s_date;
		$r["kefu"] = $_POST["kefu"];
	}

	$r["zongliang"] = $_POST["zongliang"];
	$r["tel_all"] = $_POST["tel_all"];
	$r["tel_ok"] = $_POST["tel_ok"];
	$r["yuyue"] = $_POST["yuyue"];
	$r["yudao"] = $_POST["yudao"];
	$r["jiuzhen"] = $_POST["jiuzhen"];

	$r["wangluo"] = $_POST["wangluo"];
	$r["wuxian"] = $_POST["wuxian"];
	$r["ditu"] = $_POST["ditu"];
	$r["guahaowang"] = $_POST["guahaowang"];
	$r["qita"] = $r["jiuzhen"] - $r["wangluo"] - $r["wuxian"] - $r["ditu"] - $r["guahaowang"];
	if ($r["qita"] < 0) {
		$r["qita"] = 0;
	}

	if ($mode == "add") {
		$r["uid"] = $uid;
		$r["uname"] = $realname;
		$r["addtime"] = time();
	}

	$sqldata = $db->sqljoin($r);
	if ($mode == "add") {
		$sql = "insert into $table set $sqldata";
	} else {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	}

	if ($db->query($sql)) {
		if ($mode == "add") {
			echo '<script> parent.update_content(); </script>';
			echo '<script> parent.msg_box("��ӳɹ�", 2); </script>';
		} else {
			echo '<script> parent.msg_box("�޸ĳɹ����б�δ����", 2); </script>';
		}
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "�ύʧ�ܣ����Ժ����ԣ�";
	}
	exit;
}

if ($op == "edit") {
	$s_date = date("Ymd", strtotime($date." 0:0:0"));
	$line = $db->query("select * from $table where type_id=$cur_type and kefu='$kefu' and date='$s_date' limit 1", 1);
}


$title = $op == "edit" ? "�޸�����" : "�������";
?>
<html>
<head>
<title><?php echo $title; ?> (<?php echo $date; ?>: <?php echo $kefu; ?>)</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.item {padding:8px 3px 6px 3px; }
.left {width:auto !important; }
.right {width:auto !important; }
</style>

<script language="javascript">
function check_data() {
	var oForm = document.mainform;
	if (oForm.code.value == "") {
		alert("�����롰��š���"); oForm.code.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>
<form name="mainform" action="" method="POST" onsubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="4" class="head">�绰ͳ������</td>
	</tr>

	<tr>
		<td class="left" style="width:25% !important;">�ܴ���绰����</td>
		<td class="right" colspan="3">
			<input name="zongliang" value="<?php echo $line["zongliang"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left" style="width:25% !important;">�ܵ绰��</td>
		<td class="right" style="width:25% !important;">
			<input name="tel_all" value="<?php echo $line["tel_all"]; ?>" class="input" style="width:100px">
		</td>
		<td class="left" style="width:10% !important;">��Ч��</td>
		<td class="right">
			<input name="tel_ok" value="<?php echo $line["tel_ok"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left">ԤԼ��</td>
		<td colspan="3" class="right">
			<input name="yuyue" value="<?php echo $line["yuyue"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left">Ԥ����</td>
		<td class="right">
			<input name="yudao" value="<?php echo $line["yudao"]; ?>" class="input" style="width:100px">
		</td>
		<td class="left">ʵ����</td>
		<td class="right">
			<input name="jiuzhen" value="<?php echo $line["jiuzhen"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td colspan="4" class="head">��ϸ��Ժ����</td>
	</tr>
	<tr>
		<td class="left">���磺</td>
		<td class="right">
			<input name="wangluo" value="<?php echo $line["wangluo"]; ?>" class="input" style="width:100px">
		</td>
		<td class="left">���ߣ�</td>
		<td class="right">
			<input name="wuxian" value="<?php echo $line["wuxian"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left">��ͼ��</td>
		<td class="right">
			<input name="ditu" value="<?php echo $line["ditu"]; ?>" class="input" style="width:100px">
		</td>
		<td class="left">�Һ�����</td>
		<td class="right">
			<input name="guahaowang" value="<?php echo $line["guahaowang"]; ?>" class="input" style="width:100px">
		</td>
	</tr>
	<tr>
		<td class="left">������</td>
		<td colspan="3" class="right">
			�Զ����� (���� = ʵ�� - ���� - ���� - ��ͼ - �Һ���)
		</td>
	</tr>
</table>
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">
<input type="hidden" name="date" value="<?php echo date("Y-m-d", strtotime($date." 0:0:0")); ?>">
<input type="hidden" name="kefu" value="<?php echo $kefu; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>