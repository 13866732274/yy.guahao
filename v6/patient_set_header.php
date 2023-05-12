<?php
/*
// 说明: 设置首页显示数据模块
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-09-10
*/
require "lib/set_env.php";

$op = $_REQUEST["op"];

if ($op == "save") {
	$hs = trim(@implode("\r\n", $_POST["patient_headers"]));
	if ($uid > 0) {
		$db->query("update sys_admin set patient_headers='".$hs."' where id='".$uid."' limit 1");
	} else {
		$_SESSION["patient_headers"] = $hs;
	}

	//user_op_log("设置病人列表显示字段");

	echo '<script type="text/javascript">'."\r\n";
	echo 'parent.load_box(0);'."\r\n";
	echo 'parent.msg_box("设置保存成功")'."\r\n";
	echo 'parent.update_content()'."\r\n";
	echo '</script>'."\r\n";
	exit;
}

if ($debug_mode) {
	$show_headers_str = $_SESSION["patient_headers"];
	$config["show_xiaofei"] = 2;
}
$show_headers_str = $uinfo["patient_headers"];

$show_headers_str = str_replace("\r", "", $show_headers_str);
$show_headers = explode("\n", $show_headers_str);

$all_headers = array();
$all_headers["name"] = "姓名";
$all_headers["sex"] = "性别";
$all_headers["age"] = "年龄";
$all_headers["tel"] = "电话";
$all_headers["zhuanjia_num"] = "专家号";
$all_headers["card_id"] = "身份证号";
$all_headers["content"] = "咨询内容|备注|回访";
$all_headers["order_date"] = "预约时间";
//$all_headers["remain_time"] = "剩余天数";
$all_headers["disease_id"] = "病患类型";
$all_headers["media_from"] = "媒体来源";
$all_headers["engine"] = "搜索引擎";
$all_headers["from_site"] = "网站来源";
$all_headers["key_word"] = "关键词";
$all_headers["youhuazu"] = "优化组";
$all_headers["part_id"] = "部门";
$all_headers["depart"] = "科室";
$all_headers["order_soft"] = "软件";
//$all_headers["shichang"] = "市场";
$all_headers["account"] = "账号";
$all_headers["author"] = "客服";
if ($config["show_xiaofei"] > 0 || $debug_mode) {
	$all_headers["xiaofei"] = "消费";
}
if ($config["show_guiji"] > 0 || $debug_mode) {
	$all_headers["guiji"] = "轨迹";
}
$all_headers["status"] = "状态";
$all_headers["doctor"] = "医生";
$all_headers["yibao"] = "医保";
$all_headers["suozaidi"] = "现居住地";
$all_headers["tuiguangren"] = "推广人";
$all_headers["addtime"] = "添加时间";


if (count($show_headers) <= 1) {
	if ($debug_mode) {
		$show_headers = explode(" ", "name tel content order_date disease_id media_from part_id doctor addtime status author op");
	}
	if (in_array($uinfo["part_id"], array(1,9,13))) { // 这些是管理人员
		$show_headers = explode(" ", "name tel content order_date disease_id media_from part_id doctor guiji addtime status yibao author op");
	}
	if (in_array($uinfo["part_id"], array(2))) { //网络客服
		$show_headers = explode(" ", "name sex tel content order_date disease_id media_from depart addtime status doctor xiaofei author op");
	}
	if (in_array($uinfo["part_id"], array(3))) { //电话客服
		$show_headers = explode(" ", "name sex tel content order_date disease_id media_from depart addtime status doctor xiaofei huifang_time author op");
	}
	if (in_array($uinfo["part_id"], array(4))) { //导医
		$show_headers = explode(" ", "name sex tel content order_date disease_id doctor depart addtime author status yibao op");
	}
	if (in_array($uinfo["part_id"], array(12))) { //电话回访
		$show_headers = explode(" ", "name sex tel content order_date disease_id doctor huifang_time depart addtime author status op");
	}
	if (in_array($uinfo["part_id"], array(14, 15))) { //现场医生
		$show_headers = explode(" ", "name sex tel content order_date disease_id doctor huifang_time depart addtime author status op");
	}
	if (count($show_headers) < 1) { //任何其他情况
		$show_headers = array_keys($all_headers);
	}
}


// 检查有无搜索来源的权限:
if (!$debug_mode) {
	if ($config["show_engine"] != 1) {
		unset($all_headers["engine"]);
		unset($all_headers["from_site"]);
		unset($all_headers["key_word"]);
		unset($all_headers["youhuazu"]);
	}
}


header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
?>
<html>
<head>
<title>设置表头字段</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.aline {margin-left:20px; float:left; width:160px; }
.submit_line {text-align:center; padding:10px; }
legend b {color:#ff8040; }
a.b {font-weight:bold; color:#008080 }
a.b:hover {color:red; }
</style>
</head>

<body>
<form name="mainform" action="" method="POST">
<fieldset>
	<legend><b>请勾选要显示的列</b>  (全部不选则恢复为系统默认状态) 　<a href="javascript:;" onclick="select_all();" class="b">[全选]</a>　<a href="javascript:;" onclick="select_none();" class="b">[全部不选]</a></legend>

	<div style="margin-top:5px;">
<?php
foreach ($all_headers as $k => $v) {
?>
		<div class="aline"><input type="checkbox" name="patient_headers[]" value="<?php echo $k; ?>" <?php echo in_array($k, $show_headers) ? "checked" : ""; ?> id="im<?php echo $k; ?>" ><label for="im<?php echo $k; ?>"><?php echo $v; ?></label></div>
<?php
}
?>
		<div class="clear"></div>
	</div>
</fieldset>

<div class="submit_line">
	<input type="submit" class="buttonb" value="确定" />
</div>

<input type="hidden" name="op" value="save" />

</form>

</body>
</html>