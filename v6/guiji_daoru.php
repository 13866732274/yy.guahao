<?php
// --------------------------------------------------------
// - 功能说明 : 商务通记录导入
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2016-11-11
// --------------------------------------------------------
require "lib/set_env.php";

// 要导入的医院，当前医院优先
$hids = count($hospital_ids) ? implode(",", $hospital_ids) : "0";
$sname_arr = $db->query("select distinct sname from hospital where ishide=0 and id in ($hids) order by sname asc", "sname", "sname");
$cur_sname = $hinfo["sname"];


if ($op == "daoru_do") {

	// 是否预览模式
	$is_preview = $_POST["is_preview"] ? 1 : 0;

	// 上传文件:
	if ($_FILES["file"]["name"]) {
		$to_dir = "v4/data/";
		@chmod(ROOT.$to_dir, 0777);

		$ext = strtolower(strrchr($_FILES["file"]["name"], '.'));
		if (!in_array($ext, explode(" ", ".xls"))) {
			echo "不是所需的文件格式，仅支持.xls格式";
			exit;
		}

		$newname = "gj_".date("Ymd_His").$ext;
		if (move_uploaded_file($_FILES["file"]["tmp_name"], ROOT.$to_dir.$newname)) {
			$file_upload_name = ROOT.$to_dir.$newname;
		}
	}

	if ($file_upload_name == '') {
		exit("文件未上传成功。");
	}

	require 'lib/PHPExcel/IOFactory.php';
	$objReader = PHPExcel_IOFactory::createReader('Excel5');
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($file_upload_name);
	$objWorksheet = $objPHPExcel->getActiveSheet();
	$highestRow = $objWorksheet->getHighestRow();
	$highestColumn = $objWorksheet->getHighestColumn();
	$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
	$excelData = array();

	// 去除第一行 表头:
	for ($column = 0; $column < 50; $column++) {
		$val = (string) $objWorksheet->getCellByColumnAndRow($column, 1)->getValue();
		$header[$column] = trim(mb_convert_encoding(trim($val), "gbk", "UTF-8"));
	}


	$need_head["sfid"] = array_search("永久身份", $header);
	$need_head["keyword"] = array_search("关键词", $header);
	$need_head["engine"] = array_search("访问来源", $header);
	$need_head["from_site"] = array_search("初次访问网址", $header);

	if ($need_head["sfid"] === false || $need_head["keyword"] === false || $need_head["engine"] === false || $need_head["from_site"] === false) {
		exit("表头提取错误，第一行未包含足够的列：永久身份|关键词|访问来源|初次访问网址");
	}

	if ($is_preview) {
		echo '<title>预览模式</title><style type="text/css">* {font-size:12px; }</style>';
	}

	if ($is_preview) {
		echo '永久身份 位于第 '.$need_head["sfid"]." 列<br>";
		echo '访问来源 位于第 '.$need_head["engine"]." 列<br>";
		echo '初次访问网址 位于第 '.$need_head["from_site"]." 列<br>";
		echo '关键词 位于第 '.$need_head["keyword"]." 列<br><br>";
	}

	for ($row = 2; $row <= $highestRow; $row++) {
		foreach ($need_head as $data_name => $column) {
			$val = (string) $objWorksheet->getCellByColumnAndRow($column, $row)->getValue();
			$data[$row][$data_name] = trim(mb_convert_encoding(trim($val), "gbk", "UTF-8"));
		}
	}


	// 将文件删除
	//@unlink($file_upload_name);

	// 轨迹匹配设置:
	$guiji_set_arr = $db->query("select id, main_id, main_name, name, guiji_key_type, guiji_keyword from dict_qudao where guiji_key_type!='' and guiji_keyword!='' order by sort desc", "id");
	foreach ($guiji_set_arr as $id => $set) {
		$guiji_set_arr[$id]["guiji_key_arr"] = explode("\n", trim(str_replace("\r", "", $set["guiji_keyword"])));
	}

	// 将要更新的医院:
	$sname = trim($_POST["sname"]);
	$hid_arr = $db->query("select id, name from hospital where sname='$sname' and ishide=0 order by id asc", "id", "name");

	// 对每一条记录进行判断:
	$all_num_update = 0;
	foreach ($data as $line_id => $li) {
		$swt_id = str_replace("'", "", $li["sfid"]);
		if ($swt_id != "") {
			$qudao_id = $guiji_id = "";
			foreach ($guiji_set_arr as $id => $set) {
				$tocheck_string = "";
				if ($set["guiji_key_type"] == "engine") {
					$tocheck_string = $li["engine"];
				} else if ($set["guiji_key_type"] == "site_url") {
					$tocheck_string = $li["from_site"];
				} else {
					$tocheck_string = $li["engine"]."   ".$li["from_site"];
				}
				foreach ($set["guiji_key_arr"] as $key) {
					if (substr_count($tocheck_string, $key) > 0) {
						$guiji_id = $set["main_id"];
						$qudao_id = $set["id"];
						break;
					}
				}
				if ($qudao_id > 0) {
					break;
				}
			}

			$from_site = '';
			if ($li["from_site"] != "") {
				$from_site = str_replace("http://", "", $li["from_site"]);
				if (substr_count($from_site, "/") > 0) {
					list($from_site, $tmp) = explode("/", $from_site, 2);
				}
				$from_site = str_replace('"', "", $from_site);
				$from_site = str_replace("'", "", $from_site);
				$from_site = trim($from_site);
				if (strlen($from_site) > 20) {
					$from_site = substr($from_site, 0, 20)."…";
				}
			}

			$keyword = '';
			if ($li["keyword"] != "") {
				$keyword = str_replace('"', "", $li["keyword"]);
				$keyword = str_replace("'", "", $keyword);
				$keyword = str_replace("　", "", $keyword);
				$keyword = str_replace(",", "", $keyword);
				$keyword = str_replace("，", "", $keyword);
				$keyword = str_replace("。", "", $keyword);
				$keyword = str_replace(".", "", $keyword);
				$keyword = trim($keyword);
				if (strlen($keyword) > 40) {
					$keyword = cut($keyword, 0, 40)."…";
				}
			}

			$to_update = array();
			if ($qudao_id > 0) {
				$to_update[] = 'guiji='.$guiji_id;
				$to_update[] = 'qudao='.$qudao_id;
			}
			if ($from_site != '') {
				$to_update[] = 'from_site="'.$from_site.'"';
			}
			if ($keyword != "") {
				$to_update[] = 'key_word="'.$keyword.'"';
			}
			$to_update_sql = implode(", ", $to_update);

			if ($is_preview) {
				echo "永久身份 = ".$swt_id."<br>";
				echo "访问来源 = ".$li["engine"]."<br>";
				echo "初次访问网站 = ".$li["from_site"]."<br>";
				echo "搜索关键词 = ".$li["keyword"]."<br>";
				echo "<b>判定结果：</b><br><font color=red>";
				echo "轨迹 = ".($guiji_id > 0 ? $guiji_arr[$guiji_id] : "")."<br>";
				echo "渠道 = ".($qudao_id > 0 ? $guiji_set_arr[$qudao_id]["name"] : "")."<br>";
				echo "来源站点 = ".$from_site."<br>";
				echo "关键词 = ".$keyword."<br></font>";
				foreach ($hid_arr as $hid => $hname) {
					$line = $db->query("select name, tel from patient_{$hid} where swt_id='$swt_id'", 1);
					if (count($line) > 0) {
						echo "<font color=blue>关联患者 = ".$line["name"]." 手机 ".$line["tel"]." ".$hname."</font><br>";
						break;
					}
				}
				echo "<br><br><br>";

				if ($preview_nums ++ > 50) {
					echo "预览模式最多显示50条...<br>";
					exit;
				}
			} else {
				foreach ($hid_arr as $hid => $hname) {
					//$db->query("update patient_{$hid} set $to_update_sql where swt_id='$swt_id'");
					//$all_num_update += mysql_affected_rows();
				}
			}
		}
	}

	if ($is_preview) {
		exit;
	}

	echo '<script> parent.msg_box("更新成功数='.$all_num_update.'", 2); </script>';
	echo '<script> parent.load_src(0); </script>';
	exit;

}


