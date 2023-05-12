<?php
// --------------------------------------------------------
// - ����˵�� : �����б�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-05-01 08:09
// --------------------------------------------------------
require "lib/set_env.php";
require "lib/dianhua_quhao.php";
$table = "patient_" . $hid;

$super_delete_users = $sys_super_admin; //����ɾ�����л��ߡ�(��Ҫ�ȿ�ɾ����Ȩ��)
$is_super_edit = in_array($realname, explode(" ", $sys_super_admin));

check_power('', $pinfo) or exit("û�д�Ȩ��...");
if ($hid == 0) {
	exit_html("û��ѡ��ҽԺ�����������Ͻ�ѡ��ҽԺ��");
}

$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);


// �����Ĵ��� ----------- op begin --------------
$op = trim($_GET["op"]);

if ($op == "delete") {
	if (!in_array("patient_delete", $gGuaHaoConfig)) {
		exit("�Բ�����û��ɾ��Ȩ��...");
	}
	$id = intval($_GET["id"]);
	if ($id > 0) {
		$old = $db->query("select * from $table where id=$id limit 1", 1);
		del_data($db, $table, $id, 1, "ɾ�����ߡ�{name}��") ? $del_ok++ : $del_fail++;
		// ������ @ 2016-10-19
		if ($old["from_table"] != "" && $old["from_id"] > 0) {
			$db->query("update `" . $old["from_table"] . "` set is_yuyue=0, is_come=0 where id=" . $old["from_id"] . " limit 1");
		}
	}

	msg_box("ɾ���ɹ�", "back", 1);
}
// --------------------op end -------------------

// ��־:
if ($_GET["from"] == "search") {
	if ($_GET["searchword"]) {
		//user_op_log("����[".trim($_GET["searchword"])."]");
	} else {
		//user_op_log("��������");
	}
} else {
	//user_op_log("�򿪻����б�");
}


if ($_GET["btime"]) {
	$_GET["begin_time"] = strtotime($_GET["btime"] . " 0:0:0");
}
if ($_GET["etime"]) {
	$_GET["end_time"] = strtotime($_GET["etime"] . " 23:59:59");
}

// ���嵱ǰҳ��Ҫ�õ��ĵ��ò���:
$aLinkInfo = explode(" ", "page sort sorttype searchword search_type begin_time end_time time_type show come kefu_23_name kefu_4_name doctor_name wish_doctor xiaofei disease part_id from depart names engine media_from date account my hf_time qq_from tel_from condition status sex order_soft callid show_remind_all remind_date guiji qudao tuiguangren guoqi_days shijiancha from_site");

// ��ȡҳ����ò���:
foreach ($aLinkInfo as $v) {
	$$v = $_GET[$v];
}
$show_type = $show;


$doctor_mode = 0;
if ($uinfo["character_id"] == 28) {
	$doctor_mode = 1;
}


// 2013-10-11 ҽ��
if ($doctor_mode) {
	if ($_GET["date"] == "") {
		$_GET["date"] = date("Y-m-d");
	}
}


if ($debug_mode) {
	$config["show_xiaofei"] = 2;
}

// ���嵥Ԫ���ʽ:
$aOrderType = array(0 => "", 1 => "asc", 2 => "desc");
$hds = array();
$hds["name"] = array("title" => "����", "width" => "50", "align" => "center", "sort" => "name", "defaultorder" => 1);
$hds["sex"] = array("title" => "�Ա�", "width" => "", "align" => "center", "sort" => "", "defaultorder" => 1);
$hds["age"] = array("title" => "����", "width" => "", "align" => "center", "sort" => "age", "defaultorder" => 1);
$hds["tel"] = array("title" => "�绰", "width" => "80", "align" => "center", "sort" => "tel", "defaultorder" => 1);
$hds["zhuanjia_num"] = array("title" => "ר�Һ�", "width" => "", "align" => "center", "sort" => "zhuanjia_num", "defaultorder" => 1);
$hds["card_id"] = array("title" => "���֤��", "width" => "", "align" => "center", "sort" => "card_id", "defaultorder" => 1);
$hds["content"] = array("title" => "&nbsp;��ѯ����|��ע|�ط�", "width" => "300", "align" => "left", "sort" => "", "defaultorder" => 1);
$hds["order_date"] = array("title" => "ԤԼʱ��", "width" => "80", "align" => "center", "sort" => "order_date", "defaultorder" => 2);
//$hds["remain_time"] = array("title"=>"����", "width"=>"", "align"=>"center", "sort"=>"remain_time", "defaultorder"=>2);
$hds["disease_id"] = array("title" => "����", "width" => "", "align" => "center", "sort" => "disease_id", "defaultorder" => 1);
$hds["media_from"] = array("title" => "ý��", "width" => "", "align" => "center", "sort" => "media_from", "defaultorder" => 1);
$hds["engine"] = array("title" => "��������", "width" => "", "align" => "center", "sort" => "engine", "defaultorder" => 1);
$hds["from_site"] = array("title" => "��Դ��վ", "width" => "", "align" => "center", "sort" => "from_site", "defaultorder" => 1);
$hds["key_word"] = array("title" => "�ؼ���", "width" => "", "align" => "center", "sort" => "key_word", "defaultorder" => 1);
$hds["youhuazu"] = array("title" => "�Ż���", "width" => "", "align" => "center", "sort" => "youhuazu", "defaultorder" => 1);
$hds["part_id"] = array("title" => "����", "width" => "", "align" => "center", "sort" => "part_id", "defaultorder" => 1);
$hds["depart"] = array("title" => "����", "width" => "", "align" => "center", "sort" => "depart", "defaultorder" => 1);
$hds["account"] = array("title" => "�˺�", "width" => "", "align" => "center", "sort" => "account", "defaultorder" => 1);
$hds["shichang"] = array("title" => "�г�", "width" => "", "align" => "center", "sort" => "shichang", "defaultorder" => 1);
if ($config["show_guiji"] > 0 || $debug_mode) {
	$hds["guiji"] = array("title" => "�켣", "width" => "", "align" => "center", "sort" => "guiji", "defaultorder" => 1);
}
$hds["order_soft"] = array("title" => "���", "width" => "", "align" => "center", "sort" => "order_soft", "defaultorder" => 1);
$hds["yibao"] = array("title" => "ҽ��", "width" => "", "align" => "center", "sort" => "is_yibao", "defaultorder" => 1);
$hds["suozaidi"] = array("title" => "�־�ס��", "width" => "", "align" => "center", "sort" => "suozaidi", "defaultorder" => 1);
$hds["tuiguangren"] = array("title" => "�ƹ���", "width" => "", "align" => "center", "sort" => "tuiguangren", "defaultorder" => 1);
//$hds["memo"] = array("title"=>"��ע", "width"=>"", "align"=>"center", "sort"=>"memo", "defaultorder"=>1);
$hds["author"] = array("title" => "�ͷ�", "width" => "", "align" => "center", "sort" => "uid", "defaultorder" => 1);
$hds["status"] = array("title" => "״̬", "width" => "", "align" => "center", "sort" => "status", "defaultorder" => 2, "sort2" => "addtime desc");
$hds["doctor"] = array("title" => "ҽ��", "width" => "", "align" => "center", "sort" => "doctor", "defaultorder" => 1);
if ($config["show_xiaofei"] > 0 || $debug_mode) {
	$hds["xiaofei"] = array("title" => "����", "width" => "", "align" => "center", "sort" => "xiaofei_count", "defaultorder" => 2);
}
$hds["huifang_time"] = array("title" => "�ط�ʱ��", "width" => "", "align" => "center", "sort" => "huifang_nexttime", "defaultorder" => 2);
$hds["addtime"] = array("title" => "���ʱ��", "width" => "80", "align" => "center", "sort" => "addtime", "defaultorder" => 2);
$hds["op"] = array("title" => "����", "width" => "", "align" => "center");

// Ҫ��ʾ����:
if ($debug_mode) {
	$uinfo["patient_headers"] = $_SESSION["patient_headers"];
}
$show_headers = explode("\n", str_replace("\r", "", $uinfo["patient_headers"]));
if (count($show_headers) <= 1) {
	if ($debug_mode) {
		$show_headers = explode(" ", "name tel content order_date disease_id media_from part_id doctor addtime status author op");
	}
	if (in_array($uinfo["part_id"], array(1, 9, 13))) { // ��Щ�ǹ�����Ա
		$show_headers = explode(" ", "name tel content order_date disease_id media_from part_id doctor guiji addtime status yibao author op");
	}
	if (in_array($uinfo["part_id"], array(2))) { //����ͷ�
		$show_headers = explode(" ", "name sex tel content order_date disease_id media_from depart addtime status doctor xiaofei author op");
	}
	if (in_array($uinfo["part_id"], array(3))) { //�绰�ͷ�
		$show_headers = explode(" ", "name sex tel content order_date disease_id media_from depart addtime status doctor xiaofei huifang_time author op");
	}
	if (in_array($uinfo["part_id"], array(4))) { //��ҽ
		$show_headers = explode(" ", "name sex tel content order_date disease_id doctor depart addtime author status yibao op");
	}
	if (in_array($uinfo["part_id"], array(12))) { //�绰�ط�
		$show_headers = explode(" ", "name sex tel content order_date disease_id doctor huifang_time depart addtime author status op");
	}
	if (in_array($uinfo["part_id"], array(14, 15))) { //�ֳ�ҽ��
		$show_headers = explode(" ", "name sex tel content order_date disease_id doctor huifang_time depart addtime author status op");
	}
	if (count($show_headers) < 1) { //�κ��������
		$show_headers = array_keys($hds);
	}
} else {
	$show_headers[] = "op";
}

