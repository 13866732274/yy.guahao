<?php
// --------------------------------------------------------
// - ����˵�� : �ط�����ͳ�� - ����ά��
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-12-02
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_huifang";
include "count_huifang.inc.php";

$hid = intval($_REQUEST["hid"]);
if ($hid <= 0 || !array_key_exists($hid, $yiyuan_arr)) {
	echo $hid;
	exit("ҽԺID��������");
}
$hid_set = $db->query("select * from count_huifang_set where hid=$hid limit 1", 1);
$kefu_names = explode(",", $hid_set["kefu_names"]);

if ($_GET["js_kefu"] != "") {
	$_GET["kefu"] = mb_convert_encoding($_GET["js_kefu"], "gbk", "UTF-8");
}
$kefu = $_GET["kefu"];

if ($_REQUEST["date"] == "") $_REQUEST["date"] = date("Y-m-d"); //����Ĭ��Ϊ����
$int_date = date("Ymd", strtotime($_REQUEST["date"]));

$line = $db->query("select * from $table where hid=$hid and date=$int_date and kefu='$kefu' limit 1", 1);
$id = intval($line["id"]);

if ($_POST) {
	$r = array();
	if (trim($_POST["kefu"]) == "") {
		exit("��ѡ��ͷ����������������û����Ҫ�����á�");
	}
	$r["hid"] = $hid;
	$r["hname"] = $yiyuan_arr[$hid];
	$r["kefu"] = trim($_POST["kefu"]);
	$r["date"] = $int_date;
	$r["x1"] = $_POST["x1"];
	$r["x2"] = $_POST["x2"];
	$r["x3"] = $_POST["x3"];
	$r["x4"] = $_POST["x4"];
	$r["x5"] = $_POST["x5"];
	$r["x6"] = $_POST["x6"];
	$r["x7"] = $_POST["x7"];
	$r["x8"] = $_POST["x8"];
	$r["x9"] = $_POST["x9"];

	if ($id == 0) {
		$r["addtime"] = time();
		$r["author"] = $username;
	}

	$sqldata = $db->sqljoin($r);
	if ($id == 0) {
		$db->query("insert into $table set $sqldata");
	} else {
		$db->query("update $table set $sqldata where id='$id' limit 1");
	}

	echo '<script> parent.update_content(); </script>';
	echo '<script> alert("���ݱ���ɹ���"); </script>';
	$to_url = "?hid=".$hid."&date=".$_POST["date"]."&kefu=".$_POST["kefu"];
	echo '<script> self.location = "'.$to_url.'"; </script>';
	exit;
}

?>
<html>
<head>
<title>����ά�� - <?php echo $yiyuan_arr[$hid]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style type="text/css">
.new_edit .left {width:50%; }
.new_edit .right {width:50%; }
</style>
<script language="javascript">
var sys_hid = "<?php echo $hid; ?>";
function reload_data() {
	var kefu = byid("kefu").value;
	var date = byid("date").value;
	self.location = "?hid="+sys_hid+"&date="+date+"&js_kefu="+encodeURIComponent(kefu);
}

function detect_multi_input(obj) {
	var v = obj.value;
	if (v.split("\t").length > 1) {
		var arr = v.split("\t");
		for (var i=0; i<arr.length; i++) {
			byid("x"+(i+1)).value = arr[i];
		}
	}
}
</script>
</head>

<body>
<form name="mainform" method="POST">
<div style="padding:20px; text-align:center;">
	<b>�ͷ���</b>��<select name="kefu" id="kefu" class="combo" onchange="reload_data()"><?php echo list_option($kefu_names, "_value_", "_value_", $_GET["kefu"]); ?></select>����<b>���ڣ�</b>��<input id="date" class="input" style="width:120px" value="<?php echo $_REQUEST["date"]; ?>" onchange="reload_data()" onclick="picker({el:'date',dateFmt:'yyyy-MM-dd'})">
</div>

<table width="100%" class="new_edit">
	<tr>
		<td class="left">�ط�������</td>
		<td class="right"><input name="x1" id="x1" value="<?php echo $line["x1"]; ?>" class="input" style="width:120px" onkeyup="detect_multi_input(this)"></td>
	</tr>
	<tr>
		<td class="left">��Լ������</td>
		<td class="right"><input name="x2" id="x2" value="<?php echo $line["x2"]; ?>" class="input" style="width:120px"></td>
	</tr>
	<tr>
		<td class="left">���˽�����</td>
		<td class="right"><input name="x3" id="x3" value="<?php echo $line["x3"]; ?>" class="input" style="width:120px"></td>
	</tr>
	<tr>
		<td class="left">��Լ���</td>
		<td class="right"><input name="x4" id="x4" value="<?php echo $line["x4"]; ?>" class="input" style="width:120px"></td>
	</tr>
	<tr>
		<td class="left">�绰���</td>
		<td class="right"><input name="x5" id="x5" value="<?php echo $line["x5"]; ?>" class="input" style="width:120px"></td>
	</tr>
	<tr>
		<td class="left">������</td>
		<td class="right"><input name="x6" id="x6" value="<?php echo $line["x6"]; ?>" class="input" style="width:120px"></td>
	</tr>
	<tr>
		<td class="left">�������</td>
		<td class="right"><input name="x7" id="x7" value="<?php echo $line["x7"]; ?>" class="input" style="width:120px"></td>
	</tr>
	<tr>
		<td class="left">΢�ž��</td>
		<td class="right"><input name="x8" id="x8" value="<?php echo $line["x8"]; ?>" class="input" style="width:120px"></td>
	</tr>
	<tr>
		<td class="left">����ϼƣ�</td>
		<td class="right"><input name="x9" id="x9" value="<?php echo $line["x9"]; ?>" class="input" style="width:120px"></td>
	</tr>
</table>
<input type="hidden" name="hid" value="<?php echo $hid; ?>">
<input type="hidden" name="date" value="<?php echo $_REQUEST["date"]; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>