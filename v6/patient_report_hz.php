<?php
/* --------------------------------------------------------
// 说明: 图形报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2017-03-03
// ----------------------------------------------------- */
require "lib/set_env.php";
include "chart/FusionCharts_Gen.php";

if (!$debug_mode) {
	//exit("本模块正在开发测试中。");
}

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$s_name = $db->query("select * from hospital where id='$user_hospital_id' limit 1", 1, "sname");
$hid_name_arr = $db->query("select id, name from hospital where ishide=0 and sname='$s_name'", "id", "name");


// 查询选项:
$type_arr = array("month"=>"按月统计", "day"=>"按天统计");

$time_arr = array("order_date"=>"到院时间", "addtime"=>"添加时间");
$come_arr = array(1=>"已到", 2=>"未到");

$index_module_arr = $db->query("select * from index_module where hospital_id=0 and isshow>0 and if_dingzhi=1 order by sort desc", "id","name");

if ($_GET["op"] == "show_chart") {
	extract($_GET);

	$index_module = intval($index_module);

	// 构建 where:
	$where = array();

	if ($type == "month") {
		$tb = strtotime($_GET["month_begin"]."-01");
		$te = strtotime("+1 month", strtotime($_GET["month_end"]."-01")) - 1;
		$format = "%Y%m";
		$caption = "月";
	} else if ($type == "day") {
		$tb = strtotime($_GET["day_begin"]);
		$te = strtotime($_GET["day_end"]." 23:59:59");
		$format = "%Y%m%d";
		$caption = "日";
	}

	$time_field = array_key_exists($time, $time_arr) ? $time : "addtime";
	if ($tb > 0) {
		$where[] = $time_field.">=".$tb;
	}
	if ($te > 0) {
		$where[] = $time_field."<".$te;
	}

	if ($index_module > 0) {
		$m_arr = $db->query("select * from index_module where id=$index_module limit 1", 1);
		$condi = $m_arr["condition_code"];
		if ($condi != '') {
			$where[] = "(".$condi.")";
		}
	}

	if ($come > 0) {
		$where[] = $come == 1 ? "status=1" : "status=0";
	}

	// 处理数据:
	$sqlwhere = implode(" and ", $where);

	$list = array();
	foreach ($hid_name_arr as $_hid => $_hname) {
		$tmp = $db->query("select t,count(t) as c from (select id,part_id,$time_field,status,from_unixtime($time_field,'$format') as t from patient_{$_hid} where $sqlwhere) as tmp group by t", "t", "c");
		foreach ($tmp as $t => $c) $list[$t] += $c;
	}

	//echo "<pre>";
	//print_r($list);

	ksort($list);

	if ($type == "day") {
		$chart_width = 25 * count($list);
	} else {
		$chart_width = 60 * count($list);
	}
	if ($chart_width < 750) $chart_width = 750;


	// 统计图:
	$FC = new FusionCharts("Column2D",$chart_width,"200");
	$FC->setSWFPath("chart/");
	$FC->setChartParams("decimalPrecision=0; formatNumberScale=0; baseFontSize=10; baseFont=Arial; chartBottomMargin=0; outCnvBaseFontSize=12; hoverCapSepChar=$caption: " );

	$ymax = intval((@max($list) + 10) * 1.2);
	$FC->setChartParams("yAxisMaxValue={$ymax}");

	foreach ($list as $t => $c) {
		if ($type == "day") {
			$t = intval(substr($t, -2, 2));
		}
		$FC->addChartData($c, "name=".$t);
	}
}



function con($s) {
	$s = iconv("gbk", "utf-8", $s);
	return urlencode($s);
}
?>
<html>
<head>
<title>汇总趋势图</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src='chart/FusionCharts.js' language='javascript'></script>
<script src="lib/datejs/picker.js" language="javascript"></script>

<script type="text/javascript">
function show_hide_date(v) {
	byid("day_select").style.display = "none";
	byid("month_select").style.display = "none";
	if (v == "day") {
		byid("day_select").style.display = "";
	}
	if (v == "month") {
		byid("month_select").style.display = "";
	}
}

function check_form(f) {
	if (f.type.value == "month") {
		if (f.month_begin.value == '' || f.month_end.value == '') {
			alert("请设置开始和结束月份。"); return false;
		}
	} else if (f.type.value == "day") {
		if (f.day_begin.value == '' || f.day_end.value == '') {
			alert("请设置开始和结束日期。"); return false;
		}
	}
	return true;
}
</script>
</head>

<style type="text/css">
body {overflow-y:auto !important; }
#chart_zhu div {overflow-y:auto !important; }
</style>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">刷新</button></td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<table width="100%"><tr><td align="center">

<div>
	<center>当前医院：<b><?php echo $s_name." (".count($hid_name_arr)."个科室)"; ?></b></center>
</div>

<form action="" method="GET" onsubmit="return check_form(this)">
<div style="margin-top:40px; ">
	<b>报表显示: </b>
	<select name="type" class="combo" onchange="show_hide_date(this.value)">
		<option value="" style="color:gray">-类型-</option>
		<?php echo list_option($type_arr, "_key_", "_value_", noe($_GET["type"], "day")); ?>
	</select>
	<select name="time" class="combo">
		<option value="" style="color:gray">-时间类型-</option>
		<?php echo list_option($time_arr, "_key_", "_value_", noe($_GET["time"], "order_date")); ?>
	</select>
	<span id="day_select">
		<input name="day_begin" id="day_begin" class="input" style="width:80px" value="<?php echo $_GET["day_begin"]; ?>" onclick="picker({el:'day_begin',dateFmt:'yyyy-MM-dd'})"> ~ <input name="day_end" id="day_end" class="input" style="width:80px" value="<?php echo $_GET["day_end"]; ?>" onclick="picker({el:'day_end',dateFmt:'yyyy-MM-dd'})">
	</span>
	<span id="month_select" style="display:none;">
		<input name="month_begin" id="month_begin" class="input" style="width:80px" value="<?php echo $_GET["month_begin"]; ?>" onclick="picker({el:'month_begin',dateFmt:'yyyy-MM'})"> ~ <input name="month_end" id="month_end" class="input" style="width:80px" value="<?php echo $_GET["month_end"]; ?>" onclick="picker({el:'month_end',dateFmt:'yyyy-MM'})">
	</span>
	<select name="index_module" class="combo ml20">
		<option value="" style="color:gray">不限</option>
		<?php echo list_option($index_module_arr, "_key_", "_value_", $_GET["index_module"]); ?>
	</select>
	<select name="come" class="combo">
		<option value="" style="color:gray">不限</option>
		<?php echo list_option($come_arr, "_key_", "_value_", $_GET["come"]); ?>
	</select>
	<input type="submit" class="button" value="执行">
</div>
<input type="hidden" name="op" value="show_chart">
</form>

<script type="text/javascript">
show_hide_date('<?php echo noe($_GET["type"],"day"); ?>');
</script>

<br>
<br>

<?php if ($_GET) { ?>

	<div id="chart_zhu">
	<?php $FC->renderChart(); ?>
	</div>

<?php } else { ?>

<div>请设置查询条件后,点击执行显示图形报表</div>

<?php } ?>

</td>
</tr>
</table>

</body>
</html>