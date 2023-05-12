<?php
// --------------------------------------------------------
// - 功能说明 : 新增、修改病人资料
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-01 08:57
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$hid;

// 对内容进行关键词过滤 @ 2014-8-23
$hid_filter_arr = array(
	1 => "",
);

if ($hid == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}
$h_name = $db->query("select id,name from hospital where id=$hid limit 1", 1, "name");

if (!in_array("patient_edit", $gGuaHaoConfig)) {
	exit_html("对不起，您没有修改权限!");
}

$id = intval($_REQUEST["id"]);
if (empty($id)) {
	exit_html("参数错误。");
}

$line = $db->query("select * from $table where id=$id limit 1", 1);
if (!is_array($line) || $line["id"] != $id) {
	exit_html("无此ID: ".$id);
}


// 查询该病人下次回访信息：
$remind_arr = $db->query("select * from patient_remind where hid=$hid and patient_id=$id and uid=$uid", 1);

$t = $remind_arr["remind_date"];
if (strlen($t) == 8) {
	$huifang_time = substr($t, 0, 4)."-".substr($t, 4, 2)."-".substr($t, 6, 2);
} else {
	$huifang_time = '';
}

if ($_POST) {
	$r = array();

	// 下次回访时间:
	$remind_date = $_POST["huifang_nexttime"] ? intval(str_replace("-", "", $_POST["huifang_nexttime"])) : 0;
	if ($remind_date != $remind_arr["remind_date"]) {
		if (is_array($remind_arr) && $remind_arr["id"]) {
			$remind_id = $remind_arr["id"];
			if ($remind_date > 0) {
				$db->query("update patient_remind set remind_date='$remind_date' where id=$remind_id limit 1");
			} else {
				$db->query("delete from patient_remind where id=$remind_id limit 1");
			}
		} else {
			if ($remind_date > 0) {
				$time = time();
				$db->query("insert into patient_remind set hid=$hid, patient_id=$id, patient_name='".$line["name"]."', remind_date='$remind_date', uid=$uid, u_name='$realname', addtime=$time ");
			}
		}
	}

	$post_field = explode(" ", "name sex age tel qq order_qq weixin order_weixin swt_id content zhusu disease_id depart media_from account shichang zhuanjia_num talk_content wish_doctor");

	foreach ($post_field as $v) {
		if (isset($_POST[$v])) {
			$r[$v] = $_POST[$v];
		}
	}


	if (isset($_POST["order_soft"])) {
		$r["order_soft"] = $_POST["order_soft"];
	}


	// QQ来源和电话来源，特殊处理 @ 2012-12-12
	if ($_POST["media_from"] != '') {
		$r["qq_from"] = $_POST["media_from"] == "QQ" ? $_POST["qq_from"] : "";
		$r["tel_from"] = $_POST["media_from"] == "电话" ? $_POST["tel_from"] : "";
	}

	if ($_POST["disease_2_submit"]) {
		$r["disease_2"] = @implode(",", $_POST["disease_2"]);
	}

	// 2011-12-01
	if (isset($_POST["tel"])) {
		$r["tel"] = trim($r["tel"]);
		if (strlen($r["tel"]) == 11 && $line["tel_location"] == '') {
			$r["tel_location"] = @get_mobile_location($r["tel"]);
		}
	}

	if ($line["status"] != 1) {
		if (isset($_POST["order_date"])) {
			$r["order_date"] = @strtotime($_POST["order_date"]);
		}
	}

	if ($_POST["memo"]) {
		$r["memo"] = rtrim($line["memo"])."\n".date("Y-m-d H:i ").$realname.": ".$_POST["memo"];
	}

	$log_field = "name sex age tel qq order_qq weixin order_weixin swt_id content zhusu order_type order_soft disease_id disease_2 depart media_from qq_from tel_from account zhuanjia_num wish_doctor status order_date";
	$logs = patient_modify_log($r, $line, $log_field);
	if ($logs) {
		$r["edit_log"] = $logs;
	}

	if (count($r) > 0) {
		$sqldata = $db->sqljoin($r);
		$sql = "update $table set $sqldata where id='$id' limit 1";

		ob_start();
		$return = $db->query($sql);
		$error = ob_get_clean();
	} else {
		$return = 1;
	}

	//user_op_log("修改病人[".$r["name"]."]");

	if ($return && empty($error)) {
		echo '<script type="text/javascript">'."\r\n";
		echo 'parent.load_box(0);'."\r\n";
		echo 'parent.msg_box("资料修改成功");'."\r\n";
		echo '</script>'."\r\n";
		exit;
	} else {
		echo "资料提交出错，请联系开发人员解决: <br><br>";
		echo $db->sql."<br><br>";
		echo $error."<br><br>";
		exit;
	}
}

