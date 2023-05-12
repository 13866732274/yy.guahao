<?php
// --------------------------------------------------------
// - 功能说明 : 图像函数库
// - 注意事项 : 该库中的函数需要加载模块gd2.dll方能使用
// - 创建作者 : zhuwenya (zhuwenya@126.com)
// - 创建时间 : 2006-01-15 21:50
// --------------------------------------------------------
function GenThumb($InFile, $ToDir, $nWidth=128, $nHeight=128)
{
	$Ext = strrchr($InFile, ".");
	$OnlyName = basename(substr($InFile, 0, strlen($InFile) - strlen($Ext)));
	$NewName = $ToDir . $OnlyName . "s.jpg";
	$Count = 0;
	while (file_exists($NewName))
	{
		$NewName = $ToDir . $OnlyName . "s" . (++$Count) . ".jpg";
	}
	if (! image_resize($InFile, $NewName, $nWidth, $nHeight))
	{
		$NewName = "";
	}

	return $NewName;
}

function ResizeTo($cImageFile, $nNewWidth, $nNewHeight)
{
	$cOutFile = md5(time()*rand()) . "jpg";
	if (image_resize($cImageFile, $cOutFile, $nNewWidth, $nNewHeight))
	{
		unlink($cImageFile);
		rename($cOutFile, $cImageFile);
	}

	return $cImageFile;
}

function image_resize($cImageFile, $cOutFile, $nNewWidth, $nNewHeight)
{
	if (! file_exists($cImageFile))
	{
		return false;
	}

	if (! function_exists("imagejpeg"))
	{
		//copy($cImageFile, $cOutFile);
		//return true;
		return false;
	}

	list($nW, $nH) = getimagesize($cImageFile);

	// 依照图像格式创建一幅图片:
	switch (strtolower(substr($cImageFile, -4, 4)))
	{
		case ".jpg":
			$im = imagecreatefromjpeg($cImageFile); break;
		case ".gif":
			$im = imagecreatefromgif($cImageFile); break;
		case ".png":
			$im = imagecreatefrompng($cImageFile); break;
		case ".bmp":
			$im = imagecreatefromwbmp($cImageFile); break;
		default:
			// 指定的文件可能不是图像文件！
			return false;
	}

	// 若不等比例，则按照原图比例重新计算新图片边长:
	if ($nW / $nH != $nNewWidth / $nNewHeight)
	{
		$nRate = min($nNewWidth / $nW, $nNewHeight / $nH);
		$nNewW = $nW * $nRate;
		$nNewH = $nH * $nRate;
	} else {
		$nNewW = $nNewWidth;
		$nNewH = $nNewHeight;
	}
	$im1 = imagecreatetruecolor($nNewW, $nNewH);
	imagecopyresampled($im1, $im, 0, 0, 0, 0, $nNewW, $nNewH, $nW, $nH);
	$nW = $nNewW;
	$nH = $nNewH;
	imagedestroy($im);
	$im = $im1;

	// 将图像输出到文件并释放内存:
	if (file_exists($cOutFile))
	{
		unlink($cOutFile);
	}
	imagejpeg($im, $cOutFile, 85);
	imagedestroy($im);

	return true;
}

