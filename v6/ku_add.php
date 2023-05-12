<?php
// --------------------------------------------------------
// - 功能说明 : 病人资料库
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2013-7-11
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";

$is_talk_not_empty = $uinfo["part_id"] == 2 ? 1 : 0; //聊天内容是否必填

if ($op == "check_repeat") {
	header('Content-Type: application/x-javascript;');
	$allow_field = explode(" ", "name mobile qq weixin");
	$search_field = trim($_GET["search_type"]);
	if (!in_array($search_field, $allow_field)) exit("参数错误");
	$value = trim(wee_safe_key(mb_convert_encoding($_GET["js_value"], "gbk", "UTF-8")));
	$value = str_replace("　", "", $value);

	$line = $db->query("select * from ku_list where $search_field='$value' order by id desc limit 1", 1);
	$tips = $alert = '';
	if ($line["id"] > 0) {
		$alert = '['.$value.'] 已经被 ['.$line["u_name"]."] 于 ".date("Y-m-d H:i", $line["addtime"])." 添加到 [".$line["h_name"]."]，请酌情考虑是否继续添加！";
		$tips = "　<font color=red>重复</font>　";
	} else {
		$tips = "　有效　";
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
		$arr = $db->query("select id, concat(realname,if(online>0,' [在线]','')) as realname from sys_admin where part_id=$part_id and concat(',',hospitals,',') like '%,{$hid},%' and character_id in (50,51,52) order by realname asc", "id", "realname");
		if (count($arr) == 0) {
			$arr[0] = "该科室暂无微信对接人";
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
		exit("所属医院必须选择！");
	}

	// 2014-3-11 新增
	$r["part_id"] = $uinfo["part_id"];
	$r["name"] = trim($_POST["name"]);
	$r["sex"] = trim($_POST["sex"]);
	$r["age"] = trim($_POST["age"]);
	$r["mobile"] = str_replace("　", "", trim($_POST["mobile"]));
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
		if (substr($dname, 0, 2) != "【" && (substr_count($dname, "(") > 0 || substr_count($dname, "（") > 0 || substr_count($dname, "【") > 0)) {
			$dname = str_replace("（", "(", $dname);
			$dname = str_replace("【", "(", $dname);
			list($name2, $tmp) = explode("(", $dname, 2);
			if (trim($name2) != '') $dname = trim($name2);
		}
		$r["disease_name"] = $dname;
	}

	// 提交给微信组 @ 2016-10-20
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

		// 添加回访提醒：
		if (trim($_POST["remind_date"]) != '') {
			$r_date = date("Ymd", strtotime($_POST["remind_date"]));
			$time = time();
			$db->query("insert into ku_remind set remind_date='$r_date', ku_id=$ins_id, patient_name='".$_POST["name"]."', uid=$uid, u_name='$realname', addtime=$time");
		}

		echo '<script> parent.update_content(); </script>';
		echo '<script> parent.msg_box("资料提交成功", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "提交失败，请稍后再试！";
	}
	exit;
}

$title = "新增";
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
		alert("请选择患者所属医院！"); o.hid.focus(); return false;
	}
	if (o.area.value.trim() == "") {
		alert("【所在地】为必填项，请填写完整再提交。"); o.area.focus(); return false;
	}
	if (o.zx_content.value.trim() == "") {
		alert("【咨询内容】为必填项，请勿偷懒 ~ "); o.zx_content.focus(); return false;
	}
	if (o.zx_content.value.length > 200) {
		alert("【咨询内容】字数太多，将会影响页面显示效果，请控制到200个汉字以内（此处不要粘贴聊天记录）当前字数："+o.zx_content.value.length); o.zx_content.focus(); return false;
	}
