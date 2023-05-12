<?php
//
// - ����˵�� : ��ҳ
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2009-10-01 => 2013-01-07
//
require "lib/set_env.php";
define("BRDD_MAIN", 1);

// �Ƿ���ʾcome list���ܱ�Ŀ���
$show_list_all = $config["show_list_all"] ? 1 : 0;
if ($debug_mode) {
	$show_list_all = 1;
}


// �ر��������� @ 2016-10-13
if ($op == "hide_search") {
	$index_config = @unserialize($uinfo["index_config"]);
	$index_config["hide_search"] = 1;
	$config_str = serialize($index_config);
	if ($uid > 0) {
		$db->query("update sys_admin set index_config='" . $config_str . "' where id='" . $uid . "' limit 1");
	} else {
		$_SESSION["index_config"] = $config_str;
	}
	echo '<script> alert("���������Ѿ��رգ�������Ҫ�������ڡ���ҳ���ơ��д򿪡�"); self.location = "?"; </script>';
	exit;
}


// �л�ҽԺ
$_hid = $_GET["_tohid_"];
if (isset($_hid) && $_hid != '') {
	// �л�Ȩ�޼��:
	if ($_hid == "all") {
		$_SESSION[$cfgSessionName]["hospital_id"] = "";
	} else {
		if (@in_array($_hid, $hospital_ids)) {
			$_SESSION[$cfgSessionName]["hospital_id"] = intval($_hid);
		}
	}
	header("location:main.php");
	exit;
}

$db->select_count = 0;

// ����֪ͨ�б�:
$notice_arr = array();

$manage_parts = $uinfo["part_manage"] ? explode(",", $uinfo["part_manage"]) : array();


$hid = $user_hospital_id;
$hid_str = trim(implode(',', $hospital_ids));
$hid_str = $hid_str == "" ? "0" : $hid_str;
if ($hid_str != '0') {
	$hospital_list = $db->query("select id,name,color from hospital where ishide=0 and id in (" . $hid_str . ") order by sort desc, name asc", 'id');
} else {
	$hospital_list = array();
}

// �Զ��л�����һ������ @ 2016-10-13
if (!$show_list_all && $_SESSION[$cfgSessionName]["hospital_id"] == "" && count($hospital_list) > 0) {
	$_all_hids = array_keys($hospital_list);
	$hid = $user_hospital_id = $_SESSION[$cfgSessionName]["hospital_id"] = $_all_hids[0];
}

$h_name = '';
if ($hid > 0) {
	$h_name = $hospital_list[$hid]["name"];
	$h_info = $db->query("select * from hospital where id=$hid limit 1", 1);
}

// ����id => name����:
$part_id_name = $db->query("select id,name from sys_part", 'id', 'name');
$character_id_name = $db->query("select id,name from sys_character", 'id', 'name');

if ($debug_mode) {
	$index_config = (array) @unserialize($_SESSION["index_config"]);
	if (!isset($index_config["global_hide"])) {
		$index_config["global_hide"] = array("youhua", "yibao");
	}
} else {
	$index_config = (array) @unserialize($uinfo["index_config"]);
}


if ($hid > 0) {
	$disease_id_name = $db->query("select id,name from disease where hospital_id=$hid ", "id", "name");
	$depart_id_name = $db->query("select id,name from depart where hospital_id=$hid order by id asc", "id", "name");
}


// �������������
$hids = count($hospital_ids) > 0 ? implode(",", $hospital_ids) : "0";
$group_id_name = $db->query("select id,name from hospital_group order by sort desc, name asc", "id", "name");
$options = array();
foreach ($group_id_name as $_gid => $_gname) {
	$h_list = $db->query("select id,name,color from hospital where ishide=0 and group_id=$_gid and id in ($hids) order by sort desc, name asc", "id");
	if (count($h_list) > 0) {
		$options[] = array('-1', $_gname . " (" . count($h_list) . ')', 'color:red');
		foreach ($h_list as $_hid => $_arr) {
			$options[] = array($_hid, '��' . $_arr["name"], ($_arr["color"] ? ('color:' . $_arr["color"]) : 'color:blue'));
		}
	}
}


