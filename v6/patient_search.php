<?php
// --------------------------------------------------------
// - 功能说明 : 搜索
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-11-03
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$user_hospital_id;


// 搜索提交之后:
if ($_GET["from"] == "search") {
	list($a, $url_end) = explode("?", $_SERVER["REQUEST_URI"], 2);
	$url = "patient.php?".$url_end;

	// 记录本次参数(用于下次搜索时 修改搜索条件) 2011-11-03
	$_SESSION["search_condition"] = @serialize($_GET);

	echo '正在搜索，请稍候...'."\r\n";
	echo '<script>'."\r\n";
	echo 'parent.byid("sys_frame").src = "'.$url.'";'."\r\n";
	echo 'setTimeout("parent.load_src(0)", 300);'."\r\n";
	echo '</script>'."\r\n";
	exit;
}


if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

if ($_GET["op"] == "new_search") {
	$_SESSION["search_condition"] = '';
}

$p_type = $uinfo["part_id"]; // 0,1,2,3,4

$title = '病人搜索';

$admin_name = $db->query("select realname from sys_admin", "", "realname");
$author_name = $db->query("select distinct author from $table order by binary author", "", "author");
$kefu_23_list = array_intersect($admin_name, $author_name);

$kefu_4_list = $db->query("select name,realname from sys_admin where hospitals='$user_hospital_id' and part_id in (4)");
$doctor_list = $db->query("select name from doctor where hospital_id='$user_hospital_id'");

$disease_list = $db->query("select id,name from disease where hospital_id=$user_hospital_id");
$depart_list = $db->query("select id,name from depart where hospital_id=$user_hospital_id");

$engine_list = $db->query("select id,name from engine", "id", "name");

//$media_from_array = explode(" ", "网络 电话 网挂 杂志 市场 地铁 朋友介绍 路牌 电视 电台 短信 路过 车身 广告 报纸 其他");
$media_from_array = explode(" ", "网络 电话");
$media_2 = $db->query("select name from media where (hospital_id=0 or hospital_id=$hid) order by sort desc,addtime asc", "", "name");
$media_from_array = array_merge($media_from_array, $media_2);

$qq_from_arr = $db->query("select id,name from qq_from order by sort desc,id asc", "id", "name");
$tel_from_arr = $db->query("select id,name from tel_from order by sort desc,id asc", "id", "name");

// 时间定义
// 昨天
$yesterday_begin = strtotime("-1 day");
// 明天
$tomorrow_begin = strtotime("+1 day");
// 本月
$this_month_begin = mktime(0,0,0,date("m"), 1);
$this_month_end = strtotime("+1 month", $this_month_begin) - 1;
// 上个月
$last_month_end = $this_month_begin - 1;
$last_month_begin = strtotime("-1 month", $this_month_begin);
//今年
$this_year_begin = mktime(0,0,0,1,1);
$this_year_end = strtotime("+1 year", $this_year_begin) - 1;
// 最近一个月
$near_1_month_begin = strtotime("-1 month");
// 最近三个月
$near_3_month_begin = strtotime("-3 month");
// 最近一年
$near_1_year_begin = strtotime("-12 month");

// 本周
$weekday = date("w");
if ($weekday == 0) $weekday = 7; //每周的开始为周一, 而不是周日
$this_week_begin = mktime(0, 0, 0, date("m"), (date("d") - $weekday + 1));



$se = array();
if ($_SESSION["search_condition"]) {
	$se = @unserialize($_SESSION["search_condition"]);
}

