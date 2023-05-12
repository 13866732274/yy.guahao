<?php
/* --------------------------------------------------------
// 说明: 图形报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2010-01-14 13:07
// ----------------------------------------------------- */
require "lib/set_env.php";
include "chart/FusionCharts_Gen.php";

check_power('', $pinfo) or exit("没有打开权限...");

if ($hid == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}


$table = "patient_".$hid;
$hospital_name = $db->query("select name from hospital where id='$hid' limit 1",1,"name");

// 查询选项:
$type_arr = array("month"=>"一年内的每个月", "day"=>"一月中的每天", "hour"=>"一天内的时段");
//$year_arr = array(2010,2009,2008);
for ($i = date("Y"); $i >= 2008; $i--) {
	$year_arr[] = $i;
}
$month_arr = array(1,2,3,4,5,6,7,8,9,10,11,12);
$day_arr = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
$part_arr = array(0=>"整个医院", 2=>"网络组", 3=>"电话组", 4=>"导医组");
$come_arr = array(1=>"已到", 2=>"未到");
$time_arr = array("order_date"=>"到院时间", "addtime"=>"添加时间");
$kf_arr = $db->query("select distinct author from $table where author!='' and part_id in (2,3,4) order by part_id,author", "", "author");
//$media_arr = array("杂志", "电话");

$media_arr = explode(" ", "网络 电话");
$media_2 = $db->query("select name from media where (hospital_id=0 or hospital_id=$hid) order by sort desc,addtime asc", "", "name");
$media_arr = array_merge($media_arr, $media_2);

// 清理无效的
$admin_id_name = $db->query("select realname from sys_admin", "", "realname");
foreach ($kf_arr as $k => $v) {
	if (!in_array($v, $admin_id_name)) {
		unset($kf_arr[$k]);
	}
}


