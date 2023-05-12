<?php
// --------------------------------------------------------
// - 功能说明 : 文件上传函数(多文件upfiles,单个文件upfile)
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2008-01-22 16:16
// --------------------------------------------------------

/*
	上传单个文件的处理
	参数:
		$file_id_name 表单中文件域的名字 <input type='file' name='pic'> 中的"pic"
		$to_dir 要将文件保存到的文件夹位置
		$file_max_size 可选参数 字节为单位，限定文件的大小
		$allow_file_type 可选参数 限定文件的类型 array('mp3', 'wma')
	返回值:
		如果上传成功，则返回不包含路径的新文件名，否则返回false
	修改历史:
		2008-01-22 17:25
			本函数原版本有 $sql_read_old_file 参数,考虑函数内部的一致性和安全性,取消此功能,请在程序内自由控制
			加入参数 $allow_file_type
*/
function upfile($file_id_name, $to_dir='', $file_max_size=0, $allow_file_type=array()) {
	$count = 0; //用于累加计数

	// 判断目录是否存在,不存在的话将尝试创建之,失败则返回false:
	$to_dir = $to_dir ? (substr($to_dir, -1, 1) != "/" ? $to_dir."/" : $to_dir) : "./";
	if ($to_dir && !file_exists($to_dir) && !@mkdir($to_dir, 0777)) {
		return false;
	}

	if ($oriname = $_FILES[$file_id_name]['name']) {
		// 临时文件的名称:
		$tmpname = $_FILES[$file_id_name]['tmp_name'];

		// 文件大小检查:
		if ($file_max_size > 0 && filesize($tmpname) > $file_max_size) {
			echo "文件太大，超过限制(最大允许 ".round($file_max_size/1024)."KB)";
			return false;
		}

		// 文件类型检查:
		$ext = strtolower(strrchr($oriname,'.'));
		$just_ext = substr($ext, 1); // 不包含点号的ext
		if ($allow_file_type && !in_array($just_ext, $allow_file_type)) {
			echo "文件必须是(".implode("或", $allow_file_type).")格式！";
			return false;
		}

		// 生成一个不重复的文件名:
		$onlyname = date("Ymd_His_").rand(100, 999);
		$newname = $onlyname.$ext; // 形如:20080122_122340_432.jpg
		while (file_exists($to_dir.$newname)) {
			$newname = $onlyname."_".(++$count).$ext; // 形如:20080122_122340_432_1.jpg
		}

		// 移动临时文件:
		if (move_uploaded_file($tmpname, $to_dir.$newname)) {
			return $newname;
		} else {
			echo $newname;
			echo "文件移动失败！请检查目录写入权限！";
			return false;
		}
	}

	return false;
}


/*
	同时上传多个文件
	参数:
		$file_id_name: 这种格式定义的: <input type='file' name='pic[]'> "pic"就是这里的file_id_name
		$to_dir: 文件保存路径;
		$file_max_size: 限制文件的最大值,可以不指定(不限制)
		$allow_file_type: 格式 array('jpg', 'gif') 或 array('mp3', 'wma', 'wav', 'rm')
	返回值:
		上传成功后的文件名数组,包含路径,如果失败返回空数组
	最后修改:
		2008-01-22 16:37 by zhuwenya
*/
function upfiles($file_id_name, $to_dir='', $file_max_size=0, $allow_file_type=array()) {

	// to_dir 的处理，不存在将尝试创建，如果创建失败，将返回上传失败:
	$to_dir = $to_dir ? (substr($to_dir, -1, 1) != "/" ? $to_dir."/" : $to_dir) : "./";
	if ($to_dir && !file_exists($to_dir) && !@mkdir($to_dir, 0777)) {
		return false;
	}

	// 返回值数组初始化:
	$result = array();

	// 如果有上传文件:
	if ($file_count = count($_FILES[$file_id_name]["name"])) {
		$count = 0;
		for ($ni=0; $ni<$file_count; $ni++) {
			if ($upname = $_FILES[$file_id_name]["name"][$ni]) {
				// 上传信息:
				$uptmpname = $_FILES[$file_id_name]["tmp_name"][$ni];
				$upsize = $_FILES[$file_id_name]["size"][$ni];

				// 扩展名:
				$ext = strtolower(strrchr($upname, '.'));
				$just_ext = substr($ext, 1); // 不包含点号的ext

				// 文件大小限制检查:
				if ($file_max_size > 0 && $upsize > $file_max_size) {
					continue;
				}

				// 文件类型限制检查:
				if (is_array($allow_file_type) && count($allow_file_type) && !in_array($just_ext, $allow_file_type)) {
					continue;
				}

				// 计算新的文件名:
				$onlyname = date("Ymd_His_").rand(100, 999)."_".$ni; // 形如:20070925_092835_345_1.jpg
				$newname = $onlyname.$ext;

				// 判断名称有没有重复，如果重复的话，递加count,for 循环内count不归零
				while (file_exists($to_dir.$newname)) {
					$newname = $onlyname.(++$count).$ext; //形如:20070925_092835_345_12.jpg
				}

				// 移动这个文件:
				if (move_uploaded_file($uptmpname, $to_dir.$newname)) {
					$result[] = $newname;
				}
			}
		} //--> end for
	}

	return $result;
}

?>