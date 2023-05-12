<?php
/*
// 说明: 统计报表的分类方式
// 作者: 幽兰 (weelia@126.com)
// 时间: 2016-07-25
*/


$hid_str = count($hospital_ids) ? implode(",", $hospital_ids) : "0";
$all_hid_to_list = $db->query("select hid, count(hid) as c from count_type where type='web' and ishide=0 and hid in ($hid_str) group by hid", "", "hid");

$hid_str2 = count($all_hid_to_list) ? implode(",", $all_hid_to_list) : "0";
$hid_arr = $db->query("select * from hospital where ishide=0 and id in ($hid_str2) order by sort desc, name asc", "id");

$allow_hid = array_keys($hid_arr);

// 计算合并关系:
foreach ($hid_arr as $_hid => $li) {
	if ($li["count_web_hids"] == "-1") {
		$_hname = $li["sname"];
		$hebing_arr[$_hid] = $db->query("select id from hospital where ishide=0 and sname='$_hname'", "", "id");
	} else if ($li["count_web_hids"] != "") {
		$hebing_arr[$_hid] = explode(",", $li["count_web_hids"]);
	} else {
		$hebing_arr[$_hid] = array($_hid);
	}
	$hebing_string[$_hid] = count($hebing_arr[$_hid]) > 0 ? implode(",", $hebing_arr[$_hid]) : "0";
}

//echo "<pre>";
//print_r($hebing_string);

?>