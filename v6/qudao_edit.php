<?php
// --------------------------------------------------------
// - ����˵�� : ��������
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2014-10-18
// --------------------------------------------------------
$id = intval($_REQUEST["id"]);
$mode = $id > 0 ? "edit" : "add";
require "lib/set_env.php";
$table = "dict_qudao";


$guiji_key_type_arr = array(
	"" => "���Զ��ж�",
	"engine" => "�ⲿ��Դ��(��������/������վ)��(��Ӧ����ͨ��������Դ)",
	"site_url" => "������ַ��(�ƹ���վ/�Ż�վ)��(��Ӧ����ͨ�����η�����ַ)",
	"engine_site_url" => "�ⲿ��Դ+������ַ��(�������ַ����ϲ���һ���ж�)",
);


if ($_POST) {
	$r = array();
	$r["main_id"] = $_POST["main_id"];
	$r["main_name"] = $guiji_arr[$_POST["main_id"]];
	$r["name"] = $_POST["name"];
	$r["sort"] = $_POST["sort"];

	$r["guiji_key_type"] = trim($_POST["guiji_key_type"]);

	$keyword = str_replace("\r", "", $_POST["guiji_keyword"]);
	$key_arr = explode("\n", $keyword);
	$ok_key = array();
	foreach ($key_arr as $key) {
		$key = strip_tags($key);
		$key = str_replace('"', "", $key);
		$key = str_replace("'", "", $key);
		$key = str_replace("\\", "", $key);
		$key = trim($key);
		if ($key != "") {
			$ok_key[] = $key;
		}
	}
	$r["guiji_keyword"] = implode("\n", $ok_key);

	if ($mode == "add") {
		$r["addtime"] = time();
		$r["author"] = $username;
	}

	$sqldata = $db->sqljoin($r);
	if ($mode == "add") {
		$sql = "insert into $table set $sqldata";
	} else {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	}

	if ($db->query($sql)) {
		//echo '<script> parent.update_content(); </script>';
		echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "�ύʧ�ܣ����Ժ����ԣ�";
	}
	exit;
}

if ($mode == "edit") {
	$line = $db->query("select * from $table where id='$id' limit 1", 1);
}

?>
<html>
<head>
<title>�༭����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function check_data(f) {
	if (f.name.value == "") {
		alert("�����롰���ơ���");
		f.name.focus();
		return false;
	}
	return true;
}
</script>
</head>

<body>

<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" class="new_edit">
	<tr>
		<td class="left"><font color="red">*</font> ���ࣺ</td>
		<td class="right">
			<select name="main_id" class="combo">
				<?php echo list_option($guiji_arr, "_key_", "_value_", $line["main_id"]); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> ���ƣ�</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" size="30" style="width:200px"> <span class="intro">���Ʊ�����д</span></td>
	</tr>
	<tr>
		<td class="left">���ȶȣ�</td>
		<td class="right"><input name="sort" value="<?php echo $line["sort"]; ?>" class="input" style="width:80px"> <span class="intro">���ȶ�Խ��,����Խ��ǰ</span></td>
	</tr>
	<tr>
		<td class="left">�켣�ж����ݣ�</td>
		<td class="right">
			<select name="guiji_key_type" class="combo">
				<?php echo list_option($guiji_key_type_arr, "_key_", "_value_", $line["guiji_key_type"]); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left" valign="top">�켣�жϹؼ��ʣ�</td>
		<td class="right">
			<textarea name="guiji_keyword" class="input" style="width:400px; height:200px; "><?php echo $line["guiji_keyword"]; ?></textarea>
			<div style="margin-top:5px;">ÿ����дһ���ؼ��ʣ���������׼ȷ</div>
		</td>
	</tr>
</table>
<input type="hidden" name="id" value="<?php echo $id; ?>">

<div class="button_line">
	<input type="submit" class="submit" value="�ύ����">
</div>

</form>
</body>
</html>