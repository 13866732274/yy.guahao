<?php
// --------------------------------------------------------
// - ����˵�� : ҽԺ�б�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-01 00:36
// --------------------------------------------------------
require "lib/set_env.php";
$table = "hospital";

check_power('', $pinfo) or exit("û�д�Ȩ��...");

// �����Ĵ���:
if ($op = $_GET["op"]) {
	if ($op == "delete") {
		$id = intval($_GET["id"]);
		$crc = intval($_GET["crc"]);
		if ($id > 0 && $crc > 0) {
			$line = $db->query("select * from $table where id=$id limit 1", 1);
			if ($line["addtime"] == $crc) {
				$db->query("delete from $table where id=$id limit 1");

				$log_str = "ɾ������[".$line["name"]."] ID=[".$id."]";
				$db->query("insert into sys_op_log set content='$log_str', uid='$uid', author='$realname', addtime='$time'");

				echo 'alert("���ҡ�'.$line["name"].'�� �ѱ�ɾ������ע�������ԱȨ�ޡ�");';
				echo 'self.location.reload();';
			} else {
				echo 'alert("ɾ��ʧ�ܣ�crcУ�����");';
			}
		} else {
			echo 'alert("��������...");';
		}
		exit();
	}

	if ($op == "hname_to_table_comment") {
		$h_id_arr = $db->query("select id, name, ishide from $table", "id");
		foreach ($h_id_arr as $_hid => $_hinfo) {
			$t_name = "patient_".$_hid;
			$_hname = ($_hinfo["ishide"] ? "�ѹأ�" : "").$_hinfo["name"];
			$db->query("ALTER TABLE `{$t_name}` COMMENT='{$_hname}'");
		}
		echo '<script>alert("��ע���³ɹ���"); self.location = "?"; </script>';
		exit;
	}
}


// ��ѯ����:
$where = array();
$key = $_GET["key"];
if ($key != "") {
	$where[] = "(name like '%{$key}%')";
}
if ($_GET["show_type"] == "hide") {
	$where[] = "ishide!=0";
} else {
	$where[] = "ishide=0";
}

$sqlwhere = count($where) > 0 ? ("where ".implode(" and ", $where)) : "";

// ��ѯ:
$data = $db->query("select * from $table $sqlwhere order by group_name asc,name asc");

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
	parent.load_src(1,'hospital_edit.php', 900, 550);
	return false;
}

function edit(id, obj) {
	set_high_light(obj);
	parent.load_src(1,'hospital_edit.php?id='+id, 900, 550);
	return false;
}

function delete_line(id, crc) {
	if (!confirm("Ҫɾ���Ŀ���IDΪ��"+id+"��������ϸȷ�ϣ���ֹ��ɾ����Ӧ�����ݱ���ɾ�������Ƿ�ȷ��ɾ����")) {
		return false;
	}
	load_js("hospital.php?op=delete&id="+id+"&crc="+crc, "hospital_delete");
}
</script>
<style type="text/css">
.tips_2 {font-size:14px; }
.m {margin-left:10px; }
</style>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td style="width:200px">
			<nobr class="tips_2">ҽԺ�б�</nobr>
		</td>
		<td align="center">
<?php if (check_power("i", $pinfo, $pagepower)) { ?>
			<button onclick="add()" class="button">���</button>
<?php } ?>
<?php if ($debug_mode || substr_count($sys_super_admin, $username) > 0) { ?>
			<a href="hospital_tools.php" class="m">������ԱȨ��</a>
			<button onclick="hebing()" class="buttonb" style="margin-left:10px">���Һϲ�</button>
			<script type="text/javascript">
			function hebing() {
				parent.load_src(1, 'hospital_hebing.php', 800, 500);
			}
			</script>
<?php } ?>

<?php if ($_GET["show_type"] != "hide") { ?>
			<a href="?show_type=hide" class="m red">�鿴�����ؿ���</a>
<?php } else { ?>
			<a href="?" class="m">�鿴δ���ؿ���</a>
<?php } ?>

		</td>
		<td align="right" style="width:320px;">
			<form name="topform" method="GET">
				<nobr>
				�ؼ��ʣ�<input name="key" value="<?php echo $_GET["key"]; ?>" class="input" size="15">&nbsp;
				<input type="submit" class="search" value="����" style="font-weight:bold" title="�������">&nbsp;
				<a href="?">����</a>
				</nobr>
				<input type="hidden" name="show_type" value="<?php echo $_GET["show_type"]; ?>">
			</form>
		</td>
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
		<td class="head column_sortable" title="���������" align="left">����ҽԺ</td>
		<td class="head column_sortable" title="���������" align="left">���ڵ���</td>
		<td class="head column_sortable" title="���������" align="left" >���</td>
		<td class="head column_sortable" title="���������" align="left" >��������</td>
		<td class="head column_sortable" title="���������" align="left">���ʱ��</td>
		<td class="head column_sortable" title="���������" align="left">���ȶ�</td>
		<td class="head sorttable_nosort" align="center" width="80">����</td>
	</tr>

	<!-- ��Ҫ�б����� begin -->
<?php
if (count($data) == 0) {
	echo '<tr><td colspan="9" align="center" class="nodata">(û������...)</td></tr>';
}
foreach ($data as $line) {
	$id = $line["id"];

	$op = array();
	if (check_power("e", $pinfo, $pagepower)) {
		$op[] = "<button class='button_op' onclick='edit(".$id.", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='�޸�' alt=''></button>";
	}
	if ($line["ishide"] > 0 && check_power("d", $pinfo, $pagepower)) {
		$op[] = '<a href="javascript:;" onclick="delete_line('.$id.', '.$line["addtime"].');" style="color:red">ɾ��</button>';
	}
	$op_button = implode("&nbsp; ", $op);

	$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;

	if ($line["color"] != '') {
		$line["name"] = '<font color="'.$line["color"].'">'.$line["name"].'<font>';
	}
?>
	<tr<?php echo $hide_line ? " class='hide'" : ""; ?> onmouseover="mi(this)" onmouseout="mo(this)">
		<td align="center" class="item"><?php echo $line["id"]; ?></td>
		<td align="left" class="item"><?php echo ($line["ishide"] > 0 ? '<font color="red">���� �� </font>' : '').$line["name"]; ?></td>
		<td align="left" class="item"><?php echo $line["sname"]; ?></td>
		<td align="left" class="item"><?php echo $line["area"]; ?></td>
		<td align="left" class="item"><?php echo $line["short_name"]; ?></td>
		<td align="left" class="item"><?php echo $line["group_name"]; ?></td>
		<td align="left" class="item"><?php echo date("Y-m-d H:i", $line["addtime"]); ?></td>
		<td align="left" class="item"><?php echo $line["sort"]; ?></td>
		<td align="center" class="item"><?php echo $op_button; ?></td>
	</tr>
<?php } ?>
	<!-- ��Ҫ�б����� end -->
</table>
</form>
<!-- �����б� end -->

<div class="space"></div>

<!-- ��ҳ���� begin -->
<div class="footer_op">
	<div class="footer_op_left">����(��ʾ��������Ϊ���غ󣬲���ɾ������)</div>
	<div class="footer_op_right">���� <b><?php echo count($data); ?></b> ������&nbsp;</div>
	<div class="clear"></div>
</div>
<!-- ��ҳ���� end -->

<br>
<br>

<?php if ($key != "") { ?>
<!-- �ؼ��ʸ��� -->
<script>
highlightWord(document.body, "<?php echo $key; ?>");
</script>
<?php } ?>

</body>
</html>