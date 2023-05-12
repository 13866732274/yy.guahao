<?php
// --------------------------------------------------------
// - 功能说明 : index.php
// - 创建作者 : yuanwu (yuanyue20191211@163.com)
// - 创建时间 : 2023-05-11 08-42
// --------------------------------------------------------
require "lib/set_env.php";
require "lib/class.fastjson.php";
require '../vendor/autoload.php';

$agent     = strtolower($_SERVER['HTTP_USER_AGENT']);
$is_iphone = (strpos($agent, 'iphone')) ? true : false;
$is_ipad   = (strpos($agent, 'ipad')) ? true : false;

if ($is_iphone) {
    header("location: index_iphone.php");
    exit;
}
if ($is_ipad) {
    header("location: index_ipad.php");
    exit;
}

$menu_stru = parse_menu($usermenu, 'stru');
$menu_ids  = parse_menu($usermenu, 'mid');

$menu_id_list = implode(",", $menu_ids);
$menu_data    = array();
if ($tmp_data = $db->query("select id,title,link,isshow from sys_menu where id in ($menu_id_list) and isshow=1 order by sort")) {
    foreach ($tmp_data as $tmp_line) {
        $menu_data[$tmp_line["id"]] = array($tmp_line["title"], $tmp_line["link"]);
    }
}

// 验证&删除多余mid:
foreach ($menu_stru as $mainid => $mlevel1) {
    if (!array_key_exists($mainid, $menu_data)) {
        unset($menu_stru[$mainid]);
        continue;
    }
    foreach ($mlevel1 as $key => $itemid) {
        if (!array_key_exists($itemid, $menu_data)) {
            unset($mlevel1[$key]);
        }
    }
    $menu_stru[$mainid] = array_merge($mlevel1);
}
$menu_mids      = FastJSON::convert(array_keys($menu_stru));
$menu_stru_json = FastJSON::convert($menu_stru);
$menu_data_json = FastJSON::convert($menu_data);

// 快捷菜单:
$shortcut_data = array();
if ($uinfo["shortcut"]) {
    $shortcut_data = explode(",", $uinfo["shortcut"]);
} else {
    $tmp_data = $db->query("select id from sys_menu where type=0 and id in ($menu_id_list) and shortcut=1 and isshow=1 order by mid asc, sort asc limit 12");
    foreach ($tmp_data as $tmp_line) {
        $shortcut_data[] = $tmp_line["id"];
    }
}
foreach ($shortcut_data as $key => $shid) {
    if (!array_key_exists($shid, $menu_data)) {
        unset($shortcut_data[$key]);
    }
}
$menu_shortcut = implode(",", $shortcut_data);

$is_show_dyn_menu = 1;
$is_show_shortcut = 1;
$submenu_pos      = 1;
$is_show_logobar  = 1;
$is_show_navibar  = 0;
$is_show_footer   = 0;
$ukey_sn          = $_SESSION[$cfgSessionName]["ukey_sn"];


