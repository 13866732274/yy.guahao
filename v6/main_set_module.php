<?php
/*
// 说明: 设置首页显示数据模块
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-09-10
*/
require "lib/set_env.php";


// 可以隐藏的数据区块:
$can_hide_arr = array();
if (in_array("all", $config["data_power"])) {
	$can_hide_arr["all"] = "总数据";
	if ($debug_mode || $config["show_feiyongjiankong"]) {
		//$can_hide_arr["feiyongjiankong"] = "费用监控数据";
	}
	if ($debug_mode || $config["show_hr_info"]) {
		//$can_hide_arr["hr_info"] = "人事系统数据";
	}
}

if (in_array("web", $config["data_power"])) {
	$can_hide_arr["web"] = "网络咨询部";
}

if (in_array("tel", $config["data_power"])) {
	$can_hide_arr["tel"] = "电话咨询部";
}

if ($debug_mode || $config["show_qihua_detail"]) {
	//$can_hide_arr["qihua_detail"] = "右侧企划数据";
}

if ($debug_mode || $uinfo["show_zixun_yudao"]) {
	$can_hide_arr["zixun_yudao"] = "咨询明日预到";
}




// 当前配置数据:
$index_config = @unserialize($uinfo["index_config"]);
if ($debug_mode) {
	$index_config = @unserialize($_SESSION["index_config"]);
}

$op = $_REQUEST["op"];

if ($op == "save") {

	$index_config["hide_search"] = $_POST["hide_search"] ? 1 : 0;

	$all_modes = explode(",", $_POST["show1_modules_all"]);
	$hide_modes = array();
	foreach ($all_modes as $mcode) {
		if (!@in_array($mcode, $_POST["show1_modules"])) {
			$hide_modes[] = $mcode;
		}
	}
	$index_config["global_hide"] = $hide_modes;

	$index_config["global"] = (array) $_POST["show_modules"];

	if ($hid > 0) {
		$index_config[$hid]["depart"] = (array) $_POST["depart"];
		$index_config[$hid]["disease"] = (array) $_POST["disease"];
	}

	$config_str = serialize($index_config);
	if ($uid > 0) {
		$db->query("update sys_admin set index_config='".$config_str."' where id='".$uid."' limit 1");
	} else {
		$_SESSION["index_config"] = $config_str;
	}

	echo '<script type="text/javascript">'."\r\n";
	echo 'parent.load_box(0);'."\r\n";
	echo 'parent.msg_box("设置保存成功")'."\r\n";
	echo 'parent.update_content()'."\r\n";
	echo '</script>'."\r\n";
	exit;
}


// 通用模块:
$module_list = $db->query("select * from index_module where isshow=1 and if_dingzhi=1 order by sort desc, id asc", "id");

// 疾病 id=>name数组:
$disease_id_name = $db->query("select id,name from disease where hospital_id=$hid order by sort desc, id asc", "id", "name");

// 科室
$depart_id_name = $db->query("select id,name from depart where hospital_id=$hid order by id asc", "id", "name");


header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
?>
<html>
<head>
<title>首页显示模块设置</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.aline {margin-left:20px; float:left; width:160px; height:22px; }
.submit_line {text-align:center; padding:10px;}
</style>
<script language="javascript">
function wee_select_all(id) {
	var objs = byid(id).getElementsByTagName("INPUT");
	for (var i=0; i<objs.length; i++) {
		var o = objs[i];
		if (o.type == "checkbox") {
			o.checked = true;
		}
	}
}

function wee_select_reverse(id) {
	var objs = byid(id).getElementsByTagName("INPUT");
	for (var i=0; i<objs.length; i++) {
		var o = objs[i];
		if (o.type == "checkbox") {
			o.checked = !o.checked;
		}
	}
}
</script>
</head>

<body>

<form action="" method="POST">

