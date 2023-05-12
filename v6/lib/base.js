/*
 - JavaScript 函数库 zhuwenya @ 2012-03-08
*/

/*
 - 第一部分：基础函数，必须
*/
// 获取id对象
function byid(id) {
	return document.getElementById(id);
}

// 设置对象居中
function set_center(obj) {
	var objwidth = obj.offsetWidth;
	var objheight = obj.offsetHeight;
	var left = (document.documentElement.clientWidth - objwidth) / 2;
	obj.style.left = left+"px";
	var top = document.documentElement.scrollTop+(document.documentElement.clientHeight - objheight) / 2;
	obj.style.top = top+"px";
}

// 页面关键词高亮
function highlightWord(node, word) {
	if (node.hasChildNodes) {
		var hi_cn;
		for (hi_cn = 0; hi_cn < node.childNodes.length; hi_cn++) {
			highlightWord(node.childNodes[hi_cn], word);
		}
	}

	if (node.nodeType == 3) {
		tempNodeVal = node.nodeValue.toLowerCase();
		tempWordVal = word.toLowerCase();
		if (tempNodeVal.indexOf(tempWordVal) != -1) {
			pn = node.parentNode;
			if (pn.className != "highlight") {
				nv = node.nodeValue;
				ni = tempNodeVal.indexOf(tempWordVal);
				before = document.createTextNode(nv.substr(0, ni));
				docWordVal = nv.substr(ni, word.length);
				after = document.createTextNode(nv.substr(ni + word.length));
				hiwordtext = document.createTextNode(docWordVal);
				hiword = document.createElement("span");
				hiword.className = "highlight";
				hiword.appendChild(hiwordtext);
				pn.insertBefore(before, node);
				pn.insertBefore(hiword, node);
				pn.insertBefore(after, node);
				pn.removeChild(node);
			}
		}
	}
}


