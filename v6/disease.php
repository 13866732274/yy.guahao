<?php
// --------------------------------------------------------
// - ����˵�� : ҽԺ�б�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-01 00:36
// --------------------------------------------------------
require "lib/set_env.php";
$table = "disease";

if ($user_hospital_id == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

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
				del_data($db, $table, $_id, 1, "ɾ��������{name}��") ? $del_ok++ : $del_fail++;
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
	10=>array("title"=>"ѡ", "width"=>"40", "align"=>"center"),
	11=>array("title"=>"ID", "width"=>"60", "align"=>"center", "sort"=>"id", "defaultorder"=>1),
	20=>array("title"=>"��������", "width"=>"", "align"=>"left", "sort"=>"name", "defaultorder"=>1),
	25=>array("title"=>"��������", "width"=>"", "align"=>"left", "sort"=>"disease_2", "defaultorder"=>1),
	30=>array("title"=>"������Ŀ", "width"=>"", "align"=>"left", "sort"=>"xiangmu", "defaultorder"=>1),
	50=>array("title"=>"���ȶ�", "width"=>"", "align"=>"center", "sort"=>"sort", "defaultorder"=>2),
	60=>array("title"=>"ʹ��Ƶ��", "width"=>"", "align"=>"center", "sort"=>"", "defaultorder"=>2),
	90=>array("title"=>"���ʱ��", "width"=>"", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	99=>array("title"=>"����", "width"=>"100", "align"=>"center"),
);


// ��ѯ����:
$where = array();
$where[] = "hospital_id=$user_hospital_id";
if ($searchword) {
	if (substr_count($searchword, "��") > 0) {
		$_arr = explode("��", $searchword);
		$where_2 = array();
		foreach ($_arr as $v) {
			$v = trim($v);
			if ($v != '') {
				$where_2[] = "name='{$v}'";
			}
		}
		$s = $where[] = "(".implode(" or ", $where_2).")";
		//echo $s;
	} else {
		$where[] = "(concat(name,' ',disease_2,' ',xiangmu)  like '%{$searchword}%')";
	}
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
	$sqlsort = "order by sort desc, id asc";
}

// ��ҳ����:
$pagesize = 9999;
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// ��ѯ:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");

$hospital_id_name = $db->query("select id,name from hospital", 'id', 'name');


// ��ѯ������Ӧ������:
$did_num_arr = $db->query("select disease_id, count(disease_id) as num from patient_{$hid} group by disease_id", "disease_id", "num");

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

function add() {
	set_high_light('');
	parent.load_src(1,'disease_edit.php', 900, 550);
	return false;
}

function add_piliang() {
	set_high_light('');
	parent.load_src(1,'disease_add_piliang.php', 400, 500);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'disease_edit.php?id='+id, 900, 550);
	return false;
}
</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips"><?php echo $hospital_id_name[$user_hospital_id]; ?> - �����б�</nobr></td>
		<td class="header_cneter" align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">���</button>&nbsp;
			<button onclick="add_piliang()" class="buttonb">�������</button>&nbsp;
			<button onclick="disease_hebing()" class="buttonb">�ϲ�����</button>&nbsp;
			<script type="text/javascript">
				function disease_hebing() {
					parent.load_src(1,'disease_hebing.php', 700, 350);
					return false;
				}
			</script>
<?php } ?>
		</td>
		<td class="headers_oprate" style="width:320px;"><form name="topform" method="GET"><nobr>�ؼ��ʣ�<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">&nbsp;<button onclick="location='?'" class="search" title="�˳�������ѯ">����</button>&nbsp;<button onclick="self.location.reload()" class="button">ˢ��</button></nobr></form></td>
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
		<td class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>"><nobr><?php echo $tdtitle; ?></nobr></td>
<?php } ?>
	</tr>
	<!-- ��ͷ���� end -->

	<!-- ��Ҫ�б����� begin -->
<?php
if (count($data) > 0) {
	foreach ($data as $line) {
		$id = $line["id"];

		$op = array();
		if (check_power("e", $pinfo, $pagepower)) {
			$op[] = "<button class='button_op' onclick='edit(".$id.", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='�޸�' alt=''></button>";
		}
		if (check_power("d", $pinfo, $pagepower)) {
			$op[] = "<button class='button_op' id='?op=delete&id=$id' onclick='if (isdel()) location=this.id;'><img src='image/b_delete.gif' align='absmiddle' title='ɾ��'></button>";
		}
		$op_button = implode("&nbsp;", $op);

		$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="center" class="item"><?php echo $line["id"]; ?></td>
		<td align="left" class="item"><?php echo $line["name"]; ?></td>
		<td align="left" class="item"><?php echo $line["disease_2"]; ?></td>
		<td align="left" class="item"><?php echo $line["xiangmu"]; ?></td>
		<td align="center" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo intval($did_num_arr[$id]); ?></td>
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
	<div class="footer_op_left">
		<button onclick="select_all()" class="button">ȫѡ</button>&nbsp;
		<button onclick="unselect()" class="button">��ѡ</button>&nbsp;
		<?php echo show_button("hd", $pinfo, $pagepower); ?>&nbsp;
		<button onclick="show_selected_id();" class="buttonb">��ѡID</button>
	</div>
	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
	<div class="clear"></div>
</div>
<script type="text/javascript">
function show_selected_id() {
	var s = get_select();
	byid("wee_ids").innerHTML = s;
}
</script>
<!-- ��ҳ���� end -->

<div id="wee_ids"></div>

</body>
</html>