<?php
// --------------------------------------------------------
// - ����˵�� : ���� ���ݶԱ�
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2010-10-27 09:46
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_web";

$print = $_GET["print"] ? true : false;


// ���пɹ�����Ŀ:
if ($debug_mode || in_array($uinfo["part_id"], array(9))) {
	$types = $db->query("select id,name from count_type where ishide=0 and type='web' order by name asc", "id", "name");
} else {
	$hids = implode(",", $hospital_ids);
	$types = $db->query("select id,name from count_type where ishide=0 and type='web' and hid in ($hids) order by name asc", "id", "name");
}
if (count($types) == 0) {
	exit("û�п��Թ������Ŀ");
}

$cur_type = $_SESSION["count_type_id_web"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_web"] = $type_ids[0];
}

// �����Ĵ���:
if ($op = $_REQUEST["op"]) {
	if ($op == "change_type") {
		$cur_type = $_SESSION["count_type_id_web"] = intval($_GET["type_id"]);
	}
}

$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);
$kefu_list = $type_detail["kefu"] ? explode(",", $type_detail["kefu"]) : array();


// ��ʼֵΪ����:
if ($_GET["btime"] == '') {
	$_GET["btime"] = date("Y-m-d", mktime(0,0,0,date("m"), 1));
}
if ($_GET["etime"] == '') {
	$_GET["etime"] = date("Y-m-d", strtotime("+1 month", strtotime($_GET["btime"]." 0:0:0")) - 1);
}


