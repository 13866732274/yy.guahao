<?php
// --------------------------------------------------------
// - ����˵�� : ת����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2015-6-4
// --------------------------------------------------------
require "lib/set_env.php";
require "lib/function.create_table.php";

if ($hid <= 0) {
	exit("û��ѡ�����...");
}
$table = "patient_".$hid;
$hline = $db->query("select * from hospital where id=$hid limit 1", 1);
$cur_hname = $hline["name"];


// ��һ�������������н���ֶ�
function _parse_fields($s) {
	$list = explode("\n", $s);
	$out = array();
	foreach ($list as $k) {
		$k = trim($k);
		if (substr($k, 0, 1) == "`") {
			$fname = ltrim($k, "`");
			list($sa, $sb) = explode(" ", $fname, 2);
			$sa = rtrim($sa, "`");
			$out[$sa] = rtrim(trim($k), ',');
		}
	}

	return $out;
}


// ��ǰҪת�Ļ�����Ϣ:
$patient_id = intval($_GET["patient_id"]);
if ($patient_id <= 0) {
	exit("��������");
}
$patient_info = $db->query("select * from $table where id=$patient_id limit 1", 1);


// ִ��ת���Ҳ���:
if ($_POST["op"] == "submit_move") {
	$zhuanru_hid = intval($_POST["zhuanru_hid"]);
	$disease_id = intval($_POST["disease_id"]);
	$depart_id = intval($_POST["depart_id"]);

	// ϵͳ�������е��ֶ�
	$_arr = _parse_fields($db_tables["patient"]);
	$sys_table_fields = array_keys($_arr);
	if (count($sys_table_fields) < 10 || !in_array("name", $sys_table_fields)) {
		exit("ϵͳ���ֶν������ƹ��ϣ�����ϵ������Ա�����");
	}


	if ($zhuanru_hid > 0) {
		$zhuanru_hname = $db->query("select * from hospital where id=$zhuanru_hid limit 1", 1, "name");
		$new_info = $patient_info;
		unset($new_info["id"]);
		unset($new_info["hospital_id"]);
		unset($new_info["zx_group"]);
		unset($new_info["sms_skip_send"]);
		unset($new_info["isshow"]);

		// ���Ǳ�ϵͳ���ֶΣ�һ��unset����Ϊ�����insertʧ�ܣ�
		foreach ($new_info as $k => $v) {
			if (!in_array($k, $sys_table_fields)) {
				unset($new_info[$k]);
			}
			if (is_string($new_info[$k])) {
				$new_info[$k] = str_replace("'", "~", $new_info[$k]);
				$new_info[$k] = str_replace('"', "~", $new_info[$k]);
			}
		}

		$new_info["memo"] = ltrim($new_info["memo"]."\r\n".date("Y-m-d H:i ").$realname." ��[".$cur_hname."]ת��");
		$new_info["disease_id"] = $disease_id;
		$new_info["depart"] = $depart_id;

		$ins_id = $db->insert("patient_".$zhuanru_hid, $new_info);
		if ($ins_id > 0) {
			$db->query("delete from $table where id=$patient_id limit 1");
			user_op_log("��[".$patient_info["name"]."]��[".$cur_hname."]ת���ҵ�[".$zhuanru_hname."]");
			echo "<script> parent.load_box(0); parent.update_content(); alert('��".$patient_info["name"]."��ת���ҳɹ�');</script>";
			exit;
		} else {
			echo "ת����ʧ�ܣ�����ϵ������Ա��鹦��";
			if ($debug_mode) {
				echo "<br>patient_".$zhuanru_hid."<br>";
				echo "<pre>";
				print_r($new_info);
				echo "</pre>";
			}
			exit;
		}
	} else {
		exit("�ύ��������");
	}
}



// ��ѯͬ����:
$hids = implode(",", $hospital_ids);
$sname = $hline["sname"];
$h_arr = $db->query("select * from hospital where ishide=0 and id in ($hids) and sname='$sname' and id!=$hid order by name asc", "id", "name");


$tohid = intval($_GET["zhuanru_hid"]);
if ($tohid > 0) {
	$disease_id_name = $db->query("select id,name from disease where hospital_id='$tohid' and isshow=1 order by sort desc,id asc", "id", "name");
	$depart_id_name = $db->query("select id,name from depart where hospital_id='$tohid'", "id", "name");
}



?>
<html>
<head>
<title>ת����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
* {font-family:"΢���ź�" !important; }
select {font-family:"����" !important; }
.l {text-align:right; border-bottom:0px solid #D8D8D8; padding:6px 20px 6px 0px; width:200px; }
.r {text-align:left; border-bottom:0px solid #D8D8D8; padding:6px 6px; }
</style>
<script language="javascript">
function reload_zhuanru_hid(o) {
	var patient_id = byid("patient_id").value;
	self.location = "patient_move_keshi.php?patient_id="+patient_id+"&zhuanru_hid="+o.value;
}
function check_data(f) {
	if (f.zhuanru_hid.value == '') {
		alert("��ѡ��Ҫת��Ŀ��ҡ�"); return false;
	}
	if (!confirm("ȷ��Ҫִ��ת���Ҳ�����")) {
		return false;
	}
	return true;
}
</script>
</head>

<body>

<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" style="margin-top:10px;">
	<tr>
		<td class="l">����������</td>
		<td class="r">
			<b style="color:red"><?php echo $patient_info["name"]; ?></b>
		</td>
	</tr>

	<tr>
		<td class="l">��ǰ���ң�</td>
		<td class="r">
			<b><?php echo $cur_hname; ?></b>
		</td>
	</tr>

	<tr>
		<td class="l">ת����ң�</td>
		<td class="r">
			<select name="zhuanru_hid" class="combo" onchange="reload_zhuanru_hid(this);">
				<option value="" style="color:gray">--��ѡ��--</option>
				<?php echo list_option($h_arr, '_key_', '_value_', $_GET["zhuanru_hid"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="l">ת�����-����ѡ��</td>
		<td class="r">
			<select name="disease_id" class="combo">
				<option value="" style="color:gray">--��ѡ��--</option>
				<?php echo list_option($disease_id_name, '_key_', '_value_'); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="l">ת�����-������ң�</td>
		<td class="r">
			<select name="depart_id" class="combo">
				<option value="" style="color:gray">--��ѡ��--</option>
				<?php echo list_option($depart_id_name, '_key_', '_value_'); ?>
			</select>
		</td>
	</tr>

</table>

<div class="button_line">
	<input type="submit" class="submit" value="�ύת��">
</div>

<input type="hidden" name="patient_id" id="patient_id" value="<?php echo $patient_id; ?>">
<input type="hidden" name="op" value="submit_move">
</form>

</body>
</html>