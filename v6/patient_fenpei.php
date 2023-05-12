<?php
/*
// 作者: 幽兰 (weelia@126.com)
*/
include "lib/set_env.php";

$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

if ($_POST["op"] == "submit_fenpei") {

	$from_names = _wee_name_func($_POST["from_names"]);
	$to_names = _wee_name_func($_POST["to_names"]);

	// 记录日志 @ 2016-09-27
	$_log = "[".implode(",", $from_names)."]患者再分配给[".implode(",", $to_names)."]";
	user_op_log($_log);

	if (count($from_names) == 0 || count($to_names) == 0) {
		exit("对不起，姓名填写错误...");
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

	echo "分配完成，以下为分配结果：<br>";
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
<title>平均分配患者</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script type="text/javascript">
function check_confirm() {
	if (confirm("请务必检查仔细，一旦分配无法撤销，一定要考虑和确认清楚之后再操作！")) {
		if (confirm("真的确定要提交给系统处理吗？要不要再看一下？")) {
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
<b>提示：</b><br>
1. 只分配未到的患者，不分配已到的患者；<br>
2. 客服名字，请使用<b>真实姓名（不是登录名）</b>。<br>
3. 分配之后不能撤销，操作请务必慎重。<br>
<br>
</div>

<b>* 当前要分配的科室为</b>：<b style="color:#ff00ff"><?php echo $hinfo["name"]; ?></b>  ID=<?php echo $hid; ?>  (如果不正确，请在系统首页切换)<br>
<br>

A. 要分配走的客服姓名（一行一个名字）：<br>
<textarea name="from_names" style="width:500px; height:250px;" class="input"></textarea>
<br>
<br>

B. 平均分配到这些人名下（一行一个名字）：<br>
<textarea name="to_names" style="width:500px; height:250px;" class="input"></textarea>
<br>
<br>

<input type="submit" class="submit" value="开始分配">
<br>
<br>
<input type="hidden" name="op" value="submit_fenpei">
</form>

<b>当前客服情况统计：</b><br>
<b>网络客服：</b><br>
<?php
$names = $db->query("select concat(author, '  [数量=', count(author), ']') as author from patient_{$hid} where part_id=2 and status!=1 group by author order by author asc", "", "author");

echo implode("<br>", $names);
?>
<br>
<br>
<b>电话客服：</b><br>
<?php
$names = $db->query("select concat(author, '  [数量=', count(author), ']') as author from patient_{$hid} where part_id=3 and status!=1 group by author order by author asc", "", "author");

echo implode("<br>", $names);
?>

</body>
</html>