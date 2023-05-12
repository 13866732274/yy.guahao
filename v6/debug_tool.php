<?php
/*
// 作者: 幽兰 (weelia@126.com)
*/
require "lib/set_env.php";
if (!$debug_mode) {
	exit("无执行权限...");
}
set_time_limit(0);

$op = $_REQUEST["op"];

// 显示phpinfo
if ($op == "phpinfo") {
	phpinfo();
	exit;
}

// 现场医生改名
if ($op == "rename_xianchang_doctor") {
	$from_real_name = trim($_POST["from_real_name"]);
	$to_login_name = trim($_POST["to_login_name"]);
	$to_real_name = trim($_POST["to_real_name"]);

	$db->query("update sys_admin set name='$to_login_name', realname='$to_real_name' where realname='$from_real_name' limit 1");
	echo $db->sql." 已执行，受影响行数：".mysql_affected_rows()."<br><br>";

	if ($hid > 0) {
		$db->query("update patient_{$hid} set xianchang_doctor='$to_real_name' where xianchang_doctor='$from_real_name'");
		echo $db->sql." 已执行，受影响行数：".mysql_affected_rows()."<br><br>";
	}

	echo "现场医生改名执行结束";
	exit;
}


// 主治医生改名
if ($op == "rename_doctor") {
	$from_real_name = trim($_POST["from_real_name"]);
	$to_login_name = trim($_POST["to_login_name"]);
	$to_real_name = trim($_POST["to_real_name"]);

	$db->query("update sys_admin set name='$to_login_name', realname='$to_real_name' where realname='$from_real_name' limit 1");
	echo $db->sql." 已执行，受影响行数：".mysql_affected_rows()."<br><br>";

	if ($hid > 0) {
		$db->query("update doctor set name='$to_real_name' where name='$from_real_name' and hospital_id='$hid' limit 1");
		echo $db->sql." 已执行，受影响行数：".mysql_affected_rows()."<br><br>";

		$db->query("update patient_{$hid} set doctor='$to_real_name' where doctor='$from_real_name'");
		echo $db->sql." 已执行，受影响行数：".mysql_affected_rows()."<br><br>";
	}

	echo "主治医生改名执行结束";
	exit;
}



// 更新字段
if ($op == "update_field") {
	include_once "lib/function.create_table.php";

	// 读取需更新的每家医院:
	$hids = $db->query("select id from hospital", "", "id");

	echo "正在处理，请稍候...".str_repeat("&nbsp;", 50)."<br>";
	flush();
	ob_flush();
	ob_end_flush();

	// 要处理的表:
	$table_names = array();
	$table_names[] = "patient";
	foreach ($hids as $hid) {
		$table_names[] = "patient_".$hid;
	}

	// 处理:
	foreach ($table_names as $table_name) {
		$cs = $db_tables["patient"];

		if (!table_exists($table_name, $db->dblink)) {
			//$db->query(str_replace("{hid}", $hid, $cs));
			//echo "创建表 ".$table_name." <br>";
		} else {
			$fields = parse_fields($cs);
			foreach ($fields as $f => $fs) {
				if (!field_exists($f, $table_name, $db->dblink)) {
					$tm = array_keys($fields);
					$tm2 = array_search($f, $tm);
					if ($tm2 == 0) {
						$pos = " first";
					} else {
						$pos = " after `".$tm[$tm2-1]."`";
					}
					$sql = "alter table `".$table_name."` add ".$fs.$pos;
					$db->query($sql);
					echo "表 ".$table_name." 添加字段 ".$f."<br>";
				}
			}
		}

		flush();
		ob_flush();
		ob_end_flush();
		sleep(1);
	}

	echo "<br>全部完成。";
	exit;
}