// �Ƿ������ҹ���
if ($hid != 15 && in_array("depart", $show_headers)) {
	$k = array_search("depart", $show_headers);
	if ($k) unset($show_headers[$k]);
}

// ɾ����Щ��Ч�ı�ͷ:
foreach ($show_headers as $k => $v) {
	if (!array_key_exists($v, $hds)) {
		unset($show_headers[$k]);
	}
}

// �������������Դ��Ȩ��:
/*
if (!$debug_mode) {
	if ($config["show_engine"] != 1) {
		foreach ($show_headers as $k => $v) {
			if (in_array($v, array("engine", "from_site", "key_word", "youhuazu"))) {
				unset($show_headers[$k]);
			}
		}
	}
}
*/

if ($show_type == 'today') {
	$begin_time = mktime(0, 0, 0);
	$end_time = mktime(23, 59, 59);
} else if ($show_type == 'yesterday') {
	$begin_time = mktime(0, 0, 0) - 24 * 3600;
	$end_time = mktime(0, 0, 0);
} else if ($show_type == "thismonth") {
	$begin_time = mktime(0, 0, 0, date("m"), 1);
	$end_time = strtotime("+1 month", $begin_time);
} else if ($show_type == "lastmonth") {
	$end_time = mktime(0, 0, 0, date("m"), 1);
	$begin_time = strtotime("-1 month", $end_time);
}

// ����������:
if ($_GET["date"]) {
	$begin_time = strtotime($_GET["date"] . " 0:0:0");
	$end_time = strtotime($_GET["date"] . " 23:59:59");
}

// ��ѯ����:
$where = array();

// ͬԺ��֧�� @ 2014-9-25
if ($callid > 0) {
	// �����������κ�����
	$where[] = "(id=" . intval($callid) . " and addtime=" . intval($_GET["crc"]) . ")";
} else {

	// �����ж�:
	if ($condition) {
		if ($condition == "my") {
			$where[] = "uid=" . $uid;
		} else {
			$condition = intval($condition);
			$cline = $db->query("select * from index_module where id=$condition limit 1", 1);
			if (intval($cline["id"]) == 0) {
				exit("�������� condition=" . $condition);
			}
			if ($cline["condition_code"]) {
				$where[] = "(" . $cline["condition_code"] . ")";
			}
		}

		if ($condition == "2") {
			if ($time_type == "addtime") {
				$where[] = "part_id not in (4)";
			}
		}
	}


	// �绰�ط�:
	if ($uinfo["part_id"] == 12) {
		//if ($hid == 3) {
		//$where[] = "status!=1"; //���ط��ѵ�����   @ 2014-8-23 ֻ��Ծ���
		//}
		if ($uinfo["part_admin"]) {
			//$where[] = "status=1"; //�绰�طò��Ź���Ա
		} else {
			$where[] = "huifang_kf='" . $realname . "'"; //�绰�ط���ͨ��Ա
		}
	}

	// �ֳ�ҽ��:
	if ($uinfo["part_id"] == 14) {
		$where[] = "(binary xianchang_doctor='" . $realname . "' or uid=" . $uid . ")";
	}

	// ����ҽ����2013-4-6
	if ($uinfo["part_id"] == 15) {
		$where[] = "(binary doctor='" . $realname . "' or uid=" . $uid . ")";
	}

	if ($searchword) {
		$sw = trim($searchword);
		if (strlen($sw) < 4) {
			// �����ַ�������4λ���������绰�ֶ�
			if ($search_type == "name_tel") {
				$where[] = "(name like '%{$sw}%')";
			} else {
				$where[] = "concat(name,'_',content,'_',memo,'_',zhuanjia_num,'_',huifang,'_',yuyue_num) like '%{$sw}%'";
			}
		} else {
			// ���绰����
			if ($search_type == "name_tel") {
				$where[] = "(name like '%{$sw}%' or tel like '%{$sw}%')";
			} else {
				$where[] = "concat(name,'_',tel,'_',content,'_',memo,'_',zhuanjia_num,'_',huifang,'_',yuyue_num) like '%{$sw}%'";
			}
		}
	} else {

		/*
		// �ͷ�����ʾ��������ǰ������ 2010-08-18 11:45:
		if ($debug_mode || $username == "admin" || $uinfo["part_admin"] || in_array($uinfo["part_id"], array(1, 4, 9))) {
			// ������
		} else {
			if (in_array($hid, array(1, 15))) {
				$two_month = strtotime("-2 month");
				$where[] = "order_date>=".$two_month;
			}
		}
	*/
	}


	if ($uinfo["limit_month"] > 0) {
		$_month = strtotime("-" . $uinfo["limit_month"] . " month");
		$where[] = "order_date>=" . $_month;
	}




	/*############################2016-5-7����##############################*/
	if (strlen($searchword) <= 3) { //����ģʽ������
		if ($uinfo["part_id"] == 4) { //����Ե�ҽ
			$t0 = strtotime(date("Y-m-d"));
			$where[] = "order_date>=" . $t0;
		}
	}
	/*############################2016-5-7����##############################*/




	// ���ƽ���ʾ���ѵ�����δ����
	if ($uinfo["limit_status"] != 0) {
		if ($uinfo["limit_status"] == 1) {
			$where[] = "status=1";
		} else {
			$where[] = "status!=1";
		}
	}

	if ($status != '') {
		$where[] = "status=" . intval($status);
	}

	// ���ƽ���ʾ����������δ������ͨ�����Ѷ��жϣ� 2012-09-07
	if ($uinfo["limit_xiaofei"] != 0) {
		if ($uinfo["limit_xiaofei"] == 1) {
			$where[] = "xiaofei_count>0";
		} else {
			$where[] = "xiaofei_count=0";
		}
	}


	// where ���Ӳ��� @ 2013-5-4
	if (trim($uinfo["sql_add"]) != '') {
		$where[] = trim($uinfo["sql_add"]);
	}


	// ������������:
	if (trim($names)) {
		$names = str_replace("\r", "", $names);
		$names_arr = explode("\n", $names);
		$names_search = array();
		foreach ($names_arr as $k) {
			if ($k = trim($k)) {
				$names_search[] = "binary name='" . $k . "'";
			}
		}
		if (count($names_search) > 0) {
			$where[] = "(" . implode(" or ", $names_search) . ")";
		}
		$name_search = 1;
	}

	// ��ȡȨ��:
	$today_where = '';

	/*
	if (!$debug_mode) {
		$read_parts = get_manage_part(); //�����Ӳ��ţ���ͬ��������)
		if ($uinfo["part_admin"] || $uinfo["part_manage"]) { //���Ź���Ա�����ݹ���Ա
			$where[] = "(part_id in (".$read_parts.") or uid=".$uid.")";
		} else { //��ͨ�û�ֻ��ʾ�Լ�������
			$where[] = "uid=".$uid."";
		}
	}
	*/

	// ��ȡȨ��:
	// ����������ݵ�Ȩ�ޣ��Ͳ��ü��κε�����
	if (in_array("all", $user_data_power)) {
		// ����Ҫ����
	} else {
		if (empty($config["data_power"])) {
			$where[] = "uid=" . $uid; //ֻ�ܿ��Լ���
		} else {
			// ����Ȩ��:
			$_part_ids = array();
			if (in_array("web", $user_data_power)) {
				$_part_ids[] = 2; //��webȨ��
			}
			if (in_array("tel", $user_data_power)) {
				$_part_ids[] = 3; //��telȨ��
			}
			if (in_array("dy", $user_data_power)) {
				$_part_ids[] = 4; //��telȨ��
			}
			if (in_array("qh", $user_data_power)) {
				$_part_ids[] = 13; //��telȨ��
			}
			if ($uinfo["part_admin"] && !in_array($uinfo["part_id"], $_part_ids)) {
				$_part_ids[] = $uinfo["part_id"]; //���Ź���Ա
			}
			$where[] = "(part_id in (" . ($_part_ids ? implode(",", $_part_ids) : "0") . ") or uid=" . $uid . ")";
		}
	}

	$time_type = empty($time_type) ? 'order_date' : $time_type;
	if ($begin_time > 0) {
		$where[] = $time_type . '>=' . $begin_time;
	}
	if ($end_time > 0) {
		$where[] = $time_type . '<' . $end_time;
	}
	if ($come != '') {
		if ($come == 1) {
			$where[] = "status=1";
		} else {
			$where[] = "status!=1";
		}
	}
	if ($sex != '') {
		$where[] = "sex='$sex'";
	}
	if ($kefu_23_name != '') {
		$where[] = "author='$kefu_23_name'";
	}
	if ($kefu_4_name != '') {
		$where[] = "jiedai='$kefu_4_name'";
	}
	if ($doctor_name != '') {
		$where[] = "doctor='$doctor_name'";
	}
	if ($wish_doctor != '') {
		$where[] = "wish_doctor='$wish_doctor'";
	}
	if ($disease != '') {
		if ($from == "main") {
			$where[] = "(disease_id=$disease and part_id in (2,3))";
		} else {
			$where[] = "disease_id=$disease";
		}
	}
	if ($part_id != '') {
		$where[] = "part_id=$part_id";
	}
	if ($depart != '') {
		$where[] = "depart=$depart";
	}
	if ($media_from) {
		$where[] = "media_from='" . $media_from . "'";
	}
	if ($engine) {
		$where[] = "engine='" . $engine . "'";
	}
	if ($account) {
		$where[] = "account='" . $account . "'";
	}
	if ($my) {
		$where[] = "uid=" . $uid . "";
	}
	if ($from_site != "") {
		$where[] = "from_site like '%{$from_site}%'";
	}
	if ($hf_time) {
		$_remind_date = intval($hf_time);
		$_ids = $db->query("select patient_id from patient_remind where hid=$hid and remind_date='$_remind_date' and uid=$uid", "", "patient_id");
		if (count($_ids) > 0) {
			$where[] = "id in (" . implode(",", $_ids) . ")";
		} else {
			$where[] = "id in (0)";
		}
	}
	if ($qq_from) {
		$where[] = "qq_from='" . $qq_from . "'";
	}
	if ($tel_from) {
		$where[] = "tel_from='" . $tel_from . "'";
	}
	if ($order_soft) {
		$where[] = "order_soft='" . $order_soft . "'";
	}
	if ($guiji > 0) {
		$where[] = "guiji=" . intval($guiji);
	}
	if ($qudao > 0) {
		$where[] = "qudao=" . intval($qudao);
	}
	if ($tuiguangren != '') {
		$where[] = "tuiguangren='" . $tuiguangren . "'";
	}
	if ($guoqi_days > 0) {
		$_t = strtotime("-" . $guoqi_days . " days");
		$where[] = "order_date<=" . $_t;
	}

	if ($shijiancha > 0) {
		$where[] = "(status=1 and part_id!=4 and abs(order_date-addtime)<=" . ($shijiancha * 60) . ")";
	}

	// ��ʾ�������Ļط����� 2015-3-17
	if ($show_remind_all) {
		if ($remind_date) {
			$remind_date = intval($remind_date);
			$_id_arr = $db->query("select patient_id from patient_remind where hid='$hid' and remind_date=$remind_date limit 500", "", "patient_id");
			$_ids = count($_id_arr) > 0 ? implode(",", $_id_arr) : "0";
			$where[] = "id in (" . $_ids . ")";
		} else {
			$t1 = date("Ymd", strtotime("-1 days"));
			$t2 = date("Ymd", strtotime("+1 days"));
			$_id_arr = $db->query("select patient_id from patient_remind where hid='$hid' and remind_date>=$t1 and remind_date<=$t2 limit 500", "", "patient_id");
			$_ids = count($_id_arr) > 0 ? implode(",", $_id_arr) : "0";
			$where[] = "id in (" . $_ids . ")";
		}
	}
}


