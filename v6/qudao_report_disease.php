<?php
// --------------------------------------------------------
// - 功能说明 : 轨迹报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2015-6-12
// --------------------------------------------------------
require "lib/set_env.php";

if ($hid <= 0) {
	exit("请先在首页选择医院科室");
}
$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

if ($_GET["from_date"] == '') {
	$_GET["from_date"] = date("Y-m-01");
}
if ($_GET["to_date"] == '') {
	$_GET["to_date"] = date("Y-m-d");
}
$from_date = $_GET["from_date"];
$to_date = $_GET["to_date"];

$from_time = strtotime($from_date);
$to_time = strtotime($to_date." 23:59:59");



?>
<html>
<head>
<title>轨迹报表(仅包含竞价部分)</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/sorttable_keep.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
* {font-family:"微软雅黑"; }
.input, .input_focus {font-family:"宋体"; }
td {line-height:20px;  }
.button1 {color:red !important; font-weight:bold;  }
</style>
<script language="javascript">
</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td style="width:120px"><nobr class="tips">轨迹统计(按疾病)</nobr></td>
		<td align="center">
			<form method="GET">
				科室：<?php echo $hinfo["name"]; ?>　　　日期起止： <input name="from_date" id="from_date" class="input" size="12" value="<?php echo $from_date; ?>" onclick="picker({el:'from_date',dateFmt:'yyyy-MM-dd'})"> ~ <input name="to_date" id="to_date" class="input" size="12" value="<?php echo $to_date; ?>" onclick="picker({el:'to_date',dateFmt:'yyyy-MM-dd'})"> <input class="button" type="submit" value="确定">
			</form>
		</td>
		<td align="right" style="width:120px">
			<a href="#" target="_blank">在新窗口打开</a>
			<button onclick="self.location.reload()" class="button" title="">刷新</button>
		</td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<!-- 数据列表 begin -->
<form name="mainform">
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="left" width="40%">名称</td>
		<td class="head" align="center" width="10%">预约</td>
		<td class="head" align="center" width="10%">到院</td>
		<td class="head" align="center" width="40%"></td>
	</tr>

	<!-- 主要列表数据 begin -->
<?php

$disease_id_name = $db->query("select id, name from disease where hospital_id=$hid order by sort desc, id asc", "id", "name");

$where_add = "";
if ($_GET["gid"] != "") {
	$where_add = " and guiji='".$_GET["gid"]."'";
}

// 使用group by的速度非常快:
$yuyue_guiji = $db->query("select disease_id, count(disease_id) as c from patient_{$hid} where addtime>=$from_time and addtime<=$to_time and guiji in (1,2,25,26,21,22,23,24,3) $where_add group by disease_id", "disease_id", "c");
$daoyuan_guiji = $db->query("select disease_id, count(disease_id) as c from patient_{$hid} where order_date>=$from_time and order_date<=$to_time and guiji in (1,2,25,26,21,22,23,24,3) $where_add and status=1 group by disease_id", "disease_id", "c");

foreach ($disease_id_name as $gid => $gname) {
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item"><?php echo $gname; ?></td>
		<td align="center" class="item"><?php echo $yuyue_guiji[$gid]; ?></td>
		<td align="center" class="item"><?php echo $daoyuan_guiji[$gid]; ?></td>
		<td align="center" class="item">&nbsp;</td>
	</tr>
<?php
}
?>

	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item red">汇总</td>
		<td align="center" class="item red"><?php echo array_sum($yuyue_guiji); ?></td>
		<td align="center" class="item red"><?php echo array_sum($daoyuan_guiji); ?></td>
		<td align="center" class="item">&nbsp;</td>
	</tr>

	<!-- 主要列表数据 end -->
</table>
</form>

<br>
执行耗时：<?php echo round(now() - $pagebegintime, 4); ?>s
<br>
<br>

</body>
</html>