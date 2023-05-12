<?php
// --------------------------------------------------------
// - ����˵�� : ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2010-08-02 14:14
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_web";

// ���пɹ�����Ŀ:
if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
	$types_full = $db->query("select id,name,wangluo_zuzhang,zixun_zhuguan from count_type where ishide=0 and type='web' order by name asc", "id");
} else {
	$hids = implode(",", $hospital_ids);
	$types_full = $db->query("select id,name,wangluo_zuzhang,zixun_zhuguan from count_type where ishide=0 and type='web' and hid in ($hids) order by name asc", "id");
}

$name_max_lens = 0;
foreach ($types_full as $k => $v) {
	$types[$k] = $v["name"];
	$fname = trim($v["name"]);
	if (strlen($fname) > $name_max_lens) {
		$name_max_lens = strlen($fname);
	}
}

// Ч��1
$char_add_lens = $name_max_lens + 1;
foreach ($types_full as $k => $v) {
	$fname = str_pad(trim($v["name"]), $char_add_lens, "-", STR_PAD_RIGHT);
	//$fname .= str_pad(trim($v["wangluo_zuzhang"]), 7, "-", STR_PAD_RIGHT);
	//$fname .= str_pad(trim($v["zixun_zhuguan"]), 6, "-", STR_PAD_RIGHT);
	$type_name_full[$k] = rtrim($fname, "-");
}

/*
// Ч��2
foreach ($types_full as $k => $v) {
	$fname = trim($v["name"]);
	$fname = str_pad($fname, strlen($fname)+3, "-", STR_PAD_RIGHT);
	$s = trim($v["wangluo_zuzhang"]);
	$fname .= str_pad($s, strlen($s)+2, "-", STR_PAD_RIGHT);
	$fname .= trim($v["zixun_zhuguan"]);
	$type_name_full[$k] = rtrim($fname, "-");
}
*/


if ($debug_mode) {
	//echo "<pre>";
	//print_r($type_name_full);
	//exit;
}


if (count($types) == 0) {
	exit("û�п��Թ������Ŀ");
}

$cur_type = $_SESSION["count_type_id_web"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_web"] = $type_ids[0];
}


if ($_GET["date"] && strlen($_GET["date"]) == 6) {
	$date = $_GET["date"];
} else {
	$date = date("Ym"); //����
	$_GET["date"] = $date;
}
$date_time = strtotime(substr($date,0,4)."-".substr($date,4,2)."-01 0:0:0");

// ���� ��,�� ����
$y_array = $m_array = $d_array = array();
for ($i = date("Y"); $i >= (date("Y") - 2); $i--) $y_array[] = $i;
for ($i = 1; $i <= 12; $i++) $m_array[] = $i;
for ($i = 1; $i <= 31; $i++) {
	if ($i <= 28 || checkdate(date("n", $date_time), $i, date("Y", $date_time))) {
		$d_array[] = $i;
	}
}



// �����Ĵ���:
if ($op = $_REQUEST["op"]) {
	if ($op == "add") {
		include "count_web_edit.php";
		exit;
	}

	if ($op == "edit") {
		include "count_web_edit.php";
		exit;
	}

	if ($op == "change_type") {
		$cur_type = $_SESSION["count_type_id_web"] = intval($_GET["type_id"]);
	}
}


$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);
$kefu_list = $type_detail["kefu"] ? explode(",", $type_detail["kefu"]) : array();


// ���½���:
$month_end = strtotime("+1 month", $date_time);

$b = date("Ymd", $date_time);
$e = date("Ymd", $month_end);


// ��ѯ��ǰʱ��εĿͷ� @ 2012-03-07
$lizhi_kefu = array();
$data_kefu = $db->query("select distinct kefu from $table where type_id=$cur_type and date>=$b and date<$e order by kefu asc", "", "kefu");
if (count($data_kefu) > 0) {
	foreach ($data_kefu as $v) {
		if (!in_array($v, $kefu_list)) {
			$lizhi_kefu[] = $v;
		}
	}
}
$option_kefu = array(); //�����б�ʹ�õĿͷ�
foreach ($kefu_list as $v) {
	if (trim($v)) {
		$option_kefu[$v] = trim($v);
	}
}
foreach ($lizhi_kefu as $v) {
	if (trim($v)) {
		$option_kefu[$v] = trim($v).' (��ְ)';
	}
}
if ($_GET["kefu"] && !array_key_exists($_GET["kefu"], $option_kefu)) {
	$option_kefu[$_GET["kefu"]] = $_GET["kefu"]." (��ְ)";
}



