<?php
/*
// - 功能说明 : 病种统计
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-05-17
*/
require "lib/set_env.php";
set_time_limit(0);

$_GET["show"] = "all";

$table = "patient_".$hid;
$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

// 时间定义
$today_b = mktime(0,0,0); //今天的开始
$today_e = strtotime("+1 day", $today_b) - 1; //今天结束
$yesterday_b = strtotime("-1 day", $today_b); // 昨天
$this_month_b = mktime(0,0,0,date("m"), 1); // 本月开始
$this_month_e = strtotime("+1 month", $this_month_b) - 1; //本月结束
$last_month_e = $this_month_b - 1; // 上个月结束
$last_month_b = strtotime("-1 month", $this_month_b); //上月开始
$tb_b = $last_month_b;
$tb_e = strtotime("-1 month", time());

$time_array = array(
	"今日" => array($today_b, $today_e),
	"昨日" => array($yesterday_b, $today_b - 1),
	"本月" => array($this_month_b, $today_e),
	"同期" => array($tb_b, $tb_e),
	"上月" => array($last_month_b, $last_month_e)
);

/*
if ($debug_mode) {
	echo date("Y-m-d H:i:s", $last_month_b)."<br>";
	echo date("Y-m-d H:i:s", $last_month_e)."<br>";
}
*/

$count_type_array = array(
	1 => "整个医院",
	2 => "网络",
	3 => "电话",
	4 => "网络+电话",
);

if ($_GET["ct"] && array_key_exists($_GET["ct"], $count_type_array)) {
	$count_type = $_GET["ct"];
} else {
	$count_type = 1;
}
$count_type_text = $count_type_array[$count_type];


// 病种列表:
$disease_list = $db->query("select id,name from disease where hospital_id='$hid' order by id asc", "id", "name");

/*
if ($debug_mode) {
	echo "<pre>";
	print_r($disease_list);
}
*/

if ($count_type == 2) {
	$sqlwhere = " and part_id=2";
} else if ($count_type == 3) {
	$sqlwhere = " and part_id=3";
} else if ($count_type == 4) {
	$sqlwhere = " and (part_id in (2,3))";
}

// 针对每个病种进行查询:
// 由于需要查询的sql语句太多,所以考虑把一次读取出来处理(注意这里的数据量可能很多,虽然是一条语句,可能也会很慢)
$datas = $db->query("select disease_id, order_date, addtime, status from $table where (order_date>=$last_month_b or addtime>=$last_month_b) and (order_date<=$this_month_e or addtime<=$this_month_e) $sqlwhere");

/*
if ($debug_mode) {
	echo $db->sql."<br>";
}
*/

// 分析:
$rs = array();
$dsort = array(); //病种排序
foreach ($datas as $v) {
	$d_arr = explode(",", $v["disease_id"]);
	foreach ($d_arr as $did) {
		if (intval($did) > 0) $dsort[$did] = intval($dsort[$did]) + 1;
	}
	foreach ($disease_list as $did => $dname) {
		if (in_array($did, $d_arr)) {
			foreach ($time_array as $ta => $ti) {
				// 预约:
				if ($v["addtime"] >= $ti[0] && $v["addtime"] <= $ti[1]) {
					$rs[$dname][$ta]["预约"] = intval($rs[$dname][$ta]["预约"]) + 1;
				}
				// 预计到院
				if ($v["order_date"] >= $ti[0] && $v["order_date"] <= $ti[1]) {
					$rs[$dname][$ta]["预到"] = intval($rs[$dname][$ta]["预到"]) + 1;
				}
				// 实际到院
				if ($v["order_date"] >= $ti[0] && $v["order_date"] <= $ti[1] && $v["status"] == 1) {
					$rs[$dname][$ta]["实到"] = intval($rs[$dname][$ta]["实到"]) + 1;
				}
			}
		}
	}
}

arsort($dsort);

$cut_disease = 0;

$title = '病种报表';
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
a:hover {text-decoration:underline; }
.edit a {font-weight:bold; }
s {width:90px; display:inline-block; text-decoration:none; }
.t1 {width:80px; text-align:right; font-weight:bold; }
.t2 {width:45px; text-align:center; font-weight:bold; }
.red {color:red; }
.l {text-align:left !important; }
.box_float {float:left; margin-right:10px; margin-bottom:10px; }
.nob {font-weight:normal !important; }


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
<script type="text/javascript">
</script>
</head>

<body>
<!-- 头部 b -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:40%"><nobr class="tips"><?php echo $h_name." - ".$title; ?></nobr></td>
		<td style="width:20%" align="center">
			<button class="buttonb" onclick="location='patient_report_disease.php'" title="点击查看详细报表">详细报表</button>&nbsp;&nbsp;
			<a href="patient_disease_all.php">查看本院汇总</a>
		</td>
		<td class="headers_oprate" style="width:40%"><button onclick="self.location.reload()" class="button">刷新</button></td>
	</tr>
</table>
<!-- 头部 e -->

