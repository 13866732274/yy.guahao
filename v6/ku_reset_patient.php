<?php
/*
// ����: ���� (weelia@126.com)
*/
include "lib/set_env.php";

if ($_POST["op"] == "submit_fenpei") {

	$from_names = _wee_name_func($_POST["from_names"]);
	$to_names = _wee_name_func($_POST["to_names"]);

	if (count($from_names) == 0 || count($to_names) == 0) {
		exit("�Բ���������д����...");
	}

	$q = array();
	foreach ($from_names as $v) {
		$q[] = '"' . $v . '"';
	}
	$qs = "u_name in (" . implode(",", $q) . ")";
	$list = $db->query("select id,u_name from ku_list where $qs order by addtime asc", "id", "u_name");

	$name_index = 0;
	$count = array();
	foreach ($list as $id => $name) {
		$new_name = _get_next($to_names);
		$db->query("update ku_list set uid=0, u_name='$new_name' where id='$id' limit 1");
		$count[$new_name] += 1;
	}

	$db->query("update ku_list k, sys_admin a set k.uid=a.id where k.uid=0 and k.u_name=a.realname");

	echo "������ɣ�����Ϊ��������<br>";
	echo "<pre>";
	print_r($count);
	exit;
}


function _get_next($to_names)
{
	global $name_index;
	if ($name_index >= count($to_names)) {
		$name_index = 0;
	}
	$this_name = $to_names[$name_index];
	$name_index++;
	return $this_name;
}


function _wee_name_func($str)
{
	$str = trim($str);
	$str = str_replace("\r", "", $str);
	$_arr = explode("\n", $str);
	$res_arr = array();
	foreach ($_arr as $k => $v) {
		$v = trim($v);
		if ($v != '') {
			$res_arr[] = $v;
		}
	}
	return $res_arr;
}

?>
<html>

<head>
	<title>�����ٷ��� (���ڷ�����ְ��ѯԱ�Ļ���)</title>
	<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
	<link href="lib/base.css" rel="stylesheet" type="text/css">
	<script src="lib/base.js" language="javascript"></script>
	<script type="text/javascript">
		function check_confirm() {
			if (confirm("����ؼ����ϸ��һ����������޷�������һ��Ҫ���Ǻ�ȷ�����֮���ٲ�����")) {
				return true;
			}
			return false;
		}
	</script>
</head>

<body>
	<center>

		<br>

		<form name="mainform" action="" method="POST" onsubmit="return check_confirm();">

			<b>Ҫ����Ŀͷ�����</b>������ʵ������ҳ����ʾ��Ϊ׼��һ��һ�����֣���<br>
			<textarea name="from_names" style="width:500px; height:100px;" class="input"></textarea>
			<br>
			<br>

			<b>ƽ�����䵽��Щ������</b>������ʵ������ҳ����ʾ��Ϊ׼��һ��һ�����֣���<br>
			<textarea name="to_names" style="width:500px; height:250px;" class="input"></textarea>
			<br>
			<br>

			<input type="submit" class="submit" value="��ʼ����">
			<br>
			<br>
			<input type="hidden" name="op" value="submit_fenpei">
		</form>

	</center>

</body>

</html>