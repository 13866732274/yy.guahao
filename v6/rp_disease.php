<?php
/*
// 说明: 报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-11-24
*/
require "lib/set_env.php";

// 报表核心定义:
include "rp.core.php";

$tongji_tips = " - 疾病类型统计 - ".$type_tips;
?>
<html>
<head>
<title>疾病报表</title>
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
.item {border-left:1px solid #eeeeee !important; border-right:1px solid #eeeeee !important; }
.red {color:red !important;  }
</style>
</head>

<body>

<?php include_once "rp.condition_form.php"; ?>

<?php if ($_GET["op"] == "report") { ?>
<?php

// 疾病:
$disease_arr = $db->query("select id,name from disease where hospital_id=$hid and isshow=1 order by id asc", "id", "name");
if (count($disease_arr) == 0) {
	exit_html("<center>尚未定义疾病类型，该项无法进行报表分析。</center>");
}

// 疾病数量太多：删除访问量小的疾病:
$max_disease_num = 15;
if (count($disease_arr) > $max_disease_num) {
	$new_disease_arr = $db->query("select disease_id,count(disease_id) as c from $table where $where disease_id>0 and {$timetype}>=$max_tb and {$timetype}<=$max_te group by disease_id order by c desc", "disease_id", "c");

	$disease_arr2 = array();
	foreach ($new_disease_arr as $k => $v) {
		$disease_arr2[$k] = $disease_arr[$k];
		if (count($disease_arr2) >= $max_disease_num) {
			break;
		}
	}
	$disease_arr = $disease_arr2;
	$tips = " (为简化表格，只统计频率最高的{$max_disease_num}个病种)";
}


if (in_array($type, array(1,2,3,4))) {
	// 计算统计数据:
	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = $db->query("select count(*) as c from $table where $where {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");

		foreach ($disease_arr as $did => $dname) {
			$data[$k][$did] = $db->query("select count(*) as c from $table where $where disease_id=$did and {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");
		}
	}
} else if ($type == 5) {
	$arr = array();
	$arr["总"] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");

	foreach ($disease_arr as $did => $dname) {
		$arr[$did] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where disease_id=$did and $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");
	}

	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = intval($arr["总"][$v]);
		foreach ($disease_arr as $did => $dname) {
			$data[$k][$did] = intval($arr[$did][$v]);
		}
	}
}


?>
<div class="date_tips"><?php echo $h_name.$tongji_tips.$tips; ?></div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center">时间</td>
		<td class="head red" align="center">总计</td>
<?php foreach ($disease_arr as $did => $dname) { ?>
		<td class="head" align="center"><?php echo $dname; ?></td>
<?php } ?>
	</tr>

<?php foreach ($final_dt_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["总"]; ?></td>
<?php   foreach ($disease_arr as $did => $dname) { ?>
		<td class="item" align="center"><?php echo $data[$k][$did]; ?></td>
<?php   } ?>
	</tr>
<?php } ?>
</table>

<br>
<?php } ?>


</body>
</html>