// ʱ�䶨�� 2011-12-28:
// ʱ�����ʼ�㶼�� YYYY-MM-DD 00:00:00 �������� YYYY-MM-DD 23:59:59
$today_tb = mktime(0, 0, 0); //���쿪ʼ
$today_te = strtotime("+1 day", $today_tb) - 1; //�������

$tomorrow_tb = $today_te + 1; //���쿪ʼ
$tomorrow_te = strtotime("+1 day", $tomorrow_tb) - 1; //�������

$yesterday_tb = strtotime("-1 day", $today_tb); //���쿪ʼ
$yesterday_te = $today_tb - 1; //�������

$month_tb = mktime(0, 0, 0, date("m"), 1); //���¿�ʼ
$month_te = strtotime("+1 month", $month_tb) - 1; //���½���

$lastmonth_tb = strtotime("-1 month", $month_tb); //���¿�ʼ
$lastmonth_te = $month_tb - 1; //���½���

$tb_tb = strtotime("-1 month", $month_tb); //ͬ��ʱ�俪ʼ
$tb_te = strtotime("-1 month", time()); //ͬ��ʱ�����
if (date("d", $tb_te) != date("d")) {
	$tb_te = $month_tb - 1;
}

// ȥ��ͬ�±�
$yb_tb = strtotime("-1 year", $month_tb);
$_days = get_month_days(date("Y-m", $yb_tb));
if (date("j") > $_days) { //��ǰ�����Ѿ�����ȥ��ͬ�µ�������(����29�պ�ȥ���28��)
	$yb_te = strtotime(date("Y-m-", $yb_tb) . $_days . date(" 23:59:59")); //�Ա�Ϊȥ��ͬ�µ�����
} else {
	$yb_te = strtotime(date("Y-m-", $yb_tb) . date("d H:i:s"));
}

// ���ݲ�ѯ���մ����鶨��
$time_arr = array(
	"����" => array($today_tb, $today_te),
	"����" => array($yesterday_tb, $yesterday_te),
	"����" => array($month_tb, $today_te),
	"ͬ��" => array($yb_tb, $yb_te),
	"����" => array($lastmonth_tb, $lastmonth_te),
);

// ��ҳժҪ���ݣ�
$summary_info = '���ã�<font color="#FF0000"><b>' . $realname . '</b></font>';
if ($uinfo["hospitals"] || $uinfo["part_id"] > 0) {
	if ($uinfo["part_id"] > 0) {
		$summary_info .= ' ��ݣ�' . $part_id_name[$uinfo["part_id"]] . "";
	}
	if ($uinfo["character_id"] > 0) {
		$summary_info .= ' Ȩ�ޣ�' . $character_id_name[$uinfo["character_id"]];
	}
}
$summary_info .= '������ <font color="red"><b>' . date("Y-m-d") . '</b></font>';
$summary_info .= '������<b><font color="red">' . substr("��һ����������", date("w") * 2, 2) . '</font></b>';
if (in_array($uinfo["part_id"], array(1, 9)) || $uinfo["part_admin"] || $debug_mode) {
	$onlines = $db->query("select count(*) as count from sys_admin where online=1", 1, "count");
	$summary_info .= '���������� <font color="red"><b>' . $onlines . '</b></font> ��';
}

if ($debug_mode) {
	//$summary_info .= '<br><br><b style="color:#1696c7;font-size:14px;">����ҵ֪ͨ��6��12����22:30~1:30ͣ�磬��ʱ���������޷����ʣ�����ǰ����׼����</b>';
}


