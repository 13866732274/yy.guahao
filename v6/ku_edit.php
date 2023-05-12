<?php
// --------------------------------------------------------
// - 功能说明 : 病人资料库
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2013-7-11
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";

$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("参数错误，请刷新页面后重试...");
}
$mode = "edit";


// 删除提醒 @ 2014-6-11
if ($_GET["op"] == "delete_remind") {
	$remind_id = intval($_GET["id"]);
	$db->query("delete from ku_remind where id=$remind_id and uid=$uid limit 1");
	echo '<script> alert("提醒删除成功~          "); history.back(); </script>';
	exit;
}


if ($_POST) {
	$old = $db->query("select * from $table where id=$id limit 1", 1);

	if (trim($_POST["remind_date"]) != '') {
		$r_date = date("Ymd", strtotime($_POST["remind_date"]));
		$p_name = $old["name"];

		// 查询是否有添加过:
		$remind_line = $db->query("select * from ku_remind where ku_id=$id and uid=$uid limit 1", 1);
		if ($remind_line["id"] > 0) {
			// 如果日期变更，则更新日期:
			if ($remind_line["remind_date"] != $r_date) {
				$r_id = $remind_line["id"];
				$db->query("update ku_remind set remind_date='$r_date' where id=$r_id limit 1");
			}
		} else {
			// 没有记录，则添加
			$time = time();
			$db->query("insert into ku_remind set remind_date='$r_date', ku_id=$id, patient_name='$p_name', uid=$uid, u_name='$realname', addtime=$time");
		}
	}

	$r = array();

	$r["name"] = $_POST["name"];
	$r["mobile"] = $_POST["mobile"];
	$r["area"] = $_POST["area"];
	$r["qq"] = $_POST["qq"];
	$r["order_qq"] = $_POST["order_qq"];
	$r["weixin"] = $_POST["weixin"];
	$r["order_weixin"] = $_POST["order_weixin"];
	$r["disease_id"] = $did = intval($_POST["disease_id"]);
	if ($did > 0) {
		$dname = $db->query("select name from disease where id=$did limit 1", 1, "name");
		if (substr($dname, 0, 2) != "【" && (substr_count($dname, "(") > 0 || substr_count($dname, "（") > 0 || substr_count($dname, "【") > 0)) {
			$dname = str_replace("（", "(", $dname);
			$dname = str_replace("【", "(", $dname);
			list($name2, $tmp) = explode("(", $dname, 2);
			if (trim($name2) != '') $dname = trim($name2);
		}
		$r["disease_name"] = $dname;
	}

	$r["zx_content"] = $_POST["zx_content"];
	$r["talk_content"] = $_POST["talk_content"];
	$r["swt_id"] = trim($_POST["swt_id"]);
	$r["updatetime"] = time();

	if ($_POST["is_add_haoyou_submit"] > 0) {
		$r["wx_is_add"] = $_POST["is_add_haoyou"] ? 1 : 0;
		if ($_POST["is_add_haoyou"] > 0) {
			$_POST["weixin_submit"] = 0;
		}
	}

	if ($_POST["weixin_submit"] > 0) {
		$r["to_weixin"] = intval($_POST["to_weixin"]) ? 1 : 0;
		if ($r["to_weixin"] > 0) {
			$r["wx_uid"] = $wx_uid = intval($_POST["weixin_kefu_id"]);
			if ($wx_uid > 0) {
				$r["wx_uname"] = $db->query("select realname from sys_admin where id=$wx_uid limit 1", 1, "realname");
			} else {
				$r["wx_uname"] = "";
			}
		} else {
			$r["wx_uid"] = 0;
			$r["wx_uname"] = "";
			//$r["wx_is_add"] = 0;
		}
	}

	// 记录修改日志 @ 2016-09-21
	$log_fields_name = array(
      "name" => "姓名",
      "mobile" => "手机号",
		"qq" => "患者QQ",
		"order_qq" => "我方QQ",
		"weixin" => "患者微信",
		"order_weixin" => "我方微信",
		"zx_content" => "咨询内容");
	$log_fields = array_keys($log_fields_name);
	$log = array();
	foreach ($log_fields as $f) {
		if ($r[$f] != $old[$f]) {
			$log[] = "将".$log_fields_name[$f]."由“".$old[$f]."”修改为“".$r[$f]."”";
		}
	}
	if (count($log) > 0) {
		$log_str = date("Y-m-d H:i ").$realname." ".implode("，", $log);
		$r["edit_log"] = trim(rtrim($old["edit_log"])."\r\n".$log_str);
        $userip = get_ip();
  user_op_log($r["edit_log"], "", $tmp_uinfo["id"], $tmp_uinfo["realname"]);
	}
  

	// end 记录修改日志

	$sqldata = $db->sqljoin($r);

	$sql = "update $table set $sqldata where id='$id' limit 1";

	if ($db->query($sql)) {
		echo '<script> parent.msg_box("提交成功", 2); </script>';
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "提交失败，请稍后再试！";
	}



	exit;
}

