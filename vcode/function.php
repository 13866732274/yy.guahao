<?php
/* --------------------------------------------------------
// 说明: 通过hash获得一个数字
// 作者: 幽兰 (weelia@126.com)
// 时间: 2011-04-14 14:50
// ----------------------------------------------------- */


function get_code_from_hash($str)
{
	$hash = md5($str);

	$num1 = ord(substr($hash, 27, 1)) + ord(substr($hash, 5, 1)) + ord(substr($hash, 19, 1));
	$num2 = ord(substr($hash, 3, 1)) + ord(substr($hash, 22, 1)) + ord(substr($hash, 27, 1));
	$num3 = ord(substr($hash, 8, 1)) + ord(substr($hash, 26, 1)) + ord(substr($hash, 14, 1));
	$num4 = ord(substr($hash, 13, 1)) + ord(substr($hash, 7, 1)) + ord(substr($hash, 31, 1));

	$num1 = $num1 % 10;
	$num2 = $num2 % 10;
	$num3 = $num3 % 10;
	$num4 = $num4 % 10;

	return $num1 . $num2 . $num3 . $num4;
}