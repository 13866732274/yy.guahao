<?php
/* --------------------------------------------------------
// ˵��: ͼ�α���
// ����: ���� (weelia@126.com)
// ʱ��: 2010-01-14 13:07
// ----------------------------------------------------- */
require "lib/set_env.php";
include "chart/FusionCharts_Gen.php";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

if ($user_hospital_id == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}


$table = "patient_".$user_hospital_id;
$hospital_name = $db->query("select name from hospital where id='$user_hospital_id' limit 1",1,"name");

// ��ѯѡ��:
$type_arr = array("month"=>"һ���ڵ�ÿ����", "day"=>"һ���е�ÿ��", "hour"=>"һ���ڵ�ʱ��");
for ($i = date("Y"); $i >= 2008; $i--) {
	$year_arr[] = $i;
}
$month_arr = array(1,2,3,4,5,6,7,8,9,10,11,12);
$day_arr = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);

$time_arr = array("order_date"=>"��Ժʱ��", "addtime"=>"���ʱ��");
$come_arr = array(0 => "����", 1=>"�ѵ�", 2=>"δ��");

$index_module_arr = $db->query("select * from index_module where (hospital_id=0 or hospital_id=$hid) and isshow>0 and if_dingzhi=1 order by sort desc", "id","name");


if ($_GET) {
	extract($_GET);

	$index_module = intval($index_module);

	// ���� where:
	$where = array();

	if ($type == "month") {
		$tb = mktime(0,0,0,1,1,$year);
		$te = strtotime("+1 year", $tb);
		$format = "%Y%m";
		$caption = "��";
		$tips = $year."�� ���·�";
	} else if ($type == "day") {
		$tb = mktime(0,0,0,$month,1,$year);
		$te = strtotime("+1 month", $tb);
		$format = "%Y%m%d";
		$caption = "��";
		$tips = $year."��".$month."�� ����";
	} else if ($type == "hour") {
		$tb = mktime(0,0,0,$month,$day,$year);
		$te = strtotime("+1 day", $tb);
		$format = "%Y%m%d%H";
		$caption = "ʱ";
		$tips = $year."��".$month."��".$day."�� ��ʱ��";
	}

	// ͼ���·���ʾ:
	$tips .= ($time == "addtime" ? "ԤԼ" : "��Ժ")."����";
	$tips .= "(";
	$tips .= $come_arr[$come];
	$tips .= ")";


	$time_field = array_key_exists($time, $time_arr) ? $time : "addtime";
	if ($tb > 0) {
		$where[] = $time_field.">=".$tb;
	}
	if ($te > 0) {
		$where[] = $time_field."<".$te;
	}

	if ($index_module > 0) {
		$m_arr = $db->query("select * from index_module where id=$index_module limit 1", 1);
		$condi = $m_arr["condition_code"];
		if ($condi != '') {
			$where[] = "(".$condi.")";
		}
	} else {
		exit("��ѡ��ͳ�Ʒ���~");
	}

	if ($come > 0) {
		$where[] = $come == 1 ? "status=1" : "status=0";
	}

	// ��������:
	$sqlwhere = implode(" and ", $where);
	$list = $db->query("select t,count(t) as c from (select id,part_id,$time_field,status,from_unixtime($time_field,'$format') as t from $table where $sqlwhere) as tmp group by t", "t", "c");
	$sql_01 = $db->sql;


	// ͳ��ͼ:
	$FC = new FusionCharts("Column2D","750","200");
	$FC->setSWFPath("chart/");
	$FC->setChartParams("decimalPrecision=0; formatNumberScale=0; baseFontSize=10; baseFont=Arial; chartBottomMargin=0; outCnvBaseFontSize=12; hoverCapSepChar=$caption: " );

	$ymax = intval((@max($list) + 10) * 1.2);
	$FC->setChartParams("yAxisMaxValue={$ymax}");

	if ($type == "month") {
		for ($i=1; $i<=12; $i++) {
			$value = max(0, $list[$year.($i<10?"0":"").$i]);
			$FC->addChartData($value, "name=".$i);
		}
	} else if ($type == "day") {
		for ($i=1; $i<=31; $i++) {
			$value = max(0, $list[$year.($month<10?"0":"").$month.($i<10?"0":"").$i]);
			$FC->addChartData($value, "name=".$i);
		}
	} else if ($type == "hour") {
		for ($i=0; $i<24; $i++) {
			$value = max(0, $list[$year.($month<10?"0":"").$month.($day<10?"0":"").$day.($i<10?"0":"").$i]);
			$FC->addChartData($value, "name=".$i);
		}
	}
}





