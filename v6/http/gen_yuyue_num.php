<?php
// --------------------------------------------------------
// - ����˵�� : ����һ�����ظ��������
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2015-8-4
// --------------------------------------------------------
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";

$max_try_times = 100; // ����Դ���

$gen_yuyue_num = '';
for ($i=0; $i < $max_try_times; $i++) {
	$num = mt_rand(10000000, 99999999);
	$repeat = $db->query("select count(*) as c from yuyue_num_rand where yuyue_num=$num", 1, "c");
	if ($repeat > 0) {
		continue;
	} else {
		$gen_yuyue_num = $num;
		break;
	}
}

if ($gen_yuyue_num != '') {
	echo 'gen_yuyue_num_do("'.$gen_yuyue_num.'");';
} else {
	echo 'gen_yuyue_num_do("����ʧ�� ����������");';
}

?>