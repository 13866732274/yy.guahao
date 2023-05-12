<?php
// --------------------------------------------------------
// - 功能说明 : 部门列表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-03-30 12:48
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_part";

check_power('', $pinfo) or exit("没有打开权限...");

// 操作的处理:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		$ids = explode(",", $_GET["id"]);
		$del_fail = $del_ok = 0;
		foreach ($ids as $_id) {
			$_id = intval($_id);
			if ($_id > 0) {
				del_data($db, $table, $_id, 1, "删除部门“{name}”") ? $del_ok++ : $del_fail++;
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
$aLinkInfo = array();

// 读取页面调用参数:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// 定义单元格格式:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	4 => array("title" => "ID", "width" => "60", "align" => "center"),
	1 => array("title" => "部门名称", "width" => "", "align" => "left"),
	6 => array("title" => "优先度", "width" => "", "align" => "left"),
	2 => array("title" => "添加时间", "width" => "", "align" => "left"),
	3 => array("title" => "操作", "width" => "100", "align" => "center"),
);

// 数组:
$part_arr = $db->query("select * from $table order by sort desc, id asc", "id");

?>
<html>

<head>
    <title><?php echo $pinfo["title"]; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <style>
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
        parent.load_src(1, 'sys_part_edit.php', 600, 350);
    }

    function edit(id, obj) {
        set_high_light(obj);
        parent.load_src(1, 'sys_part_edit.php?id=' + id, 600, 350);
    }
    </script>
</head>

<body>
    <!-- 头部 begin -->
    <table class="headers" width="100%">
        <tr>
            <td class="headers_title">
                <nobr class="tips">部门列表</nobr>
            </td>
            <td class="header_center">
                <?php if (check_power("i", $pinfo, $pagepower)) { ?>
                <button onclick="add()" class="button">添加</button>
                <?php } ?>
            </td>
            <td class="headers_oprate"><button onclick="self.location.reload()" class="button">刷新</button></td>
        </tr>
    </table>
    <!-- 头部 end -->


    <div class="space"></div>
    <table width="100%" class="description description_light">
        <tr>
            <td>&nbsp;<b>严重警告：</b>请勿随意修改部门，如果修改混乱，将导致<b>系统崩溃</b>，<b>数据丢失</b>等严重问题，如果确实需要修改，请先咨询开发人员。</td>
        </tr>
    </table>

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
			if (count($part_arr) > 0) {
				foreach ($part_arr as $id => $line) {
					$op = array();
					if (check_power("e", $pinfo, $pagepower)) {
						$op[] = "<button onclick='edit(" . $id . ", this); return false' class='button_op'><img src='image/b_edit.gif' align='absmiddle' title='修改' alt=''></button>";
					}
					$op_button = implode("&nbsp;", $op);

			?>
            <tr>
                <td align="center" class="item"><?php echo $line["id"]; ?></td>
                <td align="left" class="item"><?php echo $line["name"]; ?></td>
                <td align="left" class="item"><?php echo $line["sort"]; ?></td>
                <td align="left" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
                <td align="center" class="item"><?php echo $op_button; ?></td>
            </tr>
            <?php
				}
			} else {
				?>
            <tr>
                <td colspan="<?php echo count($aTdFormat); ?>" align="center" class="nodata">(暂无数据)</td>
            </tr>
            <?php } ?>
            <!-- 主要列表数据 end -->
        </table>
    </form>
    <!-- 数据列表 end -->

    <div class="space"></div>
    <center>共 <b><?php echo count($part_arr); ?></b> 条资料　　(如需删除部门，请联系开发人员处理)</center>
    <div class="space"></div>

</body>

</html>