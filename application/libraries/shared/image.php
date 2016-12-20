<?php

/**
 * Class to control various image related functions
 *
 * @author Hemant Mann
 */
namespace Shared;

class Image {
	/**
	 * @param string $image Name of the image
	 * @param int $width Width of the image
	 * @param int $height Height of the image
	 *
	 * @return boolean|string Thumbnail Name
	 */
	public static function resize($image, $width, $height, $name, $dir) {
		$path = APP_PATH . "/public/uploads/" . $dir;

		if (file_exists($image)) {
		   	$extension = pathinfo($image, PATHINFO_EXTENSION);
		    $extension = strtolower($extension);
		    
		    if ($extension) {
		        
		        $thumbnail = $name . ".{$extension}";
	            $imagine = new \Imagine\Gd\Imagine();
	            $size = new \Imagine\Image\Box($width, $height);
	            $mode = \Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND;
	            $imagine->open("{$image}")->resize($size)->save("{$path}/{$thumbnail}");
		        
		        return 1;
		    }
		}
		return false;
	}

	public static function resource($file) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $extension = strtolower($extension);
        switch ($extension) {
            case 'png':
                $res = imagecreatefrompng($file);
                break;

            case 'jpg':
            case 'jpeg':
                $res = imagecreatefromjpeg($file);
                break;
        }
        return $res;
	}

	/**
	 * @param $resource
	 * @param string $file Name of the file to which image should be output
	 * @return boolean
	 */
	public static function create($resource, $file) {
		$extension = pathinfo($file, PATHINFO_EXTENSION);
		$extension = strtolower($extension);
        switch ($extension) {
            case 'png':
                $res = imagepng($resource, $file);
                break;

            case 'jpg':
            case 'jpeg':
                $res = imagejpeg($resource, $file);
                break;
        }
        imagedestroy($resource);
        return $res;
	}

	public static function rgbFromHex($hex) {
		$hex = str_replace("#", "", $hex);

		if(strlen($hex) == 3) {
		  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
		  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
		  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
		  $r = hexdec(substr($hex,0,2));
		  $g = hexdec(substr($hex,2,2));
		  $b = hexdec(substr($hex,4,2));
		}

		return [
			'r' => $r, 'g' => $g, 'b' => $b		
		];
	}
}
