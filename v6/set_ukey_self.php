<?php
/*
// ˵��: ������uKey
// ����: ���� (weelia@126.com)
// ʱ��: 2011-07-07
*/
require "lib/session.php";
require "lib/config.php";
require "lib/function.php";

if ($_POST) {
	$name = $_POST["username"];
	$pass = $_POST["password"];
	if (empty($name) || empty($pass)) {
		exit_html("�ʻ���Ϣ�����������������롣");
	}

	$ukey_sn = $_POST["ukey_sn"];
	if (strlen($ukey_sn) != 16) {
		exit_html("uKey��ų��Ȳ���ȷ��������16λ��");
	}

	// ���ukey�Ƿ��ѱ�ʹ��:
	if ($db->query("select count(*) as c from sys_admin where ukey_sn='".$ukey_sn."' and name!='$name' and isshow>0", 1, "c") > 0) {
		exit_html("��uKey��š�{$ukey_sn}���Ѿ�������ʹ�ã��󶨲��ɹ���");
	}

	$uinfo = array();
	ob_start();
	// �˴����Բ�������κ�sql����ѯ���󣬷��򼫶�Σ�ա�
	$uinfo = @$db->query("select * from sys_admin where name='".$name."' and pass='".gen_pass($pass)."' limit 1", 1);
	ob_end_clean();
	$uid = intval($uinfo["id"]);
	if (!$uinfo || $uid == 0) {
		exit_html("�û��������벻��ȷ���뷵���������롣");
	}

	if (strlen($uinfo["ukey_sn"]) == 16) {
		exit_html("�Բ��𣬸��˺ſ����������ڼ��ѱ������˰󶨹��ˣ��뷵�ص�½ҳ���½���ԡ�");
	}

	// ִ�а󶨲�����
	if ($db->query("update sys_admin set use_ukey=1, ukey_sn='".$ukey_sn."', ukey_no='��¼��' where id=$uid limit 1")) {
		msg_box("uKey�󶨳ɹ��������µ�½", "login.php", 3);
	}
	exit;
}

?>
<html>
<head>
<title>������uKey</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script type="text/javascript">
function write_cur_ukey_sn() {
	et99 = byid("ET99");
	if (et99) {
		window.onerror = function() {
			//alert("��ȡET99�豸���ִ���");
			return true;
		}
		var count = et99.FindToken("FFFFFFFF");
		if (count > 0) {
			et99.OpenToken("FFFFFFFF", 1)
			sn = et99.GetSN();
			if (sn != '') {
				byid("ukey_sn").value = sn;
				byid("ukey_sn_show").innerHTML = sn.substring(0,6)+"****"+sn.substring(10,16);
				return;
			}
		}
	}
}

function check_data(f) {
	if (f.ukey_sn.value == '') {
		alert("���ȡuKey���֮�����ύ��");
		return false;
	}
	if (f.username.value == '') {
		alert("����д�û�������                ");
		return false;
	}
	if (f.password.value == '') {
		alert("���������ĵ�½���룡            ");
		return false;
	}
	if (!confirm("�ύ����ȡ����Ҳ���������޸İ󶨡�ȷ��Ҫ�ύ��")) {
		return false;
	}
	return true;
}


</script>
</head>

<body>

<form method="POST" onsubmit="return check_data(this)">
	<table width="400" align="left" style="margin-left:30px; margin-top:30px;" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td colspan="2" style="font-size:14px; font-weight:bold; color:blue;">
				���Ȳ���uKey���ȴ�ϵͳ�Զ���ȡ��uKey���֮������д�û��������롣���ֹ�ˢ�´�ҳ��Ҳ���ԣ�<br><br>
				�����û��uKey���뵽���²���ȡ��Ȼ��󶨡�<br><br>
			</td>
		</tr>
		<tr>
			<td height="30" align="left">uKey��ţ�</td>
			<td><span id="ukey_sn_show" style="font-family:Tahoma; font-weight:bold;">(δ��ȡ)</span></td>
		</tr>
		<tr>
			<td width="39%" height="30" align="left">�û�������</td>
			<td width="61%"><input name="username" id="username" type="text" class="input" size="20" style="width:120px" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left">��¼���룺</td>
			<td><input name="password" id="password" type="password" class="input" size="20" style="width:120px"></td>
		</tr>
		<tr>
			<td colspan="2">
				<br />
				<br />
				ע�⣺<br />
				��1�������ܻ�ȡ������ <a href="/ukey/" title="���߰�װ��������" target="_blank">[����]</a> ���߰�װ����������ɺ󣬱�ҳ����Զ����²���ȡuKey����Ϣ��<br />
				��2��uKey�ύ���´ε�¼����ʹ��uKey�������ظ��ύ����ȷ�ϲ����uKeyû�кͱ��˵Ļ�����<br />
				<br />
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" class="submit" value="�ύ��"></td>
		</tr>
	</table>
	<input type="hidden" name="ukey_sn" id="ukey_sn" value="">
</form>

<!-- ukey -->
<object classid="clsid:e6bd6993-164f-4277-ae97-5eb4bab56443" id="ET99" name="ET99" style="left:0px; top:0px" width="0" height="0"></object>


<script type="text/javascript">
var Timer = setTimeout("self.location.reload()", 3000);
write_cur_ukey_sn();
if (byid("ukey_sn").value != '') {
	clearTimeout(Timer);
}
</script>

</body>
</html>