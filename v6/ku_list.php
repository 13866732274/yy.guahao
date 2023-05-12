<?php
// --------------------------------------------------------
// - 功能说明 : 病人资料库
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2013-7-11
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";
$part_id_name = $db->query("select id,name from sys_part", "id", "name");

function wee_hf_cut($hf, $keep_num)
{
    $hf = trim($hf);
    if ($hf == '') return '';
    $hf = str_replace("\r", "", $hf);
    $hf = str_replace("[回访渠道:", "[", $hf);
    $hf = str_replace("【HE】", "", $hf);
    $arr = explode("\n", $hf);
    if (count($arr) <= $keep_num) {
        return $hf;
    }
    $arr2 = array_slice($arr, -$keep_num, $keep_num);
    return "<span title='为保持页面整洁，更多回访内容已裁切'>……</span>\n" . implode("\n", $arr2);
}

if ($op = $_GET["op"]) {
    include "ku.op.php";
}

// 定义当前页需要用到的调用参数:
$aLinkInfo = array(
    "page" => "page",
    "hospital" => "hospital",
    "sortid" => "sort",
    "sorttype" => "sorttype",
    "key" => "key",
    "btime" => "btime",
    "etime" => "etime",
    "part_id" => "part_id",
    "my" => "my",
    "is_yuyue" => "is_yuyue",
    "is_come" => "is_come",
    "remind" => "remind",
    "sou" => "sou",
);

// 读取页面调用参数:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
    $$local_var_name = $_GET[$call_var_name];
}

// 定义单元格格式:
$aTdFormat = array(
    10 => array("title" => "所属医院", "width" => "", "align" => "left", "sort" => "hid", "defaultorder" => 1),
    12 => array("title" => "状态", "width" => "", "align" => "left", "sort" => "is_yuyue", "defaultorder" => 1),
    14 => array("title" => "跟踪", "width" => "", "align" => "left", "sort" => "huifang_num", "defaultorder" => 1),
    16 => array("title" => "姓名|手机", "width" => "", "align" => "left", "sort" => "name", "defaultorder" => 1),
    20 => array("title" => "QQ|我方QQ", "width" => "", "align" => "left", "sort" => "qq", "defaultorder" => 2),
    22 => array("title" => "微信|我方微信", "width" => "", "align" => "left", "sort" => "weixin", "defaultorder" => 2),
    //30=>array("title"=>"疾病", "width"=>"", "align"=>"left", "sort"=>"disease_name", "defaultorder"=>2),
    32 => array("title" => "咨询内容", "width" => "", "align" => "left", "sort" => "", "defaultorder" => 1),
    40 => array("title" => "来源渠道", "width" => "", "align" => "left", "sort" => "laiyuan", "defaultorder" => 1),
    50 => array("title" => "添加日期", "width" => "", "align" => "left", "sort" => "addtime", "defaultorder" => 2),
    // 20200630二次开发提醒日期
    55 => array("title" => "提醒日期", "width" => "", "align" => "left", "sort" => "", "defaultorder" => 2),
    52 => array("title" => "添加人", "width" => "", "align" => "left", "sort" => "u_name", "defaultorder" => 1),
    60 => array("title" => "转微信", "width" => "", "align" => "center", "sort" => "to_weixin", "defaultorder" => 2),
    62 => array("title" => "微信对接", "width" => "", "align" => "left", "sort" => "wx_uname", "defaultorder" => 2),
    64 => array("title" => "微信好友", "width" => "", "align" => "left", "sort" => "wx_is_add", "defaultorder" => 2),
    99 => array("title" => "操作", "width" => "", "align" => "center"),
);

// 默认排序方式:
$defaultsort = 50;
$defaultorder = 2;


// 预处理高级搜索条件:
if (is_array($sou)) {
    $sou_set = $sou;
    $sou = base64_encode(serialize($sou));
} else {
    if ($sou != '') {
        $sou_set = @unserialize(base64_decode($sou));
    }
}


