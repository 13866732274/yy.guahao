<?php
// --------------------------------------------------------
// - ����˵�� : ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-25 15:45
// --------------------------------------------------------
require "lib/set_env.php";
check_power('', $pinfo) or exit("û�д�Ȩ��...");

if ($user_hospital_id == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

$hospital_ids = array($user_hospital_id);

$title = '���ݱ���';
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.list td {text-align:center; }
.list .left {text-align:left; }
.list .line1 {background-color:#FFFFFF; }
.list .line1 .a {background:#FFFFFF; }
.list .line1 .b {}

.list .line2 {background-color:#F7FBFF; }
.list .line2 .a {background:#F0F8FF; }
.list .line2 .b {}
.list .line2 .c {background:#FFF5F0; }
.list .line2 .d {background:#FFF7FF; }
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
<?php
if ($_GET["do"] == "show") {
	$hid_name = $db->query("select id,name from hospital", "id", "name");
	foreach ($hospital_ids as $hid) {
		$htable = "patient_".$hid;
?>
<table width="100%" class="list">
	<tr>
		<td colspan="16" class="head left"><?php echo $hid_name[$hid]; ?></td>
	</tr>
	<tr>
		<td rowspan="2" class="head">����</td>
		<td colspan="3" class="head">����</td>
		<td colspan="3" class="head">����</td>
		<td colspan="3" class="head">����</td>
		<td colspan="3" class="head">����</td>
		<td colspan="3" class="head">ȫ��</td>
	</tr>
	<tr>
		<td class="head a">�ѵ�</td>
		<td class="head a">δ��</td>
		<td class="head a">�ܹ�</td>
		<td class="head b">�ѵ�</td>
		<td class="head b">δ��</td>
		<td class="head b">�ܹ�</td>
		<td class="head a">�ѵ�</td>
		<td class="head a">δ��</td>
		<td class="head a">�ܹ�</td>
		<td class="head b">�ѵ�</td>
		<td class="head b">δ��</td>
		<td class="head b">�ܹ�</td>
		<td class="head a">�ѵ�</td>
		<td class="head a">δ��</td>
		<td class="head a">�ܹ�</td>
	</tr>

<?php
// ʱ�䶨��:
$today_begin = mktime(0,0,0);
$today_end = $today_begin + 24*3600;
$yesterday_begin = $today_begin - 24*3600;
$thismonth_begin = mktime(0,0,0,date("m"),1);
$lastmonth_begin = strtotime("-1 month", $thismonth_begin);

$parts = array(2 => "����", 3 => "�绰");
foreach ($parts as $i => $pname) {
	echo '<tr><td colspan="16" class="head left">'.$pname."</td></tr>";

	if ($uinfo["part_id"] == $i || in_array($uinfo["part_id"], array(0,1,9))) {
		//if (!$uinfo["part_admin"]) {
		//	$uwhere = " and author='$author'";
		//}
		//$users = $db->query("select distinct author from $htable where part_id=$i $uwhere and author!='' order by binary author", "", "author");
		if (!$uinfo["part_admin"]) {
			$uwhere = " and realname='$realname'";
		}
		$users = $db->query("select realname from sys_admin where concat(',',hospitals,',') like '%,$hid,%' and part_id=$i", '', "realname");
		foreach ($users as $u) {
			// ����:
			$count_today = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$today_begin and order_date<$today_end", 1, "count");
			$count_today_come = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$today_begin and order_date<$today_end and status=1", 1, "count");
			$count_today_not = $count_today - $count_today_come;

			// ����:
			$count_yesterday = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$yesterday_begin and order_date<$today_begin", 1, "count");
			$count_yesterday_come = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$yesterday_begin and order_date<$today_begin and status=1", 1, "count");
			$count_yesterday_not = $count_yesterday - $count_yesterday_come;

			// ����:
			$count_thismonth = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$thismonth_begin and order_date<$today_end", 1, "count");
			$count_thismonth_come = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$thismonth_begin and order_date<$today_end and status=1", 1, "count");
			$count_this_month_not = $count_thismonth - $count_thismonth_come;

			// ����:
			$count_lastmonth = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$lastmonth_begin and order_date<$thismonth_begin", 1, "count");
			$count_lastmonth_come = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$lastmonth_begin and order_date<$thismonth_begin and status=1", 1, "count");
			$count_lastmonth_not = $count_lastmonth - $count_lastmonth_come;

			// ����:
			$count_all = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u'", 1, "count");
			$count_all_come = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and status=1", 1, "count");
			$count_all_not = $count_all - $count_all_come;

			if ($count_all > 0) {
?>
	<tr class="<?php echo $count++%2==0 ? 'line1' : 'line2'; ?>">
		<td class="item b"><?php echo $u; ?></td>
		<td class="item a"><?php echo $count_today_come; ?></td>
		<td class="item a"><?php echo $count_today_not; ?></td>
		<td class="item a"><?php echo $count_today; ?></td>
		<td class="item d"><?php echo $count_yesterday_come; ?></td>
		<td class="item d"><?php echo $count_yesterday_not; ?></td>
		<td class="item d"><?php echo $count_yesterday; ?></td>
		<td class="item a"><?php echo $count_thismonth_come; ?></td>
		<td class="item a"><?php echo $count_this_month_not; ?></td>
		<td class="item a"><?php echo $count_thismonth; ?></td>
		<td class="item c"><?php echo $count_lastmonth_come; ?></td>
		<td class="item c"><?php echo $count_lastmonth_not; ?></td>
		<td class="item c"><?php echo $count_lastmonth; ?></td>
		<td class="item a"><?php echo $count_all_come; ?></td>
		<td class="item a"><?php echo $count_all_not; ?></td>
		<td class="item a"><?php echo $count_all; ?></td>
	</tr>
<?php
			}
		}
	}
}

?>

</table>
<?php
	}
} else {
?>
<p style="padding-left:30px;">
<br>
<b>�ر�˵����</b><br>
<br>
���ر�����Ҫ�ϳ�ʱ�䣬�ҷǳ�ռ�÷�����CPU�����������ʱ�鿴�����򽫵��·������޷����ʣ�<br>
<br>
<input type="button" onclick="this.disabled=true; this.value='������..'; location='?do=show'" value="�鿴����" class="buttonb">
<br>
</p>
<?php } ?>

</body>
</html>