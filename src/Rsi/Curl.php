<?php

namespace Rsi;

/**
 *  cURL wrapper.
 */
class Curl{

  protected $_handle = null;
  protected $_constants = null;
  protected $_options = [];
  protected $_infos = [];

  public function __construct($url = null,$return_transfer = true,$follow_location = true){
    if($this->_handle = curl_init($url)){
      if($return_transfer) $this->returntransfer = true;
      if($follow_location) $this->followlocation = true;
    }
  }

  public function __destruct(){
    $this->close();
  }

  public function close(){
    if($this->_handle) curl_close($this->_handle);
    $this->_handle = null;
  }

  protected function getConstants(){
    if($this->_constants === null) $this->_constants = Record::get(get_defined_constants(true),'curl');
    return $this->_constants;
  }

  public function getOptions(){
    if(!$this->_options) foreach($this->getConstants() as $name => $value)
      $this->_options[lcfirst(Str::camel(strtolower(substr($name,8))))] = $value;
    return $this->_options;
  }

  public function getInfos(){
    if(!$this->_infos) foreach($this->getConstants() as $name => $value)
      $this->_infos[lcfirst(Str::camel(strtolower(substr($name,9))))] = $value;
    return $this->_infos;
  }

  public function __set($key,$value){
    if(!array_key_exists($key,$this->getOptions())) throw new \Exception("Unknown option '$key'");
    $this->setopt($this->_options[$key],$value);
  }

  public function __get($key){
    if(!array_key_exists($key,$this->getInfos())) throw new \Exception("Unknown info '$key'");
    return $this->getinfo($this->_infos[$key]);
  }

  public function __call($func_name,$params){
    return call_user_func_array('curl_' . Str::snake($func_name),array_merge([$this->_handle],$params));
  }

}