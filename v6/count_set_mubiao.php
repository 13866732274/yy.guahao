<?php
/*
// ˵��: ����Ŀ��
// ����: ���� (weelia@126.com)
// ʱ��: 2014-5-8
*/
require "lib/set_env.php";

$month = intval($_REQUEST["month"]);
$field = $_REQUEST["field"];

$cur_type = intval($_SESSION["count_type_id_web"]);
if ($cur_type <= 0) {
	exit("��Ŀû��ѡ��...");
}

$old = $db->query("select * from count_mubiao where type_id=$cur_type and month='$month' limit 1", 1);
if ($old["id"] > 0) {
	$mode = "edit";
} else {
	$mode = "add";
}

$data = @unserialize($old["config"]);

$old_data = $data[$field];

if ($_POST) {
	$d = $_REQUEST["data"];

	// �ж��Ƿ��Ѿ����
	$data[$field] = $d;
	$str = serialize($data);

	if ($mode == "add") {
		$db->query("insert into count_mubiao set type_id=$cur_type, month='$month', config='$str', author='$realname'");
	} else {
		$db->query("update count_mubiao set config='$str' where id=".$old["id"]." limit 1");
	}


	//echo '<script> parent.update_content(); </script>';
	$up_id = "mubiao_".$field;
	echo '<script> parent.update_content_byid("'.$up_id.'", "'.$d.'", "innerHTML"); </script>';
	echo '<script> parent.msg_box("Ŀ�����óɹ�", 2); </script>';
	echo '<script> parent.load_src(0); </script>';

	exit;
}


?>
<html>
<head>
<title>�޸����� - <?php echo $field; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script type="text/javascript">
function check_data() {
	return true;
}
</script>
</head>

<body>
<form name="mainform" action="" method="POST" onsubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">������Ŀ��</td>
	</tr>

	<tr>
		<td class="left">Ŀ�꣺</td>
		<td class="right">
			<input name="data" value="<?php echo $old_data; ?>" class="input" style="width:100px"> (�޸Ľ����¼��־��������)
		</td>
	</tr>
</table>

<input type="hidden" name="month" value="<?php echo $month; ?>">
<input type="hidden" name="field" value="<?php echo $field; ?>">
<div class="button_line">
	<input id="submit_button" type="submit" class="submit" value="�ύ����">
</div>

</form>

</body>
</html>