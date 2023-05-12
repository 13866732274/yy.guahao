<?php
// --------------------------------------------------------
// - ����˵�� : �������Ͽ�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2013-7-11
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";

$is_talk_not_empty = $uinfo["part_id"] == 2 ? 1 : 0; //���������Ƿ����

if ($op == "check_repeat") {
	header('Content-Type: application/x-javascript;');
	$allow_field = explode(" ", "name mobile qq weixin");
	$search_field = trim($_GET["search_type"]);
	if (!in_array($search_field, $allow_field)) exit("��������");
	$value = trim(wee_safe_key(mb_convert_encoding($_GET["js_value"], "gbk", "UTF-8")));
	$value = str_replace("��", "", $value);

	$line = $db->query("select * from ku_list where $search_field='$value' order by id desc limit 1", 1);
	$tips = $alert = '';
	if ($line["id"] > 0) {
		$alert = '['.$value.'] �Ѿ��� ['.$line["u_name"]."] �� ".date("Y-m-d H:i", $line["addtime"])." ��ӵ� [".$line["h_name"]."]�������鿼���Ƿ������ӣ�";
		$tips = "��<font color=red>�ظ�</font>��";
	} else {
		$tips = "����Ч��";
	}

	if ($alert) {
		echo 'alert("'.$alert.'");';
	}
	if ($tips && $_GET["res_id"]) {
		echo 'byid("'.$_GET["res_id"].'").innerHTML = "'.$tips.'";';
	}
	exit;
}

if ($op == "load_kefu") {
	header('Content-Type: application/x-javascript;');
	$hid = intval($_GET["hid"]);
	$part_id = $uinfo["part_id"];
	if (!in_array($part_id, array(2,3))) $part_id = 2;
	if ($hid > 0) {
		$arr = $db->query("select id, concat(realname,if(online>0,' [����]','')) as realname from sys_admin where part_id=$part_id and concat(',',hospitals,',') like '%,{$hid},%' and character_id in (50,51,52) order by realname asc", "id", "realname");
		if (count($arr) == 0) {
			$arr[0] = "�ÿ�������΢�ŶԽ���";
		} else {
			$arr[0] = " ";
		}
		foreach ($arr as $id => $name) {
			$arr[$id] = mb_convert_encoding($name, "UTF-8", "gbk");
		}
		echo 'var arr='.json_encode($arr).";";
		echo 'load_kefu_do(arr);';
	}
	exit;
}

if ($op == "get_disease") {
	header('Content-Type: application/x-javascript; charset=UTF-8');
	$hid = intval($_GET["hid"]);
	if ($hid > 0) {
		$disease_arr = $db->query("select id, name from disease where hospital_id=$hid and isshow=1 order by sort desc, name asc", "id", "name");
		$disease_arr[0] = " ";
		foreach ($disease_arr as $id => $name) {
			$disease_arr[$id] = mb_convert_encoding($name, "UTF-8", "gbk");
		}
		echo 'var arr='.json_encode($disease_arr).";";
		echo 'update_disease_do(arr);';
	}
	exit;
}

$mode = "add";

