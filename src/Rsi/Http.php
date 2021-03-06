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
   *  Protocol version.
   *  @return float  Protocol version (false if unknown).
   */
  public static function version(){
    static $version = null;
    if($version === null) $version = ($protocol = Record::get($_SERVER,'SERVER_PROTOCOL')) && ($i = strpos($protocol,'/'))
      ? (float)substr($protocol,$i + 1)
      : false;
    return $version;
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
      if(($port = Record::get($_SERVER,'SERVER_PORT')) && ($port != ($secure ? 443 : 80))) $host .= ':' . $port;
    }
    return $host;
  }
  /**
   *  Make sure an URL starts with a protocol. Add one if not present.
   *  @param string $url  Base URL.
   *  @param string $protocol  Protocol to add if none present.
   *  @return string  URL with protocol.
   */
  public static function ensureProtocol($url,$protocol = 'http'){
    return strpos($url,'://') ? $url : $protocol . ':' . (substr($url,0,2) == '//' ? '' : '//') . $url;
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
      ($max = \Rsi\Number::shorthandBytes(ini_get('post_max_size'),false)) &&
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
   *  Exand an abbreviated IPv6 address to its full representation.
   *  @param string $addr  Basic address.
   *  @return string  Expanded address (IPv4 addresses ae untouched).
   */
  public static function expandAddr($addr){
    if(count($groups = explode(':',$addr)) > 1){
      $addr = [];
      foreach($groups as $index => $group) if(!$index || $group) $addr[] = Str::pad($group,4);
      else $addr = array_merge($addr,array_fill(0,9 - count($groups),'0000'));
      $addr = implode(':',$addr);
    }
    return $addr;
  }
  /**
   *  Returns the expanded, remote address (if available).
   *  @return string
   */
  public static function remoteAddr(){
    static $remote_addr = null;
    if($remote_addr === null) $remote_addr = self::expandAddr(Record::get($_SERVER,'REMOTE_ADDR'));
    return $remote_addr;
  }
  /**
   *  Check if an IP-adres lies within a certain subnet.
   *  @param string $subnet  Semicolon separated list of IP-adresses. Within these address an asterisk may be used to indicate a
   *    group of alphanumeric characters. Regular expression notation is also allowed for this (e.g. '[1-4]', '\\d', '\\w').
   *    Dots (IPv4) and colons (IPv6) will always be escaped in the regualr expression.
   *  @param string $remote_addr  The IP-address to check (defaults to remoteAddr()).
   *  @return bool
   */
  public static function inSubnet($subnet,$remote_addr = null){
    return (bool)preg_match(
      '/^(' . strtr($subnet,['.' => '\.','::' => '(0+:)+0*',':' => '\:0*',';' => '|','*' => '\w+']) . ')$/',
      $remote_addr ?: self::remoteAddr()
    );
  }
  /**
   *  Add a redirection header.
   *  @param string $url  URL to redirect to.
   *  @param bool $permanent  True to make the redirection permanent (HTTP code 301; defaul is 302).
   */
  public static function redirHeader($url,$permanent = false){
    header('Location: ' . $url,true,$permanent ? 301 : 302);
  }
  /**
   *  Add a push header.
   *  @param string $src  Location of the resource.
   *  @param string $type  Type of the resource.
   *  @return bool  True if push is supported and the header is set, false otherwise.
   */
  public static function pushHeader($src,$type){
    if($push = self::version() >= 2) header("Link: <$src>; rel=preload; as=$type",false);
    return $push;
  }
  /**
   *  Send download headers.
   *  @param string $filename  Filename of the attached file.
   *  @param string $content_type  Content type (defaults to 'application/' + extension of filename).
   *  @param int $size  Size of the download (gives user progress indication).
   */
  public static function downloadHeaders($filename,$content_type = null,$size = null){
    header('Content-Type: ' . ($content_type ?: File::mime($filename)));
    header('Content-Transfer-Encoding: binary');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    if(($size !== null) || is_file($filename)) header('Content-Length: ' . ($size === null ? filesize($filename) : $size));
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0'); //HTTP/1.1
    header('Pragma: public'); //HTTP/1.0
  }
  /**
   *  Get content from URL.
   *  @param string $url  URL to make request to.
   *  @param array $post  Data to post with request (key =&gt; value pairs).
   *  @param array $headers  Extra HTTP headers to add to request (key = header name, value = header value).
   *  @param array $options  Extra cURL options (key = option name, value = option value).
   *  @param int $timeout  Request timeout in seconds.
   *  @param int $max_redirs  Maximum number of redirections to follow.
   *  @return mixed  Response text if susccessful (200) or false if not found (404). Exceptions thrown on cURL error or other
   *    response codes.
   */
  public static function urlGetContents($url,$post = null,$headers = null,$options = null,$timeout = null,$max_redirs = null){
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    if($post){
      curl_setopt($ch,CURLOPT_POST,true);
      curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
    }
    if($headers){
      foreach($headers as $key => &$value) $value = $key . ': ' . $value;
      unset($value);
      curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    }
    if($options) curl_setopt_array($ch,$options);
    curl_setopt($ch,CURLOPT_TIMEOUT,$timeout ?: ini_get('default_socket_timeout'));
    if($max_redirs){
      curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
      curl_setopt($ch,CURLOPT_MAXREDIRS,$max_redirs);
    }
    $result = curl_exec($ch);
    $message = ($error = curl_errno($ch)) ? curl_error($ch) : null;
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($error) throw new \Exception("cURL error: $message ($error)");
    switch($code){
      case 200: return $result;
      case 404: return false;
      default: throw new \Exception("unexpected response ($code): $result");
    }
  }

}