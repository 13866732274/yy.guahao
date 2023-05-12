<?php
// --------------------------------------------------------
// - ����˵�� : ͳ�� ��Ŀ ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2010-10-13 11:34
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_type";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

// �����Ĵ���:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		if ($id > 0) {
			del_data($db, $table, $id, 1, "ɾ��������Ŀ��{name}��");
		}
		msg_box("ɾ���ɹ�", "back", 1);
	}

	if ($op == "set_show_hide") {
		$value = $_GET["value"];
		$db->query("update $table set ishide=$value where id=$id limit 1");
		echo 'self.location.reload();';
		exit;
	}
}


// ��ѯ����:
$where = array();

$where[] = "type='web'";

if ($_GET["limitshow"]) {
	$where[] = "hid=$hid";
}

if ($_GET["key"] != "") {
	$where[] = "(concat(h_name, name, kefu) like '%".$_GET["key"]."%')";
}
$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

$data = $db->query("select * from $table $sqlwhere");

$admin_id_name = $db->query("select id,realname from sys_admin where isshow=1", "id", "realname");

// ҳ�濪ʼ ------------------------
?>
<html>
<head>
<title>ͳ�������</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/sorttable_keep.js" language="javascript"></script>
<style>
.column_sortable {color:blue !important; cursor:pointer;}
.sorttable_nosort {color:gray; }
.tr_high_light td {background:#FFE1D2; }
</style>
<script language="javascript">
function add() {
	set_high_light('');
	parent.load_src(1,'count_web_type_add.php', 900, 600);
	return false;
}
function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'count_web_type_edit.php?id='+id, 900, 600);
	return false;
}
function set_swt(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'count_web_type_set_swt.php?id='+id, 600, 600);
	return false;
}
function set_show_hide(id,show_hide_value) {
	load_js("?op=set_show_hide&value="+show_hide_value+"&id="+id);
}
function del_confirm() {
	return confirm("ɾ�����ָܻ���ȷ��Ҫɾ����");
}
function set_data_hids(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'count_type_set_hids.php?id='+id, 600, 500);
	return false;
}
</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px"><nobr class="tips">ͳ�������(����)</nobr></td>
		<td class="headers_cneter" align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">���</button>
<?php } ?>
		</td>
		<td class="headers_oprate" style="width:280px"><form name="topform" method="GET"><nobr>�ؼ��ʣ�<input name="key" value="<?php echo $_GET["key"]; ?>" class="input" size="15">��<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">��<a href="?" title="�˳�������ѯ">����</a></nobr></form></td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<!-- �����б� begin -->
<form name="mainform">
<table width="100%" align="center" class="list sortable">
	<!-- ��ͷ���� begin -->
	<tr>
		<td class="head column_sortable" title="���������" align="center" width="40">����</td>
		<td class="head column_sortable" title="���������" align="center" width="40">ID</td>
		<td class="head column_sortable" title="���������" align="left">��Ŀ����<br>����ҽԺ</td>
		<td class="head column_sortable" title="���������" align="left">�ͷ�����</td>
		<td class="head column_sortable" title="���������" align="center">���ʱ��</td>
		<td class="head sorttable_nosort" align="center">����</td>
	</tr>
	<!-- ��ͷ���� end -->

	<!-- ��Ҫ�б����� begin -->
<?php
if (count($data) > 0) {
	foreach ($data as $line) {
		$id = $line["id"];

		$op = array();
		$op[] = "<a href='javascript:;' onclick='edit(".$id.", this);'>�޸�</a>";
		$op[] = "<a href='javascript:;' onclick='set_swt(".$id.", this);'>��������ͨ</a>";
		$op[] = "<a href='javascript:;' onclick='set_data_hids(".$id.", this); return false;'>���õ��ÿ���</a>";

		if (check_power("d", $pinfo, $pagepower)) {
			$op[] = '<a href="?op=delete&id='.$id.'" onclick="return del_confirm();">ɾ��</a>';
		}
		$op_button = implode("&nbsp;", $op);

		if ($line["ishide"] == 1) {
			$show_hide = '<a href="javascript:;" onclick="set_show_hide('.$id.',0);" title="����л�Ϊ��ʾ"><font color=red>����</font></a>';
		} else {
			$show_hide = '<a href="javascript:;" onclick="set_show_hide('.$id.',1);" title="����л�Ϊ����">��ʾ</a>';
		}


?>
	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="center" class="item"><?php echo $show_hide; ?></td>
		<td align="center" class="item"><?php echo $line["id"]; ?></td>
		<td align="left" class="item">
			<nobr><?php echo $line["name"]; ?></nobr><br><font color="silver"><nobr><?php echo $line["h_name"]; ?></nobr></font>
		</td>
		<td align="left" class="item"><?php echo $line["kefu"]; ?></td>
		<td align="center" class="item"><nobr><?php echo nl2br(date("Y-m-d\nH:i", $line["addtime"])); ?></nobr></td>
		<td align="center" class="item"><nobr><?php echo $op_button; ?></nobr></td>
	</tr>
<?php
	}
} else {
?>
	<tr>
		<td colspan="6" align="center" class="nodata">(û������...)</td>
	</tr>
<?php } ?>
	<!-- ��Ҫ�б����� end -->
</table>
</form>
<!-- �����б� end -->

<br>
<br>
<br>

<?php if ($_GET["key"]) { ?>
<script>
highlightWord(document.body, "<?php echo $_GET["key"]; ?>");
</script>
<?php } ?>

</body>
</html>