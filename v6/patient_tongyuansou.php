<?php
// --------------------------------------------------------
// - 功能说明 : 同院搜
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-9-25
// --------------------------------------------------------
require "lib/set_env.php";

if ($_GET["hid"] > 0) {
	$hid = $user_hospital_id = $_SESSION[$cfgSessionName]["hospital_id"] = intval($_GET["hid"]);
}

$hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);

// 需要先设置正确的医院名，否则无法查询同医院的其他科室:
$real_hname = $hinfo["sname"];
if ($real_hname == '') {
	exit_html("对不起，当前医院未设置有效的医院名，不能使用此功能！");
}

// 查询与当前医院相同的其他科室：
$area = $hinfo["area"];
$s_list = $db->query("select * from hospital where ishide=0 and area='$area' and sname='$real_hname' order by name asc", "id");
if (count($s_list) <= 1) {
	exit_html("对不起，当前医院没有其他科室，请直接搜索，无需使用“同院搜”功能！");
}

// 检查权限：
if ($username != 'admin' && !$debug_mode) {
	$my_hlist = @explode(",", $uinfo["hospitals"]);
	foreach ($s_list as $_hid => $_hinfo) {
		if (!in_array($_hid, $my_hlist)) {
			unset($s_list[$_hid]);
		}
	}
	if (count($s_list) <= 1) {
		exit_html("对不起，您的科室授权不够，无法使用“同院搜”功能！");
	}
}


// 搜索关键词：
$key = $_GET["key"];
if ($_GET["code"] == "utf8") {
	$key = iconv("UTF-8", "GBK", $key);
}
$key = trim(wee_safe_key($key));


// 搜索所有有权限的科室：
if (strlen($key) > 3) {
	$h_res = array();
	foreach ($s_list as $_hid => $_hinfo) {
		$table = "patient_".$_hid;
		$h_res[$_hid] = $db->query("select * from $table where concat(name,'_',tel,'_',yuyue_num,'_',content,'_',memo) like '%{$key}%' order by id desc limit 10", "id");
	}

	// 字典:
	$part_id_name = $db->query("select id,name from sys_part", "id", "name");
	$disease_id_name = $db->query("select id,name from disease", "id", "name");


	// 所有查询的医院名
	$h_str = array();
	foreach ($s_list as $_hid => $_hinfo) {
		$h_str[] = $_hinfo["name"];
	}
	$h_str = implode("、", $h_str);

}


// 日志 @ 2012-07-23
//$log_str = "[".date("Y-m-d H:i")."] [".$realname."] 在 [".$h_str."] 中搜索 [".$key."]\r\n";
//@file_put_contents(dirname(__FILE__)."/tongyuansou.log", $log_str, FILE_APPEND);


function _tongyuansou_text_show($s, $len = 20) {
	$s = strip_tags($s);
	$s = str_replace("\r", "", $s);
	$s = str_replace("\n", " ", $s);
	$s = str_replace("\t", "  ", $s);
	$s = cut($s, $len, "…");
	$s = str_replace(" ", "&nbsp;", $s);
	return $s;
}