if ($_POST) {

	$r = array();
	$r["hid"] = $_hid = intval($_POST["hid"]);
	if ($_hid > 0) {
		$r["h_name"] = $db->query("select short_name as name from hospital where id=$_hid limit 1", 1, "name");
	} else {
		exit("����ҽԺ����ѡ��");
	}

	// 2014-3-11 ����
	$r["part_id"] = $uinfo["part_id"];
	$r["name"] = trim($_POST["name"]);
	$r["sex"] = trim($_POST["sex"]);
	$r["age"] = trim($_POST["age"]);
	$r["mobile"] = str_replace("��", "", trim($_POST["mobile"]));
	$r["area"] = trim($_POST["area"]);
	$r["qq"] = trim($_POST["qq"]);
	$r["order_qq"] = trim($_POST["order_qq"]);
	$r["weixin"] = trim($_POST["weixin"]);
	$r["order_weixin"] = trim($_POST["order_weixin"]);
	$r["zx_content"] = trim($_POST["zx_content"]);
	$r["talk_content"] = trim($_POST["talk_content"]);
	$r["laiyuan"] = trim($_POST["laiyuan"]);
	$r["swt_id"] = trim($_POST["swt_id"]);

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

	// �ύ��΢���� @ 2016-10-20
	$r["to_weixin"] = $to_weixin = intval($_POST["to_weixin"]) > 0 ? 1 : 0;
	if ($to_weixin) {
		$r["wx_uid"] = $wx_uid = intval($_POST["weixin_kefu_id"]);
		if ($wx_uid > 0) {
			$r["wx_uname"] = $db->query("select realname from sys_admin where id=$wx_uid limit 1", 1, "realname");
		}
	}

	$r["addtime"] = time();
	$r["updatetime"] = time();
	$r["uid"] = $uid;
	$r["u_name"] = $realname;


	$sqldata = $db->sqljoin($r);
	$sql = "insert into $table set $sqldata";
	$ins_id = $db->query($sql);

	if ($ins_id > 0) {

		// ��ӻط����ѣ�
		if (trim($_POST["remind_date"]) != '') {
			$r_date = date("Ymd", strtotime($_POST["remind_date"]));
			$time = time();
			$db->query("insert into ku_remind set remind_date='$r_date', ku_id=$ins_id, patient_name='".$_POST["name"]."', uid=$uid, u_name='$realname', addtime=$time");
		}

		echo '<script> parent.update_content(); </script>';
		echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "�ύʧ�ܣ����Ժ����ԣ�";
	}
	exit;
}

$title = "����";
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
</style>

<script language="javascript">
function check_submit(o) {
	if (o.hid.value == "") {
		alert("��ѡ��������ҽԺ��"); o.hid.focus(); return false;
	}
	if (o.area.value.trim() == "") {
		alert("�����ڵء�Ϊ���������д�������ύ��"); o.area.focus(); return false;
	}
	if (o.zx_content.value.trim() == "") {
		alert("����ѯ���ݡ�Ϊ���������͵�� ~ "); o.zx_content.focus(); return false;
	}
	if (o.zx_content.value.length > 200) {
		alert("����ѯ���ݡ�����̫�࣬����Ӱ��ҳ����ʾЧ��������Ƶ�200���������ڣ��˴���Ҫճ�������¼����ǰ������"+o.zx_content.value.length); o.zx_content.focus(); return false;
	}
<?php if ($is_talk_not_empty) { ?>
	if (o.talk_content.value == "") {
		alert("�������¼��Ϊ������~ "); o.talk_content.focus(); return false;
	}
<?php } ?>
	if (o.qq.value != '' && o.order_qq.value == "") {
		alert("����д����QQʱ�����ҷ�QQ��Ҳ������д��"); return false;
	}
	if (o.weixin.value != '' && o.order_weixin.value == "") {
		alert("����д����΢��ʱ�����ҷ�΢�š�Ҳ������д��"); return false;
	}
	if (o.laiyuan.value.trim() == "") {
		alert("��������Դ��������д~ "); o.laiyuan.focus(); return false;
	}
	if (o.mobile.value.trim() == '' && o.qq.value.trim() == '' && o.weixin.value.trim() == '') {
		alert("�ֻ��š�QQ�š�΢�ź�����Ҫ������һ����ϵ��ʽ��"); return false;
	}
	if (o.laiyuan.value.trim() == "����ͨ" && byid("swt_id").value == "") {
		alert("��ԴΪ����ͨʱ��������ͨ������ݡ�������д~ "); byid("swt_id").focus(); return false;
	}
	return true;
}
function update_check_color(o) {
	o.parentNode.getElementsByTagName("label")[0].style.color = o.checked ? "blue" : "";
}
//20200713ԤԼ�����ظ����-yuanwu
function check_name_repeat() {
	var form_id = "name";
	var res_id = "name_tips";
	var search_type = "name";
	check_repeat(form_id, res_id, search_type);
}

function check_qq_repeat() {
	var form_id = "qq";
	var res_id = "qq_tips";
	var search_type = "qq";
	check_repeat(form_id, res_id, search_type);
}

function check_wx_repeat() {
	var form_id = "weixin";
	var res_id = "weixin_tips";
	var search_type = "weixin";
	check_repeat(form_id, res_id, search_type);
}

