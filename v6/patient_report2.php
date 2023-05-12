<?php
/* --------------------------------------------------------
// 说明: 图形报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2009-06-25 14:01
// ----------------------------------------------------- */
require "lib/set_env.php";
include "chart/FusionCharts_Gen.php";

check_power('', $pinfo) or exit("没有打开权限...");

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$table = "patient_".$user_hospital_id;

// 医院名称:
$h_name = $db->query("select name from hospital where id=$user_hospital_id limit 1", "1", "name");

// 第一个，本月统计:
$FC = new FusionCharts("Column2D","800","200");
$FC->setSWFPath("chart/");
$FC->setChartParams("decimalPrecision=0; formatNumberScale=1; baseFontSize=10; baseFont=Arial; chartBottomMargin=0; outCnvBaseFontSize=12; hoverCapSepChar=日: " );

// 统计:
$timebegin = $_GET["month"] ? $_GET["month"] : mktime(0,0,0,date("m"),1);
$timeend = strtotime("+1 month", $timebegin);
$list_1 = $db->query("select id,addtime,status from $table where addtime>$timebegin and addtime<$timeend");

$a2 = array();
foreach ($list_1 as $li) {
	$a2[date("j", $li["addtime"])] += 1;
}

$ymax = 10 * ceil((@max($a2) + 10) / 10);
$FC->setChartParams("yAxisMaxValue={$ymax}");

for ($i=1; $i<=31; $i++) {
	//$FC->aa($a2[$i]);
	$FC->addChartData($a2[$i], "name=".$i);
}





// 最近趋势:
$FC2 = new FusionCharts("Line","800","186");
$FC2->setSWFPath("chart/");
$FC2->setChartParams("decimalPrecision=0; formatNumberScale=1; baseFontSize=10; baseFont=Arial; chartBottomMargin=0; outCnvBaseFontSize=12; shownames=0; showValues=0; hoverCapSepChar=: ; chartBottomMargin=10; ");


$time = time();
$tb = strtotime("-3 month");
$list_3 = $db->query("select id,addtime,status from $table where addtime>$tb and addtime<=$time");
$a3_all = $a3_come = array();
foreach ($list_3 as $li) {
	$a3_all[date("y-n-j", $li["addtime"])] += 1;
	if ($li["status"] == 1) {
		$a3_come[date("y-n-j", $li["addtime"])] += 1;
	}
}

$ymax = 10 * ceil((@max($a3_all) + 5) / 10);
$FC2->setChartParams("yAxisMaxValue={$ymax}");


foreach ($a3_all as $d => $s) {
	$FC2->addChartData($s, "name=".date("n月j日", strtotime($d)));
}

$title = $h_name.' - 病人预约数据走势图';


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
<style>
.w400 {width:400px }
.w800 {width:800px; }
.hr {border:0; margin:0; padding:0; height:3px; line-height:0; font-size:0; background-color:red; color:white; border-top:1px solid silver; }
</style>
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

<div style="text-align:center; ">

<div style="margin-left:20px;">
月份：
<?php
for ($i=0; $i<=6; $i++) {
	$date = mktime(0,0,0,date("m")-$i,1);
?>
	<a href="?month=<?php echo $date; ?>"><?php echo date("Y-m", $date); ?></a>&nbsp;
<?php
}
?>
</div>


<?php $FC->renderChart(); ?>
<center><?php echo "<b>".date("Y年n月", $timebegin)." 预约病人</b> (x轴为日期，y轴是预约人数)"; ?></center>

<br>

<?php $FC2->renderChart(); ?>
<center><?php echo "<b>最近3个月预约趋势</b> (x轴为日期，y轴是预约人数)"; ?></center>

</div>

</body>
</html>