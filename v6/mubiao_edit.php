<?php
// --------------------------------------------------------
// - 功能说明 : 设置到院人数目标
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-12-20
// --------------------------------------------------------
require "lib/set_env.php";
$table = "mubiao";

$hid = intval($_REQUEST["hid"]);
if (empty($hid)) {
	exit_html("参数错误");
}
$mode = "edit";
$hname = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

if ($mode == "edit") {
	check_power("e", $pinfo, $pagepower) or exit("没有修改权限");
}

if ($_POST) {
	ob_start();
	$to_update = array();
	foreach ($_POST["mubiao"] as $m => $num) {
		$m = intval($m);
		$num = intval($num);
		$line = $db->query("select * from $table where hid=$hid and month=$m limit 1", 1);
		if ($line["id"] > 0) {
			// 修改模式
			if ($line["num"] != $num) { //是否修改
				// 记录日志
				$log_str = $line["logs"].date("Y-m-d H:i:s ").$realname." 将来诊目标由“".$line["num"]."”修改为“".$num."”\r\n";
				$log_str = addslashes($log_str);
				$db->query("update $table set num=$num, logs='$log_str' where hid=$hid and month=$m limit 1");
				$to_update["data_".$hid."_".$m] = $num;
			}
		} else {
			// 没有记录，新增
			if ($num > 0) {
				$r = array();
				$r["hid"] = $hid;
				$r["hname"] = $hname;
				$r["month"] = $m;
				$r["num"] = $num;
				$r["uid"] = $uid;
				$r["uname"] = $realname;
				$r["addtime"] = time();

				$sql_data = $db->sqljoin($r);
				$db->query("insert into $table set $sql_data ");
				$to_update["data_".$hid."_".$m] = $num;
			}
		}
	}
	$error_str = ob_get_clean();

	if ($error_str != '') {
		echo "资料提交过程出现错误，请检查：".$error_str;
	} else {
		if (count($to_update) > 0) {
			foreach ($to_update as $data_id => $data_value) {
				echo '<script> parent.document.frames["sys_frame"].document.getElementById("'.$data_id.'").innerHTML = "'.$data_value.'"; </script>';
			}
		}
		echo '<script> parent.msg_box("资料提交成功", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	}
	exit;
}


// 最近6个月的目标
$m_arr = array();
$m_arr[] = date("Ym", strtotime("+1 month")); //下个月
$m_arr[] = date("Ym"); //本月
for($i = 1; $i <= 4; $i++) {
	$m = date("Ym", strtotime("-".$i." month"));
	$m_arr[] = $m;
}
asort($m_arr);


$mubiao = array();
if ($mode == "edit") {
	foreach ($m_arr as $m) {
		$mubiao[$m] = $db->query("select num from $table where hid=$hid and month=$m limit 1", 1, "num");
	}
}
$title = "来诊目标设置";

function int_month_to_month($m) {
	if (strlen($m) == 6) {
		return substr($m, 0, 4)."-".substr($m, 4, 2);
	}
	return $m;
}

?>
<html>
<head>
<title><?php echo $title." - ".$hname; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function check_data(f) {
	return true;
}
</script>
</head>

<body>
<div class="description">
	<div class="d_title"><b>提示：</b>输入各月来诊目标。</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
	<table width="100%" class="edit">
		<tr>
			<td colspan="2" class="head"></td>
		</tr>
<?php foreach ($m_arr as $m) { ?>
		<tr>
			<td class="left" style="width:40%"><?php echo int_month_to_month($m); ?>来诊目标：</td>
			<td class="right" style="width:60%"><input name="mubiao[<?php echo $m; ?>]" value="<?php echo $mubiao[$m]; ?>" class="input" style="width:200px"></td>
		</tr>
<?php } ?>
	</table>
	<input type="hidden" name="hid" value="<?php echo $hid; ?>">
	<div class="button_line">
		<input type="submit" class="submit" value="提交资料">
	</div>
</form>

</body>
</html>