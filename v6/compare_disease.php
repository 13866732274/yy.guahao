<?php
/*
// 说明: 跨科室病种对比
// 作者: 幽兰 (weelia@126.com)
// 时间: 2014-3-20
*/
include "lib/set_env.php";


$hids = implode(",", $hospital_ids);
$h_list = $db->query("select id,name from hospital where ishide=0 and id in ($hids) order by name asc", "id", "name");



// 读取对比配置数据:
$d = $db->query("select content from compare_disease where uid=$uid limit 1", 1, "content");
$con_arr = array();
if ($d != '') {
	$con_arr = @unserialize($d);
}

$op = $_GET["op"];
if ($op == "delete") {
	$index = intval($_GET["index"]);
	unset($con_arr[$index]);

	$s = serialize($con_arr);
	$db->query("update compare_disease set content='$s' where uid=$uid limit 1");

	header("location:compare_disease.php");
	exit;
}


// 初始值为本月:
if ($_GET["btime"] == '') {
	$_GET["btime"] = date("Y-m-d", mktime(0,0,0,date("m"), 1));
}
if ($_GET["etime"] == '') {
	$_GET["etime"] = date("Y-m-d", strtotime("+1 month", strtotime($_GET["btime"]." 0:0:0")) - 1);
}


function _num($num) {
	if ($num == '') return 0;
	return $num;
}


?>
<html>
<head>
<title>跨科室病种数据对比</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>

<style type="text/css">
* {font-family:"微软雅黑" !important; }
.add_area {text-align:center; margin-top:15px; }

.page_name {text-align:center; font-size:18px; margin-top:15px; }
.list {margin-top:20px; }

.td_l {text-align:left !important; }
.td_c {text-align:center !important; }
.td_r {text-align:right !important; }

.delete {color:#8398be; font-weight:normal; }
</style>

<script type="text/javascript">
function write_dt(da, db) {
	byid("begin_time").value = da;
	byid("end_time").value = db;
}

function delete_compare(k) {
	var url = "compare_disease.php?op=delete&index="+k;
	if (!confirm("确定要删除编号为“ "+(k+1)+" ”的对比项目吗？")) {
		return false;
	}
	self.location = url;
}
</script>
</head>

<body>

<div class="page_name">跨科室病种数据对比</div>

<?php

foreach ($con_arr as $k => $def) {
	echo '<table class="list" width="100%">';

	echo '<tr>';
	echo ' <td class="head" width="15%">科室 &nbsp; <a href="#" class="delete" onclick="return delete_compare('.$k.')">删除</a></td>';
	echo ' <td class="head" width="15%">疾病</td>';

	echo ' <td class="head td_c">今日约</td>';
	echo ' <td class="head td_c">今日到</td>';

	echo ' <td class="head td_c">昨日约</td>';
	echo ' <td class="head td_c">昨日到</td>';

	echo ' <td class="head td_c">本月约</td>';
	echo ' <td class="head td_c">本月到</td>';

	echo ' <td class="head td_c">上月约</td>';
	echo ' <td class="head td_c">上月到</td>';
	echo '</tr>';

	foreach ($def as $index => $h_d) {
		$_hid = $h_d[0];
		$_dis = $h_d[1];
		$h_name = $h_list[$_hid];
		$dis_name = $db->query("select name from disease where id=$_dis limit 1", 1, "name");

		$cache = $db->query("select data from index_cache where hid=$_hid limit 1", 1, "data");
		$cache_arr = @unserialize($cache);
		$data = $cache_arr["DIS_".$_dis];

		echo '<tr>';
		echo ' <td class="item">'.$h_name.'</td>';
		echo ' <td class="item">'.$dis_name.'</td>';

		echo ' <td class="item td_c">'._num($data["预约"]["今日"]).'</td>';
		echo ' <td class="item td_c">'._num($data["实到"]["今日"]).'</td>';

		echo ' <td class="item td_c">'._num($data["预约"]["昨日"]).'</td>';
		echo ' <td class="item td_c">'._num($data["实到"]["昨日"]).'</td>';

		echo ' <td class="item td_c">'._num($data["预约"]["本月"]).'</td>';
		echo ' <td class="item td_c">'._num($data["实到"]["本月"]).'</td>';

		echo ' <td class="item td_c">'._num($data["预约"]["上月"]).'</td>';
		echo ' <td class="item td_c">'._num($data["实到"]["上月"]).'</td>';
		echo '</tr>';
	}
	echo '</table>';
}

?>

<div class="add_area">
	<a href="javascript:;" onclick="add_compare(); return false;" class="add_compare">[添加对比项目]</a>
	<script type="text/javascript">
	function add_compare() {
		var link = "compare_add.php";
		parent.load_src(1, link, 800, 400);
		return false;
	}
	</script>
</div>

</body>
</html>