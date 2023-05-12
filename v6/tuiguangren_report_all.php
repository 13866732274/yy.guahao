<?php
// --------------------------------------------------------
// - 功能说明 : 推广人统计报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2015-9-12
// --------------------------------------------------------
require "lib/set_env.php";

if ($hid <= 0) exit("请先选择医院科室");

$hospital_name = $hinfo["sname"];

// 时间的起始点都是 YYYY-MM-DD 00:00:00 结束则是 YYYY-MM-DD 23:59:59
$today_tb = mktime(0,0,0); //今天开始
$today_te = strtotime("+1 day", $today_tb) - 1; //今天结束

$tomorrow_tb = $today_te + 1; //明天开始
$tomorrow_te = strtotime("+1 day", $tomorrow_tb) - 1; //明天结束

$yesterday_tb = strtotime("-1 day", $today_tb); //昨天开始
$yesterday_te = $today_tb - 1; //昨天结束

$month_tb = mktime(0, 0, 0, date("m"), 1); //本月开始
$month_te = strtotime("+1 month", $month_tb) - 1; //本月结束

$lastmonth_tb = strtotime("-1 month", $month_tb); //上月开始
$lastmonth_te = $month_tb - 1; //上月结束

// 自定义月份:
$t = strtotime(date("Y-m-01"));
for ($i=2; $i<=7; $i++) {
	$t0 = strtotime("-".$i." month", $t);
	$his_month[date("Y-m", $t0)] = date("Y-m", $t0);
}

$show_his_month = $_GET["his_month"] != '' ? 1 : 0;
if ($show_his_month) {
	$his_tb = strtotime($_GET["his_month"]."-01");
	$his_te = strtotime("+1 month", $his_tb) - 1;
}

$hospital_id_name = $db->query("select id, depart from hospital where ishide=0 and sname='$hospital_name' order by name asc", "id", "depart");

$huizong_keshi = $_GET["hids"];
if (!is_array($huizong_keshi) || count($huizong_keshi) == 0) {
	$huizong_keshi = array_keys($hospital_id_name); //默认汇总所有科室
}

$tuiguang_data = $sum = array();

foreach ($huizong_keshi as $hid) {
	$table = "patient_".$hid;
	$hname = $hospital_id_name[$hid];

	// 一次取出所需记录 将比分别查询快速的多:
	if ($show_his_month) {
		$sql_add = "(((addtime>=$his_tb or order_date>=$his_tb) and (addtime<=$his_te or order_date<=$his_te)) or ((addtime>=$lastmonth_tb or order_date>=$lastmonth_tb) and (addtime<=$month_te or order_date<=$month_te)))";
	} else {
		$sql_add = "(addtime>=$lastmonth_tb or order_date>=$lastmonth_tb) and (addtime<=$month_te or order_date<=$month_te)";
	}
	$all_data = $db->query("select tuiguangren,status,order_date,addtime from $table where $sql_add and tuiguangren!=''");

	foreach ($all_data as $li) {
		if (is_numeric($li["tuiguangren"])) {
			$li["tuiguangren"] = " ".$li["tuiguangren"];
		}
		if ($li["addtime"] >= $today_tb && $li["addtime"] <= $today_te) {
			$tuiguang_data[$li["tuiguangren"]][1] += 1;
		}
		if ($li["order_date"] >= $today_tb && $li["order_date"] <= $today_te && $li["status"] == 1) {
			$tuiguang_data[$li["tuiguangren"]][2] += 1;
		}

		if ($li["addtime"] >= $yesterday_tb && $li["addtime"] <= $yesterday_te) {
			$tuiguang_data[$li["tuiguangren"]][3] += 1;
		}
		if ($li["order_date"] >= $yesterday_tb && $li["order_date"] <= $yesterday_te && $li["status"] == 1) {
			$tuiguang_data[$li["tuiguangren"]][4] += 1;
		}

		if ($li["addtime"] >= $month_tb && $li["addtime"] <= $month_te) {
			$tuiguang_data[$li["tuiguangren"]][5] += 1;
		}
		if ($li["order_date"] >= $month_tb && $li["order_date"] <= $month_te && $li["status"] == 1) {
			$tuiguang_data[$li["tuiguangren"]][6] += 1;
		}

		if ($li["addtime"] >= $lastmonth_tb && $li["addtime"] <= $lastmonth_te) {
			$tuiguang_data[$li["tuiguangren"]][7] += 1;
		}
		if ($li["order_date"] >= $lastmonth_tb && $li["order_date"] <= $lastmonth_te && $li["status"] == 1) {
			$tuiguang_data[$li["tuiguangren"]][8] += 1;
		}

		if ($show_his_month) {
			if ($li["addtime"] >= $his_tb && $li["addtime"] <= $his_te) {
				$tuiguang_data[$li["tuiguangren"]][9] += 1;
			}
			if ($li["order_date"] >= $his_tb && $li["order_date"] <= $his_te && $li["status"] == 1) {
				$tuiguang_data[$li["tuiguangren"]][10] += 1;
			}
		}
	}
}