?>
<html>
<head>
<title>导入轨迹</title>
<meta http-equiv="Content-Type" content="text/html;charset=gb2312">
<link href="lib/base.css" rel="stylesheet" type="text/css">
<script src="lib/base.js" language="javascript"></script>

<style type="text/css">
.input_file {font-size:12px; }
#submit_div {text-align:center; margin-top:30px; }
</style>

<script type="text/javascript">
function check_data(f) {
	if (!confirm("上传表格并分析过程较慢，提交后请耐心等待，过程中不要进行其他操作。是否确定继续提交？")) {
		return false;
	}
	byid("submit_div").innerHTML = '<img src="image/loading.gif" align="absmiddle"> 提交分析中，请稍候...';
}
</script>


</head>

<body style="padding:30px; ">

<form name="mainform" method="POST" enctype="multipart/form-data" onsubmit="return check_data(this)">
<table width="100%" class="new_edit">
	<tr>
		<td class="left"><font color="red">*</font> 选择要导入的医院：</td>
		<td class="right">
			<select name="sname" class="combo" style="width:200px">
				<option value="">　　　　</option>
				<?php echo list_option($sname_arr, "_key_", "_value_", $cur_sname); ?>
			</select>　(将会更新本医院所有科室)
		</td>
	</tr>
	<tr>
		<td class="left"><font color="red">*</font> 商务通报表文件：</td>
		<td class="right"><input name="file" type="file" class="input_file"  style="width:200px">　(须包含字段：永久身份|关键词|访问来源|初次访问网址)</td>
	</tr>
	<tr>
		<td class="left"></td>
		<td class="right"><input type="checkbox" name="is_preview" value="1" id="chk_1"><label for="chk_1">预览模式</label></td>
	</tr>
</table>

<div id="submit_div"><input type="submit" class="submit" value="提交资料"></div>

<input type="hidden" name="op" value="daoru_do">

</form>

<!-- 预载入图片 -->
<div style="display:none;"><img src="image/loading.gif"></div>

</body>
</html>
