<?php
/*
// ˵��: ������ҳ��ʾ����ģ��
// ����: ���� (weelia@126.com)
// ʱ��: 2011-09-10
*/
require "lib/set_env.php";

$op = $_REQUEST["op"];

if ($op == "save") {
	$hs = trim(@implode("\r\n", $_POST["patient_headers"]));
	if ($uid > 0) {
		$db->query("update sys_admin set patient_headers='".$hs."' where id='".$uid."' limit 1");
	} else {
		$_SESSION["patient_headers"] = $hs;
	}

	//user_op_log("���ò����б���ʾ�ֶ�");

	echo '<script type="text/javascript">'."\r\n";
	echo 'parent.load_box(0);'."\r\n";
	echo 'parent.msg_box("���ñ���ɹ�")'."\r\n";
	echo 'parent.update_content()'."\r\n";
	echo '</script>'."\r\n";
	exit;
}

if ($debug_mode) {
	$show_headers_str = $_SESSION["patient_headers"];
	$config["show_xiaofei"] = 2;
}
$show_headers_str = $uinfo["patient_headers"];

$show_headers_str = str_replace("\r", "", $show_headers_str);
$show_headers = explode("\n", $show_headers_str);

$all_headers = array();
$all_headers["name"] = "����";
$all_headers["sex"] = "�Ա�";
$all_headers["age"] = "����";
$all_headers["tel"] = "�绰";
$all_headers["zhuanjia_num"] = "ר�Һ�";
$all_headers["card_id"] = "���֤��";
$all_headers["content"] = "��ѯ����|��ע|�ط�";
$all_headers["order_date"] = "ԤԼʱ��";
//$all_headers["remain_time"] = "ʣ������";
$all_headers["disease_id"] = "��������";
$all_headers["media_from"] = "ý����Դ";
$all_headers["engine"] = "��������";
$all_headers["from_site"] = "��վ��Դ";
$all_headers["key_word"] = "�ؼ���";
$all_headers["youhuazu"] = "�Ż���";
$all_headers["part_id"] = "����";
$all_headers["depart"] = "����";
$all_headers["order_soft"] = "���";
//$all_headers["shichang"] = "�г�";
$all_headers["account"] = "�˺�";
$all_headers["author"] = "�ͷ�";
if ($config["show_xiaofei"] > 0 || $debug_mode) {
	$all_headers["xiaofei"] = "����";
}
if ($config["show_guiji"] > 0 || $debug_mode) {
	$all_headers["guiji"] = "�켣";
}
$all_headers["status"] = "״̬";
$all_headers["doctor"] = "ҽ��";
$all_headers["yibao"] = "ҽ��";
$all_headers["suozaidi"] = "�־�ס��";
$all_headers["tuiguangren"] = "�ƹ���";
$all_headers["addtime"] = "���ʱ��";


if (count($show_headers) <= 1) {
	if ($debug_mode) {
		$show_headers = explode(" ", "name tel content order_date disease_id media_from part_id doctor addtime status author op");
	}
	if (in_array($uinfo["part_id"], array(1,9,13))) { // ��Щ�ǹ�����Ա
		$show_headers = explode(" ", "name tel content order_date disease_id media_from part_id doctor guiji addtime status yibao author op");
	}
	if (in_array($uinfo["part_id"], array(2))) { //����ͷ�
		$show_headers = explode(" ", "name sex tel content order_date disease_id media_from depart addtime status doctor xiaofei author op");
	}
	if (in_array($uinfo["part_id"], array(3))) { //�绰�ͷ�
		$show_headers = explode(" ", "name sex tel content order_date disease_id media_from depart addtime status doctor xiaofei huifang_time author op");
	}
	if (in_array($uinfo["part_id"], array(4))) { //��ҽ
		$show_headers = explode(" ", "name sex tel content order_date disease_id doctor depart addtime author status yibao op");
	}
	if (in_array($uinfo["part_id"], array(12))) { //�绰�ط�
		$show_headers = explode(" ", "name sex tel content order_date disease_id doctor huifang_time depart addtime author status op");
	}
	if (in_array($uinfo["part_id"], array(14, 15))) { //�ֳ�ҽ��
		$show_headers = explode(" ", "name sex tel content order_date disease_id doctor huifang_time depart addtime author status op");
	}
	if (count($show_headers) < 1) { //�κ��������
		$show_headers = array_keys($all_headers);
	}
}


// �������������Դ��Ȩ��:
if (!$debug_mode) {
	if ($config["show_engine"] != 1) {
		unset($all_headers["engine"]);
		unset($all_headers["from_site"]);
		unset($all_headers["key_word"]);
		unset($all_headers["youhuazu"]);
	}
}


header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
?>
<html>
<head>
<title>���ñ�ͷ�ֶ�</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.aline {margin-left:20px; float:left; width:160px; }
.submit_line {text-align:center; padding:10px; }
legend b {color:#ff8040; }
a.b {font-weight:bold; color:#008080 }
a.b:hover {color:red; }
</style>
</head>

<body>
<form name="mainform" action="" method="POST">
<fieldset>
	<legend><b>�빴ѡҪ��ʾ����</b>  (ȫ����ѡ��ָ�ΪϵͳĬ��״̬) ��<a href="javascript:;" onclick="select_all();" class="b">[ȫѡ]</a>��<a href="javascript:;" onclick="select_none();" class="b">[ȫ����ѡ]</a></legend>

	<div style="margin-top:5px;">
<?php
foreach ($all_headers as $k => $v) {
?>
		<div class="aline"><input type="checkbox" name="patient_headers[]" value="<?php echo $k; ?>" <?php echo in_array($k, $show_headers) ? "checked" : ""; ?> id="im<?php echo $k; ?>" ><label for="im<?php echo $k; ?>"><?php echo $v; ?></label></div>
<?php
}
?>
		<div class="clear"></div>
	</div>
</fieldset>

<div class="submit_line">
	<input type="submit" class="buttonb" value="ȷ��" />
</div>

<input type="hidden" name="op" value="save" />

</form>

</body>
</html>