<?php
// --------------------------------------------------------
// - ����˵�� : �ط�����ͳ��
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-12-02
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_huifang";
include "count_huifang.inc.php";

if ($_GET["btime"] == "") $_GET["btime"] = date("Y-m-d", strtotime("-1 days"));
//if ($_GET["etime"] == "") $_GET["etime"] = date("Y-m-d");


$int_btime = date("Ymd", strtotime($_GET["btime"]));
$int_tb = strtotime($_GET["btime"]);
if ($_GET["etime"] != "") {
	$int_etime = date("Ymd", strtotime($_GET["etime"]));
	$int_te = strtotime($_GET["etime"]." 23:59:59");
} else {
	$int_etime = $int_btime;
	$int_te = strtotime($_GET["btime"]." 23:59:59");
}

$x1 = $x2 = $x3 = $x4 = $x5 = $x6 = $x7 = $x8 = $x9 = $x10 = array();
$list_data = $db->query("select hid, sum(x1) as x1, sum(x2) as x2, sum(x3) as x3, sum(x4) as x4, sum(x5) as x5, sum(x6) as x6, sum(x7) as x7, sum(x8) as x8, sum(x9) as x9  from $table where hid in ($all_hid_str) and date>=$int_btime and date<=$int_etime group by hid", "hid");
foreach ($list_data as $hid => $li) {
	$x1[$hid] = $li["x1"];
	$x2[$hid] = $li["x2"];
	$x3[$hid] = $li["x3"];
	$x4[$hid] = $li["x4"];
	$x5[$hid] = $li["x5"];
	$x6[$hid] = $li["x6"];
	$x7[$hid] = $li["x7"];
	$x8[$hid] = $li["x8"];
	$x9[$hid] = $li["x9"];
}

?>
<html>
<head>
<title>�ط�����ͳ��</title>
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
</head>

<body class="new_body">
<table width="100%" style="margin-top:0px;">
	<tr>
		<td width="30%" align="left"></td>
		<td width="40%" align="center"></td>
		<td width="30%" align="right"></td>
	</tr>
</table>

<form action="?" method="GET">
	<div class="main_title">
		<span class="title_font">�ط�����ͳ��</span>�����鿴��Χ��<input name="btime" id="btime" class="input" style="width:80px" value="<?php echo $_GET["btime"]; ?>" onclick="picker({el:'btime',dateFmt:'yyyy-MM-dd'})" onchange="this.form.submit();"> ~ <input name="etime" id="etime" class="input" style="width:80px" value="<?php echo $_GET["etime"]; ?>" onclick="picker({el:'etime',dateFmt:'yyyy-MM-dd'})" onchange="this.form.submit();">
		<span>����</span><a href="count_huifang_kf.php">�ͷ����ݶԱ�</a>
	</div>
</form>

<?php $per = round(85 / 11, 3)."%"; ?>

<table width="100%" align="center" class="report_table" style="margin-top:10px;">
	<tr>
		<td class="head" align="left">ҽԺ����</td>
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
		<td class="head" align="center">��ϸ</td>
	</tr>

<?php foreach ($yiyuan_arr as $hid => $hname) { ?>
	<tr>
		<td class="item" align="left"><nobr><?php echo $hname; ?></nobr></td>
		<td class="item" align="center"><?php echo $x1[$hid]; ?></td>
		<td class="item" align="center"><?php echo $x2[$hid]; ?></td>
		<td class="item" align="center"><?php echo $x3[$hid]; ?></td>
		<td class="item" align="center"><?php echo $x4[$hid]; ?></td>
		<td class="item" align="center"><?php echo $x5[$hid]; ?></td>
		<td class="item" align="center"><?php echo $x6[$hid]; ?></td>
		<td class="item" align="center"><?php echo $x7[$hid]; ?></td>
		<td class="item" align="center"><?php echo $x8[$hid]; ?></td>
		<td class="item" align="center"><?php echo $x9[$hid]; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $x6[$hid] / $x1[$hid], 1)."%"; ?></td>
		<td class="item" align="center"><?php echo @round(100 * $x4[$hid] / $x1[$hid], 1)."%"; ?></td>
		<td class="item" align="center"><nobr><a href="count_huifang_detail.php?hid=<?php echo $hid; ?>">������ϸ</a></nobr></td>
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
		<td class="item" align="center">--</td>
	</tr>
</table>


<br>
<br>
<br>

</body>
</html>