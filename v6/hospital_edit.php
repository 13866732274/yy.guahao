<?php
// --------------------------------------------------------
// - 功能说明 : 医院新增、修改
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-01 00:40
// --------------------------------------------------------
$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";
require "lib/set_env.php";
$table = "hospital";

if ($mode == "edit") {
	check_power("e", $pinfo, $pagepower) or exit("对不起，您没有修改权限...");
} else {
	check_power("i", $pinfo, $pagepower) or exit("对不起，您没有新增权限...");
}

if ($_POST) {
	$r = array();
	$r["name"] = $_POST["name"];
	$r["intro"] = $_POST["intro"];
	$r["sort"] = $_POST["sort"];

	$r["area"] = $_POST["area"];
	$r["sname"] = trim($_POST["sname_add"]) ? trim($_POST["sname_add"]) : $_POST["sname"];
	$r["depart"] = $_POST["depart"];
	$r["group_id"] = $_POST["group_id"];
	$r["group_name"] = $db->query("select name from hospital_group where id=".$_POST["group_id"]." limit 1", 1, "name");
	$r["color"] = $_POST["color"];

	// 精简名称
	$r["short_name"] = $_POST["short_name"];

	$r["repeat_open"] = intval($_POST["repeat_open"]) ? 1 : 0;
	$r["repeat_tip_time"] = intval($_POST["repeat_tip_time"]);
	$r["repeat_deny_time"] = intval($_POST["repeat_deny_time"]);
	$r["ishide"] = intval($_POST["ishide"]);

	// 咨询内容模版 @ 2014-6-20
	$r["template"] = $_POST["template"];


	if ($mode == "add") {
		$r["addtime"] = time();
		$r["author"] = $username;
	}

	$sqldata = $db->sqljoin($r);
	if ($mode == "add") {
		$sql = "insert into $table set $sqldata";
	} else {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	}

	if ($hid = $db->query($sql)) {
		// 创建医院对应的数据表
		$hid = ($mode == "edit") ? $id : $hid;
		create_patient_table($hid);

		if ($mode == "add") {
			// 将新增医院自动增加到我的账号上
			if ($uid > 0 && $hid > 0 && !in_array($hid, $hospital_ids)) {
				$hospital_ids[] = $hid;
				@asort($hospital_ids);
				$hospital_str = @implode(",", $hospital_ids);
				$db->query("update sys_admin set hospitals='$hospital_str' where id=$uid limit 1");
			}
		}

		// 弹出窗口的处理方式:
		if ($mode == "add") {
			echo '<script> parent.update_content(); </script>';
		}
		echo '<script> parent.msg_box("资料提交成功", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "提交失败，请稍后再试！";
	}
	exit;
}

if ($mode == "edit") {
	$line = $db->query("select * from $table where id='$id' limit 1", 1);
}
$title = $mode == "edit" ? "修改医院定义" : "添加新的医院";

$h_group_id_name = $db->query("select * from hospital_group order by sort desc, name asc", "id", "name");

$hospital_arr = $db->query("select sname, count(sname) as c from hospital where ishide=0 group by sname order by sname asc", "sname", "sname");

// 下面加上隐藏的医院
$hospital_arr["----"] = "---------------------";
$tmp_2 = $db->query("select sname, count(sname) as c from hospital where ishide=1 group by sname order by sname asc", "sname", "sname");
foreach ($tmp_2 as $sname) {
	if (!in_array($sname, $hospital_arr)) {
		$hospital_arr[$sname] = $sname;
	}
}


