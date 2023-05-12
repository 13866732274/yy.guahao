<?php
// --------------------------------------------------------
// - 功能说明 : 网站列表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-5-10
// --------------------------------------------------------
require "lib/set_env.php";
$table = "site_list";

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

if (!($config["show_site"] || $debug_mode)) {
	exit("对不起，您没有权限~");
}

$op = $_GET["op"];

// 操作的处理:
if ($op == "delete") {
	$id_arr = explode(",", $_GET["id"]);
	foreach ($id_arr as $id) {
		$id = intval($id);
		$db->query("delete from $table where hid=$hid and id=$id limit 1");
	}
	msg_box("删除成功", "back", 1);
}

if ($op == "update_out_date") {
	$cache_arr = array();
	$list = $db->query("select * from site_list where hid=$hid order by sort desc, id asc", "id");
	foreach ($list as $lid => $li) {
		$url = _get_just_domain($li["site_url"]);
		if (array_key_exists($url, $cache_arr)) {
			$t = $cache_arr[$url];
		} else {
			$t = trim(get_domain_out_date($url));
			$cache_arr[$url] = $t;
		}
		$sql = "update site_list set `out_date`='$t' where id=$lid limit 1";
		$db->query($sql);
	}
	echo '<script>self.location = "site.php";</script>';
	exit;
}


if ($op == "delete_all") {
	if ($debug_mode) {
		//$db->query("delete from site_list where hid=$hid");
		//$num = mysql_affected_rows();
		//echo '<script> alert("成功删除了 '.$num.' 条数据。"); self.location = "?"; </script>';
	}
	exit;
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
	1=>array("title"=>"ID", "width"=>"", "align"=>"center", "sort"=>"id", "defaultorder"=>1),
	2=>array("title"=>"类型", "width"=>"", "align"=>"left", "sort"=>"type_name", "defaultorder"=>1),
	3=>array("title"=>"网址", "width"=>"", "align"=>"left", "sort"=>"site_url", "defaultorder"=>1),
	9=>array("title"=>"到期时间", "width"=>"", "align"=>"center", "sort"=>"out_date", "defaultorder"=>1),
	11=>array("title"=>"状态", "width"=>"", "align"=>"center", "sort"=>"out_date", "defaultorder"=>1),
	13=>array("title"=>"备案号", "width"=>"", "align"=>"center", "sort"=>"beian_num", "defaultorder"=>1),
	12=>array("title"=>"whois查询", "width"=>"", "align"=>"center", "sort"=>"", "defaultorder"=>0),
	10=>array("title"=>"备注", "width"=>"", "align"=>"center", "sort"=>"memo", "defaultorder"=>1),
	4=>array("title"=>"优先度", "width"=>"", "align"=>"left", "sort"=>"sort", "defaultorder"=>2),
	5=>array("title"=>"添加时间", "width"=>"", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	6=>array("title"=>"添加人", "width"=>"", "align"=>"center", "sort"=>"author", "defaultorder"=>2),
	7=>array("title"=>"操作", "width"=>"", "align"=>"center"),
);

// 默认排序方式:
$defaultsort = 9;
$defaultorder = 1;

// 查询条件:
$where = array();
$where[] = "hid=$user_hospital_id";
if ($searchword) {
	$where[] = "(concat(type_name, site_url, author, memo) like '%{$searchword}%')";
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
	$sqlsort = "order by out_date asc, id asc";
}

// 分页数据:
$pagesize = 100;
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// 查询:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");

$hospital_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");



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

.tip_out_date {color:blue; }
.tip_will_out_date {background:#ff359a; border:1px solid #c40062; color:yellow; padding:4px 3px 2px 3px; }
.tip_normal {}
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
	parent.load_src(1,'site_edit.php', 900, 550);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'site_edit.php?id='+id, 900, 550);
	return false;
}

function update_out_date(ym_id) {
	var url = "http/ym_update_out_date.php?ym_id="+ym_id+"&do=update_out_date_do";
	load_js(url, "ym_update_out_date"+ym_id);
	byid("outdate_"+ym_id).innerHTML = "查询中";
}

function update_out_date_do(res) {
	if (res && res["status"] == 'ok') {
		byid("outdate_"+res["ym_id"]).innerHTML = res["out_date"];
	} else {
		alert("更新失败...");
	}
}

function confirm_delete() {
	if (!confirm("删除后不能恢复，是否确定要删除？")) {
		return false;
	}
}

