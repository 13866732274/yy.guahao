<?php
// --------------------------------------------------------
// - 功能说明 : 资料库报表 - 总表
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-07-23
// --------------------------------------------------------
require "lib/set_env.php";
include "ku.config.php";
include "ku_report.config.php";
$table = "ku_list";


/*
if ($hid <= 0) {
	$hid = $_SESSION[$cfgSessionName]["hospital_id"] = $allow_hid[0];
	echo "<script>self.location.reload();</script>";
	exit;
}
*/

$sname = $hinfo["sname"];
$hid_arr = $db->query("select * from hospital where ishide=0 and sname='$sname' order by sort desc, name asc", "id");
$hids = implode(",", array_keys($hid_arr));

$cur_hid_has_data = 1;


// 日期默认值
if ($_GET["btime"] == "") $_GET["btime"] = date("Y-m-01");
if ($_GET["etime"] == "") $_GET["etime"] = date("Y-m-d");

$t_begin = strtotime($_GET["btime"]);
$t_end = strtotime($_GET["etime"]." 23:59:59");

$int_tb = date("Ymd", $t_begin);
$int_te = date("Ymd", $t_end);


if ($cur_hid_has_data) {

	//$hids = $hebing_string[$hid];

	// 所有客服:
	/*
	$_hid_arr = $hebing_arr[$hid];
	foreach ($_hid_arr as $_hid) {
		$_cond[] = "concat(',', hospitals, ',') like '%,{$_hid},%'";
	}
	$_cond_str = implode(" or ", $_cond);
	$kefu_arr = $db->query("select id, realname from sys_admin where isshow=1 and part_id in (2,3,12) and character_id not in (15,16) and ($_cond_str) order by part_id asc, realname asc", "id", "realname");
	*/

	// 读取商务通有效未预约数据 (在所有注册的子统计中查询)
	$sub_type_arr = $db->query("select id from count_type where type='web' and hid in ($hids) and ishide=0", "", "id");
	$sub_type_ids = count($sub_type_arr) > 0 ? implode(",", $sub_type_arr) : "0";
	$swt_weiyue = $db->query("select kefu, sum(ok_click)-sum(talk_swt) as c from count_web where type_id in ($sub_type_ids) and date>=$int_tb and date<=$int_te group by kefu", "kefu", "c");


	// 查询目标数据-----------
	$data_arr = $db->query("select qq, weixin, is_yuyue, is_come, u_name from $table where hid in ($hids) and addtime>=$t_begin and addtime<=$t_end");

	// 分析过程
	$data_add = $data_wx = $data_qq = $data_dh = $data_dx = $data_yuyue = $data_daozhen = array();
	foreach ($data_arr as $li) {
		$kf = $li["u_name"];
		$data_add[$kf] ++;

		if ($li["qq"] != '') {
			$data_add_qq[$kf]++;
		}

		if ($li["weixin"] != "") {
			$data_add_weixin[$kf]++;
		}

		if ($li["is_yuyue"]) {
			$data_yuyue[$kf] ++;
		}
		if ($li["is_come"]) {
			$data_daozhen[$kf] ++;
		}
	}

	$data_dh = $db->query("select author, count(*) as c from ku_huifang where hid in ($hids) and addtime>=$t_begin and addtime<=$t_end and qudao='电话' group by author", "author", "c");
	$data_wx = $db->query("select author, count(*) as c from ku_huifang where hid in ($hids) and addtime>=$t_begin and addtime<=$t_end and qudao='微信' group by author", "author", "c");
	$data_qq = $db->query("select author, count(*) as c from ku_huifang where hid in ($hids) and addtime>=$t_begin and addtime<=$t_end and qudao='QQ' group by author", "author", "c");
	$data_dx = $db->query("select author, count(*) as c from ku_huifang where hid in ($hids) and addtime>=$t_begin and addtime<=$t_end and qudao='短信' group by author", "author", "c");



	$kefu_arr = array_keys($data_add);
	sort($kefu_arr);

}

?>
<html>
<head>
<title>资料库报表</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>

<style>
* {font-family:"微软雅黑","Tahoma" !important; font-size:12px;  }
body {overflow-x:auto !important;}
form {display:inline; }

