<?php
// --------------------------------------------------------
// - 功能说明 : 电话 数据对比 按周
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2010-11-25 16:05
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_tel";

// 所有可管理项目:
if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
	$types = $db->query("select id,name from count_type where ishide=0 and type='tel' order by id asc", "id", "name");
} else {
	$hids = implode(",", $hospital_ids);
	$types = $db->query("select id,name from count_type where ishide=0 and type='tel' and hid in ($hids) order by id asc", "id", "name");
}
if (count($types) == 0) {
	exit("没有可以管理的项目");
}

$cur_type = $_SESSION["count_type_id_tel"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_tel"] = $type_ids[0];
}

// 操作的处理:
if ($op = $_REQUEST["op"]) {
	if ($op == "change_type") {
		$cur_type = $_SESSION["count_type_id_tel"] = intval($_GET["type_id"]);
	}
}

$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);
$kefu_list = $type_detail["kefu"] ? explode(",", $type_detail["kefu"]) : array();


// 初始值为本月:
if ($_GET["month"] == '') {
	$_GET["month"] = date("Y-m", mktime(0,0,0,date("m"), 1));
}

$m = strtotime($_GET["month"]."-1 0:0:0");
$m_end = date("d", strtotime("+1 month", $m) - 1);
$week_array = array(
	1 => array(date("Y-m-1", $m), date("Y-m-7", $m)),
	2 => array(date("Y-m-8", $m), date("Y-m-14", $m)),
	3 => array(date("Y-m-15", $m), date("Y-m-21", $m)),
	4 => array(date("Y-m-22", $m), date("Y-m-".$m_end, $m)),
);




// 处理数据:
if ($cur_type && $_GET["month"]) {

	$all = array();

	// 查询每周数据:
	foreach ($week_array as $wi => $w) {

		// 时间段:
		$btime = strtotime($w[0]." 0:0:0");
		$etime = strtotime($w[1]." 23:59:59");

		$b = date("Ymd", $btime);
		$e = date("Ymd", $etime);

		//查询总医院汇总数据:
		$tmp_list = $db->query("select * from $table where type_id=$cur_type and date>=$b and date<=$e order by kefu asc,date asc");

		// 计算该阶段汇总:
		$list = $dt_count = array();
		foreach ($tmp_list as $v) {
			$dt = $v["kefu"];
			$dt_count[$dt] += 1;
			foreach ($v as $a => $b) {
				if ($b && is_numeric($b)) {
					$list[$dt][$a] = floatval($list[$dt][$a]) + $b;
				}
			}
		}

		// 计算数据:
		foreach ($list as $k => $v) {
			// 咨询预约率:
			$list[$k]["per_1"] = @round($v["yuyue"] / $v["tel_all"] * 100, 2);
			// 预约就诊率:
			$list[$k]["per_2"] = @round($v["jiuzhen"] / $v["yuyue"] * 100, 2);
			// 咨询就诊率:
			$list[$k]["per_3"] = @round($v["jiuzhen"] / $v["tel_all"] * 100, 2);
			// 有效咨询率:
			$list[$k]["per_4"] = @round($v["tel_ok"] / $v["tel_all"] * 100, 2);
		}

		// 计算统计数据:
		$cal_field = explode(" ", "tel_all tel_ok yuyue jiuzhen wangluo zazhi laobao xinbao t400 t114 jieshao luguo qita per_1 per_2 per_3 per_4");

		// 加入总数组:
		foreach ($list as $k => $v) {
			foreach ($cal_field as $v2) {
				$all[$k][$wi][$v2] = $v[$v2];
			}
		}


		// 汇总计算:
		$sum_list = array();
		foreach ($list as $v) {
			foreach ($cal_field as $f) {
				$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
			}
		}
		$all["sum"][$wi]["per_1"] = @round($sum_list["yuyue"] / $sum_list["tel_all"] * 100, 2);
		$all["sum"][$wi]["per_2"] = @round($sum_list["jiuzhen"] / $sum_list["yuyue"] * 100, 2);
		$all["sum"][$wi]["per_3"] = @round($sum_list["jiuzhen"] / $sum_list["tel_all"] * 100, 2);
		$all["sum"][$wi]["per_4"] = @round($sum_list["tel_ok"] / $sum_list["tel_all"] * 100, 2);
	}
}


