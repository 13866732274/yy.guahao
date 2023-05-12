<?php
// --------------------------------------------------------
// - 功能说明 : 日志内容查看
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-12-26
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_op_log";
if (file_exists("../ip/function.ip.php")) {
	include_once "../ip/function.ip.php";
} else {
	function ip_area($ip) {
		return '无法加载ip地址查询函数库';
	}
}

if ($id = intval($_GET["id"])) {
	$line = $db->query("select * from `$table` where id='$id' limit 1", 1);
} else {
	msg_box("参数传递错误，无法打开该页面！", "back", 1);
}

!check_power("v", $pinfo, $pagepower) && msg_box("对不起，您没有查看权限!", "back", 1);

$title = "操作日志";

?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.table {border:1px solid silver; }
.table td {border:1px solid #e3e3e3; padding:4px 2px 2px 2px; }
.table .r {text-align:right; padding-right:5px !important; }
.table .l {text-align:left; padding-left:5px !important; }
.table .t {vertical-align:top; }
.table .bg {background:#f2f3f4; }
.table .h {background:#e0e1e7; text-align:left; font-weight:bold; color:#517379; padding:5px 3px 3px 10px; }
</style>
</head>

<body>
<table width="100%" class="table">
	<tr>
		<td class="h" colspan="4">操作日志详情</td>
	</tr>
	<tr>
		<td class="r t bg" width="15%">ID：</td>
		<td class="l t" width="35%"><?php echo $id; ?></td>
		<td class="r t bg" width="15%">操作时间：</td>
		<td class="l t" width="35%"><?php echo date("Y-m-d H:i:s", $line["addtime"]); ?></td>
	</tr>
	<tr>
		<td class="r t bg">医院ID：</td>
		<td class="l t"><?php echo $line["hid"]; ?></td>
		<td class="r t bg">操作说明：</td>
		<td class="l t"><?php echo $line["author"]; ?></td>
	</tr>
	<tr>
		<td class="r t bg">操作内容：</td>
		<td class="l t" colspan="3"><?php echo $line["content"]; ?></td>
	</tr>
	<tr>
		<td class="r t bg">页面：</td>
		<td class="l t" colspan="3"><?php echo $line["url"]; ?></td>
	</tr>
	<tr>
		<td class="r t bg">删除ID：</td>
		<td class="l t" colspan="3"><?php echo $line["del_id"]; ?></td>
	</tr>
</table>

<br>

<?php
$sdata = @unserialize($line["data"]);
if (is_array($sdata) && count($sdata) > 0) {
	$sdata = $sdata[0];
	echo '<table width="100%" class="table">	<tr><td class="h" colspan="4">操作数据</td></tr>'."\r\n";
	$lines = ceil(count($sdata) / 2);
	$key_arr = array_keys($sdata);
	for ($i = 0; $i < $lines; $i++) {
		echo "\t".'<tr>'."\r\n";
		for ($j = 0; $j < 2; $j++) {
			$k = $key_arr[$i*2 + $j];
			if ($k) {
				$d = text_show(trim($sdata[$k]));
				echo "\t\t".'<td width="15%" class="r t bg">'.$k.'</td><td width="35%" class="l t">'.$d.'</td>'."\r\n";
			} else {
				echo "\t\t".'<td width="15%" class="r t bg"></td><td width="35%" class="l t"></td>'."\r\n";
			}
		}
		echo "\t".'</tr>'."\r\n";
	}
	echo '</table>'."\r\n";
}
?>


<div class="button_line">
	<input type="button" class="submit" onclick="parent.load_box(0)" value="关闭">
</div>

</body>
</html>