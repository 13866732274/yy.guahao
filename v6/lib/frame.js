// --------------------------------------------------------
// - 功能说明 : Frame框架函数
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-05-14 => 2012-12-11
// --------------------------------------------------------
var s_split = " <img src='image/word_spacer.gif' width='7' height='13' align='absmiddle'> ";
var click_link = "javascript:void(0);";
var navi_pre = "<font color='red'>您的位置:</font> ";
var loading_pre = "<img src='image/loading.gif' width='16' height='16' align='absmiddle'> ";

function byid(id) {
	return document.getElementById(id);
}

function byname(name) {
	return document.getElementsByTagName(name);
}

function preload(image_list) {
	var im_count = image_list.length;
	var im = new Array();
	for (var ni = 0; ni < im_count; ni++) {
		im[ni] = new Image();
		im[ni].src = "./image/" + image_list[ni];
	}
}

// 字符串的trim压缩空格函数
String.prototype.trim = function () {
	return this.replace(/(^\s*)|(\s*$)/g, "");
}
String.prototype.ltrim = function () {
	return this.replace(/(^\s*)/g, "");
}
String.prototype.rtrim = function () {
	return this.replace(/(\s*$)/g, "");
}

// 检查一个字符串是否在数组中
function in_array(str, arr) {
	if (!arr.length || arr.length == 0) {
		return false;
	}
	for (var i = 0; i < arr.length; i++) {
		if (str == arr[i]) {
			return true;
		}
	}
	return false;
}

function set_cookie(name, value, time) { //单位秒
	var date = new Date();
	date.setTime(date.getTime() + time * 1000);
	window.document.cookie = name + "=" + escape(value) + ";expires=" + date.toUTCString();
}

function get_cookie(name) {
	var result = "";
	var array = window.document.cookie.split(";");
	var length = array.length;
	for (var i = 0; i < length; i++) {
		var nv = array[i].split("=");
		if (nv[0].trim() == name) {
			result = unescape(nv[1]);
		}
	}
	return result;
}

function delete_cookie(name) {
	window.document.cookie = name + "=;expires=" + (new Date(0)).toUTCString();
}

function init_top_menu() {
	var a_menu = Array();
	var ni = 0;
	for (var i in menu_mids) {
		var mid = menu_mids[i];
		if (menu_data[mid]) {
			var has_sub_menu = menu_stru[mid] != "";
			if (has_sub_menu) {
				a_menu[ni] = "<a id='mt" + mid + "' href='" + click_link + "' onclick='load(" + menu_stru[mid][0] + ");return false'"; //加载第一个子菜单的链接
			} else {
				a_menu[ni] = "<a id='mt" + mid + "' href='" + click_link + "' onclick='load(" + mid + ");return false'";
			}
			if (show_dyn_menu && has_sub_menu) {
				a_menu[ni] += " onmouseover='dropdownmenu(this, event, menu" + mid + ", \"150px\")' onmouseout='delayhidemenu()'";
			}
			a_menu[ni] += " onfocus='this.blur();'>" + menu_data[mid][0] + "</a>";
			ni++;

			if (show_dyn_menu && has_sub_menu) {
				eval("menu" + mid + "=Array();");
				var cnt = 0;
				for (var nm in menu_stru[mid]) {
					eval("menu" + mid + "[" + cnt + "]=\"<a href='" + click_link + "' onclick='load(" + menu_stru[mid][nm] + ");return false'>" + menu_data[menu_stru[mid][nm]][0] + "</a>\";");
					cnt++;
				}
			}
		}
	}
	byid("sys_top_menu").innerHTML = a_menu.join(s_split);
	//document.body.innerHTML = '<textarea>'+byid("sys_top_menu").innerHTML+'</textarea>';
}

function load(mid) {
	var is_load_url = (arguments.length == 1 ? 1 : arguments[1]);

	/* ----------------挪走了----------------- */

	// 加载当前页面:
	if (is_load_url && menu_data[mid][1]) {
		show_status("加载中，请稍候...");
		byid("sys_frame").mid = mid;
		byid("sys_frame").src = menu_data[mid][1];
		byid("sys_frame").framesrc = menu_data[mid][1];
		msg_box_hide();
	}
}


function build_left_shortcut() {
	// 建立快捷菜单:
	if (show_shortcut && menu_shortcut) {
		var shortcut_tmp = "<table class='leftmenu_2'><tr><td class='head'>常用功能</td></tr>";
		for (var ni in menu_shortcut) {
			item_mid = menu_shortcut[ni];
			//if (!menu_data[item_mid] || (get_parent_mid(item_mid) == top_level_mid)) {
			//	continue;
			//}
			shortcut_tmp += "<tr><td class='item' onmouseover='mi(this)' onmouseout='mo(this)'><a id='ms" + item_mid + "' href='" + click_link + "' onclick='load(" + item_mid + "," + get_parent_mid(item_mid) + ");return false' class=''>" + menu_data[item_mid][0] + "</a></td></tr>";
		}
		byid("sys_shortcut").innerHTML = shortcut_tmp;
		byid("sys_shortcut").style.display = "block";
	} else {
		byid("sys_shortcut").innerHTML = '';
		byid("sys_shortcut").style.display = "none";
	}
}


