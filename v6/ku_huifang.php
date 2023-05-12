<?php
// --------------------------------------------------------
// - ����˵�� : �ط�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2013-7-12
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";

$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("��������.");
}

$line = $db->query("select * from $table where id='$id' limit 1", 1);


if ($_POST) {
	$r = array();

	if ($_POST["track_status"] != '') {
		$track_status = $_POST["track_status"] == "-1" ? -1 : 0;
		$db->query("update $table set track_status='$track_status' where id='$id' limit 1");
	}

	if ($_POST["talk_content"] != "") {
		// ׷�������¼:
		$_POST["talk_content"] = str_replace("'", "", $_POST["talk_content"]);
		$_POST["talk_content"] = str_replace('"', "", $_POST["talk_content"]);
		$qd = $_POST["huifang_qudao"] != "" ? (" ".$_POST["huifang_qudao"]."") : "";
		$talk = trim($line["talk_content"]."\r\n��".date("Y-m-d H:i")." ".$realname.$qd." �������ݡ�\r\n".$_POST["talk_content"]);
		$db->query("update $table set talk_content='$talk' where id='$id' limit 1");
	}

	if ($_POST["track_status"] == "-1") {
		// �������٣���ɾ���ط�����(����еĻ�)
		$db->query("delete from ku_remind where ku_id=$id and uid=$uid limit 1");
	} else {
		if (trim($_POST["remind_date"]) != '') {
			$r_date = date("Ymd", strtotime($_POST["remind_date"]));
			$p_name = $line["name"];

			// ��ѯ�Ƿ�����ӹ�:
			$remind_line = $db->query("select * from ku_remind where ku_id=$id and uid=$uid limit 1", 1);
			if ($remind_line["id"] > 0) {
				// ������ڱ�������������:
				if ($remind_line["remind_date"] != $r_date) {
					$r_id = $remind_line["id"];
					$db->query("update ku_remind set remind_date='$r_date' where id=$r_id limit 1");
				}
			} else {
				// û�м�¼�������
				$time = time();
				$db->query("insert into ku_remind set remind_date='$r_date', ku_id=$id, patient_name='$p_name', uid=$uid, u_name='$realname', addtime=$time");
			}
		}
	}


	if (isset($_POST["huifang"]) && trim($_POST["huifang"]) != '') {
		$_POST["huifang"] = str_replace("'", "", $_POST["huifang"]);
		$_POST["huifang"] = str_replace('"', "", $_POST["huifang"]);
		$huifang = $_POST["huifang"];
		if ($_POST["huifang_qudao"] != "") {
			$huifang .= " [�ط�����:".$_POST["huifang_qudao"]."]";
		}
		$hf_log = $line["hf_log"].date("Y-m-d H:i")." ".$realname.": ".trim(strip_tags($huifang))."\n";
		$time = time();
		$sql = "update $table set hf_log='$hf_log', updatetime=$time where id='$id' limit 1";
		ob_start();
		$rs = $db->query($sql);
		$db->query("insert into ku_huifang set hid=".$line["hid"].", ku_id=$id, content='".$_POST["huifang"]."', qudao='".$_POST["huifang_qudao"]."', addtime=$time, author='$realname'");
		$db->query("update $table set huifang_num=huifang_num+1 where id=$id limit 1");
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


// --------- ���� -----------
function _talk_text_show($s) {
	$s = str_replace(" ", "&nbsp;", $s);
	$s = str_replace("\r", "", $s);
	$s = str_replace("\n", "<br>", $s);
	for ($i=0; $i<5; $i++) {
		$s = str_replace("<br><br>", "<br>", $s);
	}
	$s = "<br>".$s;
	$s = preg_replace("/<br>([^>]*?\d{1,2}:\d{2}:\d{2})/", "<br><br><font color=blue>[\\1]</font>", $s);
	$s = preg_replace("/<br>(��.*?��)/", "<br><br><font color=red>\\1</font>", $s);
	while (substr($s, 0, 4) == "<br>") {
		$s = substr($s, 4);
	}
	return $s;
}

// ���Ѽ�¼��
$remind_line = $db->query("select * from ku_remind where ku_id=$id and uid=$uid limit 1", 1);
$remind_id = $remind_line["id"];
$remind_date = $remind_id > 0 ? int_date_to_date($remind_line["remind_date"]) : "";


// page begin ----------------------------------------------------
?>
<html>
<head>
<title>�ط�</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
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
	if (oForm.huifang.value == '') {
		if (!confirm("����û������ط����ݣ�ȷ��Ҫ�ύ��")) {
			oForm.huifang.focus();
			return false;
		}
	}
	if (byid("huifang_zhaiyao").value != '' && byid("qudao_select").value == '') {
		alert("�ף����ط�����������Ϊ��ѡ��Ŷ����ѡ��һ����~");
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
		<td class="r"><b><?php echo $line["name"]; ?></b></td>
		<td class="l">����ҽԺ��</td>
		<td class="r"><?php echo $line["h_name"]; ?></td>
	</tr>
	<tr>
		<td class="l">��ѯ���ݣ�</td>
		<td class="r" colspan="3"><?php echo text_show(rtrim($line["zx_content"])); ?></td>
	</tr>
	<tr>
		<td class="l" width="15%">�Ա�</td>
		<td class="r" width="30%"><?php echo $line["sex"]; ?></td>
		<td class="l" width="15%">���䣺</td>
		<td class="r" width="40%"><?php echo $line["age"] > 0 ? $line["age"] : ""; ?></td>
	</tr>
	<tr>
		<td class="l">�ֻ���</td>
		<td class="r"><?php echo _ku_show_tel($line); ?><?php echo strlen($line["mobile"]) == 11 ? (" (".get_mobile_location($line["mobile"]).")") : ""; ?></td>
		<td class="l">������Դ��</td>
		<td class="r"><?php echo $line["laiyuan"]; ?></td>
	</tr>
	<tr>
		<td class="l">����QQ��</td>
		<td class="r"><?php echo $line["qq"]; ?></td>
		<td class="l">�ҷ�QQ��</td>
		<td class="r"><?php echo $line["order_qq"]; ?></td>
	</tr>
	<tr>
		<td class="l">����΢�ţ�</td>
		<td class="r"><?php echo $line["weixin"]; ?></td>
		<td class="l">�ҷ�΢�ţ�</td>
		<td class="r"><?php echo $line["order_weixin"]; ?></td>
	</tr>
	<tr>
		<td class="l">���ʱ�䣺</td>
		<td class="r"><?php echo date("Y-m-d H:i:s", $line["addtime"]); ?></td>
		<td class="l">����ʱ�䣺</td>
		<td class="r"><?php echo $line["updatetime"] != $line["addtime"] ? date("Y-m-d H:i:s", $line["updatetime"]) : "(δ���¹�)"; ?></td>
	</tr>

<?php if ($line["talk_content"]) { ?>
	<tr>
		<td class="l" valign="top">�����¼��</td>
		<td class="r" colspan="3"><?php echo _talk_text_show($line["talk_content"]); ?></td>
	</tr>
<?php } ?>

	<tr>
		<td colspan="4" class="h">�ط�</td>
	</tr>
	<tr>
		<td class="l" valign="top">�����طã�</td>
		<td class="r" colspan="3"><?php echo $line["hf_log"] ? text_show($line["hf_log"]) : "<font color=gray>(�޼�¼)</font>"; ?></td>
	</tr>
	<tr>
		<td class="l" valign="top">���λطã�</td>
		<td class="r" colspan="3">
			<div style="margin-top:0px;">
				�ط�������
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
			</div>
			<div style="margin-top:5px;">
				�ط�ժҪ��<input name="huifang" id="huifang_zhaiyao" class="input" style="width:70%;">
			</div>
			<div style="margin-top:5px;" class="v_align">
				�����¼��<textarea name="talk_content" style="width:70%; height:60px;" class="input" valign="middle"></textarea> (΢��/QQ�ط�����д)
			</div>
			<div style="margin-top:5px;">
				�Ƿ���٣�
				<input type="radio" name="track_status" value="0" <?php if ($line["track_status"] == 0) echo "checked"; ?> id="t0"><label for="t0">��������</label>
				<input type="radio" name="track_status" value="-1" <?php if ($line["track_status"] == -1) echo "checked"; ?> id="t1"><label for="t1" title="����ɾ���ط�����">��������</label>
				<span>���������´��������ڣ�</span>
				<input name="remind_date" value="<?php echo $remind_date; ?>" class="input" style="width:150px" id="remind_date"> <img src="image/calendar.gif" id="remind_date" onClick="picker({el:'remind_date',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="ѡ������">
			</div>
		</td>
	</tr>
</table>

<div class="button_line">
	<input type="submit" class="buttonb" value="�ύ����">
</div>

<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
</form>

</body>
</html>