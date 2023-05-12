<?php
// --------------------------------------------------------
// - 功能说明 : 登录错误记录
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-05-15 03:11
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_login_error";

check_power('', $pinfo) or exit("没有打开权限...");

// 操作的处理:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		$id = intval($_GET["id"]);
		$db->query("delete from $table where id=$id limit 1");

		echo 'self.location.reload()';
		exit;
	}

	if ($op == "delete_old") {
		if ($debug_mode || $username == 'admin') {
			$time = time()-7*24*3600;
			$db->query("delete from $table where addtime<$time");
			msg_box("旧记录数据被成功删除", "back", 1);
		}
	}

	if ($op == "clear") {
		if ($debug_mode) {
			$db->query("truncate table `$table`");
			msg_box("数据被成功清除..", "back", 1);
		}
	}
}


// 更新数据 2011-12-10
$db->query("update $table l, sys_admin u set l.realname=u.realname where l.realname='' and l.tryname=u.name");


// 定义当前页需要用到的调用参数:
$aLinkInfo = array(
	"page" => "page",
	"sortid" => "sort",
	"sorttype" => "sorttype",
	"searchword" => "searchword",
	"name" => "name",
);

// 读取页面调用参数:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// 定义单元格格式:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	0=>array("title"=>"选", "width"=>"4%", "align"=>"center"),
	2=>array("title"=>"尝试姓名", "width"=>"", "align"=>"center", "sort"=>"binary tryname", "defaultorder"=>1),
	3=>array("title"=>"尝试密码", "width"=>"", "align"=>"center", "sort"=>"binary trypass", "defaultorder"=>2),
	4=>array("title"=>"操作者IP", "width"=>"", "align"=>"center", "sort"=>"binary userip", "defaultorder"=>2),
	8=>array("title"=>"姓名对应实名", "width"=>"", "align"=>"center"),
	5=>array("title"=>"时间", "width"=>"15%", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	7=>array("title"=>"操作", "width"=>"10%", "align"=>"center"),
);

// 默认排序方式:
$defaultsort = 5;
$defaultorder = 2;


// 查询条件:
$where = array();
if ($name) {
	$where[] = "tryname='".$name."'";
}
if ($searchword) {
	$where[] = "(binary tryname like '%{$searchword}%' or binary trypass like '%{$searchword}%')";
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

// 分页数据:
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// sql查询:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset, $pagesize");
if (!is_array($data)) {
	exit("<b>数据库sql查询语句出错，请联系管理员检查：</b><br>".$db->sql);
}


// 查询错误最多的姓名:
$t_begin = strtotime("-1 month");
$top_list = $db->query("select tryname,count(tryname) as c from $table where addtime>$t_begin group by tryname order by c desc limit 30", "tryname", "c");

?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips">用户名密码错误记录</nobr></td>
		<td class="header_center">
<?php if ($debug_mode) { ?>
维护：<a href="?op=delete_old" onclick="return confirm('您确定要删除这些数据吗？')">删除一周之前的数据</a>
	<?php if ($debug_mode) { ?>
	&nbsp;<a href="?op=clear" onclick="return confirm('确定要清空吗？')">清空</a>
	<?php } ?>
<?php } ?>
		</td>
		<td class="headers_oprate"><form name="topform" method="GET"><nobr>关键词：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onclick="location='?'" class="search" title="退出条件查询">重置</button>&nbsp;<button onclick="self.location.reload()" class="button">刷新</button></nobr></form></td>
	</tr>
</table>
<!-- 头部 end -->

<!-- 错误最严重的用户 begin -->
<div class="space"></div>
<div style="border:2px solid #fdb53d; background:#fefff7; padding:5px; ">
	<div><b style="color:#6d8300; ">一个月内错误最多的用户：</b></div>
	<div style="margin-left:50px; margin-top:5px;">
<?php foreach ($top_list as $k => $v) { ?>
		<a href="?name=<?php echo urlencode($k); ?>"><b><?php echo $k; ?></b><font color="gray">(<?php echo $v; ?>)</font></a>&nbsp;
<?php } ?>
	</div>
</div>


<!-- 数据列表 begin -->
<div class="space"></div>
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
		if ($debug_mode || check_power("d", $pinfo, $pagepower)) {
			$op[] = '<a href="javascript:;" onclick="delete_line('.$id.', this);">删除</a>';
			$can_delete = 1;
		}
		$op_button = implode("&nbsp;", $op);
?>
	<tr>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="center" class="item"><?php echo $line["tryname"]; ?></td>
		<td align="center" class="item"><?php echo $line["trypass"]; ?></td>
		<td align="center" class="item"><?php echo $line["userip"]; ?></td>
		<td align="center" class="item"><?php echo $line["realname"]; ?></td>
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

<?php if ($can_delete) { ?>
<script type="text/javascript">
function delete_line(line_id, obj) {
	if (confirm("删除后不能恢复，是否确定删除？")) {
		load_js("?op=delete&id="+line_id);
	}
}
</script>
<?php } ?>

</body>
</html>