if ($mode == "edit") {
	$line = $db->query("select * from $table where id='$id' limit 1", 1);
}

// 提醒记录：
$remind_line = $db->query("select * from ku_remind where ku_id=$id and uid=$uid limit 1", 1);
$remind_id = $remind_line["id"];
$remind_date = $remind_id > 0 ? int_date_to_date($remind_line["remind_date"]) : "";


$title = $mode == "edit" ? "修改" : "新增";
?>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
#rec_part, #rec_user {margin-top:6px; }
.rec_user_b {width:100px; float:left; }
.rec_group_part {clear:both; margin:10px 0 5px 0; font-weight:bold; }
.z {width:100px; text-align:right; display:inline-block; }
</style>

<script language="javascript">
function check_submit(o) {
	if (o.qq.value != '' && o.order_qq.value == "") {
		alert("当填写患者QQ时，“我方QQ”也必须填写。"); return false;
	}
	if (o.weixin.value != '' && o.order_weixin.value == "") {
		alert("当填写患者微信时，“我方微信”也必须填写。"); return false;
	}
	if (o.zx_content.value == "") {
		alert("请输入患者咨询内容！"); o.zx_content.focus(); return false;
	}
	return true;
}
function update_check_color(o) {
	o.parentNode.getElementsByTagName("label")[0].style.color = o.checked ? "blue" : "";
}
</script>

</head>

<body>

<form name="mainform" action="" method="POST" onsubmit="return check_submit(this)">
<table width="100%" class="edit" style="border:0; margin-top:10px;">
	<tr>
		<td class="left">姓名：</td>
		<td class="right"><input name="name" value="<?php echo $line["name"]; ?>" class="input" style="width:200px;"><label class="z">手机号：</label><input name="mobile" value="<?php echo $line["mobile"]; ?>" class="input" style="width:150px;">
		</td>
	</tr>
	<tr>
		<td class="left">疾病：</td>
		<td class="right">
<?php
	$disease_arr = $db->query("select id, name from disease where hospital_id=".$line["hid"]." and isshow=1 order by sort desc, name asc", "id", "name");
?>
			<select name="disease_id" class="combo" style="width:200px">
				<option value="" style="color:gray">请选择疾病</option>
				<?php echo list_option($disease_arr, "_key_", "_value_", $line["disease_id"]); ?>
			</select><label class="z">所在地：</label><input id="area" name="area" value="<?php echo $line["area"]; ?>" class="input" style="width:100px">&nbsp;
			<a href="javascript:;" onclick="area_set(this);">本地</a>
			<a href="javascript:;" onclick="area_set(this);">江浙</a>
			<a href="javascript:;" onclick="area_set(this);">其他</a>
			<select name="type" id="userType" onclick="select_area_set(this);">