function check_tel_repeat() {
	var form_id = "mobile";
	var res_id = "tel_tips";
	var search_type = "mobile";
	check_repeat(form_id, res_id, search_type);
}

function check_repeat(form_id, res_id, search_type) {
	byid(res_id).innerHTML = "";
	var cur_value = byid(form_id).value;
	if (cur_value != "") {
		load_js("?op=check_repeat&search_type="+search_type+"&js_value="+encodeURIComponent(cur_value)+"&res_id="+res_id, "js");
	}
}


function update_disease(obj) {
	var cur_hid = obj.value;
	byid("disease_select").innerHTML = '(������...)';
	load_js("?op=get_disease&hid="+cur_hid, "get_disease");
}

function update_disease_do(arr) {
	var s = '<select name="disease_id" class="combo" style="width:200px">';
	for (var id in arr) {
		name = arr[id];
		s += '<option value="'+id+'">'+name+'</option>';
	}
	s += '</select>';
	byid("disease_select").innerHTML = s;
}
</script>

</head>

<body>

<form name="mainform" action="" method="POST" onsubmit="return check_submit(this)">
<table width="100%" class="edit" style="border:0; margin-top:10px;">
	<tr>
		<td class="left" style="width:20%;"><font color="red">*</font> ����ҽԺ��</td>
		<td class="right">
			<select name="hid" id="hid_select" class="combo" style="width:200px" onchange="update_disease(this); load_kefu(this);">
				<option value="" style="color:gray">-��ѡ������ҽԺ-</option>
<?php
	$h_id_name = $db->query("select id,name from hospital where ishide=0 and id in (".implode(",", $hospital_ids).") order by name asc", "id", "name");
	echo list_option($h_id_name, "_key_", "_value_", $hid);
