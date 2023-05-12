<?php
// --------------------------------------------------------
// - ����˵�� : ���õ�Ժ
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2011-09-14
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

if (!in_array("huifang", $gGuaHaoConfig)) {
	exit("�Բ�����û�лط�Ȩ��...");
}


$status_array = array(0 => '�ȴ�', 1 => '�ѵ�', 2 => 'δ��');

$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("��������.");
}

$line = $db->query("select * from $table where id=$id limit 1", 1);

// ����ҽ��:
$doctor_list = $db->query("select id,name from doctor where hospital_id='$user_hospital_id'");

// �ֳ�ҽ��:
$xianchang_doctor = $db->query("select id,realname from sys_admin where part_id=14 and concat(',',hospitals,',') like '%,{$hid},%'", "id", "realname");

// ��ѯ�ò��˻ط���Ϣ��
$remind_arr = $db->query("select * from patient_remind where hid=$hid and patient_id=$id and uid=$uid", 1);


if ($_POST) {
	$r = array();

	if ($_POST["track_status"] != '') {
		$track_status = $_POST["track_status"] == "-1" ? -1 : 0;
		$db->query("update $table set track_status='$track_status' where id=$id limit 1");
	}

	if ($_POST["track_status"] == "-1") {
		// �������٣���ɾ���ط�����(����еĻ�)
		$db->query("delete from patient_remind where patient_id=$id");
		$_POST["huifang_nexttime"] = ''; //�����ύ�Ļط�ʱ��Ҳ����
	}

	// �´λط�ʱ��:
	$remind_date = $_POST["huifang_nexttime"] ? intval(str_replace("-", "", $_POST["huifang_nexttime"])) : 0;

	$remind_time = trim($_POST["huifang_time"]);
	$remind_memo = trim($_POST["huifang_memo"]);

	if ($remind_date > 0) {
		if (is_array($remind_arr) && $remind_arr["id"]) {
			$remind_id = $remind_arr["id"];
			$db->query("update patient_remind set remind_date='$remind_date', remind_time='$remind_time', remind_memo='$remind_memo', add_uid=0, add_uname='', is_huifang=0 where id=$remind_id limit 1");
		} else {
			$time = time();
			$db->query("insert into patient_remind set hid=$hid, patient_id=$id, patient_name='".$line["name"]."', remind_date='$remind_date', remind_time='$remind_time', remind_memo='$remind_memo', uid=$uid, u_name='$realname', addtime=$time ");
		}
	} else {
		// ����ǵ���ط�
		if (is_array($remind_arr) && $remind_arr["id"] > 0) {
			$remind_id = $remind_arr["id"];
			$today_date = date("Ymd");
			//$db->query("delete from patient_remind where id=$remind_id and remind_date='$today_date' and patient_id=$id and uid=$uid limit 1");
			$db->query("update patient_remind set is_huifang=1 where id=$remind_id and remind_date='$today_date' and patient_id=$id and uid=$uid limit 1");
		}
	}


	if (isset($_POST["huifang"]) && trim($_POST["huifang"]) != '') {
		$_POST["huifang"] = str_replace("'", "", $_POST["huifang"]);
		$_POST["huifang"] = str_replace('"', "", $_POST["huifang"]);
		$huifang = trim($_POST["huifang"]);
		if ($_POST["huifang_qudao"] != "") {
			$huifang .= " [�ط�����:".$_POST["huifang_qudao"]."]";
		}
		$r["huifang"] = $line["huifang"].date("Y-m-d H:i")." ".$realname.": ".$huifang."\n";
	}

	if (isset($_POST["order_date"]) && date("Y-m-d H:i", $line["order_date"]) != $_POST["order_date"]) {
		$r["order_date"] = strtotime($_POST["order_date"]);
	}

	$talk = trim($_POST["talk_content"]);
	if ($talk != "") {
		$new_talk = trim($line["talk_content"])."\n��".$realname." ׷���� ".date("Y-m-d H:i ")."��\n".trim(strip_tags($talk));
		$new_talk = trim($new_talk);
		$new_talk = str_replace("\\", "", $new_talk);
		$new_talk = str_replace("'", "", $new_talk);
		$new_talk = str_replace('"', "", $new_talk);
		$db->query("update $table set talk_content='$new_talk' where id=$id limit 1");
	}


	if ($_POST["memo"]) {
		$r["memo"] = (rtrim($line["memo"]) ? (rtrim($line["memo"])."\n") : "").date("Y-m-d H:i ").$realname.": ".$_POST["memo"];
	}

	if (count($r) > 0) {
		//user_op_log("�طò���[".$line["name"]."]");
		$logs = patient_modify_log($r, $line, "order_date");
		if ($logs) {
			$r["edit_log"] = $logs;
		}

		$sqldata = $db->sqljoin($r);
		$sql = "update $table set $sqldata where id='$id' limit 1";
		ob_start();
		$rs = $db->query($sql);
		$error = ob_get_clean();
		if ($error) {
			echo "�ύ��������ϵ������Ա������<br>".$error;
			exit;
		}
		if ($rs) {
			$str = "�����ύ�ɹ���";
		} else {
			echo "�ύ��������ϵ������Ա������<br>".$db->sql;
			exit;
		}
	} else {
		$str = "�����ޱ䶯";
	}
	echo '<script type="text/javascript">'."\r\n";
	echo 'parent.msg_box("'.$str.'");'."\r\n";
	echo 'parent.load_src(0);'."\r\n";
	echo '</script>'."\r\n";
	exit;
}


