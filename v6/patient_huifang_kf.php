<?php
// --------------------------------------------------------
// - 功能说明 : 设置回访客服
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-09-15
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}


$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("参数错误.");
}
$line = $db->query_first("select * from $table where id='$id' limit 1");

// 所有可用回访客服:
// 部门id = 12 且分配了这家医院的:
$huifang_kf_arr = $db->query("select id,realname from sys_admin where part_id=12 and concat(',',hospitals,',') like '%,{$hid},%'", "id", "realname");

if ($_POST) {
	$p = $_POST;
	$r = array();
	$save_field = explode(" ", "huifang_kf");
	foreach ($save_field as $v) {
		if ($v && isset($p[$v]) && $p[$v] != $line[$v]) {
			$r[$v] = $p[$v];
		}
	}
	// 字段修改记录:
	if (count($r) > 0) {
		$logs = patient_modify_log($r, $line);
		if ($logs) {
			$r["edit_log"] = $logs;
		}
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
		}
		if ($rs) {
			user_op_log("为病人“".$line["name"]."”设置回访客服“".$_POST["huifang_kf"]."”");
			$str = "资料提交成功！";
		} else {
			echo "提交出错，请联系开发人员：<br>".$db->sql."<br>";
			exit;
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



// page begin ----------------------------------------------------
?>
<html>
<head>
<title><?php echo $line["name"]; ?> - 设置回访客服</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.left {text-align:right; }
.right {padding:4px 0px; }
</style>
<script language="javascript">
function check_data(oForm) {
	return true;
}
</script>
</head>

<body oncontextmenu="return false">
<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" style="margin-top:10px;">
	<tr>
		<td class="left" width="70">回访客服：</td>
		<td class="right">
			<select style="width:200px;" name="huifang_kf" class="combo" onchange="if (this.value != '') this.form.submit();">
				<option value="" style="color:gray">-请指定回访人员-</option>
				<?php echo list_option($huifang_kf_arr, '_value_', '_value_', $line["huifang_kf"]); ?>
			</select>
		</td>
	</tr>
</table>
<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
<div class="button_line"><input type="submit" class="buttonb" value="提交资料"></div>
</form>
</body>
</html>