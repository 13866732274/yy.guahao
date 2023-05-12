<?php
// --------------------------------------------------------
// - 功能说明 : 导出名单
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-3-13
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
set_time_limit(0);

// 医院:
$hid_limit = count($hospital_ids) > 0 ? implode(",", $hospital_ids) : "0";
$h_id_name = $db->query("select id,name from hospital where ishide=0 and id in ($hid_limit) order by name asc", "id", "name");

$part_id_name = array("2" => "网络", "3" => "电话");

$sys_fields = array();
$sys_fields["name"] = "姓名";
$sys_fields["sex"] = "性别";
$sys_fields["age"] = "年龄";
$sys_fields["mobile"] = "手机";
$sys_fields["zx_content"] = "咨询内容";
$sys_fields["hf_log"] = "回访";
$sys_fields["u_name"] = "客服";
$sys_fields["laiyuan"] = "来源";
$sys_fields["addtime"] = "添加时间";


// 初始勾选的字段
if (!isset($_GET["fields"])) {
	$_GET["fields"] = array_keys($sys_fields);
}


$op = $_GET["op"];

// 处理时间:
if ($op == "show") {
	$where = array();
	$where[] = "hid=".$_GET["hospital"];

	if ($_GET["btime"]) {
		$tb = strtotime($_GET["btime"]." 0:0:0");
		$where[] = "addtime>=$tb";
	}
	if ($_GET["etime"]) {
		$te = strtotime($_GET["etime"]." 23:59:59");
		$where[] = "addtime<$te";
	}

	if ($_GET["part_id"] > 0) {
		if ($_GET["part_id"] == "3") {
			$where[] = "part_id in (3, 12)"; //电话组 包含电话回访
		} else {
			$where[] = "part_id=".intval($_GET["part_id"]);
		}
	}

	$sqlwhere = implode(" and ", $where);

	// 输出:
	$fields = $_GET["fields"];

	$output_name = array();

	// 输出表头:
	$head = array();
	foreach ($fields as $x) {
		$head[] = $sys_fields[$x];
	}
	$output_name[] = @implode("\t", $head);

	$q = mysql_query("select * from ku_list where $sqlwhere order by id asc");
	while ($li = mysql_fetch_assoc($q)) {
		$line = array();
		foreach ($fields as $x) {
			if ($x == "addtime") {
				$y = @date("Y-m-d H:i", $li[$x]);
			} else {
				$y = trim($li[$x]);
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

	// 输出文件: 2011-12-14
	if ($_GET["asfile"]) {
		$file_name = $h_id_name[$_GET["hospital"]]."_资料库导出.txt";
		header('Content-type: application/txt');
		header('Content-Disposition: attachment; filename="'.$file_name.'"');
		echo $output_name;
		exit;
	}
}

?>
<html>
<head>
<title>资料库 - 导出</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
#tiaojian {border:0px solid #fdb53d; background:#fefff7; padding:5px 0; }
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

function check_data(o) {
	if (o.hospital.value == '') {
		alert("必须选择科室！");
		return false;
	}
	alert("请注意：如果导出的患者超过500个，会自动以文件的方式导出。");
	return true;
}
</script>
</head>

<body>

<div id="tiaojian">
	<form method="GET" onsubmit="return check_data(this);">

	<table width="100%" align="center" style="border:1px solid #ffba75; background:#fff2ec;">
		<tr>
			<td align="left" style="padding:10px;">
				<nobr>
				<select name="hospital" class="combo">
					<option value="" style="color:gray">-科室选择-</option>
					<?php echo list_option($h_id_name, "_key_", "_value_", $_GET["hospital"]); ?>
				</select>&nbsp;

				<input name="btime" id="begin_time" class="input" style="width:80px" value="<?php echo $_GET["btime"] ? $_GET["btime"] : date("Y-m-01"); ?>">
				<img src="image/calendar.gif" id="order_date" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">

				<input name="etime" id="end_time" class="input" style="width:80px" value="<?php echo $_GET["etime"] ? $_GET["etime"] : date("Y-m-d"); ?>">
				<img src="image/calendar.gif" id="order_date" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间">&nbsp;&nbsp;


				<select name="part_id" class="combo">
					<option value="" style="color:gray">-部门-</option>
					<?php echo list_option($part_id_name, "_key_", "_value_", $_GET["part_id"]); ?>
				</select>&nbsp;

				&nbsp;&nbsp;<input type="checkbox" class="check" name="asfile" id="asfile" value="1" <?php echo $_GET["asfile"] ? "checked" : ""; ?>><label for="asfile">导出为文件</label>
				</nobr>

				<br>

				<nobr>
				<span id="field_span">
				字段选择：
				<a title="全不选" href="javascript:;" onclick="check_field_all(this)"><b>全选</b></a>：
<?php foreach ($sys_fields as $fn => $fv) { ?>
				<input type="checkbox" class="check" name="fields[]" id="ch_<?php echo $fn; ?>" value="<?php echo $fn; ?>" <?php echo (@in_array($fn, $_GET["fields"]) ? "checked" : ""); ?>><label for="ch_<?php echo $fn; ?>"><?php echo $fv; ?></label>
<?php } ?>
				</span>
				</nobr>
			</td>

			<td align="left" style="padding:10px;">
				<input type="submit" class="button" value="提交">
				<input type="hidden" name="op" value="show">
			</td>
		</tr>
	</table>
	</form>
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