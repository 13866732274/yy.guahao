<?php
// --------------------------------------------------------
// - 功能说明 : 地区报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-25 15:45
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

// 查询条件:
if ($key = $_GET["key"]) {
	$where = " (content like '%{$key}%' or memo like '%{$key}%') and";
}

if ($_GET["account"]) {
	$where = " account='".$_GET["account"]."' and";
}

// 计算所有地区:
$area_all = $db->query("select tel_location,count(tel_location) as c from $table where $where tel_location!='' and order_date>=$m_begin and order_date<=$m_end group by tel_location order by c desc", "tel_location", "c");

// 合并城市:
$area_use = array();
$first = array_keys($area_all);
$first = array_shift($first); //数量最多的城市一般肯定是本地病人
$area_use[] = $first;
array_shift($area_all);

$area_merge = array();
foreach ($area_all as $k => $v) {
	if (substr_count($k, " ") > 0) {
		list($a, $b) = explode(" ", $k);
	} else {
		$a = $b = $k;
	}
	$area_merge[$a][] = $b;
}

foreach ($area_merge as $k => $v) {
	if (count($area_use) >= 10) {
		break;
	}
	$area_use[] = $k;
}

// 查询本月的所有客服:
$kefu_arr = $db->query("select distinct author from $table where $where tel_location!='' and order_date>=$m_begin and order_date<=$m_end order by binary author", "", "author");

// 所有预约量:
$order_all = $db->query("select author,count(author) as c from $table where $where order_date>=$m_begin and order_date<=$m_end group by author", "author", "c");
$order_come = $db->query("select author,count(author) as c from $table where $where status=1 and order_date>=$m_begin and order_date<=$m_end group by author", "author", "c");


// 每个地区进行一次查询:
$all = $come = array();
foreach ($area_use as $v) {
	// 总计:
	$list = $db->query("select author,count(author) as c from $table where $where tel_location like '{$v}%' and order_date>=$m_begin and order_date<=$m_end group by author", "author", "c");
	$all[$v] = $list;

	// 已到:
	$list = $db->query("select author,count(author) as c from $table where $where status=1 and tel_location like '{$v}%' and order_date>=$m_begin and order_date<=$m_end group by author", "author", "c");
	$come[$v] = $list;
}


?>
<html>
<head>
<title>数据报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
form {display:inline; }
.red {color:#bf0060 !important; }

.report_tips {padding:20px 0 10px 0; text-align:center; font-size:14px; font-weight:bold;  }

.list {border:2px solid silver !important; }
.head {border:0 !important; background:#e1e7ec !important; }
.item {border-top:1px solid #e2e6e7 !important; border-bottom:1px solid #e2e6e7 !important; text-align:center; padding:10px 3px 8px 3px !important; }

.hl {border-left:1px solid silver !important; }
.hr {border-right:1px solid silver !important; }
.ht {border-top:1px solid silver !important; }
.hb {border-bottom:1px solid silver !important; }

.huizong {font-weight:bold; color:#ff8040; background:#eff2f5 !important; }
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
		<b>关键词：</b>
		<input title="将搜索咨询内容和备注" name="key" class="input" style="width:100px" value="<?php echo $_GET["key"]; ?>">&nbsp;
		<b>帐号：</b>
		<select name="account" class="combo" onchange="this.form.submit();">
			<option value="" style="color:silver">-请选择-</option>
			<?php echo list_option($account_array, "_value_", "_value_", $_GET["account"]); ?>
		</select>&nbsp;
		<input type="submit" class="button" value="确定">
	</form>
</div>


<div class="report_tips"><?php echo $h_name; ?> <?php echo $month; ?> 预约病人地区分析</div>

<table class="list" width="100%">
	<tr>
		<th class="head hb"></th>

		<th class="head hb hl red" colspan="2">所有地区合计</th>

<?php foreach ($area_use as $v) { ?>
		<th class="head hb hl red" colspan="2"><?php echo $v; ?></th>
<?php } ?>
	</tr>

	<tr>
		<th class="head hb">客服</th>

		<th class="head hb hl">全部</th>
		<th class="head hb">已到</th>

<?php foreach ($area_use as $v) { ?>
		<th class="head hb hl">全部</th>
		<th class="head hb">已到</th>
<?php } ?>
	</tr>

<?php
	$sum = array();
	foreach ($kefu_arr as $kf) {
		$sum["Dall"] = intval($sum["Dall"]) + $order_all[$kf];
		$sum["Dcome"] = intval($sum["Dcome"]) + $order_come[$kf];
?>

	<tr>
		<td class="item"><font class="red"><?php echo $kf; ?></font></td>

		<td class="item hl"><?php echo $order_all[$kf]; ?></td>
		<td class="item"><?php echo $order_come[$kf]; ?></td>

<?php
	foreach ($area_use as $v) {
		$sum["all"][$v] = intval($sum["all"][$v]) + intval($all[$v][$kf]);
		$sum["come"][$v] = intval($sum["come"][$v]) + intval($come[$v][$kf]);

?>
		<td class="item hl"><?php echo $all[$v][$kf]; ?></td>
		<td class="item"><?php echo $come[$v][$kf]; ?></td>
<?php } ?>
	</tr>

<?php } ?>


	<tr>
		<td class="huizong ht item">总计</td>

		<td class="huizong ht item hl"><?php echo $sum["Dall"]; ?></td>
		<td class="huizong ht item"><?php echo $sum["Dcome"]; ?></td>

<?php foreach ($area_use as $v) { ?>
		<td class="huizong ht item hl"><?php echo $sum["all"][$v]; ?></td>
		<td class="huizong ht item"><?php echo $sum["come"][$v]; ?></td>
<?php } ?>
	</tr>

	<tr>
		<td class="huizong ht item">百分比</td>

		<td class="huizong ht item hl">-</td>
		<td class="huizong ht item">-</td>

<?php foreach ($area_use as $v) { ?>
		<td class="huizong ht item hl"><?php echo @round($sum["all"][$v] * 100 / $sum["Dall"], 1); ?>%</td>
		<td class="huizong ht item"><?php echo @round($sum["come"][$v] * 100 / $sum["Dcome"], 1); ?>%</td>
<?php } ?>
	</tr>

</table>


<br>
<br>

</body>
</html>