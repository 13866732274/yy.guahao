<?php
// --------------------------------------------------------
// - 功能说明 : index_module
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2013-8-10
// --------------------------------------------------------
require "lib/set_env.php";
$table = "index_module";

check_power('', $pinfo) or exit("没有打开权限...");

// 操作的处理:
if ($op = $_GET["op"]) {
	if ($op == "set_status") {
		header('Content-type: text/javascript');
		$v = $_GET["status"] > 0 ? 1 : 0;
		if ($id > 0) {
			$db->query("update $table set isshow='$v' where id='$id' limit 1");
		}
		echo "parent.msg_box('设置成功'); self.location.reload(); ";
		exit;
	}

	if ($op == "delete") {
		header('Content-type: text/javascript');
		if ($id > 0) {
			$db->query("delete from $table where id=$id limit 1");
		}
		echo "parent.msg_box('删除成功'); self.location.reload(); ";
		exit;
	}

	if ($op == "set_dingzhi") {
		header('Content-type: text/javascript');
		$v = $_GET["if_dingzhi"] ? 1 : 0;
		$db->query("update $table set if_dingzhi='$v' where id='$id' limit 1");
		echo "parent.msg_box('设置成功'); self.location.reload(); ";
		exit;
	}

	if ($op == "set_show_type") {
		header('Content-type: text/javascript');
		$value = intval($_GET["show_type"]);
		$db->query("update $table set show_type='$value' where id='$id' limit 1");
		echo "parent.msg_box('保存成功'); ";
		exit;
	}
}



// 查询条件:
$where = array();
$key = trim($_GET["key"]);
if ($key != "") {
	$where[] = "(concat(name, condition_show) like '%{$key}%')";
}
$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

$sqlsort = "order by sort desc, id asc";
$data = $db->query("select * from $table $sqlwhere $sqlsort");


$hid_to_name = $db->query("select id,if(short_name!='',short_name,name) as name from hospital", "id", "name");

if ($hid > 0) {
	$h_name = $hid_to_name[$hid];
}

// 页面开始 ------------------------
?>
<html>
<head>
<title>统计模块设置</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function add(hid) {
	set_high_light('');
	parent.load_src(1,'index_module_edit.php?hid='+hid, 900, 600);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'index_module_edit.php?id='+id, 900, 600);
	return false;
}

function set_hid_access(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'index_module_set_access.php?id='+id, 800, 600);
	return false;
}

function set_status(id, status) {
	load_js("?op=set_status&id="+id+"&status="+status, "op");
}

function set_dingzhi(id, if_dingzhi) {
	load_js("?op=set_dingzhi&id="+id+"&if_dingzhi="+if_dingzhi, "op");
}

function set_show_type(id, show_type) {
	load_js("?op=set_show_type&id="+id+"&show_type="+show_type, "op");
}

function delete_line(id, crc) {
	if (confirm("删除后不能恢复，确定要删除该条资料吗？")) {
		load_js("?op=delete&id="+id+"&crc="+crc, "op");
	}
}
</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips">统计模块设置</nobr></td>
		<td class="header_cneter" align="center">
<?php
	echo '<a href="javascript:void(0);" onclick="add(0)"><b>添加全局模块</b></a>';
	if ($hid > 0) {
		echo '　<a href="javascript:void(0);" onclick="add('.$hid.')"><b>添加“'.$h_name.'”专用模块</b></a>';
	}
?>
		</td>
		<td class="headers_oprate" style="width:280px;"><form name="topform" method="GET"><nobr>关键词：<input name="key" value="<?php echo $_GET["key"]; ?>" class="input" size="12">&nbsp;<input type="submit" class="search" value="搜索" style="font-weight:bold" title="点击搜索">&nbsp;<a href="?">退出搜索</a></nobr></form></td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<!-- 数据列表 begin -->
<style type="text/css">
.item {padding-top:10px !important; padding-bottom:10px !important; }
</style>
<table width="100%" align="center" class="list">
	<!-- 表头定义 begin -->
	<tr>
		<td class="head" align="center" width="40"><nobr>ID</nobr></td>
		<td class="head" align="center" width="40"><nobr>状态</nobr></td>
		<td class="head" align="center"><nobr>所属医院</nobr></td>
		<td class="head" align="left"><nobr>名称</nobr></td>
		<td class="head" align="left" width="40%"><nobr>汇总条件</nobr></td>
		<td class="head" align="center"><nobr>优先度</nobr></td>
		<td class="head" align="center"><nobr>是否允许定制</nobr></td>
		<td class="head" align="center"><nobr>添加日期</nobr></td>
		<td class="head" align="center"><nobr>添加人</nobr></td>
		<td class="head" align="center"><nobr>操作</nobr></td>
	</tr>
	<!-- 表头定义 end -->

	<!-- 主要列表数据 begin -->
<?php
if (count($data) == 0) {
	echo '	<tr><td colspan="10" align="center" class="nodata">(暂无数据)</td></tr>';
}
foreach ($data as $line) {
	$id = $line["id"];

	if ($line["isshow"] > 0) {
		$status_str = '<a href="javascript:;" onclick="set_status('.$id.', 0)" title="点击切换为关闭">开启</a>';
	} else {
		$status_str = '<a href="javascript:;" onclick="set_status('.$id.', 1)" title="点击切换为开启" class="red">关闭</a>';
	}

	if ($line["if_dingzhi"] > 0) {
		$dingzhi_str = '<a href="javascript:;" onclick="set_dingzhi('.$id.', 0)" title="点击切换为禁止定制">允许</a>';
	} else {
		$dingzhi_str = '<a href="javascript:;" onclick="set_dingzhi('.$id.', 1)" title="点击切换为允许定制" class="red">禁止</a>';
	}


	$op = array();
	if (check_power("e", $pinfo, $pagepower)) {
		$op[] = "<a href='javascript:;' onclick='edit(".$id.", this);'>修改</a>";
	}
	if ($debug_mode) {
		$op[] = "<a href='javascript:;' onclick='delete_line(".$id.", ".$line["addtime"].");'>删除</a>";
	}
	$op_button = implode("&nbsp;", $op);

	$line_class = $line["isshow"] == 0 ? "hide" : "";

?>
	<tr class="<?php echo $line_class; ?>">
		<td align="center" class="item"><nobr><?php echo $line["id"]; ?></nobr></td>
		<td align="center" class="item"><nobr><?php echo $status_str; ?></nobr></td>
		<td align="center" class="item"><?php echo $line["hospital_id"] == 0 ? '全局' : ('<font color="red">'.$hid_to_name[$line["hospital_id"]].'</font>'); ?></td>
		<td align="left" class="item"><?php echo $line["name"]; ?></td>
		<td align="left" class="item"><?php echo $line["condition_show"] ? $line["condition_show"] : '<font color="silver">(汇总全部)</font>'; ?></td>
		<td align="center" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo $dingzhi_str; ?></td>
		<td align="center" class="item"><?php echo date("Y.m.d", $line["addtime"]); ?></td>
		<td align="center" class="item"><?php echo $line["author"]; ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
	</tr>
<?php } ?>

</table>
<!-- 数据列表 end -->

<br>
<br>
<br>

</body>
</html>