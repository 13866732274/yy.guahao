<?php
// --------------------------------------------------------
// - 功能说明 : 消费报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2012-09-12
// --------------------------------------------------------
require "lib/set_env.php";
check_power('', $pinfo) or exit("没有打开权限...");
$hid = $user_hospital_id;

if ($hid == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$table = "patient_".$hid;


// 列表可用的月份：
$m_arr = array();
$dt_begin = strtotime(date("Y-m")."-01 0:0:0");
for ($i = 0; $i < 24; $i++) {
	$dt = $i > 0 ? strtotime("-{$i} month", $dt_begin) : $dt_begin;
	$m_arr[date("Y-m", $dt)] = date("Y年m月", $dt);
}


// 医院名称:
$h_name = $db->query("select name from hospital where id=$hid limit 1", "1", "name");

// 月份（包括计算该月起始时间）:
$month = $_GET["m"];
if (!$month) {
	$_GET["m"] = $month = date("Y-m");
}

$m_begin = strtotime($month."-1 0:0:0");
$m_end = strtotime("+1 month", $m_begin) - 1;


// 查询本月的所有客服:
$kefu_arr = $db->query("select author,count(author) as c from $table where xiaofei_count>0 and order_date>=$m_begin and order_date<=$m_end group by author order by c desc", "", "author");

// 所有疾病:
/*
$disease_arr = $db->query("select disease_id,count(disease_id) as c from $table where disease_id>0 and xiaofei_count>0 and order_date>=$m_begin and order_date<=$m_end group by disease_id", "", "disease_id");
if (count($disease_arr) > 15) {
	$disease_arr = array_slice($disease_arr, 0, 15);
}
*/

// 数据统计
$tmp_data = $db->query("select disease_id, author, xiaofei_count from $table where xiaofei_count>0 and order_date>=$m_begin and order_date<=$m_end");
$d = $dis_sum = $author_sum = array();
foreach ($tmp_data as $li) {
	if ($li["disease_id"] > 0) {
		$d[$li["author"]][$li["disease_id"]] = intval($d[$li["author"]][$li["disease_id"]]) + $li["xiaofei_count"];
		$dis_sum[$li["disease_id"]] = intval($dis_sum[$li["disease_id"]]) + $li["xiaofei_count"];
	}
	$author_sum[$li["author"]] = intval($author_sum[$li["author"]]) + $li["xiaofei_count"];
}

arsort($dis_sum);
$disease_arr = array_keys($dis_sum);
if (count($disease_arr) > 15) {
	$disease_arr = array_slice($disease_arr, 0, 15);
}

$disease_id_name = $db->query("select id, name from disease where hospital_id=$hid", "id", "name");


?>
<html>
<head>
<title>数据报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
* {font-family:"Tahoma"; }
form {display:inline; }
.red {color:red !important; }

.report_tips {padding:20px 0 10px 0; text-align:center; font-size:14px; font-weight:bold;  }

.list {border:2px solid #6AB5FF !important; }
.list th, .list td {border:1px solid #A4D1FF !important; }
.head {text-align:center; }
.item {text-align:center; padding:6px 3px 4px 3px !important; }
.bg1 {background-color:#fff3ec; }
</style>
</head>

<body>
<div style="margin:10px 0 0 0px;">
	<form method="GET">
		<b>月份：</b>
		<select name="m" class="combo" onchange="this.form.submit();">
			<option value="" style="color:silver">-请选择-</option>
			<?php echo list_option($m_arr, "_key_", "_value_", $_GET["m"]); ?>
		</select>&nbsp;
		<input type="submit" class="button" value="确定">
	</form>
</div>


<div class="report_tips"><?php echo $h_name; ?> <?php echo $month; ?> 消费分析</div>

<table class="list" width="100%">

	<tr>
		<th class="head hb">客服</th>
		<th class="head hb bg1">客服汇总</th>

<?php foreach ($disease_arr as $did) { ?>
		<th class="head hb hl red"><?php echo $disease_id_name[$did]; ?></th>
<?php } ?>
	</tr>

<?php
	foreach ($kefu_arr as $kf) {
?>
	<tr>
		<td class="item"><?php echo $kf; ?></td>
		<td class="item bg1"><?php echo $author_sum[$kf]; ?></td>

<?php foreach ($disease_arr as $did) { ?>
		<td class="item"><?php echo $d[$kf][$did]; ?></td>
<?php } ?>
	</tr>
<?php } ?>

	<tr>
		<td class="head ht">汇总</td>
		<td class="head hb bg1 red" title="此即当月总消费"><?php echo array_sum($author_sum); ?></td>

<?php foreach ($disease_arr as $did) { ?>
		<td class="head hb hl"><?php echo $dis_sum[$did]; ?></td>
<?php } ?>
	</tr>

	<tr>
		<td class="head ht">百分比</td>
		<td class="head hb bg1"></td>

<?php
$dall = array_sum($author_sum);
?>
<?php foreach ($disease_arr as $did) { ?>
		<td class="head hb hl"><?php echo @round(100 * $dis_sum[$did] / $dall, 1); ?>%</td>
<?php } ?>
	</tr>

</table>


<br>
<br>

</body>
</html>