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

  const ATTRIBUTES_PATTERN = '\s+(?<key>\w+)=(\'([^\']*)\'|"([^"]*)"|(\w+))';

  const PASSWORD_CHARS = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

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
   *  @param string $chars  Characters to use. Ranges can be indicated as [{from}-{to}]. E.g. '[0-9][a-f]' for the hexadecimal
   *    range.
   *  @return string
   */
  public static function random($length = 32,$chars = '[a-z][A-Z][0-9]'){
    if(preg_match_all('/\\[(.)\\-(.)\\]/',$chars,$matches,PREG_SET_ORDER))
      foreach($matches as list($full,$min,$max)) $chars = str_replace($full,implode(range($min,$max)),$chars);
    $max = strlen($chars) - 1;
    $result = '';
    $random_int = function_exists('random_int');
    while($length--) $result .= $chars[$random_int ? random_int(0,$max) : rand(0,$max)];
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
   *  @param string $replace  Character to replace non 'word' characters by.
   *  @return string  URL-friendly string.
   */
  public static function urlify($str,$replace = '-'){
    return strtolower(trim(preg_replace('/\\W+/',$replace,self::normalize($str)),$replace));
  }
  /**
   *  Replace ASCII emoji's by their Unicode equivalent.
   *  @param string $str  String with ASCII emoji's (e.g. ';-)').
   *  @return string  String with Unicode emoji's (e.g. '😉').
   */
  public static function emojify($str){
    return strtr($str,require(__DIR__ . '/emoji.php'));
  }
  /**
   *  Remove leet-speak (1337 5p33k) from a string.
   *  @param string $str  String with leet-speak characters.
   *  @param bool $upper  Replace with uppercase letters.
   *  @return string  String with leet-speak symbols replaced by their normal letter. This is near from perfect, since '13'
   *    could be 'LE' or 'B', '1' could be 'I' or 'L', etc).
   */
  public static function unleet($str,$upper = false){
    $leet = require(__DIR__ . '/leet.php');
    $length = 0;
    foreach($leet as $chars) $length = max($length,max(array_map('strlen',$chars)));
    do foreach($leet as $char => $chars) $str = str_replace(
      array_filter($chars,function($char) use ($length){
        return strlen($char) == $length;
      }),
      $upper ? $char : strtolower($char),
      $str
    );
    while(--$length);
    return $str;
  }
  /**
   *  Case insesitive string comparison.
   *  @param string $str1  First string.
   *  @param string $str2  Second string.
   *  @return  Returns < 0 if str1 is less than str2, > 0 if str1 is greater than str2, and 0 if they are equal.
   *
   */
  public static function icompare($str1,$str2){
    return strcmp(mb_strtolower($str1),mb_strtolower($str2));
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
   *  Converts a CamelCased string to snake_case.
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
      $str = mb_substr($str,0,$length - mb_strlen($delimiter));
      if($words && ($i = strrpos($str,' '))) $str = substr($str,0,$i + 1);
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
   *  Return part of a string after a needle. Shorten the string including the needle.
   *  @param string $haystack  String to search in.
   *  @param string $needle  Needle to look for (not found = return string until end).
   *  @return string  Part of string after the needle (false if needle not found).
   */
  public static function pop(&$haystack,$needle){
    $i = strrpos($haystack,$needle);
    if($i === false) return false;
    $result = substr($haystack,$i + 1);
    $haystack = substr($haystack,0,$i);
    return $result;
  }
  /**
   *  Return part of a string until a needle.
   *  @param string $haystack  String to search in.
   *  @param string $needle  Needle to look for.
   *  @param int $index  Number of parts/needles to skip.
   *  @return string  Part of string until the needle (false = index not found).
   */
  public static function part($haystack,$needle,$index = 0){
    $parts = explode($needle,$haystack);
    return $index < count($parts) ? $parts[$index] : false;
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
   *  Make sure a string starts with a specific value.
   *  @param string $haystack  Base string.
   *  @param string $needle  Value to check for at the start of the haystack.
   *  @return string  Base string with the needle added if not present.
   */
  public static function startWith($haystack,$needle){
    return self::startsWith($haystack,$needle) ? $haystack : $needle . $haystack;
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
   *  Make sure a string ends with a specific value.
   *  @param string $haystack  Base string.
   *  @param string $needle  Value to check for at the end of the haystack.
   *  @return string  Base string with the needle added if not present.
   */
  public static function endWith($haystack,$needle){
    return self::endsWith($haystack,$needle) ? $haystack : $haystack . $needle;
  }
  /**
   *  Numeric detection.
   *  Works only on decimal numbers, plus signs and exponential parts are not allowed.
   *  @param mixed $value  Value to check.
   *  @return bool  True if the value is a numeric.
   */
  public static function numeric($value){
    return is_string($value) && preg_match('/^(\\-?[1-9]\\d*|0)(\\.\\d+)?$/',$value);
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
  public static function implode($array,$last_delimiter = null,$delimiter = ', '){
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
  /**
   *  Replace date tags in a string.
   *  @param string $str  String with date tags.
   *  @param int $time  Timestamp to use (empty = now).
   *  @param string $open  Open tag.
   *  @param string $close  Close tag.
   *  @return string  String with tags replaced.
   */
  public static function replaceDate($str,$time = null,$open = '[',$close = ']'){
    if(preg_match_all('/' . preg_quote($open,'/') . '(\w+)' . preg_quote($close,'/') . '/',$str,$matches,PREG_SET_ORDER)){
      if(!$time) $time = time();
      foreach($matches as $match) $str = str_replace($match[0],date($match[1],$time),$str);
    }
    return $str;
  }
  /**
   *  Extract attributes from a string.
   *  @param string $str  String with attributes.
   *  @return array  Attributes (assoc. array).
   */
  public static function attributes($str){
    $attribs = [];
    if($str && preg_match_all('/' . self::ATTRIBUTES_PATTERN . '/',$str,$matches,PREG_SET_ORDER))
      foreach($matches as $match) $attribs[$match['key']] = html_entity_decode(array_pop($match));
    return $attribs;
  }

}