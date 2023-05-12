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
		check_power("d", $pinfo, $pagepower) or exit("û��ɾ��Ȩ��...");
		$ids = explode(",", $_GET["id"]);
		$del_fail = $del_ok = 0;
		foreach ($ids as $_id) {
			$_id = intval($_id);
			if ($_id > 0) {
				del_data($db, $table, $_id, 1, "ɾ���绰��Ŀ��{name}��") ? $del_ok++ : $del_fail++;
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
$aLinkInfo = array(
	"page" => "page",
	"sortid" => "sort",
	"sorttype" => "sorttype",
	"searchword" => "searchword",
);

// ��ȡҳ����ò���:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// ���嵥Ԫ���ʽ:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	0=>array("title"=>"ѡ", "width"=>"32", "align"=>"center"),
	1=>array("title"=>"��Ŀ����", "align"=>"left", "sort"=>"binary name", "defaultorder"=>1),
	6=>array("title"=>"����ҽԺ", "align"=>"left", "sort"=>"binary h_name", "defaultorder"=>1),
	2=>array("title"=>"�ͷ�", "align"=>"left", "sort"=>"kefu", "defaultorder"=>1),
	3=>array("title"=>"���ʱ��", "width"=>"", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	4=>array("title"=>"����", "width"=>"60", "align"=>"center"),
);

// Ĭ������ʽ:
$defaultsort = 3;
$defaultorder = 1;


// ��ѯ����:
$where = array();

$where[] = "type='tel'";

if ($searchword) {
	$where[] = "(binary name like '%{$searchword}%')";
}
$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

// ������Ĵ���
if ($sortid > 0) {
	$sqlsort = "order by ".$aTdFormat[$sortid]["sort"]." ";
	if ($sorttype > 0) {
		$sqlsort .= $aOrderType[$sorttype];
	} else {
		$sqlsort .= $aOrderType[$aTdFormat[$sortid]["defaultorder"]];
	}
} else {
	if ($defaultsort > 0 && array_key_exists($defaultsort, $aTdFormat)) {
		$sqlsort = "order by ".$aTdFormat[$defaultsort]["sort"]." ".$aOrderType[$defaultorder];
	} else {
		$sqlsort = "";
	}
}
//$sqlsort = "order by hospital, id asc";

// ��ҳ����:
$pagesize = 9999;
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// ��ѯ:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");

$admin_id_name = $db->query("select id,realname from sys_admin where isshow=1", "id", "realname");

// ҳ�濪ʼ ------------------------
?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
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

function del_confirm() {
	return confirm("ɾ���󲻿ɻָ�������Ӧ��ͳ�����ݲ���ɾ�����Ƿ�ȷ��������");
}

function add() {
	set_high_light('');
	parent.load_src(1,'count_tel_type_add.php', 800, 500);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'count_tel_type_edit.php?id='+id, 800, 500);
	return false;
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
		<td class="headers_title" style="width:280px;"><nobr class="tips">ҽԺ��Ŀ����(�绰)</nobr></td>
		<td class="headerd_cneter" align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">���</button>
<?php } ?>
		</td>
		<td class="headers_oprate" style="width:280px;"><form name="topform" method="GET"><nobr><nobr>�ؼ��ʣ�<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">&nbsp;<button onclick="location='?'" class="search" title="�˳�������ѯ">����</button>&nbsp;<button onclick="self.location.reload()" class="button">ˢ��</button></nobr></nobr></form></td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<!-- �����б� begin -->
<form name="mainform">
<table width="100%" align="center" class="list">
	<!-- ��ͷ���� begin -->
	<tr>
<?php
// ��ͷ����:
foreach ($aTdFormat as $tdid => $tdinfo) {
	list($tdalign, $tdwidth, $tdtitle) = make_td_head($tdid, $tdinfo);
?>
		<td class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>"><?php echo $tdtitle; ?></td>
<?php } ?>
	</tr>
	<!-- ��ͷ���� end -->

	<!-- ��Ҫ�б����� begin -->
<?php
if (count($data) > 0) {
	foreach ($data as $line) {
		$id = $line["id"];

		$op = array();
		$op[] = "<a href='javascript:;' onclick='edit(".$id.", this); return false;'>�޸�</a>";
		$op[] = "<a href='javascript:;' onclick='set_data_hids(".$id.", this); return false;'>���õ��ÿ���</a>";

		if (check_power("d", $pinfo, $pagepower)) {
			$op[] = "<a href='?op=delete&id=$id' onclick='return del_confirm();'>ɾ��</a>";
		}

		$op_button = implode("&nbsp;", $op);

		$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="left" class="item"><b><?php echo $line["name"]; ?></b></td>
		<td align="left" class="item"><?php echo $line["h_name"]; ?></td>
		<td align="left" class="item"><?php echo $line["kefu"]; ?></td>
		<td align="center" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
	</tr>
<?php
	}
} else {
?>
	<tr>
		<td colspan="<?php echo count($aTdFormat); ?>" align="center" class="nodata">(û������...)</td>
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
	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
	<div class="clear"></div>
</div>
<!-- ��ҳ���� end -->

</body>
</html>