// 更新索引结构
if ($op == "update_index") {
	// 所有医院名称:
	$h_id_name = $db->query("select id,name from hospital order by id asc", "id", "name");

	// 添加索引:
	foreach ($h_id_name as $_hid => $_hname) {

		$tindex = mysql_query("show index from patient_{$_hid}", $db->dblink);

		$indexs = array();
		while ($li = mysql_fetch_array($tindex)) {
			$indexs[] = $li["Column_name"];
		}

		echo $_hid.": ".$_hname." ";

		if (!in_array("quick", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX quick(`part_id`,`disease_id`,`order_date`,`addtime`,`uid`,`status`,`id`)");
			echo "添加索引(quick) ";
		}
		if (!in_array("addtime", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`addtime`)");
			echo "添加索引(addtime) ";
		}
		if (!in_array("order_date", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`order_date`)");
			echo "添加索引(order_date) ";
		}
		if (!in_array("part_id", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`part_id`)");
			echo "添加索引(part_id) ";
		}
		if (!in_array("uid", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`uid`)");
			echo "添加索引(uid) ";
		}
		if (!in_array("status", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`status`)");
			echo "添加索引(status) ";
		}
		if (!in_array("tel", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`tel`)");
			echo "添加索引(tel) ";
		}
		if (!in_array("name", $indexs)) {
			$db->query("ALTER TABLE `patient_{$_hid}` ADD INDEX (`name`)");
			echo "添加索引(name) ";
		}

		echo " 更新完毕。<br>";
		flush();
		ob_flush();
		ob_end_flush();
	}

	echo "<br><br>全部完成。";
	exit;
}


if ($op == "check_hospital") {

	$h_id_name = $db->query("select id,name from hospital order by sort desc,id asc", "id", "name");
	$keep_ids = array_keys($h_id_name);

	$users = $db->query("select id,realname,hospitals from sys_admin", "id");
	foreach ($users as $id => $v) {
		$ids = $v["hospitals"];
		if ($ids != '') {
			$ids_arr = explode(",", trim(trim($ids), ","));
			if (count($ids_arr) > 0) {
				foreach ($ids_arr as $x => $y) {
					if (!in_array($y, $keep_ids)) {
						unset($ids_arr[$x]);
					}
				}

				$new = implode(",", $ids_arr);

				if (trim($ids) != $new) {
					$db->query("update sys_admin set hospitals='$new' where id=$id limit 1");
					echo $v["realname"]." 医院变动: ".$ids." => ".$new."<br>";
				}
			}
		}
	}

	echo "执行完成..<br>";
	exit;
}


if ($op == "move_old_data") {
	$the_date = strtotime("-1 years");

	$tlist = mysql_query("show tables");
	$tables = array();
	while ($li = mysql_fetch_array($tlist)) {
		$tables[] = $li[0];
	}

	$hid_name_arr = $db->query("select id,name from hospital order by id asc", "id", "name");
	foreach ($hid_name_arr as $_hid => $_hname) {
		$table = "patient_{$_hid}";
		$backup_table = "patient_{$_hid}_history";

		// 是否需要创建历史记录表结构:
		if (!in_array($backup_table, $tables)) {
			$sql = "create table $backup_table (select * from $table limit 1)";
			$db->query($sql);
			$sql = "truncate table $backup_table";
			$db->query($sql);
		}

		// 转移数据：
		$sql = "insert into $backup_table (select * from $table where order_date<".$the_date.")";
		$db->query($sql);
		$num = mysql_affected_rows();
		if ($num > 0) {
			$sql = "delete from $table where order_date<".$the_date;
			$db->query($sql);
		}

		echo $_hid.":".$_hname." 处理完毕，共转移了".intval($num)."条数据<br>";
		flush();
		ob_flush();
		ob_end_flush();
		sleep(1);
	}

	echo "<br>全部处理完成。";
	exit;
}


// 从一个创建表的语句中解读字段
function parse_fields($s) {
	$list = explode("\n", $s);
	$out = array();
	foreach ($list as $k) {
		$k = trim($k);
		if (substr($k, 0, 1) == "`") {
			$fname = ltrim($k, "`");
			list($sa, $sb) = explode(" ", $fname, 2);
			$sa = rtrim($sa, "`");
			$out[$sa] = rtrim(trim($k), ',');
		}
	}

	return $out;
}


$index = 1;
?>
<html>
<head>
<title>调试工具</title>
<meta http-equiv="Content-Type" content="text/html;charset=gbk">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.head {padding:5px 10px 3px 10px !important;}
.item {padding:20px 10px 18px 10px !important; }
a {font-weight:bold; color:#FF8040; font-size:14px; }
</style>
<script type="text/javascript">
function confirm_op() {
	return confirm("此操作十分危险，仅限开发人员使用。\r\n\r\n如果你不是开发人员，不知道后果，请立即点击“取消”。否则问题可能非常严重。慎之！\r\n\r\n是否确定继续？");
}

//alert(parent.ZHUWENYA_IFRAME);
</script>
</head>

<body>
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips">调试工具(仅限开发人员使用)</nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">刷新</button></td>
	</tr>
</table>

<div class="space"></div>

<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center" width="32">序号</td>
		<td class="head" align="left" width="100">功能</td>
		<td class="head" align="left" width="">简介</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=phpinfo" onclick="return confirm_op()">查看phpinfo()</a></td>
		<td align="left" class="item">查看服务器phpinfo，以此诊断出现何种问题。</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=update_field" onclick="return confirm_op()">更新patient表结构</a></td>
		<td align="left" class="item">当 patient 基本结构变更时，使用此工具将变更应用到系统所有 patient 表中。</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=update_index" onclick="return confirm_op()">为patient表补充索引</a></td>
		<td align="left" class="item">添加 addtime order_date part_id author tel name 索引，查询速度更快。</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=check_hospital" onclick="return confirm_op()">检查更新用户所属医院</a></td>
		<td align="left" class="item">检查更新用户所属医院，如果某医院被删除，将从其所属医院中移去。</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="?op=move_old_data" onclick="return confirm_op()">将一年前的旧数据移到历史表中</a></td>
		<td align="left" class="item">将一年前的旧数据移到历史表中，按预约时间算。</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="patient_export.php" onclick="return confirm_op()">导出当前医院的数据</a></td>
		<td align="left" class="item">
			将当前所切换的医院的数据导出为文本格式，通过转换，可以转成excel格式。<br>
			<b>按年份导出：</b><form action="patient_export.php" method="GET" style="display:inline;">
				<input name="year" value="<?php echo date("Y"); ?>" class="input">
				<input type="submit" class="button" value="导出">
			</form>
		</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="#">现场医生改名</a></td>
		<td align="left" class="item">
			将某个现场医生登录名，真实姓名修改掉。会将当前医院的xiangchang_doctor字段也改变。<br>
			<form action="?" method="POST" style="display:inline;" onsubmit="return confirm_op();">
				要修改人员当前真实姓名：<input name="from_real_name" size="12" class="input">&nbsp;&nbsp;
				登录名改为：<input name="to_login_name" size="12" class="input">&nbsp;&nbsp;
				真实姓名改为：<input name="to_real_name" size="12" class="input">&nbsp;&nbsp;
				<input type="submit" class="button" value="确定">
				<input type="hidden" name="op" value="rename_xianchang_doctor">
			</form>
		</td>
	</tr>
	<tr>
		<td align="center" class="item"><?php echo $index++; ?></td>
		<td align="left" class="item"><a href="#">主治医生改名</a></td>
		<td align="left" class="item">
			将某个主治医生登录名，真实姓名修改掉。会将当前医院的doctor字段也改变。<br>
			<form action="?" method="POST" style="display:inline;" onsubmit="return confirm_op();">
				要修改人员当前真实姓名：<input name="from_real_name" size="12" class="input">&nbsp;&nbsp;
				登录名改为：<input name="to_login_name" size="12" class="input">&nbsp;&nbsp;
				真实姓名改为：<input name="to_real_name" size="12" class="input">&nbsp;&nbsp;
				<input type="submit" class="button" value="确定">
				<input type="hidden" name="op" value="rename_doctor">
			</form>
		</td>
	</tr>
</table>


</body>
</html>