// 页面开始 ------------------------
?>
<html>
<head>
<title>电话数据统计 - 按周</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
body {padding:5px 8px; }
form {display:inline; }
#date_tips {font-weight:bold; padding-top:1px; }
#ch_date {margin-left:20px; }
.site_name {display:block; padding:4px 0px;}
.site_name, .site_name a {font-family:"Arial", "Tahoma"; }
.ch_date_a b, .ch_date_a a {font-family:"Arial"; }
.ch_date_a b {border:0px; padding:1px 5px 1px 5px; color:red; }
.ch_date_a a {border:0px; padding:1px 5px 1px 5px; }
.ch_date_a a:hover {border:1px solid silver; padding:0px 4px 0px 4px; }
.ch_date_b {padding-top:8px; text-align:left; width:80%; color:silver; }
.ch_date_b a {padding:0 3px; }

.main_title {margin:0 auto; padding:20px; text-align:center; font-weight:bold; font-size:15px; }

.list {border:2px solid #c5d1e4 !important; }
.item {padding:8px 3px 6px 3px !important; }

.hl {border-left:2px solid #c5d1e4 !important; }
.hr {border-right:2px solid #c5d1e4 !important; }
.ht {border-top:2px solid #c5d1e4 !important; }
.hb {border-bottom:2px solid #c5d1e4 !important; }

.rate_tips {padding:30px 0 0 30px; line-height:24px; }

</style>

<script language="javascript">
function update_date(type, o) {
	byid("date_"+type).value = parseInt(o.innerHTML, 10);

	var a = parseInt(byid("date_1").value, 10);
	var b = parseInt(byid("date_2").value, 10);

	var s = a + '' + (b<10 ? "0" : "") + b;

	byid("date").value = s;
	byid("ch_date").submit();
}

function hgo(dir, o) {
	var obj = byid("type_id");
	if (dir == "up") {
		if (obj.selectedIndex > 1) {
			obj.selectedIndex = obj.selectedIndex - 1;
			obj.onchange();
			o.disabled = true;
		} else {
			parent.msg_box("已经是最前了", 3);
		}
	}
	if (dir == "down") {
		if (obj.selectedIndex < obj.options.length-1) {
			obj.selectedIndex = obj.selectedIndex + 1;
			obj.onchange();
			o.disabled = true;
		} else {
			parent.msg_box("已经是最后一个了", 3);
		}
	}
}
</script>
</head>

<body>
<div style="margin:10px auto; text-align:center;">
	<a href="count_tel.php">[返回]</a>
	<form method="GET" style="margin-left:30px;">
		<select name="type_id" id="type_id" class="combo" onchange="this.form.submit()">
			<option value="" style="color:gray">-请选择项目-</option>
			<?php echo list_option($types, "_key_", "_value_", $cur_type); ?>
		</select>&nbsp;
		<button class="button" onclick="hgo('up',this);">上</button>&nbsp;
		<button class="button" onclick="hgo('down',this);">下</button>
		<input type="hidden" name="month" value="<?php echo $_GET["month"]; ?>">
		<input type="hidden" name="op" value="change_type">
	</form>&nbsp;&nbsp;&nbsp;

	<b>月份：</b>
	<form method="GET">
		<input name="month" id="time_month" class="input" style="width:100px" value="<?php echo $_GET["month"]; ?>"> <img src="image/calendar.gif" id="order_date" onClick="picker({el:'time_month',dateFmt:'yyyy-MM'})" align="absmiddle" style="cursor:pointer" title="选择月份">
		<input type="submit" class="button" value="确定">
	</form>
</div>


<?php if ($cur_type && $_GET["month"]) { ?>

<div class="main_title"><?php echo $type_detail["name"]; ?> <?php echo $_GET["month"]; ?> 周数据对比</div>

<table width="100%" align="center" class="list">
	<tr>
		<td class="head hb" align="center"></td>
		<td class="head hl hb" colspan="4" align="center" style="color:red">咨询预约率</td>
		<td class="head hl hb" colspan="4" align="center" style="color:red">预约就诊率</td>
		<td class="head hl hb" colspan="4" align="center" style="color:red">咨询就诊率</td>
		<td class="head hl hb" colspan="4" align="center" style="color:red">有效咨询率</td>
	</tr>
	<tr>
		<td class="head hb" align="center">客服</td>

		<td class="head hl hb" align="center">1-7</td>
		<td class="head hb" align="center">8-14</td>
		<td class="head hb" align="center">15-21</td>
		<td class="head hb" align="center">22-31</td>

		<td class="head hl hb" align="center">1-7</td>
		<td class="head hb" align="center">8-14</td>
		<td class="head hb" align="center">15-21</td>
		<td class="head hb" align="center">22-31</td>

		<td class="head hl hb" align="center">1-7</td>
		<td class="head hb" align="center">8-14</td>
		<td class="head hb" align="center">15-21</td>
		<td class="head hb" align="center">22-31</td>

		<td class="head hl hb" align="center">1-7</td>
		<td class="head hb" align="center">8-14</td>
		<td class="head hb" align="center">15-21</td>
		<td class="head hb" align="center">22-31</td>

	</tr>

<?php
foreach ($kefu_list as $i) {
	$li = $all[$i];
	if (!is_array($li)) {
		$li = array();
	}

?>
	<tr>
		<td class="item" align="center"><?php echo $i; ?></td>

		<td class="item hl" align="center"><?php echo floatval($li["1"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["4"]["per_1"]); ?>%</td>

		<td class="item hl" align="center"><?php echo floatval($li["1"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["4"]["per_2"]); ?>%</td>

		<td class="item hl" align="center"><?php echo floatval($li["1"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["4"]["per_3"]); ?>%</td>

		<td class="item hl" align="center"><?php echo floatval($li["1"]["per_4"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_4"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_4"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["4"]["per_4"]); ?>%</td>

	</tr>

<?php } ?>

		<td class="item ht" align="center">汇总</td>

		<td class="item hl ht" align="center"><?php echo floatval($all["sum"]["1"]["per_1"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["2"]["per_1"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["3"]["per_1"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["4"]["per_1"]); ?>%</td>

		<td class="item hl ht" align="center"><?php echo floatval($all["sum"]["1"]["per_2"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["2"]["per_2"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["3"]["per_2"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["4"]["per_2"]); ?>%</td>

		<td class="item hl ht" align="center"><?php echo floatval($all["sum"]["1"]["per_3"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["2"]["per_3"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["3"]["per_3"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["4"]["per_3"]); ?>%</td>

		<td class="item hl ht" align="center"><?php echo floatval($all["sum"]["1"]["per_4"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["2"]["per_4"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["3"]["per_4"]); ?>%</td>
		<td class="item ht" align="center"><?php echo floatval($all["sum"]["4"]["per_4"]); ?>%</td>

	</tr>
</table>

<!-- <div class="rate_tips">
咨询预约率 = 预约人数 / 总点击<br>
预约就诊率 = 实际到院人数 / 预计到院人数<br>
咨询就诊率 = 实际到院人数 / 总点击<br>
有效咨询率 = 有效点击 / 总点击<br>
有效预约率 = 预约人数 / 有效点击<br>
</div> -->

<?php } ?>

<br>
<br>

</body>
</html>
