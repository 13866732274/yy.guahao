<?php
// --------------------------------------------------------
// - ����˵�� : ҽԺ�����б�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2014-4-16
// --------------------------------------------------------
require "lib/set_env.php";
$table = "hospital_group";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

// �����Ĵ���:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		check_power("d", $pinfo, $pagepower) or exit("û��ɾ��Ȩ��...");

		$ids = explode(",", $_GET["id"]);
		$del_fail = $del_ok = 0;
		foreach ($ids as $_id) {
			$_id = intval($_id);
			if ($_id > 0) {
				del_data($db, $table, $_id, 1, "ɾ�����顰{name}��") ? $del_ok++ : $del_fail++;
			}
		}

		if ($del_fail > 0) {
			msg_box("ɾ���ɹ� $del_ok �����ϣ�ɾ��ʧ�� $del_fail �����ϡ�", "back", 1);
		} else {
			msg_box("ɾ���ɹ�", "back", 1);
		}
	}
}

// ���嵱ǰҳ��Ҫ�õ��ĵ��ò���:
$aLinkInfo = array("searchword" => "searchword");

// ��ȡҳ����ò���:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// ��ѯ����:
$where = array();
if ($searchword) {
	$where[] = "(binary name like '%{$searchword}%')";
}
$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

// ��ѯ:
$data = $db->query("select * from $table $sqlwhere order by sort desc, name asc");

// ʹ������ͳ��
$use_count_arr = $db->query("select group_id, count(group_id) as c from hospital group by group_id", "group_id", "c");

?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/sorttable_keep.js" language="javascript"></script>
<style>
.column_sortable {color:blue !important; cursor:pointer; font-family:"΢���ź�"; }
.sorttable_nosort {color:gray; }
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

function add() {
	set_high_light('');
	parent.load_src(1,'hospital_group_edit.php', 900, 550);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'hospital_group_edit.php?id='+id, 900, 550);
	return false;
}

function apply() {
	parent.load_src(1,'hospital_group.php?op=apply', 700, 300);
	return false;
}

</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td style="width:150px"><nobr class="tips">ҽԺ�������</nobr></td>
		<td align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">���</button>&nbsp;&nbsp;
<?php } ?>
			<button onclick="apply()" class="button" title="�޸ķ������ƺ����˰�ť����ҽԺ��">Ӧ��</button>
		</td>
		<td align="right" style="width:280px"><form name="topform" method="GET"><nobr>�ؼ��ʣ�<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">&nbsp;<button onclick="location='?'" class="search" title="�˳�������ѯ">����</button>&nbsp;<button onclick="self.location.reload()" class="button">ˢ��</button></nobr></form></td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<!-- �����б� begin -->
<form name="mainform">
<table width="100%" align="center" class="list sortable" id="table_hospital">
	<tr>
		<td class="head column_sortable" title="���������" align="center">ID</td>
		<td class="head column_sortable" title="���������" align="left">��������</td>
		<td class="head" align="left">ʹ�ô���</td>
		<td class="head column_sortable" title="���������" align="left">���ȶ�</td>
		<td class="head sorttable_nosort" align="center" width="80">����</td>
	</tr>

	<!-- ��Ҫ�б����� begin -->
<?php
if (count($data) > 0) {
	foreach ($data as $line) {
		$id = $line["id"];
		$used_count = @intval($use_count_arr[$id]);

		$op = array();
		if (check_power("e", $pinfo, $pagepower)) {
			$op[] = "<button class='button_op' onclick='edit(".$id.", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='�޸�' alt=''></button>";
		}
		if (check_power("d", $pinfo, $pagepower)) {
			if ($used_count == 0) {
				$op[] = '<a href="?op=delete&id=$id" onclick="return isdel()">ɾ��</a>';
			}
		}
		$op_button = implode("&nbsp;", $op);

?>
	<tr onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="center" class="item"><?php echo $line["id"]; ?></td>
		<td align="left" class="item"><?php echo $line["name"]; ?></td>
		<td align="left" class="item"><?php echo $used_count; ?></td>
		<td align="left" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
	</tr>
<?php
	}
} else {
?>
	<tr>
		<td colspan="5" align="center" class="nodata">(û������...)</td>
	</tr>
<?php } ?>
	<!-- ��Ҫ�б����� end -->
</table>
</form>
<!-- �����б� end -->

<div class="space"></div>

<!-- ��ҳ���� begin -->
<div class="footer_op">
	<div class="footer_op_left"><button onclick="select_all()" class="button">ȫѡ</button>&nbsp;<button onclick="unselect()" class="button">��ѡ</button></div>
	<div class="footer_op_right">���� <b><?php echo count($data); ?></b> ������&nbsp;</div>
	<div class="clear"></div>
</div>
<!-- ��ҳ���� end -->

</body>
</html>