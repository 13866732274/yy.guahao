<?php
// --------------------------------------------------------
// - 功能说明 : 商务通记录导入
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-11-11
// --------------------------------------------------------
require "lib/set_env.php";
$table = "count_web";

$hids = count($hospital_ids) ? implode(",", $hospital_ids) : "0";
$types_arr = $db->query("select id, h_name, name from count_type where type='web' and hid in ($hids) and ishide=0 and local_area!='' and swt_names!='' order by h_name asc, name asc", "id");
$hos_options = array();
foreach ($types_arr as $li) {
	$hos_options[] = array("k" => $li["id"], "v" => "　".$li["name"]);
}

$type_id = intval($_REQUEST["type_id"]);
if ($type_id <= 0) {
	$type_id = $_GET["type_id"] = $_SESSION["count_type_id_web"];
}

$swt_to_zixun_name = array();

if ($type_id > 0) {
	$type_info = $db->query("select * from count_type where id=$type_id limit 1", 1);
	if ($type_info["swt_names"] != "") {
		$swt_name = str_replace("\r", "", $type_info["swt_names"]);
		$swt_name_arr = explode("\n", $swt_name);
		foreach ($swt_name_arr as $v) {
			list($_zixun, $_swt) = explode("=", $v, 2);
			if ($_swt != '' && $_zixun != '') {
				$swt_to_zixun_name[$_swt] = $_zixun;
			}
		}
	}

	$swt_to_zixun_show = '';
	foreach ($swt_to_zixun_name as $k => $v) {
		$swt_to_zixun_show .= $k."=".$v."　　 ";
	}

}

if ($op == "do_update") {

	if (empty($_SESSION["swt_daoru"])) {
		exit("您已太久没有操作，或者已在别的窗口处理过了，请重新来过...");
	}

	$type_id = intval($_SESSION["swt_daoru"]["type_id"]);
	$all_date_list = $_SESSION["swt_daoru"]["all_date_list"];
	$swt_to_zixun_name = $_SESSION["swt_daoru"]["swt_to_zixun_name"];
	$all_click = $_SESSION["swt_daoru"]["all_click"];
	$click = $_SESSION["swt_daoru"]["click"];
	$click_local = $_SESSION["swt_daoru"]["click_local"];
	$click_other = $_SESSION["swt_daoru"]["click_other"];
	$ok_click = $_SESSION["swt_daoru"]["ok_click"];
	$ok_click_local = $_SESSION["swt_daoru"]["ok_click_local"];
	$ok_click_other = $_SESSION["swt_daoru"]["ok_click_other"];
	$file_upload_name = $_SESSION["swt_daoru"]["file_upload_name"];

	//echo "<pre>";
	//print_r($_SESSION["swt_daoru"]);

	$type_name = $db->query("select name from count_type where id=$type_id", 1, "name");

	foreach ($all_date_list as $date) {
		$kefu_names = array_keys($all_click[$date]);
		foreach ($kefu_names as $kefu) {
			$zixun = $swt_to_zixun_name[$kefu];
			if (empty($zixun)) continue; //略过

			$sql_set = array();
			$sql_set[] = "all_click=".intval($all_click[$date][$kefu]);
			$sql_set[] = "click=".intval($click[$date][$kefu]);
			$sql_set[] = "click_local=".intval($click_local[$date][$kefu]);
			$sql_set[] = "click_other=".intval($click_other[$date][$kefu]);
			$sql_set[] = "ok_click=".intval($ok_click[$date][$kefu]);
			$sql_set[] = "ok_click_local=".intval($ok_click_local[$date][$kefu]);
			$sql_set[] = "ok_click_other=".intval($ok_click_other[$date][$kefu]);

			$sql_set_str = implode(", ", $sql_set);

			// 检查是否已有数据:
			$old = $db->query("select * from count_web where type_id=$type_id and date=$date and kefu='$zixun'", 1);
			if ($old["id"] > 0) {
				$db->query("update count_web set $sql_set_str where id=".$old["id"]." limit 1");
			} else {
				$db->query("insert into count_web set type_id=$type_id, type_name='$type_name', date='$date', kefu='$zixun', $sql_set_str, addtime=$time, uid=$uid, u_realname='$realname', memo='SWT报表导入'");
			}
		}
	}

	//@unlink($file_upload_name);
	unset($_SESSION["swt_daoru"]);

	echo '<script> parent.msg_box("数据已成功更新", 2); </script>';
	echo '<script> parent.load_src(0); </script>';

	exit;
}