function select_to_menu(mid, top_level_mid) {
	//top_level_mid = get_parent_mid(mid);
	mid_is_top = (top_level_mid == mid ? 1 : 0);
	top_level_mid = mid_is_top ? mid : top_level_mid;

	// 顶部当前菜单加红显示:
	var e = byid("sys_top_menu").getElementsByTagName("a");
	for (var i in e) {
		e[i].className = e[i].id == ("mt" + top_level_mid) ? "red" : "";
		if (e[i].id == ("mt" + top_level_mid)) {
			high_light_obj = e[i];
		}
	}

	// 建立左侧链接:
	var has_sub_menu = menu_stru[top_level_mid].length;
	if (has_sub_menu && menu_data[top_level_mid]) {
		var left_menu = "<table class='leftmenu_1'><tr><td class='head'>" + menu_data[top_level_mid][0] + "</td></tr>";
		for (var nm in menu_stru[top_level_mid]) {
			left_menu += "<tr><td class='item' onmouseover='mi(this)' onmouseout='mo(this)'><a id='ml" + menu_stru[top_level_mid][nm] + "' href='" + click_link + "' onclick='load(" + menu_stru[top_level_mid][nm] + ");return false' class=''>" + menu_data[menu_stru[top_level_mid][nm]][0] + "</a></td></tr>";
		}
		left_menu += "</table>";
		byid("sys_left_menu").innerHTML = left_menu;
		byid("sys_left_menu").style.display = "block";
		if (!mid_is_top) {
			byid("ml" + mid).className = "red";
		}
	} else {
		byid("sys_left_menu").innerHTML = '';
		byid("sys_left_menu").style.display = "none";
	}
	build_left_shortcut();
}


function load_url(url, navi) {
	show_status("页面加载中，请稍候...");

	byid("sys_frame").mid = 0;
	byid("sys_frame").src = url;
	byid("sys_frame").framesrc = url;
	msg_box_hide();
	byid("sys_frame").onreadystatechange = function () { update_navi(1); }
}

function get_parent_mid(mid) {
	for (var pmid in menu_stru)
		for (var nm in menu_stru[pmid])
			if (menu_stru[pmid][nm] == mid)
				return pmid;
	return 0;
}

function show_status(string) {
	var o = byid("sys_loading");
	if (string != '') {
		byid("sys_loading_tip").innerHTML = string;
		o.style.display = "block";
		byid("sys_loading").style.left = get_position(byid("logo_bar"), "left") + byid("logo_bar").offsetWidth - byid("sys_loading").offsetWidth - 3 + "px";
		byid("sys_loading").style.top = get_position(byid("logo_bar"), "top") + byid("logo_bar").offsetHeight - byid("sys_loading").offsetHeight - 1 + "px";
	} else {
		if (o) o.style.display = "none";
		if (byid("sys_loading_tip")) byid("sys_loading_tip").innerHTML = '';
	}
}

function frame_loaded_do(oframe) {
	if (window.frame_base_height) {
		oframe.style.height = window.frame_base_height + "px";
	}
	var url = oframe.contentWindow.document.location.href;
	if (url != '' && url.substring(0, 7) == "http://") {
		set_cookie("last_visit_src", url, 99999999);
	}
	show_status('');
}

function clk(obj) {
	// ...
}

function frame_auto_height() {
	var iframe = document.getElementById("sys_frame");
	try {
		var bHeight = iframe.contentWindow.document.body.scrollHeight;
		var dHeight = iframe.contentWindow.document.documentElement.scrollHeight;
		var height = Math.max(bHeight, dHeight);
		iframe.style.height = height + "px";

		// make message box always in center 2009-04-06 13:03
		if (byid("sys_msg_box").style.display == "block") {
			set_center(byid("sys_msg_box"));
		}
	} catch (ex) {
		//...
	}
}

function update_navi(is_focus) {
	//if (byid("sys_frame").readyState == "loading") {
	//byid("sys_frame").style.height = window.frame_base_height+"px";
	//}

	if (byid("sys_frame").readyState == "complete") {
		//alert("wait 1");
		//alert();
		if (typeof (byid("sys_frame").contentWindow.location.href) == typeof ('')) {
			var real_src = byid("sys_frame").contentWindow.location.href;
		} else {
			var real_src = byid("sys_frame").src;
		}
		//alert(real_src);
		var frame_src = byid("sys_frame").framesrc;
		//alert(frame_src);
		if (typeof (real_src) == typeof ('')) {
			real_src = real_src.split('/').reverse()[0];
			if (is_focus || real_src != frame_src) {
				var local_findit = false;
				for (var mid in menu_data) {
					if (menu_data[mid][1] == real_src) {
						byid("sys_frame").mid = mid;
						var findit = false;
						for (var main_id in menu_stru) {
							if (main_id == mid) {
								update_navi_status(main_id, 0, menu_data[main_id][0]);
								local_findit = true;
								break;
							} else {
								for (var nm in menu_stru[main_id]) {
									item_id = menu_stru[main_id][nm];
									if (item_id == mid) {
										update_navi_status(main_id, item_id, menu_data[main_id][0] + "," + menu_data[item_id][0]);
										local_findit = findit = true;
										break;
									}
								}
								if (findit) {
									break;
								}
							}
						}
						break;
					}
				}

				// If not find it,request to server
				if (!local_findit) {
					//oAjax = new ajax();
					//oAjax.connect("http/get_page_info.php", "GET", "p="+escape(real_src)+"&r="+Math.random(), update_navi_do);
				}
			}
		}
	}
}

function update_navi_do(oAjax) {
	try { eval("var aNaviInfo=" + oAjax.responseText + ";"); } catch (e) { return false; }
	if (typeof (byid("sys_frame").contentWindow.location.href) == typeof ('')) {
		var src = byid("sys_frame").contentWindow.location.href;
	} else {
		var src = byid("sys_frame").src;
	}
	var now_url = src.split('/').reverse()[0];
	if (aNaviInfo["url"] == now_url) {
		update_navi_status(aNaviInfo["top_mid"], aNaviInfo["left_mid"], aNaviInfo["navi"]);
	}
}

function update_navi_status(top_mid, left_mid, navi_string) {
	if (top_mid > 0 || left_mid > 0) {
		var now_url = byid("sys_frame").contentWindow.location.href.split('/').reverse()[0];
		byid("sys_frame").framesrc = now_url;
		load(((left_mid > 0 && menu_data[left_mid]) ? left_mid : top_mid), 0);
	}
}

/*
function make_navi(string) {
	var navi_split = " → ";
	var title_array = ('管理后台,'+string).split(',');
	for (var n in title_array) {
		title_array[n] = '<b>'+title_array[n]+'</b>';
	}
	byid("sys_navi").innerHTML = navi_pre+title_array.join(navi_split);
}
*/