?>
<html>
<head>
<title>同院搜：<?php echo $key; ?></title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style type="text/css">
.cur_hname {margin-top:10px; font-family:"微软雅黑"; color:#ff00ff; }
.search_res {border:2px solid #ade0ba; margin-top:2px; }
.search_res td {border:0px solid #c5ddef; border-left:0; border-right:0; padding:4px auto 3px auto; }
.search_res .head {padding:3px 3px 2px 3px; font-family:"微软雅黑"; color:#99afd0; text-align:center; background:#efefef; border:1px solid #cecece; border-top:0; border-bottom:0; }
.search_res .item {padding:4px 3px 2px 3px; text-align:center; border:1px solid #cecece; border-left:0; border-right:0; }
.no_data {line-height:40px; text-align:center; color:gray; border-top:1px solid #cecece !important; }
.yahei, .yahei * {font-family:"微软雅黑"; }
.noyahei {font-family:"宋体" !important; }
</style>
<script type="text/javascript">
global_param = '';

function goto_id(hid, param) {
	global_param = param;
	load_js("change_hospital.php?op=tys_change&hid="+hid, "change_hospital");
}

function goto_id_do() {
	var base_url = "patient.php";
	var url = base_url+"?"+global_param;
	parent.byid("sys_frame").src = url;
	parent.load_src(0);
}
</script>
</head>

<body>

<form name="mainform" action="?" method="GET" class="yahei">
	<b>请输入姓名或手机号进行搜索：</b>
	<input name="key" class="input noyahei" style="width:150px" value="<?php echo $key; ?>">
	<input type="submit" class="button" value="搜索">
	<input type="hidden" name="op" value="tongyuansou" />
</form>

<?php if ($h_res) { ?>
<div class="space"></div>

<div class="res">
	<div class="res_num yahei">在 <b style="color:red;"><?php echo $h_str; ?></b> 中搜索，每科室最多返回 <b>10</b> 条最新的记录，结果如下：</div>
<?php
	foreach ($s_list as $_hid => $_hinfo) {
		$cur_hname = $_hinfo["name"];
		$list = $h_res[$_hid];
?>
	<div class="cur_hname"><?php echo $cur_hname; ?>：</div>
	<table width="100%" class="search_res" cellpadding="0" cellspacing="0">
		<tr>
			<td class="head">姓名</td>
			<td class="head">性别</td>
			<td class="head">年龄</td>
			<td class="head">疾病</td>
			<td class="head">电话</td>
			<td class="head">媒体来源</td>
			<td class="head">咨询内容</td>
			<td class="head">备注</td>
			<td class="head">预约时间</td>
			<td class="head">状态</td>
			<td class="head">添加人</td>
			<td class="head">添加时间</td>
			<td class="head">操作</td>
		</tr>

<?php
		if (count($list) > 0) {
			foreach ($list as $id => $li) {
				// 姓名后面写括号的处理办法 @ 2012-07-10
				if (strlen($li["name"]) > 6) {
					if (substr_count($li["name"], "（") > 0) {
						$li["name"] = str_replace("（", "<br>（", $li["name"]);
					}
					if (substr_count($li["name"], "(") > 0) {
						$li["name"] = str_replace("(", "<br>(", $li["name"]);
					}
				}

?>

		<tr>
			<td class="item"><b style="color:#9f0000"><?php echo $li["name"]; ?></b></td>
			<td class="item"><?php echo $li["sex"]; ?></td>
			<td class="item"><?php echo $li["age"] > 0 ? $li["age"] : ""; ?></td>
			<td class="item"><?php echo _tongyuansou_text_show($disease_id_name[$li["disease_id"]], 12); ?></td>
			<td class="item"><?php echo tel_filter($li); ?></td>
			<td class="item"><?php echo $li["media_from"]; ?></td>
			<td class="item"><?php echo _tongyuansou_text_show($li["content"]); ?></td>
			<td class="item"><?php echo _tongyuansou_text_show($li["memo"]); ?></td>
			<td class="item"><?php echo date("Y-m-d", $li["order_date"]); ?></td>
			<td class="item"><?php echo $li["status"] == 1 ? '<font color="red">已到</font>' : '未到'; ?></td>
			<td class="item"><?php echo $li["author"]; ?></td>
			<td class="item"><?php echo date("Y-m-d", $li["addtime"]); ?></td>
			<td class="item"><a href="#" onclick="goto_id(<?php echo $_hid; ?>, 'callid=<?php echo $li["id"]; ?>&crc=<?php echo $li["addtime"]; ?>&searchword=<?php echo urlencode($key); ?>'); return false;" title="点击进入此医院查看病人">进入</a></td>
		</tr>

<?php
			}
		} else {
?>
		<tr>
			<td class="no_data" colspan="13">(未搜到数据)</td>
		</tr>
<?php
		}
?>
	</table>
<?php
	}
?>


</div>

<?php } ?>

</body>
</html>