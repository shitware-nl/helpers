<?php

namespace Rsi;

class Timer{

  public $decimals = 6;
  public $times = []; //!<  Times (value) per id (key).

  protected $_id = null; //!<  Active id.
  protected $_start = null; //!<  Time the active id started.

  /**
   *  Stop the running timer.
   */
  public function stop(){
    if($this->_id) $this->times[$this->_id] += microtime(true) - $this->_start;
  }
  /**
   *  Start a timer.
   *  @param string $id  ID for the timer.
   */
  public function start($id){
    $this->stop();
    if(!array_key_exists($this->_id = $id,$this->times)) $this->times[$this->_id] = 0;
    $this->_start = microtime(true);
  }
  /**
   *  Time for a single ID.
   *  @param string $id  ID of the timer.
   *  @return float  Time (including active timer).
   */
  public function time($id){
    if(!array_key_exists($id,$this->times)) return false;
    $time = $this->times[$id];
    if($this->_id == $id) $time += microtime(true) - $this->_start;
    return $time;
  }
  /**
   *  Text representation of times.
   *  @return string
   */
  public function asText(){
    $id_length = 2;
    $time_length = 8;
    $times = [];
    foreach($this->times as $id => $time){
      $id_length = max($id_length,strlen($id));
      $time_length = max($time_length,$times[$id] = number_format($time,$this->decimals));
    }
    $text =
      str_pad('ID',$id_length) . ' | ' . str_pad('time [s]',$time_length,' ',STR_PAD_LEFT) . "\n" .
      str_repeat('-',$id_length) . '-+-' . str_repeat('-',$time_length);
    foreach($this->times as $id => $time)
      $text .= "\n" . str_pad($id,$id_length) . ' | ' . str_pad($times[$id],$time_length,' ',STR_PAD_LEFT);
    return $text;
  }
  /**
   *  HTML representation of times.
   *  @return string
   */
  public function asHtml(){
    $html = "<table class='timer'>\n<tr><th>ID</th><th>time [s]</th></tr>\n";
    foreach($this->times as $id => $time)
      $html .= "<tr><td>$id</td><td>" . number_format($time,$this->decimals) . "</td></tr>\n";
    return $html . '</table>';
  }

  public function __get($name){
    return $this->time($name);
  }

  public function __call($name,$arguments){
    $this->start($name);
  }

  public function __toString(){
    return $this->asText();
  }

}