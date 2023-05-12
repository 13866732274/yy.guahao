<?php
// --------------------------------------------------------
// - 功能说明 : 统计 项目 管理
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2010-10-13 11:34
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_type";

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
				del_data($db, $table, $_id, 1, "删除电话项目“{name}”") ? $del_ok++ : $del_fail++;
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
$aLinkInfo = array(
	"page" => "page",
	"sortid" => "sort",
	"sorttype" => "sorttype",
	"searchword" => "searchword",
);

// 读取页面调用参数:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// 定义单元格格式:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	0=>array("title"=>"选", "width"=>"32", "align"=>"center"),
	1=>array("title"=>"项目名称", "align"=>"left", "sort"=>"binary name", "defaultorder"=>1),
	6=>array("title"=>"所属医院", "align"=>"left", "sort"=>"binary h_name", "defaultorder"=>1),
	2=>array("title"=>"客服", "align"=>"left", "sort"=>"kefu", "defaultorder"=>1),
	3=>array("title"=>"添加时间", "width"=>"", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	4=>array("title"=>"操作", "width"=>"60", "align"=>"center"),
);

// 默认排序方式:
$defaultsort = 3;
$defaultorder = 1;


// 查询条件:
$where = array();

$where[] = "type='tel'";

if ($searchword) {
	$where[] = "(binary name like '%{$searchword}%')";
}
$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

// 对排序的处理：
if ($sortid > 0) {
	$sqlsort = "order by ".$aTdFormat[$sortid]["sort"]." ";
	if ($sorttype > 0) {
		$sqlsort .= $aOrderType[$sorttype];
	} else {
		$sqlsort .= $aOrderType[$aTdFormat[$sortid]["defaultorder"]];
	}
} else {
	if ($defaultsort > 0 && array_key_exists($defaultsort, $aTdFormat)) {
		$sqlsort = "order by ".$aTdFormat[$defaultsort]["sort"]." ".$aOrderType[$defaultorder];
	} else {
		$sqlsort = "";
	}
}
//$sqlsort = "order by hospital, id asc";

// 分页数据:
$pagesize = 9999;
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// 查询:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");

$admin_id_name = $db->query("select id,realname from sys_admin where isshow=1", "id", "realname");

// 页面开始 ------------------------
?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
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

function del_confirm() {
	return confirm("删除后不可恢复，但对应的统计数据不会删除。是否确定继续？");
}

function add() {
	set_high_light('');
	parent.load_src(1,'count_tel_type_add.php', 800, 500);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'count_tel_type_edit.php?id='+id, 800, 500);
	return false;
}

function set_data_hids(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'count_type_set_hids.php?id='+id, 600, 500);
	return false;
}
</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips">医院项目管理(电话)</nobr></td>
		<td class="headerd_cneter" align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">添加</button>
<?php } ?>
		</td>
		<td class="headers_oprate" style="width:280px;"><form name="topform" method="GET"><nobr><nobr>关键词：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onclick="location='?'" class="search" title="退出条件查询">重置</button>&nbsp;<button onclick="self.location.reload()" class="button">刷新</button></nobr></nobr></form></td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<!-- 数据列表 begin -->
<form name="mainform">
<table width="100%" align="center" class="list">
	<!-- 表头定义 begin -->
	<tr>
<?php
// 表头处理:
foreach ($aTdFormat as $tdid => $tdinfo) {
	list($tdalign, $tdwidth, $tdtitle) = make_td_head($tdid, $tdinfo);
?>
		<td class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>"><?php echo $tdtitle; ?></td>
<?php } ?>
	</tr>
	<!-- 表头定义 end -->

	<!-- 主要列表数据 begin -->
<?php
if (count($data) > 0) {
	foreach ($data as $line) {
		$id = $line["id"];

		$op = array();
		$op[] = "<a href='javascript:;' onclick='edit(".$id.", this); return false;'>修改</a>";
		$op[] = "<a href='javascript:;' onclick='set_data_hids(".$id.", this); return false;'>设置调用科室</a>";

		if (check_power("d", $pinfo, $pagepower)) {
			$op[] = "<a href='?op=delete&id=$id' onclick='return del_confirm();'>删除</a>";
		}

		$op_button = implode("&nbsp;", $op);

		$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="left" class="item"><b><?php echo $line["name"]; ?></b></td>
		<td align="left" class="item"><?php echo $line["h_name"]; ?></td>
		<td align="left" class="item"><?php echo $line["kefu"]; ?></td>
		<td align="center" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
	</tr>
<?php
	}
} else {
?>
	<tr>
		<td colspan="<?php echo count($aTdFormat); ?>" align="center" class="nodata">(没有数据...)</td>
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
	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
	<div class="clear"></div>
</div>
<!-- 分页链接 end -->

</body>
</html>