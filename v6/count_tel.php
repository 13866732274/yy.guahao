<?php
// --------------------------------------------------------
// - ����˵�� : �绰
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2010-10-18
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_tel";

// ���пɹ�����Ŀ:
if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
	$types = $db->query("select id,name from count_type where ishide=0 and type='tel' order by name asc", "id", "name");
} else {
	$hids = implode(",", $hospital_ids);
	$types = $db->query("select id,name from count_type where ishide=0 and type='tel' and hid in ($hids) order by name asc", "id", "name");
}
if (count($types) == 0) {
	exit("û�п��Թ������Ŀ");
}

$cur_type = $_SESSION["count_type_id_tel"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_tel"] = $type_ids[0];
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
		include "count_tel_edit.php";
		exit;
	}

	if ($op == "edit") {
		include "count_tel_edit.php";
		exit;
	}

	if ($op == "change_type") {
		$cur_type = $_SESSION["count_type_id_tel"] = intval($_GET["type_id"]);
	}
}


$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);
$kefu_list = $type_detail["kefu"] ? explode(",", $type_detail["kefu"]) : array();


// ���½���:
$month_end = strtotime("+1 month", $date_time);

$b = date("Ymd", $date_time);
$e = date("Ymd", $month_end);


$cur_kefu = $_GET["kefu"];
if ($cur_kefu) {
	// ��ѯ�����ͷ�����:
	$list = $db->query("select * from $table where type_id=$cur_type and kefu='$cur_kefu' and date>=$b and date<$e order by date asc,kefu asc", "date");

	// ��������:
	foreach ($list as $k => $v) {
		// ��ѯԤԼ��:
		$list[$k]["per_1"] = @round($v["yuyue"] / $v["tel_all"] * 100, 1);
		// ԤԼ������:
		$list[$k]["per_2"] = @round($v["jiuzhen"] / $v["yuyue"] * 100, 1);
		// ��ѯ������:
		$list[$k]["per_3"] = @round($v["jiuzhen"] / $v["tel_all"] * 100, 1);
		// ��Ч��ѯ��:
		$list[$k]["per_4"] = @round($v["tel_ok"] / $v["tel_all"] * 100, 1);
	}

	// ����ͳ������:
	$cal_field = explode(" ", "zongliang tel_all tel_ok yuyue yudao jiuzhen wangluo wuxian ditu guahaowang qita");
	// ����:
	$sum_list = array();
	foreach ($list as $v) {
		foreach ($cal_field as $f) {
			$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
		}
	}

	$sum_list["per_1"] = @round($sum_list["yuyue"] / $sum_list["tel_all"] * 100, 1);
	// ԤԼ������:
	$sum_list["per_2"] = @round($sum_list["jiuzhen"] / $sum_list["yuyue"] * 100, 1);
	// ��ѯ������:
	$sum_list["per_3"] = @round($sum_list["jiuzhen"] / $sum_list["tel_all"] * 100, 1);
	// ��Ч��ѯ��:
	$sum_list["per_4"] = @round($sum_list["tel_ok"] / $sum_list["tel_all"] * 100, 1);

} else {
	//��ѯ��ҽԺ��������:
	$tmp_list = $db->query("select * from $table where type_id=$cur_type and date>=$b and date<$e order by date asc,kefu asc");

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
		$list[$k]["per_1"] = @round($v["yuyue"] / $v["tel_all"] * 100, 1);
		// ԤԼ������:
		$list[$k]["per_2"] = @round($v["jiuzhen"] / $v["yuyue"] * 100, 1);
		// ��ѯ������:
		$list[$k]["per_3"] = @round($v["jiuzhen"] / $v["tel_all"] * 100, 1);
		// ��Ч��ѯ��:
		$list[$k]["per_4"] = @round($v["tel_ok"] / $v["tel_all"] * 100, 1);
	}

	// ����ͳ������:
	$cal_field = explode(" ", "zongliang tel_all tel_ok yuyue yudao jiuzhen wangluo wuxian ditu guahaowang qita");
	// ����:
	$sum_list = array();
	foreach ($list as $v) {
		foreach ($cal_field as $f) {
			$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
		}
	}

	$sum_list["per_1"] = @round($sum_list["yuyue"] / $sum_list["tel_all"] * 100, 1);
	// ԤԼ������:
	$sum_list["per_2"] = @round($sum_list["jiuzhen"] / $sum_list["yuyue"] * 100, 1);
	// ��ѯ������:
	$sum_list["per_3"] = @round($sum_list["jiuzhen"] / $sum_list["tel_all"] * 100, 1);
	// ��Ч��ѯ��:
	$sum_list["per_4"] = @round($sum_list["tel_ok"] / $sum_list["tel_all"] * 100, 1);

}


