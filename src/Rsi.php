<?php

class Rsi{

  /**
   *  Check if PHP is run from the command line.
   *  @return bool
   */
  public static function commandLine(){
    return PHP_SAPI == 'cli';
  }
  /**
   *  Check PHP version.
   *  @param string $version  Version to compare to.
   *  @return bool  True if the current PHP-version is greater or equal than the provided version.
   */
  public static function version($version){
    return version_compare(PHP_VERSION,$version,'>=');
  }
  /**
   *  Check if a value is empty.
   *  @param mixed $value
   *  @return bool  True when empty.
   */
  public static function nothing($value){
    return is_array($value) ? !$value : !strlen($value);
  }

}