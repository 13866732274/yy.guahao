<?php
/*
// ˵��: ����������ݶ���
// ����: ���� (weelia@126.com)
*/

if (!$debug_mode) {
	//exit("������������Ժ�ʹ�á�");
}

if (empty($hid)) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

$table = "patient_{$hid}";
if ($_GET["op"] == "report") {
	//----------
}

$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

// ͳ�ƽ������
$type_arr = array(1 => "����ͳ��", 2 => "����ͳ��", /*3=>"����ͳ��(ʵ����)",*/ 4 => "����ͳ��", 5 => "��ʱ���ͳ��");

// ����
$part_arr = array(2 => "����", 3 => "�绰");

// ��Ժ״̬
$come_arr = array(1 => "�ѵ�", 2 => "δ��");

// �ʺ�:
$account_arr = $account_array; //ʹ��ϵͳ����

// ʱ���ʽ
$timetype_arr = array("order_date" => "��Ժʱ��", "addtime" => "���ʱ��");

// �ͷ� ���淽��Ч�ʲ���ǰ�����õĺ� (�������Ա����ȡ������Щ�ͷ���ְ��ɾ���ˣ��������ݻ���):
if ($need_kf_arr) {
	$kf_arr = $db->query("select author,count(author) as c from $table where addtime>" . strtotime("-1 year") . " group by author order by c desc", "author", "c");
}

// ý����Դ:
$media_arr = array("����", "�绰"); //����ý����Ժ
$media_arr2 = $db->query("select name from media where (hospital_id=0 or hospital_id=$hid) order by sort desc,id asc", "", "name");
$media_arr = array_merge($media_arr, $media_arr2);

// ���� ��,�� ����
$y_array = $m_array = $d_array = array();
for ($i = date("Y"); $i >= (date("Y") - 3); $i--) $y_array[] = $i;
for ($i = 1; $i <= 12; $i++) $m_array[] = $i;
for ($i = 1; $i <= 31; $i++) $d_array[] = $i;