// ��������:
if ($cur_type && $_GET["btime"] && $_GET["etime"]) {

	// ʱ���:
	$btime = strtotime($_GET["btime"]." 0:0:0");
	$etime = strtotime($_GET["etime"]." 23:59:59");


	// ��ʼ�� ��Ӧ�ĸ�ҽԺ���ܹ��ɱ���
	/*
	$is_set_gg_fee = 0; //�Ƿ������˹���
	$bm = date("Ym", $btime);
	$_hid = intval($type_detail["hid"]);
	if ($_hid > 0) {
		$tmp = $db->query("select yuji_gg_fee,shiji_gg_fee from xiangmu_mingxi where type_id=$_hid and month=$bm limit 1", 1);
		if ($tmp["yuji_gg_fee"] > 0 || $tmp["shiji_gg_fee"] > 0) { // ��������������һ������
			$gg_fee_type = $tmp["shiji_gg_fee"] > 0 ? "shiji_gg_fee" : "yuji_gg_fee";
			$gg_fee = $tmp[$gg_fee_type];
			$is_set_gg_fee = 1;
		} else {
			$gg_fee_tips = $type_detail["h_name"]."��".date("Y��m��", $btime)."��δ���á�Ԥ�������ѡ���ʵ�ʹ�����ѡ���δ�ܼ����˾��ɱ���";
		}
	} else {
		$gg_fee_tips = "����Ŀδ���ö�ӦҽԺ���޷���ѯ������������";
	}

	// ������Ѹ���Ŀ�������ֳ�:
	if ($is_set_gg_fee) {
		// ��ѯ���и�ҽԺ���ƹ���Ŀ:
		$xiangmu_arr = $db->query("select id,name from count_type where type='web' and hid='$_hid' and ishide=0", "id", "name");
		if (count($xiangmu_arr) > 0) {
			// ����Ŀ���µ�����:
			$_tb = date("Ymd", $btime);
			$_te = date("Ymd", strtotime("+1 month", $btime) - 1);
			$liuliang = array();
			foreach ($xiangmu_arr as $k => $v) {
				$liuliang[$k] = $db->query("select sum(click) as click from count_web where type_id=$k and date>=$_tb and date<=$_te", 1, "click");
			}
			$liuliang_sum = array_sum($liuliang);
			// ��ǰ��Ŀ������Ӧ̯�����Ѷ�:
			$cur_gg_fee = round($gg_fee * $liuliang[$cur_type] / $liuliang_sum, 2);
		} else {
			// ��ҽԺֻ��һ����Ŀ��������������ѷֳ�
			$cur_gg_fee = $gg_fee;
		}
	}
	*/

	$is_set_gg_fee = 0; //�Ƿ������˹���
	$cur_gg_fee = $gg_fee = 0;
	$bm = date("Ym", $btime);
	$_hid = intval($type_detail["xiangmu_id"]);
	if ($_hid > 0) {
		$tmp = $db->query("select * from xiangmu_mingxi where type_id=$_hid and month=$bm limit 1", 1);
		if ($tmp["shiji_gg_fee"] > 0) {
			$cur_gg_fee = $gg_fee = $tmp["shiji_gg_fee"];
			$is_set_gg_fee = 1;
		} else {
			$gg_fee_tips = $type_detail["xiangmu_name"]."��".date("Y��m��", $btime)."��δ���á�ʵ�ʹ�����ѡ���δ�ܼ����˾��ɱ���";
		}
	} else {
		$gg_fee_tips = "����Ŀδ���ö�Ӧ��Ŀ���޷���ѯ������������";
	}


	$b = date("Ymd", $btime);
	$e = date("Ymd", $etime);

	//��ѯ��ҽԺ��������:
	$tmp_list = $db->query("select * from $table where type_id=$cur_type and date>=$b and date<=$e order by kefu asc,date asc");

	// �������:
	$real_kefu = $list = $dt_count = array();
	foreach ($tmp_list as $v) {
		$dt = trim($v["kefu"]);
		if (!in_array($dt, $real_kefu)) {
			$real_kefu[] = $dt;
		}
		$dt_count[$dt] += 1;
		foreach ($v as $a => $b) {
			if ($b && is_numeric($b)) {
				$list[$dt][$a] = floatval($list[$dt][$a]) + $b;
			}
		}
	}


	// �ͷ���˳������ @ 2012-03-05
	$kefu_a = array();
	foreach ($kefu_list as $v) {
		if (in_array($v, $real_kefu)) {
			$kefu_a[] = $v;
		}
	}
	foreach ($real_kefu as $v) {
		if (!in_array($v, $kefu_a)) {
			$kefu_a[] = $v;
		}
	}
	$real_kefu = $kefu_a;

	// ��������:
	foreach ($list as $k => $v) {
		// ��ѯԤԼ��:
		$list[$k]["per_1"] = @round($v["talk_swt"] / $v["click"] * 100, 2);
		// ԤԼ������:
		$list[$k]["per_2"] = @round($v["come"] / $v["orders_swt"] * 100, 2);
		// ��ѯ������:
		$list[$k]["per_3"] = @round($v["come"] / $v["click"] * 100, 2);
		// ��Ч��ѯ��:
		$list[$k]["per_4"] = @round($v["ok_click"] / $v["click"] * 100, 2);
		// ��ЧԤԼ��:
		$list[$k]["per_5"] = @round($v["talk_swt"] / $v["ok_click"] * 100, 2);
	}

	// ����ͳ������:
	$cal_field = explode(" ", "click click_local click_other zero_talk ok_click ok_click_local ok_click_other talk talk_bendi talk_waidi talk_swt talk_tel talk_other orders orders_bendi orders_waidi orders_swt orders_tel orders_other come_all come_bendi come_waidi come come_tel come_other");
	// ����:
	$sum_list = array();
	foreach ($list as $v) {
		foreach ($cal_field as $f) {
			$sum_list[$f] = floatval($sum_list[$f]) + $v[$f];
		}
	}

	// ��ѯԤԼ��:
	$sum_list["per_1"] = @round($sum_list["talk_swt"] / $sum_list["click"] * 100, 2);
	// ԤԼ������:
	$sum_list["per_2"] = @round($sum_list["come"] / $sum_list["orders_swt"] * 100, 2);
	// ��ѯ������:
	$sum_list["per_3"] = @round($sum_list["come"] / $sum_list["click"] * 100, 2);
	// ��Ч��ѯ��:
	$sum_list["per_4"] = @round($sum_list["ok_click"] / $sum_list["click"] * 100, 2);
	// ��ЧԤԼ��:
	$sum_list["per_5"] = @round($sum_list["talk_swt"] / $sum_list["ok_click"] * 100, 2);

}


