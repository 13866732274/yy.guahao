<?php
// --------------------------------------------------------
// - ����˵�� : ������־
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2008-05-15 02:32
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_op_log";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

// �����Ĵ���:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		if (!$debug_mode) {
			check_power("d", $pinfo, $pagepower) or exit("û��ɾ��Ȩ��...");
		}

		$ids = explode(",", $_GET["id"]);
		$del_fail = $del_ok = 0;
		foreach ($ids as $_id) {
			$_id = intval($_id);
			if ($_id > 0) {
				del_data($db, $table, $_id, 0, "") ? $del_ok++ : $del_fail++;
			}
		}
		if ($del_fail > 0) {
			msg_box("ɾ���ɹ� $del_ok �����ϣ�ɾ��ʧ�� $del_fail �����ϡ�", "back", 1);
		} else {
			msg_box("ɾ���ɹ�", "back", 1);
		}
	}

	if ($op == "delete_old") {
		if ($debug_mode || $username == 'admin') {
			$time = strtotime("-1 month");
			$db->query("delete from $table where addtime<$time");
			msg_box("�ɼ�¼���ݱ��ɹ�ɾ��", "back", 1);
		}
	}

	if ($op == "clear") {
		if ($debug_mode) {
			//$db->query("truncate table `$table`");
			//msg_box("���ݱ��ɹ����..", "back", 1);
		}
	}

	if ($op == "rollback") {
		$del_id = intval($_GET["del_id"]);
		$li = $db->query("select * from sys_del_log where id='$del_id' limit 1", 1);
		if ($li["table"] != '' && $li["data"] != '') {
			$arr = unserialize($li["data"]);

			if (!is_array($arr)) {
				exit_html("�����ݲ��ܱ��ָ�....");
			}

			$count = 0;
			$sqldata = $db->sqljoin($arr);
			if ($db->query("insert into ".$li["table"]." set ".$sqldata)) {
				$count++;
			}

			if ($count > 0) {
				$db->query("delete from sys_del_log where id=$del_id limit 1");
				msg_box("�ɹ��ָ� {$count} �����ݣ�", "back", 1);
			} else {
				exit_html("���ݻָ�ʧ��...");
			}
		} else {
			exit_html("�����ݲ��ܱ��ָ�..");
		}
	}
}

// ���嵱ǰҳ��Ҫ�õ��ĵ��ò���:
$aLinkInfo = array(
	"page" => "page",
	"sortid" => "sort",
	"sorttype" => "sorttype",
	"searchword" => "searchword",
	"view_type" => "view_type",
	"author_uid" => "author_uid",
);

// ��ȡҳ����ò���:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// ���嵥Ԫ���ʽ:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	0=>array("title"=>"ѡ", "width"=>"4%", "align"=>"center"),
	2=>array("title"=>"����", "width"=>"", "align"=>"left", "sort"=>"content", "defaultorder"=>1),
	3=>array("title"=>"������IP", "width"=>"", "align"=>"left", "sort"=>"ip", "defaultorder"=>2),
	8=>array("title"=>"ҽԺ", "width"=>"", "align"=>"left", "sort"=>"hid", "defaultorder"=>1),
	4=>array("title"=>"������", "width"=>"10%", "align"=>"center", "sort"=>"uid", "defaultorder"=>2),
	5=>array("title"=>"ʱ��", "width"=>"15%", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	7=>array("title"=>"����", "width"=>"10%", "align"=>"center"),
);

// Ĭ������ʽ:
$defaultsort = 5;
$defaultorder = 2;


