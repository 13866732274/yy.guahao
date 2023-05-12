<?php
// --------------------------------------------------------
// - 功能说明 : admin.php
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-05-15 00:51
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_admin";

check_power('', $pinfo) or exit("没有打开权限...");

$super_edit = 0;
if ($debug_mode || substr_count($sys_super_admin, $realname) > 0) {
	$super_edit = 1;
}

// 操作的处理:
$op = $_REQUEST["op"];
if ($op) {
	include "sys_admin.op.php";
}

$search = array();

if ($_GET["guo_month"] > 0) {
	$_t = strtotime("-".intval($_GET["guo_month"])." month");
	$search[] = "(thislogin<".$_t." and addtime<".$_t.")";
}

if ($_GET["show_dp_empty"] > 0) {
	$search[] = "hospitals=''";
}

if ($_GET["show_not_login"] > 0) {
	$search[] = "logintimes=0";
}

$_hid = intval($_GET["hospital_id"]);
if ($_hid > 0) { //单独查看某家医院模式
	//  医院
	if (in_array($_hid, $hospital_ids)) {
		$search[] = "concat(',',hospitals,',') like '%,{$_hid},%'";
	} else {
		exit("对不起，您没有查看这个医院的权限...");
	}

} else { //查看最大权限

	// 人员读取条件:
	if (!$super_edit) {
		$h_search = array();
		foreach ($hospital_ids as $v) {
			$h_search[] = "concat(',',hospitals,',') like '%,{$v},%'";
		}
		$search[] = "(".implode(" or ", $h_search).")";
	}

}

// 搜索:
if ($key = $_GET["key"]) {
	$_do = array();
	$key = str_replace("、", " ", $key);
	$key = str_replace("，", " ", $key);
	$key = str_replace(",", " ", $key);
	$_arr = explode(" ", $key);
	foreach ($_arr as $v) {
		$v = trim($v);
		if ($v != '') {
			$_do[] = "(name like '%{$v}%' or realname like '%{$v}%' or ukey_sn like '%{$v}%')";
		}
	}
	$search[] = "(".implode(" or ", $_do).")";
}


$group_type = array(1 => "部门", 2 => "权限", 4 => "部门主管", 5 => "禁用的账号", 6 => "在线用户", 7 => "uKey用户");
$cur_group = intval($_SESSION["admin_group_type"]);
if (!$cur_group) {
	$cur_group = $_SESSION["admin_group_type"] = 2;
}



// 汇总数据
$users_count = $db->query("select count(*) as c from sys_admin", 1, "c"); //总人数
$users_count_close = $db->query("select count(*) as c from sys_admin where isshow=0", 1, "c"); //总关闭人数
$users_count_open = intval($users_count - $users_count_close); //总开通人数
$users_online = $db->query("select count(*) as c from sys_admin where online=1", 1, "c"); //在线
$users_ukey = $db->query("select count(*) as c from sys_admin where isshow=1 and ukey_sn!=''", 1, "c"); //ukey


$allow_ids = implode(",", $hospital_ids);

//$hid_arr = $db->query("select id,name from hospital where id in ($allow_ids) and ishide=0 order by name asc", "id", "name");


// 按分组进行整理
$options = array();
$hids = implode(",", $hospital_ids);
$h_list = $db->query("select id,name,if(group_name='','其他',group_name) as group_name,color from hospital where ishide=0 and id in ($hids) order by name", "id");
$group = array();
$group_hid = array();
foreach ($h_list as $_hid => $v) {
	$group[$v["group_name"]] = intval($group[$v["group_name"]]) + 1;
	$group_hid[$v["group_name"]][] = $_hid;
}
arsort($group);
foreach ($group as $_sname => $count) {
	$options[] = array('-1', $_sname." (".$count.')', 'color:red' );
	foreach ($group_hid[$_sname] as $_hid) {
		$options[] = array($_hid, '　'.$h_list[$_hid]["name"], ($h_list[$_hid]["color"] ? ('color:'.$h_list[$_hid]["color"]) : 'color:blue') );
	}
}


function show_name($arr) {
	if ($arr["realname"] == $arr["name"]) {
		$s = $arr["realname"];
	} else {
		$s = $arr["realname"]."(".$arr["name"].")";
	}
	if (strlen($s) > 20) {
		$s = cut($s, 20, "…");
	}
	if ($arr["online"] > 0) {
		$s = '<font color="red">'.$s.'</font>';
	}
	if (isset($arr["isshow"])) {
		if ($arr["isshow"] <= 0) {
			$s .= ' <font color="red">禁</font>';
		}
	}
	return $s;
}

