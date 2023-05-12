<?php
// --------------------------------------------------------
// - 功能说明 : 设置轨迹
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2013-8-11
// --------------------------------------------------------
require "lib/set_env.php";
$table = "patient_".$user_hospital_id;

if ($user_hospital_id == 0) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$id = intval($_REQUEST["id"]);
if (!$id) {
	exit("参数错误.");
}
$line = $db->query("select * from $table where id='$id' limit 1", 1);

if ($_POST) {
	$p = $_POST;
	$r = array();
	$save_field = explode(" ", "guiji qudao from_site key_word");
	foreach ($save_field as $v) {
		//if ($v && isset($p[$v]) && $p[$v] != $line[$v]) {
			$r[$v] = $p[$v];
		//}
	}
	// 字段修改记录:
	if (count($r) > 0) {
		$logs = patient_modify_log($r, $line);
		if ($logs) {
			$r["edit_log"] = $logs;
		}
	}

	if (count($r) > 0) {
		$sqldata = $db->sqljoin($r);
		$sql = "update $table set $sqldata where id='$id' limit 1";
		ob_start();
		$rs = $db->query($sql);
		$error = ob_get_clean();
		if ($error) {
			echo "提交出错，请联系开发人员：<br>".$error;
			exit;
		}
		if ($rs) {
			$str = "资料提交成功！";
		} else {
			echo "提交出错，请联系开发人员：<br>".$db->sql."<br>";
			exit;
		}
	} else {
		$str = "资料无变动";
	}
	echo '<script type="text/javascript">'."\r\n";
	echo 'parent.msg_box("'.$str.'");'."\r\n";
	echo 'parent.close_divs();'."\r\n";
	echo '</script>'."\r\n";
	exit;
}



// page begin ----------------------------------------------------
?>
<html>
<head>
<title><?php echo $line["name"]; ?> - 设置轨迹</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.left {text-align:right; }
.right {padding:4px 0px; }
</style>
<script language="javascript">
function check_data(oForm) {
	return true;
}

function update_qudao() {
	var value = byid("guiji").value;
	if (value != '') {
		var url = "/v6/http/get_qudao.php?main_id="+value+"&do=update_qudao_do";
		load_js(url, "get_qudao");
	} else {
		byid("qudao_select").innerHTML = '<select name="qudao" id="qudao" class="combo" style="width:100px;"><option value=""></option></select>';
	}
}

function update_qudao_do(res) {
	var s = '';
	var cur_qudao = byid("cur_qudao_id").value;
	if (res && res["status"] == 'ok') {
		if (res["data"].length > 0) {
			s += '<select name="qudao" id="qudao" class="combo" style="width:100px;">';
			//s += '<option value="">-请选择渠道-</option>';
			for (var i=0; i<res["data"].length; i++) {
				var a = res["data"][i].split("#");
				if (a[0] == cur_qudao) {
					s += '<option value="'+a[0]+'" selected>'+a[1]+' *</option>&nbsp; ';
				} else {
					s += '<option value="'+a[0]+'">'+a[1]+'</option>&nbsp; ';
				}
			}
			s += '</select>';
		} else {
			s += '(无下拉选择)';
		}
	} else {
		s += '(调用出错)';
	}
	byid("qudao_select").innerHTML = s;
}
</script>
</head>

<body oncontextmenu="return false">
<form name="mainform" action="" method="POST" onsubmit="return check_data(this)">
<table width="100%" style="margin-top:10px;">
	<tr>
		<td class="left" style="width:80px;">轨迹：</td>
		<td class="right">
			<select name="guiji" id="guiji" class="combo" onchange="update_qudao()" style="width:100px;">
				<option value="" style="color:gray">-轨迹来源-</option>
				<?php echo list_option($guiji_arr, '_key_', '_value_', $line["guiji"]); ?>
			</select>
			<span class="ml10" id="qudao_select"><!-- 渠道下拉 --></span>
			<input type="hidden" id="cur_qudao_id" value="<?php echo $line["qudao"]; ?>">
		</td>
	</tr>
	<tr>
		<td class="left">来源网站：</td>
		<td class="right">
			<input name="from_site" value="<?php echo $line["from_site"]; ?>" class="input" style="width:210px">&nbsp;
		</td>
	</tr>
	<tr>
		<td class="left">关键词：</td>
		<td class="right">
			<input name="key_word" value="<?php echo $line["key_word"]; ?>" class="input" style="width:210px">&nbsp;
		</td>
	</tr>
</table>
<input type="hidden" name="id" id="id" value="<?php echo $id; ?>">
<div class="button_line">
	<input type="submit" class="buttonb" value="提交资料">
</div>
</form>


<script type="text/javascript">
update_qudao();
</script>

</body>
</html>