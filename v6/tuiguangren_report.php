<?php
// --------------------------------------------------------
// - ����˵�� : �ƹ���ͳ�Ʊ���
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2015-9-12
// --------------------------------------------------------
require "lib/set_env.php";

if ($hid <= 0) exit("����ѡ��ҽԺ����");
$table = "patient_".$hid;

// ʱ�䶨�� 2011-12-28:
// ʱ�����ʼ�㶼�� YYYY-MM-DD 00:00:00 �������� YYYY-MM-DD 23:59:59
$today_tb = mktime(0,0,0); //���쿪ʼ
$today_te = strtotime("+1 day", $today_tb) - 1; //�������

$tomorrow_tb = $today_te + 1; //���쿪ʼ
$tomorrow_te = strtotime("+1 day", $tomorrow_tb) - 1; //�������

$yesterday_tb = strtotime("-1 day", $today_tb); //���쿪ʼ
$yesterday_te = $today_tb - 1; //�������

$month_tb = mktime(0, 0, 0, date("m"), 1); //���¿�ʼ
$month_te = strtotime("+1 month", $month_tb) - 1; //���½���

$lastmonth_tb = strtotime("-1 month", $month_tb); //���¿�ʼ
$lastmonth_te = $month_tb - 1; //���½���


// �Զ����·�:
$t = strtotime(date("Y-m-01"));
for ($i=2; $i<=7; $i++) {
	$t0 = strtotime("-".$i." month", $t);
	$his_month[date("Y-m", $t0)] = date("Y-m", $t0);
}

$show_his_month = $_GET["his_month"] != '' ? 1 : 0;
if ($show_his_month) {
	$his_tb = strtotime($_GET["his_month"]."-01");
	$his_te = strtotime("+1 month", $his_tb) - 1;
}


// ��ͳ���ʱ����ڵ��ƹ��ˣ�
//$tuiguangren_arr = $db->query("select tuiguangren, count(tuiguangren) as c from $table where (addtime>=$lastmonth_tb or order_date>=$lastmonth_tb) and (addtime<=$month_te or order_date<=$month_te) and tuiguangren!='' group by tuiguangren order by c desc", "", "tuiguangren");

$tuiguang_data = $sum = array();

// һ��ȡ�������¼ ���ȷֱ��ѯ���ٵĶ�:
if ($show_his_month) {
	$sql_add = "(((addtime>=$his_tb or order_date>=$his_tb) and (addtime<=$his_te or order_date<=$his_te)) or ((addtime>=$lastmonth_tb or order_date>=$lastmonth_tb) and (addtime<=$month_te or order_date<=$month_te)))";
} else {
	$sql_add = "(addtime>=$lastmonth_tb or order_date>=$lastmonth_tb) and (addtime<=$month_te or order_date<=$month_te)";
}
$all_data = $db->query("select tuiguangren,status,order_date,addtime from $table where $sql_add and tuiguangren!=''");


foreach ($all_data as $li) {
	if (is_numeric($li["tuiguangren"])) {
		$li["tuiguangren"] = " ".$li["tuiguangren"];
	}
	if ($li["addtime"] >= $today_tb && $li["addtime"] <= $today_te) {
		$tuiguang_data[$li["tuiguangren"]][1] += 1;
	}
	if ($li["order_date"] >= $today_tb && $li["order_date"] <= $today_te && $li["status"] == 1) {
		$tuiguang_data[$li["tuiguangren"]][2] += 1;
	}

	if ($li["addtime"] >= $yesterday_tb && $li["addtime"] <= $yesterday_te) {
		$tuiguang_data[$li["tuiguangren"]][3] += 1;
	}
	if ($li["order_date"] >= $yesterday_tb && $li["order_date"] <= $yesterday_te && $li["status"] == 1) {
		$tuiguang_data[$li["tuiguangren"]][4] += 1;
	}

	if ($li["addtime"] >= $month_tb && $li["addtime"] <= $month_te) {
		$tuiguang_data[$li["tuiguangren"]][5] += 1;
	}
	if ($li["order_date"] >= $month_tb && $li["order_date"] <= $month_te && $li["status"] == 1) {
		$tuiguang_data[$li["tuiguangren"]][6] += 1;
	}

	if ($li["addtime"] >= $lastmonth_tb && $li["addtime"] <= $lastmonth_te) {
		$tuiguang_data[$li["tuiguangren"]][7] += 1;
	}
	if ($li["order_date"] >= $lastmonth_tb && $li["order_date"] <= $lastmonth_te && $li["status"] == 1) {
		$tuiguang_data[$li["tuiguangren"]][8] += 1;
	}

	if ($show_his_month) {
		if ($li["addtime"] >= $his_tb && $li["addtime"] <= $his_te) {
			$tuiguang_data[$li["tuiguangren"]][9] += 1;
		}
		if ($li["order_date"] >= $his_tb && $li["order_date"] <= $his_te && $li["status"] == 1) {
			$tuiguang_data[$li["tuiguangren"]][10] += 1;
		}
	}

}


