<?php
/*
// 说明: 按性别报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-11-23
*/
require "lib/set_env.php";

// 报表核心定义:
include "rp.core.php";

$tongji_tips = " - 性别统计 - ".$type_tips;
?>
<html>
<head>
<title>性别报表</title>
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
</style>
</head>

<body>

<?php include_once "rp.condition_form.php"; ?>

<?php if ($_GET["op"] == "report") { ?>
<?php

if (in_array($type, array(1,2,3,4))) {
	// 计算统计数据:
	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = $db->query("select count(*) as c from $table where $where {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");
		$data[$k]["男"] = $db->query("select count(*) as c from $table where $where sex='男' and {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");
		$data[$k]["女"] = $db->query("select count(*) as c from $table where $where sex='女' and {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");
		$data[$k]["未知"] = $data[$k]["总"] - $data[$k]["男"] - $data[$k]["女"];
	}
} else if ($type == 5) {
	$arr_all = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");
	$arr_man = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where sex='男' and $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");
	$arr_woman = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where sex='女' and $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");

	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["总"] = intval($arr_all[$v]);
		$data[$k]["男"] = intval($arr_man[$v]);
		$data[$k]["女"] = intval($arr_woman[$v]);
		$data[$k]["未知"] = $data[$k]["总"] - $data[$k]["男"] - $data[$k]["女"];
	}
}

?>
<div class="date_tips"><?php echo $h_name.$tongji_tips; ?></div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center" width="10%">时间</td>
		<td class="head" align="center" width="18%">总人数</td>
		<td class="head" align="center" width="18%">男</td>
		<td class="head" align="center" width="18%">女</td>
		<td class="head" align="center" width="18%">未知</td>
		<td class="head" align="center" width="18%">男女比例</td>
	</tr>

<?php foreach ($final_dt_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["总"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["男"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["女"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["未知"]; ?></td>
		<td class="item" align="center">1:<?php echo $data[$k]["男"] == 0 ? "∞" : @round($data[$k]["女"] / $data[$k]["男"], 2); ?></td>
	</tr>
<?php } ?>
</table>

<br>
<?php } ?>


</body>
</html>