.condition_set {text-align:center; margin-top:20px; }
.column_sortable {cursor:pointer; color:blue; padding:8px 3px 6px 3px !important; }
.report_tips {margin-top:15px; text-align:center; font-size:16px; font-family:"微软雅黑"; }
.center_show {margin:0 auto; text-align:center; }

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
</style>

<script language="javascript">
function show_kf_detail(kf) {
	var tb = byid("begin_time").value;
	var te = byid("end_time").value;
	var url = "ku_report_kf.php?js_kf="+encodeURIComponent(kf)+"&btime="+tb+"&etime="+te;
	parent.load_src(1, url);
}

function change_hospital(obj) {
	if (obj.value > 0) {
		var tb = byid("begin_time").value;
		var te = byid("end_time").value;
		self.location = "?op=change_hospital&hid="+obj.value+"&btime="+tb+"&etime="+te;
	}
}

function hgo(dir){
	var t="已经是最"+(dir=="up"?"上":"下")+"一家医院了";
	var obj=byid("hospital_id");if(dir=="up"){var i=obj.selectedIndex-1;while(i>0){if(obj.options[i].value>0){obj.selectedIndex=i;obj.onchange();break}i--}if(i==0){parent.msg_box(t,3)}}if(dir=="down"){var i=obj.selectedIndex+1;while(i<obj.options.length){if(obj.options[i].value>0){obj.selectedIndex=i;obj.onchange();break}i++}if(i==obj.options.length){parent.msg_box(t,3)}}
}
</script>

</head>

<body style="padding:10px 20px;">

<div class="center_show">
	<div class="report_tips"><?php echo $sname; ?> <?php echo " (".count($hid_arr)."个科室)　跟踪汇总表 (".$_GET["btime"]."~".$_GET["etime"].")"; ?></div>

	<table class="excel_table" width="100%">
		<tr class="excel_head">
			<td><nobr>咨询员</nobr></td>
			<td><nobr>商务通有效未预约</nobr></td>
			<td><nobr>增加人数</nobr></td>
			<td><nobr>微信增加人数</nobr></td>
			<td><nobr>QQ增加人数</nobr></td>
			<td><nobr>电话跟踪次数</nobr></td>
			<td><nobr>微信跟踪次数</nobr></td>
			<td><nobr>QQ跟踪次数</nobr></td>
			<td><nobr>短信跟踪次数</nobr></td>
			<td><nobr>转预约人数</nobr></td>
			<td><nobr>到诊人数</nobr></td>
		</tr>

<?php
	foreach ($kefu_arr as $kf) {
?>
		<tr class="excel_item" onmouseover="mi(this)" onmouseout="mo(this)">
			<td><nobr><?php echo $kf; ?></nobr></td>
			<td><nobr><?php echo $swt_weiyue[$kf] > 0 ? $swt_weiyue[$kf] : $swt_weiyue[str_replace("【HE】", "", $kf)]; ?></nobr></td>
			<td><nobr><?php echo $data_add[$kf]; ?></nobr></td>
			<td><nobr><?php echo $data_add_weixin[$kf]; ?></nobr></td>
			<td><nobr><?php echo $data_add_qq[$kf]; ?></nobr></td>
			<td><nobr><?php echo $data_dh[$kf]; ?></nobr></td>
			<td><nobr><?php echo $data_wx[$kf]; ?></nobr></td>
			<td><nobr><?php echo $data_qq[$kf]; ?></nobr></td>
			<td><nobr><?php echo $data_dx[$kf]; ?></nobr></td>
			<td><nobr><?php echo $data_yuyue[$kf]; ?></nobr></td>
			<td><nobr><?php echo $data_daozhen[$kf]; ?></nobr></td>
		</tr>
<?php
	}
?>

		<tr class="excel_item huizong" onmouseover="mi(this)" onmouseout="mo(this)">
			<td><nobr>汇总</nobr></td>
			<td><nobr><?php echo array_sum($swt_weiyue); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_add); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_add_weixin); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_add_qq); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_dh); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_wx); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_qq); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_dx); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_yuyue); ?></nobr></td>
			<td><nobr><?php echo array_sum($data_daozhen); ?></nobr></td>
		</tr>

	</table>
</div>

<br>

</body>
</html>