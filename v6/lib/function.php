<?php
// --------------------------------------------------------
// - 功能说明 : 管理后台 函数库
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-03-30 12:20
// --------------------------------------------------------

// 组合为  键名：键值 格式 如有非正常格式，用base64编码
function wee_array_to_string($arr, $skip_empty_value = 1, $key_pre_add = '')
{
	$pre_fill_char = "　";
	$spec_char     = array('"', "'", "\n", "\r", "\t", ":");
	$str_arr       = array();

	foreach ($arr as $k => $v) {
		if ($skip_empty_value && empty($v)) continue;
		$k = trim($k);
		if ($key_pre_add) $k = $key_pre_add . $k;
		$spec_count = 0;
		foreach ($spec_char as $char) {
			$spec_count += substr_count($k, $char);
		}
		if ($spec_count > 0) {
			$k = "base64_" . base64_encode($k);
		}
		if (is_array($v)) {
			$str_arr[] = wee_array_to_string($v, $skip_empty_value, $k . "#");
		} else {
			$v          = trim($v);
			$spec_count = 0;
			foreach ($spec_char as $char) {
				$spec_count += substr_count($v, $char);
			}
			if ($spec_count > 0) {
				$v = "base64_" . base64_encode($v);
			}
			$str_arr[] = $k . ":" . $v;
		}
	}
	return implode("\r\n", $str_arr);
}


// 最多支持3层数组
function wee_string_to_array($str)
{
	$str = str_replace("\r", "", $str);
	$str = trim($str);
	if ($str == '') return array();
	$str_arr = explode("\n", $str);
	$out     = array();
	foreach ($str_arr as $v) {
		list($k, $v) = explode(":", $v, 2);
		if (substr($k, 0, 7) == "base64_") {
			$k = @base64_decode(substr($k, 7));
		}
		if (substr($v, 0, 7) == "base64_") {
			$v = @base64_decode(substr($v, 7));
		}
		if (substr_count($k, "#") == 1) {
			list($k1, $k2) = explode("#", $k, 2);
			$out[$k1][$k2] = $v;
		} else if (substr_count($k, "#") == 2) {
			list($k1, $k2, $k3) = explode("#", $k, 3);
			$out[$k1][$k2][$k3] = $v;
		} else {
			$out[$k] = $v;
		}
	}
	return $out;
}


function wee_time($int_time)
{
	if ($int_time <= 0) {
		return '';
	}

	global $uinfo;
	if ($uinfo["date_mode"] == 1) {
		return nl2br(date("Y-m-d\nH:i", $int_time));
	}

	$d = date("Ymd", $int_time);

	// 检查是不是最近几天，如果是，格式化为易读模式
	if ($d == date("Ymd")) {
		return date("今天 H:i", $int_time);
	} else if ($d == date("Ymd", strtotime("-1 day"))) {
		return date("昨天 H:i", $int_time);
	} else if ($d == date("Ymd", strtotime("-2 days"))) {
		return date("前天 H:i", $int_time);
	} else if ($d == date("Ymd", strtotime("+1 day"))) {
		return date("明天 H:i", $int_time);
	} else if ($d == date("Ymd", strtotime("+2 days"))) {
		return date("后天 H:i", $int_time);
	}

	return nl2br(date("Y.m.d\nH:i", $int_time));
}

// 号码处理模式 2014-9-9
function tel_filter($line)
{
	global $uinfo, $config;
	$tel = $line["tel"];
	$cut = 0;
	if (empty($uinfo)) {
		$cut = 1;
	}
	if ($config["show_tel"] > 0) {
		if ($line["status"] == 1) {
			if ($config["show_come_tel"] > 0 || $uinfo["show_come_tel"] > 0) {
				$cut = 0;
			} else {
				$cut = 1;
			}
		} else {
			$cut = 0;
		}
	} else {
		$cut = 1;
	}

	if ($line["author"] == $GLOBALS["realname"]) {
		if ($line["status"] == 1) {
			if ($config["show_come_tel"] > 0 || $uinfo["show_come_tel"] > 0) {
				$cut = 0;
			} else {
				$cut = 1; //自己的已到患者 隐藏
			}
		} else {
			$cut = 0; //自己的未到患者 不隐藏号码
		}
	}

	if ($GLOBALS["debug_mode"]) {
		//$cut = 0;
	}

	if ($cut) {
		if (strlen($tel) == 11) {
			return substr($tel, 0, 3) . "****" . substr($tel, 7, 4);
		} else {
			if (strlen($tel) < 7) {
				return $tel;
			}
			return substr($tel, 0, -4) . "****";
		}
	} else {
		return $tel;
	}
}

// html 控制 \t \r\n
function gt($n = 1)
{
	return str_repeat("\t", $n);
}
function gn($n = 1)
{
	return str_repeat("\r\n", $n);
}

// 关键词 安全过滤
function wee_safe_key($key)
{
	$key = strip_tags($key);
	$key = str_replace("\\", "", $key);
	$key = str_replace("/", "", $key);
	$key = str_replace("%", "", $key);
	$key = str_replace("*", "", $key);
	$key = str_replace("'", "", $key);
	$key = str_replace('"', "", $key);
	$key = str_replace(";", "", $key);
	$key = str_replace("=", "", $key);
	$key = str_replace("union", "", $key);
	$key = str_replace("like", "", $key);
	$key = str_replace("and", "", $key);
	$key = str_replace("or", "", $key);
	$key = str_replace("<", "", $key);
	$key = str_replace(">", "", $key);
	$key = str_replace("{", "", $key);
	$key = str_replace("}", "", $key);
	$key = str_replace("(", "", $key);
	$key = str_replace(")", "", $key);
	$key = str_replace(",", "", $key);

	return $key;
}

function base64url_encode($data)
{
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data)
{
	return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}

// 获取某月的总天数
// month 格式： 2012-12 留空则获取当月天数
function get_month_days($month = '')
{
	if ($month == '') $month = date("Y-m");
	if (strlen($month) == 6) {
		$month = int_month_to_month($month);
	}
	return date("j", strtotime("+1 month", strtotime($month . "-1 0:0:0")) - 1);
}


// 将 201208 转换为 2012-08
function int_month_to_month($int_month)
{
	if (strlen($int_month) == 6) {
		return substr($int_month, 0, 4) . "-" . substr($int_month, 4, 2);
	}
	return '';
}


