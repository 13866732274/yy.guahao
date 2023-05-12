<?php
/*
// 说明: 统计数据汇总
// 作者: 幽兰 (weelia@126.com)
// 时间: 2015-6-24
*/
require "lib/set_env.php";

$hids = implode(",", $hospital_ids);
$s_arr = $db->query("select sname,count(sname) as c from hospital where ishide=0 and id in ($hids) group by sname order by sname asc", "sname", "c");
foreach ($s_arr as $sname => $count) {
	if ($count <= 1) unset($s_arr[$sname]);
}

// 查询每个医院的下属科室:
foreach ($s_arr as $sname => $count) {
	$h_arr[$sname] = $db->query("select id,depart as name from hospital where ishide=0 and id in ($hids) and sname='$sname' order by name asc", "id", "name");
}

function _list_check($check_name, $arr, $key_use, $value_use, $default_value=array(), $split_char = " ", $onclick="") {
	$res = array();
	$id_base = $check_name."_".mt_rand(1, 9999)."_";
	foreach ($arr as $k => $v) {
		$id = $id_base.$k;
		$key = $key_use == "k" ? $k : $v;
		$value = $value_use == "k" ? $k : $v;
		$check = @in_array($key, $default_value) ? " checked" : "";
		$click = $onclick ? ' onclick="'.$onclick.'"' : "";
		$res[] = '<input type="checkbox" name="'.$check_name.'" value="'.$key.'" id="'.$id.'"'.$check.$click.'><label for="'.$id.'">'.$value.'</label>';
	}
	return implode($split_char, $res);
}


if ($_GET["from_date"] == '') {
	$_GET["from_date"] = date("Y-m-01");
}
if ($_GET["to_date"] == '') {
	$_GET["to_date"] = date("Y-m-d");
}
$from_date = $_GET["from_date"];
$to_date = $_GET["to_date"];

$from_time = strtotime($from_date);
$to_time = strtotime($to_date." 23:59:59");




header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
?>
<html>
<head>
<title>轨迹统计数据汇总</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
* {font-family:"微软雅黑"; }
.input, .input_focus {font-family:"宋体"; }