/*
// �Բ�ѯ�����Ĵ���------------------------
*/
if ($_GET["op"] == "report") {

	// ��������¼�� session����ļ�������ʹ��ͬ������
	$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;

	// ͳ�ƽ������:
	$type = noe($_GET["type"], 1);
	$type_tips = $type_arr[$type];


	$final_dt_arr = array(); //����ʱ����ֹ���key�����飬��������ѭ����ѯ�����
	if ($type == 1) { //��
		$y_begin = $_GET["b_year"];
		$y_end = $_GET["e_year"];

		// ���ʱ�䷶Χ:
		$tb = $max_tb = strtotime("{$y_begin}-01-01 00:00:00");
		$te = $max_te = strtotime("{$y_end}-12-31 23:59:59");

		$y_arr = array();
		if ($y_begin == $y_end) {
			$y_arr = array($y_begin);
			$final_dt_arr[$y_begin] = array(strtotime("{$y_begin}-01-01 00:00:00"), strtotime("{$y_begin}-12-31 23:59:59"));
		} else {
			for ($i = $y_end; $i >= $y_begin; $i--) {
				$y_arr[] = $i;
				$final_dt_arr[$i] = array(strtotime("{$i}-01-01 00:00:00"), strtotime("{$i}-12-31 23:59:59"));
			}
		}
	} else if ($type == 2) { //��
		$m_begin = $_GET["b_year"] . "-" . str_pad($_GET["b_month"], 2, '0', STR_PAD_LEFT);
		$m_end = $_GET["e_year"] . "-" . str_pad($_GET["e_month"], 2, '0', STR_PAD_LEFT);

		// ���ʱ�䷶Χ:
		$tb = $max_tb = strtotime("{$m_begin}-01 00:00:00");
		$te = $max_te = strtotime("+1 month", strtotime("{$m_end}-01 00:00:00")) - 1;

		$m_arr = array();
		if ($m_begin == $m_end) {
			$m_arr = array($m_begin);
			$final_dt_arr[$m_begin] = array(strtotime("{$m_begin}-01 00:00:00"), (strtotime("+1 month", strtotime("{$m_begin}-01 00:00:00") - 1)));
		} else {
			$tmp = 0;
			do {
				////��Ǳ�����⣬�������ѡ��Ϊ31�գ�-1 month�ͻ���������
				// �޸� 2012-01-31
				$m_arr[] = $_dt = date("Y-m", strtotime("-" . $tmp . " month", mktime(0, 0, 0, date("m", $te), 1, date("Y", $te))));
				$final_dt_arr[$_dt] = array(strtotime("{$_dt}-01 00:00:00"), (strtotime("+1 month", strtotime("{$_dt}-01 00:00:00")) - 1));
				$tmp++;
				if ($tmp > 24) {
					$html_tip .= "����ͳ��Ŀǰ�޶����ܳ���25���£��������Զ�������";
					$tb = $max_tb = strtotime("{$_dt}-01 00:00:00");
					$_GET["b_year"] = date("Y", $tb);
					$_GET["b_month"] = date("n", $tb);
					$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;
					break; //�������x����
				}
			} while (intval(str_replace("-", "", $_dt)) > intval(str_replace("-", "", $m_begin)));
		}
	} else if ($type == 3) { //��
		exit_html("����ͳ�ƹ����Ӻ󿪷���....");
	} else if ($type == 4) { //��
		$d_begin = $_GET["b_year"] . "-" . str_pad($_GET["b_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["b_day"], 2, '0', STR_PAD_LEFT);
		$d_end = $_GET["e_year"] . "-" . str_pad($_GET["e_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["e_day"], 2, '0', STR_PAD_LEFT);

		// ���ʱ�䷶Χ:
		$tb = $max_tb = strtotime("{$d_begin} 00:00:00");
		$te = $max_te = strtotime("{$d_end} 23:59:59");

		// ����ѡ��Ĵ������⣬����2�±���û��31�ţ�ȴѡ����31�ŵ����
		$html_tip = "";
		if (date("Y-m-d", $tb) != $d_begin) {
			$html_tip .= "��ʼʱ��" . $_GET["b_year"] . "��" . $_GET["b_month"] . "��û��" . $_GET["b_day"] . "�ţ����Զ�������";
			$_GET["b_day"] = date("j", strtotime("+1 month", mktime(0, 0, 0, $_GET["b_month"], 1, $_GET["b_year"])) - 1);
			$d_begin = $_GET["b_year"] . "-" . str_pad($_GET["b_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["b_day"], 2, '0', STR_PAD_LEFT);
			$tb = $max_tb = strtotime("{$d_begin} 00:00:00");
			$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;
		}
		if (date("Y-m-d", $te) != $d_end) {
			$html_tip .= "����ʱ��" . $_GET["e_year"] . "��" . $_GET["e_month"] . "��û��" . $_GET["e_day"] . "�ţ����Զ�������";
			$_GET["e_day"] = date("j", strtotime("+1 month", mktime(0, 0, 0, $_GET["e_month"], 1, $_GET["e_year"])) - 1);
			$d_end = $_GET["e_year"] . "-" . str_pad($_GET["e_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["e_day"], 2, '0', STR_PAD_LEFT);
			$te = $max_te = strtotime("{$d_end} 00:00:00");
			$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;
		}

		$d_arr = array();
		if ($d_begin == $d_end) {
			$d_arr = array($d_begin);
			$final_dt_arr[$d_begin] = array(strtotime("{$d_begin} 00:00:00"), strtotime("{$d_begin} 23:59:59"));
		} else {
			$tmp = 0;
			do {
				$d_arr[] = $_dt = date("Y-m-d", strtotime("-" . $tmp . " day", $te));
				$final_dt_arr[$_dt] = array(strtotime("{$_dt} 00:00:00"), strtotime("{$_dt} 23:59:59"));
				$tmp++;
				if ($tmp > 31) {
					$html_tip .= "����ͳ��Ŀǰ�޶����ܳ���32�죬�������Զ�������";
					$tb = $max_tb = strtotime("{$_dt} 00:00:00");
					$_GET["b_year"] = date("Y", $tb);
					$_GET["b_month"] = date("n", $tb);
					$_GET["b_day"] = date("j", $tb);
					$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;
					break; //�������x��
				}
			} while (intval(str_replace("-", "", $_dt)) > intval(str_replace("-", "", $d_begin)));
		}
	} else if ($type == 5) { //ʱ��(����)
		$d_begin = $_GET["b_year"] . "-" . str_pad($_GET["b_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["b_day"], 2, '0', STR_PAD_LEFT);
		$d_end = $_GET["e_year"] . "-" . str_pad($_GET["e_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["e_day"], 2, '0', STR_PAD_LEFT);

		// ���ʱ�䷶Χ:
		$tb = $max_tb = strtotime("{$d_begin} 00:00:00");
		$te = $max_te = strtotime("{$d_end} 23:59:59");

		$sd_arr = array();
		for ($i = 6; $i <= 23; $i++) {
			$sd_arr[] = $i;
			$final_dt_arr[$i . "~" . ($i + 1)] = $i;
		}
	}


	// ʱ������:
	$timetype = noe($_GET["timetype"], "order_date");
	$timetype_tips = $timetype_arr[$timetype];


	// �����޶���
	$w = array();
	if ($_GET["part"]) {
		$w[] = "part_id=" . intval($_GET["part"]);
	}
	if ($_GET["media"]) {
		$w[] = "media_from='" . $_GET["media"] . "'";
	}
	if ($_GET["come"]) {
		if ($_GET["come"] == 1) {
			$w[] = "status=1";
		} else {
			$w[] = "status!=1";
		}
	}
	if ($_GET["account"]) {
		$w[] = "account='" . $_GET["account"] . "'";
	}

	$where = '';
	if (count($w) > 0) {
		$where = implode(" and ", $w) . " and ";
	}
}