<?php
// --------------------------------------------------------
// - 功能说明 : 网站列表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-5-10
// --------------------------------------------------------
require "lib/set_env.php";
$table = "site_list";

if (!($config["show_site"] || $debug_mode)) {
	exit("对不起，您没有权限~");
}

$type_name_arr = array("主站", "推广站", "优化站", "其它");

$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";


if ($_POST) {
	ob_start();

	if ($mode == "add") {
		$_arr = explode("\n", trim(str_replace("\r", "", $_POST["site_url"])));
		if (count($_arr) > 0) {
			foreach ($_arr as $_site) {
				$url = _url_replace($_site);
				if ($url != '') {
					$r = array();
					$r["hid"] = $user_hospital_id;
					$r["type_name"] = $_POST["type_name"];
					$r["site_url"] = $url;
					$r["memo"] = $_POST["memo"];
					$r["sort"] = $_POST["sort"];
					$r["beian_num"] = $_POST["beian_num"];
					$r["is_beian"] = trim($_POST["beian_num"]) != "" ? 1 : 0;
					$r["addtime"] = time();
					$r["author"] = $username;

					$sqldata = $db->sqljoin($r);
					$sql = "insert into $table set $sqldata";
					$db->query($sql);
				}
			}
		}
	} else {
		$url = _url_replace($_POST["site_url"]);

		$r = array();
		$r["type_name"] = $_POST["type_name"];
		$r["site_url"] = $url;
		$r["sort"] = $_POST["sort"];
		$r["memo"] = $_POST["memo"];
		$r["beian_num"] = $_POST["beian_num"];
		$r["is_beian"] = trim($_POST["beian_num"]) != "" ? 1 : 0;
		$r["auto_update"] = intval($_POST["is_auto_update"]);
		if ($_POST["is_auto_update"] == "0") {
			$_POST["out_date"] = str_replace("年", "-", $_POST["out_date"]);
			$_POST["out_date"] = str_replace("月", "-", $_POST["out_date"]);
			$_POST["out_date"] = str_replace("日", "", $_POST["out_date"]);
			$r["out_date"] = date("Y-m-d", strtotime($_POST["out_date"]));
		}

		$sqldata = $db->sqljoin($r);
		$sql = "update $table set $sqldata where id='$id' limit 1";
		$db->query($sql);
	}

	$e = ob_get_clean();

	if ($e == '') {
		echo '<script> parent.update_content(); </script>';
		echo '<script> parent.msg_box("资料提交成功", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "提交失败: ".$e;
	}
	exit;
}

if ($mode == "edit") {
	$line = $db->query_first("select * from $table where id='$id' limit 1");
}
$title = ($mode == "edit") ? "修改网站" : "添加新的网站";

$hospital_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");


// ------------------------------- 函数 --------------------------------

function _url_replace($s) {
	$s = trim($s);
	$s = str_replace("http://", "", $s);
	$s = rtrim($s, "/");
	return $s;
}


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
	if (oForm.type_name.value == "") {
		alert("网站类型必须要选择~"); oForm.type_name.focus(); return false;
	}
	if (oForm.site_url.value == "") {
		alert("网址必须输入，不能为空~"); oForm.site_url.focus(); return false;
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
		<td colspan="2" class="head">网站资料</td>
	</tr>
	<tr>
		<td class="left">网站类型：</td>
		<td class="right">
			<select name="type_name" class="combo" style="width:150px">
				<option value="" style="color:gray">-网站类型-</option>
				<?php echo list_option($type_name_arr, '_value_', '_value_', $line["type_name"]); ?>
			</select>
		</td>
	</tr>

<?php if ($mode == "add") { ?>
	<tr>
		<td class="left">网址：</td>
		<td class="right"><textarea name="site_url" class="input" style="width:50%; height:100px; overflow:visible; vertical-align:middle;"></textarea> <font color="gray">请输入网址，每行一个</font></td>
	</tr>
<?php } ?>

<?php if ($mode == "edit") { ?>
	<tr>
		<td class="left">网址：</td>
		<td class="right"><input name="site_url" value="<?php echo $line["site_url"]; ?>" class="input" size="10" style="width:50%"> <font color="gray">请输入网址</font></td>
	</tr>

	<tr>
		<td class="left">到期时间：</td>
		<td class="right">
			<input type="radio" name="is_auto_update" id="radio1" value="1" onclick="show_hide_write(1)"><label for="radio1">程序自动获取(.org和.net等域名无法自动获取到过期时间)</label><br>
			<input type="radio" name="is_auto_update" id="radio2" value="0" onclick="show_hide_write(0)"><label for="radio2">手工填写</label>&nbsp;&nbsp;<span id="write_out_date" style="display:none"><b>请填写到期时间：</b><input name="out_date" value="<?php echo $line["out_date"]; ?>" class="input" size="10" style="width:100px"> <font color="gray"></font></span>
		</td>
	</tr>

	<tr>
		<td class="left">备案号：</td>
		<td class="right"><input name="beian_num" value="<?php echo $line["beian_num"]; ?>" class="input" style="width:30%"> <font color="gray">不填则表示未备案</font></td>
	</tr>


	<script type="text/javascript">
	var auto_update = "<?php echo $line["auto_update"]; ?>"
	if (auto_update == 0) {
		byid("radio2").checked = true;
		byid("write_out_date").style.display = "";
	} else {
		byid("radio1").checked = true;
	}

	function show_hide_write(value) {
		byid("write_out_date").style.display = value > 0 ? "none" : "";
	}
	</script>

<?php } ?>

	<tr>
		<td class="left">优先度：</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" size="10" style="width:100px"> <font color="gray">默认为0，大的优先</font></td>
	</tr>
	<tr>
		<td class="left">备注：</td>
		<td class="right"><input name="memo" value="<?php echo $line["memo"]; ?>" class="input" size="10" style="width:50%"> <font color="gray">备注内容</font></td>
	</tr>
</table>
<?php if ($mode == "edit") { ?>
<input type="hidden" name="id" value="<?php echo $id; ?>">
<?php } ?>

<div class="button_line">
	<input type="submit" class="submit" value="提交资料">
</div>

</form>

</body>
</html>