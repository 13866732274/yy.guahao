<?php
// --------------------------------------------------------
// - ����˵�� : ���Ͽⱨ�� - ��΢�����������
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-10-24
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";

if ($op == "fenpei") {

	// ��¼�����������´η���
	$_SESSION["wx_fenpei_btime"] = $_GET["btime"];
	$_SESSION["wx_fenpei_etime"] = $_GET["etime"];
	$_SESSION["wx_fenpei_num"] = $_GET["fenpei_num"];

	$tbegin = strtotime($_GET["btime"]);
	$tend = strtotime($_GET["etime"]." 23:59:59");
	$fenpei_num = intval($_GET["fenpei_num"]);
	$wx_uid = intval($_GET["wx_uid"]);
	if ($fenpei_num > 0 && $wx_uid > 0) {
		$_uinfo = $db->query("select * from sys_admin where id=$wx_uid limit 1", 1);
		$wx_uname = $_uinfo["realname"];
		$wx_hospitals = $_uinfo["hospitals"];
		if ($wx_hospitals == '') {
			exit('��΢����Աδ��������ң���ǰ���˺����õ������ٷ��䡣');
		}
		$wx_partid = $_uinfo["part_id"];
		if (!in_array($wx_partid, array(2,3))) {
			exit('��΢����Ա�������ò���ȷ����Ϊ����ͷ���绰�ͷ�����ǰ���˺����õ������ٷ��䡣');
		}

		$db->query("update $table set to_weixin=1, wx_is_fenpei=1, wx_uid=$wx_uid, wx_uname='$wx_uname' where hid in ($wx_hospitals) and part_id=$wx_partid and addtime>=$tbegin and addtime<=$tend and to_weixin=0 and wx_is_fenpei=0 and wx_is_add=0 and is_yuyue=0 and is_come=0 order by id desc limit $fenpei_num");
		$nums = mysql_affected_rows();

		echo '<script> alert("��Ϊ ['.$wx_uname.'] �ѷ����� ['.$nums.'] �����ϡ�"); self.location = "?"; </script>';
	} else {
		echo '��������';
	}
	exit;
}

if ($op == "get_info") {
	header('Content-type: text/javascript');
	$wx_uid = intval($_GET["wx_uid"]);

	$wx_uname = $db->query("select realname from sys_admin where id=$wx_uid limit 1", 1, "realname");

	// ��ѯ�������:
	$count1 = $db->query("select count(*) as c from $table where to_weixin>0 and wx_is_fenpei>0 and wx_uid=$wx_uid", 1, "c");

	// �ѼӺ�������:
	$count2 = $db->query("select count(*) as c from $table where to_weixin>0 and wx_is_fenpei>0 and wx_uid=$wx_uid and wx_is_add>0", 1, "c");

	$count3 = $count1 - $count2;

	$str = "[".$wx_uname."] �ܷ��仼������".$count1."���ѼӺ��ѣ�".$count2."��δ�Ӻ��ѣ�".$count3;
	if (count($count3) >= 0) {
		$str .= '��<a href="javascript:;" onclick="back_to_system('.$wx_uid.');" title="���ѷ���δ�Ӻ��ѵĻ��߹黹ϵͳ���������·����������">[�黹ϵͳ]</a>';
	}
	echo 'byid("wx_info").innerHTML = "'.addslashes($str).'"; ';

	exit;
}

if ($op == "back_to_system") {
	header('Content-type: text/javascript');
	$wx_uid = intval($_GET["wx_uid"]);

	$db->query("update $table set to_weixin=0, wx_is_fenpei=0, wx_uid=0, wx_uname='' where to_weixin>0 and wx_is_fenpei>0 and wx_uid=$wx_uid and wx_is_add=0");
	echo 'alert("�ѳɹ���δ�Ӻ��ѵĻ��߹黹ϵͳ��"); get_info('.$wx_uid.'); ';

	exit;
}

$weixin_zixun_arr = $db->query("select id, name from sys_admin where character_id in (50, 51, 52) order by character_id asc, name asc", "id", "name");


?>
<html>
<head>
<title>��΢�����������</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<script type="text/javascript">
function get_info(wx_uid) {
	load_js("?op=get_info&wx_uid="+wx_uid+"", "load_js");
}
function back_to_system(wx_uid) {
	if (confirm("���ѷ���δ�Ӻ��ѵĻ��߹黹ϵͳ���������·���������ˣ��Ƿ�ȷ������")) {
		load_js("?op=back_to_system&wx_uid="+wx_uid+"", "load_js");
	}
}
function check_data(oForm) {
	if (oForm.wx_uid.value == '') {
		alert("��΢����Ա������ѡ��"); return false;
	}
	if (oForm.btime.value == '' || oForm.etime.value == '') {
		alert("������ʱ��Ρ�Ϊ���������д�����ύ��"); return false;
	}
	if (oForm.fenpei_num.value == '' || oForm.fenpei_num.value == '0') {
		alert("������������Ϊ���������д�����ύ��"); return false;
	}
	return true;
}
</script>
</head>

<body style="padding:10px 20px;">

<style type="text/css">
.head_tips {border:2px solid #ffa87d; background:#ffe4d5; padding:5px 10px; border-radius:3px;  }
</style>
<div class="head_tips">����˵����ֻ�����δԤԼ��δ��Ժ��δ�Ӻ��ѵ����ݡ��뿴���������ز�����</div>

<form method="GET" action="" onsubmit="return check_data(this)">
<table width="100%" class="new_edit" style="margin-top:20px;">
	<tr>
		<td class="left" style="width:20%;">* ΢����Ա��</td>
		<td class="right" style="width:80%;">
			<select name="wx_uid" class="combo" onchange="get_info(this.value)">
				<option value="">������������</option>
				<?php echo list_option($weixin_zixun_arr, "_key_", "_value_"); ?>
			</select>
			<span id="wx_info" style="margin-left:10px;"></span>
		</td>
	</tr>

	<tr>
		<td class="left">* ����ʱ��Σ�</td>
		<td class="right">
			<input name="btime" id="begin_time" class="input" style="width:100px" value="<?php echo $_SESSION["wx_fenpei_btime"]; ?>" onclick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})"> ~ <input name="etime" id="end_time" class="input" style="width:100px" value="<?php echo $_SESSION["wx_fenpei_etime"]; ?>" onclick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})">��(���ȷ������ڽ��µĻ���)
		</td>
	</tr>

	<tr>
		<td class="left">* ����������</td>
		<td class="right">
			<input name="fenpei_num" class="input" style="width:100px" value="<?php echo $_SESSION["wx_fenpei_num"]; ?>"> ��
		</td>
	</tr>
</table>

<div class="button_line" style="margin-top:20px;">
	<input type="submit" class="submit" value="��ʼ����">
</div>
<input type="hidden" name="op" value="fenpei">

</form>



</body>
</html>