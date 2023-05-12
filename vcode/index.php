<?php
/*
// - 功能说明 : 创建一幅验证码图片
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2007-11-23 09:39
*/
include dirname(__FILE__) . "/function.php";

/*
	参数说明：
	s: 一个随机参数
	w: width，验证码图片宽度;
	h: height，验证码图片高度;
*/

// 获取当前目录下的所有字体文件:
$aFonts = array();
$handle = opendir(dirname(__FILE__));
while (false !== ($file = readdir($handle))) {
	if (strrchr($file, ".") == ".ttf") {
		$aFonts[] = $file;
	}
}
closedir($handle);

// 随机挑选一种字体使用:
if (($nFonts = count($aFonts)) > 0) {
	$rand = rand() % $nFonts;
	$fontname = $aFonts[$rand];
} else {
	exit("error: no font file found!");
}

// 验证码的大小:
$w = $_GET["w"] ? max(40, intval($_GET["w"])) : 60;
$h = $_GET["h"] ? max(16, intval($_GET["h"])) : 20;

// 缩进幅度:
$offsetx = 6;
$offsety = 6;

$Out = get_code_from_hash($_GET["s"]);


// 生成需要的图片:
$im = imagecreate($w, $h);
$bg = imagecolorallocate($im, 235, 235, 245);
$bd = imagecolorallocate($im, 102, 102, 102);

// 随机的线条:
for ($ni = 0; $ni < 8; $ni++) {
	$lc = imagecolorallocate($im, rand() % 55 + 200, rand() % 55 + 200, rand() % 55 + 200);
	imageline($im, rand() % $w, rand() % $h, rand() % $w, rand() % $h, $lc);
}

// 随机的像素点:
for ($ni = 0; $ni < 100; $ni++) {
	$pc = imagecolorallocate($im, rand() % 255, rand() % 255, rand() % 255);
	imagesetpixel($im, rand() % $w, rand() % $h, $pc);
}

// 边框线:
imagepolygon($im, array(0, 0, $w - 1, 0, $w - 1, $h - 1, 0, $h - 1), 4, $bd);

// 字:
$charlens = strlen($Out);
$fontsize = floor(min(($w - $offsetx) / $charlens, $h - $offsety));
for ($ni = 0; $ni < $charlens; $ni++) {
	$fc = imagecolorallocate($im, rand() % 100, rand() % 100, rand() % 100);
	$txt = substr($Out, $ni, 1);
	$x = $ni * (($w - $offsetx) / $charlens) + $offsetx / 2 + (($w - $offsetx) / $charlens - $fontsize) / 2;
	$y = rand() % 3 - 1 + $h / 2 + $fontsize / 2;
	imagettftext($im, $fontsize, rand() % 40 - 15, $x, $y, $fc, $fontname, $txt);
}

// 输出:
header("Content-type: image/jpeg");
imagejpeg($im);
imagedestroy($im);

// 随机获取一个验证码号码:
function GetVCode($CharSet = 0)
{
	$aUseBit = array(2, 12, 15, 31);
	switch ($CharSet) {
		case 1:
			$UseChars = "abcdefghijkmnpqrstuvwxy";
			break;
		case 2:
			$UseChars = "0123456789abcdefghijkmnpqrstuvwxy";
			break;
		default:
			$UseChars = "0123456789";
	}
	$UseCharLen = strlen($UseChars);
	$Out = "";
	foreach ($aUseBit as $pos) {
		$rand = rand() % $UseCharLen;
		$Out .= substr($UseChars, $rand, 1);
	}

	return $Out;
}