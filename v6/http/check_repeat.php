<?php
// --------------------------------------------------------
// - 功能说明 : 检查数据重复情况
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-06-04 13:01
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
$tip_sec  = $tip_days * 24 * 3600; //秒数
$thetime  = time() - $tip_sec; //检查重复的时间限定


// 搜索其他同名医院:
$sname          = trim($_hinfo["sname"]);
$same_h_id_name = $db->query("select id, name from hospital where sname='$sname' and ishide=0 and id!=$hid order by name asc", "id", "name");


$type  = $_GET["type"];
$value = trim($_GET["value"]);
if (in_array($type, array("name", "tel")) && $value != '') {
	$tipdata = array();

	// 预约系统重复查询：
	$data = $db->query("select * from $table where binary $type='$value' and addtime>$thetime order by id desc limit 3");

	// 是否其他科室有重复:
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
		$out["tips"] .= "请注意，资料有重复，您正要添加的资料系统已存在（提醒范围" . $tip_days . "天）  ##";
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
				$come_s = "（已到院" . ($doctor ? ("：" . $doctor . " 接诊") : "") . "）";
				}
			else {
				$come_s = "（未到院）";
				}
			$tipstr .= "患者姓名：" . $line["name"] . "　　性别：" . $line["sex"] . "　　年龄：" . $line["age"] . "　　电话：" . $line["tel"] . "　　" . $come_s . "#";
			$dis_name = $db->query("select name from disease where id=" . $line["disease_id"] . " limit 1", 1, "name");
			$tipstr .= "疾病类型：" . $dis_name . "#";
			if (trim($line["content"]) != '') {
				$line["content"] = str_replace("\r", "", $line["content"]);
				$line["content"] = str_replace("\n", " ", $line["content"]);
				$line["content"] = str_replace("<br>", " ", $line["content"]);
				$tipstr .= "咨询内容：" . cut($line["content"], 100) . "#";
				}

			$tipstr .= "预约时间：" . date("Y-m-d H:i", $line["order_date"]) . "#";
			$tipstr .= "添加时间：" . date("Y-m-d H:i", $line["addtime"]) . "　　（" . $line["author"] . " @ " . $user_part_name[$line["part_id"]] . "）#";

			if (trim($line["memo"]) != '') {
				//$line["memo"] = str_replace("\r", "", $line["memo"]);
				$line["memo"] = str_replace("<br>", "\n", $line["memo"]);
				$tipstr .= "备注：" . cut($line["memo"], 2000) . "#";
				}
			$tipdata[] = $tipstr . "#";
			}
		}

	// 资料库重复提醒:
	if ($type == "tel") {
		$data = $db->query("select * from ku_list where hid=$hid and mobile='$value' order by id desc limit 2");
		if (count($data) > 0) {
			$out["tips"] .= "#资料库重复提醒 【新增功能】  ##";
			foreach ($data as $line) {
				$tipstr = '';
				$tipstr .= "患者姓名：" . $line["name"] . "　　性别：" . $line["sex"] . "　　年龄：" . $line["age"] . "#";
				if (trim($line["zx_content"]) != '') {
					$line["zx_content"] = str_replace("\r", "", $line["zx_content"]);
					$line["zx_content"] = str_replace("\n", " ", $line["zx_content"]);
					$line["zx_content"] = str_replace("<br>", " ", $line["zx_content"]);
					$tipstr .= "咨询内容：" . cut(ltrim($line["zx_content"]), 100) . "#";
					}

				$tipstr .= "添加时间：" . date("Y-m-d H:i", $line["addtime"]) . "　　（" . $line["u_name"] . " @ " . $user_part_name[$line["part_id"]] . "）#";

				if (trim($line["hf_log"]) != '') {
					$line["hf_log"] = str_replace("<br>", "\n", $line["hf_log"]);
					$tipstr .= "回访记录：" . cut($line["hf_log"], 500) . "#";
					}
				$tipdata[] = $tipstr . "#";
				}
			}
		}


	if (count($tipdata) > 0) {
		$out["tips"] .= implode('', $tipdata);
		$out["tips"] .= "请酌情考虑是否继续添加！";
		$out["tips"] = str_replace("#", "\n", trim($out["tips"], "#"));
		}

	$out["status"] = "ok";
	$out["type"]   = $type;
	$out["value"]  = $value;
	}

echo FastJSON::convert($out);
?>