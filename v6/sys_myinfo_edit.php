<?php
// --------------------------------------------------------
// - ����˵�� : ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2015-6-25
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_admin";

$uinfo = $db->query("select * from $table where id=$uid limit 1", 1);

if ($_POST) {
	extract($_POST);

	if ($op == "set_info") {
		$db->query("update $table set mobile='$mobile', qq='$qq' where id=$uid limit 1");
		exit('<script>alert("���ϱ���ɹ���"); parent.load_src(0);</script>');
	}

	if ($op == "modify_pass") {
		if (strlen($newpass) < 6) {
			exit("�����볤������Ҫ6λ�����ϣ��뷵�������趨");
		}
		if (gen_pass($oldpass) != $uinfo["pass"]) {
			exit('<script>alert("���������벻��ȷ�������޸Ĳ��ɹ�"); history.go(-2);</script>');
		} else {
			$pass = gen_pass($newpass);
			$db->query("update $table set pass='$pass' where id=$uid limit 1");
			exit('<script>alert("�����޸ĳɹ���"); parent.load_src(0);</script>');
		}
	}

}

?>
<html>
<head>
<title>����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
* {font-family:"΢���ź�"; }
.input, .input_focus {font-family:"����" !important;}
.new_edit {border:0; margin-top:10px; }
.new_edit .left {width:30%; text-align:right; }
.new_edit .right {text-align:left; }
.new_edit td {padding:4px; }
</style>
<script type="text/javascript">
function check_pass(form) {
	if (form.oldpass.value.length < 6) {
		alert("������ԭ���룬����������6λ");
		return false;
	}
	if (form.newpass.value.length < 6) {
		alert("�����������룬����������6λ");
		return false;
	}
	if (!confirm("�����в���ʹ�����ģ�ֻ����Ӣ����ĸ�����֣��Ƿ�ȷ���ύ��")) {
		return false;
	}
	return true;
}
</script>
</head>

<body>

<table width="100%" style="margin-top:10px;">
	<tr>
		<td width="50%" valign="top">
			<form name="mainform" action="" method="POST">
			<table width="100%" class="new_edit">
				<tr>
					<td class="left">�˻�������</td>
					<td class="right"><?php echo $username; ?></td>
				</tr>
				<tr>
					<td class="left">�ֻ����룺</td>
					<td class="right"><input name="mobile" value="<?php echo $uinfo["mobile"]; ?>" class="input" style="width:200px"></td>
				</tr>
				<tr>
					<td class="left">QQ���룺</td>
					<td class="right"><input name="qq" value="<?php echo $uinfo["qq"]; ?>" class="input" style="width:200px"></td>
				</tr>
			</table>
			<div class="button_line">
				<input type="submit" class="submit" value="��������">
			</div>
			<input type="hidden" name="op" value="set_info">
			</form>
			<br>
			<br>
			<br>
		</td>

		<td width="50%" valign="top" style="border-left:1px solid silver;">
			<form name="mainform" method="POST" onsubmit="return check_pass(this)">
			<table width="100%" class="new_edit">
				<tr>
					<td class="left">* ԭ���룺</td>
					<td class="right"><input name="oldpass" type="password" style="width:150px" class="input"></td>
				</tr>
				<tr>
					<td class="left">* �����룺</td>
					<td class="right"><input name="newpass" style="width:150px" class="input"></td>
				</tr>
			</table>
			<div class="button_line">
				<input type="submit" class="submit" value="ȷ���޸�����">
			</div>
			<input type="hidden" name="op" value="modify_pass">
			</form>
		</td>
	</tr>
</table>
</body>
</html>