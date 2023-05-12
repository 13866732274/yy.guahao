<?php
/*
// ˵��: sys_admin.op.php
// ����: ���� (weelia@126.com)
// ʱ��: 2010-10-16 13:39
*/
if (!defined("ROOT")) exit("Error.");

if ($op == "insert") {
	check_power("i", $pinfo, $pagepower) or msg_box("û������Ȩ��...", "back", 1);
	header("location:".$pinfo["insertpage"]);
	exit;
}

if ($op == "delete") {
	if (!$super_edit) {
		exit("û��ɾ��Ȩ��");
	}

	$ids = explode(",", $_GET["uids"]);
	$op_names = array();
	foreach ($ids as $opid) {
		$opid = intval($opid);
		if ($opid > 0) {
			$line = $db->query("select * from $table where id='$opid' limit 1", 1);
			if ($line["id"] > 0 && $line["id"] == $opid) {
				del_data($db, $table, $opid, 1, "ɾ����Ա��{realname}��");
				$op_names[] = $line["realname"];
			}
		}
	}

	$tips = "������Ч";
	if (count($op_names) > 0) {
		if (count($op_names) > 5) {
			$tips = "[".implode("��", array_slice($op_names, 0, 5))."]��".count($op_names)."���ѱ�ɾ��";
		} else {
			$tips = "[".implode("��", $op_names)."]�ѱ�ɾ��";
		}
	}

	header('Content-type: text/javascript');
	echo 'parent.msg_box("'.$tips.'"); self.location.reload(); ';
	exit;
}

if ($op == "open" || $op == "close") {
	check_power("h", $pinfo, $pagepower) or msg_box("û�п�ͨ�͹ر�Ȩ��...", "back", 1);

	$isshow_value = ($op == "open" ? 1 : 0);
	$op_name = ($op == "open" ? "����" : "����");
	$ids = explode(",", $_GET["uids"]);
	$op_names = array();
	foreach ($ids as $opid) {
		$opid = intval($opid);
		if ($opid > 0) {
			$line = $db->query("select * from $table where id='$opid' limit 1", 1);
			$db->query("update $table set isshow='$isshow_value' where id='$opid' limit 1");
			user_op_log($op_name."�˺š�".$line["realname"]."��");
			$op_names[] = $line["realname"];
		}
	}


	$tips = "������Ч";
	if (count($op_names) > 0) {
		if (count($op_names) > 5) {
			$tips = "[".implode("��", array_slice($op_names, 0, 5))."]��".count($op_names)."���ѱ�".$op_name;
		} else {
			$tips = "[".implode("��", $op_names)."]�ѱ�".$op_name;
		}
	}
	header('Content-type: text/javascript');
	echo 'parent.msg_box("'.$tips.'"); self.location.reload(); ';
	exit;
}

if ($op == "change_group_type") {
	$cur_group = $_SESSION["admin_group_type"] = intval($_GET["group"]);
}

?>