$sqlwhere = count($where) > 0 ? ("where " . implode(" and ", $where)) : "";


// ������Ĵ���
if ($sort) {
	$sqlsort = "" . $hds[$sort]["sort"] . " ";
	if ($sorttype) {
		$sqlsort .= $aOrderType[$sorttype];
	} else {
		$sqlsort .= $aOrderType[$hds[$sort]["defaultorder"]];
	}
	if ($hds[$sort]["sort2"]) {
		$sqlsort .= ',' . $hds[$sort]["sort2"];
	}
} else {

	// Ĭ������ʽ:
	if (in_array($uinfo["part_id"], array(2, 3))) {

		if ($uinfo["part_admin"]) {
			$sqlsort = "addtime desc";
			$defaultsort = "addtime";
			$defaultorder = 2;
		} else {
			$sqlsort = "addtime desc";
			$defaultsort = "addtime";
			$defaultorder = 2;
		}
	} else if (in_array($uinfo["part_id"], array(4))) {
		$sqlsort = "order_date desc, status asc";
		$defaultsort = "order_date";
		$defaultorder = 2;
	} else if (in_array($uinfo["part_id"], array(12))) {
		$sqlsort = "addtime desc";
		$defaultsort = "addtime";
		$defaultorder = 2;
	} else if (in_array($uinfo["part_id"], array(14))) {
		$sqlsort = "order_date desc, status desc";
		$defaultsort = "order_date";
		$defaultorder = 2;
	} else {
		$sqlsort = "order_date desc, status desc";
		$defaultsort = "order_date";
		$defaultorder = 2;
	}
}

$today_begin = mktime(0, 0, 0);
$today_end = $today_begin + 24 * 3600;

if (substr_count($sqlsort, "order_date") > 0) {
	// ���ڲ���ԤԼʱ����������� @ 2012-05-10  1:������ǰ��2-�����Ժ�3-���쵱��
	$sql_add = ",if(order_date<$today_begin,1,if (order_date>$today_end,2,3)) as sd";
	$sqlsort = $sqlsort ? ("order by sd desc," . $sqlsort) : "";
} else {
	$sql_add = '';
	$sqlsort = $sqlsort ? ("order by " . $sqlsort) : "";
}

// ���� @ 2012-06-07 ��������쳣���
if ($sqlsort != '') {
	$sqlsort .= ", id asc";
} else {
	$sqlsort = "id asc";
}

// ��ҳ����:
ob_start();
$count = $db->query("select count(*) as count from $table $sqlwhere $sqlgroup", 1, "count");
$pagecount = max(ceil($count / $pagesize), 1);
$page = max(min($pagecount, intval($page)), 1);
$offset = ($page - 1) * $pagesize;

// ��ѯ:
$query_begin_time = now();
$time = time();
$ids_arr = $db->query("select id $sql_add from $table $sqlwhere $sqlgroup $sqlsort limit $offset,$pagesize", "", "id");
$s_sql = $db->sql;
if (!is_array($ids_arr) || count($ids_arr) == 0) {
	$data = array();
} else {
	$ids = implode(",", $ids_arr);
	$data = array();
	$data2 = $db->query("select * from $table where id in ($ids)", "id");
	$s_sql .= "   " . $db->sql;
	foreach ($ids_arr as $_id) {
		$data[$_id] = $data2[$_id];
	}
}


$cur_page_line_count = @intval(count($data));

// id => name:
$hospital_id_name = $db->query("select id,name from hospital", 'id', 'name');
$part_id_name = $db->query("select id,name from sys_part", 'id', 'name');
$disease_id_name = $db->query("select id,name from disease", 'id', 'name');
$depart_id_name = $db->query("select id,name from depart where hospital_id=$hid", 'id', 'name');
$qudao_arr = $db->query("select id, name from dict_qudao", "id", "name");

$estr = ob_get_clean();
if ($estr != '') {
	exit_html("��ѯ��������ϵ������Ա��<br>" . $estr);
}


// �ѵ绰���룬Ҫ���⴦��һ�� ʹ�������������˵ĺ���
if ($uinfo["part_id"] == 12) { // �绰�ط���
	// ��������û���ѵ�������һ���绰����������:
	if ($cur_page_line_count == 0 && strlen($searchword) == 11 && substr($searchword, 0, 1) == "1") {
		$id = $db->query("select id from $table where tel like '%" . $searchword . "%' order by id desc limit 1", 1, "id");
		// ���������:
		if ($id > 0) {
			echo '<script>' . "\r\n";
			echo 'parent.load_src(1,"patient_view.php?id=' . $id . '", 900,550);' . "\r\n"; //�����Ի�����ʾ��¼
			echo 'history.go(-1);' . "\r\n"; //�б�ҳ�����Ϊ��ǰ��
			echo '</script>' . "\r\n";
			exit;
		}
	}
}


// ���б����ݷ���:
// 1. �� addtime ����:
if ($sort == "addtime" || ($sort == "" && $defaultsort == "addtime")) {
	if ($sorttype == 2 || $defaultorder == 2) {
		$today_begin = mktime(0, 0, 0);
		$today_end = $today_begin + 24 * 3600;
		$yesterday_begin = $today_begin - 24 * 3600;

		$data_part = array();
		foreach ($data as $line) {
			if ($line["addtime"] < $yesterday_begin) {
				$data_part[3][] = $line;
			} else if ($line["addtime"] < $today_begin) {
				$data_part[2][] = $line;
			} else if ($line["addtime"] < $today_end) {
				$data_part[1][] = $line;
			}
		}

		$data = array();
		if (is_array($data_part[1]) && count($data_part[1]) > 0) { //�н��������:
			$data[] = array("id" => 0, "name" => "���� [" . count($data_part[1]) . "]");
			$data = array_merge($data, $data_part[1]);
		}
		if (is_array($data_part[2]) && count($data_part[2]) > 0) { //�н��������:
			$data[] = array("id" => 0, "name" => "���� [" . count($data_part[2]) . "]");
			$data = array_merge($data, $data_part[2]);
		}

		if (is_array($data_part[3]) && count($data_part[3]) > 0) { //�н��������:
			$data[] = array("id" => 0, "name" => "ǰ������ [" . count($data_part[3]) . "]");
			$data = array_merge($data, $data_part[3]);
		}
		unset($data_part);
	}

	// 2. �� �Ƿ�Ժ ��:
} else if ($sort == "status" || ($sort == "" && $defaultsort == "status")) {
	$data_part = array();
	foreach ($data as $line) {
		if ($line["status"] == 1) { //�ѵ�
			$data_part[1][] = $line;
		} else { //δ��
			$data_part[2][] = $line;
		}
	}

	$data = array();
	if (count($data_part[1]) > 0) {
		$data[] = array("id" => 0, "name" => "�ѵ�Ժ [" . count($data_part[1]) . "]");
		$data = array_merge($data, $data_part[1]);
	}
	if (count($data_part[2]) > 0) {
		$data[] = array("id" => 0, "name" => "δ��Ժ [" . count($data_part[2]) . "]");
		$data = array_merge($data, $data_part[2]);
	}
	unset($data_part);

	// 3. �� order_date ��:
} else if ($sort == "order_date" || ($sort == "" && $defaultsort == "order_date")) {
	$today_begin = mktime(0, 0, 0);
	$today_end = $today_begin + 24 * 3600;
	$yesterday_begin = $today_begin - 24 * 3600;

	$data_part = array();
	foreach ($data as $line) {
		if ($line["order_date"] < $yesterday_begin) {
			$data_part[1][] = $line;
		} else if ($line["order_date"] < $today_begin) {
			$data_part[2][] = $line;
		} else if ($line["order_date"] < $today_end) {
			$data_part[3][] = $line;
		} else {
			$data_part[4][] = $line;
		}
	}

	$data = array();

	if (is_array($data_part[3]) && count($data_part[3]) > 0) {
		$data[] = array("id" => 0, "name" => "���� [" . count($data_part[3]) . "]");
		$data = array_merge($data, $data_part[3]);
	}
	if (is_array($data_part[4]) && count($data_part[4]) > 0) {
		$data[] = array("id" => 0, "name" => "������Ժ� (ʱ��δ��) [" . count($data_part[4]) . "]");
		$data = array_merge($data, $data_part[4]);
	}
	if (is_array($data_part[2]) && count($data_part[2]) > 0) {
		$data[] = array("id" => 0, "name" => "���� [" . count($data_part[2]) . "]");
		$data = array_merge($data, $data_part[2]);
	}
	if (is_array($data_part[1]) && count($data_part[1]) > 0) {
		$data[] = array("id" => 0, "name" => "ǰ������ [" . count($data_part[1]) . "]");
		$data = array_merge($data, $data_part[1]);
	}
	unset($data_part);
}


