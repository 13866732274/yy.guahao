<?php
// --------------------------------------------------------
// - ����˵�� : ��¼�����¼
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2008-05-15 03:11
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_login_error";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

// �����Ĵ���:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		$id = intval($_GET["id"]);
		$db->query("delete from $table where id=$id limit 1");

		echo 'self.location.reload()';
		exit;
	}

	if ($op == "delete_old") {
		if ($debug_mode || $username == 'admin') {
			$time = time()-7*24*3600;
			$db->query("delete from $table where addtime<$time");
			msg_box("�ɼ�¼���ݱ��ɹ�ɾ��", "back", 1);
		}
	}

	if ($op == "clear") {
		if ($debug_mode) {
			$db->query("truncate table `$table`");
			msg_box("���ݱ��ɹ����..", "back", 1);
		}
	}
}


// �������� 2011-12-10
$db->query("update $table l, sys_admin u set l.realname=u.realname where l.realname='' and l.tryname=u.name");


// ���嵱ǰҳ��Ҫ�õ��ĵ��ò���:
$aLinkInfo = array(
	"page" => "page",
	"sortid" => "sort",
	"sorttype" => "sorttype",
	"searchword" => "searchword",
	"name" => "name",
);

// ��ȡҳ����ò���:
foreach ($aLinkInfo as $local_var_name => $call_var_name) {
	$$local_var_name = $_GET[$call_var_name];
}

// ���嵥Ԫ���ʽ:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$aTdFormat = array(
	0=>array("title"=>"ѡ", "width"=>"4%", "align"=>"center"),
	2=>array("title"=>"��������", "width"=>"", "align"=>"center", "sort"=>"binary tryname", "defaultorder"=>1),
	3=>array("title"=>"��������", "width"=>"", "align"=>"center", "sort"=>"binary trypass", "defaultorder"=>2),
	4=>array("title"=>"������IP", "width"=>"", "align"=>"center", "sort"=>"binary userip", "defaultorder"=>2),
	8=>array("title"=>"������Ӧʵ��", "width"=>"", "align"=>"center"),
	5=>array("title"=>"ʱ��", "width"=>"15%", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	7=>array("title"=>"����", "width"=>"10%", "align"=>"center"),
);

// Ĭ������ʽ:
$defaultsort = 5;
$defaultorder = 2;


// ��ѯ����:
$where = array();
if ($name) {
	$where[] = "tryname='".$name."'";
}
if ($searchword) {
	$where[] = "(binary tryname like '%{$searchword}%' or binary trypass like '%{$searchword}%')";
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


// ��ѯ������������:
$t_begin = strtotime("-1 month");
$top_list = $db->query("select tryname,count(tryname) as c from $table where addtime>$t_begin group by tryname order by c desc limit 30", "tryname", "c");

?>
<html>
<head>
<title><?php echo $pinfo["title"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips">�û�����������¼</nobr></td>
		<td class="header_center">
<?php if ($debug_mode) { ?>
ά����<a href="?op=delete_old" onclick="return confirm('��ȷ��Ҫɾ����Щ������')">ɾ��һ��֮ǰ������</a>
	<?php if ($debug_mode) { ?>
	&nbsp;<a href="?op=clear" onclick="return confirm('ȷ��Ҫ�����')">���</a>
	<?php } ?>
<?php } ?>
		</td>
		<td class="headers_oprate"><form name="topform" method="GET"><nobr>�ؼ��ʣ�<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">&nbsp;<button onclick="location='?'" class="search" title="�˳�������ѯ">����</button>&nbsp;<button onclick="self.location.reload()" class="button">ˢ��</button></nobr></form></td>
	</tr>
</table>
<!-- ͷ�� end -->

<!-- ���������ص��û� begin -->
<div class="space"></div>
<div style="border:2px solid #fdb53d; background:#fefff7; padding:5px; ">
	<div><b style="color:#6d8300; ">һ�����ڴ��������û���</b></div>
	<div style="margin-left:50px; margin-top:5px;">
<?php foreach ($top_list as $k => $v) { ?>
		<a href="?name=<?php echo urlencode($k); ?>"><b><?php echo $k; ?></b><font color="gray">(<?php echo $v; ?>)</font></a>&nbsp;
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
		if ($debug_mode || check_power("d", $pinfo, $pagepower)) {
			$op[] = '<a href="javascript:;" onclick="delete_line('.$id.', this);">ɾ��</a>';
			$can_delete = 1;
		}
		$op_button = implode("&nbsp;", $op);
?>
	<tr>
		<td align="center" class="item"><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"></td>
		<td align="center" class="item"><?php echo $line["tryname"]; ?></td>
		<td align="center" class="item"><?php echo $line["trypass"]; ?></td>
		<td align="center" class="item"><?php echo $line["userip"]; ?></td>
		<td align="center" class="item"><?php echo $line["realname"]; ?></td>
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

<?php if ($can_delete) { ?>
<script type="text/javascript">
function delete_line(line_id, obj) {
	if (confirm("ɾ�����ָܻ����Ƿ�ȷ��ɾ����")) {
		load_js("?op=delete&id="+line_id);
	}
}
</script>
<?php } ?>

</body>
</html>