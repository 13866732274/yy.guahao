<?php
// --------------------------------------------------------
// - ����˵�� : Ϊ��ѯԱ���ûط�����ʱ��
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2015-7-22
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$hid;

$hline = $db->query("select * from hospital where id=$hid limit 1", 1);
$cur_hname = $hline["name"];


if ($_GET["op"] == "delete_huifang") {
	$_id = intval($_GET["remind_id"]);
	$_crc = intval($_GET["crc"]);
	$db->query("delete from patient_remind where id='$_id' and addtime='$_crc' limit 1");
	echo "<script> alert('�ط������ѱ�ɾ����������������'); history.back(); </script>";
	exit;
}


// ��ǰ������Ϣ:
$patient_id = intval($_REQUEST["id"]);
if ($patient_id <= 0) {
	exit("��������");
}
$patient_info = $db->query("select * from $table where id=$patient_id limit 1", 1);


// ��ѯ��ǰ������Ȩ�޵Ļط���Ա(������ѡ��)
$huifang_user_arr = $db->query("select id, concat(if(part_id=2,'����',if(part_id=3,'�绰',if(part_id=12,'�ط�','��'))),': ',realname) as realname from sys_admin where part_id in (2,3,12,13) and isshow=1 and concat(',',hospitals,',') like '%,{$hid},%' and concat(',',guahao_config,',') like '%,huifang,%' order by part_id asc, realname asc", "id", "realname");


// ��ѯ��ǰ���ߵĻط����
$huifang_list = $db->query("select * from patient_remind where hid='$hid' and patient_id='$patient_id' and remind_date>0 order by u_name asc", "uid");

$huifang_user_option = array();
foreach ($huifang_user_arr as $_uid => $_uname) {
	$_dt = $huifang_list[$_uid]["remind_date"];
	if ($_dt > 0) {
		$huifang_user_option[$_uid] = $_uname." (".int_date_to_date($_dt).")";
	} else {
		$huifang_user_option[$_uid] = $_uname;
	}
}


$cur_huifang_date = $cur_huifang_time = $cur_huifang_memo = '';
if ($_GET["huifang_uid"] > 0) {
	$_dt = $huifang_list[$_GET["huifang_uid"]]["remind_date"];
	$cur_huifang_date = $_dt > 0 ? int_date_to_date($_dt) : "";
	$cur_huifang_time = $huifang_list[$_GET["huifang_uid"]]["remind_time"];
	$cur_huifang_memo = $huifang_list[$_GET["huifang_uid"]]["remind_memo"];
}


// ִ�в���:
if ($_POST["op"] == "submit") {

	$date = intval(str_replace("-", "", trim($_POST["huifang_date"])));
	$hm_time = trim($_POST["huifang_time"]);
	$memo = trim($_POST["huifang_memo"]);

	$to_uid = intval($_POST["huifang_uid"]);
	$to_uname = $db->query("select realname from sys_admin where id='$to_uid' limit 1", 1, "realname");

	// ��ӻ��Ǹ���
	$line = $db->query("select * from patient_remind where hid='$hid' and patient_id='$patient_id' and uid='$to_uid' limit 1", 1);
	$_old_id = $line["id"];
	if ($_old_id > 0) {
		$db->query("update patient_remind set remind_date='$date', remind_time='$hm_time', remind_memo='$memo', flag=9, add_uid='$uid', add_uname='$realname' where id=$_old_id limit 1");
	} else {
		$p_name = $patient_info["name"];
		$time = time();
		$db->query("insert into patient_remind set hid='$hid', patient_id='$patient_id', patient_name='$p_name', remind_date='$date', remind_time='$hm_time', remind_memo='$memo', uid='$to_uid', u_name='$to_uname', flag=9, addtime='$time', add_uid='$uid', add_uname='$realname'");
	}

	echo "<script> parent.load_box(0); parent.msg_box('����ɹ�'); </script>";
	exit;
}



$_huifang_uid = intval($_GET["huifang_uid"]);
if ($_huifang_uid > 0) {
	$line = $db->query("select * from patient_remind where hid='$hid' and patient_id='$patient_id' and uid='$_huifang_uid' limit 1", 1);
}


?>
<html>
<head>
<title>��ط�����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/wee_time.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style type="text/css">
* {font-family:"΢���ź�" !important; }
input, select {font-family:"Tahoma","����" !important; }
.l {text-align:right; border-bottom:0px solid #D8D8D8; padding:6px 20px 6px 0px; width:254px; }
.r {text-align:left; border-bottom:0px solid #D8D8D8; padding:6px 6px; }
.button_line {margin-top:30px; }
</style>
<script language="javascript">
function reload_form(o) {
	var patient_id = byid("patient_id").value;
	self.location = "patient_set_huifang_tixing.php?id="+patient_id+"&huifang_uid="+o.value;
}

function check_data(f) {
	if (f.huifang_uid.value == '') {
		alert("��ѡ��ط���ѯԱ����������������");
		return false;
	}
	if (f.huifang_date.value == '') {
		alert("��ָ���ط����ڡ�����������������");
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
		<td class="l">����������</td>
		<td class="r">
			<b style="color:#ff8000"><?php echo $patient_info["name"]; ?></b>
		</td>
	</tr>

	<tr>
		<td class="l">��ǰ�ط����ã�</td>
		<td class="r">
			<?php
			if (count($huifang_list) > 0) {
				foreach ($huifang_list as $v) {
					echo $v["u_name"]."��".int_date_to_date($v["remind_date"])." ".($v["remind_time"])." ".($v["remind_memo"])." (".($v["add_uname"] ? $v["add_uname"] : "�Լ�")."����)<br>";
				}
			} else {
				echo "(��������)";
			}
			?>
		</td>
	</tr>


	<tr>
		<td class="l">ָ���ط���Ա��</td>
		<td class="r">
			<select name="huifang_uid" class="combo" onchange="reload_form(this);">
				<option value="" style="color:gray">--��ѡ��--</option>
				<?php echo list_option($huifang_user_option, '_key_', '_value_', $_GET["huifang_uid"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="l">ָ���ط����ڣ�</td>
		<td class="r">
			<input name="huifang_date" id="huifang_date" readonly="readonly" title="������������(����ֱ������)" value="<?php echo $cur_huifang_date; ?>" class="input" style="width:100px;" onclick="picker({el:'huifang_date',dateFmt:'yyyy-MM-dd'})" onfocus="this.blur()">
			<input name="huifang_time" id="huifang_time" value="<?php echo $cur_huifang_time; ?>" class="input" style="width:80px; cursor:help;" onclick="wee_time_show_picker('huifang_time','right','bottom')" title="ָ������ʱ��">
			<?php if ($line["id"] > 0) { ?>
			&nbsp; <a href="?op=delete_huifang&remind_id=<?php echo $line["id"]; ?>&crc=<?php echo $line["addtime"]; ?>" onclick="return confirm('ɾ�����ָܻ����Ƿ�ȷ��Ҫɾ����');">ɾ���ûط�����</a>
			<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="l">�طñ�ע��</td>
		<td class="r">
			<input name="huifang_memo" style="width:185px;" value="<?php echo $cur_huifang_memo; ?>" class="input">
		</td>
	</tr>

</table>

<div class="button_line">
	<input type="submit" class="submit" value="�ύ">
</div>

<input type="hidden" name="id" id="patient_id" value="<?php echo $patient_id; ?>">
<input type="hidden" name="op" value="submit">
</form>



</body>
</html>