function _content_color($s)
{
	global $hinfo;
	if ($hinfo["template"] == '') {
		return $s;
	}
	$s = str_replace("<br>", "\n", $s);
	$s = str_replace("\r", "", $s);
	$arr = explode("\n", $s);
	foreach ($arr as $k => $v) {
		if (substr_count($v, "��") > 0 && substr_count($v, "����ָ��ҽ��") == 0) {
			list($a, $b) = explode("��", $v, 2);
			if ($b == '') {
				unset($arr[$k]);
			} else {
				$arr[$k] = '<font color="#c4833c">' . $a . '��</font>' . $b;
			}
		}
	}
	return implode("<br>", $arr);
}


function _content_filter($str, $filter_string = '')
{
	if (trim($filter_string) == '') return $str;
	$arr = explode(" ", trim($filter_string));
	foreach ($arr as $v) {
		$str = str_replace($v, "***", $str);
	}
	return $str;
}


// ��ɫ����
$day7_color = "#00c600";

$line_color = array('black', 'red', 'silver', '#8AC2DD', '#ff00ff', '#7d007d', $day7_color);
$line_color_tip = array("�ȴ�", "�ѵ�", "δ��", "����", "������", "�ѻط�", "7�պ�Ժ");


// ҳ�濪ʼ ------------------------
?>
<html>

<head>
	<title><?php echo $pinfo["title"]; ?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
	<link href="lib/base.css" rel="stylesheet" type="text/css">
	<script src="lib/base.js" language="javascript"></script>
	<script src="lib/datejs/picker.js" language="javascript"></script>
	<style>
		#color_tips {
			padding: 0 0 0 12px;
		}

		#float_box {
			border: 2px solid #ffac84;
			position: absolute;
			z-index: 99999;
			background: #F5FAFA;
			-moz-border-radius: 4px;
			-webkit-border-radius: 4px;
			border-radius: 4px;
			-webkit-box-shadow: 0 0 8px rgba(64, 0, 0, .5);
			-moz-box-shadow: 0 0 8px rgba(64, 0, 0, .5);
		}

		.tr_high_light td {
			background: #FFE1D2;
		}

		.num {
			font-family: "Tahoma";
		}

		/* ���ƴ����ͼ�¼ @ 2013-01-08 */
		.zl_history {
			margin-left: 35px;
			margin-top: 6px;
		}

		.zl_no {
			float: left;
			border: 1px solid silver;
			width: 10px;
			height: 10px;
			line-height: 0px;
			overflow: hidden;
			margin: 0;
		}

		.zl_yes {
			float: left;
			border: 1px solid red;
			background: #fea45a;
			width: 10px;
			height: 10px;
			line-height: 0px;
			overflow: hidden;
			margin: 0;
		}

		.zl_line {
			float: left;
			width: 3px;
			height: 4px;
			margin-top: 5px;
			border-top: 1px dotted silver;
			margin-left: 1px;
			margin-right: 1px;
			line-height: 0px;
		}

		.zl_clear {
			clear: both;
			line-height: 0px;
			font-size: 0;
		}

		.zl_word {
			font-family: "Tahoma";
		}

		/* �б�������ʾ��������� @ 2013-01-21 */
		.ct {
			border: 0;
			margin: 3px 0;
		}

		.ct * {
			line-height: 150% !important;
		}

		.ct_td_a {
			width: 32px;
			vertical-align: top;
		}

		.ct_td_b {
			/*color:#484848;*/
		}

		.c_1 {
			color: #2d2d2d;
		}

		.c_2 {
			color: #d36001;
		}

		.c_3 {
			color: #36874a;
		}

		.c_4 {
			color: #484848;
		}

		.c_5 {
			color: #484848;
		}

		.c_6 {
			color: #484848;
		}
	</style>

	<script language="javascript">
		window.last_high_obj = '';

		function set_high_light(obj) {
			if (last_high_obj) {
				//last_high_obj.parentNode.parentNode.parentNode.className = "";
				set_high_light_to_tr(last_high_obj, "");
			}
			if (obj) {
				//obj.parentNode.parentNode.parentNode.className = "tr_high_light";
				set_high_light_to_tr(obj, "tr_high_light");
				last_high_obj = obj;
			} else {
				last_high_obj = '';
			}
		}

		// ����Ѱ��TR�ڵ㣬�ҵ�����className
		function set_high_light_to_tr(obj, class_name) {
			max_level = 5;
			o = obj;
			for (var n = 1; n <= max_level; n++) {
				o = o.parentNode;
				if (o.tagName == "TR") {
					o.className = class_name;
					return true;
				}
			}
			return false;
		}

		function add() {
			set_high_light('');
			parent.load_src(1, 'patient_add.php?from=list');
			return false;
		}

		function edit(id, chk, obj) {
			set_high_light(obj);
			parent.load_src(1, 'patient_edit.php?id=' + id + '&chk=' + chk);
			return false;
		}

		function ld(id, obj) {
			set_high_light(obj);
			parent.load_src(1, 'patient_view.php?id=' + id, 900, 550);
			return false;
		}

		function close_divs() {
			byid("float_box").innerHTML = '';
			byid("float_box").style.display = "none";
		}

		function set_come(id, chk, obj) {
			if (id > 0) {
				set_high_light(obj);
				var left = get_position(obj, "left");
				var top = get_position(obj, "top");
				var src = "patient_set_come.php?id=" + id + "&chk=" + chk;
				var w = 300;
				var h = 240;
				byid("float_box").innerHTML = '<iframe id="wee_frame" src="about:blank" width="' + w + '" height="' + h +
					'" border="0" frameborder="0"></iframe>';
				byid("float_box").style.display = "block";
				byid("float_box").style.left = (left - w - 10) + "px";
				byid("float_box").style.top = top - (h / 2) + 8 + "px";
				byid("wee_frame").src = src; //���IE6�в���ʾ������
			}
			event.cancelBubble = true;
		}

		<?php if ($is_super_edit) { ?>

			function debug(id, obj) {
				set_high_light(obj);
				parent.load_src(1, 'patient_debug.php?id=' + id);
				return false;
			}
		<?php } ?>

		function yuyue_card(id, obj) {
			set_high_light(obj);
			parent.load_src(1, 'patient_yuyue_card.php?id=' + id, 900, 550);
			return false;
		}

		function yuyue_card_add() {
			set_high_light('');
			parent.load_src(1, 'patient_yuyue_card_add.php', 900, 550);
			return false;
		}

		function set_huifang_tixing(id, chk, obj) {
			set_high_light(obj);
			parent.load_src(1, 'patient_set_huifang_tixing.php?id=' + id + '&chk=' + chk, 700, 400);
			return false;
		}

		function set_huifang(id, chk, obj) {
			set_high_light(obj);
			parent.load_src(1, 'patient_huifang.php?id=' + id + '&chk=' + chk, 900, 550);
			return false;
		}

		function set_tuiguangren(id, chk, obj) {
			set_high_light(obj);
			parent.load_src(1, 'patient_set_tuiguangren.php?patient_id=' + id + '&chk=' + chk, 600, 350);
			return false;
		}

		function set_huifang_kf(id, obj) {
			if (id > 0) {
				set_high_light(obj);
				var left = get_position(obj, "left");
				var top = get_position(obj, "top");
				var src = "patient_huifang_kf.php?id=" + id;
				var w = 300;
				var h = 100;

				byid("float_box").innerHTML = '<iframe id="wee_frame" src="about:blank" width="' + w + '" height="' + h +
					'" border="0" frameborder="0"></iframe>';
				byid("float_box").style.display = "block";
				byid("float_box").style.left = (left - w - 10) + "px";
				byid("float_box").style.top = top - (h / 2) + 8 + "px";
				byid("wee_frame").src = src;
			}
			event.cancelBubble = true;
		}

		function set_guiji(id, obj) {
			if (id > 0) {
				set_high_light(obj);
				var left = get_position(obj, "left");
				var top = get_position(obj, "top");
				var src = "patient_set_guiji.php?id=" + id;
				var w = 340;
				var h = 160;

				byid("float_box").innerHTML = '<iframe id="wee_frame" src="about:blank" width="' + w + '" height="' + h +
					'" border="0" frameborder="0"></iframe>';
				byid("float_box").style.display = "block";
				byid("float_box").style.left = (left - w - 10) + "px";
				byid("float_box").style.top = top - (h / 2) + 8 + "px";
				byid("wee_frame").src = src;
			}
			event.cancelBubble = true;
		}


		function upload_luyin(id, obj) {
			if (id > 0) {
				set_high_light(obj);
				var left = get_position(obj, "left");
				var top = get_position(obj, "top");
				var src = "patient_upload_luyin.php?id=" + id;
				var w = 340;
				var h = 160;

				byid("float_box").innerHTML = '<iframe id="wee_frame" src="about:blank" width="' + w + '" height="' + h +
					'" border="0" frameborder="0"></iframe>';
				byid("float_box").style.display = "block";
				byid("float_box").style.left = (left - w - 10) + "px";
				byid("float_box").style.top = top - (h / 2) + 8 + "px";
				byid("wee_frame").src = src;
			}
			event.cancelBubble = true;
		}


		function set_xiaofei(id, obj) {
			if (id > 0) {
				set_high_light(obj);
				parent.load_src(1, "patient_set_xiaofei.php?id=" + id, 600, 500);
			}
		}

		function set_patient_header(obj) {
			parent.load_src(1, 'patient_set_header.php', 650, 400);
			return false;
		}

		function move_keshi(id, obj) {
			set_high_light(obj);
			parent.load_src(1, 'patient_move_keshi.php?patient_id=' + id, 800, 500);
			return false;
		}

		function search() {
			parent.load_src(1, 'patient_search.php?from=list');
			return false;
		}

		function search_name_tel() {
			byid("search_condition").innerHTML = '';
			byid("search_type").value = "name_tel";
		}

		function search_all() {
			byid("search_condition").innerHTML = '';
			byid("search_type").value = "";
		}

		function search_history() {
			parent.load_src(1, 'patient_search_history.php');
			return false;
		}

		function delete_patient(id, chk) {
			if (isdel()) {
				self.location = "patient.php?op=delete&id=" + id + "&chk=" + chk;
			}
		}

		function set_zixun_group(id, chk, obj) {
			if (id > 0) {
				set_high_light(obj);
				var left = get_position(obj, "left");
				var top = get_position(obj, "top");
				var src = "patient_set_zixun_group.php?id=" + id + "&chk=" + chk;
				var w = 300;
				var h = 120;
				byid("float_box").innerHTML = '<iframe id="wee_frame" src="about:blank" width="' + w + '" height="' + h +
					'" border="0" frameborder="0"></iframe>';
				byid("float_box").style.display = "block";
				byid("float_box").style.left = (left - w - 10) + "px";
				byid("float_box").style.top = top - (h / 2) + 8 + "px";
				byid("wee_frame").src = src; //���IE6�в���ʾ������
			}
			event.cancelBubble = true;
		}
	</script>
