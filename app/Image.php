<?php

namespace App;

use SeinopSys\RGBAColor;

class Image {
	/**
	 * Checks image type and returns an array containing the image width and height
	 *
	 * @param string   $tmp
	 * @param string[] $allowedMimeTypes
	 *
	 * @return int[]
	 * @throws \RuntimeException
	 */
	public static function checkType($tmp, $allowedMimeTypes):array {
		$imageSize = getimagesize($tmp);
		if ($imageSize === false)
			throw new \RuntimeException("getimagesize could not read $tmp");
		/** @var $imageSize array */
		if (\is_array($allowedMimeTypes) && !\in_array($imageSize['mime'], $allowedMimeTypes, true))
			Response::fail('This type of image is now allowed: '.$imageSize['mime']);
		[$width, $height] = $imageSize;

		if ($width + $height === 0)
			Response::fail('The uploaded file is not an image');

		return [$width, $height];
	}

	/**
	 * Check image size with a preset minimum
	 *
	 * @param string $path
	 * @param int $width
	 * @param int $height
	 * @param array $min
	 * @param array $max
	 */
	public static function checkSize($path, $width, $height, $min, $max):void {
		$tooSmall = $width < $min[0] || $height < $min[1];
		$tooBig = $width > $max[0] || $height > $max[1];
		if ($tooSmall || $tooBig){
			CoreUtils::deleteFile($path);
			Response::fail("The image's ".(
				($tooBig ? $width > $max[0] : $width < $min[0])
				?(
					($tooBig ? $height > $max[1] : $height < $min[1])
					?'width and height are'
					:'width is'
				)
				:(
					($tooBig ? $height > $max[1] : $height < $min[1])
					?'height is'
					:'dimensions are'
				)
			).' too '.($tooBig?'big':'small').', please upload a '.($tooBig?'smaller':'larger').' image.<br>The '.($tooBig?'maximum':'minimum').' size is '.($tooBig?$max[0]:$min[0]).'px wide by '.($tooBig?$max[1]:$min[1])."px tall, and you uploaded an image that's {$width}px wide and {$height}px tall.</p>");
		}
	}

	/**
	 * Preseving alpha
	 *
	 * @param resource $img
	 * @param int      $background
	 *
	 * @return resource
	 */
	public static function preserveAlpha($img, &$background = null) {
		$background = imagecolorallocatealpha($img, 0, 0, 0, 127);
		imagecolortransparent($img, $background);
		imagealphablending($img, false);
		imagesavealpha($img, true);
		return $img;
	}

	/**
	 * Transparent Image creator
	 *
	 * @param int      $width
	 * @param int|null $height
	 *
	 * @return resource
	 */
	public static function createTransparent($width, $height = null){
		if ($height === null)
			$height = $width;

		$png = self::preserveAlpha(imagecreatetruecolor($width, $height), $transparency);
		imagefill($png, 0, 0, $transparency);
		return $png;
	}

	/**
	 * White Image creator
	 *
	 * @param int      $width
	 * @param int|null $height
	 *
	 * @return resource
	 */
	public static function createWhiteBG($width, $height = null){
		if ($height === null)
			$height = $width;

		$png = imagecreatetruecolor($width, $height);
		$white = imagecolorallocate($png, 255, 255, 255);
		imagefill($png, 0, 0, $white);
		return $png;
	}

	/**
	 * Draw a an (optionally filled) square on an $image
	 *
	 * @param resource    $image
	 * @param int         $x
	 * @param int         $y
	 * @param int|int[]   $size
	 * @param string|null $fill
	 * @param string|int  $outline
	 */
	public static function drawSquare($image, $x, $y, $size, $fill, $outline):void {
		if ($fill !== null && \is_string($fill)){
			$fill = RGBAColor::parse($fill);
			$fill = imagecolorallocate($image, $fill->red, $fill->green, $fill->blue);
		}
		if (\is_string($outline)){
			$outline = RGBAColor::parse($outline);
			$outline = imagecolorallocate($image, $outline->red, $outline->green, $outline->blue);
		}

		if (\is_array($size)){
			$x2 = $x + $size[0];
			$y2 = $y + $size[1];
		}
		else {
			$x2 = $x + $size;
			$y2 = $y + $size;
		}

		$x2--; $y2--;

		if ($fill !== null)
			imagefilledrectangle($image, $x, $y, $x2, $y2, $fill);
		if ($outline !== null)
			imagerectangle($image, $x, $y, $x2, $y2, $outline);
	}

