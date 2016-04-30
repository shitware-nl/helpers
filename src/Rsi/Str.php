<?php

namespace Rsi;

/**
 *  String helpers.
 */
class Str{

  const TRANSFORM_LCFIRST = 'lcfirst';
  const TRANSFORM_LOWER = 'lower';
  const TRANSFORM_NL2BR = 'nl2br';
  const TRANSFORM_TRIM = 'trim';
  const TRANSFORM_UCFIRST = 'ucfirst';
  const TRANSFORM_UCWORDS = 'ucwords';
  const TRANSFORM_UPPER = 'upper';

  /**
   *  Returns literally 'true' or 'false'.
   *  @param bool $value
   *  @return string
   */
  public static function bool($value){
    return $value ? 'true' : 'false';
  }
  /**
   *  Generate a random string.
   *  @param int $length  Length of the resulting string.
   *  @param string $chars  Characters to use. Ranges can be indicated as [{from}-{to}]. Eg '[0-9][a-f]' for the hexadecimal
   *    range.
   *  @return string
   */
  public static function random($length = 32,$chars = '[a-z][A-Z][0-9]'){
    if(preg_match_all('/\\[(.)\\-(.)\\]/',$chars,$matches,PREG_SET_ORDER))
      foreach($matches as $match){
        $full = '';
        for($c = $match[1]; $c <= $match[2]; $c++) $full .= $c;
        $chars = str_replace($match[0],$full,$chars);
      }
    $max = strlen($chars) - 1;
    $result = '';
    while($length--) $result .= $chars[\Rsi::version(7) ? random_int(0,$max) : mt_rand(0,$max)];
    return $result;
  }
  /**
   *  Remove accents from a string.
   *  @param string $str
   *  @return string
   */
  public static function normalize($str){
    return str_replace(["'",'"'],'',iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$str));
  }
  /**
   *  Transform a string to a URL-friendly version.
   *  @param string $str  Original string.
   *  @return string  URL-friendly string.
   */
  public static function urlify($str){
    return preg_replace('/\\W+/','-',self::normalize($str));
  }
  /**
   *  Pad function that defaults to left padding with zeros.
   *  @param string $str
   *  @param int $length
   *  @param string $pad
   *  @param int $type
   *  @return string
   */
  public static function pad($str,$length,$pad = '0',$type = STR_PAD_LEFT){
    return str_pad($str,$length,$pad,$type);
  }
  /**
   *  Converts a delimited string to CamelCase.
   *  @param string $str  Delimited string.
   *  @param string $delimiters  Word delimiters.
   *  @return string
   */
  public static function camel($str,$delimiters = ' -_'){
    return str_replace(str_split($delimiters),'',ucwords($str,$delimiters));
  }
  /**
   *  Converts a CamelCased string to snail_case.
   *  @param string $str  CamelCased string.
   *  @param string $delimiter  Delimiter to put between the words.
   *  @return string
   */
  public static function snake($str,$delimiter = '_'){
    return strtolower(preg_replace('/([A-Z])([A-Z][a-z])/',"\$1$delimiter\$2",preg_replace('/([a-z\\d])([A-Z])/',"\$1$delimiter\$2",$str)));
  }
  /**
   *  Limit a string to a number of characters.
   *  @param string $str  Original string.
   *  @param int $length  Maximum length (including delimiter).
   *  @param bool $words  Break at a word boundary.
   *  @param string $delimiter  Character to indicate a limited string.
   *  @return string
   */
  public static function limit($str,$length,$words = false,$delimiter = '…'){
    if(mb_strlen($str) > $length){
      if(!$words) $str = substr($str,0,$length - mb_strlen($delimiter));
      else while(mb_strlen($str . $delimiter) > $length) $str = preg_replace('/\\w*\\s*$/','',$str);
      $str .= $delimiter;
    }
    return $str;
  }
  /**
   *  Return part of a string until a needle. Shorten the string including the needle.
   *  @param string $haystack  String to search in.
   *  @param string $needle  Needle to look for (not found = return string until end).
   *  @return string  Part of string until the needle.
   */
  public static function strip(&$haystack,$needle){
    $result = substr($haystack,0,$i = strpos($haystack . $needle,$needle));
    $haystack = substr($haystack,$i + 1);
    return $result;
  }
  /**
   *  Return part of a string until a needle.
   *  @param string $haystack  String to search in.
   *  @param string $needle  Needle to look for (not found = return string until end).
   *  @param int $skip  Number of parts/needles to skip.
   *  @return string  Part of string until the needle.
   */
  public static function part($haystack,$needle,$skip = 0){
    $result = false;
    while($skip-- >= 0) $result = self::strip($haystack,$needle);
    return $result;
  }
  /**
   *  Insert a string at a certain position in another string.
   *  @param string $str  String to insert to.
   *  @param string $insert  String to insert into $str.
   *  @param int $position  Position to insert at (negative = relative to end).
   *  @param int $length  Length of the part to replace with the insertion.
   *  @return string
   */
  public static function insert($str,$insert,$position,$length = 0){
    return substr($str,0,$position) . $insert . substr($str,$position + $length);
  }
  /**
   *  Remove optinal quotes from a string.
   *  Quotes are only removed if they appear on both ends of the string.
   *  @param string $str  String to remove quotes from.
   *  @param array $quotes  Possible quote characters.
   *  @return string  String without quotes.
   */
  public static function stripQuotes($str,$quotes = ['\'','"']){
    return in_array($quote = substr($str,0,1),$quotes) && (substr($str,-1) == $quote) ? substr($str,1,-1) : $str;
  }
  /**
   *  Transforms a string according to a certain function.
   *  @param string $str  String to format.
   *  @param string|array $methods  One or more format function(s).
   *  @param int $count  Number of format functions applied (> 0 = ok; 0 = unkown function).
   *  @return string  Tranformed string.
   */
  public static function transform($str,$methods,&$count = null){
    if(!is_array($methods)) $methods = [$methods];
    $count = count($methods);
    foreach($methods as $method) switch($method){
      case self::TRANSFORM_LCFIRST: $str = lcfirst($str); break;
      case self::TRANSFORM_LOWER: $str = strtolower($str); break;
      case self::TRANSFORM_NL2BR: $str = nl2br($str); break;
      case self::TRANSFORM_TRIM: $str = trim($str); break;
      case self::TRANSFORM_UCFIRST: $str = ucfirst($str); break;
      case self::TRANSFORM_UCWORDS: $str = ucwords($str); break;
      case self::TRANSFORM_UPPER: $str = strtoupper($str); break;
      default: $count--;
    }
    return $str;
  }
  /**
   *  Check if a string starts with a specific value.
   *  @param string $haystack  String to search in.
   *  @param string $needle  Value to look for at the start of the haystack.
   *  @result bool  True if the haystack starts with the needle.
   */
  public static function startsWith($haystack,$needle){
    return substr($haystack,0,strlen($needle)) == $needle;
  }
  /**
   *  Check if a string ends with a specific value.
   *  @param string $haystack  String to search in.
   *  @param string $needle  Value to look for at the end of the haystack.
   *  @result bool  True if the haystack ends with the needle.
   */
  public static function endsWith($haystack,$needle){
    return substr($haystack,-strlen($needle)) == $needle;
  }
  /**
   *  Evaluate an operator between a value and a reference value.
   *  Besides the usual '==', '!=', '>', '>=', '<', '<=', en '%' operators there are also some specific operators::
   *  - '*-' : Reference starts with the value.
   *  - '*-' : Reference ends with the value.
   *  - '*'  : Reference contains the value.
   *  - '//' : Reference matches the regular expression in the value.
   *  @param string $ref  Reference value. The value to look at.
   *  @param string $operator  Operator.
   *  @param string $value  Value sought after in the reference.
   *  @param bool $default  Default result (unknown operator).
   *  @return bool  True if the value matches the reference according to the operator.
   */
  public static function operator($ref,$operator,$value,$default = false){
    switch($operator){
      case '==': return $ref == $value;
      case '!=': return $ref != $value;
      case '>':  return $ref >  $value;
      case '>=': return $ref >= $value;
      case '<':  return $ref <  $value;
      case '<=': return $ref <= $value;
      case '%':  return $ref %  $value;
      case '*-': return self::startsWith($ref,$value);
      case '-*': return self::endsWith($ref,$value);
      case '*':  return strpos($ref,$value) !== false;
      case '//': return preg_match(substr($value,0,1) == '/' ? $value : '/' . $value . '/',$ref);
    }
    return $default;
  }
  /**
   *  Convert an array to a list.
   *  @param array $array  Array with list items.
   *  @param string $last_delimiter  Delimiter for the last item (same as delimiter when empty).
   *  @param string $delimiter  Delimiter for the items.
   *  @return string
   */
  public static function list($array,$last_delimiter = null,$delimiter = ', '){
    $last = array_pop($array);
    return $array ? implode($delimiter,$array) . ($last_delimiter ?: $delimiter) . $last : $last;
  }
  /**
   *  Show ranges from a numerical array.
   *  For example [1,2,3,5,6,8,9,10] becomes '1 - 3, 5, 6, 8 - 10'.
   *  @param array $array  Array with numbers.
   *  @param string $delimiter  Delimiter to use between ranges.
   *  @param string $separator  Seperator to use between boundaries of a range.
   *  @return string
   */
  public static function ranges($array,$delimiter = ', ',$separator = ' - '){
    $result = [];
    foreach(Record::ranges($array) as $start => $end)
      if($end > $start + 1) $result[] = $start . $separator . $end;
      else{
        $result[] = $start;
        if($start != $end) $result[] = $end;
      }
    return implode($delimiter,$result);
  }

}