$huifang_time = int_date_to_date($remind_arr["remind_date"]);

// page begin ----------------------------------------------------
?>
<html>
<head>
<title><?php echo $line["name"]; ?> - �ط�</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/wee_time.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.view {border:2px solid #ACD6FF; }
.view td {padding:5px 3px 3px 8px; border:1px solid #D9ECFF; }
.view .h {font-weight:bold; background:#E8F3FF; text-align:left; padding-left:15px; }
.view .l {text-align:right; color:#000000; background:#F4FAFF; }
.view .r {text-align:left; }
.fo_line {margin:15px 0 auto; text-align:center; }

.left {text-align:right; }
.right {padding:4px 0px; }
.v_align * {vertical-align:middle; }
</style>
<script language="javascript">
function check_data(oForm) {
	if (byid("order_date_need_check").value == "1") {
		if (byid("order_date").value == '' || byid("order_date").value == '0') {
			alert("ԤԼʱ��������ã�����Ϊ�գ������¼��˶ԡ�");
			return false;
		}
	}
	if (oForm.huifang.value == '') {
		if (!confirm("����û������ط����ݣ�ȷ��Ҫ�ύ��")) {
			oForm.huifang.focus();
			return false;
		}
	}
	if (byid("huifang_zhaiyao").value != '' && byid("qudao_select").value == '') {
		alert("�ף����ط������� Ϊ��ѡ��Ŷ����ѡ��һ����~");
		return false;
	}
	return true;
}
</script>
</head>

<body>
<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" align="center" class="view">
	<tr>
		<td colspan="4" class="h">��������</td>
	</tr>
	<tr>
		<td class="l">������</td>
		<td class="r" colspan="3"><b><?php echo $line["name"]; ?></b></td>
	</tr>
	<tr>
		<td class="l" width="15%">�Ա�</td>
		<td class="r" width="30%"><?php echo $line["sex"]; ?></td>
		<td class="l" width="15%">���䣺</td>
		<td class="r" width="40%"><?php echo $line["age"] > 0 ? $line["age"] : ""; ?></td>
	</tr>
	<tr>
		<td class="l">�绰��</td><!-- �ط���Ҫ��ʾ���� -->
		<td class="r"><?php echo $line["tel"]; ?></td>
		<td class="l">�ͷ�������</td>
		<td class="r"><?php echo $line["author"]; ?> @ <?php echo date("Y-m-d H:i", $line["addtime"]); ?> <?php echo $part_id_name[$line["part_id"]]; ?></td>
	</tr>
	<tr>
		<td class="l">��ѯ���ݣ�</td>
		<td class="r" colspan="3"><?php echo text_show(rtrim($line["content"])); ?></td>
	</tr>
	<tr>
		<td class="l">�������ͣ�</td>
		<td class="r"><?php echo $disease_id_name[$line["disease_id"]]; ?></td>
		<td class="l">ý����Դ��</td>
		<td class="r"><?php echo $line["media_from"]; ?></td>
	</tr>
	<tr>
		<td class="l">�����˺ţ�</td>
		<td class="r"><?php echo $line["account"]; ?></td>
		<td class="l">ר�Һţ�</td>
		<td class="r"><?php echo $line["zhuanjia_num"]; ?></td>
	</tr>
	<tr>
		<td class="l">ԤԼʱ�䣺</td>
		<td class="r" colspan="3"><?php echo @date("Y-m-d H:i", $line["order_date"]); ?></td>
	</tr>
	<tr>
		<td class="l">��Լ״̬��</td>
		<td class="r"><?php echo $status_array[$line["status"]]; ?></td>
		<td class="l">ҽ����</td>
		<td class="r">
<?php
if (in_array($uinfo["part_id"], array(2,3))) {
	echo "<font color='gray'>(����ʾ)</font>";
} else {
	if ($line["xianchang_doctor"] || $line["doctor"]) {
		echo $line["xianchang_doctor"] ? ("�ֳ�ҽ����".$line["xianchang_doctor"]."&nbsp;") : "";
		echo $line["doctor"] ? ("����ҽ����".$line["doctor"]) : "";
	} else {
		echo "<font color='gray'>(δ����)</font>";
	}
}
?>
		</td>
	</tr>
	<tr>
		<td colspan="4" class="h">�ط�</td>
	</tr>
	<tr>
		<td class="l" valign="top">�����طã�</td>
		<td class="r" colspan="3"><?php echo $line["huifang"] ? text_show($line["huifang"]) : "<font color=gray>(���޼�¼)</font>"; ?></td>
	</tr>
	<tr>
		<td class="l" valign="top">�ط����ѣ�</td>
		<td class="r" colspan="3" style="color:blue">
<?php
if ($huifang_time) {
	echo "<b>".$huifang_time." ".$remind_arr["remind_time"]."</b> ".$remind_arr["remind_memo"]." (".($remind_arr["add_uname"] ? $remind_arr["add_uname"] : "�Լ�")." @ ".date("Y-m-d H:i", $remind_arr["addtime"]).")";
} else {
	echo "(��������)";
}
?>
		</td>
	</tr>

	<tr>
		<td class="l">�ط�������</td>
		<td class="r" colspan="3">
			<input type="radio" name="huifang_qudao" onclick="set_qudao(this)" value="�绰" id="h1"><label for="h1">�绰</label>
			<input type="radio" name="huifang_qudao" onclick="set_qudao(this)" value="΢��" id="h3"><label for="h3">΢��</label>
			<input type="radio" name="huifang_qudao" onclick="set_qudao(this)" value="QQ" id="h4"><label for="h4">QQ</label>
			<input type="radio" name="huifang_qudao" onclick="set_qudao(this)" value="����" id="h2"><label for="h2">����</label>
			<span style="color:red; margin-left:20px;">(��ѡ)</span>
			<input type="hidden" id="qudao_select" value="" />
			<script type="text/javascript">
			function set_qudao(obj) {
				byid("qudao_select").value = obj.value;
			}
			</script>
		</td>
	</tr>

	<tr>
		<td class="l" valign="top">�ط��ܽ᣺</td>
		<td class="r" colspan="3">
			<textarea name="huifang" id="huifang_zhaiyao" style="width:500px; height:40px;" class="input"></textarea>
			<div style="margin-top:5px;">��д�ط�ժҪ����Ҫ�������¼</div>
		</td>
	</tr>

	<tr>
		<td class="l" valign="top">�����¼��</td>
		<td class="r" colspan="3">
			<textarea name="talk_content" id="talk_content" style="width:500px; height:100px;" class="input"></textarea>
			<div style="margin-top:5px;">��׷�ӵ�ԭ�������¼����</div>
		</td>
	</tr>

	<tr>
		<td class="l" valign="top">�Ƿ�������٣�</td>
		<td class="r" colspan="3">
			<input type="radio" name="track_status" value="0" <?php if ($line["track_status"] == 0) echo "checked"; ?> id="t0"><label for="t0">��������</label>
			<input type="radio" name="track_status" value="-1" <?php if ($line["track_status"] == -1) echo "checked"; ?> id="t1"><label for="t1" title="����ɾ���ط�����">��������</label>
		</td>
	</tr>

	<tr>
		<td class="l" valign="top">�޸�ԤԼʱ�䣺</td>
		<td class="r" colspan="3">
<?php if ($line["status"] != 1) { ?>
			<input name="order_date" value="<?php echo date("Y-m-d H:i", $line["order_date"]); ?>" class="input" style="width:150px" id="order_date" readonly="true"> <img src="image/calendar.gif" onClick="picker({el:'order_date',dateFmt:'yyyy-MM-dd HH:mm'})" align="absmiddle" style="cursor:pointer" title="ѡ��ʱ��"> <span class="intro">���ޱ�Ҫ �����޸� (�޸Ľ���¼��־)</span>
			<input type="hidden" id="order_date_need_check" value="1" />
<?php } else { ?>
			<font color="red">�ѵ�Ժ�������޸�</font>
<?php } ?>
		</td>
	</tr>
	<tr>
		<td class="l" valign="top">�´λط����ѣ�</td>
		<td class="r" colspan="3">
			���ڣ�<input name="huifang_nexttime" value="" class="input" style="width:100px" id="huifang_nexttime" onclick="picker({el:'huifang_nexttime',dateFmt:'yyyy-MM-dd'})">&nbsp;
			��ʱ�䣺<input name="huifang_time" id="huifang_time" value="" class="input" style="width:80px; cursor:help;" onclick="wee_time_show_picker('huifang_time','left','top')">&nbsp;
			����ע��<input name="huifang_memo" style="width:100px;" value="" class="input" title="��д�´����ѵı�ע">
			<span class="intro">(������Ե�ǰ���Ѳ����ı�)</span>
		</td>
	</tr>
	<!-- <tr>
		<td class="l" valign="top">��ӱ�ע��</td>
		<td class="r" colspan="3"><input name="memo" style="width:80%;" class="input"> <span class="intro">���һ����ע����</span></td>
	</tr> -->
</table>
<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">

<br>
<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>

<br>
<br>

</body>
</html>