// ~~~~~~~~~~ 裁切的方法变换图片大小
function image_resize_cut($cImageFile, $cOutFile, $nNewW, $nNewH)
{
	if (!file_exists($cImageFile) || !function_exists("imagejpeg"))
	{
		return false;
	}

	list($nW, $nH) = getimagesize($cImageFile);

	// 依照图像格式创建一幅图片:
	switch (strtolower(substr($cImageFile, -4, 4)))
	{
		case ".jpg":
			$im = imagecreatefromjpeg($cImageFile); break;
		case ".gif":
			$im = imagecreatefromgif($cImageFile); break;
		case ".png":
			$im = imagecreatefrompng($cImageFile); break;
		case ".bmp":
			$im = imagecreatefromwbmp($cImageFile); break;
		default:
			return false;
	}

	$im1 = imagecreatetruecolor($nNewW, $nNewH);

	// 计算截取大小和截取位置坐标:
	$nRate1 = $nW / $nH;
	$nRate2 = $nNewW / $nNewH;
	if ($nRate1 > $nRate2)
	{
		$w1 = $nNewW * ($nH / $nNewH);
		$h1 = $nH;
		$x1 = ($nW - $w1) / 2;
		$y1 = 0;
	} elseif ($nRate1 < $nRate2) {
		$w1 = $nW;
		$h1 = $nNewH * ($nW / $nNewW);
		$x1 = 0;
		$y1 = ($nH - $h1) / 2;
	} else {
		$w1 = $nW;
		$h1 = $nH;
		$x1 = $y1 = 0;
	}

	imagecopyresampled($im1, $im, 0, 0, $x1, $y1, $nNewW, $nNewH, $w1, $h1);
	imagedestroy($im);

	// 将图像输出到文件并释放内存:
	if (file_exists($cOutFile))
	{
		unlink($cOutFile);
	}
	imagejpeg($im1, $cOutFile, 85);
	imagedestroy($im1);

	return true;
}

function image_resize_back($cImageFile, $cOutFile, $nNewW, $nNewH, $sBackColor="255,255,255", $sBorder="")
{
	if (!file_exists($cImageFile) || !function_exists("imagejpeg"))
	{
		return false;
	}

	list($cr, $cg, $cb) = explode(",", $sBackColor);
	list($nW, $nH) = getimagesize($cImageFile);

	// 计算背景画布的大小:
	if ($nW/$nH > $nNewW/$nNewH)
	{
		$nBackW = $nW > $nNewW ? $nNewW : $nW;
		$nBackH = $nNewH * $nBackW / $nNewW;
	} else {
		$nBackH = $nH > $nNewH ? $nNewH : $nH;
		$nBackW = $nNewW * $nBackH / $nNewH;
	}

	// 计算源图的缩放与否以及缩放比例:
	if ($nW > $nBackW || $nH > $nBackH)
	{
		$nRate = max($nW / $nBackW, $nH / $nBackH);
		$nImgW = $nW / $nRate;
		$nImgH = $nH / $nRate;
	} else {
		$nImgW = $nW;
		$nImgH = $nH;
	}

	// 计算复制图像时 x,y 坐标的位置:
	$nx = ($nBackW - $nImgW) / 2;
	$ny = ($nBackH - $nImgH) / 2;

	$bk = imagecreatetruecolor($nBackW, $nBackH);
	$back_color = imagecolorallocate($bk, $cr, $cg, $cb);
	imagefilledpolygon($bk, array(0,0, 0,$nBackH-1, $nBackW-1,$nBackH-1, $nBackW-1, 0), 4, $back_color);

	// 依照图像格式创建一幅图片:
	switch (strtolower(substr($cImageFile, -4, 4)))
	{
		case ".jpg":
			$im = imagecreatefromjpeg($cImageFile); break;
		case ".gif":
			$im = imagecreatefromgif($cImageFile); break;
		case ".png":
			$im = imagecreatefrompng($cImageFile); break;
		case ".bmp":
			$im = imagecreatefromwbmp($cImageFile); break;
		default:
			return false;
	}

	// 计算截取大小和截取位置坐标:
	imagecopyresampled($bk, $im, $nx, $ny, 0, 0, $nImgW, $nImgH, $nW, $nH);
	imagedestroy($im);

	if ($sBorder)
	{
		list($br, $bg, $bb) = explode(",", $sBorder);
		$border_color = imagecolorallocate($bk, $br, $bg, $bb);
		imagepolygon($bk, array(0,0, 0,$nBackH-1, $nBackW-1,$nBackH-1, $nBackW-1, 0), 4, $border_color);
	}

	// 将图像输出到文件并释放内存:
	if (file_exists($cOutFile))
	{
		unlink($cOutFile);
	}
	imagejpeg($bk, $cOutFile, 85);
	imagedestroy($bk);

	return true;
}


?>