<?php
/*
// 说明: 报表核心数据定义
// 作者: 幽兰 (weelia@126.com)
*/

if (!$debug_mode) {
	//exit("程序调整，请稍后使用。");
}

if (empty($hid)) {
	exit_html("对不起，没有选择医院，请先在首页选择医院！");
}

$table = "patient_{$hid}";
if ($_GET["op"] == "report") {
	//----------
}

$h_name = $db->query("select name from hospital where id=$hid limit 1", 1, "name");

// 统计结果类型
$type_arr = array(1 => "按年统计", 2 => "按月统计", /*3=>"按周统计(实现中)",*/ 4 => "按日统计", 5 => "按时间段统计");

// 部门
$part_arr = array(2 => "网络", 3 => "电话");

// 来院状态
$come_arr = array(1 => "已到", 2 => "未到");

// 帐号:
$account_arr = $account_array; //使用系统定义

// 时间格式
$timetype_arr = array("order_date" => "到院时间", "addtime" => "添加时间");

// 客服 下面方法效率差，还是按需调用的好 (如果从人员表中取，怕有些客服离职被删除了，但其数据还在):
if ($need_kf_arr) {
	$kf_arr = $db->query("select author,count(author) as c from $table where addtime>" . strtotime("-1 year") . " group by author order by c desc", "author", "c");
}

// 媒体来源:
$media_arr = array("网络", "电话"); //内置媒体来院
$media_arr2 = $db->query("select name from media where (hospital_id=0 or hospital_id=$hid) order by sort desc,id asc", "", "name");
$media_arr = array_merge($media_arr, $media_arr2);

// 可用 年,月 数组
$y_array = $m_array = $d_array = array();
for ($i = date("Y"); $i >= (date("Y") - 3); $i--) $y_array[] = $i;
for ($i = 1; $i <= 12; $i++) $m_array[] = $i;
for ($i = 1; $i <= 31; $i++) $d_array[] = $i;


