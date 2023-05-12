<?php
// --------------------------------------------------------
// - 功能说明 : admin.php
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-05-15 00:51
// --------------------------------------------------------
require "lib/set_env.php";
$table = "sys_admin";

if (!$debug_mode && $username != "admin") {
	exit("对不起，你无权操作。");
}


if ($_POST) {
	$uids = trim($_POST["uids"]);
	$add_hid = intval($_POST["add_hid"]);
	if ($uids != '' && $add_hid > 0) {
		$uid_arr = explode(",", $uids);
		foreach ($uid_arr as $u) {
			$u = intval($u);
			if ($u > 0) {
				$line = $db->query("select * from sys_admin where id=$u limit 1", 1);
				if ($line["id"] > 0) {
					$h_arr = explode(",", $line["hospitals"]);
					if (!in_array($add_hid, $h_arr)) {
						$h_arr[] = $add_hid;
						asort($h_arr);
						$new_hospitals = implode(",", $h_arr);
						$db->query("update sys_admin set hospitals='$new_hospitals' where id=$u limit 1");
					}
				}
			}
		}
	} else {
		exit("参数错误");
	}

	echo '<script type="text/javascript">';
	echo 'parent.load_box(0);';
	echo 'parent.msg_box("处理成功");';
	echo 'parent.update_content();';
	echo '</script>';
	exit;
}


// 按分组进行整理
$hids = implode(",", $hospital_ids);
$group_id_name = $db->query("select id,name from hospital_group order by sort desc, name asc", "id", "name");
$options = array();
foreach ($group_id_name as $_gid => $_gname) {
	$h_list = $db->query("select id,name,color from hospital where ishide=0 and group_id=$_gid and id in ($hids) order by sort desc, name asc", "id");
	if (count($h_list) > 0) {
		$options[] = array('-1', $_gname." (".count($h_list).')', 'color:red' );
		foreach ($h_list as $_hid => $_arr) {
			$options[] = array($_hid, '　'.$_arr["name"], ($_arr["color"] ? ('color:'.$_arr["color"]) : 'color:blue') );
		}
	}
}


function _uids_to_name($uids) {
	global $db;
	$uid_arr = explode(",", $uids);
	foreach ($uid_arr as $u) {
		$u = intval($u);
		if ($u > 0) {
			$name = $db->query("select realname from sys_admin where id=$u limit 1", 1, "realname");
			if ($name != '') {
				$out[] = $name;
			} else {
				$out[] = $u;
			}
		}
	}

	return implode("、", $out);
}


?>
<html>
<head>
<title>批量增加科室</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.wee_new_edit {border:1px solid #c6c6c6; }
.wee_new_edit td {border-top:1px dotted #c6c6c6; }
.wee_new_edit .left {width:22%; text-align:right; padding:5px; background-color:#f3f3f3; }
.wee_new_edit .right {text-align:left; padding:5px; }
</style>

<script type="text/javascript">
function check_data(f) {
	if (byid("uids").value == '') {
		alert("UID：参数错误。　　　　　");
		return false;
	}
	if (byid("add_hid").value <= 0) {
		alert("请选择有效的科室。　　　　　");
		return false;
	}
	if (!confirm("提交处理后不能撤销，如还需检查一下，请点取消。直接提交请点确定。")) {
		return false;
	}
	return true;
}
</script>

</head>

<body>

<form method="POST" action="" onsubmit="return check_data(this);" style="margin-top:10px;">
<table width="100%" class="wee_new_edit">
	<tr>
		<td class="left">已选人员：</td>
		<td class="right">
			<?php echo _uids_to_name($_GET["uids"]); ?>
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 要增加的科室：</td>
		<td class="right">
<?php
	echo '<select name="add_hid" id="add_hid" class="combo">';
	echo '  <option value="0" style="color:gray">-请选择科室-</option>';
	foreach ($options as $v) {
		echo '  <option value="'.$v[0].'"'.($v[2] ? ' style="'.$v[2].'"' : '').'>'.$v[1].'</option>';
	}
	echo '</select>';
?>
		</td>
	</tr>

</table>

<br>
<br>

<div class="button_line">
	<input type="submit" class="submit" value="确定">
</div>

<input type="hidden" name="uids" id="uids" value="<?php echo $_GET["uids"]; ?>">

</form>


</body>
</html>