?>
<html>
<head>
<title>高级搜索</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
body {background:#f1f4f5; }
.sep {color:gray; padding:0 3px 0 3px; }
.head_tips {border:2px solid #ffa87d; background:#fffaf7; padding:4px 10px 2px 10px;  }
.margin20 {margin-left:20px; }
.myleft {padding:4px; text-align:right; background:white; }
.myright {padding:4px; text-align:left; background:white; }
</style>
<script language="javascript">
function write_dt(da, db) {
	byid("begin_time").value = da;
	byid("end_time").value = db;
}

function check_submit(form) {
	if (form.qudao.value < 0) {
		alert("二级轨迹选择有误，选择一级的无效，请选二级");
		return false;
	}
	return true;
}
</script>
</head>

<body>

<div class="head_tips">默认会记录上次搜索条件，如需清空记忆的条件，全新搜索，请点击这里：<a href="?op=new_search" title="清空记忆的条件，重新搜索"><b>[清空记忆条件]</b></a></div>
<div class="space"></div>

<form name="mainform" action="?" method="GET" onsubmit="return check_submit(this)">
<table width="100%" class="edit">
	<colgroup>
		<col style="width:20%"></col>
		<col style="width:20%"></col>
		<col style="width:15%"></col>
		<col style="width:45%"></col>
	</colgroup>

	<tbody>
	<tr>
		<td colspan="4" class="head">关键词</td>
	</tr>
	<tr>
		<td class="myleft">关键词：</td>
		<td class="myright" colspan="3" ><input name="searchword" class="input" style="width:150px" value="<?php echo $se["searchword"]; ?>"> <span class="intro">(留空则忽略此条件)</span></td>
	</tr>
	<tr>
		<td class="myleft">名字搜索：</td>
		<td class="myright" colspan="3"><textarea name="names" class="input" style="width:250px; height:40px; vertical-align:middle; overflow:visible; "><?php echo $se["names"]; ?></textarea> <span class="intro">换行隔开，每行一个名字，每次最多100个</span></td>
	</tr>
	<tr>
		<td colspan="4" class="head">时间限制</td>
	</tr>
	<tr>
		<td class="myleft">时间类型：</td>
		<td class="myright" colspan="3">
			<select name="time_type" class="combo">
				<option value="" style="color:gray">--请选择--</option>
<?php
$time_arr = array("order_date" => "预约时间", "addtime" => "客服添加时间");
echo list_option($time_arr, "_key_", "_value_", $se["time_type"]);
?>
			</select>
			<span class="intro">选择搜索的时间类型，默认为预约时间</span>
		</td>
	</tr>
	<tr>
		<td class="myleft" valign="top">时间起止：</td>
		<td class="myright" colspan="3">
			<input name="btime" id="begin_time" class="input" style="width:120px" value="<?php echo $se["btime"]; ?>" onclick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})"> ~ <input name="etime" id="end_time" class="input" style="width:120px" value="<?php echo $se["etime"]; ?>" onclick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})">

			<span style="margin-left:80px;">添加时间与到诊时间小于 <input name="shijiancha" class="input" style="width:60px" value="<?php echo $se["shijiancha"]; ?>"> 分钟</span>

			<div style="margin-top:4px;">速填：
<?php
	$show_day = array(
		"昨天" => array($yesterday_begin, 0),
		"今天" => array(time(), 0),
		"明天" => array($tomorrow_begin, 0),
		"后天" => array(strtotime("+2 day"), 0),

		"周六" => array(strtotime("next Saturday"), 0),
		"周日" => array(strtotime("next Sunday"), 0),

		"上周" => array(strtotime("-7 day", $this_week_begin), $this_week_begin - 1),
		"本周" => array($this_week_begin, strtotime("+6 day", $this_week_begin)),
		"下周" => array(strtotime("+7 day", $this_week_begin), strtotime("+13 day", $this_week_begin)),

		"本月" => array($this_month_begin, $this_month_end),
		"上月" => array($last_month_begin, $last_month_end),
		"今年" => array($this_year_begin, $this_year_end),

		"近一个月" => array($near_1_month_begin, time()),
		"近三个月" => array($near_3_month_begin, time()),
		"近一年" => array($near_1_year_begin, time())
	);

	$tmp = array();
	foreach ($show_day as $d1 => $d2) {
		if ($d2[1] == 0) $d2[1] = $d2[0];
		$tmp[] = '<a href="javascript:write_dt(\''.date("Y-m-d", $d2[0]).'\', \''.date("Y-m-d", $d2[1]).'\')">'.$d1.'</a>';
	}

	echo implode('　', $tmp);
