<?php
// --------------------------------------------------------
// - 功能说明 : 报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-25 15:45
// --------------------------------------------------------
require "lib/set_env.php";
check_power('', $pinfo) or exit("没有打开权限...");
$hid = $user_hospital_id;

if ($hid == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$table = "patient_".$hid;

// 医院名称:
$h_name = $db->query("select name from hospital where id=$hid limit 1", "1", "name");

// 时间定义:
$today_begin = mktime(0,0,0); //今天开始
$today_end = $today_begin + 24*3600 - 1; //今天结束
$yesterday_begin = $today_begin - 24*3600; //昨天开始
$yesterday_end = $today_begin - 1; //昨天结束
$thismonth_begin = mktime(0,0,0,date("m"),1); //本月开始
$thismonth_end = strtotime("+1 month", $thismonth_begin) - 1; //本月开始
$lastmonth_begin = strtotime("-1 month", $thismonth_begin); //上月开始
$lastmonth_end = $thismonth_begin - 1; //上月开始


$date_array = array(
	"今日" => array($today_begin, $today_end),
	"昨日" => array($yesterday_begin, $yesterday_end),
	"本月" => array($thismonth_begin, $thismonth_end),
	"上月" => array($lastmonth_begin, $lastmonth_end),
);

$tf = "order_date";

$kefu = array();
// 所有网络客服:
$kefu[2] = $db->query("select distinct author from $table where part_id=2 and $tf>=$lastmonth_begin and $tf<=$thismonth_end order by author", "", "author");

// 所有电话客服:
$kefu[3] = $db->query("select distinct author from $table where part_id=3 and $tf>=$lastmonth_begin and $tf<=$thismonth_end order by author", "", "author");

$data = array();
foreach ($kefu as $ptid => $kfs) {
	foreach ($kfs as $kf) {
		foreach ($date_array as $tname => $t) {
			$b = $t[0];
			$e = $t[1];

			// 预计总到院:
			$data[$ptid][$kf][$tname]["all"] = $d1 = $db->query("select count(*) as c from $table where part_id=$ptid and author='$kf' and $tf>=$b and $tf<=$e", 1, "c");
			// 已到:
			$data[$ptid][$kf][$tname]["come"] = $d2 = $db->query("select count(*) as c from $table where part_id=$ptid and author='$kf' and $tf>=$b and $tf<=$e and status=1", 1, "c");
			// 未到:
			$data[$ptid][$kf][$tname]["leave"] = $d1 - $d2;
		}
	}
}


?>
<html>
<head>
<title>数据报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.red {color:#bf0060 !important; }

.report_tips {padding:20px 0 20px 0; text-align:center; font-size:16px; font-family:"微软雅黑"; }

.list {border:2px solid silver !important; }
.head {border:0 !important; background:#e1e7ec !important; }
.item {border-top:1px solid #e2e6e7 !important; border-bottom:1px solid #e2e6e7 !important; text-align:center; padding:8px 3px !important; }

.hl {border-left:1px solid silver !important; }
.hr {border-right:1px solid silver !important; }
.ht {border-top:1px solid silver !important; }
.hb {border-bottom:1px solid silver !important; }

.huizong {font-weight:bold; color:#ff8040; background:#eff2f5 !important; padding:5px; text-align:center; }
</style>
</head>

<body>

<?php
if ($debug_mode || in_array("web", $user_data_power)) {
?>

<div class="report_tips"><?php echo $h_name; ?> 网络咨询预约情况</div>

<table class="list" width="100%">
	<tr>
		<th class="head hb"></th>
		<th class="head hl hb red" colspan="3">今日</th>
		<th class="head hl hb red" colspan="3">昨日</th>
		<th class="head hl hb red" colspan="3">本月</th>
		<th class="head hl hb red" colspan="3">上月</th>
	</tr>

	<tr>
		<th class="head hb">客服</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>
	</tr>

<?php foreach ($data[2] as $kf => $arr) { ?>

	<tr>
		<td class="item"><?php echo $kf; ?></td>

		<td class="item hl"><?php echo $arr["今日"]["all"]; ?></td>
		<td class="item"><?php echo $arr["今日"]["come"]; ?></td>
		<td class="item"><?php echo $arr["今日"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["昨日"]["all"]; ?></td>
		<td class="item"><?php echo $arr["昨日"]["come"]; ?></td>
		<td class="item"><?php echo $arr["昨日"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["本月"]["all"]; ?></td>
		<td class="item"><?php echo $arr["本月"]["come"]; ?></td>
		<td class="item"><?php echo $arr["本月"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["上月"]["all"]; ?></td>
		<td class="item"><?php echo $arr["上月"]["come"]; ?></td>
		<td class="item"><?php echo $arr["上月"]["leave"]; ?></td>
	</tr>

<?php } ?>

</table>

<?php } ?>


<!-- 电话组 -->

<?php
if ($debug_mode || in_array("tel", $user_data_power)) {
?>
<div class="report_tips" style="margin-top:20px;"><?php echo $h_name; ?> 电话咨询预约情况</div>

<table class="list" width="100%">
	<tr>
		<th class="head hb"></th>
		<th class="head hl hb red" colspan="3">今日</th>
		<th class="head hl hb red" colspan="3">昨日</th>
		<th class="head hl hb red" colspan="3">本月</th>
		<th class="head hl hb red" colspan="3">上月</th>
	</tr>

	<tr>
		<th class="head hb">客服</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>

		<th class="head hl hb">总共</th>
		<th class="head hb">已到</th>
		<th class="head hb">未到</th>
	</tr>

<?php foreach ($data[3] as $kf => $arr) { ?>

	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td class="item"><?php echo $kf; ?></td>

		<td class="item hl"><?php echo $arr["今日"]["all"]; ?></td>
		<td class="item"><?php echo $arr["今日"]["come"]; ?></td>
		<td class="item"><?php echo $arr["今日"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["昨日"]["all"]; ?></td>
		<td class="item"><?php echo $arr["昨日"]["come"]; ?></td>
		<td class="item"><?php echo $arr["昨日"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["本月"]["all"]; ?></td>
		<td class="item"><?php echo $arr["本月"]["come"]; ?></td>
		<td class="item"><?php echo $arr["本月"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["上月"]["all"]; ?></td>
		<td class="item"><?php echo $arr["上月"]["come"]; ?></td>
		<td class="item"><?php echo $arr["上月"]["leave"]; ?></td>
	</tr>

<?php } ?>

</table>

<?php } ?>

<br>
<br>
<center>备注：上述数据，由患者预约到院时间进行统计（不是资料添加时间）<br></center>
<br>

</body>
</html>