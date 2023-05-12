<?php
/* --------------------------------------------------------
// ˵��: ͼ�α���
// ����: ���� (weelia@126.com)
// ʱ��: 2009-06-25 14:01
// ----------------------------------------------------- */
require "lib/set_env.php";
include "chart/FusionCharts_Gen.php";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

if ($user_hospital_id == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

$table = "patient_".$user_hospital_id;

// ҽԺ����:
$h_name = $db->query("select name from hospital where id=$user_hospital_id limit 1", "1", "name");

// ��һ��������ͳ��:
$FC = new FusionCharts("Column2D","800","200");
$FC->setSWFPath("chart/");
$FC->setChartParams("decimalPrecision=0; formatNumberScale=1; baseFontSize=10; baseFont=Arial; chartBottomMargin=0; outCnvBaseFontSize=12; hoverCapSepChar=��: " );

// ͳ��:
$timebegin = $_GET["month"] ? $_GET["month"] : mktime(0,0,0,date("m"),1);
$timeend = strtotime("+1 month", $timebegin);
$list_1 = $db->query("select id,addtime,status from $table where addtime>$timebegin and addtime<$timeend");

$a2 = array();
foreach ($list_1 as $li) {
	$a2[date("j", $li["addtime"])] += 1;
}

$ymax = 10 * ceil((@max($a2) + 10) / 10);
$FC->setChartParams("yAxisMaxValue={$ymax}");

for ($i=1; $i<=31; $i++) {
	//$FC->aa($a2[$i]);
	$FC->addChartData($a2[$i], "name=".$i);
}





// �������:
$FC2 = new FusionCharts("Line","800","186");
$FC2->setSWFPath("chart/");
$FC2->setChartParams("decimalPrecision=0; formatNumberScale=1; baseFontSize=10; baseFont=Arial; chartBottomMargin=0; outCnvBaseFontSize=12; shownames=0; showValues=0; hoverCapSepChar=: ; chartBottomMargin=10; ");


$time = time();
$tb = strtotime("-3 month");
$list_3 = $db->query("select id,addtime,status from $table where addtime>$tb and addtime<=$time");
$a3_all = $a3_come = array();
foreach ($list_3 as $li) {
	$a3_all[date("y-n-j", $li["addtime"])] += 1;
	if ($li["status"] == 1) {
		$a3_come[date("y-n-j", $li["addtime"])] += 1;
	}
}

$ymax = 10 * ceil((@max($a3_all) + 5) / 10);
$FC2->setChartParams("yAxisMaxValue={$ymax}");


foreach ($a3_all as $d => $s) {
	$FC2->addChartData($s, "name=".date("n��j��", strtotime($d)));
}

$title = $h_name.' - ����ԤԼ��������ͼ';


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
<style>
.w400 {width:400px }
.w800 {width:800px; }
.hr {border:0; margin:0; padding:0; height:3px; line-height:0; font-size:0; background-color:red; color:white; border-top:1px solid silver; }
</style>
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

<div style="text-align:center; ">

<div style="margin-left:20px;">
�·ݣ�
<?php
for ($i=0; $i<=6; $i++) {
	$date = mktime(0,0,0,date("m")-$i,1);
?>
	<a href="?month=<?php echo $date; ?>"><?php echo date("Y-m", $date); ?></a>&nbsp;
<?php
}
?>
</div>


<?php $FC->renderChart(); ?>
<center><?php echo "<b>".date("Y��n��", $timebegin)." ԤԼ����</b> (x��Ϊ���ڣ�y����ԤԼ����)"; ?></center>

<br>

<?php $FC2->renderChart(); ?>
<center><?php echo "<b>���3����ԤԼ����</b> (x��Ϊ���ڣ�y����ԤԼ����)"; ?></center>

</div>

</body>
</html>