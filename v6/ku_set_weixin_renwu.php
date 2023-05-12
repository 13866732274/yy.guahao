<?php
// --------------------------------------------------------
// - 功能说明 : 资料库报表 - 给微信组分配任务
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-10-24
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
$table = "ku_list";

if ($op == "fenpei") {

	// 记录条件，方便下次分配
	$_SESSION["wx_fenpei_btime"] = $_GET["btime"];
	$_SESSION["wx_fenpei_etime"] = $_GET["etime"];
	$_SESSION["wx_fenpei_num"] = $_GET["fenpei_num"];

	$tbegin = strtotime($_GET["btime"]);
	$tend = strtotime($_GET["etime"]." 23:59:59");
	$fenpei_num = intval($_GET["fenpei_num"]);
	$wx_uid = intval($_GET["wx_uid"]);
	if ($fenpei_num > 0 && $wx_uid > 0) {
		$_uinfo = $db->query("select * from sys_admin where id=$wx_uid limit 1", 1);
		$wx_uname = $_uinfo["realname"];
		$wx_hospitals = $_uinfo["hospitals"];
		if ($wx_hospitals == '') {
			exit('该微信组员未被分配科室，请前往账号设置调整后再分配。');
		}
		$wx_partid = $_uinfo["part_id"];
		if (!in_array($wx_partid, array(2,3))) {
			exit('该微信组员部门设置不正确，需为网络客服或电话客服。请前往账号设置调整后再分配。');
		}

		$db->query("update $table set to_weixin=1, wx_is_fenpei=1, wx_uid=$wx_uid, wx_uname='$wx_uname' where hid in ($wx_hospitals) and part_id=$wx_partid and addtime>=$tbegin and addtime<=$tend and to_weixin=0 and wx_is_fenpei=0 and wx_is_add=0 and is_yuyue=0 and is_come=0 order by id desc limit $fenpei_num");
		$nums = mysql_affected_rows();

		echo '<script> alert("已为 ['.$wx_uname.'] 已分配了 ['.$nums.'] 条资料。"); self.location = "?"; </script>';
	} else {
		echo '参数错误';
	}
	exit;
}

if ($op == "get_info") {
	header('Content-type: text/javascript');
	$wx_uid = intval($_GET["wx_uid"]);

	$wx_uname = $db->query("select realname from sys_admin where id=$wx_uid limit 1", 1, "realname");

	// 查询分配情况:
	$count1 = $db->query("select count(*) as c from $table where to_weixin>0 and wx_is_fenpei>0 and wx_uid=$wx_uid", 1, "c");

	// 已加好友数量:
	$count2 = $db->query("select count(*) as c from $table where to_weixin>0 and wx_is_fenpei>0 and wx_uid=$wx_uid and wx_is_add>0", 1, "c");

	$count3 = $count1 - $count2;

	$str = "[".$wx_uname."] 总分配患者数：".$count1."　已加好友：".$count2."　未加好友：".$count3;
	if (count($count3) >= 0) {
		$str .= '　<a href="javascript:;" onclick="back_to_system('.$wx_uid.');" title="将已分配未加好友的患者归还系统，可以重新分配给其他人">[归还系统]</a>';
	}
	echo 'byid("wx_info").innerHTML = "'.addslashes($str).'"; ';

	exit;
}

if ($op == "back_to_system") {
	header('Content-type: text/javascript');
	$wx_uid = intval($_GET["wx_uid"]);

	$db->query("update $table set to_weixin=0, wx_is_fenpei=0, wx_uid=0, wx_uname='' where to_weixin>0 and wx_is_fenpei>0 and wx_uid=$wx_uid and wx_is_add=0");
	echo 'alert("已成功将未加好友的患者归还系统。"); get_info('.$wx_uid.'); ';

	exit;
}

$weixin_zixun_arr = $db->query("select id, name from sys_admin where character_id in (50, 51, 52) order by character_id asc, name asc", "id", "name");


?>
<html>
<head>
<title>给微信组分配任务</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<script type="text/javascript">
function get_info(wx_uid) {
	load_js("?op=get_info&wx_uid="+wx_uid+"", "load_js");
}
function back_to_system(wx_uid) {
	if (confirm("将已分配未加好友的患者归还系统，可以重新分配给其他人，是否确定处理？")) {
		load_js("?op=back_to_system&wx_uid="+wx_uid+"", "load_js");
	}
}
function check_data(oForm) {
	if (oForm.wx_uid.value == '') {
		alert("“微信人员”必须选择。"); return false;
	}
	if (oForm.btime.value == '' || oForm.etime.value == '') {
		alert("“分配时间段”为必填项，请填写后再提交。"); return false;
	}
	if (oForm.fenpei_num.value == '' || oForm.fenpei_num.value == '0') {
		alert("“分配数量”为必填项，请填写后再提交。"); return false;
	}
	return true;
}
</script>
</head>

<body style="padding:10px 20px;">

<style type="text/css">
.head_tips {border:2px solid #ffa87d; background:#ffe4d5; padding:5px 10px; border-radius:3px;  }
</style>
<div class="head_tips">分配说明：只会分配未预约、未到院、未加好友的数据。请看清设置慎重操作。</div>

<form method="GET" action="" onsubmit="return check_data(this)">
<table width="100%" class="new_edit" style="margin-top:20px;">
	<tr>
		<td class="left" style="width:20%;">* 微信人员：</td>
		<td class="right" style="width:80%;">
			<select name="wx_uid" class="combo" onchange="get_info(this.value)">
				<option value="">　　　　　　</option>
				<?php echo list_option($weixin_zixun_arr, "_key_", "_value_"); ?>
			</select>
			<span id="wx_info" style="margin-left:10px;"></span>
		</td>
	</tr>

	<tr>
		<td class="left">* 分配时间段：</td>
		<td class="right">
			<input name="btime" id="begin_time" class="input" style="width:100px" value="<?php echo $_SESSION["wx_fenpei_btime"]; ?>" onclick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})"> ~ <input name="etime" id="end_time" class="input" style="width:100px" value="<?php echo $_SESSION["wx_fenpei_etime"]; ?>" onclick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})">　(优先分配日期较新的患者)
		</td>
	</tr>

	<tr>
		<td class="left">* 分配条数：</td>
		<td class="right">
			<input name="fenpei_num" class="input" style="width:100px" value="<?php echo $_SESSION["wx_fenpei_num"]; ?>"> 条
		</td>
	</tr>
</table>

<div class="button_line" style="margin-top:20px;">
	<input type="submit" class="submit" value="开始分配">
</div>
<input type="hidden" name="op" value="fenpei">

</form>



</body>
</html>