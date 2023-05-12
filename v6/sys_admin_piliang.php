<?php
/*
// 作者: 幽兰 (weelia@126.com)
*/
require "lib/set_env.php";
$table = "sys_admin";

if (!in_array($realname, explode(" ", $sys_super_admin))) {
	exit("没有操作权限...");
}

$part_id_name_arr = $db->query("select id, name from sys_part order by sort desc, id asc", "id", "name");
$character_id_name_arr = $db->query("select id, name from sys_character order by sort desc, id asc", "id", "name");

if ($_POST) {

	$op_type = $_POST["op_type"] == "add" ? "add" : "remove";
	$uids = trim($_POST["uids"]);
	$uids = $uids == "" ? "0" : $uids;

	$list = $db->query("select * from sys_admin where id in ($uids)", "id");

	$to_set = $_POST["set"];
	foreach ($list as $id => $li) {
		$sql_update = array();

		if (intval($to_set["part_id"]) > 0) {
			$sql_update[] = "part_id='".intval($to_set["part_id"])."'";
		}

		if (intval($to_set["character_id"]) > 0) {
			$sql_update[] = "character_id='".intval($to_set["character_id"])."'";
		}


		if (count($to_set["data_power"]) > 0) {
			$now_set = $li["data_power"] ? explode(",", $li["data_power"]) : array();
			foreach ($to_set["data_power"] as $v) {
				if ($op_type == "add") {
					if (!in_array($v, $now_set)) {
						$now_set[] = $v;
					}
				} else {
					$_key = array_search($v, $now_set);
					if ($_key !== false) {
						unset($now_set[$_key]);
					}
				}
			}

			$sql_update[] = "data_power='".implode(",", $now_set)."'";
		}

		// 修改挂号核心设置:
		if (count($to_set["guahao_config"]) > 0) {
			$now_set = $li["guahao_config"] ? explode(",", $li["guahao_config"]) : array();
			foreach ($to_set["guahao_config"] as $v) {
				if ($op_type == "add") {
					if (!in_array($v, $now_set)) {
						$now_set[] = $v;
					}
				} else {
					$_key = array_search($v, $now_set);
					if ($_key !== false) {
						unset($now_set[$_key]);
					}
				}
			}

			$sql_update[] = "guahao_config='".implode(",", $now_set)."'";
		}

		// 修改模块权限开关:
		if (count($to_set["module_config"]) > 0) {
			$now_set = $li["module_config"] ? explode("\n", str_replace("\r", "", $li["module_config"])) : array();
			foreach ($to_set["module_config"] as $v) {
				$v = $v.":1";
				if ($op_type == "add") {
					if (!in_array($v, $now_set)) {
						$now_set[] = $v;
					}
				} else {
					$_key = array_search($v, $now_set);
					if ($_key !== false) {
						unset($now_set[$_key]);
					}
				}
			}

			$sql_update[] = "module_config='".implode("\r\n", $now_set)."'";
		}

		if ($to_set["is_ukey"] != '') {
			if ($to_set["is_ukey"] == "open") {
				$sql_update[] = "use_ukey=1";
			} else {
				$sql_update[] = "use_ukey=0";
			}
		}


		if (count($sql_update) > 0) {
			$to_update = implode(", ", $sql_update);
			$db->query("update sys_admin set $to_update where id=$id limit 1");
		}
	}

	echo '<script type="text/javascript">';
	echo 'parent.load_box(0);';
	echo 'parent.msg_box("处理成功");';
	echo 'parent.update_content();';
	echo '</script>';
	exit;

}


function _uids_to_name($uids) {
	global $db;
	$name = $db->query("select realname from sys_admin where id in ($uids) order by realname asc", "", "realname");
	return implode("、", $name);
}


?>
<html xmlns=http://www.w3.org/1999/xhtml>
<head>
<title>批量设置权限</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
#type_select {height:25px; overflow:hidden; margin-top:10px; background:url("image/tab_bg.jpg") repeat-x; }