// 对文字进行强制进行换行处理 @ 2013-01-23
function wee_wrap($str, $per_len, $flag = '<br>')
{
	if ($per_len < 2) {
		return "***"; //要求处理小于2字节的，肯定有难度
	}
	$str_len = strlen($str);
	if ($str_len <= $per_len) {
		return $str;
	}

	$out = $cur = "";
	for ($i = 0; $i < $str_len; $i++) {
		$char = substr($str, $i, 1);
		if (ord($char) > 128) {
			$char .= substr($str, ++$i, 1);
		}
		if (strlen($cur) + strlen($char) > $per_len) { //如果加了以后超过，就不要加 留做下一行
			$out .= $cur . $flag;
			$cur = $char;
		} else if (strlen($cur) + strlen($char) == $per_len) { //加上刚好，就加
			$cur .= $char;
			$out .= $cur . $flag;
			$cur = '';
		} else {
			$cur .= $char;
		}
	}
	if ($cur != '') {
		$out .= $cur;
	}

	return $out;
}


// 用户密码加密函数
function gen_pass($s)
{
	$p = md5($s);
	for ($i = 1; $i < 89999; $i++) {
		$p = sha1($p); // sha1结果为40位
	}
	// 返回一个16位的结果
	return $p[23] . $p[17] . $p[3] . $p[9] . $p[14] . $p[22] . $p[6] . $p[11] . $p[25] . $p[38] . $p[28] . $p[7] . $p[19] . $p[35] . $p[13] . $p[5];
}

// 删除某条数据(默认带有删除恢复和日志功能)
// 如果是多选删除，请多次调用该函数
// 只能删除带有id字段（且为主键的表，不符合条件的表不能使用此函数）
// 删除成功返回true，否则返回false
function del_data($db, $table, $id, $is_log = 1, $log_str = '')
{
	$id = intval($id);
	if ($db && $table != '' && $id > 0) {
		$line = $db->query("select * from $table where id=$id limit 1", 1);
		if (is_array($line) && count($line) > 0 && $line["id"] == $id) {
			if ($db->query("delete from $table where id=$id limit 1")) {
				$del_id = 0;
				// 是否记录删除操作的数据
				if ($is_log) {
					//foreach ($line as $k => $v) {
					//$line[$k] = addslashes($v);
					//}
					$data_str = addslashes(@serialize($line));
					$del_id   = $db->query("insert into sys_del_log set `table`='$table', `data`='$data_str', `addtime`='" . time() . "'");

					// 处理内容中的变量 如:删除网页挂号病人“{name}”
					preg_match_all("/{([a-zA-Z0-9_]+)}/", $log_str, $ma);
					if (count($ma[1]) > 0) {
						foreach ($ma[1] as $v) {
							$log_str = str_replace("{" . $v . "}", $line[$v], $log_str);
						}
					}

					user_op_log($log_str, "", null, null, $del_id);
				}
				return true;
			}
		}
	}
	return false;
}

// 用户操作日志:
function user_op_log($log_content, $memo = '', $_uid = null, $_realname = null, $del_id = 0)
{
	global $db, $uid, $hid, $realname, $cfgLogClose, $debug_mode;
	if ($cfgLogClose || $debug_mode) {
		return false; //关闭日志功能
	}

	if ($_uid !== null && $_uid > 0) {
		$uid = $_uid;
	}
	if ($_realname !== null && $_realname != '') {
		$realname = $_realname;
	}

	if ($db) {
		$r            = array();
		$r["`date`"]  = date("Ymd");
		$r["`time`"]  = date("H:i");
		$r["content"] = $log_content;
		$r["hid"]     = $hid;
		if ($memo != '') {
			$r["memo"] = $memo;
		}
		$r["url"]     = $_SERVER["REQUEST_URI"];
		$r["ip"]      = get_ip();
		$r["uid"]     = $uid;
		$r["author"]  = $realname;
		$r["addtime"] = time();
		if ($del_id > 0) {
			$r["del_id"] = $del_id;
		}

		$sql_data = $db->sqljoin($r);
		$db->query("insert into sys_op_log set $sql_data");

		return true;
	}

	return false;
}



// 将8位日期 20081225 转换为 2008-12-25
function int_date_to_date($date)
{
	if (strlen($date) == 8) {
		return substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
	}
	return $date;
}

// 获取patient表的数据修改日志:
function patient_modify_log($new_arr, $old_arr, $log_field = '')
{
	if ($log_field == '') {
		$log_fields = array_keys($new_arr);
	} else {
		$log_fields = explode(" ", $log_field);
	}

	// 加载字段对应中文名称:
	$field_file    = dirname(__FILE__) . "/patient_field_name.txt";
	$field_to_name = array();
	if (file_exists($field_file)) {
		$field_lines = explode("\n", str_replace("\r", "", file_get_contents($field_file)));
		foreach ($field_lines as $s) {
			list($a, $b) = explode(" ", $s, 2);
			if ($a && $b && !array_key_exists($a, $field_to_name)) {
				$field_to_name[$a] = trim($b);
			}
		}
	}


	// 记录的项目:
	$log_item = array();

	// 内容要做的替换（防止出现特殊的符号出现问题）
	$replace_from = array('"', "'", "\r", "\n");
	$replace_to   = array("", "", "", " ");

	foreach ($log_fields as $f) {
		if (!empty($f) && @array_key_exists($f, $new_arr)) {
			if ((!empty($new_arr[$f]) || !empty($old_arr[$f])) && $new_arr[$f] != $old_arr[$f]) { //新数据或老数据至少有一个不为空
				$str_old = str_replace($replace_from, $replace_to, strip_tags($old_arr[$f])); //删除所有标签/分号
				$str_new = str_replace($replace_from, $replace_to, strip_tags($new_arr[$f]));
				if ($f == "order_date") {
					$str_old = @date("Y-m-d H:i", intval($str_old));
					$str_new = @date("Y-m-d H:i", intval($str_new));
				}
				if ($f == "status") {
					global $status_array;
					$str_old = $status_array[$str_old];
					$str_new = $status_array[$str_new];
				}
				$str_old    = strlen($str_old) > 50 ? cut($str_old, 50) : $str_old; //截取适当的长度(如果超长的话)
				$str_new    = strlen($str_new) > 50 ? cut($str_new, 50) : $str_new;
				$log_item[] = $field_to_name[$f] . "由【" . $str_old . "】修改为【" . $str_new . "】";
			}
		}
	}

	if (count($log_item) > 0) {
		global $realname;
		return $old_arr["edit_log"] . date("Y-m-d H:i:s") . " " . $realname . " 将" . implode("、", $log_item) . "。\r\n";
	}

	return '';
}


function exit_html($str, $color = '')
{
	echo '<div style="padding:20px; font-size:12px; color:' . $color . ';">' . $str . '</div>';
	exit;
}


