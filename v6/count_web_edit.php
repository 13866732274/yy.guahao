<?php
// --------------------------------------------------------
// - ����˵�� : ��ӡ��޸�����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2010-10-08 13:31
// --------------------------------------------------------

$date = $_REQUEST["date"];
if (!$date) {
	exit("��������");
}

$kefu = $_GET["kefu"];
if (!$kefu) {
	exit("��������");
}

if ($_POST) {
	$r = array();

	// �ж��Ƿ��Ѿ����:
	$mode = "add";
	$s_date = date("Ymd", strtotime($date." 0:0:0"));
	$kefu = $_POST["kefu"];
	$cur_id = $db->query("select id from $table where type_id=$cur_type and kefu='$kefu' and date='$s_date' limit 1", 1, "id");
	if ($cur_id > 0) {
		$mode = "edit";
		$id = $cur_id;
	}

	if ($mode == "add") {
		$r["type_id"] = $cur_type;
		$r["type_name"] = $db->query("select name from count_type where id=".$r["type_id"]." limit 1", 1, "name");
		$r["date"] = $s_date;
		$r["kefu"] = $_POST["kefu"];
	}

	$r["all_click"] = $_POST["all_click"];
	$r["click"] = $_POST["click"];
	$r["click_local"] = $_POST["click_local"];
	$r["click_other"] = $_POST["click_other"];

	$r["ok_click"] = $_POST["ok_click"];
	$r["ok_click_local"] = $_POST["ok_click_local"];
	$r["ok_click_other"] = $_POST["ok_click_other"];

	$r["talk_swt"] = $_POST["talk_swt"];
	$r["talk_tel"] = $_POST["talk_tel"];
	$r["talk_other"] = $_POST["talk_other"];
	$r["talk"] = $r["talk_swt"] + $r["talk_tel"] + $r["talk_other"];

	$r["orders_swt"] = $_POST["orders_swt"];
	$r["orders_tel"] = $_POST["orders_tel"];
	$r["orders_other"] = $_POST["orders_other"];
	$r["orders"] = $r["orders_swt"] + $r["orders_tel"] + $r["orders_other"];

	$r["come"] = $_POST["come"];
	$r["come_tel"] = $_POST["come_tel"];
	$r["come_other"] = $_POST["come_other"];
	$r["come_all"] = $r["come"] + $r["come_tel"] + $r["come_other"];

	if ($mode == "add") {
		$r["addtime"] = time();
		$r["uid"] = $uid;
		$r["u_realname"] = $realname;
	}

	$sqldata = $db->sqljoin($r);
	if ($mode == "add") {
		$sql = "insert into $table set $sqldata";
	} else {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	}

	if ($db->query($sql)) {
		if ($mode == "add") {
			echo '<script> parent.update_content(); </script>';
			echo '<script> parent.msg_box("��ӳɹ�", 2); </script>';
		} else {
			echo '<script> parent.msg_box("�޸ĳɹ����б�δ����", 2); </script>';
		}
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "�ύʧ�ܣ����Ժ����ԣ�";
	}
	exit;
}

if ($op == "edit") {
	$s_date = date("Ymd", strtotime($date." 0:0:0"));
	$line = $db->query("select * from $table where type_id=$cur_type and kefu='$kefu' and date='$s_date' limit 1", 1);
}


// ������������Ƿ���ȷ:
$admin_info = $db->query("select * from sys_admin where realname='$kefu' order by isshow desc limit 1", 1);
$user_not_exists = count($admin_info) == 0 ? 1 : 0;


// �Զ�����ϵͳ�е�����
$tb = strtotime($date);
$te = strtotime("+1 day", $tb) - 1;

$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);

// ��Χ:
if ($type_detail["data_hids"] == "") {
	$hospital_ids = array($hid);
} else if ($type_detail["data_hids"] == "-1") {
	$hospital_name = $hinfo["sname"];
	$hospital_ids = $db->query("select id from hospital where ishide=0 and sname='$hospital_name' order by id asc", "", "id");
} else {
	$hospital_ids = explode(",", $type_detail["data_hids"]);
}

//print_r($hospital_ids);

