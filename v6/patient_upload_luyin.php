<?php
// --------------------------------------------------------
// - ����˵�� : ���ù켣
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2013-8-11
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}


$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("��������");
}
$line = $db->query("select * from $table where id='$id' limit 1", 1);

if ($_POST) {

	include_once "lib/function.upload.php";
	$uppath = ROOT.'luyin/'.$hid."/";
	@mkdir(dirname($uppath));
	$upfile = upfile("luyin", $uppath, 8000*1024, array('wav', 'mp3'));
	$uploaded_url = '';
	if ($upfile) {
		$uploaded_url = "/luyin/".$hid."/".$upfile;
	}

	$r = array();
	if ($uploaded_url != '') {
		$r["luyin_file"] = $uploaded_url;
		$r["upload_uid"] = $uid;
		$r["upload_time"] = time();
		if ($_POST["set_status"] == "come_not_set" && $line["status"] != 1) {
			$r["status"] = "-2";
		}
	} else {
		exit("¼���ļ�δ�ϴ��ɹ��������³����ϴ���");
	}

	if (count($r) > 0) {
		$sqldata = $db->sqljoin($r);
		$sql = "update $table set $sqldata where id='$id' limit 1";
		ob_start();
		$rs = $db->query($sql);
		$error = ob_get_clean();
		if ($error) {
			echo "�ύ��������ϵ������Ա��<br>".$error;
			exit;
		} else {
			$str = "�����ύ�ɹ���";
		}
	} else {
		$str = "�����ޱ䶯";
	}
	echo '<script type="text/javascript">'."\r\n";
	echo 'parent.msg_box("'.$str.'");'."\r\n";
	echo 'parent.close_divs();'."\r\n";
	echo '</script>'."\r\n";
	exit;
}


$to_set_status_arr = array();

if ($line["status"] != 1) {
	$to_set_status_arr = array(
		"come_not_set" => "�ѵ�δ��",
	);
}


// page begin ----------------------------------------------------
?>
<html>
<head>
<title><?php echo $line["name"]; ?> - �ϴ�¼��</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.left {text-align:right; }
.right {padding:4px 0px; }
</style>
<script language="javascript">
function check_data(oForm) {
	if (oForm.luyin.value == '') {
		alert("��ѡ��¼���ļ���");
		return false;
	}
	if (!confirm("�ϴ�¼���ļ�������Ҫ�ϳ�ʱ�䣬��û���ϴ���֮ǰ���벻Ҫ��������������ȷ���ϴ���")) {
		return false;
	}
	show_upload_div();
	return true;
}

function show_upload_div() {
	byid("uploading").style.display = "block";
}
</script>
</head>

<body oncontextmenu="return false">
<form name="mainform" action="" method="POST" onsubmit="return check_data(this)" enctype="multipart/form-data">
<table width="100%" style="margin-top:10px;">
	<tr>
		<td class="left" style="width:80px; padding-top:6px;" valign="top">¼���ļ���</td>
		<td class="right">
			<input type="file" name="luyin" class="file" size="20"><br>
			<font color="gray">֧�ָ�ʽ��wav��mp3  �ļ���󲻳���5M</font>
		</td>
	</tr>
	<tr>
		<td class="left">״̬���ã�</td>
		<td class="right">
			<select name="set_status" class="combo">
				<option value="" style="color:gray">-�����޸�-</option>
				<?php echo list_option($to_set_status_arr, "_key_", "_value_", ""); ?>
			</select>
		</td>
	</tr>
</table>
<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
<div class="button_line">
	<input type="submit" class="buttonb" value="�ϴ��ļ�">
</div>
</form>


<div id="uploading" style="display:none; position:absolute; left:0; top:0; width:340px; height:160px; text-align:center; line-height:160px; background:#ffffff; "><div style="margin-top:60px;"><img src="image/loading.gif" align="absmiddle"> �ϴ��У������ĵȴ����...</div></div>

<!-- <a href="#" onclick="show_upload_div(); return false;">��һ��</a> -->

</body>
</html>