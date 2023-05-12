<?php
// --------------------------------------------------------
// - ����˵�� : module.php
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2008-05-13 18:46
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_menu";
check_power('', $pinfo) or exit("û�д�Ȩ��...");

// �����Ĵ���:
if ($op == "delete") {
	header('Content-type: text/javascript');
	if ($id > 0) {
		$crc = intval($_GET["crc"]);
		$db->query("delete from $table where id=$id and addtime=$crc limit 1");
	}
	echo "parent.msg_box('ɾ���ɹ�'); self.location.reload(); ";
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
	0 => array("title" => "ID", "width" => "4%", "align" => "center"),
	1 => array("title" => "����", "width" => "5%", "align" => "center", "sort" => "", "defaultorder" => 1),
	2 => array("title" => "���", "width" => "5%", "align" => "center", "sort" => "", "defaultorder" => 1),
	3 => array("title" => "����?", "width" => "6%", "align" => "center", "sort" => "", "defaultorder" => 2),
	4 => array("title" => "����", "width" => "15%", "align" => "left", "sort" => "", "defaultorder" => 1),
	5 => array("title" => "����", "width" => "25%", "align" => "left", "sort" => "", "defaultorder" => 1),
	6 => array("title" => "����˵��", "width" => "30%", "align" => "left", "sort" => "", "defaultorder" => 1),
	7 => array("title" => "����", "width" => "10%", "align" => "center"),
);

// Ĭ������ʽ:
$defaultsort = 0;
$defaultorder = 0;


// ��ѯ����:
$where = array();
if ($searchword) {
	$where[] = "(binary t.title like '%{$searchword}%' or binary t.link like '%{$searchword}%' or binary tips like '%{$searchword}%')";
}
$sqlwhere = count($where) > 0 ? ("where " . implode(" and ", $where)) : "";

// ������Ĵ���
if ($sortid > 0) {
	$sqlsort = "order by " . $aTdFormat[$sortid]["sort"] . " ";
	if ($sorttype > 0) {
		$sqlsort .= $aOrderType[$sorttype];
	} else {
		$sqlsort .= $aOrderType[$aTdFormat[$sortid]["defaultorder"]];
	}
} else {
	if ($defaultsort > 0 && array_key_exists($defaultsort, $aTdFormat)) {
		$sqlsort = "order by " . $aTdFormat[$defaultsort]["sort"] . " " . $aOrderType[$defaultorder];
	} else {
		$sqlsort = "";
	}
}
//$sqlsort = "order by type desc, sort asc";

// ��ҳ����:
$pagesize = 9999;
$count = $db->query_count("select count(*) from $table");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// sql��ѯ:
$data = array();
$tm = $db->query("select * from $table where type=1 order by sort asc,id asc");
foreach ($tm as $tml) {
	$data[] = $tml;
	$tm2 = $db->query("select * from $table where mid=" . $tml["mid"] . " and type=0 order by sort asc, id asc");
	foreach ($tm2 as $tml2) {
		$data[] = $tml2;
	}
}
?>
<html>

<head>
    <title><?php echo $pinfo["title"]; ?></title>
    <meta http-equiv="Content-Type" content="text/html;charset=gb2312">
    <link href="lib/base.css" rel="stylesheet" type="text/css">
    <script src="lib/base.js" language="javascript"></script>
    <script language="javascript">
    function add() {
        set_high_light('');
        parent.load_src(1, 'sys_menu_edit.php', 1000, 600);
        return false;
    }

    function edit(id, obj) {
        set_high_light(obj);
        parent.load_src(1, 'sys_menu_edit.php?id=' + id, 1000, 600);
        return false;
    }

    function delete_line(id, crc) {
        if (confirm("ɾ�����ָܻ���ȷ��Ҫɾ������������")) {
            load_js("?op=delete&id=" + id + "&crc=" + crc, "delete_line");
        }
    }
    </script>
</head>

<body>
    <!-- ͷ�� begin -->
    <table class="headers" width="100%">
        <tr>
            <td class="headers_title">
                <nobr class="tips">�˵�ģ���б�</nobr>
            </td>
            <td class="header_center">
                <?php if (check_power("i", $pinfo, $pagepower)) { ?>
                <button onclick="add()" class="button">���</button>
                <?php } ?>
            </td>
            <td class="headers_oprate">
                <form name="topform" method="GET">
                    <nobr>�ؼ��ʣ�<input name="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input"
                            size="15">&nbsp;<input type="submit" class="search" value="����" style="font-weight:bold"
                            title="�������">&nbsp;<a href="?">�˳�����</a></nobr>
                </form>
            </td>
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
                <td class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>"><?php echo $tdtitle; ?>
                </td>
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
						$op[] = '<a href="javascript:;" onclick="edit(' . $id . ', this);">�޸�</a>';
					}
					if (check_power("d", $pinfo, $pagepower)) {
						$op[] = '<a href="javascript:;" onclick="delete_line(' . $id . ',' . $line["addtime"] . ')">ɾ��</a>';
					}
					$op_button = implode("&nbsp;", $op);

					$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;
			?>
            <tr<?php echo $hide_line ? " class='hide'" : ""; ?>>
                <td align="center" class="item"><?php echo $line["id"]; ?></td>
                <td align="center" class="item"><?php echo $line["sort"]; ?></td>
                <td align="center" class="item"><?php echo $line["mid"]; ?></td>
                <td align="center" class="item"><?php echo $line["type"] > 0 ? "<b>��</b>" : ""; ?></td>
                <td align="left" class="item"><?php echo $line["title"]; ?></td>
                <td align="left" class="item"><?php echo $line["link"]; ?></td>
                <td align="left" class="item"><?php echo $line["tips"]; ?></td>
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
    <div style="padding:10px;">
        <center>(�� <?php echo count($data); ?> ������)</center>
    </div>
    <!-- ��ҳ���� end -->

</body>

</html>