.m_select {border:1px solid #c6c6c6 !important; width:100%; }
.m_select td {border:1px solid #c6c6c6 !important; padding:4px 8px !important; }
</style>
<script language="javascript">
function menu_sub_check(o) {
	var value = o.value;
	var g = byid("sub_check_"+value).getElementsByTagName("INPUT");
	for (var i = 0; i < g.length; i++) {
		g[i].checked = o.checked;
	}
}

var menu_check_flag = true;
function menu_check_all() {
	var g = byid("menu_select_area").getElementsByTagName("INPUT");
	for (var i = 0; i < g.length; i++) {
		g[i].checked = menu_check_flag;
	}
	menu_check_flag = !menu_check_flag;
}

function update_sub_hid(s) {
	var g = byid("sub_hid_select").getElementsByTagName("DIV");
	for (var i=0; i<g.length; i++) {
		g[i].style.display = "none";
	}
	byid("s_"+s).style.display = "";
}

function open_in_new_window(obj) {
	var url = self.location;
	obj.href = url;
	parent.load_src(0);
}
</script>
</head>

<body>

<?php
if (count($s_arr) == 0) {
	echo '<div id="no_data">没有可供汇总的科室</div>';
} else {
?>

<form method="GET" action="" onsubmit="">
<table class="m_select" id="menu_select_area">
	<tr>
		<td style="width:120px" align="right">请选择医院：</td>
		<td style="">
			<select name="g" class="combo" onchange="update_sub_hid(this.value)">
				<option value="">--请选择医院--</option>
				<?php echo list_option($s_arr, "_key_", "_key_", $_GET["g"]); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right">选择具体科室：</td>
		<td id="sub_hid_select">
<?php
foreach ($s_arr as $sname => $count) {
	echo '<div id="s_'.$sname.'" style="display:none">';
	echo _list_check("gg[]", array($sname=>"全选"), "k", "v", array(), " ", "menu_sub_check(this)");
	echo '	<span id="sub_check_'.$sname.'">&nbsp;';
	echo _list_check("sub_hid[$sname][]", $h_arr[$sname], "k", "v", $_GET["sub_hid"][$sname]);
	echo '	</span>';
	echo '</div>';
}
?>
		</td>
	</tr>
	<tr>
		<td align="right">日期起止：</td>
		<td><input name="from_date" id="from_date" class="input" size="12" value="<?php echo $from_date; ?>" onclick="picker({el:'from_date',dateFmt:'yyyy-MM-dd'})"> ~ <input name="to_date" id="to_date" class="input" size="12" value="<?php echo $to_date; ?>" onclick="picker({el:'to_date',dateFmt:'yyyy-MM-dd'})">
		</td>
	</tr>
	<tr>
		<td align="right"></td>
		<td>
			<input type="submit" class="buttonb" value="点此查询">
			<input type="button" onclick="self.location.reload();" class="button" value="刷新" style="margin-left:50px;">
			<a href="#" onclick="open_in_new_window(this)" target="_blank" style="margin-left:50px;">在新窗口打开</a>
		</td>
	</tr>
</table>
</form>
<?php } ?>

<script type="text/javascript">
update_sub_hid("<?php echo $_GET["g"]; ?>");
</script>


<?php
function _hebing($arr1, $arr2) {
	foreach ($arr2 as $k => $v) {
		$arr1[$k] += $v;
	}
	return $arr1;
}

if ($_GET) {
	$hids = $_GET["sub_hid"][$_GET["g"]];
	$yuyue_guiji = $daoyuan_guiji = $yuyue_qudao = $daoyuan_qudao = array();
	foreach ($hids as $_hid) {
		$yuyue_guiji1 = $db->query("select guiji, count(guiji) as c from patient_{$_hid} where addtime>=$from_time and addtime<=$to_time and guiji!='' group by guiji", "guiji", "c");
		$daoyuan_guiji1 = $db->query("select guiji, count(guiji) as c from patient_{$_hid} where order_date>=$from_time and order_date<=$to_time and guiji!='' and status=1 group by guiji", "guiji", "c");

		$yuyue_qudao1 = $db->query("select qudao, count(qudao) as c from patient_{$_hid} where addtime>=$from_time and addtime<=$to_time and qudao!='' group by qudao", "qudao", "c");
		$daoyuan_qudao1 = $db->query("select qudao, count(qudao) as c from patient_{$_hid} where order_date>=$from_time and order_date<=$to_time and qudao!='' and status=1 group by qudao", "qudao", "c");

		$yuyue_guiji = _hebing($yuyue_guiji, $yuyue_guiji1);
		$daoyuan_guiji = _hebing($daoyuan_guiji, $daoyuan_guiji1);
		$yuyue_qudao = _hebing($yuyue_qudao, $yuyue_qudao1);
		$daoyuan_qudao = _hebing($daoyuan_qudao, $daoyuan_qudao1);
	}

?>
<br>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="left" width="40%">名称</td>
		<td class="head" align="center" width="10%">预约</td>
		<td class="head" align="center" width="10%">到院</td>
		<td class="head" align="center" width="40%"></td>
	</tr>

	<!-- 主要列表数据 begin -->
<?php

foreach ($guiji_arr as $gid => $gname) {
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item"><b style="color:red"><?php echo $gname; ?></b></td>
		<td align="center" class="item"><b style="color:red"><?php echo $yuyue_guiji[$gid]; ?></b></td>
		<td align="center" class="item"><b style="color:red"><?php echo $daoyuan_guiji[$gid]; ?></b></td>
		<td align="center" class="item">&nbsp;</td>
	</tr>
<?php

	// 查询:
	$data = $db->query("select * from dict_qudao where main_id=$gid order by sort desc, id asc");
	foreach ($data as $line) {
		$id = $line["id"];
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="left" class="item">　　　　　　<?php echo $line["name"]; ?></td>
		<td align="center" class="item"><?php echo $yuyue_qudao[$id]; ?></td>
		<td align="center" class="item"><?php echo $daoyuan_qudao[$id]; ?></td>
		<td align="center" class="item">&nbsp;</td>
	</tr>
<?php
	}
}
?>
	<!-- 主要列表数据 end -->
</table>
<?php } ?>

</body>
</html>