<?php

namespace Rsi;

/**
 *  File an file system helper functions.
 */
class File{

  /**
   *  Add a directory separator to the path (if not present)
   *  @param string $path
   *  @return string  Path including the final directory separator.s
   */
  public static function addDirSeparator($path){
    return preg_match('/[\\\\\\/]$/',$path) ? $path : $path . DIRECTORY_SEPARATOR;
  }
  /**
   *  Create a directory (if not exists).
   *  @param string $path.
   *  @param int $mode  Mode (read/write/exec) for the new directory.
   */
  public static function mkdir($path,$mode = 0777){
    $mask = umask(0);
    if(!is_dir($path)) mkdir($path,$mode,true);
    umask($mask);
  }
  /**
   *  Dirname that does not care about separator, and does not return '.' when no directory.
   *  @param string $path
   *  @return string  Name of the directory of the given path.
   */
  public static function dirname($path){
    return substr($path,0,max(strrpos($path,'/'),strrpos($path,'\\')));
  }
  /**
   *  Basename that does not care about separator, and also removes possible query parameters.
   *  @param string $path
   *  @return string  Base name of the given path.
   */
  public static function basename($path){
    return preg_replace('/(^.*[\\\\\\/]|\\?.*$)/','',$path);
  }
  /**
   *  Get extension of a file.
   *  @param string $filename  Filename.
   *  @param bool $lower_case  True to convert the extension to lower case.
   *  @return string  Extension (without the leading dot).
   */
  public static function ext($filename,$lower_case = true){
    $ext = pathinfo($filename,PATHINFO_EXTENSION);
    return $lower_case ? strtolower($ext) : $ext;
  }
  /**
   *  Temporarily directory of the system.
   *  @return string.
   */
  public static function tempDir(){
    $path = sys_get_temp_dir();
    if(!$path){
      $path = dirname($filename = tempnam(__FILE__,''));
      unlink($filename);
    }
    return self::addDirSeparator($path);
  }
  /**
   *  Create a temporarily file.
   *  @param string $ext  Extension of the temporarily file (without the leading dot).
   *  @param string $data  Data to put in the file (file will be created anyway if empty).
   *  @return string  Name of the temporarily file.
   */
  public static function tempFile($ext = 'tmp',$data = null){
    $dir = self::tempDir();
    while(file_exists($filename = $dir . Str::random() . '.' . $ext));
    file_put_contents($filename,$data);
    return $filename;
  }
  /**
   *  Optimistic filemtime (no need to check file_exists() first).
   *  @param string $filename  File to get modification time for.
   *  @param int  Last modification time. False if file does not exists.
   */
  public static function mtime($filename){
    try{
      return filemtime($filename);
    }
    catch(\Exception $e){
      return false;
    }
  }
  /**
   *  Safe unlink.
   *  @param string $filename  File to delete.
   *  @return bool  True if file does not exist afterwards.
   */
  public static function unlink($filename){
    try{
      return unlink($filename);
    }
    catch(\Exception $e){
      return !file_exists($filename);
    }
  }
  /**
   *  Serialize data and write it to a file.
   *  @param string $filename  File to write to.
   *  @param mixed $data  Data to serialize and save.
   *  @return int  Number of bytes written or false on error.
   */
  public static function serialize($filename,$data){
    return file_put_contents($filename,serialize($data));
  }
  /**
   *  Unserialize the content of a file.
   *  @param string $filename  File to read from.
   *  @return mixed  Unserialized data.
   */
  public static function unserialize($filename){
    return unserialize(file_get_contents($filename));
  }
  /**
   *  Encode data to JSON and write it to a file.
   *  @param string $filename  File to write to.
   *  @param mixed $data  Data to decode and save.
   *  @return int  Number of bytes written or false on error.
   */
  public static function jsonEncode($filename,$data,$options = JSON_PRETTY_PRINT){
    return file_put_contents($filename,json_encode($data,$options));
  }
  /**
   *  Decode the JSON content of a file.
   *  @param string $filename  File to read from.
   *  @return mixed  Decoded data.
   */
  public static function jsonDecode($filename,$assoc = true){
    return json_decode(file_get_contents($filename),$assoc);
  }
  /**
   *  Get MIME type for a file.
   *  @param string $filename  Filename.
   *  @return string  MIME type.
   */
  public static function mime($filename){
    if(!function_exists('finfo_open')) return false;
    $info = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($info,$filename);
    finfo_close($info);
    return $mime;
  }

}