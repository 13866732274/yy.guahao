<?php
// --------------------------------------------------------
// - 功能说明 : 媒体类型新增、修改
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-03 14:48
// --------------------------------------------------------
require "lib/set_env.php";
$table = "media";

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

	if ($r["name"] == "网络" || $r["name"] == "电话") {
		exit("“网络”和“电话”为系统内置媒体来源，不需要在此添加。");
	}

	$r["hospital_id"] = intval($_POST["media_type"]);

	$r["is_often"] = $_POST["is_often"] ? 1 : 0;

	$r["sort"] = intval($_POST["sort"]);

	if ($mode == "add") {
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
$title = $mode == "edit" ? "修改媒体来源" : "添加新的来源";

$_hid = $mode == "edit" ? $line["hospital_id"] : intval($_GET["hid"]);

// 下拉选项:
$shid = $_hid ? $_hid : $hid;
$h_name = $db->query("select name from hospital where id=$shid limit 1", 1, "name");

$media_type_arr = array("0" => "全局媒体来源", $shid => "“".$h_name."”私有媒体来源");

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
		alert("请输入“名称”！"); oForm.name.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>
<div class="description">
	<div class="d_title">注意：“网络”和“电话”为系统内置媒体来源，不需要在此添加。</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">媒体类型资料</td>
	</tr>
	<tr>
		<td class="left">类型：</td>
		<td class="right">
			<select name="media_type" class="combo">
				<?php echo list_option($media_type_arr, "_key_", "_value_", $mode == "edit" ? $line["hospital_id"] : $_GET["hid"]); ?>
			</select>
			<span class="intro">媒体类型必须填写</span>
		</td>
	</tr>
	<tr>
		<td class="left">名称：</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">名称必须填写</span></td>
	</tr>
	<tr>
		<td class="left">是否常用：</td>
		<td class="right"><input type="checkbox" name="is_often" value="1" id="is_often" <?php echo $line["is_often"] ? "checked" : ""; ?>><label for="is_often">是否常用媒体来源（常用媒体将优先列出）</label></td>
	</tr>
	<tr>
		<td class="left">排序：</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">越大越靠前；可以为负值，排最后</span></td>
	</tr>
</table>

<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>