$title = '����ԤԼ��������ͼ';

function con($s) {
	$s = iconv("gbk", "utf-8", $s);
	return urlencode($s);
}
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src='chart/FusionCharts.js' language='javascript'></script>
<script type="text/javascript">
function hgo(dir) {
	var obj = byid("hospital_id");
	if (dir == "up") {
		if (obj.selectedIndex > 1) {
			obj.selectedIndex = obj.selectedIndex - 1;
			obj.onchange();
		} else {
			parent.msg_box("�Ѿ�������һ��ҽԺ��", 3);
		}
	}
	if (dir == "down") {
		if (obj.selectedIndex < obj.options.length-1) {
			obj.selectedIndex = obj.selectedIndex + 1;
			obj.onchange();
		} else {
			parent.msg_box("�Ѿ�������һ��ҽԺ��", 3);
		}
	}
}
function go_change(id) {
	var url = encodeURIComponent(self.location.href);
	self.location='?do=change&hospital_id='+id+'&go='+url;
}
</script>
<style>
.input {height:20px; }
.w400 {width:400px }
.w800 {width:800px; }
</style>
<script type="text/javascript">
function show_hide_date(v) {
	byid("s_month").style.display = "none";
	byid("s_day").style.display = "none";
	if (v == "day" || v == "hour") {
		byid("s_month").style.display = "inline";
	}
	if (v == "hour") {
		byid("s_day").style.display = "inline";
	}
}
</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips"><?php echo $title; ?></nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">ˢ��</button></td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<table width="100%"><tr><td align="center">

<div>
	<center>��ǰ���ң�<b><?php echo $hinfo["name"]; ?></b>����<a href="javascript:;" onclick="parent.load_src(1,'patient_report_hz.php');">��Ժ����</a></center>
</div>

<form action="" method="GET">
<div style="margin-top:40px; ">
	<b>������ʾ: </b>
	<select name="type" class="combo" onchange="show_hide_date(this.value)">
		<option value="" style="color:gray">-����-</option>
		<?php echo list_option($type_arr, "_key_", "_value_", noe($_GET["type"], "day")); ?>
	</select>
	<select name="time" class="combo">
		<option value="" style="color:gray">-ʱ������-</option>
		<?php echo list_option($time_arr, "_key_", "_value_", noe($_GET["time"], "order_date")); ?>
	</select>
	<select name="year" class="combo">
		<option value="" style="color:gray">-��-</option>
		<?php echo list_option($year_arr, "_value_", "_value_", noe($_GET["year"], date("Y"))); ?>
	</select>
	<select name="month" id="s_month" class="combo" style="display:none;">
		<option value="" style="color:gray">-��-</option>
		<?php echo list_option($month_arr, "_value_", "_value_", noe($_GET["month"], date("n"))); ?>
	</select>
	<select name="day" id="s_day" class="combo" style="display:none;">
		<option value="" style="color:gray">-��-</option>
		<?php echo list_option($day_arr, "_value_", "_value_", noe($_GET["day"], date("j"))); ?>
	</select>
	<select name="index_module" class="combo ml20">
		<!-- <option value="" style="color:gray">-��ѡ��ͳ�Ʒ���-</option> -->
		<?php echo list_option($index_module_arr, "_key_", "_value_", $_GET["index_module"]); ?>
	</select>
	<select name="come" class="combo">
		<!-- <option value="" style="color:gray">-�Ƿ�Ժ-</option> -->
		<?php echo list_option($come_arr, "_key_", "_value_", $_GET["come"]); ?>
	</select>
	<input type="submit" class="button" value="ִ��">
</div>
</form>

<script type="text/javascript">
show_hide_date('<?php echo noe($_GET["type"],"day"); ?>');
</script>

<br>
<br>

<?php if ($_GET) { ?>

	<?php $FC->renderChart(); ?>
	<div class="w800" style="text-align:center; margin-top:10px; "><?php echo "<b>".$hospital_name." ".$tips."</b>"; ?></div>

<?php } else { ?>

<div>�����ò�ѯ������,���ִ����ʾͼ�α���</div>

<?php } ?>

</td>
</tr>
</table>

<!-- <?php echo $sql_01; ?> -->


</body>
</html>