<?php

namespace Rsi;

/**
 *  Color helpers.
 *  Default color notation is a triplet array with byte values for red, green, and blue.
 */
class Color{

  /**
   *  Convert integer color to triplet array.
   *  @param mixed $value  Color in integer notation.
   *  @return array  Color as triplet array.
   */
  public static function intToRgb($value){
    return is_integer($value) ? [($value >> 16) & 0xff,($value >> 8) & 0xff,$value & 0xff] : null;
  }
  /**
   *  Convert hexadecimal color to triplet array.
   *  @param mixed $value  Color as hexadecimal string (3 or 6 digits, with or without leading #).
   *  @return array  Color as triplet array.
   */
  public static function hexToRgb($value){
    if(!is_string($value)) return null;
    switch(strlen($value = strtolower(ltrim($value,'#')))){
      case 3: $value = $value[0] . $value[0] . $value[1] . $value[1] . $value[2] . $value[2];
      case 6: if(preg_match('/^[\\da-f]{6}$/',$value)) break;
      default: return null;
    }
    return [hexdec(substr($value,0,2)),hexdec(substr($value,2,2)),hexdec(substr($value,4,2))];
  }
  /**
   *  Parse color to triplet array.
   *  @param mixed $value  Color as hexadecimal string (3 or 6 digits, with or without leading #), integer, or triplet array.
   *  @return array  Color as triplet array.
   */
  public static function toRgb($value){
    if(is_integer($value)) return self::intToRgb($value);
    if(is_string($value)) return self::hexToRgb($value);
    if(is_array($value) && (count($value) == 3) && (min($value) >= 0) && (max($value) < 256)) return array_vaules($value);
    return null;
  }
  /**
   *  Parse color to integer.
   *  @param mixed $value  Color as hexadecimal string (3 or 6 digits, with or without leading #), integer, or triplet array.
   *  @return array  Color as integer.
   */
  public static function toInt($value){
    return ($value = self::toRgb($value)) ? ($value[0] << 16) | ($value[1] << 8) | $value[0] : null;
  }
  /**
   *  Format a color value to two digit hexadecimal.
   *  @param byte $value  Color value (0..255).
   *  @return string  Two digit hexadecimal representation.
   */
  protected static function decToHex($value){
    return str_pad(dechex($value),2,'0',STR_PAD_LEFT);
  }
  /**
   *  Parse color to hexadecimal.
   *  @param mixed $value  Color as hexadecimal string (3 or 6 digits, with or without leading #), integer, or triplet array.
   *  @return array  Color as hexadecimal string.
   */
  public static function toHex($value){
    return ($value = self::toRgb($value)) ? '#' . static::decToHex($value[0]) . static::decToHex($value[1]) . static::decToHex($value[2]) : null;
  }
  /**
   *  Grey value of a color.
   *  @param mixed $value  Color as hexadecimal string (3 or 6 digits, with or without leading #), integer, or triplet array.
   *  @return byte  Grey scale value (0..255).
   */
  public static function grey($value){
    return ($value = self::toRgb($value)) ? round(0.2125 * $value[0] + 0.7154 * $value[1] + 0.0721 * $value[2]) : null;
  }
  /**
   *  Lighten (tint) or darken (shade) a color.
   *  @param mixed $value  Color as hexadecimal string (3 or 6 digits, with or without leading #), integer, or triplet array.
   *  @param float $tint  Tint (0..1 makes a color lighter, with 1 = white; -1..0 makes a color darker, with -1 = black).
   *  @return array  Color as triplet array.
   */
  public static function tint($value,$tint){
    if($value = self::toRgb($value)){
      if($tint > 0) for($i = 0; $i < 3; $i++) $value[$i] += (255 - $value[$i]) * min($tint,1);
      elseif($tint < 0) for($i = 0; $i < 3; $i++) $value[$i] *= 1 + max($tint,-1);
    }
    return $value;
  }

}