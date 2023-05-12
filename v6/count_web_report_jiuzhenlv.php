<?php
/* --------------------------------------------------------
// 说明: 就诊率对比报表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2015-6-6
// ----------------------------------------------------- */
require "lib/set_env.php";
include "chart/FusionCharts_Gen.php";
$table = "count_web";


$hids = implode(",", $hospital_ids);
if ($debug_mode) {
	//$hids = "5,6,7,8,9,10,11,40,50,60,70,80";
}
$types_all = $db->query("select t.id,t.hid,h.sname,h.name as h_name,t.name from count_type t, hospital h where t.ishide=0 and t.type='web' and t.hid in ($hids) and t.hid=h.id order by h.name asc, t.name asc", "id");

$options = array();
$s_name_arr = $h_name_arr = array();
foreach ($types_all as $li) {
	if (!in_array($li["sname"], $s_name_arr)) {
		$s_name_arr[] = $li["sname"];
		$options[] = array("a" => "sname:".$li["sname"], "b" => $li["sname"]);
	}
	if (!in_array($li["h_name"], $h_name_arr)) {
		$h_name_arr[] = $li["h_name"];
		$options[] = array("a" => "hid:".$li["hid"], "b" => "　　　".$li["h_name"]);
	}
	$options[] = array("a" => "type:".$li["id"], "b" => "　　　　　　".$li["name"]);
}


// 操作的处理:
$cur_type = $_GET["count_method"];
if ($cur_type != "") {
	list($method, $values) = explode(":", $cur_type, 2);
	if ($method == "sname") {
		$_hids = $db->query("select id from hospital where sname='$values' and id in ($hids)", "", "id");
		$hid_str = count($_hids) > 0 ? implode(",", $_hids) : "0";
		$type_ids = $db->query("select id from count_type where ishide=0 and type='web' and hid in ($hid_str)", "", "id");
	} else if ($method == "hid") {
		$hid = intval($values);
		$type_ids = $db->query("select id from count_type where ishide=0 and type='web' and hid=$hid and hid in ($hids)", "", "id");
	} else {
		$type_ids = array(intval($values));
	}

	// 本次查询要涉及的type_id
	$type_id_str = count($type_ids) > 0 ? implode(",", $type_ids) : "0";


	//最近一年的月份:
	$_t = strtotime(date("Y-m-01"));
	$month_arr = array();
	for ($i=12; $i>=1; $i--) {
		$_t2 = strtotime("-".$i." month", $_t);
		$month_arr[date("Ym", $_t2)] = array(date("Y.n", $_t2), $_t2, strtotime("+1 month", $_t2) - 1);
	}


	$kefu_list = $need_data = array();
	foreach ($month_arr as $m_int => $m_arr) {
		$m_name = $m_arr[0];
		$m_begin = date("Ymd", $m_arr[1]);
		$m_end = date("Ymd", $m_arr[2]);

		// 对各客服查询数据:
		$arr = $db->query("select kefu, sum(come_all) as a, sum(click) as b from $table where type_id in ($type_id_str) and date>=$m_begin and date<=$m_end group by kefu", "kefu");
		foreach ($arr as $kf_name => $per) {
			if ($per["a"] > 0 && $per["b"] > 0) {
				if (!in_array($kf_name, $kefu_list)) {
					$kefu_list[] = $kf_name;
				}
				$percent = round($per["a"] / $per["b"] * 100, 1);
				if ($percent > 100) {
					$percent = "无效";
				}
				$need_data[$kf_name][$m_name] = $percent;
			}
		}
	}


	// 计算均值
	$all_all_count = $all_all_num = 0;
	foreach ($kefu_list as $kf_name) {
		$count_all = $num_count = 0;
		foreach ($month_arr as $m_int => $m_arr) {
			$m_name = $m_arr[0];
			if ($need_data[$kf_name][$m_name] > 0) {
				$count_all += $need_data[$kf_name][$m_name];
				$all_all_count += $need_data[$kf_name][$m_name];
				$num_count += 1;
				$all_all_num += 1;
			}
		}
		$per = round($count_all / $num_count, 1);
		$junzhi_arr[$kf_name] = $per;
	}

	$all_per = round($all_all_count / $all_all_num, 1);
}

function wee_compare_with_junzhi($num) {
	global $all_per;
	if ($num > 0) {
		if ($num >= $all_per) {
			return '<font color="green" title="高于全体均值">'.$num."% ↑</font>";
		} else {
			return '<font color="red" title="低于全体均值">'.$num."% ↓</font>";
		}
	}
	return $num;
}


?>
<html>
<head>
<title>咨询就诊率对比图</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/sorttable_keep.js" language="javascript"></script>
<script type="text/javascript">
</script>
<style>
body {padding:5px 8px; }
form {display:inline; }
.combo {font-family:"宋体"; height:21px; }
.name_show, .name_show * {font-family:"微软雅黑"; font-size:13px; }
</style>
</head>

<body>
<div style="margin:30px 0 0 0px;">
	<center>
	<a href="count_web.php">[返回]</a>
	<form method="GET" style="margin-left:30px;">
		<select name="count_method" id="count_method" class="combo" onchange="this.form.submit()">
			<option value="" style="color:gray">-请选择医院/科室/统计项目-</option>
			<?php echo list_option($options, "a", "b", $_GET["count_method"]); ?>
		</select>　　(第一级为医院汇总；第二级为科室汇总；第三级为具体统计项)
		<input type="hidden" name="op" value="change_type">

	</form>

	</center>
</div>

<div style="margin-top:20px; font-size:15px; text-align:center;">咨询员最近12个月咨询就诊率比较 (全体均值=<?php echo $all_per; ?>%)</div>

<table width="100%" align="center" class="list sortable" id="jiuzhenlv" style="margin-top:20px;">
	<tr>
		<td class="head column_sortable" title="点击可排序" align="center">姓名</td>
<?php foreach ($month_arr as $m_int => $m_arr) { ?>
		<td class="head column_sortable sorttable_numeric" title="点击可排序" align="center"><?php echo $m_arr[0]; ?></td>
<?php } ?>
		<td class="head column_sortable sorttable_numeric" title="点击可排序" align="center">平均值</td>

	</tr>

<?php foreach ($kefu_list as $kf_name) { ?>
	<tr>
		<td class="item" align="center"><?php echo $kf_name; ?></td>
<?php   foreach ($month_arr as $m_int => $m_arr) { ?>
		<td class="item" align="center"><?php echo $need_data[$kf_name][$m_arr[0]] ? wee_compare_with_junzhi($need_data[$kf_name][$m_arr[0]]) : "--"; ?></td>
<?php   } ?>
		<td class="item" align="center"><?php echo $junzhi_arr[$kf_name] > 0 ? wee_compare_with_junzhi($junzhi_arr[$kf_name]) : "--"; ?></td>
	</tr>
<?php   } ?>

</table>


<br>
<center>(说明：该表为总就诊率，非商务通就诊率)</center>
<br>
<br>


</body>
</html>