<?php
/*
// - 功能说明 : 整个医院病种叠加统计
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-11-18
*/
require "lib/set_env.php";

$h_name = $hinfo["name"];
$sname = $hinfo["sname"];
$all_hids = $db->query("select id from hospital where ishide=0 and sname='$sname'", "", "id");


$this_month_b = strtotime(date("Y-m-01"));

$month_arr = array();
for ($i=0; $i<=6; $i++) {
	$mb = strtotime("-{$i} month", $this_month_b);
	$me = strtotime("+1 month", $mb) - 1;
	$mn = date("Y-m", $mb);
	$month_arr[$mn] = array($mb, $me);
}

if (!isset($_GET["month"])) {
	$_GET["month"] = date("Y-m");
}

if (!array_key_exists($_GET["month"], $month_arr)) {
	exit("参数错误: ".$_GET["month"]);
}




foreach ($all_hids as $hid) {

	// 病种列表:
	$disease_id_name = $db->query("select id,name from disease where hospital_id='$hid' order by id asc", "id", "name");
	foreach ($disease_id_name as $k => $v) {
		$v = trim($v);
		$all_disease_arr[$v] += 1;
	}

	foreach ($month_arr as $mname => $mdef) {
		list($m_begin, $m_end) = $mdef;
		$datas = $db->query("select disease_id, count(*) as c from patient_{$hid} where order_date>=$m_begin and order_date<=$m_end and disease_id>0 and status=1 group by disease_id", "disease_id", "c");
		foreach ($datas as $did => $c) {
			$dname = trim($disease_id_name[$did]);
			if ($dname != "") {
				$all_data[$dname][$mname] += $c;
			}
		}
	}
}

ksort($all_disease_arr);






?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.report_tips {padding:20px; text-align:center; font-size:15px; font-weight:bold; }
.list .head {text-align:left; }
.list .item {text-align:left; }
</style>
</head>

<body>


<div class="report_tips"><?php echo $sname; ?> 疾病到诊汇总统计表 (含自然到诊)</div>

<?php $per = round(80 / count($month_arr), 3)."%"; ?>

<table id="list_float_head" style="display:none;" width="100%" class="list">
	<tr>
		<th class="head" width="15%">疾病名称</th>
		<th class="head" width="5%" style="text-align:center;">来源科室</th>
<?php foreach ($month_arr as $mname => $mdef) { ?>
		<th class="head" width="<?php echo $per; ?>" style="text-align:center;"><?php echo $mname; ?></th>
<?php } ?>
	</tr>
</table>


<table id="list" class="list" width="100%">
	<tr>
		<th class="head" width="15%">疾病名称</th>
		<th class="head" width="5%" style="text-align:center;">来源科室</th>
<?php foreach ($month_arr as $mname => $mdef) { ?>
		<th class="head" width="<?php echo $per; ?>" style="text-align:center;"><?php echo $mname; ?></th>
<?php } ?>
	</tr>


<?php
	foreach ($all_disease_arr as $dname => $count) {
		//if ($count == 1) continue;
		if (array_sum($all_data[$dname]) == 0) {
			continue;
		}
?>

	<tr>
		<td class="item"><b><?php echo $dname; ?></b></td>
		<td class="item" style="text-align:center;"><?php echo $count; ?></td>
	<?php foreach ($month_arr as $mname => $mdef) { ?>
		<td class="item" style="text-align:center;"><?php echo $all_data[$dname][$mname]; ?></td>
	<?php } ?>
	</tr>
<?php } ?>

</table>

<br>
<br>
<br>
<br>

<script type="text/javascript">
function scroll_table() {
	var s_top = document.body.scrollTop;
	var top = byid(float_table).offsetTop;
	var top_head = byid(float_table+"_float_head").offsetHeight;
	byid("list_float_head").style.width = byid("list").offsetWidth;

	if (s_top >= (0 + top + top_head)) {
		var o = byid(float_table+"_float_head");
		o.style.display = "";
		o.style.position = "absolute";
		o.style.top = s_top;
	} else {
		byid(float_table+"_float_head").style.display = "none";
	}
};

function make_float(table_id) {
	window.onscroll = scroll_table;
}

var float_table = "list";
make_float(float_table);
</script>


</body>
</html>