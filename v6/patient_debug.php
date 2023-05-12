<?php
/*
// - ���� : ����Ա�޸�����
// - ���� : ���� QQ 934834734
// - ʱ�� : 2011-09-19
*/
require "lib/set_env.php";
$table = "patient_".$hid;

if ($hid == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

$is_super_edit = in_array($realname, explode(" ", $sys_super_admin));
if (!$is_super_edit) {
	exit_html("�Բ�����û�в���Ȩ�ޡ�");
}

$id = intval($_REQUEST["id"]);
if (empty($id)) {
	exit_html("�Բ���ֻ�ܱ༭���ϣ�����������");
}

$line = $db->query("select * from $table where id=$id limit 1", 1);
if (!is_array($line) || $line["id"] != $id) {
	exit_html("�Բ����޴�ID: ".$id);
}

if ($_POST) {
	$r = array();

	$r["name"] = trim($_POST["name"]);
	$r["sex"] = $_POST["sex"];
	$r["age"] = $_POST["age"];
	$r["tel"] = trim($_POST["tel"]);
	if (strlen($r["tel"]) == 11 && $line["tel_location"] == '') {
		$r["tel_location"] = @get_mobile_location($r["tel"]);
	}
	$r["content"] = $_POST["content"];

	$r["disease_id"] = $_POST["disease_id"];
	if ($_POST["disease_2_submit"]) {
		$r["disease_2"] = @implode(",", $_POST["disease_2"]);
	}
	$r["depart"] = $_POST["depart"];
	$r["media_from"] = $_POST["media_from"];

	// QQ��Դ�͵绰��Դ�����⴦�� @ 2012-12-12
	$r["qq_from"] = ($_POST["media_from"] == "QQ") ? $_POST["qq_from"] : "";
	$r["tel_from"] = ($_POST["media_from"] == "�绰") ? $_POST["tel_from"] : "";

	$r["engine"] = $_POST["engine"];
	$r["from_site"] = $_POST["from_site"];
	$r["key_word"] = $_POST["key_word"];
	$r["account"] = $_POST["account"];
	$r["zhuanjia_num"] = $_POST["zhuanjia_num"];
	$r["tuiguangren"] = trim($_POST["tuiguangren"]);
	$r["order_date"] = strtotime($_POST["order_date"]);
	$r["doctor"] = $_POST["doctor"];
	$r["xianchang_doctor"] = $_POST["xianchang_doctor"];
	$r["status"] = $_POST["status"];

	if (!$debug_mode) { //����ģʽ����¼��־
		// �ֶ��޸ļ�¼:
		$logs = patient_modify_log($r, $line);
		if ($logs) {
			$r["edit_log"] = $logs;
		}
	}

	$r["memo"] = $_POST["memo"];

	$sqldata = $db->sqljoin($r);
	$sql = "update $table set $sqldata where id='$id' limit 1";

	ob_start();
	$return = $db->query($sql);
	$error = ob_get_clean();

	user_op_log("����Ա�޸Ĳ���[".$line["name"]."]");

	if ($return && empty($error)) {
		echo '<script type="text/javascript">'."\r\n";
		echo 'parent.load_box(0);'."\r\n";
		echo 'parent.msg_box("�����޸ĳɹ�");'."\r\n";
		echo '</script>'."\r\n";
		exit;
	} else {
		echo "�����ύ��������ϵ������Ա���: <br><br>";
		echo $db->sql."<br><br>";
		echo $error."<br><br>";
		exit;
	}
}


$disease_id_name = $db->query("select id,name from disease where hospital_id='$hid' and isshow=1 order by sort desc,id asc", "id", "name");
$disease_2_name = $db->query("select id,disease_2 from disease where hospital_id='$hid' and isshow=1", "id", "disease_2");
$doctor_list = $db->query("select id,name from doctor where hospital_id='$hid'");
$part_id_name = $db->query("select id,name from sys_part", "id", "name");
$depart_list = $db->query("select id,name from depart where hospital_id='$hid'");
$engine_list = $db->query("select id,name from engine", "id", "name");
$xianchang_doctor = $db->query("select id,realname from sys_admin where part_id=14 and concat(',',hospitals,',') like '%,{$hid},%'", "id", "realname");
$qq_from_arr = $db->query("select id,name from qq_from order by sort desc,id asc", "id", "name");
$tel_from_arr = $db->query("select id,name from tel_from order by sort desc,id asc", "id", "name");


$status_array = array(
	array("id"=>0, "name"=>'�ȴ�'),
	array("id"=>1, "name"=>'�ѵ�'),
	array("id"=>2, "name"=>'δ��'),
);

$media_from_array = explode(" ", "���� �绰");
$media_2 = $db->query("select name from media where (hospital_id=0 or hospital_id=$hid) order by sort desc,addtime asc", "", "name");
$media_from_array = array_merge($media_from_array, $media_2);
$engine_array = $db->query("select name from engine order by id asc", "", "name");

?>
<html>
<head>
<title>�޸Ĳ�������(����Աģʽ)</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<script language="javascript">
function check_data(oForm) {
	if (!confirm("��ȷ��Ҫ������д���󣬽����ύ��")) {
		return false;
	}
	return true;
}

function input(id, value) {
	if (byid(id).disabled != true) {
		byid(id).value = value;
	}
}

function input_date(id, value) {
	var cv = byid(id).value;
	var time = cv.split(" ")[1];

	if (byid(id).disabled != true) {
		byid(id).value = value+" "+(time ? time : '');
	}
}

function input_time(id, time) {
	var s = byid(id).value;
	if (s == '') {
		alert("������д���ڣ�����дʱ�䣡");
		return;
	}
	var date = s.split(" ")[0];
	var datetime = date+" "+time;

	if (byid(id).disabled != true) {
		byid(id).value = datetime;
	}
}

Array.prototype.in_array = function(e) {
	for(i=0; i<this.length; i++) {
		if(this[i] == e)
		return true;
	}
	return false;
}

function show_disease_2(disease_id) {
	var s = '';
	var default_disease_id = byid("default_disease_id").value;
	var cur_2 = byid("disease_2_old").value.split(",");
	var o = byid("disease_2_"+disease_id);
	if (o && o.value != '' && o.title != '') {
		var d1 = o.title;
		var d2s = o.value.split(" ");

		// �����Ĭ��id�����δ֪�Ķ�������Ҳ�ŵ�ѡ���У����ѡ���µļ������ɵ�δ֪���������Ͷ���
		if (disease_id == default_disease_id) {
			for (var i=0; i<cur_2.length; i++) {
				var cur_2_name = cur_2[i];
				if (d2s.in_array(cur_2_name) == false) {
					d2s[d2s.length] = cur_2_name;
				}
			}
		}

		// ����ѡ��
		for (var i=0; i<d2s.length; i++) {
			if (d2s[i] != '') {
				var dis_2_name = d2s[i];
				var sel = cur_2.in_array(dis_2_name);
				s += '<input type="checkbox" name="disease_2[]" value="'+dis_2_name+'"'+(sel ? ' checked' : "")+' id="d2_'+dis_2_name+'"><label for="d2_'+dis_2_name+'">'+(sel ? ('<font color=red>'+dis_2_name+'</font>') : dis_2_name)+'</label>&nbsp;';
			}
		}
	}

	byid("disease_2_box").innerHTML = s;
	byid("disease_tips").style.display = (s == '' ? "inline" : "none"); //�ж��������Ͳ���ʾ�������ʾ��
}

function get_location(obj) {
	var tel = byid("tel").value;
	if (tel.length = 11) {
		var xm = new ajax();
		xm.connect("http/get_mobile_location.php?m="+tel+"&r="+Math.random(), "GET", "", get_location_do);
	}
}

function get_location_do(o) {
	var out = ajax_out(o);
	byid("tel_location_show").innerHTML = '';
	if (out["status"] == "ok") {
		byid("tel_location_show").innerHTML = out["location"];
	}
}

</script>
</head>

<body>
<table width="100%" class="description description_light">
	<tr>
		<td>&nbsp;<b>��ʾ��</b>�����ܽ�������Աʹ�ã�Ȩ��δ���κ����ƣ��޸������ء����κ�����������ѯ������Ա���ٲ�����</td>
	</tr>
</table>

<div class="space"></div>
<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">���˻�������</td>
	</tr>
	<tr>
		<td class="left">������</td>
		<td class="right"><input name="name" id="name" value="<?php echo $line["name"]; ?>" class="input" style="width:200px"> <span class="intro">* ���Ʊ�����д</span></td>
	</tr>
	<tr>
		<td class="left">�Ա�</td>
		<td class="right"><input name="sex" id="sex" value="<?php echo $line["sex"]; ?>" class="input" style="width:80px"> <a href="javascript:input('sex', '��')">[��]</a> <a href="javascript:input('sex', 'Ů')">[Ů]</a> <span class="intro">��д�����Ա�</span></td>
	</tr>
	<tr>
		<td class="left">���䣺</td>
		<td class="right"><input name="age" id="age" value="<?php echo $line["age"]; ?>" class="input" style="width:80px"> <span class="intro">��д����</span></td>
	</tr>

	<tr>
		<td class="left">�绰��</td>
		<td class="right"><input name="tel" id="tel" value="<?php echo $line["tel"]; ?>" class="input" style="width:200px" <?php echo $ce["tel"]; ?> onchange="get_location(this);"> <span id="tel_location_show"><?php echo $line["tel_location"]; ?></span> <span class="intro">�绰������ֻ�(�ɲ���)</span></td>
	</tr>
	<input type="hidden" name="tel_location" id="tel_location" value="<?php echo $line["tel_location"]; ?>">

	<tr>
		<td class="left" valign="top">��ѯ���ݣ�</td>
		<td class="right"><textarea name="content" style="width:60%; height:60px;" class="input"><?php echo $line["content"]; ?></textarea> <span class="intro">��ѯ�����ܽ�</span></td>
	</tr>
</table>

<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">��������</td>
	</tr>
	<tr>
		<td class="left">�������ͣ�</td>
		<td class="right">
			<!-- �༭�������� begin -->
			<select name="disease_id" onchange="show_disease_2(this.value)" class="combo">
				<option value="0" style="color:gray">--��ѡ��--</option>
				<?php echo list_option($disease_id_name, '_key_', '_value_', $line["disease_id"]); ?>
			</select>&nbsp;
			<span id="disease_2_box"></span> &nbsp; <font color="green" id="disease_tips">�������ͺͶ���������������ϵ����Ա��������ѡ��</font>

			<span style="display:none">
<?php foreach ($disease_2_name as $k => $v) { ?>
				<input type="hidden" id="disease_2_<?php echo $k; ?>" title="<?php echo $disease_id_name[$k]; ?>" value="<?php echo $v; ?>">
<?php } ?>
				<input type="hidden" id="disease_2_old" value="<?php echo $line["disease_2"]; ?>">
				<input type="hidden" id="default_disease_id" value="<?php echo $line["disease_id"]; ?>">
				<input type="hidden" name="disease_2_submit" value="1">
			</span>
			</span>
		</td>
	</tr>
<?php if ($line["disease_id"] > 0) { ?>
	<script type="text/javascript">
	show_disease_2(<?php echo $line["disease_id"]; ?>);
	</script>
<?php } ?>

	<tr>
		<td class="left">������ң�</td>
		<td class="right">
			<select name="depart" class="combo" <?php echo $ce["depart"]; ?>>
				<option value="0" style="color:gray">--��ѡ��--</option>
				<?php echo list_option($depart_list, 'id', 'name', $line["depart"]); ?>
			</select>
			<span class="intro">��ѡ��������</span>
		</td>
	</tr>

	<tr>
		<td class="left">ý����Դ��</td>
		<td class="right">
			<select name="media_from" class="combo" <?php echo $ce["media_from"]; ?>  onchange="on_media_from_change(this)">
				<option value="" style="color:gray">--��ѡ��--</option>
				<?php echo list_option($media_from_array, '_value_', '_value_', $line["media_from"]); ?>
			</select>
			<select name="qq_from" class="combo" id="qq_from" style="display:none; margin-right:5px;">
				<option value="" style="color:gray">--QQ��Դ--</option>
				<?php echo list_option($qq_from_arr, '_value_', '_value_', $line["qq_from"]); ?>
			</select>
			<select name="tel_from" class="combo" id="tel_from" style="display:none; margin-right:5px;">
				<option value="" style="color:gray">--�绰��Դ--</option>
				<?php echo list_option($tel_from_arr, '_value_', '_value_', $line["tel_from"]); ?>
			</select>
			<select name="engine" class="combo">
				<option value="" style="color:gray">--��������--</option>
				<?php echo list_option($engine_array, '_value_', '_value_', $line["engine"]); ?>
			</select>&nbsp;
			��Դ��վ��<input name="from_site" value="<?php echo $line["from_site"]; ?>" class="input" style="width:150px">
			�ؼ��ʣ�<input name="key_word" value="<?php echo $line["key_word"]; ?>" class="input" style="width:100px">
			<script type="text/javascript">
			function on_media_from_change(o) {
				byid("qq_from").style.display = "none";
				byid("tel_from").style.display = "none";
				if (o.value == "QQ") {
					byid("qq_from").style.display = "";
				} else if (o.value == "�绰") {
					byid("tel_from").style.display = "";
				}
			}
			on_media_from_change(byid("media_from"));
			</script>
		</td>
	</tr>

	<tr>
		<td class="left">�����˺ţ�</td>
		<td class="right">
			<select name="account" class="combo" <?php echo $ce["account"]; ?>>
				<option value="" style="color:gray">--��ѡ��--</option>
				<?php echo list_option($account_array, '_value_', '_value_', $line["account"]); ?>
			</select>
			<span class="intro">��ѡ�������˺�</span>
		</td>
	</tr>

	<tr>
		<td class="left"><?php echo $uinfo["part_id"] == 4 ? "�����" : "ר�Һ�"; ?>��</td>
		<td class="right">
			<input name="zhuanjia_num" value="<?php echo $line["zhuanjia_num"]; ?>" class="input" size="30" style="width:200px" <?php echo $ce["zhuanjia_num"]; ?>>
			&nbsp;&nbsp;&nbsp;
			�ƹ��ˣ�<input name="tuiguangren" value="<?php echo $line["tuiguangren"]; ?>" class="input" size="20" style="width:100px" <?php echo $ce["tuiguangren"]; ?>>
		</td>
	</tr>
	<tr>
		<td class="left" valign="top">ԤԼʱ�䣺</td>
		<td class="right">
			<input name="order_date" value="<?php echo $line["order_date"] ? @date('Y-m-d H:i:s', $line["order_date"]) : ''; ?>" class="input" style="width:150px" id="order_date"> <img src="image/calendar.gif" id="order_date" onClick="picker({el:'order_date',dateFmt:'yyyy-MM-dd HH:mm:ss'})" align="absmiddle" style="cursor:pointer" title="ѡ��ʱ��">

			<?php
			$show_days = array(
				"��" => $today = date("Y-m-d"), //����
				"��" => date("Y-m-d", strtotime("+1 day")), //����
				"��" => date("Y-m-d", strtotime("+2 days")), //����
				"�����" => date("Y-m-d", strtotime("+3 days")), //�����
				"����" => date("Y-m-d", strtotime("next Saturday")), //����
				"����" => date("Y-m-d", strtotime("next Sunday")), // ����
				"��һ" => date("Y-m-d", strtotime("next Monday")), // ��һ
				"һ�ܺ�" => date("Y-m-d", strtotime("+7 days")), // һ�ܺ�
				"���º�" => date("Y-m-d", strtotime("+15 days")), //����º�
			);
			if (!$ce["order_date"]) {
				echo '<br>��������: ';
				$arr = array();
				foreach ($show_days as $name => $value) {
					$arr[] = '<a href="javascript:input_date(\'order_date\', \''.$value.'\')">['.$name.']</a>';
				}
				echo implode(' ', $arr);
				echo '&nbsp;&nbsp; ʱ��: ';
				echo '<a href="javascript:input_time(\'order_date\',\'09:00:00\')">[����9��]</a>&nbsp;';
				echo '<a href="javascript:input_time(\'order_date\',\'14:00:00\')">[����2��]</a>&nbsp;';
			}
			?>
			<?php if ($line["order_date_log"]) { ?>
			<div id="order_date_log" style="padding-top:6px;"><b>ԤԼʱ���޸ļ�¼:</b> <br><?php echo strim($line["order_date_log"], '<br>'); ?></div>
			<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">ҽ����</td>
		<td class="right">
			<select style="width:90px;" name="xianchang_doctor" class="combo">
				<option value="" style="color:gray">-�ֳ�ҽ��-</option>
				<?php echo list_option($xianchang_doctor, '_value_', '_value_', $line["xianchang_doctor"]); ?>
			</select>&nbsp;
			<select style="width:90px;" name="doctor" class="combo">
				<option value="" style="color:gray">-����ҽ��-</option>
				<?php echo list_option($doctor_list, 'name', 'name', $line["doctor"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="left">��Լ״̬��</td>
		<td class="right">
			<select name="status" class="combo" onchange="change_xiaofei(this.value)" <?php echo $ce["status"]; ?>>
				<option value="0" style="color:gray">--��ѡ��--</option>
				<?php echo list_option($status_array, 'id', 'name', $line["status"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="left" valign="top">��ע��</td>
		<td class="right"><textarea name="memo" style="width:60%; height:60px;" class="input" <?php echo $ce["memo"]; ?>><?php echo $line["memo"]; ?></textarea> <span class="intro">������ע��Ϣ</span></td>
	</tr>

<?php if ($line["edit_log"]) { ?>
	<tr>
		<td class="left" valign="top">�����޸ļ�¼��</td>
		<td class="right"><?php echo str_replace("\r\n", "<br>", str_replace(" ", "&nbsp;", $line["edit_log"])); ?></td>
	</tr>
<?php } ?>

</table>

<?php
	$huifang = trim($line["huifang"]);
?>
<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">�绰�طü�¼</td>
	</tr>
	<tr>
		<td class="left" valign="top">�طü�¼��</td>
		<td class="right"><?php echo $line["huifang"] ? text_show($line["huifang"]) : "<font color=gray>(���޼�¼)</font>"; ?></td>
	</tr>
</table>

<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">��������</td>
	</tr>
	<tr>
		<td class="left" valign="top">����ˣ�</td>
		<td class="right"><?php echo $line["author"]; ?> @ <?php echo date("Y-m-d H:i", $line["addtime"]); ?> <?php echo $part_id_name[$line["part_id"]]; ?></td>
	</tr>
</table>

<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>