<option value="贵阳">贵阳</option>
<option value="安顺">安顺</option>
<option value="毕节">毕节</option>
<option value="遵义">遵义</option>
<option value="六盘水">六盘水</option>
<option value="黔南">黔南</option>
<option value="黔西南">黔西南</option>
<option value="黔东南">黔东南</option>
<option value="铜仁">铜仁</option>
</select>
			<script type="text/javascript">
			function area_set(obj) {
				byid("area").value = obj.innerHTML;
			}
			function select_area_set(obj) {
				var myType = document.getElementById("userType");//获取select对象
var index = myType.selectedIndex; //获取选项中的索引，selectIndex表示的是当前所选中的index
byid("area").value = myType.options[index].value;//获取选项中options的value值
			}
			</script>
		</td>
	</tr>
	<tr>
		<td class="left">患者QQ：</td>
		<td class="right"><input name="qq" value="<?php echo $line["qq"]; ?>" class="input" style="width:200px"><label class="z">我方QQ：</label><input name="order_qq" value="<?php echo $line["order_qq"]; ?>" class="input" style="width:150px"></td>
	</tr>
	<tr>
		<td class="left">患者微信：</td>
		<td class="right"><input name="weixin" value="<?php echo $line["weixin"]; ?>" class="input" style="width:200px;"><label class="z">我方微信：</label><input name="order_weixin" value="<?php echo $line["order_weixin"]; ?>" class="input" style="width:150px"></td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 咨询内容：</td>
		<td class="right">
			<textarea name="zx_content" style="width:80%; height:60px; vertical-align:middle;" class="input"><?php echo $line["zx_content"]; ?></textarea>
		</td>
	</tr>

	<tr>
		<td class="left">聊天记录：</td>
		<td class="right">
			<textarea name="talk_content" style="width:80%; height:100px; vertical-align:middle;" class="input"><?php echo $line["talk_content"]; ?></textarea>
		</td>
	</tr>

	<tr>
		<td class="left">商务通永久身份：</td>
		<td class="right">
			<input name="swt_id" value="<?php echo $line["swt_id"]; ?>" class="input" style="width:200px">
		</td>
	</tr>

	<tr>
		<td class="left">提醒日期：</td>
		<td class="right">
			<input name="remind_date" value="<?php echo $remind_date; ?>" class="input" style="width:150px" id="remind_date"> <img src="image/calendar.gif" id="remind_date" onClick="picker({el:'remind_date',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择日期">
			<?php if ($remind_id > 0) { ?><a href="?op=delete_remind&id=<?php echo $remind_id; ?>">删除提醒</a><?php } else { ?><span class="intro">设置下次提醒日期，为空则不提醒</span><?php } ?>
		</td>
	</tr>

<?php if ($line["to_weixin"] == 0 && ($line["uid"] == $uid || $is_super_admin)) { ?>
	<tr>
		<td class="left">微信好友：</td>
		<td class="right">
			<input type="hidden" name="is_add_haoyou_submit" value="1">
			<input type="checkbox" name="is_add_haoyou" value="1" id="is_add_haoyou" onchange="show_hide_to_weixin()" onclick="show_hide_to_weixin()" <?php if ($line["wx_is_add"]) echo " checked"; ?>><label for="is_add_haoyou">我已加上好友</label>
		</td>
	</tr>
	<script type="text/javascript">
	function show_hide_to_weixin() {
		byid("to_weixin_tr").style.display = byid("is_add_haoyou").checked ? "none" : "";
	}
	</script>
<?php } ?>

<?php if ($is_super_admin || $line["uid"] == $uid) { ?>
	<tr id="to_weixin_tr" style="display:<?php echo $line["wx_is_add"] ? "none" : ""; ?>">
		<td class="left">微信对接</td>
		<td class="right">
			<input type="hidden" name="weixin_submit" value="1">
			<input type="checkbox" name="to_weixin" value="1" id="to_weixin" onclick="show_hide_weixin()"><label for="to_weixin">转微信组</label>
			<span id="weixin_area" style="display:none; margin-left:20px;">
				<span id="weixin_kefu_select">
<?php
	$_hid = $line["hid"];
	$part_id = $uinfo["part_id"];
	if (!in_array($part_id, array(2,3))) $part_id = 2;
	$wx_arr = $db->query("select id, concat(realname,if(online>0,' [在线]','')) as realname from sys_admin where part_id=$part_id and concat(',',hospitals,',') like '%,{$_hid},%' and character_id in (50,51,52) order by realname asc", "id", "realname");
?>
					<select name="weixin_kefu_id" class="combo">
						<option value="" style="color:gray">请选择微信对接人</option>
						<?php echo list_option($wx_arr, "_key_", "_value_", $line["wx_uid"]); ?>
					</select>
				</span>
				<span>　如果不选，则整个微信组都可以看到</span>
			</span>
		</td>
	</tr>
	<script type="text/javascript">
	function show_hide_weixin() {
		var is_check = byid("to_weixin").checked ? 1 : 0;
		byid("weixin_area").style.display = is_check ? "" : "none";
	}
	</script>

	<?php if ($line["to_weixin"]) { ?>
	<script type="text/javascript">
	byid("to_weixin").checked = true;
	byid("weixin_area").style.display = "";
	</script>
	<?php } ?>
<?php } ?>

</table>

<input type="hidden" name="id" value="<?php echo $id; ?>">

<div class="button_line"><input type="submit" class="submit" value="提交资料"></div>
</form>
</body>
</html>