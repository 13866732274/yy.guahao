<?php
/*
// 说明: 按医院查看汇总
// 作者: 幽兰 (weelia@126.com)
// 时间: 2015-7-18
*/
require "lib/set_env.php";
$table = "count_web";

$hids = implode(",", $hospital_ids);
$s_arr = $db->query("select sname,count(sname) as c from hospital where ishide=0 and id in ($hids) group by sname order by sname asc", "sname", "c");
foreach ($s_arr as $sname => $count) {
	if ($count <= 1) unset($s_arr[$sname]);
}
$h_show_arr = array_keys($s_arr);
sort($h_show_arr);

// 查询每个医院的下属科室:
foreach ($s_arr as $sname => $count) {
	$h_arr[$sname] = $db->query("select id,depart as name from hospital where ishide=0 and id in ($hids) and sname='$sname' order by name asc", "id", "name");
}


if ($_GET["h_name"] == '') {
	$_GET["h_name"] = $h_show_arr[0];
}

// 要汇总的项目
$hid_str = @implode(",", array_keys($h_arr[$_GET["h_name"]]));
$xm_arr = $db->query("select id, hid, name from count_type where type='web' and ishide=0 and hid in ($hid_str) ");
//print_r($xm_arr);

$hid_to_xm_id = $hid_to_xm_name = array();
foreach ($xm_arr as $li) {
	$hid_to_xm_id[] = $li["id"];
	$hid_to_xm_name[] = $li["name"];
}
//print_r($hid_to_xm_id);
//print_r($hid_to_xm_name);

$h_select = array();
foreach ($h_count as $h => $c) {
	$h_select[$h] = $hid_name_arr[$h]." [".$c."]";
}

$month_select = array();
$t0 = mktime(0, 0, 0, date("m"), 1);
for ($i=0; $i<25; $i++) {
	$t = strtotime("-".$i." month", $t0);
	$month_select[date("Ym", $t)] = date("Y-m", $t);
}

if ($_GET["month"] == '') {
	$_GET["month"] = date("Ym");
}
$full_month = int_month_to_month($_GET["month"]);


// 当月有多少天数
$month_days = get_month_days($_GET["month"]);

$d_array = array();
for ($n = 1; $n <= $month_days; $n++) {
	$d_array[] = date("Ymd", strtotime($full_month."-".$n));
}
$date_from = $d_array[0];
$date_end = $d_array[count($d_array) - 1];

// 数据:
$f = explode(" ", "all_click click click_local click_other zero_talk ok_click ok_click_local ok_click_other talk talk_bendi talk_waidi talk_swt talk_tel talk_other orders orders_bendi orders_waidi orders_swt orders_tel orders_other come_all come_bendi come_waidi come come_tel come_other");
$fs = array();
foreach ($f as $v) {
	$fs[] = "sum(".$v.") as ".$v;
}
$f_str = implode(",", $fs);

$xm_ids = count($hid_to_xm_id) ? implode(",", $hid_to_xm_id) : '0';

$list = $db->query("select date, $f_str from count_web where type_id in ($xm_ids) and date>=$date_from and date<=$date_end group by date", "date");

// 计算数据:
foreach ($list as $date => $li) {
	$list[$date] = calc_per($li);
}

$sum = array();
foreach ($list as $li) {
	foreach ($f as $f2) {
		$sum[$f2] = floatval($sum[$f2]) + $li[$f2];
	}
}

$per = array();
$per_days = count($list);
foreach ($sum as $f => $v) {
	$per[$f] = round($v / $per_days, 1);
}


$sum = calc_per($sum);



// ----------------------  汇总形式2 ---------------------------------

$jintian_b = mktime(0,0,0); //今天的开始
$jintian_e = strtotime("+1 day", $jintian_b) - 1; //今天结束
$zuotian_b = strtotime("-1 day", $jintian_b); // 昨天
$qiantian_b = strtotime("-1 day", $zuotian_b); // 前天
$benyue_b = mktime(0,0,0,date("m"), 1); // 本月开始

$d = date("d");
if ($d > 2) {
	$d = $d - 2;
	$benyue_e = mktime(23,59,59,date("m"), $d);
} else {
	$benyue_e = 0;
}


$shangyue_e = $benyue_b - 1; // 上个月结束
$shangyue_b = strtotime("-1 month", $benyue_b) + 1; //上月开始
$tb_b = $shangyue_b;
if ($benyue_e > 0) {
	$tb_e = strtotime("-1 month", $benyue_e);
	if (date("d", $tb_e) != date("d", $benyue_e)) {
		$tb_e = $benyue_b - 1; //同比到上月的结束（防止本月31天，而上月没有31天的情况）
	}
} else {
	$tb_e = 0;
}


