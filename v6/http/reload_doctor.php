<?php
// --------------------------------------------------------
// - 功能说明 : 读取医生列表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2013-11-18
// --------------------------------------------------------
require "../lib/set_env.php";

if (strlen($_GET["date"]) == 10) {
	$date = @date("Y-m-d", strtotime($_GET["date"]));
} else {
	$date = '';
}

$doctor_arr = $db->query("select * from doctor where hospital_id=$hid order by name asc");

if ($date > 0) {
	$tb = strtotime($date." 0:0:0");
	$te = strtotime($date." 23:59:59");
	$d_num_arr = $db->query("select wish_doctor, count(wish_doctor) as c from patient_{$hid} where order_date>=$tb and order_date<=$te group by wish_doctor", "wish_doctor", "c");
	$doctor_arr_2 = array();
	foreach ($doctor_arr as $v) {
		$weekday = date("w", strtotime($date));
		if ($weekday == 0 || $weekday == 6) {
			$max_num = $v["max_weekend"];
		} else {
			$max_num = $v["max_weekday"];
		}
		$max_num_tip = $max_num == 0 ? "不限": $max_num;

		$doctor_arr_2[$v["name"]] = $v["name"].($v["intro"] ? ("(".$v["intro"].")") : "")." [".intval($d_num_arr[$v["name"]])."/".$max_num_tip."]";
	}
} else {
	$doctor_arr_2 = array();
	foreach ($doctor_arr as $v) {
		$doctor_arr_2[$v["name"]] = $v["name"].($v["intro"] ? ("(".$v["intro"].")") : "");
	}
}

?>

document.getElementById("wish_doctor").options.length = 0;

<?php if ($date != '') { ?>

add_option("wish_doctor", "", "-请选择医生(<?php echo $date; ?>)-");

<?php
foreach ($doctor_arr_2 as $k => $v) {
?>

add_option("wish_doctor", "<?php echo $k; ?>", "<?php echo $v; ?>");

<?php } ?>

<?php } else { ?>

add_option("wish_doctor", "", "-请先选择预约日期-");

<?php } ?>
