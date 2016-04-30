<?php

namespace Rsi;

class Ini{

  /**
   *  Parse a configuration string.
   *  @param string $ini  The contents of the ini file being parsed.
   *  @param bool $sections  Include the sections names in the keys.
   *  @param int $mode  INI_SCANNER_NORMAL, INI_SCANNER_RAW, or INI_SCANNER_TYPED.
   *  @see http://php.net/parse_ini_string
   */
  public static function fromString($ini,$sections = true,$mode = INI_SCANNER_TYPED){
    return parse_ini_string($filename,$sections,$mode);
  }
  /**
   *  Parse a configuration file.
   *  @param string $filename  The filename of the ini file being parsed.
   *  @param bool $sections  Include the sections names in the keys.
   *  @param int $mode  INI_SCANNER_NORMAL, INI_SCANNER_RAW, or INI_SCANNER_TYPED.
   *  @see http://php.net/parse_ini_file
   */
  public static function fromFile($filename,$sections = true,$mode = INI_SCANNER_TYPED){
    return parse_ini_file($filename,$sections,$mode);
  }
  /**
   *  Encode a configuration value.
   *  @param string $prefix  Key prefix.
   *  @param mixed $data  Value (single or array).
   *  @param int $mode  INI_SCANNER_NORMAL, INI_SCANNER_RAW, or INI_SCANNER_TYPED.
   *  @return string
   *  @see http://php.net/parse_ini_file
   */
  protected static function stringEncode($prefix,$data,$mode){
    if(!is_array($value = $data)){
      if($mode == INI_SCANNER_TYPED){
        if($value === null) $value = 'null';
        elseif(is_bool($value)) $value = Str::bool($value);
      }
      return "$prefix = $value\n";
    }
    $str = '';
    foreach($data as $key => $value)
      $str .= self::stringEncode($prefix ? $prefix . "[$key]" : $key,$value,$mode);
    return $str;
  }
  /**
   *  Write a configuration string.
   *  @param string $filename  The filename of the ini file.
   *  @param array $data  Multidimensional array.
   *  @param bool $sections  Use sections (first evel of data).
   *  @param int $mode  INI_SCANNER_NORMAL, INI_SCANNER_RAW, or INI_SCANNER_TYPED.
   *  @return string
   *  @see http://php.net/parse_ini_file
   */
  public static function toString($data,$sections = true,$mode = INI_SCANNER_TYPED){
    if(!$sections) return self::stringEncode(null,$data,$mode);
    $str = '';
    foreach($data as $section => $data){
      $str .= ($str ? "\n" : '') . "[$section]\n";
      if($data) foreach($data as $key => $value) $str .= self::stringEncode($key,$value,$mode);
    }
    return $str;
  }
  /**
   *  Write a configuration file.
   *  @param string $filename  The filename of the ini file.
   *  @param array $data  Multidimensional array.
   *  @param bool $sections  Use sections (first evel of data).
   *  @param int $mode  INI_SCANNER_NORMAL, INI_SCANNER_RAW, or INI_SCANNER_TYPED.
   *  @return int  Bytes written (false on failure).
   *  @see http://php.net/parse_ini_file
   */
  public static function toFile($filename,$data,$sections = true,$mode = INI_SCANNER_TYPED){
    return file_put_contents($filename,self::toString($data,$sections,$mode));
  }

}