// 字典数组:
$disease_id_name = $db->query("select id,name from disease where hospital_id='$hid' and isshow=1 order by sort desc,id asc", "id", "name");
$disease_2_name = $db->query("select id,disease_2 from disease where hospital_id='$hid' and isshow=1", "id", "disease_2");
$part_id_name = $db->query("select id,name from sys_part", "id", "name");
$depart_id_name = $db->query("select id,name from depart where hospital_id='$hid'", "id", "name");
$status_arr = array(0 => '等待', 1 => '已到', 2 => '未到');
$media_from_array = explode(" ", "网络 电话");
$qq_from_arr = $db->query("select id,name from qq_from order by sort desc,id asc", "id", "name");
$tel_from_arr = $db->query("select id,name from tel_from order by sort desc,id asc", "id", "name");
$media_2 = $db->query("select name from media where (hospital_id=0 or hospital_id=$hid) order by sort desc,addtime asc", "", "name");
$media_from_array = array_merge($media_from_array, $media_2);
$engine_array = $db->query("select name from engine order by id asc", "", "name");


// 控制各选项是否可以编辑:
$all_field = explode(" ", "name sex age tel qq weixin swt_id content order_type disease media_from account shichang zhuanjia_num order_date depart talk_content");

if ($line["status"] == 1) {
	if ($uinfo["edit_come_patient"] == 1) {
		$edit_field = explode(" ", "sex age qq weixin swt_id order_type disease media_from account shichang depart memo"); //已到修改特权
	} else {
		$edit_field = explode(" ", "memo");
	}
} else {
	if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
		$edit_field = $all_field;
	} else {
		$edit_field = explode(" ", "qq weixin swt_id order_type order_date content talk_content disease media_from account shichang depart memo zhuanjia_num");
	}
}

// 幽兰 @ 2012-05-10 修改：阻止导医修改名字和联系方式
if ($uinfo["part_id"] == 4) { //导医
	$edit_field = explode(" ", "sex age disease account shichang zhuanjia_num depart memo");
}


// 每个字段是否能编辑:
$ce = array();
foreach ($all_field as $v) {
	$ce[$v] = in_array($v, $edit_field) ? true : false;
}


if ($config["show_tel"] || $line["author"] == $realname) {
	// 有权限
} else {
	$ce["tel"] = false;
	$line["tel"] = '<span title="无权限">#</span>';
}

if ($line["status"] == 1 && $config["show_come_tel"] != 1) {
	$line["tel"] = "***";
}

if ($line["status"] == 1 && $config["show_come_doctor"] != 1) {
	if ($line["xianchang_doctor"] != '') {
		$line["xianchang_doctor"] = "***";
	}
	if ($line["doctor"] != '') {
		$line["doctor"] = "***";
	}
}

if ($line["status"] == 1 && $config["show_come_doctor"] != 1) {
	$line["memo"] = _content_filter($line["memo"], $hid_filter_arr[$hid]);
}


function _content_filter($str, $filter_string = '') {
	if (trim($filter_string) == '') return $str;
	$arr = explode(" ", trim($filter_string));
	foreach ($arr as $v) {
		$str = str_replace($v, "***", $str);
	}
	return $str;
}

//user_op_log("打开修改病人页面[".$line["name"]."]");