</head>

<body onclick="close_divs()">

	<div id="float_box" style="display:none;">
		<!-- ���ڵ���С�� -->
	</div>

	<!-- ͷ�� begin -->
	<table class="headers" width="100%">
		<tr>
			<td class="headers_title" style="width:280px">
				<nobr class="tips"><?php echo $hospital_id_name[$hid]; ?> - �����б�</nobr>
			</td>
			<td class="header_center" style="width:auto;">
				<?php if (in_array("patient_add", $gGuaHaoConfig)) { ?>
					<button onclick="add()" class="buttonb">��ӻ���</button>
				<?php } ?>
				<button onclick="search()" class="buttonb" style="margin-left:10px;">�߼�����</button>

				<form action="?" method="GET" style="display:inline;"><input name="date" id="ch_date" onchange="this.form.submit();" value="<?php echo $_GET["date"]; ?>" style="width:0px; overflow:hidden; padding:0; border:0; margin-left:10px; "></form>
				<a href="javascript:;" onclick="picker({el:'ch_date',dateFmt:'yyyy-MM-dd'});" title="��ԤԼʱ��鿴ĳһ������"><?php echo $_GET["date"] != "" ? $_GET["date"] : "���ղ鿴"; ?></a>


				<?php if (in_array("set_huifang_renwu", $gGuaHaoConfig)) { ?>
					<a href="javascript:;" onclick="parent.load_src(1, 'patient_set_huifang_work.php', 800, 500);;" style="margin-left:10px;">��ط�����</a>
				<?php } ?>

			</td>
			<td class="headers_oprate" style="width:330px;">
				<form name="topform" method="GET">�������ݣ�
					<input name="searchword" id="searchword" value="<?php echo $_GET["searchword"]; ?>" class="input" style="width:100px;">
					<!-- <input type="submit" class="button3" title="ֻ�������ֺ͵绰" value="��ȷ��" onclick="search_name_tel(this)" style="margin-left:5px;"> --><input type="submit" class="button3" onclick="search_all(this)" value="����" title="�����������֣��绰����ѯ���ݣ���ע���طã�ר�Һ�" style="margin-left:5px;">&nbsp;
					<button onclick="tongyuansou(); return false;" class="button3" title="�ڱ�Ժ���п�������������">ͬԺ��</button>
					<script type="text/javascript">
						function tongyuansou() {
							var key = byid("searchword").value;
							if (key == '') {
								alert("��������Ҫ������������绰���루�绰��������Ǻ���λ��");
								byid("searchword").focus();
								return false;
							}
							var url = "patient_tongyuansou.php?code=utf8&key=" + encodeURIComponent(key) + "&r=" + Math
								.random();
							parent.load_src(1, url);
						}
					</script>
					<!-- <button onclick="location='?'" class="search" title="�˳�������ѯ">����</button> -->
					<input type="hidden" name="search_type" value="" />
					<span id="search_condition">
						<?php
						// ����ԭ�ȵ�����
						$arr = explode(" ", "sort sorttype begin_time end_time time_type show come kefu_23_name kefu_4_name doctor_name xiaofei disease part_id from depart names engine media_from date account my hf_time");
						foreach ($arr as $v) {
							if (isset($_GET[$v]) && $_GET[$v] != '') {
						?>
								<input type="hidden" name="<?php echo $v; ?>" value="<?php echo $_GET[$v]; ?>" />
						<?php
							}
						}
						?>
					</span>
				</form>
			</td>
		</tr>
	</table>
	<!-- ͷ�� end -->


	<?php if ($_GET["from"] == "search") { ?>
		<!-- ����������ʾ�� -->
		<div class="space"></div>
		<table width="100%" class="description description_light" style="border:1px solid #85c479; background:#e4ece1;">
			<tr>
				<td align="left" style="width:80px;">&nbsp;<b>����������</b></td>
				<td align="left">
					<?php
					$f_to_name = array(
						"searchword" => "�ؼ���",
						"begin_time" => "��ʼʱ��",
						"end_time" => "����ʱ��",
						"time_type" => "ʱ������",
						"come" => "�Ƿ�Ժ",
						"kefu_23_name" => "�ͷ�",
						"kefu_4_name" => "��ҽ",
						"doctor_name" => "ҽ��",
						"xiaofei" => "�Ƿ�����",
						"disease" => "��������",
						"part_id" => "����",
						"depart" => "����",
						"engine" => "��������",
						"media_from" => "ý����Դ",
						"qq_from" => "QQ��Դ",
						"tel_from" => "�绰��Դ",
						"order_soft" => "ԤԼ���",
						"date" => "����",
						"account" => "�˻�",
						"hf_time" => "�ط�ʱ��",
						"guiji" => "�켣",
						"qudao" => "�����켣",
						"tuiguangren" => "�ƹ���",
						"shijiancha" => "ʱ���С��",
						"from_site" => "��վ��Դ",
					);
					$arr = array();
					foreach ($_GET as $k => $v) {
						if (array_key_exists($k, $f_to_name) && $v) {
							if ($k == "begin_time") {
								$v = date("Y-m-d", $v);
							} else if ($k == "end_time") {
								$v = date("Y-m-d", $v);
							} else if ($k == "come") {
								$v = ($v == '' ? '' : ($v == "1" ? "�ѵ�" : "δ��"));
							} else if ($k == "xiaofei") {
								$v = $v ? "������" : "δ����";
							} else if ($k == "time_type") {
								$v = $v == "addtime" ? "���ʱ��" : "ԤԼʱ��";
							} else if ($k == "part_id") {
								$part_id_arr = array(2 => "����", 3 => "�绰", 4 => "��ҽ");
								$v = $part_id_arr[$v];
							} else if ($k == "disease") {
								$v = $disease_id_name[$v];
							} else if ($k == "guiji") {
								$v = $guiji_arr[$v];
							} else if ($k == "qudao") {
								$v = $qudao_arr[$v];
							} else if ($k == "order_soft") {
								$v = $web_soft_arr[$v];
							}
							$arr[] = $f_to_name[$k] . "��<font color=red><b>" . $v . "</b></font>";
						}
					}
					echo implode("��", $arr);
					?>
				</td>
			</tr>
		</table>
	<?php } ?>


	<!-- ͳ������ begin -->
	<div class="space"></div>
	<?php
	$sbar_left = $sbar_center = $sbar_right = '';

	// ������ͳ������ 2009-05-13 16:46
	$sqlwhere_s = $sqlwhere ? ($sqlwhere . " and status=1") : "where status=1";
	$count_come = $db->query("select count(*) as count from $table $sqlwhere_s $sqlgroup order by id desc", 1, "count");
	$sqlwhere_s = $sqlwhere ? ($sqlwhere . " and status!=1") : "where status!=1";
	$count_not = $db->query("select count(*) as count from $table $sqlwhere_s $sqlgroup order by id desc", 1, "count");
	$count_all = $count_come + $count_not;
	$sbar_left = "&nbsp;<b>ͳ�����ݣ�</b> �ܹ�: <b>" . $count_all . "</b> &nbsp; �ѵ�: <b>" . $count_come . "</b> &nbsp; δ��: <b>" . $count_not . "</b>";

	if (in_array($uinfo["part_id"], array(2, 3, 4))) {
		// ͳ�ƽ�������:
		$t_time_type = "order_date";
		$today_where = ($today_where ? ($today_where . " and") : "") . " $t_time_type>=" . $today_begin;
		$today_where .= " and $t_time_type<" . $today_end;
		$sqlwhere_s = "where " . ($today_where ? ($today_where . " and status=1") : "status=1");
		$count_today_come = $db->query("select count(*) as count from $table $sqlwhere_s order by id desc", 1, "count");

		$sqlwhere_s = "where " . ($today_where ? ($today_where . " and status!=1") : "status!=1");
		$count_today_not = $db->query("select count(*) as count from $table $sqlwhere_s order by id desc", 1, "count");

		$count_today_all = $count_today_come + $count_today_not;

		$sbar_right = "<b>��������: </b> <a href='?show=today'>�ܹ�: <b>" . $count_today_all . "</b></a> &nbsp; <a href='?show=today&come=1'>�ѵ�: <b>" . $count_today_come . "</b></a> &nbsp; <a href='?show=today&come=0'>δ��: <b>" . $count_today_not . "</b></a>&nbsp;";

		// ��������ͳ��(����):
		if (in_array($uinfo["part_id"], array(2, 3))) {
			$basewhere = "part_id=" . $uinfo["part_id"];
			$part_today_all = $db->query("select count(*) as count from $table where $basewhere and order_date>=$today_begin and order_date<$today_end", 1, "count");
			$part_today_come = $db->query("select count(*) as count from $table where $basewhere and order_date>=$today_begin and order_date<$today_end and status=1", 1, "count");
			$part_today_not = $part_today_all - $part_today_come;

			$sbar_center = "<b>���Ž���:</b> �ܹ�: <b>" . $part_today_all . "</b>  �ѵ�: <b>" . $part_today_come . "</b>  δ��: <b>" . $part_today_not . "</b>&nbsp;";
		}
	}

	// 2013-10-11
	if ($doctor_mode) {
		$sbar_center = '��ǰ���ڣ�<b>' . $_GET["date"] . '</b>&nbsp; <a href="?date=' . date("Y-m-d", strtotime("-1 days", strtotime($_GET["date"]))) . '">ǰһ��</a> &nbsp;<a href="?date=' . date("Y-m-d", strtotime("+1 days", strtotime($_GET["date"]))) . '">��һ��</a>&nbsp; ';
		$sbar_right = '';
	}

	?>

	<table width="100%" class="description description_light">
		<tr>
			<td width="33%"><?php echo $sbar_left; ?></td>
			<td align="center"><?php echo $sbar_center; ?></td>
			<td width="33%" align="right"><?php echo $sbar_right; ?></td>
		</tr>
	</table>

	<!-- ���������ͳ������ end -->


	<div class="space"></div>

	<table width="100%">
		<tr>
			<td>
				<div id="color_tips">
					��ɫ��ǣ�
					<?php foreach ($line_color_tip as $k => $v) { ?>
						<font color="<?php echo $line_color[$k]; ?>"><?php echo $v; ?></font>&nbsp;
					<?php } ?>

					&nbsp; &nbsp;��ݷ�ʽ:
					<?php
					$cur_condition = '';
					list($a, $cur_condition) = explode("?", $_SERVER["REQUEST_URI"], 2);
					list($cur_condition, $b) = explode("#", $cur_condition, 2);

					$quick_links = array(
						"����" => "show=today",
						"�����ѵ�" => "show=today&come=1",
						"����" => "show=yesterday",
						"�����ѵ�" => "show=yesterday&come=1",
					);

					foreach ($quick_links as $k => $v) {
						$k_text = $v == $cur_condition ? ('<font color="red">[' . $k . ']</font>') : $k;
					?>
						<a href="?<?php echo $v; ?>" title="�鿴<?php echo $k; ?>����"><?php echo $k_text; ?></a>&nbsp;
					<?php } ?>
					<a href="?" title="�鿴��������">����</a>&nbsp;

					&nbsp; &nbsp;ɸѡ����ʱ��:
					<?php
					$t_def = array("7" => "һ��", "30" => "һ����", "90" => "������");
					foreach ($t_def as $k => $v) {
						$vs = $_GET["guoqi_days"] == $k ? ('<font color="red">[' . $v . ']</font>') : $v;
					?>
						<a href="?guoqi_days=<?php echo $k; ?>"><?php echo $vs; ?></a>&nbsp;
					<?php } ?>
				</div>
			</td>
			<td align="center"></td>
			<td align="right">
				<?php if ($debug_mode || $config["show_remind_all"]) { ?>
					<a href="?show_remind_all=1" title="�鿴���졢���������Ҫ�طõ����л���"><b>�鿴�ط�����</b></a>&nbsp;
				<?php } ?>
				<a href="javascript:;" onclick="parent.load_src(1,'set_date_mode.php', 600, 300);"><b>�����ڸ�ʽ</b></a>&nbsp;
				<a href="javascript:void(0);" onclick="set_patient_header(this)" title="ѡ������ʾ����(���ñ�ͷ)"><b>���ñ�ͷ</b></a>&nbsp;
				<a href="javascript:void(0);" onclick="self.location.reload();" title="ˢ�±�ҳ"><b>ˢ��</b></a>&nbsp;
			</td>
		</tr>
	</table>

	<!-- �����б� begin -->
	<div class="space"></div>
	<form name="mainform">
		<table width="100%" align="center" class="list">
			<!-- ��ͷ���� begin -->
			<tr>
				<?php
				// ��ͷ����:
				foreach ($show_headers as $tdid) {
					$tdinfo = $hds[$tdid];
					list($tdalign, $tdwidth, $tdtitle) = wee_td_head($tdid, $tdinfo);
				?>
					<td class="head" align="<?php echo $tdalign; ?>" width="<?php echo $tdwidth; ?>">
						<nobr><?php echo $tdtitle; ?></nobr>
					</td>
				<?php } ?>
			</tr>
			<!-- ��ͷ���� end -->

			<!-- ��Ҫ�б����� begin -->
			<?php
			if (count($data) > 0) {
				foreach ($data as $line) {
					$id = $line["id"];
					$chk = $line["addtime"]; //������Ч�Լ�飬��ֹ����
					if ($id == 0) {
			?>
						<tr>
							<td colspan="<?php echo count($show_headers); ?>" align="left" class="group">
								<?php echo $line["name"]; ?></td>
						</tr>
					<?php
					} else {

						$op = array();
						$op[] = "<button class='button_op' onclick='ld($id, this); return false;'><img src='image/b_detail.gif' align='absmiddle' title='�鿴'></button>";

						if ($debug_mode) {
							$gGuaHaoConfig = explode(" ", "set_come huifang set_huifang_tixing move_keshi set_tuiguangren");
						}

						// ��ҽ
						if ((in_array($uinfo["part_id"], array(0, 1, 4, 9)) && in_array("set_come", $gGuaHaoConfig))) {
							$op[] = "<button class='button_op' onclick='set_come(" . $id . "," . $chk . ", this); return false;'><img src='image/b_pass.gif' align='absmiddle' title='����Ժ'></button>";
						}

						// �绰�طÿͷ� ����
						if (in_array("set_huifang_kf", $gGuaHaoConfig)) {
							//$op[] = "<button class='button_op' onclick='set_huifang_kf(".$id.", this); return false;'><img src='image/b_user.gif' align='absmiddle' title='���ûطÿͷ�'></button>";
						}

						// �������ûط���Ա
						if (in_array("set_huifang_tixing", $gGuaHaoConfig)) {
							$op[] = "<button class='button_op' onclick='set_huifang_tixing(" . $id . "," . $chk . ", this); return false;'><img src='image/b_user.gif' align='absmiddle' title='���ûط�����'></button>";
						}

						// �绰�طÿͷ�
						if (in_array("huifang", $gGuaHaoConfig)) {
							$op[] = "<button class='button_op' onclick='set_huifang(" . $id . "," . $chk . ", this); return false;'><img src='image/b_tel.gif' align='absmiddle' title='�ط�'></button>";
						}

						// �ֳ���ѯ
						if (in_array("set_xiaofei", $gGuaHaoConfig)) {
							$op[] = "<button class='button_op' onclick='set_xiaofei(" . $id . ", this); return false;'><img src='image/b_check_good.gif' align='absmiddle' title='¼����'></button>";
						}

						// �޸�Ȩ��
						$can_edit = 0;
						if (in_array("patient_edit", $gGuaHaoConfig)) {
							if ($line["author"] == $realname && $line["status"] != 1) {
								$can_edit = 1;
							}
							if ($uinfo["part_admin"] && $line["status"] != 1) {
								$can_edit = 1;
							}
							if ($line["status"] == 1 && $uinfo["edit_come_patient"]) {
								$can_edit = 1;
							}
						}
						if ($can_edit) {
							$op[] = "<button class='button_op' onclick='edit(" . $id . "," . $chk . ", this); return false;' class='op'><img src='image/b_edit.gif' align='absmiddle' title='�޸�' alt=''></button>";
						}

						// �ϴ�¼���ļ�
						if (in_array("upload_luyin", $gGuaHaoConfig)) {
							$op[] = "<button class='button_op' onclick='upload_luyin(" . $id . ", this); return false;'><img src='image/b_upload_luyin.gif' align='absmiddle' title='�ϴ�¼���ļ�'></button>";
						}

						if ($is_super_edit) {
							$op[] = "<button class='button_op' onclick='debug(" . $id . ", this); return false;' class='op'><img src='image/b_lock.gif' align='absmiddle' title='����Ա�޸�ģʽ' alt=''></button>";
						}

						if (in_array("set_guiji", $gGuaHaoConfig)) {
							$op[] = "<button class='button_op' onclick='set_guiji(" . $id . ", this); return false;' class='op'><img src='image/b_guiji.gif' align='absmiddle' title='��켣' alt=''></button>";
						}

						if (in_array("set_zixun_group", $gGuaHaoConfig)) {
							$op[] = "<button class='button_op' onclick='set_zixun_group(" . $id . "," . $chk . ", this); return false;' class='op'><img src='image/b_zixun.gif' align='absmiddle' title='���û��߹���' alt=''></button>";
						}

						if (in_array("move_keshi", $gGuaHaoConfig)) {
							$op[] = "<button class='button_op' onclick='move_keshi(" . $id . ", this); return false;'><img src='image/b_move.gif' align='absmiddle' title='ת����'></button>";
						}

						if (in_array("set_tuiguangren", $gGuaHaoConfig)) {
							$op[] = "<button class='button_op' onclick='set_tuiguangren(" . $id . "," . $chk . ", this); return false;'><img src='image/b_zixun.gif' align='absmiddle' title='�����ƹ���'></button>";
						}

						//ɾ��Ȩ��:
						$can_delete = 0;
						if (in_array("patient_delete", $gGuaHaoConfig)) {
							// ����Ȩ�޴���
							if ($uinfo["delete_patient"] && $line["status"] == 0) {
								$can_delete = 1;
							}
							if ($uinfo["delete_come_patient"] && $line["status"] == 1) {
								$can_delete = 1;
							}
							// �����ύ�߱���
							if ($line["author"] == $realname) {
								if ($line["status"] == 0) { //�ѵ�����ɾ��
									$can_delete = 1;
								}
							}
						}
						if ($can_delete || $debug_mode) {
							$op[] = "<button class='button_op' onclick='delete_patient({$id}, {$chk}); return false;'><img src='image/b_delete.gif' align='absmiddle' title='ɾ��' alt=''></button>";
						}

						$op_per_line = 4;
						if (count($op) > $op_per_line) {
							$op_button = '';
							while (count($op) > 0) {
								$op_button .= '<div style="margin-top:3px; text-align:center;">' . implode("&nbsp;", array_slice($op, 0, $op_per_line)) . "</div>";
								$op = array_slice($op, $op_per_line);
							}
						} else {
							$op_button = implode("&nbsp;", $op);
						}

						if ($line["tel_location"] == "Array") {
							$line["tel_location"] = '';
						}

						$tel = tel_filter($line);

						$hide_line = ($pinfo && $pinfo["ishide"] && $line["isshow"] != 1) ? 1 : 0;

						$color_status = $line["status"];
						$today_begin = mktime(0, 0, 0);
						if ($color_status == 0 && $line["order_date"] < $today_begin) {
							$color_status = 3;
						}

						// 2014-8-11
						if ($line["status"] == 0 && $line["huifang"] != '' && $line["order_date"] > $today_begin) {
							/*
			if ($debug_mode) {
				echo date("Y-m-d H:i:s", $line["order_date"]);
				echo date("Y-m-d H:i:s", $today_begin);
				echo "<br>";
			}
			*/
							$color_status = 5; //�ѻط�
						}

						if ($line["status"] == 1 && $line["xiaofei_count"] > 0) {
							$color_status = 4; //������
						}

						$cur_color = $line_color[$color_status];

						// ԤԼ7���Ժ��
						if ($line["order_date"] - time() > 7 * 24 * 3600) {
							$cur_color = $day7_color;
						}

						$tds = array();
						//$tds["name"] = '<span><a href="javascript:void(0);" onclick="yuyue_card('.$id.',this)" title="�������ԤԼ��" style="color:'.$cur_color.'"><b><nobr>'.wee_wrap($line["name"], 6, '<br>')."</nobr></b></a></span>";
						$tds["name"] = '<b><nobr>' . wee_wrap($line["name"], 6, '<br>') . "</nobr></b>";
						if ($line["yuyue_num"] > 0) {
							$tds["name"] .= '<br><nobr title="ԤԼ��">' . $line["yuyue_num"] . '</nobr>';
						}
						$tds["sex"] = $line["sex"];
						$tds["age"] = $line["age"] > 0 ? $line["age"] : "";

						// ������Ϊ�գ��Һ��벻Ϊ�գ������ǹ̻����жϹ̻������� @ 2015-3-7
						if (trim($line["tel_location"]) == '' && $line["tel"] != '' && substr($line["tel"], 0, 1) == "0") {
							$quhao = substr($line["tel"], 0, 4);
							if ($sys_quhao[$quhao] != '') {
								$line["tel_location"] = $sys_quhao[$quhao];
							} else {
								$quhao = substr($line["tel"], 0, 3);
								$line["tel_location"] = $sys_quhao[$quhao];
							}
						}

						$tds["tel"] = ($tel ? ("<nobr>" . $tel . "</nobr><br>") : "") . ($line["tel_location"] ? ('<nobr>(' . $line["tel_location"] . ')</nobr>') : "");
						if ($line["is_fuzhen"]) {
							$tds["tel"] = "<b style='color:red'>[����]</b><br>" . $tds["tel"];
						}

						$tds["zhuanjia_num"] = $line["zhuanjia_num"];

						// 2016-3-29 ���֤��
						if ($debug_mode || $line["uid"] == $uid || $uinfo["show_card_id"]) {
							$tds["card_id"] = $line["card_id"];
						} else {
							$tds["card_id"] = $line["card_id"] ? (substr($line["card_id"], 0, -4) . "****") : "";
						}

						$content_arr = array();
						$line["content"] = _content_color(text_show(trim($line["content"])));

						// ΢�źź�QQ��ʾ����ǰ�� @ 2016-07-26
						if ($line["weixin"] != "" || $line["qq"] != "") {
							$content_add = array();
							if ($line["weixin"] != "") $content_add[] = "����΢�ţ�" . $line["weixin"];
							if ($line["qq"] != "") $content_add[] = "����QQ��" . $line["qq"];
							if (count($content_add)) {
								$line["content"] = "[" . implode("��", $content_add) . "]<br>" . $line["content"];
							}
						}


						if ($line["wish_doctor"]) {
							$line["content"] .= (trim($line["content"]) ? "<br>" : "") . '[����ָ��ҽ����' . $line["wish_doctor"] . ']';
						}
						if ($line["content"]) {
							$content_arr[] = '<table class="ct" cellpadding="0" cellspacing="0"><tr><td class="ct_td_a c_1"><nobr>����</nobr></td><td class="ct_td_b c_4">' . $line["content"] . '</td></tr></table>';
						}
						if ($line["memo"]) {
							$content_arr[] = '<table class="ct" cellpadding="0" cellspacing="0"><tr><td class="ct_td_a c_2"><nobr>��ע</nobr></td><td class="ct_td_b c_5">' . text_show(trim($line["memo"])) . '</td></tr></table>';
						}
						if ($line["huifang"]) {
							$line["huifang"] = str_replace("[�ط�����:", "[", $line["huifang"]);
							$content_arr[] = '<table class="ct" cellpadding="0" cellspacing="0"><tr><td class="ct_td_a c_3"><nobr>�ط�</nobr></td><td class="ct_td_b c_6">' . text_show(trim(strip_tags($line["huifang"]))) . '</td></tr></table>';
						}
						$tds["content"] = implode('<div class="hr_line"></div>', $content_arr);

						// �����ݽ��йؼ��ʹ��� @ 2014-8-23
						$hid_filter_arr = array(
							3 => "",
						);

						if ($line["status"] == 1 && $config["show_come_doctor"] != 1) {
							$tds["content"] = _content_filter($tds["content"], $hid_filter_arr[$hid]);
						}
						// end �ؼ��ʹ���

						$tds["order_date"] = wee_time($line["order_date"]);
						$tds["remain_time"] = ($line["order_date"] - time() > 0 ? ceil(($line["order_date"] - time()) / 24 / 3600) : '0');
						if ($line["disease_2"]) {
							$tds["disease_id"] = $line["disease_2"];
						} else {
							$tds["disease_id"] = $disease_id_name[$line["disease_id"]];
						}
						if ($line["media_from"] == "QQ") {
							$tds["media_from"] = $line["qq_from"] ? $line["qq_from"] : $line["media_from"];
						} else if ($line["media_from"] == "�绰") {
							$tds["media_from"] = $line["tel_from"] ? $line["tel_from"] : $line["media_from"];
						} else {
							$tds["media_from"] = $line["media_from"];
						}
						$tds["engine"] = $line["engine"];
						$tds["key_word"] = $line["key_word"];
						$tds["from_site"] = cut($line["from_site"], 20, "��");
						$tds["part_id"] = $part_id_name[$line["part_id"]];
						$tds["depart"] = $depart_id_name[$line["depart"]];
						$tds["account"] = $line["account"];
						$tds["tuiguangren"] = $line["tuiguangren"];

						$tds["yibao"] = $line["is_yibao"] ? "ҽ��" : "�Է�";

						$tds["suozaidi"] = "";
						if ($line["suozaidi"] == 1) {
							$tds["suozaidi"] = "����";
						}
						if ($line["suozaidi"] == 2) {
							$tds["suozaidi"] = "���";
						}
						if ($line["suozaidi"] == 3) {
							$tds["suozaidi"] = "����";
						}
						if ($line["suozaidi"] == 4) {
							$tds["suozaidi"] = "��˳";
						}
						if ($line["suozaidi"] == 5) {
							$tds["suozaidi"] = "�Ͻ�";
						}
						if ($line["suozaidi"] == 6) {
							$tds["suozaidi"] = "����";
						}
						if ($line["suozaidi"] == 7) {
							$tds["suozaidi"] = "����ˮ";
						}
						if ($line["suozaidi"] == 8) {
							$tds["suozaidi"] = "ǭ��";
						}
						if ($line["suozaidi"] == 9) {
							$tds["suozaidi"] = "ǭ����";
						}
						if ($line["suozaidi"] == 10) {
							$tds["suozaidi"] = "ǭ����";
						}
						if ($line["suozaidi"] == 11) {
							$tds["suozaidi"] = "ͭ��";
						}
						if ($line["suozaidi"] == 12) {
							$tds["suozaidi"] = "����";
						}

						$gj = '';
						if ($line["guiji"] != '') {
							if (array_key_exists($line["guiji"], $guiji_arr)) {
								$gj .= '<nobr>' . $guiji_arr[$line["guiji"]] . '</nobr>';
							}
							if ($line["qudao"] != '') {
								$gj .= "<br><nobr>" . $qudao_arr[$line["qudao"]] . "</nobr>";
							}
							if ($line["from_site"] != '') {
								$line["from_site"] = trim(trim(trim($line["from_site"]), ":"), "/");
								if (substr($line["from_site"], 0, 7) == "http://") {
									$line["from_site"] = substr($line["from_site"], 7);
								}
								$gj .= '<br><nobr title="' . $line["from_site"] . '">' . cut($line["from_site"], 16, "��") . '</nobr>';
							}
							if ($line["key_word"] != '') {
								$line["key_word"] = trim($line["key_word"], ":");
								$s = strlen($line["key_word"]) > 16 ? cut($line["key_word"], 16, "��") : $line["key_word"];
								$gj .= '<br><nobr title="' . $line["key_word"] . '">' . $s . '</nobr>';
							}
						}
						$tds["guiji"] = $gj;

						$tds["shichang"] = $line["shichang"];
						$tds["order_soft"] = $line["order_soft"] ? $web_soft_arr[$line["order_soft"]] : "";

						$_t = $line["huifang_nexttime"];
						$tds["huifang_time"] = $_t > 0 ? (substr($_t, 0, 4) . "-" . substr($_t, 4, 2) . "-" . substr($_t, 6, 2)) : "";

						// 2012-09-05
						$tds["xiaofei"] = '';
						if ($config["show_xiaofei"] > 0) {
							$line["xiaofei_count"] = str_replace(".0", "", $line["xiaofei_count"]);
							if ($line["xiaofei_count"] == 0) {
								$line["xiaofei_count"] = '';
							}
							if ($config["show_xiaofei"] == 1) {
								if ($line["author"] == $realname) {
									$tds["xiaofei"] = '<nobr>' . $line["xiaofei_count"] . '</nobr>';
								}
							} else if ($config["show_xiaofei"] == 2) {
								$tds["xiaofei"] = '<nobr>' . $line["xiaofei_count"] . '</nobr>';
							}
						}

						$tds["author"] = $line["author"];
						$tds["status"] = '<span id="status_' . $id . '">' . $status_array[$line["status"]] . '</span>';
						if ($line["status"] != 1) {
							$tds["status"] .= $line["track_status"] == -1 ? '<br><nobr style="color:gray">��������</nobr>' : '<br><nobr>��������</nobr>';
						}
						if ($line["is_jiancha"] != 0) {
							$tds["status"] .= $line["is_jiancha"] > 0 ? '<br><nobr>�Ѽ��</nobr>' : '<br><font color="gray"><nobr>δ���</nobr></font>';
						}
						if ($line["is_zhiliao"] != 0) {
							$tds["status"] .= $line["is_zhiliao"] == 1 ? '<br><nobr>������</nobr>' : '<br><font color="gray"><nobr>δ����</nobr></font>';
						}

						// ���ƴ�����ʱ�� ---------------------------- begin
						if ($hid == 15) {
							$s = '';
							$zhiliao_all = intval($line["zhiliao_all"]);
							$zhiliao_log = $line["zhiliao_log"];
							if ($zhiliao_all > 0) {
								$zhiliao_log = trim(str_replace("\r", "", $zhiliao_log));
								$_arr = explode("\n", $zhiliao_log);
								$zhiliao_arr = array();
								foreach ($_arr as $v) {
									list($_a, $_b) = explode("@", $v, 2);
									if ($_a && $_b) {
										$zhiliao_arr[intval($_a)] = $_b;
									}
								}
								$zhiliao_times = count($zhiliao_arr);

								if ($zhiliao_all > 10) {
									$s .= '<span class="zl_word" title="����' . $zhiliao_times . '��">' . $zhiliao_times . '</span>/<span class="zl_word" title="��' . $zhiliao_all . '��">' . $zhiliao_all . "</span><br>";
								} else {
									$w = 10 * $zhiliao_all;
									$s .= '<div class="zl_history"><nobr>';
									for ($n = 1; $n <= $zhiliao_all; $n++) {
										$_do = array_key_exists($n, $zhiliao_arr) ? 1 : 0;

										if (!$_do) {
											$s .= '<div class="zl_no" title="��' . $n . '��δ��"></div>';
										} else {
											$_time = $zhiliao_arr[$n];
											$_tips = "��" . $n . "�Σ�" . $_time;
											$s .= '<div class="zl_yes" onclick="alert(this.title)" title="' . $_tips . '"></div>';
										}
										if ($n < $zhiliao_all) {
											$s .= '<div class="zl_line"></div>';
										}
									}
									$s .= '<div class="zl_clear"></div>';
									$s .= '</nobr></div>';
								}
							}

							if ($s != '') {
								$tds["content"] .= $s;
							}
						}
						// ���ƴ�����ʱ�� ---------------------------- end

						$tds["doctor"] = ($line["xianchang_doctor"] ? ("<div title='�ֳ�ҽ��'><nobr>" . $line["xianchang_doctor"] . "</nobr></div>") : "") . ($line["doctor"] ? ("<div title='����ҽ��'><nobr>" . $line["doctor"] . "</nobr></div>") : "");

						$tds["addtime"] = wee_time($line["addtime"]);
						$tds["op"] = '<nobr>' . $op_button . '</nobr>';

					?>
						<tr<?php echo $hide_line ? " class='hide'" : ""; ?> id="list_line_<?php echo $id; ?>" style="color:<?php echo $cur_color; ?>">
							<?php foreach ($show_headers as $v) { ?>
								<td align="<?php echo $hds[$v]["align"]; ?>" class="item"><?php echo $tds[$v]; ?></td>
							<?php } ?>
							</tr>
					<?php
					}
				}
			} else {
					?>
					<tr>
						<td colspan="<?php echo count($hds); ?>" align="center" class="nodata">(û������...)</td>
					</tr>
				<?php } ?>
				<!-- ��Ҫ�б����� end -->

		</table>
	</form>
	<!-- �����б� end -->

	<!-- ��ҳ���� begin -->
	<div class="space"></div>
	<div class="footer_op">
		<div class="footer_op_left">&nbsp;��ҳ�� <span class="num"><b><?php echo $cur_page_line_count; ?></b></span> ��
			&nbsp;&nbsp; <font color="silver">ҳ��ִ��ʱ�䣺<?php echo round(now() - $pagebegintime, 4); ?>��</font>
		</div>
		<div class="footer_op_right">
			<?php echo pagelinkc($page, $pagecount, $count, wee_link_info($aLinkInfo, "page"), "button"); ?></div>
		<div class="clear"></div>
	</div>
	<!-- ��ҳ���� end -->

	<?php if ($searchword) { ?>
		<!-- �ؼ��ʸ��� -->
		<script>
			highlightWord(document.body, "<?php echo $searchword; ?>");
		</script>
	<?php } ?>

	<!-- <?php echo $s_sql; ?> -->

</body>

</html>