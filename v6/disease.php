<?php
// --------------------------------------------------------
// - 功能说明 : 医院列表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-05-01 00:36
// --------------------------------------------------------
require "lib/set_env.php";
$table = "disease";

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
				del_data($db, $table, $_id, 1, "删除疾病“{name}”") ? $del_ok++ : $del_fail++;
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
	10=>array("title"=>"选", "width"=>"40", "align"=>"center"),
	11=>array("title"=>"ID", "width"=>"60", "align"=>"center", "sort"=>"id", "defaultorder"=>1),
	20=>array("title"=>"疾病名称", "width"=>"", "align"=>"left", "sort"=>"name", "defaultorder"=>1),
	25=>array("title"=>"二级疾病", "width"=>"", "align"=>"left", "sort"=>"disease_2", "defaultorder"=>1),
	30=>array("title"=>"治疗项目", "width"=>"", "align"=>"left", "sort"=>"xiangmu", "defaultorder"=>1),
	50=>array("title"=>"优先度", "width"=>"", "align"=>"center", "sort"=>"sort", "defaultorder"=>2),
	60=>array("title"=>"使用频率", "width"=>"", "align"=>"center", "sort"=>"", "defaultorder"=>2),
	90=>array("title"=>"添加时间", "width"=>"", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	99=>array("title"=>"操作", "width"=>"100", "align"=>"center"),
);


// 查询条件:
$where = array();
$where[] = "hospital_id=$user_hospital_id";
if ($searchword) {
	if (substr_count($searchword, "、") > 0) {
		$_arr = explode("、", $searchword);
		$where_2 = array();
		foreach ($_arr as $v) {
			$v = trim($v);
			if ($v != '') {
				$where_2[] = "name='{$v}'";
			}
		}
		$s = $where[] = "(".implode(" or ", $where_2).")";
		//echo $s;
	} else {
		$where[] = "(concat(name,' ',disease_2,' ',xiangmu)  like '%{$searchword}%')";
	}
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
	$sqlsort = "order by sort desc, id asc";
}

// 分页数据:
$pagesize = 9999;
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// 查询:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");

$hospital_id_name = $db->query("select id,name from hospital", 'id', 'name');


// 查询疾病对应的人数:
$did_num_arr = $db->query("select disease_id, count(disease_id) as num from patient_{$hid} group by disease_id", "disease_id", "num");

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

function add() {
	set_high_light('');
	parent.load_src(1,'disease_edit.php', 900, 550);
	return false;
}

function add_piliang() {
	set_high_light('');
	parent.load_src(1,'disease_add_piliang.php', 400, 500);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'disease_edit.php?id='+id, 900, 550);
	return false;
}
</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips"><?php echo $hospital_id_name[$user_hospital_id]; ?> - 疾病列表</nobr></td>
		<td class="header_cneter" align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">添加</button>&nbsp;
			<button onclick="add_piliang()" class="buttonb">批量添加</button>&nbsp;
			<button onclick="disease_hebing()" class="buttonb">合并疾病</button>&nbsp;
			<script type="text/javascript">
				function disease_hebing() {
					parent.load_src(1,'disease_hebing.php', 700, 350);
					return false;
				}
			</script>
<?php } ?>
		</td>
		<td class="headers_oprate" style="width:320px;"><form name="topform" method="GET"><nobr>关键词：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<button onclick="location='?'" class="search" title="退出条件查询">重置</button>&nbsp;<button onclick="self.location.reload()" class="button">刷新</button></nobr></form></td>
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
		<td class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>"><nobr><?php echo $tdtitle; ?></nobr></td>
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
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="center" class="item"><?php echo $line["id"]; ?></td>
		<td align="left" class="item"><?php echo $line["name"]; ?></td>
		<td align="left" class="item"><?php echo $line["disease_2"]; ?></td>
		<td align="left" class="item"><?php echo $line["xiangmu"]; ?></td>
		<td align="center" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo intval($did_num_arr[$id]); ?></td>
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
	<div class="footer_op_left">
		<button onclick="select_all()" class="button">全选</button>&nbsp;
		<button onclick="unselect()" class="button">反选</button>&nbsp;
		<?php echo show_button("hd", $pinfo, $pagepower); ?>&nbsp;
		<button onclick="show_selected_id();" class="buttonb">所选ID</button>
	</div>
	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
	<div class="clear"></div>
</div>
<script type="text/javascript">
function show_selected_id() {
	var s = get_select();
	byid("wee_ids").innerHTML = s;
}
</script>
<!-- 分页链接 end -->

<div id="wee_ids"></div>

</body>
</html>