<?php

namespace Rsi;

/**
 *  Helpers for numbers of all sorts.
 */
class Number{
  /**
   *  Returns the size in bytes for shorthand notations (e.g. 1k -> 1024).
   *  @see http://php.net/manual/en/faq.using.php#faq.using.shorthandbytes
   *  @param string $size  Size in shorthand format.
   *  @return int  Size in bytes.
   */
  public static function shorthandBytes($size){
    $factor = 1;
    switch(strtolower(substr($size,-1))){
      case 'e': $factor <<= 10;
      case 'p': $factor <<= 10;
      case 't': $factor <<= 10;
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
  /**
   *  Format a number as a Roman number.
   *  @param int $value  Decimal value.
   *  @return string  Roman representation.
   */
  public static function toRoman($value){
    $result = '';
    $numbers = ['M' => 1000,'CM' => 900,'D' => 500,'CD' => 400,'C' => 100,'XC' => 90,'L' => 50,'XL' => 40,'X' => 10,'IX' => 9,'V' => 5,'IV' => 4,'I' => 1];
    while($value > 0) foreach($numbers as $roman => $number) if($value >= $number){
      $value -= $number;
      $result .= $roman;
      break;
    }
    return $result;
  }

}