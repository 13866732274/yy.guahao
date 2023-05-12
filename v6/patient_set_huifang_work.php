<?php
// --------------------------------------------------------
// - 功能说明 : 设置回访任务
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-12-17
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

if ($_GET["btime"] == "") $_GET["btime"] = date("Y-m-d", strtotime("-1 days"));
if ($_GET["etime"] == "") $_GET["etime"] = date("Y-m-d");

$huifang_kefu_arr = $db->query("select id, realname from sys_admin where concat(',', hospitals, ',') like '%,{$hid},%' and concat(',',guahao_config,',') like '%,huifang,%' order by part_id asc, realname asc", "id", "realname");


$huifang_last_uid = 0;
function _wee_get_next_uid($uid_arr) {
	if (count($uid_arr) == 0) {
		exit("分配客服未选择");
	}
	global $huifang_last_uid;

	$last_uid = $uid_arr[count($uid_arr) - 1];

	if ($huifang_last_uid == 0 || $huifang_last_uid == $last_uid) {
		$huifang_last_uid = $uid_arr[0];
	} else {
		$k = array_search($huifang_last_uid, $uid_arr);
		$huifang_last_uid = $uid_arr[$k+1];
	}
	return $huifang_last_uid;
}


if ($_POST) {

	$cond = array();

	$is_preview = $_POST["preview_mode"] ? 1 : 0;
	$preview_string = $is_preview ? '<font color=red>[预览] </font>' : "";

	if ($_POST["btime"] == '' || $_POST["etime"] == '') {
		exit("日期选择不正确");
	}
	$b = strtotime($_POST["btime"]." 0:00:00");
	$e = strtotime($_POST["etime"]." 23:59:59");
	$cond[] = "order_date>=$b and order_date<=$e";

	if (count($_POST["part_set"]) == 0) {
		exit("部门选择错误，至少要选择一个。");
	}
	$cond[] = "part_id!=4";
	if (!in_array("-1", $_POST["part_set"])) {
		$cond[] = "part_id in (".implode(",", $_POST["part_set"]).")";
	}

	if (in_array("not_come", $_POST["status_set"]) && in_array("come", $_POST["status_set"])) {
		// all
	} else if (in_array("not_come", $_POST["status_set"])) {
		$cond[] = "status!=1";
	} else if (in_array("come", $_POST["status_set"])) {
		$cond[] = "status=1";
	}

	$cond_str = implode(" and ", $cond);
	$list = $db->query("select id, name, order_date, uid, author from $table where $cond_str order by id desc");

	$uid_name_arr = array();
	if (count($_POST["huifang_uids"]) > 0) {
		$huifang_uids = implode(",", $_POST["huifang_uids"]);
		$uid_name_arr = $db->query("select id, realname from sys_admin where id in (".$huifang_uids.")", "id", "realname");
	}

	$res = array();
	foreach ($list as $li) {

		if ($_POST["huifang_people"] == "self") {
			$remind_uid = $li["uid"];
			$remind_uname = $li["author"];
		} else {
			$remind_uid = _wee_get_next_uid($_POST["huifang_uids"]);
			$remind_uname = $uid_name_arr[$remind_uid];
		}

		if ($_POST["huifang_date_type"] == "delay") {
			$remind_time = strtotime("+".$_POST["huifang_delay_days"]." days", $li["order_date"]);
			if ($remind_time <= time()) {
				$remind_time = time();
			}
		} else {
			if ($_POST["huifang_date"] == "") {
				exit("请指定回访日期");
			}
			$remind_time = strtotime($_POST["huifang_date"]);
		}
		$remind_date = date("Ymd", $remind_time);

		$remind = array();
		$remind["hid"] = $hid;
		$remind["patient_id"] = $li["id"];
		$remind["patient_name"] = $li["name"];
		$remind["remind_date"] = $remind_date;
		$remind["uid"] = $remind_uid;
		$remind["u_name"] = $remind_uname;
		$remind["flag"] = 5;
		$remind["addtime"] = time();
		$remind["add_uid"] = $uid;
		$remind["add_uname"] = $realname;

		if (!$is_preview) {
			$db->insert("patient_remind", $remind);
		}

		$res[] = $preview_string."患者 ".$li["name"]." 预约日期 ".date("Y-m-d", $li["order_date"])." 由【".$remind_uname."】在【".date("Y-m-d", $remind_time)."】回访";
	}

	if (count($res) == 0) {
		echo "没有查询到符合条件的患者，请重新设置条件。";
	} else {
		echo '<title>设置回访任务</title>';
		echo '<style type="text/css"> * {font-size:13px; font-family:"微软雅黑"; }</style>';
		if ($is_preview) {
			echo "<font color=red>当前为预览模式（如需真实分配回访任务，请返回重新设置条件，并不要勾选预览模式）：</font><br><br>";
		} else {
			echo "回访提醒设置成功，以下为结果：<br><br>";
		}
		echo implode("<br>", $res);
	}
	exit;
}



