<?php
/*
// 说明: 自助绑定uKey
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-07-07
*/
require "lib/session.php";
require "lib/config.php";
require "lib/function.php";

if ($_POST) {
	$name = $_POST["username"];
	$pass = $_POST["password"];
	if (empty($name) || empty($pass)) {
		exit_html("帐户信息不完整，请重新输入。");
	}

	$ukey_sn = $_POST["ukey_sn"];
	if (strlen($ukey_sn) != 16) {
		exit_html("uKey编号长度不正确，必须是16位。");
	}

	// 检查ukey是否已被使用:
	if ($db->query("select count(*) as c from sys_admin where ukey_sn='".$ukey_sn."' and name!='$name' and isshow>0", 1, "c") > 0) {
		exit_html("该uKey编号“{$ukey_sn}”已经被别人使用，绑定不成功。");
	}

	$uinfo = array();
	ob_start();
	// 此处绝对不能输出任何sql语句查询错误，否则极度危险。
	$uinfo = @$db->query("select * from sys_admin where name='".$name."' and pass='".gen_pass($pass)."' limit 1", 1);
	ob_end_clean();
	$uid = intval($uinfo["id"]);
	if (!$uinfo || $uid == 0) {
		exit_html("用户名或密码不正确，请返回重新输入。");
	}

	if (strlen($uinfo["ukey_sn"]) == 16) {
		exit_html("对不起，该账号可能在您绑定期间已被其他人绑定过了，请返回登陆页面登陆测试。");
	}

	// 执行绑定操作：
	if ($db->query("update sys_admin set use_ukey=1, ukey_sn='".$ukey_sn."', ukey_no='登录绑定' where id=$uid limit 1")) {
		msg_box("uKey绑定成功，请重新登陆", "login.php", 3);
	}
	exit;
}

?>
<html>
<head>
<title>自助绑定uKey</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script type="text/javascript">
function write_cur_ukey_sn() {
	et99 = byid("ET99");
	if (et99) {
		window.onerror = function() {
			//alert("读取ET99设备出现错误。");
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
		alert("请获取uKey编号之后再提交！");
		return false;
	}
	if (f.username.value == '') {
		alert("请填写用户姓名！                ");
		return false;
	}
	if (f.password.value == '') {
		alert("请输入您的登陆密码！            ");
		return false;
	}
	if (!confirm("提交后不能取消，也不能自行修改绑定。确定要提交吗？")) {
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
				请先插入uKey，等待系统自动获取到uKey编号之后再填写用户名和密码。（手工刷新此页面也可以）<br><br>
				如果还没有uKey，请到人事部领取。然后绑定。<br><br>
			</td>
		</tr>
		<tr>
			<td height="30" align="left">uKey编号：</td>
			<td><span id="ukey_sn_show" style="font-family:Tahoma; font-weight:bold;">(未获取)</span></td>
		</tr>
		<tr>
			<td width="39%" height="30" align="left">用户姓名：</td>
			<td width="61%"><input name="username" id="username" type="text" class="input" size="20" style="width:120px" value=""></td>
		</tr>
		<tr>
			<td height="30" align="left">登录密码：</td>
			<td><input name="password" id="password" type="password" class="input" size="20" style="width:120px"></td>
		</tr>
		<tr>
			<td colspan="2">
				<br />
				<br />
				注意：<br />
				　1、若不能获取，请点击 <a href="/ukey/" title="在线安装驱动程序" target="_blank">[这里]</a> 在线安装驱动程序，完成后，本页面会自动更新并读取uKey的信息。<br />
				　2、uKey提交后，下次登录必须使用uKey。不能重复提交，请确认插入的uKey没有和别人的混淆。<br />
				<br />
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" class="submit" value="提交绑定"></td>
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