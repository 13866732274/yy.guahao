<?php
// --------------------------------------------------------
// - ����˵�� : ����ͨ��¼����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-11-11
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_web";

$hids = count($hospital_ids) ? implode(",", $hospital_ids) : "0";
$types_arr = $db->query("select id, h_name, name from count_type where type='web' and hid in ($hids) and ishide=0 and local_area!='' and swt_names!='' order by h_name asc, name asc", "id");
$hos_options = array();
foreach ($types_arr as $li) {
	$hos_options[] = array("k" => $li["id"], "v" => "��".$li["name"]);
}

$type_id = intval($_REQUEST["type_id"]);
if ($type_id <= 0) {
	$type_id = $_GET["type_id"] = $_SESSION["count_type_id_web"];
}

$swt_to_zixun_name = array();

if ($type_id > 0) {
	$type_info = $db->query("select * from count_type where id=$type_id limit 1", 1);
	if ($type_info["swt_names"] != "") {
		$swt_name = str_replace("\r", "", $type_info["swt_names"]);
		$swt_name_arr = explode("\n", $swt_name);
		foreach ($swt_name_arr as $v) {
			list($_zixun, $_swt) = explode("=", $v, 2);
			if ($_swt != '' && $_zixun != '') {
				$swt_to_zixun_name[$_swt] = $_zixun;
			}
		}
	}

	$swt_to_zixun_show = '';
	foreach ($swt_to_zixun_name as $k => $v) {
		$swt_to_zixun_show .= $k."=".$v."���� ";
	}

}

if ($op == "do_update") {

	if (empty($_SESSION["swt_daoru"])) {
		exit("����̫��û�в������������ڱ�Ĵ��ڴ�����ˣ�����������...");
	}

	$type_id = intval($_SESSION["swt_daoru"]["type_id"]);
	$all_date_list = $_SESSION["swt_daoru"]["all_date_list"];
	$swt_to_zixun_name = $_SESSION["swt_daoru"]["swt_to_zixun_name"];
	$all_click = $_SESSION["swt_daoru"]["all_click"];
	$click = $_SESSION["swt_daoru"]["click"];
	$click_local = $_SESSION["swt_daoru"]["click_local"];
	$click_other = $_SESSION["swt_daoru"]["click_other"];
	$ok_click = $_SESSION["swt_daoru"]["ok_click"];
	$ok_click_local = $_SESSION["swt_daoru"]["ok_click_local"];
	$ok_click_other = $_SESSION["swt_daoru"]["ok_click_other"];
	$file_upload_name = $_SESSION["swt_daoru"]["file_upload_name"];

	//echo "<pre>";
	//print_r($_SESSION["swt_daoru"]);

	$type_name = $db->query("select name from count_type where id=$type_id", 1, "name");

	foreach ($all_date_list as $date) {
		$kefu_names = array_keys($all_click[$date]);
		foreach ($kefu_names as $kefu) {
			$zixun = $swt_to_zixun_name[$kefu];
			if (empty($zixun)) continue; //�Թ�

			$sql_set = array();
			$sql_set[] = "all_click=".intval($all_click[$date][$kefu]);
			$sql_set[] = "click=".intval($click[$date][$kefu]);
			$sql_set[] = "click_local=".intval($click_local[$date][$kefu]);
			$sql_set[] = "click_other=".intval($click_other[$date][$kefu]);
			$sql_set[] = "ok_click=".intval($ok_click[$date][$kefu]);
			$sql_set[] = "ok_click_local=".intval($ok_click_local[$date][$kefu]);
			$sql_set[] = "ok_click_other=".intval($ok_click_other[$date][$kefu]);

			$sql_set_str = implode(", ", $sql_set);

			// ����Ƿ���������:
			$old = $db->query("select * from count_web where type_id=$type_id and date=$date and kefu='$zixun'", 1);
			if ($old["id"] > 0) {
				$db->query("update count_web set $sql_set_str where id=".$old["id"]." limit 1");
			} else {
				$db->query("insert into count_web set type_id=$type_id, type_name='$type_name', date='$date', kefu='$zixun', $sql_set_str, addtime=$time, uid=$uid, u_realname='$realname', memo='SWT������'");
			}
		}
	}

	//@unlink($file_upload_name);
	unset($_SESSION["swt_daoru"]);

	echo '<script> parent.msg_box("�����ѳɹ�����", 2); </script>';
	echo '<script> parent.load_src(0); </script>';

	exit;
}

