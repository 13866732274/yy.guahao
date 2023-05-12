<?php
// --------------------------------------------------------
// - ����˵�� : �鿴������ϸ
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-12-02
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_huifang";
include "count_huifang.inc.php";


if ($_GET["btime"] == "") $_GET["btime"] = date("Y-m-01");
if ($_GET["etime"] == "") $_GET["etime"] = date("Y-m-d");

$hid = intval($_GET["hid"]);
if ($hid == 0) {
	$hid = array_shift(array_keys($yiyuan_arr));
}
if (!array_key_exists($hid, $yiyuan_arr)) {
	exit("��������");
}

$hid_set = $db->query("select * from count_huifang_set where hid=$hid limit 1", 1);
$kefu_names = explode(",", $hid_set["kefu_names"]);


$int_btime = date("Ymd", strtotime($_GET["btime"]));
$int_tb = strtotime($_GET["btime"]);
if ($_GET["etime"] != "") {
	$int_etime = date("Ymd", strtotime($_GET["etime"]));
	$int_te = strtotime($_GET["etime"]." 23:59:59");
} else {
	$int_etime = $int_btime;
	$int_te = strtotime($_GET["btime"]." 23:59:59");
}

$sql_add = "";
if ($_GET["kefu"] != "") {
	$sql_add .= " and kefu='".$_GET["kefu"]."'";
}

$x1 = $x2 = $x3 = $x4 = $x5 = $x6 = $x7 = $x8 = $x9 = $x10 = array();
$list_data = $db->query("select date, sum(x1) as x1, sum(x2) as x2, sum(x3) as x3, sum(x4) as x4, sum(x5) as x5, sum(x6) as x6, sum(x7) as x7, sum(x8) as x8, sum(x9) as x9 from $table where hid in ($hid) and date>=$int_btime and date<=$int_etime $sql_add group by date", "date");
foreach ($list_data as $date => $li) {
	$x1[$date] = $li["x1"];
	$x2[$date] = $li["x2"];
	$x3[$date] = $li["x3"];
	$x4[$date] = $li["x4"];
	$x5[$date] = $li["x5"];
	$x6[$date] = $li["x6"];
	$x7[$date] = $li["x7"];
	$x8[$date] = $li["x8"];
	$x9[$date] = $li["x9"];
}


$dates = array_keys($x1);
sort($dates);
$days_count = count($dates);

?>
<html>
<head>
<title>��ϸ����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.new_body {padding:10px; }
.main_title {margin:0 auto; padding:10px; text-align:center;  }
.title_font {font-weight:bold; font-size:15px; }
.report_table .item {border:1px solid silver; }
</style>

<script type="text/javascript">
function set_kefu(hid) {
	parent.load_src(1, "count_huifang_set.php?hid="+hid, 900, 500);
}
function set_data(hid) {
	parent.load_src(1, "count_huifang_edit.php?hid="+hid, 600, 500);
}
</script>

</head>

<body class="new_body">

<form action="?" method="GET">
	<select name="hid" class="combo" onchange="this.form.submit();">
		<?php echo list_option($yiyuan_arr, "_key_", "_value_", $hid); ?>
	</select>
	<?php if ($hid > 0 && $is_modify_data) { ?>��<a href="javascript:;" onclick="set_kefu(<?php echo $hid; ?>)">���ÿͷ�</a>��<a href="javascript:;" onclick="set_data(<?php echo $hid; ?>)">����ά��</a><?php } ?>
	<span>�������ڷ�Χ��</span><input name="btime" id="btime" class="input" style="width:80px" value="<?php echo $_GET["btime"]; ?>" onclick="picker({el:'btime',dateFmt:'yyyy-MM-dd'})" onchange="this.form.submit();"> ~ <input name="etime" id="etime" class="input" style="width:80px" value="<?php echo $_GET["etime"]; ?>" onclick="picker({el:'etime',dateFmt:'yyyy-MM-dd'})" onchange="this.form.submit();">
	<span>��������ͷ���</span><select name="kefu" class="combo" onchange="this.form.submit();"><option value="">����</option><?php echo list_option($kefu_names, "_value_", "_value_", $_GET["kefu"]); ?></select>
	<span>����</span><a href="count_huifang.php">���ػ���ҳ��</a>