.hs_tab_cur {margin-left:5px; float:left; }
.hs_tab_cur .hs_tab_left {float:left; width:3px; height:25px; background:url("image/tab_cur_left.jpg") no-repeat; }
.hs_tab_cur .hs_tab_center {float:left; height:25px; background:url("image/tab_cur_center.jpg") repeat-x; }
.hs_tab_cur .hs_tab_right {float:left; width:3px; height:25px; background:url("image/tab_cur_right.jpg") no-repeat; }
.hs_tab_cur a {font-weight:bold; text-decoration:none; display:block; line-height:25px; padding:0 3px; color:red; }

.hs_tab_nor {margin-left:5px; float:left; }
.hs_tab_nor .hs_tab_left {float:left; width:3px; height:25px; background:url("image/tab_nor_left.jpg") no-repeat; }
.hs_tab_nor .hs_tab_center {float:left; height:25px; background:url("image/tab_nor_center.jpg") repeat-x; }
.hs_tab_nor .hs_tab_right {float:left; width:3px; height:25px; background:url("image/tab_nor_right.jpg") no-repeat; }
.hs_tab_nor a {font-weight:normal; text-decoration:none; display:block; line-height:25px; padding:0 3px; }

.wee_new_edit {border:1px solid #c6c6c6; }
.wee_new_edit td {border-top:1px dotted #c6c6c6; }
.wee_new_edit .left {width:18%; text-align:right; padding:5px; background-color:#f3f3f3; }
.wee_new_edit .right {text-align:left; padding:5px; }
</style>
<script type="text/javascript">
function check_data(o) {
	if (!confirm("设置错了就不好恢复了，确认检查清楚了吗？ 点“取消”可以再看看。")) {
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
		<td class="left"><font color="red">*</font> 方法：</td>
		<td class="right">
			<select name="op_type" class="combo">
				<option value="add">增加</option>
				<option value="remove">去除</option>
			</select>&nbsp;&nbsp;
			<font color="red">去除的含义：选择去除后，下面所勾选的项目，将会移除其权限</font>
		</td>
	</tr>

</table>

<div class="space"></div>

<table width="100%" class="wee_new_edit">
	<tr>
		<td class="left">新的部门：</td>
		<td class="right">
			<select name="set[part_id]" class="combo" style="width:200px;">
				<option value="" style="color:gray">　　　　</option>
				<?php echo list_option($part_id_name_arr, "_key_", "_value_"); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left">新的权限：</td>
		<td class="right">
			<select name="set[character_id]" class="combo" style="width:200px;">
				<option value="" style="color:gray">　　　　</option>
				<?php echo list_option($character_id_name_arr, "_key_", "_value_"); ?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="left">数据范围：</td>
		<td class="right">
			<?php echo show_check("set[data_power][]", $data_power_arr, "k", "v", array(), " "); ?>
		</td>
	</tr>

	<tr>
		<td class="left">挂号核心：</td>
		<td class="right">
			<?php echo show_check("set[guahao_config][]", $guahao_config_arr, "k", "v", array(), " "); ?>
		</td>
	</tr>

	<tr>
		<td class="left">功能开关：</td>
		<td class="right">
			<?php echo show_check("set[module_config][]", $special_config, "k", "v", array(), " "); ?>
		</td>
	</tr>

	<tr>
		<td class="left">uKey开关：</td>
		<td class="right">
			<select name="set[is_ukey]" class="combo">
				<option value=""></option>
				<option value="open">启用(要求使用uKey登录)</option>
				<option value="close">不启用(登录不需uKey)</option>
			</select>
		</td>
	</tr>
</table>

<div class="space"></div>

<div class="button_line">
	<input type="submit" class="submit" value="提交修改">
</div>

<input type="hidden" name="uids" id="uids" value="<?php echo $_GET["uids"]; ?>">

</form>




</body>
</html>