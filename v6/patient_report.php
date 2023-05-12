<?php
// --------------------------------------------------------
// - ����˵�� : ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-25 15:45
// --------------------------------------------------------
require "lib/set_env.php";
check_power('', $pinfo) or exit("û�д�Ȩ��...");
$hid = $user_hospital_id;

if ($hid == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

$table = "patient_".$hid;

// ҽԺ����:
$h_name = $db->query("select name from hospital where id=$hid limit 1", "1", "name");

// ʱ�䶨��:
$today_begin = mktime(0,0,0); //���쿪ʼ
$today_end = $today_begin + 24*3600 - 1; //�������
$yesterday_begin = $today_begin - 24*3600; //���쿪ʼ
$yesterday_end = $today_begin - 1; //�������
$thismonth_begin = mktime(0,0,0,date("m"),1); //���¿�ʼ
$thismonth_end = strtotime("+1 month", $thismonth_begin) - 1; //���¿�ʼ
$lastmonth_begin = strtotime("-1 month", $thismonth_begin); //���¿�ʼ
$lastmonth_end = $thismonth_begin - 1; //���¿�ʼ


$date_array = array(
	"����" => array($today_begin, $today_end),
	"����" => array($yesterday_begin, $yesterday_end),
	"����" => array($thismonth_begin, $thismonth_end),
	"����" => array($lastmonth_begin, $lastmonth_end),
);

$tf = "order_date";

$kefu = array();
// ��������ͷ�:
$kefu[2] = $db->query("select distinct author from $table where part_id=2 and $tf>=$lastmonth_begin and $tf<=$thismonth_end order by author", "", "author");

// ���е绰�ͷ�:
$kefu[3] = $db->query("select distinct author from $table where part_id=3 and $tf>=$lastmonth_begin and $tf<=$thismonth_end order by author", "", "author");

$data = array();
foreach ($kefu as $ptid => $kfs) {
	foreach ($kfs as $kf) {
		foreach ($date_array as $tname => $t) {
			$b = $t[0];
			$e = $t[1];

			// Ԥ���ܵ�Ժ:
			$data[$ptid][$kf][$tname]["all"] = $d1 = $db->query("select count(*) as c from $table where part_id=$ptid and author='$kf' and $tf>=$b and $tf<=$e", 1, "c");
			// �ѵ�:
			$data[$ptid][$kf][$tname]["come"] = $d2 = $db->query("select count(*) as c from $table where part_id=$ptid and author='$kf' and $tf>=$b and $tf<=$e and status=1", 1, "c");
			// δ��:
			$data[$ptid][$kf][$tname]["leave"] = $d1 - $d2;
		}
	}
}


?>
<html>
<head>
<title>���ݱ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.red {color:#bf0060 !important; }

.report_tips {padding:20px 0 20px 0; text-align:center; font-size:16px; font-family:"΢���ź�"; }

.list {border:2px solid silver !important; }
.head {border:0 !important; background:#e1e7ec !important; }
.item {border-top:1px solid #e2e6e7 !important; border-bottom:1px solid #e2e6e7 !important; text-align:center; padding:8px 3px !important; }

.hl {border-left:1px solid silver !important; }
.hr {border-right:1px solid silver !important; }
.ht {border-top:1px solid silver !important; }
.hb {border-bottom:1px solid silver !important; }

.huizong {font-weight:bold; color:#ff8040; background:#eff2f5 !important; padding:5px; text-align:center; }
</style>
</head>

<body>

<?php
if ($debug_mode || in_array("web", $user_data_power)) {
?>

<div class="report_tips"><?php echo $h_name; ?> ������ѯԤԼ���</div>

<table class="list" width="100%">
	<tr>
		<th class="head hb"></th>
		<th class="head hl hb red" colspan="3">����</th>
		<th class="head hl hb red" colspan="3">����</th>
		<th class="head hl hb red" colspan="3">����</th>
		<th class="head hl hb red" colspan="3">����</th>
	</tr>

	<tr>
		<th class="head hb">�ͷ�</th>

		<th class="head hl hb">�ܹ�</th>
		<th class="head hb">�ѵ�</th>
		<th class="head hb">δ��</th>

		<th class="head hl hb">�ܹ�</th>
		<th class="head hb">�ѵ�</th>
		<th class="head hb">δ��</th>

		<th class="head hl hb">�ܹ�</th>
		<th class="head hb">�ѵ�</th>
		<th class="head hb">δ��</th>

		<th class="head hl hb">�ܹ�</th>
		<th class="head hb">�ѵ�</th>
		<th class="head hb">δ��</th>
	</tr>

<?php foreach ($data[2] as $kf => $arr) { ?>

	<tr>
		<td class="item"><?php echo $kf; ?></td>

		<td class="item hl"><?php echo $arr["����"]["all"]; ?></td>
		<td class="item"><?php echo $arr["����"]["come"]; ?></td>
		<td class="item"><?php echo $arr["����"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["����"]["all"]; ?></td>
		<td class="item"><?php echo $arr["����"]["come"]; ?></td>
		<td class="item"><?php echo $arr["����"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["����"]["all"]; ?></td>
		<td class="item"><?php echo $arr["����"]["come"]; ?></td>
		<td class="item"><?php echo $arr["����"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["����"]["all"]; ?></td>
		<td class="item"><?php echo $arr["����"]["come"]; ?></td>
		<td class="item"><?php echo $arr["����"]["leave"]; ?></td>
	</tr>

<?php } ?>

</table>

<?php } ?>


<!-- �绰�� -->

<?php
if ($debug_mode || in_array("tel", $user_data_power)) {
?>
<div class="report_tips" style="margin-top:20px;"><?php echo $h_name; ?> �绰��ѯԤԼ���</div>

<table class="list" width="100%">
	<tr>
		<th class="head hb"></th>
		<th class="head hl hb red" colspan="3">����</th>
		<th class="head hl hb red" colspan="3">����</th>
		<th class="head hl hb red" colspan="3">����</th>
		<th class="head hl hb red" colspan="3">����</th>
	</tr>

	<tr>
		<th class="head hb">�ͷ�</th>

		<th class="head hl hb">�ܹ�</th>
		<th class="head hb">�ѵ�</th>
		<th class="head hb">δ��</th>

		<th class="head hl hb">�ܹ�</th>
		<th class="head hb">�ѵ�</th>
		<th class="head hb">δ��</th>

		<th class="head hl hb">�ܹ�</th>
		<th class="head hb">�ѵ�</th>
		<th class="head hb">δ��</th>

		<th class="head hl hb">�ܹ�</th>
		<th class="head hb">�ѵ�</th>
		<th class="head hb">δ��</th>
	</tr>

<?php foreach ($data[3] as $kf => $arr) { ?>

	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td class="item"><?php echo $kf; ?></td>

		<td class="item hl"><?php echo $arr["����"]["all"]; ?></td>
		<td class="item"><?php echo $arr["����"]["come"]; ?></td>
		<td class="item"><?php echo $arr["����"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["����"]["all"]; ?></td>
		<td class="item"><?php echo $arr["����"]["come"]; ?></td>
		<td class="item"><?php echo $arr["����"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["����"]["all"]; ?></td>
		<td class="item"><?php echo $arr["����"]["come"]; ?></td>
		<td class="item"><?php echo $arr["����"]["leave"]; ?></td>

		<td class="item hl"><?php echo $arr["����"]["all"]; ?></td>
		<td class="item"><?php echo $arr["����"]["come"]; ?></td>
		<td class="item"><?php echo $arr["����"]["leave"]; ?></td>
	</tr>

<?php } ?>

</table>

<?php } ?>

<br>
<br>
<center>��ע���������ݣ��ɻ���ԤԼ��Ժʱ�����ͳ�ƣ������������ʱ�䣩<br></center>
<br>

</body>
</html>