function load_js_file(src, id, loaded_fn) {

	var headerDom = document.getElementsByTagName('head').item(0);
	var jsDom = document.createElement('script');
	jsDom.type = 'text/javascript';
	jsDom.src = src;
	if (id) {
		jsDom.id = id;
	}

	headerDom.appendChild(jsDom);

	if (loaded_fn) {
		if (!document.all) {
			jsDom.onload = function () {
				loaded_fn();
			}
		} else {
			jsDom.onreadystatechange = function () {
				if (jsDom.readyState == 'loaded' || jsDom.readyState == 'complete') {
					loaded_fn();
				}
			}
		}
	}
}

function init() {
	// 原始页面标题:
	ori_doc_title = document.title;

	set_body_height();
	reg_event(window, "resize", set_body_height);

	init_top_menu();
	get_online();

	// init loading status bar:
	//preload("image/loading.gif".split(","));

	var last_src = get_cookie("last_visit_src");
	if (last_src) {
		byid("sys_frame").src = last_src;
		//build_left_shortcut();
	} else {
		byid("sys_top_menu").getElementsByTagName("A")[0].onclick();
	}

	/*
	if ((guess_mid = location.href.split("#")[1]) && menu_data[guess_mid]) {
		load(guess_mid);
	} else {
		var is_load = false;
		byid("sys_top_menu").getElementsByTagName('A')[0].onclick();
		is_load = true;
	}
	*/
}

function get_int_time() {
	var d = new Date();
	return Math.round(d.getTime() / 1000, 0);
}

function get_display_time() {
	var t = new Date();
	var y = t.getYear();
	var m = t.getMonth() + 1;
	var d = t.getDate();
	var h = t.getHours();
	var i = t.getMinutes();
	var s = t.getSeconds();
	var ms = t.getMilliseconds();
	m = (m < 10 ? '0' : '') + m;
	d = (d < 10 ? '0' : '') + d;
	h = (h < 10 ? '0' : '') + h;
	i = (i < 10 ? '0' : '') + i;
	s = (s < 10 ? '0' : '') + s;
	ms = (ms < 10 ? '00' : (ms < 100 ? '0' : '')) + ms;
	return y + "-" + m + "-" + d + " " + h + ":" + i + ":" + s + ' ' + ms;
}

function log(s) {
	if (debugOnline && byid("log")) {
		if (byid("log").style.display == "none") {
			byid("log").style.display = "block";
		}
		byid("log").innerHTML = get_display_time() + " " + s + "<br>" + byid("log").innerHTML;
	}
}


// 时间周期设置
var getOnlineInterval = 30; //请求周期 s
var getOnlineTimeout = 10; //请求后过多少时间算超时 s
var getOnlineInterval_small = 1; //循环间隔时间
var debugOnline = 0; //是否开启调试

// 变量初始化
var getOnlineLastRequest = 0;
var getOnlineTimer = 0;
var onlineErrorTimer = 0;
var isGetOnlineTimeout = 0; //是否超时标记

var headerDom = document.head || document.getElementsByTagName("head")[0] || document.documentElement;

// 在线信息:
function get_online() {
	if (getOnlineTimer) clearInterval(getOnlineTimer);

	if (get_int_time() - getOnlineLastRequest >= getOnlineInterval) {
		if (byid("js_online_info")) {
			byid("js_online_info").parentNode.removeChild(byid("js_online_info"));
		}

		if (isGetOnlineTimeout) {
			document.title = "正在请求";
		}

		log('开始请求 get_online.php');

		jsGetOnline = document.createElement('script');
		jsGetOnline.type = 'text/javascript';
		jsGetOnline.src = "http/get_online.php?r=" + Math.random();
		jsGetOnline.id = "js_online_info";
		headerDom.insertBefore(jsGetOnline, headerDom.firstChild);

		onlineLastSendTime = get_int_time();
		onlineErrorTimer = setTimeout("get_online_error()", getOnlineTimeout * 1000); //超时
	} else {
		if (isGetOnlineTimeout) {
			document.title = "请求超时 " + ((getOnlineInterval) - (get_int_time() - getOnlineLastRequest)) + "秒后再试";
		}
		log('周期未到，稍后继续');
		getOnlineTimer = setInterval("get_online()", getOnlineInterval_small * 1000); //时间没到，一会再试试
	}
}


w_timer_id = 0;

function w_title_show() {
	clean_title_show();
	w_timer_id = setTimeout("title_show()", 200);
}

function title_show() {
	document.title = show_title;
	show_title = show_title.substring(1, show_title.length) + show_title.substring(0, 1);
	w_timer_id = setTimeout("title_show()", 200);
}

function clean_title_show() {
	if (w_timer_id) {
		clearTimeout(w_timer_id);
	}
	document.title = ori_doc_title;
}


function play_new_data_music() {
	try {
		var mp3 = '/v6/lib/mind.mp3';
		var myAudio = new Audio();
		myAudio.setAttribute('src', mp3);
		myAudio.volume = 1;
		myAudio.play();
	} catch (e) {
		var wma = '/v6/lib/mind.wma';
		var o = byid("player_area");
		o.innerHTML = '<embed src="' + wma + '" autostart="true" loop="0" volume="100" width="0" height="0"></embed>';
	}
}