<fieldset id="global_hide_area">
	<legend>默认数据模块 <a href="javascript:;" onclick="wee_select_all('global_hide_area');return false;"><b>全选</b></a> <a href="javascript:;" onclick="wee_select_reverse('global_hide_area');return false;"><b>反选</b></a></legend>

<?php
foreach ($can_hide_arr as $mcode => $mname) {
	$chk = ' checked';
	if (in_array($mcode, $index_config["global_hide"])) {
		$chk = '';
	}
?>
	<div class="aline"><input type="checkbox" name="show1_modules[]" value="<?php echo $mcode; ?>" <?php echo $chk; ?> id="ID_<?php echo $mcode; ?>" ><label for="ID_<?php echo $mcode; ?>"><?php echo $mname; ?></label></div>
<?php } ?>

	<div class="clear"></div>
</fieldset>
<input type="hidden" name="show1_modules_all" value="<?php echo implode(",", array_keys($can_hide_arr)); ?>">
<br>

<fieldset id="global_area">
	<legend>可定制模块(全局) <a href="javascript:;" onclick="wee_select_all('global_area');return false;"><b>全选</b></a> <a href="javascript:;" onclick="wee_select_reverse('global_area');return false;"><b>反选</b></a></legend>

<?php
$all_mids = array();
foreach ($module_list as $mid => $mdef) {
	$all_mids[] = $mid;
	$chk = '';
	if (in_array($mid, $index_config["global"])) {
		$chk = ' checked';
	}
?>
	<div class="aline"><input type="checkbox" name="show_modules[]" value="<?php echo $mid; ?>" <?php echo $chk; ?> id="ID_<?php echo $mid; ?>" ><label for="ID_<?php echo $mid; ?>"><?php echo $mdef["name"]; ?></label></div>
<?php } ?>

	<div class="clear"></div>
</fieldset>
<br>


<?php
$cm = (array) $index_config[$hid]["depart"];
?>
<?php
if (count($depart_id_name) > 0) {
?>
<fieldset id="depart_area">
	<legend>关注科室 <a href="javascript:;" onclick="wee_select_all('depart_area');return false;"><b>全选</b></a> <a href="javascript:;" onclick="wee_select_reverse('depart_area');return false;"><b>反选</b></a> </legend>
<?php
foreach ($depart_id_name as $k => $v) {
?>
	<div class="aline"><input type="checkbox" name="depart[]" value="<?php echo $k; ?>" <?php echo in_array($k, $cm) ? "checked" : ""; ?> id="imx<?php echo $k; ?>" ><label for="imx<?php echo $k; ?>"><?php echo $v; ?></label></div>
<?php
}
?>
	<div class="clear"></div>
</fieldset>
<br>
<?php } ?>


<?php
$cm = (array) $index_config[$hid]["disease"];
?>
<fieldset id="disease_area">
	<legend>关注病种 <a href="javascript:;" onclick="wee_select_all('disease_area');return false;"><b>全选</b></a> <a href="javascript:;" onclick="wee_select_reverse('disease_area');return false;"><b>反选</b></a> </legend>
<?php
foreach ($disease_id_name as $k => $v) {
?>
	<div class="aline"><input type="checkbox" name="disease[]" value="<?php echo $k; ?>" <?php echo in_array($k, $cm) ? "checked" : ""; ?> id="imd<?php echo $k; ?>" ><label for="imd<?php echo $k; ?>"><?php echo $v; ?></label></div>
<?php
}
?>
	<div class="clear"></div>
</fieldset>

<div style="margin-top:10px; ">
	<div class="aline"><input type="checkbox" name="hide_search" value="1" <?php echo $index_config["hide_search"] > 0 ? "checked" : ""; ?> id="hide_search" ><label for="hide_search">隐藏首页搜索功能</label></div>
	<div class="clear"></div>
</div>

<div class="submit_line">
	<input type="submit" class="submit" value="确定" />
</div>

<input type="hidden" name="op" value="save" />

</form>



</body>
</html>