// 查询条件:
$where = array();
if (!$debug_mode) {
    $where[] = "hid in (" . implode(",", $hospital_ids) . ")";
}

if ($_GET["hospital"] > 0) {
    $where[] = "hid=" . intval($_GET["hospital"]);
}

//根据科室列出未约患者-yuanwu-2020-09-05
if ($hid > 0) {
    $where[] = "hid=" . $hid;
}
// 是否搜号码模式:
$search_mobile_mode = 0;
if (substr($key, 0, 1) == "1" && strlen($key) == 11) {
    $search_mobile_mode = 1;
}

$num_limit = 0;
if ($search_mobile_mode) {
    // 搜号码模式，不限制部门
    $num_limit = 5;
} else {
    // 2015-12-12 限制查看部门

    if ($ku_config["data_limit"] == "0" || $ku_config["data_limit"] == "") {
        $where[] = "uid=" . $uinfo["id"];
    } else if ($ku_config["data_limit"] == "2") {
        $where[] = "part_id in (2,12)";
    } else if ($ku_config["data_limit"] == "3") {
        $where[] = "part_id in (3,12)";
    } else if ($ku_config["data_limit"] == "-1") {
        $where[] = "part_id in (2,3,12)";
    } else if ($ku_config["data_limit"] == "-2") {
        $where[] = "part_id in (" . $uinfo["part_id"] . ")";
    } else if ($ku_config["data_limit"] == "-9") {
        // 看全部资料，无需限制语句
    }

    if ($is_weixin) {
        if ($is_weixin_zuzhang) {
            $where[] = "(to_weixin=1 or uid=$uid)";
        } else {
            $where[] = "((to_weixin=1 and (wx_uid=0 or wx_uid=$uid)) or uid=$uid)";
        }
    }
}

// 是否搜号码模式:
if ($search_mobile_mode) {
    $where[] = "(mobile like '{$key}%' or weixin like '{$key}%')";
} else {
    if ($key) {
        //限制查询时间为两年内 2023-03-01 edit by Code Pioneer
        $limit_time = strtotime("-2 year");
        $where[] = "(addtime>$limit_time and concat(h_name,' ',name,' ',mobile,' ',qq,' ',order_qq,' ',zx_content,' ',weixin,' ',order_weixin,' ',u_name,' ',hf_log,' ',wx_uname) like '%{$key}%')";
    }
}

if ($_GET["btime"]) {
    $where[] = "addtime>=" . @strtotime($_GET["btime"] . " 0:0:0");
}

if ($_GET["etime"]) {
    $where[] = "addtime<=" . @strtotime($_GET["etime"] . " 23:59:59");
}

if ($_GET["part_id"]) {
    $where[] = "part_id=" . intval($_GET["part_id"]);
}

if ($_GET["is_yuyue"] != '') {
    $where[] = "is_yuyue>0";
}

if ($_GET["is_come"] != '') {
    $where[] = "is_come>0";
}

if ($_GET["js_kefu"] != "") {
    $where[] = "u_name='" . mb_convert_encoding($_GET["js_kefu"], "gbk", "UTF-8") . "'";
}

if ($my) {
    $where[] = "(uid=" . $uid . " or wx_uid=" . $uid . ")";
}

if ($remind) {
    if ($config["show_all_remind"]) {
        $id_arr = $db->query("select ku_id from ku_remind where remind_date='$remind' order by id desc limit 1000", "", "ku_id");
    } else {
        $id_arr = $db->query("select ku_id from ku_remind where remind_date='$remind' and uid=$uid", "", "ku_id");
    }
    $_ids = count($id_arr) > 0 ? implode(",", $id_arr) : "0";
    $where[] = "id in ($_ids)";
}


