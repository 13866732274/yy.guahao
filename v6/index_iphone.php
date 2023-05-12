<?php
// --------------------------------------------------------
// - 功能说明 : index.php
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-12-12
// --------------------------------------------------------
require "lib/set_env.php";
require "lib/class.fastjson.php";

$menu_stru = parse_menu($usermenu, 'stru');
$menu_ids = parse_menu($usermenu, 'mid');

$menu_id_list = implode(",", $menu_ids);
$menu_data = array();
if ($tmp_data = $db->query("select id,title,link,isshow from sys_menu where id in ($menu_id_list) and isshow=1 order by sort")) {
	foreach ($tmp_data as $tmp_line) {
		$menu_data[$tmp_line["id"]] = array($tmp_line["title"], $tmp_line["link"]);
	}
}

// 验证&删除多余mid:
foreach ($menu_stru as $mainid => $mlevel1) {
	if (!array_key_exists($mainid, $menu_data)) {
		unset($menu_stru[$mainid]); continue;
	}
	foreach ($mlevel1 as $key => $itemid) {
		if (!array_key_exists($itemid, $menu_data)) {
			unset($mlevel1[$key]);
		}
	}
	$menu_stru[$mainid] = array_merge($mlevel1);
}
$menu_mids = FastJSON::convert(array_keys($menu_stru));
$menu_stru_json = FastJSON::convert($menu_stru);
$menu_data_json = FastJSON::convert($menu_data);

