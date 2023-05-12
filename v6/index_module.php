<?php
// --------------------------------------------------------
// - ����˵�� : index_module
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2013-8-10
// --------------------------------------------------------
require "lib/set_env.php";
$table = "index_module";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

// �����Ĵ���:
if ($op = $_GET["op"]) {
	if ($op == "set_status") {
		header('Content-type: text/javascript');
		$v = $_GET["status"] > 0 ? 1 : 0;
		if ($id > 0) {
			$db->query("update $table set isshow='$v' where id='$id' limit 1");
		}
		echo "parent.msg_box('���óɹ�'); self.location.reload(); ";
		exit;
	}

	if ($op == "delete") {
		header('Content-type: text/javascript');
		if ($id > 0) {
			$db->query("delete from $table where id=$id limit 1");
		}
		echo "parent.msg_box('ɾ���ɹ�'); self.location.reload(); ";
		exit;
	}

	if ($op == "set_dingzhi") {
		header('Content-type: text/javascript');
		$v = $_GET["if_dingzhi"] ? 1 : 0;
		$db->query("update $table set if_dingzhi='$v' where id='$id' limit 1");
		echo "parent.msg_box('���óɹ�'); self.location.reload(); ";
		exit;
	}

	if ($op == "set_show_type") {
		header('Content-type: text/javascript');
		$value = intval($_GET["show_type"]);
		$db->query("update $table set show_type='$value' where id='$id' limit 1");
		echo "parent.msg_box('����ɹ�'); ";
		exit;
	}
}



// ��ѯ����:
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

// ҳ�濪ʼ ------------------------
?>
<html>
<head>
<title>ͳ��ģ������</title>
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
	if (confirm("ɾ�����ָܻ���ȷ��Ҫɾ������������")) {
		load_js("?op=delete&id="+id+"&crc="+crc, "op");
	}
}
</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips">ͳ��ģ������</nobr></td>
		<td class="header_cneter" align="center">
<?php
	echo '<a href="javascript:void(0);" onclick="add(0)"><b>���ȫ��ģ��</b></a>';
	if ($hid > 0) {
		echo '��<a href="javascript:void(0);" onclick="add('.$hid.')"><b>��ӡ�'.$h_name.'��ר��ģ��</b></a>';
	}
?>
		</td>
		<td class="headers_oprate" style="width:280px;"><form name="topform" method="GET"><nobr>�ؼ��ʣ�<input name="key" value="<?php echo $_GET["key"]; ?>" class="input" size="12">&nbsp;<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">&nbsp;<a href="?">�˳�����</a></nobr></form></td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<!-- �����б� begin -->
<style type="text/css">
.item {padding-top:10px !important; padding-bottom:10px !important; }
</style>
<table width="100%" align="center" class="list">
	<!-- ��ͷ���� begin -->
	<tr>
		<td class="head" align="center" width="40"><nobr>ID</nobr></td>
		<td class="head" align="center" width="40"><nobr>״̬</nobr></td>
		<td class="head" align="center"><nobr>����ҽԺ</nobr></td>
		<td class="head" align="left"><nobr>����</nobr></td>
		<td class="head" align="left" width="40%"><nobr>��������</nobr></td>
		<td class="head" align="center"><nobr>���ȶ�</nobr></td>
		<td class="head" align="center"><nobr>�Ƿ�������</nobr></td>
		<td class="head" align="center"><nobr>�������</nobr></td>
		<td class="head" align="center"><nobr>�����</nobr></td>
		<td class="head" align="center"><nobr>����</nobr></td>
	</tr>
	<!-- ��ͷ���� end -->

	<!-- ��Ҫ�б����� begin -->
<?php
if (count($data) == 0) {
	echo '	<tr><td colspan="10" align="center" class="nodata">(��������)</td></tr>';
}
foreach ($data as $line) {
	$id = $line["id"];

	if ($line["isshow"] > 0) {
		$status_str = '<a href="javascript:;" onclick="set_status('.$id.', 0)" title="����л�Ϊ�ر�">����</a>';
	} else {
		$status_str = '<a href="javascript:;" onclick="set_status('.$id.', 1)" title="����л�Ϊ����" class="red">�ر�</a>';
	}

	if ($line["if_dingzhi"] > 0) {
		$dingzhi_str = '<a href="javascript:;" onclick="set_dingzhi('.$id.', 0)" title="����л�Ϊ��ֹ����">����</a>';
	} else {
		$dingzhi_str = '<a href="javascript:;" onclick="set_dingzhi('.$id.', 1)" title="����л�Ϊ������" class="red">��ֹ</a>';
	}


	$op = array();
	if (check_power("e", $pinfo, $pagepower)) {
		$op[] = "<a href='javascript:;' onclick='edit(".$id.", this);'>�޸�</a>";
	}
	if ($debug_mode) {
		$op[] = "<a href='javascript:;' onclick='delete_line(".$id.", ".$line["addtime"].");'>ɾ��</a>";
	}
	$op_button = implode("&nbsp;", $op);

	$line_class = $line["isshow"] == 0 ? "hide" : "";

?>
	<tr class="<?php echo $line_class; ?>">
		<td align="center" class="item"><nobr><?php echo $line["id"]; ?></nobr></td>
		<td align="center" class="item"><nobr><?php echo $status_str; ?></nobr></td>
		<td align="center" class="item"><?php echo $line["hospital_id"] == 0 ? 'ȫ��' : ('<font color="red">'.$hid_to_name[$line["hospital_id"]].'</font>'); ?></td>
		<td align="left" class="item"><?php echo $line["name"]; ?></td>
		<td align="left" class="item"><?php echo $line["condition_show"] ? $line["condition_show"] : '<font color="silver">(����ȫ��)</font>'; ?></td>
		<td align="center" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo $dingzhi_str; ?></td>
		<td align="center" class="item"><?php echo date("Y.m.d", $line["addtime"]); ?></td>
		<td align="center" class="item"><?php echo $line["author"]; ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
	</tr>
<?php } ?>

</table>
<!-- �����б� end -->

<br>
<br>
<br>

</body>
</html>