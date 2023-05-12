<?php
// --------------------------------------------------------
// - 功能说明 : module.php
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-05-13 18:46
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_menu";
check_power('', $pinfo) or exit("没有打开权限...");

// 操作的处理:
if ($op == "delete") {
	header('Content-type: text/javascript');
	if ($id > 0) {
		$crc = intval($_GET["crc"]);
		$db->query("delete from $table where id=$id and addtime=$crc limit 1");
	}
	echo "parent.msg_box('删除成功'); self.location.reload(); ";
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
	0 => array("title" => "ID", "width" => "4%", "align" => "center"),
	1 => array("title" => "排序", "width" => "5%", "align" => "center", "sort" => "", "defaultorder" => 1),
	2 => array("title" => "组号", "width" => "5%", "align" => "center", "sort" => "", "defaultorder" => 1),
	3 => array("title" => "顶层?", "width" => "6%", "align" => "center", "sort" => "", "defaultorder" => 2),
	4 => array("title" => "标题", "width" => "15%", "align" => "left", "sort" => "", "defaultorder" => 1),
	5 => array("title" => "链接", "width" => "25%", "align" => "left", "sort" => "", "defaultorder" => 1),
	6 => array("title" => "功能说明", "width" => "30%", "align" => "left", "sort" => "", "defaultorder" => 1),
	7 => array("title" => "操作", "width" => "10%", "align" => "center"),
);

// 默认排序方式:
$defaultsort = 0;
$defaultorder = 0;


// 查询条件:
$where = array();
if ($searchword) {
	$where[] = "(binary t.title like '%{$searchword}%' or binary t.link like '%{$searchword}%' or binary tips like '%{$searchword}%')";
}
$sqlwhere = count($where) > 0 ? ("where " . implode(" and ", $where)) : "";

// 对排序的处理：
if ($sortid > 0) {
	$sqlsort = "order by " . $aTdFormat[$sortid]["sort"] . " ";
	if ($sorttype > 0) {
		$sqlsort .= $aOrderType[$sorttype];
	} else {
		$sqlsort .= $aOrderType[$aTdFormat[$sortid]["defaultorder"]];
	}
} else {
	if ($defaultsort > 0 && array_key_exists($defaultsort, $aTdFormat)) {
		$sqlsort = "order by " . $aTdFormat[$defaultsort]["sort"] . " " . $aOrderType[$defaultorder];
	} else {
		$sqlsort = "";
	}
}
//$sqlsort = "order by type desc, sort asc";

// 分页数据:
$pagesize = 9999;
$count = $db->query_count("select count(*) from $table");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// sql查询:
$data = array();
$tm = $db->query("select * from $table where type=1 order by sort asc,id asc");
foreach ($tm as $tml) {
	$data[] = $tml;
	$tm2 = $db->query("select * from $table where mid=" . $tml["mid"] . " and type=0 order by sort asc, id asc");
	foreach ($tm2 as $tml2) {
		$data[] = $tml2;
	}
}
?>
<html>

<head>
    <title><?php echo $pinfo["title"]; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <script language="javascript">
    function add() {
        set_high_light('');
        parent.load_src(1, 'sys_menu_edit.php', 1000, 600);
        return false;
    }

    function edit(id, obj) {
        set_high_light(obj);
        parent.load_src(1, 'sys_menu_edit.php?id=' + id, 1000, 600);
        return false;
    }

    function delete_line(id, crc) {
        if (confirm("删除后不能恢复，确定要删除该条资料吗？")) {
            load_js("?op=delete&id=" + id + "&crc=" + crc, "delete_line");
        }
    }
    </script>
</head>

<body>
    <!-- 头部 begin -->
    <table class="headers" width="100%">
        <tr>
            <td class="headers_title">
                <nobr class="tips">菜单模块列表</nobr>
            </td>
            <td class="header_center">
                <?php if (check_power("i", $pinfo, $pagepower)) { ?>
                <button onclick="add()" class="button">添加</button>
                <?php } ?>
            </td>
            <td class="headers_oprate">
                <form name="topform" method="GET">
                    <nobr>关键词：<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input"
                            size="15">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold"
                            title="点击搜索">&nbsp;<a href="?">退出搜索</a></nobr>
                </form>
            </td>
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
                <td class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>"><?php echo $tdtitle; ?>
                </td>
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
						$op[] = '<a href="javascript:;" onclick="edit(' . $id . ', this);">修改</a>';
					}
					if (check_power("d", $pinfo, $pagepower)) {
						$op[] = '<a href="javascript:;" onclick="delete_line(' . $id . ',' . $line["addtime"] . ')">删除</a>';
					}
					$op_button = implode("&nbsp;", $op);

					$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;
			?>
            <tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
                <td align="center" class="item"><?php echo $line["id"]; ?></td>
                <td align="center" class="item"><?php echo $line["sort"]; ?></td>
                <td align="center" class="item"><?php echo $line["mid"]; ?></td>
                <td align="center" class="item"><?php echo $line["type"] > 0 ? "<b>是</b>" : ""; ?></td>
                <td align="left" class="item"><?php echo $line["title"]; ?></td>
                <td align="left" class="item"><?php echo $line["link"]; ?></td>
                <td align="left" class="item"><?php echo $line["tips"]; ?></td>
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
    <div style="padding:10px;">
        <center>(共 <?php echo count($data); ?> 条数据)</center>
    </div>
    <!-- 分页链接 end -->

</body>

</html>