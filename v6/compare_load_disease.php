<?php
/*
// 说明: 加载对比的疾病列表
// 作者: 幽兰 (weelia@126.com)
// 时间: 2014-03-20
*/
include "lib/set_env.php";
include "lib/class.fastjson.php";

$hid = intval($_GET["hid"]);
$index = intval($_GET["index"]);

if ($hid > 0) {
	$list = $db->query("select id,name from disease where hospital_id=$hid order by sort desc, name asc", "id", "name");
} else {
	$list = array();
}

echo ' var arr=' . FastJSON::convert($list) . ";";
echo 'update_hid_disease_do(arr, ' . $index . ');';
