<?php
// --------------------------------------------------------
// - ����˵�� : ҽԺ�б�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-01 00:36
// --------------------------------------------------------
require "lib/set_env.php";
$table = "disease";

if ($hid == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

$disease_list = $db->query("select id,name from $table where hospital_id=$hid order by name asc", "id", "name");


if ($_GET["op"] == "hebing") {
	$todo_disease_id = intval($_GET["todo_disease_id"]);
	$end_disease_id = intval($_GET["end_disease_id"]);

	if ($todo_disease_id > 0 && $end_disease_id > 0 && $todo_disease_id != $end_disease_id) {
		// ����������:
		$db->query("update patient_{$hid} set disease_id=$end_disease_id where disease_id=$todo_disease_id");
		$db->query("delete from $table where id=$todo_disease_id limit 1");

		echo '<script type="text/javascript">'."\r\n";
		echo 'parent.load_box(0);'."\r\n";
		echo 'parent.msg_box("�ϲ��ɹ�")'."\r\n";
		echo 'parent.update_content()'."\r\n";
		echo '</script>'."\r\n";
	} else {
		echo "����ѡ��Ĳ���ȷ���뷵������ѡ��";
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
<title>�����ϲ�����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script type="text/javascript">
function check_data(f) {
	if (f.todo_disease_id.value == '') {
		alert("��ѡ��Ҫ�ϲ��ļ�������   ");
		f.todo_disease_id.focus();
		return false;
	}
	if (f.end_disease_id.value == '') {
		alert("��ѡ�����ձ��ϲ������ļ������ƣ�   ");
		f.end_disease_id.focus();
		return false;
	}
	if (f.todo_disease_id.value == f.end_disease_id.value) {
		alert("��Ҫ�ϲ��ļ������͡����ձ��ϲ������ļ���������ͬ�����������ã�   ");
		f.end_disease_id.focus();
		return false;
	}
	if (!confirm("��ע�⣺�ϲ����ܳ�����ȷ��Ҫ�ϲ���")) {
		return false;
	}
	return true;
}
</script>
</head>

<body style="padding:20px 20px;">

<form method="GET" action="" onsubmit="return check_data(this)">
	<fieldset>
		<legend>ע������</legend>
		<div style="padding:10px;">
			1. ��ͬ������ԤԼ���ݣ�������ԤԼ������Ҳ��ͬʱ�仯��<br>
			2. �ϲ����ܳ�������ʹ������ԱҲ�޷��������������С�Ĳ�������Ҫ�ϲ����ˡ�<br>
			3. �����ϣ���������ԤԼ���ݣ��벻Ҫʹ�ô˹��ܡ�
		</div>
	</fieldset>
	<br>
	<fieldset>
		<legend>�ϲ�</legend>
		<div style="padding:10px;">
			Ҫ�ϲ��ļ�����<select name="todo_disease_id" class="combo">
				<option value="" style="color:gray">-��ѡ��-</option>
				<?php echo list_option($disease_combo_arr, "_key_", "_value_"); ?>
			</select> (�ںϲ��󣬸ü�������ɾ��) <br>
			<br>
			���ձ��ϲ�����<select name="end_disease_id" class="combo">
				<option value="" style="color:gray">-��ѡ��-</option>
				<?php echo list_option($disease_combo_arr, "_key_", "_value_"); ?>
			</select> (�ϲ������ݶ���������) <br>
			<br>
			<div style="text-align:center">
				<input type="submit" class="buttonb" value="ȷ���ϲ�" />
			</div>
		</div>
	</fieldset>
	<input type="hidden" name="op" value="hebing" />
</form>



</body>
</html>