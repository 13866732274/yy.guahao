<?php
// --------------------------------------------------------
// - 功能说明 : 客服数据对比
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-12-02
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_huifang";
include "count_huifang.inc.php";

if ($_GET["btime"] == "") $_GET["btime"] = date("Y-m-01");
if ($_GET["etime"] == "") $_GET["etime"] = date("Y-m-d");

$hid = intval($_GET["hid"]);
if ($hid == 0) {
	$hid = array_shift(array_keys($yiyuan_arr));
}
if (!array_key_exists($hid, $yiyuan_arr)) {
	exit("参数错误");
}

//$hid_set = $db->query("select * from count_huifang_set where hid=$hid limit 1", 1);
//$kefu_names = explode(",", $hid_set["kefu_names"]);


$int_btime = date("Ymd", strtotime($_GET["btime"]));
$int_tb = strtotime($_GET["btime"]);
if ($_GET["etime"] != "") {
	$int_etime = date("Ymd", strtotime($_GET["etime"]));
	$int_te = strtotime($_GET["etime"]." 23:59:59");
} else {
	$int_etime = $int_btime;
	$int_te = strtotime($_GET["btime"]." 23:59:59");
}

$x1 = $x2 = $x3 = $x4 = $x5 = $x6 = $x7 = $x8 = $x9 = $x10 = array();
$list_data = $db->query("select kefu, sum(x1) as x1, sum(x2) as x2, sum(x3) as x3, sum(x4) as x4, sum(x5) as x5, sum(x6) as x6, sum(x7) as x7, sum(x8) as x8, sum(x9) as x9 from $table where hid in ($hid) and date>=$int_btime and date<=$int_etime group by kefu", "kefu");
foreach ($list_data as $kefu => $li) {
	$x1[$kefu] = $li["x1"];
	$x2[$kefu] = $li["x2"];
	$x3[$kefu] = $li["x3"];
	$x4[$kefu] = $li["x4"];
	$x5[$kefu] = $li["x5"];
	$x6[$kefu] = $li["x6"];
	$x7[$kefu] = $li["x7"];
	$x8[$kefu] = $li["x8"];
	$x9[$kefu] = $li["x9"];
}


$kefus = array_keys($x1);
sort($kefus);
$data_count = count($kefus);

?>
<html>
<head>
<title>客服数据对比</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.new_body {padding:10px; }
.main_title {margin:0 auto; padding:10px; text-align:center;  }
.title_font {font-weight:bold; font-size:15px; }
.report_table .item {border:1px solid silver; }
</style>

<script type="text/javascript">
function set_kefu(hid) {
	parent.load_src(1, "count_huifang_set.php?hid="+hid, 900, 500);
}
function set_data(hid) {
	parent.load_src(1, "count_huifang_edit.php?hid="+hid, 600, 500);
}
</script>

</head>

<body class="new_body">

<form action="?" method="GET">
	<select name="hid" class="combo" onchange="this.form.submit();">
		<?php echo list_option($yiyuan_arr, "_key_", "_value_", $hid); ?>
	</select>
	<span>　　日期范围：</span><input name="btime" id="btime" class="input" style="width:80px" value="<?php echo $_GET["btime"]; ?>" onclick="picker({el:'btime',dateFmt:'yyyy-MM-dd'})" onchange="this.form.submit();"> ~ <input name="etime" id="etime" class="input" style="width:80px" value="<?php echo $_GET["etime"]; ?>" onclick="picker({el:'etime',dateFmt:'yyyy-MM-dd'})" onchange="this.form.submit();">
	<span>　　</span><a href="count_huifang.php">返回汇总页面</a>
</form>

<?php $per = round(90 / 11, 3)."%"; ?>

<table width="100%" align="center" class="report_table" style="margin-top:10px;">
	<tr>
		<td class="head" align="left">客服</td>
		<td class="head" width="<?php echo $per; ?>" align="center">回访总量</td>
		<td class="head" width="<?php echo $per; ?>" align="center">改约人数</td>
		<td class="head" width="<?php echo $per; ?>" align="center">无人接听</td>
		<td class="head" width="<?php echo $per; ?>" align="center">改约就诊</td>
		<td class="head" width="<?php echo $per; ?>" align="center">电话就诊</td>
		<td class="head" width="<?php echo $per; ?>" align="center">当天就诊</td>
		<td class="head" width="<?php echo $per; ?>" align="center">其他就诊</td>
		<td class="head" width="<?php echo $per; ?>" align="center">微信就诊</td>
		<td class="head" width="<?php echo $per; ?>" align="center">就诊合计</td>
		<td class="head" width="<?php echo $per; ?>" align="center">当天就诊率</td>
		<td class="head" width="<?php echo $per; ?>" align="center">回访就诊率</td>
	</tr>

<?php
foreach ($kefus as $kefu) {
	$is_yuanqi = in_array($kefu, $yuanqi_kefu_names);
	$color = $is_yuanqi ? "blue" : "";
	$display_kefu = $is_yuanqi ? "远期 ".$kefu : $kefu;
?>
	<tr style="color:<?php echo $color; ?>">
		<td class="item" align="left"><nobr><?php echo '<font color="'.$color.'">'.$display_kefu.'</font>'; ?></nobr></td>
		<td class="item" align="center"><?php echo $x1[$kefu]; ?></td>
		<td class="item" align="center"><?php echo $x2[$kefu]; ?></td>
		<td class="item" align="center"><?php echo $x3[$kefu]; ?></td>
		<td class="item" align="center"><?php echo $x4[$kefu]; ?></td>
		<td class="item" align="center"><?php echo $x5[$kefu]; ?></td>
		<td class="item" align="center"><?php echo $x6[$kefu]; ?></td>
		<td class="item" align="center"><?php echo $x7[$kefu]; ?></td>
		<td class="item" align="center"><?php echo $x8[$kefu]; ?></td>
		<td class="item" align="center"><?php echo $x9[$kefu]; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $x6[$kefu] / $x1[$kefu], 1)."%"; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $x4[$kefu] / $x1[$kefu], 1)."%"; ?></td>
	</td>
<?php } ?>

	<tr class="line_huizong">
		<td class="item" align="left">汇总</td>
		<td class="item" align="center"><?php echo @array_sum($x1); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x2); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x3); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x4); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x5); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x6); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x7); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x8); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x9); ?></td>
		<td class="item" align="center"><?php echo @round(100 * array_sum($x6) / array_sum($x1), 1)."%"; ?></td>
		<td class="item" align="center"><?php echo @round(100 * array_sum($x4) / array_sum($x1), 1)."%"; ?></td>
	</tr>

	<tr class="line_huizong">
		<td class="item" align="left">平均 (<?php echo $data_count; ?>)</td>
		<td class="item" align="center"><?php echo @round(@array_sum($x1) / $data_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x2) / $data_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x3) / $data_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x4) / $data_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x5) / $data_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x6) / $data_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x7) / $data_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x8) / $data_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x9) / $data_count); ?></td>
		<td class="item" align="center"><?php echo @round(100 * array_sum($x6) / array_sum($x1), 1)."%"; ?></td>
		<td class="item" align="center"><?php echo @round(100 * array_sum($x4) / array_sum($x1), 1)."%"; ?></td>
	</tr>

</table>


<br>
<br>
<br>

</body>
</html>