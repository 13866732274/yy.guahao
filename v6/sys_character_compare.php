<?php
//
// - ����˵�� : Ȩ�޴�С�Ƚ�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2011-12-13
//
require "lib/set_env.php";
$table = "sys_character";

$id = intval($_REQUEST["id"]);
if (empty($id)) {
	exit_html("��������...");
}

// ��ȡ��Ȩ��:
$cur_ch = $db->query("select * from $table where id=$id limit 1", 1);
if ($cur_ch["id"] != $id) {
	exit_html("�������� {$id}...");
}
$cur_p = $cur_ch["menu"];

// Ҫ�Ƚϵ�Ȩ��:
$ch_list = $db->query("select id,name,author,menu from $table where id!=$id", "id");

// ����Ƚ�:
$big = $small = array();
foreach ($ch_list as $_id => $_ch) {
	if (!check_power_in($_ch["menu"], $cur_p)) {
		//���������������ѡޡ�
		$big[] = '<font style="color:#ffa579;size:6px;font-family:Tahoma;">��</font> '.$_ch["name"]." (�����:".$_ch["author"].") (ID:".$_id.")";
	} else {
		$small[] = '<font style="color:#ffa579;size:6px;font-family:Tahoma;">��</font> '.$_ch["name"]." (�����:".$_ch["author"].") (ID:".$_id.")";
	}
}

$title = "�Ƚϡ�".$cur_ch["name"]."����Ȩ�޴�С";
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

<b>���� <?php echo $cur_ch["name"]; ?> ��Χ�ڵ�Ȩ�� (����ĳЩȨ��<?php echo $cur_ch["name"]; ?>û��)��</b>
<div style="margin-left:60px;">
<?php
if (count($big) > 0) {
	echo implode("<br>", $big);
} else {
	echo '<font color="gray">(û��)</font>';
}
?>
</div>
<br>

<b>�� <?php echo $cur_ch["name"]; ?> ��Χ�ڵ�Ȩ�� (��<?php echo $cur_ch["name"]; ?>��ȣ����߱�<?php echo $cur_ch["name"]; ?>��С)��</b>
<div style="margin-left:60px;">
<?php
if (count($small) > 0) {
	echo implode("<br>", $small);
} else {
	echo '<font color="gray">(û��)</font>';
}
?>
</div>
<br>

</body>
</html>