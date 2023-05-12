<?php
// --------------------------------------------------------
// - 功能说明 : 资料库报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2015-1-6
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";

if ($_GET["new_hid"] > 0) {
	$hid = $_SESSION[$cfgSessionName]["hospital_id"] = intval($_GET["new_hid"]);
}

// 按分组进行整理
$hids = implode(",", $hospital_ids);
$group_id_name = $db->query("select id,name from hospital_group order by sort desc, name asc", "id", "name");
$options = array(); $first_hid = 0;
foreach ($group_id_name as $_gid => $_gname) {
	$h_list = $db->query("select id,name,color from hospital where ishide=0 and group_id=$_gid and id in ($hids) order by sort desc, name asc", "id");
	if (count($h_list) > 0) {
		$options[] = array('-1', $_gname." (".count($h_list).')', 'color:red' );
		foreach ($h_list as $_hid => $_arr) {
			$options[] = array($_hid, '　'.$_arr["name"], ($_arr["color"] ? ('color:'.$_arr["color"]) : 'color:blue') );
			if ($first_hid == 0) $first_hid = $_hid;
		}
	}
}

if ($hid <= 0 && $first_hid > 0) {
	$hid = $first_hid;
}

// 读取本科室客服:
$t = strtotime("-1 month");
$kefu_arr = $db->query("select u_name, count(u_name) as c from ku_list where hid=$hid and addtime>$t group by u_name order by c desc, u_name asc", "u_name", "c");

$get_kf = $_GET["kf"];
if ($get_kf == '') {
	$get_kf = $_GET["kf"] = array_shift(array_keys($kefu_arr));
}

if ($get_kf != '') {
	$data = $db->query("select * from ku_list where hid=$hid and addtime>$t and u_name='$get_kf' order by id desc", "id");
} else {
	$data = array();
}


function _huifang_to_arr($line) {
	if (trim($line["hf_log"]) == '') {
		return $line;
	}
	$arr = explode("\n", $line["hf_log"]);
	$xy = 1;
	foreach ($arr as $v) {
		list($a, $b) = explode(": ", $v);
		if (trim($b) != '') {
			$line["huifang_".$xy++] = $b;
		}
	}
	return $line;
}


function _fenxi_jieguo($line) {
	$res = "继续";
	if ($line["track_status"] == "-1") {
		$res = "放弃";
	}
	if ($line["is_yuyue"]) {
		$res = "已约";
	}
	if ($line["is_come"]) {
		$res = "已约已到";
	}
	return $res;
}


function _tel_filter($tel) {
	// 2015-5-18 修改: 放开号码
	return $tel;

	if (strlen($tel) == 11) {
		return substr($tel, 0, 3)."****".substr($tel, 7, 4);
	} else {
		if (strlen($tel) < 7) {
			return $tel;
		}
		return substr($tel, 0, -4)."****";
	}
}



?>
<html>
<head>
<title>资料库报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>

