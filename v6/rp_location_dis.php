<?php
/*
// 说明: 报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2014-3-5
*/
require "lib/set_env.php";

// 报表核心定义:
include "rp.core.php";

$tongji_tips = " - 地区疾病分析 - ".$type_tips;
?>
<html>
<head>
<title>地区疾病分析</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
body {margin-top:6px; }
#rp_condition_form {text-align:center; }
.head, .head a {font-family:"微软雅黑","Verdana"; }
.item {font-family:"Tahoma"; padding:8px 3px 6px 3px !important; }
.footer_op_left {font-family:"Tahoma"; }
.date_tips {padding:15px 0 15px 0px; font-weight:bold; text-align:center; font-size:15px; font-family:"微软雅黑","Verdana"; }
form {display:inline; }
.red {color:red !important;  }
</style>
</head>

<body>

<?php include_once "rp.condition_form.php"; ?>

<?php if ($_GET["op"] == "report") { ?>
<?php
$max_location_num = 100;

$location_arr = $db->query("select tel_location,count(tel_location) as c from $table where $where tel_location!='' and {$timetype}>=$max_tb and {$timetype}<=$max_te group by tel_location order by c desc limit $max_location_num", "tel_location", "c");

// 处理成省份：
$pov_arr = array();
foreach ($location_arr as $lo => $lo_count) {
	$arr = explode(" ", $lo, 2);
	$pov_arr[$arr[0]] += $lo_count;
}

arsort($pov_arr);



$disease_arr = $db->query("select id,name from disease where hospital_id=$hid and isshow=1 order by id asc", "id", "name");

// 针对每个地区查询疾病
$dis_arr = array();
foreach ($pov_arr as $lo => $lo_count) {
	$dis_arr[$lo] = $db->query("select disease_id,count(disease_id) as c from $table where $where tel_location like '{$lo}%' and {$timetype}>=$max_tb and {$timetype}<=$max_te group by disease_id order by c desc limit 15", "disease_id", "c");
}

?>

<?php foreach ($pov_arr as $lo => $lo_count) { ?>
<br>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center" style="width:100px;">地区</td>
		<td class="head red" align="center" style="width:40px;">总计</td>
<?php foreach ($dis_arr[$lo] as $dis => $dis_count) { ?>
		<td class="head" align="center"><?php echo $disease_arr[$dis]; ?></td>
<?php } ?>
	</tr>

	<tr>
		<td class="item" align="center"><?php echo $lo; ?></td>
		<td class="item" align="center"><?php echo $lo_count; ?></td>
<?php  foreach ($dis_arr[$lo] as $dis => $dis_count) { ?>
		<td class="item" align="center"><?php echo $dis_count; ?></td>
<?php   } ?>
	</tr>
</table>
<?php } ?>

<?php } ?>


</body>
</html>