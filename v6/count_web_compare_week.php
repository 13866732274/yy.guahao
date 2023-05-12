<?php
// --------------------------------------------------------
// - 功能说明 : 网络 数据对比 按周
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2010-11-04 15:09
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_web";

// 所有可管理项目:
if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
	$types = $db->query("select id,name from count_type where ishide=0 and type='web' order by name asc", "id", "name");
} else {
	$hids = implode(",", $hospital_ids);
	$types = $db->query("select id,name from count_type where ishide=0 and type='web' and hid in ($hids) order by name asc", "id", "name");
}
if (count($types) == 0) {
	exit("没有可以管理的项目");
}

$cur_type = $_SESSION["count_type_id_web"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_web"] = $type_ids[0];
}

// 操作的处理:
if ($op = $_REQUEST["op"]) {
	if ($op == "change_type") {
		$cur_type = $_SESSION["count_type_id_web"] = intval($_GET["type_id"]);
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
$int_month = date("Ym", $m);


$week_array = array(
	1 => array(date("Y-m-1", $m), date("Y-m-10", $m)),
	2 => array(date("Y-m-11", $m), date("Y-m-20", $m)),
	3 => array(date("Y-m-21", $m), date("Y-m-".$m_end, $m)),
);



// 处理数据:
if ($cur_type && $_GET["month"]) {

	$kefu_arr = array();

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

			if (!in_array($dt, $kefu_arr)) {
				$kefu_arr[] = $dt;
			}

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
			$list[$k]["per_1"] = @round($v["talk_swt"] / $v["click"] * 100, 2);
			// 预约就诊率:
			$list[$k]["per_2"] = @round($v["come"] / $v["orders_swt"] * 100, 2);
			// 咨询就诊率:
			$list[$k]["per_3"] = @round($v["come"] / $v["click"] * 100, 2);
			// 有效咨询率:
			$list[$k]["per_4"] = @round($v["ok_click"] / $v["click"] * 100, 2);
			// 有效预约率:
			$list[$k]["per_5"] = @round($v["talk_swt"] / $v["ok_click"] * 100, 2);
		}

		// 计算统计数据:
		$cal_field = explode(" ", "click click_local click_other zero_talk ok_click ok_click_local ok_click_other talk talk_swt talk_tel talk_other orders orders_swt orders_tel orders_other come_all come come_tel come_other per_1 per_2 per_3 per_4 per_5");

		// 加入总数组:
		foreach ($list as $k => $v) {
			foreach ($cal_field as $v2) {
				$all[$k][$wi][$v2] = $v[$v2];
			}
		}


		// 汇总计算:
		//$sum = array();
		foreach ($list as $v) {
			foreach ($cal_field as $f) {
				$sum[$wi][$f] = floatval($sum[$wi][$f]) + $v[$f];
			}
		}

		$sum[$wi]["per_1"] = @round($sum[$wi]["talk_swt"] / $sum[$wi]["click"] * 100, 2);
		$sum[$wi]["per_2"] = @round($sum[$wi]["come"] / $sum[$wi]["orders_swt"] * 100, 2);
		$sum[$wi]["per_3"] = @round($sum[$wi]["come"] / $sum[$wi]["click"] * 100, 2);
		$sum[$wi]["per_4"] = @round($sum[$wi]["ok_click"] / $sum[$wi]["click"] * 100, 2);
		$sum[$wi]["per_5"] = @round($sum[$wi]["talk_swt"] / $sum[$wi]["ok_click"] * 100, 2);

		//echo "<pre>";
		//print_r($sum);


		// 流失人数:
		$liushi_sum = 0;
		foreach ($kefu_arr as $_kefu) {
			$liushi = round($sum[$wi]["come"] * $all[$_kefu][$wi]["click"] / $sum[$wi]["click"]) - $all[$_kefu][$wi]["come"];
			if ($liushi > 0) {
				$all[$_kefu][$wi]["liushi"] = $liushi;
			} else {
				$all[$_kefu][$wi]["liushi"] = '';
			}
			if ($liushi > 0) {
				$liushi_sum += intval($liushi);
			}
		}
		$sum[$wi]["liushi"] = $liushi_sum;

	}


	// 客服备注:
	foreach ($kefu_arr as $_kefu) {
		foreach ($week_array as $_dname => $v) {
			$_arr = $db->query("select * from count_week_memo where type_id=$cur_type and month=$int_month and sub_id=$_dname and kefu='$_kefu' limit 1", 1);
			if ($_arr["id"] > 0 && trim($_arr["memo"]) != '') {
				$title = $_arr["memo"];
				$text = cut($_arr["memo"], 20);
				$class = "a_red";
			} else {
				$title = "点击添加备注";
				$text = "<nobr>添加</nobr>";
				$class = 'noprint';
			}

			$s = '<a href="javascript:;" onclick="edit_memo('.$cur_type.','.$int_month.','.$_dname.',\''.base64url_encode($_kefu).'\')" title="'.$title.'" class="'.$class.'">'.$text.'</a>';
			$all[$_kefu][$_dname]["memo"] = $s;
		}
	}

}






