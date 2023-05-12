<?php
// --------------------------------------------------------
// - 功能说明 : 报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-25 15:45
// --------------------------------------------------------
require "lib/set_env.php";
check_power('', $pinfo) or exit("没有打开权限...");

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$hospital_ids = array($user_hospital_id);

$title = '数据报表';
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
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips"><?php echo $title; ?></nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">刷新</button></td>
	</tr>
</table>
<!-- 头部 end -->

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
		<td rowspan="2" class="head">姓名</td>
		<td colspan="3" class="head">今日</td>
		<td colspan="3" class="head">昨日</td>
		<td colspan="3" class="head">本月</td>
		<td colspan="3" class="head">上月</td>
		<td colspan="3" class="head">全部</td>
	</tr>
	<tr>
		<td class="head a">已到</td>
		<td class="head a">未到</td>
		<td class="head a">总共</td>
		<td class="head b">已到</td>
		<td class="head b">未到</td>
		<td class="head b">总共</td>
		<td class="head a">已到</td>
		<td class="head a">未到</td>
		<td class="head a">总共</td>
		<td class="head b">已到</td>
		<td class="head b">未到</td>
		<td class="head b">总共</td>
		<td class="head a">已到</td>
		<td class="head a">未到</td>
		<td class="head a">总共</td>
	</tr>

<?php
// 时间定义:
$today_begin = mktime(0,0,0);
$today_end = $today_begin + 24*3600;
$yesterday_begin = $today_begin - 24*3600;
$thismonth_begin = mktime(0,0,0,date("m"),1);
$lastmonth_begin = strtotime("-1 month", $thismonth_begin);

$parts = array(2 => "网络", 3 => "电话");
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
			// 今日:
			$count_today = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$today_begin and order_date<$today_end", 1, "count");
			$count_today_come = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$today_begin and order_date<$today_end and status=1", 1, "count");
			$count_today_not = $count_today - $count_today_come;

			// 昨日:
			$count_yesterday = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$yesterday_begin and order_date<$today_begin", 1, "count");
			$count_yesterday_come = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$yesterday_begin and order_date<$today_begin and status=1", 1, "count");
			$count_yesterday_not = $count_yesterday - $count_yesterday_come;

			// 本月:
			$count_thismonth = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$thismonth_begin and order_date<$today_end", 1, "count");
			$count_thismonth_come = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$thismonth_begin and order_date<$today_end and status=1", 1, "count");
			$count_this_month_not = $count_thismonth - $count_thismonth_come;

			// 上月:
			$count_lastmonth = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$lastmonth_begin and order_date<$thismonth_begin", 1, "count");
			$count_lastmonth_come = $db->query("select count(*) as count from $htable where part_id=$i and binary author='$u' and order_date>=$lastmonth_begin and order_date<$thismonth_begin and status=1", 1, "count");
			$count_lastmonth_not = $count_lastmonth - $count_lastmonth_come;

			// 所有:
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
<b>特别说明：</b><br>
<br>
加载报表需要较长时间，且非常占用服务器CPU，请务必在闲时查看，否则将导致服务器无法访问！<br>
<br>
<input type="button" onclick="this.disabled=true; this.value='加载中..'; location='?do=show'" value="查看报表" class="buttonb">
<br>
</p>
<?php } ?>

</body>
</html>