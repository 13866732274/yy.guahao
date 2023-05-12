// --------------------------------------------------------
// - 功能说明 : 后台动态菜单模块
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-04-01 08:00
// --------------------------------------------------------
window.onerror = function () { return false; };

document.write('<style type="text/css">');
document.write('A.menulink:link {font-size:12px;color:#003399;text-decoration:none;}');
document.write('A.menulink:hover {font-size:12px;text-decoration:underline;}');
document.write('A.submenulink:link {font-size:12px;color:#0000CC;text-decoration:none;padding:2px 1px 0px 1px;}');
document.write('A.submenulink:hover {font-size:12px;color:#8080FF;text-decoration:none;padding:2px 1px 0px 1px;}');
document.write('.webmenu {background-color:#EBEBEB;font-family:宋体,arial,helvetica;font-size:12px;}');
document.write('#dropmenudiv {position:absolute;border:1px solid black;font:normal 12px Verdana;line-height:18px;z-index:100; padding:4px 0; }');
document.write('#dropmenudiv a {width:100%;display:block;text-indent:12px;padding:2px 1px 0px 1px; }');
document.write('#dropmenudiv a:hover {background-color:#E8F3FF;padding:2px 1px 0px 1px;  }');
document.write('</style>');

var menuwidth = '150px';
var menubgcolor = 'white';
var disappeardelay = 500;
var hidemenu_onclick = "yes";
var high_light_obj = false;
var last_visit_obj = false;

var ie4 = document.all;
var ns6 = document.getElementById && !document.all;

if (ie4 || ns6)
	document.write('<div id="dropmenudiv" style="Z-index:100; visibility:hidden; overflow-x:hidden; border:1px solid #bfbfbf; line-height:150%; width:' + menuwidth + '; background-color:' + menubgcolor + '" onMouseover="clearhidemenu()" onMouseout="dynamichide(event)"></div>')

function getposOffset(what, offsettype) {
	var totaloffset = (offsettype == "left") ? what.offsetLeft : what.offsetTop;
	var parentEl = what.offsetParent;
	while (parentEl != null) {
		totaloffset = (offsettype == "left") ? totaloffset + parentEl.offsetLeft : totaloffset + parentEl.offsetTop;
		parentEl = parentEl.offsetParent;
	}
	return totaloffset;
}


function showhide(obj, e, visible, hidden, menuwidth) {
	if (ie4 || ns6) dropmenuobj.style.left = dropmenuobj.style.top = -500;
	if (menuwidth != "") {
		dropmenuobj.widthobj = dropmenuobj.style;
		dropmenuobj.widthobj.width = menuwidth;
	}
	if (e.type == "click" && obj.visibility == hidden || e.type == "mouseover") {
		obj.visibility = visible;
		//menubackframeobj.style.visibility = visible;
	} else if (e.type == "click") {
		obj.visibility = hidden;
		//menubackframeobj.style.visibility = hidden;
	}
}

function iecompattest() {
	return (document.compatMode && document.compatMode != "BackCompat") ? document.documentElement : document.body;
}

function clearbrowseredge(obj, whichedge) {
	var edgeoffset = 0;
	if (whichedge == "rightedge") {
		var windowedge = ie4 && !window.opera ? iecompattest().scrollLeft + iecompattest().clientWidth - 15 : window.pageXOffset + window.innerWidth - 15;
		dropmenuobj.contentmeasure = dropmenuobj.offsetWidth;
		if (windowedge - dropmenuobj.x < dropmenuobj.contentmeasure)
			edgeoffset = dropmenuobj.contentmeasure - obj.offsetWidth;
	} else {
		var windowedge = ie4 && !window.opera ? (iecompattest().scrollTop + iecompattest().clientHeight - 15) : (window.pageYOffset + window.innerHeight - 18);
		dropmenuobj.contentmeasure = dropmenuobj.offsetHeight;
		if (windowedge - dropmenuobj.y < dropmenuobj.contentmeasure)
			edgeoffset = dropmenuobj.contentmeasure + obj.offsetHeight;
	}
	return edgeoffset
}

function populatemenu(what) {
	if (ie4 || ns6) dropmenuobj.innerHTML = what.join("");
}

function dropdownmenu(obj, e, menucontents, menuwidth, mid) {
	if (window.event) {
		event.cancelBubble = true;
	} else if (e.stopPropagation) {
		e.stopPropagation();
	}
	if (last_visit_obj) {
		last_visit_obj.className = "";
	}
	if (obj.className == "red") {
		high_light_obj = obj;
	}
	if (obj) {
		obj.className = "a_hover";
		last_visit_obj = obj;
	}
	clearhidemenu();
	dropmenuobj = document.getElementById ? document.getElementById("dropmenudiv") : dropmenudiv;
	//menubackframeobj = document.getElementById ? document.getElementById("menubackframe") : menubackframe;
	populatemenu(menucontents);

	if (ie4 || ns6) {
		showhide(dropmenuobj.style, e, "visible", "hidden", menuwidth);
		dropmenuobj.x = getposOffset(obj, "left");
		dropmenuobj.y = getposOffset(obj, "top");
		dropmenuobj.style.left = dropmenuobj.x - clearbrowseredge(obj, "rightedge") + "px";
		dropmenuobj.style.top = dropmenuobj.y - clearbrowseredge(obj, "bottomedge") + obj.offsetHeight - 1 + "px";
	}

	if (mid && menu_max_char[mid]) {
		byid("dropmenudiv").style.width = menu_max_char[mid] * 12 + 40 + "px";
	}

	return clickreturnvalue();
}

function clickreturnvalue() {
	return (ie4 || ns6) ? false : true;
}

function contains_ns6(a, b) {
	while (b.parentNode)
		if ((b = b.parentNode) == a)
			return true;
	return false;
}

function dynamichide(e) {
	if (ie4 && !dropmenuobj.contains(e.toElement))
		delayhidemenu();
	else if (ns6 && e.currentTarget != e.relatedTarget && !contains_ns6(e.currentTarget, e.relatedTarget))
		delayhidemenu();
}

function hidemenu(e) {
	if (typeof dropmenuobj != "undefined") {
		if (ie4 || ns6) {
			dropmenuobj.style.visibility = "hidden";
			if (last_visit_obj) {
				if (high_light_obj == last_visit_obj) {
					last_visit_obj.className = "red";
				} else {
					last_visit_obj.className = "";
				}
				last_visit_obj = false;
			}
		}
	}
}

function delayhidemenu() {
	if (ie4 || ns6)
		delayhide = setTimeout("hidemenu()", disappeardelay);
}

function clearhidemenu() {
	if (typeof delayhide != "undefined")
		clearTimeout(delayhide);
}

if (hidemenu_onclick == "yes") {
	document.onclick = hidemenu;
}