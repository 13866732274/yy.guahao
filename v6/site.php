<?php
// --------------------------------------------------------
// - ����˵�� : ��վ�б�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2014-5-10
// --------------------------------------------------------
require "lib/set_env.php";
$table = "site_list";

if ($user_hospital_id == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

if (!($config["show_site"] || $debug_mode)) {
	exit("�Բ�����û��Ȩ��~");
}

$op = $_GET["op"];

// �����Ĵ���:
if ($op == "delete") {
	$id_arr = explode(",", $_GET["id"]);
	foreach ($id_arr as $id) {
		$id = intval($id);
		$db->query("delete from $table where hid=$hid and id=$id limit 1");
	}
	msg_box("ɾ���ɹ�", "back", 1);
}

if ($op == "update_out_date") {
	$cache_arr = array();
	$list = $db->query("select * from site_list where hid=$hid order by sort desc, id asc", "id");
	foreach ($list as $lid => $li) {
		$url = _get_just_domain($li["site_url"]);
		if (array_key_exists($url, $cache_arr)) {
			$t = $cache_arr[$url];
		} else {
			$t = trim(get_domain_out_date($url));
			$cache_arr[$url] = $t;
		}
		$sql = "update site_list set `out_date`='$t' where id=$lid limit 1";
		$db->query($sql);
	}
	echo '<script>self.location = "site.php";</script>';
	exit;
}


if ($op == "delete_all") {
	if ($debug_mode) {
		//$db->query("delete from site_list where hid=$hid");
		//$num = mysql_affected_rows();
		//echo '<script> alert("�ɹ�ɾ���� '.$num.' �����ݡ�"); self.location = "?"; </script>';
	}
	exit;
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
	0=>array("title"=>"ѡ", "width"=>"4%", "align"=>"center"),
	1=>array("title"=>"ID", "width"=>"", "align"=>"center", "sort"=>"id", "defaultorder"=>1),
	2=>array("title"=>"����", "width"=>"", "align"=>"left", "sort"=>"type_name", "defaultorder"=>1),
	3=>array("title"=>"��ַ", "width"=>"", "align"=>"left", "sort"=>"site_url", "defaultorder"=>1),
	9=>array("title"=>"����ʱ��", "width"=>"", "align"=>"center", "sort"=>"out_date", "defaultorder"=>1),
	11=>array("title"=>"״̬", "width"=>"", "align"=>"center", "sort"=>"out_date", "defaultorder"=>1),
	13=>array("title"=>"������", "width"=>"", "align"=>"center", "sort"=>"beian_num", "defaultorder"=>1),
	12=>array("title"=>"whois��ѯ", "width"=>"", "align"=>"center", "sort"=>"", "defaultorder"=>0),
	10=>array("title"=>"��ע", "width"=>"", "align"=>"center", "sort"=>"memo", "defaultorder"=>1),
	4=>array("title"=>"���ȶ�", "width"=>"", "align"=>"left", "sort"=>"sort", "defaultorder"=>2),
	5=>array("title"=>"���ʱ��", "width"=>"", "align"=>"center", "sort"=>"addtime", "defaultorder"=>2),
	6=>array("title"=>"�����", "width"=>"", "align"=>"center", "sort"=>"author", "defaultorder"=>2),
	7=>array("title"=>"����", "width"=>"", "align"=>"center"),
);

// Ĭ������ʽ:
$defaultsort = 9;
$defaultorder = 1;

// ��ѯ����:
$where = array();
$where[] = "hid=$user_hospital_id";
if ($searchword) {
	$where[] = "(concat(type_name, site_url, author, memo) like '%{$searchword}%')";
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
	$sqlsort = "order by out_date asc, id asc";
}

// ��ҳ����:
$pagesize = 100;
$count = $db->query_count("select count(*) from $table $sqlwhere");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// ��ѯ:
$data = $db->query("select * from $table $sqlwhere $sqlsort limit $offset,$pagesize");

$hospital_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");



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

.tip_out_date {color:blue; }
.tip_will_out_date {background:#ff359a; border:1px solid #c40062; color:yellow; padding:4px 3px 2px 3px; }
.tip_normal {}
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
	parent.load_src(1,'site_edit.php', 900, 550);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'site_edit.php?id='+id, 900, 550);
	return false;
}

function update_out_date(ym_id) {
	var url = "http/ym_update_out_date.php?ym_id="+ym_id+"&do=update_out_date_do";
	load_js(url, "ym_update_out_date"+ym_id);
	byid("outdate_"+ym_id).innerHTML = "��ѯ��";
}

function update_out_date_do(res) {
	if (res && res["status"] == 'ok') {
		byid("outdate_"+res["ym_id"]).innerHTML = res["out_date"];
	} else {
		alert("����ʧ��...");
	}
}

function confirm_delete() {
	if (!confirm("ɾ�����ָܻ����Ƿ�ȷ��Ҫɾ����")) {
		return false;
	}
}

