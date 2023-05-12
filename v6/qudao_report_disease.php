<?php
// --------------------------------------------------------
// - ����˵�� : �켣����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2015-6-12
// --------------------------------------------------------
require "lib/set_env.php";

if ($hid <= 0) {
	exit("��������ҳѡ��ҽԺ����");
}
$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

if ($_GET["from_date"] == '') {
	$_GET["from_date"] = date("Y-m-01");
}
if ($_GET["to_date"] == '') {
	$_GET["to_date"] = date("Y-m-d");
}
$from_date = $_GET["from_date"];
$to_date = $_GET["to_date"];

$from_time = strtotime($from_date);
$to_time = strtotime($to_date." 23:59:59");



?>
<html>
<head>
<title>�켣����(���������۲���)</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/sorttable_keep.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
* {font-family:"΢���ź�"; }
.input, .input_focus {font-family:"����"; }
td {line-height:20px;  }
.button1 {color:red !important; font-weight:bold;  }
</style>
<script language="javascript">
</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td style="width:120px"><nobr class="tips">�켣ͳ��(������)</nobr></td>
		<td align="center">
			<form method="GET">
				���ң�<?php echo $hinfo["name"]; ?>������������ֹ�� <input name="from_date" id="from_date" class="input" size="12" value="<?php echo $from_date; ?>" onclick="picker({el:'from_date',dateFmt:'yyyy-MM-dd'})"> ~ <input name="to_date" id="to_date" class="input" size="12" value="<?php echo $to_date; ?>" onclick="picker({el:'to_date',dateFmt:'yyyy-MM-dd'})"> <input class="button" type="submit" value="ȷ��">
			</form>
		</td>
		<td align="right" style="width:120px">
			<a href="#" target="_blank">���´��ڴ�</a>
			<button onclick="self.location.reload()" class="button" title="">ˢ��</button>
		</td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<!-- �����б� begin -->
<form name="mainform">
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="left" width="40%">����</td>
		<td class="head" align="center" width="10%">ԤԼ</td>
		<td class="head" align="center" width="10%">��Ժ</td>
		<td class="head" align="center" width="40%"></td>
	</tr>

	<!-- ��Ҫ�б����� begin -->
<?php

$disease_id_name = $db->query("select id, name from disease where hospital_id=$hid order by sort desc, id asc", "id", "name");

$where_add = "";
if ($_GET["gid"] != "") {
	$where_add = " and guiji='".$_GET["gid"]."'";
}

// ʹ��group by���ٶȷǳ���:
$yuyue_guiji = $db->query("select disease_id, count(disease_id) as c from patient_{$hid} where addtime>=$from_time and addtime<=$to_time and guiji in (1,2,25,26,21,22,23,24,3) $where_add group by disease_id", "disease_id", "c");
$daoyuan_guiji = $db->query("select disease_id, count(disease_id) as c from patient_{$hid} where order_date>=$from_time and order_date<=$to_time and guiji in (1,2,25,26,21,22,23,24,3) $where_add and status=1 group by disease_id", "disease_id", "c");

foreach ($disease_id_name as $gid => $gname) {
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item"><?php echo $gname; ?></td>
		<td align="center" class="item"><?php echo $yuyue_guiji[$gid]; ?></td>
		<td align="center" class="item"><?php echo $daoyuan_guiji[$gid]; ?></td>
		<td align="center" class="item">&nbsp;</td>
	</tr>
<?php
}
?>

	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item red">����</td>
		<td align="center" class="item red"><?php echo array_sum($yuyue_guiji); ?></td>
		<td align="center" class="item red"><?php echo array_sum($daoyuan_guiji); ?></td>
		<td align="center" class="item">&nbsp;</td>
	</tr>

	<!-- ��Ҫ�б����� end -->
</table>
</form>

<br>
ִ�к�ʱ��<?php echo round(now() - $pagebegintime, 4); ?>s
<br>
<br>

</body>
</html>