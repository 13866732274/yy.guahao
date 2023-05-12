<?php
// --------------------------------------------------------
// - 功能说明 : 导出名单
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-12-14
// --------------------------------------------------------
require "lib/set_env.php";
check_power('', $pinfo) or exit("没有打开权限...");
set_time_limit(0);

if ($hid == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

if ($config["is_output"] != 1 && !$debug_mode) {
	exit_html("对不起，您没有权限，请联系管理员！");
}

// 医院:
$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

$table = "patient_".$hid;

$time_array = array("order_date"=>"到院时间", "addtime"=>"添加时间");
$status_array = array("all"=>"不限", "come"=>"已到", "not"=>"未到");
$sort_array = array("order_date"=>"到院时间", "name"=>"名字");
$part_array = array("2"=>"网络", "3"=>"电话", "12"=>"电话回访");
$depart_array = $db->query("select id,name from depart where hospital_id='$user_hospital_id'", "id", "name");
$account_array = array("大账号", "小账号", "小小账号");
$t = strtotime("-12 month");
$author_array = $db->query("select distinct author from patient_{$hid} where addtime>$t order by author", "", "author");

// 疾病 @ 2012-11-06
$disease_id_name = $db->query("select id,name from disease where hospital_id={$hid}", "id", "name");
// 查询疾病对应的人数:
$did_num_arr = $db->query("select disease_id, count(disease_id) as num from patient_{$hid} group by disease_id", "disease_id", "num");
arsort($did_num_arr);

//echo "<pre>";
//print_r($did_num_arr);

// 处理疾病:
$display_disease_arr = array();
foreach ($did_num_arr as $k => $v) {
	if (!array_key_exists($k, $disease_id_name)) {
		continue;
	}
	$display_disease_arr[$k] = $disease_id_name[$k].' ['.intval($v).']';
}


$sys_fields = array();
$sys_fields["addtime"] = "添加时间";
$sys_fields["name"] = "姓名";
$sys_fields["sex"] = "性别";
$sys_fields["age"] = "年龄";
$sys_fields["tel"] = "电话号码";
$sys_fields["tel_location"] = "号码归属地";
$sys_fields["zhuanjia_num"] = "专家号";
$sys_fields["disease_id"] = "疾病";
$sys_fields["content"] = "咨询内容";
$sys_fields["media_from"] = "媒体来源";
$sys_fields["qq_from"] = "QQ来源";
$sys_fields["tel_from"] = "电话来源";
if ($config["show_guiji"] || $debug_mode) {
	$sys_fields["guiji"] = "轨迹";
	$sys_fields["from_site"] = "来源网站";
	$sys_fields["key_word"] = "关键词";
}
$sys_fields["memo"] = "备注";
$sys_fields["huifang"] = "回访";
$sys_fields["author"] = "客服";
$sys_fields["xianchang_doctor"] = "现场医生";
$sys_fields["status"] = "状态";
$sys_fields["order_date"] = "预约时间";
$sys_fields["suozaidi"] = "所在地区";


// 初始勾选的字段
if (!isset($_GET["fields"])) {
	$_GET["fields"] = array_keys($sys_fields);
}


$op = $_GET["op"];

// 处理时间:
if ($op == "show") {
	$where = "";

	$time_ty = "order_date";
	if ($_GET["ty"] && array_key_exists($_GET["ty"], $time_array)) {
		$time_ty = $_GET["ty"];
	}

	if ($_GET["btime"]) {
		$tb = strtotime($_GET["btime"]." 0:0:0");
		$where[] = "$time_ty>=$tb";
	}
	if ($_GET["etime"]) {
		$te = strtotime($_GET["etime"]." 23:59:59");
		$where[] = "$time_ty<$te";
	}

	if ($_GET["status"] == '') $_GET["status"] = "all";
	if ($st = $_GET["status"]) {
		if ($st != "all") {
			$where[] = ($st == "come") ? "status=1" : "status!=1";
		}
	}

	if ($_GET["part"]) {
		$where[] = "part_id=".intval($_GET["part"]);
	}

	if ($_GET["depart"]) {
		$where[] = "depart=".intval($_GET["depart"]);
	}

	if ($_GET["acc"]) {
		$where[] = "account='".$_GET["acc"]."'";
	}

	if ($_GET["disease"]) {
		$where[] = "disease_id=".intval($_GET["disease"]);
	}

	if ($_GET["author"]) {
		$where[] = "author='".$_GET["author"]."'";
	}

	$sqlwhere = count($where) ? ("where ".implode(" and ", $where)) : "";

	$sort = $_GET["sort"] ? $_GET["sort"] : "order_date";



	// 输出:
	$fields = $_GET["fields"];

	// 疾病类型转换:
	if (in_array("disease_id", $fields)) {
		//$disease_id_name = $db->query("select id,name from disease", "id", "name");
	}

	$output_name = array();

	// 输出表头:
	$head = array();
	foreach ($fields as $x) {
		$head[] = $sys_fields[$x];
	}
	$output_name[] = @implode("\t", $head);

	$q = mysql_query("select * from $table $sqlwhere order by $sort asc");
	while ($li = mysql_fetch_assoc($q)) {
	    //判断所在地20200806-开始
			if($li[suozaidi] == 1){$li[suozaidi] = '本地';}
			else if($li[suozaidi] == 2){$li[suozaidi] = '外地';}
			else if($li[suozaidi] == 3){$li[suozaidi] = '贵阳';}
			else if($li[suozaidi] == 4){$li[suozaidi] = '安顺';}
			else if($li[suozaidi] == 5){$li[suozaidi] = '毕节';}
			else if($li[suozaidi] == 6){$li[suozaidi] = '遵义';}
			else if($li[suozaidi] == 7){$li[suozaidi] = '六盘水';}
			else if($li[suozaidi] == 8){$li[suozaidi] = '黔南';}
			else if($li[suozaidi] == 9){$li[suozaidi] = '黔西南';}
			else if($li[suozaidi] == 10){$li[suozaidi] = '黔东南';}
			else if($li[suozaidi] == 11){$li[suozaidi] = '铜仁';}
			else{$li[suozaidi] = '其它';}
		//判断所在地20200806-结束	
		$line = array();
		foreach ($fields as $x) {
			if ($x == "order_date" || $x == "addtime") {
				$y = @date("Y-m-d H:i", $li[$x]);
			} else if ($x == "disease_id") {
				$y = $disease_id_name[$li[$x]];
			} else if ($x == "status") {
				$y = $li[$x] == 1 ? "已到院" : "未到院";
			} else if ($x == "tel") {
				$y = tel_filter($li);
			} else {
				$y = $li[$x];
			}
			// 替换所有回车换行为空格:
			$y = str_replace("\n", " ", str_replace("\r", "", $y));
			// 多个空格替换为一个:
			while (substr_count($y, "  ") > 0) {
				$y = str_replace("  ", " ", $y);
			}
			// 空值显示横线作为占位
			$line[] = (trim($y) == "" ? "-" : $y);
		}
		
		$output_name[] = @implode("\t", $line);
	}

	$output_num = count($output_name) - 1;
	$output_name = implode("\r\n", $output_name);

	if ($output_num > 500) {
		$_GET["asfile"] = 1;
	}

	//user_op_log("导出[".$output_num."]个患者");

	// 输出文件: 2011-12-14
	if ($_GET["asfile"]) {
		$file_name = $h_name."_".$_GET["btime"]."_".$_GET["etime"].".txt";
		header('Content-type: application/txt');
		header('Content-Disposition: attachment; filename="'.$file_name.'"');
		echo $output_name;
		exit;
	}
}


function _wee_date($unix_time) {
	$date = date("Y-m-d", $unix_time);
	$h = date("H", $unix_time);
	if ($h >= 7 && $h < 12) {
		$date .= " 上午";
	} else if ($h >= 12 && $h < 18) {
		$date .= " 下午";
	} else if ($h >= 18 && $h < 22) {
		$date .= " 晚上";
	} else {
		$date .= " 全天";
	}
	return $date;
}


$title = $h_name.' - 导出患者名单';

//user_op_log("打开导出患者名单");
?>
<html>
<head>
<title>导出数据</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
#tiaojian {border:2px solid #fdb53d; background:#f7ecdf; padding:10px; }
form {display:inline; }

#result {margin-left:0px; margin-top:0px; }
.h_name {font-weight:bold; margin-top:20px; }
.h_kf {margin-left:0px; }
.kf_li {border-bottom:0px dotted silver; }
</style>
<script type="text/javascript">
function check_field_all(o) {
	var chk = o.title == "全选" ? "checked" : "";
	var objs = byid("field_span").getElementsByTagName("INPUT");
	for (var i=0; i<objs.length; i++) {
		var obj = objs[i];
		if (obj.type == "checkbox") {
			obj.checked = chk;
		}
	}
	o.title = o.title == "全选" ? "全不选" : "全选";
}

function check_data() {
	alert("请注意：如果导出的患者超过500个，页面显示将比较慢，会自动以文件的方式导出。");
	return true;
}
</script>
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
<div id="tiaojian">
	<div>
	<span>设置条件：</span>
	<form method="GET" onsubmit="return check_data();">
	<select name="ty" class="combo">
		<option value="" style="color:gray">-时间类型-</option>
		<?php echo list_option($time_array, "_key_", "_value_", $time_ty); ?>
	</select>&nbsp;
	<input name="btime" id="begin_time" class="input" style="width:80px" value="<?php echo $_GET["btime"] ? $_GET["btime"] : date("Y-m-01"); ?>">
	<img src="image/calendar.gif" id="order_date" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">

	<input name="etime" id="end_time" class="input" style="width:80px" value="<?php echo $_GET["etime"] ? $_GET["etime"] : date("Y-m-d"); ?>">
	<img src="image/calendar.gif" id="order_date" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">

	<select name="status" class="combo">
		<option value="" style="color:gray">-是否到院-</option>
		<?php echo list_option($status_array, "_key_", "_value_", $_GET["status"]); ?>
	</select>&nbsp;
	<select name="sort" class="combo">
		<option value="" style="color:gray">-结果排序-</option>
		<?php echo list_option($sort_array, "_key_", "_value_", $_GET["sort"]); ?>
	</select>&nbsp;
	<select name="part" class="combo">
		<option value="" style="color:gray">-部门-</option>
		<?php echo list_option($part_array, "_key_", "_value_", $_GET["part"]); ?>
	</select>&nbsp;
	<select name="depart" class="combo">
		<option value="" style="color:gray">-科室-</option>
		<?php echo list_option($depart_array, "_key_", "_value_", $_GET["depart"]); ?>
	</select>&nbsp;
	<select name="acc" class="combo">
		<option value='' style="color:gray">-账号-</option>
		<?php echo list_option($account_array, "_value_", "_value_", $_GET["acc"]); ?>
	</select>&nbsp;
	<select name="disease" class="combo">
		<option value='' style="color:gray">-疾病-</option>
		<?php echo list_option($display_disease_arr, "_key_", "_value_", $_GET["disease"]); ?>
	</select>&nbsp;
	<select name="author" class="combo">
		<option value='' style="color:gray">-咨询员-</option>
		<?php echo list_option($author_array, "_value_", "_value_", $_GET["author"]); ?>
	</select>&nbsp;
	<input type="checkbox" class="check" name="asfile" id="asfile" value="1" <?php echo $_GET["asfile"] ? "checked" : ""; ?>><label for="asfile">导出为文件</label>
	<br>

	<span id="field_span">
	<a title="全不选" href="javascript:;" onclick="check_field_all(this)"><b>全选</b></a>：
<?php foreach ($sys_fields as $fn => $fv) { ?>
	<input type="checkbox" class="check" name="fields[]" id="ch_<?php echo $fn; ?>" value="<?php echo $fn; ?>" <?php echo (@in_array($fn, $_GET["fields"]) ? "checked" : ""); ?>><label for="ch_<?php echo $fn; ?>"><?php echo $fv; ?></label>
<?php } ?>
	</span>

	<input type="submit" class="button" value="提交">
	<input type="hidden" name="op" value="show">
	</form>
	</div>
</div>

<?php if ($op == "show") { ?>
<div style="margin:20px 0 5px 0px; color:red; ">患者总数：<b><?php echo $output_num; ?></b>人 &nbsp; 如果下面的患者太多，可勾选“导出为文件”直接下载。</div>
<div id="result">
	<textarea id="result_box" style="width:100%; height:400px;" class="input"><?php echo $output_name; ?></textarea><br>
	<br>
	说明：上表导出的结果复制到Excel中，会自动分列显示。<br>
	<br>
</div>
<?php } ?>


</body>
</html>