<?php
/*
// 说明:
// 作者: 幽兰 (934834734@qq.com)
// 时间:
*/
include "../v6/lib/set_env.php";

$sort_head = mb_convert_encoding(strip_tags($_GET["sort_head"]), "gbk", "UTF-8");
$sort_type = $_GET["sort_type"];
$str       = date("Y-m-d H:i:s") . " " . $realname . " [" . $sort_head . "] " . $sort_type . "\r\n";
@file_put_contents("log.txt", $str, FILE_APPEND);