// ------------- 页面开始 ---------------
?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.admin_list {margin-left:10px; margin-top:10px; }
#rec_part, #rec_user {margin-top:6px; }
.rub {width:180px; float:left; }
.rub input {float:left; }
.rub a {display:block; float:left; padding-top:2px; }
.rgp {clear:both; margin:12px 0 5px 0; font-weight:bold; font-family:"微软雅黑"; font-size:13px; color:#4b4b4b; }
.group_select {margin-top:10px; margin-bottom:0px; text-align:center; }
.adms {padding:0 20px 4px 40px; }
</style>

<script language="javascript">
function ucc(o) {
	o.parentNode.getElementsByTagName("a")[0].style.color = o.checked ? "red" : "";
}
function sd(id) {
	var ss = byid("g_"+id).getElementsByTagName("INPUT");
	for (var i=0; i<ss.length; i++) {
		ss[i].checked = !ss[i].checked;
	}
	return false;
}

function add() {
	parent.load_src(1,'sys_admin_edit.php');
	return false;
}

function ld(id) {
	parent.load_src(1,'sys_admin_edit.php?id='+id);
	return false;
}

function get_current_select_uids() {
	var g = document.getElementsByTagName("INPUT");
	var s = '';
	for (var i=0; i<g.length; i++) {
		if (g[i].type == "checkbox" && g[i].name == "uid[]" && g[i].checked == true) {
			s += (s != '' ? "," : "") + g[i].value;
		}
	}
	return s;
}

function del() {
	var uids = get_current_select_uids();
	if (uids == '') {
		alert("请先选中要操作的人员~ ");
		return false;
	}
	var sel_count = uids.split(",").length;
	if (confirm("真的确定要删除选中的"+sel_count+"人吗？请务必谨慎啊。。")) {
		load_js("?op=delete&uids="+uids, "op");
	}
}

function close_account() {
	var uids = get_current_select_uids();
	if (uids == '') {
		alert("请先选中要操作的人员~ ");
		return false;
	}
	var sel_count = uids.split(",").length;
	if (confirm("已选中"+sel_count+"个账号，是否确定要关闭这些账号？")) {
		load_js("?op=close&uids="+uids, "op");
	}
}

function open_account() {
	var uids = get_current_select_uids();
	if (uids == '') {
		alert("请先选中要操作的人员~ ");
		return false;
	}
	var sel_count = uids.split(",").length;
	if (confirm("已选中"+sel_count+"个账号，是否确定要开启这些账号？")) {
		load_js("?op=open&uids="+uids, "op");
	}
}
</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" width="30%"><nobr class="tips">系统人员管理</nobr></td>
		<td class="header_center" width="40%">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">添加</button>&nbsp;&nbsp;
<?php } ?>
		</td>
		<td class="headers_oprate"><button onclick="self.location.reload();return false;" class="button" title="刷新本页面">刷新</button></td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>
<div class="group_select">
	<b>排列方式：</b>
	<form method="GET" style="display:inline;">
		<select name="group" class="combo" onchange="this.form.submit()">
			<?php echo list_option($group_type, "_key_", "_value_", $cur_group); ?>
		</select>
		<input type="hidden" name="op" value="change_group_type">
		<input type="hidden" name="key" value="<?php echo $_GET["key"]; ?>">
		<input type="hidden" name="hospital_id" value="<?php echo $_GET["hospital_id"]; ?>" />
	</form>&nbsp;

	<b>医院筛选：</b>
	<form method="GET" style="display:inline;">
		<select name="hospital_id" class="combo" onchange="if (this.value!='-1') this.form.submit()">
			<option value="" style="color:gray">-所有医院(科室)-</option>
<?php
foreach ($options as $v) {
	echo '  <option value="'.$v[0].'"'.($v[0] == $_GET["hospital_id"] ? ' selected' : '').($v[2] ? ' style="'.$v[2].'"' : '').'>'.$v[1].($v[0] == $_GET["hospital_id"] ? ' *' : '').'</option>'."\r\n";
}
?>
		</select>
		<input type="hidden" name="key" value="<?php echo $_GET["key"]; ?>" class="input" size="12">
	</form>&nbsp;

	<b>搜索名字：</b>
	<form method="GET" style="display:inline;">
		<input name="key" value="<?php echo $_GET["key"]; ?>" class="input" style="width:200px;">
		<input type="submit" class="button" value="搜索" style="font-weight:bold;">
		<input type="submit" class="button" onclick="this.form.key.value=''" value="重置">
		<input type="hidden" name="hospital_id" value="<?php echo $_GET["hospital_id"]; ?>" />
	</form>
</div>

<div style="padding:10px; padding-top:20px; text-align:center; "><b>人数统计数据</b> 总人数：<b style="color:blue"><?php echo $users_count; ?></b>　开通帐号：<b style="color:blue"><?php echo $users_count_open; ?></b>　关闭帐号：<b style="color:blue"><?php echo $users_count_close; ?></b>　总在线：<b style="color:blue"><?php echo $users_online; ?></b>　使用uKey用户数：<b style="color:blue"><?php echo $users_ukey; ?></b></div>