// 在线信息处理结果:
function get_online_do(out) {
	isGetOnlineTimeout = 0;
	document.title = ori_doc_title;
	log('请求成功，费时：' + (get_int_time() - onlineLastSendTime) + "s ----");
	clearTimeout(onlineErrorTimer); //立即停止调用无响应函数
	clearTimeout(getOnlineTimer); //立即其它加载
	getOnlineLastRequest = get_int_time(); //更新上次请求时间

	log("请求间隔；" + (getOnlineInterval));
	getOnlineTimer = setInterval("get_online()", getOnlineInterval_small * 1000); //稍后继续下一次请求

	if (out["status"] == 'ok') {
		if (out["mo_catch_num"] > 0) {
			show_title = "[" + out["mo_catch_num"] + "条抓取手机号待回访]………";
			w_title_show();
			play_new_data_music();
		} else {
			show_title = "";
			clean_title_show();
		}
		if (out["online_list"]) {
			show_online_list(out["online_list"]);
		}
		if (out["online_message"]) {
			show_online_message(out["online_message"]);
		}
		if (out["alert"]) {
			msg_box(out["alert"], 3); //显示消息
		}
	}

	if (out["status"] == "logout") {
		//alert("服务器端已经退出，请您重新登录！");
		top.location = "/v6/login.php";
	}
}

// 获取在线消息错误:
function get_online_error() {
	isGetOnlineTimeout = 1;
	document.title = "请求超时";
	log('请求超时，稍后继续');
	if (byid("js_online_info")) {
		if (headerDom && jsGetOnline.parentNode) {
			headerDom.removeChild(jsGetOnline);
		}

		jsGetOnline = undefined;
	}
	clearInterval(getOnlineTimer);
	getOnlineLastRequest = get_int_time() + 10; //可能这会服务器卡，时间长点再试
	log("请求间隔；" + (getOnlineInterval + 10));
	getOnlineTimer = setInterval("get_online()", getOnlineInterval_small * 1000); //继续请求
}


// 现在在线用户列表:
function show_online_list(aOnline) {
	if (typeof (aOnline) == typeof (window)) {
		var string = "<table width='100%' class='leftmenu_online'>";
		string += "<tr><td class='head'>在线用户</td></tr>";
		for (var n in aOnline) {
			string += "<tr><td class='item' onmouseover='mi(this)' onmouseout='mo(this)'>";
			string += "<a href='#' onclick=\"load_box(1, 'src', 'sys_admin_view_web.php?name=" + n + "'); return false;\" title='" + (aOnline[n]["isowner"] != 1 ? "查看用户资料" : "查看我的资料") + "'>" + aOnline[n]["realname"] + "</a>";
			if (aOnline[n]["isowner"] != 1) {
				string += "&nbsp;<a href='#' onclick=\"online_talk('" + n + "','" + aOnline[n]["realname"] + "');return false;\" class='talk' title='点击发送消息'>[交谈]</a>";
			}
			string + "</td></tr>";
		}
		string += "</table>";
	} else {
		var string = "<div class='online_tips'>没有其他用户在线</div>";
	}
	if (typeof (byid("sys_online")) == typeof (window) && string) {
		byid("sys_online").innerHTML = string;
	}
}

// 显示收到的消息:
function show_online_message(aMess) {
	if (typeof (aMess) == typeof (window)) {
		var string = '';
		for (var mid in aMess) {
			if (!mid) continue;
			string += "<table width='100%' id='online_mess_" + mid + "' style='border:1px solid #E1E1E1'><tr><td width='75%' style='padding:3px 6px 3px 6px' style='color:#000000'>" + aMess[mid]["time"] + "&nbsp;<font color='#FF5F11'><b>" + aMess[mid]["realname"] + "</b></font> 说：</td><td width='25%' align='right' style='padding-right:6px'><a href='#' onclick=\"online_talk('" + aMess[mid]["fromname"] + "','" + aMess[mid]["realname"] + "'," + mid + ");return false\" class='talk_op'>[回复]</a>&nbsp;<a href='#' onclick='online_talk_close(" + mid + ");return false' title='我已经阅读,不要再显示' class='talk_op'>[关闭]</a></td></tr><tr><td colspan='2' style='padding:6px' style='color:#000000'>";
			if (aMess[mid]["link"] != '') {
				string += "<a href='" + aMess[mid]["link"] + "' target='main'><b>" + aMess[mid]["content"] + "</b></a>";
			} else {
				string += "<b>" + aMess[mid]["content"] + "</b>";
			}
			string += "</td></tr></table>";
		}

		var obj = byid("online_message");
		if (string && typeof (obj) == typeof (window)) {
			obj.innerHTML += string;
			obj.style.display = "block";
			var wsize = get_size();
			var psize = get_scroll();
			obj.style.top = psize[1] + wsize[3] - obj.offsetHeight - 5 + "px";
			obj.style.left = wsize[0] - obj.offsetWidth - 20 + "px";
		}
	}
}

// 消息提示位置控制:
function message_list_keep_position() {
	var obj = byid("online_message");

	// 显示的有消息时才执行:
	if (obj.style.display == "block" && obj.innerHTML != '') {
		var wsize = get_size();
		var psize = get_scroll();
		obj.style.top = psize[1] + wsize[3] - obj.offsetHeight - 5 + "px";
		obj.style.left = wsize[0] - obj.offsetWidth - 20 + "px";
	}
}

// 显示一个“弹出”对话框:
// type = "src|str"  "src":iframe.src, "str":string, innerHTML
//function load_box(isshow, type, src_or_str, params_or_title) {
//	if (isshow) {
//		var wsize = get_size();
//		var width = wsize[0];
//		var height = Math.max(wsize[1], wsize[3]);
//
//		$("dl_layer_div").style.top = $("dl_layer_div").style.left = "0px";
//		$("dl_layer_div").style.width = width+"px";
//		$("dl_layer_div").style.height = height+"px";
//		$("dl_layer_div").style.display = "block";
//
//		$("dl_box_div").style.left = (width-584)/2;
//		$("dl_box_div").style.top = 150;
//		$("dl_box_div").style.display = "block";
//
//		$("dl_iframe").style.display = $("dl_content").style.display = "none";
//		if (type == "src") {
//			setTimeout(function() {$("dl_set_iframe").src = src_or_str+(params_or_title ? "?"+params_or_title : '');}, 20);
//			$("dl_box_loading").style.display = "block";
//			$("dl_box_title").innerHTML = "加载中...";
//			$("dl_box_div").style.height = $("dl_box_title_box").offsetHeight + $("dl_box_loading").offsetHeight + "px";
//			timer_box = setTimeout("reset_iframe_size()", 100);
//		} else {
//			//$("dl_content").innerHTML = src_or_str;
//			setInnerHTML($("dl_content"), src_or_str);
//			$("dl_content").style.display = "block";
//			$("dl_box_loading").style.display = "none";
//			$("dl_box_title").innerHTML = params_or_title;
//			$("dl_box_div").style.height = $("dl_box_title_box").offsetHeight + $("dl_content").offsetHeight + "px";
//		}
//
//		set_center($("dl_box_div"));
//
//	} else {
//		$("dl_layer_div").style.display = "none";
//		$("dl_box_div").style.display = "none";
//		$("dl_set_iframe").src = "about:blank";
//		try {
//			clearInterval(timer_box);
//		} catch (e) {
//			return;
//		}
//	}
//}











