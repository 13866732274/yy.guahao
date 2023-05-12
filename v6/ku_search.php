<?php
// --------------------------------------------------------
// - 功能说明 : 资料库搜索
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-4-28
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";


include "lib/class.fastjson.php";
include "ku.search_config.php";

// 搜索提交之后:
if ($_GET["from"] == "search") {
	list($a, $url_end) = explode("?", $_SERVER["REQUEST_URI"], 2);
	$url = "ku_list.php?".$url_end;

	// 记录本次参数(用于下次搜索时 修改搜索条件) 2011-11-03
	$_SESSION["ku_search_condition"] = @serialize($_GET);

	echo '正在搜索，请稍候...'."\r\n";
	echo '<script>'."\r\n";
	echo 'parent.byid("sys_frame").src = "'.$url.'";'."\r\n";
	echo 'setTimeout("parent.load_src(0)", 100);'."\r\n";
	echo '</script>'."\r\n";
	exit;
}



if ($_GET["op"] == "new_search") {
	$_SESSION["ku_search_condition"] = '';
}


$se = array();
if ($_SESSION["ku_search_condition"]) {
	$se = @unserialize($_SESSION["ku_search_condition"]);
}

?>
<html>
<head>
<title>高级搜索</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.head_tips {border:2px solid #ffa87d; background:#ffe4d5; padding:5px 10px; border-radius:3px;  }
.new_body {padding:20px; }
.new_edit td {padding:8px 5px !important; }
.new_edit .left {text-align:right !important; }
</style>
<script language="javascript">
function write_dt(da, db) {
	byid("begin_time").value = da;
	byid("end_time").value = db;
}
</script>
</head>

<body class="new_body">

<div class="head_tips">默认会记录上次搜索条件，如需重新搜索，请点击：<a href="?op=new_search" title="清空记忆的条件，重新搜索">[清空条件 重新搜索]</a></div>
<div class="space"></div>

<form name="mainform" id="wee_search_form" action="?" method="GET">
<table width="100%" class="new_edit">
	<tr>
		<td class="left" style="width:200px;">搜索日期范围：</td>
		<td class="right">
			<input name="btime" id="btime" class="input" style="width:122px" value="<?php echo $se["btime"]; ?>" onclick="picker({el:'btime',dateFmt:'yyyy-MM-dd'})"> ~ <input name="etime" id="etime" class="input" style="width:122px" value="<?php echo $se["etime"]; ?>" onclick="picker({el:'etime',dateFmt:'yyyy-MM-dd'})">　(以添加时间算)
		</td>
	</tr>
	<tr>
		<td class="left">第一条件：</td>
		<td class="right">
			<select name="sou[1][f]" id="sou_1_f" class="combo" onchange="update_value_ctrl(1, this)">
				<option value="" style="color:gray"></option>
				<?php echo list_option($fields_arr, "_key_", "_value_"); ?>
			</select>
			<span id="value_ctrl_1"></span>
		</td>
	</tr>
	<tr>
		<td class="left">第二条件：</td>
		<td class="right">
			<select name="sou[2][f]" id="sou_2_f" class="combo" onchange="update_value_ctrl(2, this)">
				<option value="" style="color:gray"></option>
				<?php echo list_option($fields_arr, "_key_", "_value_"); ?>
			</select>
			<span id="value_ctrl_2"></span>
		</td>
	</tr>
	<tr>
		<td class="left">第三条件：</td>
		<td class="right">
			<select name="sou[3][f]" id="sou_3_f" class="combo" onchange="update_value_ctrl(3, this)">
				<option value="" style="color:gray"></option>
				<?php echo list_option($fields_arr, "_key_", "_value_"); ?>
			</select>
			<span id="value_ctrl_3"></span>
		</td>
	</tr>
	<tr>
		<td class="left">第四条件：</td>
		<td class="right">
			<select name="sou[4][f]" id="sou_4_f" class="combo" onchange="update_value_ctrl(4, this)">
				<option value="" style="color:gray"></option>
				<?php echo list_option($fields_arr, "_key_", "_value_"); ?>
			</select>
			<span id="value_ctrl_4"></span>
		</td>
	</tr>
	<tr>
		<td class="left">第五条件：</td>
		<td class="right">
			<select name="sou[5][f]" id="sou_5_f" class="combo" onchange="update_value_ctrl(5, this)">
				<option value="" style="color:gray"></option>
				<?php echo list_option($fields_arr, "_key_", "_value_"); ?>
			</select>
			<span id="value_ctrl_5"></span>
		</td>
	</tr>
	<tr>
		<td class="left">第六条件：</td>
		<td class="right">
			<select name="sou[6][f]" id="sou_6_f" class="combo" onchange="update_value_ctrl(6, this)">
				<option value="" style="color:gray"></option>
				<?php echo list_option($fields_arr, "_key_", "_value_"); ?>
			</select>
			<span id="value_ctrl_6"></span>
		</td>
	</tr>
	<tr>
		<td class="left">第七条件：</td>
		<td class="right">
			<select name="sou[7][f]" id="sou_7_f" class="combo" onchange="update_value_ctrl(7, this)">
				<option value="" style="color:gray"></option>
				<?php echo list_option($fields_arr, "_key_", "_value_"); ?>
			</select>
			<span id="value_ctrl_7"></span>
		</td>
	</tr>
	<tr>
		<td class="left">第八条件：</td>
		<td class="right">
			<select name="sou[8][f]" id="sou_8_f" class="combo" onchange="update_value_ctrl(8, this)">
				<option value="" style="color:gray"></option>
				<?php echo list_option($fields_arr, "_key_", "_value_"); ?>
			</select>
			<span id="value_ctrl_8"></span>
		</td>
	</tr>
</table>

<script type="text/javascript">
var fields_dict = <?php echo FastJSON::encode($fields_dict); ?>;
var search_mode = <?php echo FastJSON::encode($search_mode); ?>;

var chk_index = 1;

function update_value_ctrl(num, obj) {
	var ctrl = byid("value_ctrl_"+num);
	var f = obj.value;
	if (f == '') ctrl.innerHTML = "";
	var str = '　';
	if (fields_dict[f]) {
		str += '<input type="hidden" name="sou['+num+'][c]" id="sou_'+num+'_c" value="in">';
		str += '<span id="sou_'+num+'_v">';
		for (var i in fields_dict[f]) {
			var v = fields_dict[f][i];
			str += '<input type="checkbox" title="请勾选" name="sou['+num+'][v][]" value="'+i+'" id="chk_'+chk_index+'"><label for="chk_'+(chk_index++)+'">'+v+'</label>　';
		}
		str += '</span>';
	} else {
		str += '<select name="sou['+num+'][c]" id="sou_'+num+'_c" class="combo">';
		for (var i in search_mode) {
			var v = search_mode[i];
			str += '<option value="'+i+'">'+v+'</option>';
		}
		str += '</select>　';
		str += '<input name="sou['+num+'][v]" id="sou_'+num+'_v" value="" class="input">';
	}
	ctrl.innerHTML = str;
}
</script>

<input type="hidden" name="from" value="search">
<div class="button_line"><input type="submit" class="submit" value="搜索"></div>
<input type="hidden" name="search_mode" value="high_search">
</form>


<style type="text/css">
.jieshi {margin-top:15px; padding:5px 10px; border:1px solid #ffa87d; border-radius:3px; }
</style>
<div class="jieshi">
	简要操作说明：1.多个条件是以“并且”的原则相连（设置的条件数量越多，得到结果会越少）；2.大于|小于这类的判断，后面必须填数字; 3.包含的用法举例 资料来源 包含 “商务通,电话,短信”; 4.若选“不为空”，最后一个空格是不需要填写内容的
</div>


<?php if (count($se["sou"]) > 0) { ?>
<script type="text/javascript">
var sou = <?php echo FastJSON::encode($se["sou"]); ?>;

function set_select_value(obj, value) {
   var opts = obj.options;
   for (i=0; i<opts.length; i++) {
      if (opts[i].value == value) {
         opts[i].selected = true;
      }
   }
}

for (var num in sou) {
	var def = sou[num];
	var f = def["f"];
	var c = def["c"];
	var v = def["v"];
	if (f != '') {
		var f_obj = byid("sou_"+num+"_f");
		set_select_value(f_obj, f);
		f_obj.onchange();
		if (byid("sou_"+num+"_c").tagName.toLowerCase() == "select") {
			set_select_value(byid("sou_"+num+"_c"), c);
			byid("sou_"+num+"_v").value = v;
		} else {
			byid("sou_"+num+"_c").value = c;
			if (v && v.length > 0) {
				var chks = byid("sou_"+num+"_v").getElementsByTagName("INPUT");
				for (var i in chks) {
					if (chks[i].title == "请勾选" && in_array(chks[i].value, v)) {
						chks[i].checked = true;
					}
				}
			}
		}
	}
}
</script>
<?php } ?>

<?php //echo "<pre>"; print_r($se); ?>


</body>
</html>