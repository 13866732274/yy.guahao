<?php
/*
// ˵��: ����
// ����: ���� (weelia@126.com)
// ʱ��: 2011-11-24
*/
require "lib/set_env.php";

// ������Ķ���:
include "rp.core.php";

$tongji_tips = " - ý����Դͳ�� - ".$type_tips;
?>
<html>
<head>
<title>ý����Դ����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
body {margin-top:6px; }
#rp_condition_form {text-align:center; }
.head, .head a {font-family:"΢���ź�","Verdana"; }
.item {font-family:"Tahoma"; padding:8px 3px 6px 3px !important; }
.footer_op_left {font-family:"Tahoma"; }
.date_tips {padding:15px 0 15px 0px; font-weight:bold; text-align:center; font-size:15px; font-family:"΢���ź�","Verdana"; }
form {display:inline; }
.red {color:red !important;  }
</style>
</head>

<body>

<?php include_once "rp.condition_form.php"; ?>

<?php if ($_GET["op"] == "report") { ?>
<?php

// $media_arr �� rp.core.php �ж���,Ϊϵͳ�ֵ�

if (in_array($type, array(1,2,3,4))) {
	// ����ͳ������:
	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["�ܼ�"] = $db->query("select count(*) as c from $table where $where {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");

		foreach ($media_arr as $me) {
			$data[$k][$me] = $db->query("select count(*) as c from $table where $where media_from='{$me}' and {$timetype}>=".$v[0]." and {$timetype}<=".$v[1]." ", 1, "c");
		}
	}
} else if ($type == 5) {
	$arr = array();
	$arr["�ܼ�"] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");

	foreach ($media_arr as $me) {
		$arr[$me] = $db->query("select from_unixtime({$timetype},'%k') as sd,count(from_unixtime({$timetype},'%k')) as c from $table where media_from='{$me}' and $where {$timetype}>=".$tb." and {$timetype}<=".$te." group by from_unixtime({$timetype},'%k')", "sd", "c");
	}

	$data = array();
	foreach ($final_dt_arr as $k => $v) {
		$data[$k]["�ܼ�"] = intval($arr["�ܼ�"][$v]);
		foreach ($media_arr as $me) {
			$data[$k][$me] = intval($arr[$me][$v]);
		}
	}
}

// ͳ��Ƶ����ߵ�ý��:
$sum = array();
foreach ($data as $date => $m_arr) {
	foreach ($m_arr as $m_name => $m_count) {
		$sum[$m_name] += $m_count;
	}
}
arsort($sum);
$media_all_arr = array_keys($sum);
if (count($media_all_arr) > 16) {
	$media_arr = array_slice($media_all_arr, 0, 16);
	$tips = ' (Ϊ�򻯱��ֻͳ��Ƶ����ߵ�15��ý��)';
}


?>
<div class="date_tips"><?php echo $h_name.$tongji_tips.$tips; ?></div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center">ʱ��</td>
<?php foreach ($media_arr as $me) { ?>
		<td class="head" align="center"><?php echo $me; ?></td>
<?php } ?>
	</tr>

<?php foreach ($final_dt_arr as $k => $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $k; ?></td>
<?php   foreach ($media_arr as $me) { ?>
		<td class="item" align="center"><?php echo $data[$k][$me]; ?></td>
<?php   } ?>
	</tr>
<?php } ?>
</table>

<br>
<?php } ?>


</body>
</html>