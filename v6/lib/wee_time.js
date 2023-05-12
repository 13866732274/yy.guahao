/*
// 说明： wee_time 幽兰时间控件
// 作者： 幽兰 (weelia@126.com)
// 时间： 2012-07-03
*/

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

function wee_update_time(type, o) {
	var num = parseInt(o.innerHTML, 10);
	if (type == 1) default_hour = num;
	if (type == 2) default_minute = num;
	wee_time_show();
	return false;
}


function wee_tshow(arr, default_value, click) {
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
	}
	return s;
}


function wee_time_show() {
	var hour_arr = new Array();
	for (var i = 7; i <= 22; i++) {
		hour_arr[i] = i;
	}

	var minute_arr = new Array();
	minute_arr[0] = "0";
	minute_arr[1] = "10";
	minute_arr[2] = "20";
	minute_arr[3] = "30";
	minute_arr[4] = "40";
	minute_arr[5] = "50";

	byid("wee_hour_area").innerHTML = wee_tshow(hour_arr, default_hour, "return wee_update_time(1, this)");
	byid("wee_minute_area").innerHTML = wee_tshow(minute_arr, default_minute, "return wee_update_time(2, this)");

	wee_time_update_res();
}

function wee_time_update_res() {
	var s = (default_hour < 10 ? "0" : "") + default_hour +":"+ (default_minute<10 ? "0" : "") + default_minute;
	byid("wee_time_s").innerHTML = s;
}

// position_x : left / right
// position_y : top / bottom
function wee_time_show_picker(obj_id, position_x, position_y) {
	window.wee_time_obj_id = obj_id;

	var o = byid(obj_id);
	var left = _get_position(o, "left");
	var w_left = left;
	var top = _get_position(o, "top");

	var v = (o.type == "text") ? o.value : o.innerHTML;
	if (v != '') {
		var arr = v.split(":");
		if (arr.length == 2) {
			default_hour = parseInt(arr[0], 10);
			default_minute = parseInt(arr[1], 10);
		}
	} else {
		var d = new Date();
		default_hour = 9;
		default_minute = 0;
	}

	wee_time_show();

	byid("wee_time").style.display = "block";
	if (position_x == "right") {
		w_left = left + o.offsetWidth - byid("wee_time").offsetWidth;
	}
	if (position_y == "top") {
		var w_top = top - byid("wee_time").offsetHeight - 1;
	} else {
		var w_top = top + o.offsetHeight + 1;
	}
	byid("wee_time").style.left = w_left+"px";
	byid("wee_time").style.top = w_top+"px";

	wee_set_select_visible(0);

	event.cancelBubble = true;
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



function wee_hide_time() {
	wee_set_select_visible(1);
	byid("wee_time").style.display = "none";
}

function wee_time_finish() {
	var o = byid(wee_time_obj_id);
	if (o.type == "text") {
		o.value = byid("wee_time_s").innerHTML;
	} else {
		o.innerHTML = byid("wee_time_s").innerHTML;
	}
	wee_hide_time();
}

function wee_time_init() {
	document.write('<style>');
	document.write('#wee_time {font-size:12px; }');
	document.write('#wee_time {border:2px solid #79acc1; padding:5px; width:300px; position:absolute; z-index:1000000; background:white; }');
	document.write('.wee_time_t td {padding: 4px 3px; border-bottom:1px solid #e3eff2; }');
	document.write('.wee_time_al {vertical-align:top; padding:4px 3px 2px 3px !important; }');
	document.write('.wee_time_ar b, .wee_time_ar a {font-family:"Arial"; }');
	document.write('.wee_time_ar b {border:0px; padding:1px 5px 1px 5px; color:red; }');
	document.write('.wee_time_ar a {border:0px; padding:1px 5px 1px 5px; }');
	document.write('.wee_time_ar a:hover {border:1px solid silver; padding:0px 4px 0px 4px; }');
	document.write('.wee_time_res { margin-top:10px; padding:3px; }');
	document.write('.wee_time_res button {border:1px solid #94c0cd; background:#e9f1f3; height:20px; font-size:12px; }');
	document.write('#wee_time_s {font-family:"Tahoma"; font-weight:bold; color:red; }');
	document.write('</style>');

	document.write('<div id="wee_time" style="display:none;" onclick="event.cancelBubble = true;">');
	document.write('  <table width="100%" cellpadding="0" cellspacing="0" class="wee_time_t">');
	document.write('    <tr>');
	document.write('      <td class="wee_time_al"><b>时</b></td>');
	document.write('      <td id="wee_hour_area" class="wee_time_ar"></td>');
	document.write('    </tr>');
	document.write('    <tr>');
	document.write('      <td class="wee_time_al"><b>分</b></td>');
	document.write('      <td id="wee_minute_area" class="wee_time_ar"></td>');
	document.write('    </tr>');
	document.write('  </table>');
	document.write('  <div class="wee_time_res">');
	document.write('    所选时间：<span id="wee_time_s"></span> &nbsp;<button onclick="wee_time_finish()">确定</button>');
	document.write('  </div>');
	document.write('</div>');

	document.body.onclick = function() {
		wee_hide_time();
		if (byid("wee_date")) {
			wee_hide_date();
		}
	}
}


wee_time_init();
