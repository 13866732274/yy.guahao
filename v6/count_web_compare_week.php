<?php
// --------------------------------------------------------
// - ����˵�� : ���� ���ݶԱ� ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2010-11-04 15:09
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_web";

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
if ($_GET["month"] == '') {
	$_GET["month"] = date("Y-m", mktime(0,0,0,date("m"), 1));
}

$m = strtotime($_GET["month"]."-1 0:0:0");
$m_end = date("d", strtotime("+1 month", $m) - 1);
$int_month = date("Ym", $m);


$week_array = array(
	1 => array(date("Y-m-1", $m), date("Y-m-10", $m)),
	2 => array(date("Y-m-11", $m), date("Y-m-20", $m)),
	3 => array(date("Y-m-21", $m), date("Y-m-".$m_end, $m)),
);



// ��������:
if ($cur_type && $_GET["month"]) {

	$kefu_arr = array();

	$all = array();

	// ��ѯÿ������:
	foreach ($week_array as $wi => $w) {

		// ʱ���:
		$btime = strtotime($w[0]." 0:0:0");
		$etime = strtotime($w[1]." 23:59:59");

		$b = date("Ymd", $btime);
		$e = date("Ymd", $etime);

		//��ѯ��ҽԺ��������:
		$tmp_list = $db->query("select * from $table where type_id=$cur_type and date>=$b and date<=$e order by kefu asc,date asc");

		// ����ý׶λ���:
		$list = $dt_count = array();
		foreach ($tmp_list as $v) {
			$dt = $v["kefu"];

			if (!in_array($dt, $kefu_arr)) {
				$kefu_arr[] = $dt;
			}

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
		$cal_field = explode(" ", "click click_local click_other zero_talk ok_click ok_click_local ok_click_other talk talk_swt talk_tel talk_other orders orders_swt orders_tel orders_other come_all come come_tel come_other per_1 per_2 per_3 per_4 per_5");

		// ����������:
		foreach ($list as $k => $v) {
			foreach ($cal_field as $v2) {
				$all[$k][$wi][$v2] = $v[$v2];
			}
		}


		// ���ܼ���:
		//$sum = array();
		foreach ($list as $v) {
			foreach ($cal_field as $f) {
				$sum[$wi][$f] = floatval($sum[$wi][$f]) + $v[$f];
			}
		}

		$sum[$wi]["per_1"] = @round($sum[$wi]["talk_swt"] / $sum[$wi]["click"] * 100, 2);
		$sum[$wi]["per_2"] = @round($sum[$wi]["come"] / $sum[$wi]["orders_swt"] * 100, 2);
		$sum[$wi]["per_3"] = @round($sum[$wi]["come"] / $sum[$wi]["click"] * 100, 2);
		$sum[$wi]["per_4"] = @round($sum[$wi]["ok_click"] / $sum[$wi]["click"] * 100, 2);
		$sum[$wi]["per_5"] = @round($sum[$wi]["talk_swt"] / $sum[$wi]["ok_click"] * 100, 2);

		//echo "<pre>";
		//print_r($sum);


		// ��ʧ����:
		$liushi_sum = 0;
		foreach ($kefu_arr as $_kefu) {
			$liushi = round($sum[$wi]["come"] * $all[$_kefu][$wi]["click"] / $sum[$wi]["click"]) - $all[$_kefu][$wi]["come"];
			if ($liushi > 0) {
				$all[$_kefu][$wi]["liushi"] = $liushi;
			} else {
				$all[$_kefu][$wi]["liushi"] = '';
			}
			if ($liushi > 0) {
				$liushi_sum += intval($liushi);
			}
		}
		$sum[$wi]["liushi"] = $liushi_sum;

	}


	// �ͷ���ע:
	foreach ($kefu_arr as $_kefu) {
		foreach ($week_array as $_dname => $v) {
			$_arr = $db->query("select * from count_week_memo where type_id=$cur_type and month=$int_month and sub_id=$_dname and kefu='$_kefu' limit 1", 1);
			if ($_arr["id"] > 0 && trim($_arr["memo"]) != '') {
				$title = $_arr["memo"];
				$text = cut($_arr["memo"], 20);
				$class = "a_red";
			} else {
				$title = "�����ӱ�ע";
				$text = "<nobr>���</nobr>";
				$class = 'noprint';
			}

			$s = '<a href="javascript:;" onclick="edit_memo('.$cur_type.','.$int_month.','.$_dname.',\''.base64url_encode($_kefu).'\')" title="'.$title.'" class="'.$class.'">'.$text.'</a>';
			$all[$_kefu][$_dname]["memo"] = $s;
		}
	}

}






// ҳ�濪ʼ ------------------------
?>
<html>
<head>
<title>��������ͳ�� - ����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
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
.no_b {font-weight:normal !important; }
.main_title {margin:0 auto; padding:20px; text-align:center; font-size:15px; }
.rate_tips {padding:30px 0 0 30px; line-height:24px; }
.a_red {color:red; }
</style>

<style media="print">
.noprint {display:none; }
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

/// $cur_type.','.$int_month.','.$_dname.',\''.($_kefu)
function edit_memo(type_id, int_month, sub_id, kefu) {
	var url = "count_week_set_memo.php?type_id="+type_id+"&month="+int_month+"&sub_id="+sub_id+"&kefu="+kefu;
	parent.load_src(1, url, 600, 300);
	return false;
}
</script>
</head>

<body>
<div style="margin:15px auto 0 auto; text-align:center;" class="noprint">
	<a href="count_web.php">[����]</a>
	<form method="GET" style="margin-left:20px;">
		<select name="type_id" id="type_id" class="combo" onchange="this.form.submit()">
			<option value="" style="color:gray">-��ѡ����Ŀ-</option>
			<?php echo list_option($types, "_key_", "_value_", $cur_type); ?>
		</select>&nbsp;
		<button class="button" onclick="hgo('up',this);">��</button>&nbsp;
		<button class="button" onclick="hgo('down',this);">��</button>
		<input type="hidden" name="month" value="<?php echo $_GET["month"]; ?>">
		<input type="hidden" name="op" value="change_type">
	</form>

	<b style="margin-left:20px;">�·ݣ�</b>
	<form method="GET">
		<input name="month" id="time_month" class="input" style="width:100px" value="<?php echo $_GET["month"]; ?>" onclick="picker({el:'time_month',dateFmt:'yyyy-MM'})" onchange="this.form.submit();">&nbsp;&nbsp;&nbsp;
	</form>

	<a href="count_web_compare_week_print.php?month=<?php echo $_GET["month"]; ?>" target="_blank" title="�´��ڲ鿴��ӡҳ��" style="font-family:΢���ź�; font-weight:bold;">[��ӡ]</a>
</div>


<?php if ($cur_type && $_GET["month"]) { ?>

<div class="main_title"><?php echo $type_detail["name"]." ".$_GET["month"]; ?> ����ͨ���ݶԱ�</div>

<style type="text/css">
.list td {padding:5px 2px !important; font-family:"Tahoma","΢���ź�" !important; }
.huizong td {color:red; }
</style>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head hr hb" width="4%" rowspan="2" align="center">�ͷ�</td>

		<td class="head hl hb" width="32%" colspan="7" align="center">1��-10��</td>
		<td class="head hl hb" width="32%" colspan="7" align="center">11��-20��</td>
		<td class="head hl hb" width="32%" colspan="7" align="center">21��-31��</td>
	</tr>

	<tr>
		<td class="head hl hb no_b" width="4%" align="center">�ܵ���</td>
		<td class="head hb no_b" width="4%" align="center">����ͨ����</td>
		<td class="head hb no_b" align="center">��ѯԤԼ��</td>
		<td class="head hb no_b" align="center">ԤԼ������</td>
		<td class="head hb no_b" align="center">��ѯ������</td>
		<td class="head hb no_b" width="4%" align="center">��ʧ����</td>
		<td class="head hb no_b" align="center">��ע</td>

		<td class="head hl hb no_b" width="4%" align="center">�ܵ���</td>
		<td class="head hb no_b" width="4%" align="center">����ͨ����</td>
		<td class="head hb no_b" align="center">��ѯԤԼ��</td>
		<td class="head hb no_b" align="center">ԤԼ������</td>
		<td class="head hb no_b" align="center">��ѯ������</td>
		<td class="head hb no_b" width="4%" align="center">��ʧ����</td>
		<td class="head hb no_b" align="center">��ע</td>

		<td class="head hl hb no_b" width="4%" align="center">�ܵ���</td>
		<td class="head hb no_b" width="4%" align="center">����ͨ����</td>
		<td class="head hb no_b" align="center">��ѯԤԼ��</td>
		<td class="head hb no_b" align="center">ԤԼ������</td>
		<td class="head hb no_b" align="center">��ѯ������</td>
		<td class="head hb no_b" width="4%" align="center">��ʧ����</td>
		<td class="head hb no_b" align="center">��ע</td>
	</tr>


<?php
foreach ($kefu_arr as $i) {
	$li = $all[$i];
	if (!is_array($li)) {
		$li = array();
	}

?>
	<tr>
		<td class="item" align="center"><?php echo $i; ?></td>

		<td class="item hl" align="center"><?php echo floatval($li["1"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["1"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["1"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["1"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["1"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($li["1"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($li["1"]["memo"]); ?></td>

		<td class="item hl" align="center"><?php echo floatval($li["2"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["2"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["2"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($li["2"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($li["2"]["memo"]); ?></td>

		<td class="item hl" align="center"><?php echo floatval($li["3"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["3"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($li["3"]["per_3"]); ?></td>
		<td class="item" align="center"><?php echo ($li["3"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($li["3"]["memo"]); ?></td>
	</tr>

<?php } ?>

	<tr class="huizong">
		<td class="item ht" align="center">����</td>

		<td class="item hl" align="center"><?php echo floatval($sum["1"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($sum["1"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($sum["1"]["memo"]); ?></td>

		<td class="item hl" align="center"><?php echo floatval($sum["2"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($sum["2"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($sum["2"]["memo"]); ?></td>

		<td class="item hl" align="center"><?php echo floatval($sum["3"]["come_all"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["come"]); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo ($sum["3"]["liushi"]); ?></td>
		<td class="item" align="center"><?php echo ($sum["3"]["memo"]); ?></td>

	</tr>

<?php $sum_nums = count($kefu_arr); ?>

	<tr class="huizong">
		<td class="item ht" align="center">ƽ��<?php echo $sum_nums; ?></td>

		<td class="item hl" align="center"><?php echo round($sum["1"]["come_all"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo round($sum["1"]["come"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["1"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo round($sum["1"]["liushi"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"></td>

		<td class="item hl" align="center"><?php echo round($sum["2"]["come_all"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo round($sum["2"]["come"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["2"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo round($sum["2"]["liushi"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"></td>

		<td class="item hl" align="center"><?php echo round($sum["3"]["come_all"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo round($sum["3"]["come"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_1"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_2"]); ?>%</td>
		<td class="item" align="center"><?php echo floatval($sum["3"]["per_3"]); ?>%</td>
		<td class="item" align="center"><?php echo round($sum["3"]["liushi"]/$sum_nums, 1); ?></td>
		<td class="item" align="center"></td>

	</tr>

</table>

<div class="rate_tips noprint">
��ѯԤԼ�� = ԤԼ���� / �ܵ��<br>
ԤԼ������ = ʵ�ʵ�Ժ���� / Ԥ�Ƶ�Ժ����<br>
��ѯ������ = ʵ�ʵ�Ժ���� / �ܵ��<br>
��Ч��ѯ�� = ��Ч��� / �ܵ��<br>
��ЧԤԼ�� = ԤԼ���� / ��Ч���<br>
</div>

<?php } ?>


</body>
</html>
