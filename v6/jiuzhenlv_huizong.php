<?php
/*
// 说明: 就诊率汇总数据
// 作者: 幽兰 (weelia@126.com)
// 时间: 2017-03-13
*/
require "lib/set_env.php";

if ($hid <= 0) exit("请先选择医院");

$sname = $db->query("select * from hospital where id=$hid limit 1", 1, "sname");

$all_hid_name = $db->query("select * from hospital where sname='$sname' and ishide=0 order by name asc", "id", "depart");

$select_ids = $_GET["do"] == "select" ? $_GET["hids"] : array_keys($all_hid_name);

foreach ($all_hid_name as $hid => $hname) {
	if (in_array($hid, $select_ids)) {
		$data = $db->query("select data from index_cache where hid=$hid limit 1", 1, "data");
		$module_data_arr = @unserialize($data);

		$arr["网挂预约就诊率"]["a"] += $module_data_arr["ID_22"]["实到"]["本月"];
		$arr["网挂预约就诊率"]["b"] += $module_data_arr["ID_22"]["预到"]["本月"];

		$arr["网查预约就诊率"]["a"] += $module_data_arr["ID_20"]["实到"]["本月"];
		$arr["网查预约就诊率"]["b"] += $module_data_arr["ID_20"]["预到"]["本月"];

		$arr["网络无线预约就诊率"]["a"] += $module_data_arr["ID_8"]["实到"]["本月"];
		$arr["网络无线预约就诊率"]["b"] += $module_data_arr["ID_8"]["预到"]["本月"];

		$arr["电话无线预约就诊率"]["a"] += $module_data_arr["ID_23"]["实到"]["本月"];
		$arr["电话无线预约就诊率"]["b"] += $module_data_arr["ID_23"]["预到"]["本月"];
	}
}


?>
<html>
<head>
<title><?php echo $sname; ?> 就诊率汇总</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.new_body {padding:20px 40px; }
.main_title {font-weight:bold; font-size:14px; font-family:"微软雅黑"; }
.head {padding:6px 3px !important;}
.huizong {padding:4px; text-align:center; background-color:#e4e9eb; }
</style>
</head>

<script type="text/javascript">
function set_zero() {
	var g = byid("from1").getElementsByTagName("INPUT");
	for (var i=0; i<g.length; i++) {
		if (g[i].type == "checkbox") g[i].checked = false;
	}
}
function set_all() {
	var g = byid("from1").getElementsByTagName("INPUT");
	for (var i=0; i<g.length; i++) {
		if (g[i].type == "checkbox") g[i].checked = true;
	}
	byid("from1").submit();
}
function set_reverse() {
	var g = byid("from1").getElementsByTagName("INPUT");
	for (var i=0; i<g.length; i++) {
		if (g[i].type == "checkbox") g[i].checked = !g[i].checked;
	}
	byid("from1").submit();
}
</script>

<body class="new_body">


<form method="GET" action="" onsubmit="" id="from1">
	<b>请勾选要汇总的科室：</b>
<?php
	foreach ($all_hid_name as $hid => $hname) {
		$is_check = in_array($hid, $select_ids) ? ' checked' : "";
		$style = $is_check ? ' style="color:red; font-weight:bold;"' : "";
		echo '<input type="checkbox" name="hids[]" onclick="this.form.submit()" value="'.$hid.'" '.$is_check.' id="h_'.$hid.'"><label for="h_'.$hid.'" '.$style.'>'.$hname.'</label> ';
	}
?>
	　　<a href="javascript:;" onclick="set_zero();">[清空重选]</a>　<a href="javascript:;" onclick="set_reverse();">[反向选中]</a>　<a href="javascript:;" onclick="set_all();">[全部选中]</a>
	<input type="hidden" name="do" value="select">
</form>


<br>
<br>
所选<b><?php echo count($select_ids); ?></b>个科室的就诊率：<br>
<br>
网挂预约就诊率 = <b><?php echo @round(100 * $arr["网挂预约就诊率"]["a"] / $arr["网挂预约就诊率"]["b"], 1)."%"; ?></b><br>
网查预约就诊率 = <b><?php echo @round(100 * $arr["网查预约就诊率"]["a"] / $arr["网查预约就诊率"]["b"], 1)."%"; ?></b><br>
网络无线预约就诊率 = <b><?php echo @round(100 * $arr["网络无线预约就诊率"]["a"] / $arr["网络无线预约就诊率"]["b"], 1)."%"; ?></b><br>
电话无线预约就诊率 = <b><?php echo @round(100 * $arr["电话无线预约就诊率"]["a"] / $arr["电话无线预约就诊率"]["b"], 1)."%"; ?></b><br>

<br>
<br>
<br>

</body>
</html>
