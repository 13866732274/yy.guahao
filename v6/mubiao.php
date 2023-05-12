<?php
// --------------------------------------------------------
// - 功能说明 : 设置到院人数目标
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-12-20
// --------------------------------------------------------
require "lib/set_env.php";
$table = "mubiao";

if (!is_array($hospital_ids) || count($hospital_ids) == 0) {
	exit_html("对不起，没有可管理的医院。");
}
$hid_str = implode(",", $hospital_ids);


// 可管理的医院:
$hid_name = $db->query("select id,name from hospital where id in ($hid_str) order by name asc", "id", "name");

// 最近6个月的目标
$base_month = mktime(0,0,0,date("m"), 1); //本月1号
$m_arr = array();
$m_arr[] = date("Ym", strtotime("+1 month", $base_month)); //下个月
$m_arr[] = date("Ym", $base_month); //本月
for($i = 1; $i <= 4; $i++) {
	$m = date("Ym", strtotime("-".$i." month", $base_month));
	$m_arr[] = $m;
}
asort($m_arr);

// 加载目标数据
$mubiao = array();
foreach ($m_arr as $m) {
	$tmp = $db->query("select * from $table where hid in ($hid_str) and month=$m");
	if (count($tmp) > 0) {
		foreach ($tmp as $v) {
			$mubiao[$v["hid"]][$m] = $v["num"];
		}
	}
}

// 页面开始 ------------------------
?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
#data_list .item {padding:8px 2px 6px 2px; }
.tr_high_light td {background:#FFE1D2; }
</style>
<script language="javascript">
window.last_high_obj = '';
function set_high_light(obj) {
	if (last_high_obj) {
		last_high_obj.parentNode.parentNode.className = "";
	}
	if (obj) {
		obj.parentNode.parentNode.className = "tr_high_light";
		last_high_obj = obj;
	} else {
		last_high_obj = '';
	}
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'mubiao_edit.php?hid='+id, 700, 400);
	return false;
}

window.onscroll = function () {
	var s_top = document.body.scrollTop;
	var top = byid("data_list").offsetTop;
	var top_head = byid("data_head").offsetHeight;

	if (s_top >= (0 + top + top_head)) {
		var o = byid("float_head");
		o.style.display = "";
		o.style.position = "absolute";
		o.style.left = byid("data_list").style.left;
		o.style.top = s_top;
	} else {
		byid("float_head").style.display = "none";
	}
};
</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips">来诊目标设置</nobr></td>
		<td class="header_cneter" align="center">
		</td>
		<td class="headers_oprate" style="width:280px;"><button onclick="self.location.reload()" class="button">刷新</button></td>
	</tr>
</table>
<!-- 头部 end -->

<!-- 浮动表头 注意：此技术需要指定每个单元格的宽度否则上下表格可能不对齐 -->
<table id="float_head" style="display:none; border-bottom:0;" width="100%" align="center" cellpadding="0" cellspacing="0" class="list">
	<tr>
		<td class="head" style="width:20%;" align="center" width="60">医院 <font style="color:gray;font-weight:normal;">(按医院名排序)</font></td>
<?php foreach ($m_arr as $m) { ?>
		<td class="head" style="width:12%;" align="center"><?php echo int_month_to_month($m); ?></td>
<?php } ?>
		<td class="head" style="width:8%;" align="center">操作</td>
	</tr>
</table>

<!-- 数据列表 begin -->
<div class="space"></div>
<table id="data_list" width="100%" align="center" cellpadding="0" cellspacing="0" class="list">
	<tr id="data_head">
		<td class="head" style="width:20%;" align="center" width="60">医院 <font style="color:gray;font-weight:normal;">(按医院名排序)</font></td>
<?php foreach ($m_arr as $m) { ?>
		<td class="head" style="width:12%;" align="center"><?php echo int_month_to_month($m); ?></td>
<?php } ?>
		<td class="head" style="width:8%;" align="center">操作</td>
	</tr>

<?php foreach ($hid_name as $_hid => $_hname) { ?>
	<tr>
		<td class="item" align="center"><?php echo $_hname; ?></td>
<?php   foreach ($m_arr as $m) { ?>
		<td class="item" align="center" id="data_<?php echo $_hid."_".$m; ?>"><?php echo $mubiao[$_hid][$m] ? $mubiao[$_hid][$m] : '-'; ?></td>
<?php   } ?>
		<td class="item" align="center"><a href="javascript:void(0);" onclick="edit(<?php echo $_hid; ?>, this)">修改</a></td>
	</tr>
<?php } ?>

</table>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>


</body>
</html>