</form>

<?php $per = round(90 / 11, 3)."%"; ?>

<table width="100%" align="center" class="report_table" style="margin-top:10px;">
	<tr>
		<td class="head" align="left">����</td>
		<td class="head" width="<?php echo $per; ?>" align="center">�ط�����</td>
		<td class="head" width="<?php echo $per; ?>" align="center">��Լ����</td>
		<td class="head" width="<?php echo $per; ?>" align="center">���˽���</td>
		<td class="head" width="<?php echo $per; ?>" align="center">��Լ����</td>
		<td class="head" width="<?php echo $per; ?>" align="center">�绰����</td>
		<td class="head" width="<?php echo $per; ?>" align="center">�������</td>
		<td class="head" width="<?php echo $per; ?>" align="center">��������</td>
		<td class="head" width="<?php echo $per; ?>" align="center">΢�ž���</td>
		<td class="head" width="<?php echo $per; ?>" align="center">����ϼ�</td>
		<td class="head" width="<?php echo $per; ?>" align="center">���������</td>
		<td class="head" width="<?php echo $per; ?>" align="center">�طþ�����</td>
	</tr>

<?php foreach ($dates as $date) { ?>
	<tr>
		<td class="item" align="left"><nobr><?php echo int_date_to_date($date); ?></nobr></td>
		<td class="item" align="center"><?php echo $x1[$date]; ?></td>
		<td class="item" align="center"><?php echo $x2[$date]; ?></td>
		<td class="item" align="center"><?php echo $x3[$date]; ?></td>
		<td class="item" align="center"><?php echo $x4[$date]; ?></td>
		<td class="item" align="center"><?php echo $x5[$date]; ?></td>
		<td class="item" align="center"><?php echo $x6[$date]; ?></td>
		<td class="item" align="center"><?php echo $x7[$date]; ?></td>
		<td class="item" align="center"><?php echo $x8[$date]; ?></td>
		<td class="item" align="center"><?php echo $x9[$date]; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $x6[$date] / $x1[$date], 1)."%"; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $x4[$date] / $x1[$date], 1)."%"; ?></td>
	</td>
<?php } ?>

	<tr class="line_huizong">
		<td class="item" align="left">����</td>
		<td class="item" align="center"><?php echo @array_sum($x1); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x2); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x3); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x4); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x5); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x6); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x7); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x8); ?></td>
		<td class="item" align="center"><?php echo @array_sum($x9); ?></td>
		<td class="item" align="center"><?php echo @round(100 * array_sum($x6) / array_sum($x1), 1)."%"; ?></td>
		<td class="item" align="center"><?php echo @round(100 * array_sum($x4) / array_sum($x1), 1)."%"; ?></td>
	</tr>

	<tr class="line_huizong">
		<td class="item" align="left">�վ� (<?php echo $days_count; ?>��)</td>
		<td class="item" align="center"><?php echo @round(@array_sum($x1) / $days_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x2) / $days_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x3) / $days_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x4) / $days_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x5) / $days_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x6) / $days_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x7) / $days_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x8) / $days_count); ?></td>
		<td class="item" align="center"><?php echo @round(@array_sum($x9) / $days_count); ?></td>
		<td class="item" align="center"><?php echo @round(100 * array_sum($x6) / array_sum($x1), 1)."%"; ?></td>
		<td class="item" align="center"><?php echo @round(100 * array_sum($x4) / array_sum($x1), 1)."%"; ?></td>
	</tr>

</table>


<br>
<br>
<br>

</body>
</html>