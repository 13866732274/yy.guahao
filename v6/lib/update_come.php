<?php
/*
// 更新到院数据，用于摘要显示
// 作者: 幽兰 (weelia@126.com)
// 更新: 2013-8-12
*/
header("Content-Type:text/html;charset=gb2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
set_time_limit(0);
ignore_user_abort(true);

$update_interval = 100; // 数据最短更新频率(秒)
$index_cache_table = "index_cache";

include "mysql.php";
$db = new mysql();


function _get_now() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
$page_begintime = _get_now();

function flush_echo($s = '') {
	echo $s."<br>\r\n";
	flush();
	ob_flush();
	ob_end_flush();
}

function _get_month_days($month = '') {
	if ($month == '') $month = date("Y-m");
	return date("j", strtotime("+1 month", strtotime($month."-1 0:0:0")) - 1);
}

// 将sql_code解析为数组条件
// (part_id in ("2") and media_from not in ("QQ"))  or (part_id in ("3") and media_from in ("网络","网挂")) or media_from in ("无线")
// media_from in ("QQ") or order_soft in ("qq")
// youhuazu in ("何康","黄瑞琪","刘镇祥","安琪")
// media_from in ("无线")
function _wee_parse_condition($code) {
	if (trim($code) == '') return array();
	$pies = explode(" or ", $code);
	$res = array();
	foreach ($pies as $v) {
		$v = trim($v);
		if (substr_count($v, " and ") > 0) {
			$v = substr($v, 1, -1);
			$v_arr = explode(" and ", $v);
		} else {
			$v_arr = array($v);
		}
		foreach ($v_arr as $k2 => $v2) {
			if (substr_count($v2, " not in ") > 0) {
				$arr_2 = explode(" not in ", $v2);
				$arr_2[2] = "!in_array";
			} else {
				$arr_2 = explode(" in ", $v2);
				$arr_2[2] = "in_array";
			}
			$arr_2[1] = explode(",", str_replace('"', "", substr($arr_2[1], 1, -1)));
			$v_arr[$k2] = $arr_2;
		}
		$res[] = $v_arr;
	}
	return $res;
}


// 判断某行是否在给定的条件内:
function _wee_line_is_in_condition($line, $condition) {
	if (empty($condition)) return 1;
	foreach ($condition as $k => $arr) {
		$is_in = 0;
		foreach ($arr as $define) {
			if ($define[2] == "in_array") {
				if (in_array($line[$define[0]], $define[1])) $is_in++;
			} else {
				if (!in_array($line[$define[0]], $define[1])) $is_in++;
			}
			//echo $is_in.":".$define[0]." ".$define[2]." ".implode(",", $define[1])."<br>";
		}
		if ($is_in == count($arr)) return 1;
	}
	return 0;
}


function _wee_get_field($hid, $all_condition_list, $id_condition_arr) {
	$f = array();
	foreach ($all_condition_list as $k => $li) {
		if ($li["hospital_id"] == 0 || $li["hospital_id"] == $hid) {
			$arr = $id_condition_arr[$k];
			foreach ($arr as $k2 => $arr2) {
				foreach ($arr2 as $def) {
					if ($def[0] != '' && !in_array($def[0], $f)) {
						$f[] = $def[0];
					}
				}
			}
		}
	}
	return $f;
}



// 时间的起始点都是 YYYY-MM-DD 00:00:00 结束则是 YYYY-MM-DD 23:59:59
$today_tb = mktime(0,0,0); //今天开始
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

// 年比
$nb_tb = strtotime("-1 year", $month_tb);
$_days = _get_month_days(date("Y-m", $nb_tb));
if (date("j") > $_days) { //当前的日已经大于去年同月的天数了(比如29日和去年的28日)
	$nb_te = strtotime(date("Y-m-", $nb_tb).$_days.date(" 23:59:59")); //对比为去年同月的整月
} else {
	$nb_te = strtotime(date("Y-m-", $nb_tb).date("d H:i:s"));
}

// 数据查询依照此数组定义
$time_arr = array(
	"明日" => array($tomorrow_tb, $tomorrow_te),
	"今日" => array($today_tb, $today_te),
	"昨日" => array($yesterday_tb, $yesterday_te),
	"本月" => array($month_tb, $today_te),
	"上月" => array($lastmonth_tb, $lastmonth_te),
	"同期" => array($nb_tb, $nb_te),
	"上月同期" => array($tb_tb, $tb_te),
);

$tb = $lastmonth_tb; //最小时间
$te = $tomorrow_te; //最大时间

// 要执行的统计模块:
$all_condition_list = $db->query("select id, name, hospital_id, condition_code from index_module where isshow>0", "id");
$id_condition_arr = array();
foreach ($all_condition_list as $li) {
	$id_condition_arr[$li["id"]] = _wee_parse_condition($li["condition_code"]);
}


$time_file = rtrim(str_replace("\\", "/", dirname(__FILE__)), "/")."/../data/update_data.txt";
$last_update = @intval(file_get_contents($time_file));

if (time() - $last_update > $update_interval) {
	flush_echo("正在更新，请稍候...".str_repeat("&nbsp; ", 100) );

	// 更新文件时间:
	file_put_contents($time_file, time());

	// 要更新的医院数据:
	$_hlist = $db->query("select id, name from hospital where ishide=0", "id", "name");

	// 当前缓存的数据:
	$cur_cache_data = $db->query("select hid,data from {$index_cache_table}", "hid", "data");
	$cur_cache_hid = array_keys($cur_cache_data);

	// ------------------------------------------------------------------------------------------
	// begin 模块数据更新
	foreach ($_hlist as $_hid => $_name) {
		$table = "patient_{$_hid}";

		$field_arr = _wee_get_field($_hid, $all_condition_list, $id_condition_arr);

		if (!in_array("addtime", $field_arr)) $field_arr[] = "addtime";
		if (!in_array("order_date", $field_arr)) $field_arr[] = "order_date";
		if (!in_array("status", $field_arr)) $field_arr[] = "status";
		if (!in_array("depart", $field_arr)) $field_arr[] = "depart";
		if (!in_array("disease_id", $field_arr)) $field_arr[] = "disease_id";
		if (!in_array("uid", $field_arr)) $field_arr[] = "uid";
		if (!in_array("part_id", $field_arr)) $field_arr[] = "part_id";

		$f_str = implode(",", $field_arr);
		$sql = "SELECT $f_str FROM $table WHERE ((addtime>=$tb and addtime<=$te) or (order_date>=$tb and order_date<=$te)) or ((addtime>=$nb_tb and addtime<=$nb_te) or (order_date>=$nb_tb and order_date<=$nb_te))";

		$time_begin_1 = _get_now();
		$q = mysql_query($sql, $db->dblink);

		$res = array();
		while ($li = mysql_fetch_assoc($q)) {
			$ad = $li["addtime"]; //ad为addtime
			$od = $li["order_date"]; //od为order_date
			foreach ($all_condition_list as $c_id => $c_detail) {
				if ($c_detail["hospital_id"] == 0 || $c_detail["hospital_id"] == $_hid) {
					if (_wee_line_is_in_condition($li, $id_condition_arr[$c_id])) {
						//$data_name = $c_detail["name"];
						$data_name = "ID_".$c_id;
						foreach ($time_arr as $tn => $d) {
							if ($ad >= $d[0] && $ad <= $d[1]) {
								// 对总数据"预约"的修正:预约只算网络和电话，导医添加的不算 @ 2014-5-13
								if ($c_id == 2) {
									if ($li["part_id"] != 4) {
										$res[$data_name]["预约"][$tn] += 1;
									}
								} else {
									$res[$data_name]["预约"][$tn] += 1;
								}
							}
							if ($od >= $d[0] && $od <= $d[1]) {
								$res[$data_name]["预到"][$tn] += 1;
								if ($li["status"] == 1) $res[$data_name]["实到"][$tn] += 1;
							}
						}
					}
				}
			}

			// 处理 uid 数据
			if ($li["uid"]) {
				$data_name = "UID_".$li["uid"];
				foreach ($time_arr as $tn => $d) {
					if ($ad >= $d[0] && $ad <= $d[1]) {
						$res[$data_name]["预约"][$tn] += 1;
					}
					if ($od >= $d[0] && $od <= $d[1]) {
						$res[$data_name]["预到"][$tn] += 1;
						if ($li["status"] == 1) $res[$data_name]["实到"][$tn] += 1;
					}
				}
			}

			// 处理disease_id:
			// 2014-6-5 修正：疾病数据只统计网络和电话的，不统计导医的
			if ($li["disease_id"] > 0 && ($li["part_id"] != 4) ) {
				$data_name = "DIS_".$li["disease_id"];
				foreach ($time_arr as $tn => $d) {
					if ($ad >= $d[0] && $ad <= $d[1]) {
						$res[$data_name]["预约"][$tn] += 1;
					}
					if ($od >= $d[0] && $od <= $d[1]) {
						$res[$data_name]["预到"][$tn] += 1;
						if ($li["status"] == 1) $res[$data_name]["实到"][$tn] += 1;
					}
				}
			}

			// 处理depart:
			if ($li["depart"] > 0) {
				$data_name = "DP_".$li["depart"];
				foreach ($time_arr as $tn => $d) {
					if ($ad >= $d[0] && $ad <= $d[1]) {
						$res[$data_name]["预约"][$tn] += 1;
					}
					if ($od >= $d[0] && $od <= $d[1]) {
						$res[$data_name]["预到"][$tn] += 1;
						if ($li["status"] == 1) $res[$data_name]["实到"][$tn] += 1;
					}
				}
			}

		}

		$time_used = round(_get_now() - $time_begin_1, 4);
		//flush_echo($_hid." 用时： ".$time_used."s");

		$s = addslashes(serialize($res));

		if (!in_array($_hid, $cur_cache_hid)) {
			$db->query("insert into $index_cache_table set hid=$_hid, data='$s'");
		} else {
			$db->query("update $index_cache_table set data='$s' where hid=$_hid limit 1");
		}

		// 延迟一会 再进行下一个
		//usleep(100000);
	}
	// end 模块数据更新
	// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



	// ------------------------------------------------------------------------------------------
	// 更新资料库统计数据:
	// 资料库数据，按医院名保存，昨日/今日/本月/上月
	$ku_dt_arr = array(
		"今日" => array($today_tb, $today_te),
		"昨日" => array($yesterday_tb, $yesterday_te),
		"本月" => array($month_tb, strtotime(date("Y-m-d")." 23:59:59")),
		"上月" => array($lastmonth_tb, $lastmonth_te),
	);


	// 按医院而处理:
	$all_hospital_list = $db->query("select sname, count(sname) as c from hospital where ishide=0 group by sname order by sort desc, sname asc", "sname", "c");
	foreach ($all_hospital_list as $hname => $hcount) {

		if ($hname == "上海华美医院") continue;

		$cur_hid_arr = $db->query("select id,name from hospital where ishide=0 and sname='$hname'", "id", "name");
		$cur_hids = count($cur_hid_arr) > 0 ? implode(",", array_keys($cur_hid_arr)) : "0";

		$sub_type_arr = $db->query("select id from count_type where hid in ($cur_hids) and ishide=0", "", "id");
		$sub_type_ids = count($sub_type_arr) > 0 ? implode(",", $sub_type_arr) : "0";

		$h_data = array();
		$h_data["医院"] = $hname;
		foreach ($ku_dt_arr as $dt_name => $dt_def) {
			$_tb = $dt_def[0];
			$_te = $dt_def[1];
			$int_tb = date("Ymd", $_tb);
			$int_te = date("Ymd", $_te);

			// 读取商务通有效未预约数据 (在所有注册的子统计中查询)
			$h_data[$dt_name]["有效未约"] = $db->query("select sum(ok_click)-sum(talk_swt) as c from count_web where type_id in ($sub_type_ids) and date>=$int_tb and date<=$int_te", 1, "c");
			//$h_data[$dt_name]["有效未约"] += $db->query("select sum(tel_ok)-sum(yuyue) as c from count_tel where type_id in ($sub_type_ids) and date>=$int_tb and date<=$int_te", 1, "c");

			$data_arr = $db->query("select qq, weixin, is_yuyue, is_come from ku_list where hid in ($cur_hids) and addtime>=$_tb and addtime<=$_te");
			foreach ($data_arr as $li) {
				$h_data[$dt_name]["增加人数"] ++;
				if ($li["qq"] != '') $h_data[$dt_name]["QQ增加"] ++;
				if ($li["weixin"] != "") $h_data[$dt_name]["微信增加"] ++;
				if ($li["is_yuyue"]) $h_data[$dt_name]["转约人数"] ++;
				if ($li["is_come"]) $h_data[$dt_name]["到诊人数"] ++;
			}

			// 预约中的数据加入
			foreach ($cur_hid_arr as $_hid => $_h_name) {
				$data_arr = $db->query("select qq, weixin from patient_{$_hid} where (qq!='' or weixin!='') and addtime>=$_tb and addtime<=$_te and ku_id=0");
				foreach ($data_arr as $li) {
					if ($li["qq"] != '') $h_data[$dt_name]["QQ增加"] ++;
					if ($li["weixin"] != "") $h_data[$dt_name]["微信增加"] ++;
				}
			}

			$h_data[$dt_name]["电话跟踪"] = $db->query("select count(*) as c from ku_huifang where hid in ($cur_hids) and addtime>=$_tb and addtime<=$_te and qudao='电话'", 1, "c");
			$h_data[$dt_name]["微信跟踪"] = $db->query("select count(*) as c from ku_huifang where hid in ($cur_hids) and addtime>=$_tb and addtime<=$_te and qudao='微信'", 1, "c");
			$h_data[$dt_name]["QQ跟踪"] = $db->query("select count(*) as c from ku_huifang where hid in ($cur_hids) and addtime>=$_tb and addtime<=$_te and qudao='QQ'", 1, "c");
			$h_data[$dt_name]["短信跟踪"] = $db->query("select count(*) as c from ku_huifang where hid in ($cur_hids) and addtime>=$_tb and addtime<=$_te and qudao='短信'", 1, "c");
		}

		// 数据保存:
		$data_str = @serialize($h_data);
		$old = $db->query("select * from index_cache_ku where h_name='$hname'", 1);
		if (count($old) == 0) {
			$db->query("insert into index_cache_ku set h_name='$hname', data='$data_str'");
		} else {
			$db->query("update index_cache_ku set data='$data_str' where h_name='$hname'");
		}
	}
	// end  资料库数据统计
	// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



	// ------------------------------------------------------------------------------------------
	// 统计数据归档
	if (date("H") == 2 && date("i") < 10) {
		// 网络数据归档
		$count_web_type_arr = $db->query("select id, name, hid, kefu, data_hids from count_type where ishide=0 and type='web'"); // 所有需要更新的统计子项
		$date = date("Ymd", $yesterday_tb);
		foreach ($count_web_type_arr as $t_arr) {
			$type_id = $t_arr["id"];
			$type_name = $t_arr["name"];
			$kefu_arr = explode(",", trim($t_arr["kefu"]));
			$hid = $t_arr["hid"];

			$hid_set = $t_arr["data_hids"];
			if ($hid_set == "-1") {
				$sname = $db->query("select sname from hospital where id=$hid limit 1", 1, "sname");
				$hid_fanwen_arr = $db->query("select id from hospital where ishide=0 and sname='$sname'", "", "id");
			} else if ($hid_set != "") {
				$hid_fanwen_arr = explode(",", $hid_set);
			} else {
				$hid_fanwen_arr = array($hid);
			}

			$yuyue_swt = $yuyue_dh = $yuyue_qita = $yuyue_bendi = $yuyue_waidi = array();
			$yudao_swt = $yudao_dh = $yudao_qita = $yudao_bendi = $yudao_waidi = array();
			$daoyuan_swt = $daoyuan_dh = $daoyuan_qita = $daoyuan_bendi = $daoyuan_waidi = array();
			foreach ($hid_fanwen_arr as $h) {
				$sql_base = "select author, count(*) as c from patient_{$h} where";
				$where_a = " addtime>=$yesterday_tb and addtime<=$yesterday_te and";
				$where_b = " order_date>=$yesterday_tb and order_date<=$yesterday_te and";

				// 预约:
				$a = $db->query($sql_base.$where_a." order_soft in ('swt', 'kst') group by author", "author", "c");
				foreach ($a as $k => $v) $yuyue_swt[$k] += $v;
				$a = $db->query($sql_base.$where_a." order_soft in ('dh') group by author", "author", "c");
				foreach ($a as $k => $v) $yuyue_dh[$k] += $v;
				$a = $db->query($sql_base.$where_a." order_soft not in ('swt', 'kst', 'dh') group by author", "author", "c");
				foreach ($a as $k => $v) $yuyue_qita[$k] += $v;
				$a = $db->query($sql_base.$where_a." suozaidi=1 group by author", "author", "c");
				foreach ($a as $k => $v) $yuyue_bendi[$k] += $v;
				$a = $db->query($sql_base.$where_a." suozaidi=2 group by author", "author", "c");
				foreach ($a as $k => $v) $yuyue_waidi[$k] += $v;

				// 预到:
				$a = $db->query($sql_base.$where_b." order_soft in ('swt', 'kst') group by author", "author", "c");
				foreach ($a as $k => $v) $yudao_swt[$k] += $v;
				$a = $db->query($sql_base.$where_b." order_soft in ('dh') group by author", "author", "c");
				foreach ($a as $k => $v) $yudao_dh[$k] += $v;
				$a = $db->query($sql_base.$where_b." order_soft not in ('swt', 'kst', 'dh') group by author", "author", "c");
				foreach ($a as $k => $v) $yudao_qita[$k] += $v;
				$a = $db->query($sql_base.$where_b." suozaidi=1 group by author", "author", "c");
				foreach ($a as $k => $v) $yudao_bendi[$k] += $v;
				$a = $db->query($sql_base.$where_b." suozaidi=2 group by author", "author", "c");
				foreach ($a as $k => $v) $yudao_waidi[$k] += $v;

				// 到院:
				$a = $db->query($sql_base.$where_b." order_soft in ('swt', 'kst') and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan_swt[$k] += $v;
				$a = $db->query($sql_base.$where_b." order_soft in ('dh') and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan_dh[$k] += $v;
				$a = $db->query($sql_base.$where_b." order_soft not in ('swt', 'kst', 'dh') and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan_qita[$k] += $v;
				$a = $db->query($sql_base.$where_b." suozaidi=1 and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan_bendi[$k] += $v;
				$a = $db->query($sql_base.$where_b." suozaidi=2 and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan_waidi[$k] += $v;
			}

			foreach ($kefu_arr as $kf) {
				$to_update = array();
				$to_update[] = "talk=".intval($yuyue_swt[$kf] + $yuyue_dh[$kf] + $yuyue_qita[$kf]);
				$to_update[] = "talk_swt=".intval($yuyue_swt[$kf]);
				$to_update[] = "talk_tel=".intval($yuyue_dh[$kf]);
				$to_update[] = "talk_other=".intval($yuyue_qita[$kf]);
				$to_update[] = "talk_bendi=".intval($yuyue_bendi[$kf]);
				$to_update[] = "talk_waidi=".intval($yuyue_waidi[$kf]);

				$to_update[] = "orders=".intval($yudao_swt[$kf] + $yudao_dh[$kf] + $yudao_qita[$kf]);
				$to_update[] = "orders_swt=".intval($yudao_swt[$kf]);
				$to_update[] = "orders_tel=".intval($yudao_dh[$kf]);
				$to_update[] = "orders_other=".intval($yudao_qita[$kf]);
				$to_update[] = "orders_bendi=".intval($yudao_bendi[$kf]);
				$to_update[] = "orders_waidi=".intval($yudao_waidi[$kf]);

				$to_update[] = "come_all=".intval($daoyuan_swt[$kf] + $daoyuan_dh[$kf] + $daoyuan_qita[$kf]);
				$to_update[] = "come=".intval($daoyuan_swt[$kf]);
				$to_update[] = "come_tel=".intval($daoyuan_dh[$kf]);
				$to_update[] = "come_other=".intval($daoyuan_qita[$kf]);
				$to_update[] = "come_bendi=".intval($daoyuan_bendi[$kf]);
				$to_update[] = "come_waidi=".intval($daoyuan_waidi[$kf]);

				$sql_set = implode(", ", $to_update);

				// 检查是否存在数据:
				$old = $db->query("select * from count_web where type_id='$type_id' and date=$date and kefu='$kf'", 1);
				if ($old["id"] > 0) {
					$db->query("update count_web set $sql_set where id=".$old["id"]." limit 1");
				} else {
					$time = time();
					$db->query("insert into count_web set type_id=$type_id, type_name='$type_name', date=$date, kefu='$kf', $sql_set, addtime=$time, uid=0, u_realname='系统自动'");
				}
			}
		}
		// end 网络数据归档


		// 电话数据归档
		$count_tel_type_arr = $db->query("select id, name, hid, kefu, data_hids from count_type where ishide=0 and type='tel'"); // 所有需要更新的统计子项
		$date = date("Ymd", $yesterday_tb);
		foreach ($count_tel_type_arr as $t_arr) {
			$type_id = $t_arr["id"];
			$type_name = $t_arr["name"];
			$kefu_arr = explode(",", trim($t_arr["kefu"]));
			$hid = $t_arr["hid"];

			$hid_set = $t_arr["data_hids"];
			if ($hid_set == "-1") {
				$sname = $db->query("select sname from hospital where id=$hid limit 1", 1, "sname");
				$hid_fanwen_arr = $db->query("select id from hospital where ishide=0 and sname='$sname'", "", "id");
			} else if ($hid_set != "") {
				$hid_fanwen_arr = explode(",", $hid_set);
			} else {
				$hid_fanwen_arr = array($hid);
			}


			// 初始化数组:
			$yuyue = $yudao = $daoyuan = $daoyuan_wangluo = $daoyuan_wuxian = $daoyuan_ditu = $daoyuan_guahaowang = array();

			foreach ($hid_fanwen_arr as $h) {
				$sql_base = "select author, count(*) as c from patient_{$h} where";
				$where_a = " addtime>=$yesterday_tb and addtime<=$yesterday_te and";
				$where_b = " order_date>=$yesterday_tb and order_date<=$yesterday_te and";

				// 预约:
				$a = $db->query($sql_base.$where_a." part_id in (3,12) group by author", "author", "c");
				foreach ($a as $k => $v) $yuyue[$k] += $v;

				// 预到:
				$a = $db->query($sql_base.$where_b." part_id in (3,12) group by author", "author", "c");
				foreach ($a as $k => $v) $yudao[$k] += $v;

				// 到院:
				$a = $db->query($sql_base.$where_b." part_id in (3,12) and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan[$k] += $v;
				$a = $db->query($sql_base.$where_b." part_id in (3,12) and media_from='网络' and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan_wangluo[$k] += $v;
				$a = $db->query($sql_base.$where_b." part_id in (3,12) and media_from like '%无线%' and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan_wuxian[$k] += $v;
				$a = $db->query($sql_base.$where_b." part_id in (3,12) and media_from like '%地图%' and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan_ditu[$k] += $v;
				$a = $db->query($sql_base.$where_b." part_id in (3,12) and media_from like '%挂号网%' and status=1 group by author", "author", "c");
				foreach ($a as $k => $v) $daoyuan_guahaowang[$k] += $v;

			}

			foreach ($kefu_arr as $kf) {
				$to_update = array();
				$to_update[] = "yuyue=".intval($yuyue[$kf]);
				$to_update[] = "yudao=".intval($yudao[$kf]);
				$to_update[] = "jiuzhen=".intval($daoyuan[$kf]);
				$to_update[] = "wangluo=".intval($daoyuan_wangluo[$kf]);
				$to_update[] = "wuxian=".intval($daoyuan_wuxian[$kf]);
				$to_update[] = "ditu=".intval($daoyuan_ditu[$kf]);
				$to_update[] = "guahaowang=".intval($daoyuan_guahaowang[$kf]);
				$qita = intval($daoyuan[$kf] - $daoyuan_wangluo[$kf] - $daoyuan_wuxian[$kf] - $daoyuan_ditu[$kf] - $daoyuan_guahaowang[$kf]);
				$to_update[] = "qita=".($qita >= 0 ? $qita : 0);

				$sql_set = implode(", ", $to_update);

				// 检查是否存在数据:
				$old = $db->query("select * from count_tel where type_id='$type_id' and date=$date and kefu='$kf'", 1);
				if ($old["id"] > 0) {
					$db->query("update count_tel set $sql_set where id=".$old["id"]." limit 1");
				} else {
					$time = time();
					$db->query("insert into count_tel set type_id=$type_id, type_name='$type_name', date=$date, kefu='$kf', $sql_set, addtime=$time, uid=0, uname='系统自动'");
				}
			}
		}
		// end 电话数据归档
	}
	// end 统计数据归档
	// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++



	$status = "数据已更新";
} else {
	$status = "数据更新周期未到，请稍后再试";
}

$time_used = round(_get_now() - $page_begintime, 4);
flush_echo();
flush_echo($status." @ ".$time_used."s");

// 记录日志：
$log_str = date("Y-m-d H:i:s")." [".$time_used."s] ".$status."\r\n";
$log_file = rtrim(str_replace("\\", "/", dirname(__FILE__)), "/")."/../data/update_data_log.txt";
@file_put_contents($log_file, $log_str, FILE_APPEND);

?>