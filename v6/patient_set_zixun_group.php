<?php
/*
// 说明: 设置咨询分组
// 作者: 幽兰 (weelia@126.com)
// 时间: 2014-2-27
*/
require "lib/set_env.php";
$table = "patient_".$hid;

$line = $db->query("select * from $table where id=$id limit 1", 1);

if (!in_array("set_zixun_group", $gGuaHaoConfig)) {
	exit_html("对不起，您没有权限~");
}

if ($_POST) {
	$zx_group = $_POST["zx_group"];

	$db->query("update $table set zx_group='$zx_group' where id='$id' limit 1");
	//echo $db->sql;

	echo '<script type="text/javascript">'."\r\n";
	//echo 'parent.load_box(0);'."\r\n";
	echo 'parent.msg_box("设置成功",2);'."\r\n";
	echo 'parent.close_divs();'."\r\n";
	//echo 'parent.update_content();'."\r\n";
	echo '</script>'."\r\n";
	exit;
}

?>
<html>
<head>
<title>设置患者分类</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.wee_class {border:1px solid #d7d7d7; background:#f7f7f7; padding:1px 5px 0px 5px; float:left; display:block; margin-right:5px; color:#000000; }
.wee_class:hover {border:1px solid #ffa477; background:#fff5ec; margin-right:5px; }
.wee_class_select {border:1px solid #ff0000; background:#ffeeee; padding:1px 5px 0px 5px; float:left; display:block; margin-right:5px; color:#8000ff; }
.wee_class_select:hover {border:1px solid #ff0000; background:#ffdddd; margin-right:5px; }
</style>

<script type="text/javascript">
function wee_set_select(obj, str, toset_id, torun_function) {
	byid(toset_id).value = str;
	var ls = obj.parentNode.getElementsByTagName("A");
	for (var i = 0; i < ls.length; i++) {
		ls[i].className = "wee_class";
	}
	obj.className = "wee_class_select";
	obj.blur();
	if (torun_function != undefined) {
		eval(torun_function+'();');
	}
}
</script>
<style type="text/css">
body {margin-top:15px; }
.name_area {float:left; padding-top:2px; }
.op_area {float:left; margin-left:5px; }
.submit_area {margin-left:5px; margin-top:10px; }
</style>
</head>

<body>
<form name="mainform" method="POST">

<div>
<span class="op_area" id="op_area">
	<a href="javascript:;" title="" class="wee_class" onclick="wee_set_select(this, '', 'zx_group'); return false;">无</a>
<?php foreach ($zx_group_arr as $_oid => $_oname) { ?>
	<a href="javascript:;" title="<?php echo $_oid; ?>" class="wee_class" onclick="wee_set_select(this, '<?php echo $_oid; ?>', 'zx_group'); return false;"><?php echo $_oname; ?></a>
<?php } ?>
</span>
<input type="hidden" name="zx_group" id="zx_group" value="">

<script type="text/javascript">
var zixun_group = "<?php echo $line["zx_group"]; ?>";
var objs = byid("op_area").getElementsByTagName("A");
for (var i=0; i<objs.length; i++) {
	if (objs[i].title == zixun_group) {
		objs[i].onclick(); break;
	}
}
</script>

<div class="clear"></div>
</div>

<div class="submit_area">
	<input type="submit" class="buttonb" value="提交">
</div>

<input type="hidden" name="id" value="<?php echo $id; ?>">
</form>

</body>
</html>