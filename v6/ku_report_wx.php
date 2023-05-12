<?php
// --------------------------------------------------------
// - ����˵�� : ���Ͽⱨ�� - ΢����ѯԱ����
// - �������� : zhuwenya (zhuwenya@126.com)
// - ����ʱ�� : 2016-10-26
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
//include "ku_report.config.php";
$table = "ku_list";

$ku_area_arr = array(
	"" => "���е���",
	"����" => "����",
	"����" => "����",
	"����" => "����",
);

$ku_part_arr = array(
	"" => "���в���",
	"2" => "����",
	"3" => "�绰",
);

$hids = count($hospital_ids) ? implode(",", $hospital_ids) : "0";
$hospital_arr = $db->query("select id, name, sname from hospital where ishide=0 and id in ($hids) order by sname asc, sort desc, name asc", "id");
$hos_options = $snames = array();
$hos_options[] = array("k" => "", "v" => "����ҽԺ/����");
foreach ($hospital_arr as $_hid => $_hli) {
	if (!in_array($_hli["sname"], $snames)) {
		$snames[] = $_hli["sname"];
		$hos_options[] = array("k" => "sname:".$_hli["sname"], "v" => $_hli["sname"]);
	}
	$hos_options[] = array("k" => "hid:".$_hid, "v" => "����".$_hli["name"]);
}


// ����Ĭ��ֵ
if ($_GET["btime"] == "") $_GET["btime"] = date("Y-m-01");
if ($_GET["etime"] == "") $_GET["etime"] = date("Y-m-d");

$t_begin = strtotime($_GET["btime"]);
$t_end = strtotime($_GET["etime"]." 23:59:59");

$where_add = '';
$area = $_GET["area"];
if ($area != "") {
	$where_add .= " and area='".wee_safe_key($area)."'";
}
$part_id = intval($_GET["part_id"]);
if ($part_id > 0) {
	$where_add .= " and part_id=".$part_id;
}
$hospital = $_GET["hospital"];
if ($hospital != "") {
	list($htype, $hvalue) = explode(":", $hospital, 2);
	if ($htype == "sname") {
		$hvalue = wee_safe_key($hvalue);
		$hid_arr = $db->query("select id from hospital where ishide=0 and sname='$hvalue' and id in ($hids)", "", "id");
	} else {
		$hid_arr = array(intval($hvalue));
	}
	$hid_limit = count($hid_arr) ? implode(",", $hid_arr) : "0";
	$where_add .= " and hid in (".$hid_limit.")";
}


// ��ѯ��ʱ����ڣ�ָ����΢������Ա������:
$zong_zhiding = $db->query("select count(*) as c from ku_list where wx_is_fenpei=0 and addtime>=$t_begin and addtime<=$t_end and to_weixin=1 $where_add", 1, "c");
$list = $db->query("select wx_uname, wx_is_add, is_yuyue, is_come from ku_list where wx_is_fenpei=0 and addtime>=$t_begin and addtime<=$t_end and to_weixin=1 and wx_uname!='' $where_add");

foreach ($list as $li) {
	$kf = $li["wx_uname"];
	if (!in_array($kf, $kefu_arr)) {
		$kefu_arr[] = $kf;
	}
	$wx_all_num[$kf]++;
	if ($li["wx_is_add"] > 0) {
		$wx_is_add[$kf]++;
	} else {
		$wx_not_add[$kf]++;
	}
	if ($li["is_yuyue"] > 0) {
		$wx_is_yuyue[$kf]++;
	}
	if ($li["is_come"] > 0) {
		$wx_is_come[$kf]++;
	}
}

sort($kefu_arr);

?>
<html>
<head>
<title>΢��ͳ�Ʊ���</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>

<style>
* {font-family:"΢���ź�","Tahoma" !important; font-size:12px;  }
body {overflow-x:auto !important;}
form {display:inline; }

.condition_set {text-align:center; margin-top:20px; }
.column_sortable {cursor:pointer; color:blue; padding:8px 3px 6px 3px !important; }
.report_tips {margin-top:30px; text-align:center; font-size:16px; font-family:"΢���ź�"; }
.center_show {margin:0 auto; width:90%; text-align:center; }

