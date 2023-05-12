<?php
/*
// 说明: 添加预约 - 选择目标科室
// 作者: 幽兰 (weelia@126.com)
// 时间: 2016-10-12
*/
if (!$db) exit();

// 当前科室在内:
$cur_hid = $line["hid"];
$hname = $db->query("select sname from hospital where id=$cur_hid limit 1", 1, "sname");

$h_limit = count($hospital_ids) ? implode(",", $hospital_ids) : "0";
$h_list = $db->query("select id, name from hospital where id in ($h_limit) and sname='$hname' and ishide=0 order by name asc");

?>
<html>
<head>
<title>选择科室</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
* {font-family:"微软雅黑"; }
form * {vertical-align:middle; }
</style>
<script type="text/javascript">

</script>
</head>

<body>

<div style="padding:30px; ">
	<form method="GET">
		请选择要添加到的科室：
		<select name="to_hid" class="combo">
			<?php echo list_option($h_list, "id", "name", $cur_hid); ?>
		</select>
		<input type="submit" value="下一步" class="buttonb" style="margin-left:10px;">
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<input type="hidden" name="op" value="set_yuyue2">
	</form>
</div>

</body>
</html>