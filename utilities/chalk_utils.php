<?php

class ChalkUtils {

  /**
   * list_dir
   *
   * @param $path String path to directory
   *
   */
  public static function list_dir($path, $ext = "php") {
    return glob(self::join_paths($path, "*.$ext"));
  }
  
  /**
   * require_once_dir
   *
   * @param $path String  path to directory
   * @param $cb   Func    callback function after file is loaded 
   * @param $ext  Bool    should the callback receive the filename with extension
   *
   * @return nada
   */
  public static function require_once_dir($path, $cb = false, $ext = true) {
    $list_files = self::list_dir($path);
    if (is_array($list_files)) {
      foreach ($list_files as $filename) {
        $ret = require_once $filename; 
        $filename = preg_replace( "/\/.*\//", "", $filename );
        
        if( !$ext ) {
          $filename = preg_replace( "/\..*$/", "", $filename );
        } 

        if( is_callable($cb) ) {
          call_user_func($cb, $ret, $filename);
        }
      } 
    }
  }

  /**
   * get_once_dir
   *
   * @param $path String  path to directory
   * @param $cb   Func    callback function after file is loaded 
   * @param $ext  Bool    should the callback receive the filename with extension
   *
   * @return nada
   */
  public static function get_dir($path, $cb = false, $ext = true) {
    $list_files = self::list_dir( $path );

    if( is_array($list_files) ) {
      foreach($list_files as $filename) {
        $php_txt = file_get_contents( "$filename" );
        $filename = preg_replace( "/\/.*\//", "", $filename );

        if( !$ext ) {
          $filename = preg_replace( "/\..*$/", "", $filename );
        } 

        if( is_callable($cb) ) {
          call_user_func($cb, $php_txt, $filename);
        }
      }
    }
  }

  /**
   * join_paths 
   *
   * @param multiple String directory or string
   *
   * @return String 
   */
  public static function join_paths() {
    $args = func_get_args();
    $paths = array();

    foreach($args as $arg) {
      $paths = array_merge($paths, (array)$arg);
    }

    foreach($paths as &$path) {
      $path = trim($path, '/');
    }

    if (substr($args[0], 0, 1) == '/') {
      $paths[0] = '/' . $paths[0];
    }

    return join('/', $paths);
  }

  /*
   * get_php_tokens
   *
   * @param $txt String php code from which to extract a token type
   *
   * @return Array list of php of the type of token 
   */
  public static function get_php_tokens($txt, $type) {
    $objects = array();
    $tokens = token_get_all($txt);

    $object_token = false;

    foreach($tokens as $token) {

      if(is_array($token)) {
        if($token[0] == $type) {
          $object_token = true;
        } elseif($object_token && $token[0] == T_STRING) {
          $objects[] = $token[1];
          $object_token = false;
        }
      }

    }

    return $objects;
  }

  /*
   * get_php_classes
   *
   * @param $txt String php code from which to extract classes
   *
   * @return Array list of classes
   */
  public static function get_php_classes($txt) {
    return self::get_php_tokens($txt, T_CLASS);
  }

  /*
   * humanize
   *
   * @param $txt String CamelCase string to humanize
   *
   * @return String humanized string
   */
  public static function humanize($txt) {
    $reg = "/(?<=[A-Z])(?=[A-Z][a-z])|(?<=[^A-Z])(?=[A-Z])|(?<=[A-Za-z])(?=[^A-Za-z])/";
    return preg_replace($reg, " ", $txt);
  }

}
