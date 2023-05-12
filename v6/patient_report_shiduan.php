<?php
// --------------------------------------------------------
// - 功能说明 : 回访统计报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-07-09
// --------------------------------------------------------
require "lib/set_env.php";

$daochu_user_arr = array("zhuwenya", "卓志华");

if ($hid <= 0) exit("请在右上角“切换医院”中选择医院查看。");

if ($_GET["btime"] == "") {
	$_GET["btime"] = date("Y-m-01");
}
if ($_GET["etime"] == "") {
	$_GET["etime"] = date("Y-m-d");
}

$t_begin = strtotime($_GET["btime"]);
$t_end = strtotime($_GET["etime"]." 23:59:59");


$shiduan_arr = array(
	"00-06" => array(0, 1, 2, 3, 4, 5),
	"06-09" => array(6, 7, 8),
	"09-12" => array(9, 10, 11),
	"12-14" => array(12, 13),
	"14-17" => array(14, 15, 16),
	"17-19" => array(17, 18),
	"19-21" => array(19, 20),
	"21-24" => array(21, 22, 23),
);


// 读取指定时间段的患者:
$p_list = $db->query("select name, tel, addtime, status from patient_{$hid} where part_id not in (4) and addtime>=$t_begin and addtime<=$t_end");
foreach ($p_list as $li) {
	$sd = intval(date("H", $li["addtime"]));
	$shiduan = wee_get_shiduan($sd);
	$yuyue_num[$shiduan] += 1;
	$yuyue_detail[$shiduan][] = $li["name"]."\t".$li["tel"];
	if ($li["status"] > 0) {
		$daozhen_num[$shiduan] += 1;
		$daozhen_detail[$shiduan][] = $li["name"]."\t".$li["tel"];
	}
}


function wee_get_shiduan($sd) {
	global $shiduan_arr;
	foreach ($shiduan_arr as $k => $v) {
		if (in_array($sd, $v)) {
			return $k;
		}
	}
	echo "wee_get_shiduan 错误参数: ".$sd."<br>";
}


// 生成导出链接:
function wee_daochu_link($type, $string) {
	global $username, $daochu_user_arr;
	if (in_array($username, $daochu_user_arr)) {
		return '<a href="javascript:;" onclick="daochu_data(\''.$type.'\', this)">'.$string.'</a>';
	} else {
		return $string;
	}
}


?>
<html>
<head>
<title>时段预约到诊统计</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
* {font-family:"微软雅黑"; }
.condition_set {text-align:center; margin-top:20px; }
.center_show {margin:0 auto; width:800px; text-align:center; }
.list {border:2px solid silver; }
.head {color:#bf0060 !important; border:1px solid silver !important; background:#e1e7ec !important; }
.item {text-align:center; padding:4px !important; border-top:1px solid silver !important; border-bottom:1px solid silver !important; }
.line_huizong td {color:red; }
.report_tips {padding:30px 0 20px 0; text-align:center; font-size:16px; font-family:"微软雅黑"; }
</style>
<script type="text/javascript">
function show_huizong() {
	var b = byid("begin_time").value;
	var e = byid("end_time").value;
	parent.load_src(1, '/v6/patient_report_shiduan_all.php?btime='+b+"&etime="+e, 900);
}
</script>
</head>

<body>

<div class="condition_set">
	<form method="GET" action="" onsubmit="" style="display:inline;">
		<input name="btime" id="begin_time" class="input" style="width:120px" value="<?php echo $_GET["btime"]; ?>" onclick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})"> ~ <input name="etime" id="end_time" class="input" style="width:120px" value="<?php echo $_GET["etime"]; ?>" onclick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})">　<input type="submit" class="button" value="确定">
	</form>
	<a href="javascript:;" onclick="show_huizong();" style="margin-left:40px;">显示本院汇总</a>
</div>

<div class="center_show">

	<div class="report_tips"><?php echo $hinfo["name"]; ?>　时段统计报表</div>

	<table class="list" width="100%">
		<tr>
			<th class="head">时间段</th>
			<th class="head">预约量</th>
			<th class="head">到诊量</th>
			<th class="head">预约到诊率</th>
		</tr>

<?php foreach ($shiduan_arr as $sd => $sd_def) { ?>
		<tr>
			<td class="item"><?php echo $sd; ?></td>
			<td class="item"><?php echo wee_daochu_link($sd."_1", $yuyue_num[$sd]); ?></td>
			<td class="item"><?php echo wee_daochu_link($sd."_2", $daozhen_num[$sd]); ?></td>
			<td class="item"><?php echo @round(100 * $daozhen_num[$sd] / $yuyue_num[$sd], 1)."%"; ?></td>
		</tr>
<?php } ?>

		<tr class="line_huizong">
			<td class="item">汇总</td>
			<td class="item"><?php echo @array_sum($yuyue_num); ?></td>
			<td class="item"><?php echo @array_sum($daozhen_num); ?></td>
			<td class="item"><?php echo @round(100 * array_sum($daozhen_num) / array_sum($yuyue_num), 1)."%"; ?></td>
		</tr>

	</table>

	<br>
	<br>

<?php if (in_array($username, $daochu_user_arr)) { ?>
	<!-- 仅有权限者显示此部分 -->

<?php foreach ($yuyue_detail as $sd => $arr) { ?>
	<input type="hidden" id="sd_<?php echo $sd; ?>_1" value="<?php echo implode("#", $arr); ?>">
<?php } ?>

<?php foreach ($daozhen_detail as $sd => $arr) { ?>
	<input type="hidden" id="sd_<?php echo $sd; ?>_2" value="<?php echo implode("#", $arr); ?>">
<?php } ?>

	<script type="text/javascript">
	function daochu_data(type, obj) {
		byid("detail_show").value = wee_relace(byid("sd_"+type).value);
		byid("detail_show").style.display = "block";
		//alert("结果已经显示于下方文本框内，请复制即可。");
	}

	function wee_relace(str) {
		return str.replace(new RegExp("#","gm"), "\n");
	}
	</script>

	<textarea id="detail_show" style="width:100%; height:200px; display:none;"></textarea>

<?php } ?>

</div>

<br>
<br>

</body>
</html>