?>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="4" class="head">人员搜索</td>
	</tr>

	<tr>
		<td class="myleft">搜客服：</td>
		<td class="myright">
			<select name="kefu_23_name" class="combo">
				<option value='' style="color:gray">--客服选择--</option>
				<?php echo list_option($kefu_23_list, '_value_', '_value_', $se["kefu_23_name"]); ?>
			</select>
		</td>
		<td class="myleft">搜推广人：</td>
		<td class="myright">
			<input name="tuiguangren" class="input" style="width:120px" value="<?php echo $se["tuiguangren"]; ?>">
		</td>
	</tr>

	<tr>
		<td class="myleft">搜导医：</td>
		<td class="myright" colspan="3">
			<select name="kefu_4_name" class="combo">
				<option value='' style="color:gray">--导医选择--</option>
				<?php echo list_option($kefu_4_list, 'realname', 'realname', $se["kefu_4_name"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="myleft">搜医生：</td>
		<td class="myright">
			<select name="doctor_name" class="combo">
				<option value='' style="color:gray">--接待医生--</option>
				<?php echo list_option($doctor_list, 'name', 'name', $se["doctor_name"]); ?>
			</select>
		</td>

		<td class="myleft">患者指定医生：</td>
		<td class="myright">
			<select name="wish_doctor" class="combo">
				<option value='' style="color:gray">--患者指定医生--</option>
				<?php echo list_option($doctor_list, 'name', 'name', $se["wish_doctor"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td colspan="4" class="head">更多搜索项</td>
	</tr>

	<tr>
		<td class="myleft">部门选择：</td>
		<td class="myright">
			<select name="part_id" class="combo">
				<option value='' style="color:gray">--部门选择--</option>
<?php
$part_id_arr = array(2 => "网络", 3 => "电话", 4 => "导医");
echo list_option($part_id_arr, "_key_", "_value_", $se["part_id"]);
?>
			</select>
		</td>

		<td class="myleft">就诊科室：</td>
		<td class="myright">
			<select name="depart" class="combo">
				<option value='' style="color:gray">--科室选择--</option>
				<?php echo list_option($depart_list, "id", "name", $se["depart"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="myleft">赴约状态：</td>
		<td class="myright">
			<select name="come" class="combo">
				<option value='' style="color:gray">--已到未到选择--</option>
<?php
$come_arr = array("1" => "已到", "-1" => "未到");
echo list_option($come_arr, '_key_', '_value_', $se["come"])
?>
			</select>
		</td>

		<td class="myleft">性别：</td>
		<td class="myright">
			<select name="sex" class="combo">
				<option value='' style="color:gray">--性别选择--</option>
<?php
$sex_arr = array("男", "女");
echo list_option($sex_arr, '_value_', '_value_', $se["sex"])
?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="myleft">疾病类型：</td>
		<td class="myright">
			<select name="disease" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($disease_list, "id", "name", $se["disease"]); ?>
			</select>
		</td>

		<td class="myleft">搜索来源：</td>
		<td class="myright">
			<select name="engine" class="combo">
				<option value='' style="color:gray">--引擎选择--</option>
				<?php echo list_option($engine_list, "_value_", "_value_", $se["engine"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="myleft">搜轨迹：</td>
		<td class="myright">
			<select name="guiji" class="combo">
				<option value='' style="color:gray">--轨迹选择--</option>
				<?php echo list_option($guiji_arr, "_key_", "_value_", $se["guiji"]); ?>
			</select>
		</td>

		<td class="myleft">二级轨迹：</td>
		<td class="myright">
<?php
$qudao_arr = array();
foreach ($guiji_arr as $k => $v) {
	$arr = $db->query("select * from dict_qudao where main_id='$k' order by sort desc, id asc", "id", "name");
	if (count($arr) > 0) {
		$qudao_arr[-$k] = $v;
		foreach ($arr as $k2 => $v2) {
			$qudao_arr[$k2] = "　　".$v2;
		}
	}
}
?>
			<select name="qudao" class="combo">
				<option value='' style="color:gray">--二级轨迹选择--</option>
				<?php echo list_option($qudao_arr, "_key_", "_value_", $se["qudao"]); ?>
			</select>
		</td>
	</tr>


	<tr>
		<td class="myleft">媒体来源：</td>
		<td class="myright">
			<select name="media_from" class="combo">
				<option value='' style="color:gray">--媒体来源--</option>
				<?php echo list_option($media_from_array, "_value_", "_value_", $se["media_from"]); ?>
			</select>
		</td>
		<td class="myleft">预约软件：</td>
		<td class="myright">
			<select name="order_soft" class="combo">
				<option value='' style="color:gray">--预约软件--</option>
				<?php echo list_option($web_soft_arr, "_key_", "_value_", $se["order_soft"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="myleft">QQ来源：</td>
		<td class="myright">
			<select name="qq_from" class="combo">
				<option value='' style="color:gray">--QQ来源--</option>
				<?php echo list_option($qq_from_arr, "_value_", "_value_", $se["qq_from"]); ?>
			</select>
		</td>

		<td class="myleft">电话来源：</td>
		<td class="myright">
			<select name="tel_from" class="combo">
				<option value='' style="color:gray">--电话来源--</option>
				<?php echo list_option($tel_from_arr, "_value_", "_value_", $se["tel_from"]); ?>
			</select>
		</td>
	</tr>

	<tr>
		<td class="myleft">账号：</td>
		<td class="myright">
			<select name="account" class="combo">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($account_array, '_value_', '_value_', $se["account"]); ?>
			</select>
		</td>
		<td class="myleft">网站来源：</td>
		<td class="myright">
			<input name="from_site" class="input" style="width:150px" value="<?php echo $se["from_site"]; ?>">
		</td>
	</tr>
	</tbody>
</table>

<input type="hidden" name="from" value="search">
<input type="hidden" name="sort" value="order_date">
<input type="hidden" name="sorttype" value="2">
<div class="button_line"><input type="submit" class="submit" value="搜索"></div>

</form>
</body>
</html>