<?php
/*
// ˵��: ��������
// ����: ���� (weelia@126.com)
// ʱ��: 2016-10-22
*/

$fields_arr = array(
	"h_name" => "ҽԺ����",
	"part_id" => "�ͷ���������",
	"name" => "��������",
	"sex" => "�����Ա�",
	"age" => "��������",
	"mobile" => "�����ֻ�",
	"area" => "�������ڵ�",
	"qq" => "����QQ",
	"weixin" => "����΢��",
	"order_qq" => "�ҷ�QQ",
	"order_weixin" => "�ҷ�΢��",
	"swt_id" => "����ͨ�������",
	"zx_content" => "��ѯ����",
	"talk_content" => "��������",
	"disease_name" => "���Ｒ��",
	"hf_log" => "�ط�����",
	"huifang_num" => "�طô���",
	"laiyuan" => "������Դ",
	"u_name" => "���������",
	"track_status" => "����״̬",
	"to_weixin" => "�Ƿ�ת΢����",
	"wx_uname" => "΢�ŶԽ�������",
	"wx_is_add" => "�����Ƿ���ӳɹ�",
	"is_yuyue" => "�Ƿ�ԤԼ",
	"is_come" => "�Ƿ�Ժ",
);

$fields_dict = array(
	"part_id" => array(2 => "����", 3 => "�绰"),
	"track_status" => array(-1 => "����", 0 => "����"),
	"to_weixin" => array(1 => "��", 0 => "��"),
	"wx_is_add" => array(1 => "��", 0 => "��"),
	"is_yuyue" => array(1 => "��", 0 => "��"),
	"is_come" => array(1 => "��", 0 => "��"),
);


$search_mode = array(
	"like" => "ģ����ѯ",
	"deng" => "����",
	"da" => "����",
	"xiao" => "С��",
	"dadeng" => "���ڵ���",
	"xiaodeng" => "С�ڵ���",
	"in" => "����",
	"budeng" => "������",
	"bukong" => "��Ϊ��",
);

$search_mode_2 = array(
	"like" => " like ",
	"deng" => "=",
	"da" => ">",
	"xiao" => "<",
	"dadeng" => ">=",
	"xiaodeng" => "<=",
	"in" => " in ",
	"budeng" => "!=",
	"bukong" => " not in ",
);


function wee_build_high_search_sql($sou_set) {
	global $fields_arr, $search_mode, $search_mode_2, $fields_dict;
	$where = $show_str = array();
	foreach ($sou_set as $sou_id => $sou_def) {
		if ($sou_def["f"] != "") {
			$f_name = $fields_arr[$sou_def["f"]];
			if ($sou_def["c"] == "like") {
				$show_str[] = $f_name."���У�".wee_sql_safe($sou_def["v"]);
				$sou_def["v"] = '"%'.wee_sql_safe($sou_def["v"]).'%"';
			} else if ($sou_def["c"] == "in") {
				if (is_array($fields_dict[$sou_def["f"]])) {
					$fv = array();
					foreach ($sou_def["v"] as $v) {
						$fv[] = array_key_exists($v, $fields_dict[$sou_def["f"]]) ? $fields_dict[$sou_def["f"]][$v] : wee_sql_safe($v);
					}
					$show_str[] = $f_name."��".implode("��", $fv);
				} else {
					$show_str[] = $f_name."����[".(is_array($sou_def["v"]) ? implode("��", $sou_def["v"]) : $sou_def["v"])."]";
				}
				if (!is_array($sou_def["v"])) {
					$sou_def["v"] = str_replace("��", ",", $sou_def["v"]);
					$sou_def["v"] = explode(",", $sou_def["v"]);
				}
				foreach ($sou_def["v"] as $k => $v) {
					$sou_def["v"][$k] = '"'.trim(wee_sql_safe($v)).'"';
				}
				$sou_def["v"] = "(".@implode(",", $sou_def["v"]).")";
			} else if ($sou_def["c"] == "deng" || $sou_def["c"] == "budeng") {
				$show_str[] = $f_name.$search_mode[$sou_def["c"]]."��".wee_sql_safe($sou_def["v"]);
				$sou_def["v"] = '"'.wee_sql_safe($sou_def["v"]).'"';
			} else if ($sou_def["c"] == "bukong") {
				$sou_def["v"] = "('','0')";
				$show_str[] = $f_name."����Ϊ��";
			} else {
				$show_str[] = $f_name.$search_mode[$sou_def["c"]]."��".intval($sou_def["v"]);
				$sou_def["v"] = intval($sou_def["v"]);
			}
			$where[] = $sou_def["f"].$search_mode_2[$sou_def["c"]].$sou_def["v"];
		}
	}
	return array($where, $show_str);
}


// �ؼ��� ��ȫ����
function wee_sql_safe($key) {
	$key = strip_tags($key);
	$key = str_replace("\\", "", $key);
	$key = str_replace("/", "", $key);
	$key = str_replace("%", "", $key);
	$key = str_replace("*", "", $key);
	$key = str_replace("'", "", $key);
	$key = str_replace('"', "", $key);
	$key = str_replace(";", "", $key);
	$key = str_replace("=", "", $key);
	$key = str_ireplace("union", "", $key);
	$key = str_ireplace("like", "", $key);
	$key = str_ireplace("and", "", $key);
	$key = str_ireplace("or", "", $key);
	$key = str_replace("<", "", $key);
	$key = str_replace(">", "", $key);
	$key = str_replace("{", "", $key);
	$key = str_replace("}", "", $key);
	$key = str_replace("(", "", $key);
	$key = str_replace(")", "", $key);

	return $key;
}


?>