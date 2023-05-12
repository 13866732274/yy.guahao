<?php
// --------------------------------------------------------
// - 功能说明 : 渠道成本统计
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-5-27
// --------------------------------------------------------
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

require "lib/set_env.php";

$hids = implode(",", $hospital_ids);
$s_arr = $db->query("select sname,count(sname) as c from hospital where ishide=0 and id in ($hids) group by sname order by sname asc", "sname", "c");
foreach ($s_arr as $sname => $count) {
	if ($count <= 1) unset($s_arr[$sname]);
}

// 查询每个医院的下属科室:
foreach ($s_arr as $sname => $count) {
	$h_arr[$sname] = $db->query("select id,depart as name from hospital where ishide=0 and id in ($hids) and sname='$sname' order by name asc", "id", "name");
}

function _list_check($check_name, $arr, $key_use, $value_use, $default_value=array(), $split_char = " ", $onclick="") {
	$res = array();
	$id_base = $check_name."_".mt_rand(1, 9999)."_";
	foreach ($arr as $k => $v) {
		$id = $id_base.$k;
		$key = $key_use == "k" ? $k : $v;
		$value = $value_use == "k" ? $k : $v;
		$check = @in_array($key, $default_value) ? " checked" : "";
		$click = $onclick ? ' onclick="'.$onclick.'"' : "";
		$res[] = '<input type="checkbox" name="'.$check_name.'" value="'.$key.'" id="'.$id.'"'.$check.$click.'><label for="'.$id.'">'.$value.'</label>';
	}
	return implode($split_char, $res);
}


$qudao_arr = array(
	1 => "百度PC",
	2 => "百度无线",
	25 => "百度健康",
	26 => "百度网盟",
	21 => "搜狗",
	//22 => "搜狗无线",
	23 => "360",
	24 => "神马",
	3 => "其它搜索引擎",
);

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

$cal_hids = $_GET["sub_hid"][$_GET["g"]];

