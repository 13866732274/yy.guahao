<?php
// --------------------------------------------------------
// - ����˵�� : ҽԺ�������޸�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-01 00:40
// --------------------------------------------------------
$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";
require "lib/set_env.php";
$table = "hospital";

if ($mode == "edit") {
	check_power("e", $pinfo, $pagepower) or exit("�Բ�����û���޸�Ȩ��...");
} else {
	check_power("i", $pinfo, $pagepower) or exit("�Բ�����û������Ȩ��...");
}

if ($_POST) {
	$r = array();
	$r["name"] = $_POST["name"];
	$r["intro"] = $_POST["intro"];
	$r["sort"] = $_POST["sort"];

	$r["area"] = $_POST["area"];
	$r["sname"] = trim($_POST["sname_add"]) ? trim($_POST["sname_add"]) : $_POST["sname"];
	$r["depart"] = $_POST["depart"];
	$r["group_id"] = $_POST["group_id"];
	$r["group_name"] = $db->query("select name from hospital_group where id=".$_POST["group_id"]." limit 1", 1, "name");
	$r["color"] = $_POST["color"];

	// ��������
	$r["short_name"] = $_POST["short_name"];

	$r["repeat_open"] = intval($_POST["repeat_open"]) ? 1 : 0;
	$r["repeat_tip_time"] = intval($_POST["repeat_tip_time"]);
	$r["repeat_deny_time"] = intval($_POST["repeat_deny_time"]);
	$r["ishide"] = intval($_POST["ishide"]);

	// ��ѯ����ģ�� @ 2014-6-20
	$r["template"] = $_POST["template"];


	if ($mode == "add") {
		$r["addtime"] = time();
		$r["author"] = $username;
	}

	$sqldata = $db->sqljoin($r);
	if ($mode == "add") {
		$sql = "insert into $table set $sqldata";
	} else {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	}

	if ($hid = $db->query($sql)) {
		// ����ҽԺ��Ӧ�����ݱ�
		$hid = ($mode == "edit") ? $id : $hid;
		create_patient_table($hid);

		if ($mode == "add") {
			// ������ҽԺ�Զ����ӵ��ҵ��˺���
			if ($uid > 0 && $hid > 0 && !in_array($hid, $hospital_ids)) {
				$hospital_ids[] = $hid;
				@asort($hospital_ids);
				$hospital_str = @implode(",", $hospital_ids);
				$db->query("update sys_admin set hospitals='$hospital_str' where id=$uid limit 1");
			}
		}

		// �������ڵĴ���ʽ:
		if ($mode == "add") {
			echo '<script> parent.update_content(); </script>';
		}
		echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "�ύʧ�ܣ����Ժ����ԣ�";
	}
	exit;
}

if ($mode == "edit") {
	$line = $db->query("select * from $table where id='$id' limit 1", 1);
}
$title = $mode == "edit" ? "�޸�ҽԺ����" : "����µ�ҽԺ";

$h_group_id_name = $db->query("select * from hospital_group order by sort desc, name asc", "id", "name");

$hospital_arr = $db->query("select sname, count(sname) as c from hospital where ishide=0 group by sname order by sname asc", "sname", "sname");

// ����������ص�ҽԺ
$hospital_arr["----"] = "---------------------";
$tmp_2 = $db->query("select sname, count(sname) as c from hospital where ishide=1 group by sname order by sname asc", "sname", "sname");
foreach ($tmp_2 as $sname) {
	if (!in_array($sname, $hospital_arr)) {
		$hospital_arr[$sname] = $sname;
	}
}


