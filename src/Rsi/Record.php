<?php

namespace Rsi;

/**
 *  Record (array) hulpfuncties.
 */
class Record{

  /**
   *  Enhanced explode.
   *  @param mixed $array  Value to turn into an array.
   *  @param string $delimiter  Delimiter in case of a string.
   *  @param string $separator  Key-value separator.
   *  @return array
   */
  public static function explode($array,$delimiter = null,$separator = null){
    if(is_array($array)) return $array;
    if($array === null) return [];
    if(!$delimiter) return [$array];
    $result = [];
    foreach(explode($delimiter,$array) as $value){
      if($separator) list($key,$value) = explode($separator,$value,2);
      else $key = count($result);
      $result[$key] = $value;
    }
    return $result;
  }
  /**
   *  Enhanced implode.
   *  @param mixed $array  Array to turn into a string
   *  @param string $delimiter  Delimiter.
   *  @param string $separator  Key-value separator.
   *  @return string
   */
  public static function implode($array,$delimiter = null,$separator = null){
    if(!is_array($array)) return $array;
    if($separator) foreach($array as $key => &$value) $value = $key . $separator . $value;
    unset($value);
    return implode($delimiter,$array);
  }
  /**
   *  Get a value from an array.
   *  @param array $array  Array to look in.
   *  @param string|array $key  Key to look at. An array indicates a nested key. If the array is ['foo' => ['bar' => 'acme']],
   *    then the nested key for the 'acme' value will be ['foo','bar'].
   *  @param mixed $default  Default value if the key does not exist.
   *  @return mixed  Found value, or default value if the key does not exist.
   */
  public static function get($array,$key,$default = null){
    if(!is_array($key)) $key = [$key];
    elseif(!$key) return $default;
    while($key){
      if(!is_array($array) || !array_key_exists($sub = array_shift($key),$array)) return $default;
      $array = $array[$sub];
    }
    return $array;
  }
  /**
   *  Get a value from an array in a case insensitive way.
   *  @see get()
   *  @param array $array  Array to look in.
   *  @param string|array $key  Key to look at (case insensitive). An array indicates a nested key. If the array is
   *    ['foo' => ['bar' => 'acme']], then the nested key for the 'acme' value will be ['foo','bar'].
   *  @param mixed $default  Default value if the key does not exist.
   *  @return mixed  Found value, or default value if the key does not exist.
   */
  public static function iget($array,$key,$default = null){
    if(!is_array($key)) $key = [$key];
    elseif(!$key) return $default;
    while($key){
      if(!is_array($array)) return $default;
      if(!array_key_exists($sub = array_shift($key),$array)){
        $found = false;
        foreach($array as $index => $value) if($found = !strcasecmp($index,$sub)){
          $sub = $index;
          break;
        }
        if(!$found) return $default;
      }
      $array = $array[$sub];
    }
    return $array;
  }
  /**
   *  Set a value of an array.
   *  If the key does not exist it will be created. With a nested key, sub-array will also be created.
   *  @param array $array  Array to store the value in.
   *  @param string|array $key  Key to store the value at. An array indicates a nested key. If the array is
   *    ['foo' => ['bar' => 'acme']], then the nested key for the 'acme' value will be ['foo','bar'].
   *  @param mixed $value
   */
  public static function set(&$array,$key,$value){
    if(is_array($keys = $key)) $key = array_shift($keys);
    else $keys = null;
    if($keys){
      if(!array_key_exists($key,$array)) $array[$key] = [];
      self::set($array[$key],$keys,$value);
    }
    else $array[$key] = $value;
  }
  /**
   *  Add a value to an array if the key does not already exist.
   *  @param array $array  Array to store the value in.
   *  @param string|array $key  Key to store the value at. An array indicates a nested key. If the array is
   *    ['foo' => ['bar' => 'acme']], then the nested key for the 'acme' value will be ['foo','bar'].
   *  @param mixed $value
   */
  public static function add(&$array,$key,$value = null){
    if(is_array($keys = $key)) $key = array_shift($keys);
    else $keys = null;
    if(!array_key_exists($key,$array)) $array[$key] = $keys ? [] : $value;
    if($keys) self::add($array[$key],$keys,$value);
  }
  /**
   *  Get the n-th value from an array.
   *  @param array $array  Array to get value from.
   *  @param int $index  Value index.
   *  @param mixed $default  Default value if the index does not exist.
   *  @return mixed
   */
  public static function value($array,$index = 0,$default = null){
    return self::get(array_values($array),$index,$default);
  }
  /**
   *  Get the n-th key from an array.
   *  @param array $array  Array to get key from.
   *  @param int $index  Key index.
   *  @return mixed  Found key, false if not existing.
   */
  public static function key($array,$index = 0){
    return self::get(array_keys($array),$index,false);
  }
  /**
   *  Determine if an array is associative (that is, no ascending numerical key).
   *  @param array $array
   *  @return bool  True if the array is associative.
   */
  public static function assoc($array){
    return array_values($array) !== $array;
  }
  /**
   *  Add a prefix to all members of an array.
   *  @param array $array  Array with values.
   *  @param string $prefix  Prefix to add.
   *  @return array  Prefixed array.
   */
  public static function prefix($array,$prefix){
    foreach($array as &$value) $value = $prefix . $value;
    unset($value);
    return $array;
  }
  /**
   *  Add a prefix to all keys of an array.
   *  @param array $array  Array with values.
   *  @param string $prefix  Prefix to add.
   *  @return array  Prefixed array.
   */
  public static function prefixKey($array,$prefix){
    $result = [];
    foreach($array as $key => $value) $result[$prefix . $key] = $value;
    return $result;
  }
  /**
   *  Change the keys of an array.
   *  @param array $array  Source array.
   *  @param array $keys  Key = old key, value = new key.
   *  @param bool $keep  If true, entries in the source array for which there is nog new key defined, will be kept in the result
   *    (with their original key).
   */
  public static function changeKey($array,$keys,$keep = true){
    $result = [];
    foreach($array as $key => $value)
      if(array_key_exists($key,$keys)) $result[$keys[$key]] = $value;
      elseif($keep) $result[$key] = $value;
    return $result;
  }
  /**
   *  Combine the key and value of an assoc.array into seperate records.
   *  @param array $array  Assoc.array.
   *  @param string $key_name  Name under which the key is added to the record. If empty the key is not added.
   *  @param string $value_name  Name under which the value is added to the record. If empty, and the value is an array, this
   *    array is merged with the record. Otherwise the value is not added.
   *  @return array  Array of records (key is preserved).
   */
  public static function combine($array,$key_name = null,$value_name = null){
    $records = [];
    foreach($array as $key => $value){
      $record = [];
      if($key_name) $record[$key_name] = $key;
      if($value_name) $record[$value_name] = $value;
      elseif(is_array($value)) $record += $value;
      $records[$key] = $record;
    }
    return $records;
  }
  /**
   *  Return the values from a single column in the input array.
   *  Basicly PHP's array_column, but then retaining key association.
   *  @param array $array  A multi-dimensional array (record set) from which to pull a column of values.
   *  @param string $column  The column of values to return.
   *  @return array  Returns an array of values representing a single column from the input array.
   */
  public static function column($array,$column){
    $result = [];
    foreach($array as $key => $record) $result[$key] = self::get($record,$column);
    return $result;
  }
  /**
   *  Return specific keys from an array.
   *  @param array $array  Array to select from.
   *  @param array $keys  Keys to select.
   *  @return array
   */
  public static function select($array,$keys){
    return array_intersect_key($array,array_flip($keys));
  }
  /**
   *  Retrieve numerical ranges from an array.
   *  @param array $array  Array with numbers.
   *  @return array  Key = start of range, value = end of range.
   */
  public static function ranges($array){
    $result = [];
    if($array){
      sort($array);
      $previous = $start = array_shift($array);
      while($array){
        $current = array_shift($array);
        if($current > $previous + 1){
          $result[$start] = $previous;
          $start = $current;
        }
        $previous = $current;
      }
      $result[$start] = $previous;
    }
    return $result;
  }

}