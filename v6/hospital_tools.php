<?php
/*
// ����: ���� (weelia@126.com)
*/
include "lib/set_env.php";

$preview_mode = ($_GET["real_do"] == "1" ? 0 : 1);

$hid_name = $db->query("select id,name from hospital where ishide=0 order by id asc", "id", "name");
if ($preview_mode == 1) {
	echo '<a href="?real_do=1">��ǰΪԤ��Ч��ģʽ������ִ��д���ݿ����</a>';
	//echo "<pre>";
	//print_r($hid_name);
	//echo "</pre>";
}

$sys_admin_arr = $db->query("select id,realname, hospitals from sys_admin where hospitals!=''", "id");
foreach ($sys_admin_arr as $id => $line) {
	$arr = explode(",", $line["hospitals"]);
	foreach ($arr as $k => $v) {
		if (!array_key_exists($v, $hid_name)) {
			unset($arr[$k]);
		}
	}
	$s = implode(",", $arr);
	if ($s != $line["hospitals"]) {
		if ($preview_mode == 0) {
			$db->query("update sys_admin set hospitals='$s' where id=$id limit 1");
		}
		echo $line["realname"]." :<br>".$line["hospitals"]. "<br>".$s."<br><br>";
	}
}

if ($preview_mode == 0) {
	echo "���ݿ������ɣ�";
}



?>