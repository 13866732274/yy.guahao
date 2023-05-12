<?php
// --------------------------------------------------------
// - ����˵�� : ��ӡ��޸Ĺ���Ա����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2008-05-15 01:03
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_admin";

$mode = $id > 0 ? "edit" : "add";

$super_edit = $is_edit_ukey = 0;
if ($debug_mode || in_array($username, explode(" ", $sys_super_admin))) {
	$super_edit = 1;
	$is_edit_ukey = 1;
}
if (in_array($realname, explode(" ", $sys_super_admin))) {
	$is_edit_ukey = 1;
}



// ��ȡ����
if ($id > 0) {
	$line = $db->query("select * from $table where id=$id limit 1", 1);
}

$title = $id > 0 ? ($line["realname"]." - �޸�����") : "�����Ա";

$part_id_name = $db->query("select * from sys_part order by sort desc, id asc", "id", "name");
$hospital_id_name = $db->query("select * from hospital where ishide=0 order by binary name asc", "id", "name");
$hospital_id_color = $db->query("select id,color from hospital where ishide=0 and color!=''", "id", "color");

// ����ѡ��������
$select_part_id_name = array();
// [0] => all [1] => web [2] => tel [3] => dy [4] => qh
if ($debug_mode || $username == "admin" || in_array("all", $user_data_power) ) {
	$select_part_id_name = $part_id_name;
} else {
	$allow_part_id = array();
	if (in_array("web", $user_data_power)) {
		$allow_part_id[] = 2;
	}
	if (in_array("tel", $user_data_power)) {
		$allow_part_id[] = 3;
		$allow_part_id[] = 12;
	}
	if (in_array("dy", $user_data_power)) {
		$allow_part_id[] = 4;
	}
	if (in_array("qh", $user_data_power)) {
		$allow_part_id[] = 13;
	}
	// �����Լ����ڵĲ���
	if (!in_array($uinfo["part_id"], $allow_part_id)) {
		array_unshift($allow_part_id, $uinfo["part_id"]); //���ڿ�ͷ�������һѡ��
	}
	// ����Ǳ༭ģʽ���������Ա�������ڵĲ���
	if ($line["part_id"] > 0 && !in_array($line["part_id"], $allow_part_id)) {
		$allow_part_id[] = $line["part_id"];
	}
	foreach ($allow_part_id as $v) {
		$select_part_id_name[$v] = $part_id_name[$v];
	}
}


if ($mode == "edit") {
	check_power("e", $pinfo, $pagepower) or exit("�Բ�����û���޸�Ȩ�ޡ�");
} else {
	check_power("i", $pinfo, $pagepower) or exit("�Բ�����û������Ȩ�ޡ�");
}


