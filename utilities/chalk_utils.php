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
        $filename = preg_replace( "/\/.*\//", "", end( get_included_files() ) );
        
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
   * get_php_classes
   *
   * @param $txt String php code from which to extract classes
   *
   * @return Array list of classes
   */
  public static function get_php_classes($txt) {
    $classes = array();
    $tokens = token_get_all($txt);
    $class_token = false;

    foreach($tokens as $token) {

      if(is_array($token)) {
        if($token[0] == T_CLASS) {
          $class_token = true;
        } elseif($class_token && $token[0] == T_STRING) {
          $classes[] = $token[1];
          $class_token = false;
        }
      }

    }

    return $classes;
  }

}
