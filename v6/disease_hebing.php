<?php
// --------------------------------------------------------
// - 功能说明 : 医院列表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-01 00:36
// --------------------------------------------------------
require "lib/set_env.php";
$table = "disease";

if ($hid == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$disease_list = $db->query("select id,name from $table where hospital_id=$hid order by name asc", "id", "name");


if ($_GET["op"] == "hebing") {
	$todo_disease_id = intval($_GET["todo_disease_id"]);
	$end_disease_id = intval($_GET["end_disease_id"]);

	if ($todo_disease_id > 0 && $end_disease_id > 0 && $todo_disease_id != $end_disease_id) {
		// 处理病人数据:
		$db->query("update patient_{$hid} set disease_id=$end_disease_id where disease_id=$todo_disease_id");
		$db->query("delete from $table where id=$todo_disease_id limit 1");

		echo '<script type="text/javascript">'."\r\n";
		echo 'parent.load_box(0);'."\r\n";
		echo 'parent.msg_box("合并成功")'."\r\n";
		echo 'parent.update_content()'."\r\n";
		echo '</script>'."\r\n";
	} else {
		echo "疾病选择的不正确，请返回重新选择！";
	}
	exit;
}


$disease_combo_arr = array();
foreach ($disease_list as $k => $v) {
	$disease_combo_arr[$k] = $v." [ID:".$k."]";
}


?>
<html>
<head>
<title>疾病合并工具</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script type="text/javascript">
function check_data(f) {
	if (f.todo_disease_id.value == '') {
		alert("请选择“要合并的疾病”！   ");
		f.todo_disease_id.focus();
		return false;
	}
	if (f.end_disease_id.value == '') {
		alert("请选择“最终被合并到”的疾病名称！   ");
		f.end_disease_id.focus();
		return false;
	}
	if (f.todo_disease_id.value == f.end_disease_id.value) {
		alert("“要合并的疾病”和“最终被合并到”的疾病不能相同，请重新设置！   ");
		f.end_disease_id.focus();
		return false;
	}
	if (!confirm("请注意：合并后不能撤销，确定要合并吗？")) {
		return false;
	}
	return true;
}
</script>
</head>

<body style="padding:20px 20px;">

<form method="GET" action="" onsubmit="return check_data(this)">
	<fieldset>
		<legend>注意事项</legend>
		<div style="padding:10px;">
			1. 将同步更改预约数据，调整后，预约的数据也会同时变化。<br>
			2. 合并后不能撤销（即使开发人员也无法撤销），请务必小心操作，不要合并错了。<br>
			3. 如果不希望程序操作预约数据，请不要使用此功能。
		</div>
	</fieldset>
	<br>
	<fieldset>
		<legend>合并</legend>
		<div style="padding:10px;">
			要合并的疾病：<select name="todo_disease_id" class="combo">
				<option value="" style="color:gray">-请选择-</option>
				<?php echo list_option($disease_combo_arr, "_key_", "_value_"); ?>
			</select> (在合并后，该疾病将被删除) <br>
			<br>
			最终被合并到：<select name="end_disease_id" class="combo">
				<option value="" style="color:gray">-请选择-</option>
				<?php echo list_option($disease_combo_arr, "_key_", "_value_"); ?>
			</select> (合并后，数据都在这里了) <br>
			<br>
			<div style="text-align:center">
				<input type="submit" class="buttonb" value="确定合并" />
			</div>
		</div>
	</fieldset>
	<input type="hidden" name="op" value="hebing" />
</form>



</body>
</html>