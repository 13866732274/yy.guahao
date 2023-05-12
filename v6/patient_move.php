<?php
// --------------------------------------------------------
// - ����˵�� : ת������
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2011-12-19
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$hid;

if (empty($hid)) {
	exit_html("�Բ���û��ѡ��ҽԺ����������ҳѡ��ҽԺ��");
}
$hname = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

if ($_POST) {
	$fromname = $_POST["fromname"];
	$touid = intval($_POST["touid"]);
	if ($fromname != '' && $touid > 0) {
		$toname = $db->query("select realname from sys_admin where id=$touid limit 1", 1, "realname");
		if ($db->query("update $table set uid={$touid}, author='{$toname}' where binary author='{$fromname}'")) {
			msg_box("����ɹ���", "patient_move.php", 1);
		}
	}
}


$title = '����ת�ƹ���';

$kefu_23_list = $db->query("select author,count(author) as acount from $table where author!='' group by author order by binary author");
foreach ($kefu_23_list as $k => $li) {
	$kefu_23_list[$k]["author_name"] = $li["author"]." (".$li["acount"].")";
}

// ת�Ƶ������û�:
$new_user_id_name = $db->query("select id,concat(realname,' [',id,']') as realname from sys_admin where concat(',',hospitals,',') like '%,{$hid},%' order by realname asc", "id", "realname");

?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<script language="javascript">
function Check() {
	var oForm = document.mainform;
	if (oForm.fromname.value == "") {
		alert("��ѡ��ԭ���֡���"); oForm.fromname.focus(); return false;
	}
	if (oForm.touid.value == "") {
		alert("��ѡ�������֡���"); oForm.touid.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>
<!-- ͷ�� begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips"><?php echo $hname." - ".$title; ?></nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">ˢ��</button></td>
	</tr>
</table>
<!-- ͷ�� end -->

<div class="space"></div>

<div class="description">
	<div class="d_title">����˵����</div>
	<div class="d_item">�˹�����;Ϊ�����˹�����������ԭ����������ӵģ���������轫���޸�Ϊ������ӵģ��˹��߼��ɽ����ע�⣬���������Ա���Ų�ͬ����������ʹ�á��������ù��ߡ����ò��˶�Ӧ�Ĳ��š�</div>
</div>

<div class="space"></div>

<form name="mainform" action="?action=move" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">ת������</td>
	</tr>
	<tr>
		<td class="left red"><font color="red">*</font> ԭ���֣�</td>
		<td class="right">
			<select name="fromname" class="combo" style="width:200px;">
				<option value='' style="color:gray">--��ѡ��--</option>
				<?php echo list_option($kefu_23_list, 'author', 'author_name', ''); ?>
			</select>
			<span class="intro">����������Ϊ����ӵĲ�������</span>
		</td>
	</tr>
	<tr>
		<td class="left red"><font color="red">*</font> �����֣�</td>
		<td class="right">
			<select name="touid" class="combo" style="width:200px;">
				<option value='' style="color:gray">--��ѡ��--</option>
				<?php echo list_option($new_user_id_name, '_key_', '_value_', ''); ?>
			</select>
			<span class="intro">����������ΪUID</span>
		</td>
	</tr>
</table>

<div class="button_line"><input type="submit" class="submit" value="�ύ"></div>

</form>
</body>
</html>