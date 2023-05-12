<?php
// --------------------------------------------------------
// - ����˵�� : index_module
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2013-8-10
// --------------------------------------------------------
require "lib/set_env.php";
$table = "index_module";

$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";

$to_hid = intval($_REQUEST["hid"]); //ȫ��orר��ģ����

if ($mode == "edit") {
	check_power("e", $pinfo, $pagepower) or exit("�Բ�����û���޸�Ȩ��...");
} else {
	check_power("i", $pinfo, $pagepower) or exit("�Բ�����û������Ȩ��...");
}

if ($_POST) {
	$r = array();
	$name = $r["name"] = trim($_POST["name"]);

	/*
	if ($mode == "add") {
		$repeat = $db->query("select * from $table where name='$name'", 1);
	} else {
		$repeat = $db->query("select * from $table where id!='$id' and name='$name'", 1);
	}
	if ($repeat["id"] > 0) {
		exit("�Բ������ύ�����ơ�{$name}����ϵͳ���еļ�¼���ظ����뷵�������޸ģ�");
	}
	*/


	$r["condition_code"] = trim($_POST["condition_code"]);

	$r["condition_show"] = trim($_POST["condition_show"]);

	$r["sort"] = $_POST["sort"];

	$r["bg_color"] = $_POST["bg_color"];


	if ($mode == "add") {
		$r["hospital_id"] = $to_hid;
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
		echo '<script> parent.update_content(); </script>';
		echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "�ύʧ�ܣ����Ժ����ԣ�";
	}
	exit;
}

if ($mode == "edit") {
	$line = $db->query("select * from $table where id='$id' limit 1", 1);
	if ($line["hospital_id"] > 0) {
		$to_hid = $line["hospital_id"];
	}
}
$title = ($mode == "edit") ? "�޸�ͳ������" : ($to_hid > 0 ? "���ר��ģ��" : "���ͳ������");



$come_status_array = array("1" => "�ѵ�", "0" => "δ��", "2"=>"δ��(����)", "-2" => "©��");

$part_id_name = array(
	"2" => "����",
	"3" => "�绰",
	"4" => "��ҽ",
	"13" => "��",
	"17" => "�Թ�",
);

$from_table_arr = array("ku_list" => "���Ͽ�", "mobile_catch" => "ץȡ");

$is_fuzhen_arr = array("1" => "����", "0" => "�Ǹ���");

$yibao_arr = array("1" => "ҽ��", "0" => "�Է�");

$suozaidi_arr = array("1" => "����", "2" => "���");

$qq_from_arr = $db->query("select id,name from qq_from order by sort desc, id asc", "", "name");

$qudao_arr = $db->query("select id, concat(main_id,':',name) as name from dict_qudao order by main_id asc, sort desc, id asc", "id", "name");

$media_from_arr = array("����", "�绰");
$media_from_2 = $db->query("select * from media where hospital_id=0 order by sort desc, id asc", "", "name");
foreach ($media_from_2 as $v) {
	if (!in_array($v, $media_from_arr)) {
		$media_from_arr[] = $v;
	}
}
if ($to_hid > 0) {
	$media_from_3 = $db->query("select * from media where hospital_id='$to_hid' order by sort desc, id asc", "", "name");
	foreach ($media_from_3 as $v) {
		if (!in_array($v, $media_from_arr)) {
			$media_from_arr[] = $v;
		}
	}
}

if ($to_hid > 0) {
	$disease_arr = $db->query("select id,name from disease where hospital_id='$to_hid' and isshow=1 order by sort desc,id asc", "id", "name");
	$depart_arr = $db->query("select id,name from depart where hospital_id='$to_hid'", "id", "name");
}

//$hid_sname = $db->query("select id,if (short_name!='', short_name, name) as name from hospital where ishide=0 order by name asc", "id", "name");

function _show_in_notin($name) {
	echo '<select name="'.$name.'" class="combo"><option value="1">����</option><option value="2">�ų�</option></select>&nbsp;';
}

?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function check_data(oForm) {
	if (oForm.name.value == "") {
		alert("�����롰�����������ơ���"); oForm.name.focus(); return false;
	}
	condition_to_form();
	return true;
}
</script>

