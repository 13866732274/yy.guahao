<?php
// --------------------------------------------------------
// - 功能说明 : 网络
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2010-08-02 14:14
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_web";

$is_zhuren = in_array($realname, explode(" ", $sys_super_admin));
$is_daoru = in_array($realname, explode(" ", "周丽兵 曹黎明 刘清锋"));

$valid_hids = $db->query("select distinct hid from count_type where type='web' and ishide=0", "", "hid");
$valid_hid_str = count($valid_hids) ? implode(",", $valid_hids) : "0";

// 按分组进行整理
$hids = implode(",", $hospital_ids);
$group_id_name = $db->query("select id,name from hospital_group order by sort desc, name asc", "id", "name");
$options = array();
foreach ($group_id_name as $_gid => $_gname) {
	$h_list = $db->query("select id,name,color from hospital where ishide=0 and group_id=$_gid and id in ($hids) and id in ($valid_hid_str) order by sort desc, name asc", "id");
	if (count($h_list) > 0) {
		$options[] = array('-1', $_gname." (".count($h_list).')', 'color:red' );
		foreach ($h_list as $_hid => $_arr) {
			$options[] = array($_hid, '　'.$_arr["name"], ($_arr["color"] ? ('color:'.$_arr["color"]) : 'color:blue') );
		}
	}
}

$_hid = $_GET["_tohid_"];
if (isset($_hid) && $_hid != '') {
	if (@in_array($_hid, $hospital_ids)) {
		$_SESSION[$cfgSessionName]["hospital_id"] = intval($_hid);
	}
	$url = $_SERVER["REQUEST_URI"];
	$url = str_replace("_tohid_=", "", $url);
	header("location:".$url);
	exit;
}

if ($_GET["op"] == "change_hid") {
	$_SESSION["count_type_id_web"] = '';
}

$types_full = array();
if ($hid > 0) {
	$types_full = $db->query("select id,name from count_type where ishide=0 and type='web' and hid=$hid order by id asc", "id");
}

$cur_type = $_SESSION["count_type_id_web"];

// 没有选择，自动选择第一个：
if (!$cur_type && $types_full) {
	$type_ids = array_keys($types_full);
	$cur_type = $_SESSION["count_type_id_web"] = $type_ids[0];
}

// 已有选择，但不是当前下拉里的：
if ($types_full && !array_key_exists($cur_type, $types_full)) {
	$type_ids = array_keys($types_full);
	$cur_type = $_SESSION["count_type_id_web"] = $type_ids[0];
}


if ($_GET["date"] && strlen($_GET["date"]) == 6) {
	$date = $_GET["date"];
} else {
	$date = date("Ym"); //本月
	$_GET["date"] = $date;
}
$date_time = strtotime(substr($date,0,4)."-".substr($date,4,2)."-01 0:0:0");

// 可用 年,月 数组
$y_array = $m_array = $d_array = array();
for ($i = date("Y"); $i >= (date("Y") - 2); $i--) $y_array[] = $i;
for ($i = 1; $i <= 12; $i++) $m_array[] = $i;
for ($i = 1; $i <= 31; $i++) {
	if ($i <= 28 || checkdate(date("n", $date_time), $i, date("Y", $date_time))) {
		$d_array[] = $i;
	}
}

// 操作的处理:
if ($op = $_REQUEST["op"]) {
	if ($op == "add") {
		include "count_web_edit.php";
		exit;
	}

	if ($op == "edit") {
		include "count_web_edit.php";
		exit;
	}

	if ($op == "change_type") {
		$cur_type = $_SESSION["count_type_id_web"] = intval($_GET["type_id"]);
	}
}