// �Ƿ��Ѿ�ѡ����ҽԺ
if ($hid > 0) {

	$table = "patient_" . $hid;

	// ��������
	$data = $db->query("select data from index_cache where hid=$hid limit 1", 1, "data");
	$module_data_arr = @unserialize($data);

	// --------------------------

	//�Ż�ͳ������ @ 2013-03-15
	$isshow_youhua = 0;
	if ($config["show_youhua"]) {
		// ���ݸ���:
		$db->query("update youhua_data set yuyue=x1_yuyue+x2_yuyue+x3_yuyue+x4_yuyue+x5_yuyue+x6_yuyue+x7_yuyue+x8_yuyue+x9_yuyue where yuyue=0");
		$db->query("update youhua_data set daoyuan=x1_daoyuan+x2_daoyuan+x3_daoyuan+x4_daoyuan+x5_daoyuan+x6_daoyuan+x7_daoyuan+x8_daoyuan+x9_daoyuan where daoyuan=0");

		// ��ѯ��ҽԺ�Ƿ��������������:
		$begin_date = date("Ymd", $lastmonth_tb);
		$end_date = date("Ymd");

		$data_count = $db->query("select sum(yuyue)+sum(daoyuan) as c from youhua_data where hid=$hid and date>=$begin_date and date<=$end_date", 1, "c");
		if ($data_count > 0) {
			$isshow_youhua = 1;
		}
	}

	if ($config["show_zixun_confirm_come"] || $debug_mode) {
		// ���� ��ѯȷ�ϵ�Ժ���ݣ�
		$last_month = date("Ym", strtotime("-1 month", strtotime(date("Y-m-01 0:0:0"))));
		$come_confirm_data = $db->query("select * from come_confirm where hid=$hid and month='$last_month' limit 1", 1);

		// ������ѯȷ������:
		$show_web_confirm = 0;
		if ($come_confirm_data["web"] != '') {
			$show_web_confirm = 1;
			$lastmonth_web_confirm = $come_confirm_data["web"];
		}

		// �绰��ѯȷ������:
		$show_tel_confirm = 0;
		if ($come_confirm_data["tel"] != '') {
			$show_tel_confirm = 1;
			$lastmonth_tel_confirm = $come_confirm_data["tel"];
		}

		// ������ȷ�ϵ�Ժ���ݣ�
		$show_all_confirm = 0;
		if ($come_confirm_data["all_come"] != '') {
			$show_all_confirm = 1;
			$lastmonth_all_confirm = $come_confirm_data["all_come"];
		}

		// ͬ��
		$zx_cfm_month = strtotime("-1 month", $yb_tb);
		$tb_int_month = date("Ym",  $zx_cfm_month);
		$zx_cfm_month_show = date("Y��n��",  $zx_cfm_month);
		$tb_come_confirm_data = $db->query("select * from come_confirm where hid=$hid and month='$tb_int_month' limit 1", 1);
		$tb_come_all = $tb_come_confirm_data["all_come"] > 0 ? $tb_come_confirm_data["all_come"] : "��";
		$tb_come_web = $tb_come_confirm_data["web"] > 0 ? $tb_come_confirm_data["web"] : "��";
		$tb_come_tel = $tb_come_confirm_data["tel"] > 0 ? $tb_come_confirm_data["tel"] : "��";
	} else {
		$show_all_confirm = 0;
		$show_web_confirm = 0;
		$show_tel_confirm = 0;
		$tb_come_all = 0;
		$tb_come_web = 0;
		$tb_come_tel = 0;
	}
}


// �������ܵ�ͼ��:
$new_icon = ' <img src="image/new.gif" align="absmiddle"> ';

// $notice_arr[] = '<font color="red">QQͳ������Ҳ������ʾ�ˣ����ڡ���ҳ���á��й�ѡ������ʾ��</font>'.$new_icon."<br>";

// $notice_arr[] = '��С�˺ŵ������Ѿ����������ڡ���ҳ���á��й�ѡ������ʾ��<br>';

//$notice_arr[] = '����ͳ�Ƶ�����Ϊֹ�����ݣ������7�ţ���ͳ�Ƶ�1��~7�����ݣ�<br>';

