<?php
// --------------------------------------------------------
// - 功能说明 : character.php
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-05-15 02:19
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_character";

check_power('', $pinfo) or exit("没有打开权限...");

// 操作的处理:
$op = $_GET["op"];

if ($op == "insert") {
	check_power("i", $pinfo, $pagepower) or msg_box("没有新增权限...", "back", 1);
	header("location:" . $pinfo["insertpage"]);
	exit;
}

if ($op == "delete") {
	$ids = explode(",", $_GET["id"]);
	$del_fail = $del_ok = 0;
	foreach ($ids as $_id) {
		$_id = intval($_id);
		if ($_id > 0) {
			del_data($db, $table, $_id, 1, "删除权限“{name}”") ? $del_ok++ : $del_fail++;
		}
	}
	if ($del_fail > 0) {
		msg_box("删除成功 $del_ok 条资料，删除失败 $del_fail 条资料。", "back", 1);
	} else {
		msg_box("删除成功", "back", 1);
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
	2 => array("title" => "ID", "width" => "40", "align" => "center"),
	1 => array("title" => "名称", "width" => "", "align" => "left"),
	4 => array("title" => "当前使用者", "width" => "50%", "align" => "left",),
	5 => array("title" => "添加人", "width" => "", "align" => "left"),
	3 => array("title" => "添加时间", "width" => "", "align" => "left"),
	9 => array("title" => "优先度", "width" => "", "align" => "center"),
	8 => array("title" => "比较", "width" => "", "align" => "center"),
	7 => array("title" => "操作", "width" => "", "align" => "center"),
);

// 默认排序方式:
$defaultsort = 3;
$defaultorder = 1;


// 查询条件:
$where = array();
if ($searchword) {
	$where[] = "(binary t.name like '%{$searchword}%' or binary t.author like '%{$searchword}%')";
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
	$sqlsort = "order by sort desc, id asc";
}

// 分页数据:
$count = $db->query_count("select count(*) from $table t $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// sql查询:
$data = $db->query("select * from $table t $sqlwhere $sqlsort limit $offset, $pagesize");
if (!is_array($data)) {
	exit("<b>数据库sql查询语句出错，请联系管理员检查：</b><br>" . $db->sql);
}

// 删除超过我的权限:
foreach ($data as $k => $v) {
	if (!check_power_in($v["menu"], $usermenu)) {
		unset($data[$k]);
	}
}

// admin 信息:
$tm_admins = $db->query("select id,name,realname,character_id from sys_admin where isshow=1 order by realname");
$ch_info = array();
foreach ($tm_admins as $tm_ad_info) {
	$ch_info[$tm_ad_info["character_id"]][] = $tm_ad_info["realname"];
}


?>
<html>

<head>
    <title><?php echo $pinfo["title"]; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <style type="text/css">
    .tr_high_light td {
        background: #FFE1D2;
    }
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
        parent.load_src(1, 'sys_character_edit.php');
        return false;
    }

    function edit(id, obj) {
        set_high_light(obj);
        parent.load_src(1, 'sys_character_edit.php?id=' + id);
        return false;
    }

    function compare(id, obj) {
        set_high_light(obj);
        parent.load_src(1, 'sys_character_compare.php?id=' + id, 900, 550);
        return false;
    }

    function set_config(id, obj) {
        set_high_light(obj);
        parent.load_src(1, 'sys_character_config.php?id=' + id, 800, 500);
        return false;
    }

    function del_confirm() {
        return confirm("删除后真的不能恢复，确定要删吗？");
    }
    </script>
</head>

<body>
    <!-- 头部 begin -->
    <table class="headers" width="100%">
        <tr>
            <td class="headers_title">
                <nobr class="tips">权限列表</nobr>
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
                            title="点击搜索">&nbsp;<button onclick="location='?'" class="search"
                            title="退出条件查询">重置</button>&nbsp;<button onclick="self.location.reload()"
                            class="button">刷新</button></nobr>
                </form>
            </td>
        </tr>
    </table>
    <!-- 头部 end -->


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
						$op[] = "<button class='button_op' onclick='edit(" . $id . ", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='编辑权限' alt=''></button>";
					}
					$op[] = "<button class='button_op' onclick='set_config(" . $id . ", this); return false;' class='op'><img src='image/b_set.png' align='absmiddle' title='修改配置' alt=''></button>";
					if (check_power("d", $pinfo, $pagepower) && is_array($ch_info[$id]) && count($ch_info[$id]) == 0) {
						$op[] = "&nbsp;<a href='?op=delete&id=$id' onclick='return del_confirm();'>删除</a>";
					}
					$op_button = implode("&nbsp;", $op);

					$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;
			?>
            <tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
                <td align="center" class="item"><?php echo $line["id"]; ?></td>
                <td align="left" class="item"><?php echo "<b>" . $line["name"] . "</b>"; ?></td>
                <?php
						if (is_array($ch_info[$id]) && count($ch_info[$id]) > 30) {
							$_tm = array_slice($ch_info[$id], 0, 30);
							$str = @implode(" <font color=silver>|</font> ", $_tm) . " … （共<b style='color:red'>" . count($ch_info[$id]) . "</b>人）";
						} else {
							$_tm = $ch_info[$id];
							$str = @implode(" <font color=silver>|</font> ", $_tm);
						}
						?>
                <td align="left" class="item"><?php echo $str; ?></td>
                <td align="left" class="item"><?php echo $line["author"]; ?></td>
                <td align="left" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
                <td align="center" class="item"><?php echo $line["sort"]; ?></td>
                <td align="center" class="item">
                    <?php
							if ($debug_mode || $username == "admin" || $uinfo["part_id"] == 9) {
								echo "<a href='javascript:void(0);' onclick='compare($id, this);' class='op' title='比较权限大小'>比较</a>";
							}
							?>
                </td>
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
        <div class="footer_op_left">　<b>注意：</b>没有人使用的权限才能删除</div>
        <div class="footer_op_right">
            <?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
        <div class="clear"></div>
    </div>
    <!-- 分页链接 end -->

</body>

</html>