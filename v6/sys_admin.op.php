<?php
/*
// 说明: sys_admin.op.php
// 作者: 幽兰 (weelia@126.com)
// 时间: 2010-10-16 13:39
*/
if (!defined("ROOT")) exit("Error.");

if ($op == "insert") {
	check_power("i", $pinfo, $pagepower) or msg_box("没有新增权限...", "back", 1);
	header("location:".$pinfo["insertpage"]);
	exit;
}

if ($op == "delete") {
	if (!$super_edit) {
		exit("没有删除权限");
	}

	$ids = explode(",", $_GET["uids"]);
	$op_names = array();
	foreach ($ids as $opid) {
		$opid = intval($opid);
		if ($opid > 0) {
			$line = $db->query("select * from $table where id='$opid' limit 1", 1);
			if ($line["id"] > 0 && $line["id"] == $opid) {
				del_data($db, $table, $opid, 1, "删除人员“{realname}”");
				$op_names[] = $line["realname"];
			}
		}
	}

	$tips = "参数无效";
	if (count($op_names) > 0) {
		if (count($op_names) > 5) {
			$tips = "[".implode("、", array_slice($op_names, 0, 5))."]等".count($op_names)."人已被删除";
		} else {
			$tips = "[".implode("、", $op_names)."]已被删除";
		}
	}

	header('Content-type: text/javascript');
	echo 'parent.msg_box("'.$tips.'"); self.location.reload(); ';
	exit;
}

if ($op == "open" || $op == "close") {
	check_power("h", $pinfo, $pagepower) or msg_box("没有开通和关闭权限...", "back", 1);

	$isshow_value = ($op == "open" ? 1 : 0);
	$op_name = ($op == "open" ? "开启" : "禁用");
	$ids = explode(",", $_GET["uids"]);
	$op_names = array();
	foreach ($ids as $opid) {
		$opid = intval($opid);
		if ($opid > 0) {
			$line = $db->query("select * from $table where id='$opid' limit 1", 1);
			$db->query("update $table set isshow='$isshow_value' where id='$opid' limit 1");
			user_op_log($op_name."账号“".$line["realname"]."”");
			$op_names[] = $line["realname"];
		}
	}


	$tips = "参数无效";
	if (count($op_names) > 0) {
		if (count($op_names) > 5) {
			$tips = "[".implode("、", array_slice($op_names, 0, 5))."]等".count($op_names)."人已被".$op_name;
		} else {
			$tips = "[".implode("、", $op_names)."]已被".$op_name;
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