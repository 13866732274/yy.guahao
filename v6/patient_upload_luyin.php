<?php
// --------------------------------------------------------
// - 功能说明 : 设置轨迹
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2013-8-11
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}


$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("参数错误");
}
$line = $db->query("select * from $table where id='$id' limit 1", 1);

if ($_POST) {

	include_once "lib/function.upload.php";
	$uppath = ROOT.'luyin/'.$hid."/";
	@mkdir(dirname($uppath));
	$upfile = upfile("luyin", $uppath, 8000*1024, array('wav', 'mp3'));
	$uploaded_url = '';
	if ($upfile) {
		$uploaded_url = "/luyin/".$hid."/".$upfile;
	}

	$r = array();
	if ($uploaded_url != '') {
		$r["luyin_file"] = $uploaded_url;
		$r["upload_uid"] = $uid;
		$r["upload_time"] = time();
		if ($_POST["set_status"] == "come_not_set" && $line["status"] != 1) {
			$r["status"] = "-2";
		}
	} else {
		exit("录音文件未上传成功，请重新尝试上传。");
	}

	if (count($r) > 0) {
		$sqldata = $db->sqljoin($r);
		$sql = "update $table set $sqldata where id='$id' limit 1";
		ob_start();
		$rs = $db->query($sql);
		$error = ob_get_clean();
		if ($error) {
			echo "提交出错，请联系开发人员：<br>".$error;
			exit;
		} else {
			$str = "资料提交成功！";
		}
	} else {
		$str = "资料无变动";
	}
	echo '<script type="text/javascript">'."\r\n";
	echo 'parent.msg_box("'.$str.'");'."\r\n";
	echo 'parent.close_divs();'."\r\n";
	echo '</script>'."\r\n";
	exit;
}


$to_set_status_arr = array();

if ($line["status"] != 1) {
	$to_set_status_arr = array(
		"come_not_set" => "已到未勾",
	);
}


// page begin ----------------------------------------------------
?>
<html>
<head>
<title><?php echo $line["name"]; ?> - 上传录音</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.left {text-align:right; }
.right {padding:4px 0px; }
</style>
<script language="javascript">
function check_data(oForm) {
	if (oForm.luyin.value == '') {
		alert("请选择录音文件！");
		return false;
	}
	if (!confirm("上传录音文件可能需要较长时间，在没有上传完之前，请不要进行其它操作。确认上传吗？")) {
		return false;
	}
	show_upload_div();
	return true;
}

function show_upload_div() {
	byid("uploading").style.display = "block";
}
</script>
</head>

<body oncontextmenu="return false">
<form name="mainform" action="" method="POST" onsubmit="return check_data(this)" enctype="multipart/form-data">
<table width="100%" style="margin-top:10px;">
	<tr>
		<td class="left" style="width:80px; padding-top:6px;" valign="top">录音文件：</td>
		<td class="right">
			<input type="file" name="luyin" class="file" size="20"><br>
			<font color="gray">支持格式：wav或mp3  文件最大不超过5M</font>
		</td>
	</tr>
	<tr>
		<td class="left">状态设置：</td>
		<td class="right">
			<select name="set_status" class="combo">
				<option value="" style="color:gray">-不作修改-</option>
				<?php echo list_option($to_set_status_arr, "_key_", "_value_", ""); ?>
			</select>
		</td>
	</tr>
</table>
<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
<div class="button_line">
	<input type="submit" class="buttonb" value="上传文件">
</div>
</form>


<div id="uploading" style="display:none; position:absolute; left:0; top:0; width:340px; height:160px; text-align:center; line-height:160px; background:#ffffff; "><div style="margin-top:60px;"><img src="image/loading.gif" align="absmiddle"> 上传中，请耐心等待完成...</div></div>

<!-- <a href="#" onclick="show_upload_div(); return false;">测一下</a> -->

</body>
</html>