if ($cur_type > 0) {

	$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);
	$kefu_list = $type_detail["kefu"] ? explode(",", $type_detail["kefu"]) : array();


	// 该月结束:
	$month_end = strtotime("+1 month", $date_time);

	$b = date("Ymd", $date_time);
	$e = date("Ymd", $month_end);


	// 查询当前时间段的客服 @ 2012-03-07
	$lizhi_kefu = array();
	$data_kefu = $db->query("select distinct kefu from $table where type_id=$cur_type and date>=$b and date<$e order by kefu asc", "", "kefu");
	if (count($data_kefu) > 0) {
		foreach ($data_kefu as $v) {
			if (!in_array($v, $kefu_list)) {
				$lizhi_kefu[] = $v;
			}
		}
	}
	$option_kefu = array(); //下拉列表使用的客服
	foreach ($kefu_list as $v) {
		if (trim($v)) {
			$option_kefu[$v] = trim($v);
		}
	}
	foreach ($lizhi_kefu as $v) {
		if (trim($v)) {
			$option_kefu[$v] = trim($v).' (离职)';
		}
	}
	if ($_GET["kefu"] && !array_key_exists($_GET["kefu"], $option_kefu)) {
		$option_kefu[$_GET["kefu"]] = $_GET["kefu"]." (离职)";
	}



	$cur_kefu = $_GET["kefu"];
	if ($cur_kefu) {
		// 查询单个客服数据:
		$list = $db->query("select * from $table where type_id=$cur_type and kefu='$cur_kefu' and date>=$b and date<$e order by date asc,kefu asc", "date");
		$sql = $db->sql;

		// 计算数据:
		foreach ($list as $k => $v) {
			// 咨询预约率:
			$list[$k]["per_1"] = @round($v["talk_swt"] / $v["click"] * 100, 1);
			// 预约就诊率:
			$list[$k]["per_2"] = @round($v["come"] / $v["orders_swt"] * 100, 1);
			// 咨询就诊率:
			$list[$k]["per_3"] = @round($v["come"] / $v["click"] * 100, 1);
			// 有效咨询率:
			$list[$k]["per_4"] = @round($v["ok_click"] / $v["click"] * 100, 1);
			// 有效预约率:
			$list[$k]["per_5"] = @round($v["talk_swt"] / $v["ok_click"] * 100, 1);
			// 总预约到诊率
			$list[$k]["per_6"] = @round($v["come_all"] / $v["orders"] * 100, 1);
		}

		// 计算统计数据:
		$cal_field = explode(" ", "all_click click click_local click_other zero_talk ok_click ok_click_local ok_click_other talk talk_bendi talk_waidi talk_swt talk_tel talk_other orders orders_bendi orders_waidi orders_swt orders_tel orders_other come_all come_bendi come_waidi come come_tel come_other");
		// 处理:
		$sum_list = array();
		foreach ($list as $v) {
			foreach ($cal_field as $f) {
				$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
			}
		}

		// 咨询预约率:
		$sum_list["per_1"] = @round($sum_list["talk_swt"] / $sum_list["click"] * 100, 1);
		// 预约就诊率:
		$sum_list["per_2"] = @round($sum_list["come"] / $sum_list["orders_swt"] * 100, 1);
		// 咨询就诊率:
		$sum_list["per_3"] = @round($sum_list["come"] / $sum_list["click"] * 100, 1);
		// 有效咨询率:
		$sum_list["per_4"] = @round($sum_list["ok_click"] / $sum_list["click"] * 100, 1);
		// 有效预约率:
		$sum_list["per_5"] = @round($sum_list["talk_swt"] / $sum_list["ok_click"] * 100, 1);
		// 总预约到诊率
		$sum_list["per_6"] = @round($sum_list["come_all"] / $sum_list["orders"] * 100, 1);



	} else {
		//查询总医院汇总数据:
		$tmp_list = $db->query("select * from $table where type_id=$cur_type and date>=$b and date<$e order by date asc,kefu asc");
		$sql = $db->sql;

		// 计算汇总:
		$list = $dt_count = array();
		foreach ($tmp_list as $v) {
			$dt = $v["date"];
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
			$list[$k]["per_1"] = @round($v["talk_swt"] / $v["click"] * 100, 1);
			// 预约就诊率:
			$list[$k]["per_2"] = @round($v["come"] / $v["orders_swt"] * 100, 1);
			// 咨询就诊率:
			$list[$k]["per_3"] = @round($v["come"] / $v["click"] * 100, 1);
			// 有效咨询率:
			$list[$k]["per_4"] = @round($v["ok_click"] / $v["click"] * 100, 1);
			// 有效预约率:
			$list[$k]["per_5"] = @round($v["talk_swt"] / $v["ok_click"] * 100, 1);
			// 总预约到诊率
			$list[$k]["per_6"] = @round($v["come_all"] / $v["orders"] * 100, 1);
		}

		// 计算统计数据:
		$cal_field = explode(" ", "all_click click click_local click_other zero_talk ok_click ok_click_local ok_click_other talk talk_bendi talk_waidi talk_swt talk_tel talk_other orders orders_bendi orders_waidi orders_swt orders_tel orders_other come_all come_bendi come_waidi come come_tel come_other");
		// 处理:
		$sum_list = array();
		foreach ($list as $v) {
			foreach ($cal_field as $f) {
				$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
			}
		}

		// 咨询预约率:
		$sum_list["per_1"] = @round($sum_list["talk_swt"] / $sum_list["click"] * 100, 1);
		// 预约就诊率:
		$sum_list["per_2"] = @round($sum_list["come"] / $sum_list["orders_swt"] * 100, 1);
		// 咨询就诊率:
		$sum_list["per_3"] = @round($sum_list["come"] / $sum_list["click"] * 100, 1);
		// 有效咨询率:
		$sum_list["per_4"] = @round($sum_list["ok_click"] / $sum_list["click"] * 100, 1);
		// 有效预约率:
		$sum_list["per_5"] = @round($sum_list["talk_swt"] / $sum_list["ok_click"] * 100, 1);
		// 总预约到诊率
		$sum_list["per_6"] = @round($sum_list["come_all"] / $sum_list["orders"] * 100, 1);

	}


	// 是否能添加或修改数据:
	$can_edit_data = 0;
	if ($debug_mode || in_array($uinfo["part_id"], array(9)) || in_array($uid, explode(",", $type_detail["uids"])) || $uinfo["modify_count_data"]) {
		$can_edit_data = 1;
	}


	// 汇总页面 显示目标设置
	if ($cur_kefu == '') {

		$_cur_month = date("Ym", $date_time);

		$fs = array();
		$fs[] = "all_click";
		$fs[] = "click";
		$fs[] = "click_local";
		$fs[] = "click_other";
		$fs[] = "ok_click";
		$fs[] = "ok_click_local";
		$fs[] = "ok_click_other";

		$fs[] = "talk";
		$fs[] = "talk_bendi";
		$fs[] = "talk_waidi";
		$fs[] = "talk_swt";
		$fs[] = "talk_tel";
		$fs[] = "talk_other";

		$fs[] = "orders";
		$fs[] = "orders_bendi";
		$fs[] = "orders_waidi";
		$fs[] = "orders_swt";
		$fs[] = "orders_tel";
		$fs[] = "orders_other";

		$fs[] = "come_all";
		$fs[] = "come_bendi";
		$fs[] = "come_waidi";
		$fs[] = "come";
		$fs[] = "come_tel";
		$fs[] = "come_other";

		$fs[] = "per_1";
		$fs[] = "per_2";
		$fs[] = "per_3";
		$fs[] = "per_4";
		$fs[] = "per_5";

		$fs[] = "per_6";

		$mubiao_data = $db->query("select * from count_mubiao where type_id=$cur_type and month='$_cur_month' limit 1", 1);
		$mubiao_value = @unserialize($mubiao_data["config"]);

		$mubiao = array();
		foreach ($fs as $v) {
			if ($mubiao_value[$v] != '') {
				$mubiao[$v] = '<a href="#" onclick="set_mubiao(\''.$_cur_month.'\', \''.$v.'\'); return false;">'.$mubiao_value[$v].'</a>';
			} else {
				$mubiao[$v] = '<a href="#" onclick="set_mubiao(\''.$_cur_month.'\', \''.$v.'\'); return false;">添加</a>';
			}
		}
	}

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
			$s .= '<a href="#" onclick="'.$click.'">'.$v.'</a>';
		}
	}
	return $s;
}