	/**
	 * Draw a an (optionally filled) circle on an $image
	 *
	 * @param resource    $image
	 * @param int         $x
	 * @param int         $y
	 * @param mixed       $size
	 * @param string|null $fill
	 * @param string|int  $outline
	 */
	public static function drawCircle($image, $x, $y, $size, $fill, $outline):void {
		if ($fill !== null && \is_string($fill)){
			$fill = RGBAColor::parse($fill);
			$fill = imagecolorallocate($image, $fill->red, $fill->green, $fill->blue);
		}
		if (\is_string($outline)){
			$outline = RGBAColor::parse($outline);
			$outline = imagecolorallocate($image, $outline->red, $outline->green, $outline->blue);
		}

		if (\is_array($size)){
			/** @var $size int[] */
			[$width,$height] = $size;
			$x2 = $x + $width;
			$y2 = $y + $height;
		}
		else {
			/** @var $size int */
			$x2 = $x + $size;
			$y2 = $y + $size;
			$width = $height = $size;
		}
		$cx = (int)CoreUtils::average([$x,$x2]);
		$cy = (int)CoreUtils::average([$y,$y2]);

		if ($fill !== null)
			imagefilledellipse($image, $cx, $cy, $width, $height, $fill);
		imageellipse($image, $cx, $cy, $width, $height, $outline);
	}

	/**
	 * Writes on an image
	 *
	 * @param resource        $image
	 * @param string|string[] $text
	 * @param int             $x
	 * @param int             $font_size
	 * @param int             $font_color
	 * @param array|null      $origin
	 * @param string          $font_file
	 * @param array           $box
	 * @param int             $y_offset
	 */
	public static function writeOn($image, $text, $x, $font_size, $font_color, &$origin, $font_file, $box = null, $y_offset = 0) {
		$line_count = \is_array($text) ? \count($text) : 1;
		$line_padding_bottom = 2;
		if (empty($box)){
			$box = self::saneGetTTFBox($font_size, $font_file, $text);
			$origin['y'] += $box['height'];
			$y = $origin['y'] - $box['bottom right']['y'];
		}
		else $y = $origin['y'] + $box['height'] - $box['bottom right']['y'];

		if ($line_count === 1){
			imagettftext($image, $font_size, 0, $x, $y + $y_offset, $font_color, $font_file, $text);
		}
		else {
			$y += $y_offset;
			foreach ($text as $line){
				imagettftext($image, $font_size, 0, $x, $y, $font_color, $font_file, $line);

				$line_box = self::lineSaneGetTTFBox($font_size, $font_file, $line);
				$y += $line_box['height'] + $line_padding_bottom;
			}
		}
	}


	/**
	 * @param int    $font_size
	 * @param string $font_file
	 * @param string $line
	 *
	 * @return array
	 */
	public static function lineSaneGetTTFBox($font_size, $font_file, $line):array {
		$box = imagettfbbox($font_size, 0, $font_file, $line);
		$return =  [
			'bottom left' => ['x' => $box[0], 'y' => $box[1]],
			'bottom right' => ['x' => $box[2], 'y' => $box[3]],
			'top right' => ['x' => $box[4], 'y' => $box[5]],
			'top left' => ['x' => $box[6], 'y' => $box[7]],
		];
		$return['width'] = abs($return['bottom right']['x'] - $return['top left']['x']);
		$return['height'] = abs($return['bottom right']['y'] - $return['top left']['y']);

		return $return;
	}

	/**
	 * imagettfbbox wrapper with sane output
	 * -------------------------------------
	 * imagettfbbox returns (x,y):
	 * 6,7--4,5
	 *  |    |
	 *  |    |
	 * 0,1--2,3
	 *
	 * @param int             $font_size
	 * @param string          $font_file
	 * @param string|string[] $text
	 *
	 * @return array
	 */
	public static function saneGetTTFBox($font_size, $font_file, $text):array {
		if (!\is_array($text)){
			$first_line = $text;
			$text = [];
		}
		else $first_line = array_splice($text, 0, 1)[0];

		$box = self::lineSaneGetTTFBox($font_size, $font_file, $first_line);
		if (!empty($text)) {
			foreach ($text as $line){
				$line_box = self::lineSaneGetTTFBox($font_size, $font_file, $line);
				$lines[] = [$line_box, $font_size];
				// Nudge height down by the line height
				$box['height'] += $line_box['height'];
				$box['bottom left']['y'] += $line_box['height'];
				$box['bottom right']['y'] += $line_box['height'];
				// Nudge width out further right if line exceeds current
				if ($line_box['width'] > $box['width'])
					$box['width'] = $line_box['width'];
				if ($line_box['top right']['x'] > $box['top right']['x']){
					$box['top right']['x'] = $line_box['top right']['x'];
					$box['bottom right']['x'] = $line_box['bottom right']['x'];
				}
			}
		}

		return $box;
	}