?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.edit, .edit td {border:0px solid #c0c0c0 !important; background:white !important; }
</style>
<script language="javascript">
function Check() {
	var oForm = document.mainform;
	if (oForm.name.value == "") {
		alert("�����롰ҽԺ���ơ���");
		oForm.name.focus();
		return false;
	}
	return true;
}
</script>
</head>

<body>
<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td class="left" style="width:20%;"><font color="red">*</font> ����ȫ�ƣ�</td>
		<td class="right" style="width:80%;"><input name="name" value="<?php echo $line["name"]; ?>" class="input" style="width:300px"> <span class="intro">(���Ʊ�����д)</span></td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> ҽԺ���ڵ�����</td>
		<td class="right">
			<input name="area" value="<?php echo $line["area"]; ?>" class="input" style="width:150px; margin-left:30px"> <span class="intro">(���ڰ��������࣬�硰�Ϻ���)</span>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> ҽԺ���ƣ�</td>
		<td class="right">
			<select name="sname" class="combo" style="width:150px; margin-left:30px">
				<option value=""></option>
				<?php echo list_option($hospital_arr, "_key_", "_value_", $line["sname"]); ?>
			</select>���������û�У����ֹ���д��<input name="sname_add" value="" class="input" style="width:100px"> <span class="intro">(�硰�Ϻ�����ҽԺ��)</span>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> ��������</td>
		<td class="right">
			<input name="depart" value="<?php echo $line["depart"]; ?>" class="input" style="width:150px; margin-left:30px"> <span class="intro">(���ڰ����ҹ��࣬�硰�пơ�)</span>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> �������ƣ�</td>
		<td class="right">
			<input name="short_name" value="<?php echo $line["short_name"]; ?>" class="input" size="30" style="width:150px; margin-left:30px"> <span class="intro">(�������б�����ʾ���硰�����пơ�)</span>
		</td>
	</tr>

	<tr>
		<td class="left"><font color="red">*</font> ����ѡ��</td>
		<td class="right">
			<select name="group_id" class="combo">
				<option value="" style="color:gray;">-��ѡ��-</option>
				<?php echo list_option($h_group_id_name, "_key_", "_value_", $line["group_id"]); ?>
			</select>&nbsp;<span class="intro">(��������ѡ���жԿ��ҽ��з���)</span>
		</td>
	</tr>

	<tr>
		<td class="left">��ʾ��ɫ��</td>
		<td class="right">
			<select name="color" class="combo">
				<option value="" style="color:gray">-Ĭ��-</option>
<?php
$colors = array(
	"red" => "��ɫ",
	"green" => "��ɫ",
	"blue" => "��ɫ",
	"black" => "��ɫ",
	"gray" => "��ɫ",
	"silver" => "��ɫ",
	"#0080ff" => "����",
	"#408080" => "��ɫ",
	"#ff00ff" => "Ʒ��",
	"#8080ff" => "#8080ff",
	"#cc66ff" => "#cc66ff",
	"#ff66cc" => "#ff66cc",
);
foreach ($colors as $k => $v) {
	echo '<option value="'.$k.'" style="color:'.$k.'"'.($k == $line["color"] ? 'selected' : '').'>'.$v.' '.($k == $line["color"] ? ' *' : '').'</option>';
}
?>
			</select>&nbsp;<span class="intro">(������ҳ�������б����ʾ)</span>
		</td>
	</tr>

	<tr>
		<td class="left">�����ظ����ã�</td>
		<td class="right">
			<input type="checkbox" name="repeat_open" value="1" <?php echo ($mode == "add" ? "checked" : ($line["repeat_open"] > 0 ? "checked" : "")); ?> id="repeat_open" onclick='byid("repeat_days_set").style.display = byid("repeat_open").checked ? "" : "none";'><label for="repeat_open">��ֹ�����ظ�</label>&nbsp;
			<span id="repeat_days_set" style="display:none;">
				<b>����������</b><input name="repeat_tip_time" value="<?php echo $line["repeat_tip_time"]; ?>" size="4" class="input">&nbsp;&nbsp;
				<b>��ֹ�ύ������</b><input name="repeat_deny_time" value="<?php echo $line["repeat_deny_time"]; ?>" size="4" class="input">&nbsp;&nbsp;(����д����������������60��)
			</span>
			<br>
			<span style="color:gray;"><b>ע�⣺</b>������˽�ֹ�����ظ����ܣ���δ���������ģ�����ϵͳĬ��ֵ���д���Ĭ����������<?php echo $cfgRepeatTipDays; ?>�죬��ֹ�ύ������Ϊ<?php echo $cfgRepeatDenyDays; ?>��</span>
		</td>
	</tr>

	<script type="text/javascript">
		byid("repeat_days_set").style.display = byid("repeat_open").checked ? "" : "none";
	</script>

	<tr>
		<td class="left">�Ƿ����أ�</td>
		<td class="right">
			<input type="checkbox" name="ishide" value="1" <?php echo ($mode == "add" ? "" : ($line["ishide"] != 0 ? "checked" : "")); ?> id="ishide"><label for="ishide">��ѡ������</label>
		</td>
	</tr>

	<tr>
		<td class="left">��ѯ����ģ�壺</td>
		<td class="right"><textarea name="template" class="input" style="width:600px; height:80px;"><?php echo $line["template"]; ?></textarea></td>
	</tr>

	<tr>
		<td class="left">���ȶȣ�</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" style="width:80px"> <span class="intro">���ȶ�Խ��,����Խ��ǰ</span></td>
	</tr>


</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>