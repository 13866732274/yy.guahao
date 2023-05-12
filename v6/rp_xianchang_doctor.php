<?php
/*
// 说明: 报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-11-24
*/
require "lib/set_env.php";

// 报表核心定义:
include "rp.core.php";

$tongji_tips = " - 现场医生统计 - ".$type_tips;
?>
<html>
<head>
<title>现场医生报表</title>
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

// 读取医生，最多15个
$xianchang_doctor_arr = $db->query("select xianchang_doctor,count(xianchang_doctor) as c from $table where $where xianchang_doctor!='' and {$timetype}>=$max_tb and {$timetype}<=$max_te group by xianchang_doctor order by c desc limit 15", "xianchang_doctor", "c");
if (count($xianchang_doctor_arr) == 0) {
	exit_html("<center>对不起，该医院未使用医生功能，无法统计。</center>");
}

if (in_array($type, array(1,2,3,4))) {
	// 计算统计数据:
	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = $db->query("select count(*) as c from $table where $where {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");

		foreach ($xianchang_doctor_arr as $me => $num) {
			$data[$k][$me] = $db->query("select count(*) as c from $table where $where xianchang_doctor='{$me}' and {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");
		}
	}
} else if ($type == 5) {
	$arr = array();
	$arr["总"] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");

	foreach ($xianchang_doctor_arr as $me => $num) {
		$arr[$me] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where xianchang_doctor='{$me}' and $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");
	}

	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = intval($arr["总"][$v]);
		foreach ($xianchang_doctor_arr as $me => $num) {
			$data[$k][$me] = intval($arr[$me][$v]);
		}
	}
}


?>
<div class="date_tips"><?php echo $h_name.$tongji_tips.$tips; ?></div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center">时间</td>
		<td class="head red" align="center">总计</td>
<?php foreach ($xianchang_doctor_arr as $me => $num) { ?>
		<td class="head" align="center"><?php echo $me; ?></td>
<?php } ?>
	</tr>

<?php foreach ($final_dt_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["总"]; ?></td>
<?php   foreach ($xianchang_doctor_arr as $me => $num) { ?>
		<td class="item" align="center"><?php echo $data[$k][$me]; ?></td>
<?php   } ?>
	</tr>
<?php } ?>
</table>

<br>
<?php } ?>


</body>
</html>