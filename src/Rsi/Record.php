<?php

namespace Rsi;

/**
 *  Record (array) helpers.
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
    if(!$delimiter || !is_string($array)) return $array === null ? [] : [$array];
    $result = [];
    if($array) foreach(explode($delimiter,$array) as $value){
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
   *  Checks if a key exists in an array.
   *  @param array $array  Array to look in.
   *  @param string|array $key  Key to look at. An array indicates a nested key. If the array is ['foo' => ['bar' => 'acme']],
   *    then the nested key for the 'acme' value will be ['foo','bar'].
   *  @return bool  True if the key exists.
   */
  public static function exists($array,$key){
    if(!is_array($key)) $key = [$key];
    elseif(!$key) return false;
    while($key){
      if(!is_array($array) || !array_key_exists($sub = array_shift($key),$array)) return false;
      $array = $array[$sub];
    }
    return true;
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
    if(!$array || !is_array($array)) return $default;
    if(!is_array($key)) return array_key_exists($key,$array) ? $array[$key] : $default;
    while($key) if(!array_key_exists($sub = array_shift($key),$array) || (!is_array($array = $array[$sub]) && $key)) return $default;
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
   *  Delete a (nested) key from an array.
   *  @param array $array  Array to delete the key from.
   *  @param string|array $key  Key to delete. An array indicates a nested key.
   */
  public static function delete(&$array,$key){
    if(is_array($keys = $key)){
      $key = array_pop($keys);
      foreach($keys as $sub)
        if(array_key_exists($sub,$array)) $array = &$array[$sub];
        else return false;
    }
    unset($array[$key]);
  }
  /**
   *  Flattens a multi-dimensional array.
   *  @param array $array  Array to flatten.
   *  @param string $key_glue  Key to combine the keys with (sequential, numerical keys if empty).
   *  @param string $key_prefix  Prefix for the key.
   *  @return array  One dimensional array.
   */
  public static function flatten($array,$key_glue = null,$key_prefix = null){
    $result = [];
    foreach($array as $key => $value)
      if(is_array($value)) $result = array_merge($result,self::flatten($value,$key_glue,$key_prefix . $key . $key_glue));
      else $result[$key_glue ? $key_prefix . $key : count($result)] = $value;
    return $result;
  }
  /**
   *  Brings a flattend array back to its multi-dimensional form.
   *  @param array $array  One dimensional array.
   *  @param string $key_glue  Glue with which the keys are combined.
   *  @return array  Multi-dimensional array.
   */
  public static function expand($array,$key_glue){
    $result = [];
    foreach($array as $key => $value) self::set($result,explode($key_glue,$key),$value);
    return $result;
  }
  /**
   *  Get the n-th value from an array.
   *  @param array $array  Array to get value from.
   *  @param int $index  Value index (negative = start from end).
   *  @param mixed $default  Default value if the index does not exist.
   *  @return mixed
   */
  public static function value($array,$index = 0,$default = null){
    return self::get(array_values($array),$index + ($index < 0 ? count($array) : 0),$default);
  }
  /**
   *  Get the n-th key from an array.
   *  @param array $array  Array to get key from.
   *  @param int $index  Key index (negative = start from end).
   *  @return mixed  Found key, false if not existing.
   */
  public static function key($array,$index = 0){
    return self::get(array_keys($array),$index + ($index < 0 ? count($array) : 0),false);
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
   *  Splice with keys.
   *  @param array $array  Input array.
   *  @param int $offset  Where to start (negative = from end).
   *  @param int $length  Length of part to remove.
   *  @param array $replace  Array to replace removed part with.
   *  @return array
   */
  public static function splice($array,$offset,$length = 0,$replace = null){
    if($offset < 0) $offset += count($array);
    return array_merge(array_slice($array,0,$offset),$replace ?: [],array_slice($array,$offset + $length));
  }
  /**
   *  Merge arrays while preserving (duplicate) key sequence.
   *  E.g. ['a' => 5,'b' => 7,'d' => 3] + ['b' => 4,'c' => 2,'d' => 1] returns ['a' => 5,'b' => 4,'c' => 2,'d' => 1].
   *  @param array $array,... input array(s).
   *  @return array
   */
  public static function merge($array){
    $result = [];
    foreach(func_get_args() as $array) if($result){
      $insert = count($keys = array_keys($result));
      foreach($keys as $index => $key) if(array_key_exists($key,$array)){
        $insert = $index;
        break;
      }
      foreach($array as $key => $value) if(in_array($key,$keys)){
        $result[$key] = $value;
        $insert = array_search($key,array_keys($result)) + 1;
      }
      else $result = self::splice($result,$insert++,0,[$key => $value]);
    }
    else $result = $array;
    return $result;
  }
  /**
   *  Set the length of an array.
   *  @param array $array  Input array.
   *  @param int $length  Desired length.
   *  @param mixed $filler  Value for new possible entries.
   *  @return array  Input array with its length chopped or extended.
   */
  public static function resize($array,$length,$filler = null){
    return array_slice(array_pad($array,$length,$filler),0,$length);
  }
  /**
   *  Creates an array by using one array for keys and another for its values.
   *  Basicly PHP's array_combine, but then without the same length restriction.
   *  @param array $keys  Array of keys.
   *  @param array $values  Array of values.
   *  @param mixed $filler  Filler to use if array of values is shorter than keys.
   *  @return array  Combined array.
   */
  public static function combine($keys,$values,$filler = null){
    return array_combine($keys,self::resize($values,count($keys),$filler));
  }
  /**
   *  Searches the array for a given value and returns the first corresponding key if successful.
   *  Basicly PHP's array_search, but case insensitive.
   *  @param array $array  Array with values.
   *  @param mixed $value  Value to look for.
   *  @return mixed  Key if found, otherwise false.
   */
  public static function isearch($array,$value){
    $value = mb_strtolower($value);
    foreach($array as $key => $sub) if(mb_strtolower($sub) == $value) return $key;
    return false;
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
   *  Merge the key and value of an assoc.array into a record.
   *  @param array $array  Assoc.array.
   *  @param string $key_name  Name under which the key is added to the record. If empty the key is not added.
   *  @param string $value_name  Name under which the value is added to the record. If empty, and the value is an array, this
   *    array is merged with the record. Otherwise the value is not added.
   *  @return array  Array of records (key is preserved).
   */
  public static function mergeKey($array,$key_name = null,$value_name = null){
    $result = [];
    foreach($array as $key => $value){
      $record = [];
      if($key_name) $record[$key_name] = $key;
      if($value_name) $record[$value_name] = $value;
      elseif(is_array($value)) $record += $value;
      $result[$key] = $record;
    }
    return $result;
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
   *  Group records by a column.
   *  @param array $array  A multi-dimensional array (record set).
   *  @param string $column  The column to group by.
   *  @return array  Records (value; maintaining key association) grouped by column (key).
   */
  public static function group($array,$column){
    $result = [];
    foreach($array as $key => $record){
      if(!array_key_exists($group = self::get($record,$column),$result)) $result[$group] = [];
      $result[$group][$key] = $record;
    }
    return $result;
  }
  /**
   *  Shuffle an array.
   *  Basicly PHP's shuffle, but then retaining key association (and not altering the input array).
   *  @param array $array  Input array.
   *  @return array  Shuffled array.
   */
  public static function shuffle($array){
    $result = [];
    $keys = array_keys($array);
    shuffle($keys);
    foreach($keys as $key) $result[$key] = $array[$key];
    return $result;
  }
  /**
   *  Calculate the average of an array.
   *  @param array $array  Array with numerical values.
   *  @return float  Average value (false on empty array).
   */
  public static function average($array){
    return ($count = count($array)) ? array_sum($array) / $count : false;
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