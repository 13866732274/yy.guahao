<?php
// --------------------------------------------------------
// - 功能说明 : 设置消费额等
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2011-09-16 => 2013-01-14
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

if (!in_array("set_xiaofei", $gGuaHaoConfig)) {
	exit("对不起，你没有设消费额权限...");
}

$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("参数错误.");
}

$line = $db->query_first("select * from $table where id='$id' limit 1");

if ($_POST) {
	$p = $_POST;
	$r = array();

	//$r["is_chengjiao"] = $p["is_chengjiao"];
	//$r["is_xiaofei"] = $p["is_xiaofei"];

	$r["is_jiancha"] = $p["is_jiancha"];

	$r["is_zhiliao"] = $p["is_zhiliao"];

	if ($p["update_xiangmu"] && count($p["xiangmu"]) > 0) {
		$r["xiangmu"] = $line["xiangmu"].$p["shoushu_time"]." ".implode("、", $p["xiangmu"])."\n";
	}

	if ($p["xiaofei"] > 0) {
		$r["xiaofei_count"] = floatval($line["xiaofei_count"]) + floatval($p["xiaofei"]);
		$r["xiaofei_log"] = $line["xiaofei_log"].($p["shoushu_time"] ? $p["shoushu_time"] : date("Y-m-d"))." 消费 ".$p["xiaofei"]." 元"."\n";
	}

	if (trim($p["memo"]) != '') {
		$r["memo"] = (rtrim($line["memo"]) ? (rtrim($line["memo"])."\n") : "").date("Y-m-d H:i ").$realname.": ".$_POST["memo"];
	}

	if ($hid == 15) {
		$zhiliao_all = intval($_POST["zhiliao_all"]);
		$zhiliao_log = '';
		if ($zhiliao_all > 0) {
			$s = '';
			foreach ($_POST["zhiliao_log"] as $k => $v) {
				if ($v != '') {
					$s .= $k.'@'.$v."\r\n";
				}
			}
			$zhiliao_log = trim($s);
		}

		$r["zhiliao_all"] = $zhiliao_all;
		$r["zhiliao_log"] = $zhiliao_log;
	}

	// 字段修改记录:
	/*
	if (count($r) > 0) {
		$logs = patient_modify_log($r, $line);
		if ($logs) {
			$r["edit_log"] = $logs;
		}
	}
	*/

	//user_op_log("修改手术记录[".$line["name"]."]");

	if (count($r) > 0) {
		$sqldata = $db->sqljoin($r);
		$sql = "update $table set $sqldata where id='$id' limit 1";
		ob_start();
		$rs = $db->query($sql);
		$error = ob_get_clean();
		if ($error) {
			echo $error;
			exit;
		}
		if ($rs) {
			$str = "资料提交成功！";
		} else {
			$str = "提交出错，请稍后再试。";
		}
	} else {
		$str = "资料无变动";
	}
	echo '<script type="text/javascript">'."\r\n";
	echo 'parent.load_src(0);'."\r\n";
	echo 'parent.msg_box("'.$str.'");'."\r\n";
	echo '</script>'."\r\n";
	exit;
}



