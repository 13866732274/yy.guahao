<?php
/*
// ˵��: op��Ӧ
// ����: ���� (weelia@126.com)
// ʱ��: 2016-10-21
*/

if (!$db) exit;

if ($op == "set_add_haoyou") {
	header('Content-type: text/javascript');
	if ($line["wx_is_add"] == 0 && ($line["wx_uid"] == 0 || $line["wx_uid"] == $uid)) {
		$db->query("update $table set wx_is_add=1 where id=$id limit 1");
		if ($line["wx_uid"] == 0) {
			$db->query("update $table set wx_uid=$uid, wx_uname='$realname' where id=$id limit 1");
		}
		echo 'set_add_haoyou_do('.$id.');';
	} else {
		echo 'alert("�Բ��𣬸û��߿����ѱ��������ȼ�Ϊ�����ˣ���ˢ��ҳ������ԡ�");';
	}
	exit;
}

if ($op == "delete") {
	header('Content-type: text/javascript');
	if ($id > 0) {
		del_data($db, $table, $id, 1, "ɾ����{name}��");
		$db->query("delete from ku_remind where ku_id=$id limit 1");
	}
	echo "parent.msg_box('ɾ���ɹ�'); self.location.reload(); ";
	exit;
}

if ($op == "set_yuyue") {
	$line = $db->query("select * from $table where id=$id limit 1", 1);
	if (empty($line)) exit("��������:ID");
	include "ku_select_hs.php";
	exit;
}

if ($op == "set_yuyue2") {

	$line = $db->query("select * from $table where id=$id limit 1", 1);
	if (!$line || $line["id"] != $id) {
		exit("����id��Ӧ�����ϲ�����...");
	}

	$_SESSION[$cfgSessionName]["hospital_id"] = $_GET["to_hid"];

	$r = array();
	$r["part_id"] = $uinfo["part_id"];
	$r["name"] = $line["name"];
	$r["sex"] = $line["sex"];
	$r["age"] = $line["age"];
	$r["tel"] = $line["mobile"];
	$r["media_from"] = '����';

	if (strlen($line["zx_content"]) > 200) {
		$line["zx_content"] = cut($line["zx_content"], 200, "��");
	}
	$content = $line["zx_content"];
	if ($line["order_qq"] != '') {
		$content .= " ԤԼ��QQ:".$line["order_qq"];
	}
	if ($line["order_weixin"] != '') {
		$content .= " ԤԼ��΢��:".$line["order_weixin"];
	}

	$memo = "�����Ͽ�ת��".($realname != $line["u_name"] ? " (ԭ��ѯԱ:".$line["u_name"].")" : "");

	$r["weixin"] = $line["weixin"];
	$r["qq"] = $line["qq"];
	$r["swt_id"] = $line["swt_id"];
	$r["disease_id"] = $line["disease_id"];

	$r["content"] = $content;
	$r["memo"] = $memo;
	$r["from"] = "ku";
	$r["ku_id"] = $id;

	$_SESSION["ku_talk_content"] = $line["talk_content"];

	foreach ($r as $k => $v) {
		$r[$k] = $k."=".urlencode($v);
	}

	$link = "patient_add.php?op=add&".implode("&", $r);
	header("location:".$link);

	exit;
}

?>