<?php
// --------------------------------------------------------
// - 功能说明 : 检查人数是否可以
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2013-11-18
// --------------------------------------------------------
require "../lib/set_env.php";

if (strlen($_GET["date"]) == 10) {
	$date = @date("Y-m-d", strtotime($_GET["date"]));
} else {
	echo 'alert("日期格式不正确！");';
	exit;
}

$doctor_name = mb_convert_encoding(trim($_GET["doctor"]), "gbk", "UTF-8");
if ($doctor_name == '') {
	exit;
}

$doctor_info = $db->query("select * from doctor where hospital_id=$hid and name='$doctor_name' limit 1", 1);

$tb = strtotime($date." 0:0:0");
$te = strtotime($date." 23:59:59");
$num = $db->query("select count(wish_doctor) as c from patient_{$hid} where order_date>=$tb and order_date<=$te and wish_doctor='$doctor_name'", 1, "c");

$weekday = date("w", strtotime($date));
if ($weekday == 0 || $weekday == 6) {
	$max_num = $doctor_info["max_weekend"];
} else {
	$max_num = $doctor_info["max_weekday"];
}

if ($max_num == 0) {
	exit; //0为不限制人数
}

if ($num >= $max_num) {
	echo 'alert("对不起，['.$doctor_name.'] 在 '.$date.' 的接诊人数('.$num.')已达到或超过人数上限('.$max_num.')，不能接诊，请选择其他医生！");';
	echo 'document.getElementById("wish_doctor").options[0].selected = "selected";';
}


?>