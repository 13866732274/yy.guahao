<?php
/*
// ˵��: �����ʻ�������
// ����: ���� (weelia@126.com)
// ʱ��: 2017-03-13
*/
require "lib/set_env.php";

if ($hid <= 0) exit("����ѡ��ҽԺ");

$sname = $db->query("select * from hospital where id=$hid limit 1", 1, "sname");

$all_hid_name = $db->query("select * from hospital where sname='$sname' and ishide=0 order by name asc", "id", "depart");

$select_ids = $_GET["do"] == "select" ? $_GET["hids"] : array_keys($all_hid_name);

foreach ($all_hid_name as $hid => $hname) {
	if (in_array($hid, $select_ids)) {
		$data = $db->query("select data from index_cache where hid=$hid limit 1", 1, "data");
		$module_data_arr = @unserialize($data);

		$arr["����ԤԼ������"]["a"] += $module_data_arr["ID_22"]["ʵ��"]["����"];
		$arr["����ԤԼ������"]["b"] += $module_data_arr["ID_22"]["Ԥ��"]["����"];

		$arr["����ԤԼ������"]["a"] += $module_data_arr["ID_20"]["ʵ��"]["����"];
		$arr["����ԤԼ������"]["b"] += $module_data_arr["ID_20"]["Ԥ��"]["����"];

		$arr["��������ԤԼ������"]["a"] += $module_data_arr["ID_8"]["ʵ��"]["����"];
		$arr["��������ԤԼ������"]["b"] += $module_data_arr["ID_8"]["Ԥ��"]["����"];

		$arr["�绰����ԤԼ������"]["a"] += $module_data_arr["ID_23"]["ʵ��"]["����"];
		$arr["�绰����ԤԼ������"]["b"] += $module_data_arr["ID_23"]["Ԥ��"]["����"];
	}
}


?>
<html>
<head>
<title><?php echo $sname; ?> �����ʻ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.new_body {padding:20px 40px; }
.main_title {font-weight:bold; font-size:14px; font-family:"΢���ź�"; }
.head {padding:6px 3px !important;}
.huizong {padding:4px; text-align:center; background-color:#e4e9eb; }
</style>
</head>

<script type="text/javascript">
function set_zero() {
	var g = byid("from1").getElementsByTagName("INPUT");
	for (var i=0; i<g.length; i++) {
		if (g[i].type == "checkbox") g[i].checked = false;
	}
}
function set_all() {
	var g = byid("from1").getElementsByTagName("INPUT");
	for (var i=0; i<g.length; i++) {
		if (g[i].type == "checkbox") g[i].checked = true;
	}
	byid("from1").submit();
}
function set_reverse() {
	var g = byid("from1").getElementsByTagName("INPUT");
	for (var i=0; i<g.length; i++) {
		if (g[i].type == "checkbox") g[i].checked = !g[i].checked;
	}
	byid("from1").submit();
}
</script>

<body class="new_body">


<form method="GET" action="" onsubmit="" id="from1">
	<b>�빴ѡҪ���ܵĿ��ң�</b>
<?php
	foreach ($all_hid_name as $hid => $hname) {
		$is_check = in_array($hid, $select_ids) ? ' checked' : "";
		$style = $is_check ? ' style="color:red; font-weight:bold;"' : "";
		echo '<input type="checkbox" name="hids[]" onclick="this.form.submit()" value="'.$hid.'" '.$is_check.' id="h_'.$hid.'"><label for="h_'.$hid.'" '.$style.'>'.$hname.'</label> ';
	}
?>
	����<a href="javascript:;" onclick="set_zero();">[�����ѡ]</a>��<a href="javascript:;" onclick="set_reverse();">[����ѡ��]</a>��<a href="javascript:;" onclick="set_all();">[ȫ��ѡ��]</a>
	<input type="hidden" name="do" value="select">
</form>


<br>
<br>
��ѡ<b><?php echo count($select_ids); ?></b>�����ҵľ����ʣ�<br>
<br>
����ԤԼ������ = <b><?php echo @round(100 * $arr["����ԤԼ������"]["a"] / $arr["����ԤԼ������"]["b"], 1)."%"; ?></b><br>
����ԤԼ������ = <b><?php echo @round(100 * $arr["����ԤԼ������"]["a"] / $arr["����ԤԼ������"]["b"], 1)."%"; ?></b><br>
��������ԤԼ������ = <b><?php echo @round(100 * $arr["��������ԤԼ������"]["a"] / $arr["��������ԤԼ������"]["b"], 1)."%"; ?></b><br>
�绰����ԤԼ������ = <b><?php echo @round(100 * $arr["�绰����ԤԼ������"]["a"] / $arr["�绰����ԤԼ������"]["b"], 1)."%"; ?></b><br>

<br>
<br>
<br>

</body>
</html>