$cur_kefu = $_GET["kefu"];
if ($cur_kefu) {
	// ��ѯ�����ͷ�����:
	$list = $db->query("select * from $table where type_id=$cur_type and kefu='$cur_kefu' and date>=$b and date<$e order by date asc,kefu asc", "date");
	$sql = $db->sql;

	// ��������:
	foreach ($list as $k => $v) {
		// ��ѯԤԼ��:
		$list[$k]["per_1"] = @round($v["talk"] / $v["click"] * 100, 2);
		// ԤԼ������:
		$list[$k]["per_2"] = @round($v["come"] / $v["orders"] * 100, 2);
		// ��ѯ������:
		$list[$k]["per_3"] = @round($v["come"] / $v["click"] * 100, 2);
		// ��Ч��ѯ��:
		$list[$k]["per_4"] = @round($v["ok_click"] / $v["click"] * 100, 2);
		// ��ЧԤԼ��:
		$list[$k]["per_5"] = @round($v["talk"] / $v["ok_click"] * 100, 2);
	}

	// ����ͳ������:
	$cal_field = explode(" ", "click click_local click_other zero_talk other qq ok_click ok_click_local ok_click_other talk talk_local talk_other orders order_local order_other come come_local come_other");
	// ����:
	$sum_list = array();
	foreach ($list as $v) {
		foreach ($cal_field as $f) {
			$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
		}
	}

	// ��ѯԤԼ��:
	$sum_list["per_1"] = @round($sum_list["talk"] / $sum_list["click"] * 100, 2);
	// ԤԼ������:
	$sum_list["per_2"] = @round($sum_list["come"] / $sum_list["orders"] * 100, 2);
	// ��ѯ������:
	$sum_list["per_3"] = @round($sum_list["come"] / $sum_list["click"] * 100, 2);
	// ��Ч��ѯ��:
	$sum_list["per_4"] = @round($sum_list["ok_click"] / $sum_list["click"] * 100, 2);
	// ��ЧԤԼ��:
	$sum_list["per_5"] = @round($sum_list["talk"] / $sum_list["ok_click"] * 100, 2);



} else {
	//��ѯ��ҽԺ��������:
	$tmp_list = $db->query("select * from $table where type_id=$cur_type and date>=$b and date<$e order by date asc,kefu asc");
	$sql = $db->sql;

	// �������:
	$list = $dt_count = array();
	foreach ($tmp_list as $v) {
		$dt = $v["date"];
		$dt_count[$dt] += 1;
		foreach ($v as $a => $b) {
			if ($b && is_numeric($b)) {
				$list[$dt][$a] = floatval($list[$dt][$a]) + $b;
			}
		}
	}

	// ��������:
	foreach ($list as $k => $v) {
		// ��ѯԤԼ��:
		$list[$k]["per_1"] = @round($v["talk"] / $v["click"] * 100, 2);
		// ԤԼ������:
		$list[$k]["per_2"] = @round($v["come"] / $v["orders"] * 100, 2);
		// ��ѯ������:
		$list[$k]["per_3"] = @round($v["come"] / $v["click"] * 100, 2);
		// ��Ч��ѯ��:
		$list[$k]["per_4"] = @round($v["ok_click"] / $v["click"] * 100, 2);
		// ��ЧԤԼ��:
		$list[$k]["per_5"] = @round($v["talk"] / $v["ok_click"] * 100, 2);
	}

	// ����ͳ������:
	$cal_field = explode(" ", "click click_local click_other zero_talk other qq ok_click ok_click_local ok_click_other talk talk_local talk_other orders order_local order_other come come_local come_other");
	// ����:
	$sum_list = array();
	foreach ($list as $v) {
		foreach ($cal_field as $f) {
			$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
		}
	}

	// ��ѯԤԼ��:
	$sum_list["per_1"] = @round($sum_list["talk"] / $sum_list["click"] * 100, 2);
	// ԤԼ������:
	$sum_list["per_2"] = @round($sum_list["come"] / $sum_list["orders"] * 100, 2);
	// ��ѯ������:
	$sum_list["per_3"] = @round($sum_list["come"] / $sum_list["click"] * 100, 2);
	// ��Ч��ѯ��:
	$sum_list["per_4"] = @round($sum_list["ok_click"] / $sum_list["click"] * 100, 2);
	// ��ЧԤԼ��:
	$sum_list["per_5"] = @round($sum_list["talk"] / $sum_list["ok_click"] * 100, 2);

}