<?php if ($debug_mode || $uinfo["part_id"] == 9) { ?>
<div style="padding:10px; text-align:center; ">
	筛选过期帐号：<a href="?guo_month=1">一个月</a> &nbsp; <a href="?guo_month=3">三个月</a> &nbsp; <a href="?guo_month=6">半年</a> &nbsp; <a href="?guo_month=12">一年</a>
	<a href="?show_dp_empty=1" style="margin-left:40px;">查看无科室账号</a> <a href="?show_not_login=1" style="margin-left:20px;">未登陆过的账号</a>
</div>
<?php } ?>

<form method="POST" name="mainform" id="mainform" action="?">
<div class="admin_list">
	<div id="rec_user">
<?php
$fs = "id,name,realname,online";

if ($cur_group == 1) { //部门
	$id_name = $db->query("select id,name from sys_part order by sort desc, id asc", "id", "name");
	$f = "part_id";
	$search[] = "isshow=1";
} else if ($cur_group == 2) { //角色
	$id_name = $db->query("select id,name from sys_character order by sort desc, id asc", "id", "name");
	$f = "character_id";
	$search[] = "isshow=1";
} else if ($cur_group == 4) { //主管
	$id_name = array(1 => "部门主管");
	$f = "part_admin";
	$search[] = "isshow=1";
} else if ($cur_group == 5) {
	$id_name = array(0 => "禁用的账号");
	$f = "isshow";
	$fs = "id,name,realname,online,isshow";
} else if ($cur_group == 6) { //在线
	$id_name = array(1 => "在线");
	$f = "online";
	$search[] = "isshow=1";
} else if ($cur_group == 7) { //ukey
	$id_name = array(1 => "已使用uKey", 0 => "未使用uKey");
	$f = "use_ukey";
	$search[] = "isshow=1";
}

$sqlwhere = count($search) ? implode(" and ", $search) : "1";

foreach ($id_name as $k => $v) {
	$all_admin = $db->query("select $fs from sys_admin where $sqlwhere and id!='$uid' and $f='$k' order by realname", "id");
	$main_sql = $db->sql;
	if (count($all_admin) > 0) {
		echo gt(2).'<div class="rgp">'.$v.'('.count($all_admin).')'.' <a href="#" onclick="sd('.$k.');return false;">全选</a></div>'.gn();
		echo gt(2).'<div class="adms" id="g_'.$k.'">'.gn();
		foreach ($all_admin as $a => $b) {
			echo gt(3).'<div class="rub"><input type="checkbox" name="uid[]" value="'.$a.'" onclick="ucc(this)"><a href="#" onclick="return ld('.$b["id"].')">'.show_name($b).'</a></div>'.gn();
		}
		echo gt(2).'<div class="clear"></div></div>'.gn();
	}
}

?>
		<div class="clear"></div>
	</div>
</div>
<input type="hidden" name="op" id="op_value" value="">

<center style="margin-left:12px; margin-top:50px;">

	<button onclick="select_all();return false;" class="button">全选</button>&nbsp;
	<button onclick="unselect();return false;" class="button">反选</button>&nbsp;

	<b>　　所选人员：</b>
<?php if ($super_edit) { ?>
	<button onclick="del();return false;" class="button">删除</button>&nbsp;
<?php } ?>


<?php if ($super_edit || check_power("h", $pinfo, $pagepower)) { ?>
	<button onclick="close_account();return false;" class="buttonb">关闭帐户</button>&nbsp;
	<button onclick="open_account();return false;" class="buttonb">开通帐户</button>&nbsp;
<?php } ?>


<?php if ($super_edit) { ?>
	<button onclick="piliang_add_hospital(); return false;" class="buttonb">增加科室</button>&nbsp;
	<script type="text/javascript">
	function piliang_add_hospital() {
		var uids = get_current_select_uids();
		if (uids == '') {
			alert("对不起，您还没有选中任何帐号。　　　　");
			return false;
		}
		parent.load_src(1, 'sys_admin_hospital.php?uids='+uids, 900, 500);
	}
	</script>

	<button onclick="piliang_quanxian(); return false;" class="buttonb">批量权限</button>
	<script type="text/javascript">
	function piliang_quanxian() {
		var uids = get_current_select_uids();
		if (uids == '') {
			alert("对不起，您还没有选中任何帐号。　　　　");
			return false;
		}
		parent.load_src(1, 'sys_admin_piliang.php?uids='+uids, 900, 500);
	}
	</script>
<?php } ?>

</center>

</form>

<br>
<br>

</body>
</html>