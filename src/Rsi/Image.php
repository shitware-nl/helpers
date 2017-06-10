<?php

namespace Rsi;

/**
 *  Image helpers.
 */
class Image{

  const IMAGE_SCALE_INSIDE = 1; //!<  Scale image inside box (normally it will scale to fit at least the box).
  const IMAGE_SCALE_ONLY_SMALLER = 2; //!<  Only make an image smaller, not larger.

  /**
   *  Create an image resource from file.
   *  @param string $filename  Image filename.
   *  @return resource  Image resource (false if the image type is unsupported, the data is not in a recognised format, or the
   *    image is corrupt and cannot be loaded).
   */
  public static function fromFile($filename){
    return imagecreatefromstring(file_get_contents($filename));
  }
  /**
   *  Convert an image resource to base64 encoded uri.
   *  @param resource $image  Image resource.
   *  @param string $type  Output image type (see PHP's IMAGETYPE_* constants).
   *  @param int $quality  Quality for JPEG or PNG format.
   *  @return string  False if unsupported type.
   */
  public static function dataUri($image,$type = IMAGETYPE_JPEG,$quality = 75){
    ob_start();
    switch($type){
      case IMAGETYPE_PNG:
        imagepng($image,null,$quality);
        break;
      case IMAGETYPE_GIF:
        imagegif($image);
        break;
      case IMAGETYPE_JPEG:
        imagejpeg($image,null,$quality);
        break;
      default:
        ob_end_clean();
        return false;
    }
    return 'data:' . image_type_to_mime_type($type) . ';base64,' . base64_encode(ob_get_clean());
  }
  /**
   *  Scale an image resource to fit a certain box size, maintaining aspect ratio.
   *  @param resource $image  Image resource.
   *  @param int $width  Box width.
   *  @param int $height  Box height.
   *  @param int $options  See IMAGE_SCALE_* constants.
   *  @param int $mode  Scaling mode (see http://php.net/manual/en/function.imagescale.php).
   *  @return resource  Scaled image resource, or false on failure (including image smaller than requested dimension and
   *    IMAGE_SCALE_ONLY_SMALLER in options).
   */
  public static function scaleBox($image,$width,$height,$options = 0,$mode = IMG_BILINEAR_FIXED){
    $x_factor = $width / ($orig_width = imagesx($image));
    $y_factor = $height / ($orig_height = imagesy($image));
    $factor = $options & self::IMAGE_SCALE_INSIDE ? min($x_factor,$y_factor) : max($x_factor,$y_factor);
    if(($options & self::IMAGE_SCALE_ONLY_SMALLER) && ($factor >= 1)) return false;
    return imagescale($image,$orig_width * $factor,$orig_height * $factor,$mode);
  }

}