<?php if ($is_talk_not_empty) { ?>
	if (o.talk_content.value == "") {
		alert("【聊天记录】为必填项~ "); o.talk_content.focus(); return false;
	}
<?php } ?>
	if (o.qq.value != '' && o.order_qq.value == "") {
		alert("当填写患者QQ时，【我方QQ】也必须填写。"); return false;
	}
	if (o.weixin.value != '' && o.order_weixin.value == "") {
		alert("当填写患者微信时，【我方微信】也必须填写。"); return false;
	}
	if (o.laiyuan.value.trim() == "") {
		alert("【资料来源】必须填写~ "); o.laiyuan.focus(); return false;
	}
	if (o.mobile.value.trim() == '' && o.qq.value.trim() == '' && o.weixin.value.trim() == '') {
		alert("手机号、QQ号、微信号至少要填其中一种联系方式。"); return false;
	}
	if (o.laiyuan.value.trim() == "商务通" && byid("swt_id").value == "") {
		alert("来源为商务通时，【商务通永久身份】必须填写~ "); byid("swt_id").focus(); return false;
	}
	return true;
}
function update_check_color(o) {
	o.parentNode.getElementsByTagName("label")[0].style.color = o.checked ? "blue" : "";
}
//20200713预约姓名重复检测-yuanwu
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
	byid("disease_select").innerHTML = '(加载中...)';
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
		<td class="left" style="width:20%;"><font color="red">*</font> 所属医院：</td>
		<td class="right">
			<select name="hid" id="hid_select" class="combo" style="width:200px" onchange="update_disease(this); load_kefu(this);">
				<option value="" style="color:gray">-请选择所属医院-</option>
<?php
	$h_id_name = $db->query("select id,name from hospital where ishide=0 and id in (".implode(",", $hospital_ids).") order by name asc", "id", "name");
	echo list_option($h_id_name, "_key_", "_value_", $hid);
?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left">姓名：</td>
		<td class="right">
			<input name="name" id="name" value="<?php echo $line["name"]; ?>" class="input" size="20" style="width:200px" onchange="check_name_repeat();" onblur="check_name_repeat();"><span id="name_tips"></span>　(输入姓名将自动检查是否重复)
		</td>
	</tr>
	
	<tr>
		<td class="left"></td>
		<td class="right">
			<select name="sex" class="combo" style="width:60px; margin-left:20px; ">
				<option value="" style="color:gray">性别</option>
				<?php echo list_option(array("男", "女"), "_value_", "_value_"); ?>
			</select>
			<span style="margin-left:20px; ">年龄：<input name="age" value="<?php echo $line["age"]; ?>" class="input" style="width:60px"> 岁</span>
			<span style="margin-left:20px; "><font color="red">*</font> 所在地：</span><input id="area" name="area" value="" class="input" style="width:70px">&nbsp;
			<a href="javascript:;" onclick="area_set(this);">本地</a>
			<a href="javascript:;" onclick="area_set(this);">江浙</a>
			<a href="javascript:;" onclick="area_set(this);">其他</a>
			<select name="type" id="userType" onclick="select_area_set(this);">