// ���ظ����ƹ���:
$tuiguangren_arr = array_keys($tuiguang_data);
sort($tuiguangren_arr);


// ����
foreach ($tuiguang_data as $v) {
	$sum[1] += $v[1];
	$sum[2] += $v[2];
	$sum[3] += $v[3];
	$sum[4] += $v[4];
	$sum[5] += $v[5];
	$sum[6] += $v[6];
	$sum[7] += $v[7];
	$sum[8] += $v[8];
	$sum[9] += $v[9];
	$sum[10] += $v[10];
}


?>
<html>
<head>
<title>�ƹ���ͳ�Ʊ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
* {font-family:"΢���ź�"; }
.center_show {margin:0 auto; width:900px; text-align:center; }
.list {border:2px solid silver; }
.head {color:#bf0060 !important; border:1px solid silver !important; background:#e1e7ec !important; }
.item {text-align:center; padding:4px !important; border-top:1px solid silver !important; border-bottom:1px solid silver !important; }
.bl {border-left:1px solid silver !important; }
#title_div {font-size:16px; font-weight:bold; margin:20px; text-align:center; }
</style>
<script type="text/javascript">
function show_huizong() {
	parent.load_src(1, '/v6/tuiguangren_report_all.php', 1000);
}
</script>

</head>

<body>

<div class="center_show">

	<div id="title_div">
		<?php echo $hinfo["name"]." �ƹ���ͳ��"; ?> &nbsp;
		<button onclick="self.location.reload();return false;" class="button">ˢ��</button> &nbsp;
		<a href="javascript:;" onclick="show_huizong();">��ʾ��Ժ����</a> &nbsp;
		�Զ����·ݣ�
		<select class="combo" onchange="self.location='?his_month='+this.value">
			<option value="" style="color:gray">������</option>
			<?php echo list_option($his_month, "_key_", "_value_", $_GET["his_month"]); ?>
		</select>
	</div>

	<table class="list" width="100%">
		<tr>
			<th class="head"></th>
<?php if ($show_his_month) { ?>
			<th class="head" colspan="2"><?php echo $_GET["his_month"]; ?></th>
<?php } ?>
			<th class="head" colspan="2">����</th>
			<th class="head" colspan="2">����</th>
			<th class="head" colspan="2">����</th>
			<th class="head" colspan="2">����</th>
		</tr>

		<tr>
			<th class="head">---�ƹ���---</th>

<?php if ($show_his_month) { ?>
			<th class="head">ԤԼ</th>
			<th class="head">��Ժ</th>
<?php } ?>

			<th class="head">ԤԼ</th>
			<th class="head">��Ժ</th>

			<th class="head">ԤԼ</th>
			<th class="head">��Ժ</th>

			<th class="head">ԤԼ</th>
			<th class="head">��Ժ</th>

			<th class="head">ԤԼ</th>
			<th class="head">��Ժ</th>
		</tr>


<?php foreach ($tuiguangren_arr as $k) { ?>
		<tr>
			<td class="item"><?php echo $k; ?></td>

<?php if ($show_his_month) { ?>
			<td class="item bl"><?php echo $tuiguang_data[$k][9]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][10]; ?></td>
<?php } ?>

			<td class="item bl"><?php echo $tuiguang_data[$k][7]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][8]; ?></td>

			<td class="item bl"><?php echo $tuiguang_data[$k][5]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][6]; ?></td>

			<td class="item bl"><?php echo $tuiguang_data[$k][3]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][4]; ?></td>

			<td class="item bl"><?php echo $tuiguang_data[$k][1]; ?></td>
			<td class="item"><?php echo $tuiguang_data[$k][2]; ?></td>
		</tr>

<?php } ?>

		<tr style="color:red">
			<td class="item">����</td>

<?php if ($show_his_month) { ?>
			<td class="item bl"><?php echo $sum[9]; ?></td>
			<td class="item"><?php echo $sum[10]; ?></td>
<?php } ?>

			<td class="item bl"><?php echo $sum[7]; ?></td>
			<td class="item"><?php echo $sum[8]; ?></td>

			<td class="item bl"><?php echo $sum[5]; ?></td>
			<td class="item"><?php echo $sum[6]; ?></td>

			<td class="item bl"><?php echo $sum[3]; ?></td>
			<td class="item"><?php echo $sum[4]; ?></td>

			<td class="item bl"><?php echo $sum[1]; ?></td>
			<td class="item"><?php echo $sum[2]; ?></td>
		</tr>

	</table>
</div>

<br>
<br>
<br>
<br>
<br>

</body>
</html>