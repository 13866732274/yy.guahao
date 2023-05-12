<?php
/*
// 说明: 查看最近几天的回访提醒
// 作者: 幽兰 (weelia@126.com)
// 时间: 2014-10-11
*/
include "lib/set_env.php";

// 查询出该科室最近几天的回访提醒
$t = mktime(0,0,0);
$date_from = date("Ymd", strtotime("-2 day", $t));
$date_end = date("Ymd", strtotime("+1 day", $t));

$list = $db->query("select * from patient_remind where hid='$hid' and remind_date>=$date_from and remind_date<=$date_end order by remind_date desc limit 500");


$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

function _content_color($s) {
	global $hinfo;
	if ($hinfo["template"] == '') {
		return $s;
	}
	$s = str_replace("<br>", "\n", $s);
	$s = str_replace("\r", "", $s);
	$arr = explode("\n", $s);
	foreach ($arr as $k => $v) {
		if (substr_count($v, "：") > 0 && substr_count($v, "患者指定医生") == 0) {
			list($a, $b) = explode("：", $v, 2);
			if ($b == '') {
				unset($arr[$k]);
			} else {
				$arr[$k] = '<font color="#c4833c">'.$a.'：</font>'.$b;
			}
		}
	}
	return implode("<br>", $arr);
}

?>
<html>
<head>
<title>回访提醒 - 查看</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
/* 列表内容显示区域表格控制 @ 2013-01-21 */
.ct {border:0; margin:3px 0; }
.ct * {line-height:150% !important; }
.ct_td_a {width:32px; vertical-align:top; }
.ct_td_b {/*color:#484848;*/ }
.c_1 {color:#2d2d2d; }
.c_2 {color:#d36001; }
.c_3 {color:#36874a; }

.c_4 {color:#484848; }
.c_5 {color:#484848; }
.c_6 {color:#484848; }
</style>
</head>

<body>

<form name="mainform">
<table width="100%" align="center" class="list">
	<!-- 表头定义 begin -->
	<tr>
		<td class="head" align="left">患者姓名</td>
		<td class="head" align="left">预约时间</td>
		<td class="head" align="left">提醒日期</td>
		<td class="head" align="left">内容</td>
		<td class="head" align="left">操作人</td>
		<td class="head" align="left">操作日期</td>
	</tr>
	<!-- 表头定义 end -->

	<!-- 主要列表数据 begin -->
<?php
foreach($list as $k => $li) {
	$pid = $li["patient_id"];
	$line = $db->query("select * from patient_{$hid} where id=$pid limit 1", 1);

	$content_arr = array();
	$line["content"] = _content_color(text_show(trim($line["content"])));
	if ($line["wish_doctor"]) {
		$line["content"] .= (trim($line["content"]) ? "<br>" : "").'（患者指定医生：'.$line["wish_doctor"].'）';
	}
	if ($line["content"]) {
		$content_arr[] = '<table class="ct" cellpadding="0" cellspacing="0"><tr><td class="ct_td_a c_1"><nobr>内容</nobr></td><td class="ct_td_b c_4">'.$line["content"].'</td></tr></table>';
	}
	if ($line["memo"]) {
		$content_arr[] = '<table class="ct" cellpadding="0" cellspacing="0"><tr><td class="ct_td_a c_2"><nobr>备注</nobr></td><td class="ct_td_b c_5">'.text_show(trim($line["memo"])).'</td></tr></table>';
	}
	if ($line["huifang"]) {
		$content_arr[] = '<table class="ct" cellpadding="0" cellspacing="0"><tr><td class="ct_td_a c_3"><nobr>回访</nobr></td><td class="ct_td_b c_6">'.text_show(trim(strip_tags($line["huifang"]))).'</td></tr></table>';
	}
	$content = implode('<div class="hr_line"></div>', $content_arr);


?>

	<tr class="">
		<td align="left" class="item"><?php echo $li["patient_name"]; ?></td>
		<td align="left" class="item"><?php echo nl2br(date("Y-m-d\nH:i", $line["order_date"])); ?></td>
		<td align="left" class="item"><?php echo int_date_to_date($li["remind_date"]); ?></td>
		<td align="left" class="item"><?php echo $content; ?></td>
		<td align="left" class="item"><?php echo $li["u_name"]; ?></td>
		<td align="left" class="item"><?php echo date("Y-m-d", $li["addtime"]); ?></td>
	</tr>
<?php } ?>

	<!-- 主要列表数据 end -->
</table>
</form>
<!-- 数据列表 end -->

<div class="space"></div>

</body>
</html>