if ($_POST) {
	$r = array();

	if ($debug_mode) {
		//echo "<pre>";
		//print_r($_POST);
		//exit;
	}

	$name = trim($_POST["str_name"]);
	$pass = trim($_POST["str_pass"]);

	if ($mode == "add") {
		if (trim($name) == '') {
			exit("��¼������Ϊ�գ�����д��");
		}
		if ($db->query("select count(*) as count from $table where name='$name' or realname='$name' limit 1", 1, "count") > 0) {
			exit("���ʻ����ơ�$name���Ѿ�����ʹ�ã��볢���������ƣ�");
		}
		$r["name"] = $name;
		$r["realname"] = $name;
	}


	if ($pass) {
		$r["pass"] = gen_pass($pass);
	}

	$r["memo"] = trim($_POST["memo"]);

	// ҽԺ:
	if ($_POST["hospital_submit"] == "12458") {
		if ($super_edit) {
			@asort($_POST["hospital_ids"]); //����С���� 2011-05-26
			$r["hospitals"] = @implode(",", $_POST["hospital_ids"]);
		} else {
			// ֻ����ǰ���Թ�Ͻ�Ŀ���
			$ori_hids = array();
			if ($mode != "add") {
				$ori_hids = @explode(",", $line["hospitals"]);
				foreach ($hospital_ids as $v) {
					if (in_array($v, $ori_hids)) {
						$key = array_search($v, $ori_hids);
						unset($ori_hids[$key]);
					}
				}
			}
			// ��ѡ�е����ӽ�ȥ
			foreach ($_POST["hospital_ids"] as $v) {
				if (!in_array($v, $ori_hids)) { //��ֹ�ظ����
					$ori_hids[] = $v;
				}
			}
			@asort($ori_hids);
			$r["hospitals"] = trim(@implode(",", $ori_hids), ","); //��ֹ���߳��ֶ���
		}
	}

	if ($_POST["part_submit"] == "74982") {
		$r["part_id"] = $_POST["part_id"];
		if ($super_edit || $uinfo["part_admin"]) { //2014-7-22
			$r["part_admin"] = $_POST["part_admin"] ? 1 : 0;
		}
	}

	$r["data_power"] = @implode(",", $_POST["data_power"]);
	$r["guahao_config"] = @implode(",", $_POST["guahao_config"]);

	if ($_POST["character_submit"] == "35489") {
		$r["character_id"] = intval($_POST["character_id"]);
	}

	if ($super_edit) {
		$r["module_config"] = wee_array_to_string($_POST["special_config"]);
		$r["sql_add"] = $_POST["sql_add"];
	}

	if ($is_edit_ukey) {
		$r["use_ukey"] = $_POST["use_ukey"] ? "1" : "0";
		$r["ukey_sn"] = $_POST["ukey_sn"];
		$r["ukey_no"] = $_POST["ukey_no"];
	}

	// ���Ͽ����� @ 2015-12-12
	if ($super_edit || $uinfo["ku_allow_set"]) {
		$r["ku_config"] = @serialize($_POST["ku_config"]);
		$r["ku_allow_set"] = $_POST["ku_allow_set"] ? 1 : 0;
	}

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
		if ($mode == "edit" && $_POST["pass"] && !$debug_mode) {
			$li = $db->query("select * from $table where id=$id limit 1", 1);
			$save_pass = substr($_POST["pass"], 0, 1).str_repeat("*", strlen($_POST["pass"])-2).substr($_POST["pass"], -1, 1);
		}

		// �������ڵĴ���ʽ:
		if ($mode == "add") {
			echo '<script> parent.update_content(); </script>';
		}
		echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
		exit;
	} else {
		exit("�����ύʧ�ܣ�ϵͳ��æ�����Ժ����ԡ�");
	}
}

// �Ƿ�ɱ༭�Ŀ���:
$is_part_edit = 1;
$is_power_edit = 1;

if ($mode == "edit") {
	if ($line["power_mode"] == 1) {
		$line_power = $line["menu"];
	} else {
		$line_power = $db->query("select menu from sys_character where id=".$line["character_id"]." limit 1", 1, "menu");
	}
	if (!check_power_in($line_power, $usermenu)) {
		$is_power_edit = 0;
	}
}

?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
body {margin:10px; padding:0; }
.man_check {float:left; width:200px; height:22px; text-overflow:ellipsis; white-space:nowrap; overflow:hidden; }
.right * {vertical-align:middle; }
.check {margin:0px !important; padding:0px !important; }
label {margin-left:3px; margin-right:5px; }
.wee2 {width:55% !important; }
.mr_20 {margin-right:20px; }
#name_tips {color:#ff0080; font-weight:bold; }
</style>

<script language="javascript">
var mode = "<?php echo $mode; ?>";

function check_data() {
	oForm = document.mainform;
	if (oForm.str_name.value == "") {
		alert("�������¼��������ֱ��ʹ����ʵ������¼��");
		oForm.str_name.focus();
		return false;
	}
	if (mode == "add" && oForm.str_pass.value.length < 6) {
		alert("�����������룬�ҳ������6λ");
		oForm.str_pass.focus();
		return false;
	}
	if (oForm.part_id.value == "0") {
		alert("���������б���ѡ���������š�");
		oForm.part_id.focus();
		return false;
	}
	if (oForm.character_id.value == "0") {
		alert("���������б���ѡ���û�Ȩ�ޡ�");
		oForm.character_id.focus();
		return false;
	}
	if (!confirm("ÿһ�����˰ɣ��������Ŷ����������ٿ�һ�£�������ȡ����")) {
		return false;
	}
	return true;
}

function check_repeat(o) {
	if (mode == "add") {
		load_js("/v6/http/check_admin_repeat.php?js_name="+encodeURIComponent(o.value));
	}
}
</script>
</head>

<body>

<form name="mainform" method="POST" onsubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="3" class="head">�������� (��ɫ*��ǵ���Ŀ�ǳ���Ҫ�������ضԴ�)</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> �û�����</td>
		<td class="right wee2"><input name="str_name" class="input" value="<?php echo $line["name"]; ?>" size="20" style="width:150px" <?php if ($id > 0) echo "disabled"; ?> onchange="check_repeat(this)" onblur="check_repeat(this)"> <span class="intro"><span id="name_tips"></span>�������ܸ���</span></td>
		<td rowspan="2"><nobr>�˺ű�ע��<input name="memo" value="<?php echo $line["memo"]; ?>" class="input" style="width:70%"></nobr></td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> ���룺</td>
		<td class="right wee2"><input name="str_pass" value="" class="input" size="20" style="width:150px"> <?php echo $id ? "<span class='intro'>�����µ����뽫����ԭ���룬����6���ַ�</span>" : "<span class='intro'>��������6���ַ�</span>"; ?><!-- <?php echo $line["pass"]; ?> --></td>
	</tr>