if ($op == "daoru_do") {

	//echo "<pre>";
	//print_r($_POST);
	//print_r($_FILES);

	// �ϴ��ļ�:
	if ($_FILES["file"]["name"]) {

		$allow_file_type = explode(" ", "xls");
		$file_max_size = 8 * 1024 * 1024; //8M

		$to_dir = "v6/data/";
		@chmod(ROOT.$to_dir, 0777);

		$upname = $_FILES["file"]["name"];
		$type = $_FILES["file"]["type"];
		$uptmpname = $_FILES["file"]["tmp_name"];
		$upsize = $_FILES["file"]["size"];

		// ��չ��:
		$ext = strtolower(strrchr($upname, '.'));
		$just_ext = substr($ext, 1); // ��������ŵ�ext

		// ����ļ���̫�������е�һЩ
		if (strlen($upname) > 50) {
			$elens = strlen($just_ext) + 1;
			$just_name = substr($upname, 0, -$elens);
			$just_name = cut($just_name, 40, "");
			$upname = $just_name.".".$just_ext;
		}

		// �ļ���С���Ƽ��:
		if ($file_max_size > 0 && $upsize > $file_max_size) {
			echo "��".$upname."�� �ļ���С����".$file_max_size." �ϴ�δ�ɹ�<br>";
			exit;
		}

		// �ļ��������Ƽ��:
		if (!in_array($just_ext, $allow_file_type)) {
			echo "��".$upname."�� ����������ϴ���ʽ�����ļ�δ�ϴ��ɹ�<br>";
			exit;
		}

		// �����µ��ļ���:
		$newname = "swt_".date("Ymd_His").$ext;

		// �ƶ�����ļ�:
		if (move_uploaded_file($uptmpname, ROOT.$to_dir.$newname)) {
			$file_upload_name = ROOT.$to_dir.$newname;
		}
	}


	if ($file_upload_name == '') {
		exit("�ļ�δ�ϴ��ɹ���");
	}

	if (substr($file_upload_name, -4) != ".xls") {
		exit("�ļ���ʽ����ȷ��ֻ������ .xls ��ʽ��");
	}

	echo "�ļ� ".basename($file_upload_name)." ���ϴ��ɹ�<br>";

	require 'lib/PHPExcel/IOFactory.php';
	$objReader = PHPExcel_IOFactory::createReader('Excel5');
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($file_upload_name);
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$highestRow = $objWorksheet->getHighestRow();
	$highestColumn = $objWorksheet->getHighestColumn();
	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
	$excelData = array();

	echo "�Ѽ��ص�PHPExcel��<br>";
	echo "������ = ".$highestRow."<br>";

	// ȥ����һ�� ��ͷ:
	for ($column = 0; $column < 50; $column++) {
		$val = (string) $objWorksheet->getCellByColumnAndRow($column, 1)->getValue();
		$header[$column] = trim(mb_convert_encoding(trim($val), "gbk", "UTF-8"));
	}

	$need_head["date"] = array_search("��ʼ����ʱ��", $header);
	$need_head["count_customer"] = array_search("����ѶϢ��", $header);
	$need_head["count_kefu"] = array_search("�ͷ�ѶϢ��", $header);
	$need_head["area"] = array_search("IP��λ", $header);
	$need_head["kefu"] = array_search("��ʼ�Ӵ��ͷ�", $header);

	if ($need_head["date"] === false || $need_head["count_customer"] === false || $need_head["count_kefu"] === false || $need_head["area"] === false || $need_head["kefu"] === false) {
		exit("��ͷ��ȡ���󣬵�һ��δ�����㹻���У���ʼ����ʱ�������ѶϢ�����ͷ�ѶϢ����IP��λ����ʼ�Ӵ��ͷ�");
	}

	echo "��ͷ�������<br>";

	for ($row = 2; $row <= $highestRow; $row++) {
		foreach ($need_head as $data_name => $column) {
			$val = (string) $objWorksheet->getCellByColumnAndRow($column, $row)->getValue();
			$data[$row][$data_name] = trim(mb_convert_encoding(trim($val), "gbk", "UTF-8"));
		}
	}

	echo "���ݶ�ȡ���<br><br>";


	// ���ļ�ɾ��
	//@unlink($file_upload_name);

	// Ҫ���µ�ָ�����ڣ����û������±����е���������
	$todo_date = 0;
	if ($_POST["date"] != "") {
		$todo_date = date("Ymd", strtotime($_POST["date"]));
	}

	$kefu_data = array();
	foreach ($data as $arr) {
		$date = date("Ymd", strtotime(str_replace("/", "-", $arr["date"])));
		if ($todo_date > 0 && $date != $todo_date) continue;
		$all_date_list[$date] = $date;
		$kefu = $arr["kefu"];
		if ($kefu == '') {
			continue;
		}
		if ($arr["count_customer"] + $arr["count_kefu"] >= 1) {
			$all_click[$date][$kefu] += 1;
		}
		if ($arr["count_customer"] >= 1 && $arr["count_kefu"] >= 1) {
			$click[$date][$kefu] += 1;
			if (substr_count($arr["area"], $type_info["local_area"]) > 0) {
				$click_local[$date][$kefu] += 1;
			} else {
				$click_other[$date][$kefu] += 1;
			}
		}
		if ($arr["count_customer"] >= 5 && $arr["count_kefu"] >= 5) {
			$ok_click[$date][$kefu] += 1;
			if (substr_count($arr["area"], $type_info["local_area"]) > 0) {
				$ok_click_local[$date][$kefu] += 1;
			} else {
				$ok_click_other[$date][$kefu] += 1;
			}
		}
	}


	// ��������Ԥ��:
	$preview = '';
	foreach ($all_date_list as $date) {
		$preview .= '<br><div>���ڣ�'.int_date_to_date($date).'</div>';
		$preview .= '<table width="100%" class="list"><tr>';
		$preview .= '  <td class="head">����ͨ�ͷ�</td>';
		$preview .= '  <td class="head">��Ӧ��ѯԱ����</td>';
		$preview .= '  <td class="head">���е��(1����)</td>';
		$preview .= '  <td class="head">�ܵ��(1����1��)</td>';
		$preview .= '  <td class="head">����</td>';
		$preview .= '  <td class="head">���</td>';
		$preview .= '  <td class="head">��Ч(5����5��)</td>';
		$preview .= '  <td class="head">����</td>';
		$preview .= '  <td class="head">���</td>';
		$preview .= '</tr>';

		$_kefu_name = array_keys($all_click[$date]);
		sort($_kefu_name);
		foreach ($_kefu_name as $kefu) {
			$preview .= '<tr>';
			$preview .= '  <td class="item">'.$kefu.'</td>';
			$preview .= '  <td class="item">'.$swt_to_zixun_name[$kefu].'</td>';
			$preview .= '  <td class="item">'.$all_click[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$click[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$click_local[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$click_other[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$ok_click[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$ok_click_local[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$ok_click_other[$date][$kefu].'</td>';
			$preview .= '</tr>';
		}
		$preview .= '</table>';
	}

	$_SESSION["swt_daoru"] = array();
	if (count($all_date_list) > 0 && count($all_click) > 0) {
		$_SESSION["swt_daoru"]["type_id"] = $type_id;
		$_SESSION["swt_daoru"]["all_date_list"] = $all_date_list;
		$_SESSION["swt_daoru"]["swt_to_zixun_name"] = $swt_to_zixun_name;
		$_SESSION["swt_daoru"]["all_click"] = $all_click;
		$_SESSION["swt_daoru"]["click"] = $click;
		$_SESSION["swt_daoru"]["click_local"] = $click_local;
		$_SESSION["swt_daoru"]["click_other"] = $click_other;
		$_SESSION["swt_daoru"]["ok_click"] = $ok_click;
		$_SESSION["swt_daoru"]["ok_click_local"] = $ok_click_local;
		$_SESSION["swt_daoru"]["ok_click_other"] = $ok_click_other;
		$_SESSION["swt_daoru"]["file_upload_name"] = $file_upload_name;
	} else {
		$preview_nodata = 'û��ָ�����ڵ����ݣ����߱������ݸ�ʽ��������';
	}

	$in_preview_mode = 1;
}


?>
<html>
<head>
<title>����ͨ��¼����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>

<style type="text/css">
.input_file {font-size:12px; }
#submit_div {text-align:center; margin-top:30px; }
</style>

<script type="text/javascript">
function reload(obj) {
	if (obj.value > 0) {
		self.location = "?type_id="+obj.value;
	}
}
function check_data(f) {
	if (!confirm("�ϴ���񲢷������̽������ύ�������ĵȴ��������в�Ҫ���������������Ƿ�ȷ�������ύ��")) {
		return false;
	}
	byid("submit_div").innerHTML = '<img src="image/loading.gif" align="absmiddle"> �ύ�����У����Ժ�...';
}
</script>


</head>

<body style="padding:30px; ">

<?php if ($in_preview_mode) { ?>

	<?php if ($preview_nodata) { ?>
		<?php echo $preview_nodata; ?>
	<?php } else { ?>
		<div>��Ԥ���������ݣ����ȷ�����£������ݱ��浽ϵͳ�С�������Ӧ��ѯԱ����Ϊ�գ���������ݲ�����£�</div>
		<?php echo $preview; ?>
		<form name="mainform" method="GET">
			<div id="submit_div"><input type="submit" class="submit" value="ȷ������"></div>
			<input type="hidden" name="op" value="do_update">
		</form>
	<?php } ?>

<?php } else { ?>

	<form name="mainform" method="POST" enctype="multipart/form-data" onsubmit="return check_data(this)">
	<table width="100%" class="new_edit">
		<tr>
			<td class="left"><font color="red">*</font> ����Ҫ�����ͳ���</td>
			<td class="right">
				<select name="type_id" class="combo" onchange="reload(this)" style="width:250px">
					<option value="">��������</option>
					<?php echo list_option($hos_options, "k", "v", $_GET["type_id"]); ?>
				</select>��(������ͳ����δ���֣����ʾ����ͨ��Ӧ����û�����ú�)
			</td>
		</tr>
	<?php if ($_GET["type_id"] > 0) { ?>
		<tr>
			<td class="left">�������ڵأ�</td>
			<td class="right">
				<?php echo $type_info["local_area"]; ?>
			</td>
		</tr>
		<tr>
			<td class="left">����ͨ������Ӧ��ѯԱ������</td>
			<td class="right">
				<?php echo trim($swt_to_zixun_show); ?>
			</td>
		</tr>
	<?php } ?>
		<tr>
			<td class="left"><font color="red">*</font> ����ͨ�����ļ���</td>
			<td class="right"><input name="file" type="file" class="input_file"  style="width:250px">��(������ֶΣ���ʼ����ʱ�������ѶϢ�����ͷ�ѶϢ����IP��λ����ʼ�Ӵ��ͷ�)</td>
		</tr>
		<tr>
			<td class="left">�������ڣ�</td>
			<td class="right"><input name="date" id="date" class="input" style="width:100px" value="" onclick="picker({el:'date',dateFmt:'yyyy-MM-dd'})">��(�����ѡ�����Ա������������ڸ���ϵͳ)</td>
		</tr>
	</table>

	<div id="submit_div"><input type="submit" class="submit" value="�ύ����"></div>

	<input type="hidden" name="op" value="daoru_do">

	</form>

	<!-- Ԥ����ͼƬ -->
	<div style="display:none;"><img src="image/loading.gif"></div>

<?php } ?>

</body>
</html>