// ������ѯ������: ��ѯ������ = ʵ�ʵ�Ժ���� / �ܵ��
if ($hid > 0) {
	$xiangmu_arr = $db->query("select id,name,kefu from count_type where type='web' and hid=$hid and ishide=0 order by id asc", "id");
	if (count($xiangmu_arr) > 0) {
		$t_m_begin = mktime(0, 0, 0, date("m"), 1); // ���µĿ�ʼ:
		$t_m_end = strtotime("+1 month", $t_m_begin) - 1; //���½���

		$_m_begin = date("Ymd", $t_m_begin);
		$_m_end = date("Ymd", $t_m_end);

		// ��ѯ���:
		$zixun_jiuzhen_arr = array();
		foreach ($xiangmu_arr as $xmid => $v) {
			$rs = $db->query("select sum(click) as click, sum(come) as come from count_web where type_id=$xmid and date>=$_m_begin and date<=$_m_end", 1);
			$zixun_jiuzhen_arr[$xmid] = @round(100 * $rs["come"] / $rs["click"], 1);
		}

		$zixun_jiuzhen_str = array();
		foreach ($zixun_jiuzhen_arr as $k => $v) {
			$zixun_jiuzhen_str[] = $xiangmu_arr[$k]["name"] . ': <a href="count_web.php?type_id=' . $k . '&op=change_type" title="�������鿴"><font color="red">' . $v . "%</font></a> ";
		}
		$notice_arr[] = "<b>������ѯ������</b>��" . implode(" ", $zixun_jiuzhen_str);

		// ���ԤԼ��:
		$dianji_yuyue_arr = array();
		foreach ($xiangmu_arr as $xmid => $v) {
			$rs = $db->query("select sum(click) as a, sum(talk_swt) as b from count_web where type_id=$xmid and date>=$_m_begin and date<=$_m_end", 1);
			$dianji_yuyue_arr[$xmid] = @round(100 * $rs["b"] / $rs["a"], 1);
		}

		$dianji_yuyue_str = array();
		foreach ($dianji_yuyue_arr as $k => $v) {
			$dianji_yuyue_str[] = $xiangmu_arr[$k]["name"] . ': <a href="count_web.php?type_id=' . $k . '&op=change_type" title="�������鿴"><font color="red">' . $v . "%</font></a> ";
		}
		$notice_arr[] = "<b>������ѯԤԼ��</b>��" . implode(" ", $dianji_yuyue_str);
	}
}


// 2013-8-30 ��������
if ($hid > 0) {
	//if (($debug_mode || $config["show_qihua_detail"])) {
	$arr = array();
	$arr[] = "����ԤԼ������: <font color=red>" . (@round($module_data_arr["ID_22"]["ʵ��"]["����"] / $module_data_arr["ID_22"]["Ԥ��"]["����"], 3) * 100) . "%</font>";
	$arr[] = "����ԤԼ������: <font color=red>" . (@round($module_data_arr["ID_20"]["ʵ��"]["����"] / $module_data_arr["ID_20"]["Ԥ��"]["����"], 3) * 100) . "%</font>";
	$arr[] = "��������ԤԼ������: <font color=red>" . (@round($module_data_arr["ID_8"]["ʵ��"]["����"] / $module_data_arr["ID_8"]["Ԥ��"]["����"], 3) * 100) . "%</font>";
	$arr[] = "�绰����ԤԼ������: <font color=red>" . (@round($module_data_arr["ID_23"]["ʵ��"]["����"] / $module_data_arr["ID_23"]["Ԥ��"]["����"], 3) * 100) . "%</font>";
	$notice_arr[] = implode(" &nbsp;", $arr) . "��(<a href='javascript:;' onclick='jiuzhenlv_huizong();'>��˲鿴��Ժ����</a>)";
	//$notice_arr[] = '����=������ѯ��ý����Դ�����磻 ����=�绰��ѯ��ý����Դ������';
	//}
}

if ($hid == 0 && $show_list_all) {
	include "main_list_all.php";
} else {
	include "main_directly.php";
}