?>
<html>
<head>
<title><?php echo $line["name"]; ?> : 修改资料</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<script language="javascript">
function check_data() {
	if (!confirm("每项资料如果修改都会有日志记录，请确定是否要提交？")) {
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
		alert("请先填写日期，再填写时间！");
		return;
	}
	var date = s.split(" ")[0];
	var datetime = date+" "+time;

	if (byid(id).disabled != true) {
		byid(id).value = datetime;
	}
}

// 检查数据重复:
function check_repeat(type, obj) {
	if (!byid("id") || (byid("id").value == '0' || byid("id").value == '')) {
		var value = obj.value;
		if (value != '') {
			var xm = new ajax();
			xm.connect("http/check_repeat.php?type="+type+"&value="+value+"&r="+Math.random(), "GET", "", check_repeat_do);
		}
	}
}

function check_repeat_do(o) {
	var out = ajax_out(o);
	if (out["status"] == "ok") {
		if (out["tips"] != '') {
			alert(out["tips"]);
		}
	}
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

function in_array(find, arr) {
	for(i=0; i<arr.length; i++) {
		if(arr[i] == find)
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

		// 如果是默认id，则把未知的二级疾病也放到选项中，如果选择新的疾病，旧的未知二级疾病就丢弃
		if (disease_id == default_disease_id) {
			for (var i=0; i<cur_2.length; i++) {
				var cur_2_name = cur_2[i];
				if (in_array(cur_2_name, d2s) == false) {
					d2s[d2s.length] = cur_2_name;
				}
			}
		}

		// 构建选项
		for (var i=0; i<d2s.length; i++) {
			if (d2s[i] != '') {
				var dis_2_name = d2s[i];
				var sel = in_array(dis_2_name, cur_2);
				s += '<input type="checkbox" name="disease_2[]" value="'+dis_2_name+'"'+(sel ? ' checked' : "")+' id="d2_'+dis_2_name+'"><label for="d2_'+dis_2_name+'">'+(sel ? ('<font color=red>'+dis_2_name+'</font>') : dis_2_name)+'</label>&nbsp;';
			}
		}
	}

	byid("disease_2_box").innerHTML = s;
	byid("disease_tips").style.display = (s == '' ? "inline" : "none"); //有二级疾病就不显示后面的提示了
}

</script>
</head>

<body>
<div class="description">
	<div class="d_item"><b>提示：</b>下面的每项资料如果修改，都会有日志记录，如无必要，请勿修改。恶意修改资料将会追究责任。</div>
</div>
<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">病人基本资料</td>
	</tr>
	<tr>
		<td class="left">姓名：</td>
		<td class="right">
<?php if ($ce["name"]) { ?>
			<input name="name" id="name" value="<?php echo $line["name"]; ?>" class="input" style="width:200px" onchange="check_repeat('name', this)"> &nbsp; <font color="green">姓名不能为空</font>
<?php } else { ?>
			<?php echo $line["name"]; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">性别：</td>
		<td class="right">
<?php if ($ce["sex"]) { ?>
			<input name="sex" id="sex" value="<?php echo $line["sex"]; ?>" class="input" style="width:80px"> <a href="javascript:input('sex', '男')">[男]</a> <a href="javascript:input('sex', '女')">[女]</a>
<?php } else { ?>
			<?php echo $line["sex"]; ?>
<?php } ?>
		</td>
	</tr>
	<tr>
		<td class="left">年龄：</td>
		<td class="right">
<?php if ($ce["age"]) { ?>
			<input name="age" id="age" value="<?php echo $line["age"]; ?>" class="input" style="width:80px">
<?php } else { ?>
			<?php echo $line["age"]; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">电话：</td>
		<td class="right">
<?php if ($ce["tel"]) { ?>
			<input name="tel" id="tel" value="<?php echo $line["tel"]; ?>" class="input" style="width:200px" <?php echo $ce["tel"]; ?> onchange="check_repeat('tel', this);"> <span id="tel_location_show"><?php echo $line["tel_location"]; ?></span> &nbsp; <font color="green">手机号请填写11位格式，不要带+86等不必要的前缀</font>
<?php } else { ?>
			<?php echo $line["tel"]; ?> <?php echo $line["tel_location"]; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">患者微信：</td>
		<td class="right">
<?php if ($ce["weixin"]) { ?>
			<input name="weixin" value="<?php echo $line["weixin"]; ?>" class="input" style="width:100px">　　我方微信：<input name="order_weixin" value="<?php echo $line["order_weixin"]; ?>" class="input" style="width:100px">
<?php } else { ?>
			<?php echo $line["weixin"] ? $line["weixin"] : "(无)"; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">患者QQ：</td>
		<td class="right">
<?php if ($ce["qq"]) { ?>
			<input name="qq" value="<?php echo $line["qq"]; ?>" class="input" style="width:100px">　　我方QQ：<input name="order_qq" value="<?php echo $line["order_qq"]; ?>" class="input" style="width:100px">
<?php } else { ?>
			<?php echo $line["qq"] ? $line["qq"] : "(无)"; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">主诉：</td>
		<td class="right">
<?php if ($ce["zhusu"]) { ?>
			<input name="zhusu" value="<?php echo $line["zhusu"]; ?>" class="input" style="width:60%"> <span class="intro">用于生成预约卡</span>
<?php } else { ?>
			<?php echo $line["zhusu"] ? $line["zhusu"] : "(无)"; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">咨询内容总结：</td>
		<td class="right">
<?php if ($ce["content"]) { ?>
			<textarea name="content" style="width:60%; height:48px; vertical-align:middle;" class="input"><?php echo $line["content"]; ?></textarea> &nbsp; <font color="green">请填写咨询内容摘要，切勿粘贴大量聊天记录</font>
<?php } else { ?>
			<?php echo $line["content"] ? text_show($line["content"]) : "(无)"; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">聊天记录：</td>
		<td class="right">
<?php if ($ce["talk_content"]) { ?>
			<textarea name="talk_content" style="width:60%; height:100px; vertical-align:middle;" class="input"><?php echo $line["talk_content"]; ?></textarea> &nbsp; <font color="green">可复制聊天记录备查</font>
<?php } else { ?>
	<?php if (trim($line["talk_content"]) != '') { ?>
			<div style="height:200px; overflow-y:scroll;"><?php echo text_show($line["talk_content"]); ?></div>
	<?php } else { ?>
			(无)
	<?php } ?>
<?php } ?>
		</td>
	</tr>
</table>

<div class="space"></div>
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">附加资料</td>
	</tr>

<?php if ($debug_mode || $line["part_id"] == 2) { ?>
	<tr>
		<td class="left">预约软件：</td>
		<td class="right">
<?php if ($ce["order_type"]) { ?>
			<select name="order_soft" id="order_soft" class="combo">
				<option value="" style="color:gray">--预约软件--</option>
				<?php echo list_option($web_soft_arr, '_key_', '_value_', $line["order_soft"]); ?>
			</select>
<?php } else { ?>
			<?php echo $web_soft_arr[$line["order_soft"]]; ?>
<?php } ?>
		</td>
	</tr>
	<tr>
		<td class="left">商务通永久身份：</td>
		<td class="right">
<?php if ($ce["swt_id"]) { ?>
			<input name="swt_id" id="swt_id" value="<?php echo $line["swt_id"]; ?>" class="input" style="width:250px" >
<?php } else { ?>
			<?php echo $line["swt_id"]; ?>
<?php } ?>
		</td>
	</tr>
<?php } ?>

	<tr>
		<td class="left">媒体来源：</td>
		<td class="right">
<?php if ($ce["media_from"]) { ?>
			<select name="media_from" id="media_from" class="combo" onchange="on_media_from_change(this)">
				<option value="" style="color:gray">--请选择--</option>
				<?php echo list_option($media_from_array, '_value_', '_value_', $line["media_from"]); ?>
			</select>&nbsp;
			<select name="qq_from" class="combo" id="qq_from" style="display:none; margin-right:5px;">
				<option value="" style="color:gray">--QQ来源--</option>
				<?php echo list_option($qq_from_arr, '_value_', '_value_', $line["qq_from"]); ?>
			</select>
			<select name="tel_from" class="combo" id="tel_from" style="display:none; margin-right:5px;">
				<option value="" style="color:gray">--电话来源--</option>
				<?php echo list_option($tel_from_arr, '_value_', '_value_', $line["tel_from"]); ?>
			</select>
			<select name="shichang" class="combo" id="shichang" style="display:none; margin-right:5px;">
				<option value="" style="color:gray">--市场来源--</option>
				<?php echo list_option($shichang_arr, '_value_', '_value_', $line["shichang"]); ?>
			</select>

			<script type="text/javascript">
			function on_media_from_change(o) {
				byid("qq_from").style.display = "none";
				byid("tel_from").style.display = "none";
				byid("shichang").style.display = "none";
				if (o.value == "QQ") {
					byid("qq_from").style.display = "";
				} else if (o.value == "电话") {
					byid("tel_from").style.display = "";
				} else if (o.value == "市场") {
					byid("shichang").style.display = "";
				}
			}
			on_media_from_change(byid("media_from"));
			</script>
<?php } else { ?>
			媒体来源：<?php echo $line["depart"]; ?>&nbsp;<?php echo $line["qq_from"] ? ("QQ来源：".$line["qq_from"]) : ""; ?>&nbsp;
<?php } ?>

		</td>
	</tr>

	<tr>
		<td class="left">疾病类型：</td>
		<td class="right">
<?php if ($ce["disease"]) { ?>
			<!-- 编辑疾病类型 begin -->
			<select name="disease_id" onchange="show_disease_2(this.value)" class="combo">
				<option value="0" style="color:gray">--请选择--</option>
				<?php echo list_option($disease_id_name, '_key_', '_value_', $line["disease_id"]); ?>
			</select>&nbsp;
			<span id="disease_2_box"></span> &nbsp; <font color="green" id="disease_tips">疾病类型和二级疾病，均可联系管理员设置下拉选项</font>

			<span style="display:none">
<?php foreach ($disease_2_name as $k => $v) { ?>
				<input type="hidden" id="disease_2_<?php echo $k; ?>" title="<?php echo $disease_id_name[$k]; ?>" value="<?php echo $v; ?>">
<?php } ?>
				<input type="hidden" id="disease_2_old" value="<?php echo $line["disease_2"]; ?>">
				<input type="hidden" id="default_disease_id" value="<?php echo $line["disease_id"]; ?>">
				<input type="hidden" name="disease_2_submit" value="1">
			</span>
<?php if ($line["disease_id"] > 0) { ?>
			<script type="text/javascript">
			show_disease_2(<?php echo $line["disease_id"]; ?>);
			</script>
<?php } ?>
			<!-- 编辑疾病类型 end -->
<?php } else { ?>
			<?php echo $disease_id_name[$line["disease_id"]]; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">就诊科室：</td>
		<td class="right">
<?php if ($ce["depart"]) { ?>
			<select name="depart" class="combo">
				<option value="0" style="color:gray">--请选择--</option>
				<?php echo list_option($depart_id_name, '_key_', '_value_', $line["depart"]); ?>
			</select>
			<span class="intro">就诊科室（如果下拉中没有科室，请先联系相关管理人员添加科室）</span>
<?php } else { ?>
			<?php echo $depart_id_name[$line["depart"]]; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">所属账号：</td>
		<td class="right">
<?php if ($ce["account"]) { ?>
			<select name="account" class="combo">
				<option value="" style="color:gray">--请选择--</option>
				<?php echo list_option($account_array, '_value_', '_value_', $line["account"]); ?>
			</select>
<?php } else { ?>
			<?php echo $line["account"]; ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">专家号：</td>
		<td class="right">
<?php if ($ce["zhuanjia_num"]) { ?>
			<input name="zhuanjia_num" value="<?php echo $line["zhuanjia_num"]; ?>" class="input" style="width:150px">
<?php
$wish_doctor_array = array();
$wish_doctor_array = $db->query("select name from doctor where hospital_id=$hid order by id asc", "", "name");
if ($line["wish_doctor"] != '' && !in_array($line["wish_doctor"], $wish_doctor_array)) {
	$wish_doctor_array[] = $line["wish_doctor"];
}
?>
			&nbsp;&nbsp;指定医生：
			<select name="wish_doctor" class="combo">
				<option value="" style="color:gray">--请选择--</option>
				<?php echo list_option($wish_doctor_array, '_value_', '_value_', $line["wish_doctor"]); ?>
			</select>
<?php } else { ?>
			<?php echo $line["zhuanjia_num"].($line["wish_doctor"] ? ("&nbsp;&nbsp;指定医生：".$line["wish_doctor"]) : ""); ?>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">预约时间：</td>
		<td class="right">
<?php if ($ce["order_date"]) { ?>
			<input name="order_date" value="<?php echo $line["order_date"] ? @date('Y-m-d H:i:s', $line["order_date"]) : ''; ?>" class="input" style="width:150px" id="order_date"> <img src="image/calendar.gif" id="order_date" onClick="picker({el:'order_date',dateFmt:'yyyy-MM-dd HH:mm:ss'})" align="absmiddle" style="cursor:pointer" title="选择时间"> &nbsp; <font color="red">(提示：修改会有日志记录哦)</font>
			<?php
			$show_days = array(
				"今" => $today = date("Y-m-d"), //今天
				"明" => date("Y-m-d", strtotime("+1 day")), //明天
				"后" => date("Y-m-d", strtotime("+2 days")), //后天
				"大后天" => date("Y-m-d", strtotime("+3 days")), //大后天
				"周六" => date("Y-m-d", strtotime("next Saturday")), //周六
				"周日" => date("Y-m-d", strtotime("next Sunday")), // 周日
				"周一" => date("Y-m-d", strtotime("next Monday")), // 周一
				"一周后" => date("Y-m-d", strtotime("+7 days")), // 一周后
				"半月后" => date("Y-m-d", strtotime("+15 days")), //半个月后
			);

			echo '<br>速填日期: ';
			foreach ($show_days as $name => $value) {
				echo '<a href="javascript:input_date(\'order_date\', \''.$value.'\')">['.$name.']</a>&nbsp;';
			}
			echo '&nbsp;&nbsp; 时间: ';
			echo '<a href="javascript:input_time(\'order_date\',\'09:00:00\')">[上午9点]</a>&nbsp;';
			echo '<a href="javascript:input_time(\'order_date\',\'14:00:00\')">[下午2点]</a>&nbsp;';

			?>
<?php } else { ?>
			<b style="color:red"><?php echo date("Y-m-d H:i", $line["order_date"]); ?></b> <font color="red">(不能修改预约时间)</font>
<?php } ?>
		</td>
	</tr>

	<tr>
		<td class="left">回访提醒：</td>
		<td class="right"><input name="huifang_nexttime" value="<?php echo $huifang_time; ?>" class="input" style="width:150px" id="huifang_nexttime"> <img src="image/calendar.gif" id="huifang_nexttime" onClick="picker({el:'huifang_nexttime',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择日期"> <span class="intro">下次回访提醒日期，为空则不提醒</span></td>
	</tr>

<?php if ($uinfo["part_id"] == 4) { ?>
	<tr>
		<td class="left" valign="top">提醒：</td>
		<td class="right"><font color="red">导医勾到院，请返回列表使用 <img src="image/b_pass.gif"> 这个功能。(此提示仅导医可见。非导医身份不能勾到院)</font></td>
	</tr>
<?php } ?>

<?php if ($line["memo"]) { ?>
	<tr>
		<td class="left" valign="top">当前备注：</td>
		<td class="right"><font color="green"><?php echo text_show(trim($line["memo"])); ?></font></td>
	</tr>
<?php } ?>
	<tr>
		<td class="left">添加备注：</td>
		<td class="right"><textarea name="memo" style="width:60%; height:48px; vertical-align:middle;" class="input"></textarea></td>
	</tr>
	<tr>
		<td class="left">添加人：</td>
		<td class="right"><?php echo $line["author"]; ?> @ <?php echo date("Y-m-d H:i", $line["addtime"]); ?> <?php echo $part_id_name[$line["part_id"]]; ?></td>
	</tr>
</table>

<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>

</form>
</body>
</html>