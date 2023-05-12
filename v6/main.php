<?php
//
// - 功能说明 : 首页
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2009-10-01 => 2013-01-07
//
require "lib/set_env.php";
define("BRDD_MAIN", 1);

// 是否显示come list汇总表的开关
$show_list_all = $config["show_list_all"] ? 1 : 0;
if ($debug_mode) {
	$show_list_all = 1;
}


// 关闭搜索区域 @ 2016-10-13
if ($op == "hide_search") {
	$index_config = @unserialize($uinfo["index_config"]);
	$index_config["hide_search"] = 1;
	$config_str = serialize($index_config);
	if ($uid > 0) {
		$db->query("update sys_admin set index_config='" . $config_str . "' where id='" . $uid . "' limit 1");
	} else {
		$_SESSION["index_config"] = $config_str;
	}
	echo '<script> alert("搜索区域已经关闭，如有需要还可以在“首页定制”中打开。"); self.location = "?"; </script>';
	exit;
}


// 切换医院
$_hid = $_GET["_tohid_"];
if (isset($_hid) && $_hid != '') {
	// 切换权限检查:
	if ($_hid == "all") {
		$_SESSION[$cfgSessionName]["hospital_id"] = "";
	} else {
		if (@in_array($_hid, $hospital_ids)) {
			$_SESSION[$cfgSessionName]["hospital_id"] = intval($_hid);
		}
	}
	header("location:main.php");
	exit;
}

$db->select_count = 0;

// 最新通知列表:
$notice_arr = array();

$manage_parts = $uinfo["part_manage"] ? explode(",", $uinfo["part_manage"]) : array();


$hid = $user_hospital_id;
$hid_str = trim(implode(',', $hospital_ids));
$hid_str = $hid_str == "" ? "0" : $hid_str;
if ($hid_str != '0') {
	$hospital_list = $db->query("select id,name,color from hospital where ishide=0 and id in (" . $hid_str . ") order by sort desc, name asc", 'id');
} else {
	$hospital_list = array();
}

// 自动切换到第一个科室 @ 2016-10-13
if (!$show_list_all && $_SESSION[$cfgSessionName]["hospital_id"] == "" && count($hospital_list) > 0) {
	$_all_hids = array_keys($hospital_list);
	$hid = $user_hospital_id = $_SESSION[$cfgSessionName]["hospital_id"] = $_all_hids[0];
}

$h_name = '';
if ($hid > 0) {
	$h_name = $hospital_list[$hid]["name"];
	$h_info = $db->query("select * from hospital where id=$hid limit 1", 1);
}

// 部门id => name数组:
$part_id_name = $db->query("select id,name from sys_part", 'id', 'name');
$character_id_name = $db->query("select id,name from sys_character", 'id', 'name');

if ($debug_mode) {
	$index_config = (array) @unserialize($_SESSION["index_config"]);
	if (!isset($index_config["global_hide"])) {
		$index_config["global_hide"] = array("youhua", "yibao");
	}
} else {
	$index_config = (array) @unserialize($uinfo["index_config"]);
}


if ($hid > 0) {
	$disease_id_name = $db->query("select id,name from disease where hospital_id=$hid ", "id", "name");
	$depart_id_name = $db->query("select id,name from depart where hospital_id=$hid order by id asc", "id", "name");
}


// 按分组进行整理
$hids = count($hospital_ids) > 0 ? implode(",", $hospital_ids) : "0";
$group_id_name = $db->query("select id,name from hospital_group order by sort desc, name asc", "id", "name");
$options = array();
foreach ($group_id_name as $_gid => $_gname) {
	$h_list = $db->query("select id,name,color from hospital where ishide=0 and group_id=$_gid and id in ($hids) order by sort desc, name asc", "id");
	if (count($h_list) > 0) {
		$options[] = array('-1', $_gname . " (" . count($h_list) . ')', 'color:red');
		foreach ($h_list as $_hid => $_arr) {
			$options[] = array($_hid, '　' . $_arr["name"], ($_arr["color"] ? ('color:' . $_arr["color"]) : 'color:blue'));
		}
	}
}


// 时间定义 2011-12-28:
// 时间的起始点都是 YYYY-MM-DD 00:00:00 结束则是 YYYY-MM-DD 23:59:59
$today_tb = mktime(0, 0, 0); //今天开始
$today_te = strtotime("+1 day", $today_tb) - 1; //今天结束

$tomorrow_tb = $today_te + 1; //明天开始
$tomorrow_te = strtotime("+1 day", $tomorrow_tb) - 1; //明天结束

$yesterday_tb = strtotime("-1 day", $today_tb); //昨天开始
$yesterday_te = $today_tb - 1; //昨天结束

$month_tb = mktime(0, 0, 0, date("m"), 1); //本月开始
$month_te = strtotime("+1 month", $month_tb) - 1; //本月结束

$lastmonth_tb = strtotime("-1 month", $month_tb); //上月开始
$lastmonth_te = $month_tb - 1; //上月结束