// xmlhttp函数的封装
function ajax() {
	var xm, bC = false;
	try { xm = new ActiveXObject("Msxml2.XMLHTTP") } catch (e) { try { xm = new ActiveXObject("Microsoft.XMLHTTP") } catch (e) { try { xm = new XMLHttpRequest() } catch (e) { xm = false } } }
	if (!xm) return null; this.connect = function (sU, sM, sV, fn) {
		if (!xm) return false; bC = false; sM = sM.toUpperCase();
		try {
			if (sM == "GET") { xm.open(sM, sU + "?" + sV, true); sV = "" } else {
				xm.open(sM, sU, true);
				xm.setRequestHeader("Method", "POST " + sU + " HTTP/1.1");
				xm.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8")
			}
			xm.onreadystatechange = function () { if (xm.readyState == 4 && !bC) { bC = true; if (xm.status == 200) { fn(xm) } else { window.status = "ajax error status code: " + xm.status } } };
			xm.send(sV)
		} catch (z) { return false } return true
	}; return this;
}

function get_position(what, offsettype) {
	var pos = { "left": what.offsetLeft, "top": what.offsetTop };
	var parentEl = what.offsetParent;
	while (parentEl != null) {
		pos.left += parentEl.offsetLeft;
		pos.top += parentEl.offsetTop;
		parentEl = parentEl.offsetParent;
	}
	if (offsettype) {
		return offsettype == "left" ? pos.left : pos.top;
	} else {
		return pos;
	}
}

function get_online_list() {
	var obj = document.getElementById("sys_online");
	if (obj.innerHTML == "") {
		obj.innerHTML = "<div class='online_tips'>正在读取在线用户...</div>";
	}
	oAjax = new ajax();
	oAjax.connect("http/get_online_list.php", "GET", "r=" + Math.random(), get_online_list_do);
}

//function get_online_list_do(oAjax) {
//	try {eval("var aOnline="+oAjax.responseText+";");} catch (e) {return false; }
//	if (typeof(aOnline) == typeof(window)) {
//		var string = "<table width='100%' class='leftmenu_online'>";
//		string += "<tr><td class='head'>在线用户</td></tr>";
//		for (var n in aOnline) {
//			string += "<tr><td class='item' onmouseover='mi(this)' onmouseout='mo(this)'>";
//			string += "<a href='#' onclick=\"load_url('sys_admin_view.php?name="+n+"','在线用户,用户资料'); return false;\" title='"+(aOnline[n]["isowner"] != 1 ? "查看用户资料" : "查看我的资料")+"'>"+aOnline[n]["realname"]+"</a>";
//			if (aOnline[n]["isowner"] != 1) {
//				string += "&nbsp;<a href='#' onclick=\"online_talk('"+n+"','"+aOnline[n]["realname"]+"');return false;\" class='talk' title='点击发送消息'>[交谈]</a>";
//			}
//			string + "</td></tr>";
//		}
//		string += "</table>";
//	} else {
//		var string = "<div class='online_tips'>没有其他用户在线</div>";
//	}
//	if (typeof(byid("sys_online")) == typeof(window) && string) {
//		byid("sys_online").innerHTML = string;
//	}
//}

function online_talk(username, realname, messid) {
	if (messid == undefined) messid = 0;
	var string = "<table width='100%' border='0' style='border:0px solid #E6E6E6' height='100%'>";
	string += "<tr><td colspan='2' style='padding:4px 3px 2px 6px'>给 <font color='red'><b>" + realname + "</b></font> 发送消息:&nbsp;&nbsp;&nbsp;<a href='#' onclick='show_face(this);return false;'>[表情]</a></td></tr>";
	string += "<tr><td width='20%' align='center'>内容：</td><td width='80%'><textarea id='online_talk_content' style='width:90%;height:80px;' class='input'></textarea></td></tr>";
	string += "<tr><td colspan='2' height='60' align='center'><input type='button' class='submit' onclick=\"online_talk_submit('" + username + "'," + messid + ")\" value='发送消息'>&nbsp;&nbsp;<input type='button' class='submit' onclick='online_talk_hide()' value='取消'></td></tr>";
	string += "</table>";

	//screen_lock();
	//var width = 400; var height = 165;
	//var left = (document.body.clientWidth - width) / 2;
	//var top = document.body.scrollTop + (document.body.clientHeight - height) / 2;
	//dialog_show(string, width, height, left, top);
	//byid("online_talk_content").focus();
	load_box(1, "str", string, "发送消息");
}

function show_face(obj) {
	var odiv = byid("sys_face_list");
	if (odiv && odiv.style.display == "block") {
		odiv.style.display = "none"; return false;
	}
	if (!odiv) {
		var f = "<table border='1' bordercolor='#E7E7E7' cellpadding='1'>";
		var face_count = 135;
		for (var x = 0; x < 9; x++) {
			f += "<tr>";
			for (var y = 0; y < 15; y++) {
				var n = x * 15 + y;
				f += "<td>";
				if (n < face_count) {
					f += "<img src='image/face/" + n + ".gif' onclick='write_face(" + n + ")' style='cursor:pointer'>";
				}
				f += "</td>";
			}
			f += "</tr>";
		}
		f += "</table>";

		var odiv = document.createElement("div");
		odiv.id = "sys_face_list";
		odiv.style.border = "2px solid gray";
		odiv.style.backgroundColor = "white";
		odiv.style.zIndex = "1000";
		odiv.style.position = "absolute";
		odiv.style.left = get_position(obj, "left") - 100;
		odiv.style.top = get_position(obj, "top") + obj.offsetHeight + 3;
		//odiv.style.zIndex = "6";
		odiv.innerHTML = f;
		document.body.appendChild(odiv);
	}
	odiv.style.display = "block";
}