// page begin ----------------------------------------------------
?>
<html>
<head>
<title><?php echo $line["name"]; ?> - 消费及治疗记录</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
.l {text-align:right; border-bottom:1px solid #D8D8D8; padding:6px 0px 2px 0px; width:120px; }
.r {text-align:left; border-bottom:1px solid #D8D8D8; padding:4px 0px; }
.foot_button {margin-top:15px; text-align:center; }
</style>
<script language="javascript">
function check_data(oForm) {
	return true;
}
</script>
</head>

<body>
<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" style="margin-top:10px;">
	<tr>
		<td class="l">是否检查：</td>
		<td class="r">
			<input type="radio" name="is_jiancha" value="1" <?php echo $line["is_jiancha"] == 1 ? "checked" : ""; ?> id="is_jiancha_1"><label for="is_jiancha_1">已检查</label>
			<input type="radio" name="is_jiancha" value="-1" <?php echo $line["is_jiancha"] == -1 ? "checked" : ""; ?> id="is_jiancha_2"><label for="is_jiancha_2">未检查</label>
			<input type="radio" name="is_jiancha" value="0" <?php echo $line["is_jiancha"] == 0 ? "checked" : ""; ?> id="is_jiancha_0" disabled="true"><label for="is_jiancha_0">未知</label>
		</td>
	</tr>
	<tr>
		<td class="l">是否治疗：</td>
		<td class="r">
			<input type="radio" name="is_zhiliao" value="1" <?php echo $line["is_zhiliao"] == 1 ? "checked" : ""; ?> id="is_zhiliao_1"><label for="is_zhiliao_1">已治疗</label>
			<input type="radio" name="is_zhiliao" value="-1" <?php echo $line["is_zhiliao"] == -1 ? "checked" : ""; ?> id="is_zhiliao_2"><label for="is_zhiliao_2">未治疗</label>
			<input type="radio" name="is_zhiliao" value="0" <?php echo $line["is_zhiliao"] == 0 ? "checked" : ""; ?> id="is_zhiliao_0" disabled="true"><label for="is_zhiliao_0">未知</label>
		</td>
	</tr>
	<tr>
		<td class="l">手术时间：</td>
		<td class="r"><input name="shoushu_time" value="<?php echo $line["shoushu_time"] > 0 ? date("Y-m-d", $line["shoushu_time"]) : date("Y-m-d"); ?>" class="input" style="width:120px" id="shoushu_time" readonly="true" title="只能选择时间，不能输入" style="cursor:help;"> <img src="image/calendar.gif" id="shoushu_time" onClick="picker({el:'shoushu_time',dateFmt:'yyyy-MM-dd'})" align="absmiddle" style="cursor:pointer" title="选择时间"></td>
	</tr>
	<tr>
		<td class="l" valign="top" style="padding-top:6px;">治疗类型：</td>
		<td class="r">
<?php
$xiangmu_str = $db->query("select xiangmu from disease where id=".$line["disease_id"]." limit 1", 1, "xiangmu");
$xiangmu = explode(" ", trim($xiangmu_str));
//$cur_xiangmu = explode(" ", trim($line["xiangmu"]));
//$xiangmu = array_unique(array_merge($cur_xiangmu, $xiangmu));
foreach ($xiangmu as $k) {
	if ($k == '') continue;
	//$checked = in_array($k, $cur_xiangmu) ? " checked" : "";
	$checked = '';
	$makered = $checked ? ' style="color:red"' : '';
	echo '<input type="checkbox" name="xiangmu[]" value="'.$k.'"'.$checked.' id="xiangmu_'.$k.'"'. $ce["xiangmu"].'><label for="xiangmu_'.$k.'"'.$makered.'>'.$k.'</label>&nbsp;&nbsp;';
}
?>
<?php if (!$ce["xiangmu"]) { ?>
		<input type="hidden" name="update_xiangmu" value="1">
		<span id="xiangmu_user"></span>
		<span id="xiangmu_add"><b>新增：</b><input id="xiangmu_my_add" class="input" size="10">&nbsp;<button onclick="xiangmu_user_add()" class="button">添加</button></span>
<script language="JavaScript">
function xiangmu_user_add() {
	var name = byid("xiangmu_my_add").value;
	if (name == '') {
		alert("请输入新的治疗项目名称！"); return false;
	}
	var str = '<input type="checkbox" name="xiangmu[]" value="'+name+'" checked id="xiangmu_'+name+'"><label for="xxiangmu_'+name+'">'+name+'</label>&nbsp;&nbsp;';
	byid("xiangmu_user").insertAdjacentHTML("beforeEnd", str);
	byid("xiangmu_my_add").value = '';
}
</script>
<?php } ?>

<?php if ($line["xiangmu"]) { ?>
		<fieldset>
			<legend><b>治疗记录</b></legend>
			<?php echo text_show($line["xiangmu"]); ?>
		</fieldset>
<?php } ?>

		</td>
	</tr>
	<tr>
		<td class="l" valign="top" style="padding-top:6px;">本次消费：</td>
		<td class="r"><input name="xiaofei" value="" class="input" style="width:100px;"> RMB &nbsp; &nbsp; 历史消费额：<b><?php echo round($line["xiaofei_count"], 1); ?></b> RMB

<?php if ($line["xiaofei_log"]) { ?>
		<fieldset>
			<legend><b>消费记录</b></legend>
			<?php echo text_show($line["xiaofei_log"]); ?>
		</fieldset>
<?php } ?>

		</td>
	</tr>

<?php
// 处理治疗记录:
$zhiliao_log = array();
if (trim($line["zhiliao_log"]) != '') {
	$_s = str_replace("\r", "", trim($line["zhiliao_log"]));
	$_arr = explode("\n", $_s);
	foreach ($_arr as $_v) {
		list($_a, $_b) = explode("@", $_v, 2);
		if ($_a && $_b) {
			$zhiliao_log[intval($_a)] = $_b;
		}
	}
}

$zhiliao_cishu_arr = array();
for ($i=1; $i <= 15; $i++) {
	$zhiliao_cishu_arr[$i] = $i."次";
}
?>
	<tr>
		<td class="l" valign="top" style="padding-top:6px;">治疗次数：</td>
		<td class="r">
			<select name="zhiliao_all" id="zhiliao_all" class="combo" id="" onchange="update_zhiliao_log_area(this)">
				<option value="0" style="color:gray">--请选择--</option>
				<?php echo list_option($zhiliao_cishu_arr, '_key_', '_value_', $line["zhiliao_all"]); ?>
			</select>
			<span class="intro">请选择所需治疗次数</span>
			<div id="zhiliao_log_area"><!-- to fill --></div>
		</td>
	</tr>
<script type="text/javascript">
var zhiliao_log = <?php echo json($zhiliao_log); ?>;
function update_zhiliao_log_area() {
	var cishu = byid("zhiliao_all").value;
	if (cishu > 0) {
		var s = '';
		for (var i=1; i<= cishu; i++) {
			var zhiliao_date = zhiliao_log[i] ? zhiliao_log[i] : '';
			s += '第'+i+'次治疗时间：<input name="zhiliao_log['+i+']" id="zhiliao_log_'+i+'" value="'+zhiliao_date+'" class="input"> <img src="image/calendar.gif" onclick="picker({el:\'zhiliao_log_'+i+'\',dateFmt:\'yyyy-MM-dd\'})" align="absmiddle" style="cursor:pointer" title="选择治疗日期"><br>';
		}
		byid("zhiliao_log_area").innerHTML = s;
	} else {
		byid("zhiliao_log_area").innerHTML = '';
	}
}
if (byid("zhiliao_all").value > 0) {
	update_zhiliao_log_area();
}
</script>

	<tr>
		<td class="l" valign="top" style="padding-top:6px;">添加备注：</td>
		<td class="r"><textarea name="memo" style="width:75%; height:48px;" class="input"></textarea></td>
	</tr>
</table>

<div class="foot_button">
	<input type="submit" class="buttonb" value="提交资料">
</div>

<input type="hidden" name="id" value="<?php echo $id; ?>">
</form>
</body>
</html>