// page begin ----------------------------------------------------
?>
<html>
<head>
<title>设置回访任务</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>

</style>
<script language="javascript">

</script>
</head>

<body style="padding:20px; ">
<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" style="margin-top:10px;" class="new_edit">
	<tr>
		<td class="left" width="70">日期范围：</td>
		<td class="right">
			<input name="btime" id="btime" class="input" style="width:80px" value="<?php echo $_GET["btime"]; ?>" onclick="picker({el:'btime',dateFmt:'yyyy-MM-dd'})"> ~ <input name="etime" id="etime" class="input" style="width:80px" value="<?php echo $_GET["etime"]; ?>" onclick="picker({el:'etime',dateFmt:'yyyy-MM-dd'})">　（按预约时间）
		</td>
	</tr>
	<tr>
		<td class="left" width="70">部门：</td>
		<td class="right">
			<input type="checkbox" name="part_set[]" value="-1" id="chk_01" checked><label for="chk_01">所有</label>&nbsp;&nbsp;
			<input type="checkbox" name="part_set[]" value="2" id="chk_02"><label for="chk_02">网络</label>&nbsp;&nbsp;
			<input type="checkbox" name="part_set[]" value="3" id="chk_03"><label for="chk_03">电话</label>
		</td>
	</tr>
	<tr>
		<td class="left" width="70">是否到院：</td>
		<td class="right">
			<input type="checkbox" name="status_set[]" value="not_come" id="chk_1" checked><label for="chk_1">未到</label>&nbsp;&nbsp;
			<input type="checkbox" name="status_set[]" value="come" id="chk_2"><label for="chk_2">已到</label>
		</td>
	</tr>

	<tr>
		<td class="left" width="70">回访人：</td>
		<td class="right">
			<input type="radio" name="huifang_people" value="self" id="chk_11" onchange="show_hide_huifang_uids_div(this.value);" checked><label for="chk_11">本人回访</label>&nbsp;&nbsp;
			<input type="radio" name="huifang_people" value="uids" id="chk_12" onchange="show_hide_huifang_uids_div(this.value);"><label for="chk_12">平均分配给以下所选人员</label>
			<div id="huifang_uids_div" style="padding:10px 10px 10px 40px; display:none; ">
<?php foreach ($huifang_kefu_arr as $_uid => $_uname) { ?>
				<nobr style="margin-right:10px;"><input type="checkbox" name="huifang_uids[]" value="<?php echo $_uid; ?>" id="u_<?php echo $_uid; ?>"><label for="u_<?php echo $_uid; ?>"><?php echo $_uname; ?></label></nobr>
<?php } ?>
			</div>
			<script type="text/javascript">
			function show_hide_huifang_uids_div(v) {
				byid("huifang_uids_div").style.display = (v == "uids" ? "block" : "none");
			}
			</script>
		</td>
	</tr>

	<tr>
		<td class="left" width="70">回访时间：</td>
		<td class="right">
			<input type="radio" name="huifang_date_type" value="delay" id="chk_21" checked><label for="chk_21">以预约日期顺延 <input name="huifang_delay_days" class="input" style="width:24px" value="3"> 天回访</label>&nbsp;&nbsp;
			<input type="radio" name="huifang_date_type" value="date" id="chk_22"><label for="chk_22">指定日期回访：</label> <input name="huifang_date" id="huifang_date" class="input" style="width:80px" value="" onclick="picker({el:'huifang_date',dateFmt:'yyyy-MM-dd'})">
		</td>
	</tr>

	<tr>
		<td class="left" width="70"></td>
		<td class="right">
			<input type="checkbox" name="preview_mode" value="1" id="chk_30"><label for="chk_30">预览模式</label>　　(勾选后，只显示分配结果，不会实际分配回访任务)
		</td>
	</tr>

</table>

<div class="button_line">
	<input type="submit" class="submit" value="确定">
</div>
</form>
</body>
</html>