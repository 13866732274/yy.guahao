<?php
// --------------------------------------------------------
// - 功能说明 : 检查重复数据
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2012-03-03
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_web";

if (!($debug_mode || $uinfo["part_id"] == 9)) {
	exit("没有权限");
}

$cur_type = $_SESSION["count_type_id_web"];
if (!$cur_type) {
	$type_ids = array_keys($types);
	$cur_type = $_SESSION["count_type_id_web"] = $type_ids[0];
}

if (!($cur_type > 0)) {
	exit("医院项目没有选择");
}

$op = $_REQUEST["op"];
if ($op == "repeat_del") {
	$rc = $_GET["str"];
	if ($rc != '') {
		list($a, $b, $c) = explode("_", $rc, 3);
		$rs_arr = $db->query("select * from count_web where type_id=".intval($a)." and date=".intval($b)." and kefu='".$c."' order by id asc", "");
		if (count($rs_arr) > 1) {
			for ($i = 1; $i < count($rs_arr); $i++) {

				// 备份是必须的:
				$back = @serialize($rs_arr[$i]);
				@file_put_contents("count_repeat_log.txt", date("Y-m-d H:i:s ").$realname." ".$back."\r\n", FILE_APPEND);

				$cur_id = $rs_arr[$i]["id"];
				$db->query("delete from count_web where id=$cur_id limit 1");
			}
			msg_box("处理成功", "back", 1, 2);
		} else {
			exit_html("未查询到重复数据，请联系其他管理人员确认是否已经被处理过了。");
		}
	} else {
		exit_html("参数不正确");
	}
	exit;
}


// 更新重复数据字典字段:
$db->query("update count_web set repeatcheck=concat(type_id,'_',date,'_',kefu) where repeatcheck='' ");

// 检查重复数据:
$list = $db->query("select * from (select type_name,date,kefu,repeatcheck,count(repeatcheck) as c from `count_web` where repeatcheck!='' group by repeatcheck order by c desc) as t where t.c>1");

if (count($list) == 0) {
	exit_html("在所有项目、所有日期的网络数据中均未发现重复数据。");
}


// 页面开始 ------------------------
?>
<html>
<head>
<title>重复数据检查</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>
<style>
body {padding:5px 8px; }
form {display:inline; }
</style>
<script type="text/javascript">
function do_u_confirm() {
	return confirm("是否确认要将重复的数据删除？");
}
</script>
</head>

<body>
<table class="headers" width="100%">
	<tr>
		<td class="headers_title"><nobr class="tips">重复数据检查</nobr></td>
		<td class="headers_oprate"><button onclick="self.location.reload()" class="button">刷新</button></td>
	</tr>
</table>

<div class="space"></div>
<table width="100%" align="center" class="list">
	<tr>
		<td class="head" align="center">重复次数</td>
		<td class="head" align="center">项目名称</td>
		<td class="head" align="center">日期</td>
		<td class="head" align="center">客服</td>
		<td class="head" align="center">操作</td>
	</tr>
<?php foreach ($list as $v) { ?>
	<tr>
		<td class="item" align="center"><?php echo $v["c"]; ?></td>
		<td class="item" align="center"><?php echo $v["type_name"]; ?></td>
		<td class="item" align="center"><?php echo $v["date"]; ?></td>
		<td class="item" align="center"><?php echo $v["kefu"]; ?></td>
		<td class="item" align="center"><a href="?op=repeat_del&str=<?php echo $v["repeatcheck"]; ?>" onclick="return do_u_confirm()">处理</a></td>
	</tr>
<?php } ?>

</table>

<br>

<div style="text-align:right;">点“处理”，将自动把多余的重复数据删除。&nbsp;</div>

<br>

</body>
</html>