/*
// 对查询条件的处理------------------------
*/
if ($_GET["op"] == "report") {

	// 将参数记录到 session，别的几个报表使用同样条件
	$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;

	// 统计结果类型:
	$type = noe($_GET["type"], 1);
	$type_tips = $type_arr[$type];


	$final_dt_arr = array(); //包含时间起止点和key的数组，最终用于循环查询和输出
	if ($type == 1) { //年
		$y_begin = $_GET["b_year"];
		$y_end = $_GET["e_year"];

		// 最大化时间范围:
		$tb = $max_tb = strtotime("{$y_begin}-01-01 00:00:00");
		$te = $max_te = strtotime("{$y_end}-12-31 23:59:59");

		$y_arr = array();
		if ($y_begin == $y_end) {
			$y_arr = array($y_begin);
			$final_dt_arr[$y_begin] = array(strtotime("{$y_begin}-01-01 00:00:00"), strtotime("{$y_begin}-12-31 23:59:59"));
		} else {
			for ($i = $y_end; $i >= $y_begin; $i--) {
				$y_arr[] = $i;
				$final_dt_arr[$i] = array(strtotime("{$i}-01-01 00:00:00"), strtotime("{$i}-12-31 23:59:59"));
			}
		}
	} else if ($type == 2) { //月
		$m_begin = $_GET["b_year"] . "-" . str_pad($_GET["b_month"], 2, '0', STR_PAD_LEFT);
		$m_end = $_GET["e_year"] . "-" . str_pad($_GET["e_month"], 2, '0', STR_PAD_LEFT);

		// 最大化时间范围:
		$tb = $max_tb = strtotime("{$m_begin}-01 00:00:00");
		$te = $max_te = strtotime("+1 month", strtotime("{$m_end}-01 00:00:00")) - 1;

		$m_arr = array();
		if ($m_begin == $m_end) {
			$m_arr = array($m_begin);
			$final_dt_arr[$m_begin] = array(strtotime("{$m_begin}-01 00:00:00"), (strtotime("+1 month", strtotime("{$m_begin}-01 00:00:00") - 1)));
		} else {
			$tmp = 0;
			do {
				////有潜在问题，如果日期选择为31日，-1 month就会有问题了
				// 修改 2012-01-31
				$m_arr[] = $_dt = date("Y-m", strtotime("-" . $tmp . " month", mktime(0, 0, 0, date("m", $te), 1, date("Y", $te))));
				$final_dt_arr[$_dt] = array(strtotime("{$_dt}-01 00:00:00"), (strtotime("+1 month", strtotime("{$_dt}-01 00:00:00")) - 1));
				$tmp++;
				if ($tmp > 24) {
					$html_tip .= "按月统计目前限定不能超过25个月，日期已自动修正。";
					$tb = $max_tb = strtotime("{$_dt}-01 00:00:00");
					$_GET["b_year"] = date("Y", $tb);
					$_GET["b_month"] = date("n", $tb);
					$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;
					break; //限制最多x个月
				}
			} while (intval(str_replace("-", "", $_dt)) > intval(str_replace("-", "", $m_begin)));
		}
	} else if ($type == 3) { //周
		exit_html("按周统计功能延后开发中....");
	} else if ($type == 4) { //天
		$d_begin = $_GET["b_year"] . "-" . str_pad($_GET["b_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["b_day"], 2, '0', STR_PAD_LEFT);
		$d_end = $_GET["e_year"] . "-" . str_pad($_GET["e_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["e_day"], 2, '0', STR_PAD_LEFT);

		// 最大化时间范围:
		$tb = $max_tb = strtotime("{$d_begin} 00:00:00");
		$te = $max_te = strtotime("{$d_end} 23:59:59");

		// 日期选择的错误问题，比如2月本来没有31号，却选择了31号的情况
		$html_tip = "";
		if (date("Y-m-d", $tb) != $d_begin) {
			$html_tip .= "起始时间" . $_GET["b_year"] . "年" . $_GET["b_month"] . "月没有" . $_GET["b_day"] . "号，已自动修正。";
			$_GET["b_day"] = date("j", strtotime("+1 month", mktime(0, 0, 0, $_GET["b_month"], 1, $_GET["b_year"])) - 1);
			$d_begin = $_GET["b_year"] . "-" . str_pad($_GET["b_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["b_day"], 2, '0', STR_PAD_LEFT);
			$tb = $max_tb = strtotime("{$d_begin} 00:00:00");
			$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;
		}
		if (date("Y-m-d", $te) != $d_end) {
			$html_tip .= "结束时间" . $_GET["e_year"] . "年" . $_GET["e_month"] . "月没有" . $_GET["e_day"] . "号，已自动修正。";
			$_GET["e_day"] = date("j", strtotime("+1 month", mktime(0, 0, 0, $_GET["e_month"], 1, $_GET["e_year"])) - 1);
			$d_end = $_GET["e_year"] . "-" . str_pad($_GET["e_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["e_day"], 2, '0', STR_PAD_LEFT);
			$te = $max_te = strtotime("{$d_end} 00:00:00");
			$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;
		}

		$d_arr = array();
		if ($d_begin == $d_end) {
			$d_arr = array($d_begin);
			$final_dt_arr[$d_begin] = array(strtotime("{$d_begin} 00:00:00"), strtotime("{$d_begin} 23:59:59"));
		} else {
			$tmp = 0;
			do {
				$d_arr[] = $_dt = date("Y-m-d", strtotime("-" . $tmp . " day", $te));
				$final_dt_arr[$_dt] = array(strtotime("{$_dt} 00:00:00"), strtotime("{$_dt} 23:59:59"));
				$tmp++;
				if ($tmp > 31) {
					$html_tip .= "按日统计目前限定不能超过32天，日期已自动修正。";
					$tb = $max_tb = strtotime("{$_dt} 00:00:00");
					$_GET["b_year"] = date("Y", $tb);
					$_GET["b_month"] = date("n", $tb);
					$_GET["b_day"] = date("j", $tb);
					$_SESSION[$cfgSessionName]["rp_condition"] = $_GET;
					break; //限制最多x天
				}
			} while (intval(str_replace("-", "", $_dt)) > intval(str_replace("-", "", $d_begin)));
		}
	} else if ($type == 5) { //时段(叠加)
		$d_begin = $_GET["b_year"] . "-" . str_pad($_GET["b_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["b_day"], 2, '0', STR_PAD_LEFT);
		$d_end = $_GET["e_year"] . "-" . str_pad($_GET["e_month"], 2, '0', STR_PAD_LEFT) . "-" . str_pad($_GET["e_day"], 2, '0', STR_PAD_LEFT);

		// 最大化时间范围:
		$tb = $max_tb = strtotime("{$d_begin} 00:00:00");
		$te = $max_te = strtotime("{$d_end} 23:59:59");

		$sd_arr = array();
		for ($i = 6; $i <= 23; $i++) {
			$sd_arr[] = $i;
			$final_dt_arr[$i . "~" . ($i + 1)] = $i;
		}
	}


	// 时间类型:
	$timetype = noe($_GET["timetype"], "order_date");
	$timetype_tips = $timetype_arr[$timetype];


	// 条件限定：
	$w = array();
	if ($_GET["part"]) {
		$w[] = "part_id=" . intval($_GET["part"]);
	}
	if ($_GET["media"]) {
		$w[] = "media_from='" . $_GET["media"] . "'";
	}
	if ($_GET["come"]) {
		if ($_GET["come"] == 1) {
			$w[] = "status=1";
		} else {
			$w[] = "status!=1";
		}
	}
	if ($_GET["account"]) {
		$w[] = "account='" . $_GET["account"] . "'";
	}

	$where = '';
	if (count($w) > 0) {
		$where = implode(" and ", $w) . " and ";
	}
}