.excel_table {border:2px solid #c8c8c8; margin-top:20px; }
.excel_table td {padding:5px 10px 3px 5px; border:1px solid #d3d3d3; text-align:center; }
.excel_head td {background:#dce4e9; color:#ff8040; padding:5px 10px 3px 5px; border-bottom:1px solid #d3d3d3; text-align:center; }
.excel_index {background:#f3f3f3; border-right:1px solid #d3d3d3; text-align:center; padding-left:20px !important; padding-right:20px !important; }
.content_left {border-left:1px solid #efefef; }
.huizong td {color:red; }
.nodata td {padding:20px; text-align:center; color:gray; }

.kf {color:#0080c0; }
.kf:hover {color:red; }

.ml {margin-left:8px; }

.big_font {font-size:14px; }
.big_font:hover {font-size:14px; }

.wenhao {color:#1caee6 !important; font-style:italic; }

.wx_duijie {margin-top:20px; padding-left:10px; }
</style>

<script language="javascript">

</script>

</head>

<body style="padding:10px 20px;">

<div class="condition_set">
	<!-- <a href="ku_list.php" class="big_font">[�������Ͽ�]</a> -->

	<form method="GET" action="" onsubmit="" style="margin-left:20px;">
		ʱ��Σ�<input name="btime" id="begin_time" class="input" style="width:100px" value="<?php echo $_GET["btime"]; ?>" onclick="picker({el:'begin_time',dateFmt:'yyyy-MM-dd'})"> ~ <input name="etime" id="end_time" class="input" style="width:100px" value="<?php echo $_GET["etime"]; ?>" onclick="picker({el:'end_time',dateFmt:'yyyy-MM-dd'})">
		<select name="hospital" class="combo ml">
			<?php echo list_option($hos_options, "k", "v", $_GET["hospital"]); ?>
		</select>
		<select name="part_id" class="combo ml">
			<?php echo list_option($ku_part_arr, "_key_", "_value_", $_GET["part_id"]); ?>
		</select>
		<select name="area" class="combo ml">
			<?php echo list_option($ku_area_arr, "_key_", "_value_", $_GET["area"]); ?>
		</select>
		<input type="submit" class="button ml" value="ȷ��">
	</form>
</div>

<div class="center_show">
	<div class="report_tips">΢����ѯԱЧ��ͳ�Ʊ�</div>

	<table class="excel_table" width="100%">
		<tr class="excel_head">
			<td><nobr>����</nobr></td>
			<td><nobr>�ܷ�������</nobr></td>
			<td><nobr>��ӳɹ�</nobr></td>
			<td><nobr>δ�ɹ�</nobr></td>
			<td><nobr>΢��ԤԼ</nobr></td>
			<td><nobr>΢�ŵ�Ժ</nobr></td>
			<td><nobr>΢��ԤԼ�� <a href="javascript:;" title="΢����ԤԼ����΢�ź�ռ��" onclick="alert(this.title)" class="wenhao">?</a></nobr></td>
			<td><nobr>΢�ŵ�Ժ�� <a href="javascript:;" title="΢�ŵ�Ժ����΢�ź�ռ��" onclick="alert(this.title)" class="wenhao">?</a></nobr></td>
		</tr>

<?php
if (count($kefu_arr) == 0) {
	echo '<tr class="nodata"><td colspan="8">(������)</td></tr>';
} else {
	foreach ($kefu_arr as $kf) {
?>
		<tr class="excel_item" onmouseover="mi(this)" onmouseout="mo(this)">
			<td><nobr><?php echo $kf; ?></nobr></td>
			<td><nobr><?php echo $wx_all_num[$kf]; ?></nobr></td>
			<td><nobr><?php echo $wx_is_add[$kf]; ?></nobr></td>
			<td><nobr><?php echo $wx_not_add[$kf]; ?></nobr></td>
			<td><nobr><?php echo $wx_is_yuyue[$kf]; ?></nobr></td>
			<td><nobr><?php echo $wx_is_come[$kf]; ?></nobr></td>
			<td><nobr><?php echo @round(100 * $wx_is_yuyue[$kf] / $wx_all_num[$kf], 1)."%"; ?></nobr></td>
			<td><nobr><?php echo @round(100 * $wx_is_come[$kf] / $wx_all_num[$kf], 1)."%"; ?></nobr></td>
		</tr>
<?php
	}
?>

		<tr class="excel_item huizong" onmouseover="mi(this)" onmouseout="mo(this)">
			<td><nobr>����</nobr></td>
			<td><nobr><?php echo array_sum($wx_all_num); ?></nobr></td>
			<td><nobr><?php echo array_sum($wx_is_add); ?></nobr></td>
			<td><nobr><?php echo array_sum($wx_not_add); ?></nobr></td>
			<td><nobr><?php echo array_sum($wx_is_yuyue); ?></nobr></td>
			<td><nobr><?php echo array_sum($wx_is_come); ?></nobr></td>
			<td><nobr><?php echo @round(100 * array_sum($wx_is_yuyue) / array_sum($wx_all_num))."%"; ?></nobr></td>
			<td><nobr><?php echo @round(100 * array_sum($wx_is_come) / array_sum($wx_all_num))."%"; ?></nobr></td>
		</tr>

<?php } ?>

	</table>

	<div class="wx_duijie">ָ����΢���������� = <?php echo $zong_zhiding; ?>�����ѶԽ� = <?php echo array_sum($wx_all_num); ?>����δ�Խ� = <?php echo ($zong_zhiding - array_sum($wx_all_num)); ?></div>

</div>

<br>

</body>
</html>