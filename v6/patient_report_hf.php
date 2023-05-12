<?php
// --------------------------------------------------------
// - ����˵�� : �ط�ͳ�Ʊ���
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-07-09
// --------------------------------------------------------
require "lib/set_env.php";

if ($hid <= 0) {
	//$hid = $_SESSION[$cfgSessionName]["hospital_id"] = $hospital_ids[0];
	exit("�������Ͻǡ��л�ҽԺ����ѡ��ҽԺ�鿴��");
}

$huifang_time_length = strlen("2016-06-13 10:49"); //ʱ�䳤�ȣ����ڽ�ȡ
$edit_log_time_length = strlen("2016-06-12 18:25:18");

// ��ѡ�·�����:
$month_arr = array();
$month_arr[date("Y-m")] = "����";
$t = strtotime(date("Y-m-01"));
for ($i=1; $i<=6; $i++) {
	$t2 = strtotime("-".$i." month", $t);
	$month_arr[date("Y-m", $t2)] = date("Y-m", $t2);
}

// ��ȡ��ǰ���ҵĵ绰�ͷ�:
$kefu_id_name_arr = $db->query("select id, realname from sys_admin where isshow=1 and part_id in (3,12) and character_id not in (16) and concat(',', hospitals, ',') like '%,{$hid},%' and concat(',',guahao_config,',') like '%,huifang,%' order by realname asc", "id", "realname");

$month = $_GET["month"];
if ($month == '') $month = date("Y-m"); //Ĭ��Ϊ����

$month_begin = strtotime($month."-01");
$month_end = strtotime("+1 month", $month_begin) - 1;


$t_from = strtotime("-2 month", $month_begin);
$t_end = $month_end;


$huifang_num = array(); //�طô���
$gaiyue_num = array(); //��Լ����
$gaiyue_num_dangri = array(); //���ո�Լ

// ����ͷ��������ݷ���:
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

		//2016-11-22 17:01:45 ��ԪԪ ��ԤԼʱ���ɡ�2016-03-06 14:00���޸�Ϊ��2016-11-23 14:00����

		$log_arr = wee_log_parse($li["edit_log"], $edit_log_time_length, $kf_name);
		$flag_string = "ԤԼʱ����";
		foreach ($log_arr as $t => $c) {
			if ($t >= $month_begin && $t <= $month_end && substr_count($c, $flag_string) > 0) {
				$gaiyue_num[$kf_name] ++;
				if ($li["status"] == 1) {
					$gaiyue_jiuzhen_num[$kf_name] ++; //��Լ����
				}

				// �ж��Ƿ��ո�Լ:
				preg_match_all("/��([0-9\-\ \:]+?)��/", $c, $out);
				$t1 = $out[1][0];
				$t2 = $out[1][1];
				if ($t1 != "" && $t2 != "") {
					if (date("Ymd", strtotime($t1)) == date("Ymd", strtotime($t2))) {
						$gaiyue_num_dangri[$kf_name] ++; //���ո�Լ
						if ($li["status"] == 1 && date("Ymd", strtotime($t1)) == date("Ymd", $li["order_date"])) {
							$gaiyue_jiuzhen_num_dangri[$kf_name] ++; //���ո�Լ����
						}
					}
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
<title>�ط�ͳ�Ʊ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<style>
* {font-family:"΢���ź�"; }
.center_show {margin:0 auto; width:1000px; text-align:center; }
.list {border:2px solid silver; }
.head {color:#bf0060 !important; border:1px solid silver !important; background:#e1e7ec !important; }
.item {text-align:center; padding:4px !important; border-top:1px solid silver !important; border-bottom:1px solid silver !important; }
.line_huizong td {color:red; }
.report_tips {padding:20px 0 20px 0; text-align:center; font-size:16px; font-family:"΢���ź�"; }
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
	<a href="javascript:;" onclick="show_hospital_all();">�鿴ҽԺ����</a>
	<form method="GET" action="" onsubmit="" style="display:inline; margin-left:20px;">
		�л��·ݣ�<select name="month" class="combo" onchange="this.form.submit()">
			<?php echo list_option($month_arr, "_key_", "_value_", $_GET["month"]); ?>
		</select>
	</form>
</div>

<div class="center_show">

	<div class="report_tips"><?php echo $hinfo["name"]; ?>��<?php echo $month; ?>���ط�ͳ�Ʊ���</div>

	<?php $per = round(90 / 6, 3)."%"; ?>
	<table class="list" width="100%">
		<tr>
			<th class="head" width="10%">�ͷ�����</th>
			<th class="head" width="<?php echo $per; ?>">�ط�����</th>
			<th class="head" width="<?php echo $per; ?>">��Լ����</th>
			<th class="head" width="<?php echo $per; ?>">���ո�Լ����</th>
			<th class="head" width="<?php echo $per; ?>">��Լ��������</th>
			<th class="head" width="<?php echo $per; ?>">���ո�Լ����</th>
			<th class="head" width="<?php echo $per; ?>">���µ�������</th>
		</tr>

<?php foreach ($kefu_id_name_arr as $kf_id => $kf_name) { ?>
		<tr>
			<td class="item"><a href="javascript:;" onclick="show_kf_detail(this);" title="����鿴ÿ����ϸ"><?php echo $kf_name; ?></a></td>
			<td class="item"><?php echo $huifang_num[$kf_name]; ?></td>
			<td class="item"><?php echo $gaiyue_num[$kf_name]; ?></td>
			<td class="item"><?php echo $gaiyue_num_dangri[$kf_name]; ?></td>
			<td class="item"><?php echo $gaiyue_jiuzhen_num[$kf_name]; ?></td>
			<td class="item"><?php echo $gaiyue_jiuzhen_num_dangri[$kf_name]; ?></td>
			<td class="item"><?php echo $dangri_daozhen_num[$kf_name]; ?></td>
		</tr>
<?php } ?>

		<tr class="line_huizong">
			<td class="item">����</td>
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