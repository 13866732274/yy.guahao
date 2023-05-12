<?php
// --------------------------------------------------------
// - 功能说明 : 渠道新增
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-10-18
// --------------------------------------------------------
$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";
require "lib/set_env.php";
$table = "dict_qudao";


$guiji_key_type_arr = array(
	"" => "不自动判定",
	"engine" => "外部来源　(搜索引擎/第三方站)　(对应商务通：访问来源)",
	"site_url" => "访问网址　(推广网站/优化站)　(对应商务通：初次访问网址)",
	"engine_site_url" => "外部来源+访问网址　(将两个字符串合并在一起判断)",
);


if ($_POST) {
	$r = array();
	$r["main_id"] = $_POST["main_id"];
	$r["main_name"] = $guiji_arr[$_POST["main_id"]];
	$r["name"] = $_POST["name"];
	$r["sort"] = $_POST["sort"];

	$r["guiji_key_type"] = trim($_POST["guiji_key_type"]);

	$keyword = str_replace("\r", "", $_POST["guiji_keyword"]);
	$key_arr = explode("\n", $keyword);
	$ok_key = array();
	foreach ($key_arr as $key) {
		$key = strip_tags($key);
		$key = str_replace('"', "", $key);
		$key = str_replace("'", "", $key);
		$key = str_replace("\\", "", $key);
		$key = trim($key);
		if ($key != "") {
			$ok_key[] = $key;
		}
	}
	$r["guiji_keyword"] = implode("\n", $ok_key);

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

	if ($db->query($sql)) {
		//echo '<script> parent.update_content(); </script>';
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

?>
<html>
<head>
<title>编辑渠道</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function check_data(f) {
	if (f.name.value == "") {
		alert("请输入“名称”！");
		f.name.focus();
		return false;
	}
	return true;
}
</script>
</head>

<body>

<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" class="new_edit">
	<tr>
		<td class="left"><font color="red">*</font> 大类：</td>
		<td class="right">
			<select name="main_id" class="combo">
				<?php echo list_option($guiji_arr, "_key_", "_value_", $line["main_id"]); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 名称：</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">名称必须填写</span></td>
	</tr>
	<tr>
		<td class="left">优先度：</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" style="width:80px"> <span class="intro">优先度越大,排序越靠前</span></td>
	</tr>
	<tr>
		<td class="left">轨迹判断依据：</td>
		<td class="right">
			<select name="guiji_key_type" class="combo">
				<?php echo list_option($guiji_key_type_arr, "_key_", "_value_", $line["guiji_key_type"]); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left" valign="top">轨迹判断关键词：</td>
		<td class="right">
			<textarea name="guiji_keyword" class="input" style="width:400px; height:200px; "><?php echo $line["guiji_keyword"]; ?></textarea>
			<div style="margin-top:5px;">每行填写一个关键词；尽量精简、准确</div>
		</td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">

<div class="button_line">
	<input type="submit" class="submit" value="提交资料">
</div>

</form>
</body>
</html>