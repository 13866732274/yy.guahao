<?php
// --------------------------------------------------------
// - ����˵�� : ý������
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-03 14:47
// --------------------------------------------------------
require "lib/set_env.php";
$table = "media";

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
				del_data($db, $table, $_id, 1, "ɾ��ý����Դ��{name}��") ? $del_ok++ : $del_fail++;
			}
		}
		if ($del_fail > 0) {
			msg_box("ɾ���ɹ� $del_ok �����ϣ�ɾ��ʧ�� $del_fail �����ϡ�", "back", 1);
		} else {
			msg_box("ɾ���ɹ�", "back", 1);
		}
	}
}

//user_op_log("��ý����Դ");

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
	0=>array("title"=>"ѡ", "width"=>"50", "align"=>"center"),
	6=>array("title"=>"����", "width"=>"80", "align"=>"center", "sort"=>"hospital_id", "defaultorder"=>1),
	5=>array("title"=>"����", "width"=>"80", "align"=>"center", "sort"=>"sort", "defaultorder"=>2),
	1=>array("title"=>"����", "width"=>"", "align"=>"left", "sort"=>"binary name", "defaultorder"=>1),
	9=>array("title"=>"ͳ��", "width"=>"", "align"=>"center"),
	3=>array("title"=>"���ʱ��", "width"=>"120", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	4=>array("title"=>"����", "width"=>"80", "align"=>"center"),
);

// Ĭ������ʽ:
$defaultsort = 5;
$defaultorder = 2;


// ��ѯ����:
$where = array();
$where[] = "(hospital_id=0 or hospital_id=".$hid.")";
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

// ҽԺ��:
$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, 'name');

// ͳ�����ݣ�
$t_from = strtotime("-3 month");
$count_data_arr = $db->query("select media_from, count(media_from) as c from patient_{$hid} where addtime>$t_from group by media_from", "media_from", "c");


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

function add(hid) {
	set_high_light('');
	parent.load_src(1,'media_edit.php?hid='+hid, 700, 300);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'media_edit.php?id='+id, 700, 300);
	return false;
}
</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips">ý����Դ</nobr></td>
		<td class="header_cneter" align="center">
<?php
if (check_power("i", $pinfo, $pagepower)) {
	echo '<a href="javascript:void(0);" onclick="add(0)"><b>���ȫ��ý����Դ</b></a>&nbsp;|&nbsp;<a href="javascript:void(0);" onclick="add('.$hid.')"><b>��ӡ�'.$h_name.'��ý����Դ(˽��)</b></a>';
}
?>
		</td>
		<td class="headers_oprate" style="width:280px;"><form name="topform" method="GET"><nobr>�ؼ��ʣ�<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">&nbsp;<button onclick="location='?'" class="search" title="�˳�������ѯ">����</button>&nbsp;<button onclick="self.location.reload()" class="button">ˢ��</button></nobr></form></td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<div class="description">
	<div class="d_title">��ȫ�֡�Ϊ��ҽԺͨ�ã����������޸ĺ�ɾ������˽�С�ֻ����ǰҽԺʹ�á� ��ͳ�ơ�Ϊ3�����ڵ�ʹ��Ƶ��</div>
</div>

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
		if (check_power("e", $pinfo, $pagepower)) {
			$op[] = "<button class='button_op' onclick='edit(".$id.", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='�޸�' alt=''></button>";
		}
		if (check_power("d", $pinfo, $pagepower)) {
			$op[] = "<button class='button_op' id='?op=delete&id=$id' onclick='if (isdel()) location=this.id;'><img src='image/b_delete.gif' align='absmiddle' title='ɾ��'></button>";
		}
		$op_button = implode("&nbsp;", $op);

		$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;

		if ($line["hospital_id"] != 0) {
			$tr_style = 'color:blue;';
		} else {
			$tr_style = 'color:red;';
		}
?>
	<tr class="<?php echo $tr_class; ?>" style="<?php echo $tr_style; ?>">
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="center" class="item"><?php echo $line["hospital_id"] == 0 ? "ȫ��" : "˽��"; ?></td>
		<td align="center" class="item"><?php echo $line["sort"]; ?></td>
		<td align="left" class="item"><?php echo $line["name"]; ?></td>
		<td align="center" class="item"><?php echo $count_data_arr[$line["name"]]; ?></td>
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
	<div class="footer_op_left"><button onclick="select_all()" class="button">ȫѡ</button>&nbsp;<button onclick="unselect()" class="button">��ѡ</button>&nbsp;<?php echo show_button("hd", $pinfo, $pagepower); ?></div>
	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
	<div class="clear"></div>
</div>
<!-- ��ҳ���� end -->

</body>
</html>