<?php
// --------------------------------------------------------
// - 功能说明 : 医院分组列表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-4-16
// --------------------------------------------------------
require "lib/set_env.php";
$table = "hospital_group";

check_power('', $pinfo) or exit("没有打开权限...");

// 操作的处理:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		check_power("d", $pinfo, $pagepower) or exit("没有删除权限...");

		$ids = explode(",", $_GET["id"]);
		$del_fail = $del_ok = 0;
		foreach ($ids as $_id) {
			$_id = intval($_id);
			if ($_id > 0) {
				del_data($db, $table, $_id, 1, "删除分组“{name}”") ? $del_ok++ : $del_fail++;
			}
		}

		if ($del_fail > 0) {
			msg_box("删除成功 $del_ok 条资料，删除失败 $del_fail 条资料。", "back", 1);
		} else {
			msg_box("删除成功", "back", 1);
		}
	}
}

// 定义当前页需要用到的调用参数:
$aLinkInfo = array("searchword" => "searchword");

// 读取页面调用参数:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// 查询条件:
$where = array();
if ($searchword) {
	$where[] = "(binary name like '%{$searchword}%')";
}
$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

// 查询:
$data = $db->query("select * from $table $sqlwhere order by sort desc, name asc");

// 使用数量统计
$use_count_arr = $db->query("select group_id, count(group_id) as c from hospital group by group_id", "group_id", "c");

?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/sorttable_keep.js" language="javascript"></script>
<style>
.column_sortable {color:blue !important; cursor:pointer; font-family:"微软雅黑"; }
.sorttable_nosort {color:gray; }
.tr_high_light td {background:#FFE1D2; }
</style>
<script language="javascript">
window.last_high_obj = '';
function set_high_light(obj) {
	if (last_high_obj) {
		last_high_obj.parentNode.parentNode.className = "";
	}
	if (obj) {
		obj.parentNode.parentNode.className = "tr_high_light";
		last_high_obj = obj;
	} else {
		last_high_obj = '';
	}
}

function add() {
	set_high_light('');
	parent.load_src(1,'hospital_group_edit.php', 900, 550);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'hospital_group_edit.php?id='+id, 900, 550);
	return false;
}

function apply() {
	parent.load_src(1,'hospital_group.php?op=apply', 700, 300);
	return false;
}

</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td style="width:150px"><nobr class="tips">医院分组管理</nobr></td>
		<td align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">添加</button>&nbsp;&nbsp;
<?php } ?>
			<button onclick="apply()" class="button" title="修改分组名称后，请点此按钮更新医院表">应用</button>
		</td>
		<td align="right" style="width:280px"><form name="topform" method="GET"><nobr>关键词：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onclick="location='?'" class="search" title="退出条件查询">重置</button>&nbsp;<button onclick="self.location.reload()" class="button">刷新</button></nobr></form></td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<!-- 数据列表 begin -->
<form name="mainform">
<table width="100%" align="center" class="list sortable" id="table_hospital">
	<tr>
		<td class="head column_sortable" title="点击可排序" align="center">ID</td>
		<td class="head column_sortable" title="点击可排序" align="left">分组名称</td>
		<td class="head" align="left">使用次数</td>
		<td class="head column_sortable" title="点击可排序" align="left">优先度</td>
		<td class="head sorttable_nosort" align="center" width="80">操作</td>
	</tr>

	<!-- 主要列表数据 begin -->
<?php
if (count($data) > 0) {
	foreach ($data as $line) {
		$id = $line["id"];
		$used_count = @intval($use_count_arr[$id]);

		$op = array();
		if (check_power("e", $pinfo, $pagepower)) {
			$op[] = "<button class='button_op' onclick='edit(".$id.", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='修改' alt=''></button>";
		}
		if (check_power("d", $pinfo, $pagepower)) {
			if ($used_count == 0) {
				$op[] = '<a href="?op=delete&id=$id" onclick="return isdel()">删除</a>';
			}
		}
		$op_button = implode("&nbsp;", $op);

?>
	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="center" class="item"><?php echo $line["id"]; ?></td>
		<td align="left" class="item"><?php echo $line["name"]; ?></td>
		<td align="left" class="item"><?php echo $used_count; ?></td>
		<td align="left" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
	</tr>
<?php
	}
} else {
?>
	<tr>
		<td colspan="5" align="center" class="nodata">(没有数据...)</td>
	</tr>
<?php } ?>
	<!-- 主要列表数据 end -->
</table>
</form>
<!-- 数据列表 end -->

<div class="space"></div>

<!-- 分页链接 begin -->
<div class="footer_op">
	<div class="footer_op_left"><button onclick="select_all()" class="button">全选</button>&nbsp;<button onclick="unselect()" class="button">反选</button></div>
	<div class="footer_op_right">共有 <b><?php echo count($data); ?></b> 条数据&nbsp;</div>
	<div class="clear"></div>
</div>
<!-- 分页链接 end -->

</body>
</html>