function write_face(face_num) {
	byid("online_talk_content").value += "[" + face_num + "]";
	byid("sys_face_list").style.display = "none";
}

function online_talk_submit(username, messid) {
	var obj = byid("online_talk_content");
	var content = obj.value;
	if (content == "") {
		alert("请输入发送消息的内容！");
		obj.focus();
		return false;
	}
	obj.value = "";
	online_talk_hide();
	oAjax = new ajax();
	oAjax.connect("http/online_talk_submit.php", "POST", "messid=" + messid + "&name=" + username + "&content=" + content, online_talk_submit_do);
}

function online_talk_submit_do(oAjax) {
	var s = oAjax.responseText;
	if (s != "") {
		if (s > 0) {
			online_talk_close(s);
		}
	} else {
		alert("消息发送失败...");
	}
}

function online_talk_hide() {
	parent.load_box(0);
}

function get_online_messages() {
	var lastchecktime = typeof (online_message_lastchecktime) == typeof (0) ? online_message_lastchecktime : 0;
	oAjax = new ajax();
	oAjax.connect("http/get_online_message.php", "GET", "t=" + lastchecktime + "&r=" + Math.random(), get_online_messages_do);
}

function get_online_messages_do(oAjax) {
	try { eval("var aMess=" + oAjax.responseText + ";"); } catch (e) { return false; }
	if (typeof (aMess) == typeof (window)) {
		var string = '';
		for (var mid in aMess) {
			if (!mid) continue;
			string += "<table width='100%' id='online_mess_" + mid + "' style='border:1px solid #E1E1E1'><tr><td width='75%' style='padding:3px 6px 3px 6px' style='color:#000000'>" + aMess[mid]["time"] + "&nbsp;<font color='#FF5F11'><b>" + aMess[mid]["realname"] + "</b></font> 说：</td><td width='25%' align='right' style='padding-right:6px'><a href='#' onclick=\"online_talk('" + aMess[mid]["fromname"] + "','" + aMess[mid]["realname"] + "'," + mid + ");return false\" class='talk_op'>[回复]</a>&nbsp;<a href='#' onclick='online_talk_close(" + mid + ");return false' title='我已经阅读,不要再显示' class='talk_op'>[关闭]</a></td></tr><tr><td colspan='2' style='padding:6px' style='color:#000000'><a href='" + aMess[mid]["link"] + "' target='main'><b>" + aMess[mid]["content"] + "</b></a></td></tr></table>";
		}
		var obj = byid("online_message");
		if (string && typeof (obj) == typeof (window)) {
			obj.innerHTML += string;
			obj.style.display = "block";
			obj.style.top = document.body.scrollTop + document.body.clientHeight - obj.offsetHeight - 5 + "px";
			obj.style.left = document.body.clientWidth - obj.offsetWidth - 20 + "px";
		}
	}
}

function online_talk_close(nMessID) {
	oAjax = new ajax();
	oAjax.connect("http/online_message_close.php", "GET", "id=" + nMessID, online_talk_close_do);
}

function online_talk_close_do(oAjax) {
	var sMessID = oAjax.responseText;
	if (sMessID != "") {
		close_message(sMessID);
	}
}

function close_message(sMessID) {
	var obj = document.getElementById("online_mess_" + sMessID);
	obj.style.display = "none";
	obj = document.getElementById("online_message");
	if (obj.offsetHeight < 10) {
		obj.style.display = "none";
	}
}


function browser_info() {
	var ua, s, i; this.isIE = false; this.isNS = false; this.isOP = false; this.isSF = false; ua = navigator.userAgent.toLowerCase(); s = "opera"; if ((i = ua.indexOf(s)) >= 0) { this.isOP = true; return; } s = "msie"; if ((i = ua.indexOf(s)) >= 0) { this.isIE = true; return; } s = "netscape6/"; if ((i = ua.indexOf(s)) >= 0) { this.isNS = true; return; } s = "gecko"; if ((i = ua.indexOf(s)) >= 0) { this.isNS = true; return; } s = "safari"; if ((i = ua.indexOf(s)) >= 0) { this.isSF = true; return; }
}

function screen_lock() {
	var browser = new browser_info();
	var objScreen = byid("sl_screen_over");
	if (!objScreen) var objScreen = document.createElement("div");
	var oS = objScreen.style;
	objScreen.id = "sl_screen_over";
	oS.display = "block";
	oS.top = oS.left = oS.margin = oS.padding = "0px";
	if (document.body.scrollHeight) {
		var wh = (document.body.scrollHeight < document.body.clientHeight ? document.body.clientHeight : document.body.scrollHeight) + "px";
	} else if (window.innerHeight) {
		var wh = window.innerHeight + "px";
	} else {
		var wh = "100%";
	}
	oS.width = document.body.clientWidth;
	oS.height = wh;
	oS.position = "absolute";
	oS.zIndex = "3";
	if ((!browser.isSF) && (!browser.isOP)) {
		oS.background = "#B0B0B0";
	} else {
		oS.background = "#B4B4B4";
	}
	oS.filter = "alpha(opacity=50)";
	oS.opacity = 40 / 100;
	oS.MozOpacity = 40 / 100;
	document.body.appendChild(objScreen);
	set_select_visible(0);
}