</table>

<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">��Ȩ</td>
	</tr>

	<tr>
		<td class="left"><font color="red">*</font> ����Ȩ�ޣ�</td>
		<td class="right" valign="top">
			<?php echo show_check("guahao_config[]", $guahao_config_arr, "k", "v", explode(",", $line["guahao_config"]), " "); ?>
		</td>
	</tr>

	<tr>
		<td class="left"><font color="red">*</font> ���ݷ�Χ��</td>
		<td class="right" valign="top">
			<?php echo show_check("data_power[]", $data_power_arr, "k", "v", explode(",", $line["data_power"]), " "); ?>
		</td>
	</tr>

	<tr>
		<td class="left"><font color="red">*</font> �������ţ�</td>
		<td class="right" valign="top">
<?php if ($is_part_edit) { ?>
			<select name="part_id" class="combo" style="width:150px;">
				<option value="0" style="color:gray"></option>
				<?php echo list_option($select_part_id_name, "_key_", "_value_", $line["part_id"]); ?>
			</select>
<?php if ($super_edit || $uinfo["part_admin"]) { ?>
			��<input type="checkbox" class="check" name="part_admin" value="1" id="part_admin" <?php if ($line["part_admin"]) echo "checked"; ?>><label for="part_admin">�����鳤</label>
<?php } ?>
			<span class='intro'>���ű���ѡ�񡣲����鳤���б����Žϸ�Ȩ�ޣ��������ö��ˣ�</span>
			<input type="hidden" name="part_submit" value="74982">
<?php } else { ?>
			<?php echo $part_id_name[$line["part_id"]]; ?>
<?php } ?>
		</td>
	</tr>

	<!-- Ȩ�� -->
	<tr id="power_detail">
		<td class="left"><font color="red">*</font> Ȩ�����ã�</td>
		<td class="right">
