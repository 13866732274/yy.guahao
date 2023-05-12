<?php
// --------------------------------------------------------
// - 功能说明 : 电话 数据对比
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2010-11-25 16:05
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_tel";

// 所有可管理项目:
if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
	$types = $db->query("select id,name from count_type where ishide=0 and type='tel' order by name asc", "id", "name");
} else {
	$hids = implode(",", $hospital_ids);
	$types = $db->query("select id,name from count_type where ishide=0 and type='tel' and hid in ($hids) order by name asc", "id", "name");
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
if ($_GET["btime"] == '') {
	$_GET["btime"] = date("Y-m-d", mktime(0,0,0,date("m"), 1));
}
if ($_GET["etime"] == '') {
	$_GET["etime"] = date("Y-m-d", strtotime("+1 month", strtotime($_GET["btime"]." 0:0:0")) - 1);
}


// 处理数据:
if ($cur_type && $_GET["btime"] && $_GET["etime"]) {

	// 时间段:
	$btime = strtotime($_GET["btime"]." 0:0:0");
	$etime = strtotime($_GET["etime"]." 23:59:59");

	$b = date("Ymd", $btime);
	$e = date("Ymd", $etime);

	//查询总医院汇总数据:
	$tmp_list = $db->query("select * from $table where type_id=$cur_type and date>=$b and date<=$e order by kefu asc,date asc");

	// 计算汇总:
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
	$cal_field = explode(" ", "tel_all tel_ok yuyue jiuzhen wangluo wuxian ditu guahaowang qita");
	// 处理:
	$sum_list = array();
	foreach ($list as $v) {
		foreach ($cal_field as $f) {
			$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
		}
	}

	// 咨询预约率:
	$sum_list["per_1"] = @round($sum_list["yuyue"] / $sum_list["tel_all"] * 100, 2);
	// 预约就诊率:
	$sum_list["per_2"] = @round($sum_list["jiuzhen"] / $sum_list["yuyue"] * 100, 2);
	// 咨询就诊率:
	$sum_list["per_3"] = @round($sum_list["jiuzhen"] / $sum_list["tel_all"] * 100, 2);
	// 有效咨询率:
	$sum_list["per_4"] = @round($sum_list["tel_ok"] / $sum_list["tel_all"] * 100, 2);

}


// 是否能添加或修改数据:
$can_edit_data = 0;
if ($debug_mode || in_array($uinfo["part_id"], array(9)) || in_array($uid, explode(",", $type_detail["uids"]))) {
	$can_edit_data = 1;
}


/*
// ------------------ 函数 -------------------
*/
function my_show($arr, $default_value='', $click='') {
	$s = '';
	foreach ($arr as $v) {
		if ($v == $default_value) {
			$s .= '<b>'.$v.'</b>';
		} else {
			$s .= '<a href="javascript:void(0);" onclick="'.$click.'">'.$v.'</a>';
		}
	}
	return $s;
}


// 页面开始 ------------------------
?>
<html>
<head>
<title>电话数据统计</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<script src="lib/sorttable_keep.js" language="javascript"></script>
<style>
* {font-family:"Tahoma"; }
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

.main_title {margin:0 auto; padding:20px; text-align:center; font-size:15px; font-weight:bold; }

.item {padding:8px 3px 6px 3px !important; }

.rate_tips {padding:30px 0 0 30px; line-height:24px; }
</style>
<style type="text/css">
.column_sortable {cursor:pointer; color:blue; font-family:"微软雅黑"; padding:8px 3px 6px 3px !important; }
.huizong td {font-style:"italic"; color:"#0080c0"; }
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

function write_dt(da, db) {
	byid("begin_time").value = da;
	byid("end_time").value = db;
}
</script>
</head>

<body>
<div style="margin:10px auto; text-align:center;">
	<a href="count_tel.php">[返回]</a>
	<form method="GET" style="margin-left:20px;">
		<select name="type_id" id="type_id" class="combo" onchange="this.form.submit()">
			<option value="" style="color:gray">-请选择项目-</option>
			<?php echo list_option($types, "_key_", "_value_", $cur_type); ?>
		</select>&nbsp;
		<button class="button" onclick="hgo('up',this);">上</button>&nbsp;
		<button class="button" onclick="hgo('down',this);">下</button>
		<input type="hidden" name="btime" value="<?php echo $_GET["btime"]; ?>">
		<input type="hidden" name="etime" value="<?php echo $_GET["etime"]; ?>">
		<input type="hidden" name="op" value="change_type">
	</form>&nbsp;&nbsp;&nbsp;

	<b>时间段：</b>
	<form method="GET">
		<input name="btime" id="begin_time" class="input" style="width:100px" value="<?php echo $_GET["btime"]; ?>"> <img src="image/calendar.gif" id="order_date" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">&nbsp;&nbsp;到&nbsp;&nbsp;
		<input name="etime" id="end_time" class="input" style="width:100px" value="<?php echo $_GET["etime"]; ?>"> <img src="image/calendar.gif" id="order_date" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">&nbsp;&nbsp;
		<input type="submit" class="button" value="确定">&nbsp;&nbsp;
<?php
$lmb = strtotime("-1 month", strtotime($_GET["btime"]));
$lme = strtotime($_GET["btime"]) - 1;
$nmb = strtotime("+1 month", strtotime($_GET["btime"]));
$nme = strtotime("+1 month", $nmb) - 1;
?>
		<input type="button" class="button" onclick="write_dt('<?php echo date("Y-m-d", $lmb); ?>', '<?php echo date("Y-m-d", $lme); ?>'); this.form.submit();" value="上月">&nbsp;
		<input type="button" class="button" onclick="write_dt('<?php echo date("Y-m-d", $nmb); ?>', '<?php echo date("Y-m-d", $nme); ?>'); this.form.submit();" value="下月">&nbsp;
	</form>
</div>


<?php if ($cur_type && $_GET["btime"] && $_GET["etime"]) { ?>

<div class="main_title"><?php echo $type_detail["name"]; ?> <?php echo $_GET["btime"]; ?> 到 <?php echo $_GET["etime"]; ?> 客服数据对比</div>

<table width="100%" align="center" class="list sortable" id="list_001">
	<tr>
		<td class="head column_sortable" align="center" width="60">客服</td>
		<td class="head column_sortable" align="center">总电话</td>
		<td class="head column_sortable" align="center" style="color:red">有效</td>
		<td class="head column_sortable" align="center">预约</td>
		<td class="head column_sortable" align="center" style="color:red">就诊</td>

		<td class="head column_sortable" align="center">网络</td>
		<td class="head column_sortable" align="center">无线</td>
		<td class="head column_sortable" align="center">地图</td>
		<td class="head column_sortable" align="center">挂号网</td>
		<td class="head column_sortable" align="center">其他</td>

		<td class="head column_sortable" align="center">咨询预约率</td>
		<td class="head column_sortable" align="center">预约就诊率</td>
		<td class="head column_sortable" align="center">咨询就诊率</td>
		<td class="head column_sortable" align="center">有效咨询率</td>

		<td class="head column_sortable" align="center">流失人数</td>
	</tr>

<?php
$liushi_sum = 0;
foreach ($kefu_list as $i) {
	$li = $list[$i];
	if (!is_array($li)) {
		$li = array();
	}

	// 流失人数:
	$liushi = round($sum_list["jiuzhen"] * $li["tel_all"] / $sum_list["tel_all"]) - $li["jiuzhen"];
	if ($liushi > 0) {
		$liushi_sum += intval($liushi);
	}


?>
	<tr>
		<td class="item" align="center"><?php echo $i; ?></td>
		<td class="item" align="center"><?php echo $li["tel_all"]; ?></td>
		<td class="item" align="center"><?php echo $li["tel_ok"]; ?></td>
		<td class="item" align="center"><?php echo $li["yuyue"]; ?></td>
		<td class="item" align="center"><?php echo $li["jiuzhen"]; ?></td>

		<td class="item" align="center"><?php echo $li["wangluo"]; ?></td>
		<td class="item" align="center"><?php echo $li["wuxian"]; ?></td>
		<td class="item" align="center"><?php echo $li["ditu"]; ?></td>
		<td class="item" align="center"><?php echo $li["guahaowang"]; ?></td>
		<td class="item" align="center"><?php echo $li["qita"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_1"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_2"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_3"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_4"]); ?>%</td>

		<td class="item" align="center" style="color:red"><?php echo ($liushi > 0 ? $liushi : ''); ?></td>
	</tr>

<?php } ?>

	<tr class="huizong">
		<td class="item" align="center" style="font-family:'微软雅黑'">[汇总]</td>
		<td class="item" align="center"><?php echo $sum_list["tel_all"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["tel_ok"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["yuyue"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["jiuzhen"]; ?></td>

		<td class="item" align="center"><?php echo $sum_list["wangluo"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["wuxian"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ditu"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["guahaowang"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["qita"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_1"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_2"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_3"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_4"]); ?>%</td>

		<td class="item" align="center" style="color:red"><?php echo $liushi_sum; ?></td>

	</tr>
</table>

<?php } ?>

<br>
<br>

</body>
</html>
