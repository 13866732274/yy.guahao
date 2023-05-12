<?php
// --------------------------------------------------------
// - 功能说明 : 媒体类型
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-03 14:47
// --------------------------------------------------------
require "lib/set_env.php";
$table = "media";

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

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
				del_data($db, $table, $_id, 1, "删除媒体来源“{name}”") ? $del_ok++ : $del_fail++;
			}
		}
		if ($del_fail > 0) {
			msg_box("删除成功 $del_ok 条资料，删除失败 $del_fail 条资料。", "back", 1);
		} else {
			msg_box("删除成功", "back", 1);
		}
	}
}

//user_op_log("打开媒体来源");

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
	0=>array("title"=>"选", "width"=>"50", "align"=>"center"),
	6=>array("title"=>"类型", "width"=>"80", "align"=>"center", "sort"=>"hospital_id", "defaultorder"=>1),
	5=>array("title"=>"排序", "width"=>"80", "align"=>"center", "sort"=>"sort", "defaultorder"=>2),
	1=>array("title"=>"名称", "width"=>"", "align"=>"left", "sort"=>"binary name", "defaultorder"=>1),
	9=>array("title"=>"统计", "width"=>"", "align"=>"center"),
	3=>array("title"=>"添加时间", "width"=>"120", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	4=>array("title"=>"操作", "width"=>"80", "align"=>"center"),
);

// 默认排序方式:
$defaultsort = 5;
$defaultorder = 2;


// 查询条件:
$where = array();
$where[] = "(hospital_id=0 or hospital_id=".$hid.")";
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

// 医院名:
$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, 'name');

// 统计数据：
$t_from = strtotime("-3 month");
$count_data_arr = $db->query("select media_from, count(media_from) as c from patient_{$hid} where addtime>$t_from group by media_from", "media_from", "c");


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

function add(hid) {
	set_high_light('');
	parent.load_src(1,'media_edit.php?hid='+hid, 700, 300);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'media_edit.php?id='+id, 700, 300);
	return false;
}
</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips">媒体来源</nobr></td>
		<td class="header_cneter" align="center">
<?php
if (check_power("i", $pinfo, $pagepower)) {
	echo '<a href="javascript:void(0);" onclick="add(0)"><b>添加全局媒体来源</b></a>&nbsp;|&nbsp;<a href="javascript:void(0);" onclick="add('.$hid.')"><b>添加“'.$h_name.'”媒体来源(私有)</b></a>';
}
?>
		</td>
		<td class="headers_oprate" style="width:280px;"><form name="topform" method="GET"><nobr>关键词：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onclick="location='?'" class="search" title="退出条件查询">重置</button>&nbsp;<button onclick="self.location.reload()" class="button">刷新</button></nobr></form></td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<div class="description">
	<div class="d_title">“全局”为各医院通用，请勿随意修改和删除；“私有”只被当前医院使用。 “统计”为3个月内的使用频率</div>
</div>

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
		if (check_power("e", $pinfo, $pagepower)) {
			$op[] = "<button class='button_op' onclick='edit(".$id.", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='修改' alt=''></button>";
		}
		if (check_power("d", $pinfo, $pagepower)) {
			$op[] = "<button class='button_op' id='?op=delete&id=$id' onclick='if (isdel()) location=this.id;'><img src='image/b_delete.gif' align='absmiddle' title='删除'></button>";
		}
		$op_button = implode("&nbsp;", $op);

		$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;

		if ($line["hospital_id"] != 0) {
			$tr_style = 'color:blue;';
		} else {
			$tr_style = 'color:red;';
		}
?>
	<tr class="<?php echo $tr_class; ?>" style="<?php echo $tr_style; ?>">
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="center" class="item"><?php echo $line["hospital_id"] == 0 ? "全局" : "私有"; ?></td>
		<td align="center" class="item"><?php echo $line["sort"]; ?></td>
		<td align="left" class="item"><?php echo $line["name"]; ?></td>
		<td align="center" class="item"><?php echo $count_data_arr[$line["name"]]; ?></td>
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
	<div class="footer_op_left"><button onclick="select_all()" class="button">全选</button>&nbsp;<button onclick="unselect()" class="button">反选</button>&nbsp;<?php echo show_button("hd", $pinfo, $pagepower); ?></div>
	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
	<div class="clear"></div>
</div>
<!-- 分页链接 end -->

</body>
</html>