// 获取对象位置
function get_position(what, offsettype) {
	var pos = {"left":what.offsetLeft, "top":what.offsetTop};
	var parentEl = what.offsetParent;
	while (parentEl != null && parentEl.nodeType > 0) {
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

function get_size() {
	var xScroll, yScroll;
	if (window.innerHeight && window.scrollMaxY) {
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
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
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else {
		pageHeight = yScroll;
	}

	if(xScroll < windowWidth){
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}

	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight)
	return arrayPageSize;
}

// ajax函数
function ajax() {
	var xm,bC=false;
	try{xm=new ActiveXObject("Msxml2.XMLHTTP")}catch(e){try{xm=new ActiveXObject("Microsoft.XMLHTTP")}catch(e){try{xm=new XMLHttpRequest()}catch(e){xm=false}}}
	if(!xm)return null;this.connect=function(sU,sM,sV,fn){if(!xm)return false;bC=false;sM=sM.toUpperCase();
	try{if(sM=="GET"){xm.open(sM,sU+"?"+sV,true);sV=""}else{xm.open(sM,sU,true);
	xm.setRequestHeader("Method","POST "+sU+" HTTP/1.1");
	xm.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8")}
	xm.onreadystatechange=function(){if(xm.readyState==4&&!bC){bC=true;if(xm.status==200){fn(xm)}else{window.status="ajax error status code: "+xm.status}}};
	xm.send(sV)}catch(z){return false}return true};return this;
}

// json 返回值的解析处理:
function ajax_out(xm) {
	var s = xm.responseText;
	if (s == "") {
		alert("ajax返回结果为空.."); return {};
	}
	try {
		eval("var out="+s+";");
	} catch (e) {
		alert(s);
		return {};
	}

	return out;
}

// 文档加载完成检测
var dom_loaded = {
	onload: [],
	loaded: function() {
		if (arguments.callee.done) return;
		arguments.callee.done = true;
		for (i = 0;i < dom_loaded.onload.length;i++) dom_loaded.onload[i]();
	},
	load: function(fireThis) {
		this.onload.push(fireThis);
		if (document.addEventListener)
			document.addEventListener("DOMContentLoaded", dom_loaded.loaded, null);
		if (/KHTML|WebKit/i.test(navigator.userAgent)) {
			var _timer = setInterval(function() {
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


// 获取字符串（或任意类型）中包含的数字
function get_num(string) {
	var nums = '';
	string = '' + string;
	for (var i=0; i<string.length; i++) {
		var ch = string.substring(i, i+1);
		if (ch in [0,1,2,3,4,5,6,7,8,9]) {
			nums += ch;
		}
	}
	return nums;
}



// 字符串的trim压缩空格函数 zhuwenya @ 2013-02-01
String.prototype.trim = function() {
	return this.replace(/(^\s*)|(\s*$)/g, "");
}
String.prototype.ltrim = function() {
	return this.replace(/(^\s*)/g, "");
}
String.prototype.rtrim = function() {
	return this.replace(/(\s*$)/g, "");
}

String.prototype.replaceAll = function(s1, s2) {
	return this.replace(new RegExp(s1,"gm"),s2);
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


// 加载 js 文件函数:
function load_js(sfile, js_id) {
	if (typeof(js_id) == "string" && js_id != '') {
		var o = byid(js_id);
		if (o) {
			o.parentNode.removeChild(o);
		}
	}

	var obj = document.createElement('script');
	obj.type = "text/javascript";
	obj.src = sfile;
	if (typeof(js_id) == "string" && js_id != '') {
		obj.id = js_id;
	}
	var head = document.getElementsByTagName('head')[0];
	return head.appendChild(obj);
}



/*
 - 第二部分：本系统定义函数
*/

// 调用父窗口的对话框模式，如果没有父窗口，则使用alert提醒
function msg_box(string, showtime) {
	if (window.parent && window.parent.msg_box) {
		window.parent.msg_box(string, showtime);
	} else {
		alert(string);
	}
}

// 鼠标移入
function mi(o) {
	o.style.backgroundColor = "#D2E9FF";
}

// 鼠标移出
function mo(o) {
	o.style.backgroundColor = "";
}

// 设置标题
function set_title(obj, title_id) {
	var oti = byid(title_id);
	if (oti.value=="") {
		var s=obj.value; var isb=false; u="";
		for (ni=s.length; ni>=0; ni--) {c=s.charAt(ni);if(isb){if(c=='/'||c=='\\'||c=='_'||c=='.')break;u=c+u;}if(c=='.')isb=true;}
		oti.value=u;
	}
}



/*
 - 第三部分：表单处理
*/

var nSelCount=0;

function select_all() {
	var ofm=document.forms["mainform"]; nSelCount=0;
	for(var i=0; i<ofm.elements.length; i++) {
		var e=ofm.elements[i];
		if(e.type=='checkbox'&&e.disabled!=true){e.checked=true; nSelCount++;}
	}
}

function select_none() {
	ofm=document.forms["mainform"]; nSelCount=0;
	for(var i=0; i<ofm.elements.length; i++) {
		var e=ofm.elements[i];
		if(e.type=='checkbox' && e.disabled!=true) {e.checked=false; nSelCount++;}
	}
}

function unselect() {
	ofm=document.forms["mainform"]; nSelCount = 0;
	for(var i=0; i<ofm.elements.length; i++) {
		var e = ofm.elements[i];
		if(e.type == 'checkbox' && e.disabled != true) {e.checked=!e.checked; nSelCount++;}
	}
}

function get_select() {
	ofm=document.forms["mainform"]; var u=''; nSelCount=0;
	for (var i=0; i<ofm.elements.length; i++) {
		var e = ofm.elements[i];
		if(e.type == 'checkbox' && e.checked == true && e.name != 'group'){if(nSelCount>0)u+=","; u+=e.value; nSelCount++;}
	}
	return u;
}


function del() {
	cl=get_select();
	if (nSelCount == 0) {
		alert("您没有选择任何一条资料，无法执行删除！");
		return false;
	}
	if (!confirm("共选择了 "+nSelCount+" 条资料，您确定要删除吗？")) return false;
	window.location="?op=delete&id="+cl+"&r="+Math.random();
}

function set_show(n) {
	ofm=document.forms["mainform"]; cl=get_select();
	if (nSelCount == 0){alert("您没有选择任何一条资料！"); return false;}
	window.location="?op=setshow&id="+cl+"&value="+n+"&r="+Math.random();
}


function set_check(cl, chk) {
	k=chk.checked; al=cl.split(','); ofm=document.forms["mainform"];
	for (ni=0; ni<al.length; ni++) {if (al[ni]) {s="ofm."+al[ni]+".checked="+k+";"; eval(s); }}
}

function set_parent_check(cl, chk) {
	k=chk.checked;
	if (k) {
		al=cl.split(','); ofm=document.forms["mainform"];
		for (ni=0; ni<al.length; ni++) {if (al[ni]) {s="ofm."+al[ni]+".checked="+k+";"; eval(s); }}
	}
}

function set_focus() {
	afm=document.getElementsByTagName("form");
	for (i=0; i<afm.length; i++) {
		ofm=afm[i]; ai=ofm.getElementsByTagName("input");
		for (ni=0; ni<ai.length; ni++) {oi=ai[ni];if(oi.name=="title") {oi.focus();return true;}}
	}
}

function isdel() {
	return confirm("删除后不能恢复，您确定要删除该条资料吗？        ");
}



/*
 - 第四部分：为页面控件增加效果
*/

function set_input_color(obj, focus_blur) {
	obj.className = focus_blur == "focus" ? "input_focus" : "input";
}


function intro_color_ctrl(obj, focus_blur) {
	var sps = obj.parentNode ? obj.parentNode.getElementsByTagName("span") : [];
	if (!sps) return false;
	var check_class = focus_blur == "focus" ? "intro" : "intro_focus";
	var set_class = focus_blur == "focus" ? "intro_focus" : "intro";
	for (var n=0; n<sps.length; n++) {
		if (sps[n].className == check_class) {
			sps[n].className = set_class;
		}
	}
}

function set_item_color(obj) {
	if (obj.checked) {
		obj.parentNode.parentNode.style.backgroundColor = "#fff9f7";
		obj.parentNode.parentNode.mouseflag = 0;
	} else {
		obj.parentNode.parentNode.style.backgroundColor = "";
		obj.parentNode.parentNode.mouseflag = 1;
	}
}

function init() {

	/*
		step 1
	*/
	var bgcolor='', mouseover_color='#eeefe0';
	var tr = document.getElementsByTagName('TR');
	for (i=0;i<tr.length;i++) {
		var td_all = tr[i].getElementsByTagName("TD");
		if (td_all.length > 0 && td_all[0].className.toLowerCase() == "item") {
			tr[i].onmouseover = function() {
				if (this.style.backgroundColor == "" || this.mouseflag == 1) {
					this.style.backgroundColor = mouseover_color;
					this.mouseflag = 1;
				}
			}
			tr[i].onmouseout = function() {
				if (this.mouseflag == 1) {
					this.style.backgroundColor = bgcolor;
				}
			}
		}
	}

	/*
		step 2
	*/
	var es = document.getElementsByTagName("INPUT");
	for (var i=0; i<es.length; i++) {
		if (es[i].className == "input") {
			es[i].onfocus = function() {
				this.className = "input_focus";
				intro_color_ctrl(this, "focus");
			}
			es[i].onblur = function() {
				this.className = "input";
				intro_color_ctrl(this, "blur");
			}
		} else {
			es[i].onfocus = function() {
				intro_color_ctrl(this, "focus");
			}
			es[i].onblur = function() {
				intro_color_ctrl(this, "blur");
			}
		}
	}

	var es = document.getElementsByTagName("SELECT");
	for (var i=0; i<es.length; i++) {
		es[i].onfocus = function() {
			intro_color_ctrl(this, "focus");
		}
		es[i].onblur = function() {
			intro_color_ctrl(this, "blur");
		}
	}

	var es = document.getElementsByTagName("TEXTAREA");
	for (var i=0; i<es.length; i++) {
		if (es[i].className == "input") {
			es[i].onfocus = function() {
				this.className = "input_focus";
				intro_color_ctrl(this, "focus");
			}
			es[i].onblur = function() {
				this.className = "input";
				intro_color_ctrl(this, "blur");
			}
		} else {
			es[i].onfocus = function() {
				intro_color_ctrl(this, "focus");
			}
			es[i].onblur = function() {
				intro_color_ctrl(this, "blur");
			}
		}
	}

	/*
		step 3
	*/
	var es = document.getElementsByTagName("INPUT");
	for (var i=0; i<es.length; i++) {
		if (es[i].className == "button") {
			es[i].onmouseover = function() {
				this.className = "button_over";
			}
			es[i].onmouseout = function() {
				this.className = "button";
			}
		}
		if (es[i].className == "buttonb") {
			es[i].onmouseover = function() {
				this.className = "buttonb_over";
			}
			es[i].onmouseout = function() {
				this.className = "buttonb";
			}
		}
		if (es[i].className == "button_op") {
			es[i].onmouseover = function() {
				this.className = "button_op_over";
			}
			es[i].onmouseout = function() {
				this.className = "button_op";
			}
		}
		if (es[i].className == "submit") {
			es[i].onmouseover = function() {
				this.className = "submit_over";
			}
			es[i].onmouseout = function() {
				this.className = "submit";
			}
		}
	}

	var es = document.getElementsByTagName("BUTTON");
	for (var i=0; i<es.length; i++) {
		if (es[i].className == "button") {
			es[i].onmouseover = function() {
				this.className = "button_over";
			}
			es[i].onmouseout = function() {
				this.className = "button";
			}
		}
		if (es[i].className == "buttonb") {
			es[i].onmouseover = function() {
				this.className = "buttonb_over";
			}
			es[i].onmouseout = function() {
				this.className = "buttonb";
			}
		}
		if (es[i].className == "button_op") {
			es[i].onmouseover = function() {
				this.className = "button_op_over";
			}
			es[i].onmouseout = function() {
				this.className = "button_op";
			}
		}
		if (es[i].className == "submit") {
			es[i].onmouseover = function() {
				this.className = "submit_over";
			}
			es[i].onmouseout = function() {
				this.className = "submit";
			}
		}
	}

}


// 主动式更新父框架菜单 @ 2015-6-25
var menu_id = get_cookie("sys_menu_id");
var top_menu_id = get_cookie("sys_menu_top_mid");
if (parent.ZHUWENYA_IFRAME) {
	parent.select_to_menu(menu_id, top_menu_id);
}


var userAgent = navigator.userAgent,
rMsie = /(msie\s|trident.*rv:)([\w.]+)/,
rFirefox = /(firefox)\/([\w.]+)/,
rOpera = /(opera).+version\/([\w.]+)/,
rChrome = /(chrome)\/([\w.]+)/,
rSafari = /version\/([\w.]+).*(safari)/;
var browser;
var version;
var ua = userAgent.toLowerCase();
function uaMatch(ua) {
	var match = rMsie.exec(ua);
	if (match != null) {
		return { browser : "IE", version : match[2] || "0" };
	}
	var match = rFirefox.exec(ua);
	if (match != null) {
		return { browser : match[1] || "", version : match[2] || "0" };
	}
	var match = rOpera.exec(ua);
	if (match != null) {
		return { browser : match[1] || "", version : match[2] || "0" };
	}
	var match = rChrome.exec(ua);
	if (match != null) {
		return { browser : match[1] || "", version : match[2] || "0" };
	}
	var match = rSafari.exec(ua);
	if (match != null) {
		return { browser : match[2] || "", version : match[1] || "0" };
	}
	if (match != null) {
		return { browser : "", version : "0" };
	}
}
var browserMatch = uaMatch(userAgent.toLowerCase());
if (browserMatch.browser) {
	browser = browserMatch.browser;
	version = browserMatch.version;
}

//if (browser != "IE" || (browser == "IE" && version >= 10) ) {
if (browser != "IE") {
	document.write('<style type="text/css">');
	document.write('fieldset {border-radius:4px; border:1px dotted blue; background:#f0f2ee; }');
	document.write('</style>');
}


function set_high_light(obj) {
	if (typeof(last_high_obj) != "undefined" && last_high_obj) {
		last_high_obj.className = last_high_obj.className.replaceAll("tr_high_light", "").trim();
	}
	if (obj) {
		var node = obj.parentNode;
		var tr = 0;
		for (var i=1; i<=5; i++) {
			if (node.nodeName.toLowerCase() == "tr") {
				var tr = node; break;
			}
			node = node.parentNode;
		}
		if (tr) {
			tr.className = (tr.className != "" ? (tr.className + " ") : "") + "tr_high_light";
			last_high_obj = tr;
		}
	} else {
		last_high_obj = 0;
	}
}


dom_loaded.load(init);


