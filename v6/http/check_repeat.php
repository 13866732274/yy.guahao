<?php
// --------------------------------------------------------
// - ����˵�� : ��������ظ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-06-04 13:01
// --------------------------------------------------------
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$table = "patient_" . $hid;

$out           = array();
$out["status"] = "bad";
$out["tips"]   = '';

$user_part_name = $db->query("select id,name from sys_part", "id", "name");

$_hinfo   = $db->query("select * from hospital where id=$hid limit 1", 1);
$tip_days = $_hinfo["repeat_tip_time"] > 0 ? $_hinfo["repeat_tip_time"] : $cfgRepeatTipDays;
$tip_sec  = $tip_days * 24 * 3600; //����
$thetime  = time() - $tip_sec; //����ظ���ʱ���޶�


// ��������ͬ��ҽԺ:
$sname          = trim($_hinfo["sname"]);
$same_h_id_name = $db->query("select id, name from hospital where sname='$sname' and ishide=0 and id!=$hid order by name asc", "id", "name");


$type  = $_GET["type"];
$value = trim($_GET["value"]);
if (in_array($type, array("name", "tel")) && $value != '') {
	$tipdata = array();

	// ԤԼϵͳ�ظ���ѯ��
	$data = $db->query("select * from $table where binary $type='$value' and addtime>$thetime order by id desc limit 3");

	// �Ƿ������������ظ�:
	//if ($type == "tel") {
	if (count($same_h_id_name) > 0) {
		foreach ($same_h_id_name as $_hid => $_hname) {
			$tmp = $db->query("select * from patient_{$_hid} where binary $type='$value' and addtime>$thetime order by id desc limit 3");
			if (count($tmp) > 0) {
				foreach ($tmp as $li) {
					$data[] = $li;
					}
				}
			}
		}
	//}

	if (count($data) > 0) {
		$out["tips"] .= "��ע�⣬�������ظ�������Ҫ��ӵ�����ϵͳ�Ѵ��ڣ����ѷ�Χ" . $tip_days . "�죩  ##";
		foreach ($data as $line) {
			$tipstr = '';
			if ($line["author"] != $realname) {
				if ($line["tel"] != "") {
					//$line["tel"] = substr($line["tel"], 0, -4)."****";
					$line["tel"] = "*";
					}
				}
			if ($line["status"] == 1) {
				$doctor = $line["xianchang_doctor"] ? $line["xianchang_doctor"] : $line["doctor"];
				$come_s = "���ѵ�Ժ" . ($doctor ? ("��" . $doctor . " ����") : "") . "��";
				}
			else {
				$come_s = "��δ��Ժ��";
				}
			$tipstr .= "����������" . $line["name"] . "�����Ա�" . $line["sex"] . "�������䣺" . $line["age"] . "�����绰��" . $line["tel"] . "����" . $come_s . "#";
			$dis_name = $db->query("select name from disease where id=" . $line["disease_id"] . " limit 1", 1, "name");
			$tipstr .= "�������ͣ�" . $dis_name . "#";
			if (trim($line["content"]) != '') {
				$line["content"] = str_replace("\r", "", $line["content"]);
				$line["content"] = str_replace("\n", " ", $line["content"]);
				$line["content"] = str_replace("<br>", " ", $line["content"]);
				$tipstr .= "��ѯ���ݣ�" . cut($line["content"], 100) . "#";
				}

			$tipstr .= "ԤԼʱ�䣺" . date("Y-m-d H:i", $line["order_date"]) . "#";
			$tipstr .= "���ʱ�䣺" . date("Y-m-d H:i", $line["addtime"]) . "������" . $line["author"] . " @ " . $user_part_name[$line["part_id"]] . "��#";

			if (trim($line["memo"]) != '') {
				//$line["memo"] = str_replace("\r", "", $line["memo"]);
				$line["memo"] = str_replace("<br>", "\n", $line["memo"]);
				$tipstr .= "��ע��" . cut($line["memo"], 2000) . "#";
				}
			$tipdata[] = $tipstr . "#";
			}
		}

	// ���Ͽ��ظ�����:
	if ($type == "tel") {
		$data = $db->query("select * from ku_list where hid=$hid and mobile='$value' order by id desc limit 2");
		if (count($data) > 0) {
			$out["tips"] .= "#���Ͽ��ظ����� ���������ܡ�  ##";
			foreach ($data as $line) {
				$tipstr = '';
				$tipstr .= "����������" . $line["name"] . "�����Ա�" . $line["sex"] . "�������䣺" . $line["age"] . "#";
				if (trim($line["zx_content"]) != '') {
					$line["zx_content"] = str_replace("\r", "", $line["zx_content"]);
					$line["zx_content"] = str_replace("\n", " ", $line["zx_content"]);
					$line["zx_content"] = str_replace("<br>", " ", $line["zx_content"]);
					$tipstr .= "��ѯ���ݣ�" . cut(ltrim($line["zx_content"]), 100) . "#";
					}

				$tipstr .= "���ʱ�䣺" . date("Y-m-d H:i", $line["addtime"]) . "������" . $line["u_name"] . " @ " . $user_part_name[$line["part_id"]] . "��#";

				if (trim($line["hf_log"]) != '') {
					$line["hf_log"] = str_replace("<br>", "\n", $line["hf_log"]);
					$tipstr .= "�طü�¼��" . cut($line["hf_log"], 500) . "#";
					}
				$tipdata[] = $tipstr . "#";
				}
			}
		}


	if (count($tipdata) > 0) {
		$out["tips"] .= implode('', $tipdata);
		$out["tips"] .= "�����鿼���Ƿ������ӣ�";
		$out["tips"] = str_replace("#", "\n", trim($out["tips"], "#"));
		}

	$out["status"] = "ok";
	$out["type"]   = $type;
	$out["value"]  = $value;
	}

echo FastJSON::convert($out);
?>