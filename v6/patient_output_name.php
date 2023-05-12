<?php
// --------------------------------------------------------
// - ����˵�� : ��������
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2011-12-14
// --------------------------------------------------------
require "lib/set_env.php";
check_power('', $pinfo) or exit("û�д�Ȩ��...");
set_time_limit(0);

if ($hid == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}

if ($config["is_output"] != 1 && !$debug_mode) {
	exit_html("�Բ�����û��Ȩ�ޣ�����ϵ����Ա��");
}

// ҽԺ:
$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

$table = "patient_".$hid;

$time_array = array("order_date"=>"��Ժʱ��", "addtime"=>"���ʱ��");
$status_array = array("all"=>"����", "come"=>"�ѵ�", "not"=>"δ��");
$sort_array = array("order_date"=>"��Ժʱ��", "name"=>"����");
$part_array = array("2"=>"����", "3"=>"�绰", "12"=>"�绰�ط�");
$depart_array = $db->query("select id,name from depart where hospital_id='$user_hospital_id'", "id", "name");
$account_array = array("���˺�", "С�˺�", "СС�˺�");
$t = strtotime("-12 month");
$author_array = $db->query("select distinct author from patient_{$hid} where addtime>$t order by author", "", "author");

// ���� @ 2012-11-06
$disease_id_name = $db->query("select id,name from disease where hospital_id={$hid}", "id", "name");
// ��ѯ������Ӧ������:
$did_num_arr = $db->query("select disease_id, count(disease_id) as num from patient_{$hid} group by disease_id", "disease_id", "num");
arsort($did_num_arr);

//echo "<pre>";
//print_r($did_num_arr);

// ������:
$display_disease_arr = array();
foreach ($did_num_arr as $k => $v) {
	if (!array_key_exists($k, $disease_id_name)) {
		continue;
	}
	$display_disease_arr[$k] = $disease_id_name[$k].' ['.intval($v).']';
}


$sys_fields = array();
$sys_fields["addtime"] = "���ʱ��";
$sys_fields["name"] = "����";
$sys_fields["sex"] = "�Ա�";
$sys_fields["age"] = "����";
$sys_fields["tel"] = "�绰����";
$sys_fields["tel_location"] = "���������";
$sys_fields["zhuanjia_num"] = "ר�Һ�";
$sys_fields["disease_id"] = "����";
$sys_fields["content"] = "��ѯ����";
$sys_fields["media_from"] = "ý����Դ";
$sys_fields["qq_from"] = "QQ��Դ";
$sys_fields["tel_from"] = "�绰��Դ";
if ($config["show_guiji"] || $debug_mode) {
	$sys_fields["guiji"] = "�켣";
	$sys_fields["from_site"] = "��Դ��վ";
	$sys_fields["key_word"] = "�ؼ���";
}
$sys_fields["memo"] = "��ע";
$sys_fields["huifang"] = "�ط�";
$sys_fields["author"] = "�ͷ�";
$sys_fields["xianchang_doctor"] = "�ֳ�ҽ��";
$sys_fields["status"] = "״̬";
$sys_fields["order_date"] = "ԤԼʱ��";
$sys_fields["suozaidi"] = "���ڵ���";


// ��ʼ��ѡ���ֶ�
if (!isset($_GET["fields"])) {
	$_GET["fields"] = array_keys($sys_fields);
}


$op = $_GET["op"];