if ($op == "daoru_do") {

	//echo "<pre>";
	//print_r($_POST);
	//print_r($_FILES);

	// 上传文件:
	if ($_FILES["file"]["name"]) {

		$allow_file_type = explode(" ", "xls");
		$file_max_size = 8 * 1024 * 1024; //8M

		$to_dir = "v6/data/";
		@chmod(ROOT.$to_dir, 0777);

		$upname = $_FILES["file"]["name"];
		$type = $_FILES["file"]["type"];
		$uptmpname = $_FILES["file"]["tmp_name"];
		$upsize = $_FILES["file"]["size"];

		// 扩展名:
		$ext = strtolower(strrchr($upname, '.'));
		$just_ext = substr($ext, 1); // 不包含点号的ext

		// 如果文件名太长，裁切掉一些
		if (strlen($upname) > 50) {
			$elens = strlen($just_ext) + 1;
			$just_name = substr($upname, 0, -$elens);
			$just_name = cut($just_name, 40, "");
			$upname = $just_name.".".$just_ext;
		}

		// 文件大小限制检查:
		if ($file_max_size > 0 && $upsize > $file_max_size) {
			echo "“".$upname."” 文件大小超过".$file_max_size." 上传未成功<br>";
			exit;
		}

		// 文件类型限制检查:
		if (!in_array($just_ext, $allow_file_type)) {
			echo "“".$upname."” 不是允许的上传格式，该文件未上传成功<br>";
			exit;
		}

		// 计算新的文件名:
		$newname = "swt_".date("Ymd_His").$ext;

		// 移动这个文件:
		if (move_uploaded_file($uptmpname, ROOT.$to_dir.$newname)) {
			$file_upload_name = ROOT.$to_dir.$newname;
		}
	}


	if ($file_upload_name == '') {
		exit("文件未上传成功。");
	}

	if (substr($file_upload_name, -4) != ".xls") {
		exit("文件格式不正确，只能允许 .xls 格式。");
	}

	echo "文件 ".basename($file_upload_name)." 已上传成功<br>";

	require 'lib/PHPExcel/IOFactory.php';
	$objReader = PHPExcel_IOFactory::createReader('Excel5');
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($file_upload_name);
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$highestRow = $objWorksheet->getHighestRow();
	$highestColumn = $objWorksheet->getHighestColumn();
	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
	$excelData = array();

	echo "已加载到PHPExcel中<br>";
	echo "总行数 = ".$highestRow."<br>";

	// 去除第一行 表头:
	for ($column = 0; $column < 50; $column++) {
		$val = (string) $objWorksheet->getCellByColumnAndRow($column, 1)->getValue();
		$header[$column] = trim(mb_convert_encoding(trim($val), "gbk", "UTF-8"));
	}

	$need_head["date"] = array_search("开始访问时间", $header);
	$need_head["count_customer"] = array_search("客人讯息数", $header);
	$need_head["count_kefu"] = array_search("客服讯息数", $header);
	$need_head["area"] = array_search("IP定位", $header);
	$need_head["kefu"] = array_search("初始接待客服", $header);

	if ($need_head["date"] === false || $need_head["count_customer"] === false || $need_head["count_kefu"] === false || $need_head["area"] === false || $need_head["kefu"] === false) {
		exit("表头提取错误，第一行未包含足够的列：开始访问时间｜客人讯息数｜客服讯息数｜IP定位｜初始接待客服");
	}

	echo "表头分析完成<br>";

	for ($row = 2; $row <= $highestRow; $row++) {
		foreach ($need_head as $data_name => $column) {
			$val = (string) $objWorksheet->getCellByColumnAndRow($column, $row)->getValue();
			$data[$row][$data_name] = trim(mb_convert_encoding(trim($val), "gbk", "UTF-8"));
		}
	}

	echo "数据读取完成<br><br>";


	// 将文件删除
	//@unlink($file_upload_name);

	// 要更新的指定日期，如果没有则更新报表中的所有日期
	$todo_date = 0;
	if ($_POST["date"] != "") {
		$todo_date = date("Ymd", strtotime($_POST["date"]));
	}

	$kefu_data = array();
	foreach ($data as $arr) {
		$date = date("Ymd", strtotime(str_replace("/", "-", $arr["date"])));
		if ($todo_date > 0 && $date != $todo_date) continue;
		$all_date_list[$date] = $date;
		$kefu = $arr["kefu"];
		if ($kefu == '') {
			continue;
		}
		if ($arr["count_customer"] + $arr["count_kefu"] >= 1) {
			$all_click[$date][$kefu] += 1;
		}
		if ($arr["count_customer"] >= 1 && $arr["count_kefu"] >= 1) {
			$click[$date][$kefu] += 1;
			if (substr_count($arr["area"], $type_info["local_area"]) > 0) {
				$click_local[$date][$kefu] += 1;
			} else {
				$click_other[$date][$kefu] += 1;
			}
		}
		if ($arr["count_customer"] >= 5 && $arr["count_kefu"] >= 5) {
			$ok_click[$date][$kefu] += 1;
			if (substr_count($arr["area"], $type_info["local_area"]) > 0) {
				$ok_click_local[$date][$kefu] += 1;
			} else {
				$ok_click_other[$date][$kefu] += 1;
			}
		}
	}


	// 生成数据预览:
	$preview = '';
	foreach ($all_date_list as $date) {
		$preview .= '<br><div>日期：'.int_date_to_date($date).'</div>';
		$preview .= '<table width="100%" class="list"><tr>';
		$preview .= '  <td class="head">商务通客服</td>';
		$preview .= '  <td class="head">对应咨询员姓名</td>';
		$preview .= '  <td class="head">所有点击(1条起)</td>';
		$preview .= '  <td class="head">总点击(1条对1条)</td>';
		$preview .= '  <td class="head">本地</td>';
		$preview .= '  <td class="head">外地</td>';
		$preview .= '  <td class="head">有效(5条对5条)</td>';
		$preview .= '  <td class="head">本地</td>';
		$preview .= '  <td class="head">外地</td>';
		$preview .= '</tr>';

		$_kefu_name = array_keys($all_click[$date]);
		sort($_kefu_name);
		foreach ($_kefu_name as $kefu) {
			$preview .= '<tr>';
			$preview .= '  <td class="item">'.$kefu.'</td>';
			$preview .= '  <td class="item">'.$swt_to_zixun_name[$kefu].'</td>';
			$preview .= '  <td class="item">'.$all_click[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$click[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$click_local[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$click_other[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$ok_click[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$ok_click_local[$date][$kefu].'</td>';
			$preview .= '  <td class="item">'.$ok_click_other[$date][$kefu].'</td>';
			$preview .= '</tr>';
		}
		$preview .= '</table>';
	}

	$_SESSION["swt_daoru"] = array();
	if (count($all_date_list) > 0 && count($all_click) > 0) {
		$_SESSION["swt_daoru"]["type_id"] = $type_id;
		$_SESSION["swt_daoru"]["all_date_list"] = $all_date_list;
		$_SESSION["swt_daoru"]["swt_to_zixun_name"] = $swt_to_zixun_name;
		$_SESSION["swt_daoru"]["all_click"] = $all_click;
		$_SESSION["swt_daoru"]["click"] = $click;
		$_SESSION["swt_daoru"]["click_local"] = $click_local;
		$_SESSION["swt_daoru"]["click_other"] = $click_other;
		$_SESSION["swt_daoru"]["ok_click"] = $ok_click;
		$_SESSION["swt_daoru"]["ok_click_local"] = $ok_click_local;
		$_SESSION["swt_daoru"]["ok_click_other"] = $ok_click_other;
		$_SESSION["swt_daoru"]["file_upload_name"] = $file_upload_name;
	} else {
		$preview_nodata = '没有指定日期的数据，或者报表数据格式有误，请检查';
	}

	$in_preview_mode = 1;
}


?>
<html>
<head>
<title>商务通记录导入</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>
<script src="lib/datejs/picker.js" language="javascript"></script>

<style type="text/css">
.input_file {font-size:12px; }
#submit_div {text-align:center; margin-top:30px; }
</style>

<script type="text/javascript">
function reload(obj) {
	if (obj.value > 0) {
		self.location = "?type_id="+obj.value;
	}
}
function check_data(f) {
	if (!confirm("上传表格并分析过程较慢，提交后请耐心等待，过程中不要进行其他操作。是否确定继续提交？")) {
		return false;
	}
	byid("submit_div").innerHTML = '<img src="image/loading.gif" align="absmiddle"> 提交分析中，请稍候...';
}
</script>


</head>

<body style="padding:30px; ">

<?php if ($in_preview_mode) { ?>

	<?php if ($preview_nodata) { ?>
		<?php echo $preview_nodata; ?>
	<?php } else { ?>
		<div>请预览计算数据，点击确定更新，将数据保存到系统中　（若对应咨询员姓名为空，则该行数据不会更新）</div>
		<?php echo $preview; ?>
		<form name="mainform" method="GET">
			<div id="submit_div"><input type="submit" class="submit" value="确定更新"></div>
			<input type="hidden" name="op" value="do_update">
		</form>
	<?php } ?>

<?php } else { ?>

	<form name="mainform" method="POST" enctype="multipart/form-data" onsubmit="return check_data(this)">
	<table width="100%" class="new_edit">
		<tr>
			<td class="left"><font color="red">*</font> 数据要导入的统计项：</td>
			<td class="right">
				<select name="type_id" class="combo" onchange="reload(this)" style="width:250px">
					<option value="">　　　　</option>
					<?php echo list_option($hos_options, "k", "v", $_GET["type_id"]); ?>
				</select>　(若所需统计项未出现，则表示商务通对应名单没有设置好)
			</td>
		</tr>
	<?php if ($_GET["type_id"] > 0) { ?>
		<tr>
			<td class="left">本地所在地：</td>
			<td class="right">
				<?php echo $type_info["local_area"]; ?>
			</td>
		</tr>
		<tr>
			<td class="left">商务通姓名对应咨询员姓名：</td>
			<td class="right">
				<?php echo trim($swt_to_zixun_show); ?>
			</td>
		</tr>
	<?php } ?>
		<tr>
			<td class="left"><font color="red">*</font> 商务通报表文件：</td>
			<td class="right"><input name="file" type="file" class="input_file"  style="width:250px">　(须包含字段：开始访问时间｜客人讯息数｜客服讯息数｜IP定位｜初始接待客服)</td>
		</tr>
		<tr>
			<td class="left">更新日期：</td>
			<td class="right"><input name="date" id="date" class="input" style="width:100px" value="" onclick="picker({el:'date',dateFmt:'yyyy-MM-dd'})">　(如果不选，则以报表所含的日期更新系统)</td>
		</tr>
	</table>

	<div id="submit_div"><input type="submit" class="submit" value="提交资料"></div>

	<input type="hidden" name="op" value="daoru_do">

	</form>

	<!-- 预载入图片 -->
	<div style="display:none;"><img src="image/loading.gif"></div>

<?php } ?>

</body>
</html>