// ��ѯ����:
$where = array();
if ($author_uid) {
	$where[] = "uid='".$author_uid."'";
}
if ($searchword) {
	$where[] = "(binary concat(content,' ',ip,' ',author) like '%{$searchword}%')";
}
if ($_GET["view_type"] != '') {
	$where[] = "type='".$view_type."'";
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

// ��ҳ����:
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// sql��ѯ:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset, $pagesize");
if (!is_array($data)) {
	exit("<b>���ݿ�sql��ѯ����������ϵ����Ա��飺</b><br>".$db->sql);
}


// ��ѯ��������:
$t_begin = strtotime("-7 day");
$sqlwhere2 = count($where) > 0 ? (implode(" and ", $where)." and ") : "";
$top_list = $db->query("select uid,author,count(uid) as c from $table where $sqlwhere2 addtime>$t_begin group by uid order by c desc, author asc limit 100", "uid", "");


// hid => name
$hid_name_arr = $db->query("select id,name from hospital", "id", "name");
?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
* {font-family:"΢���ź�"; }
.tr_high_light td {background:#FFE1D2; }
</style>
<script language="javascript">
function is_rollback() {
	return confirm("�Ƿ�ȷ��Ҫ�ָ������ݣ�");
}
</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:20%"><nobr class="tips">������־�б�</nobr></td>
		<td class="header_center">
			<a href="?searchword=%B5%C7%C2%BC">���鿴�û���¼</a>&nbsp;&nbsp;
			<a href="?searchword=%C9%BE%B3%FD%BB%BC%D5%DF">���鿴ɾ������</a>&nbsp;&nbsp;
          <a href="?searchword=%d0%de%b8%c4">���鿴�޸Ļ���</a>&nbsp;&nbsp;
<?php if ($debug_mode) { ?>
			<a href="?op=delete_old" onclick="return confirm('��ȷ��Ҫɾ����Щ������')" style="color:red">[ɾ��һ����֮ǰ������]</a>&nbsp;&nbsp;
<?php } ?>
		</td>
		<td class="headers_oprate" style="width:30%"><form name="topform" method="GET"><nobr>�����ʣ�<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">&nbsp;<a href="?">�˳�����</a></nobr></form></td>
	</tr>
</table>
<!-- ͷ�� end -->


<div class="space"></div>
<div style="border:2px solid #fdb53d; background:#fefff7; padding:5px; ">
	<div><b style="color:#6d8300; ">һ�������Ծ���û�����ǰɸѡ�����£���</b></div>
	<div style="margin-left:50px; margin-top:5px;">
<?php foreach ($top_list as $k => $v) { ?>
		<a href="?author_uid=<?php echo $k; ?>"><b><?php echo $v["author"]; ?></b><font color="gray">(<?php echo $v["c"]; ?>)</font></a>&nbsp;
<?php } ?>
	</div>
</div>


<!-- �����б� begin -->
<div class="space"></div>
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
		if ($line["del_id"] > 0) {
			$op[] = "<a href='?op=rollback&del_id=".$line["del_id"]."' onclick='return is_rollback()' title='�ָ�������'>�ָ�</a>";
		}

		if ($debug_mode || check_power("d", $pinfo, $pagepower)) {
			$op[] = "<a href='?op=delete&id=$id' title='����ɾ�������ᵯ��ȷ�Ͽ�'>ɾ��</a>";
		}
		$op_button = implode("&nbsp;", $op);
?>
	<tr>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="left" class="item"><?php echo $line["content"]; ?></td>
		<td align="left" class="item"><?php echo cut($line["ip"], 50,"��"); ?></td>
		<td align="left" class="item"><?php echo $line["hid"] > 0 ? ($line["hid"].":".$hid_name_arr[$line["hid"]]) : '-'; ?></td>
		<td align="center" class="item"><?php echo $line["uid"].":".$line["author"]; ?></td>
		<td align="center" class="item"><?php echo date('Y-m-d<\b\r>H:i', $line["addtime"]); ?></td>
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
	<div class="footer_op_left"><button onclick="select_all()" class="button">ȫѡ</button>&nbsp;<button onclick="unselect()" class="button">��ѡ</button>&nbsp;<font color="silver">ҳ��ִ��ʱ�䣺<?php echo round(now() - $pagebegintime, 4); ?>��</font></div>
	<div class="footer_op_right"><?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?></div>
	<div class="clear"></div>
</div>
<!-- ��ҳ���� end -->

</body>
</html>