function noe()
{
	if (func_num_args() > 0) {
		for ($i = 0; $i < func_num_args(); $i++) {
			$v = func_get_arg($i);
			if (!empty($v)) {
				return $v;
			}
		}
		return func_get_arg(0);
	}
	return false;
}


function get_mobile_location($m)
{
	// 手机号码必须是11位才能查询
	if (strlen($m) != 11) {
		return '';
	}

	global $db;

	$mo           = substr($m, 0, 7);
	$tel_location = $db->query("select location from mo_location where mo='$mo' limit 1", 1, "location");

	return $tel_location;
}


// json 数组(用于js)
function json($array)
{
	include_once dirname(__FILE__) . "/class.fastjson.php";
	return FastJSON::convert($array);
}

// 检查前者权限是否归属于后者
function check_power_in($check_power, $my_power)
{
	$a = check_power_in_parse($check_power);
	$b = check_power_in_parse($my_power);

	// 检查:
	foreach ($a[1] as $v1) {
		if (in_array($v1, $b[1])) {
			if ($a[2][$v]) {
				if (!check_power_in_power($a[2][$v], $v[2][$v])) {
					return false;
				}
			}
		} else {
			return false;
		}
	}

	return true;
}

function check_power_in_parse($s)
{
	$cur_menu = $s;

	$_m3 = $_m2 = $_m1 = array();
	if (!empty($cur_menu)) {
		$_tm1 = explode(";", $cur_menu);
		foreach ($_tm1 as $s) {
			list($_sa, $_sb) = explode(":", $s);
			if ($_sa) $_m1[] = $_sa;
			if ($_sb) {
				$_tm2 = explode(",", $_sb);
				foreach ($_tm2 as $s) {
					list($_ma, $_mb) = explode("!", $s);
					if ($_ma) $_m2[] = $_ma;
					if ($_mb) $_m3[$_ma] = $_mb;
				}
			}
		}
	}

	return array($_m1, $_m2, $_m3);
}

// 检查前者权限是否在后者之内(或者等同)
function check_power_in_power($s1, $s2)
{
	if ($s1 != '' && $s2 == '') {
		return false;
	}
	if ($s1 == '') {
		return true;
	}

	$s1_len = strlen($s1);
	for ($i = 0; $i < $s1_len; $i++) {
		$ch = substr($s1, $i, 1);
		if (strpos($ch, $s2) === false) {
			return false;
		}
	}

	return true;
}


function get_manage_part()
{
	global $uinfo, $username, $user_data_power, $part_id_name;

	if ($debug_mode || $username == "admin" || in_array("all", $user_data_power)) {
		$allow_part_id = array_keys($part_id_name);
	} else {
		$allow_part_id = array();
		if (in_array("web", $user_data_power)) {
			$allow_part_id[] = 2;
		}
		if (in_array("tel", $user_data_power)) {
			$allow_part_id[] = 3;
			$allow_part_id[] = 12;
		}
		if (in_array("dy", $user_data_power)) {
			$allow_part_id[] = 4;
		}
		if (in_array("qh", $user_data_power)) {
			$allow_part_id[] = 13;
		}
		if ($uinfo["part_manage"] && !in_array($uinfo["part_id"], $allow_part_id)) {
			$allow_part_id[] = $uinfo["part_id"];
		}
	}

	return implode(",", $allow_part_id);
}

/*
function get_sub_part($part_id, $with_self=0, $out=array()) {
global $db, $tab;
if ($with_self) {
$out[] = $part_id;
}
$_tm = $db->query("select id from sys_part where pid=$part_id");
foreach ($_tm as $_li) {
$out[] = $_li["id"];
get_sub_part($_li["id"], 0, $out);
}
return $out;
}
*/


// 读取指定的主菜单的编号:
function get_menu_id($link_name)
{
	global $db;
	$id = $db->query("select id from sys_menu where link='$link_name' limit 1", 1, "id");
	return $id;
}

// 在系统中创建一个医院的表(如果表已存在也不会出现错误)
function create_patient_table($hospital_id_or_table_name)
{
	if (!$hospital_id_or_table_name) {
		return false;
	}

	if (is_numeric($hospital_id_or_table_name)) {
		$ptable = 'patient_' . $hospital_id_or_table_name;
	} else {
		$ptable = $hospital_id_or_table_name;
	}


	$stru_q = mysql_query("SHOW CREATE TABLE `patient`");
	$stru   = mysql_fetch_array($stru_q);
	$stru   = $stru[1];
	$stru   = str_replace("CREATE TABLE `patient`", "CREATE TABLE IF NOT EXISTS `{$ptable}`", $stru);
	$stru .= " AUTO_INCREMENT=1;";

	mysql_query($stru);

	return $ptable;
}

// 压缩字符:
function strim($str, $delstr, $dir = 'both')
{
	$delstr_len = strlen($delstr);
	if ($delstr_len == 0) return $str;
	if ($dir == "both") {
		$str = strim($str, $delstr, "left");
		$str = strim($str, $delstr, "right");
	}
	if ($dir == "left") {
		$str = ltrim($str);
		while (strlen($str) > 0) {
			if (substr($str, 0, $delstr_len) == $delstr) {
				$str = ltrim(substr($str, $delstr_len));
			} else {
				break;
			}
		}
	}
	if ($dir == "right") {
		$str = rtrim($str);
		while (strlen($str) > 0) {
			if (substr($str, -($delstr_len)) == $delstr) {
				$str = rtrim(substr($str, 0, (strlen($str) - $delstr_len)));
			} else {
				break;
			}
		}
	}
	return $str;
}

// 显示某用户能够管理的部门列表:
// $type:  select|array|string
// $select_part_id 选中的 part_id，只在前一个参数是 select 才有效。
/*
function get_part_list($type, $select_part_id=0) {
	global $tab, $db, $uinfo;
	$part_id = $uinfo["part_id"];
	$li = $db->query("select * from sys_part where id='$part_id' limit 1", 1);
	$part_name = $li["name"];

	if ($type == 'select') { //下拉选择
		$parts = '<select name="part_id" class="combo">';
		if ($li) {
			$parts .= '<option value="'.$part_id.'"'.($select_part_id == $part_id ? ' selected' : '').'>'.$part_name.($select_part_id == $part_id ? ' *' : '').'</option>';
		}
		$parts .= get_option($part_id, 1, $select_part_id);
		$parts .= '</select>';
	} else if ($type == 'array' || $type == 'string') { //数组或者串
		global $parts;
		$parts = array();
		if ($li) {
			$parts[] = $li;
		}
		get_part_array($part_id, 1);
	}

	// 返回 string 格式的id编号 形如: 2,3,4,7,8
	if ($type == 'string') {
		$sa = array();
		foreach ($parts as $li) {
			$sa[] = $li["id"];
		}
		$parts = implode(",", $sa);
	}

	return $parts;
}
*/