// �Ƿ�����ӻ��޸�����:
$can_edit_data = 0;
if ($debug_mode || in_array($uinfo["part_id"], array(9)) || in_array($uid, explode(",", $type_detail["uids"]))) {
	$can_edit_data = 1;
}


/*
// ------------------ ���� -------------------
*/
function my_show($arr, $default_value='', $click='') {
	$s = '';
	foreach ($arr as $v) {
		if ($v == $default_value) {
			$s .= '<b>'.$v.'</b>';
		} else {
			$s .= '<a href="#" onclick="'.$click.'">'.$v.'</a>';
		}
	}
	return $s;
}


// ҳ�濪ʼ ------------------------
?>
<html>
<head>
<title>��������ͳ��</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
body {padding:5px 8px; }
form {display:inline; }
#date_tips {float:left; font-weight:bold; padding-top:1px; }
#ch_date {float:left; margin-left:20px; }
.site_name {display:block; padding:4px 0px;}
.site_name, .site_name a {font-family:"Arial", "Tahoma"; }
.ch_date_a b, .ch_date_a a {font-family:"Arial"; }
.ch_date_a b {border:0px; padding:1px 5px 1px 5px; color:red; }
.ch_date_a a {border:0px; padding:1px 5px 1px 5px; }
.ch_date_a a:hover {border:1px solid silver; padding:0px 4px 0px 4px; }
.ch_date_b {padding-top:8px; text-align:left; width:80%; color:silver; }
.ch_date_b a {padding:0 3px; }

.main_title {margin:0 auto; padding-top:30px; padding-bottom:15px; text-align:center; font-weight:bold; font-size:12px; font-family:"����"; }

.item {padding:8px 3px 6px 3px !important; }
.item {font-family:"Tahoma"; }

.head {padding:12px 3px !important;}

.rate_tips {padding:30px 0 0 30px; line-height:24px; }