// �Ƿ�����ӻ��޸�����:
$can_edit_data = 0;
if ($debug_mode || in_array($uinfo["part_id"], array(9)) || in_array($uid, explode(",", $type_detail["uids"]))) {
	$can_edit_data = 1;
}


$show_memo = 0;
if ($debug_mode || $username == "admin" ) {
	$show_memo = 1;
}
if ($config["zixun_memo"]) {
	$show_memo = 1;
}

$show_memo = 0;

/*
// ------------------ ���� -------------------
*/
function my_show($arr, $default_value='', $click='') {
	$s = '';
	foreach ($arr as $v) {
		if ($v == $default_value) {
			$s .= '<b>'.$v.'</b>';
		} else {
			$s .= '<a href="javascript:void(0);" onclick="'.$click.'">'.$v.'</a>';
		}
	}
	return $s;
}


// ҳ�濪ʼ ------------------------
?>
<html>
<head>
<title><?php echo $type_detail["name"].""; ?> <?php echo $_GET["btime"]; ?> �� <?php echo $_GET["etime"]; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<script src="lib/sorttable_keep.js" language="javascript"></script>
<?php if ($print) { ?>
<style type="text/css" media="screen">
* {color:black !important; }
.main_title {font-size:15px; font-weight:bold; text-align:center; padding:20px; }
.list {border:1px solid black; }
.list .head {border:1px solid black; }
.list .item {border:1px solid black; }
.print_set {margin:5px; text-align:center; }
.item {padding:8px 3px 6px 3px !important; }
</style>
<?php } else { ?>
<style media="screen">
* {font-family:"Tahoma","΢���ź�"; }
body {padding:5px 8px; }
form {display:inline; }
.combo {font-family:"΢���ź�" !important; font-size:12px !important; }
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
.main_title {margin:0 auto; padding:20px; text-align:center; font-weight:bold; font-size:15px; }
.item {padding:8px 3px 6px 3px !important; }
.rate_tips {padding:30px 0 0 30px; line-height:24px; }
.huizong, .huizong td {background:#ece9d7; }
</style>
<?php } ?>
<style type="text/css" media="print">
body {padding:0; }
.no_print {display:none; }
* {color:black !important; }
.main_title {font-size:15px; font-weight:bold; text-align:center; padding:20px; }
.list {border:1px solid black; }
.list .head {border:1px solid black; }
.list .item {border:1px solid black; }
.item {padding:8px 3px 6px 3px !important; }
.bl {border-left:1px solid black !important; }
.br {border-right:1px solid black !important; }
.bt {border-top:1px solid black !important; }
.bb {border-bottom:1px solid black !important; }
</style>

<script language="javascript">
function update_date(type, o) {
	byid("date_"+type).value = parseInt(o.innerHTML, 10);

	var a = parseInt(byid("date_1").value, 10);
	var b = parseInt(byid("date_2").value, 10);

	var s = a + '' + (b<10 ? "0" : "") + b;

	byid("date").value = s;
	byid("ch_date").submit();
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

function write_dt(da, db) {
	byid("begin_time").value = da;
	byid("end_time").value = db;
}

function set_memo(o) {
	parent.load_src(1, o.href, 600, 300);
	return false;
}
</script>
</head>

<body>
<?php if (!$print) { ?>
<div style="margin:15px auto 0 auto; text-align:center;">
	<a href="count_web.php">[����]</a>
	<form method="GET" style="margin-left:20px;">
		<select name="type_id" id="type_id" class="combo" onchange="this.form.submit()">
			<option value="" style="color:gray">-��ѡ����Ŀ-</option>
			<?php echo list_option($types, "_key_", "_value_", $cur_type); ?>
		</select>&nbsp;
		<button class="button" onclick="hgo('up',this);">��</button>&nbsp;
		<button class="button" onclick="hgo('down',this);">��</button>
		<input type="hidden" name="btime" value="<?php echo $_GET["btime"]; ?>">
		<input type="hidden" name="etime" value="<?php echo $_GET["etime"]; ?>">
		<input type="hidden" name="op" value="change_type">
	</form>

	<b style="margin-left:20px;">ʱ��Σ�</b>
	<form method="GET">
		<input name="btime" id="begin_time" class="input" style="width:80px" value="<?php echo $_GET["btime"]; ?>" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})"> ~ <input name="etime" id="end_time" class="input" style="width:80px" value="<?php echo $_GET["etime"]; ?>" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})">��<input type="submit" class="button" value="ȷ��">&nbsp;&nbsp;
<?php
$lmb = strtotime("-1 month", strtotime($_GET["btime"]));
$lme = strtotime($_GET["btime"]) - 1;
$nmb = strtotime("+1 month", strtotime($_GET["btime"]));
$nme = strtotime("+1 month", $nmb) - 1;
?>
		<input type="button" class="button" onclick="write_dt('<?php echo date("Y-m-d", $lmb); ?>', '<?php echo date("Y-m-d", $lme); ?>'); this.form.submit();" value="����">&nbsp;
		<input type="button" class="button" onclick="write_dt('<?php echo date("Y-m-d", $nmb); ?>', '<?php echo date("Y-m-d", $nme); ?>'); this.form.submit();" value="����">&nbsp;&nbsp;&nbsp;
		<a href="?print=1&btime=<?php echo $_GET["btime"]; ?>&etime=<?php echo $_GET["etime"]; ?>" target="_blank">��ӡ��ҳ</a>
	</form>
</div>
<?php } ?>


