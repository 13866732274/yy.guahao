<?php
// --------------------------------------------------------
// - 功能说明 : 回访统计报表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-07-09
// --------------------------------------------------------
require "lib/set_env.php";

if ($hid <= 0) {
	//$hid = $_SESSION[$cfgSessionName]["hospital_id"] = $hospital_ids[0];
	exit("请在右上角“切换医院”中选择医院查看。");
}

$huifang_time_length = strlen("2016-06-13 10:49"); //时间长度，用于截取
$edit_log_time_length = strlen("2016-06-12 18:25:18");

// 可选月份下拉:
$month_arr = array();
$month_arr[date("Y-m")] = "本月";
$t = strtotime(date("Y-m-01"));
for ($i=1; $i<=6; $i++) {
	$t2 = strtotime("-".$i." month", $t);
	$month_arr[date("Y-m", $t2)] = date("Y-m", $t2);
}

// 读取当前科室的电话客服:
$kefu_id_name_arr = $db->query("select id, realname from sys_admin where isshow=1 and part_id in (3,12) and character_id not in (16) and concat(',', hospitals, ',') like '%,{$hid},%' and concat(',',guahao_config,',') like '%,huifang,%' order by realname asc", "id", "realname");

$month = $_GET["month"];
if ($month == '') $month = date("Y-m"); //默认为本月

$month_begin = strtotime($month."-01");
$month_end = strtotime("+1 month", $month_begin) - 1;


$t_from = strtotime("-2 month", $month_begin);
$t_end = $month_end;


$huifang_num = array(); //回访次数
$gaiyue_num = array(); //改约次数
$gaiyue_num_dangri = array(); //当日改约

// 逐个客服进行数据分析:
foreach ($kefu_id_name_arr as $kf_id => $kf_name) {
	$list = $db->query("select id, name, huifang, edit_log, status, order_date from patient_{$hid} where order_date>=$t_from and order_date<=$t_end and concat(huifang, edit_log) like '% {$kf_name}%'");

	foreach ($list as $li) {
		$huifang_arr = wee_log_parse($li["huifang"], $huifang_time_length, $kf_name);
		foreach ($huifang_arr as $t => $c) {
			if ($t >= $month_begin && $t <= $month_end) {
				$huifang_num[$kf_name] ++;
				break;
			}
		}

		//2016-11-22 17:01:45 朱元元 将预约时间由【2016-03-06 14:00】修改为【2016-11-23 14:00】。

		$log_arr = wee_log_parse($li["edit_log"], $edit_log_time_length, $kf_name);
		$flag_string = "预约时间由";
		foreach ($log_arr as $t => $c) {
			if ($t >= $month_begin && $t <= $month_end && substr_count($c, $flag_string) > 0) {
				$gaiyue_num[$kf_name] ++;
				if ($li["status"] == 1) {
					$gaiyue_jiuzhen_num[$kf_name] ++; //改约就诊
				}

				// 判断是否当日改约:
				preg_match_all("/【([0-9\-\ \:]+?)】/", $c, $out);
				$t1 = $out[1][0];
				$t2 = $out[1][1];
				if ($t1 != "" && $t2 != "") {
					if (date("Ymd", strtotime($t1)) == date("Ymd", strtotime($t2))) {
						$gaiyue_num_dangri[$kf_name] ++; //当日改约
						if ($li["status"] == 1 && date("Ymd", strtotime($t1)) == date("Ymd", $li["order_date"])) {
							$gaiyue_jiuzhen_num_dangri[$kf_name] ++; //当日改约就诊
						}
					}
				}

				break;
			}
		}
	}
}


// 当月到诊人数统计:
$names = array();
foreach ($kefu_id_name_arr as $kf_id => $kf_name) {
	$names[] = "'".$kf_name."'";
}
$name_str = count($names) ? implode(", ", $names) : "0";
$dangri_daozhen_num = $db->query("select author, count(*) as c from patient_{$hid} where order_date>=$month_begin and order_date<=$month_end and status=1 and author in ($name_str) group by author", "author", "c");


