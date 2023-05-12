<?php
/*
// ˵��: ��������ݻ���
// ����: ���� (weelia@126.com)
// ʱ��: 2014-12-30
*/
require "lib/set_env.php";

$hids = implode(",", $hospital_ids);
$s_arr = $db->query("select sname,count(sname) as c from hospital where ishide=0 and id in ($hids) and id not in (15) group by sname order by sname asc", "sname", "c");
foreach ($s_arr as $sname => $count) {
	$s_arr2[$sname] = $sname." [".$count."]";
	//if ($count <= 1) unset($s_arr[$sname]);
}

// ��ѯÿ��ҽԺ����������:
foreach ($s_arr as $sname => $count) {
	$h_arr[$sname] = $db->query("select id,depart as name from hospital where ishide=0 and id in ($hids) and sname='$sname' order by name asc", "id", "name");
}

function _list_check($check_name, $arr, $key_use, $value_use, $default_value=array(), $split_char = " ", $onclick="") {
	$res = array();
	$id_base = $check_name."_".mt_rand(1, 9999)."_";
	foreach ($arr as $k => $v) {
		$id = $id_base.$k;
		$key = $key_use == "k" ? $k : $v;
		$value = $value_use == "k" ? $k : $v;
		$check = @in_array($key, $default_value) ? " checked" : "";
		$click = $onclick ? ' onclick="'.$onclick.'"' : "";
		$res[] = '<input type="checkbox" name="'.$check_name.'" value="'.$key.'" id="'.$id.'"'.$check.$click.'><label for="'.$id.'">'.$value.'</label>';
	}
	return implode($split_char, $res);
}


$date_mode = 0;
if ($_GET["from_date"] != '' && $_GET["to_date"] != '') {
	$from_date = $_GET["from_date"];
	$to_date = $_GET["to_date"];
	$from_time = strtotime($from_date);
	$to_time = strtotime($to_date." 23:59:59");
	$date_mode = 1;
}


header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
?>
<html>
<head>
<title>��������ݻ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.aline {margin-left:20px; float:left; width:130px; }
.submit_line {text-align:center; padding:10px;}