<?php if ($cur_type && $_GET["btime"] && $_GET["etime"]) { ?>

<?php
$noshow = array();
if ($print) {
	if ($show_memo) {
		$arr = explode(" ", " ��ѯԤԼ�� ԤԼ������ ��ѯ������ ��Ч��ѯ�� ��ЧԤԼ�� ��ʧ���� �˾��ɱ� �����ע");
	} else {
		$arr = explode(" ", " ��ѯԤԼ�� ԤԼ������ ��ѯ������ ��Ч��ѯ�� ��ЧԤԼ�� ��ʧ���� �˾��ɱ�");
	}
	$noshow = (array) $_SESSION["count_web_compare_noshow"];
	if (isset($_GET["noshow"])) {
		$noshow = $_GET["noshow"];
		$_SESSION["count_web_compare_noshow"] = $noshow;
	}
	echo '<div class="no_print print_set">';
	echo '<form method="GET">��Ҫ���ص��У�';
	foreach ($arr as $k => $v) {
		if ($v) {
			$chk = in_array($k, $noshow) ? ' checked' : '';
			echo '<input type="checkbox" name="noshow[]" value="'.$k.'" id="noshow_'.$k.'"'.$chk.'><label for="noshow_'.$k.'">'.$v.'</label>&nbsp;';
		}
	}
	echo '<input type="hidden" name="noshow[]" value="" />';
	echo '<input type="hidden" name="print" value="1" />';
	echo '<input type="hidden" name="btime" value="'.$_GET["btime"].'" />';
	echo '<input type="hidden" name="etime" value="'.$_GET["etime"].'" />';
	echo '<input type="submit" class="button" value="ȷ��">';
	echo '</form>';
	echo '</div>';
}
?>

<div class="main_title"><?php echo $type_detail["name"]; ?>��<?php echo $_GET["btime"]; ?> �� <?php echo $_GET["etime"]; ?>��(����Ϊ����ͨ����)</div>

<table width="100%" align="center" class="list sortable" id="zixun_compare">
	<tr>
		<td class="head column_sortable" title="���������" align="center" width="60">�ͷ�</td>

		<td class="head bl column_sortable" align="center" style="color:red">�ܵ��</td>
		<td class="head column_sortable" align="center">���ص��</td>
		<td class="head column_sortable" align="center">��ص��</td>
		<td class="head column_sortable" align="center" style="color:red">����Ч</td>
		<td class="head column_sortable" align="center">������Ч</td>
		<td class="head column_sortable" align="center">�����Ч</td>

		<td class="head bl column_sortable" align="center" style="color:red">��ԤԼ</td>
		<td class="head column_sortable" align="center">����ԤԼ</td>
		<td class="head column_sortable" align="center">���ԤԼ</td>
		<td class="head column_sortable" align="center">����ͨԤԼ</td>
		<td class="head column_sortable" align="center">�绰ԤԼ</td>
		<td class="head column_sortable" align="center">����ԤԼ</td>

		<td class="head bl column_sortable" align="center" style="color:red">��Ԥ��</td>
		<td class="head column_sortable" align="center">����Ԥ��</td>
		<td class="head column_sortable" align="center">���Ԥ��</td>
		<td class="head column_sortable" align="center">����ͨԤ��</td>
		<td class="head column_sortable" align="center">�绰Ԥ��</td>
		<td class="head column_sortable" align="center">����Ԥ��</td>

		<td class="head bl column_sortable" align="center" style="color:red">�ܵ�Ժ</td>
		<td class="head column_sortable" align="center">���ص�Ժ</td>
		<td class="head column_sortable" align="center">��ص�Ժ</td>
		<td class="head column_sortable" align="center">����ͨ��Ժ</td>
		<td class="head column_sortable" align="center">�绰��Ժ</td>
		<td class="head br column_sortable" align="center">������Ժ</td>

<?php if (!$print || ($print && !in_array(1, $noshow))) { ?>
		<td class="head column_sortable" align="center" style="color:red">��ѯԤԼ��%</td>
<?php } ?>
<?php if (!$print || ($print && !in_array(2, $noshow))) { ?>
		<td class="head column_sortable" align="center" style="color:red">ԤԼ������%</td>
<?php } ?>
<?php if (!$print || ($print && !in_array(3, $noshow))) { ?>
		<td class="head column_sortable" align="center" style="color:red">��ѯ������%</td>
<?php } ?>
<?php if (!$print || ($print && !in_array(4, $noshow))) { ?>
		<td class="head column_sortable" align="center" style="color:red">��Ч��ѯ��%</td>
<?php } ?>
<?php if (!$print || ($print && !in_array(5, $noshow))) { ?>
		<td class="head column_sortable" align="center" style="color:red">��ЧԤԼ��%</td>
<?php } ?>
<?php if (!$print || ($print && !in_array(6, $noshow))) { ?>
		<td class="head column_sortable" align="center" style="color:red">��ʧ����</td>
<?php } ?>

<?php if (!$print || ($print && !in_array(7, $noshow))) { ?>
		<!-- <td class="head" align="center" style="color:red">�˾��ɱ�</td> -->
<?php } ?>

<?php if ($show_memo) { ?>
<?php if (!$print || ($print && !in_array(8, $noshow))) { ?>
		<td class="head bl" align="center">�����ע</td>
<?php } ?>
<?php } ?>

	</tr>

<?php
$empty_lines = array();
$liushi_sum = 0;

asort($real_kefu);

if ($debug_mode) {
	//echo "<pre>";
	//print_r($real_kefu);
	//exit;
}

$p_count = 0;
foreach ($real_kefu as $i) {
	$li = $list[$i];
	if (!is_array($li)) {
		$li = array();
	}
	if ($li["click"] + $li["ok_click"] + $li["talk"] + $li["orders"] + $li["come_all"] == 0) {
		$empty_lines[] = $i;
		continue;
	}

	// ��ʧ����:
	$liushi = round($sum_list["come"] * $li["click"] / $sum_list["click"]) - $li["come"];
	if ($liushi > 0) {
		$liushi_sum += intval($liushi);
	}

	// �˾��ɱ���
	$chengben = '';
	if ($is_set_gg_fee && $li["come"] && $li["click"]) {
		$chengben = round((($cur_gg_fee * $li["click"]) / $sum_list["click"]) / $li["come"]);
	}

	// �����ע:
	if ($show_memo) {
		$bm = date("Ym", $btime);
		$memo = $db->query("select memo from count_memo where type_id=$cur_type and month=$bm and kefu='$i' order by id desc limit 1", 1, "memo");
		if ($print) {
			$memo_str = cut($memo, 20, "��");
		} else {
			$memo_str = '<a href="count_set_memo.php?type_id='.$cur_type.'&month='.$bm.'&kefu='.base64url_encode($i).'" onclick="set_memo(this);return false;" id="memo_'.$i.'">';
			if (trim($memo) == "") {
				$memo_str .= '(���)';
			} else {
				$memo_str .= '<span title="'.trim(strip_tags($memo)).'" style="color:red">'.cut($memo, 20, "��").'</span>';
			}
			$memo_str .= '</a>';
		}
	} else {
		$memo_str = '';
	}

	$p_count ++;

?>
	<tr>
		<td class="item" align="center"><?php echo !in_array($i, $kefu_list) ? ('<font style="text-decoration:line-through;color:silver;" title="�ÿͷ��ѱ��Ƴ�">'.$i.'</font>') : $i; ?></td>
		<td class="item bl" align="center" style="color:red"><?php echo $li["click"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $li["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $li["ok_click_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $li["talk"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_swt"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_tel"]; ?></td>
		<td class="item" align="center"><?php echo $li["talk_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $li["orders"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_swt"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_tel"]; ?></td>
		<td class="item" align="center"><?php echo $li["orders_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $li["come_all"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $li["come"]; ?></td>
		<td class="item" align="center"><?php echo $li["come_tel"]; ?></td>
		<td class="item br" align="center"><?php echo $li["come_other"]; ?></td>

<?php if (!$print || ($print && !in_array(1, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_1"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(2, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_2"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(3, $noshow))) { ?>
		<td class="item" align="center" style="color:red;font-weight:bold;"><?php echo floatval($li["per_3"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(4, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_4"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(5, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo floatval($li["per_5"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(6, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo ($liushi > 0 ? $liushi : ''); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(7, $noshow))) { ?>
		<!-- <td class="item" align="center" style="color:red"><?php if ($is_set_gg_fee) echo $chengben; ?></td> -->
<?php } ?>

<?php if ($show_memo) { ?>
<?php if (!$print || ($print && !in_array(8, $noshow))) { ?>
		<td class="item bl" align="center"><?php echo $memo_str; ?></td>
<?php } ?>
<?php } ?>

	</tr>


<?php } ?>

<?php if (!$print) { ?>
	<!-- <tr>
		<td colspan="30" class="tips">���ݻ���</td>
	</tr> -->
<?php } ?>
	<style type="text/css">
	.huizong_zuo {display:none; }
	</style>
	<tr class="huizong">
		<td class="item" align="center"><font class="huizong_zuo">��</font><b>����</b></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum_list["click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["click_other"]; ?></td>
		<td class="item" align="center" style="color:red"><?php echo $sum_list["ok_click"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_local"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["ok_click_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum_list["talk"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_swt"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_tel"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["talk_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum_list["orders"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_swt"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_tel"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["orders_other"]; ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo $sum_list["come_all"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come_bendi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come_waidi"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come"]; ?></td>
		<td class="item" align="center"><?php echo $sum_list["come_tel"]; ?></td>
		<td class="item br" align="center"><?php echo $sum_list["come_other"]; ?></td>

<?php if (!$print || ($print && !in_array(1, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_1"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(2, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_2"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(3, $noshow))) { ?>
		<td class="item" align="center" style="color:red; font-weight:bold;"><?php echo @floatval($sum_list["per_3"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(4, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_4"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(5, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo @floatval($sum_list["per_5"]); ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(6, $noshow))) { ?>
		<td class="item" align="center" style="color:red"><?php echo $liushi_sum; ?></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(7, $noshow))) { ?>
		<!-- <td class="item" align="center" style="color:red"><?php if ($is_set_gg_fee) echo round($cur_gg_fee / $sum_list["come"]); ?></td> -->
<?php } ?>

<?php if ($show_memo) { ?>
<?php if (!$print || ($print && !in_array(8, $noshow))) { ?>
		<td class="item bl" align="center"></td>
<?php } ?>
<?php } ?>

	</tr>


	<tr class="huizong">
		<td class="item" align="center"><font class="huizong_zuo">��</font><b>��ֵ</b></td>

		<td class="item bl" align="center" style="color:red"><?php echo round($sum_list["click"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["click_local"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["click_other"] / $p_count); ?></td>
		<td class="item" align="center" style="color:red"><?php echo round($sum_list["ok_click"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["ok_click_local"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["ok_click_other"] / $p_count); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo round($sum_list["talk"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["talk_bendi"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["talk_waidi"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["talk_swt"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["talk_tel"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["talk_other"] / $p_count); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo round($sum_list["orders"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["orders_bendi"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["orders_waidi"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["orders_swt"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["orders_tel"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["orders_other"] / $p_count); ?></td>

		<td class="item bl" align="center" style="color:red"><?php echo round($sum_list["come_all"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["come_bendi"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["come_waidi"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["come"] / $p_count); ?></td>
		<td class="item" align="center"><?php echo round($sum_list["come_tel"] / $p_count); ?></td>
		<td class="item br" align="center"><?php echo round($sum_list["come_other"] / $p_count); ?></td>

<?php if (!$print || ($print && !in_array(1, $noshow))) { ?>
		<td class="item" align="center" style="color:red"></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(2, $noshow))) { ?>
		<td class="item" align="center" style="color:red"></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(3, $noshow))) { ?>
		<td class="item" align="center" style="color:red; font-weight:bold;"></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(4, $noshow))) { ?>
		<td class="item" align="center" style="color:red"></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(5, $noshow))) { ?>
		<td class="item" align="center" style="color:red"></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(6, $noshow))) { ?>
		<td class="item" align="center" style="color:red"></td>
<?php } ?>
<?php if (!$print || ($print && !in_array(7, $noshow))) { ?>
		<!-- <td class="item" align="center" style="color:red"><?php if ($is_set_gg_fee) echo round($cur_gg_fee / $sum_list["come"]); ?></td> -->
<?php } ?>

<?php if ($show_memo) { ?>
<?php if (!$print || ($print && !in_array(8, $noshow))) { ?>
		<td class="item bl" align="center"></td>
<?php } ?>
<?php } ?>

	</tr>

</table>


<?php if (!$print) { ?><!-- print -->

<?php
if (count($empty_lines) > 0) {
	echo "<br><div style='text-align:center'>".implode("��", $empty_lines)." �����ݣ��ѱ����ԡ�</div>";
}
?>

<div style="text-align:center">
<?php
/*
if ($is_set_gg_fee) {
	if (count($xiangmu_arr) > 0) {
		echo $type_detail["h_name"]." ".date("Y��m��", $btime)." ������".($gg_fee_type == "yuji_gg_fee" ? "<b>Ԥ��</b>" : "<b>ʵ��</b>")."������Ϊ".$gg_fee."Ԫ��".implode("��", $xiangmu_arr)."�ܵ�����ֱ�Ϊ".implode("��", $liuliang)."������Ŀ��̯����Ϊ��".$cur_gg_fee."Ԫ";
	} else {
		echo $type_detail["h_name"]."ֻ�д�һ����Ŀ��".date("Y��m��", $btime)."������".($gg_fee_type == "yuji_gg_fee" ? "<b>Ԥ��</b>" : "<b>ʵ��</b>")."������Ϊ".$gg_fee."Ԫ";
	}
} else {
	echo $gg_fee_tips;
}
*/
?>
</div>

<div class="rate_tips">
��ѯԤԼ�� = ԤԼ���� / �ܵ��<br>
ԤԼ������ = ʵ�ʵ�Ժ���� / Ԥ�Ƶ�Ժ����<br>
��ѯ������ = ʵ�ʵ�Ժ���� / �ܵ��<br>
��Ч��ѯ�� = ��Ч��� / �ܵ��<br>
��ЧԤԼ�� = ԤԼ���� / ��Ч���<br>
��ʧ�������ÿͷ�����ѯ������û�дﵽƽ��ֵ������ʧ����+ʵ�ʵ�Ժ�������������ܴﵽƽ����ѯ������<br>
�˾��ɱ��������ƹ�Ͷ����ܳɱ����ܵ����̯�󣬳���ʵ�ʵ�Ժ���������ý�����˾��ɱ�<br>
</div>

<?php } ?><!-- print -->

<?php } ?>

<br>
<br>

</body>
</html>