function set_select_visible(show) {
	var visible = show ? "visible" : "hidden";
	var allselect = byname("select");
	for (var i = 0; i < allselect.length; i++) {
		allselect[i].style.visibility = visible;
	}
	var frms = byname("iframe");
	for (var i = 0; i < frms.length; i++) {
		var allselect = frms[i].contentWindow.document.getElementsByTagName("select");
		for (var j = 0; j < allselect.length; j++) {
			allselect[j].style.visibility = visible;
		}
	}
}

function screen_clean() {
	var objScreen = document.getElementById("sl_screen_over");
	if (objScreen) objScreen.style.display = "none";
	set_select_visible(1);
}

function dialog_show(showdata, width, height, left, top) {
	var objDialog = document.getElementById("sl_dialog_move"); if (!objDialog) objDialog = document.createElement("div"); objDialog.id = "sl_dialog_move"; var oS = objDialog.style; oS.display = "block"; oS.top = top + "px"; oS.left = left + "px"; oS.margin = "0px"; oS.padding = "0px"; oS.width = width + "px"; oS.height = height + "px"; oS.position = "absolute"; oS.zIndex = "5"; oS.background = "#FFF"; oS.border = "2px solid #838383"; objDialog.innerHTML = showdata; document.body.appendChild(objDialog);
}

function dialog_hide() {
	screen_clean(); var objDialog = document.getElementById("sl_dialog_move"); if (objDialog) objDialog.style.display = "none";
}

function msg_box(string, showtime) {
	omsg = byid("sys_msg_box");
	if (string == undefined || string == "") {
		return true;
	}
	if (typeof (showtime) == "undefined") {
		var showtime = 5;
	} else {
		if (typeof (showtime) != typeof (0)) showtime *= 1;
		showtime = Math.min(20, Math.max(1, showtime));
	}
	byid("sys_msg_box_content").innerHTML = string;
	omsg.style.display = "block";
	set_center(omsg);
	sys_msg_box_timer = setTimeout("msg_box_hide()", showtime * 1000);
}

function msg_box_hold() {
	clearInterval(sys_msg_box_timer);
}

function msg_box_delay_hide(time) {
	clearInterval(sys_msg_box_timer);
	if (typeof (time) == "undefined") {
		time = 1;
	} else {
		if (typeof (time) != typeof (0)) time *= 1;
		time = Math.min(20, Math.max(1, time));
	}
	sys_msg_box_timer = setTimeout("msg_box_hide()", time * 1000);
}

function msg_box_hide() {
	omsg = byid("sys_msg_box");
	omsg.style.display = "none";
}

function set_center(obj) {
	var objw = obj.offsetWidth;
	var objh = obj.offsetHeight;
	var pscroll = get_scroll();
	var psize = get_size();
	var page_width = psize[0];
	var page_width1 = document.documentElement.clientWidth;
	if (page_width1 && page_width1 > 0) {
		page_width = page_width1;
	}
	var left = (page_width - objw) / 2;
	var top = pscroll[1] + (psize[3] - objh) / 2;
	obj.style.left = left < 0 ? "0px" : left + "px";
	obj.style.top = top < 0 ? "0px" : top + "px";
}

function mi(o) {
	o.style.backgroundColor = "#E8F4FF";
}

function mo(o) {
	o.style.backgroundColor = "";
}

/*
function set_body_height() {
	var all = get_size()[1];
	var main_bar_height = all - byid("top_border").offsetHeight - byid("logo_bar").offsetHeight - byid("menu_bar").offsetHeight - byid("foot_bar").offsetHeight - byid("border_border").offsetHeight - 10;
	byid("main_bar").style.height = main_bar_height + "px";
	window.body_height = main_bar_height;
	window.frame_base_height = byid("frame_content").offsetHeight - byid("sys_navi").offsetHeight - 12 - 20; //iframe的基准高度(刚好填充页面的高度)
}
*/

/*
function set_body_height() {
	var all = get_size()[3];
	var main_bar_height = all - byid("top_border").offsetHeight - byid("logo_bar").offsetHeight - byid("menu_bar").offsetHeight - byid("foot_bar").offsetHeight - byid("border_border").offsetHeight - 6;

	var bw = new browser_info();
	if (!bw.isIE) {
		main_bar_height = main_bar_height - 6;
	}

	byid("main_bar").style.height = main_bar_height + "px";
	var frame_base_height = main_bar_height - 2; //iframe的基准高度(刚好填充页面的高度)
	byid("sys_frame").style.height = frame_base_height+"px";
}
*/

function set_body_height() {
	var all = get_size()[3];
	var main_bar_height = all - byid("top_border").offsetHeight - byid("logo_bar").offsetHeight - byid("menu_bar").offsetHeight - byid("bottom_border").offsetHeight - 6; // 6 是上下padding值

	frame_base_height = main_bar_height - 0; //iframe的基准高度(刚好填充页面的高度)
	byid("frame_content").style.height = frame_base_height + "px";
	byid("sys_frame").style.height = frame_base_height + "px";
	byid("main_bar").style.height = frame_base_height + "px";

	// debug:
	//document.title = all + ", "+main_bar_height + ", "+frame_base_height+", "+byid("bottom_border").offsetHeight;
}

