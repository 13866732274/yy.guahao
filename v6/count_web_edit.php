<?php
// --------------------------------------------------------
// - 功能说明 : 添加、修改资料
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2010-10-08 13:31
// --------------------------------------------------------

$date = $_REQUEST["date"];
if (!$date) {
	exit("参数错误");
}

$kefu = $_GET["kefu"];
if (!$kefu) {
	exit("参数错误");
}

if ($_POST) {
	$r = array();

	// 判断是否已经添加:
	$mode = "add";
	$s_date = date("Ymd", strtotime($date." 0:0:0"));
	$kefu = $_POST["kefu"];
	$cur_id = $db->query("select id from $table where type_id=$cur_type and kefu='$kefu' and date='$s_date' limit 1", 1, "id");
	if ($cur_id > 0) {
		$mode = "edit";
		$id = $cur_id;
	}

	if ($mode == "add") {
		$r["type_id"] = $cur_type;
		$r["type_name"] = $db->query("select name from count_type where id=".$r["type_id"]." limit 1", 1, "name");
		$r["date"] = $s_date;
		$r["kefu"] = $_POST["kefu"];
	}

	$r["all_click"] = $_POST["all_click"];
	$r["click"] = $_POST["click"];
	$r["click_local"] = $_POST["click_local"];
	$r["click_other"] = $_POST["click_other"];

	$r["ok_click"] = $_POST["ok_click"];
	$r["ok_click_local"] = $_POST["ok_click_local"];
	$r["ok_click_other"] = $_POST["ok_click_other"];

	$r["talk_swt"] = $_POST["talk_swt"];
	$r["talk_tel"] = $_POST["talk_tel"];
	$r["talk_other"] = $_POST["talk_other"];
	$r["talk"] = $r["talk_swt"] + $r["talk_tel"] + $r["talk_other"];

	$r["orders_swt"] = $_POST["orders_swt"];
	$r["orders_tel"] = $_POST["orders_tel"];
	$r["orders_other"] = $_POST["orders_other"];
	$r["orders"] = $r["orders_swt"] + $r["orders_tel"] + $r["orders_other"];

	$r["come"] = $_POST["come"];
	$r["come_tel"] = $_POST["come_tel"];
	$r["come_other"] = $_POST["come_other"];
	$r["come_all"] = $r["come"] + $r["come_tel"] + $r["come_other"];

	if ($mode == "add") {
		$r["addtime"] = time();
		$r["uid"] = $uid;
		$r["u_realname"] = $realname;
	}

	$sqldata = $db->sqljoin($r);
	if ($mode == "add") {
		$sql = "insert into $table set $sqldata";
	} else {
		$sql = "update $table set $sqldata where id='$id' limit 1";
	}

	if ($db->query($sql)) {
		if ($mode == "add") {
			echo '<script> parent.update_content(); </script>';
			echo '<script> parent.msg_box("添加成功", 2); </script>';
		} else {
			echo '<script> parent.msg_box("修改成功。列表未更新", 2); </script>';
		}
		echo '<script> parent.load_src(0); </script>';
	} else {
		echo "提交失败，请稍后再试！";
	}
	exit;
}

if ($op == "edit") {
	$s_date = date("Ymd", strtotime($date." 0:0:0"));
	$line = $db->query("select * from $table where type_id=$cur_type and kefu='$kefu' and date='$s_date' limit 1", 1);
}


// 检查名称设置是否正确:
$admin_info = $db->query("select * from sys_admin where realname='$kefu' order by isshow desc limit 1", 1);
$user_not_exists = count($admin_info) == 0 ? 1 : 0;


// 自动调用系统中的数据
$tb = strtotime($date);
$te = strtotime("+1 day", $tb) - 1;

$type_detail = $db->query("select * from count_type where id=$cur_type limit 1", 1);