$tb_tb = strtotime("-1 month", $month_tb); //同比时间开始
$tb_te = strtotime("-1 month", time()); //同比时间结束
if (date("d", $tb_te) != date("d")) {
	$tb_te = $month_tb - 1;
}

// 去年同月比
$yb_tb = strtotime("-1 year", $month_tb);
$_days = get_month_days(date("Y-m", $yb_tb));
if (date("j") > $_days) { //当前的日已经大于去年同月的天数了(比如29日和去年的28日)
	$yb_te = strtotime(date("Y-m-", $yb_tb) . $_days . date(" 23:59:59")); //对比为去年同月的整月
} else {
	$yb_te = strtotime(date("Y-m-", $yb_tb) . date("d H:i:s"));
}

// 数据查询依照此数组定义
$time_arr = array(
	"今日" => array($today_tb, $today_te),
	"昨日" => array($yesterday_tb, $yesterday_te),
	"本月" => array($month_tb, $today_te),
	"同期" => array($yb_tb, $yb_te),
	"上月" => array($lastmonth_tb, $lastmonth_te),
);

// 首页摘要内容：
$summary_info = '您好，<font color="#FF0000"><b>' . $realname . '</b></font>';
if ($uinfo["hospitals"] || $uinfo["part_id"] > 0) {
	if ($uinfo["part_id"] > 0) {
		$summary_info .= ' 身份：' . $part_id_name[$uinfo["part_id"]] . "";
	}
	if ($uinfo["character_id"] > 0) {
		$summary_info .= ' 权限：' . $character_id_name[$uinfo["character_id"]];
	}
}
$summary_info .= '　日期 <font color="red"><b>' . date("Y-m-d") . '</b></font>';
$summary_info .= '　星期<b><font color="red">' . substr("日一二三四五六", date("w") * 2, 2) . '</font></b>';
if (in_array($uinfo["part_id"], array(1, 9)) || $uinfo["part_admin"] || $debug_mode) {
	$onlines = $db->query("select count(*) as count from sys_admin where online=1", 1, "count");
	$summary_info .= '　在线人数 <font color="red"><b>' . $onlines . '</b></font> 人';
}

if ($debug_mode) {
	//$summary_info .= '<br><br><b style="color:#1696c7;font-size:14px;">接物业通知，6月12日晚22:30~1:30停电，届时服务器将无法访问，请提前做好准备。</b>';
}


// 是否已经选择了医院
if ($hid > 0) {

	$table = "patient_" . $hid;

	// 加载数据
	$data = $db->query("select data from index_cache where hid=$hid limit 1", 1, "data");
	$module_data_arr = @unserialize($data);

	// --------------------------

	//优化统计数据 @ 2013-03-15
	$isshow_youhua = 0;
	if ($config["show_youhua"]) {
		// 数据更新:
		$db->query("update youhua_data set yuyue=x1_yuyue+x2_yuyue+x3_yuyue+x4_yuyue+x5_yuyue+x6_yuyue+x7_yuyue+x8_yuyue+x9_yuyue where yuyue=0");
		$db->query("update youhua_data set daoyuan=x1_daoyuan+x2_daoyuan+x3_daoyuan+x4_daoyuan+x5_daoyuan+x6_daoyuan+x7_daoyuan+x8_daoyuan+x9_daoyuan where daoyuan=0");

		// 查询该医院是否最近两月有数据:
		$begin_date = date("Ymd", $lastmonth_tb);
		$end_date = date("Ymd");

		$data_count = $db->query("select sum(yuyue)+sum(daoyuan) as c from youhua_data where hid=$hid and date>=$begin_date and date<=$end_date", 1, "c");
		if ($data_count > 0) {
			$isshow_youhua = 1;
		}
	}

	if ($config["show_zixun_confirm_come"] || $debug_mode) {
		// 上月 咨询确认到院数据：
		$last_month = date("Ym", strtotime("-1 month", strtotime(date("Y-m-01 0:0:0"))));
		$come_confirm_data = $db->query("select * from come_confirm where hid=$hid and month='$last_month' limit 1", 1);

		// 网络咨询确认数据:
		$show_web_confirm = 0;
		if ($come_confirm_data["web"] != '') {
			$show_web_confirm = 1;
			$lastmonth_web_confirm = $come_confirm_data["web"];
		}

		// 电话咨询确认数据:
		$show_tel_confirm = 0;
		if ($come_confirm_data["tel"] != '') {
			$show_tel_confirm = 1;
			$lastmonth_tel_confirm = $come_confirm_data["tel"];
		}

		// 上月总确认到院数据：
		$show_all_confirm = 0;
		if ($come_confirm_data["all_come"] != '') {
			$show_all_confirm = 1;
			$lastmonth_all_confirm = $come_confirm_data["all_come"];
		}

		// 同比
		$zx_cfm_month = strtotime("-1 month", $yb_tb);
		$tb_int_month = date("Ym",  $zx_cfm_month);
		$zx_cfm_month_show = date("Y年n月",  $zx_cfm_month);
		$tb_come_confirm_data = $db->query("select * from come_confirm where hid=$hid and month='$tb_int_month' limit 1", 1);
		$tb_come_all = $tb_come_confirm_data["all_come"] > 0 ? $tb_come_confirm_data["all_come"] : "无";
		$tb_come_web = $tb_come_confirm_data["web"] > 0 ? $tb_come_confirm_data["web"] : "无";
		$tb_come_tel = $tb_come_confirm_data["tel"] > 0 ? $tb_come_confirm_data["tel"] : "无";
	} else {
		$show_all_confirm = 0;
		$show_web_confirm = 0;
		$show_tel_confirm = 0;
		$tb_come_all = 0;
		$tb_come_web = 0;
		$tb_come_tel = 0;
	}
}


