<?php

namespace Rsi;

/**
 *  FTP wrapper.
 */
class Ftp{

  protected $_connection = null;

  public function __construct($host,$port = 21,$secure = false,$timeout = 90){
    $this->_connection = $secure ? ftp_ssl_connect($host,$port,$timeout) : ftp_connect($host,$port,$timeout);
  }

  public function __destruct(){
    $this->close();
  }

  public function close(){
    if($this->_connection) ftp_close($this->_connection);
    $this->_connection = null;
  }

  public function putContents($remote,$data,$mode = FTP_BINARY){
    $file = fopen('php://memory','r+');
    fputs($file,$data);
    rewind($file);
    $result = $this->fput($remote,$file,$mode);
    fclose($file);
    return $result;
  }

  public function getContents($remote,$mode = FTP_BINARY){
    $file = fopen('php://memory','r+');
    $data = false;
    if($this->fget($file,$remote,$mode)){
      rewind($file);
      while(!feof($file)) $data .= fread($file,1024);
      fclose($file);
    }
    return $data;
  }

  public function mkdir($path,$mode = 0777){
    $result = true;
    if($this->nlist($path) === false){
      $this->mkdir(dirname($path));
      $result = ftp_mkdir($this->_connection,$path);
      if($mode) $this->chmod($mode,$path);
    }
    return $result;
  }

  public function isDir($path){
    $current = $this->pwd();
    $result = false;
    try{
      if($result = $this->chdir($path)) $this->chdir($current);
    }
    catch(\Exception $e){}
    return $result;
  }
  /**
   *  Find and filter files.
   *  @see \\Rsi\\File::find
   */
  public function find($path,$filters = null,$recursive = false){
    if(!$this->isDir($path)) return false;
    if(!$filters) $filters = [];
    if(!array_key_exists(File::FIND_FILTER_TYPE,$filters)) $filters[File::FIND_FILTER_TYPE] = File::FIND_TYPE_FILE;
    $files = [];
    if($dir = $this->nlist($path)) foreach($dir as $entry) if(trim(basename($entry),'.')) try{
      $info = ['dir' => $is_dir = $this->isDir($entry)];
      if($is_dir && $recursive) $files += $this->find($entry,$filters,$recursive);
      foreach($filters as $key => $value){
        if($operator = preg_match('/^(\\w+)(\\W+)$/',$key,$match) ? $match[2] : null) $key = $match[1];
        else $operator = '==';
        switch($key){
          case File::FIND_FILTER_TYPE:
            if($value == File::FIND_TYPE_ALL) break;
            if(($value & File::FIND_TYPE_DIR) && !$is_dir) continue 3;
            if(($value & File::FIND_TYPE_FILE) && $is_dir) continue 3;
            break;
          case File::FIND_FILTER_NAME:
            if(!Str::operator($info[$key] = $entry,$operator,$value)) continue 3;
            break;
          case File::FIND_FILTER_TIME:
            if($is_dir || !Str::operator($info[$key] = $this->mdtm($entry),$operator,$value)) continue 3;
            break;
          case File::FIND_FILTER_SIZE:
            if($is_dir || !Str::operator($info[$key] = $this->size($entry),$operator,\Rsi\Number::shorthandBytes($value))) continue 3;
            break;
          case File::FIND_FILTER_FUNC:
            if(!($info[$key] = call_user_func($value,$full,$info))) continue 3;
            break;
        }
      }
      $files[$entry] = $info;
    }
    catch(\Exception $e){}
    return $files;
  }
  /**
   *  Find files.
   *  @see \\Rsi\\File::dir
   */
  public function dir($path,$pattern = null,$recursive = false){
    $filters = [File::FIND_FILTER_TYPE => File::FIND_TYPE_FILE];
    if($pattern) $filters[File::FIND_FILTER_NAME . '//'] = '/^' . strtr(preg_quote($pattern,'/') . '$/i',['\\*' => '.*','\\?' => '.']);
    return ($files = $this->find($path,$filters,$recursive)) ? array_keys($files) : $files;
  }

  public function __call($func_name,$params){
    return call_user_func_array('ftp_' . Str::snake($func_name),array_merge([$this->_connection],$params));
  }

}