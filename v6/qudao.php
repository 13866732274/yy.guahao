<?php
// --------------------------------------------------------
// - ����˵�� : ���������б�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2014-10-18
// --------------------------------------------------------
require "lib/set_env.php";
$table = "dict_qudao";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

if ($_GET["op"] == "pack") {
	$db->query("delete from $table where name='0'");
	echo '<script>alert("ɾ���ɹ�"); self.location = "qudao.php"; </script>';
	exit;
}

?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/sorttable_keep.js" language="javascript"></script>
<style>
* {font-family:"΢���ź�"; }
td {line-height:20px;  }
</style>
<script language="javascript">
function add() {
	set_high_light('');
	parent.load_src(1,'qudao_edit.php', 700, 500);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'qudao_edit.php?id='+id, 700, 500);
	return false;
}

</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td style="width:150px"><nobr class="tips">�����б�</nobr></td>
		<td align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">���</button>&nbsp;&nbsp;
<?php } ?>
			&nbsp; (������Ӵ�������ϵ������Ա)
		</td>
		<td align="right" style="width:280px">
			<button onclick="self.location.reload()" class="button" title="">ˢ��</button>
		</td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<!-- �����б� begin -->
<form name="mainform">
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="left">����</td>
		<td class="head" align="center">ID</td>
		<td class="head" align="center">���ȶ�</td>
		<td class="head" align="center">���ʱ��</td>
		<td class="head" align="center">�����</td>
		<td class="head" align="center" width="80">����</td>
	</tr>

	<!-- ��Ҫ�б����� begin -->
<?php
foreach ($guiji_arr as $gid => $gname) {
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item" colspan="6"><font color="red"><b><?php echo $gname; ?></b></font></td>
	</tr>
<?php

	// ��ѯ:
	$data = $db->query("select * from $table where main_id=$gid order by sort desc, id asc");
	foreach ($data as $line) {
		$id = $line["id"];

		$op_button = '<a href="javascript:;" onclick="edit('.$id.', this);">�޸�</a>';

?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item">������������<?php echo $line["name"]; ?></td>
		<td align="center" class="item"><?php echo $line["id"]; ?></td>
		<td align="center" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
		<td align="center" class="item"><?php echo $line["author"]; ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
	</tr>
<?php
	}
}
?>
	<!-- ��Ҫ�б����� end -->
</table>
</form>
<!-- �����б� end -->

<br>
<center><a href="?op=pack" title="ɾ�����ָܻ���������">[������Ϊ0����Ŀɾ��]</a></center>
<br>

</body>
</html>