// �Ƿ�����ӻ��޸�����:
$can_edit_data = 0;
if ($debug_mode || in_array($uinfo["part_id"], array(9)) || in_array($uid, explode(",", $type_detail["uids"])) || $uinfo["modify_count_data"]) {
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
<title>�绰����ͳ��</title>
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

.main_title {margin:0 auto; padding:20px; text-align:center; font-weight:bold; font-size:15px;  }

.item {padding:8px 3px 6px 3px !important; }
.item {font-family:"Tahoma"; }

.head {padding:6px 3px !important; font-weight:normal; font-family:"΢���ź�"; }

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
	parent.load_src(1, link, 700, 500);
	return false;
}

function edit(link, obj) {
	set_high_light(obj);
	parent.load_src(1, link, 700, 500);
	return false;
}
</script>
</head>

<body>
<table width="100%" style="margin-top:15px;">
	<tr>
		<td width="50%" align="left" style="padding-left:10px;">
			<span class="ch_date_a">�꣺<?php echo my_show($y_array, date("Y", $date_time), "update_date(1,this)"); ?>&nbsp;&nbsp;&nbsp;</span>
			<span class="ch_date_a">�£�<?php echo my_show($m_array, date("m", $date_time), "update_date(2,this)"); ?>&nbsp;&nbsp;&nbsp;</span>

			<input type="hidden" id="date_1" value="<?php echo date("Y", $date_time); ?>">
			<input type="hidden" id="date_2" value="<?php echo date("n", $date_time); ?>">

			<form name="cha_date" id="cha_date" method="GET">
				<input type="hidden" name="date" id="date" value="">
			</form>
		</td>
		<td width="50%" align="right" style="padding-right:10px;">
			<form method="GET" style="margin-left:30px;">
				<select name="type_id" id="type_id" class="combo" onchange="this.form.submit()">
					<option value="" style="color:gray">-��ѡ����Ŀ-</option>
					<?php echo list_option($types, "_key_", "_value_", $cur_type); ?>
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
				<?php echo list_option($kefu_list, "_value_", "_value_", $_GET["kefu"]); ?>
			</select>
			<input type="hidden" name="date" value="<?php echo $date; ?>">
			</form>&nbsp;&nbsp;&nbsp;

			<button onclick="location='count_tel_compare.php'" class="buttonb" title="�鿴�ͷ����ݶԱ�">���ݶԱ�</button>&nbsp;&nbsp;
			<button onclick="location='count_tel_compare_week.php?month=<?php echo date("Y-m", $date_time); ?>'" class="buttonb" title="�鿴�����ݶԱ�">�ܶԱ�</button>&nbsp;&nbsp;
		</td>
	</tr>
</table>

<div class="main_title"><?php echo $type_detail["name"]; ?> - <?php echo date("Y-n", $date_time); ?> �绰ͳ������</div>

