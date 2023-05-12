<?php
// --------------------------------------------------------
// - ����˵�� : �������Ͽ�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2013-7-11
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";

$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("����������ˢ��ҳ�������...");
}
$mode = "edit";


// ɾ������ @ 2014-6-11
if ($_GET["op"] == "delete_remind") {
	$remind_id = intval($_GET["id"]);
	$db->query("delete from ku_remind where id=$remind_id and uid=$uid limit 1");
	echo '<script> alert("����ɾ���ɹ�~          "); history.back(); </script>';
	exit;
}


if ($_POST) {
	$old = $db->query("select * from $table where id=$id limit 1", 1);

	if (trim($_POST["remind_date"]) != '') {
		$r_date = date("Ymd", strtotime($_POST["remind_date"]));
		$p_name = $old["name"];

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

	$r = array();

	$r["name"] = $_POST["name"];
	$r["mobile"] = $_POST["mobile"];
	$r["area"] = $_POST["area"];
	$r["qq"] = $_POST["qq"];
	$r["order_qq"] = $_POST["order_qq"];
	$r["weixin"] = $_POST["weixin"];
	$r["order_weixin"] = $_POST["order_weixin"];
	$r["disease_id"] = $did = intval($_POST["disease_id"]);
	if ($did > 0) {
		$dname = $db->query("select name from disease where id=$did limit 1", 1, "name");
		if (substr($dname, 0, 2) != "��" && (substr_count($dname, "(") > 0 || substr_count($dname, "��") > 0 || substr_count($dname, "��") > 0)) {
			$dname = str_replace("��", "(", $dname);
			$dname = str_replace("��", "(", $dname);
			list($name2, $tmp) = explode("(", $dname, 2);
			if (trim($name2) != '') $dname = trim($name2);
		}
		$r["disease_name"] = $dname;
	}

	$r["zx_content"] = $_POST["zx_content"];
	$r["talk_content"] = $_POST["talk_content"];
	$r["swt_id"] = trim($_POST["swt_id"]);
	$r["updatetime"] = time();

	if ($_POST["is_add_haoyou_submit"] > 0) {
		$r["wx_is_add"] = $_POST["is_add_haoyou"] ? 1 : 0;
		if ($_POST["is_add_haoyou"] > 0) {
			$_POST["weixin_submit"] = 0;
		}
	}

	if ($_POST["weixin_submit"] > 0) {
		$r["to_weixin"] = intval($_POST["to_weixin"]) ? 1 : 0;
		if ($r["to_weixin"] > 0) {
			$r["wx_uid"] = $wx_uid = intval($_POST["weixin_kefu_id"]);
			if ($wx_uid > 0) {
				$r["wx_uname"] = $db->query("select realname from sys_admin where id=$wx_uid limit 1", 1, "realname");
			} else {
				$r["wx_uname"] = "";
			}
		} else {
			$r["wx_uid"] = 0;
			$r["wx_uname"] = "";
			//$r["wx_is_add"] = 0;
		}
	}

	// ��¼�޸���־ @ 2016-09-21
	$log_fields_name = array(
      "name" => "����",
      "mobile" => "�ֻ���",
		"qq" => "����QQ",
		"order_qq" => "�ҷ�QQ",
		"weixin" => "����΢��",
		"order_weixin" => "�ҷ�΢��",
		"zx_content" => "��ѯ����");
	$log_fields = array_keys($log_fields_name);
	$log = array();
	foreach ($log_fields as $f) {
		if ($r[$f] != $old[$f]) {
			$log[] = "��".$log_fields_name[$f]."�ɡ�".$old[$f]."���޸�Ϊ��".$r[$f]."��";
		}
	}
	if (count($log) > 0) {
		$log_str = date("Y-m-d H:i ").$realname." ".implode("��", $log);
		$r["edit_log"] = trim(rtrim($old["edit_log"])."\r\n".$log_str);
        $userip = get_ip();
  user_op_log($r["edit_log"], "", $tmp_uinfo["id"], $tmp_uinfo["realname"]);
	}
  

	// end ��¼�޸���־

	$sqldata = $db->sqljoin($r);

	$sql = "update $table set $sqldata where id='$id' limit 1";

	if ($db->query($sql)) {
		echo '<script> parent.msg_box("�ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "�ύʧ�ܣ����Ժ����ԣ�";
	}



	exit;
}

if ($mode == "edit") {
	$line = $db->query("select * from $table where id='$id' limit 1", 1);
}

// ���Ѽ�¼��
$remind_line = $db->query("select * from ku_remind where ku_id=$id and uid=$uid limit 1", 1);
$remind_id = $remind_line["id"];
$remind_date = $remind_id > 0 ? int_date_to_date($remind_line["remind_date"]) : "";


$title = $mode == "edit" ? "�޸�" : "����";
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
#rec_part, #rec_user {margin-top:6px; }
.rec_user_b {width:100px; float:left; }
.rec_group_part {clear:both; margin:10px 0 5px 0; font-weight:bold; }
.z {width:100px; text-align:right; display:inline-block; }
</style>

<script language="javascript">
function check_submit(o) {
	if (o.qq.value != '' && o.order_qq.value == "") {
		alert("����д����QQʱ�����ҷ�QQ��Ҳ������д��"); return false;
	}
	if (o.weixin.value != '' && o.order_weixin.value == "") {
		alert("����д����΢��ʱ�����ҷ�΢�š�Ҳ������д��"); return false;
	}
	if (o.zx_content.value == "") {
		alert("�����뻼����ѯ���ݣ�"); o.zx_content.focus(); return false;
	}
	return true;
}
function update_check_color(o) {
	o.parentNode.getElementsByTagName("label")[0].style.color = o.checked ? "blue" : "";
}
</script>

</head>

<body>

<form name="mainform" action="" method="POST" onsubmit="return check_submit(this)">
<table width="100%" class="edit" style="border:0; margin-top:10px;">
	<tr>
		<td class="left">������</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" style="width:200px;"><label class="z">�ֻ��ţ�</label><input name="mobile" value="<?php echo $line["mobile"]; ?>" class="input" style="width:150px;">
		</td>
	</tr>
	<tr>
		<td class="left">������</td>
		<td class="right">
<?php
	$disease_arr = $db->query("select id, name from disease where hospital_id=".$line["hid"]." and isshow=1 order by sort desc, name asc", "id", "name");
?>
			<select name="disease_id" class="combo" style="width:200px">
				<option value="" style="color:gray">��ѡ�񼲲�</option>
				<?php echo list_option($disease_arr, "_key_", "_value_", $line["disease_id"]); ?>
			</select><label class="z">���ڵأ�</label><input id="area" name="area" value="<?php echo $line["area"]; ?>" class="input" style="width:100px">&nbsp;
			<a href="javascript:;" onclick="area_set(this);">����</a>
			<a href="javascript:;" onclick="area_set(this);">����</a>
			<a href="javascript:;" onclick="area_set(this);">����</a>
			<select name="type" id="userType" onclick="select_area_set(this);">
<option value="����">����</option>
<option value="��˳">��˳</option>
<option value="�Ͻ�">�Ͻ�</option>
<option value="����">����</option>
<option value="����ˮ">����ˮ</option>
<option value="ǭ��">ǭ��</option>
<option value="ǭ����">ǭ����</option>
<option value="ǭ����">ǭ����</option>
<option value="ͭ��">ͭ��</option>
</select>
			<script type="text/javascript">
			function area_set(obj) {
				byid("area").value = obj.innerHTML;
			}
			function select_area_set(obj) {
				var myType = document.getElementById("userType");//��ȡselect����
var index = myType.selectedIndex; //��ȡѡ���е�������selectIndex��ʾ���ǵ�ǰ��ѡ�е�index
byid("area").value = myType.options[index].value;//��ȡѡ����options��valueֵ
			}
			</script>
		</td>
	</tr>
	<tr>
		<td class="left">����QQ��</td>
		<td class="right"><input name="qq" value="<?php echo $line["qq"]; ?>" class="input" style="width:200px"><label class="z">�ҷ�QQ��</label><input name="order_qq" value="<?php echo $line["order_qq"]; ?>" class="input" style="width:150px"></td>
	</tr>
	<tr>
		<td class="left">����΢�ţ�</td>
		<td class="right"><input name="weixin" value="<?php echo $line["weixin"]; ?>" class="input" style="width:200px;"><label class="z">�ҷ�΢�ţ�</label><input name="order_weixin" value="<?php echo $line["order_weixin"]; ?>" class="input" style="width:150px"></td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> ��ѯ���ݣ�</td>
		<td class="right">
			<textarea name="zx_content" style="width:80%; height:60px; vertical-align:middle;" class="input"><?php echo $line["zx_content"]; ?></textarea>
		</td>
	</tr>

	<tr>
		<td class="left">�����¼��</td>
		<td class="right">
			<textarea name="talk_content" style="width:80%; height:100px; vertical-align:middle;" class="input"><?php echo $line["talk_content"]; ?></textarea>
		</td>
	</tr>

	<tr>
		<td class="left">����ͨ������ݣ�</td>
		<td class="right">
			<input name="swt_id" value="<?php echo $line["swt_id"]; ?>" class="input" style="width:200px">
		</td>
	</tr>

	<tr>
		<td class="left">�������ڣ�</td>
		<td class="right">
			<input name="remind_date" value="<?php echo $remind_date; ?>" class="input" style="width:150px" id="remind_date"> <img src="image/calendar.gif" id="remind_date" onClick="picker({el:'remind_date',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="ѡ������">
			<?php if ($remind_id > 0) { ?><a href="?op=delete_remind&id=<?php echo $remind_id; ?>">ɾ������</a><?php } else { ?><span class="intro">�����´��������ڣ�Ϊ��������</span><?php } ?>
		</td>
	</tr>

<?php if ($line["to_weixin"] == 0 && ($line["uid"] == $uid || $is_super_admin)) { ?>
	<tr>
		<td class="left">΢�ź��ѣ�</td>
		<td class="right">
			<input type="hidden" name="is_add_haoyou_submit" value="1">
			<input type="checkbox" name="is_add_haoyou" value="1" id="is_add_haoyou" onchange="show_hide_to_weixin()" onclick="show_hide_to_weixin()" <?php if ($line["wx_is_add"]) echo " checked"; ?>><label for="is_add_haoyou">���Ѽ��Ϻ���</label>
		</td>
	</tr>
	<script type="text/javascript">
	function show_hide_to_weixin() {
		byid("to_weixin_tr").style.display = byid("is_add_haoyou").checked ? "none" : "";
	}
	</script>
<?php } ?>

<?php if ($is_super_admin || $line["uid"] == $uid) { ?>
	<tr id="to_weixin_tr" style="display:<?php echo $line["wx_is_add"] ? "none" : ""; ?>">
		<td class="left">΢�ŶԽ�</td>
		<td class="right">
			<input type="hidden" name="weixin_submit" value="1">
			<input type="checkbox" name="to_weixin" value="1" id="to_weixin" onclick="show_hide_weixin()"><label for="to_weixin">ת΢����</label>
			<span id="weixin_area" style="display:none; margin-left:20px;">
				<span id="weixin_kefu_select">
<?php
	$_hid = $line["hid"];
	$part_id = $uinfo["part_id"];
	if (!in_array($part_id, array(2,3))) $part_id = 2;
	$wx_arr = $db->query("select id, concat(realname,if(online>0,' [����]','')) as realname from sys_admin where part_id=$part_id and concat(',',hospitals,',') like '%,{$_hid},%' and character_id in (50,51,52) order by realname asc", "id", "realname");
?>
					<select name="weixin_kefu_id" class="combo">
						<option value="" style="color:gray">��ѡ��΢�ŶԽ���</option>
						<?php echo list_option($wx_arr, "_key_", "_value_", $line["wx_uid"]); ?>
					</select>
				</span>
				<span>�������ѡ��������΢���鶼���Կ���</span>
			</span>
		</td>
	</tr>
	<script type="text/javascript">
	function show_hide_weixin() {
		var is_check = byid("to_weixin").checked ? 1 : 0;
		byid("weixin_area").style.display = is_check ? "" : "none";
	}
	</script>

	<?php if ($line["to_weixin"]) { ?>
	<script type="text/javascript">
	byid("to_weixin").checked = true;
	byid("weixin_area").style.display = "";
	</script>
	<?php } ?>
<?php } ?>

</table>

<input type="hidden" name="id" value="<?php echo $id; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>