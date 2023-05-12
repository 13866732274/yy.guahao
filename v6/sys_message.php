<?php
// --------------------------------------------------------
// - 功能说明 : sys_message_list
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-04-28 21:05
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_message";

check_power('', $pinfo) or exit("没有打开权限...");

// 操作的处理:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		$ids = explode(",", $_GET["id"]);
		$del_fail = $del_ok = 0;
		foreach ($ids as $_id) {
			$_id = intval($_id);
			if ($_id > 0) {
				del_data($db, $table, $_id, 1, "删除聊天记录“{id}”") ? $del_ok++ : $del_fail++;
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
	0=>array("title"=>"选", "width"=>"4%", "align"=>"center"),
	1=>array("title"=>"状态", "width"=>"5%", "align"=>"center"),
	2=>array("title"=>"发送人", "width"=>"8%", "align"=>"center", "sort"=>"binary fromname", "defaultorder"=>1),
	3=>array("title"=>"接收人", "width"=>"8%", "align"=>"center", "sort"=>"binary toname", "defaultorder"=>1),
	4=>array("title"=>"消息内容", "width"=>"45%", "align"=>"center", "sort"=>"binary content", "defaultorder"=>1),
	5=>array("title"=>"时间", "width"=>"20%", "align"=>"center", "sort"=>"addtime", "defaultorder"=>1),
	7=>array("title"=>"操作", "width"=>"10%", "align"=>"center"),
);

// 默认排序方式:
$defaultsort = 5;
$defaultorder = 2;


// 查询条件:
$where = array();
$where[] = "(binary fromname='{$username}' or binary toname='{$username}')";
if ($searchword) {
	$where[] = "(binary content like '%{$searchword}%')";
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
		$sqlsort = "order by flag1 desc,".$aTdFormat[$defaultsort]["sort"]." ".$aOrderType[$defaultorder];
	} else {
		$sqlsort = "";
	}
}

// 分页数据:
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// sql查询:
$data = $db->query("select *,if(readtime=0 and toname='$username',1,0) as flag1 from $table $sqlwhere $sqlsort limit $offset, $pagesize");
if (!is_array($data)) {
	exit("<b>数据库sql查询语句出错，请联系管理员检查：</b><br>".$db->sql);
}

// 登录名 - 真实姓名 对照数据：
$name_2_nick = $db->query("select name,realname from sys_admin", "name", "realname");
?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript"></script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips">消息列表</nobr></td>
		<td class="headers_oprate"><form name="topform" method="GET"><nobr><?php echo show_button("i", $pinfo, $pagepower); ?>&nbsp;&nbsp;&nbsp;&nbsp;关键词：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onclick="location='?'" class="search" title="退出条件查询">重置</button>&nbsp;<button onclick="self.location.reload()" class="button">刷新</button></nobr></form></td>
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
		$line["content"] = face_show($line["content"]);

		$op = array();
		if (check_power("v", $pinfo, $pagepower)) {
			$op[] = "<a href='".$pinfo["viewpage"]."?id=$id' class='op'><img src='image/b_detail.gif' align='absmiddle' title='查看' alt=''></a>";
		}
		if (check_power("d", $pinfo, $pagepower)) {
			$op[] = "<a href='?op=delete&id=$id' onclick='return isdel()' class='op'><img src='image/b_delete.gif' align='absmiddle' title='删除' alt=''></a>";
		}
		$op_button = implode("&nbsp;", $op);

		$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="center" class="item"><?php echo $line["flag1"] > 0 ? "<b><font color=red>新！</font></b>" : "-"; ?></td>
		<td align="center" class="item"><?php echo $name_2_nick[$line["fromname"]]; ?></td>
		<td align="center" class="item"><?php echo $name_2_nick[$line["toname"]]; ?></td>
		<td align="left" class="item"><?php echo $line["link"] ? ('<a href="'.$line["link"].'">'.$line["content"].'</a>') : $line["content"]; ?></td>
		<td align="center" class="item"><?php echo date("Y-m-d H:i:s", $line["addtime"]); ?></td>
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