<style type="text/css">
.condition_show_table {border:1px solid #ffa64d; width:500px; margin:5px auto; }
.condition_show_table .left {text-align:left; width:80%; border:1px solid #ffd0a2; padding:3px 5px; }
.condition_show_table .right {text-align:center; border:1px solid #ffd0a2; padding:3px 5px; }

.condition_edit {border:0; }
.condition_edit .left {text-align:right; width:120px; border-bottom:1px solid silver; padding:4px 6px 2px 3px; }
.condition_edit .right {text-align:left; border-bottom:1px solid silver; padding:3px; }

#hid_access_show {margin-top:5px; }

.d_item {color:green !important; font-family:΢���ź� !important; }
</style>

</head>

<body>
<div class="description">
	<div class="d_title">����˵����</div>
	<div class="d_item"> 1. ���������� ��<font color="red">����=[����]</font>�������ȡ���粿�����ݣ���<font color="red">����=[����,�绰]</font>�� ���ȡ������ѯ+�绰��ѯ�����ݡ�</div>
	<div class="d_item"> 2. һ���ڿ���ͬʱָ���������������硰<font color="red">����=[�绰] ���� ý����Դ=[����]</font>�������ȡͬʱ������Щ���������ݡ�</div>
	<div class="d_item"> 3. �������ö������������е����ݻ���ӡ�</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">������������</td>
	</tr>
	<tr>
		<td class="left">��ʾ���ƣ�</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <font color="gray">���Ʊ�����д</font></td>
	</tr>
	<tr>
		<td class="left">ͳ�ƹ�ʽ��</td>
		<td class="right">
			<div>
				<button onclick="add_condition();return false;" class="buttonb">�������</button>
			</div>
			<div id="condition_show_area"></div>
			<div id="condition_code_area" style="clear:both; color:silver;"></div>
			<input type="hidden" name="condition_show" id="condition_show" value="<?php echo $line["condition_show"]; ?>">
			<input type="hidden" name="condition_code" id="condition_code" value='<?php echo $line["condition_code"]; ?>'>
		</td>
	</tr>
	<tr>
		<td class="left">���ȶȣ�</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" size="10" style="width:200px"> <font color="gray">Խ��Խ���ȣ���Ϊ����������ĩβ</font></td>
	</tr>
	<tr>
		<td class="left">����ɫ��</td>
		<td class="right"><input name="bg_color" value="<?php echo $line["bg_color"]; ?>" class="input" size="10" style="width:200px"> <font color="gray">��ɫֵ����#ade0ba ����ΪĬ����ɫ</font></td>
	</tr>
</table>

<?php if ($mode == "edit") { ?>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<?php } ?>
<input type="hidden" name="hid" value="<?php echo $to_hid; ?>">

<div class="button_line"><input type="submit" class="submit" value="�ύ����"></div>
</form>


<!-- �������� -->
<div id="dl_layer_div" onclick="condition_cancel();" style="position:absolute; filter:Alpha(opacity=85); display:none; background:#e1e1e1; z-index:999998; opacity:0.85;"></div>


<!-- �������ñ� -->
<form id="condition_edit_form" style="display:none; position:absolute; width:800px; height:480px; overflow:auto; left:40px; top:40px; border:2px solid #ffad5b; background:white; padding:10px; z-index:999999; ">
	<table width="100%" class="condition_edit" style="border:0;">
		<tr>
			<td class="left">���Ʋ��ţ�</td>
			<td class="right"><?php echo _show_in_notin("part_id[]"); ?><?php echo show_check("part_id[]", $part_id_name, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">����ԤԼ������</td>
			<td class="right"><?php echo _show_in_notin("order_type[]"); ?><?php echo show_check("order_type[]", $order_type_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">����ԤԼ�����</td>
			<td class="right"><?php echo _show_in_notin("order_soft[]"); ?><?php echo show_check("order_soft[]", $web_soft_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">�����Ż��飺</td>
			<td class="right"><?php echo _show_in_notin("youhuazu[]"); ?><?php echo show_check("youhuazu[]", $youhua_group_arr, "v", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">����ý����Դ��</td>
			<td class="right"><?php echo _show_in_notin("media_from[]"); ?><?php echo show_check("media_from[]", $media_from_arr, "v", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">����QQ��Դ��</td>
			<td class="right"><?php echo _show_in_notin("qq_from[]"); ?><?php echo show_check("qq_from[]", $qq_from_arr, "v", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">�����˻���</td>
			<td class="right"><?php echo _show_in_notin("account[]"); ?><?php echo show_check("account[]", $account_array, "v", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">���Ƶ�Ժ״̬��</td>
			<td class="right"><?php echo _show_in_notin("status[]"); ?><?php echo show_check("status[]", $come_status_array, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">���ƹ켣��</td>
			<td class="right"><?php echo _show_in_notin("guiji[]"); ?><?php echo show_check("guiji[]", $guiji_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">����������</td>
			<td class="right"><?php echo _show_in_notin("qudao[]"); ?><?php echo show_check("qudao[]", $qudao_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">�����г���Դ��</td>
			<td class="right"><?php echo _show_in_notin("shichang[]"); ?><?php echo show_check("shichang[]", $shichang_arr, "v", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">����ҽ����</td>
			<td class="right"><?php echo _show_in_notin("is_yibao[]"); ?><?php echo show_check("is_yibao[]", $yibao_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">������ѯ���飺</td>
			<td class="right"><?php echo _show_in_notin("zx_group[]"); ?><?php echo show_check("zx_group[]", $zx_group_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">����������Դ��</td>
			<td class="right"><?php echo _show_in_notin("from_table[]"); ?><?php echo show_check("from_table[]", $from_table_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">�����־�ס�أ�</td>
			<td class="right"><?php echo _show_in_notin("suozaidi[]"); ?><?php echo show_check("suozaidi[]", $suozaidi_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
		<tr>
			<td class="left">�����Ƿ��</td>
			<td class="right"><?php echo _show_in_notin("is_fuzhen[]"); ?><?php echo show_check("is_fuzhen[]", $is_fuzhen_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>

<?php if ($to_hid > 0) { ?>

<?php if (count($disease_arr) > 0) { ?>
		<tr>
			<td class="left">���Ƽ�����</td>
			<td class="right"><?php echo _show_in_notin("disease_id[]"); ?><?php echo show_check("disease_id[]", $disease_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
<?php } ?>

<?php if (count($depart_arr) > 0) { ?>
		<tr>
			<td class="left">���ƿ��ң�</td>
			<td class="right"><?php echo _show_in_notin("depart[]"); ?><?php echo show_check("depart[]", $depart_arr, "k", "v", array(), " ", ""); ?></td>
		</tr>
<?php } ?>

<?php } ?>
	</table>
	<div class="button_line">
		<button class="buttonb" onclick="save_edit_condition(); return false;">ȷ��</button>&nbsp;
		<button class="buttonb" onclick="condition_cancel(); return false;">ȡ��</button>
	</div>
</form>
<input type="hidden" id="op_mode" value="">
<input type="hidden" id="edit_id" value="">

<script type="text/javascript">
Array.prototype.remove = function(index) {
	if (isNaN(index) || index > this.length) return false;
	for (var i=0,n=0; i<this.length; i++) {
		if (this[i] != this[index]) this[n++] = this[i];
	}
	this.length -= 1;
}

String.prototype.trim = function() {
	return this.replace(/(^\s*)|(\s*$)/g, "");
}

String.prototype.replaceAll = function(reallyDo, replaceWith, ignoreCase) {
    if (!RegExp.prototype.isPrototypeOf(reallyDo)) {
		return this.replace(new RegExp(reallyDo, (ignoreCase ? "gi": "g")), replaceWith);
	} else {
		return this.replace(reallyDo, replaceWith);
	}
}
</script>

<script type="text/javascript">
window.condition_show_arr = new Array();
window.condition_code_arr = new Array();

function condition_to_form() {
	byid("condition_show").value = condition_show_arr.join("<br>");
	byid("condition_code").value = condition_code_arr.join(" or ");
	byid("condition_code_area").innerHTML = byid("condition_code").value;
}

if (byid("condition_show").value != '') {
	condition_show_arr = byid("condition_show").value.split("<br>");
	condition_code_arr = byid("condition_code").value.split(" or ");
	build_condition_table();
}

function add_condition() {
	byid("op_mode").value = "add";
	byid("edit_id").value = "";
	show_hide_bg(1);
	byid("condition_edit_form").style.display = "block";
}

function save_edit_condition() {
	var op_mode = byid("op_mode").value;
	var edit_id = byid("edit_id").value;

	var f = byid("condition_edit_form");
	var objs = f.getElementsByTagName("TR");
	var full_code = '';
	var full_show = '';
	for (var i=0; i<objs.length; i++) {
		var tds = objs[i].getElementsByTagName("TD");
		var show_name = tds[0].innerHTML.split("����")[1].split("��")[0];
		var inputs = tds[1].getElementsByTagName("INPUT");
		var code_name = inputs[0].name.split("[")[0];
		var code = '';
		var show = '';
		for (var j=0; j<inputs.length; j++) {
			if (inputs[j].checked) {
				code += (code ? ',' : '')+'"'+inputs[j].value+'"';
				show += (show ? ',' : '')+inputs[j].nextSibling.innerHTML;
			}
		}
		if (code) {
			var in_notin = tds[1].getElementsByTagName("SELECT")[0].value;
			var in_or_notin = (in_notin == "1" ? " in " : " not in ");
			var in_or_notin_show = (in_notin == "1" ? "=" : "��");
			code = code_name+in_or_notin+"("+code+")";
			full_code += (full_code ? " and " : "") + code;
			show = show_name+in_or_notin_show+"["+show+"]";
			full_show += (full_show ? " ���� " : "")+show;
		}
	}
	if (full_code.split(" and ").length > 1) {
		full_code = "("+full_code+")";
	}
	if (full_code != '') {
		if (op_mode == "add") {
			condition_show_arr[condition_show_arr.length] = full_show;
			condition_code_arr[condition_code_arr.length] = full_code;
		} else {
			var id = parseInt(edit_id);
			condition_show_arr[id] = full_show;
			condition_code_arr[id] = full_code;
		}
		build_condition_table();
		reset_condition_form();
		f.style.display = "none";
		show_hide_bg(0);
	} else {
		alert("��û�й�ѡ�κ����������������á�");
		return false;
	}
}

function build_condition_table() {
	if (condition_show_arr.length > 0) {
		var html = '<table id="condition_show_table" class="condition_show_table" align="left">';
		for (var i=0; i<condition_show_arr.length; i++) {
			html += '<tr><td class="left">'+condition_show_arr[i]+'</td>';
			html += '<td class="right"><a href="javascript:;" onclick="edit_sub_condition('+i+')">�޸�</a>&nbsp;';
			html += '<a href="javascript:;" onclick="delete_sub_condition('+i+')">ɾ��</a></td></tr>';
		}
		html += '</table>';
		byid("condition_show_area").innerHTML = html;
	} else {
		byid("condition_show_area").innerHTML = "";
	}
	condition_to_form();
}

function edit_sub_condition(index) {
	var name = condition_show_arr[index];
	var code = condition_code_arr[index];
	condition_set_check(code);
	byid("op_mode").value = "edit";
	byid("edit_id").value = index;
	show_hide_bg(1);
	byid("condition_edit_form").style.display = "block";
}

function delete_sub_condition(index) {
	if (confirm("ȷ��Ҫɾ������������")) {
		condition_show_arr.remove(index);
		condition_code_arr.remove(index);
		build_condition_table();
	}
}

function condition_cancel() {
	var f = byid("condition_edit_form");
	reset_condition_form();
	f.style.display = "none";
	show_hide_bg(0);
}

function condition_set_check(code) {
	code = code.trim();
	if (code.split(" and ").length > 1) {
		code = code.substring(1, code.length-1);
	}
	var arr = code.split(" and ");
	for (var i=0; i<arr.length; i++) {
		if (arr[i].split(" not in ").length > 1) {
			var s_arr = arr[i].split(" not in ");
			var name = s_arr[0]+"[]";
			wee_set_in_notin(name, "2");
		} else {
			var s_arr = arr[i].split(" in ");
			var name = s_arr[0]+"[]";
		}
		var item = s_arr[1].replaceAll('"', '');
		var check_arr = item.substring(1, item.length-1).split(",");
		for (var j=0; j<check_arr.length; j++) {
			wee_set_check(name, check_arr[j]);
		}
	}
}

function wee_set_in_notin(name, value) {
	var objs = byid("condition_edit_form").getElementsByTagName("SELECT");
	for (var i=0; i<objs.length; i++) {
		if (objs[i].name == name) {
			objs[i].value = value;
			break;
		}
	}
}

function wee_set_check(name, value) {
	var objs = byid("condition_edit_form").getElementsByTagName("INPUT");
	for (var i=0; i<objs.length; i++) {
		if (objs[i].name == name && objs[i].value == value) {
			objs[i].checked = true;
			objs[i].nextSibling.style.color = "red";
			break;
		}
	}
}

function reset_condition_form() {
	var f = byid("condition_edit_form");
	f.reset();
	var arr = f.getElementsByTagName("LABEL");
	for (var i=0; i<arr.length; i++) {
		arr[i].style.color = "";
	}
}

function show_hide_bg(isshow) {
	var o = byid("dl_layer_div");

	var wsize = get_size();
	var width = wsize[0];
	var height = wsize[1];

	if (isshow) {
		o.style.top = o.style.left = "0px";
		o.style.width = width+"px";
		o.style.height = height+"px";
		o.style.display = "block";
	} else {
		o.style.display = "none";
	}
}
</script>


</body>
</html>