<!-- ������ͷ -->
<style type="text/css">
.small_font {font-size:11px !important; color:#aaaaaa; display:block; font-weight:normal; }
</style>
<div id="to_float">
<?php $per = round(100 / 17, 3)."%"; ?>
<table id="float_head" align="center" class="list" style="display:none;">
	<tr>
		<td width="<?php echo $per; ?>" class="head" align="center">����</td>

		<td width="<?php echo $per; ?>" class="head" align="center">�ܴ���绰��</td>
		<td width="<?php echo $per; ?>" class="head" align="center">�ܵ绰<font class="small_font">�м���</font></td>
		<td width="<?php echo $per; ?>" class="head" align="center" style="color:red">��Ч</td>
		<td width="<?php echo $per; ?>" class="head" align="center">ԤԼ</td>
		<td width="<?php echo $per; ?>" class="head" align="center">Ԥ��</td>
		<td width="<?php echo $per; ?>" class="head" align="center" style="color:red">ʵ��</td>

		<td width="<?php echo $per; ?>" class="head" align="center">����</td>
		<td width="<?php echo $per; ?>" class="head" align="center">����</td>
		<td width="<?php echo $per; ?>" class="head" align="center">��ͼ</td>
		<td width="<?php echo $per; ?>" class="head" align="center">�Һ���</td>
		<td width="<?php echo $per; ?>" class="head" align="center">����</td>

		<td width="<?php echo $per; ?>" class="head" align="center">��ѯԤԼ��</td>
		<td width="<?php echo $per; ?>" class="head" align="center">ԤԼ������</td>
		<td width="<?php echo $per; ?>" class="head" align="center">��ѯ������</td>
		<td width="<?php echo $per; ?>" class="head" align="center">��Ч��ѯ��</td>

		<td width="<?php echo $per; ?>" class="head" align="center">����</td>
	</tr>
</table>
</div>

<table id="data_list" width="100%" align="center" class="list">
	<tr id="data_head">
		<td width="<?php echo $per; ?>" class="head" align="center">����</td>

		<td width="<?php echo $per; ?>" class="head" align="center">�ܴ���绰��</td>
		<td width="<?php echo $per; ?>" class="head" align="center">�ܵ绰<font class="small_font">�м���</font></td>
		<td width="<?php echo $per; ?>" class="head" align="center" style="color:red">��Ч</td>
		<td width="<?php echo $per; ?>" class="head" align="center">ԤԼ</td>
		<td width="<?php echo $per; ?>" class="head" align="center">Ԥ��</td>
		<td width="<?php echo $per; ?>" class="head" align="center" style="color:red">ʵ��</td>

		<td width="<?php echo $per; ?>" class="head" align="center">����</td>
		<td width="<?php echo $per; ?>" class="head" align="center">����</td>
		<td width="<?php echo $per; ?>" class="head" align="center">��ͼ</td>
		<td width="<?php echo $per; ?>" class="head" align="center">�Һ���</td>
		<td width="<?php echo $per; ?>" class="head" align="center">����</td>

		<td width="<?php echo $per; ?>" class="head" align="center">��ѯԤԼ��</td>
		<td width="<?php echo $per; ?>" class="head" align="center">ԤԼ������</td>
		<td width="<?php echo $per; ?>" class="head" align="center">��ѯ������</td>
		<td width="<?php echo $per; ?>" class="head" align="center">��Ч��ѯ��</td>

		<td width="<?php echo $per; ?>" class="head" align="center">����</td>
	</tr>

<?php
foreach ($d_array as $i) {
	$cur_date = date("Ymd", strtotime(date("Y-m-", $date_time).$i." 0:0:0"));
	$li = $list[$cur_date];
	if (!is_array($li)) {
		$li = array();
	}

	$c = array_sum($li);

	if ($cur_kefu == '' && $c == 0) {
		continue;
	}

?>
	<tr>
		<td class="item" align="center"><?php echo date("n", $date_time); ?>.<?php echo $i; ?></td>
		<td class="item" align="center"><?php echo $li["zongliang"]; ?></td>
		<td class="item" align="center"><?php echo $li["tel_all"]; ?></td>
		<td class="item" align="center"><?php echo $li["tel_ok"]; ?></td>
		<td class="item" align="center"><?php echo $li["yuyue"]; ?></td>
		<td class="item" align="center"><?php echo $li["yudao"]; ?></td>
		<td class="item" align="center"><?php echo $li["jiuzhen"]; ?></td>

		<td class="item" align="center"><?php echo $li["wangluo"]; ?></td>
		<td class="item" align="center"><?php echo $li["wuxian"]; ?></td>
		<td class="item" align="center"><?php echo $li["ditu"]; ?></td>
		<td class="item" align="center"><?php echo $li["guahaowang"]; ?></td>
		<td class="item" align="center"><?php echo $li["qita"]; ?></td>

		<td class="item" align="center"><?php echo floatval($li["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["per_4"]); ?>%</td>

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
		<td class="item" align="center">����</td>
		<td class="item" align="center"><?php echo $sum_list["zongliang"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["tel_all"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["tel_ok"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["yuyue"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["yudao"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["jiuzhen"]; ?></td>

		<td class="item" align="center"><?php echo $sum_list["wangluo"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["wuxian"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ditu"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["guahaowang"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["qita"]; ?></td>

		<td class="item" align="center"><?php echo @round($sum_list["per_1"], 2); ?>%</td>
		<td class="item" align="center"><?php echo @round($sum_list["per_2"], 2); ?>%</td>
		<td class="item" align="center"><?php echo @round($sum_list["per_3"], 2); ?>%</td>
		<td class="item" align="center"><?php echo @round($sum_list["per_4"], 2); ?>%</td>

		<td class="item" align="center">
			-
		</td>
	</tr>

	<tr>
		<td class="item" align="center">�վ�</td>
		<td class="item" align="center"><?php echo @round($sum_list["zongliang"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["tel_all"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["tel_ok"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["yuyue"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["yudao"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["jiuzhen"] / count($list), 1); ?></td>

		<td class="item" align="center"><?php echo @round($sum_list["wangluo"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["wuxian"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["ditu"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["guahaowang"] / count($list), 1); ?></td>
		<td class="item" align="center"><?php echo @round($sum_list["qita"] / count($list), 1); ?></td>

		<td class="item" align="center"></td>
		<td class="item" align="center"></td>
		<td class="item" align="center"></td>
		<td class="item" align="center"></td>

		<td class="item" align="center">
			-
		</td>
	</tr>
</table>



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
<br>
<br>

<script type="text/javascript">
window.onscroll = function () {
	var s_top = document.body.scrollTop;
	var top = byid("data_list").offsetTop;
	var top_head = byid("data_head").offsetHeight;

	if (s_top >= (0 + top + top_head)) {
		var o = byid("float_head");
		o.style.display = "";
		o.style.position = "absolute";
		o.style.left = byid("data_list").style.left;
		o.style.top = s_top;
		o.style.width = byid("data_list").offsetWidth;
	} else {
		byid("float_head").style.display = "none";
	}
};
</script>

</body>
</html>