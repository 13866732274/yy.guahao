<?php
// --------------------------------------------------------
// - ����˵�� : ���ñ�ע
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2014-5-9
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_week_memo";

$type_id = intval($_REQUEST["type_id"]);
$month = intval($_REQUEST["month"]);
$kefu = $_GET["kefu"] ? base64url_decode($_GET["kefu"]) : $_POST["kefu"];
$sub_id = intval($_REQUEST["sub_id"]);

$line = $db->query("select * from $table where type_id=$type_id and month=$month and sub_id=$sub_id and kefu='$kefu' order by id desc limit 1", 1);
$mode = count($line) > 0 ? "edit" : "add";

if ($_POST) {
	$r = array();
	$r["memo"] = $_POST["memo"];

	if ($mode == "add") {
		$r["type_id"] = $type_id;
		$r["month"] = $month;
		$r["sub_id"] = $sub_id;
		$r["kefu"] = $kefu;
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

?>
<html>
<head>
<title>������Ա��ע</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function check() {
	var oForm = document.mainform;
	return true;
}
</script>
</head>

<body>
<form name="mainform" action="" method="POST" onsubmit="return check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">Ϊ��<?php echo $kefu; ?>�����ñ�ע</td>
	</tr>
	<tr>
		<td class="left">��ע���ݣ�</td>
		<td class="right"><textarea name="memo"class="input"  style="width:400px; height:100px; overflow:visible; vertical-align:middle;"><?php echo $line["memo"]; ?></textarea> <font color="gray">��50����</font></td>
	</tr>
</table>

<input type="hidden" name="id" value="<?php echo $line["id"]; ?>">
<input type="hidden" name="type_id" value="<?php echo $type_id; ?>">
<input type="hidden" name="month" value="<?php echo $month; ?>">
<input type="hidden" name="sub_id" value="<?php echo $sub_id; ?>">
<input type="hidden" name="kefu" value="<?php echo $kefu; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>