<?php
// --------------------------------------------------------
// - 功能说明 : 回访统计报表 - 明细
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-07-09
// --------------------------------------------------------
require "lib/set_env.php";

$huifang_time_length = strlen("2016-06-13 10:49"); //时间长度，用于截取
$edit_log_time_length = strlen("2016-06-12 18:25:18");

// 参数处理:
$kf_name = trim(mb_convert_encoding($_GET["j_kf_name"], "gbk", "UTF-8"));
$month = $_GET["month"];
$max_day = get_month_days($month);

$month_begin = strtotime($month."-01");
$month_end = strtotime("+1 month", $month_begin) - 1;

$t_from = strtotime("-2 month", $month_begin);
$t_end = $month_end;

$huifang_num = array(); //回访次数
$gaiyue_num = array(); //改约次数


$list = $db->query("select id, name, huifang, edit_log, status from patient_{$hid} where order_date>=$t_from and order_date<=$t_end and concat(huifang, edit_log) like '% {$kf_name}%'");

foreach ($list as $li) {
	$huifang_arr = wee_log_parse($li["huifang"], $huifang_time_length);
	$_d1 = $_d2 = array();
	foreach ($huifang_arr as $t => $c) {
		if ($t >= $month_begin && $t <= $month_end && substr($c, 0, strlen($kf_name)) == $kf_name) {
			$_day = date("j", $t);
			if (!in_array($_day, $_d1)) {
				$huifang_num[$_day] ++;
				$huifang_detail[$_day][] = "[".$li["name"]."] ".date("H:i", $t)." ".$c;
				$_d1[] = $_day;
			}
		}
	}

	$log_arr = wee_log_parse($li["edit_log"], $edit_log_time_length);
	$flag_string = "预约时间由";
	foreach ($log_arr as $t => $c) {
		if ($t >= $month_begin && $t <= $month_end && substr($c, 0, strlen($kf_name)) == $kf_name && substr_count($c, $flag_string) > 0) {
			$_day = date("j", $t);
			if (!in_array($_day, $_d2)) {
				$gaiyue_num[$_day] ++;
				if ($li["status"] == 1) {
					$gaiyue_jiuzhen_num[$_day] ++;
				}
				$_d2[] = $_day;
			}
		}
	}
}


// 到诊人数统计:
$_arr = $db->query("select order_date from patient_{$hid} where order_date>=$month_begin and order_date<=$month_end and status=1 and author='$kf_name'", "", "order_date");
foreach ($_arr as $t) {
	$dangri_daozhen_num[date("j", $t)] ++;
}


function wee_log_parse($str, $time_length) {
	$str = trim(str_replace("\r", "", $str));
	$arr = explode("\n", $str);
	$return = array();
	foreach ($arr as $v) {
		if (trim($v) == '') continue;
		$t = substr($v, 0, $time_length);
		$t2 = @strtotime($t);
		$c2 = trim(substr($v, $time_length + 1));
		if ($t2 > 0 && $c2 != '') {
			$return[$t2] = $c2;
		}
	}
	return $return;
}


?>
<html>
<head>
<title>回访统计报表 - <?php echo $kf_name; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
* {font-family:"微软雅黑"; }
.list {border:2px solid silver; }
.head {color:#bf0060 !important; border:1px solid silver !important; background:#e1e7ec !important; }
.item {text-align:center; padding:4px !important; border-top:1px solid silver !important; border-bottom:1px solid silver !important; }
.line_huizong td {color:red; }
.report_tips {padding:20px 0 20px 0; text-align:center; font-size:16px; font-family:"微软雅黑"; }
.condition_set {text-align:right; }
</style>
<script type="text/javascript">
var month = "<?php echo $month; ?>";

function w_alert(str) {
	alert(str.replace(new RegExp("#","gm"), "\n"));
}

</script>
</head>

<body style="padding:10px 20px;">

<div class="report_tips"><?php echo $hinfo["name"]; ?>　回访统计报表　<?php echo $kf_name; ?>　<?php echo $month; ?> </div>

<table class="list" width="100%">
	<tr>
		<th class="head">日期</th>
		<th class="head">回访人数</th>
		<th class="head">改约人数</th>
		<th class="head">改约就诊人数</th>
		<th class="head">当日到诊人数</th>
	</tr>

<?php for ($day = 1; $day <= $max_day; $day ++) { ?>
	<tr>
		<td class="item"><?php echo $day; ?></td>
		<td class="item"><a href="javascript:;" title="点击查看详情" onclick="w_alert('<?php echo implode("#", $huifang_detail[$day]); ?>');"><?php echo $huifang_num[$day]; ?></a></td>
		<td class="item"><?php echo $gaiyue_num[$day]; ?></td>
		<td class="item"><?php echo $gaiyue_jiuzhen_num[$day]; ?></td>
		<td class="item"><?php echo $dangri_daozhen_num[$day]; ?></td>
	</tr>
<?php } ?>

	<tr class="line_huizong">
		<td class="item">汇总</td>
		<td class="item"><?php echo @array_sum($huifang_num); ?></td>
		<td class="item"><?php echo @array_sum($gaiyue_num); ?></td>
		<td class="item"><?php echo @array_sum($gaiyue_jiuzhen_num); ?></td>
		<td class="item"><?php echo @array_sum($dangri_daozhen_num); ?></td>
	</tr>

</table>


<br>
<br>

</body>
</html>