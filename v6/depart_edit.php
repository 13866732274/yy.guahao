<?php
// --------------------------------------------------------
// - 功能说明 : 科室新增、修改
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-01 00:40
// --------------------------------------------------------
require "lib/set_env.php";
$table = "depart";

$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";

if ($mode == "edit") {
	check_power("e", $pinfo, $pagepower) or exit("对不起，您没有修改权限...");
} else {
	check_power("i", $pinfo, $pagepower) or exit("对不起，您没有新增权限...");
}

if ($_POST) {
	$r = array();
	$r["name"] = $_POST["name"];
	$r["intro"] = $_POST["intro"];

	if ($mode == "add") {
		$r["hospital_id"] = $user_hospital_id;
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
		echo '<script> parent.msg_box("资料提交成功", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "提交失败，请稍后再试！";
	}
	exit;
}

if ($mode == "edit") {
	$line = $db->query_first("select * from $table where id='$id' limit 1");
}
$title = $mode == "edit" ? "修改科室" : "添加新的科室";

$hospital_list = $db->query("select id,name from hospital");
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function Check() {
	var oForm = document.mainform;
	if (oForm.name.value == "") {
		alert("请输入“科室名称”！"); oForm.name.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>

<div class="description">
	<div class="d_item"><b>提示：</b>输入科室名称及简介（简介可不输入），点击提交即可</div>
</div>

<div class="space"></div>
<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">科室资料</td>
	</tr>
	<tr>
		<td class="left">科室名称：</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">名称必须填写</span></td>
	</tr>
	<tr>
		<td class="left">科室简介：</td>
		<td class="right"><textarea name="intro"class="input"  style="width:60%; height:80px; overflow:visible;"><?php echo $line["intro"]; ?></textarea> <span class="intro">选填</span></td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>