?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.edit, .edit td {border:0px solid #c0c0c0 !important; background:white !important; }
</style>
<script language="javascript">
function Check() {
	var oForm = document.mainform;
	if (oForm.name.value == "") {
		alert("请输入“医院名称”！");
		oForm.name.focus();
		return false;
	}
	return true;
}
</script>
</head>

<body>
<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td class="left" style="width:20%;"><font color="red">*</font> 科室全称：</td>
		<td class="right" style="width:80%;"><input name="name" value="<?php echo $line["name"]; ?>" class="input" style="width:300px"> <span class="intro">(名称必须填写)</span></td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 医院所在地区：</td>
		<td class="right">
			<input name="area" value="<?php echo $line["area"]; ?>" class="input" style="width:150px; margin-left:30px"> <span class="intro">(用于按地区归类，如“上海”)</span>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 医院名称：</td>
		<td class="right">
			<select name="sname" class="combo" style="width:150px; margin-left:30px">
				<option value=""></option>
				<?php echo list_option($hospital_arr, "_key_", "_value_", $line["sname"]); ?>
			</select>　如果下拉没有，请手工填写：<input name="sname_add" value="" class="input" style="width:100px"> <span class="intro">(如“上海九龙医院”)</span>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 科室名：</td>
		<td class="right">
			<input name="depart" value="<?php echo $line["depart"]; ?>" class="input" style="width:150px; margin-left:30px"> <span class="intro">(用于按科室归类，如“男科”)</span>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 精简名称：</td>
		<td class="right">
			<input name="short_name" value="<?php echo $line["short_name"]; ?>" class="input" size="30" style="width:150px; margin-left:30px"> <span class="intro">(用于在列表中显示，如“九龙男科”)</span>
		</td>
	</tr>

	<tr>
		<td class="left"><font color="red">*</font> 分组选择：</td>
		<td class="right">
			<select name="group_id" class="combo">
				<option value="" style="color:gray;">-请选择-</option>
				<?php echo list_option($h_group_id_name, "_key_", "_value_", $line["group_id"]); ?>
			</select>&nbsp;<span class="intro">(用于下拉选择中对科室进行分组)</span>
		</td>
	</tr>

	<tr>
		<td class="left">显示颜色：</td>
		<td class="right">
			<select name="color" class="combo">
				<option value="" style="color:gray">-默认-</option>
<?php
$colors = array(
	"red" => "红色",
	"green" => "绿色",
	"blue" => "蓝色",
	"black" => "黑色",
	"gray" => "灰色",
	"silver" => "银色",
	"#0080ff" => "天蓝",
	"#408080" => "青色",
	"#ff00ff" => "品红",
	"#8080ff" => "#8080ff",
	"#cc66ff" => "#cc66ff",
	"#ff66cc" => "#ff66cc",
);
foreach ($colors as $k => $v) {
	echo '<option value="'.$k.'" style="color:'.$k.'"'.($k == $line["color"] ? 'selected' : '').'>'.$v.' '.($k == $line["color"] ? ' *' : '').'</option>';
}
?>
			</select>&nbsp;<span class="intro">(用于首页的下拉列表等显示)</span>
		</td>
	</tr>

	<tr>
		<td class="left">号码重复设置：</td>
		<td class="right">
			<input type="checkbox" name="repeat_open" value="1" <?php echo ($mode == "add" ? "checked" : ($line["repeat_open"] > 0 ? "checked" : "")); ?> id="repeat_open" onclick='byid("repeat_days_set").style.display = byid("repeat_open").checked ? "" : "none";'><label for="repeat_open">禁止号码重复</label>&nbsp;
			<span id="repeat_days_set" style="display:none;">
				<b>提醒天数：</b><input name="repeat_tip_time" value="<?php echo $line["repeat_tip_time"]; ?>" size="4" class="input">&nbsp;&nbsp;
				<b>禁止提交天数：</b><input name="repeat_deny_time" value="<?php echo $line["repeat_deny_time"]; ?>" size="4" class="input">&nbsp;&nbsp;(请填写天数，如两个月填60天)
			</span>
			<br>
			<span style="color:gray;"><b>注意：</b>如果打开了禁止号码重复功能，又未设置天数的，则按照系统默认值进行处理，默认提醒天数<?php echo $cfgRepeatTipDays; ?>天，禁止提交的天数为<?php echo $cfgRepeatDenyDays; ?>天</span>
		</td>
	</tr>

	<script type="text/javascript">
		byid("repeat_days_set").style.display = byid("repeat_open").checked ? "" : "none";
	</script>

	<tr>
		<td class="left">是否隐藏：</td>
		<td class="right">
			<input type="checkbox" name="ishide" value="1" <?php echo ($mode == "add" ? "" : ($line["ishide"] != 0 ? "checked" : "")); ?> id="ishide"><label for="ishide">勾选则隐藏</label>
		</td>
	</tr>

	<tr>
		<td class="left">咨询内容模板：</td>
		<td class="right"><textarea name="template" class="input" style="width:600px; height:80px;"><?php echo $line["template"]; ?></textarea></td>
	</tr>

	<tr>
		<td class="left">优先度：</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" style="width:80px"> <span class="intro">优先度越大,排序越靠前</span></td>
	</tr>


</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="mode" value="<?php echo $mode; ?>">
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>