<?php
// --------------------------------------------------------
// - 功能说明 : 资料库报表 - 客服明细表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-07-25
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
include "ku_report.config.php";
$table = "ku_list";

$kf = $_GET["js_kf"] ? mb_convert_encoding($_GET["js_kf"], "gbk", "UTF-8") : $_GET["kf"];
if ($kf == '') exit("参数错误。");


if ($_GET["btime"] == "") $_GET["btime"] = date("Y-m-01");
if ($_GET["etime"] == "") $_GET["etime"] = date("Y-m-d");

$t_begin = strtotime($_GET["btime"]);
$t_end = strtotime($_GET["etime"]." 23:59:59");
$int_tb = date("Ymd", $t_begin);
$int_te = date("Ymd", $t_end);

$date_arr = array();
$cur_t = $t_begin;
while ($cur_t < $t_end) {
	$date_arr[] = date("Y-m-d", $cur_t);
	$cur_t = strtotime("+1 day", $cur_t);
}


// 查询目标数据-----------
$data_arr = $db->query("select qq, weixin, hf_log, is_yuyue, is_come, addtime from $table where hid in ($hid) and addtime>=$t_begin and addtime<=$t_end and u_name='$kf'");

// 分析过程
$data_add = $data_wx = $data_qq = $data_dh = $data_dx = $data_yuyue = $data_daozhen = array();
foreach ($data_arr as $li) {
	$day = date("Y-m-d", $li["addtime"]);
	$data_add[$day] ++;

	if ($li["qq"] != '') {
		$data_add_qq[$day]++;
	}

	if ($li["weixin"] != "") {
		$data_add_weixin[$day]++;
	}

	if ($li["is_yuyue"]) {
		$data_yuyue[$day] ++;
	}
	if ($li["is_come"]) {
		$data_daozhen[$day] ++;
	}
}

$data_dh = $db->query("select from_unixtime(addtime, '%Y-%m-%d') as t, count(from_unixtime(addtime, '%Y-%m-%d')) as c from ku_huifang where hid in ($hid) and addtime>=$t_begin and addtime<=$t_end and author='$kf' and qudao='电话' group by t", "t", "c");

$data_wx = $db->query("select from_unixtime(addtime, '%Y-%m-%d') as t, count(from_unixtime(addtime, '%Y-%m-%d')) as c from ku_huifang where hid in ($hid) and addtime>=$t_begin and addtime<=$t_end and author='$kf' and qudao='微信' group by t", "t", "c");

$data_qq = $db->query("select from_unixtime(addtime, '%Y-%m-%d') as t, count(from_unixtime(addtime, '%Y-%m-%d')) as c from ku_huifang where hid in ($hid) and addtime>=$t_begin and addtime<=$t_end and author='$kf' and qudao='QQ' group by t", "t", "c");

$data_dx = $db->query("select from_unixtime(addtime, '%Y-%m-%d') as t, count(from_unixtime(addtime, '%Y-%m-%d')) as c from ku_huifang where hid in ($hid) and addtime>=$t_begin and addtime<=$t_end and author='$kf' and qudao='短信' group by t", "t", "c");

?>
<html>
<head>
<title>资料库报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>

<style>
* {font-family:"微软雅黑","Tahoma" !important; font-size:12px;  }
body {overflow-x:auto !important;}
form {display:inline; }

.condition_set {text-align:center; margin-top:20px; }
.column_sortable {cursor:pointer; color:blue; padding:8px 3px 6px 3px !important; }
.report_tips {margin-top:30px; text-align:center; font-size:16px; font-family:"微软雅黑"; }
.center_show {margin:0 auto; width:90%; text-align:center; }

.excel_table {border:2px solid #c8c8c8; margin-top:20px; }
.excel_table td {padding:5px 10px 3px 5px; border:1px solid #d3d3d3; text-align:center; }
.excel_head td {background:#dce4e9; color:#ff8040; padding:5px 10px 3px 5px; border-bottom:1px solid #d3d3d3; text-align:center; }
.excel_index {background:#f3f3f3; border-right:1px solid #d3d3d3; text-align:center; padding-left:20px !important; padding-right:20px !important; }
.content_left {border-left:1px solid #efefef; }
.huizong td {color:red; }

.kf {color:#0080c0; }
.kf:hover {color:red; }
</style>

</head>

<body style="padding:10px 20px;">


<div class="center_show">
	<div class="report_tips"><?php echo $kf; ?> 跟踪情况表</div>

	<table class="excel_table" width="100%">
		<tr class="excel_head">
			<td><nobr>日期</nobr></td>
			<td><nobr>增加人数</nobr></td>
			<td><nobr>微信增加人数</nobr></td>
			<td><nobr>QQ增加人数</nobr></td>
			<td><nobr>电话跟踪次数</nobr></td>
			<td><nobr>微信跟踪次数</nobr></td>
			<td><nobr>QQ跟踪次数</nobr></td>
			<td><nobr>短信跟踪次数</nobr></td>
			<td><nobr>转预约人数</nobr></td>
			<td><nobr>到诊人数</nobr></td>
		</tr>

<?php
foreach ($date_arr as $day) {
?>
		<tr class="excel_item" onmouseover="mi(this)" onmouseout="mo(this)">
			<td><nobr><?php echo $day; ?></nobr></td>
			<td><nobr><?php echo $data_add[$day]; ?></nobr></td>
			<td><nobr><?php echo $data_add_weixin[$day]; ?></nobr></td>
			<td><nobr><?php echo $data_add_qq[$day]; ?></nobr></td>
			<td><nobr><?php echo $data_dh[$day]; ?></nobr></td>
			<td><nobr><?php echo $data_wx[$day]; ?></nobr></td>
			<td><nobr><?php echo $data_qq[$day]; ?></nobr></td>
			<td><nobr><?php echo $data_dx[$day]; ?></nobr></td>
			<td><nobr><?php echo $data_yuyue[$day]; ?></nobr></td>
			<td><nobr><?php echo $data_daozhen[$day]; ?></nobr></td>
		</tr>
<?php
}
?>

		<tr class="excel_item huizong" onmouseover="mi(this)" onmouseout="mo(this)">
			<td><nobr>汇总</nobr></td>
			<td><nobr><?php echo array_sum($data_add); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_add_weixin); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_add_qq); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_dh); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_wx); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_qq); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_dx); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_yuyue); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_daozhen); ?></nobr></td>
		</tr>

	</table>
</div>

<br>

</body>
</html>