<?php if ($is_power_edit) { ?>
			<select name="character_id" class="combo" style="width:150px;">
				<option value="0" style="color:gray"></option>
<?php
$ch_data = $db->query("select * from sys_character where isshow>0 order by sort desc, id asc");
foreach ($ch_data as $ch_line) {
	if (!check_power_in($ch_line["menu"], $usermenu)) {
		continue;
	}
	$opt_select = $ch_line["id"] == $line["character_id"] ? " selected" : "";
	$opt_title = $ch_line["name"].($opt_select ? " *" : "");
?>
			<option value="<?php echo $ch_line["id"]; ?>"<?php echo $opt_select; ?>><?php echo $opt_title; ?></option>
<?php
}
?>
			</select><span class="intro">����ָ��һ��Ȩ�ޣ�Ҫ�޸�Ȩ�����飬�뵽��Ȩ�޹���</span>
			<input type="hidden" name="character_submit" value="35489">
<?php } else { ?>
			(Ȩ�޲����������޸�)
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">
			<font color="red">*</font> ������Ȩ��<br>
			<a href="#" onclick="hid_check_all();return false;">ȫѡ</a>&nbsp;&nbsp;
		</td>
		<td class="right" id="hid_select_area">
		<style type="text/css">
		.hospital_select_table, .hospital_select_table td {border:1px solid #ffc7ac; }
		.hospital_select_table td {padding:4px; }
		.h_left {text-align:center; background-color:#fff3ee; }
		</style>
		<script type="text/javascript">
		var gid_select_status = [];
		function h_select_all(gid) {
			var check = (gid_select_status[gid] == true) ? false : true;
			gid_select_status[gid] = !gid_select_status[gid];
			var arr = byid("hgid_"+gid).getElementsByTagName("INPUT");
			for (var i=0; i<arr.length; i++) {
				arr[i].checked = check;
			}
		}
		</script>
<?php
$user_hospitals = explode(",", $line["hospitals"]);

	// �߼�������ǳ����޸�ģʽ����һ���г����п��ҹ��޸ģ�����֮�е�ǰ���е�Ȩ��
	if ($super_edit) {
		$hid_list = $db->query("select id from hospital where ishide=0", "", "id");
		$all_allow_hids = count($hid_list) ? implode(",", $hid_list) : "0";
	} else {
		$all_allow_hids = count($hospital_ids) ? implode(",", ($hospital_ids)) : "0";
	}
	// ����:
	$group_arr = $db->query("select sname, count(sname) as c from hospital where ishide=0 group by sname order by sname asc", "sname", "sname");
	echo '<table width="100%" class="hospital_select_table">';
	foreach ($group_arr as $_sname) {
		// ��ѯ�÷����µĿ���:
		$_hospital_id_name = $db->query("select id,name from hospital where ishide=0 and sname='$_sname' and id in ($all_allow_hids) order by name asc", "id", "name");
		if (count($_hospital_id_name) > 0) {
			echo '<tr><td class="h_left"><nobr>'.$_sname.'</nobr><br><a href="javascript:;" onclick="h_select_all(\''.$_sname.'\')">ȫѡ</a></td><td class="h_right" id="hgid_'.$_sname.'">';
			foreach ($_hospital_id_name as $id => $name) {
				$checked = in_array($id, $user_hospitals) ? "checked" : "";
?>
			<div class="man_check"><input type="checkbox" class="check" name="hospital_ids[]" value="<?php echo $id; ?>" id="hc_<?php echo $id; ?>" <?php echo $checked; ?>><label id="hn_<?php echo $id; ?>" for="hc_<?php echo $id; ?>" style="color:<?php echo $color; ?>"><?php echo $hospital_id_name[$id]; ?></label></div>
<?php
			}
			echo '</td></tr>';
		}
	}
	echo '</table>';
?>
			<input type="hidden" name="hospital_submit" value="12458">
		</td>
	</tr>

	<script type="text/javascript">
	var hid_check_all_status = false;
	function hid_check_all() {
		var check = hid_check_all_status == true ? false : true;
		hid_check_all_status = !hid_check_all_status;
		var objs = byid("hid_select_area").getElementsByTagName("INPUT");
		if (objs.length > 0) {
			for (var i=0; i<objs.length; i++) {
				objs[i].checked = check;
			}
		}
	}
	</script>


<?php if ($super_edit) { ?>
	<tr>
		<td class="left">����Ȩ�ޣ�</td>
		<td class="right">
<?php
	$cur_value = wee_string_to_array($line["module_config"]);
	foreach ($special_config as $k => $v) {
		$value = ($mode == "edit" ? $cur_value[$k] : $special_config_default_value[$k]);
?>
			<input type="checkbox" name="special_config[<?php echo $k; ?>]" value="1" id="sp_<?php echo $k; ?>" <?php echo $value ? "checked" : ""; ?>><label for="sp_<?php echo $k; ?>"><?php echo $v; ?></label>&nbsp;&nbsp;
<?php } ?>
		</td>
	</tr>
<?php } ?>


<?php if ($is_edit_ukey) { ?>
	<tr>
		<td class="left"><font color="red">*</font> uKey��¼��</td>
		<td class="right">
<?php if ($mode == "edit") { ?>
			<input type="checkbox" name="use_ukey" title="noclick" onclick="show_hide_ukey_box(this.checked)" <?php echo ($line["use_ukey"] == 1) ? "checked" : ""; ?> value="1" id="use_ukey_001"><label for="use_ukey_001">ʹ��uKey��¼</label>&nbsp; &nbsp;
			<span id="use_ukey_box" style="display:<?php echo ($line["use_ukey"] == 1) ? "" : "none"; ?>">
				Ӳ����ţ�<input name="ukey_sn" id="ukey_sn" value="<?php echo $line["ukey_sn"]; ?>" class="input" style="width:180px"> <a href="javascript:write_cur_ukey_sn()">��д��ǰ�����uKey���к�</a>&nbsp;&nbsp;
				��ע��<input name="ukey_no" value="<?php echo $line["ukey_no"]; ?>" class="input" style="width:120px">
			</span>
<?php } else { ?>
			<input type="checkbox" name="use_ukey" checked value="1" id="use_ukey_001"><label for="use_ukey_001">ʹ��uKey��¼(����Ĭ��Ϊѡ��)</label>&nbsp; &nbsp;
<?php } ?>

		</td>
	</tr>

	<script type="text/javascript">
	function show_hide_ukey_box(value) {
		byid("use_ukey_box").style.display = (value) ? "" : "none";
	}
	</script>

	<object classid="clsid:e6bd6993-164f-4277-ae97-5eb4bab56443" id="ET99" name="ET99" style="left:0px; top:0px" width="0" height="0"></object>
	<script type="text/javascript">
	function write_cur_ukey_sn() {
		et99 = byid("ET99");
		if (et99) {
			window.onerror = function() {
				alert("��ȡET99�豸���ִ���");
				return true;
			}
			var count = et99.FindToken("FFFFFFFF");
			if (count > 0) {
				et99.OpenToken("FFFFFFFF", 1)
				sn = et99.GetSN();
				if (sn != '') {
					byid("ukey_sn").value = sn;
					return;
				}
			}
		}
	}
	</script>

<?php } ?>


<?php if ($super_edit || $uinfo["ku_allow_set"]) { ?>
	<!-- ���Ͽ� @ 2015-12-12 -->
	<?php $ku_config = @unserialize($line["ku_config"]); ?>
	<tr>
		<td class="left">���Ͽ⣺</td>
		<td class="right">
			<select name="ku_config[data_limit]" class="combo">
<?php
$_arr = array(
	"0" => "ֻ���Լ�������",
	"2" => "�ɿ�������",
	"3" => "�ɿ��绰��",
	"-1" => "�ɿ�������+�绰��",
	"-2" => "�ɿ������ڲ�������",
	"-9" => "������������",
);
echo list_option($_arr, "_key_", "_value_", ($mode == "edit" ? $ku_config["data_limit"] : "-9"));
?>
			</select>&nbsp;&nbsp;
			<input type="checkbox" name="ku_config[show_ku_tel]" value="1" id="show_ku_tel" <?php echo ($ku_config["show_ku_tel"]) ? "checked" : ""; ?>><label for="show_ku_tel">����鿴���˻����ֻ���(�Լ��Ļ���һֱ�ɼ�)</label>&nbsp;&nbsp;
			<input type="checkbox" name="ku_config[show_ku_report]" value="1" id="show_ku_report" <?php echo ($ku_config["show_ku_report"]) ? "checked" : ""; ?>><label for="show_ku_report">��ʾ���Ͽⱨ��</label>&nbsp;&nbsp;
			<input type="checkbox" name="ku_allow_set" value="1" id="ku_allow_set" <?php echo $line["ku_allow_set"] ? "checked" : ""; ?>><label for="ku_allow_set">������������˺ŵ����Ͽ�����</label>&nbsp;&nbsp;
		</td>
	</tr>
<?php } ?>


<?php if ($mode == "edit") { ?>
	<tr>
		<td class="left">�˻����</td>
		<td class="right">
			��¼�ܴ�����<?php echo intval($line["logintimes"]); ?> &nbsp; �����¼��<?php echo $line["thislogin"] ? date("Y-m-d H:i", $line["thislogin"]) : "��"; ?> &nbsp; ��ǰ�Ƿ����ߣ�<?php echo $line["online"] ? "��" : "��"; ?> &nbsp; IE:<?php echo $line["ie_ver"]; ?> &nbsp; ��Ļ�ֱ��ʣ�<?php echo $line["window_size"]; ?> &nbsp; ���˺�ʱ�䣺<?php echo date("Y-m-d H:i", $line["addtime"]); ?>
			<?php if ($line["author"] != '') { ?> &nbsp; ���˺��ˣ�<?php echo $line["author"]; ?><?php } ?>
		</td>
	</tr>
<?php } ?>

</table>

<?php if ($super_edit) { ?>
<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">��������(���������)</td>
	</tr>
	<tr>
		<td class="left">����SQL��ѯ��</td>
		<td class="right"><input name="sql_add" class="input" value="<?php echo $line["sql_add"]; ?>" style="width:600px"> <span class="intro">SQL���where���֣������ and ����</span></td>
	</tr>
</table>

<?php } ?>


<div class="space"></div>

<input type="hidden" name="id" value="<?php echo $line["id"]; ?>">
<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>


</form>


<!-- ��ҳ��������ѡ�е�ѡ��Ӻ�ɫ -->
<script type="text/javascript">
var os = document.getElementsByTagName("INPUT");
for (var i = 0; i < os.length; i++) {
	var o = os[i];
	if (o.type == "checkbox") {
		o.className = "check";
		if (o.checked) {
			o.nextSibling.style.color = "red";
		}
		// ע�⣬����Ĳ������ܻḲ��Ĭ�ϵ��¼�����������Ҫ�Ļ���ע�͵�
		if (o.title != 'noclick') {
			o.onclick = (function () {
				this.nextSibling.style.color = this.checked ? "blue" : "";
			});
		}
	}
}
</script>

</body>
</html>