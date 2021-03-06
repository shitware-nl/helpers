<?php

namespace Rsi;

/**
 *  File and file system helper functions.
 */
class File{

  const FIND_FILTER_TYPE = 'type'; //!<  Specify the type (see FIND_TYPE_* constants).
  const FIND_FILTER_NAME = 'name'; //!<  Specify the name.
  const FIND_FILTER_TIME = 'time'; //!<  Specify the time (Unix format).
  const FIND_FILTER_SIZE = 'size'; //!<  Speficy the size (shorthand notation allowed).
  const FIND_FILTER_FUNC = 'func'; //!<  Specify a callback function (1st param = full name, 2nd param = info gathered). The
    //  entry is only included when the function returns a non-empty value (this value is added to the info).

  const FIND_TYPE_DIR = 1;
  const FIND_TYPE_FILE = 2;
  const FIND_TYPE_ALL = 255;

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
   *  @param int $levels  Number of levels to go up.
   *  @return string  Name of the directory of the given path.
   */
  public static function dirname($path,$levels = 1){
    while($levels-- && $path) $path = substr($path,0,max(strrpos($path,'/'),strrpos($path,'\\')));
    return $path;
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
   *  @return int  Last modification time. False if file does not exists.
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
   *  Write data to a file.
   *  @param string $filename  File to write to.
   *  @param mixed $data  Data to save.
   *  @param int $mode  File mode (not set if empty).
   *  @param bool $append  Append on true, overwrite on false.
   *  @return int  Number of bytes written or false on error.
   */
  public static function write($filename,$data,$mode = null,$append = false){
    $result = file_put_contents($filename,$data,$append ? FILE_APPEND : null);
    if($mode) chmod($filename,$mode);
    return $result;
  }
  /**
   *  Read the content of a file.
   *  @param string $filename  File to read from.
   *  @param mixed $default  Value to return if the file does not exist.
   *  @return mixed  Data.
   */
  public static function read($filename,$default = false){
    return is_file($filename) ? file_get_contents($filename) : $default;
  }
  /**
   *  Serialize data and write it to a file.
   *  @param string $filename  File to write to.
   *  @param mixed $data  Data to serialize and save.
   *  @param int $mode  File mode (not set if empty).
   *  @return int  Number of bytes written or false on error.
   */
  public static function serialize($filename,$data,$mode = null){
    return self::write($filename,serialize($data),$mode);
  }
  /**
   *  Unserialize the content of a file.
   *  @param string $filename  File to read from.
   *  @param mixed $default  Value to return if the file does not exist.
   *  @return mixed  Unserialized data.
   */
  public static function unserialize($filename,$default = false){
    return is_file($filename) ? unserialize(file_get_contents($filename)) : $default;
  }
  /**
   *  Encode data to JSON and write it to a file.
   *  @param string $filename  File to write to.
   *  @param mixed $data  Data to decode and save.
   *  @param int $mode  File mode (not set if empty).
   *  @param int $options  PHP's json_encode options.
   *  @return int  Number of bytes written or false on error.
   */
  public static function jsonEncode($filename,$data,$mode = null,$options = JSON_PRETTY_PRINT){
    return self::write($filename,json_encode($data,$options),$mode);
  }
  /**
   *  Decode the JSON content of a file.
   *  @param string $filename  File to read from.
   *  @param mixed $default  Value to return if the file does not exist.
   *  @param bool $assoc  When true, returned objects will be converted into assoc.arrays.
   *  @return mixed  Decoded data.
   */
  public static function jsonDecode($filename,$default = false,$assoc = true){
    return is_file($filename) ? json_decode(file_get_contents($filename),$assoc) : $default;
  }
  /**
   *  Get MIME type for a file.
   *  @param string $filename  Filename.
   *  @return string  MIME type.
   */
  public static function mime($filename){
    if(function_exists('mime_content_type()') && is_file($filename)) return mime_content_type($filename);
    if(array_key_exists($ext = self::ext($filename),$types = require(__DIR__ . '/mime.php'))) return $types[$ext];
    return 'application/' . $ext;
  }
  /**
   *  Find and filter files.
   *  @param string $path  Base path.
   *  @param array $filters  Array with filters. Key = filter (see FIND_FILTER_* constants) plus optional operator (see
   *    Str::operator()), value = value to compare to.
   *  @param bool $recursive  True to do a recursive search.
   *  @return array  Key = full name, value = info gathered for the filtering (key = filter key, value = entry value). False if
   *    path is not a directory.
   */
  public static function find($path,$filters = null,$recursive = false){
    if(!is_dir($path)) return false;
    if(!$filters) $filters = [];
    if(!array_key_exists(self::FIND_FILTER_TYPE,$filters)) $filters[self::FIND_FILTER_TYPE] = self::FIND_TYPE_FILE;
    $files = [];
    $dir = dir($path = self::addDirSeparator($path));
    while(($entry = $dir->read()) !== false) if(trim($entry,'.') !== '') try{
      $info = ['dir' => $is_dir = is_dir($full = $path . $entry)];
      if($is_dir && $recursive) $files += self::find($full,$filters,$recursive);
      foreach($filters as $key => $value){
        if($operator = preg_match('/^(\\w+)(\\W+)$/',$key,$match) ? $match[2] : null) $key = $match[1];
        else $operator = '==';
        switch($key){
          case self::FIND_FILTER_TYPE:
            if($value == self::FIND_TYPE_ALL) break;
            if(($value & self::FIND_TYPE_DIR) && !$is_dir) continue 3;
            if(($value & self::FIND_TYPE_FILE) && $is_dir) continue 3;
            break;
          case self::FIND_FILTER_NAME:
            if(!Str::operator($info[$key] = $entry,$operator,$value)) continue 3;
            break;
          case self::FIND_FILTER_TIME:
            if($is_dir || !Str::operator($info[$key] = filemtime($full),$operator,$value)) continue 3;
            break;
          case self::FIND_FILTER_SIZE:
            if($is_dir || !Str::operator($info[$key] = filesize($full),$operator,\Rsi\Number::shorthandBytes($value))) continue 3;
            break;
          case self::FIND_FILTER_FUNC:
            if(!($info[$key] = call_user_func($value,$full,$info))) continue 3;
            break;
        }
      }
      $files[$full] = $info;
    }
    catch(\Exception $e){}
    $dir->close();
    return $files;
  }
  /**
   *  Find files.
   *  @param string $path  Base path.
   *  @param string $pattern  Filename pattern (with '*' and '?' as possible wildcards).
   *  @param bool $recursive  True to do a recursive search.
   *  @return array  Full names. False if path is not a directory.
   */
  public static function dir($path,$pattern = null,$recursive = false){
    $filters = [self::FIND_FILTER_TYPE => self::FIND_TYPE_FILE];
    if($pattern) $filters[self::FIND_FILTER_NAME . '//'] = '/^' . strtr(preg_quote($pattern,'/') . '$/i',['\\*' => '.*','\\?' => '.']);
    return ($files = self::find($path,$filters,$recursive)) ? array_keys($files) : $files;
  }

}