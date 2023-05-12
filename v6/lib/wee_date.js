/*
// 说明： wee_date 幽兰日期控件
// 作者： 幽兰 (weelia@126.com)
// 时间： 2012-06-19
*/

var d = new Date();
var year_begin = d.getFullYear() - 3;
var year_end = d.getFullYear() + 2;

function byid(id) {
	return document.getElementById(id);
}

// 获取对象位置
function _get_position(what, offsettype) {
	var pos = {"left":what.offsetLeft, "top":what.offsetTop};
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

function wee_update_date(type, o) {
	var num = parseInt(o.innerHTML, 10);
	if (type == 1) default_year = num;
	if (type == 2) default_month = num;
	if (type == 3) default_day = num;
	wee_show_date();
	return false;
}


function wee_show(arr, default_value, click) {
	var s = '';
	var sum = 0;
	for (var i in arr) {
		d = arr[i];
		if (d == default_value) {
			s += '<b>'+d+'</b>';
		} else {
			s += '<a href="#" onclick="'+click+'">'+d+'</a>';
		}
		sum++;
		if (sum == 12 || sum == 22) s += '<br>';
	}
	return s;
}

function _get_month_days(year, month){
	month = parseInt(month, 10) + 1;
	var d = new Date(year+"/"+month+"/0");
	return d.getDate();
}

function wee_show_date() {
	var year_arr = new Array();
	for (var i = year_end; i >= year_begin; i--) {
		year_arr[i] = i;
	}

	var month_arr = new Array();
	for (var i = 1; i <= 12; i++) {
		month_arr[i] = i;
	}

	var day_arr = new Array();
	var days = _get_month_days(default_year, default_month);
	if (default_day > days) default_day = days;
	for (var i = 1; i <= days; i++) {
		day_arr[i] = i;
	}

	byid("wee_year_area").innerHTML = wee_show(year_arr, default_year, "return wee_update_date(1, this)");
	byid("wee_month_area").innerHTML = wee_show(month_arr, default_month, "return wee_update_date(2, this)");
	byid("wee_day_area").innerHTML = wee_show(day_arr, default_day, "return wee_update_date(3, this)");

	wee_date_update_res();
}

function wee_date_update_res() {
	var s = default_year +"-"+ (default_month<10 ? "0" : "") + default_month +"-"+ (default_day<10 ? "0" : "") + default_day;
	byid("wee_date_s").innerHTML = s;
}

// position_x : left / right
// position_y : top / bottom
function wee_date_show_picker(obj_id, position_x, position_y) {
	window.wee_date_obj_id = obj_id;

	var o = byid(obj_id);
	var left = _get_position(o, "left");
	var w_left = left;
	var top = _get_position(o, "top");

	var v = (o.type == "text") ? o.value : o.innerHTML;
	if (v != '') {
		var arr = v.split("-");
		if (arr.length == 3) {
			default_year = parseInt(arr[0], 10);
			default_month = parseInt(arr[1], 10);
			default_day = parseInt(arr[2], 10);
		}
	} else {
		var d = new Date();
		default_year = d.getFullYear();
		default_month = d.getMonth() + 1;
		default_day = d.getDate();
	}

	wee_show_date();

	byid("wee_date").style.display = "block";
	if (position_x == "right") {
		w_left = left + o.offsetWidth - byid("wee_date").offsetWidth;
	}
	if (position_y == "top") {
		var w_top = top - byid("wee_date").offsetHeight - 1;
	} else {
		var w_top = top + o.offsetHeight + 1;
	}
	byid("wee_date").style.left = w_left+"px";
	byid("wee_date").style.top = w_top+"px";

	wee_set_select_visible(0);

	event.cancelBubble = true;
}


// 相对于今日的便宜量
function wee_change_to(offset) {
	var d = new Date();
	if (offset != 0) {
		d.setDate(d.getDate() + offset);
	}
	default_year = d.getFullYear();
	default_month = d.getMonth() + 1;
	default_day = d.getDate();
	wee_show_date();
	wee_finish_input();
}

// 显示或隐藏select控件 （仅IE6下需要）
function wee_set_select_visible(show) {
	var isIE = !!window.ActiveXObject;
	if (isIE) {
		var ie = (function() {
			var undef = 0, v = 3;
			var div = document.createElement('div');
			var all = div.getElementsByTagName('i');
			while (div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->', all[0]);
			return v > 4 ? v : undef;
		}());
		if (ie < 7 && ie > 0) {
			var visible = show ? "visible" : "hidden";
			var allselect = document.getElementsByTagName("select");
			for (var i=0; i<allselect.length; i++) {
				allselect[i].style.visibility = visible;
			}
			var frms = document.getElementsByTagName("iframe");
			for (var i=0; i<frms.length; i++) {
				var allselect = frms[i].contentWindow.document.getElementsByTagName("select");
				for (var j=0; j<allselect.length; j++) {
					allselect[j].style.visibility = visible;
				}
			}
		}
	}
}



function wee_hide_date() {
	wee_set_select_visible(1);
	byid("wee_date").style.display = "none";
}

function wee_finish_input() {
	var o = byid(wee_date_obj_id);
	if (o.type == "text") {
		o.value = byid("wee_date_s").innerHTML;
	} else {
		o.innerHTML = byid("wee_date_s").innerHTML;
	}
	wee_hide_date();
}

function wee_date_init() {
	document.write('<style>');
	document.write('#wee_date {font-size:12px; }');
	document.write('#wee_date {border:2px solid #79acc1; padding:5px; width:300px; position:absolute; z-index:1000000; background:white; }');
	document.write('.wee_date_t td {padding: 4px 3px; border-bottom:1px solid #e3eff2; }');
	document.write('.wee_date_al {vertical-align:top; padding:6px 3px 2px 3px !important; }');
	document.write('.wee_date_ar b, .wee_date_ar a {font-family:"Arial"; }');
	document.write('.wee_date_ar b {border:0px; padding:1px 5px 1px 5px; color:red; }');
	document.write('.wee_date_ar a {border:0px; padding:1px 5px 1px 5px; }');
	document.write('.wee_date_ar a:hover {border:1px solid silver; padding:0px 4px 0px 4px; }');
	document.write('.wee_date_res { margin-top:10px; padding:3px; }');
	document.write('.wee_date_res button {border:1px solid #94c0cd; background:#e9f1f3; height:20px; font-size:12px; }');
	document.write('#wee_date_s {font-family:"Tahoma"; font-weight:bold; color:red; }');
	document.write('</style>');

	document.write('<div id="wee_date" style="display:none;" onclick="event.cancelBubble = true;">');
	document.write('  <table width="100%" cellpadding="0" cellspacing="0" class="wee_date_t">');
	document.write('    <tr>');
	document.write('      <td class="wee_date_al" style="width:20px;"><b>年</b>：</td>');
	document.write('      <td id="wee_year_area" class="wee_date_ar"></td>');
	document.write('    </tr>');
	document.write('    <tr>');
	document.write('      <td class="wee_date_al"><b>月</b>：</td>');
	document.write('      <td id="wee_month_area" class="wee_date_ar"></td>');
	document.write('    </tr>');
	document.write('    <tr>');
	document.write('      <td class="wee_date_al"><b>日</b>：</td>');
	document.write('      <td id="wee_day_area" class="wee_date_ar"></td>');
	document.write('    </tr>');
	document.write('  </table>');
	document.write('  <div class="wee_date_res">');
	document.write('    所选日期：<span id="wee_date_s"></span> &nbsp;<button onclick="wee_finish_input()">确定</button> &nbsp;<a href="javascript:void(0)" onclick="wee_change_to(0)">今天</a> <a href="javascript:void(0)" onclick="wee_change_to(1)">明天</a> <a href="javascript:void(0)" onclick="wee_change_to(-1)">昨天</a>');
	document.write('  </div>');
	document.write('</div>');

	document.body.onclick = function() { wee_hide_date(); }
}


wee_date_init();
