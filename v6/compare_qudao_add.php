<?php
/*
// 说明: 跨科室对比渠道数据 - 添加对比项目
// 作者: 幽兰 (weelia@126.com)
// 时间: 2014-03-20
*/
include "lib/set_env.php";

$hids = implode(",", $hospital_ids);
$h_list = $db->query("select id,name from hospital where ishide=0 and id in ($hids) order by group_name asc, name asc", "id", "name");


// 保存设置
if ($_POST) {
	$d = $db->query("select uid,content from compare_qudao where uid=$uid limit 1", 1);
	$con_arr = array();
	if ($d["uid"] != 0) {
		$con_arr = @unserialize($d["content"]);
	}

	// 当前提交
	$compare_set = array();
	$x = 0;
	for ($i = 1; $i <= 5; $i++) {
		if ($_POST["hid"][$i] > 0) {
			$compare_set[$x++] = array($_POST["hid"][$i], $_POST["qudao"][$i]);
		}
	}

	$con_arr[] = $compare_set;

	$s = serialize($con_arr);

	if ($d["uid"] != 0) {
		$db->query("update compare_qudao set content='$s' where uid=$uid limit 1");
	} else {
		$db->query("insert into compare_qudao set uid=$uid, content='$s'");
	}

	echo '<script> parent.update_content(); </script>';
	echo '<script> parent.msg_box("添加成功", 2); </script>';
	echo '<script> parent.load_src(0); </script>';

}



?>
<html>
<head>
<title>添加对比项目</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
* {font-family:"微软雅黑" !important; }
.con_set {border:1px solid silver; }
.con_set td {text-align:left; }
.con_set .head td {border:1px solid silver; border-left:0; border-right:0; background:#f0f0f0; padding:5px; }
.con_set .line td {border:1px solid silver; border-left:0; border-right:0; padding:5px; }

.td_l {text-align:left !important; }
.td_c {text-align:center !important; }
.td_r {text-align:right !important; }
</style>

<script type="text/javascript">
// 加载 js 文件函数:
function load_js(sfile, js_id) {
	if (typeof(js_id) == "string" && js_id != '') {
		var o = byid(js_id);
		if (o) {
			o.parentNode.removeChild(o);
		}
	}

	var obj = document.createElement('script');
	obj.type = "text/javascript";
	obj.src = sfile;
	if (typeof(js_id) == "string" && js_id != '') {
		obj.id = js_id;
	}
	var head = document.getElementsByTagName('head')[0];
	return head.appendChild(obj);
}

function update_hid_qudao(o, index) {
	var hid = o.value;
	if (hid > 0) {
		var url = "compare_load_qudao.php?hid="+hid+"&index="+index;
		load_js(url, "compare_load_qudao");
	} else {
		byid("qudao_area_"+index).innerHTML = "(请先选择科室)";
	}
}

function update_hid_qudao_do(arr, index) {
	var s = '';

	s += '　→　<select name="qudao['+index+']" class="combo">';
	for (var i in arr) {
		s += ' <option value="'+i+'">'+arr[i]+'</option>';
	}
	s += '</select>';

	byid("qudao_area_"+index).innerHTML = s;
}

function check_data(f) {
	var num = 0;
	for (i=1; i<=5; i++) {
		if (byid("hid_"+i).value != '') {
			num++;
		}
	}
	if (num < 2) {
		alert("至少要选择2个渠道进行对比分析~");
		return false;
	}
	return true;
}
</script>
</head>

<body>

<form method="POST" onsubmit="return check_data(this)">

<table width="100%" class="con_set">
	<tr class="head">
		<td width="10%" class="td_c">对比编号</td>
		<td>科室 & 渠道</td>
	</tr>

<?php for ($i=1; $i<=5; $i++) { ?>

	<tr class="line">
		<td class="td_c"><?php echo $i; ?></td>
		<td>
			<select name="hid[<?php echo $i; ?>]" id="hid_<?php echo $i; ?>" onchange="update_hid_qudao(this, <?php echo $i; ?>)" class="combo">
				<option value="" style="color:silver">---科室选择---</option>
				<?php echo list_option($h_list, "_key_", "_value_"); ?>
			</select>&nbsp;
			<span id="qudao_area_<?php echo $i; ?>">(先选科室后选渠道)</span>
		</td>
	</tr>

<?php } ?>

</table>

<div class="button_line">
	<input type="submit" id="submit_button" class="submit" value="确认并提交">
</div>

</form>

</body>
</html>