function per_detect($num) {
	if ($num > 100) return "~";
	if ($num == 0) return "";
	return round($num, 1);
}


// 页面开始 ------------------------
?>
<html>
<head>
<title>网络数据统计</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
body {padding:5px 8px; }
form {display:inline; }
a, a:hover {font-family:"Tahoma","微软雅黑";}
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
.main_title {margin:0 auto; padding:10px; text-align:center; font-weight:bold; font-size:15px; }
.item {font-family:"Tahoma"; padding:6px 3px !important; }
.head {padding:6px 3px !important;}
.rate_tips {padding:30px 0 0 30px; line-height:24px; }
.tr_high_light td {background:#FFE1D2; }
.huizong {padding:4px; text-align:center; background-color:#e4e9eb; }
</style>

<script language="javascript">
function update_date(type, o) {
	byid("date_"+type).value = parseInt(o.innerHTML, 10);

	var a = parseInt(byid("date_1").value, 10);
	var b = parseInt(byid("date_2").value, 10);

	var s = a + '' + (b<10 ? "0" : "") + b;

	byid("date").value = s;
	byid("cha_date").submit();

	return false;
}

function hgo(dir){
	var t="已经是最"+(dir=="up"?"上":"下")+"一家医院了";
	var obj=byid("hospital_id");if(dir=="up"){var i=obj.selectedIndex-1;while(i>0){if(obj.options[i].value>0){obj.selectedIndex=i;obj.onchange();break}i--}if(i==0){parent.msg_box(t,3)}}if(dir=="down"){var i=obj.selectedIndex+1;while(i<obj.options.length){if(obj.options[i].value>0){obj.selectedIndex=i;obj.onchange();break}i++}if(i==obj.options.length){parent.msg_box(t,3)}}
}

window.last_high_obj = '';
function set_high_light(obj) {
	if (last_high_obj) {
		last_high_obj.parentNode.parentNode.className = "";
	}
	if (obj) {
		obj.parentNode.parentNode.className = "tr_high_light";
		last_high_obj = obj;
	} else {
		last_high_obj = '';
	}
}

function add(link, obj) {
	set_high_light(obj);
	parent.load_src(1, link, 800, 500);
	return false;
}

function edit(link, obj) {
	set_high_light(obj);
	parent.load_src(1, link, 800, 500);
	return false;
}

function set_mubiao(month, field) {
	var url = "count_set_mubiao.php?month="+month+"&field="+field;
	parent.load_src(1, url, 600, 250);
	return false;
}

function daoru() {
	parent.load_src(1, "count_web_swt.php", 1000, 600);
}
</script>
</head>

<body>
<table width="100%" style="margin-top:15px">
	<tr>
		<td valign="top" style="padding-left:10px;">
			<span class="ch_date_a">年：<?php echo my_show($y_array, date("Y", $date_time), "return update_date(1,this)"); ?>&nbsp;&nbsp;&nbsp;</span>
			<span class="ch_date_a">月：<?php echo my_show($m_array, date("m", $date_time), "return update_date(2,this)"); ?>&nbsp;&nbsp;&nbsp;</span>
			<input type="hidden" id="date_1" value="<?php echo date("Y", $date_time); ?>">
			<input type="hidden" id="date_2" value="<?php echo date("n", $date_time); ?>">

			<form method="GET" name="cha_date" id="cha_date">
				<input type="hidden" name="date" id="date" value="">
				<input type="hidden" name="kefu" value="<?php echo $kefu; ?>">
			</form>
			<div class="clear"></div>
		</td>

		<td align="right" style="padding-right:10px;">
			<style type="text/css">
			.ml {margin-left:10px; }
			</style>
<?php if ($debug_mode || $config["zixun_chengben"]) { ?>
			<!-- <a href="count_web_chengben.php" title="查看咨询员成本表" class="ml">咨询员成本表</a> -->
<?php } ?>

<?php if ($debug_mode || $config["show_jiuzhenlv_compare"]) { ?>
			<a href="count_web_report_jiuzhenlv.php" title="查看就诊率对比曲线" class="ml">就诊率对比</a>
<?php } ?>
			<a href="count_web_compare.php" title="查看客服数据对比" class="ml">咨询员数据对比</a>
			<a href="count_web_report.php" title="查看汇总统计数据" class="ml">汇总数据</a>
		</td>
	</tr>
</table>

<table width="100%" style="margin-top:15px">
	<tr>
		<td align="left" style="padding-left:10px;">
			<form method="GET">
				<select name="_tohid_" id="hospital_id" class="combo" onchange="this.form.submit()" style="width:250px;">
					<option value="" style="color:gray">-请选择医院-</option>
<?php
foreach ($options as $v) {
	echo '			<option value="'.$v[0].'"'.($v[0] == $hid ? ' selected' : '').($v[2] ? ' style="'.$v[2].'"' : '').'>'.$v[1].($v[0] == $hid ? ' *' : '').'</option>';
}
?>
				</select>&nbsp;&nbsp;
				<button class="button" onclick="hgo('up');">上</button>&nbsp;<button class="button" onclick="hgo('down');">下</button>
				<input type="hidden" name="date" value="<?php echo $_GET["date"]; ?>">
				<input type="hidden" name="type_id" value="">
				<input type="hidden" name="kefu" value="">
				<input type="hidden" name="op" value="change_hid">
			</form>
		</td>
		<td align="left">
			<form method="GET" style="margin-left:30px;">
				<b>统计子项：</b>
				<select name="type_id" id="type_id" class="combo" onchange="this.form.submit()">
					<option value="" style="color:gray">-请选择子项-</option>
					<?php echo list_option($types_full, "id", "name", $cur_type); ?>
				</select>&nbsp;
				<input type="hidden" name="date" value="<?php echo $_GET["date"]; ?>">
				<input type="hidden" name="op" value="change_type">
			</form>
<?php if ($cur_type > 0) { ?>
			<?php if ($is_zhuren) { ?><a href="javascript:;" onclick="parent.load_src(1, 'count_web_type_edit.php?id=<?php echo $cur_type; ?>', 900, 600);" class="ml" title="修改当前统计子项">[修改]</a><?php } ?>
<?php } ?>
		</td>
		<td align="left">
			<form method="GET" style="margin-left:30px;">
				<b>客服：</b>
				<select name="kefu" id="kefu_select" class="combo" onchange="this.form.submit()">
					<option value="" style="color:gray">-客服汇总-</option>
					<?php echo list_option($option_kefu, "_key_", "_value_", $_GET["kefu"]); ?>
				</select>&nbsp;
				<a href="javascript:;" id="change_to_pre_kefu" onclick="change_to_pre_kefu();"></a>&nbsp;
				<a href="javascript:;" id="change_to_next_kefu" onclick="change_to_next_kefu();"></a>
				<script type="text/javascript">
				var g = byid("kefu_select");
				if (g.selectedIndex > 1) {
					var pre = g.options[g.selectedIndex - 1].text;
				} else {
					var pre = "无";
				}
				if (g.selectedIndex < g.options.length - 1) {
					var next = g.options[g.selectedIndex + 1].text;
				} else {
					var next = "无";
				}
				byid("change_to_pre_kefu").innerHTML = "上一个["+pre+"]";
				byid("change_to_next_kefu").innerHTML = "下一个["+next+"]";

				function change_to_pre_kefu() {
					var g = byid("kefu_select");
					if (g.selectedIndex > 1) {
						g.options[g.selectedIndex - 1].selected = true;
						g.onchange();
					}
				}
				function change_to_next_kefu() {
					var g = byid("kefu_select");
					if (g.selectedIndex < g.options.length - 1) {
						g.options[g.selectedIndex + 1].selected = true;
						g.onchange();
					}
				}
				</script>
				<input type="hidden" name="date" value="<?php echo $date; ?>">
			</form>
		</td>
		<td>&nbsp;</td>
	</tr>
</table>

<div style="margin-top:10px;">
	<a href="count_keshi.php?month=<?php echo date("Ym", $date_time); ?>" title="按科室查看汇总" class="ml">科室汇总</a>
	<a href="count_hospital.php?month=<?php echo date("Ym", $date_time); ?>" title="按医院查看汇总" class="ml">医院汇总</a>
	<?php if ($is_daoru) { ?><a href="javascript:;" onclick="daoru();" class="ml">SWT导入</a><?php } ?>
</div>

<?php if ($cur_type > 0) { ?>

<div class="main_title"><?php echo $type_detail["name"]; ?> - <?php echo date("Y-n", $date_time); ?><?php if ($cur_kefu) echo " - 客服 ".$cur_kefu.""; ?> 网络统计数据</div>


<!-- 浮动表头 注意：此技术需要指定每个单元格的宽度否则上下表格可能不对齐 -->
<style type="text/css">
.small_font {font-size:11px !important; color:#aaaaaa; display:block; font-weight:normal; }
</style>
<?php $w = round(100 / 33, 3); ?>
<style type="text/css">
.list td {padding-left:1px !important; padding-right:1px !important; }
</style>
<div id="to_float">
	<table id="data_list_float_head" style="display:none;border-bottom:1px;" align="center" cellpadding="0" cellspacing="0" class="list">
		<tr>
			<td width="<?php echo $w."%"; ?>" class="head br no_b" align="center" rowspan="2">月.日</td>
			<td width="<?php echo 7*$w."%"; ?>" style="color:red" class="head bl" align="center" colspan="7">商务通点击量</td>
			<td width="<?php echo 6*$w."%"; ?>" style="color:red" class="head bl" align="center" colspan="6">预约</td>
			<td width="<?php echo 6*$w."%"; ?>" style="color:red" class="head bl " align="center" colspan="6">预到</td>
			<td width="<?php echo 6*$w."%"; ?>" style="color:red" class="head bl" align="center" colspan="6">到院</td>
			<td width="<?php echo 5*$w."%"; ?>" style="color:red" class="head bl" align="center" colspan="5">商务通比率%</td>
			<td width="<?php echo $w."%"; ?>" class="head bl no_b" align="center" rowspan="2">总预约就诊率%</td>
			<td width="<?php echo $w."%"; ?>" class="head bl no_b" align="center" rowspan="2">操作</td>
		</tr>

		<tr>
			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">所有点击<font class="small_font">(1条起)</font></td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">总点击<font class="small_font">(1条对1条)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">有效<font class="small_font">(5条对5条)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总预约</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总预到</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总到院</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b bl" align="center">咨询预约率%</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">预约就诊率%</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">咨询就诊率%</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">有效咨询率%</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">有效预约率%</td>
		</tr>
	</table>
</div>

<table width="100%" id="data_list" align="center" cellpadding="0" cellspacing="0" class="list" style="margin-top:15px;">
		<tr>
			<td width="<?php echo $w."%"; ?>" class="head br no_b" align="center" rowspan="2">月.日</td>
			<td width="<?php echo 7*$w."%"; ?>" style="color:red" class="head bl" align="center" colspan="7">商务通点击量</td>
			<td width="<?php echo 6*$w."%"; ?>" style="color:red" class="head bl" align="center" colspan="6">预约</td>
			<td width="<?php echo 6*$w."%"; ?>" style="color:red" class="head bl " align="center" colspan="6">预到</td>
			<td width="<?php echo 6*$w."%"; ?>" style="color:red" class="head bl" align="center" colspan="6">到院</td>
			<td width="<?php echo 5*$w."%"; ?>" style="color:red" class="head bl" align="center" colspan="5">商务通比率%</td>
			<td width="<?php echo $w."%"; ?>" class="head bl no_b" align="center" rowspan="2">总预约就诊率%</td>
			<td width="<?php echo $w."%"; ?>" class="head bl no_b" align="center" rowspan="2">操作</td>
		</tr>

		<tr>
			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">所有点击<font class="small_font">(1条起)</font></td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">总点击<font class="small_font">(1条对1条)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">有效<font class="small_font">(5条对5条)</font></td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总预约</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总预到</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" class="head no_b bl" align="center">总到院</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">本地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">外地</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">商务通</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">电话</td>
			<td width="<?php echo $w."%"; ?>" class="head no_b" align="center">其它</td>

			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b bl" align="center">咨询预约率%</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">预约就诊率%</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">咨询就诊率%</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">有效咨询率%</td>
			<td width="<?php echo $w."%"; ?>" style="color:red" class="head no_b" align="center">有效预约率%</td>
		</tr>

<?php
foreach ($d_array as $i) {
	$cur_date = date("Ymd", strtotime(date("Y-m-", $date_time).$i." 0:0:0"));
	$li = $list[$cur_date];
	if (!is_array($li)) {
		$li = array();
	}

	// 汇总情况下，没有数据的日期不显示 @ 2014-5-8
	if ($cur_kefu == '') {
		if ($li["click"] + $li["ok_click"] + $li["talk"] + $li["orders"] + $li["come_all"] == 0) {
			continue;
		}
	}

?>
	<tr>
		<td class="item" align="center"><?php echo date("n", $date_time); ?>.<?php echo $i; ?></td>

		<td class="item bl" align="center"><?php echo $li["all_click"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["click"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_other"]; ?></td>

		<td class="item bl" align="center"><?php echo $li["talk"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_swt"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_tel"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_other"]; ?></td>

		<td class="item bl" align="center"><?php echo $li["orders"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_swt"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_tel"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_other"]; ?></td>

		<td class="item bl" align="center"><?php echo $li["come_all"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $li["come"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_tel"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo per_detect($li["per_1"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo per_detect($li["per_2"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo per_detect($li["per_3"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo per_detect($li["per_4"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo per_detect($li["per_5"]); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo per_detect($li["per_6"]); ?></td>

		<td class="item bl" align="center">
<?php if ($cur_kefu && $can_edit_data) { ?>
			<?php if (!$li) { ?>
			<a onclick="add(this.href,this);return false;" href="?op=add&kefu=<?php echo urlencode($cur_kefu); ?>&date=<?php echo date("Y-m-", $date_time).$i; ?>">添加</a>
			<?php } else { ?>
			<a onclick="edit(this.href,this);return false;" href="?op=edit&kefu=<?php echo urlencode($cur_kefu); ?>&date=<?php echo date("Y-m-", $date_time).$i; ?>">修改</a>
			<?php } ?>
<?php } else { ?>
			<span>--</span>
<?php } ?>
		</td>
	</tr>

<?php } ?>

	<tr>
		<td colspan="33" class="huizong">数据汇总</td>
	</tr>

	<tr>
		<td class="item" align="center">汇总</td>

		<td class="item bl" align="center"><?php echo $sum_list["all_click"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum_list["talk"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_swt"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_tel"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum_list["orders"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_swt"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_tel"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum_list["come_all"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come_tel"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo @per_detect($sum_list["per_1"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @per_detect($sum_list["per_2"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @per_detect($sum_list["per_3"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @per_detect($sum_list["per_4"]); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @per_detect($sum_list["per_5"]); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo @per_detect($sum_list["per_6"]); ?></td>

		<td class="item bl" align="center">--</td>
	</tr>

	<tr>
		<td class="item" align="center">日均</td>

		<td class="item bl" align="center"><?php echo @round($sum_list["all_click"] / count($list), 0); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["click"] / count($list), 0); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["click_local"] / count($list), 0); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["click_other"] / count($list), 0); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["ok_click"] / count($list), 0); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["ok_click_local"] / count($list), 0); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["ok_click_other"] / count($list), 0); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo @round($sum_list["talk"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["talk_bendi"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["talk_waidi"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["talk_swt"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["talk_tel"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["talk_other"] / count($list), 1); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo @round($sum_list["orders"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["orders_bendi"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["orders_waidi"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["orders_swt"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["orders_tel"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["orders_other"] / count($list), 1); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo @round($sum_list["come_all"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["come_bendi"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["come_waidi"] / count($list), 1); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["come"] / count($list), 1); ?></td>
		<td class="item" align="center" ><?php echo @round($sum_list["come_tel"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["come_other"] / count($list), 1); ?></td>

		<td class="item bl" align="center" style="color:red">-</td>
		<td class="item" align="center" style="color:red">-</td>
		<td class="item" align="center" style="color:red">-</td>
		<td class="item" align="center" style="color:red">-</td>
		<td class="item" align="center" style="color:red">-</td>

		<td class="item bl" align="center" style="color:red">-</td>

		<td class="item bl" align="center">--</td>
	</tr>

<?php if ($cur_kefu == '') { ?>
	<tr>
		<td class="item" align="center">日目标</td>

		<td id="mubiao_all_click" class="item bl" align="center"><?php echo $mubiao["all_click"]; ?></td>
		<td id="mubiao_click" class="item" align="center" style="color:red"><?php echo $mubiao["click"]; ?></td>
		<td id="mubiao_click_local" class="item" align="center"><?php echo $mubiao["click_local"]; ?></td>
		<td id="mubiao_click_other" class="item" align="center"><?php echo $mubiao["click_other"]; ?></td>
		<td id="mubiao_ok_click" class="item" align="center" style="color:red"><?php echo $mubiao["ok_click"]; ?></td>
		<td id="mubiao_ok_click_local" class="item" align="center"><?php echo $mubiao["ok_click_local"]; ?></td>
		<td id="mubiao_ok_click_other" class="item" align="center"><?php echo $mubiao["ok_click_other"]; ?></td>

		<td id="mubiao_talk" class="item bl" align="center" style="color:red"><?php echo $mubiao["talk"]; ?></td>
		<td id="mubiao_talk" class="item" align="center"><?php echo $mubiao["talk_bendi"]; ?></td>
		<td id="mubiao_talk" class="item" align="center"><?php echo $mubiao["talk_waidi"]; ?></td>
		<td id="mubiao_talk" class="item" align="center"><?php echo $mubiao["talk_swt"]; ?></td>
		<td id="mubiao_talk" class="item" align="center"><?php echo $mubiao["talk_tel"]; ?></td>
		<td id="mubiao_talk" class="item" align="center"><?php echo $mubiao["talk_other"]; ?></td>

		<td id="mubiao_orders" class="item bl" align="center" style="color:red"><?php echo $mubiao["orders"]; ?></td>
		<td id="mubiao_talk" class="item" align="center"><?php echo $mubiao["orders_bendi"]; ?></td>
		<td id="mubiao_talk" class="item" align="center"><?php echo $mubiao["orders_waidi"]; ?></td>
		<td id="mubiao_orders" class="item" align="center"><?php echo $mubiao["orders_swt"]; ?></td>
		<td id="mubiao_orders" class="item" align="center"><?php echo $mubiao["orders_tel"]; ?></td>
		<td id="mubiao_orders" class="item" align="center"><?php echo $mubiao["orders_other"]; ?></td>

		<td id="mubiao_come_all" class="item bl" align="center" style="color:red"><?php echo $mubiao["come_all"]; ?></td>
		<td id="mubiao_talk" class="item" align="center"><?php echo $mubiao["come_bendi"]; ?></td>
		<td id="mubiao_talk" class="item" align="center"><?php echo $mubiao["come_waidi"]; ?></td>
		<td id="mubiao_come" class="item" align="center" style="color:red"><?php echo $mubiao["come"]; ?></td>
		<td id="mubiao_come_tel" class="item" align="center"><?php echo $mubiao["come_tel"]; ?></td>
		<td id="mubiao_come_other" class="item" align="center"><?php echo $mubiao["come_other"]; ?></td>

		<td id="mubiao_per_1" class="item bl" align="center" style="color:red"><?php echo $mubiao["per_1"]; ?></td>
		<td id="mubiao_per_2" class="item" align="center" style="color:red"><?php echo $mubiao["per_2"]; ?></td>
		<td id="mubiao_per_3" class="item" align="center" style="color:red"><?php echo $mubiao["per_3"]; ?></td>
		<td id="mubiao_per_4" class="item" align="center" style="color:red"><?php echo $mubiao["per_4"]; ?></td>
		<td id="mubiao_per_5" class="item" align="center" style="color:red"><?php echo $mubiao["per_5"]; ?></td>

		<td id="mubiao_per_6" class="item bl" align="center" style="color:red"><?php echo $mubiao["per_6"]; ?></td>

		<td id="" class="item bl" align="center">--</td>
	</tr>
<?php } ?>

</table>

<div class="rate_tips">
总点击：商务通按有效对话统计<br>
总有效：商务通按极佳对话统计<br>
咨询预约率 = 预约人数 / 总点击<br>
预约就诊率 = 实际到院人数 / 预计到院人数<br>
咨询就诊率 = 实际到院人数 / 总点击<br>
有效咨询率 = 有效点击 / 总点击<br>
有效预约率 = 预约人数 / 有效点击<br>
</div>

<?php } else { ?>

<style type="text/css">
.no-data {text-align:center; margin-top:20px; height:100px; line-height:100px; font-size:12px; color:gray; border:2px solid silver; }
</style>
<div class="no-data">(没有设置统计子项，暂无数据)</div>

<?php } ?>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

<script type="text/javascript">
function scroll_table() {
	var s_top = document.body.scrollTop;
	var top = byid(float_table).offsetTop;
	var top_head = byid(float_table+"_float_head").offsetHeight;
	byid("data_list_float_head").style.width = byid("data_list").offsetWidth;

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

var float_table = "data_list";
make_float(float_table);
</script>

</body>
</html>