// 年度汇总:
$ty_b = mktime(0,0,0,1,1); //今年的开始
$ty_e = mktime(0,0,0,12,31); //今年的结束
$ly_b = mktime(0,0,0,1,1,date("Y")-1);


// 要处理的时间:
$time_array = array(
	"昨天" => array($zuotian_b, $jintian_b - 1),
	"前天" => array($qiantian_b, $zuotian_b - 1),
	"本月" => array($benyue_b, $benyue_e),
	"同比" => array($tb_b, $tb_e),
	"上月" => array($shangyue_b, $shangyue_e),
);


// 加入最近6个月的数据:
for ($i=2; $i<=7; $i++) {
	$mon = strtotime("-".$i." month", $benyue_b);
	$time_array[date("Y-m", $mon)] = array($mon, strtotime("+1 month", $mon) - 1);
}

$list2 = array();
foreach ($time_array as $tname => $tt) {

	$b = date("Ymd", $tt[0]);
	$e = date("Ymd", $tt[1]);

	//查询总医院汇总数据:
	$_tmp = $db->query("select $f_str from count_web where type_id in ($xm_ids) and date>=$b and date<=$e", 1);

	$_tmp = calc_per($_tmp);

	$list2[$tname] = $_tmp;
}




// --------------------- 函数 ------------------------

function calc_per($li) {
	// 咨询预约率:
	$li["per_1"] = @round($li["talk_swt"] / $li["click"] * 100, 1);
	// 预约就诊率:
	$li["per_2"] = @round($li["come"] / $li["orders_swt"] * 100, 1);
	// 咨询就诊率:
	$li["per_3"] = @round($li["come"] / $li["click"] * 100, 1);
	// 有效咨询率:
	$li["per_4"] = @round($li["ok_click"] / $li["click"] * 100, 1);
	// 有效预约率:
	$li["per_5"] = @round($li["talk_swt"] / $li["ok_click"] * 100, 1);
	// 总预约到诊率
	$li["per_6"] = @round($li["come_all"] / $li["orders"] * 100, 1);

	return $li;
}

function per_detect($num) {
	if ($num > 100) return "~";
	if ($num == 0) return "";
	return round($num, 1);
}

?>
<html>
<head>
<title>医院汇总统计</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.mt5 {margin-top:5px; }
.mt10 {margin-top:10px; }
.mt20 {margin-top:20px; }
.center {text-align:center; }

.main_title {font-weight:bold; font-size:15px;; }

