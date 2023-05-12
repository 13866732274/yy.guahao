<?php
// --------------------------------------------------------
// - 功能说明 : 转移数据
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-12-19
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$hid;

if (empty($hid)) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}
$hname = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

if ($_POST) {
	$fromname = $_POST["fromname"];
	$touid = intval($_POST["touid"]);
	if ($fromname != '' && $touid > 0) {
		$toname = $db->query("select realname from sys_admin where id=$touid limit 1", 1, "realname");
		if ($db->query("update $table set uid={$touid}, author='{$toname}' where binary author='{$fromname}'")) {
			msg_box("处理成功！", "patient_move.php", 1);
		}
	}
}


$title = '资料转移工具';

$kefu_23_list = $db->query("select author,count(author) as acount from $table where author!='' group by author order by binary author");
foreach ($kefu_23_list as $k => $li) {
	$kefu_23_list[$k]["author_name"] = $li["author"]." (".$li["acount"].")";
}

// 转移到的新用户:
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
		alert("请选择“原名字”！"); oForm.fromname.focus(); return false;
	}
	if (oForm.touid.value == "") {
		alert("请选择“新名字”！"); oForm.touid.focus(); return false;
	}
	return true;
}
</script>
</head>

<body>
<!-- 头部 begin -->
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips"><?php echo $hname." - ".$title; ?></nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">刷新</button></td>
	</tr>
</table>
<!-- 头部 end -->

<div class="space"></div>

<div class="description">
	<div class="d_title">工具说明：</div>
	<div class="d_item">此工具用途为“病人过户”：病人原本是张三添加的，如果现在需将其修改为李四添加的，此工具即可解决。注意，如果新老人员部门不同，过户后还需使用“部门设置工具”设置病人对应的部门。</div>
</div>

<div class="space"></div>

<form name="mainform" action="?action=move" method="POST" onsubmit="return Check()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">转移设置</td>
	</tr>
	<tr>
		<td class="left red"><font color="red">*</font> 原名字：</td>
		<td class="right">
			<select name="fromname" class="combo" style="width:200px;">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($kefu_23_list, 'author', 'author_name', ''); ?>
			</select>
			<span class="intro">括号内数字为其添加的病人数量</span>
		</td>
	</tr>
	<tr>
		<td class="left red"><font color="red">*</font> 新名字：</td>
		<td class="right">
			<select name="touid" class="combo" style="width:200px;">
				<option value='' style="color:gray">--请选择--</option>
				<?php echo list_option($new_user_id_name, '_key_', '_value_', ''); ?>
			</select>
			<span class="intro">括号内数字为UID</span>
		</td>
	</tr>
</table>

<div class="button_line"><input type="submit" class="submit" value="提交"></div>

</form>
</body>
</html>