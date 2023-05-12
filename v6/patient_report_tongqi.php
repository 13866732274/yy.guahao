<?php
// --------------------------------------------------------
// - 功能说明 : 同期数据报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-08-12
// --------------------------------------------------------
require "lib/set_env.php";
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

check_power('', $pinfo) or exit("没有打开权限...");

$used_hid = implode(",", $hospital_ids);
if ($used_hid == '') $used_hid = '0';

// 所有医院名称:
$h_id_name = $db->query("select id,name from hospital where ishide=0 and id in ($used_hid) order by id asc", "id", "name");

// 时间定义:
$day = date("d");
$his = date("H:i:s");
$month_arr = $month_b_e = array();
for ($i = 0; $i < 6; $i++) {
	$month_arr[$i] = $t = date("Y-m", strtotime("-".$i." month", time()));
	$m_begin =  strtotime($t."-1 0:0:0");
	$m_end = strtotime($t."-".$day." ".$his);
	// 判断日有没有超标(当一个月没有31日的时候 strtotime() 函数会计算为下个月的1日，导致出问题)
	if (date("Y-m-d", $m_end) != ($t."-".$day)) {
		$m_end = strtotime("+1 month") - 1; //结束时间重新定为一个月的最后一天的23:59:59
	}
	$month_b_e[$i] = array($m_begin, $m_end);
}

asort($month_arr);

$type_arr = array(0 => "整个医院", 2 => "网络", 3 => "电话");

$sqladd = '';
if (array_key_exists($_GET["type"], $type_arr)) {
	if ($_GET["type"] > 0) {
		$sqladd = "and part_id=".$_GET["type"];
	}
}


?>
<html>
<head>
<title>同期数据报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.clear {clear:both; font-size:0; line-height:0; }

.red {color:red !important; }
.report_tips {padding:20px 0 20px 0; text-align:center; font-size:14px; font-weight:bold;  }
.list {border:2px solid #6AB5FF !important; }
.head {}
.item {text-align:center; padding:6px 3px 4px 3px !important; }
.hl {border-left:2px solid #A4D1FF !important; }
.hr {border-right:2px solid #A4D1FF !important; }
.ht {border-top:2px solid #A4D1FF !important; }
.hb {border-bottom:2px solid #A4D1FF !important; }

/* 进度条 */
#progress_bar {margin:20px auto 20px auto; }
#p_title {float:left; width:90px; text-align:left; padding-top:0px; }
#p_border {float:left; width:202px; height:12px; border:1px solid #408080; background:white; }
#p_cur {margin:1px; width:0px; height:10px; font-size:0; line-height:0; background-color:#FF8000; }
</style>

<script language="javascript">
// 更新进度条
// s: 0 - 100   到100时表示100%
function update_p(s) {
	s = s + 10;
	if (s < 0) s = 0;
	if (s > 100) s = 100;
	byid("p_cur").style.width = s*2+"px";
}
</script>

</head>

<body>

<!-- 进度条，完成后隐藏 -->
<div id="progress_bar" style="display:none;">
	<div id="p_title">&nbsp;数据查询中：</div>
	<div id="p_border"><div id="p_cur"></div></div>
	<div class="clear"></div>
</div>

<?php
echo '<script>byid("progress_bar").style.display = "block"; </script>'."\r\n";
flush();
ob_flush();
ob_end_flush();

// 查询数据:
$data_arr = array();
$index = 0;
foreach ($h_id_name as $k => $v) {
	$t = "patient_".$k;
	foreach ($month_b_e as $i => $be) {
		$b = $be[0];
		$e = $be[1];
		ob_start();
		$data_arr[$k][$i] = @$db->query("select count(*) as c from $t where order_date>=$b and order_date<$e and status=1 $sqladd", 1, "c");
		ob_end_clean();
	}

	$index++; //已处理的进度
	$progress = round(($index / count($h_id_name)) * 100, 1);
	echo '<script type="text/javascript">update_p('.$progress.');</script>'."\r\n";
	flush();
	ob_flush();
	ob_end_flush();
}

?>

<script type="text/javascript">
byid("progress_bar").style.display = "none";
</script>

<div style="text-align:center; padding:20px 0 5px 0;">
	类型：<input type="button" class="buttonb" value="整个医院" onclick="location='?'">&nbsp;
	<input type="button" class="buttonb" value="网络" onclick="location='?type=2'">&nbsp;
	<input type="button" class="buttonb" value="电话" onclick="location='?type=3'">&nbsp; (提醒：本页面查询数据量大，较占用服务器资源，请尽量减少刷新。)
</div>

<div class="report_tips">各月同期数据对比 (每月<?php echo $day; ?>日内到院数据) (<?php echo $type_arr[intval($_GET["type"])]; ?>)</div>

<table class="list" width="100%">
	<tr>
		<th class="head">医院</th>
<?php foreach ($month_arr as $i => $m) { ?>
		<th class="head"><?php echo $m; ?></th>
<?php } ?>
	</tr>

<?php
$sum = array();
foreach ($h_id_name as $_hid => $_hname) {
?>

	<tr>
		<td class="item"><?php echo $_hname; ?></td>

<?php
	foreach ($month_arr as $i => $m) {
		$sum[$i] += intval($data_arr[$_hid][$i]);
?>
		<td class="item"><?php echo @intval($data_arr[$_hid][$i]); ?></td>
<?php } ?>
	</tr>

<?php } ?>

	<tr>
		<td class="item">汇总</td>

<?php
	foreach ($month_arr as $i => $m) {
?>
		<td class="item"><?php echo @intval($sum[$i]); ?></td>
<?php } ?>
	</tr>

</table>

<br>
<br>

</body>
</html>