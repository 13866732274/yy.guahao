<?php
// --------------------------------------------------------
// - ����˵�� : ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-12-13
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_type";

$mode = "add";

$hids = count($hospital_ids) ? implode(",", $hospital_ids) : "0";
$hospital_arr = $db->query("select id, name, sname from hospital where ishide=0 and id in ($hids) order by sname asc, sort desc, name asc", "id");
$hos_options = $snames = array();
foreach ($hospital_arr as $_hid => $_hli) {
	if (!in_array($_hli["sname"], $snames)) {
		$snames[] = $_hli["sname"];
		$hos_options[] = array("k" => "-1", "v" => $_hli["sname"]);
	}
	$hos_options[] = array("k" => $_hid, "v" => "����".$_hli["name"]);
}


if ($_POST) {
	$type_id = $id;

	$hid = intval($_POST["hid"]);
	$hname = $db->query("select name from hospital where id=$hid limit 1", 1, "name");
	$name = trim($_POST["name"]);
	$kefu_list = implode(",", $_POST["kefu"]);
	$db->query("insert into $table set type='tel', hid=$hid, h_name='$hname', name='$name', kefu='$kefu_list', addtime='$time', uid=$uid, u_realname='$realname'");

	echo '<script> parent.msg_box("��ӳɹ�", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;
}

?>
<html>
<head>
<title>���ͳ������</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>

<style>
.new_kefu nobr {padding:2px; width:100px; float:left; display:block; overflow:hidden; }
.new_edit .left {width:15% !important; }
.new_edit .right {width:85% !important; }
</style>

<script type="text/javascript">
function reload_hid(obj) {
	if (obj.value > 0) {
		self.location = "?hid="+obj.value;
	} else {
		if (obj.value < 0) {
			alert("ѡ��ҽԺ��Ч����ѡ�������ҡ�");
		}
	}
}
function check_data(o) {
	if (o.hid.value <= 0) {
		alert("����ҽԺ ѡ����Ч����ѡ�������ҡ�");
		return false;
	}
	if (o.name.value == '') {
		alert("�������ƣ�Ϊ������д����Ŀ��");
		return false;
	}
	return true;
}
</script>

</head>

<body>

<div class="space"></div>
<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" class="new_edit">
	<tr>
		<td class="left"><font color="red">*</font> ����ҽԺ��</td>
		<td class="right">
			<select name="hid" class="combo" onchange="reload_hid(this)" style="width:250px">
				<option value="">��������</option>
				<?php echo list_option($hos_options, "k", "v", $_GET["hid"]); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> �������ƣ�</td>
		<td class="right"><input name="name" value="" class="input" style="width:250px"></td>
	</tr>

	<tr>
		<td class="left" valign="top" style="padding-top:6px;"><font color="red">*</font> �ͷ�������</td>
		<td class="right new_kefu">
<?php
$cur_hid = $_GET["hid"];
if ($cur_hid > 0) {
	$kefu_names = explode(",", trim($line["kefu"]));

	// ������ҽԺ��Χ�ڲ�ѯ��Ա (��ֹ��©)
	$sname = $db->query("select sname from hospital where id=$cur_hid", 1, "sname");
	$sid_arr = $db->query("select id from hospital where ishide=0 and sname='$sname'", "", "id");
	foreach ($sid_arr as $_hid) {
		$my_where[] = "concat(',',hospitals,',') like '%,{$_hid},%'";
	}
	$sql_where = "(".implode(" or ", $my_where).")";
	$cur_hospital_kefu_arr = $db->query("select realname from sys_admin where part_id in (3,12) and character_id not in (4,5,19,20,32,15,16,17) and isshow=1 and $sql_where order by part_id asc, realname asc", "", "realname");
	foreach ($cur_hospital_kefu_arr as $kf) {
		echo '<nobr><input type="checkbox" name="kefu[]" value="'.$kf.'"'.'>'.$kf.'</nobr> ';
	}
} else {
	echo '��ѡ�����';
}
?>

		</td>
	</tr>

</table>

<div class="button_line">
	<input type="submit" class="submit" value="�ύ����">
</div>
</form>

</body>
</html>