// 页面开始 ------------------------
?>
<html>
<head>
<title>网络数据统计 - 按周</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
form {display:inline; }
.combo {font-family:"微软雅黑" !important; font-size:12px !important; }
#date_tips {float:left; font-weight:bold; padding-top:1px; }
#ch_date {float:left; margin-left:20px; }
.site_name {display:block; padding:4px 0px;}
.site_name, .site_name a {font-family:"Arial", "Tahoma"; }
.ch_date_a b, .ch_date_a a {font-family:"Arial"; }
.ch_date_a b {border:0px; padding:1px 5px 1px 5px; color:red; }
.ch_date_a a {border:0px; padding:1px 5px 1px 5px; }
.ch_date_a a:hover {border:1px solid silver; padding:0px 4px 0px 4px; }
.ch_date_b {padding-top:8px; text-align:left; width:80%; color:silver; }
.ch_date_b a {padding:0 3px; }
.no_b {font-weight:normal !important; }
.main_title {margin:0 auto; padding:20px; text-align:center; font-size:15px; }
.rate_tips {padding:30px 0 0 30px; line-height:24px; }
.a_red {color:red; }
</style>

<style media="print">
.noprint {display:none; }
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

/// $cur_type.','.$int_month.','.$_dname.',\''.($_kefu)
function edit_memo(type_id, int_month, sub_id, kefu) {
	var url = "count_week_set_memo.php?type_id="+type_id+"&month="+int_month+"&sub_id="+sub_id+"&kefu="+kefu;
	parent.load_src(1, url, 600, 300);
	return false;
}
</script>
</head>

<body>
<div style="margin:15px auto 0 auto; text-align:center;" class="noprint">
	<a href="count_web.php">[返回]</a>
	<form method="GET" style="margin-left:20px;">
		<select name="type_id" id="type_id" class="combo" onchange="this.form.submit()">
			<option value="" style="color:gray">-请选择项目-</option>
			<?php echo list_option($types, "_key_", "_value_", $cur_type); ?>
		</select>&nbsp;
		<button class="button" onclick="hgo('up',this);">上</button>&nbsp;
		<button class="button" onclick="hgo('down',this);">下</button>
		<input type="hidden" name="month" value="<?php echo $_GET["month"]; ?>">
		<input type="hidden" name="op" value="change_type">
	</form>

	<b style="margin-left:20px;">月份：</b>
	<form method="GET">
		<input name="month" id="time_month" class="input" style="width:100px" value="<?php echo $_GET["month"]; ?>" onclick="picker({el:'time_month',dateFmt:'yyyy-MM'})" onchange="this.form.submit();">&nbsp;&nbsp;&nbsp;
	</form>

	<a href="count_web_compare_week_print.php?month=<?php echo $_GET["month"]; ?>" target="_blank" title="新窗口查看打印页面" style="font-family:微软雅黑; font-weight:bold;">[打印]</a>
</div>