function piliang_update_out_date() {
	if (!confirm("��ע�⣺�˲��������ѽϳ�ʱ�䣬��ʼ���벻Ҫˢ��ҳ�棬������������������ɡ��Ƿ�ȷ��������")) {
		return false;
	}
	var delay = 0;
	var g = document.getElementsByTagName("A");
	if (g.length > 0) {
		for (var i=0; i<g.length; i++) {
			if (g[i].id && g[i].id.split("_")[0] == "outdate") {
				setTimeout("update_out_date("+g[i].id.split("_")[1]+")", delay);
				delay += 500;
			}
		}
	}
}

</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title" style="width:280px;"><nobr class="tips"><?php echo $hospital_name; ?> - ��վ�б�</nobr></td>
		<td class="header_cneter" align="center">
			<button onclick="add()" class="button">���</button>&nbsp;
			<a href="javascript:;" onclick="piliang_update_out_date();"><b>[�������±�ҳ��������ʱ��]</b></a>&nbsp;
		</td>
		<td class="headers_oprate" style="width:300px;">
			<form name="topform" method="GET">
				�ؼ��ʣ�<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" size="15">&nbsp;<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">&nbsp;<a href="?" title="�˳�������ѯ">����</a>&nbsp;
				<button onclick="self.location.reload();" class="button" title="���ˢ��ҳ��">ˢ��</button>
			</form>
		</td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<!-- �����б� begin -->
<form name="mainform">
<table width="100%" align="center" class="list" id="main_table">
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

	$dt0 = date("Ymd");
	$dt1 = date("Ymd", strtotime("+6 month"));

	foreach ($data as $line) {
		$id = $line["id"];

		$_dt = date("Ymd", strtotime($line["out_date"]));
		if (trim($line["out_date"]) == '') {
			$out_status = '������';
		} else if ($_dt <= 19700101) {
			$out_status = '���ֹ���ѯ';
		} else {
			if ($_dt < $dt0) {
				$out_status = '<span class="tip_out_date">�ѹ���</span>';
			} else if ($_dt < $dt1) {
				$out_status = '<span class="tip_will_out_date">��������</span>';
			} else {
				$out_status = '<span class="tip_normal">����</span>';
			}
		}


		$op = array();
		$op[] = "<button class='button_op' onclick='edit(".$id.", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='�޸�' alt=''></button>";

		$can_delete_this_line = 0;
		if ($line["author"] == $realname || $username == "������" || $debug_mode) {
			$can_delete_this_line = 1;
			$op[] = "<a href='?op=delete&id=$id' onclick='return confirm_delete()'>ɾ��</a>";
		}
		$op_button = implode("&nbsp;", $op);

		if ($line["auto_update"] < 1) {
			$out_date = '<b style="color:green" title="�˹���д">'.$line["out_date"].'</b>';
		} else {
			$out_date = $line["out_date"] ? $line["out_date"] : "��ѯ";
			$out_date = '<a href="javascript:;" onclick="update_out_date('.$line["id"].')" id="outdate_'.$line["id"].'" title="������µ���ʱ��">'.$out_date.'</a>';
		}
?>
	<tr>
		<td align="center" class="item"><?php if ($can_delete_this_line) { ?><input name="delcheck" type="checkbox" value="<?php echo $id; ?>" onpropertychange="set_item_color(this)"><?php } ?></td>
		<td align="center" class="item"><?php echo $line["id"]; ?></td>
		<td align="left" class="item"><?php echo $line["type_name"]; ?></td>
		<td align="left" class="item"><a href="http://<?php echo $line["site_url"]; ?>/" target="_blank" title="����´��ڴ�"><?php echo $line["site_url"]; ?></a></td>
		<td align="center" class="item"><?php echo $out_date; ?></td>
		<td align="center" class="item"><?php echo $out_status; ?></td>
		<td align="center" class="item"><?php echo $line["beian_num"] == "" ? "��" : $line["beian_num"]; ?></td>
		<td align="center" class="item"><a href="http://whois.chinaz.com/<?php echo $line["site_url"]; ?>" target="_blank">�鿴</a></td>
		<td align="left" class="item"><?php echo $line["memo"]; ?></td>
		<td align="left" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
		<td align="center" class="item"><?php echo $line["author"]; ?></td>
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
<table width="100%">
	<tr>
		<td align="left" style="width:300px;">
			<button onclick="select_all()" class="button">ȫѡ</button>&nbsp;
			<button onclick="unselect()" class="button">��ѡ</button>&nbsp;&nbsp;&nbsp;
			<button onclick="del()" class="buttonb">ɾ����ѡ</button>
		</td>
		<td align="center">
<?php if (0) { ?>
			<a href="?op=delete_all" onclick="return confirm('ɾ������Ĳ��ָܻ���һ��Ҫ���ذ����Ƿ�ȷ����')"><b>[����Աģʽ:��ǰ��������ȫ��ɾ��]</b></a>
<?php } ?>
		</td>
		<td align="right" class="footer_op_right" style="width:300px;">
			<?php echo pagelinkc($page, $pagecount, $count, make_link_info($aLinkInfo, "page"), "button"); ?>
		</td>
	</tr>
</table>
<!-- ��ҳ���� end -->


<?php if ($searchword) { ?>
<!-- �ؼ��ʸ��� -->
<script>
highlightWord(byid("main_table"), "<?php echo $searchword; ?>");
</script>
<?php } ?>


</body>
</html>

