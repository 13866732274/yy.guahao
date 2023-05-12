<?php
/*
// 说明: 搜索配置
// 作者: 幽兰 (weelia@126.com)
// 时间: 2016-10-22
*/

$fields_arr = array(
	"h_name" => "医院名称",
	"part_id" => "客服所属部门",
	"name" => "患者姓名",
	"sex" => "患者性别",
	"age" => "患者年龄",
	"mobile" => "患者手机",
	"area" => "患者所在地",
	"qq" => "患者QQ",
	"weixin" => "患者微信",
	"order_qq" => "我方QQ",
	"order_weixin" => "我方微信",
	"swt_id" => "商务通永久身份",
	"zx_content" => "咨询内容",
	"talk_content" => "聊天内容",
	"disease_name" => "看诊疾病",
	"hf_log" => "回访内容",
	"huifang_num" => "回访次数",
	"laiyuan" => "资料来源",
	"u_name" => "添加人姓名",
	"track_status" => "跟踪状态",
	"to_weixin" => "是否转微信组",
	"wx_uname" => "微信对接人姓名",
	"wx_is_add" => "好友是否添加成功",
	"is_yuyue" => "是否预约",
	"is_come" => "是否到院",
);

$fields_dict = array(
	"part_id" => array(2 => "网络", 3 => "电话"),
	"track_status" => array(-1 => "放弃", 0 => "继续"),
	"to_weixin" => array(1 => "是", 0 => "否"),
	"wx_is_add" => array(1 => "是", 0 => "否"),
	"is_yuyue" => array(1 => "是", 0 => "否"),
	"is_come" => array(1 => "是", 0 => "否"),
);


$search_mode = array(
	"like" => "模糊查询",
	"deng" => "等于",
	"da" => "大于",
	"xiao" => "小于",
	"dadeng" => "大于等于",
	"xiaodeng" => "小于等于",
	"in" => "包含",
	"budeng" => "不等于",
	"bukong" => "不为空",
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
				$show_str[] = $f_name."含有：".wee_sql_safe($sou_def["v"]);
				$sou_def["v"] = '"%'.wee_sql_safe($sou_def["v"]).'%"';
			} else if ($sou_def["c"] == "in") {
				if (is_array($fields_dict[$sou_def["f"]])) {
					$fv = array();
					foreach ($sou_def["v"] as $v) {
						$fv[] = array_key_exists($v, $fields_dict[$sou_def["f"]]) ? $fields_dict[$sou_def["f"]][$v] : wee_sql_safe($v);
					}
					$show_str[] = $f_name."：".implode("、", $fv);
				} else {
					$show_str[] = $f_name."包含[".(is_array($sou_def["v"]) ? implode("、", $sou_def["v"]) : $sou_def["v"])."]";
				}
				if (!is_array($sou_def["v"])) {
					$sou_def["v"] = str_replace("，", ",", $sou_def["v"]);
					$sou_def["v"] = explode(",", $sou_def["v"]);
				}
				foreach ($sou_def["v"] as $k => $v) {
					$sou_def["v"][$k] = '"'.trim(wee_sql_safe($v)).'"';
				}
				$sou_def["v"] = "(".@implode(",", $sou_def["v"]).")";
			} else if ($sou_def["c"] == "deng" || $sou_def["c"] == "budeng") {
				$show_str[] = $f_name.$search_mode[$sou_def["c"]]."：".wee_sql_safe($sou_def["v"]);
				$sou_def["v"] = '"'.wee_sql_safe($sou_def["v"]).'"';
			} else if ($sou_def["c"] == "bukong") {
				$sou_def["v"] = "('','0')";
				$show_str[] = $f_name."：不为空";
			} else {
				$show_str[] = $f_name.$search_mode[$sou_def["c"]]."：".intval($sou_def["v"]);
				$sou_def["v"] = intval($sou_def["v"]);
			}
			$where[] = $sou_def["f"].$search_mode_2[$sou_def["c"]].$sou_def["v"];
		}
	}
	return array($where, $show_str);
}


// 关键词 安全过滤
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