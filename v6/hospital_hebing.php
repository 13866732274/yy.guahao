<?php
// --------------------------------------------------------
// - ����˵�� : �������ݺϲ�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-4-1
// --------------------------------------------------------
require "lib/set_env.php";
$table = "hospital";

if (!$debug_mode) {
	//exit("�����ǿ�����Ա������ʹ�ô˹��ܡ�");
}


$group_id_name = $db->query("select id,name from hospital_group order by sort desc, name asc", "id", "name");
$options = array();
foreach ($group_id_name as $_gid => $_gname) {
	$h_list = $db->query("select id,name,color from hospital where ishide=0 and group_id=$_gid order by sort desc, name asc", "id");
	if (count($h_list) > 0) {
		$options[] = array('-1', $_gname." (".count($h_list).')', 'color:red' );
		foreach ($h_list as $_hid => $_arr) {
			$options[] = array($_hid, '��'.$_arr["name"], ($_arr["color"] ? ('color:'.$_arr["color"]) : 'color:blue') );
		}
	}
}


if ($_GET["op"] == "submit") {
	$tips = array();

	$from_hid = intval($_GET["wee_from_hid"]);
	$to_hid = intval($_GET["wee_to_hid"]);

	if ($from_hid <= 0 || $to_hid <= 0) {
		exit("�Բ��𣬿���ID����");
	}

	// ���ʽ��function.create_table.php �ڵĸ�ʽΪ׼
	include_once "lib/function.create_table.php";
	$stru_arr = _parse_fields($db_tables["patient"]);
	$field_arr = array_keys($stru_arr);

	unset($field_arr[0]); //id�ֶβ�Ҫ

	$f_str = implode(",", $field_arr);

	// ��ѯĿ������������:
	$todo_count = $db->query("select count(*) as c from patient_{$from_hid}", 1, "c");
	if ($todo_count > 0) {
		$db->query("insert into patient_{$to_hid} ($f_str) (select $f_str from patient_{$from_hid})");
		$real_do_num = mysql_affected_rows();
		if ($real_do_num == 0) {
			echo "<pre>";
			print_r($field_arr);
			exit("����δ�ܳɹ����������ͱ�ṹ��");
		} else if ($real_do_num == $todo_count) {
			$tips[] = "�ɹ�ת�� ".$real_do_num." �����ݵ�Ŀ���";
		} else {
			$tips[] = "Ԥ��ת��=".$todo_count." ʵ��ת��=".$real_do_num." ��������δ�ɹ�����ȷ��";
		}
	} else {
		$tips[] = "Ŀ�����û�����ݣ�δ����������";
	}

	$db->query("update disease set hospital_id=$to_hid where hospital_id=$from_hid");
	$tips[] = "������ת��Ŀ�����";

	$db->query("update hospital set ishide=1 where id=$from_hid limit 1");
	$tips[] = "����ID=".$from_hid." �ѱ�����";

	$tips[] = "";
	$tips[] = "�����ؿ��ң���ǵø�����ԱȨ��";

	echo '<title>�������</title>';
	echo implode("<br>", $tips);
	exit;
}


// ��һ�������������н���ֶ�
function _parse_fields($s) {
	$list = explode("\n", $s);
	$out = array();
	foreach ($list as $k) {
		$k = trim($k);
		if (substr($k, 0, 1) == "`") {
			$fname = ltrim($k, "`");
			list($sa, $sb) = explode(" ", $fname, 2);
			$sa = rtrim($sa, "`");
			$out[$sa] = rtrim(trim($k), ',');
		}
	}

	return $out;
}



?>
<html>
<head>
<title>�������ݺϲ�</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.edit, .edit td {border:0px solid #c0c0c0 !important; background:white !important; }
</style>
<script language="javascript">
</script>
</head>

<body>
<form name="mainform" action="" method="GET" onsubmit="return confirm('�ò������ɳ���������ؼ����ϸ���ύ���Ƿ�ȷ������ִ�У�');">
<table width="100%" class="edit" style="margin-top:30px;">
	<tr>
		<td class="left" style="width:35%"><font color="red">*</font> Ҫ�ϲ��Ŀ��ң�</td>
		<td class="right">
<?php
		echo '	<select name="wee_from_hid" id="hospital_id" class="combo">';
		echo '		<option value="0" style="color:gray">-��ѡ��-</option>';
		foreach ($options as $v) {
			echo '		<option value="'.$v[0].'"'.($v[2] ? ' style="'.$v[2].'"' : '').'>'.$v[1].'</option>';
		}
		echo '	</select>&nbsp;&nbsp;';
?>
			 <span class="intro">(�ϲ��󣬸ÿ��ҽ�������)</span>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> �ϲ����˿����У�</td>
		<td class="right">
<?php
		echo '	<select name="wee_to_hid" id="hospital_id" class="combo">';
		echo '		<option value="0" style="color:gray">-��ѡ��-</option>';
		foreach ($options as $v) {
			echo '		<option value="'.$v[0].'"'.($v[2] ? ' style="'.$v[2].'"' : '').'>'.$v[1].'</option>';
		}
		echo '	</select>&nbsp;&nbsp;';
?>
			 <span class="intro">(���ݽ����ϲ�������)</span>
		</td>
	</tr>

</table>

<input type="hidden" name="op" value="submit" />

<div class="button_line" style="margin-top:30px;">
	<input type="submit" class="submit" value="ȷ�ϴ���">
</div>

</form>

</body>
</html>