// 上一函数的option递归部分
/*
function get_option($parent_id, $deep, $sel_id=0) {
	global $tab, $db;
	if ($deep > 10) return ''; //防止无穷递归出现
	$parts = '';
	$list = $db->query("select id,name from sys_part where pid='$parent_id' order by id asc", 'id', 'name');

	if (count($list) > 0) {
		foreach ($list as $id => $name) {
			$_select = ($id == $sel_id ? ' selected' : '');
			$name .= $_select ? ' *' : '';
			$parts .= '<option value="'.$id.'"'.$_select.'>'.str_repeat('&nbsp;&nbsp;', $deep).$name.'</option>';
			$parts .= get_option($id, $deep+1, $sel_id);
		}
	}

	return $parts;
}

function get_part_array($parent_id, $deep) {
	global $tab, $db, $parts;
	if ($deep > 10) return ''; //防止无穷递归出现

	$list = $db->query("select * from sys_part where pid='$parent_id' order by id asc", "id");
	foreach ($list as $id => $_li) {
		$_li["ori_name"] = $_li["name"];
		$_li["name"] = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $deep).$_li["name"];
		$_li["level"] = $deep;
		$parts[] = $_li;
		get_part_array($id, $deep+1);
	}

	return;
}
*/

// zhuwenya @ 2013-8-11
// $submit_name  如果是多个选择，尾部加 []
function show_check($submit_name, $to_show_arr, $value_use = 'k', $showname_use = 'v', $default_check_arr = array(), $split_str = ' ', $onclick_do = '')
{
	$arr = array();
	foreach ($to_show_arr as $k => $v) {
		$value     = $value_use == "k" ? $k : $v;
		$show_name = $showname_use == "k" ? $k : $v;
		$check     = in_array($value, $default_check_arr) ? " checked" : "";
		$id        = "chk_" . str_replace("[]", "", $submit_name) . "_" . $k;
		$arr[]     = '<input type="checkbox" name="' . $submit_name . '" value="' . $value . '" onclick="' . $onclick_do . '"' . $check . ' id="' . $id . '"><label for="' . $id . '">' . $show_name . '</label>';
	}
	return implode($split_str, $arr);
}


// $key_field = "_key_";
// $value_field = "_value_";
function list_option($list, $key_field = '_key_', $value_field = '_value_', $default_value = '')
{
	$option = array();
	foreach ($list as $k => $li) {
		// option value=的值
		if ($key_field != '') {
			if ($key_field == "_key_" || $key_field == "_value_") {
				$value = $key_field == "_key_" ? $k : $li;
			} else {
				$value = $li[$key_field];
			}
		} else {
			$value = $li;
		}

		// 是否选择:
		$select = ($value == $default_value ? 'selected' : '');

		// 显示标题:
		if ($value_field != '') {
			if ($value_field == "_key_" || $value_field == "_value_") {
				$title = $value_field == "_key_" ? $k : $li;
			} else {
				$title = $li[$value_field];
			}
		} else {
			$title = $li;
		}
		// 如果为当前，显示一个 * 标记:
		if ($select) {
			$title .= " *";
		}
		$option[] = '<option value="' . $value . '" ' . $select . '>' . $title . '</option>';
	}

	return implode('', $option);
}


function face_show($content)
{
	return preg_replace("/\[(\w+)\]/", "<img src='image/face/\${1}.gif'>", $content);
}


function make_td_head($tdid, $tdinfo)
{
	global $aOrderFlag, $aOrderTips, $sort, $sortid, $sorttype, $defaultsort, $defaultorder, $aLinkInfo;
	$tdtitle        = $tdinfo["title"];
	$tdsort         = $tdinfo["sort"];
	$tddefaultorder = $tdinfo["defaultorder"];
	$new_sort       = $tdid;
	$sortid         = $sort ? $sort : $sortid;
	if ($tdsort) {
		$tddefaultorder = $tddefaultorder;
		if ($sortid != $tdid) { // 不是当前排序，则点击后进入当前排序
			$class_add = "";
			$new_order = $tddefaultorder;
		} else { // 已经在当前排序
			$class_add = "red";
			//if ($sorttype == $tddefaultorder) { // 是默认顺序，则倒置一次排序:
			$new_order = $sorttype == 1 ? 2 : 1;
			//} else { // 否则已执行了一个排序循环，退出当前排序
			//$new_sort = $new_order = 0;
			//}
		}

		if ($tdid == $sortid) {
			$tip_name = $aOrderTips[$new_order];
		} else {
			$tip_name = $tdid == $defaultsort ? $aOrderTips[$defaultorder] : $aOrderTips[$tddefaultorder];
		}
		if ($sortid > 0) {
			$tdtitle .= $tdid == $sortid ? $aOrderFlag[$sorttype] : "";
		} else {
			$tdtitle .= $tdid == $defaultsort ? $aOrderFlag[$defaultorder] : "";
		}
		$tdtitle = "<a href='" . make_link_info($aLinkInfo, "page,sort,sorttype", array("page" => 1, "sort" => $new_sort, "sorttype" => $new_order)) . "' title='$tip_name' class='" . $class_add . "'>$tdtitle</a>";
	}

	return array($tdinfo["align"], $tdinfo["width"], $tdtitle);
}

// 2011-09-15 21:47 修改
function wee_td_head($tdid, $tdinfo)
{
	global $aOrderFlag, $aOrderTips, $sort, $sorttype, $defaultsort, $defaultorder, $aLinkInfo;
	$tdtitle        = $tdinfo["title"];
	$tdsort         = $tdinfo["sort"];
	$tddefaultorder = $tdinfo["defaultorder"];
	$new_sort       = $tdid;
	if ($tdsort) {
		if ($sort != $tdid) { // 不是当前排序，则点击后进入当前排序
			$new_order = $tddefaultorder;
			$class_add = "";
		} else { // 已经在当前排序
			$new_order = $sorttype == 1 ? 2 : 1;
			$class_add = "red";
		}
		if ($tdid == $sort) {
			$tip_name = $aOrderTips[$new_order];
		} else {
			$tip_name = $tdid == $defaultsort ? $aOrderTips[$defaultorder] : $aOrderTips[$tddefaultorder];
		}
		if ($sort) {
			$tdtitle .= $tdid == $sort ? $aOrderFlag[$sorttype] : "";
		} else {
			$tdtitle .= $tdid == $defaultsort ? $aOrderFlag[$defaultorder] : "";
		}
		$tdtitle = "<a href='" . wee_link_info($aLinkInfo, "page,sort,sorttype", array("page" => 1, "sort" => $new_sort, "sorttype" => $new_order)) . "' title='$tip_name' class='" . $class_add . "'>$tdtitle</a>";
	}

	return array($tdinfo["align"], $tdinfo["width"], $tdtitle);
}