// 新增功能的图标:
$new_icon = ' <img src="image/new.gif" align="absmiddle"> ';

// $notice_arr[] = '<font color="red">QQ统计数据也可以显示了，请在“首页设置”中勾选即可显示。</font>'.$new_icon."<br>";

// $notice_arr[] = '大小账号等数据已经调整，请在“首页设置”中勾选即可显示。<br>';

//$notice_arr[] = '本月统计到今天为止的数据（如今天7号，则统计的1号~7号数据）<br>';

// 处理咨询就诊率: 咨询就诊率 = 实际到院人数 / 总点击
if ($hid > 0) {
	$xiangmu_arr = $db->query("select id,name,kefu from count_type where type='web' and hid=$hid and ishide=0 order by id asc", "id");
	if (count($xiangmu_arr) > 0) {
		$t_m_begin = mktime(0, 0, 0, date("m"), 1); // 本月的开始:
		$t_m_end = strtotime("+1 month", $t_m_begin) - 1; //本月结束

		$_m_begin = date("Ymd", $t_m_begin);
		$_m_end = date("Ymd", $t_m_end);

		// 查询结果:
		$zixun_jiuzhen_arr = array();
		foreach ($xiangmu_arr as $xmid => $v) {
			$rs = $db->query("select sum(click) as click, sum(come) as come from count_web where type_id=$xmid and date>=$_m_begin and date<=$_m_end", 1);
			$zixun_jiuzhen_arr[$xmid] = @round(100 * $rs["come"] / $rs["click"], 1);
		}

		$zixun_jiuzhen_str = array();
		foreach ($zixun_jiuzhen_arr as $k => $v) {
			$zixun_jiuzhen_str[] = $xiangmu_arr[$k]["name"] . ': <a href="count_web.php?type_id=' . $k . '&op=change_type" title="点击进入查看"><font color="red">' . $v . "%</font></a> ";
		}
		$notice_arr[] = "<b>本月咨询就诊率</b>　" . implode(" ", $zixun_jiuzhen_str);

		// 点击预约率:
		$dianji_yuyue_arr = array();
		foreach ($xiangmu_arr as $xmid => $v) {
			$rs = $db->query("select sum(click) as a, sum(talk_swt) as b from count_web where type_id=$xmid and date>=$_m_begin and date<=$_m_end", 1);
			$dianji_yuyue_arr[$xmid] = @round(100 * $rs["b"] / $rs["a"], 1);
		}

		$dianji_yuyue_str = array();
		foreach ($dianji_yuyue_arr as $k => $v) {
			$dianji_yuyue_str[] = $xiangmu_arr[$k]["name"] . ': <a href="count_web.php?type_id=' . $k . '&op=change_type" title="点击进入查看"><font color="red">' . $v . "%</font></a> ";
		}
		$notice_arr[] = "<b>本月咨询预约率</b>　" . implode(" ", $dianji_yuyue_str);
	}
}


// 2013-8-30 几个比率
if ($hid > 0) {
	//if (($debug_mode || $config["show_qihua_detail"])) {
	$arr = array();
	$arr[] = "网挂预约就诊率: <font color=red>" . (@round($module_data_arr["ID_22"]["实到"]["本月"] / $module_data_arr["ID_22"]["预到"]["本月"], 3) * 100) . "%</font>";
	$arr[] = "网查预约就诊率: <font color=red>" . (@round($module_data_arr["ID_20"]["实到"]["本月"] / $module_data_arr["ID_20"]["预到"]["本月"], 3) * 100) . "%</font>";
	$arr[] = "网络无线预约就诊率: <font color=red>" . (@round($module_data_arr["ID_8"]["实到"]["本月"] / $module_data_arr["ID_8"]["预到"]["本月"], 3) * 100) . "%</font>";
	$arr[] = "电话无线预约就诊率: <font color=red>" . (@round($module_data_arr["ID_23"]["实到"]["本月"] / $module_data_arr["ID_23"]["预到"]["本月"], 3) * 100) . "%</font>";
	$notice_arr[] = implode(" &nbsp;", $arr) . "　(<a href='javascript:;' onclick='jiuzhenlv_huizong();'>点此查看本院汇总</a>)";
	//$notice_arr[] = '网挂=网络咨询，媒体来源是网络； 网查=电话咨询，媒体来源是网络';
	//}
}

if ($hid == 0 && $show_list_all) {
	include "main_list_all.php";
} else {
	include "main_directly.php";
}