<option value="贵阳">贵阳</option>
<option value="安顺">安顺</option>
<option value="毕节">毕节</option>
<option value="遵义">遵义</option>
<option value="六盘水">六盘水</option>
<option value="黔南">黔南</option>
<option value="黔西南">黔西南</option>
<option value="黔东南">黔东南</option>
<option value="铜仁">铜仁</option>
</select>
			<script type="text/javascript">
			function area_set(obj) {
				byid("area").value = obj.innerHTML;
			}
			function select_area_set(obj) {
				var myType = document.getElementById("userType");//获取select对象
var index = myType.selectedIndex; //获取选项中的索引，selectIndex表示的是当前所选中的index
byid("area").value = myType.options[index].value;//获取选项中options的value值
			}
			</script>
		</td>
	</tr>
	
			
	<tr>
		<td class="left">手机号：</td>
		<td class="right">
			<input name="mobile" id="mobile" value="<?php echo $line["mobile"]; ?>" class="input" size="20" style="width:200px" onchange="check_tel_repeat();" onblur="check_tel_repeat();"><span id="tel_tips"></span>　(输入手机号将自动检查是否重复)
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 咨询内容：</td>
		<td class="right">
			<textarea name="zx_content" style="width:80%; height:60px; vertical-align:middle;" class="input" onkeyup="byid('zishu').innerHTML = ' '+this.value.length+'字';"></textarea> (必填)<span id="zishu"></span>
		</td>
	</tr>
	<tr>
		<td class="left">疾病：</td>
		<td class="right" id="disease_select"><select name="disease_id" class="combo" style="width:200px"><option value="" style="color:gray">请先选择科室</option></select></td>
	</tr>
	<tr>
		<td class="left"><?php if ($is_talk_not_empty) { ?><font color="red">*</font><?php } ?> 聊天记录：</td>
		<td class="right">
			<textarea name="talk_content" style="width:80%; height:100px; vertical-align:middle;" class="input"></textarea> <?php echo $is_talk_not_empty ? '(网络咨询必填)' : '(可选)'; ?>
		</td>
	</tr>
	<tr>
		<td class="left">患者QQ：</td>
		<td class="right"><input name="qq" id="qq" value="<?php echo $line["qq"]; ?>" class="input" size="20" style="width:200px" onchange="check_qq_repeat();" onblur="check_qq_repeat();"><span id="qq_tips"></span></td>
	</tr>
	<tr>
		<td class="left">我方QQ：</td>
		<td class="right"><input name="order_qq" value="<?php echo $line["order_qq"]; ?>" class="input" size="20" style="width:200px"> <span class="intro">与患者沟通所用的QQ号码</span></td>
	</tr>
	<tr>
		<td class="left">患者微信：</td>
		<td class="right"><input name="weixin" id="weixin" value="<?php echo $line["weixin"]; ?>" class="input" size="20" style="width:200px" onchange="check_wx_repeat();" onblur="check_wx_repeat();"><span id="weixin_tips"></span> <span class="intro">填微信帐号</span></td>
	</tr>
	<tr>
		<td class="left">我方微信：</td>
		<td class="right"><input name="order_weixin" value="<?php echo $line["order_weixin"]; ?>" class="input" size="20" style="width:200px"> <span class="intro">与患者沟通所用的微信帐号</span></td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 资料来源：</td>
		<td class="right">
			<input name="laiyuan" id="laiyuan" value="<?php echo $line["laiyuan"]; ?>" class="input" style="width:100px">&nbsp;&nbsp;
			常用：
			<a href="javascript:;" onclick="laiyuan_set(this);">商务通</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">无线</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">微信</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">电话</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">抓取</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">电视</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">手机</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">短信</a>
			<a href="javascript:;" onclick="laiyuan_set(this);">新媒体</a>

			<script type="text/javascript">
			function laiyuan_set(o) {
				byid("laiyuan").value = o.innerHTML;
			}
			</script>
		</td>
	</tr>

	<tr>
		<td class="left">商务通永久身份：</td>
		<td class="right">
			<input name="swt_id" id="swt_id" value="" class="input" style="width:200px">
			<span class="intro">请在商务通中直接复制</span>
		</td>
	</tr>

	<tr>
		<td class="left">提醒日期：</td>
		<td class="right">
			<input name="remind_date" value="" class="input" style="width:100px" id="remind_date"> <img src="image/calendar.gif" id="remind_date" onClick="picker({el:'remind_date',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择日期">
			<span class="intro">设置回访提醒日期，为空则不提醒</span>
		</td>
	</tr>
	<tr>
		<td class="left">微信对接：</td>
		<td class="right">
			<input type="checkbox" name="to_weixin" value="1" id="to_weixin" onclick="show_hide_weixin()"><label for="to_weixin">提交给微信组</label>
			<span id="weixin_area" style="display:none; margin-left:20px; ">
				对接人：<span id="weixin_kefu_select"><select name="weixin_kefu_id" class="combo" style="width:200px"><option value="">请先选择科室</option></select></span>
				<span>　对接人如果不选，则整个微信组成员都可以看到</span>
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
	byid("weixin_kefu_select").innerHTML = '(加载中...)';
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
	<input type="submit" class="submit" value="提交资料">
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