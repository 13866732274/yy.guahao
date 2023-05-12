<?php
/*
// ����: ���� (weelia@126.com)
*/
require "lib/set_env.php";
if (!$debug_mode) {
	exit("��ִ��Ȩ��...");
}
set_time_limit(0);

$op = $_REQUEST["op"];

// ��ʾphpinfo
if ($op == "phpinfo") {
	phpinfo();
	exit;
}

// �ֳ�ҽ������
if ($op == "rename_xianchang_doctor") {
	$from_real_name = trim($_POST["from_real_name"]);
	$to_login_name = trim($_POST["to_login_name"]);
	$to_real_name = trim($_POST["to_real_name"]);

	$db->query("update sys_admin set name='$to_login_name', realname='$to_real_name' where realname='$from_real_name' limit 1");
	echo $db->sql." ��ִ�У���Ӱ��������".mysql_affected_rows()."<br><br>";

	if ($hid > 0) {
		$db->query("update patient_{$hid} set xianchang_doctor='$to_real_name' where xianchang_doctor='$from_real_name'");
		echo $db->sql." ��ִ�У���Ӱ��������".mysql_affected_rows()."<br><br>";
	}

	echo "�ֳ�ҽ������ִ�н���";
	exit;
}


// ����ҽ������
if ($op == "rename_doctor") {
	$from_real_name = trim($_POST["from_real_name"]);
	$to_login_name = trim($_POST["to_login_name"]);
	$to_real_name = trim($_POST["to_real_name"]);

	$db->query("update sys_admin set name='$to_login_name', realname='$to_real_name' where realname='$from_real_name' limit 1");
	echo $db->sql." ��ִ�У���Ӱ��������".mysql_affected_rows()."<br><br>";

	if ($hid > 0) {
		$db->query("update doctor set name='$to_real_name' where name='$from_real_name' and hospital_id='$hid' limit 1");
		echo $db->sql." ��ִ�У���Ӱ��������".mysql_affected_rows()."<br><br>";

		$db->query("update patient_{$hid} set doctor='$to_real_name' where doctor='$from_real_name'");
		echo $db->sql." ��ִ�У���Ӱ��������".mysql_affected_rows()."<br><br>";
	}

	echo "����ҽ������ִ�н���";
	exit;
}



// �����ֶ�
if ($op == "update_field") {
	include_once "lib/function.create_table.php";

	// ��ȡ����µ�ÿ��ҽԺ:
	$hids = $db->query("select id from hospital", "", "id");

	echo "���ڴ������Ժ�...".str_repeat("&nbsp;", 50)."<br>";
	flush();
	ob_flush();
	ob_end_flush();

	// Ҫ����ı�:
	$table_names = array();
	$table_names[] = "patient";
	foreach ($hids as $hid) {
		$table_names[] = "patient_".$hid;
	}

	// ����:
	foreach ($table_names as $table_name) {
		$cs = $db_tables["patient"];

		if (!table_exists($table_name, $db->dblink)) {
			//$db->query(str_replace("{hid}", $hid, $cs));
			//echo "������ ".$table_name." <br>";
		} else {
			$fields = parse_fields($cs);
			foreach ($fields as $f => $fs) {
				if (!field_exists($f, $table_name, $db->dblink)) {
					$tm = array_keys($fields);
					$tm2 = array_search($f, $tm);
					if ($tm2 == 0) {
						$pos = " first";
					} else {
						$pos = " after `".$tm[$tm2-1]."`";
					}
					$sql = "alter table `".$table_name."` add ".$fs.$pos;
					$db->query($sql);
					echo "�� ".$table_name." ����ֶ� ".$f."<br>";
				}
			}
		}

		flush();
		ob_flush();
		ob_end_flush();
		sleep(1);
	}

	echo "<br>ȫ����ɡ�";
	exit;
}

