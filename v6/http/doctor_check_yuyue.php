<?php
// --------------------------------------------------------
// - ����˵�� : ��������Ƿ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2013-11-18
// --------------------------------------------------------
require "../lib/set_env.php";

if (strlen($_GET["date"]) == 10) {
	$date = @date("Y-m-d", strtotime($_GET["date"]));
} else {
	echo 'alert("���ڸ�ʽ����ȷ��");';
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
	exit; //0Ϊ����������
}

if ($num >= $max_num) {
	echo 'alert("�Բ���['.$doctor_name.'] �� '.$date.' �Ľ�������('.$num.')�Ѵﵽ�򳬹���������('.$max_num.')�����ܽ����ѡ������ҽ����");';
	echo 'document.getElementById("wish_doctor").options[0].selected = "selected";';
}


?>