<?php if ($cur_type && $_GET["month"]) { ?>

<div class="main_title"><?php echo $type_detail["name"]." ".$_GET["month"]; ?> 商务通数据对比</div>

<style type="text/css">
.list td {padding:5px 2px !important; font-family:"Tahoma","微软雅黑" !important; }
.huizong td {color:red; }
</style>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head hr hb" width="4%" rowspan="2" align="center">客服</td>

		<td class="head hl hb" width="32%" colspan="7" align="center">1日-10日</td>
		<td class="head hl hb" width="32%" colspan="7" align="center">11日-20日</td>
		<td class="head hl hb" width="32%" colspan="7" align="center">21日-31日</td>
	</tr>

	<tr>
		<td class="head hl hb no_b" width="4%" align="center">总到诊</td>
		<td class="head hb no_b" width="4%" align="center">商务通到诊</td>
		<td class="head hb no_b" align="center">咨询预约率</td>
		<td class="head hb no_b" align="center">预约就诊率</td>
		<td class="head hb no_b" align="center">咨询就诊率</td>
		<td class="head hb no_b" width="4%" align="center">流失人数</td>
		<td class="head hb no_b" align="center">备注</td>

		<td class="head hl hb no_b" width="4%" align="center">总到诊</td>
		<td class="head hb no_b" width="4%" align="center">商务通到诊</td>
		<td class="head hb no_b" align="center">咨询预约率</td>
		<td class="head hb no_b" align="center">预约就诊率</td>
		<td class="head hb no_b" align="center">咨询就诊率</td>
		<td class="head hb no_b" width="4%" align="center">流失人数</td>
		<td class="head hb no_b" align="center">备注</td>

		<td class="head hl hb no_b" width="4%" align="center">总到诊</td>
		<td class="head hb no_b" width="4%" align="center">商务通到诊</td>
		<td class="head hb no_b" align="center">咨询预约率</td>
		<td class="head hb no_b" align="center">预约就诊率</td>
		<td class="head hb no_b" align="center">咨询就诊率</td>
		<td class="head hb no_b" width="4%" align="center">流失人数</td>
		<td class="head hb no_b" align="center">备注</td>
	</tr>


<?php
foreach ($kefu_arr as $i) {
	$li = $all[$i];
	if (!is_array($li)) {
		$li = array();
	}

?>
	<tr>
		<td class="item" align="center"><?php echo $i; ?></td>

		<td class="item hl" align="center"><?php echo floatval($li["1"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["1"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["1"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["1"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["1"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($li["1"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($li["1"]["memo"]); ?></td>

		<td class="item hl" align="center"><?php echo floatval($li["2"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["2"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($li["2"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($li["2"]["memo"]); ?></td>

		<td class="item hl" align="center"><?php echo floatval($li["3"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["3"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_3"]); ?></td>
		<td class="item" align="center"><?php echo ($li["3"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($li["3"]["memo"]); ?></td>
	</tr>

<?php } ?>

	<tr class="huizong">
		<td class="item ht" align="center">汇总</td>

		<td class="item hl" align="center"><?php echo floatval($sum["1"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($sum["1"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($sum["1"]["memo"]); ?></td>

		<td class="item hl" align="center"><?php echo floatval($sum["2"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($sum["2"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($sum["2"]["memo"]); ?></td>

		<td class="item hl" align="center"><?php echo floatval($sum["3"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($sum["3"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($sum["3"]["memo"]); ?></td>

	</tr>

<?php $sum_nums = count($kefu_arr); ?>

	<tr class="huizong">
		<td class="item ht" align="center">平均<?php echo $sum_nums; ?></td>

		<td class="item hl" align="center"><?php echo round($sum["1"]["come_all"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo round($sum["1"]["come"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo round($sum["1"]["liushi"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"></td>

		<td class="item hl" align="center"><?php echo round($sum["2"]["come_all"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo round($sum["2"]["come"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo round($sum["2"]["liushi"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"></td>

		<td class="item hl" align="center"><?php echo round($sum["3"]["come_all"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo round($sum["3"]["come"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo round($sum["3"]["liushi"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"></td>

	</tr>

</table>

<div class="rate_tips noprint">
咨询预约率 = 预约人数 / 总点击<br>
预约就诊率 = 实际到院人数 / 预计到院人数<br>
咨询就诊率 = 实际到院人数 / 总点击<br>
有效咨询率 = 有效点击 / 总点击<br>
有效预约率 = 预约人数 / 有效点击<br>
</div>

<?php } ?>


</body>
</html>