function wee_log_parse($str, $time_length, $kf_name) {
	$str = trim(str_replace("\r", "", $str));
	$arr = explode("\n", $str);
	$return = array();
	foreach ($arr as $v) {
		if (trim($v) == '') continue;
		$t = substr($v, 0, $time_length);
		$t2 = @strtotime($t);
		$c2 = trim(substr($v, $time_length + 1));
		if ($t2 > 0 && $c2 != '' && substr($c2, 0, strlen($kf_name)) == $kf_name) {
			$return[$t2] = $c2;
		}
	}
	krsort($return);
	return $return;
}


?>
<html>
<head>
<title>回访统计报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
* {font-family:"微软雅黑"; }
.center_show {margin:0 auto; width:1000px; text-align:center; }
.list {border:2px solid silver; }
.head {color:#bf0060 !important; border:1px solid silver !important; background:#e1e7ec !important; }
.item {text-align:center; padding:4px !important; border-top:1px solid silver !important; border-bottom:1px solid silver !important; }
.line_huizong td {color:red; }
.report_tips {padding:20px 0 20px 0; text-align:center; font-size:16px; font-family:"微软雅黑"; }
.condition_set {text-align:right; }
</style>
<script type="text/javascript">
var month = "<?php echo $month; ?>";

function show_kf_detail(obj) {
	var kf_name = obj.innerHTML;
	parent.load_src(1, "/v6/patient_report_kf_detail.php?month="+month+"&j_kf_name="+encodeURIComponent(kf_name), 1000);
}

function show_hospital_all(obj) {
	parent.load_src(1, "/v6/patient_report_kf_all_tip.php?month="+month, 1000);
}
</script>
</head>

<body>

<div class="condition_set">
	<a href="javascript:;" onclick="show_hospital_all();">查看医院汇总</a>
	<form method="GET" action="" onsubmit="" style="display:inline; margin-left:20px;">
		切换月份：<select name="month" class="combo" onchange="this.form.submit()">
			<?php echo list_option($month_arr, "_key_", "_value_", $_GET["month"]); ?>
		</select>
	</form>
</div>

<div class="center_show">

	<div class="report_tips"><?php echo $hinfo["name"]; ?>　<?php echo $month; ?>　回访统计报表</div>

	<?php $per = round(90 / 6, 3)."%"; ?>
	<table class="list" width="100%">
		<tr>
			<th class="head" width="10%">客服姓名</th>
			<th class="head" width="<?php echo $per; ?>">回访人数</th>
			<th class="head" width="<?php echo $per; ?>">改约人数</th>
			<th class="head" width="<?php echo $per; ?>">当日改约人数</th>
			<th class="head" width="<?php echo $per; ?>">改约就诊人数</th>
			<th class="head" width="<?php echo $per; ?>">当日改约就诊</th>
			<th class="head" width="<?php echo $per; ?>">当月到诊人数</th>
		</tr>

<?php foreach ($kefu_id_name_arr as $kf_id => $kf_name) { ?>
		<tr>
			<td class="item"><a href="javascript:;" onclick="show_kf_detail(this);" title="点击查看每日明细"><?php echo $kf_name; ?></a></td>
			<td class="item"><?php echo $huifang_num[$kf_name]; ?></td>
			<td class="item"><?php echo $gaiyue_num[$kf_name]; ?></td>
			<td class="item"><?php echo $gaiyue_num_dangri[$kf_name]; ?></td>
			<td class="item"><?php echo $gaiyue_jiuzhen_num[$kf_name]; ?></td>
			<td class="item"><?php echo $gaiyue_jiuzhen_num_dangri[$kf_name]; ?></td>
			<td class="item"><?php echo $dangri_daozhen_num[$kf_name]; ?></td>
		</tr>
<?php } ?>

		<tr class="line_huizong">
			<td class="item">汇总</td>
			<td class="item"><?php echo @array_sum($huifang_num); ?></td>
			<td class="item"><?php echo @array_sum($gaiyue_num); ?></td>
			<td class="item"><?php echo @array_sum($gaiyue_num_dangri); ?></td>
			<td class="item"><?php echo @array_sum($gaiyue_jiuzhen_num); ?></td>
			<td class="item"><?php echo @array_sum($gaiyue_jiuzhen_num_dangri); ?></td>
			<td class="item"><?php echo @array_sum($dangri_daozhen_num); ?></td>
		</tr>

	</table>

</div>

<br>
<br>

</body>
</html>