.tr_high_light td {background:#FFE1D2; }
</style>

<script language="javascript">
function update_date(type, o) {
	byid("date_"+type).value = parseInt(o.innerHTML, 10);

	var a = parseInt(byid("date_1").value, 10);
	var b = parseInt(byid("date_2").value, 10);

	var s = a + '' + (b<10 ? "0" : "") + b;

	byid("date").value = s;
	byid("cha_date").submit();

	return false;
}

function hgo(dir, o) {
	var obj = byid("type_id");
	if (dir == "up") {
		if (obj.selectedIndex > 1) {
			obj.selectedIndex = obj.selectedIndex - 1;
			obj.onchange();
			o.disabled = true;
		} else {
			parent.msg_box("�Ѿ�����ǰ��", 3);
		}
	}
	if (dir == "down") {
		if (obj.selectedIndex < obj.options.length-1) {
			obj.selectedIndex = obj.selectedIndex + 1;
			obj.onchange();
			o.disabled = true;
		} else {
			parent.msg_box("�Ѿ������һ����", 3);
		}
	}
}

window.last_high_obj = '';
function set_high_light(obj) {
	if (last_high_obj) {
		last_high_obj.parentNode.parentNode.className = "";
	}
	if (obj) {
		obj.parentNode.parentNode.className = "tr_high_light";
		last_high_obj = obj;
	} else {
		last_high_obj = '';
	}
}

function add(link, obj) {
	set_high_light(obj);
	parent.load_src(1, link, 700, 400);
	return false;
}

function edit(link, obj) {
	set_high_light(obj);
	parent.load_src(1, link, 700, 400);
	return false;
}

</script>
</head>

<body>
<div style="margin:10px 0 0 0px;">
	<div id="date_tips">��ѡ�����ڣ�</div>
	<span class="ch_date_a">�꣺<?php echo my_show($y_array, date("Y", $date_time), "return update_date(1,this)"); ?>&nbsp;&nbsp;&nbsp;</span>
	<span class="ch_date_a">�£�<?php echo my_show($m_array, date("m", $date_time), "return update_date(2,this)"); ?>&nbsp;&nbsp;&nbsp;</span>
	<input type="hidden" id="date_1" value="<?php echo date("Y", $date_time); ?>">
	<input type="hidden" id="date_2" value="<?php echo date("n", $date_time); ?>">

	<form method="GET" name="cha_date" id="cha_date">
		<input type="hidden" name="date" id="date" value="">
		<input type="hidden" name="kefu" value="<?php echo $kefu; ?>">
	</form>
	<div class="clear"></div>
</div>

<div style="margin:10px 0 0 0px;">
	<div id="date_tips">ҽԺ��Ŀ��</div>
	<form method="GET" style="margin-left:30px;">
		<select name="type_id" id="type_id" class="combo" onchange="this.form.submit()">
			<?php echo list_option($type_name_full, "_key_", "_value_", $cur_type); ?>
		</select>&nbsp;
		<button class="button" onclick="hgo('up',this);">��</button>&nbsp;
		<button class="button" onclick="hgo('down',this);">��</button>
		<input type="hidden" name="date" value="<?php echo $_GET["date"]; ?>">
		<input type="hidden" name="op" value="change_type">
	</form>&nbsp;&nbsp;&nbsp;

	<b>�ͷ���</b>
	<form method="GET">
	<select name="kefu" class="combo" onchange="this.form.submit()">
		<option value="" style="color:gray">-����ҽԺ-</option>
		<?php echo list_option($option_kefu, "_key_", "_value_", $_GET["kefu"]); ?>
	</select>
	<input type="hidden" name="date" value="<?php echo $date; ?>">
	</form>&nbsp;&nbsp;&nbsp;

	<button onclick="location='count_web_compare.php'" class="buttonb" title="�鿴�ͷ����ݶԱ�">���ݶԱ�</button>&nbsp;&nbsp;
	<button onclick="location='count_web_compare_week.php?month=<?php echo date("Y-m", $date_time); ?>'" class="buttonb" title="�鿴�����ݶԱ�">�ܶԱ�</button>&nbsp;&nbsp;
	<button onclick="location='count_web_report.php'" class="buttonb" title="�鿴ͳ������">ͳ������</button>&nbsp;&nbsp;
<?php if ($debug_mode || $username == "admin") { ?>
	<button onclick="location='count_repeatcheck.php'" class="buttonb" title="�ظ����ݼ��">�ظ����</button>&nbsp;&nbsp;
<?php } ?>

	<button onclick="self.location.reload(); return false;" class="button" title="ˢ��ҳ��">ˢ��</button>

</div>

<div class="main_title"><?php echo $type_detail["name"]." (".$type_detail["zixun_zuzhang"].")"; ?> - <?php echo date("Y-n", $date_time); ?><?php if ($cur_kefu) echo " - �ͷ� ".$cur_kefu.""; ?> ����ͳ������</div>


<!-- ������ͷ ע�⣺�˼�����Ҫָ��ÿ����Ԫ��Ŀ�ȷ������±����ܲ����� -->
<div id="to_float">
	<table id="data_list_float_head" width="100%" align="center" cellpadding="0" cellspacing="0" class="list">
		<tr>
			<td></td>
		</tr>
	</table>
</div>

<table id="data_list" width="100%" align="center" cellpadding="0" cellspacing="0" class="list">
	<tr id="data_head">
		<td class="head" align="center" width="60">����</td>
		<td class="head" align="center" style="color:red">�ܵ��</td>
		<td class="head" align="center">����</td>
		<td class="head" align="center">���</td>
		<td class="head" align="center" style="color:red">����Ч</td>
		<td class="head" align="center">����</td>
		<td class="head" align="center">���</td>

		<td class="head" align="center" style="color:red">����Լ</td>
		<td class="head" align="center" style="color:red">Ԥ�Ƶ�Ժ</td>
		<td class="head" align="center" style="color:red">ʵ�ʵ�Ժ</td>
		<td class="head" align="center" style="color:red">QQ</td>
		<td class="head" align="center" style="color:red">����</td>

		<td class="head" align="center" style="color:red">��ѯԤԼ��</td>
		<td class="head" align="center" style="color:red">ԤԼ������</td>
		<td class="head" align="center" style="color:red">��ѯ������</td>
		<td class="head" align="center" style="color:red">��Ч��ѯ��</td>
		<td class="head" align="center" style="color:red">��ЧԤԼ��</td>

		<td class="head" align="center">����</td>
	</tr>

<?php
foreach ($d_array as $i) {
	$cur_date = date("Ymd", strtotime(date("Y-m-", $date_time).$i." 0:0:0"));
	$li = $list[$cur_date];
	if (!is_array($li)) {
		$li = array();
	}

?>
	<tr>
		<td class="item" align="center"><?php echo date("n", $date_time); ?>��<?php echo $i; ?>��</td>
		<td class="item" align="center" style="color:red"><?php echo $li["click"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["talk"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["orders"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["come"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["qq"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["other"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_1"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_2"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_3"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_4"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_5"]); ?>%</td>

		<td class="item" align="center">
<?php if ($cur_kefu && $can_edit_data) { ?>
			<?php if (!$li) { ?>
			<a onclick="add(this.href,this);return false;" href="?op=add&kefu=<?php echo urlencode($cur_kefu); ?>&date=<?php echo date("Y-m-", $date_time).$i; ?>">���</a>
			<?php } else { ?>
			<a onclick="edit(this.href,this);return false;" href="?op=edit&kefu=<?php echo urlencode($cur_kefu); ?>&date=<?php echo date("Y-m-", $date_time).$i; ?>">�޸�</a>
			<?php } ?>
<?php } ?>
		</td>
	</tr>

<?php } ?>

	<tr>
		<td colspan="30" class="tips">���ݻ���</td>
	</tr>

	<tr>
		<td class="item" align="center">����</td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_other"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo $sum_list["talk"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["orders"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["come"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["qq"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["other"]; ?></td>

		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_1"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_2"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_3"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_4"]); ?>%</td>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_5"]); ?>%</td>

		<td class="item" align="center">
			-
		</td>
	</tr>

	<tr>
		<td class="item" align="center">�վ�</td>
		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["click"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["click_local"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["click_other"] / count($list), 1); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["ok_click"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["ok_click_local"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["ok_click_other"] / count($list), 1); ?></td>

		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["talk"] / count($list), 1); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["orders"] / count($list), 1); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["come"] / count($list), 1); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["qq"] / count($list), 1); ?></td>
		<td class="item" align="center" style="color:red"><?php echo @round($sum_list["other"] / count($list), 1); ?></td>

		<td class="item" align="center" style="color:red"></td>
		<td class="item" align="center" style="color:red"></td>
		<td class="item" align="center" style="color:red"></td>
		<td class="item" align="center" style="color:red"></td>
		<td class="item" align="center" style="color:red"></td>

		<td class="item" align="center">
			-
		</td>
	</tr>
</table>

<div class="rate_tips">
��ѯԤԼ�� = ԤԼ���� / �ܵ��<br>
ԤԼ������ = ʵ�ʵ�Ժ���� / Ԥ�Ƶ�Ժ����<br>
��ѯ������ = ʵ�ʵ�Ժ���� / �ܵ��<br>
��Ч��ѯ�� = ��Ч��� / �ܵ��<br>
��ЧԤԼ�� = ԤԼ���� / ��Ч���<br>
</div>

<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

<script type="text/javascript">
function scroll_table() {
	var s_top = document.body.scrollTop;
	var top = byid(float_table).offsetTop;
	var top_head = byid(float_table+"_float_head").offsetHeight;

	if (s_top >= (0 + top + top_head)) {
		var o = byid(float_table+"_float_head");
		o.style.display = "";
		o.style.position = "absolute";
		o.style.top = s_top;
	} else {
		byid(float_table+"_float_head").style.display = "none";
	}
};

function make_float(table_id) {
	var t = byid(table_id);
	if (t) {
		var th = t.getElementsByTagName("TR")[0];
		if (th) {
			var newNode = th.cloneNode(true);
			byid("data_list_float_head").getElementsByTagName("TR")[0].appendChild(newNode);
			window.onscroll = scroll_table;
		}
	}
}

var float_table = "data_list";
make_float(float_table);
</script>

</body>
</html>
