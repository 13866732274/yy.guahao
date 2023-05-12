<?php
// --------------------------------------------------------
// - 功能说明 : 渠道下拉列表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-10-18
// --------------------------------------------------------
require "lib/set_env.php";
$table = "dict_qudao";

check_power('', $pinfo) or exit("没有打开权限...");

if ($_GET["op"] == "pack") {
	$db->query("delete from $table where name='0'");
	echo '<script>alert("删除成功"); self.location = "qudao.php"; </script>';
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
* {font-family:"微软雅黑"; }
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
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td style="width:150px"><nobr class="tips">渠道列表</nobr></td>
		<td align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">添加</button>&nbsp;&nbsp;
<?php } ?>
			&nbsp; (如需添加大类请联系开发人员)
		</td>
		<td align="right" style="width:280px">
			<button onclick="self.location.reload()" class="button" title="">刷新</button>
		</td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<!-- 数据列表 begin -->
<form name="mainform">
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="left">名称</td>
		<td class="head" align="center">ID</td>
		<td class="head" align="center">优先度</td>
		<td class="head" align="center">添加时间</td>
		<td class="head" align="center">添加人</td>
		<td class="head" align="center" width="80">操作</td>
	</tr>

	<!-- 主要列表数据 begin -->
<?php
foreach ($guiji_arr as $gid => $gname) {
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item" colspan="6"><font color="red"><b><?php echo $gname; ?></b></font></td>
	</tr>
<?php

	// 查询:
	$data = $db->query("select * from $table where main_id=$gid order by sort desc, id asc");
	foreach ($data as $line) {
		$id = $line["id"];

		$op_button = '<a href="javascript:;" onclick="edit('.$id.', this);">修改</a>';

?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item">　　　　　　<?php echo $line["name"]; ?></td>
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
	<!-- 主要列表数据 end -->
</table>
</form>
<!-- 数据列表 end -->

<br>
<center><a href="?op=pack" title="删除后不能恢复，请慎重">[将名称为0的项目删除]</a></center>
<br>

</body>
</html>