$high_search_show = "";
if (is_array($sou_set)) {
    include "ku.search_config.php";
    list($where2, $show_str) = wee_build_high_search_sql($sou_set);
    if (count($where2) > 0) {
        if ($_GET["btime"] != '') $show_str[] = "时间起：" . $_GET["btime"];
        if ($_GET["etime"] != '') $show_str[] = "时间止：" . $_GET["etime"];
        $where[] = $high_search_code = "(" . implode(" and ", $where2) . ")";
        $high_search_show = implode("　", $show_str);
    }
}

$sqlwhere = count($where) > 0 ? ("where " . implode(" and ", $where)) : "";
//print_r($sqlwhere);exit;
// 对排序的处理：
if ($sortid > 0) {
    $sqlsort = "order by " . $aTdFormat[$sortid]["sort"] . " "; //order by remind_date

    if ($sorttype > 0) {
        $sqlsort .= $aOrderType[$sorttype];
        //
        $count = count($db->query("select * from ku_list left join ku_remind on ku_list.id = ku_remind.ku_id limit 100"));
    } else {
        $sqlsort .= $aOrderType[$aTdFormat[$sortid]["defaultorder"]];
    }
} else {
    if ($high_search_code != "") {
        $sqlsort = "order by id desc";
    } else {
        $sqlsort = "order by updatetime desc";
    }
}

// 分页数据:
$count = $db->query("select count(*) as c from $table $sqlwhere", 1, "c");
$main_sql[] = $db->sql;
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// 结果数量限制
if ($num_limit > 0) {
    $offset = 0;
    $pagesize = $num_limit;
}

// 查询:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");
$main_sql[] = $db->sql;

if ($high_search_show && $page == 1) {
    $high_search_result = "　　结果共：" . $count . "条";
    $c = $db->query("select count(*) as c from $table $sqlwhere and is_yuyue>0", 1, "c");
    $high_search_result .= "　已约：" . $c . "条";
    $c = $db->query("select count(*) as c from $table $sqlwhere and is_come>0", 1, "c");
    $high_search_result .= "　已到：" . $c . "条";
    //$c = $db->query("select count(*) as c from $table $sqlwhere and track_status<0", 1, "c");
    //$high_search_result .= "　放弃：".$c."条";
    $c = $db->query("select count(*) as c from $table $sqlwhere and to_weixin>0", 1, "c");
    $high_search_result .= "　转微信：" . $c . "条";
    //$c = $db->query("select count(*) as c from $table $sqlwhere and wx_is_add>0", 1, "c");
    //$high_search_result .= "　加好友：".$c."条";
}


function _wee_tys($string, $obj_hid, $mobile)
{
    global $uid;
    if ($string == $mobile) {
        return '<a href="javascript:;" onclick="tongyuansou(' . $obj_hid . ',\'' . $mobile . '\');" style="color:black">' . $string . '</a>';
    }
    return $string;
}


// 页面开始 ------------------------
?>
<html>