.m_name {width:200px; border-right:0px !important; }
.m_op {border-left:0px !important; }
.m_select {border:1px solid #c6c6c6 !important; width:100%; }
.m_select td {border:1px solid #c6c6c6 !important; padding:4px 8px !important; }

</style>
<script language="javascript">
function menu_sub_check(o) {
	var value = o.value;
	var g = byid("sub_check_"+value).getElementsByTagName("INPUT");
	for (var i = 0; i < g.length; i++) {
		g[i].checked = o.checked;
	}
}

var menu_check_flag = true;
function menu_check_all() {
	var g = byid("menu_select_area").getElementsByTagName("INPUT");
	for (var i = 0; i < g.length; i++) {
		g[i].checked = menu_check_flag;
	}
	menu_check_flag = !menu_check_flag;
}

function update_sub_hid(s, check_all) {
	var g = byid("sub_hid_select").getElementsByTagName("DIV");
	for (var i=0; i<g.length; i++) {
		g[i].style.display = "none";
	}
	byid("s_"+s).style.display = "";
	if (check_all) {
		var objs = byid("s_"+s).getElementsByTagName("INPUT");
		for (var i=0; i<objs.length; i++) {
			if (objs[i].type == "checkbox") {
				objs[i].checked = "checked";
			}
		}
	}
}
</script>
</head>

<body>

<?php
if (count($s_arr) == 0) {
	echo '<div id="no_data">û�пɹ����ܵĿ���</div>';
} else {
?>

<form method="GET" action="" onsubmit="">
<table class="m_select" id="menu_select_area">
	<tr>
		<td style="width:120px" align="right">��ѡ��ҽԺ��</td>
		<td style="">
			<select name="g" class="combo" onchange="update_sub_hid(this.value, 1)">
				<option value="">--��ѡ��ҽԺ--</option>
				<?php echo list_option($s_arr2, "_key_", "_value_", $_GET["g"]); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right">ѡ�������ң�</td>
		<td id="sub_hid_select">
<?php
foreach ($s_arr as $sname => $count) {
	echo '<div id="s_'.$sname.'" style="display:none">';
	echo _list_check("gg[]", array($sname=>"ȫѡ"), "k", "v", array(), " ", "menu_sub_check(this)");
	echo '	<span id="sub_check_'.$sname.'">&nbsp;';
	echo _list_check("sub_hid[$sname][]", $h_arr[$sname], "k", "v", $_GET["sub_hid"][$sname]);
	echo '	</span>';
	echo '</div>';
}
?>
		</td>
	</tr>
	<tr>
		<td align="right">������ֹ��</td>
		<td>
			<input name="from_date" id="from_date" class="input" size="12" value="<?php echo $from_date; ?>" onclick="picker({el:'from_date',dateFmt:'yyyy-MM-dd'})"> ~ <input name="to_date" id="to_date" class="input" size="12" value="<?php echo $to_date; ?>" onclick="picker({el:'to_date',dateFmt:'yyyy-MM-dd'})"> &nbsp;
			<a href="javascript:;" onclick="byid('from_date').value = byid('to_date').value = '';">�������</a>
		</td>
	</tr>

	<tr>
		<td align="right"></td>
		<td>
			<input type="submit" class="buttonb" value="��˲�ѯ">
			<input type="button" onclick="self.location.reload();" class="button" value="ˢ��" style="margin-left:300px;">
		</td>
	</tr>
</table>
</form>
<?php } ?>

<script type="text/javascript">
update_sub_hid("<?php echo $_GET["g"]; ?>");
</script>

<style type="text/css">
.m_zhu td {text-align:center; }
</style>


<?php

function _get_data($_arr, $cid) {
	return $_arr["ID_".$cid];
}

function _hebing($arr1, $arr2) {
	foreach ($arr2 as $k => $v) {
		foreach ($v as $k2 => $v2) {
			$arr1[$k][$k2] += $v2;
		}
	}
	return $arr1;
}


function z($bid, $day, $type) {
	global $cid_data;
	$arr = $cid_data[$bid];

	if ($type == "δ��") {
		$data = $arr["Ԥ��"][$day] - $arr["ʵ��"][$day];
	} else {
		$data = intval($arr[$type][$day]);
	}

	return ' <b class="fa">'.$data.'</b>';
}


$cid[1] = 2;
$cid[2] = 3;
$cid[3] = 4;
$cid[4] = 50;
$cid[5] = 57;
$cid[6] = 77;
$cid[7] = 82;

if ($_GET) {
	$hids = $_GET["sub_hid"][$_GET["g"]];

	if ($date_mode) {
		// todo
		//echo 	$from_date." ~ ".$to_date." <br>";
		//echo 	$from_time." ~ ".$to_time." <br>";
		//echo "�ò��ֹ�������ʵ���У����Ժ����";
		//print_r($hids);

		echo '<br><br><table class="m_select m_zhu" id="menu_select_area">';
		echo '<tr class="head"><td></td><td>ԤԼ</td><td>Ԥ��</td><td>ʵ��</td><td>δ��</td>';

		$cid_data = array();
		foreach ($cid as $i => $index_id) {
			$m_info = $db->query("select * from index_module where id=$index_id limit 1", 1);
			$sql = $m_info["condition_code"] ? "(".$m_info["condition_code"].")" : "1";
			// ��ѯ��ʱ�䷶Χ�ڵ�����:
			foreach ($hids as $_hid) {
				$cid_data[$index_id][1] += $db->query("select count(*) as c from patient_{$_hid} where $sql and addtime>=$from_time and addtime<=$to_time and part_id!=4", 1, "c");
				$cid_data[$index_id][2] += $db->query("select count(*) as c from patient_{$_hid} where $sql and order_date>=$from_time and order_date<=$to_time", 1, "c");
				$cid_data[$index_id][3] += $db->query("select count(*) as c from patient_{$_hid} where $sql and order_date>=$from_time and order_date<=$to_time and status=1", 1, "c");
			}
			$cid_data[$index_id][4] = $cid_data[$index_id][2] - $cid_data[$index_id][3];

			echo '<tr><td>'.$m_info["name"].'</td><td>'.$cid_data[$index_id][1].'</td><td>'.$cid_data[$index_id][2].'</td><td>'.$cid_data[$index_id][3].'</td><td>'.$cid_data[$index_id][4].'</td>';
		}

		echo '</table>';
		//print_r($cid_data);

	} else {
		$cid_data = array();
		$show_xinmeiti = 0;
		foreach ($hids as $_hid) {
			$h_name = $db->query("select name from hospital where id=$_hid limit 1", 1, "name");
			$cache_line = $db->query("select * from index_cache where hid=$_hid limit 1", 1);
			$_arr = @unserialize($cache_line["data"]);

			foreach ($cid as $i => $index_id) {
				if ($i == 2 && substr_count($h_name, "��ý��") > 0) {
					$show_xinmeiti = 1;
					$cid_data[99] = _hebing($cid_data[99], _get_data($_arr, $cid[$i]));
				} else {
					$cid_data[$i] = _hebing($cid_data[$i], _get_data($_arr, $cid[$i]));
				}
			}
		}

?>


<style type="text/css">
.outer {margin-top:10px; margin-left:70px; }
.box_float {margin-top:10px; margin-left:10px; float:left; }

.fa, .fb, .fc {font-family:"Arial"; color:#FF8040; }
.fa {}
.fb {color:blue; }
.fb:hover {color:red; }

.list_data {border:1px solid #a1a1a1; }
.list_data td {border-bottom:1px solid #d4d4d4; }
.list_title {background-color:#dfeee3; padding:3px 5px; text-align:left; }
.dan td { }
.shuang td {background-color:#f9f9f9; }
.d0 {padding:3px 5px; text-align:left; background-color:#ffd6ac }
.d1 {padding:3px 3px; text-align:center; width:40px; }
.d2 {padding:3px 3px; text-align:left; width:60px; }
.d3 {padding:3px 3px; text-align:left; width:60px; }
.d4 {padding:3px 3px; text-align:left; width:60px; }
.d5 {padding:3px 3px; text-align:left; width:60px; }

.dweb {padding:3px 3px 3px 8px; text-align:left; }
.dbr {border-right:1px solid #d4d4d4 !important; }

#function_zhu {margin:20px 0 0 20px; }
.file_show {color:#ff00ff; font-family:"΢���ź�"; }
</style>

<div class="outer">

	<?php $bid = 1; ?>
	<div class="box_float">
		<table class="list_data" width="312">
			<tr>
				<td class="d0 red l" colspan="5" style="background-color: #9cd3b8;"><b>������</b> (<?php echo $_GET["g"].count($hids)."������"; ?>)</td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($bid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($bid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($bid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($bid, "����ͬ��", "δ��")); ?>">ͬ��</td>
				<td class="d2">ԤԼ<?php echo z($bid, "ͬ��", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "ͬ��", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "ͬ��", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "ͬ��", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
		</table>
	</div>

	<?php $bid = 2; ?>
	<div class="box_float">
		<table class="list_data" width="312">
			<tr>
				<td class="d0 red l" colspan="5" style="background-color: #ffd5bf;"><b>����</b> (<?php echo $_GET["g"].count($hids)."������"; ?>) <?php if ($show_xinmeiti) echo "(������ý��)"; ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($bid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($bid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($bid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($bid, "����ͬ��", "δ��")); ?>">ͬ��</td>
				<td class="d2">ԤԼ<?php echo z($bid, "ͬ��", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "ͬ��", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "ͬ��", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "ͬ��", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
		</table>
	</div>

	<?php $bid = 3; ?>
	<div class="box_float">
		<table class="list_data" width="312">
			<tr>
				<td class="d0 red l" colspan="5" style="background-color: #d3ddeb;"><b>�绰</b> (<?php echo $_GET["g"].count($hids)."������"; ?>)</td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($bid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($bid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($bid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($bid, "����ͬ��", "δ��")); ?>">ͬ��</td>
				<td class="d2">ԤԼ<?php echo z($bid, "ͬ��", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "ͬ��", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "ͬ��", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "ͬ��", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
		</table>
	</div>


	<!-- ��ý�� -->
<?php if ($show_xinmeiti) { ?>
	<?php $bid = 99; ?>
	<div class="box_float">
		<table class="list_data" width="312">
			<tr>
				<td class="d0 red l" colspan="5" style="background-color: #d3ddeb;"><b>��ý�� (����)</b></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($bid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($bid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($bid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($bid, "����ͬ��", "δ��")); ?>">ͬ��</td>
				<td class="d2">ԤԼ<?php echo z($bid, "ͬ��", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "ͬ��", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "ͬ��", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "ͬ��", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
		</table>
	</div>
<?php } ?>

	<?php $bid = 4; ?>
	<div class="box_float">
		<table class="list_data" width="312">
			<tr>
				<td class="d0 red l" colspan="5" style="background-color: #d3ddeb;"><b>��Ȼ����</b> (<?php echo $_GET["g"].count($hids)."������"; ?>)</td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($bid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($bid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($bid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($bid, "����ͬ��", "δ��")); ?>">ͬ��</td>
				<td class="d2">ԤԼ<?php echo z($bid, "ͬ��", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "ͬ��", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "ͬ��", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "ͬ��", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
		</table>
	</div>

	<?php $bid = 5; ?>
	<div class="box_float">
		<table class="list_data" width="312">
			<tr>
				<td class="d0 red l" colspan="5" style="background-color: #d3ddeb;"><b>PC(��)</b> (<?php echo $_GET["g"].count($hids)."������"; ?>)</td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($bid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($bid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($bid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($bid, "����ͬ��", "δ��")); ?>">ͬ��</td>
				<td class="d2">ԤԼ<?php echo z($bid, "ͬ��", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "ͬ��", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "ͬ��", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "ͬ��", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
		</table>
	</div>

	<?php $bid = 6; ?>
	<div class="box_float">
		<table class="list_data" width="312">
			<tr>
				<td class="d0 red l" colspan="5" style="background-color: #d3ddeb;"><b>����(��)</b> (<?php echo $_GET["g"].count($hids)."������"; ?>)</td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
			<tr class="shuang">
				<td class="d1" title="����ͬ�ڣ�ԤԼ<?php echo strip_tags(z($bid, "����ͬ��", "ԤԼ")); ?> Ԥ��<?php echo strip_tags(z($bid, "����ͬ��", "Ԥ��")); ?> ʵ��<?php echo strip_tags(z($bid, "����ͬ��", "ʵ��")); ?> δ��<?php echo strip_tags(z($bid, "����ͬ��", "δ��")); ?>">ͬ��</td>
				<td class="d2">ԤԼ<?php echo z($bid, "ͬ��", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "ͬ��", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "ͬ��", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "ͬ��", "δ��"); ?></td>
			</tr>
			<tr class="dan">
				<td class="d1">����</td>
				<td class="d2">ԤԼ<?php echo z($bid, "����", "ԤԼ"); ?></td>
				<td class="d3">Ԥ��<?php echo z($bid, "����", "Ԥ��"); ?></td>
				<td class="d4">ʵ��<?php echo z($bid, "����", "ʵ��"); ?></td>
				<td class="d5">δ��<?php echo z($bid, "����", "δ��"); ?></td>
			</tr>
		</table>
	</div>

</div>

<?php   } ?>

<?php } ?>

<div class="clear"></div>
<br>


</body>
</html>