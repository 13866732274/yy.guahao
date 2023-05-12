<?php
// --------------------------------------------------------
// - ����˵�� : ���õ�Ժ����Ŀ��
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2011-12-20
// --------------------------------------------------------
require "lib/set_env.php";
$table = "mubiao";

$hid = intval($_REQUEST["hid"]);
if (empty($hid)) {
	exit_html("��������");
}
$mode = "edit";
$hname = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

if ($mode == "edit") {
	check_power("e", $pinfo, $pagepower) or exit("û���޸�Ȩ��");
}

if ($_POST) {
	ob_start();
	$to_update = array();
	foreach ($_POST["mubiao"] as $m => $num) {
		$m = intval($m);
		$num = intval($num);
		$line = $db->query("select * from $table where hid=$hid and month=$m limit 1", 1);
		if ($line["id"] > 0) {
			// �޸�ģʽ
			if ($line["num"] != $num) { //�Ƿ��޸�
				// ��¼��־
				$log_str = $line["logs"].date("Y-m-d H:i:s ").$realname." ������Ŀ���ɡ�".$line["num"]."���޸�Ϊ��".$num."��\r\n";
				$log_str = addslashes($log_str);
				$db->query("update $table set num=$num, logs='$log_str' where hid=$hid and month=$m limit 1");
				$to_update["data_".$hid."_".$m] = $num;
			}
		} else {
			// û�м�¼������
			if ($num > 0) {
				$r = array();
				$r["hid"] = $hid;
				$r["hname"] = $hname;
				$r["month"] = $m;
				$r["num"] = $num;
				$r["uid"] = $uid;
				$r["uname"] = $realname;
				$r["addtime"] = time();

				$sql_data = $db->sqljoin($r);
				$db->query("insert into $table set $sql_data ");
				$to_update["data_".$hid."_".$m] = $num;
			}
		}
	}
	$error_str = ob_get_clean();

	if ($error_str != '') {
		echo "�����ύ���̳��ִ������飺".$error_str;
	} else {
		if (count($to_update) > 0) {
			foreach ($to_update as $data_id => $data_value) {
				echo '<script> parent.document.frames["sys_frame"].document.getElementById("'.$data_id.'").innerHTML = "'.$data_value.'"; </script>';
			}
		}
		echo '<script> parent.msg_box("�����ύ�ɹ�", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	}
	exit;
}


// ���6���µ�Ŀ��
$m_arr = array();
$m_arr[] = date("Ym", strtotime("+1 month")); //�¸���
$m_arr[] = date("Ym"); //����
for($i = 1; $i <= 4; $i++) {
	$m = date("Ym", strtotime("-".$i." month"));
	$m_arr[] = $m;
}
asort($m_arr);


$mubiao = array();
if ($mode == "edit") {
	foreach ($m_arr as $m) {
		$mubiao[$m] = $db->query("select num from $table where hid=$hid and month=$m limit 1", 1, "num");
	}
}
$title = "����Ŀ������";

function int_month_to_month($m) {
	if (strlen($m) == 6) {
		return substr($m, 0, 4)."-".substr($m, 4, 2);
	}
	return $m;
}

?>
<html>
<head>
<title><?php echo $title." - ".$hname; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script language="javascript">
function check_data(f) {
	return true;
}
</script>
</head>

<body>
<div class="description">
	<div class="d_title"><b>��ʾ��</b>�����������Ŀ�ꡣ</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
	<table width="100%" class="edit">
		<tr>
			<td colspan="2" class="head"></td>
		</tr>
<?php foreach ($m_arr as $m) { ?>
		<tr>
			<td class="left" style="width:40%"><?php echo int_month_to_month($m); ?>����Ŀ�꣺</td>
			<td class="right" style="width:60%"><input name="mubiao[<?php echo $m; ?>]" value="<?php echo $mubiao[$m]; ?>" class="input" style="width:200px"></td>
		</tr>
<?php } ?>
	</table>
	<input type="hidden" name="hid" value="<?php echo $hid; ?>">
	<div class="button_line">
		<input type="submit" class="submit" value="�ύ����">
	</div>
</form>

</body>
</html>