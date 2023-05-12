<?php
/*
// 说明: 报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-11-24
*/
require "lib/set_env.php";

// 报表核心定义:
include "rp.core.php";

$tongji_tips = " - 手机归属地统计 - ".$type_tips;
?>
<html>
<head>
<title>手机归属地报表</title>
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
$max_location_num = 15;

$location_arr = $db->query("select tel_location,count(tel_location) as c from $table where $where tel_location!='' and {$timetype}>=$max_tb and {$timetype}<=$max_te group by tel_location order by c desc limit $max_location_num", "tel_location", "c");


if (in_array($type, array(1,2,3,4))) {
	// 计算统计数据:
	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = $db->query("select count(*) as c from $table where $where {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");

		foreach ($location_arr as $lo => $num) {
			$data[$k][$lo] = $db->query("select count(*) as c from $table where $where tel_location='{$lo}' and {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");
		}
	}
} else if ($type == 5) {
	$arr = array();
	$arr["总"] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");

	foreach ($location_arr as $lo => $num) {
		$arr[$lo] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where tel_location='{$lo}' and $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");
	}

	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = intval($arr["总"][$v]);
		foreach ($location_arr as $lo => $num) {
			$data[$k][$lo] = intval($arr[$lo][$v]);
		}
	}
}


?>
<div class="date_tips"><?php echo $h_name.$tongji_tips.$tips; ?></div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center">时间</td>
		<td class="head red" align="center">总计</td>
<?php foreach ($location_arr as $lo => $num) { ?>
		<td class="head" align="center"><?php echo $lo; ?></td>
<?php } ?>
	</tr>

<?php foreach ($final_dt_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["总"]; ?></td>
<?php   foreach ($location_arr as $lo => $num) { ?>
		<td class="item" align="center"><?php echo $data[$k][$lo]; ?></td>
<?php   } ?>
	</tr>
<?php } ?>
</table>

<br>
<?php } ?>


</body>
</html>