if ($_GET) {
	extract($_GET);

	// 构建 where:
	$where = array();

	if ($type == "month") {
		$tb = mktime(0,0,0,1,1,$year);
		$te = strtotime("+1 year", $tb);
		$format = "%Y%m";
		$caption = "月";
		$tips = $year."年 各月份";
	} else if ($type == "day") {
		$tb = mktime(0,0,0,$month,1,$year);
		$te = strtotime("+1 month", $tb);
		$format = "%Y%m%d";
		$caption = "日";
		$tips = $year."年".$month."月 各天";
	} else if ($type == "hour") {
		$tb = mktime(0,0,0,$month,$day,$year);
		$te = strtotime("+1 day", $tb);
		$format = "%Y%m%d%H";
		$caption = "时";
		$tips = $year."年".$month."月".$day."日 各时段";
	}

	// 图标下方提示:
	$tips .= ($time == "addtime" ? "预约" : "到院")."人数";
	if ($part || $come) {
		$tips .= "(";
		$tips .= $part ? $part_arr[$part] : "";
		$tips .= $come ? $come_arr[$come] : "";
		$tips .= ")";
	}

	$time_field = array_key_exists($time, $time_arr) ? $time : "addtime";
	if ($tb > 0) {
		$where[] = $time_field.">=".$tb;
	}
	if ($te > 0) {
		$where[] = $time_field."<".$te;
	}

	if ($part > 0) {
		$where[] = "part_id=".$part;
	}

	if ($come > 0) {
		$where[] = $come == 1 ? "status=1" : "status=0";
	}

	if ($media != '') {
		$where[] = "media_from='".$media."'";
	}

	if ($guiji != '') {
		$where[] = "guiji='".$guiji."'";
	}


	// 处理数据:

	if ($_GET["kf"] == "-1") {

		$kf_nums = $FC = array();
		foreach ($kf_arr as $kf) {
			$where2 = $where;
			$where2[] = "binary author='".$kf."'";
			$sqlwhere = implode(" and ", $where2);
			$list = $db->query("select t,count(t) as c from (select id,part_id,$time_field,status,from_unixtime($time_field,'$format') as t from $table where $sqlwhere) as tmp group by t", "t", "c");

			if (count($list) > 0) {
				$kf_nums[$kf] = array_sum($list); //排序之用

				// 统计图:
				$FC[$kf] = new FusionCharts("Column2D","750","150");
				$FC[$kf]->setSWFPath("chart/");
				$FC[$kf]->setChartParams("decimalPrecision=0; formatNumberScale=0; baseFontSize=10; baseFont=Arial; chartBottomMargin=0; outCnvBaseFontSize=12; hoverCapSepChar=$caption: " );

				$ymax = intval((@max($list) + 10) * 1.2);
				$FC[$kf]->setChartParams("yAxisMaxValue={$ymax}");

				if ($type == "month") {
					for ($i=1; $i<=12; $i++) {
						$value = max(0, $list[$year.($i<10?"0":"").$i]);
						$FC[$kf]->addChartData($value, "name=".$i);
					}
				} else if ($type == "day") {
					for ($i=1; $i<=31; $i++) {
						$value = max(0, $list[$year.($month<10?"0":"").$month.($i<10?"0":"").$i]);
						$FC[$kf]->addChartData($value, "name=".$i);
					}
				} else if ($type == "hour") {
					for ($i=0; $i<24; $i++) {
						$value = max(0, $list[$year.($month<10?"0":"").$month.($day<10?"0":"").$day.($i<10?"0":"").$i]);
						$FC[$kf]->addChartData($value, "name=".$i);
					}
				}

			}
		}

		arsort($kf_nums);

	} else {

		if ($kf != '') {
			$where[] = "binary author='".$kf."'";
		}

		$sqlwhere = implode(" and ", $where);
		$list = $db->query("select t,count(t) as c from (select id,part_id,$time_field,status,from_unixtime($time_field,'$format') as t from $table where $sqlwhere) as tmp group by t", "t", "c");


		// 统计图:
		$FC = new FusionCharts("Column2D","750","200");
		$FC->setSWFPath("chart/");
		$FC->setChartParams("decimalPrecision=0; formatNumberScale=0; baseFontSize=10; baseFont=Arial; chartBottomMargin=0; outCnvBaseFontSize=12; hoverCapSepChar=$caption: " );

		$ymax = intval((@max($list) + 10) * 1.2);
		$FC->setChartParams("yAxisMaxValue={$ymax}");

		// 表格显示（方便复制出来excel保存以及后处理）
		$table_data = array();

		if ($type == "month") {
			for ($i=1; $i<=12; $i++) {
				$value = max(0, $list[$year.($i<10?"0":"").$i]);
				$FC->addChartData($value, "name=".$i);
				$table_data[$i] = $value;
				$table_data_name = "月份";
			}
		} else if ($type == "day") {
			for ($i=1; $i<=31; $i++) {
				$value = max(0, $list[$year.($month<10?"0":"").$month.($i<10?"0":"").$i]);
				$FC->addChartData($value, "name=".$i);
				$table_data[$i] = $value;
				$table_data_name = "日期";
			}
		} else if ($type == "hour") {
			for ($i=0; $i<24; $i++) {
				$value = max(0, $list[$year.($month<10?"0":"").$month.($day<10?"0":"").$day.($i<10?"0":"").$i]);
				$FC->addChartData($value, "name=".$i);
				$table_data[$i] = $value;
				$table_data_name = "小时";
			}
		}
	}
}




$title = '病人预约数量走势图';

function con($s) {
	$s = iconv("gbk", "utf-8", $s);
	return urlencode($s);
}
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src='chart/FusionCharts.js' language='javascript'></script>
<script type="text/javascript">
function hgo(dir) {
	var obj = byid("hospital_id");
	if (dir == "up") {
		if (obj.selectedIndex > 1) {
			obj.selectedIndex = obj.selectedIndex - 1;
			obj.onchange();
		} else {
			parent.msg_box("已经是最上一家医院了", 3);
		}
	}
	if (dir == "down") {
		if (obj.selectedIndex < obj.options.length-1) {
			obj.selectedIndex = obj.selectedIndex + 1;
			obj.onchange();
		} else {
			parent.msg_box("已经是最下一家医院了", 3);
		}
	}
}
function go_change(id) {
	var url = encodeURIComponent(self.location.href);
	self.location='?do=change&hospital_id='+id+'&go='+url;
}
</script>
<style>
.input {height:20px; }
.w400 {width:400px }
.w800 {width:800px; }
</style>
<script type="text/javascript">
function show_hide_date(v) {
	byid("s_month").style.display = "none";
	byid("s_day").style.display = "none";
	if (v == "day" || v == "hour") {
		byid("s_month").style.display = "inline";
	}
	if (v == "hour") {
		byid("s_day").style.display = "inline";
	}
}
</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips"><?php echo $title; ?></nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">刷新</button></td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<table width="100%"><tr><td align="center">

<div>
	<center>当前科室：<b><?php echo $hospital_name; ?></b></center>
