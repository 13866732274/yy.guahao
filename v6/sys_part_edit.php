<?php
// --------------------------------------------------------
// - 功能说明 : 部门新增、修改
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-03-30 13:30
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_part";

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
		// 弹出窗口的处理方式:
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

?>
<html>
<head>
<title><?php echo $id ? "修改部门" : "添加部门"; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function Check() {
	var oForm = document.mainform;
	if (oForm.name.value == "") {
		alert("请输入“部门名称”！");
		oForm.name.focus();
		return false;
	}
	return true;
}
</script>
</head>

<body>

<div class="space"></div>
<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">部门资料</td>
	</tr>
	<tr>
		<td class="left" style="width:25%"><font color="red">*</font> 部门名称：</td>
		<td class="right" style="width:75%"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">名称必须填写</span></td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 优先度：</td>
		<td class="right">
			<input name="sort" value="<?php echo $line["sort"]; ?>" class="input" size="30" style="width:200px">
			<span class="intro">大数优先 默认为0 负值排最末尾</span>
		</td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>