// ���������ṹ
if ($op == "update_index") {
	// ����ҽԺ����:
	$h_id_name = $db->query("select id,name from hospital order by id asc", "id", "name");

	// �������:
	foreach ($h_id_name as $_hid => $_hname) {

		$tindex = mysql_query("show index from patient_{$_hid}", $db->dblink);

		$indexs = array();
		while ($li = mysql_fetch_array($tindex)) {
			$indexs[] = $li["Column_name"];
		}

		echo $_hid.": ".$_hname." ";

		if (!in_array("quick", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX quick(`part_id`,`disease_id`,`order_date`,`addtime`,`uid`,`status`,`id`)");
			echo "�������(quick) ";
		}
		if (!in_array("addtime", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`addtime`)");
			echo "�������(addtime) ";
		}
		if (!in_array("order_date", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`order_date`)");
			echo "�������(order_date) ";
		}
		if (!in_array("part_id", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`part_id`)");
			echo "�������(part_id) ";
		}
		if (!in_array("uid", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`uid`)");
			echo "�������(uid) ";
		}
		if (!in_array("status", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`status`)");
			echo "�������(status) ";
		}
		if (!in_array("tel", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`tel`)");
			echo "�������(tel) ";
		}
		if (!in_array("name", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`name`)");
			echo "�������(name) ";
		}

		echo " ������ϡ�<br>";
		flush();
		ob_flush();
		ob_end_flush();
	}

	echo "<br><br>ȫ����ɡ�";
	exit;
}


if ($op == "check_hospital") {

	$h_id_name = $db->query("select id,name from hospital order by sort desc,id asc", "id", "name");
	$keep_ids = array_keys($h_id_name);

	$users = $db->query("select id,realname,hospitals from sys_admin", "id");
	foreach ($users as $id => $v) {
		$ids = $v["hospitals"];
		if ($ids != '') {
			$ids_arr = explode(",", trim(trim($ids), ","));
			if (count($ids_arr) > 0) {
				foreach ($ids_arr as $x => $y) {
					if (!in_array($y, $keep_ids)) {
						unset($ids_arr[$x]);
					}
				}

				$new = implode(",", $ids_arr);

				if (trim($ids) != $new) {
					$db->query("update sys_admin set hospitals='$new' where id=$id limit 1");
					echo $v["realname"]." ҽԺ�䶯: ".$ids." => ".$new."<br>";
				}
			}
		}
	}

	echo "ִ�����..<br>";
	exit;
}


if ($op == "move_old_data") {
	$the_date = strtotime("-1 years");

	$tlist = mysql_query("show tables");
	$tables = array();
	while ($li = mysql_fetch_array($tlist)) {
		$tables[] = $li[0];
	}

	$hid_name_arr = $db->query("select id,name from hospital order by id asc", "id", "name");
	foreach ($hid_name_arr as $_hid => $_hname) {
		$table = "patient_{$_hid}";
		$backup_table = "patient_{$_hid}_history";

		// �Ƿ���Ҫ������ʷ��¼��ṹ:
		if (!in_array($backup_table, $tables)) {
			$sql = "create table $backup_table (select * from $table limit 1)";
			$db->query($sql);
			$sql = "truncate table $backup_table";
			$db->query($sql);
		}

		// ת�����ݣ�
		$sql = "insert into $backup_table (select * from $table where order_date<".$the_date.")";
		$db->query($sql);
		$num = mysql_affected_rows();
		if ($num > 0) {
			$sql = "delete from $table where order_date<".$the_date;
			$db->query($sql);
		}

		echo $_hid.":".$_hname." ������ϣ���ת����".intval($num)."������<br>";
		flush();
		ob_flush();
		ob_end_flush();
		sleep(1);
	}

	echo "<br>ȫ��������ɡ�";
	exit;
}


// ��һ�������������н���ֶ�
function parse_fields($s) {
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


$index = 1;
?>
<html>
<head>
<title>���Թ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.head {padding:5px 10px 3px 10px !important;}
.item {padding:20px 10px 18px 10px !important; }
a {font-weight:bold; color:#FF8040; font-size:14px; }
</style>
<script type="text/javascript">
function confirm_op() {
	return confirm("�˲���ʮ��Σ�գ����޿�����Աʹ�á�\r\n\r\n����㲻�ǿ�����Ա����֪������������������ȡ����������������ܷǳ����ء���֮��\r\n\r\n�Ƿ�ȷ��������");
}

//alert(parent.ZHUWENYA_IFRAME);
</script>
</head>

<body>
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips">���Թ���(���޿�����Աʹ��)</nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">ˢ��</button></td>
	</tr>
</table>

<div class="space"></div>

<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center" width="32">���</td>
		<td class="head" align="left" width="100">����</td>
		<td class="head" align="left" width="">���</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=phpinfo" onclick="return confirm_op()">�鿴phpinfo()</a></td>
		<td align="left" class="item">�鿴������phpinfo���Դ���ϳ��ֺ������⡣</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=update_field" onclick="return confirm_op()">����patient��ṹ</a></td>
		<td align="left" class="item">�� patient �����ṹ���ʱ��ʹ�ô˹��߽����Ӧ�õ�ϵͳ���� patient ���С�</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=update_index" onclick="return confirm_op()">Ϊpatient��������</a></td>
		<td align="left" class="item">��� addtime order_date part_id author tel name ��������ѯ�ٶȸ��졣</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=check_hospital" onclick="return confirm_op()">�������û�����ҽԺ</a></td>
		<td align="left" class="item">�������û�����ҽԺ�����ĳҽԺ��ɾ��������������ҽԺ����ȥ��</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=move_old_data" onclick="return confirm_op()">��һ��ǰ�ľ������Ƶ���ʷ����</a></td>
		<td align="left" class="item">��һ��ǰ�ľ������Ƶ���ʷ���У���ԤԼʱ���㡣</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="patient_export.php" onclick="return confirm_op()">������ǰҽԺ������</a></td>
		<td align="left" class="item">
			����ǰ���л���ҽԺ�����ݵ���Ϊ�ı���ʽ��ͨ��ת��������ת��excel��ʽ��<br>
			<b>����ݵ�����</b><form action="patient_export.php" method="GET" style="display:inline;">
				<input name="year" value="<?php echo date("Y"); ?>" class="input">
				<input type="submit" class="button" value="����">
			</form>
		</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="#">�ֳ�ҽ������</a></td>
		<td align="left" class="item">
			��ĳ���ֳ�ҽ����¼������ʵ�����޸ĵ����Ὣ��ǰҽԺ��xiangchang_doctor�ֶ�Ҳ�ı䡣<br>
			<form action="?" method="POST" style="display:inline;" onsubmit="return confirm_op();">
				Ҫ�޸���Ա��ǰ��ʵ������<input name="from_real_name" size="12" class="input">&nbsp;&nbsp;
				��¼����Ϊ��<input name="to_login_name" size="12" class="input">&nbsp;&nbsp;
				��ʵ������Ϊ��<input name="to_real_name" size="12" class="input">&nbsp;&nbsp;
				<input type="submit" class="button" value="ȷ��">
				<input type="hidden" name="op" value="rename_xianchang_doctor">
			</form>
		</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="#">����ҽ������</a></td>
		<td align="left" class="item">
			��ĳ������ҽ����¼������ʵ�����޸ĵ����Ὣ��ǰҽԺ��doctor�ֶ�Ҳ�ı䡣<br>
			<form action="?" method="POST" style="display:inline;" onsubmit="return confirm_op();">
				Ҫ�޸���Ա��ǰ��ʵ������<input name="from_real_name" size="12" class="input">&nbsp;&nbsp;
				��¼����Ϊ��<input name="to_login_name" size="12" class="input">&nbsp;&nbsp;
				��ʵ������Ϊ��<input name="to_real_name" size="12" class="input">&nbsp;&nbsp;
				<input type="submit" class="button" value="ȷ��">
				<input type="hidden" name="op" value="rename_doctor">
			</form>
		</td>
	</tr>
</table>


</body>
</html>