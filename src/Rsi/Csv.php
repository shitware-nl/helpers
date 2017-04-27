<?php

namespace Rsi;

/**
 *  CSV wrapper.
 */
class Csv{

  public $keys = null;

  protected $_handle = null;
  protected $_delimiter = null;
  protected $_enclosure = null;
  protected $_escape = null;

  public function __construct($filename,$mode = 'r',$delimiter = ',',$enclosure = '"',$escape = '\\'){
    $this->_handle = fopen($filename,$mode);
    $this->_delimiter = $delimiter;
    $this->_enclosure = $enclosure;
    $this->_escape = $escape;
  }

  public function __destruct(){
    $this->close();
  }
  /**
   *  Read a single record from the file.
   *  @param string $filler  Filler to use if record is shorter than keys (if any).
   *  @return array  Record, ordered and with keys if any.
   */
  public function get($filler = null){
    $data = fgetcsv($this->_handle,0,$this->_delimiter,$this->_enclosure,$this->_escape);
    return $this->keys && ($data !== false) ? Record::combine($this->keys,$data,$filler) : $data;
  }
  /**
   *  Write a single record to the file.
   *  @param array $fields  Values.
   *  @param string $filler  Filler to use if array of values does not contain all keys (if any).
   *  @return int  Length of the written string or FALSE on failure.
   */
  public function put($fields,$filler = null){
    if($this->keys){
      $record = [];
      foreach($this->keys as $key) $record[] = Record::get($fields,$key,$filler);
      $fields = $record;
    }
    return fputcsv($this->_handle,$fields,$this->_delimiter,$this->_enclosure,$this->_escape);
  }
  /**
   *  Read the keys from the file and store them.
   *  @return array
   */
  public function getKeys(){
    $this->keys = null;
    return $this->keys = $this->get();
  }
  /**
   *  Write the keys to the file and store them.
   *  @param array $keys
   */
  public function putKeys($keys){
    $this->keys = null;
    $this->put($keys);
    $this->keys = $keys;
  }
  /**
   *  Return all remaining records from the file.
   *  @param string $filler  Filler to use if record is shorter than keys (if any).
   *  @return array  Array of records.
   */
  public function getAll($filler = null){
    $records = [];
    while(!$this->eof()) if(($record = $this->get($filler)) !== false) $records[] = $record;
    return $records;
  }
  /**
   *  Close the file.
   */
  public function close(){
    if($this->_handle) fclose($this->_handle);
    $this->_handle = null;
  }

  public function __call($func_name,$params){
    return call_user_func_array('f' . Str::snake($func_name),array_merge([$this->_handle],$params));
  }

}