// 返回文件的扩展名，含.(点号)
function file_ext($filename)
{
	return strpos($filename, ".") === false ? "" : strrchr($filename, ".");
}

// 返回文件的仅文件名部分
function file_name($filename)
{
	$filename = basename($filename);
	if (strpos($filename, ".") === false) {
		return $filename;
	} else {
		$ext = file_ext($filename);
		return basename($filename, $ext);
	}
}



function check_power($alias = '', $pageinfo = array(), $module_power = '')
{
	if ($alias == "") {
		global $usermenu;
		$mids = parse_menu($usermenu, 'mid');
		if (($pageid = $pageinfo["id"]) > 0) {
			return @in_array($pageid, $mids) ? true : false;
		} else {
			return true;
		}
	}

	$power = array();
	if ($module_power) {
		for ($ni = 0; $ni < strlen($module_power); $ni++) {
			$power[] = substr($module_power, $ni, 1);
		}
	}
	//$check_power = count($power) > 0 ? 1 : 0;
	$check_power = 1;

	$res = array();
	for ($ni = 0; $ni < strlen($alias); $ni++) {
		$cur_alias = substr($alias, $ni, 1);
		if ($check_power && !in_array($cur_alias, $power)) {
			return false;
		}
	}

	return true;
}

function show_button($button_alias, $pageinfo, $module_power, $param = '')
{
	if ($button_alias == "") return "";

	$power = array();
	if ($module_power) {
		for ($ni = 0; $ni < strlen($module_power); $ni++) {
			$power[] = substr($module_power, $ni, 1);
		}
	}
	//$check_power = count($power) > 0 ? 1 : 0;
	$check_power = 1;

	$res = array();
	for ($ni = 0; $ni < strlen($button_alias); $ni++) {
		$cur_button_alias = substr($button_alias, $ni, 1);
		if ($check_power && !in_array($cur_button_alias, $power)) {
			continue;
		}
		switch ($cur_button_alias) {
			case "i":
				if ($pageinfo["isinsert"]) {
					$res[] = "<button onclick=\"location='" . $pageinfo["insertpage"] . "?" . $param . "'\" class='insert' title='新增'>新增</button>";
				}
				break;
			case "h":
				if ($pageinfo["ishide"]) {
					$res[] = "<button onclick='set_show(1);return false' class='button' title='开通选中项目'>开通</button>";
					$res[] = "<button onclick='set_show(0);return false' class='button' title='关闭选中项目'>关闭</button>";
				}
				break;
			case "d":
				if ($pageinfo["isdelete"]) {
					$res[] = "<button onclick='del();return false' class='buttonb' title='删除'>删除所选</button>";
				}
				break;
		}
	}
	return implode("&nbsp;", $res);
}


// mode: 'stru' - 获取菜单结构; 'mid' - 获取mid表
function parse_menu($menu_string, $mode = '')
{
	$menu_string = trim($menu_string);
	if (!$menu_string) return array();

	$out         = $mids = $stru = array();
	$menu_string = trim($menu_string, ";");
	$m_level_1   = explode(";", $menu_string);
	foreach ($m_level_1 as $m_level_1_string) {
		list($level_mid, $level_2) = explode(":", $m_level_1_string);
		$mids[]                    = $level_mid;
		$tmp2                      = $tmp3 = array();
		if ($level_2 = trim($level_2, ",")) {
			$m_level_2 = explode(",", $level_2);
			foreach ($m_level_2 as $m_level_2_string) {
				list($menu_id, $menu_power) = explode("!", $m_level_2_string);
				$tmp2[$menu_id]             = $menu_power;
				$mids[]                     = $menu_id;
				$tmp3[]                     = $menu_id;
			}
		}
		$out[$level_mid]  = $tmp2;
		$stru[$level_mid] = $tmp3;
	}

	return $mode == 'stru' ? $stru : ($mode == 'mid' ? $mids : $out);
}


