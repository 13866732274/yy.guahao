<?php
// --------------------------------------------------------
// - ����˵�� : �ط�ͳ�Ʊ��� - ��Ժ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-07-11
// --------------------------------------------------------
require "lib/set_env.php";

if ($hid <= 0) {
	exit("�������Ͻǡ��л�ҽԺ����ѡ��ҽԺ�鿴��");
}
$hospital_name = $hinfo["sname"];
$hospital_id_name = $db->query("select id, name from hospital where ishide=0 and sname='$hospital_name' order by id asc", "id", "name");

$huifang_time_length = strlen("2016-06-13 10:49"); //ʱ�䳤�ȣ����ڽ�ȡ
$edit_log_time_length = strlen("2016-06-12 18:25:18");

$month = $_GET["month"];
if ($month == '') $month = date("Y-m"); //Ĭ��Ϊ����

$month_begin = strtotime($month."-01");
$month_end = strtotime("+1 month", $month_begin) - 1;


$t_from = strtotime("-2 month", $month_begin);
$t_end = $month_end;

$all_kf_id_name_arr = array();

$huifang_num = array(); //�طô���
$gaiyue_num = array(); //��Լ����

foreach ($hospital_id_name as $hid => $hname) {

	// ��ȡ��ǰ���ҵĵ绰�ͷ�:
	$kefu_id_name_arr = $db->query("select id, realname from sys_admin where isshow=1 and part_id in (3,12) and character_id not in (16) and concat(',', hospitals, ',') like '%,{$hid},%' and concat(',',guahao_config,',') like '%,huifang,%' order by realname asc", "id", "realname");

	foreach ($kefu_id_name_arr as $kf_id => $kf_name) {
		if (!in_array($kf_name, $all_kf_id_name_arr)) {
			$all_kf_id_name_arr[$kf_id] = $kf_name;
		}
	}


	// ����ͷ��������ݷ���:
	foreach ($kefu_id_name_arr as $kf_id => $kf_name) {
		$list = $db->query("select id, name, huifang, edit_log, status from patient_{$hid} where order_date>=$t_from and order_date<=$t_end and concat(huifang, edit_log) like '% {$kf_name}%'");

		foreach ($list as $li) {
			$huifang_arr = wee_log_parse($li["huifang"], $huifang_time_length);
			foreach ($huifang_arr as $t => $c) {
				if ($t >= $month_begin && $t <= $month_end && substr($c, 0, strlen($kf_name)) == $kf_name) {
					$huifang_num[$kf_name] ++;
					break;
				}
			}

			$log_arr = wee_log_parse($li["edit_log"], $edit_log_time_length);
			$flag_string = "ԤԼʱ����";
			foreach ($log_arr as $t => $c) {
				if ($t >= $month_begin && $t <= $month_end && substr($c, 0, strlen($kf_name)) == $kf_name && substr_count($c, $flag_string) > 0) {
					$gaiyue_num[$kf_name] ++;
					if ($li["status"] == 1) {
						$gaiyue_jiuzhen_num[$kf_name] ++;
					}
					break;
				}
			}
		}
	}


	// ���µ�������ͳ��:
	$names = array();
	foreach ($kefu_id_name_arr as $kf_id => $kf_name) {
		$names[] = "'".$kf_name."'";
	}
	$name_str = count($names) ? implode(", ", $names) : "0";
	$_tmp = $db->query("select author, count(*) as c from patient_{$hid} where order_date>=$month_begin and order_date<=$month_end and status=1 and author in ($name_str) group by author", "author", "c");
	foreach ($_tmp as $k => $v) {
		$dangri_daozhen_num[$k] += $v;
	}
}


function wee_log_parse($str, $time_length) {
	$str = trim(str_replace("\r", "", $str));
	$arr = explode("\n", $str);
	$return = array();
	foreach ($arr as $v) {
		if (trim($v) == '') continue;
		$t = substr($v, 0, $time_length);
		$t2 = @strtotime($t);
		$c2 = trim(substr($v, $time_length + 1));
		if ($t2 > 0 && $c2 != '') {
			$return[$t2] = $c2;
		}
	}
	return $return;
}

asort($all_kf_id_name_arr);


?>
<html>
<head>
<title>ҽԺ����</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
* {font-family:"΢���ź�"; }
.list {border:2px solid silver; }
.head {color:#bf0060 !important; border:1px solid silver !important; background:#e1e7ec !important; }
.item {text-align:center; padding:4px !important; border-top:1px solid silver !important; border-bottom:1px solid silver !important; }
.line_huizong td {color:red; }
.report_tips {padding:20px 0 20px 0; text-align:center; font-size:16px; font-family:"΢���ź�"; }
</style>
</head>

<body>

<div class="report_tips"><?php echo $hospital_name."(".count($hospital_id_name)."������) ����"; ?>����(�·�:<?php echo $month; ?>)</div>

<table class="list" width="100%">
	<tr>
		<th class="head">�ͷ�����</th>
		<th class="head">�ط�����</th>
		<th class="head">��Լ����</th>
		<th class="head">��Լ��������</th>
		<th class="head">���µ�������</th>
	</tr>

<?php foreach ($all_kf_id_name_arr as $kf_id => $kf_name) { ?>
	<tr>
		<td class="item"><?php echo $kf_name; ?></td>
		<td class="item"><?php echo $huifang_num[$kf_name]; ?></td>
		<td class="item"><?php echo $gaiyue_num[$kf_name]; ?></td>
		<td class="item"><?php echo $gaiyue_jiuzhen_num[$kf_name]; ?></td>
		<td class="item"><?php echo $dangri_daozhen_num[$kf_name]; ?></td>
	</tr>
<?php } ?>

	<tr class="line_huizong">
		<td class="item">����</td>
		<td class="item"><?php echo @array_sum($huifang_num); ?></td>
		<td class="item"><?php echo @array_sum($gaiyue_num); ?></td>
		<td class="item"><?php echo @array_sum($gaiyue_jiuzhen_num); ?></td>
		<td class="item"><?php echo @array_sum($dangri_daozhen_num); ?></td>
	</tr>

</table>

<br>
<br>

</body>
</html>