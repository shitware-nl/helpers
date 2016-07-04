<?php

namespace Rsi;

/**
 *  HTTP request helpers.
 */
class Http{

  /**
   *  Document root.
   *  @return string  Document root without trailing delimiter.
   */
  public static function docRoot(){
    static $root = null;
    if($root === null){
      $root = Record::get($_SERVER,'DOCUMENT_ROOT');
      if(!$root && ($filename = Record::get($_SERVER,'SCRIPT_FILENAME')))
        $root = str_replace('\\','/',\substr($filename,0,-strlen(Record::get($_SERVER,'PHP_SELF'))));
      if(!$root && ($path = Record::get($_SERVER,'PATH_TRANSLATED')))
        $root = str_replace(['\\','\\\\'],'/',substr($path,0,-strlen(Record::get($_SERVER,'PHP_SELF'))));
      $root = rtrim($root,'/');
    }
    return $root;
  }
  /**
   *  Check if the request was made over a secure connection.
   *  @return bool  True if the request was made over a secure connection.
   */
  public static function secure(){
    return array_key_exists('HTTPS',$_SERVER) && !strcasecmp($_SERVER['HTTPS'],'on');
  }
  /**
   *  Host name.
   *  @param bool $complete  Include protocol and port number.
   *  @return string
   */
  public static function host($complete = false){
    $host = Record::get($_SERVER,'SERVER_NAME') ?: php_uname('n');
    if($complete){
      $host = (($secure = self::secure()) ? 'https' : 'http') . '://' . $host;
      $default = $secure ? 443 : 80;
      if(($port = Record::get($_SERVER,'SERVER_PORT')) && ($port != ($secure ? 443 : 80))) $host .= ':' . $port;
    }
    return $host;
  }
  /**
   *  Check if the maximum POST size was exceeded.
   *  @param int $size  Size of POST.
   *  @param int $max  Maximum size.
   *  @return bool
   */
  public static function postExceeded(&$size = null,&$max = null){
    $size = $max = null;
    return
      !$_POST &&
      !$_FILES &&
      ($size = Record::get($_SERVER,'CONTENT_LENGTH')) &&
      ($max = \Rsi::shorthandBytes(ini_get('post_max_size'),false)) &&
      ($size > $max);
  }
  /**
   *  Set a cookie.
   *  The cookie is also directly added to the global $_COOKIE array.
   *  @param string $key  Cookie key.
   *  @param mixed $value  Cookie value.
   *  @param int $days  Cookie lifetime (in days).
   *  @param bool $secure  Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client.
   *    When undefined, this value is automaticly set to true when the request was made using a secure connection.
   *  @param bool $httponly  When TRUE the cookie will be made accessible only through the HTTP protocol.
   */
  public static function setCookie($key,$value,$days = 365,$secure = null,$httponly = true){
    setcookie($key,$value,$days ? time() + $days * 86400 : 0,'/','',$secure === null ? self::secure() : $secure,$httponly);
    $_COOKIE[$key] = $value;
  }
  /**
   *  Retrieve a cookie.
   *  @param string $key  Cookie key.
   *  @param mixed $default  Default value if the key does not exist.
   *  @return mixed  Found value, or default value if the key does not exist.
   */
  public static function getCookie($key,$default = null){
    return Record::get($_COOKIE,$key,$default);
  }
  /**
   *  Get the prefered language from the client.
   *  @param array $accepted  Accepted languages (with or without locale; empty = all).
   *  @param float $quality  Minimal quality a language must have.
   *  @return string  Language preference (empty = none found).
   */
  public static function lang($accepted = null,$quality = 0){
    $lang = null;
    if(preg_match_all(
      '/([a-z]{1,8})(-[a-z]{1,8})?\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i',
      Record::get($_SERVER,'HTTP_ACCEPT_LANGUAGE'),
      $client_accepted,
      PREG_SET_ORDER
    )) foreach($client_accepted as $client_accept){
      $client_lang = $client_accept[1] . Record::get($client_accept,2);
      if(
        (!$accepted || in_array($client_lang,$accepted) || in_array($client_lang = $client_accept[1],$accepted)) &&
        (($client_quality = Record::get($client_accept,4,1)) > $quality)
      ){
        $lang = $client_lang;
        $quality = $client_quality;
      }
    }
    return $lang;
  }
  /**
   *  Check if an IP-adres lies within a certain subnet.
   *  @param string $subnet  Semicolon separated list of IP-adresses. Within these address an asterisk may be used to indicate a
   *    group of alphanumeric characters. Regular expression notation is also allowed for this (eg '[1-4]', '\\d', '\\w'). Dots
   *    (IPv4) and colons (IPv6) will always be escaped in the regualr expression.
   *  @param string $remote_addr  The IP-address to check (defaults to $_SERVER['REMOTE_ADDR']).
   *  @return bool
   */
  public static function inSubnet($subnet,$remote_addr = null){
    return preg_match(
      '/^(' . strtr($subnet,['.' => '\.',':' => '\:',';' => '|','*' => '\w+']) . ')$/',
      $remote_addr ?: Record::get($_SERVER,'REMOTE_ADDR')
    );
  }
  /**
   *  Send download headers.
   *  @param string $filename  Filename of the attached file.
   *  @param string $content_type  Content type (defaults to 'application/' + extension of filename).
   *  @param int $size  Size of the download (gives user progress indication).
   */
  public static function downloadHeaders($filename,$content_type = null,$size = null){
    header('Content-Type: ' . ($content_type ?: 'application/' . File::ext($filename)));
    header('Content-Transfer-Encoding: binary');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    if($size !== null) header('Content-Length: ' . $size);
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); //HTTP/1.1
    header('Pragma: public'); //HTTP/1.0
  }

}