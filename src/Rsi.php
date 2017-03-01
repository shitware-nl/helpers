<?php

class Rsi{

  /**
   *  Check if PHP is run from the command line.
   *  @return bool
   */
  public static function commandLine(){
    return PHP_SAPI == 'cli';
  }
  /**
   *  Check PHP version.
   *  @param string $version  Version to compare to.
   *  @return bool  True if the current PHP-version is greater or equal than the provided version.
   */
  public static function version($version){
    return version_compare(PHP_VERSION,$version,'>=');
  }
  /**
   *  Check if a value is empty.
   *  @param mixed $value
   *  @return bool  True when empty.
   */
  public static function nothing($value){
    return is_array($value) ? !$value : !strlen($value);
  }
  /**
   *  Returns the size in bytes for shorthand notations (e.g. 1k -> 1024).
   *  @see http://php.net/manual/en/faq.using.php#faq.using.shorthandbytes
   *  @param string $size  Size in shorthand format.
   *  @return int  Size in bytes.
   */
  public static function shorthandBytes($size){
    $factor = 1;
    switch(strtolower(substr($size,-1))){
      case 'g': $factor <<= 10;
      case 'm': $factor <<= 10;
      case 'k': $factor <<= 10;
    }
    return $factor * (int)$size;
  }
  /**
   *  Formats a size in bytes to its shorthand format.
   *  @param int $size  Size in bytes.
   *  @param int $decimals  Number of decimals.
   *  @param string $dec_point  Separator for the decimal point.
   *  @param string  $thousands_sep  Thousands separator.
   *  @param string  $unit_sep  Separator between number and unit.
   *  @return string
   */
  public static function formatBytes($size,$decimals = 1,$dec_point = '.',$thousands_sep = ',',$unit_sep = ' '){
    $unit = 'b';
    $units = ['kb','Mb','Gb','Tb'];
    if($size < 1024) $decimals = 0;
    else while(($size >= 1024) && $units){
      $size = $size / 1024;
      $unit = array_shift($units);
    }
    return number_format($size,$decimals,$dec_point,$thousands_sep) . $unit_sep . $unit;
  }

}