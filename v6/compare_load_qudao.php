<?php
/*
// ˵��: ���ضԱȵ������б�
// ����: ���� (weelia@126.com)
// ʱ��: 2014-3-29
*/
include "lib/set_env.php";
include "lib/class.fastjson.php";

$hid = intval($_GET["hid"]);
$index = intval($_GET["index"]);

if ($hid > 0) {
	$list = $db->query("select id,name from index_module where hospital_id=0 or hospital_id=$hid order by sort desc, name asc", "id", "name");
} else {
	$list = array();
}

echo ' var arr='.FastJSON::convert($list).";";
echo 'update_hid_qudao_do(arr, '.$index.');';

?>