if ($debug_mode) {
    $realname = "系统";
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns=http://www.w3.org/1999/xhtml>

<head>
    <title>
        <?php echo $cfgSiteName; ?>
    </title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/frame.css" rel="stylesheet" type="text/css">
    <script language="javascript">
        var menu_mids = <?php echo $menu_mids; ?>;
        var menu_stru = <?php echo $menu_stru_json; ?>;
        var menu_data = <?php echo $menu_data_json; ?>;
        var menu_shortcut = [<?php echo $menu_shortcut; ?>];
        var show_dyn_menu = <?php echo $is_show_dyn_menu ? 1 : 0; ?>;
        var show_shortcut = <?php echo $is_show_shortcut ? 1 : 0; ?>;
        var client_ukey_sn = "<?php echo $ukey_sn; ?>";
        window.ZHUWENYA_IFRAME = 1;
    </script>
    <script type="text/javascript">
        if (window.top !== window.self) {
            window.top.location = window.location;
        }
    </script>
    <script language="javascript" src="lib/frame.js"></script>
    <script language="javascript" src="lib/menu.js"></script>
    <script language="javascript" src="lib/drag.js"></script>
</head>

<body>
    <div id="top_border" class="co_top">
        <div class="co_left_top"></div>
        <div class="co_right_top"></div>
        <div class="clear"></div>
    </div>

    <div id="logo_bar" class="logo">
        <div class="logo_v_line fleft"></div>
        <div class="logo_v_line fright"></div>
        <div class="clear"></div>
    </div>

    <div id="menu_bar">
        <div class="tline left"></div>
        <div class="top_menu">
            <div id="sys_account_info">
                <a href="javascript:;" onclick="set_my_info()" title="点此修改资料和密码">
                    <?php echo $realname; ?>
                </a>
                <a href="javascript:;" onclick="show_hide_side()" class="ml10" title="展开/关闭左侧栏">侧栏开关</a>
            </div>
            <div id="sys_top_menu"></div>
            <div id="sys_top_menu_right">
                <nobr>
                    <a href="javascript:;" onclick="parent.update_content()" class="ml10" title="刷新页面">刷新</a>
                    <a href="javascript:;" onclick="change_hospital(this)" class="ml10" title="显示/隐藏切换医院">切换医院</a>
                    <a href="javascript:;" onclick="hospital_change_to_pre()" class="ml10" title="切换到上一家科室">向上</a>
                    <a href="javascript:;" onclick="hospital_change_to_next()" class="ml10" title="切换到下一家科室">向下</a>
                    <a href="logout.php" title="退出系统" class="ml10">退出</a>
                </nobr>
            </div>
            <div class="clear"></div>
        </div>
        <div class="tline right"></div>
        <div class="clear"></div>
    </div>

    <div id="main_bar">
        <div id="side_menu" class="left_menu">
            <div id="sys_left_menu"></div>
            <div id="sys_shortcut"></div>
            <div id="sys_online" style="display:none;"></div>
            <div id="sys_notice"></div>
        </div>
        <div id="frame_content"><iframe id="sys_frame" name="main" onload="frame_loaded_do(this)" src="" mid=""
                framesrc="" frameborder="0" scrolling="auto" width="100%" height="365"
                onreadystatechange="update_navi()"></iframe></div>
        <div class="clear"></div>
    </div>

    <div id="bottom_border" class="co_bottom">
        <div class="co_left_bottom"></div>
        <div class="co_right_bottom"></div>
        <div class="clear"></div>
    </div>

    <?php if ($debug_mode) { ?>
        <div id="log"
            style="width:300px; height:600px; position:absolute; right:10px; bottom:10px; z-index:100000; border:2px solid silver; background:white; padding:5px; overflow:auto; display:none; ">
        </div>
    <?php } ?>


    <!-- loading status table -->
    <table id="sys_loading"
        style="display:none; position:absolute; border:1px solid #00D5D5; background:#D9FFFF; line-height:120%">
        <tr>
            <td style="padding:1px 0 0 6px"><img src='image/loading.gif' width='16' height='16' align='absmiddle' />
            </td>
            <td id="sys_loading_tip" style="padding:2px 6px 0px 6px"></td>
        </tr>
    </table>

    <!-- sys dialog box -->
    <div id="dl_layer_div" title="" onclick="load_src(0);"
        style="position:absolute; filter:Alpha(opacity=70); display:none; background:#404040; z-index:998; opacity:0.7;">
    </div>
    <div id="dl_box_div" class="obox" style="position:absolute; display:none; z-index:999">
        <div id="dl_box_title_box">
            <div id="dl_box_title"></div>
            <div id="dl_box_op"><a href="javascript:load_box(0);">关闭</a></div>
            <div class="clear"></div>
        </div>
        <div id="dl_box_loading" style="position:absolute; display:none;"><img src="image/loading.gif"
                align="absmiddle"> 加载中，请稍候... </div>
        <div id="dl_iframe"><iframe src="about:blank" frameborder="0" scrolling="auto" width="100%" id="dl_set_iframe"
                onload="update_title(this)"></iframe></div>
        <div id="dl_content" style="display:none;"></div>
    </div>

    <!-- msg_box -->
    <div id="sys_msg_box" style="display:none; position:absolute;cursor:pointer;" onclick="msg_box_hide()"
        onmouseover="msg_box_hold()" onmouseout="msg_box_delay_hide()" title="点击关闭">
        <table cellpadding="0">
            <tr>
                <td class="left_div"></td>
                <td class="center_div">
                    <table>
                        <tr>
                            <td id="sys_msg_box_content"></td>
                        </tr>
                    </table>
                </td>
                <td class="right_div"></td>
            </tr>
        </table>
    </div>

    <!-- ukey -->
    <div style="display:none;">
        <object classid="clsid:e6bd6993-164f-4277-ae97-5eb4bab56443" id="ET99" name="ET99" style="left:0px; top:0px"
            width="0" height="0"></object>
    </div>
    <script type="text/javascript">
        index = 0;

        function found_et99() {
            et99 = byid("ET99");
            if (et99) {
                window.onerror = function () {
                    document.title = "检测uKey出错，系统将马上退出";
                    setTimeout('top.location = "/v6/logout.php"', 500);
                    return true;
                }
                var count = et99.FindToken("FFFFFFFF");
                if (count > 0) {
                    window.onerror = function () {
                        document.title = "Error: et99::OpenToken";
                        return true;
                    }
                    et99.OpenToken("FFFFFFFF", 1)
                    sn = et99.GetSN();
                    if (sn != client_ukey_sn) {
                        top.location = "/v6/logout.php";
                        return;
                    } else {
                        window.onerror = function () {
                            document.title = "Error: et99::VerifyPIN";
                            return true;
                        }
                        et99.VerifyPIN(1, "FFFFFFFFFFFFFFFF");
                        if (index == 0) {
                            index = 1;
                            et99.TurnOffLED();
                        } else {
                            index = 0;
                            et99.TurnOnLED();
                        }
                    }
                    et99.CloseToken();
                } else {
                    document.title = "未检测到uKey，系统将马上退出";
                    setTimeout('top.location = "/v6/logout.php"', 500);
                }
            }
        }

        if (client_ukey_sn != "") {
            setInterval("found_et99()", 500);
        }
    </script>

    <script language="JavaScript">
        dom_loaded.load(init);

        if (byid("dl_box_div")) {
            Drag.init(byid("dl_box_title_box"), byid("dl_box_div"));
        }
    </script>

    <div id="change_h_area" style="display:none; position:absolute; border-radius:4px ">(加载中...)</div>

    <script type="text/javascript">
        function change_hospital(obj) {
            var o = byid("change_h_area");
            if (o.style.display == "none") {
                var self_l = get_position(obj, "left");
                var self_t = get_position(obj, "top");
                var self_b = self_t + obj.offsetHeight + 2;
                o.style.left = (self_l - 190) + "px";
                o.style.top = self_b + "px";
                o.style.display = "block";
                o.innerHTML =
                    '<iframe id="change_hospital_frame" style="width:100%;height:100%" src="change_hospital.php" frameborder="0"></iframe>';
                byid("change_hospital_frame").src = "change_hospital.php"; //解决IE6中不显示的问题
            } else {
                o.style.display = "none";
            }
        }

        function hospital_change_to_pre() {
            load_js_file("change_hospital.php?op=jschange&mode=pre", "change_hospital");
        }

        function hospital_change_to_next() {
            load_js_file("change_hospital.php?op=jschange&mode=next", "change_hospital");
        }

        function hospital_change_do() {
            update_content();
        }
    </script>

    <script type="text/javascript">
        function set_my_info() {
            load_src(1, "/v6/sys_myinfo_edit.php", 800, 500);
        }
    </script>

    <script type="text/javascript">
        function set_box_title(str) {
            byid("dl_box_title").innerHTML = str;
        }
    </script>

    <script language="javascript">
        if (screen.width > 0 && screen.width < 1280) {
            show_hide_side();
            setTimeout('alert("您的屏幕分辨率较低，已自动隐藏左侧栏。建议更换大屏幕显示器。如需显示左侧栏可点击左上角[侧栏开关]。")', 1000);
        }
    </script>

    <div id="player_area" style="display:none;"></div>

    <!-- Design by zhuwenya (934834734@qq.com) -->
    <!-- Frame loaded time: <?php echo date("Y-m-d H:i:s"); ?> -->

</body>

</html>