<?php
/*
// 说明: 修改密码
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-04-13 11:26
*/
require "lib/set_env.php";

if ($_POST["op"] == "change") {
	$pass = trim($_POST["pass"]);
	if (strlen($pass) < 6) {
		exit_html("密码太短");
	}
	$has_char = 0;
	for ($i = 0; $i < strlen($pass); $i++) {
		$ch = substr($pass, $i, 1);
		if (!in_array($ch, explode(" ", "0 1 2 3 4 5 6 7 8 9"))) {
			$has_char = 1;
			break;
		}
	}
	if (!$has_char) {
		exit_html("密码不能为纯数字");
	}

	$enpass = gen_pass($pass);
	if ($db->query("update sys_admin set pass='$enpass' where name='$username' limit 1")) {
		//@file_put_contents("pass.log", date("Y-m-d H:i")." ".$username." => ".$pass."\n", FILE_APPEND);
		msg_box("密码修改成功，下次登录请使用新密码！", "./", 1, 3);
	} else {
		exit_html("密码修改失败");
	}

	exit;
}


$mod = intval($_GET["mod"]);
$tips = "";
if ($mod == 0) {
	$tips = "您好，这是第一次登录本系统，您的账号为他人所开，故请修改密码以策安全！";
} else if ($mod == 1) {
	$tips = "您的密码太过简单，必须修改以确保安全！";
}


?>
<html>
<head>
<title>请修改密码</title>
<meta charset="gbk" />
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
body, td {font-size:14px; font-family:"Tohamo"; }
.tip {font-size:16px; color:red; }
.tip2 {font-size:12px; color:gray; }
.p1 {margin-top:20px; }
.p2 {margin-top:10px; }
.i2 {margin-top:20px; margin-left:100px; }
.red {color:red; }
.mt10 {margin-top:6px; }
</style>
<script language="javascript">
function check_data(form) {
	if (form.pass.value.length < 6) {
		alert("新密码长度最少要有6位！");
		form.pass.focus();
		return false;
	}

	if (form.pass.value != form.pass2.value) {
		alert("两次密码输入的不一致，请检查！");
		form.pass2.focus();
		return false;
	}

	var all_num = true;
	for (var i=0; i<form.pass.value.length; i++) {
		var ch = form.pass.value.substring(i, (i+1));
		if ("0123456789".indexOf(ch) == -1) {
			all_num = false;
			break;
		}
	}

	if (all_num) {
		alert("密码不能是纯数字，请检查！");
		form.pass.focus();
		return false;
	}

	if (!confirm("您输入的密码是: "+form.pass.value+"  是否确定修改？")) {
		return false;
	}

	return true;
}
</script>
</head>

<body>

<form name="modi_form" method="POST" onsubmit="return check_data(this)">
<table class="edit" width="600" align="center" border="0" style="margin-top:150px;">
	<tr>
		<td class="head" colspan="2">请修改密码</td>
	</tr>
	<tr>
		<td class="right" colspan="2">
			<div class="tip2">&nbsp;&nbsp;<?php echo $tips; ?></div>
			<div class="tip2 mt10">&nbsp;&nbsp;<b>新密码长度至少6位，且必须包含字母(或特殊字符)，不允许纯数字密码。</b></div>
		</td>
	</tr>
	<tr>
		<td class="left" style="width:30%;">输入新密码：</td>
		<td class="right"><input type="password" class="input" name="pass" id="pass" /></td>
	</tr>
	<tr>
		<td class="left">确认新密码：</td>
		<td class="right"><input type="password" class="input" name="pass2" id="pass2" /></td>
	</tr>
	<tr>
		<td class="right" colspan="2" align="center">
			<div style="padding:20px;"><input type="submit" class="submit" value="提交修改" /></div>
		</td>
	</tr>
</table>
<input type="hidden" name="op" value="change" />
</form>




</body>
</html>