?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left">������</td>
		<td class="right">
			<input name="name" id="name" value="<?php echo $line["name"]; ?>" class="input" size="20" style="width:200px" onchange="check_name_repeat();" onblur="check_name_repeat();"><span id="name_tips"></span>��(�����������Զ�����Ƿ��ظ�)
		</td>
	</tr>
	
	<tr>
		<td class="left"></td>
		<td class="right">
			<select name="sex" class="combo" style="width:60px; margin-left:20px; ">
				<option value="" style="color:gray">�Ա�</option>
				<?php echo list_option(array("��", "Ů"), "_value_", "_value_"); ?>
			</select>
			<span style="margin-left:20px; ">���䣺<input name="age" value="<?php echo $line["age"]; ?>" class="input" style="width:60px"> ��</span>
			<span style="margin-left:20px; "><font color="red">*</font> ���ڵأ�</span><input id="area" name="area" value="" class="input" style="width:70px">&nbsp;
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
		<td class="left">�ֻ��ţ�</td>
		<td class="right">
			<input name="mobile" id="mobile" value="<?php echo $line["mobile"]; ?>" class="input" size="20" style="width:200px" onchange="check_tel_repeat();" onblur="check_tel_repeat();"><span id="tel_tips"></span>��(�����ֻ��Ž��Զ�����Ƿ��ظ�)
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> ��ѯ���ݣ�</td>
		<td class="right">
			<textarea name="zx_content" style="width:80%; height:60px; vertical-align:middle;" class="input" onkeyup="byid('zishu').innerHTML = ' '+this.value.length+'��';"></textarea> (����)<span id="zishu"></span>
		</td>
	</tr>
	<tr>
		<td class="left">������</td>
		<td class="right" id="disease_select"><select name="disease_id" class="combo" style="width:200px"><option value="" style="color:gray">����ѡ�����</option></select></td>
	</tr>
	<tr>
		<td class="left"><?php if ($is_talk_not_empty) { ?><font color="red">*</font><?php } ?> �����¼��</td>
		<td class="right">
			<textarea name="talk_content" style="width:80%; height:100px; vertical-align:middle;" class="input"></textarea> <?php echo $is_talk_not_empty ? '(������ѯ����)' : '(��ѡ)'; ?>
		</td>
	</tr>
	<tr>
		<td class="left">����QQ��</td>
		<td class="right"><input name="qq" id="qq" value="<?php echo $line["qq"]; ?>" class="input" size="20" style="width:200px" onchange="check_qq_repeat();" onblur="check_qq_repeat();"><span id="qq_tips"></span></td>
	</tr>
	<tr>
		<td class="left">�ҷ�QQ��</td>
		<td class="right"><input name="order_qq" value="<?php echo $line["order_qq"]; ?>" class="input" size="20" style="width:200px"> <span class="intro">�뻼�߹�ͨ���õ�QQ����</span></td>
	</tr>
	<tr>
		<td class="left">����΢�ţ�</td>
		<td class="right"><input name="weixin" id="weixin" value="<?php echo $line["weixin"]; ?>" class="input" size="20" style="width:200px" onchange="check_wx_repeat();" onblur="check_wx_repeat();"><span id="weixin_tips"></span> <span class="intro">��΢���ʺ�</span></td>
	</tr>
	<tr>
		<td class="left">�ҷ�΢�ţ�</td>
		<td class="right"><input name="order_weixin" value="<?php echo $line["order_weixin"]; ?>" class="input" size="20" style="width:200px"> <span class="intro">�뻼�߹�ͨ���õ�΢���ʺ�</span></td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> ������Դ��</td>
		<td class="right">
			<input name="laiyuan" id="laiyuan" value="<?php echo $line["laiyuan"]; ?>" class="input" style="width:100px">&nbsp;&nbsp;
			���ã�
			<a href="javascript:;" onclick="laiyuan_set(this);">����ͨ</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">����</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">΢��</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">�绰</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">ץȡ</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">����</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">�ֻ�</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">����</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">��ý��</a>

			<script type="text/javascript">
			function laiyuan_set(o) {
				byid("laiyuan").value = o.innerHTML;
			}
			</script>
		</td>
	</tr>

	<tr>
		<td class="left">����ͨ������ݣ�</td>
		<td class="right">
			<input name="swt_id" id="swt_id" value="" class="input" style="width:200px">
			<span class="intro">��������ͨ��ֱ�Ӹ���</span>
		</td>
	</tr>

	<tr>
		<td class="left">�������ڣ�</td>
		<td class="right">
			<input name="remind_date" value="" class="input" style="width:100px" id="remind_date"> <img src="image/calendar.gif" id="remind_date" onClick="picker({el:'remind_date',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="ѡ������">
			<span class="intro">���ûط��������ڣ�Ϊ��������</span>
		</td>
	</tr>
	<tr>
		<td class="left">΢�ŶԽӣ�</td>
		<td class="right">
			<input type="checkbox" name="to_weixin" value="1" id="to_weixin" onclick="show_hide_weixin()"><label for="to_weixin">�ύ��΢����</label>
			<span id="weixin_area" style="display:none; margin-left:20px; ">
				�Խ��ˣ�<span id="weixin_kefu_select"><select name="weixin_kefu_id" class="combo" style="width:200px"><option value="">����ѡ�����</option></select></span>
				<span>���Խ��������ѡ��������΢�����Ա�����Կ���</span>
			</span>
		</td>
	</tr>
</table>

<script type="text/javascript">
function show_hide_weixin() {
	var is_check = byid("to_weixin").checked ? 1 : 0;
	byid("weixin_area").style.display = is_check ? "" : "none";
}

function load_kefu(obj) {
	var cur_hid = obj.value;
	byid("weixin_kefu_select").innerHTML = '(������...)';
	load_js("ku_add.php?op=load_kefu&hid="+cur_hid, "load_kefu");
}

function load_kefu_do(arr) {
	var s = '<select name="weixin_kefu_id" class="combo" style="width:200px">';
	for (var id in arr) {
		name = arr[id];
		s += '<option value="'+id+'">'+name+'</option>';
	}
	s += '</select>';
	byid("weixin_kefu_select").innerHTML = s;
}
</script>

<div class="button_line">
	<input type="submit" class="submit" value="�ύ����">
</div>
</form>

<?php if ($hid > 0) { ?>
<script type="text/javascript">
update_disease(byid("hid_select"));
load_kefu(byid("hid_select"));
</script>
<?php } ?>

</body>
</html>