<?php
// --------------------------------------------------------
// - 功能说明 : 检查电话号码是否可以提交
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2012-02-15
// --------------------------------------------------------
header("Content-Type:text/html;charset=GB2312");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$table = "patient_" . $hid;

$out           = array();
$out["status"] = "bad";
$out["tips"]   = '';

$tel = trim($_GET["tel"]);
if ($tel != '') {
	// 处理电话号码：
	$tel = str_replace("o", "0", $tel);
	$tel = str_replace("O", "0", $tel);
	// 将大写数字转换为小写:
	$char_arr = explode(" ", "０ １ ２ ３ ４ ５ ６ ７ ８ ９");
	foreach ($char_arr as $k => $v) {
		$tel = str_replace($v, $k, $tel);
		}
	//过滤不是数字的字符:
	$shuzi_arr = explode(" ", "0 1 2 3 4 5 6 7 8 9");
	$tel_lens  = strlen($tel);
	$new_tel   = '';
	for ($i = 0; $i < $tel_lens; $i++) {
		if (in_array($tel { $i}, $shuzi_arr)) {
			$new_tel .= $tel { $i};
			}
		}
	$tel = $new_tel;

	$time = time();

	// 电话号码重复检查
	$_hinfo = $db->query("select * from hospital where id=$hid limit 1", 1);
	if ($_hinfo["repeat_open"]) {
		$deny_days = $_hinfo["repeat_deny_time"] > 0 ? $_hinfo["repeat_deny_time"] : $cfgRepeatDenyDays;
		$deny_sec  = $deny_days * 24 * 3600; //秒数
		// 重复检查:
		if (strlen($tel) >= 7) {
			$count = $db->query("select count(*) as c from $table where tel='$tel' and abs({$time}-addtime)<{$deny_sec}", 1, "c");
			if ($count > 0) {
				$out["tips"] = "不能提交：电话号码“{$tel}”重复{$count}次。(系统设置" . $deny_days . "天内号码不允许重复)";
				}
			}
		if ($out["tips"] == '') {
			$out["tips"] = "经检查，号码“{$tel}”在{$deny_days}天内无重复，可以提交。";
			}
		} else {
		$out["tips"] = "该医院或科室未启用号码重复检测，即使重复，也可以提交的。";
		}
	$out["status"] = 'ok';
	$out["tel"]    = $tel;
	}

echo FastJSON::convert($out);