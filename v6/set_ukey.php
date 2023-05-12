<?php
/*
// 说明: 自助绑定uKey
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-07-07
*/
require "lib/set_env.php";

if ($uinfo["ukey_sn"] != '') {
	exit_html("对不起，您的账号已经绑定过uKey序列号：".substr($uinfo["ukey_sn"], 0, 4)."********".substr($uinfo["ukey_sn"], -4, 4));
}

if ($_POST) {
	$ukey_sn = $_POST["ukey_sn"];
	if (strlen($ukey_sn) == 16) {
		if ($db->query("select count(*) as c from sys_admin where ukey_sn='".$ukey_sn."' and isshow>0", 1, "c") > 0) {
			exit_html("该uKey编号已经被别人使用，绑定不成功。");
		}
		$db->query("update sys_admin set ukey_sn='".$ukey_sn."', ukey_no='自助绑定' where name='$username' limit 1");
		msg_box("uKey绑定成功！", "main.php", 3);
		exit;
	} else {
		exit_html("uKey编号长度不正确，必须是16位。");
	}
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
	if (!confirm("提交后不能取消，也不能自行修改绑定。确定要提交吗？")) {
		return false;
	}
	return true;
}


</script>
</head>

<body>

<form method="POST" onsubmit="return check_data(this)">
	<div style="padding:20px 0 0 40px;">
		<b>要绑定的uKey编号</b>：<span id="ukey_sn_show" style="padding:0 10px; font-family:Tahoma; font-weight:bold;">(未获取)</span><br />
		<input type="hidden" name="ukey_sn" id="ukey_sn" value="">
		<br />
		<br />
		注意：<br />
		　1、点击上面的按钮获取uKey编号。<br />
		　2、若不能获取，请点击 <a href="/ukey/" title="在线安装驱动程序" target="_blank">[这里]</a> 在线安装驱动程序，完成后，本页面会自动更新并读取uKey。<br />
		　3、uKey提交后，下次登录必须使用uKey。不能重复提交，请确认插入的uKey没有和别人的混淆。<br />
		<br />
		<br />
		<input type="submit" class="submit" value="提交">
	</div>
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