	/**
	 * Copies the source image to the destination image at the exact same positions
	 *
	 * @param resource $dest
	 * @param resource $source
	 * @param int      $x
	 * @param int      $y
	 * @param int      $w
	 * @param int      $h
	 */
	public static function copyExact($dest, $source, $x, $y, $w, $h):void {
		imagecopyresampled($dest, $source, $x, $y, $x, $y, $w, $h, $w, $h);
	}


	/**
	 * Output png file to browser
	 *
	 * @param resource $resource
	 * @param string   $path
	 * @param string   $FileRelPath
	 */
	public static function outputPNG($resource, $path, $FileRelPath):void {
		self::_output($resource, $path, $FileRelPath, function($fp,$fd){ imagepng($fd, $fp, 9, PNG_NO_FILTER); }, 'png');
	}

	/**
	 * Output png file to API
	 *
	 * @param resource $resource
	 * @param string   $path
	 */
	public static function outputPNGAPI($resource, $path):void {
		self::_outputRaw($resource, $path, function($fp,$fd){ imagepng($fd, $fp, 9, PNG_NO_FILTER); }, 'png');
	}

	/**
	 * Output svg file to browser
	 *
	 * @param string|null $svgdata
	 * @param string      $path
	 * @param string      $FileRelPath
	 */
	public static function outputSVG($svgdata, $path, $FileRelPath):void {
		self::_output($svgdata, $path, $FileRelPath, function($fp,$fd){ File::put($fp, $fd); }, 'svg+xml');
	}

	/**
	 * @param resource|string $data
	 * @param string          $file_path
	 * @param string          $relative_path
	 * @param callable        $write_callback
	 * @param string          $content_type
	 */
	private static function _output($data, $file_path, $relative_path, $write_callback, $content_type):void {
		$last_modified = file_exists($file_path) ? filemtime($file_path) : time();

		$file_portion = strtok($relative_path,'?');
		$query_string = strtok('?');
		$path_build = new NSUriBuilder($file_portion);
		if (!empty($query_string))
			$path_build->append_query_raw($query_string);
		$remove_params = null;
		if (!empty($_GET['token']))
			$path_build->append_query_param('token', $_GET['token']);
		else $remove_params = ['token'];
		$path_build->append_query_param('t', $last_modified);

		CoreUtils::fixPath($path_build, $remove_params);
		self::_outputRaw($data, $file_path, $write_callback, $content_type);
	}

	/**
	 * @param resource|string $data
	 * @param string          $file_path
	 * @param callable        $write_callback
	 * @param string          $content_type
	 */
	private static function _outputRaw($data, $file_path, $write_callback, $content_type):void {
		$development = !CoreUtils::env('PRODUCTION');
		$last_modified = file_exists($file_path) ? filemtime($file_path) : time();

		if ($data !== null){
			CoreUtils::createFoldersFor($file_path);
			$write_callback($file_path, $data);
			if (file_exists($file_path))
				File::chmod($file_path);
		}
		else if ($development){
			$since = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? null;
			if ($since){
				$since_ts = strtotime($since);
				if ($since_ts !== false && $last_modified <= $since_ts)
					HTTP::statusCode(304, AND_DIE);
			}
		}

		header("Content-Type: image/$content_type");
		if ($development){
			header('Cache-Control: public, max-age=31536000');
			header('Last-Modified: '.gmdate('r', $last_modified));
		}
		readfile($file_path);
		exit;
	}

	/**
	 * Calculate and recreate the base image in case its size need to be increased
	 *
	 * @param int      $OutWidth
	 * @param int      $OutHeight
	 * @param int      $WidthIncrease
	 * @param int      $HeightIncrease
	 * @param resource $BaseImage
	 * @param array    $origin
	 */
	public static function calcRedraw(&$OutWidth, &$OutHeight, $WidthIncrease, $HeightIncrease, &$BaseImage, $origin):void {
		$Redraw = false;
		if ($origin['x']+$WidthIncrease > $OutWidth){
			$Redraw = true;
			$origin['x'] += $WidthIncrease;
		}
		if ($origin['y']+$HeightIncrease > $OutHeight){
			$Redraw = true;
			$origin['y'] += $HeightIncrease;
		}
		if ($Redraw){
			$NewWidth = max($origin['x'],$OutWidth);
			$NewHeight = max($origin['y'],$OutHeight);
			// Create new base image since height will increase, and copy contents of old one
			$NewBaseImage = self::createTransparent($NewWidth, $NewHeight);
			self::copyExact($NewBaseImage, $BaseImage, 0, 0, $OutWidth, $OutHeight);
			imagedestroy($BaseImage);
			$BaseImage = $NewBaseImage;
			$OutWidth = $NewWidth;
			$OutHeight = $NewHeight;
		}
	}
}
