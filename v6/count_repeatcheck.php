<?php
// --------------------------------------------------------
// - ����˵�� : ����ظ�����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2012-03-03
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_web";

if (!($debug_mode || $uinfo["part_id"] == 9)) {
	exit("û��Ȩ��");
}

$cur_type = $_SESSION["count_type_id_web"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_web"] = $type_ids[0];
}

if (!($cur_type > 0)) {
	exit("ҽԺ��Ŀû��ѡ��");
}

$op = $_REQUEST["op"];
if ($op == "repeat_del") {
	$rc = $_GET["str"];
	if ($rc != '') {
		list($a, $b, $c) = explode("_", $rc, 3);
		$rs_arr = $db->query("select * from count_web where type_id=".intval($a)." and date=".intval($b)." and kefu='".$c."' order by id asc", "");
		if (count($rs_arr) > 1) {
			for ($i = 1; $i < count($rs_arr); $i++) {

				// �����Ǳ����:
				$back = @serialize($rs_arr[$i]);
				@file_put_contents("count_repeat_log.txt", date("Y-m-d H:i:s ").$realname." ".$back."\r\n", FILE_APPEND);

				$cur_id = $rs_arr[$i]["id"];
				$db->query("delete from count_web where id=$cur_id limit 1");
			}
			msg_box("����ɹ�", "back", 1, 2);
		} else {
			exit_html("δ��ѯ���ظ����ݣ�����ϵ����������Աȷ���Ƿ��Ѿ���������ˡ�");
		}
	} else {
		exit_html("��������ȷ");
	}
	exit;
}


// �����ظ������ֵ��ֶ�:
$db->query("update count_web set repeatcheck=concat(type_id,'_',date,'_',kefu) where repeatcheck='' ");

// ����ظ�����:
$list = $db->query("select * from (select type_name,date,kefu,repeatcheck,count(repeatcheck) as c from `count_web` where repeatcheck!='' group by repeatcheck order by c desc) as t where t.c>1");

if (count($list) == 0) {
	exit_html("��������Ŀ���������ڵ����������о�δ�����ظ����ݡ�");
}


// ҳ�濪ʼ ------------------------
?>
<html>
<head>
<title>�ظ����ݼ��</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
body {padding:5px 8px; }
form {display:inline; }
</style>
<script type="text/javascript">
function do_u_confirm() {
	return confirm("�Ƿ�ȷ��Ҫ���ظ�������ɾ����");
}
</script>
</head>

<body>
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips">�ظ����ݼ��</nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">ˢ��</button></td>
	</tr>
</table>

<div class="space"></div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center">�ظ�����</td>
		<td class="head" align="center">��Ŀ����</td>
		<td class="head" align="center">����</td>
		<td class="head" align="center">�ͷ�</td>
		<td class="head" align="center">����</td>
	</tr>
<?php foreach ($list as $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $v["c"]; ?></td>
		<td class="item" align="center"><?php echo $v["type_name"]; ?></td>
		<td class="item" align="center"><?php echo $v["date"]; ?></td>
		<td class="item" align="center"><?php echo $v["kefu"]; ?></td>
		<td class="item" align="center"><a href="?op=repeat_del&str=<?php echo $v["repeatcheck"]; ?>" onclick="return do_u_confirm()">����</a></td>
	</tr>
<?php } ?>

</table>

<br>

<div style="text-align:right;">�㡰���������Զ��Ѷ�����ظ�����ɾ����&nbsp;</div>

<br>

</body>
</html>