if (count($cal_hids) > 0) {
	$cal_hid_str = implode(",", $cal_hids);

	$b = date("Ymd", $from_time);
	$e = date("Ymd", $to_time);

	// 调用费用:
	$fei_db = new mysql($sys_feiyong_db_connect);
	$xiaofei = $fei_db->query("select sum(engine_fee) as x1, sum(wuxian_fee) as x2, sum(baidu_jiankang_fee) as x25, sum(baidu_wangmeng_fee) as x26, sum(sougou_pc)+sum(sougou_wuxian) as x21, sum(sougou_pc) as q177, sum(sougou_wuxian) as q178, sum(f360) as x23, sum(shenma_fee) as x24, sum(other_engine_fee) as x3 from fee_day where hid in ($cal_hid_str) and date>=$b and date<=$e", 1);

	$guiji_data = $qudao_data = array();
	foreach ($cal_hids as $hid) {
		// 使用group by的速度非常快:
		$yuyue_guiji = $db->query("select guiji, count(guiji) as c from patient_{$hid} where part_id!=4 and addtime>=$from_time and addtime<=$to_time and guiji!='' group by guiji", "guiji", "c");
		$daoyuan_guiji = $db->query("select guiji, count(guiji) as c from patient_{$hid} where part_id!=4 and order_date>=$from_time and order_date<=$to_time and guiji!='' and status=1 group by guiji", "guiji", "c");

		$yuyue_qudao = $db->query("select qudao, count(qudao) as c from patient_{$hid} where part_id!=4 and addtime>=$from_time and addtime<=$to_time and qudao!='' group by qudao", "qudao", "c");
		$daoyuan_qudao = $db->query("select qudao, count(qudao) as c from patient_{$hid} where part_id!=4 and order_date>=$from_time and order_date<=$to_time and qudao!='' and status=1 group by qudao", "qudao", "c");

		// 数据运算:
		$line_set = array();
		foreach ($guiji_arr as $gid => $gname) {
			//$line_set[] = array(1, "g", $gid, $gname);
			$guiji_data[$gid][1] = array_key_exists($gid, $qudao_arr) ? $xiaofei["x".$gid] : "";
			$guiji_data[$gid][2] += $yuyue_guiji[$gid];
			$guiji_data[$gid][3] += $daoyuan_guiji[$gid];
			//$sum_data[2] += $yuyue_guiji[$gid];
			//$sum_data[3] += $daoyuan_guiji[$gid];
			$guiji_data[$gid][4] = array_key_exists($gid, $qudao_arr) ? round($guiji_data[$gid][1] / $guiji_data[$gid][2]) : "";
			$guiji_data[$gid][5] = array_key_exists($gid, $qudao_arr) ? round($guiji_data[$gid][1] / $guiji_data[$gid][3]) : "";

			// 渠道数据:
			$data = $db->query("select * from dict_qudao where main_id=$gid order by sort desc, id asc");
			foreach ($data as $line) {
				$qid = $line["id"];
				$qudao[$gid][$qid] = $line["name"];
				$qudao_data[$qid][1] = $xiaofei["q".$qid];
				$qudao_data[$qid][2] += $yuyue_qudao[$qid];
				$qudao_data[$qid][3] += $daoyuan_qudao[$qid];
				$qudao_data[$qid][4] = '';
				$qudao_data[$qid][5] = '';
			}
		}

		// 未标记的，也算入无轨迹中:
		$yuyue_wuguiji = $db->query("select count(*) as c from patient_{$hid} where part_id!=4 and addtime>=$from_time and addtime<=$to_time and guiji=''", 1, "c");
		$daoyuan_wuguiji = $db->query("select count(*) as c from patient_{$hid} where part_id!=4 and order_date>=$from_time and order_date<=$to_time and guiji='' and status=1", 1, "c");
		$guiji_data[9][2] += $yuyue_wuguiji;
		$guiji_data[9][3] += $daoyuan_wuguiji;
		//$sum_data[2] += $yuyue_wuguiji;
		//$sum_data[3] += $daoyuan_wuguiji;

		// 已经隐藏了的新闻源 也算入汇总里（否则数据对不上）
		$sum_data[2] += array_sum($yuyue_guiji) + $yuyue_wuguiji;
		$sum_data[3] += array_sum($daoyuan_guiji) + $daoyuan_wuguiji;

	} // end foreach hid


	/*
	// 需要局部合并的:
	$todo_ids[5] = array(6,7,8,11,14);

	foreach ($todo_ids as $gid_a => $arr) {
		foreach ($arr as $gid_b) {
			$qudao[$gid_a][$guiji_arr[$gid_b]] = $guiji_arr[$gid_b];
			$qudao_data[$guiji_arr[$gid_b]] = $guiji_data[$gid_b];
			foreach ($qudao[$gid_b] as $k => $v) {
				$qudao[$gid_a][$k] = "　　　".$v;
			}
			// 数据向上叠加:
			foreach ($qudao_data[$guiji_arr[$gid_b]] as $k => $v) {
				$guiji_data[$gid_a][$k] += $v;
			}
			unset($qudao[$gid_b]);
			unset($guiji_arr[$gid_b]);
		}
	}
	*/


	// 查询外网合作费 begin
	$b = date("Ymd", $from_time);
	$e = date("Ymd", $to_time);
	$cur_date = $b;
	$_count = 0;
	while ($cur_date <= $e) {
		$fee = $fei_db->query("select sum(xiaofei_per_day) as c from fee_platform where hid in ($cal_hid_str) and date_begin<=$cur_date and date_end>=$cur_date", 1, "c");
		$fee_arr[$cur_date] = $fee;

		ob_start();
		$fee_sub = $fei_db->query("select wai_id, sum(xiaofei_per_day) as c from fee_platform where wai_id>0 and hid in ($cal_hid_str) and date_begin<=$cur_date and date_end>=$cur_date group by wai_id", "wai_id", "c");
		foreach ($fee_sub as $_wai_id => $fee_count) {
			$todo_wai_id[] = $_wai_id;
			$qudao_data[$_wai_id][1] += $fee_count;
			$qudao_data[$_wai_id][4] = @round($qudao_data[$_wai_id][1] / $qudao_data[$_wai_id][2]);
			$qudao_data[$_wai_id][5] = @round($qudao_data[$_wai_id][1] / $qudao_data[$_wai_id][3]);
		}
		ob_get_clean();

		$cur_date = date("Ymd", strtotime("+1 days", strtotime(int_date_to_date($cur_date)))); //+1天继续

		// 防止死循环出现:
		if ($_count++ > 1000) exit("循环可能出错...");
	}
	$waiwang_fee = round(array_sum($fee_arr));

	foreach ($todo_wai_id as $_wai_id) {
		$qudao_data[$_wai_id][1] = round($qudao_data[$_wai_id][1]);
	}

	$guiji_data[5][1] = $waiwang_fee;
	$guiji_data[5][4] = @round($waiwang_fee / $guiji_data[5][2]);
	$guiji_data[5][5] = @round($waiwang_fee / $guiji_data[5][3]);
	// end 查询外网合作费


	// 查询新媒体费用 begin
	$b = date("Ymd", $from_time);
	$e = date("Ymd", $to_time);
	$cur_date = $b;
	$_count = 0;
	while ($cur_date <= $e) {
		ob_start();
		$fee = $fei_db->query("select sum(xiaofei_per_day) as c from shejiao_fee where hid in ($cal_hid_str) and date_begin<=$cur_date and date_end>=$cur_date", 1, "c");
		ob_get_clean();
		$fee_arr[$cur_date] = $fee;
		$cur_date = date("Ymd", strtotime("+1 days", strtotime(int_date_to_date($cur_date)))); //+1天继续

		// 防止死循环出现:
		if ($_count++ > 1000) exit("循环可能出错...");
	}
	$xinmeiti_fee = round(array_sum($fee_arr));

	$guiji_data[35][1] = $xinmeiti_fee;
	$guiji_data[35][4] = @round($xinmeiti_fee / $guiji_data[35][2]);
	$guiji_data[35][5] = @round($xinmeiti_fee / $guiji_data[35][3]);
	// end 查询新媒体费用


	// 汇总数据
	$sum_data[1] = array_sum($xiaofei) + $waiwang_fee + $xinmeiti_fee;
	//$sum_data[2] = array_sum($yuyue_guiji);
	//$sum_data[3] = array_sum($daoyuan_guiji);
	$sum_data[4] = @round($sum_data[1] / $sum_data[2], 0);
	$sum_data[5] = @round($sum_data[1] / $sum_data[3], 0);


	// 导医数据:
	$daoyi_daoyuan = 0;
	foreach ($cal_hids as $hid) {
		$daoyi_daoyuan += $db->query("select count(*) as c from patient_{$hid} where part_id in (4) and order_date>=$from_time and order_date<=$to_time and status=1", 1, "c");
	}

	// 导医到诊对应费用 = 市场部费用+户外广告
	$cur_date = $b;
	$_count = 0;
	$fee_arr = $fee2_arr = array();
	while ($cur_date <= $e) {
		ob_start();
		$fee = @$fei_db->query("select sum(xiaofei_per_day) as c from fee_shichang where hid in ($cal_hid_str) and date_begin<=$cur_date and date_end>=$cur_date", 1, "c");
		$fee_arr[$cur_date] = $fee;
		$fee2 = @$fei_db->query("select sum(xiaofei_per_day) as c from fee_media where hid in ($cal_hid_str) and date_begin<=$cur_date and date_end>=$cur_date", 1, "c");
		$fee2_arr[$cur_date] = $fee2;
		ob_get_clean();
		$cur_date = date("Ymd", strtotime("+1 days", strtotime(int_date_to_date($cur_date)))); //+1天继续

		// 防止死循环出现:
		if ($_count++ > 1000) exit("循环可能出错...");
	}
	$shichang_fee = round(array_sum($fee_arr));
	$huwai_fee = round(array_sum($fee2_arr));

	$daoyi[1] = $daoyi_fee = $shichang_fee + $huwai_fee;
	$daoyi[2] = "";
	$daoyi[3] = $daoyi_daoyuan;
	$daoyi[4] = "";
	$daoyi[5] = @round($daoyi[1] / $daoyi[3]);

	$all_sum[1] = $sum_data[1] + $daoyi_fee;
	$all_sum[2] = $sum_data[2];
	$all_sum[3] = $sum_data[3] + $daoyi_daoyuan;
	$all_sum[4] = $sum_data[4];
	$all_sum[5] = @round($all_sum[1] / $all_sum[3], 0);

	$all_sum_repeat = $all_sum;

}