// 不重复的推广人:
$tuiguangren_arr = array_keys($tuiguang_data);
sort($tuiguangren_arr);

// 汇总
foreach ($tuiguang_data as $v) {
	$sum[1] += $v[1];
	$sum[2] += $v[2];
	$sum[3] += $v[3];
	$sum[4] += $v[4];
	$sum[5] += $v[5];
	$sum[6] += $v[6];
	$sum[7] += $v[7];
	$sum[8] += $v[8];
	$sum[9] += $v[9];
	$sum[10] += $v[10];
}


?>
<html>
<head>
<title>推广人统计报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
* {font-family:"微软雅黑"; }
.center_show {margin:0 auto; width:900px; text-align:center; }
.list {border:2px solid silver; }
.head {color:#bf0060 !important; border:1px solid silver !important; background:#e1e7ec !important; }
.item {text-align:center; padding:4px !important; border-top:1px solid silver !important; border-bottom:1px solid silver !important; }
.bl {border-left:1px solid silver !important; }
#title_div {font-size:16px; font-weight:bold; margin-top:20px; text-align:center; }
</style>

</head>

<body>

<div class="center_show">

	<div id="title_div">
		<?php echo $hospital_name." (".count($huizong_keshi)."个科室)  推广人汇总统计"; ?>
	</div>

	<div style="margin-top:10px;">
	<form method="GET" action="" onsubmit="" id="form_1">
		<script type="text/javascript">
		function wee_select_none() {
			var objs = byid("form_1").getElementsByTagName("INPUT");
			for (var i=0; i<objs.length; i++) {
				if (objs[i].type == "checkbox") {
					objs[i].checked = false;
				}
			}
		}
		</script>
		<a href="javascript:;" onclick="wee_select_none();">[清空]</a>&nbsp;
<?php foreach ($hospital_id_name as $_hid => $_hname) { ?>
		<input type="checkbox" name="hids[]" onclick="this.form.submit();" value="<?php echo $_hid; ?>" <?php echo in_array($_hid, $huizong_keshi) ? "checked" : ""; ?> id="h_<?php echo $_hid; ?>"><label for="h_<?php echo $_hid; ?>"><?php echo $_hname; ?></label>
<?php } ?>
		<!-- <input type="hidden" name="his_month" value="<?php echo $_GET["his_month"]; ?>"> -->
		<select name="his_month" class="combo" onchange="this.form.submit();" style="margin-left:20px;">
			<option value="" style="color:gray">--月份--</option>
			<?php echo list_option($his_month, "_key_", "_value_", $_GET["his_month"]); ?>
		</select>
	</form>
	</div>

	<table class="list" width="100%" style="margin-top:10px;">
		<tr>
			<th class="head"></th>
<?php if ($show_his_month) { ?>
			<th class="head" colspan="2"><?php echo $_GET["his_month"]; ?></th>
<?php } ?>
			<th class="head" colspan="2">上月</th>
			<th class="head" colspan="2">本月</th>
			<th class="head" colspan="2">昨日</th>
			<th class="head" colspan="2">今日</th>
		</tr>

		<tr>
			<th class="head">---推广人---</th>

<?php if ($show_his_month) { ?>
			<th class="head">预约</th>
			<th class="head">到院</th>
<?php } ?>

			<th class="head">预约</th>
			<th class="head">到院</th>

			<th class="head">预约</th>
			<th class="head">到院</th>

			<th class="head">预约</th>
			<th class="head">到院</th>

			<th class="head">预约</th>
			<th class="head">到院</th>
		</tr>


<?php foreach ($tuiguangren_arr as $k) { ?>
		<tr>
			<td class="item"><?php echo $k; ?></td>

<?php if ($show_his_month) { ?>
			<td class="item bl"><?php echo $tuiguang_data[$k][9]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][10]; ?></td>
<?php } ?>

			<td class="item bl"><?php echo $tuiguang_data[$k][7]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][8]; ?></td>

			<td class="item bl"><?php echo $tuiguang_data[$k][5]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][6]; ?></td>

			<td class="item bl"><?php echo $tuiguang_data[$k][3]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][4]; ?></td>

			<td class="item bl"><?php echo $tuiguang_data[$k][1]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][2]; ?></td>
		</tr>

<?php } ?>

		<tr style="color:red">
			<td class="item">汇总</td>

<?php if ($show_his_month) { ?>
			<td class="item bl"><?php echo $sum[9]; ?></td>
			<td class="item"><?php echo $sum[10]; ?></td>
<?php } ?>

			<td class="item bl"><?php echo $sum[7]; ?></td>
			<td class="item"><?php echo $sum[8]; ?></td>

			<td class="item bl"><?php echo $sum[5]; ?></td>
			<td class="item"><?php echo $sum[6]; ?></td>

			<td class="item bl"><?php echo $sum[3]; ?></td>
			<td class="item"><?php echo $sum[4]; ?></td>

			<td class="item bl"><?php echo $sum[1]; ?></td>
			<td class="item"><?php echo $sum[2]; ?></td>
		</tr>

	</table>
</div>
<br>
<br>
<br>
<br>
<br>

</body>
</html>