</div>

<form action="" method="GET">
<div style="margin-top:40px; ">
	<b>报表显示: </b>
	<select name="type" class="combo" onchange="show_hide_date(this.value)">
		<option value="" style="color:gray">-类型-</option>
		<?php echo list_option($type_arr, "_key_", "_value_", noe($_GET["type"], "day")); ?>
	</select>
	<select name="time" class="combo">
		<option value="" style="color:gray">-时间类型-</option>
		<?php echo list_option($time_arr, "_key_", "_value_", noe($_GET["time"], "addtime")); ?>
	</select>
	<select name="year" class="combo">
		<option value="" style="color:gray">-年-</option>
		<?php echo list_option($year_arr, "_value_", "_value_", noe($_GET["year"], date("Y"))); ?>
	</select>
	<select name="month" id="s_month" class="combo" style="display:none;">
		<option value="" style="color:gray">-月-</option>
		<?php echo list_option($month_arr, "_value_", "_value_", noe($_GET["month"], date("n"))); ?>
	</select>
	<select name="day" id="s_day" class="combo" style="display:none;">
		<option value="" style="color:gray">-日-</option>
		<?php echo list_option($day_arr, "_value_", "_value_", noe($_GET["day"], date("j"))); ?>
	</select>
	<select name="part" class="combo">
		<option value="" style="color:gray">-部门-</option>
		<?php echo list_option($part_arr, "_key_", "_value_", $_GET["part"]); ?>
	</select>
	<select name="media" class="combo">
		<option value="" style="color:gray">-媒体来源-</option>
		<?php echo list_option($media_arr, "_value_", "_value_", $_GET["media"]); ?>
	</select>
	<select name="come" class="combo">
		<option value="" style="color:gray">-是否到院-</option>
		<?php echo list_option($come_arr, "_key_", "_value_", $_GET["come"]); ?>
	</select>
	<select name="guiji" class="combo">
		<option value="" style="color:gray">-轨迹-</option>
		<?php echo list_option($guiji_arr, "_value_", "_value_", $_GET["guiji"]); ?>
	</select>
	<select name="kf" class="combo">
		<option value="" style="color:gray">-客服-</option>
		<option value="-1" style="color:red" <?php if ($_GET["kf"] == "-1") echo "selected"; ?>>-客服对比-</option>
		<?php echo list_option($kf_arr, "_value_", "_value_", $_GET["kf"]); ?>
	</select>
	<input type="submit" class="button" value="执行">
</div>
</form>

<script type="text/javascript">
show_hide_date('<?php echo noe($_GET["type"],"day"); ?>');
</script>

<br>
<br>

<?php if ($_GET) { ?>

<?php if ($_GET["kf"] == "-1") { ?>

<?php
	foreach ($kf_nums as $x => $v) {
		$FC[$x]->renderChart();
		echo '<div class="w800" style="text-align:center; margin-top:10px; margin-bottom:20px; "><b>'.$hospital_name." ".$x." (".$v.")".'</b></div>';
	}
?>

<?php } else { ?>
	<?php $FC->renderChart(); ?>
	<div class="w800" style="text-align:center; margin-top:10px; "><?php echo "<b>".$hospital_name." ".$tips."</b>"; ?></div>


	<br>
	<br>
	<br>

	<style type="text/css">
	.wee_table {border:2px solid silver; }
	.wee_table td {border:1px solid silver; }
	.wee_table td {padding:6px 4px; text-align:center; }
	.wee_head {background:#e9ecef; }
	</style>
	<div><b>表格显示数据（方便复制到excel保存和后期计算，数据与上述图表显示完全一致）</b></div>
	<br>
	<table width="80%" class="wee_table">
		<tr class="wee_head">
			<td align="center"><?php echo $table_data_name; ?></td>
<?php foreach ($table_data as $k => $v) { ?>
			<td align="center"><b><?php echo $k; ?></b></td>
<?php } ?>
		</tr>
		<tr>
			<td align="center">数值</td>
<?php foreach ($table_data as $k => $v) { ?>
			<td align="center" style="color:blue;"><?php echo $v; ?></td>
<?php } ?>
		</tr>
	</table>


	<?php
	if ($debug_mode) {
		//echo "<pre>";
		//print_r($table_data);
	}
	?>

<?php } ?>

<?php } else { ?>

<div>请设置查询条件后,点击执行显示图形报表</div>

<?php } ?>

</td>
</tr>
</table>


</body>
</html>