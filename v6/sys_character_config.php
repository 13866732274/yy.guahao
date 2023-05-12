<?php
/*
// ����: ���� (weelia@126.com)
*/
require "lib/set_env.php";
$table = "sys_character";

$id = intval($_REQUEST["id"]);
if ($id <= 0) {
	exit("�Բ��� ����id����...");
}


$line = $db->query("select * from $table where id='$id' limit 1", 1);

$config = @unserialize($line["config"]);

$part_arr = $db->query("select id,name from sys_part", "id", "name");
$character_arr = $db->query("select id,name from sys_character order by sort desc, id asc", "id", "name");


if ($_POST) {

	if ($line["addtime"] != $_POST["crc"]) {
		exit("crcУ��ʧ��...");
	}

	ob_start();
	$set_arr = $_POST["config"];
	$set_str = serialize($set_arr);

	$db->query("update $table set `config`='$set_str' where id='$id' limit 1");
	$err = ob_get_clean();

	if ($err) {
		echo "�ύʧ�ܣ����Ժ�����:<br>".$err;
	} else {
		echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	}
	exit;

}

?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title>����Ȩ��</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.wee_new_edit {border:1px solid #c6c6c6; }
.wee_new_edit td {border-top:1px dotted #c6c6c6; }
.wee_new_edit .left {width:18%; text-align:right; padding:5px; background-color:#f3f3f3; }
.wee_new_edit .right {text-align:left; padding:5px; }
</style>
<script type="text/javascript">
function check_data(o) {
	return true;
}
</script>
</head>

<body>

<form method="POST" onsubmit="return check_data(this);">

<table width="100%" class="wee_new_edit">

	<tr>
		<td class="left">�����ݣ�</td>
		<td class="right">
			<input type="checkbox" name="config[show_list_all]" value="1" <?php if ($config["show_list_all"]) echo "checked"; ?> id="show_list_all"><label for="show_list_all">��ʾ���ܵ��б�</label>&nbsp;
			<input type="checkbox" name="config[duokeshi_huizong]" value="1" <?php if ($config["duokeshi_huizong"]) echo "checked"; ?> id="duokeshi_huizong"><label for="duokeshi_huizong">����һ���</label>&nbsp;
		</td>
	</tr>

	<tr>
		<td class="left">��ʾ���Ѷ</td>
		<td class="right">
			<select name="config[show_xiaofei]" class="combo">
				<option value="0" style="color:silver;">-����ʾ-</option>
<?php
$_arr = array(
	"1" => "��ʾ���ԹҲ��˵����Ѷ�",
	"2" => "��ʾ���в��˵����Ѷ�",
);

echo list_option($_arr, "_key_", "_value_", $config["show_xiaofei"]);
?>
			</select> <span class='intro'>������ѡ�����ò������ܵ�����������й©��</span>
		</td>
	</tr>


	<tr>
		<td class="left">��¼�ֻ��棺</td>
		<td class="right">
			<input type="checkbox" name="config[allow_mobile_login]" value="1" id="allow_mobile_login" <?php echo $config["allow_mobile_login"] ? "checked" : ""; ?>><label for="allow_mobile_login">��ѡ�������¼�ֻ��� (Ĭ�����Ϊ������)</label>
		</td>
	</tr>

	<tr>
		<td class="left">������Դ��</td>
		<td class="right">
			<input type="checkbox" name="config[show_engine]" value="1" id="show_engine" <?php echo $config["show_engine"] ? "checked" : ""; ?>><label for="show_engine">��ѡ���С������������͡���վ��Դ����ʾȨ�� (Ĭ��û��)</label>
		</td>
	</tr>

	<tr>
		<td class="left">���Ͽ⣺</td>
		<td class="right">
			<input type="checkbox" name="config[show_ziliaoku]" value="1" id="show_ziliaoku" <?php echo $config["show_ziliaoku"] ? "checked" : ""; ?>><label for="show_ziliaoku">��ѡ��ͨ�����Ͽ⡱����</label>&nbsp;&nbsp;
			<input type="checkbox" name="config[show_all_remind]" value="1" id="show_all_remind" <?php echo $config["show_all_remind"] ? "checked" : ""; ?>><label for="show_all_remind">�鿴���лط�����(�����鳤)</label>
		</td>
	</tr>

	<tr>
		<td class="left">�켣���ݣ�</td>
		<td class="right"><input type="checkbox" name="config[show_guiji]" value="1" <?php if ($config["show_guiji"]) echo "checked"; ?> id="show_guiji"><label for="show_guiji">��ʾ�켣����</label></td>
	</tr>

	<tr>
		<td class="left">�����ע��</td>
		<td class="right"><input type="checkbox" name="config[zixun_memo]" value="1" <?php if ($config["zixun_memo"]) echo "checked"; ?> id="zixun_memo"><label for="zixun_memo">�鿴���޸������ע</label></td>
	</tr>


	<tr>
		<td class="left">��վ��ʾ��</td>
		<td class="right">
			<input type="checkbox" name="config[show_site]" value="1" <?php if ($config["show_site"]) echo "checked"; ?> id="show_site"><label for="show_site">��ʾ���޸���Ŀ��վ</label>&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="config[show_site_out_date]" value="1" <?php if ($config["show_site_out_date"]) echo "checked"; ?> id="show_site_out_date"><label for="show_site_out_date">����ҳ���Ѽ������ڵ�����</label>  &nbsp; &nbsp;
		</td>
	</tr>

	<tr>
		<td class="left">�ѵ����ߣ�</td>
		<td class="right">
			<input type="checkbox" name="config[show_come_tel]" value="1" <?php if ($config["show_come_tel"]) echo "checked"; ?> id="show_come_tel"><label for="show_come_tel">��ʾ�����(����һ������)</label>&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="config[show_come_doctor]" value="1" <?php if ($config["show_come_doctor"]) echo "checked"; ?> id="show_come_doctor"><label for="show_come_doctor">��ʾ����ҽ��(����һ������)</label>
		</td>
	</tr>

	<tr>
		<td class="left">�ط����ѣ�</td>
		<td class="right">
			<input type="checkbox" name="config[show_remind_all]" value="1" <?php if ($config["show_remind_all"]) echo "checked"; ?> id="show_remind_all"><label for="show_remind_all">��ʾ�ط������б�</label>&nbsp;&nbsp;&nbsp;
			<!-- <input type="checkbox" name="config[show_remind_index]" value="1" <?php if ($config["show_remind_index"]) echo "checked"; ?> id="show_remind_index"><label for="show_remind_index">����ҳ��ʾ�طû���</label> -->
		</td>
	</tr>

	<tr>
		<td class="left">�Ա����ߣ�</td>
		<td class="right">
			<input type="checkbox" name="config[show_jiuzhenlv_compare]" value="1" <?php if ($config["show_jiuzhenlv_compare"]) echo "checked"; ?> id="show_jiuzhenlv_compare"><label for="show_jiuzhenlv_compare">��ͨ��ѯ�����ʶԱȹ���</label>&nbsp;&nbsp;&nbsp;
		</td>
	</tr>

	<tr>
		<td class="left">�ƹ��ˣ�</td>
		<td class="right">
			<input type="checkbox" name="config[allow_edit_tuiguangren]" value="1" <?php if ($config["allow_edit_tuiguangren"]) echo "checked"; ?> id="allow_edit_tuiguangren"><label for="allow_edit_tuiguangren">�����޸�</label>&nbsp;&nbsp;&nbsp;
		</td>
	</tr>

</table>

<div class="space"></div>

<div class="button_line">
	<input type="submit" class="submit" value="�ύ�޸�">
</div>

<input type="hidden" name="id" value="<?php echo $id; ?>">
<input type="hidden" name="crc" value="<?php echo $line["addtime"]; ?>">

</form>

</body>
</html>