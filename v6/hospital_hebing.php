<?php
// --------------------------------------------------------
// - 功能说明 : 科室数据合并
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-4-1
// --------------------------------------------------------
require "lib/set_env.php";
$table = "hospital";

if (!$debug_mode) {
	//exit("您不是开发人员，请勿使用此功能。");
}


$group_id_name = $db->query("select id,name from hospital_group order by sort desc, name asc", "id", "name");
$options = array();
foreach ($group_id_name as $_gid => $_gname) {
	$h_list = $db->query("select id,name,color from hospital where ishide=0 and group_id=$_gid order by sort desc, name asc", "id");
	if (count($h_list) > 0) {
		$options[] = array('-1', $_gname." (".count($h_list).')', 'color:red' );
		foreach ($h_list as $_hid => $_arr) {
			$options[] = array($_hid, '　'.$_arr["name"], ($_arr["color"] ? ('color:'.$_arr["color"]) : 'color:blue') );
		}
	}
}


if ($_GET["op"] == "submit") {
	$tips = array();

	$from_hid = intval($_GET["wee_from_hid"]);
	$to_hid = intval($_GET["wee_to_hid"]);

	if ($from_hid <= 0 || $to_hid <= 0) {
		exit("对不起，科室ID错误");
	}

	// 表格式以function.create_table.php 内的格式为准
	include_once "lib/function.create_table.php";
	$stru_arr = _parse_fields($db_tables["patient"]);
	$field_arr = array_keys($stru_arr);

	unset($field_arr[0]); //id字段不要

	$f_str = implode(",", $field_arr);

	// 查询目标表的数据条数:
	$todo_count = $db->query("select count(*) as c from patient_{$from_hid}", 1, "c");
	if ($todo_count > 0) {
		$db->query("insert into patient_{$to_hid} ($f_str) (select $f_str from patient_{$from_hid})");
		$real_do_num = mysql_affected_rows();
		if ($real_do_num == 0) {
			echo "<pre>";
			print_r($field_arr);
			exit("处理未能成功，请检查程序和表结构。");
		} else if ($real_do_num == $todo_count) {
			$tips[] = "成功转移 ".$real_do_num." 条数据到目标表";
		} else {
			$tips[] = "预计转移=".$todo_count." 实际转移=".$real_do_num." 部分数据未成功，请确认";
		}
	} else {
		$tips[] = "目标表中没有数据，未处理患者数据";
	}

	$db->query("update disease set hospital_id=$to_hid where hospital_id=$from_hid");
	$tips[] = "疾病已转到目标科室";

	$db->query("update hospital set ishide=1 where id=$from_hid limit 1");
	$tips[] = "科室ID=".$from_hid." 已被隐藏";

	$tips[] = "";
	$tips[] = "有隐藏科室，请记得更新人员权限";

	echo '<title>处理完成</title>';
	echo implode("<br>", $tips);
	exit;
}


// 从一个创建表的语句中解读字段
function _parse_fields($s) {
	$list = explode("\n", $s);
	$out = array();
	foreach ($list as $k) {
		$k = trim($k);
		if (substr($k, 0, 1) == "`") {
			$fname = ltrim($k, "`");
			list($sa, $sb) = explode(" ", $fname, 2);
			$sa = rtrim($sa, "`");
			$out[$sa] = rtrim(trim($k), ',');
		}
	}

	return $out;
}



?>
<html>
<head>
<title>科室数据合并</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.edit, .edit td {border:0px solid #c0c0c0 !important; background:white !important; }
</style>
<script language="javascript">
</script>
</head>

<body>
<form name="mainform" action="" method="GET" onsubmit="return confirm('该操作不可撤销，请务必检查仔细再提交，是否确定立即执行？');">
<table width="100%" class="edit" style="margin-top:30px;">
	<tr>
		<td class="left" style="width:35%"><font color="red">*</font> 要合并的科室：</td>
		<td class="right">
<?php
		echo '	<select name="wee_from_hid" id="hospital_id" class="combo">';
		echo '		<option value="0" style="color:gray">-请选择-</option>';
		foreach ($options as $v) {
			echo '		<option value="'.$v[0].'"'.($v[2] ? ' style="'.$v[2].'"' : '').'>'.$v[1].'</option>';
		}
		echo '	</select>&nbsp;&nbsp;';
?>
			 <span class="intro">(合并后，该科室将被隐藏)</span>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 合并至此科室中：</td>
		<td class="right">
<?php
		echo '	<select name="wee_to_hid" id="hospital_id" class="combo">';
		echo '		<option value="0" style="color:gray">-请选择-</option>';
		foreach ($options as $v) {
			echo '		<option value="'.$v[0].'"'.($v[2] ? ' style="'.$v[2].'"' : '').'>'.$v[1].'</option>';
		}
		echo '	</select>&nbsp;&nbsp;';
?>
			 <span class="intro">(数据将被合并到这里)</span>
		</td>
	</tr>

</table>

<input type="hidden" name="op" value="submit" />

<div class="button_line" style="margin-top:30px;">
	<input type="submit" class="submit" value="确认处理">
</div>

</form>

</body>
</html>