foreach ($hospital_ids as $hid) {
	$kf["yuyue_swt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and addtime>=$tb and addtime<=$te and order_soft in ('swt', 'kst')", 1, "c");
	$kf["yuyue_dh"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and addtime>=$tb and addtime<=$te and order_soft in ('dh')", 1, "c");
	$kf["yuyue_qt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and addtime>=$tb and addtime<=$te and order_soft not in ('swt', 'kst', 'dh')", 1, "c");

	$kf["yudao_swt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and order_date>=$tb and order_date<=$te and order_soft in ('swt', 'kst')", 1, "c");
	$kf["yudao_dh"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and order_date>=$tb and order_date<=$te and order_soft in ('dh')", 1, "c");
	$kf["yudao_qt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and order_date>=$tb and order_date<=$te and order_soft not in ('swt', 'kst', 'dh')", 1, "c");

	$kf["shidao_swt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and status=1 and order_date>=$tb and order_date<=$te and order_soft in ('swt', 'kst')", 1, "c");
	$kf["shidao_dh"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and status=1 and order_date>=$tb and order_date<=$te and order_soft in ('dh')", 1, "c");
	$kf["shidao_qt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and status=1 and order_date>=$tb and order_date<=$te and order_soft not in ('swt', 'kst', 'dh')", 1, "c");
}


$title = $op == "edit" ? "�޸�����" : "�������";
?>
<html>
<head>
<title><?php echo $title; ?> (<?php echo $date; ?>: <?php echo $kefu; ?>)</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.item {padding:8px 3px 6px 3px; }
</style>

<script language="javascript">
function check_data() {
	var oForm = document.mainform;
	if (oForm.code.value == "") {
		alert("�����롰��š���"); oForm.code.focus(); return false;
	}
	byid("submit").value = "�ύ��...";
	byid("submit").disabled = true;
	return true;
}

function update_cnt(o, id_a, id_b, id_c) {
	var a = byid(id_a).value;
	var b = byid(id_b).value;
	var c = byid(id_c).value;

	var cnt = (a != "" ? 1 : 0) + (b != "" ? 1 : 0) + (c != "" ? 1 : 0);

	if (cnt == 2 && (a == "" || b == "" || c == "")) {
		if (a == "") {
			byid(id_a).value = parseInt(b) + parseInt(c);
		} else if (b == "") {
			byid(id_b).value = a - c;
		} else {
			byid(id_c).value = a - b;
		}
	}
	if (cnt == 3) {
		if (o.id == id_a) {
			byid(id_c).value = a - b;
		} else if (o.id == id_b) {
			byid(id_c).value = a - b;
		} else {
			byid(id_b).value = a - c;
		}
	}
}


</script>
</head>

<body>
<div class="description">
	<div class="d_title">��ʾ��</div>
	<div class="d_item">��Ҫ������������ϣ�����ύ����</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">������ϸ����</td>
	</tr>

	<tr>
		<td class="left">���е����</td>
		<td class="right">
			<input name="all_click"  value="<?php echo $line["all_click"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left">�ܵ����</td>
		<td class="right">
			<input name="click" id="c_a" onchange="update_cnt(this,'c_a', 'c_b', 'c_c')" value="<?php echo $line["click"]; ?>" class="input" style="width:100px">
			��=�����أ�<input name="click_local" id="c_b" onchange="update_cnt(this,'c_a', 'c_b', 'c_c')" value="<?php echo $line["click_local"]; ?>" class="input"style="width:100px">
			��+����أ�<input name="click_other" id="c_c" onchange="update_cnt(this,'c_a', 'c_b', 'c_c')" value="<?php echo $line["click_other"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left">����Ч��</td>
		<td class="right">
			<input name="ok_click" id="d_a" onchange="update_cnt(this,'d_a', 'd_b', 'd_c')" value="<?php echo $line["ok_click"]; ?>" class="input" style="width:100px">
			��=�����أ�<input name="ok_click_local" id="d_b" onchange="update_cnt(this,'d_a', 'd_b', 'd_c')" value="<?php echo $line["ok_click_local"]; ?>" class="input"style="width:100px">
			��+����أ�<input name="ok_click_other" id="d_c" onchange="update_cnt(this,'d_a', 'd_b', 'd_c')" value="<?php echo $line["ok_click_other"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left">ԤԼ��</td>
		<td class="right">
			����ͨ��<input name="talk_swt" value="<?php echo $op == "add" ? $kf["yuyue_swt"] : $line["talk_swt"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yuyue_swt"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			�绰��<input name="talk_tel" value="<?php echo $op == "add" ? $kf["yuyue_dh"] : $line["talk_tel"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yuyue_dh"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			������<input name="talk_other" value="<?php echo $op == "add" ? $kf["yuyue_qt"] : $line["talk_other"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yuyue_qt"].")"; ?>
		</td>
	</tr>

	<tr>
		<td class="left">Ԥ����</td>
		<td class="right">
			����ͨ��<input name="orders_swt" value="<?php echo $op == "add" ? $kf["yudao_swt"] : $line["orders_swt"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yudao_swt"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			�绰��<input name="orders_tel" value="<?php echo $op == "add" ? $kf["yudao_dh"] : $line["orders_tel"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yudao_dh"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			������<input name="orders_other" value="<?php echo $op == "add" ? $kf["yudao_qt"] : $line["orders_other"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yudao_qt"].")"; ?>
		</td>
	</tr>

	<tr>
		<td class="left">ʵ����</td>
		<td class="right">
			����ͨ��<input name="come" value="<?php echo $op == "add" ? $kf["shidao_swt"] : $line["come"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["shidao_swt"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			�绰��<input name="come_tel" value="<?php echo $op == "add" ? $kf["shidao_dh"] : $line["come_tel"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["shidao_dh"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			������<input name="come_other" value="<?php echo $op == "add" ? $kf["shidao_qt"] : $line["come_other"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["shidao_qt"].")"; ?>
		</td>
	</tr>
</table>
<?php if ($user_not_exists) { ?>
<center style="margin-top:5px; color:red">(ϵͳ��δ��ѯ��<b><?php echo $kefu; ?></b>�����������Ƿ�����)</center>
<?php } ?>

<?php if ($op == "add") { ?>
<center style="margin-top:5px;">(ע���Զ����õ����ݴ�<b><?php echo count($hospital_ids); ?></b>����������ȡ)</center>
<?php } else { ?>
<center style="margin-top:5px;">(ע���༭�������������Ϊ<b><?php echo count($hospital_ids); ?></b>��������ʵʱ��ѯ�Ľ�������ԱȲο�)</center>
<?php } ?>
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">
<input type="hidden" name="date" value="<?php echo date("Y-m-d", strtotime($date." 0:0:0")); ?>">
<input type="hidden" name="kefu" value="<?php echo $kefu; ?>">

<div class="button_line"><input type="submit" id="submit" class="submit" value="�ύ����"></div>
</form>
</body>
</html>