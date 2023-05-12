<?php
/*
// ����: ���� (weelia@126.com)
*/
include "lib/set_env.php";

$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

if ($_POST["op"] == "submit_fenpei") {

	$from_names = _wee_name_func($_POST["from_names"]);
	$to_names = _wee_name_func($_POST["to_names"]);

	// ��¼��־ @ 2016-09-27
	$_log = "[".implode(",", $from_names)."]�����ٷ����[".implode(",", $to_names)."]";
	user_op_log($_log);

	if (count($from_names) == 0 || count($to_names) == 0) {
		exit("�Բ���������д����...");
	}

	$q = array();
	foreach ($from_names as $v) {
		$q[] = '"'.$v.'"';
	}
	$qs = "author in (".implode(",", $q).")";

	$sql = "select id,author from patient_{$hid} where status!=1 and $qs order by addtime asc";

	$list = $db->query($sql, "id", "author");
	$name_index = 0;
	$count = array();
	foreach ($list as $id => $name) {
		$new_name = _get_next($to_names);
		$db->query("update patient_{$hid} set uid=0, author='$new_name' where id='$id' limit 1");
		$count[$new_name] += 1;
	}

	$db->query("update patient_{$hid} p, sys_admin a set p.uid=a.id where p.uid=0 and p.author=a.realname");

	echo "������ɣ�����Ϊ��������<br>";
	echo "<pre>";
	print_r($count);
	exit;

}


function _get_next($to_names) {
	global $name_index;
	if ($name_index >= count($to_names)) {
		$name_index = 0;
	}
	$this_name = $to_names[$name_index];
	$name_index++;
	return $this_name;
}


function _wee_name_func($str) {
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
<title>ƽ�����仼��</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script type="text/javascript">
function check_confirm() {
	if (confirm("����ؼ����ϸ��һ�������޷�������һ��Ҫ���Ǻ�ȷ�����֮���ٲ�����")) {
		if (confirm("���ȷ��Ҫ�ύ��ϵͳ������Ҫ��Ҫ�ٿ�һ�£�")) {
			return true;
		}
	}
	return false;
}
</script>
</head>

<body>

<form name="mainform" action="" method="POST" onsubmit="return check_confirm();">

<div style="color:red">
<b>��ʾ��</b><br>
1. ֻ����δ���Ļ��ߣ��������ѵ��Ļ��ߣ�<br>
2. �ͷ����֣���ʹ��<b>��ʵ���������ǵ�¼����</b>��<br>
3. ����֮���ܳ�����������������ء�<br>
<br>
</div>

<b>* ��ǰҪ����Ŀ���Ϊ</b>��<b style="color:#ff00ff"><?php echo $hinfo["name"]; ?></b>  ID=<?php echo $hid; ?>  (�������ȷ������ϵͳ��ҳ�л�)<br>
<br>

A. Ҫ�����ߵĿͷ�������һ��һ�����֣���<br>
<textarea name="from_names" style="width:500px; height:250px;" class="input"></textarea>
<br>
<br>

B. ƽ�����䵽��Щ�����£�һ��һ�����֣���<br>
<textarea name="to_names" style="width:500px; height:250px;" class="input"></textarea>
<br>
<br>

<input type="submit" class="submit" value="��ʼ����">
<br>
<br>
<input type="hidden" name="op" value="submit_fenpei">
</form>

<b>��ǰ�ͷ����ͳ�ƣ�</b><br>
<b>����ͷ���</b><br>
<?php
$names = $db->query("select concat(author, '  [����=', count(author), ']') as author from patient_{$hid} where part_id=2 and status!=1 group by author order by author asc", "", "author");

echo implode("<br>", $names);
?>
<br>
<br>
<b>�绰�ͷ���</b><br>
<?php
$names = $db->query("select concat(author, '  [����=', count(author), ']') as author from patient_{$hid} where part_id=3 and status!=1 group by author order by author asc", "", "author");

echo implode("<br>", $names);
?>

</body>
</html>