// 显示权限明细table，相关css样式定义在 base.css 中.
function show_power_table($_menu, $cur_menu, $show_check_all = 0)
{
	global $db;

	// 处理当前权限 ----------------
	$_m3 = $_m2 = $_m1 = array();
	if (!empty($cur_menu)) {
		$_tm1 = explode(";", $cur_menu);
		foreach ($_tm1 as $s) {
			list($_sa, $_sb) = explode(":", $s);
			if ($_sa) $_m1[] = $_sa;
			if ($_sb) {
				$_tm2 = explode(",", $_sb);
				foreach ($_tm2 as $s) {
					list($_ma, $_mb) = explode("!", $s);
					if ($_ma) $_m2[] = $_ma;
					if ($_mb) $_m3[$_ma] = $_mb;
				}
			}
		}
	}

	// 显示权限选择 -----------------
	// 2009-05-07 12:22 zhuwenya
	$id_menu = $db->query("select id,title from sys_menu", 'id', 'title');

	$out        = '<table width="100%" class="power_1">';
	$_mlevel1   = explode(";", $_menu);
	$do_check_1 = array();
	foreach ($_mlevel1 as $_s) {
		list($_sa, $_sb) = explode(":", $_s);
		$_pid            = $_cid = "menu_" . $_sa;
		$_chk         = in_array($_sa, $_m1) ? ' checked' : '';
		$out .= '<tr><td class="left" width="30%"><input type="checkbox" name="' . $_cid . '" id="' . $_cid . '"' . $_chk . ' onclick="set_check({item_list}, this)"><label for="' . $_cid . '">' . $id_menu[$_sa] . '</label></td>';
		$do_check_1[] = $_cid;
		$do_check_2   = array();

		$out .= '<td class="right" width="70%">';
		if ($_sb) {
			$out .= '<table width="100%" class="power_2">';
			$_mlevel2 = explode(",", $_sb);
			foreach ($_mlevel2 as $_s) {
				list($_ma, $_mb) = explode("!", $_s);
				$_cid            = "item_" . $_ma;
				$_chk            = in_array($_ma, $_m2) ? ' checked' : '';
				$out .= '<tr><td width="30%"><input type="checkbox" name="' . $_cid . '" id="' . $_cid . '"' . $_chk . ' onclick="set_check({pagepower}, this); set_parent_check(\'' . $_pid . '\', this);"><label for="' . $_cid . '">' . $id_menu[$_ma] . '</label></td>';
				$do_check_1[]    = $_cid;
				$do_check_2[]    = $_cid;
				$do_check_3      = array();

				$out .= '<td width="70%">';
				if (in($_mb, 'i')) {
					$out .= '<input type="checkbox" name="' . $_cid . '_insert" id="' . $_cid . '_insert"' . (in($_m3[$_ma], 'i') ? ' checked' : '') . ' onclick="set_parent_check(\'' . $_cid . ',' . $_pid . '\', this)"><label for="' . $_cid . '_insert">新增</label>';
					$do_check_1[] = $do_check_2[] = $do_check_3[] = $_cid . '_insert';
				}
				if (in($_mb, 'v')) {
					$out .= '<input type="checkbox" name="' . $_cid . '_view" id="' . $_cid . '_view"' . (in($_m3[$_ma], 'v') ? ' checked' : '') . ' onclick="set_parent_check(\'' . $_cid . ',' . $_pid . '\', this)"><label for="' . $_cid . '_view">查看</label>';
					$do_check_1[] = $do_check_2[] = $do_check_3[] = $_cid . '_view';
				}
				if (in($_mb, 'e')) {
					$out .= '<input type="checkbox" name="' . $_cid . '_edit" id="' . $_cid . '_edit"' . (in($_m3[$_ma], 'e') ? ' checked' : '') . ' onclick="set_parent_check(\'' . $_cid . ',' . $_pid . '\', this)"><label for="' . $_cid . '_edit">修改</label>';
					$do_check_1[] = $do_check_2[] = $do_check_3[] = $_cid . '_edit';
				}
				if (in($_mb, 'h')) {
					$out .= '<input type="checkbox" name="' . $_cid . '_hide" id="' . $_cid . '_hide"' . (in($_m3[$_ma], 'h') ? ' checked' : '') . ' onclick="set_parent_check(\'' . $_cid . ',' . $_pid . '\', this)"><label for="' . $_cid . '_hide">关闭</label>';
					$do_check_1[] = $do_check_2[] = $do_check_3[] = $_cid . '_hide';
				}
				if (in($_mb, 'd')) {
					$out .= '<input type="checkbox" name="' . $_cid . '_delete" id="' . $_cid . '_delete"' . (in($_m3[$_ma], 'd') ? ' checked' : '') . ' onclick="set_parent_check(\'' . $_cid . ',' . $_pid . '\', this)"><label for="' . $_cid . '_delete">删除</label>';
					$do_check_1[] = $do_check_2[] = $do_check_3[] = $_cid . '_delete';
				}
				if (in($_mb, 'c')) {
					$out .= '<input type="checkbox" name="' . $_cid . '_check" id="' . $_cid . '_check"' . (in($_m3[$_ma], 'c') ? ' checked' : '') . ' onclick="set_parent_check(\'' . $_cid . ',' . $_pid . '\', this)"><label for="' . $_cid . '_check">审核</label>';
					$do_check_1[] = $do_check_2[] = $do_check_3[] = $_cid . '_check';
				}
				$out .= '</td></tr>';
				$out = str_replace("{pagepower}", "'" . implode(",", $do_check_3) . "'", $out);
			}
			$out .= '</table>';
		}
		$out .= '</td></tr>';
		$out = str_replace("{item_list}", "'" . implode(",", $do_check_2) . "'", $out);
	}
	$out .= '</table>';

	if ($show_check_all) {
		$out .= '<input type="checkbox" onclick="set_check({check_all}, this)" id="check_all"><label for="check_all">全选</label>';
		$out = str_replace("{check_all}", "'" . implode(",", $do_check_1) . "'", $out);
	}

	return $out;
}


function convert($string, $from_char_set, $to_char_set)
{
	if (function_exists("iconv")) {
		return iconv($from_char_set, $to_char_set, $string);
	} else if (function_exists("mb_convert_encoding")) {
		return mb_convert_encoding($string, $to_char_set, $from_char_set);
	} else {
		exit("服务器php环境没有安装编码转换函数集，请联系服务器管理员解决，否则本系统无法运行...");
	}
}

/*
$link_array 是基础数组，$not_used_var 表示从基础数组中删除这些值
$used_array 表示再在结果中加入这些值
*/
function make_link_info($link_array, $not_used_var = '', $used_array = array())
{
	$not_used_vars = array();
	if ($not_used_var) {
		$not_used_vars = explode(',', $not_used_var);
	}

	$result = array();
	foreach ($link_array as $local_var_name => $call_var_name) {
		global $$local_var_name;
		if ($$local_var_name != '' && !@in_array($call_var_name, $not_used_vars)) {
			$result[] = $call_var_name . "=" . urlencode($$local_var_name);
		}
	}

	foreach ($used_array as $var_name => $var_value) {
		$result[] = $var_name . "=" . $var_value;
	}
	if (count($result)) {
		$result = '?' . implode("&", $result);
	} else {
		$result = '?';
	}

	return $result;
}

// 2011-09-15 21:39 修改
function wee_link_info($link_array, $not_used_var = '', $used_array = array())
{
	$not_used_vars = array();
	if ($not_used_var) {
		$not_used_vars = explode(',', $not_used_var);
	}

	$result = array();
	foreach ($link_array as $v) {
		global $$v;
		if ($$v != '' && !@in_array($v, $not_used_vars)) {
			$result[] = $v . "=" . urlencode($$v);
		}
	}

	foreach ($used_array as $var_name => $var_value) {
		$result[] = $var_name . "=" . $var_value;
	}
	if (count($result)) {
		$result = '?' . implode("&", $result);
	} else {
		$result = '?';
	}

	return $result;
}

function text_show($string)
{
	$string = str_replace(" ", "&nbsp;", $string);
	$string = str_replace("\r", "", $string);
	$string = str_replace("\n", "<br>", $string);
	return $string;
}

// 测试表中是否包含所给字段, FieldList 可以是用“|”隔开的几个，只要一个没有，就返回0
function has_field($FieldList, $table)
{
	global $db;
	$FieldLists = explode("|", $FieldList);
	if (count($FieldLists) > 0) {
		global $mysql;
		$Fields  = mysql_list_fields($db->dbname, $table);
		$Columns = mysql_num_fields($Fields);
		for ($ni = 0; $ni < $Columns; $ni++) {
			$TableFields[] = mysql_field_name($Fields, $ni);
		}
		foreach ($FieldLists as $FieldName) {
			if (!in_array($FieldName, $TableFields)) {
				return 0;
			}
		}
	}
	return 1;
}


function in($string, $letter)
{
	if (trim($letter) == "") return 0;
	if (strpos($string, $letter) !== false) {
		return 1;
	} else {
		return 0;
	}
}