// 快捷菜单:
$shortcut_data = array();
if ($uinfo["shortcut"]) {
	$shortcut_data = explode(",", $uinfo["shortcut"]);
} else {
	$tmp_data = $db->query("select id from sys_menu where type=0 and id in ($menu_id_list) and shortcut=1 and isshow=1 order by sort limit 8");
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
$submenu_pos = 1;
$is_show_logobar = 1;
$is_show_navibar = 0;
$is_show_footer = 0;
$ukey_sn = $_SESSION[$cfgSessionName]["ukey_sn"];

?>
<!DOCTYPE html>
<!-- <!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd"> -->
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title><?php echo $cfgSiteName; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<meta name="wap-font-scale" content="no">
<!-- <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" /> -->
<link href="lib/frame.css" rel="stylesheet" type="text/css">
<style type="text/css">
html {overflow:auto !important; }
.body_no_scroll {overflow:hidden !important; }
#sys_top_menu {left:12px; }
.h_hover {padding:0px 4px 0px 4px; border:1px solid #bfbfbf; border-bottom:0px; background:white; }
.h_list {width:186px; overflow:hidden; border:1px solid #bfbfbf; height:500px; overflow-y:auto; position:absolute; background:white; }
.h_list a {width:186px; overflow:hidden; }
.ha {line-height:16px; display:block; text-decoration:none; padding:3px 3px 1px 3px; }
.ha:hover {background:#e4eff8; text-decoration:none; }
</style>
<script language="javascript">
var menu_mids = <?php echo $menu_mids; ?>;
var menu_stru = <?php echo $menu_stru_json; ?>;
var menu_data = <?php echo $menu_data_json; ?>;
var menu_shortcut = [<?php echo $menu_shortcut; ?>];
var show_dyn_menu = <?php echo $is_show_dyn_menu ? 1 : 0; ?>;
var show_shortcut = <?php echo $is_show_shortcut ? 1 : 0; ?>;
var client_ukey_sn = "<?php echo $ukey_sn; ?>";
</script>
<script type="text/javascript">
if(top != self) {
	location.href = "about:blank";
}
</script>
<script language="javascript" src="lib/frame.js?v=5.0.11"></script>
<script language="javascript" src="lib/menu.js"></script>
<script language="javascript" src="lib/drag.js"></script>
<script type="text/javascript">
function frame_loaded_do(oframe) {
	show_status('');
	oframe.style.height = "500px";
	var h1 = oframe.contentWindow.document.documentElement.scrollHeight;
	var h2 = oframe.contentWindow.document.documentElement.offsetHeight;
	var h = Math.max(h1, h2);
	//document.title = h1+","+h2;
	oframe.style.height = (h + 10)+"px";
}

function frame_auto_height() {
	// nothing
}

function set_body_height() {
	//var iframe = document.getElementById("sys_frame");
	//var bHeight = iframe.contentWindow.document.body.offsetHeight;
	//var dHeight = iframe.contentWindow.document.documentElement.offsetHeight;
	//var height = Math.max(bHeight, dHeight);
	//iframe.style.height = (height + 20)+"px";
}

function update_title(obj) {
	if (!obj) {
		obj = byid("dl_set_iframe");
	}
	var id = obj.id;
	var subWeb = document.frames ? document.frames[id].document : obj.contentDocument;
	if(obj != null && subWeb != null) {
		byid("dl_box_title").innerHTML = subWeb.title;
	}

	var h1 = obj.contentWindow.document.documentElement.scrollHeight;
	var h2 = obj.contentWindow.document.documentElement.offsetHeight;
	var h = Math.max(h1, h2);
	//document.title = h1+","+h2;
	obj.style.height = (h+10)+"px";
	byid("dl_box_div").style.height = (h+30)+"px";
	//set_center(byid("dl_box_div"));
}

function load_src(isshow, src, w, h) {
	if (isshow) {
		var wsize = get_size();
		var width = wsize[0];
		var height = Math.max(wsize[1], wsize[3]);
		var wh = Math.min(wsize[1], wsize[3]);

		if (!w) {
			if (width < 1200) {
				w = width - 60;
			} else {
				w = width - 300;
			}
		}
		if (w > 900) {
			w = 900;
		}
		if (!h || (h > (wh - 60))) {
			h = wh - 60;
		}

		var ow = Math.max(200, w); //弹出的宽度
		var oh = Math.max(100, h); //弹出的高度

		byid("dl_content").style.display = "none";

		byid("dl_layer_div").style.top = byid("dl_layer_div").style.left = "0px";
		byid("dl_layer_div").style.width = width+"px";
		byid("dl_layer_div").style.height = height+"px";
		byid("dl_layer_div").style.display = "block";

		byid("dl_box_div").style.left = (width-ow-16)/2;
		byid("dl_box_div").style.top = 30;
		byid("dl_box_div").style.width = ow + "px";
		byid("dl_box_div").style.height = oh + "px";
		byid("dl_box_div").style.display = "block";


		byid("dl_iframe").style.display = "block";
		byid("dl_set_iframe").src = src;
		byid("dl_set_iframe").style.height = oh - 30 + "px";
		//timer_box = setInterval("reset_iframe_size()", 100);

		set_center(byid("dl_box_div"));

		document.documentElement.className = "body_no_scroll";

	} else {
		document.documentElement.className = "";
		byid("dl_layer_div").style.display = "none";
		byid("dl_box_div").style.display = "none";
		byid("dl_set_iframe").src = "about:blank";
		try {
			clearInterval(timer_box);
		} catch (e) {
			return;
		}
	}
}
</script>
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
		<div id="sys_top_menu"></div>
		<div id="sys_top_menu_right"><a href="javascript:;" onclick="update_content()" class="ml10" title="刷新页面">刷新</a></div>
		<div class="clear"></div>
	</div>
	<div class="tline right"></div>
	<div class="clear"></div>
</div>

<div id="main_bar">
	<div id="side_menu" class="left_menu" style="display:none;">
		<div id="sys_left_menu"></div>
		<div id="sys_shortcut"></div>
		<div id="sys_online" style="display:none;"></div>
		<div id="sys_notice"></div>
	</div>
	<div id="frame_content" style="border-left:0px;"><iframe id="sys_frame" name="main" onload="frame_loaded_do(this)" src="" mid="" framesrc="" frameborder="0" scrolling="auto" width="100%" height="1000" onreadystatechange="update_navi()"></iframe></div>
	<div class="clear"></div>
</div>

<div id="bottom_border" class="co_bottom">
	<div class="co_left_bottom"></div>
	<div class="co_right_bottom"></div>
	<div class="clear"></div>
</div>

<?php if ($debug_mode) { ?>
<div id="log" style="width:300px; height:600px; position:absolute; right:10px; bottom:10px; z-index:100000; border:2px solid silver; background:white; padding:5px; overflow:auto; display:none; "></div>
<?php } ?>


<!-- loading status table -->
<table id="sys_loading" style="display:none; position:absolute; border:1px solid #00D5D5; background:#D9FFFF; line-height:120%"><tr><td style="padding:1px 0 0 6px"><img src='image/loading.gif' width='16' height='16' align='absmiddle' /></td><td id="sys_loading_tip" style="padding:2px 6px 0px 6px"></td></tr>
</table>

<!-- sys dialog box -->
<div id="dl_layer_div" title="" onclick="load_src(0);" style="position:absolute; filter:Alpha(opacity=70); display:none; background:#404040; z-index:998; opacity:0.7;"></div>
<div id="dl_box_div" class="obox" style="position:absolute; display:none; z-index:999">
	<div id="dl_box_title_box">
		<div id="dl_box_title"></div>
		<div id="dl_box_op"><a href="javascript:load_box(0);">关闭</a></div>
		<div class="clear"></div>
	</div>
	<div id="dl_box_loading" style="position:absolute; display:none;"><img src="image/loading.gif" align="absmiddle"> 加载中，请稍候... </div>
	<div id="dl_iframe"><iframe src="about:blank" frameborder="0" scrolling="auto" width="100%" id="dl_set_iframe" onload="update_title(this)"></iframe></div>
	<div id="dl_content" style="display:none;"></div>
</div>

<!-- msg_box -->
<div id="sys_msg_box" style="display:none; position:absolute;cursor:pointer;" onclick="msg_box_hide()" onmouseover="msg_box_hold()" onmouseout="msg_box_delay_hide()" title="点击关闭">
	<table cellpadding="0">
		<tr>
			<td class="left_div"></td>
			<td class="center_div"><table><tr><td id="sys_msg_box_content"></td></tr></table></td>
			<td class="right_div"></td>
		</tr>
	</table>
</div>

<script language="JavaScript">
dom_loaded.load(init);

if (byid("dl_box_div")) {
	Drag.init(byid("dl_box_title_box"), byid("dl_box_div"));
}
</script>

<!-- Design by zhuwenya (weelia@126.com) -->
<!-- Frame loaded time: <?php echo date("Y-m-d-H-i-s"); ?> -->

</body>
</html>