// 范围:
if ($type_detail["data_hids"] == "") {
	$hospital_ids = array($hid);
} else if ($type_detail["data_hids"] == "-1") {
	$hospital_name = $hinfo["sname"];
	$hospital_ids = $db->query("select id from hospital where ishide=0 and sname='$hospital_name' order by id asc", "", "id");
} else {
	$hospital_ids = explode(",", $type_detail["data_hids"]);
}

//print_r($hospital_ids);

foreach ($hospital_ids as $hid) {
	$kf["yuyue_swt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and addtime>=$tb and addtime<=$te and order_soft in ('swt', 'kst')", 1, "c");
	$kf["yuyue_dh"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and addtime>=$tb and addtime<=$te and order_soft in ('dh')", 1, "c");
	$kf["yuyue_qt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and addtime>=$tb and addtime<=$te and order_soft not in ('swt', 'kst', 'dh')", 1, "c");

	$kf["yudao_swt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and order_date>=$tb and order_date<=$te and order_soft in ('swt', 'kst')", 1, "c");
	$kf["yudao_dh"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and order_date>=$tb and order_date<=$te and order_soft in ('dh')", 1, "c");
	$kf["yudao_qt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and order_date>=$tb and order_date<=$te and order_soft not in ('swt', 'kst', 'dh')", 1, "c");

	$kf["shidao_swt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and status=1 and order_date>=$tb and order_date<=$te and order_soft in ('swt', 'kst')", 1, "c");
	$kf["shidao_dh"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and status=1 and order_date>=$tb and order_date<=$te and order_soft in ('dh')", 1, "c");
	$kf["shidao_qt"] += $db->query("select count(*) as c from patient_{$hid} where author='$kefu' and status=1 and order_date>=$tb and order_date<=$te and order_soft not in ('swt', 'kst', 'dh')", 1, "c");
}


$title = $op == "edit" ? "修改资料" : "添加资料";
?>
<html>
<head>
<title><?php echo $title; ?> (<?php echo $date; ?>: <?php echo $kefu; ?>)</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
.item {padding:8px 3px 6px 3px; }
</style>

<script language="javascript">
function check_data() {
	var oForm = document.mainform;
	if (oForm.code.value == "") {
		alert("请输入“编号”！"); oForm.code.focus(); return false;
	}
	byid("submit").value = "提交中...";
	byid("submit").disabled = true;
	return true;
}

function update_cnt(o, id_a, id_b, id_c) {
	var a = byid(id_a).value;
	var b = byid(id_b).value;
	var c = byid(id_c).value;

	var cnt = (a != "" ? 1 : 0) + (b != "" ? 1 : 0) + (c != "" ? 1 : 0);

	if (cnt == 2 && (a == "" || b == "" || c == "")) {
		if (a == "") {
			byid(id_a).value = parseInt(b) + parseInt(c);
		} else if (b == "") {
			byid(id_b).value = a - c;
		} else {
			byid(id_c).value = a - b;
		}
	}
	if (cnt == 3) {
		if (o.id == id_a) {
			byid(id_c).value = a - b;
		} else if (o.id == id_b) {
			byid(id_c).value = a - b;
		} else {
			byid(id_b).value = a - c;
		}
	}
}


</script>
</head>

<body>
<div class="description">
	<div class="d_title">提示：</div>
	<div class="d_item">按要求输入各项资料，点击提交即可</div>
</div>

<div class="space"></div>

<form name="mainform" action="" method="POST" onsubmit="return check_data()">
<table width="100%" class="edit">
	<tr>
		<td colspan="2" class="head">网络详细资料</td>
	</tr>

	<tr>
		<td class="left">所有点击：</td>
		<td class="right">
			<input name="all_click"  value="<?php echo $line["all_click"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left">总点击：</td>
		<td class="right">
			<input name="click" id="c_a" onchange="update_cnt(this,'c_a', 'c_b', 'c_c')" value="<?php echo $line["click"]; ?>" class="input" style="width:100px">
			　=　本地：<input name="click_local" id="c_b" onchange="update_cnt(this,'c_a', 'c_b', 'c_c')" value="<?php echo $line["click_local"]; ?>" class="input"style="width:100px">
			　+　外地：<input name="click_other" id="c_c" onchange="update_cnt(this,'c_a', 'c_b', 'c_c')" value="<?php echo $line["click_other"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left">总有效：</td>
		<td class="right">
			<input name="ok_click" id="d_a" onchange="update_cnt(this,'d_a', 'd_b', 'd_c')" value="<?php echo $line["ok_click"]; ?>" class="input" style="width:100px">
			　=　本地：<input name="ok_click_local" id="d_b" onchange="update_cnt(this,'d_a', 'd_b', 'd_c')" value="<?php echo $line["ok_click_local"]; ?>" class="input"style="width:100px">
			　+　外地：<input name="ok_click_other" id="d_c" onchange="update_cnt(this,'d_a', 'd_b', 'd_c')" value="<?php echo $line["ok_click_other"]; ?>" class="input" style="width:100px">
		</td>
	</tr>

	<tr>
		<td class="left">预约：</td>
		<td class="right">
			商务通：<input name="talk_swt" value="<?php echo $op == "add" ? $kf["yuyue_swt"] : $line["talk_swt"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yuyue_swt"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			电话：<input name="talk_tel" value="<?php echo $op == "add" ? $kf["yuyue_dh"] : $line["talk_tel"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yuyue_dh"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			其它：<input name="talk_other" value="<?php echo $op == "add" ? $kf["yuyue_qt"] : $line["talk_other"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yuyue_qt"].")"; ?>
		</td>
	</tr>

	<tr>
		<td class="left">预到：</td>
		<td class="right">
			商务通：<input name="orders_swt" value="<?php echo $op == "add" ? $kf["yudao_swt"] : $line["orders_swt"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yudao_swt"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			电话：<input name="orders_tel" value="<?php echo $op == "add" ? $kf["yudao_dh"] : $line["orders_tel"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yudao_dh"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			其它：<input name="orders_other" value="<?php echo $op == "add" ? $kf["yudao_qt"] : $line["orders_other"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["yudao_qt"].")"; ?>
		</td>
	</tr>

	<tr>
		<td class="left">实到：</td>
		<td class="right">
			商务通：<input name="come" value="<?php echo $op == "add" ? $kf["shidao_swt"] : $line["come"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["shidao_swt"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			电话：<input name="come_tel" value="<?php echo $op == "add" ? $kf["shidao_dh"] : $line["come_tel"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["shidao_dh"].")"; ?>&nbsp;&nbsp;&nbsp;&nbsp;
			其它：<input name="come_other" value="<?php echo $op == "add" ? $kf["shidao_qt"] : $line["come_other"]; ?>" class="input" style="width:100px"> <?php if ($op == "edit") echo "(".$kf["shidao_qt"].")"; ?>
		</td>
	</tr>
</table>
<?php if ($user_not_exists) { ?>
<center style="margin-top:5px; color:red">(系统中未查询到<b><?php echo $kefu; ?></b>，请检查名字是否有误。)</center>
<?php } ?>

<?php if ($op == "add") { ?>
<center style="margin-top:5px;">(注：自动调用的数据从<b><?php echo count($hospital_ids); ?></b>个科室中提取)</center>
<?php } else { ?>
<center style="margin-top:5px;">(注：编辑框后括号内数据为<b><?php echo count($hospital_ids); ?></b>个科室中实时查询的结果，供对比参考)</center>
<?php } ?>
<input type="hidden" name="linkinfo" value="<?php echo $linkinfo; ?>">
<input type="hidden" name="op" value="<?php echo $op; ?>">
<input type="hidden" name="date" value="<?php echo date("Y-m-d", strtotime($date." 0:0:0")); ?>">
<input type="hidden" name="kefu" value="<?php echo $kefu; ?>">

<div class="button_line"><input type="submit" id="submit" class="submit" value="提交数据"></div>
</form>
</body>
</html>