<head>
    <title>资料库</title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css?2016" rel="stylesheet" type="text/css">
    <script src="lib/base.js?2016" language="javascript"></script>
    <script src="lib/datejs/picker.js" language="javascript"></script>
    <style>
        .column_sortable {
            color: blue !important;
            cursor: pointer;
        }

        .sorttable_nosort {
            color: gray;
        }

        .tr_high_light td {
            background: #FFE1D2;
        }

        .hf_line {
            border: 0;
            height: 1px;
            line-height: 0;
            font-size: 0;
            margin: 6px 20px 6px 0px;
            padding: 0;
        }

        .tr_yuyue,
        .tr_yuyue * {
            color: green !important;
        }

        .tr_come,
        .tr_come * {
            color: red !important;
        }

        .tr_abort,
        .tr_abort * {
            color: #a0a0a0 !important;
        }

        .tr_wx,
        .tr_wx * {
            color: #408080 !important;
        }

        .tips {
            background: none;
            font-family: "微软雅黑";
            font-size: 14px;
            border: 0;
        }
    </style>
    <script language="javascript">
        function add() {
            //set_high_light('');
            parent.load_src(1, 'ku_add.php', 980, 650);
            return false;
        }

        function edit(id, obj) {
            set_high_light(obj);
            parent.load_src(1, 'ku_edit.php?id=' + id, 800, 550);
            return false;
        }

        function huifang(id, obj) {
            set_high_light(obj);
            parent.load_src(1, 'ku_huifang.php?id=' + id, 980, 550);
            return false;
        }

        function set_yuyue(id, obj) {
            set_high_light(obj);
            //if (confirm("请在接下来打开的窗口中完善资料并提交，提交后才算成功。是否继续？")) {
            parent.load_src(1, 'ku_list.php?op=set_yuyue&id=' + id);
            //}
            return false;
        }

        function more_search() {
            parent.load_src(1, 'ku_search.php?op=search', 800, 600);
            return false;
        }

        function tongyuansou(hid, key) {
            var url = "patient_tongyuansou.php?code=utf8&hid=" + hid + "&key=" + encodeURIComponent(key) + "&r=" + Math
                .random();
            parent.load_src(1, url);
        }


        function set_add_haoyou(id, obj) {
            if (confirm("该操作不能撤销，请仔细检查，不要误操作。是否确定已加上好友？")) {
                load_js("ku_list.php?op=set_add_haoyou&id=" + id, "set_add_haoyou");
            }
        }

        function set_add_haoyou_do(id) {
            byid("wxhy_" + id).innerHTML = "已加上";
            self.location.reload();
        }

        function delete_line(id, crc) {
            if (confirm("删除后不能恢复，确定要删除该条资料吗？")) {
                load_js("ku_list.php?op=delete&id=" + id + "&crc=" + crc, "delete_line");
            }
        }
    </script>

</head>

