<?php
/* --------------------------------------------------------
// 说明: 按科室统计数据
// 作者: 幽兰 (weelia@126.com)
// 时间: 2010-04-06 14:56
// ----------------------------------------------------- */
require "lib/set_env.php";
$table = "patient_".$hid;

if ($hid == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$keshi_arr = $db->query("select id,name from depart where hospital_id=$hid order by name asc", "id", "name");

$media_from_array = explode(" ", "网络 电话");
$media_2 = $db->query("select name from media where (hospital_id=0 or hospital_id=$hid) order by sort desc,addtime asc", "", "name");
$media_from_array = array_merge($media_from_array, $media_2);

$status_array = array(0 => "所有", 1 => "已到", 2 => "未到");


if ($_GET["btime"] == "") $_GET["btime"] = date("Y-m-01");
if ($_GET["etime"] == "") $_GET["etime"] = date("Y-m-d");

$tb = strtotime($_GET["btime"]);
$te = strtotime($_GET["etime"]." 23:59:59");

$sql_where = "";
if ($_GET["media"]) {
	$sql_where .= " and media_from='".$_GET["media"]."'";
}

$yuyue_arr = $db->query("select depart, count(depart) as c from $table where order_date>=$tb and order_date<=$te $sql_where group by depart", "depart", "c");
$daoyuan_arr = $db->query("select depart, count(depart) as c from $table where order_date>=$tb and order_date<=$te and status=1 $sql_where group by depart", "depart", "c");




?>
<html>
<head>
<title>科室统计数据</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
</head>

<style type="text/css">
.center_show {margin:0 auto; width:700px; text-align:center; }
.report_tips {padding:30px 0 20px 0; text-align:center; font-size:16px; font-family:"微软雅黑"; }
.list {border:2px solid silver; }
.list * {text-align:center; }
.list .head {background:#e8e8e8; color:#bf0060; }
.list .head, .list .item {border:1px solid silver; }
.line_huizong * {color:red; font-weight:bold; }
.line_gray * {color:gray; font-weight:bold; }
</style>

<script type="text/javascript">
function write_dt(da, db) {
	byid("btime").value = da;
	byid("etime").value = db;
}
</script>

<body style="padding:30px">

<div class="center_show">
	<form method="GET">
		筛选条件：
		<input name="btime" id="btime" class="input" style="width:80px" value="<?php echo $_GET["btime"]; ?>" onclick="picker({el:'btime',dateFmt:'yyyy-MM-dd'})" onchange="this.form.submit();"> ~ <input name="etime" id="etime" class="input" style="width:80px" value="<?php echo $_GET["etime"]; ?>" onclick="picker({el:'etime',dateFmt:'yyyy-MM-dd'})" onchange="this.form.submit();">

<?php
$lmb = strtotime("-1 month", strtotime($_GET["btime"]));
$lme = strtotime($_GET["btime"]) - 1;
$nmb = strtotime("+1 month", strtotime($_GET["btime"]));
$nme = strtotime("+1 month", $nmb) - 1;
?>
		<input type="button" class="button" onclick="write_dt('<?php echo date("Y-m-d", $lmb); ?>', '<?php echo date("Y-m-d", $lme); ?>'); this.form.submit();" value="上月" style="margin-left:5px;">
		<input type="button" class="button" onclick="write_dt('<?php echo date("Y-m-d", $nmb); ?>', '<?php echo date("Y-m-d", $nme); ?>'); this.form.submit();" value="下月" style="margin-left:5px;">

		<select class="combo" name="media" onchange="this.form.submit();" style="margin-left:20px;">
			<option value="" style="color:gray">-媒体来源-</option>
			<?php echo list_option($media_from_array, "_value_", "_value_", $_GET["media"]); ?>
		</select>
		<!-- <input type="submit" class="button" value="确定" style="margin-left:20px;"/> -->
	</form>
</div>

<div class="center_show">
	<div class="report_tips"><?php echo $_GET["btime"]; ?> ~ <?php echo $_GET["etime"]; ?> <?php echo $hinfo["name"]; ?> 科室统计数据</div>

	<table class="list" width="100%">
		<tr>
			<th class="head">科室名称</th>
			<th class="head">预约总数</th>
			<th class="head">到院总数</th>
		</tr>
<?php foreach ($keshi_arr as $dp_id => $dp_name) { ?>
		<tr>
			<td class="item"><?php echo $dp_name; ?></td>
			<td class="item"><?php echo $yuyue_arr[$dp_id]; ?></td>
			<td class="item"><?php echo $daoyuan_arr[$dp_id]; ?></td>
		</tr>
<?php } ?>

<?php if ($yuyue_arr[0] > 0) { ?>
		<tr class="line_gray">
			<td class="item">(未标记)</td>
			<td class="item"><?php echo $yuyue_arr[0]; ?></td>
			<td class="item"><?php echo $daoyuan_arr[0]; ?></td>
		</tr>
<?php } ?>

		<tr class="line_huizong">
			<td class="item">汇总</td>
			<td class="item"><?php echo array_sum($yuyue_arr); ?></td>
			<td class="item"><?php echo array_sum($daoyuan_arr); ?></td>
		</tr>

	</table>
</div>


</body>
</html>