function get_size() {
	var xScroll, yScroll;
	if (window.innerHeight && window.scrollMaxY) {
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight) { // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	// for small pages with total height less then height of the viewport
	if (yScroll < windowHeight) {
		pageHeight = windowHeight;
	} else {
		pageHeight = yScroll;
	}

	if (xScroll < windowWidth) {
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}

	arrayPageSize = new Array(pageWidth, pageHeight, windowWidth, windowHeight)
	return arrayPageSize;
}

function get_scroll() {
	var yScroll;
	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop) {	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array('', yScroll)
	return arrayPageScroll;
}

function show_hide_side() {
	byid("side_menu").style.display = (byid("side_menu").style.display == "none" ? "" : "none");
}


function st(str) {
	str = window.status + " ==> " + str;
	if (str.length > 100) {
		str = str.substring(str.length - 100, str.length);
	}
	window.status = str;
}

var dom_loaded = {
	onload: [],
	loaded: function () {
		if (arguments.callee.done) return;
		arguments.callee.done = true;
		for (i = 0; i < dom_loaded.onload.length; i++) dom_loaded.onload[i]();
	},
	load: function (fireThis) {
		this.onload.push(fireThis);
		if (document.addEventListener)
			document.addEventListener("DOMContentLoaded", dom_loaded.loaded, null);
		if (/KHTML|WebKit/i.test(navigator.userAgent)) {
			var _timer = setInterval(function () {
				if (/loaded|complete/.test(document.readyState)) {
					clearInterval(_timer);
					delete _timer;
					dom_loaded.loaded();
				}
			}, 10);
		}
		/*@cc_on @*/
		/*@if (@_win32)
		var proto = "src='javascript:void(0)'";
		if (location.protocol == "https:") proto = "src=//0";
		document.write("<scr"+"ipt id=__ie_onload defer " + proto + "><\/scr"+"ipt>");
		var script = document.getElementById("__ie_onload");
		script.onreadystatechange = function() {
			if (this.readyState == "complete") {
				dom_loaded.loaded();
			}
		};
		/*@end @*/
		window.onload = dom_loaded.loaded;
	}
};

function swap_node(node1_name, node2_name) {
	var node1 = byid(node1_name);
	var node2 = byid(node2_name);

	var _parent = node1.parentNode;

	var o = _parent.childNodes;
	var _t1 = null, _t2 = null;
	for (var i = 0; i < o.length; i++) {
		if (o[i].id == node1_name && i < o.length - 1) {
			_t1 = o[i + 1];
		}
		if (o[i].id == node2_name && i < o.length - 1) {
			_t2 = o[i + 1];
		}
	}

	if (_t1) {
		_parent.insertBefore(node2, _t1);
	} else {
		_parent.appendChild(node2);
	}
	if (_t2) {
		_parent.insertBefore(node1, _t2);
	} else {
		_parent.appendChild(node1);
	}
}

function co(obj, ty) {
	obj.style.backgroundColor = ty == 1 ? "#BBE9FF" : "";
}


// 显示中间弹出窗口
function show_window(src, w, h) {
	var wsize = get_size();
	var width = wsize[0];
	var height = Math.max(wsize[1], wsize[3]);
	var wh = Math.min(wsize[1], wsize[3]);

	if (!w) {
		w = width < 1200 ? (width - 60) : (width - 300);
	}
	if (!h || (h > (wh - 60))) {
		h = wh - 60;
	}

	var ow = Math.max(200, w); //弹出的宽度
	var oh = Math.max(100, h); //弹出的高度

	byid("dl_content").style.display = "none";

	var layer = byid("dl_layer_div");
	layer.style.top = layer.style.left = "0px";
	layer.style.width = width + "px";
	layer.style.height = height + "px";
	layer.style.display = "block";

	var box = byid("dl_box_div");
	box.style.left = (width - ow - 16) / 2;
	box.style.top = 30;
	box.style.width = ow + "px";
	box.style.height = oh + "px";
	box.style.display = "block";

	byid("dl_iframe").style.display = "block";
	byid("dl_set_iframe").src = src;
	byid("dl_set_iframe").style.height = oh - 30 + "px";

	set_center(byid("dl_box_div"));
}

// 关闭弹出窗口
function close_window() {
	byid("dl_layer_div").style.display = "none";
	byid("dl_box_div").style.display = "none";
	byid("dl_set_iframe").src = "about:blank";
	try {
		clearInterval(timer_box);
	} catch (e) {
		return;
	}
}

// type = "src|str"  "src":iframe.src, "str":string, innerHTML
function load_box(isshow, type, src_or_str, params_or_title) {
	if (isshow) {
		// 已弃用此函数
	} else {
		close_window();
	}
}

function load_src(isshow, src, w, h) {
	if (isshow) {
		show_window(src, w, h);
	} else {
		close_window();
	}
}

function reset_iframe_size(obj) {
	if (!obj) {
		obj = byid("dl_set_iframe");
	}
	var id = obj.id;
	var subWeb = document.frames ? document.frames[id].document : obj.contentDocument;
	try {
		byid("dl_iframe").style.display = "block";
		byid("dl_box_loading").style.display = "none";
	} catch (e) {
		return;
	}
	if (obj != null && subWeb != null) {
		var height = subWeb.body.scrollHeight;
		obj.height = height;
		byid("dl_box_title").innerHTML = subWeb.title;
		byid("dl_iframe").style.height = height + "px";
		byid("dl_box_div").style.height = byid("dl_iframe").offsetHeight + byid("dl_box_title_box").offsetHeight + "px";
	}

	set_center(byid("dl_box_div"));
}


function update_title(obj) {
	if (!obj) {
		obj = byid("dl_set_iframe");
	}
	var id = obj.id;
	var subWeb = document.frames ? document.frames[id].document : obj.contentDocument;
	if (obj != null && subWeb != null) {
		byid("dl_box_title").innerHTML = subWeb.title;
	}
	set_center(byid("dl_box_div"));
}


function reg_event(obj, event_basename, fn) {
	if (document.all) {
		obj.attachEvent("on" + event_basename, fn);
	} else {
		obj.addEventListener(event_basename, fn, false);
	}
}

// 刷新 sys_frame 内的内容:
function update_content() {
	byid("sys_frame").contentWindow.location.reload();
}

// 更新内容页面的局部ID:
// type:  innerHTML | value
function update_content_byid(id, value, type) {
	var o = byid("sys_frame").contentWindow.document.getElementById(id);
	if (o) {
		if (type == "value") {
			o.value = value;
		} else {
			o.innerHTML = value;
		}
	}
}


function sync_cai(hid, mobile, status, order_date) {
	//var url = "http://mis.cloudsapp.cn:8088/ghyydzjk.aspx?id="+hid+"&mobile="+mobile+"&time="+order_date+"&status="+status;
	//load_js_file(url, "cai");
}