<body id="body">
    <!-- 头部 begin -->
    <table class="headers" width="100%">
        <tr>
            <td class="headers_title" style="width:60px">
                <nobr class="tips">资料库</nobr>
            </td>

            <td class="headers_cneter" align="center">
                <style type="text/css">
                    .ml {
                        margin-left: 8px;
                    }

                    .big_font {
                        font-size: 12px;
                    }

                    .big_font:hover {
                        font-size: 12px;
                    }
                </style>
                <button onclick="add()" class="button ml">添加</button>
                <?php if ($my) { ?>
                    <a href="?" style="color:red" class="ml big_font" title="再点一次退出此模式">当前为：查看我的资料</a>
                <?php } else { ?>
                    <a href="?my=1" class="ml big_font" title="筛选我添加的资料">仅查看我的资料</a>
                <?php } ?>

                <?php
                $z = date("Ymd", strtotime("-1 days"));
                $remind_count = $db->query("select count(*) as c from ku_remind where remind_date='$z' and uid=$uid", 1, "c");
                if ($remind_count >= 0) {
                ?>
                    <a href="?remind=<?php echo $z; ?>" style="color:#aa55ff" class="ml big_font">昨日提醒[<?php echo $remind_count; ?>]</a>
                <?php } ?>

                <?php
                $z = date("Ymd");
                $remind_count = $db->query("select count(*) as c from ku_remind where remind_date='$z' and uid=$uid", 1, "c");
                if ($remind_count >= 0) {
                ?>
                    <a href="?remind=<?php echo $z; ?>" style="color:#aa55ff" class="ml big_font">今日提醒[<?php echo $remind_count; ?>]</a>
                <?php } ?>

                <?php
                $z = date("Ymd", strtotime("+1 days"));
                $remind_count = $db->query("select count(*) as c from ku_remind where remind_date='$z' and uid=$uid", 1, "c");
                if ($remind_count >= 0) {
                ?>
                    <a href="?remind=<?php echo $z; ?>" style="color:#aa55ff" class="ml big_font">明日提醒[<?php echo $remind_count; ?>]</a>
                <?php } ?>

                <!--按照提醒日期查询start-->
                <form action="?" method="GET" style="display:inline;"><input name="remind" id="ch_date" onchange="this.form.submit();" value="<?php echo $_GET["date"]; ?>" style="width:0px; overflow:hidden; padding:0; border:0; margin-left:10px; "></form>
                <a href="javascript:;" onclick="picker({el:'ch_date',dateFmt:'yyyyMMdd'});" title="按预约时间查看某一日数据"><?php echo $_GET["date"] != "" ? $_GET["date"] : "按日查看"; ?></a>
                <!--按照提醒日期查询end-->

                <a href="ku_report_gz.php" class="red ml big_font">跟踪报表</a>

                <?php if ($is_super_admin) { ?>
                    <a href="javascript:;" onclick="parent.load_src(1, 'ku_reset_patient.php', 800, 600);" class="ml big_font">患者再分配</a>
                    <a href="javascript:;" onclick="parent.load_src(1, 'ku_set_weixin_renwu.php', 800, 600);" class="ml big_font">微信任务分配</a>
                    <a href="javascript:;" onclick="parent.load_src(1, 'ku_report_wx.php');" class="ml big_font red">微信报表</a>
                <?php } ?>
            </td>
            <td class="headers_oprate" style="width:300px">
                <nobr>
                    <form name="topform" method="GET" style="display:inline;">
                        关键词：<input name="key" value="<?php echo $_GET["key"]; ?>" class="input" size="12">&nbsp;
                        <input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">
                        <a href="javascript:;" onclick="more_search(); return false;" class="ml">高级搜索</a>
                        <font color="red">[新]</font>
                        <a href="?" class="ml">退出搜索</a>
                        <input type="hidden" name="my" value="<?php echo $my; ?>">
                    </form>
                </nobr>
            </td>
        </tr>
    </table>
    <!-- 头部 end -->

    <div class="space"></div>

    <?php if ($high_search_show != "") { ?>
        <style type="text/css">
            .high_search {
                border: 1px solid silver;
                padding: 8px;
            }
        </style>
        <div class="high_search">当前为高级搜索模式　搜索条件为　<font color="red"><?php echo $high_search_show; ?></font>
            <?php echo $high_search_result; ?>　　<?php if ($debug_mode) { ?><font color="blue">
                <?php echo $high_search_code; ?></font><?php } ?></div>
        <div class="space"></div>
    <?php } ?>

    <!-- 数据列表 begin -->
    <form name="mainform">
        <table id="list" width="100%" align="center" class="list sortable">
            <!-- 表头定义 begin -->
            <tr>
                <?php
                // 表头处理:
                foreach ($aTdFormat as $tdid => $tdinfo) {
                    list($tdalign, $tdwidth, $tdtitle) = make_td_head($tdid, $tdinfo);
                ?>
                    <th class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>">
                        <nobr><?php echo $tdtitle; ?></nobr>
                    </th>
                <?php } ?>
            </tr>
            <!-- 表头定义 end -->

            <!-- 主要列表数据 begin -->
            <?php
            if (count($data) > 0) {
                foreach ($data as $line) {
                    $id = $line["id"];

                    $part_name = $part_id_name[$line["part_id"]];
                    $part_name = str_replace("客服", "", $part_name);

                    $genzong_cishu = $line["huifang_num"];


                    $content = cut(strip_tags($line["zx_content"]), 200, "…");
                    if ($line["disease_name"] != "") {
                        $content = '[<b>疾病：' . $line["disease_name"] . ']</b><br>' . $content;
                    }
                    if ($line["hf_log"]) {
                        $content .= '<div class="hf_line"></div>';
                        $content .= nl2br(wee_hf_cut($line["hf_log"], 2));
                    }

                    $class = "";
                    $track_status = "继续";
                    if ($line["track_status"] == "-1") {
                        $class = "tr_abort";
                        $track_status = "放弃";
                    }
                    if ($line["is_yuyue"]) {
                        $class = "tr_yuyue";
                        $track_status = "已约";
                    }
                    if ($line["is_come"]) {
                        $class = "tr_come";
                        $track_status = "已约已到";
                    }

                    if ($line["qq"] != "") {
                        if (strlen($line["qq"]) > 16) {
                            $line["qq"] = '<span title="' . $line["qq"] . '">' . cut($line["qq"], 16, "…") . '</span>';
                        }
                        if (strlen($line["order_qq"]) > 16) {
                            $line["order_qq"] = '<span title="' . $line["order_qq"] . '">' . cut($line["order_qq"], 16, "…") . '</span>';
                        }
                        $line["qq"] .= "<br>" . ($line["order_qq"] != "" ? $line["order_qq"] : "---");
                    }
                    if ($line["weixin"] != "") {
                        if (strlen($line["weixin"]) > 16) {
                            $line["weixin"] = '<span title="' . $line["weixin"] . '">' . cut($line["weixin"], 16, "…") . '</span>';
                        }
                        if (strlen($line["order_weixin"]) > 16) {
                            $line["order_weixin"] = '<span title="' . $line["order_weixin"] . '">' . cut($line["order_weixin"], 16, "…") . '</span>';
                        }
                        $line["weixin"] .= "<br>" . ($line["order_weixin"] != "" ? $line["order_weixin"] : "---");
                    }

                    $sex_age = trim($line["sex"] . "" . ($line["age"] != "" ? str_replace("岁", "", $line["age"]) . "岁" : ""));

                    if (trim($line["name"]) == trim($line["mobile"])) $line["name"] = "";
                    $name_arr = array();
                    $names_show = trim($line["name"] . ($sex_age != "" ? " (" . $sex_age . ")" : ""));
                    if ($names_show != "") $name_arr[] = $names_show;
                    if ($line["mobile"] != "") $name_arr[] = _wee_tys(_ku_show_tel($line), $line["hid"], $line["mobile"]);
                    if ($line["area"] != "") $name_arr[] = $line["area"];
                    $name_string = implode("<br>", $name_arr);

                    if (strlen($line["laiyuan"]) > 16) {
                        $line["laiyuan"] = '<span title="' . $line["laiyuan"] . '">' . cut($line["laiyuan"], 16, "…") . '</span>';
                    }


                    // 微信加好友状态 @ 2016-10-21
                    if ($is_weixin && $line["to_weixin"] > 0 && ($line["wx_uid"] == 0 || $line["wx_uid"] == $uid)) {
                        if ($line["wx_is_add"] == 0) {
                            $haoyou_status = '<a href="javascript:;" onclick="set_add_haoyou(' . $id . ', this);">我已加上</a>';
                        } else {
                            $haoyou_status = '已加上';
                        }
                    } else {
                        $haoyou_status = $line["wx_is_add"] ? "已加上" : ($line["to_weixin"] ? "未加" : "--");
                    }

                    //20200701提醒日期开始-begin
                    $remind_line = $db->query("select * from ku_remind where ku_id=" . $line['id'] . " and uid=" . $line['uid'] . " limit 1", 1);
                    $remind_date = $remind_line['remind_date'];
                    //20200701提醒日期开始-end

                    $op = array();
                    if ($is_super_admin || $line["uid"] == $uid || ($is_weixin && $line["wx_uid"] == $uid)) {
                        $op[] = "<button class='button_op' onclick='edit(" . $id . ", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='修改' alt=''></button>";
                    }

                    //if ($is_super_admin || !$is_weixin || ($is_weixin && $line["wx_uid"] == $uid)) {
                    $op[] = "<button class='button_op' onclick='huifang(" . $id . ", this); return false;' class='op'><img src='image/b_tel.gif' align='absmiddle' title='回访&跟踪' alt=''></button>";
                    //}

                    if ($is_super_admin || $uid == $line["uid"] || ($is_weixin && $line["wx_uid"] == $uid && $line["wx_is_add"] > 0)) {
                        $op[] = "<button class='button_op' onclick='set_yuyue(" . $id . ", this); return false;' class='op'><img src='image/add_yuyue.gif' align='absmiddle' title='添加到预约系统' alt=''></button>";
                    }

                    if ($is_super_admin || $uinfo["delete_ku_patient"]) {
                        $op[] = "<button class='button_op' onclick='delete_line(" . $id . ", " . $line["addtime"] . "); return false;'><img src='image/b_delete.gif' align='absmiddle' title='删除'></button>";
                    }

                    $op_button = count($op) ? implode(" ", $op) : "<i title='无权限'>--</i>";

            ?>
                    <tr onmouseover="mi(this)" onmouseout="mo(this)" class="<?php echo $class; ?>">
                        <td align="left" class="item"><a href="?hospital=<?php echo $line["hid"]; ?>" title="点击查看同医院患者">
                                <nobr><?php echo $line["h_name"]; ?></nobr>
                            </a><br>
                            <nobr><?php echo $part_name; ?></nobr>
                        </td>
                        <td align="left" class="item">
                            <nobr><?php echo $track_status; ?></nobr>
                        </td>
                        <td align="left" class="item">
                            <nobr><?php echo $genzong_cishu; ?>次</nobr>
                        </td>
                        <td align="left" class="item">
                            <nobr><?php echo $name_string; ?></nobr>
                        </td>
                        <td align="left" class="item">
                            <nobr><?php echo $line["qq"]; ?></nobr>
                        </td>
                        <td align="left" class="item">
                            <nobr><?php echo $line["weixin"]; ?></nobr>
                        </td>
                        <!-- <td align="left" class="item"><?php echo cut($line["disease_name"], 16, "…"); ?></td> -->
                        <td align="left" class="item"><?php echo $content; ?></td>
                        <td align="left" class="item">
                            <nobr><?php echo $line["laiyuan"]; ?></nobr>
                        </td>
                        <td align="left" class="item">
                            <nobr><?php echo wee_time($line["addtime"]); ?></nobr>
                        </td>
                        <!-- 20200630二次开发提醒日期 -->
                        <td align="left" class="item">
                            <nobr><?php echo $remind_date; ?></nobr>
                        </td>
                        <td align="left" class="item">
                            <nobr><?php echo str_replace("【HE】", "", $line["u_name"]); ?></nobr>
                        </td>
                        <td align="center" class="item">
                            <nobr><?php echo $line["to_weixin"] ? "是" : "--"; ?></nobr>
                        </td>
                        <td align="left" class="item">
                            <nobr><?php echo str_replace("【HE】", "", $line["wx_uname"]); ?></nobr>
                        </td>
                        <td align="left" class="item">
                            <nobr id="wxhy_<?php echo $id; ?>"><?php echo $haoyou_status; ?></nobr>
                        </td>
                        <td align="center" class="item">
                            <nobr><?php echo $op_button; ?></nobr>
                        </td>
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
            共 <b><?php echo $count; ?></b> 条资料　　<a href="?is_yuyue=1">
                <font class="tr_yuyue">[已约]</font>
            </a>　<a href="?is_come=1">
                <font class="tr_come">[已到]</font>
            </a>　<?php if ($is_daochu) { ?><a href="javascript:;" onclick="parent.load_src(1, 'ku_daochu.php');">[导出]</a><?php } ?>　执行时间：<?php echo round(now() - $pagebegintime, 4); ?>秒
        </div>
        <div class="footer_op_right">
            <?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
        <div class="clear"></div>
    </div>
    <!-- 分页链接 end -->

    <?php if ($key) { ?>
        <!-- 关键词高亮 -->
        <script>
            highlightWord(document.body, "<?php echo $key; ?>");
        </script>
    <?php } ?>

    <!-- <?php print_r($where); ?>
    <?php print_r($main_sql); ?> -->

</body>

</html>