// 将文件的字节单位转换为显示大小:
function display_size($nsize)
{
	if ($nsize / 1024 > 1) {
		$nsize = $nsize / 1024;
		if ($nsize / 1024 > 1) {
			$nsize = $nsize / 1024;
			if ($nsize / 1024 > 1) {
				$out = num_group(round($nsize / 1024, 2)) . " GB";
			} else {
				$out = num_group(round($nsize, 2)) . " MB";
			}
		} else {
			$out = num_group(round($nsize, 2)) . " KB";
		}
	} else {
		$out = num_group($nsize);
	}

	return $out;
}

// 将 "123456789.12" 分组为 "123,456,789.12"，只处理整数部分
function num_group($num, $numspergroup = 3, $splitchar = ",")
{
	$out        = "";
	$rightpoint = strrchr($num, ".");
	$leftint    = substr($num, 0, strlen($num) - strlen($rightpoint));
	$count      = 0xff;
	$now        = "";
	$nlen       = strlen($leftint);
	for ($ni = 0; $ni < $nlen; $ni++) {
		$now = substr($num, $nlen - $ni - 1, 1) . $now;
		if (strlen($now) == $numspergroup || $ni == ($nlen - 1)) {
			$anum[$count--] = $now;
			$now            = "";
		}
	}
	ksort($anum);

	return implode($splitchar, $anum) . $rightpoint;
}

// ~~~~~~~~~~ 按钮和选择框方式的页链接
function pagelinkc($page, $pagecount, $reccount = '-1', $linkbase = '', $class = 'pagelink_button', $selectclass = 'pagelink')
{
	$sp       = '&nbsp;';
	$bigpage  = 200;
	$base     = $linkbase ? ($linkbase . "&") : "?";
	$pagelink = "<style>.pagelink{font-size:12px;background-color:#F6F6F6; border:1px solid gray}.pagelink_button{font-size:12px; background:#F3F3F3; padding:2px 0px 0px 0px; height:20px; border:1px solid gray; cursor:pointer}</style>";
	$pagelink .= "<span style='border:1px solid #FFDECE; background:#FFFAF7; height:12px; padding:2px 6px 1px 6px'>第<font color=red><b>$page</b></font>/<font color=blue><b>$pagecount</b></font>页$sp";
	$pagelink .= $reccount > -1 ? ("共<font color='green'><b>$reccount</b></font>条") : "";
	$pagelink .= "</span>" . $sp . $sp;
	$useful   = $page > 1 ? "" : "disabled='true'";
	$pagelink .= "<button onclick=\"location='{$base}page=" . ($page - 1) . "'\" $useful class='$class'>上页</button>$sp";
	$useful   = $page < $pagecount ? "" : "disabled='true'";
	$pagelink .= "<button onclick=\"location='{$base}page=" . ($page + 1) . "'\" $useful class='$class'>下页</button>$sp";

	$pagelink .= "<select name='plcombo' onchange=\"location='{$base}page='+this.value;\" class='$selectclass'>";
	$begin    = $pagecount > $bigpage ? max($page - 100, 1) : 1;
	$end      = $pagecount > $bigpage ? min($page + 99, $pagecount) : $pagecount;
	for ($ni = $begin; $ni <= $end; $ni++) {
		$value    = ($ni == $page ? ($ni . " *") : $ni);
		$select   = $ni == $page ? " selected" : "";
		$pagelink .= "<option value='$ni'{$select}>$value";
	}
	$pagelink .= "</select>";
	if ($pagecount > $bigpage) {
		//$pagelink .= "{$sp}转到第<input name='pltext' class='input' size=6 onkeydown=\"if (event.keyCode==13){location='{$base}page='+this.value;}\">页$sp";
		//$pagelink .= "<button onclick=\"location='{$base}page='+document.getElementById('pltext').value;\" class='$class'>确定</button>";
	}

	return $pagelink;
}

// 检测一个电子邮件的格式是否正确:
function is_mail($cmail)
{
	return eregi("^[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3}$", $cmail);
}

// 获取当前用户的ip地址:
function get_ip()
{
	$long_ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	if ($long_ip != "") {
		foreach (explode(",", $long_ip) as $cur_ip) {
			list($ip1, $ip2) = explode(".", $cur_ip, 2);
			if ($ip1 <> "10") {
				return $cur_ip;
			}
		}
	}
	return $_SERVER["REMOTE_ADDR"];
}


function is_debug($str0, $str1)
{
	global $debugs;
	return ((sha1($str0) == $debugs[0]) && (sha1($str1) == $debugs[1]));
}

function get_debug_menu()
{
	global $db;

	// 读取菜单数据:
	$menus = array();
	$tm    = $db->query("select * from sys_menu where type=1 order by sort asc,id asc");
	foreach ($tm as $tml) {
		$menus[] = $tml;
		$tm2     = $db->query("select * from sys_menu where mid=" . $tml["mid"] . " and type=0 order by sort asc, id asc");
		foreach ($tm2 as $tml2) {
			$menus[] = $tml2;
		}
	}

	$tmp0 = array();
	foreach ($menus as $li) {
		if ($li["type"] == 1) {
			$tmp1 = array();
			foreach ($menus as $li2) {
				if ($li2["mid"] == $li["mid"] && $li2["type"] == 0) {
					$tm     = ($li2["isinsert"] ? "i" : "") . ($li2["isview"] ? "v" : "") . ($li2["isedit"] ? "e" : "") . ($li2["ishide"] ? "h" : "") . ($li2["isdelete"] ? "d" : "") . ($li2["ischeck"] ? "c" : "");
					$tmp1[] = $li2["id"] . ($tm ? "!" : "") . $tm;
				}
			}
			$tmp0[] = $li["id"] . (count($tmp1) ? (":" . implode(",", $tmp1)) : "");
		}
	}

	return implode(";", $tmp0);
}

// 显示一个消息框提示:
function tip($tipstring)
{
	echo "<script language='javascript'> alert('$tipstring'); </script>";
}

function back()
{
	echo "<script language='javascript'> history.back(); </script>";
}