// ����ʱ��:
if ($op == "show") {
	$where = "";

	$time_ty = "order_date";
	if ($_GET["ty"] && array_key_exists($_GET["ty"], $time_array)) {
		$time_ty = $_GET["ty"];
	}

	if ($_GET["btime"]) {
		$tb = strtotime($_GET["btime"]." 0:0:0");
		$where[] = "$time_ty>=$tb";
	}
	if ($_GET["etime"]) {
		$te = strtotime($_GET["etime"]." 23:59:59");
		$where[] = "$time_ty<$te";
	}

	if ($_GET["status"] == '') $_GET["status"] = "all";
	if ($st = $_GET["status"]) {
		if ($st != "all") {
			$where[] = ($st == "come") ? "status=1" : "status!=1";
		}
	}

	if ($_GET["part"]) {
		$where[] = "part_id=".intval($_GET["part"]);
	}

	if ($_GET["depart"]) {
		$where[] = "depart=".intval($_GET["depart"]);
	}

	if ($_GET["acc"]) {
		$where[] = "account='".$_GET["acc"]."'";
	}

	if ($_GET["disease"]) {
		$where[] = "disease_id=".intval($_GET["disease"]);
	}

	if ($_GET["author"]) {
		$where[] = "author='".$_GET["author"]."'";
	}

	$sqlwhere = count($where) ? ("where ".implode(" and ", $where)) : "";

	$sort = $_GET["sort"] ? $_GET["sort"] : "order_date";



	// ���:
	$fields = $_GET["fields"];

	// ��������ת��:
	if (in_array("disease_id", $fields)) {
		//$disease_id_name = $db->query("select id,name from disease", "id", "name");
	}

	$output_name = array();

	// �����ͷ:
	$head = array();
	foreach ($fields as $x) {
		$head[] = $sys_fields[$x];
	}
	$output_name[] = @implode("\t", $head);

	$q = mysql_query("select * from $table $sqlwhere order by $sort asc");
	while ($li = mysql_fetch_assoc($q)) {
	    //�ж����ڵ�20200806-��ʼ
			if($li[suozaidi] == 1){$li[suozaidi] = '����';}
			else if($li[suozaidi] == 2){$li[suozaidi] = '���';}
			else if($li[suozaidi] == 3){$li[suozaidi] = '����';}
			else if($li[suozaidi] == 4){$li[suozaidi] = '��˳';}
			else if($li[suozaidi] == 5){$li[suozaidi] = '�Ͻ�';}
			else if($li[suozaidi] == 6){$li[suozaidi] = '����';}
			else if($li[suozaidi] == 7){$li[suozaidi] = '����ˮ';}
			else if($li[suozaidi] == 8){$li[suozaidi] = 'ǭ��';}
			else if($li[suozaidi] == 9){$li[suozaidi] = 'ǭ����';}
			else if($li[suozaidi] == 10){$li[suozaidi] = 'ǭ����';}
			else if($li[suozaidi] == 11){$li[suozaidi] = 'ͭ��';}
			else{$li[suozaidi] = '����';}
		//�ж����ڵ�20200806-����	
		$line = array();
		foreach ($fields as $x) {
			if ($x == "order_date" || $x == "addtime") {
				$y = @date("Y-m-d H:i", $li[$x]);
			} else if ($x == "disease_id") {
				$y = $disease_id_name[$li[$x]];
			} else if ($x == "status") {
				$y = $li[$x] == 1 ? "�ѵ�Ժ" : "δ��Ժ";
			} else if ($x == "tel") {
				$y = tel_filter($li);
			} else {
				$y = $li[$x];
			}
			// �滻���лس�����Ϊ�ո�:
			$y = str_replace("\n", " ", str_replace("\r", "", $y));
			// ����ո��滻Ϊһ��:
			while (substr_count($y, "  ") > 0) {
				$y = str_replace("  ", " ", $y);
			}
			// ��ֵ��ʾ������Ϊռλ
			$line[] = (trim($y) == "" ? "-" : $y);
		}
		
		$output_name[] = @implode("\t", $line);
	}

	$output_num = count($output_name) - 1;
	$output_name = implode("\r\n", $output_name);

	if ($output_num > 500) {
		$_GET["asfile"] = 1;
	}

	//user_op_log("����[".$output_num."]������");

	// ����ļ�: 2011-12-14
	if ($_GET["asfile"]) {
		$file_name = $h_name."_".$_GET["btime"]."_".$_GET["etime"].".txt";
		header('Content-type: application/txt');
		header('Content-Disposition: attachment; filename="'.$file_name.'"');
		echo $output_name;
		exit;
	}
}


function _wee_date($unix_time) {
	$date = date("Y-m-d", $unix_time);
	$h = date("H", $unix_time);
	if ($h >= 7 && $h < 12) {
		$date .= " ����";
	} else if ($h >= 12 && $h < 18) {
		$date .= " ����";
	} else if ($h >= 18 && $h < 22) {
		$date .= " ����";
	} else {
		$date .= " ȫ��";
	}
	return $date;
}


$title = $h_name.' - ������������';

//user_op_log("�򿪵�����������");
?>
<html>
<head>
<title>��������</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
#tiaojian {border:2px solid #fdb53d; background:#f7ecdf; padding:10px; }
form {display:inline; }

