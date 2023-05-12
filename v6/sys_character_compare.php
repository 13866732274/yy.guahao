<?php
//
// - 功能说明 : 权限大小比较
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-12-13
//
require "lib/set_env.php";
$table = "sys_character";

$id = intval($_REQUEST["id"]);
if (empty($id)) {
	exit_html("参数错误...");
}

// 读取本权限:
$cur_ch = $db->query("select * from $table where id=$id limit 1", 1);
if ($cur_ch["id"] != $id) {
	exit_html("参数错误 {$id}...");
}
$cur_p = $cur_ch["menu"];

// 要比较的权限:
$ch_list = $db->query("select id,name,author,menu from $table where id!=$id", "id");

// 逐个比较:
$big = $small = array();
foreach ($ch_list as $_id => $_ch) {
	if (!check_power_in($_ch["menu"], $cur_p)) {
		//§△▲★☆○●◇◆→⊙∞∝
		$big[] = '<font style="color:#ffa579;size:6px;font-family:Tahoma;">●</font> '.$_ch["name"]." (添加人:".$_ch["author"].") (ID:".$_id.")";
	} else {
		$small[] = '<font style="color:#ffa579;size:6px;font-family:Tahoma;">●</font> '.$_ch["name"]." (添加人:".$_ch["author"].") (ID:".$_id.")";
	}
}

$title = "比较“".$cur_ch["name"]."”的权限大小";
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
</head>

<body style="margin-left:40px; ">

<div class="space"></div>

<b>不在 <?php echo $cur_ch["name"]; ?> 范围内的权限 (可能某些权限<?php echo $cur_ch["name"]; ?>没有)：</b>
<div style="margin-left:60px;">
<?php
if (count($big) > 0) {
	echo implode("<br>", $big);
} else {
	echo '<font color="gray">(没有)</font>';
}
?>
</div>
<br>

<b>在 <?php echo $cur_ch["name"]; ?> 范围内的权限 (和<?php echo $cur_ch["name"]; ?>相等，或者比<?php echo $cur_ch["name"]; ?>更小)：</b>
<div style="margin-left:60px;">
<?php
if (count($small) > 0) {
	echo implode("<br>", $small);
} else {
	echo '<font color="gray">(没有)</font>';
}
?>
</div>
<br>

</body>
</html>