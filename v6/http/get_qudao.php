<?php
// --------------------------------------------------------
// - 功能说明 : 获取接收人数组
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2014-01-10
// --------------------------------------------------------
header("Content-Type:text/JavaScript;charset=gbk");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

ob_start();
require "../lib/set_env.php";
require "../lib/class.fastjson.php";

$main_id = intval($_REQUEST["main_id"]);
$out_do = $_REQUEST["do"];

$out = array();
$out["status"] = "ok";
$arr = $db->query("select concat(id,'#',name) as name from dict_qudao where main_id=$main_id order by sort desc, id asc", "", "name");
$out["data"] = $arr;
$out["main_id"] = $main_id;

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