function piliang_update_out_date() {
	if (!confirm("请注意：此操作将花费较长时间，开始后，请不要刷新页面，坐等所有域名更新完成。是否确定继续？")) {
		return false;
	}
	var delay = 0;
	var g = document.getElementsByTagName("A");
	if (g.length > 0) {
		for (var i=0; i<g.length; i++) {
			if (g[i].id && g[i].id.split("_")[0] == "outdate") {
				setTimeout("update_out_date("+g[i].id.split("_")[1]+")", delay);
				delay += 500;
			}
		}
	}
}

</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips"><?php echo $hospital_name; ?> - 网站列表</nobr></td>
		<td class="header_cneter" align="center">
			<button onclick="add()" class="button">添加</button>&nbsp;
			<a href="javascript:;" onclick="piliang_update_out_date();"><b>[批量更新本页域名到期时间]</b></a>&nbsp;
		</td>
		<td class="headers_oprate" style="width:300px;">
			<form name="topform" method="GET">
				关键词：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<a href="?" title="退出条件查询">重置</a>&nbsp;
				<button onclick="self.location.reload();" class="button" title="点击刷新页面">刷新</button>
			</form>
		</td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<!-- 数据列表 begin -->
<form name="mainform">
<table width="100%" align="center" class="list" id="main_table">
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

	$dt0 = date("Ymd");
	$dt1 = date("Ymd", strtotime("+6 month"));

	foreach ($data as $line) {
		$id = $line["id"];

		$_dt = date("Ymd", strtotime($line["out_date"]));
		if (trim($line["out_date"]) == '') {
			$out_status = '待更新';
		} else if ($_dt <= 19700101) {
			$out_status = '请手工查询';
		} else {
			if ($_dt < $dt0) {
				$out_status = '<span class="tip_out_date">已过期</span>';
			} else if ($_dt < $dt1) {
				$out_status = '<span class="tip_will_out_date">即将过期</span>';
			} else {
				$out_status = '<span class="tip_normal">正常</span>';
			}
		}


		$op = array();
		$op[] = "<button class='button_op' onclick='edit(".$id.", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='修改' alt=''></button>";

		$can_delete_this_line = 0;
		if ($line["author"] == $realname || $username == "王建洲" || $debug_mode) {
			$can_delete_this_line = 1;
			$op[] = "<a href='?op=delete&id=$id' onclick='return confirm_delete()'>删除</a>";
		}
		$op_button = implode("&nbsp;", $op);

		if ($line["auto_update"] < 1) {
			$out_date = '<b style="color:green" title="人工填写">'.$line["out_date"].'</b>';
		} else {
			$out_date = $line["out_date"] ? $line["out_date"] : "查询";
			$out_date = '<a href="javascript:;" onclick="update_out_date('.$line["id"].')" id="outdate_'.$line["id"].'" title="点击更新到期时间">'.$out_date.'</a>';
		}
?>
	<tr>
		<td align="center" class="item"><?php if ($can_delete_this_line) { ?><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"><?php } ?></td>
		<td align="center" class="item"><?php echo $line["id"]; ?></td>
		<td align="left" class="item"><?php echo $line["type_name"]; ?></td>
		<td align="left" class="item"><a href="http://<?php echo $line["site_url"]; ?>/" target="_blank" title="点击新窗口打开"><?php echo $line["site_url"]; ?></a></td>
		<td align="center" class="item"><?php echo $out_date; ?></td>
		<td align="center" class="item"><?php echo $out_status; ?></td>
		<td align="center" class="item"><?php echo $line["beian_num"] == "" ? "　" : $line["beian_num"]; ?></td>
		<td align="center" class="item"><a href="http://whois.chinaz.com/<?php echo $line["site_url"]; ?>" target="_blank">查看</a></td>
		<td align="left" class="item"><?php echo $line["memo"]; ?></td>
		<td align="left" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
		<td align="center" class="item"><?php echo $line["author"]; ?></td>
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
<table width="100%">
	<tr>
		<td align="left" style="width:300px;">
			<button onclick="select_all()" class="button">全选</button>&nbsp;
			<button onclick="unselect()" class="button">反选</button>&nbsp;&nbsp;&nbsp;
			<button onclick="del()" class="buttonb">删除所选</button>
		</td>
		<td align="center">
<?php if (0) { ?>
			<a href="?op=delete_all" onclick="return confirm('删除后真的不能恢复，一定要慎重啊。是否确定？')"><b>[管理员模式:当前科室域名全部删除]</b></a>
<?php } ?>
		</td>
		<td align="right" class="footer_op_right" style="width:300px;">
			<?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?>
		</td>
	</tr>
</table>
<!-- 分页链接 end -->


<?php if ($searchword) { ?>
<!-- 关键词高亮 -->
<script>
highlightWord(byid("main_table"), "<?php echo $searchword; ?>");
</script>
<?php } ?>


</body>
</html>

