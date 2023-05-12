<?php
// --------------------------------------------------------
// - ����˵�� : ��������
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


// �б���õ��·ݣ�
$m_arr = array();
$dt_begin = strtotime(date("Y-m")."-01 0:0:0");
for ($i = 0; $i < 24; $i++) {
	$dt = $i > 0 ? strtotime("-{$i} month", $dt_begin) : $dt_begin;
	$m_arr[date("Y-m", $dt)] = date("Y��m��", $dt);
}


// ҽԺ����:
$h_name = $db->query("select name from hospital where id=$hid limit 1", "1", "name");

// �·ݣ��������������ʼʱ�䣩:
$month = $_GET["m"];
if (!$month) {
	$_GET["m"] = $month = date("Y-m");
}

$m_begin = strtotime($month."-1 0:0:0");
$m_end = strtotime("+1 month", $m_begin) - 1;

// ��ѯ����:
if ($key = $_GET["key"]) {
	$where = " (content like '%{$key}%' or memo like '%{$key}%') and";
}

if ($_GET["account"]) {
	$where = " account='".$_GET["account"]."' and";
}

// �������е���:
$area_all = $db->query("select tel_location,count(tel_location) as c from $table where $where tel_location!='' and order_date>=$m_begin and order_date<=$m_end group by tel_location order by c desc", "tel_location", "c");

// �ϲ�����:
$area_use = array();
$first = array_keys($area_all);
$first = array_shift($first); //�������ĳ���һ��϶��Ǳ��ز���
$area_use[] = $first;
array_shift($area_all);

$area_merge = array();
foreach ($area_all as $k => $v) {
	if (substr_count($k, " ") > 0) {
		list($a, $b) = explode(" ", $k);
	} else {
		$a = $b = $k;
	}
	$area_merge[$a][] = $b;
}

foreach ($area_merge as $k => $v) {
	if (count($area_use) >= 10) {
		break;
	}
	$area_use[] = $k;
}

// ��ѯ���µ����пͷ�:
$kefu_arr = $db->query("select distinct author from $table where $where tel_location!='' and order_date>=$m_begin and order_date<=$m_end order by binary author", "", "author");

// ����ԤԼ��:
$order_all = $db->query("select author,count(author) as c from $table where $where order_date>=$m_begin and order_date<=$m_end group by author", "author", "c");
$order_come = $db->query("select author,count(author) as c from $table where $where status=1 and order_date>=$m_begin and order_date<=$m_end group by author", "author", "c");


// ÿ����������һ�β�ѯ:
$all = $come = array();
foreach ($area_use as $v) {
	// �ܼ�:
	$list = $db->query("select author,count(author) as c from $table where $where tel_location like '{$v}%' and order_date>=$m_begin and order_date<=$m_end group by author", "author", "c");
	$all[$v] = $list;

	// �ѵ�:
	$list = $db->query("select author,count(author) as c from $table where $where status=1 and tel_location like '{$v}%' and order_date>=$m_begin and order_date<=$m_end group by author", "author", "c");
	$come[$v] = $list;
}


?>
<html>
<head>
<title>���ݱ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
form {display:inline; }
.red {color:#bf0060 !important; }

.report_tips {padding:20px 0 10px 0; text-align:center; font-size:14px; font-weight:bold;  }

.list {border:2px solid silver !important; }
.head {border:0 !important; background:#e1e7ec !important; }
.item {border-top:1px solid #e2e6e7 !important; border-bottom:1px solid #e2e6e7 !important; text-align:center; padding:10px 3px 8px 3px !important; }

.hl {border-left:1px solid silver !important; }
.hr {border-right:1px solid silver !important; }
.ht {border-top:1px solid silver !important; }
.hb {border-bottom:1px solid silver !important; }

.huizong {font-weight:bold; color:#ff8040; background:#eff2f5 !important; }
</style>
</head>

<body>
<div style="margin:10px 0 0 0px;">
	<form method="GET">
		<b>�·ݣ�</b>
		<select name="m" class="combo" onchange="this.form.submit();">
			<option value="" style="color:silver">-��ѡ��-</option>
			<?php echo list_option($m_arr, "_key_", "_value_", $_GET["m"]); ?>
		</select>&nbsp;
		<b>�ؼ��ʣ�</b>
		<input title="��������ѯ���ݺͱ�ע" name="key" class="input" style="width:100px" value="<?php echo $_GET["key"]; ?>">&nbsp;
		<b>�ʺţ�</b>
		<select name="account" class="combo" onchange="this.form.submit();">
			<option value="" style="color:silver">-��ѡ��-</option>
			<?php echo list_option($account_array, "_value_", "_value_", $_GET["account"]); ?>
		</select>&nbsp;
		<input type="submit" class="button" value="ȷ��">
	</form>
</div>


<div class="report_tips"><?php echo $h_name; ?> <?php echo $month; ?> ԤԼ���˵�������</div>

<table class="list" width="100%">
	<tr>
		<th class="head hb"></th>

		<th class="head hb hl red" colspan="2">���е����ϼ�</th>

<?php foreach ($area_use as $v) { ?>
		<th class="head hb hl red" colspan="2"><?php echo $v; ?></th>
<?php } ?>
	</tr>

	<tr>
		<th class="head hb">�ͷ�</th>

		<th class="head hb hl">ȫ��</th>
		<th class="head hb">�ѵ�</th>

<?php foreach ($area_use as $v) { ?>
		<th class="head hb hl">ȫ��</th>
		<th class="head hb">�ѵ�</th>
<?php } ?>
	</tr>

<?php
	$sum = array();
	foreach ($kefu_arr as $kf) {
		$sum["Dall"] = intval($sum["Dall"]) + $order_all[$kf];
		$sum["Dcome"] = intval($sum["Dcome"]) + $order_come[$kf];
?>

	<tr>
		<td class="item"><font class="red"><?php echo $kf; ?></font></td>

		<td class="item hl"><?php echo $order_all[$kf]; ?></td>
		<td class="item"><?php echo $order_come[$kf]; ?></td>

<?php
	foreach ($area_use as $v) {
		$sum["all"][$v] = intval($sum["all"][$v]) + intval($all[$v][$kf]);
		$sum["come"][$v] = intval($sum["come"][$v]) + intval($come[$v][$kf]);

?>
		<td class="item hl"><?php echo $all[$v][$kf]; ?></td>
		<td class="item"><?php echo $come[$v][$kf]; ?></td>
<?php } ?>
	</tr>

<?php } ?>


	<tr>
		<td class="huizong ht item">�ܼ�</td>

		<td class="huizong ht item hl"><?php echo $sum["Dall"]; ?></td>
		<td class="huizong ht item"><?php echo $sum["Dcome"]; ?></td>

<?php foreach ($area_use as $v) { ?>
		<td class="huizong ht item hl"><?php echo $sum["all"][$v]; ?></td>
		<td class="huizong ht item"><?php echo $sum["come"][$v]; ?></td>
<?php } ?>
	</tr>

	<tr>
		<td class="huizong ht item">�ٷֱ�</td>

		<td class="huizong ht item hl">-</td>
		<td class="huizong ht item">-</td>

<?php foreach ($area_use as $v) { ?>
		<td class="huizong ht item hl"><?php echo @round($sum["all"][$v] * 100 / $sum["Dall"], 1); ?>%</td>
		<td class="huizong ht item"><?php echo @round($sum["come"][$v] * 100 / $sum["Dcome"], 1); ?>%</td>
<?php } ?>
	</tr>

</table>


<br>
<br>

</body>
</html>