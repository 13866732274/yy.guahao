<?php
/* --------------------------------------------------------
// 说明: 图形报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2009-06-25 14:01
// ----------------------------------------------------- */
require "lib/set_env.php";
check_power('', $pinfo) or exit("没有打开权限...");

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$table = "patient_".$user_hospital_id;

// 最新三个月的统计:
$timebegin = $_GET["month"] ? $_GET["month"] : mktime(0,0,0,date("m"),0);
$timeend = strtotime("+1 month", $timebegin);
$list_2 = $db->query("select id,addtime from $table where part_id=2 and addtime>=$timebegin and addtime<$timeend");
$list_3 = $db->query("select id,addtime from $table where part_id=3 and addtime>=$timebegin and addtime<$timeend");

$a1 = $b1 = array();
foreach ($list_2 as $li) {
	$a1[date("Y-m-d 0:0:0", $li["addtime"])] += 1;
}
foreach ($list_3 as $li) {
	$b1[date("Y-m-d 0:0:0", $li["addtime"])] += 1;
}

$a_max = $b_max = 0;
$a = $b = array();
foreach ($a1 as $k => $v) {
	$a[] = '['.strtotime($k).'000,'.$v.']';

	if ($a_max < $v) {
		$a_max = $v;
	}
}
foreach ($b1 as $k => $v) {
	$b[] = '['.strtotime($k).'000,'.$v.']';
	if ($b_max < $v) {
		$b_max = $v;
	}
}

$title = '病人预约数量走势图';
?>
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>

<!--[if IE]><script language="javascript" type="text/javascript" src="lib/excanvas.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="lib/jquery.js"></script>
<script language="javascript" type="text/javascript" src="lib/jquery.flot.js"></script>

<script language="javascript">
$(function () {

	var a = [<?php echo implode(', ', $a); ?>];
	var b = [<?php echo implode(', ', $b); ?>];

    var plot = $.plot($("#placeholder"),
           [
		   { data: a, label: "网络"}
		   ],
		   {
			   xaxis: { mode: 'time' },
			   lines: { show: true },
			   points: { show: true },
			   selection: { mode: "xy" },
			   grid: { hoverable: true, clickable: true },
			   yaxis: { min: 0, max: <?php echo max($a_max,$b_max); ?> }
		});

    var plot = $.plot($("#placeholder2"),
           [
		   { data: b, label: "电话" }
		   ],
		   {
			   xaxis: { mode: 'time' },
			   lines: { show: true },
			   points: { show: true },
			   selection: { mode: "xy" },
			   grid: { hoverable: true, clickable: true },
			   yaxis: { min: 0, max: <?php echo max($a_max,$b_max); ?> }
		});
});


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

<div style="margin-left:20px;">
网络：
<?php
//$this_month = mktime(0,0,0,date("m"),0);
for ($i=0; $i<=6; $i++) {
	$date = mktime(0,0,0,date("m")-$i,1);
?>
	<a href="?month=<?php echo $date; ?>"><?php echo date("Y-m", $date); ?></a>
<?php
}
?>
<div id="placeholder" style="width:800px;height:200px"></div>
<br>
电话：
<div id="placeholder2" style="width:800px;height:200px"></div>
</div>

</body>
</html>