?>
<html>
<head>
<title>渠道成本统计</title>
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

.m_select {border:1px solid #c6c6c6 !important; width:100%; }
.m_select td {border:1px solid #c6c6c6 !important; padding:4px 8px !important; }
</style>
<script language="javascript">
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

function show_huizong() {
	var from_date = byid("from_date").value;
	var to_date = byid("to_date").value;
	parent.load_src(1, 'qudao_chengben_huizong.php?from_date='+from_date+"&to_date="+to_date);
}

function show_disease_report() {
	var from_date = byid("from_date").value;
	var to_date = byid("to_date").value;
	parent.load_src(1,'qudao_report_disease.php?from_date='+from_date+"&to_date="+to_date);
}


function _zhan(gid, obj) {
	if (obj.title == "展开") {
		var method = "展开";
		obj.title = "折叠";
		obj.innerHTML = '<img src="image/wee_jian.gif" align="absmiddle">';
		var display = "";
	} else {
		var method = "折叠";
		obj.title = "展开";
		obj.innerHTML = '<img src="image/wee_jia.gif" align="absmiddle">';
		var display = "none";
	}

	var trs = byid("zhan_table").getElementsByTagName("TR");
	for (var i=0; i<trs.length; i++) {
		var tr = trs[i];
		var tr_info = tr.id.split("_");
		if (tr_info[0] == "s" && tr_info[1] == gid) {
			tr.style.display = display;
		}
	}

	var zhan = get_cookie("qudao_chengben_zhan");
	var zhan_arr = zhan.split("|");
	if (method == "展开") {
		// 展开状态记录到cookie中:
		if (zhan == '') {
			zhan_arr[0] = gid;
		} else {
			if (!in_array(gid, zhan_arr)) {
				zhan_arr[zhan_arr.length] = gid;
			}
		}
	} else {
		// 从数组中删除:
		if (in_array(gid, zhan_arr)) {
			for (var i=0; i<zhan_arr.length; i++) {
				if (zhan_arr[i] == gid) {
					zhan_arr.splice(i, 1);
				}
			}
		}
	}

	var zhan = zhan_arr.join("|");
	set_cookie("qudao_chengben_zhan", zhan, 99999999);
}


// type=1 展开 0 折叠
function zhan_with_all(type) {
	var a_title = type ? "展开" : "折叠";
	var tr = byid("zhan_table").getElementsByTagName("TR");
	for (var i=0; i<tr.length; i++) {
		if (tr[i].id.split("_")[0] == "g") {
			var gid = tr[i].id.split("_")[1];
			byid("zhan_"+gid).title = a_title;
			byid("zhan_"+gid).onclick();
		}
	}
}

function menu_sub_check(o) {
	var value = o.value;
	var g = byid("sub_check_"+value).getElementsByTagName("INPUT");
	for (var i = 0; i < g.length; i++) {
		g[i].checked = o.checked;
	}
}

var menu_check_flag = true;
function menu_check_all() {
	var g = byid("menu_select_area").getElementsByTagName("INPUT");
	for (var i = 0; i < g.length; i++) {
		g[i].checked = menu_check_flag;
	}
	menu_check_flag = !menu_check_flag;
}

function update_sub_hid(s) {
	var g = byid("sub_hid_select").getElementsByTagName("DIV");
	for (var i=0; i<g.length; i++) {
		g[i].style.display = "none";
	}
	byid("s_"+s).style.display = "";
}

function open_in_new_window(obj) {
	var url = self.location;
	obj.href = url;
	parent.load_src(0);
}

</script>
</head>

<body>

<?php
if (count($s_arr) == 0) {
	echo '<div id="no_data">没有可供汇总的科室</div>';
} else {
?>

<form method="GET" action="" onsubmit="">
<table class="m_select" id="menu_select_area">
	<tr>
		<td style="width:120px" align="right">请选择医院：</td>
		<td style="">
			<select name="g" class="combo" onchange="update_sub_hid(this.value)">
				<option value="">--请选择医院--</option>
				<?php echo list_option($s_arr, "_key_", "_key_", $_GET["g"]); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right">选择具体科室：</td>
		<td id="sub_hid_select">
<?php
foreach ($s_arr as $sname => $count) {
	echo '<div id="s_'.$sname.'" style="display:none">';
	echo _list_check("gg[]", array($sname=>"全选"), "k", "v", array(), " ", "menu_sub_check(this)");
	echo '	<span id="sub_check_'.$sname.'">&nbsp;';
	echo _list_check("sub_hid[$sname][]", $h_arr[$sname], "k", "v", $_GET["sub_hid"][$sname]);
	echo '	</span>';
	echo '</div>';
}
?>
		</td>
	</tr>
	<tr>
		<td align="right">日期起止：</td>
		<td><input name="from_date" id="from_date" class="input" size="12" value="<?php echo $from_date; ?>" onclick="picker({el:'from_date',dateFmt:'yyyy-MM-dd'})"> ~ <input name="to_date" id="to_date" class="input" size="12" value="<?php echo $to_date; ?>" onclick="picker({el:'to_date',dateFmt:'yyyy-MM-dd'})">
		</td>
	</tr>
	<tr>
		<td align="right"></td>
		<td>
			<input type="submit" class="buttonb" value="点此查询">
			<a href="javascript:;" onclick="self.location.reload();" style="margin-left:50px;">刷新</a>
			<a href="#" onclick="open_in_new_window(this)" target="_blank" style="margin-left:20px;">在新窗口打开</a>
			<a href="javascript:;" onclick="self.window.print();" style="margin-left:20px;">打印</a>
		</td>
	</tr>
</table>
</form>
<?php } ?>

<script type="text/javascript">
update_sub_hid("<?php echo $_GET["g"]; ?>");
</script>


<?php if (count($cal_hids) > 0) { ?>

<div class="space"></div>

<form name="mainform">

<div><a href="javascript:;" onclick="zhan_with_all(1)">全部展开</a>　<a href="javascript:;" onclick="zhan_with_all(0)">全部折叠</a></div>

<style type="text/css">
.print_list {border:2px solid silver !important; }
.print_list td {border-color:silver !important; }
</style>

<table width="100%" align="center" class="list print_list" id="zhan_table" style="margin-top:10px;">
	<tr>
		<td class="head" align="left" width="25%">科室名称</td>
		<td class="head" align="center" width="15%">费用</td>
		<td class="head" align="center" width="15%">预约</td>
		<td class="head" align="center" width="15%">到诊</td>
		<td class="head" align="center" width="15%">纯广告预约成本</td>
		<td class="head" align="center" width="15%">纯广告到诊成本</td>
	</tr>

	<!-- 主要列表数据 begin -->
<?php

foreach ($guiji_arr as $gid => $gname) {
?>
	<tr id="g_<?php echo $gid; ?>" onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item"><b style="color:red"><?php echo $gname; ?></b> <span id="zhan_<?php echo $gid; ?>" onclick="_zhan('<?php echo $gid; ?>', this)" title="展开" style="cursor:pointer;"><img src="image/wee_jia.gif" align="absmiddle"></span></td>
		<td align="center" class="item"><b style="color:red"><?php echo $guiji_data[$gid][1]; ?></b></td>
		<td align="center" class="item"><b style="color:red"><?php echo $guiji_data[$gid][2]; ?></b></td>
		<td align="center" class="item"><b style="color:red"><?php echo $guiji_data[$gid][3]; ?></b></td>
		<td align="center" class="item"><b style="color:red"><?php echo $guiji_data[$gid][4]; ?></b></td>
		<td align="center" class="item"><b style="color:red"><?php echo $guiji_data[$gid][5]; ?></b></td>
	</tr>
<?php

	foreach ($qudao[$gid] as $id => $q_name) {
		$color = substr_count($q_name, "　　　") > 0 ? "#a4a4d2" : "";
?>
	<tr id="s_<?php echo $gid."_".$id; ?>" onmouseover="mi(this)" onmouseout="mo(this)" style="display:none; color:<?php echo $color; ?>">
		<td align="left" class="item">　　　<?php echo $q_name; ?></td>
		<td align="center" class="item"><?php echo $qudao_data[$id][1]; ?></td>
		<td align="center" class="item"><?php echo $qudao_data[$id][2]; ?></td>
		<td align="center" class="item"><?php echo $qudao_data[$id][3]; ?></td>
		<td align="center" class="item"><?php echo $qudao_data[$id][4]; ?></td>
		<td align="center" class="item"><?php echo $qudao_data[$id][5]; ?></td>
	</tr>
<?php
	}
}
?>

	<!-- 合计部分 -->
	<tr id="h_0" onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item"><b style="color:red">网络+电话合计</b></td>
		<td align="center" class="item"><?php echo $sum_data[1]; ?></td>
		<td align="center" class="item"><?php echo $sum_data[2]; ?></td>
		<td align="center" class="item"><?php echo $sum_data[3]; ?></td>
		<td align="center" class="item"><?php echo $sum_data[4]; ?></td>
		<td align="center" class="item"><?php echo $sum_data[5]; ?></td>
	</tr>

	<!-- 自然到诊 -->
	<tr id="h_0" onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item"><b style="color:blue">自然到诊(导医)</b></td>
		<td align="center" class="item"><?php echo $daoyi[1]; ?></td>
		<td align="center" class="item"><?php echo $daoyi[2]; ?></td>
		<td align="center" class="item"><?php echo $daoyi[3]; ?></td>
		<td align="center" class="item"><?php echo $daoyi[4]; ?></td>
		<td align="center" class="item"><?php echo $daoyi[5]; ?></td>
	</tr>

	<!-- 全部合计 -->
	<tr id="h_0" onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item"><b style="color:blue">全部合计</b></td>
		<td align="center" class="item"><?php echo $all_sum[1]; ?></td>
		<td align="center" class="item"><?php echo $all_sum[2]; ?></td>
		<td align="center" class="item"><?php echo $all_sum[3]; ?></td>
		<td align="center" class="item"><?php echo $all_sum[4]; ?></td>
		<td align="center" class="item"><?php echo $all_sum[5]; ?></td>
	</tr>

	<!-- 主要列表数据 end -->
</table>
</form>


<?php
if (count($cal_hids) > 1) { // 按每家科室分别计算汇总 @ 2016-12-06
?>
<br>
<br>
<table width="100%" align="center" class="list print_list" style="margin-top:10px;">
	<tr>
		<td class="head" align="left" width="25%">科室名称</td>
		<td class="head" align="center" width="15%">费用</td>
		<td class="head" align="center" width="15%">预约</td>
		<td class="head" align="center" width="15%">到诊</td>
		<td class="head" align="center" width="15%">纯广告预约成本</td>
		<td class="head" align="center" width="15%">纯广告到诊成本</td>
	</tr>

<?php
	foreach ($cal_hids as $hid) {

		$dp_name = $db->query("select depart from hospital where id=$hid limit 1", 1, "depart");

		$b = date("Ymd", $from_time);
		$e = date("Ymd", $to_time);

		// 调用费用:
		$fei_db = new mysql($sys_feiyong_db_connect);
		$xiaofei = $fei_db->query("select sum(engine_fee) as x1, sum(wuxian_fee) as x2, sum(baidu_jiankang_fee) as x25, sum(baidu_wangmeng_fee) as x26, sum(sougou_pc)+sum(sougou_wuxian) as x21, sum(sougou_pc) as q177, sum(sougou_wuxian) as q178, sum(f360) as x23, sum(shenma_fee) as x24, sum(other_engine_fee) as x3 from fee_day where hid in ($hid) and date>=$b and date<=$e", 1);

		$sum_data = $guiji_data = $qudao_data = array();

		// 使用group by的速度非常快:
		$yuyue_guiji = $db->query("select guiji, count(guiji) as c from patient_{$hid} where part_id!=4 and addtime>=$from_time and addtime<=$to_time and guiji!='' group by guiji", "guiji", "c");
		$daoyuan_guiji = $db->query("select guiji, count(guiji) as c from patient_{$hid} where part_id!=4 and order_date>=$from_time and order_date<=$to_time and guiji!='' and status=1 group by guiji", "guiji", "c");

		$yuyue_qudao = $db->query("select qudao, count(qudao) as c from patient_{$hid} where part_id!=4 and addtime>=$from_time and addtime<=$to_time and qudao!='' group by qudao", "qudao", "c");
		$daoyuan_qudao = $db->query("select qudao, count(qudao) as c from patient_{$hid} where part_id!=4 and order_date>=$from_time and order_date<=$to_time and qudao!='' and status=1 group by qudao", "qudao", "c");

		// 数据运算:
		$line_set = array();
		foreach ($guiji_arr as $gid => $gname) {
			//$line_set[] = array(1, "g", $gid, $gname);
			$guiji_data[$gid][1] = array_key_exists($gid, $qudao_arr) ? $xiaofei["x".$gid] : "";
			$guiji_data[$gid][2] += $yuyue_guiji[$gid];
			$guiji_data[$gid][3] += $daoyuan_guiji[$gid];
			//$sum_data[2] += $yuyue_guiji[$gid];
			//$sum_data[3] += $daoyuan_guiji[$gid];
			$guiji_data[$gid][4] = array_key_exists($gid, $qudao_arr) ? round($guiji_data[$gid][1] / $guiji_data[$gid][2]) : "";
			$guiji_data[$gid][5] = array_key_exists($gid, $qudao_arr) ? round($guiji_data[$gid][1] / $guiji_data[$gid][3]) : "";

			// 渠道数据:
			$data = $db->query("select * from dict_qudao where main_id=$gid order by sort desc, id asc");
			foreach ($data as $line) {
				$qid = $line["id"];
				$qudao[$gid][$qid] = $line["name"];
				$qudao_data[$qid][1] = $xiaofei["q".$qid];
				$qudao_data[$qid][2] += $yuyue_qudao[$qid];
				$qudao_data[$qid][3] += $daoyuan_qudao[$qid];
				$qudao_data[$qid][4] = '';
				$qudao_data[$qid][5] = '';
			}
		}

		// 未标记的，也算入无轨迹中:
		$yuyue_wuguiji = $db->query("select count(*) as c from patient_{$hid} where part_id!=4 and addtime>=$from_time and addtime<=$to_time and guiji=''", 1, "c");
		$daoyuan_wuguiji = $db->query("select count(*) as c from patient_{$hid} where part_id!=4 and order_date>=$from_time and order_date<=$to_time and guiji='' and status=1", 1, "c");
		$guiji_data[9][2] += $yuyue_wuguiji;
		$guiji_data[9][3] += $daoyuan_wuguiji;

		//$sum_data[2] += $yuyue_wuguiji;
		//$sum_data[3] += $daoyuan_wuguiji;

		// 已经隐藏了的新闻源 也算入汇总里（否则数据对不上）
		//$sum_data[2] += $yuyue_guiji[4] + $yuyue_guiji[30] + $yuyue_guiji[31] + $yuyue_guiji[32];
		//$sum_data[3] += $daoyuan_guiji[4] + $daoyuan_guiji[30] + $daoyuan_guiji[31] + $daoyuan_guiji[32];

		$sum_data[2] += array_sum($yuyue_guiji) + $yuyue_wuguiji;
		$sum_data[3] += array_sum($daoyuan_guiji) + $daoyuan_wuguiji;


		// 查询外网合作费 begin
		$b = date("Ymd", $from_time);
		$e = date("Ymd", $to_time);
		$cur_date = $b;
		$_count = 0;
		while ($cur_date <= $e) {
			$fee = $fei_db->query("select sum(xiaofei_per_day) as c from fee_platform where hid in ($hid) and date_begin<=$cur_date and date_end>=$cur_date", 1, "c");
			$fee_arr[$cur_date] = $fee;

			ob_start();
			$fee_sub = $fei_db->query("select wai_id, sum(xiaofei_per_day) as c from fee_platform where wai_id>0 and hid in ($hid) and date_begin<=$cur_date and date_end>=$cur_date group by wai_id", "wai_id", "c");
			foreach ($fee_sub as $_wai_id => $fee_count) {
				$todo_wai_id[] = $_wai_id;
				$qudao_data[$_wai_id][1] += $fee_count;
				$qudao_data[$_wai_id][4] = @round($qudao_data[$_wai_id][1] / $qudao_data[$_wai_id][2]);
				$qudao_data[$_wai_id][5] = @round($qudao_data[$_wai_id][1] / $qudao_data[$_wai_id][3]);
			}
			ob_get_clean();

			$cur_date = date("Ymd", strtotime("+1 days", strtotime(int_date_to_date($cur_date)))); //+1天继续

			// 防止死循环出现:
			if ($_count++ > 1000) exit("循环可能出错...");
		}
		$waiwang_fee = round(array_sum($fee_arr));

		foreach ($todo_wai_id as $_wai_id) {
			$qudao_data[$_wai_id][1] = round($qudao_data[$_wai_id][1]);
		}

		$guiji_data[5][1] = $waiwang_fee;
		$guiji_data[5][4] = @round($waiwang_fee / $guiji_data[5][2]);
		$guiji_data[5][5] = @round($waiwang_fee / $guiji_data[5][3]);
		// end 查询外网合作费


		// 查询新媒体费用 begin
		$b = date("Ymd", $from_time);
		$e = date("Ymd", $to_time);
		$cur_date = $b;
		$_count = 0;
		while ($cur_date <= $e) {
			ob_start();
			$fee = $fei_db->query("select sum(xiaofei_per_day) as c from shejiao_fee where hid in ($hid) and date_begin<=$cur_date and date_end>=$cur_date", 1, "c");
			ob_get_clean();
			$fee_arr[$cur_date] = $fee;
			$cur_date = date("Ymd", strtotime("+1 days", strtotime(int_date_to_date($cur_date)))); //+1天继续

			// 防止死循环出现:
			if ($_count++ > 1000) exit("循环可能出错...");
		}
		$xinmeiti_fee = round(array_sum($fee_arr));

		$guiji_data[35][1] = $xinmeiti_fee;
		$guiji_data[35][4] = @round($xinmeiti_fee / $guiji_data[35][2]);
		$guiji_data[35][5] = @round($xinmeiti_fee / $guiji_data[35][3]);
		// end 查询新媒体费用


		// 汇总数据
		$sum_data[1] = array_sum($xiaofei) + $waiwang_fee + $xinmeiti_fee;
		//$sum_data[2] = array_sum($yuyue_guiji);
		//$sum_data[3] = array_sum($daoyuan_guiji);
		$sum_data[4] = @round($sum_data[1] / $sum_data[2], 0);
		$sum_data[5] = @round($sum_data[1] / $sum_data[3], 0);


		// 导医数据:
		$daoyi_daoyuan = 0;
		$daoyi_daoyuan += $db->query("select count(*) as c from patient_{$hid} where part_id in (4) and order_date>=$from_time and order_date<=$to_time and status=1", 1, "c");


		// 导医到诊对应费用 = 市场部费用+户外广告
		$cur_date = $b;
		$_count = 0;
		$fee_arr = $fee2_arr = array();
		while ($cur_date <= $e) {
			ob_start();
			$fee = @$fei_db->query("select sum(xiaofei_per_day) as c from fee_shichang where hid in ($hid) and date_begin<=$cur_date and date_end>=$cur_date", 1, "c");
			$fee_arr[$cur_date] = $fee;
			$fee2 = @$fei_db->query("select sum(xiaofei_per_day) as c from fee_media where hid in ($hid) and date_begin<=$cur_date and date_end>=$cur_date", 1, "c");
			$fee2_arr[$cur_date] = $fee2;
			ob_get_clean();
			$cur_date = date("Ymd", strtotime("+1 days", strtotime(int_date_to_date($cur_date)))); //+1天继续

			// 防止死循环出现:
			if ($_count++ > 1000) exit("循环可能出错...");
		}
		$shichang_fee = round(array_sum($fee_arr));
		$huwai_fee = round(array_sum($fee2_arr));

		$daoyi = array();
		$daoyi[1] = $daoyi_fee = $shichang_fee + $huwai_fee;
		$daoyi[2] = "";
		$daoyi[3] = $daoyi_daoyuan;
		$daoyi[4] = "";
		$daoyi[5] = @round($daoyi[1] / $daoyi[3]);

		$all_sum = array();
		$all_sum[1] = $sum_data[1] + $daoyi_fee;
		$all_sum[2] = $sum_data[2];
		$all_sum[3] = $sum_data[3] + $daoyi_daoyuan;
		$all_sum[4] = $sum_data[4];
		$all_sum[5] = @round($all_sum[1] / $all_sum[3], 0);

?>
	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item"><?php echo $dp_name; ?></td>
		<td align="center" class="item"><?php echo $all_sum[1]; ?></td>
		<td align="center" class="item"><?php echo $all_sum[2]; ?></td>
		<td align="center" class="item"><?php echo $all_sum[3]; ?></td>
		<td align="center" class="item"><?php echo $all_sum[4]; ?></td>
		<td align="center" class="item"><?php echo $all_sum[5]; ?></td>
	</tr>
<?php
	}
?>
	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item"><b style="color:blue">全部合计</b></td>
		<td align="center" class="item"><?php echo $all_sum_repeat[1]; ?></td>
		<td align="center" class="item"><?php echo $all_sum_repeat[2]; ?></td>
		<td align="center" class="item"><?php echo $all_sum_repeat[3]; ?></td>
		<td align="center" class="item"><?php echo $all_sum_repeat[4]; ?></td>
		<td align="center" class="item"><?php echo $all_sum_repeat[5]; ?></td>
	</tr>
	</table>
<?php
}
?>


<script type="text/javascript">
var zhan = get_cookie("qudao_chengben_zhan");
if (zhan != '') {
	var zhan_arr = zhan.split("|");
	for (var i=0; i<zhan_arr.length; i++) {
		if (byid("zhan_"+zhan_arr[i])) {
			byid("zhan_"+zhan_arr[i]).onclick();
		}
	}
}
</script>

<br>
<!-- 执行耗时：<?php echo round(now() - $pagebegintime, 4); ?>s -->
<br>
<br>

<?php } ?>

</body>
</html>