<div style="margin:30px 0 30px 0; text-align:center; ">
当前显示的为：<b style="color:red"><?php echo $count_type_text; ?></b>，点击查看：
<?php
foreach ($count_type_array as $k => $v) {
	if ($k != $count_type) {
		echo '<button class="buttonb" onclick="location=\'?ct='.$k.'\'">'.$v.'</button>&nbsp;&nbsp;';
	}
}
?>
</div>


<table class="list" width="100%">
	<tr>
		<th class="head hb" rowspan="2">--疾病--</th>
		<th class="head hb hl red" colspan="3">今日</th>
		<th class="head hb hl red" colspan="3">昨日</th>
		<th class="head hb hl red" colspan="3">本月</th>
		<th class="head hb hl red" colspan="3">同期</th>
		<th class="head hb hl red" colspan="3">上月</th>
	</tr>

	<tr>

		<th class="head hb hl">预约</th>
		<th class="head hb">预到</th>
		<th class="head hb">实到</th>

		<th class="head hb hl">预约</th>
		<th class="head hb">预到</th>
		<th class="head hb">实到</th>

		<th class="head hb hl">预约</th>
		<th class="head hb">预到</th>
		<th class="head hb">实到</th>

		<th class="head hb hl">预约</th>
		<th class="head hb">预到</th>
		<th class="head hb">实到</th>

		<th class="head hb hl">预约</th>
		<th class="head hb">预到</th>
		<th class="head hb">实到</th>

	</tr>


<?php
foreach ($dsort as $did => $sort_num) {
	if (!array_key_exists($did, $disease_list)) continue;
	$dname = $disease_list[$did];
	$r = $rs[$dname];
	$sum[1] += intval($r["今日"]["预约"]);
	$sum[2] += intval($r["今日"]["预到"]);
	$sum[3] += intval($r["今日"]["实到"]);

	$sum[4] += intval($r["昨日"]["预约"]);
	$sum[5] += intval($r["昨日"]["预到"]);
	$sum[6] += intval($r["昨日"]["实到"]);

	$sum[7] += intval($r["本月"]["预约"]);
	$sum[8] += intval($r["本月"]["预到"]);
	$sum[9] += intval($r["本月"]["实到"]);

	$sum[10] += intval($r["同期"]["预约"]);
	$sum[11] += intval($r["同期"]["预到"]);
	$sum[12] += intval($r["同期"]["实到"]);

	$sum[13] += intval($r["上月"]["预约"]);
	$sum[14] += intval($r["上月"]["预到"]);
	$sum[15] += intval($r["上月"]["实到"]);

?>

	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td class="item"><font class="red"><?php echo $dname; ?></font></td>

		<td class="item hl"><?php echo intval($r["今日"]["预约"]); ?></td>
		<td class="item"><?php echo intval($r["今日"]["预到"]); ?></td>
		<td class="item"><?php echo intval($r["今日"]["实到"]); ?></td>

		<td class="item hl"><?php echo intval($r["昨日"]["预约"]); ?></td>
		<td class="item"><?php echo intval($r["昨日"]["预到"]); ?></td>
		<td class="item"><?php echo intval($r["昨日"]["实到"]); ?></td>

		<td class="item hl"><?php echo intval($r["本月"]["预约"]); ?></td>
		<td class="item"><?php echo intval($r["本月"]["预到"]); ?></td>
		<td class="item"><?php echo intval($r["本月"]["实到"]); ?></td>

		<td class="item hl"><?php echo intval($r["同期"]["预约"]); ?></td>
		<td class="item"><?php echo intval($r["同期"]["预到"]); ?></td>
		<td class="item"><?php echo intval($r["同期"]["实到"]); ?></td>

		<td class="item hl"><?php echo intval($r["上月"]["预约"]); ?></td>
		<td class="item"><?php echo intval($r["上月"]["预到"]); ?></td>
		<td class="item"><?php echo intval($r["上月"]["实到"]); ?></td>

	</tr>

<?php } ?>

	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td class="huizong ht item">-总计-</td>

		<td class="huizong ht item hl"><?php echo $sum[1]; ?></td>
		<td class="huizong ht item"><?php echo $sum[2]; ?></td>
		<td class="huizong ht item"><?php echo $sum[3]; ?></td>

		<td class="huizong ht item hl"><?php echo $sum[4]; ?></td>
		<td class="huizong ht item"><?php echo $sum[5]; ?></td>
		<td class="huizong ht item"><?php echo $sum[6]; ?></td>

		<td class="huizong ht item hl"><?php echo $sum[7]; ?></td>
		<td class="huizong ht item"><?php echo $sum[8]; ?></td>
		<td class="huizong ht item"><?php echo $sum[9]; ?></td>

		<td class="huizong ht item hl"><?php echo $sum[10]; ?></td>
		<td class="huizong ht item"><?php echo $sum[11]; ?></td>
		<td class="huizong ht item"><?php echo $sum[12]; ?></td>

		<td class="huizong ht item hl"><?php echo $sum[13]; ?></td>
		<td class="huizong ht item"><?php echo $sum[14]; ?></td>
		<td class="huizong ht item"><?php echo $sum[15]; ?></td>

	</tr>

</table>


<br>

</body>
</html>