.item {font-family:"Tahoma"; }
.list .tips {border:1px solid #d1dcfa !important; }

.list .bl {border-left:1px solid #d1dcfa !important; }
.list .br {border-right:1px solid #d1dcfa !important; }
.list .bt {border-top:1px solid #d1dcfa !important; }
.list .bb {border-bottom:1px solid #d1dcfa !important; }

.head {padding:6px 3px !important;}

.huizong {padding:4px; text-align:center; background-color:#e4e9eb; }
</style>
</head>

<body>
<div style="margin:10px auto; text-align:center;">
	<a href="count_web.php">[返回]</a>
	<form method="GET" style="display:inline; margin-left:20px;">
		<span class="data_tips">医院：</span>
		<select name="h_name" class="combo" onchange="this.form.submit()">
			<?php echo list_option($h_show_arr, "_value_", "_value_", $_GET["h_name"]); ?>
		</select>&nbsp;

		<span class="data_tips">月份：</span>
		<select name="month" class="combo" onchange="this.form.submit()">
			<?php echo list_option($month_select, "_key_", "_value_", $_GET["month"]); ?>
		</select>
	</form>
	<span style="margin-left:20px;">包含科室：<?php echo implode("、", $h_arr[$_GET["h_name"]]); ?></span>
</div>


<!-- 浮动表头 注意：此技术需要指定每个单元格的宽度否则上下表格可能不对齐 -->
<style type="text/css">
.small_font {font-size:11px !important; color:#aaaaaa; display:block; font-weight:normal; }
</style>
<?php $tds_count = 32; $w = round(100 / $tds_count, 3); ?>
<style type="text/css">
.list td {padding-left:1px !important; padding-right:1px !important; }
</style>
<div id="to_float">
	<table id="data_list_float_head" style="display:none;border-bottom:1px;" width="100%" align="center" cellpadding="0" cellspacing="0" class="list">
		<tr>
			<td width="<?php echo $w."%"; ?>" class="head br no_b" align="center" rowspan="2">日期</td>
			<td width="<?php echo 7*$w."%"; ?>" class="head bl" align="center" colspan="7" style="color:red">商务通点击量</td>
			<td width="<?php echo 6*$w."%"; ?>" class="head bl" align="center" colspan="6" style="color:red">预约</td>
			<td width="<?php echo 6*$w."%"; ?>" class="head bl " align="center" colspan="6" style="color:red">预到</td>
			<td width="<?php echo 6*$w."%"; ?>" class="head bl" align="center" colspan="6" style="color:red">到院</td>
			<td width="<?php echo 5*$w."%"; ?>" class="head bl" align="center" colspan="5" style="color:red">商务通比率%</td>
			<td width="<?php echo $w."%"; ?>" class="head bl no_b" align="center" rowspan="2">总预约就诊率%</td>
		</tr>

		<tr>
			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">所有点击<font class="small_font">(1条起)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center" style="color:red">总点击<font class="small_font">(1条对1条)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center" style="color:red">有效<font class="small_font">(5条对5条)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总预约</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总预到</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总到院</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td class="head no_b bl" align="center" style="color:red">咨询预约率%</td>
			<td class="head no_b" align="center" style="color:red">预约就诊率%</td>
			<td class="head no_b" align="center" style="color:red">咨询就诊率%</td>
			<td class="head no_b" align="center" style="color:red">有效咨询率%</td>
			<td class="head no_b" align="center" style="color:red">有效预约率%</td>
		</tr>
	</table>
</div>

<table id="data_list" width="100%" align="center" cellpadding="0" cellspacing="0" class="list mt20">
		<tr>
			<td width="<?php echo $w."%"; ?>" class="head br no_b" align="center" rowspan="2">日期</td>
			<td width="<?php echo 7*$w."%"; ?>" class="head bl" align="center" colspan="7" style="color:red">商务通点击量</td>
			<td width="<?php echo 6*$w."%"; ?>" class="head bl" align="center" colspan="6" style="color:red">预约</td>
			<td width="<?php echo 6*$w."%"; ?>" class="head bl " align="center" colspan="6" style="color:red">预到</td>
			<td width="<?php echo 6*$w."%"; ?>" class="head bl" align="center" colspan="6" style="color:red">到院</td>
			<td width="<?php echo 5*$w."%"; ?>" class="head bl" align="center" colspan="5" style="color:red">商务通比率%</td>
			<td width="<?php echo $w."%"; ?>" class="head bl no_b" align="center" rowspan="2">总预约就诊率%</td>
		</tr>

		<tr>
			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">所有点击<font class="small_font">(1条起)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center" style="color:red">总点击<font class="small_font">(1条对1条)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center" style="color:red">有效<font class="small_font">(5条对5条)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总预约</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总预到</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总到院</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td class="head no_b bl" align="center" style="color:red">咨询预约率%</td>
			<td class="head no_b" align="center" style="color:red">预约就诊率%</td>
			<td class="head no_b" align="center" style="color:red">咨询就诊率%</td>
			<td class="head no_b" align="center" style="color:red">有效咨询率%</td>
			<td class="head no_b" align="center" style="color:red">有效预约率%</td>
		</tr>

<?php
foreach ($d_array as $date) {
	$n = date("j", strtotime(int_date_to_date($date)));
	$li = $list[$date];
	if (!is_array($li)) {
		$li = array();
	}

	// 汇总情况下，没有数据的日期不显示 @ 2014-5-8
	if ($li["click"] + $li["ok_click"] + $li["talk"] + $li["orders"] + $li["come_all"] == 0) {
		continue;
	}

?>
	<tr>
		<td class="item" align="center"><?php echo $n; ?></td>

		<td class="item bl" align="center"><?php echo $li["all_click"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["click"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_other"]; ?></td>

		<td class="item bl" align="center"><?php echo $li["talk"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_swt"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_tel"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_other"]; ?></td>

		<td class="item bl" align="center"><?php echo $li["orders"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_swt"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_tel"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_other"]; ?></td>

		<td class="item bl" align="center"><?php echo $li["come_all"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $li["come"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_tel"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo per_detect($li["per_1"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo per_detect($li["per_2"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo per_detect($li["per_3"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo per_detect($li["per_4"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo per_detect($li["per_5"]); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo per_detect($li["per_6"]); ?></td>

	</tr>

<?php } ?>

	<tr>
		<td colspan="<?php echo $tds_count; ?>" class="huizong">数据汇总</td>
	</tr>

	<tr>
		<td class="item" align="center">汇总</td>

		<td class="item bl" align="center"><?php echo $sum["all_click"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum["click"]; ?></td>
		<td class="item" align="center"><?php echo $sum["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $sum["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum["ok_click_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum["talk"]; ?></td>
		<td class="item" align="center"><?php echo $sum["talk_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $sum["talk_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $sum["talk_swt"]; ?></td>
		<td class="item" align="center"><?php echo $sum["talk_tel"]; ?></td>
		<td class="item" align="center"><?php echo $sum["talk_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum["orders"]; ?></td>
		<td class="item" align="center"><?php echo $sum["orders_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $sum["orders_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $sum["orders_swt"]; ?></td>
		<td class="item" align="center"><?php echo $sum["orders_tel"]; ?></td>
		<td class="item" align="center"><?php echo $sum["orders_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum["come_all"]; ?></td>
		<td class="item" align="center"><?php echo $sum["come_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $sum["come_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $sum["come"]; ?></td>
		<td class="item" align="center"><?php echo $sum["come_tel"]; ?></td>
		<td class="item" align="center"><?php echo $sum["come_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo @per_detect($sum["per_1"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @per_detect($sum["per_2"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @per_detect($sum["per_3"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @per_detect($sum["per_4"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @per_detect($sum["per_5"]); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo @per_detect($sum["per_6"]); ?></td>
	</tr>

	<tr>
		<td class="item" align="center">日均</td>

		<td class="item bl" align="center"><?php echo $per["all_click"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $per["click"]; ?></td>
		<td class="item" align="center"><?php echo $per["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $per["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $per["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $per["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $per["ok_click_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $per["talk"]; ?></td>
		<td class="item" align="center"><?php echo $per["talk_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $per["talk_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $per["talk_swt"]; ?></td>
		<td class="item" align="center"><?php echo $per["talk_tel"]; ?></td>
		<td class="item" align="center"><?php echo $per["talk_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $per["orders"]; ?></td>
		<td class="item" align="center"><?php echo $per["orders_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $per["orders_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $per["orders_swt"]; ?></td>
		<td class="item" align="center"><?php echo $per["orders_tel"]; ?></td>
		<td class="item" align="center"><?php echo $per["orders_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $per["come_all"]; ?></td>
		<td class="item" align="center"><?php echo $per["come_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $per["come_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $per["come"]; ?></td>
		<td class="item" align="center"><?php echo $per["come_tel"]; ?></td>
		<td class="item" align="center"><?php echo $per["come_other"]; ?></td>

		<td class="item bl" align="center" style="color:red">-</td>
		<td class="item" align="center" style="color:red">-</td>
		<td class="item" align="center" style="color:red">-</td>
		<td class="item" align="center" style="color:red">-</td>
		<td class="item" align="center" style="color:red">-</td>

		<td class="item bl" align="center" style="color:red">-</td>
	</tr>

</table>

<br>
<br>
<br>
<br>

<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center">日期</td>
		<td class="head" align="center">所有点击</td>
		<td class="head" align="center" style="color:red">总点击</td>
		<td class="head" align="center">本地</td>
		<td class="head" align="center">外地</td>
		<td class="head" align="center" style="color:red">总有效</td>
		<td class="head" align="center">本地</td>
		<td class="head" align="center">外地</td>

		<td class="head" align="center" style="color:red">当天约</td>
		<td class="head" align="center" style="color:red">预计到院</td>
		<td class="head" align="center" style="color:red">实际到院</td>

		<td class="head" align="center" style="color:red">咨询预约率</td>
		<td class="head" align="center" style="color:red">预约就诊率</td>
		<td class="head" align="center" style="color:red">咨询就诊率</td>
		<td class="head" align="center" style="color:red">有效咨询率</td>
		<td class="head" align="center" style="color:red">有效预约率</td>
	</tr>

<?php
foreach ($time_array as $tname => $tt) {
	$li = $list2[$tname];
?>
	<tr>
		<td class="item" align="center"><?php echo $tname; ?></td>
		<td class="item" align="center"><?php echo $li["all_click"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["click"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["talk"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["orders"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["come"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_1"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_2"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_3"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_4"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_5"]); ?>%</td>
	</tr>

<?php if ($tname == "上月") { ?>
	<tr>
		<td colspan="16" class="huizong"><b>(以下为前6个月数据)</b></td>
	</tr>
<?php } ?>


<?php } ?>

</table>

<br>
<br>

<center>包含统计项：<?php echo implode("、", $hid_to_xm_name); ?></center>

<br>
<br>

<script type="text/javascript">
function scroll_table() {
	var s_top = document.body.scrollTop;
	var top = byid(float_table).offsetTop;
	var top_head = byid(float_table+"_float_head").offsetHeight;

	if (s_top >= (0 + top + top_head)) {
		var o = byid(float_table+"_float_head");
		o.style.display = "";
		o.style.position = "absolute";
		o.style.top = s_top;
		o.style.width = byid(float_table).offsetWidth;
	} else {
		byid(float_table+"_float_head").style.display = "none";
	}
};

function make_float(table_id) {
	window.onscroll = scroll_table;
}

var float_table = "data_list";
make_float(float_table);
</script>


</body>
</html>
