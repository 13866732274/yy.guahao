<?php
/*
// 说明: 总体报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-11-23
*/
require "lib/set_env.php";

// 报表核心定义:
include "rp.core.php";

$part = $_GET["part"];
$where = '';
$tips = "总数据";
if ($part == "web") {
	$where = "part_id=2 and ";
	$tips = "网络";
} else if ($part == "tel") {
	$where = "part_id=3 and ";
	$tips = "电话";
}
?>
<html>
<head>
<title>总体报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
body {margin-top:10px; }
.head, .head a {font-family:"微软雅黑","Verdana"; }
.item {font-family:"Tahoma"; padding:8px 3px 6px 3px !important; }
.footer_op_left {font-family:"Tahoma"; }
.date_tips {padding:10px 0 10px 5px; font-weight:bold; }
form {display:inline; }
</style>
</head>

<body>

<div style="text-align:center;">
	<b><?php echo $h_name; ?></b>&nbsp;&nbsp;
	<form method="GET">
		<input type="hidden" name="part" value="all">
		<input type="submit" value="总数据" class="buttonb" <?php if ($part == "" || $part == "all") echo 'style="color:red;font-weight:bold;"'; ?> />
	</form>

	<form method="GET">
		<input type="hidden" name="part" value="web">
		<input type="submit" value="网络" class="button" <?php if ($part == "web") echo 'style="color:red;font-weight:bold;"'; ?> />
	</form>

	<form method="GET">
		<input type="hidden" name="part" value="tel">
		<input type="submit" value="电话" class="button" <?php if ($part == "tel") echo 'style="color:red;font-weight:bold;"'; ?> />
	</form>
</div>

<!-- 按年查看 -->
<!-- 今年，去年，前年的记录 -->
<?php
$y = intval(date("Y"));
$time_arr = array(
	"今年" => array(strtotime($y."-01-01 00:00:00"), strtotime($y."-12-31 00:00:00")),
	"去年" => array(strtotime(($y-1)."-01-01 00:00:00"), strtotime(($y-1)."-12-31 00:00:00")),
	"前年" => array(strtotime(($y-2)."-01-01 00:00:00"), strtotime(($y-2)."-12-31 00:00:00")),
);

// 计算统计数据:
$data = array();
foreach ($time_arr as $k => $v) {
	$data[$k]["预约"] = $db->query("select count(*) as c from $table where $where addtime>=".$v[0]." and addtime<=".$v[1]." ", 1, "c");
	$data[$k]["预到"] = $db->query("select count(*) as c from $table where $where order_date>=".$v[0]." and order_date<=".$v[1]." ", 1, "c");
	$data[$k]["已到"] = $db->query("select count(*) as c from $table where $where status=1 and order_date>=".$v[0]." and order_date<=".$v[1]." ", 1, "c");
	$data[$k]["未到"] = $data[$k]["预到"] - $data[$k]["已到"];
}
?>
<div class="date_tips">按年份输出(最近3年<?php echo $tips; ?>)：</div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center" width="10%">年份</td>
		<td class="head" align="center" width="18%">预约</td>
		<td class="head" align="center" width="18%">预到</td>
		<td class="head" align="center" width="18%">已到</td>
		<td class="head" align="center" width="18%">未到</td>
		<td class="head" align="center" width="18%">到院比例</td>
	</tr>

<?php foreach ($time_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["预约"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["预到"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["已到"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["未到"]; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $data[$k]["已到"] / $data[$k]["预到"], 1)."%"; ?></td>
	</tr>
<?php } ?>
</table>

<br>


<!-- 按月份查看 -->
<!-- 最近x个月的记录 -->
<?php

$time_arr = array();
for ($i = 0; $i < 12; $i++) {
	$m = strtotime("-".$i." month", mktime(0,0,0,date("m"), 1));
	$t1 = strtotime(date("Y-m-01 00:00:00", $m));
	$t2 = strtotime("+1 month", $t1) - 1;
	$time_arr[date("Y-m", $m)] = array($t1, $t2);
}

if ($debug_mode) {
	//echo "<pre>";
	//print_r($time_arr);
}

// 计算统计数据:
$data = array();
foreach ($time_arr as $k => $v) {
	$data[$k]["预约"] = $db->query("select count(*) as c from $table where $where addtime>=".$v[0]." and addtime<=".$v[1]." ", 1, "c");
	$data[$k]["预到"] = $db->query("select count(*) as c from $table where $where order_date>=".$v[0]." and order_date<=".$v[1]." ", 1, "c");
	$data[$k]["已到"] = $db->query("select count(*) as c from $table where $where status=1 and order_date>=".$v[0]." and order_date<=".$v[1]." ", 1, "c");
	$data[$k]["未到"] = $data[$k]["预到"] - $data[$k]["已到"];
}
?>
<div class="date_tips">按月份输出(最近12个月<?php echo $tips; ?>)：</div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center" width="10%">月份</td>
		<td class="head" align="center" width="18%">预约</td>
		<td class="head" align="center" width="18%">预到</td>
		<td class="head" align="center" width="18%">已到</td>
		<td class="head" align="center" width="18%">未到</td>
		<td class="head" align="center" width="18%">到院比例</td>
	</tr>

<?php foreach ($time_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["预约"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["预到"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["已到"]; ?></td>
		<td class="item" align="center"><?php echo $data[$k]["未到"]; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $data[$k]["已到"] / $data[$k]["预到"], 1)."%"; ?></td>
	</tr>
<?php } ?>
</table>



</body>
</html>