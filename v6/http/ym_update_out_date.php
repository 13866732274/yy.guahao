<?php
// --------------------------------------------------------
// - 功能说明 : 更新域名到期时间
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-12-29
// --------------------------------------------------------
header("Content-Type:text/JavaScript;charset=gbk");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

function _get_just_domain($url) {
	if (substr_count($url, ".") >= 2) {
		$arr = explode(".", $url);
		unset($arr[0]);
		return implode(".", $arr);
	} else {
		return $url;
	}
}

ob_start();
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$ym_id = intval($_REQUEST["ym_id"]);
$out_do = $_REQUEST["do"];

$out = array();
$out["status"] = "ok";

$line = $db->query("select * from site_list where id=$ym_id limit 1", 1);

if ($line["auto_update"] > 0) {
	$out_date = trim(get_domain_out_date(_get_just_domain($line["site_url"])));
	if ($out_date != '') {
		$db->query("update site_list set out_date='$out_date' where id=$ym_id limit 1");
	}
} else {
	$out_date = "人工:".$line["out_date"];
}

$out["ym_id"] = $ym_id;
$out["out_date"] = $out_date;

$error = ob_get_clean();
if ($error == '') {
?>

var arr = <?php echo FastJSON::convert($out); ?>;

<?php } else { ?>

alert('<?php echo $error; ?>');
var arr = [];

<?php } ?>

<?php if ($out_do) { ?>
<?php   echo $out_do."(arr)"; ?>;
<?php } ?>