<style>
* {font-family:"微软雅黑","Tahoma" !important; font-size:12px !important;  }
body {overflow-x:auto !important;}
#kefu_area {margin-top:20px; }
.kf_select {float:left; padding:5px 5px; border:1px solid white; border-bottom:1px solid silver; margin:0px; margin-right:-1px; }
.kf_cur {border:1px solid silver; border-right:2px solid silver; border-bottom:1px solid #f3f3f3; background:#f3f3f3; }
.kf_last {width:100px; }
.kf_a_select {color:#8f8f8f; }
.kf_a_cur {color:#ff0000; }
</style>

<script language="javascript">
// 鼠标移入
function mi(o) {
	o.style.backgroundColor = "#ffdbca";
}

// 鼠标移出
function mo(o) {
	o.style.backgroundColor = "";
}

function hgo(dir){
	var t="已经是最"+(dir=="up"?"上":"下")+"一家医院了";
	var obj=byid("hospital_id");if(dir=="up"){var i=obj.selectedIndex-1;while(i>0){if(obj.options[i].value>0){obj.selectedIndex=i;obj.onchange();break}i--}if(i==0){alert(t)}}if(dir=="down"){var i=obj.selectedIndex+1;while(i<obj.options.length){if(obj.options[i].value>0){obj.selectedIndex=i;obj.onchange();break}i++}if(i==obj.options.length){alert(t)}}
}

function change_h(o) {
	if (o.value!='-1') {
		self.location = '?new_hid='+o.value;
	}
}
</script>

</head>

<body style="padding:10px;">

<div style="">
<?php
	echo '	<b>切换医院：</b>';
	echo '	<select name="new_hid" id="hospital_id" class="combo" onchange="change_h(this)" style="width:216px;">';
	foreach ($options as $v) {
		echo '		<option value="'.$v[0].'"'.($v[0] == $hid ? ' selected' : '').($v[2] ? ' style="'.$v[2].'"' : '').'>'.$v[1].($v[0] == $hid ? ' *' : '').'</option>';
	}
	echo '	</select>';
	echo '　<button class="button" onclick="hgo(\'up\');">上</button>&nbsp;<button class="button" onclick="hgo(\'down\');">下</button>';
?>
</div>

<div id="kefu_area">
	<nobr>客服选择：
<?php
if (count($kefu_arr) == 0) echo "(当前科室一个月内没有数据)";
foreach ($kefu_arr as $_kf => $_c) {
	$class = $_kf == $get_kf ? "kf_select kf_cur" : "kf_select";
	$class_a = $_kf == $get_kf ? "kf_a_select kf_a_cur" : "kf_a_select";
?>
	<a class="<?php echo $class_a; ?>" href="?kf=<?php echo urlencode($_kf); ?>" title="<?php echo $_c; ?>"><?php echo $_kf; ?></a>&nbsp;&nbsp;
<?php } ?>
	</nobr>
</div>

<br>

<style type="text/css">
.excel_table {border:2px solid #c8c8c8; }
.excel_table td {padding:5px 10px 3px 5px; border:1px solid #d3d3d3; }
.excel_head td {background:#dce4e9; color:#0080c0; padding:5px 10px 3px 5px; border-bottom:1px solid #d3d3d3; }
.excel_index {background:#f3f3f3; border-right:1px solid #d3d3d3; text-align:center; padding-left:20px !important; padding-right:20px !important; }
.content_left {border-left:1px solid #efefef; }
</style>


<table class="excel_table">
	<tr class="excel_head">
		<td class="excel_index"></td>
		<td class="content_left"><nobr>跟踪结果</nobr></td>
		<td><nobr>姓名</nobr></td>
		<td><nobr>添加时间</nobr></td>
		<td><nobr>电话</nobr></td>
		<td><nobr>微信</nobr></td>
		<td><nobr>QQ</nobr></td>
		<td><nobr>性病</nobr></td>
		<td><nobr>年龄</nobr></td>
		<td><nobr>病情</nobr></td>
		<td><nobr>一次跟踪</nobr></td>
		<td><nobr>二次跟踪</nobr></td>
		<td><nobr>三次跟踪</nobr></td>
		<td><nobr>四次跟踪</nobr></td>
		<td><nobr>五次跟踪</nobr></td>
		<td><nobr>六次跟踪</nobr></td>
	</tr>

<?php
$index = 1;
foreach ($data as $line) {
	$line = _huifang_to_arr($line);
	$line["jieguo"] = _fenxi_jieguo($line);
?>
	<tr class="excel_item" onmouseover="mi(this)" onmouseout="mo(this)">
		<td class="excel_index"><nobr><?php echo $index++; ?></nobr></td>
		<td class="content_left"><nobr><?php echo $line["jieguo"]; ?></nobr></td>
		<td><nobr><?php echo $line["name"]; ?></nobr></td>
		<td><nobr><?php echo date("Y-m-d H:i", $line["addtime"]); ?></nobr></td>
		<td><nobr><?php echo _tel_filter($line["mobile"]); ?></nobr></td>
		<td><nobr><?php echo ($line["weixin"] ? $line["weixin"] : "-"); ?></nobr></td>
		<td><nobr><?php echo ($line["qq"] ? $line["qq"] : "-"); ?></nobr></td>
		<td><nobr><?php echo $line["sex"]; ?></nobr></td>
		<td><nobr><?php echo $line["age"]; ?></nobr></td>
		<td><nobr><?php echo cut($line["zx_content"], 100, "…"); ?></nobr></td>
		<td><nobr><?php echo cut($line["huifang_1"], 60, "…"); ?></nobr></td>
		<td><nobr><?php echo cut($line["huifang_2"], 60, "…"); ?></nobr></td>
		<td><nobr><?php echo cut($line["huifang_3"], 60, "…"); ?></nobr></td>
		<td><nobr><?php echo cut($line["huifang_4"], 60, "…"); ?></nobr></td>
		<td><nobr><?php echo cut($line["huifang_5"], 60, "…"); ?></nobr></td>
		<td><nobr><?php echo cut($line["huifang_6"], 60, "…"); ?></nobr></td>
	</tr>
<?php
}
?>
</table>

<br>


</body>
</html>