#result {margin-left:0px; margin-top:0px; }
.h_name {font-weight:bold; margin-top:20px; }
.h_kf {margin-left:0px; }
.kf_li {border-bottom:0px dotted silver; }
</style>
<script type="text/javascript">
function check_field_all(o) {
	var chk = o.title == "ȫѡ" ? "checked" : "";
	var objs = byid("field_span").getElementsByTagName("INPUT");
	for (var i=0; i<objs.length; i++) {
		var obj = objs[i];
		if (obj.type == "checkbox") {
			obj.checked = chk;
		}
	}
	o.title = o.title == "ȫѡ" ? "ȫ��ѡ" : "ȫѡ";
}

function check_data() {
	alert("��ע�⣺��������Ļ��߳���500����ҳ����ʾ���Ƚ��������Զ����ļ��ķ�ʽ������");
	return true;
}
</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips"><?php echo $title; ?></nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">ˢ��</button></td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>
<div id="tiaojian">
	<div>
	<span>����������</span>
	<form method="GET" onsubmit="return check_data();">
	<select name="ty" class="combo">
		<option value="" style="color:gray">-ʱ������-</option>
		<?php echo list_option($time_array, "_key_", "_value_", $time_ty); ?>
	</select>&nbsp;
	<input name="btime" id="begin_time" class="input" style="width:80px" value="<?php echo $_GET["btime"] ? $_GET["btime"] : date("Y-m-01"); ?>">
	<img src="image/calendar.gif" id="order_date" onClick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="ѡ��ʱ��">

	<input name="etime" id="end_time" class="input" style="width:80px" value="<?php echo $_GET["etime"] ? $_GET["etime"] : date("Y-m-d"); ?>">
	<img src="image/calendar.gif" id="order_date" onClick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="ѡ��ʱ��">

	<select name="status" class="combo">
		<option value="" style="color:gray">-�Ƿ�Ժ-</option>
		<?php echo list_option($status_array, "_key_", "_value_", $_GET["status"]); ?>
	</select>&nbsp;
	<select name="sort" class="combo">
		<option value="" style="color:gray">-�������-</option>
		<?php echo list_option($sort_array, "_key_", "_value_", $_GET["sort"]); ?>
	</select>&nbsp;
	<select name="part" class="combo">
		<option value="" style="color:gray">-����-</option>
		<?php echo list_option($part_array, "_key_", "_value_", $_GET["part"]); ?>
	</select>&nbsp;
	<select name="depart" class="combo">
		<option value="" style="color:gray">-����-</option>
		<?php echo list_option($depart_array, "_key_", "_value_", $_GET["depart"]); ?>
	</select>&nbsp;
	<select name="acc" class="combo">
		<option value='' style="color:gray">-�˺�-</option>
		<?php echo list_option($account_array, "_value_", "_value_", $_GET["acc"]); ?>
	</select>&nbsp;
	<select name="disease" class="combo">
		<option value='' style="color:gray">-����-</option>
		<?php echo list_option($display_disease_arr, "_key_", "_value_", $_GET["disease"]); ?>
	</select>&nbsp;
	<select name="author" class="combo">
		<option value='' style="color:gray">-��ѯԱ-</option>
		<?php echo list_option($author_array, "_value_", "_value_", $_GET["author"]); ?>
	</select>&nbsp;
	<input type="checkbox" class="check" name="asfile" id="asfile" value="1" <?php echo $_GET["asfile"] ? "checked" : ""; ?>><label for="asfile">����Ϊ�ļ�</label>
	<br>

	<span id="field_span">
	<a title="ȫ��ѡ" href="javascript:;" onclick="check_field_all(this)"><b>ȫѡ</b></a>��
<?php foreach ($sys_fields as $fn => $fv) { ?>
	<input type="checkbox" class="check" name="fields[]" id="ch_<?php echo $fn; ?>" value="<?php echo $fn; ?>" <?php echo (@in_array($fn, $_GET["fields"]) ? "checked" : ""); ?>><label for="ch_<?php echo $fn; ?>"><?php echo $fv; ?></label>
<?php } ?>
	</span>

	<input type="submit" class="button" value="�ύ">
	<input type="hidden" name="op" value="show">
	</form>
	</div>
</div>

<?php if ($op == "show") { ?>
<div style="margin:20px 0 5px 0px; color:red; ">����������<b><?php echo $output_num; ?></b>�� &nbsp; �������Ļ���̫�࣬�ɹ�ѡ������Ϊ�ļ���ֱ�����ء�</div>
<div id="result">
	<textarea id="result_box" style="width:100%; height:400px;" class="input"><?php echo $output_name; ?></textarea><br>
	<br>
	˵�����ϱ����Ľ�����Ƶ�Excel�У����Զ�������ʾ��<br>
	<br>
</div>
<?php } ?>


</body>
</html>