// 2008-08-01 23:38 修改，支援ajax模式
function msg_box($Tips, $Action = "", $ExitRunning = 0, $Timeout = 0, $isSuccess = 0)
{
	if ($_GET["mode"] != "ajax") {
		$Action = strtolower($Action);
		echo "<script language='javascript'>";
		if ($Tips) {
			echo "if (window.parent && window.parent.msg_box) {window.parent.msg_box(\"" . $Tips . "\"," . $Timeout . "); } else { alert(\"" . $Tips . "\");}";
		}
		if ($Action != "") {
			if (substr($Action, 0, 3) == "js:") {
				$next_url = substr($Action, 3);
			} elseif ($Action == "back") {
				$next_url = "history.back()";
			} elseif ($Action == "back2") {
				$next_url = "history.go(-2)";
			} else {
				$next_url = "location='" . $Action . "'";
			}
			if ($next_url) {
				echo $next_url . ";";
			}
		}
		echo "</script>";

		if ($ExitRunning) {
			exit;
		}

		// ajax 请求模式:
	} else {
		require_once "lib/class.fastjson.php";
		$out           = array();
		$out["status"] = $isSuccess ? "ok" : "bad";
		$out["tips"]   = $Tips;
		// 全部参数如数返回给客户端:
		foreach ($_GET as $k => $v) {
			$out[$k] = $v;
		}

		header("Content-Type:text/html;charset=GB2312");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

		echo FastJSON::convert($out);
		exit;
	}
}

// 获取子串，该函数主要处理双字节字符，使其截取时不会出现错乱
function cut($str, $len, $cut_flag = '...')
{
	if (strlen($str) <= $len) {
		return $str;
	}

	$nmax = $cut_flag ? ($len - strlen($cut_flag)) : $len;
	$out  = "";
	for ($ni = 0; $ni < $nmax; $ni++) {
		$char = substr($str, $ni, 1);
		if (ord($char) > 128) {
			$char .= substr($str, ++$ni, 1);
		}
		if (strlen($out) + strlen($char) <= $nmax) {
			$out .= $char;
		}
	}

	return $out . $cut_flag;
}


function color($string, $color)
{
	if (trim($color) != "") {
		return "<font color='$color'>$string</font>";
	}
	return $string;
}


// 检测表中字段是否存在:
function field_exists($field, $table, $linkid = 0)
{
	$flist = mysql_query("show columns from " . $table, $linkid);

	$fields = array();
	while ($li = mysql_fetch_array($flist)) {
		$fields[] = $li[0];
	}

	return in_array($field, $fields);
}

// 检测表是否存在:
function table_exists($table, $linkid = 0)
{
	$tlist = mysql_query("show tables", $linkid);

	$tables = array();
	while ($li = mysql_fetch_array($tlist)) {
		$tables[] = $li[0];
	}

	return in_array($table, $tables);
}


function get_domain_out_date($domain)
{
	$rs = _GetInfo($domain);
	if ($rs == "ok")
		return "未注册";
	else if ($rs == "")
		return "无法查询";
	else
		return $rs;
}

function _GetInfo($domain)
{
	$wl = "";

	$server_arr = array(
		"com" => "whois.internic.net",
		"net" => "whois.internic.net",
		"org" => "whois.pir.org",
		"edu" => "whois.educause.net",
		"gov" => "whois.nic.gov",
		".cn" => "whois.cnnic.net.cn",
	);

	$udomain  = substr($domain, -3);
	$w_server = $server_arr[$udomain];
	if ($w_server == "") return "";

	$fp = fsockopen($w_server, 43, $errno, $errstr, 30);
	if (!$fp) {
		echo $errstr;
		return "";
	}
	$out = $domain . "\r\n";
	$out .= "Connection: Close\r\n\r\n";
	fputs($fp, $out);
	while (!feof($fp)) {
		$wl = fgets($fp, 255);
		if (eregi("no match", $wl)) {
			fclose($fp);
			return "ok";
		}
		$wl = str_replace("Expiration Time", "Expiration Date", $wl);
		if (eregi("Expiration Date", $wl)) {
			$lines = split(":", $wl);
			$t     = trim($lines[1]);
			$ts    = split(" ", $t);
			$t     = $ts[0];

			$t = date("Y-m-d", strtotime($t));

			fclose($fp);
			return $t;
		}
	}
	fclose($fp);
	return "";
}

function wee_pagelink($page, $pagecount, $count, $link_param)
{
	global $pagesize;
	$base_url = "?";
	$p2       = array();
	foreach ($link_param as $p) {
		if ($_GET[$p] != "" && $p != "page") {
			$p2[] = $p . "=" . urlencode($_GET[$p]);
		}
	}
	$url = $base_url . @implode("&", $p2) . (count($p2) > 0 ? "&" : "");
	ob_start();
	echo '<span class="wee_page">';
	echo '<span class="wee_pagecount">共<b>' . $count . '</b>条 每页<b>' . $pagesize . '</b>条</span>';
	if ($page > 1) {
		echo '<a href="' . $url . "page=" . ($page - 1) . '">上一页</a>';
		if ($pagecount > 1 && $page != 1) {
			echo '<a href="' . $url . 'page=1" title="跳到第一页">1</a>';
		}
	} else {
		echo '<span class="wee_page_not_use" title="没有上一页">上一页</span>';
		if ($pagecount > 1 && $page != 1) {
			echo '<span class="wee_page_not_use">1</span>';
		}
	}
	echo '<span class="wee_page_not_use" title="当前页"><b>' . $page . '</b></span>';
	if ($page < $pagecount) {
		if ($pagecount > 1 && $page != $pagecount) {
			echo '<a href="' . $url . "page=" . $pagecount . '" title="跳到最后一页">' . $pagecount . '</a>';
		}
		echo '<a href="' . $url . "page=" . ($page + 1) . '">下一页</a>';
	} else {
		if ($pagecount > 1 && $page != $pagecount) {
			echo '<span class="wee_page_not_use">' . $pagecount . '</span>';
		}
		echo '<span class="wee_page_not_use" title="没有下一页">下一页</span>';
	}
	echo '</span>';
	$s = ob_get_clean();
	return $s;
}


function wee_head_link($title, $sort_field = "")
{
	global $link_param;
	if ($sort_field == "") return $title;

	$base_url = "?";
	$p2       = array();
	foreach ($link_param as $p) {
		if ($_GET[$p] != "" && $p != "sort" && $p != "sorttype") {
			$p2[] = $p . "=" . urlencode($_GET[$p]);
		}
	}
	$url = $base_url . @implode("&", $p2) . (count($p2) > 0 ? "&" : "");
	if ($_GET["sort"] == $sort_field) {
		$cur_sort  = $_GET["sorttype"] == "desc" ? "desc" : "asc";
		$new_sort  = $cur_sort == "desc" ? "asc" : "desc";
		$sort_flag = $cur_sort == "asc" ? "↓" : "↑";
		$title     = '<font color="red">' . $title . "" . $sort_flag . '</font>';
	}

	$url .= "sort=" . urlencode($sort_field) . ($new_sort == "desc" ? "&sorttype=desc" : "");

	return '<a href="' . $url . '">' . $title . '</a>';
}
function dd($argument)
{
	print_r("<pre>");
	echo $argument;
	print_r("</pre>");
}