<?php
// --------------------------------------------------------
// - 功能说明 : 医院新增、修改
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-01 00:40
// --------------------------------------------------------
require "lib/set_env.php";
$table = "disease";

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

	// 将两个或多个空格替换为一个:
	for ($i=1; $i<=5; $i++) {
		$_POST["disease_2"] = str_replace("  ", " ", $_POST["disease_2"]);
		$_POST["xiangmu"] = str_replace("  ", " ", $_POST["xiangmu"]);
	}

	$r["xiangmu"] = $_POST["xiangmu"];
	$r["disease_2"] = $_POST["disease_2"];
	$r["intro"] = $_POST["intro"];
	$r["sort"] = $_POST["sort"];


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
$title = ($mode == "edit") ? "修改疾病定义" : "添加新的疾病";

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
		alert("请输入“疾病名称”！"); oForm.name.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>
<div class="description">
	<div class="d_title">提示：</div>
	<div class="d_item">1.输入疾病名称及简介（简介可不输入），点击提交即可</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">疾病资料</td>
	</tr>
	<tr>
		<td class="left">疾病名称：</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <font color="gray">名称必须填写</font></td>
	</tr>
	<tr>
		<td class="left">二级疾病：</td>
		<td class="right"><textarea name="disease_2" class="input" style="width:500px; height:80px; overflow:visible; vertical-align:middle;"><?php echo $line["disease_2"]; ?></textarea> <font color="gray">多个用空格隔开</font></td>
	</tr>
	<tr>
		<td class="left">治疗项目：</td>
		<td class="right"><textarea name="xiangmu" class="input" style="width:500px; height:80px; overflow:visible; vertical-align:middle;"><?php echo $line["xiangmu"]; ?></textarea> <font color="gray">多个用空格隔开</font></td>
	</tr>
	<tr>
		<td class="left">疾病简介：</td>
		<td class="right"><textarea name="intro"class="input"  style="width:500px; height:80px; overflow:visible; vertical-align:middle;"><?php echo $line["intro"]; ?></textarea> <font color="gray">选填</font></td>
	</tr>
	<tr>
		<td class="left">优先度：</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" size="10" style="width:200px"> <font color="gray">越大越优先，可为负数，放最末尾</font></td>
	</tr>
</table>
<?php if ($mode == "edit") { ?>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<?php } ?>
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>