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

/**
 * extend 
 *
 * @param $args Arrays array to be extended and extension
 *
 * @return array $extended
 **/
  public static function extend() {
    $args = func_get_args();
    $extended = array();
    if(is_array($args) && count($args)) {
      foreach($args as $array) {
        if(is_array($array)) {
          $extended = array_merge($extended, $array);
        }
      }
    }
    return $extended;
  }
  

  /*
   * humanize
   *
   * @param $txt String CamelCase string to humanize
   *
   * @return String humanized string
   */
  public static function humanize($string) {
    $reg = "/(?<=[A-Z])(?=[A-Z][a-z])|(?<=[^A-Z])(?=[A-Z])|(?<=[A-Za-z])(?=[^A-Za-z])/";
    return preg_replace($reg, " ", $string);
  }


  /*
   * pluralize
   *
   * @param $txt String string to pluralize
   *
   * @return String pluralized string
   */
  public static function pluralize($string) {
    $plural = array(
      array( '/(quiz)$/i',               "$1zes"   ),
      array( '/^(ox)$/i',                "$1en"    ),
      array( '/([m|l])ouse$/i',          "$1ice"   ),
      array( '/(matr|vert|ind)ix|ex$/i', "$1ices"  ),
      array( '/(x|ch|ss|sh)$/i',         "$1es"    ),
      array( '/([^aeiouy]|qu)y$/i',      "$1ies"   ),
      array( '/([^aeiouy]|qu)ies$/i',    "$1y"     ),
      array( '/(hive)$/i',               "$1s"     ),
      array( '/(?:([^f])fe|([lr])f)$/i', "$1$2ves" ),
      array( '/sis$/i',                  "ses"     ),
      array( '/([ti])um$/i',             "$1a"     ),
      array( '/(buffal|tomat)o$/i',      "$1oes"   ),
      array( '/(bu)s$/i',                "$1ses"   ),
      array( '/(alias|status)$/i',       "$1es"    ),
      array( '/(octop|vir)us$/i',        "$1i"     ),
      array( '/(ax|test)is$/i',          "$1es"    ),
      array( '/s$/i',                    "s"       ),
      array( '/$/',                      "s"       )
    );

    $irregular = array(
      array( 'move',   'moves'    ),
      array( 'sex',    'sexes'    ),
      array( 'child',  'children' ),
      array( 'man',    'men'      ),
      array( 'person', 'people'   )
    );

    $uncountable = array( 
      'sheep', 
      'fish',
      'series',
      'species',
      'money',
      'rice',
      'information',
      'equipment'
    );

    $words = explode(" ", $string);
    $last  = array_pop($words);

    $string = implode(" ", $words);

    if ( in_array( strtolower( $string ), $uncountable ) )
      return $string . " " . $last;

    foreach ( $irregular as $noun ) {
    if ( strtolower( $last ) == $noun[0] )
      return $string . " " . $noun[1];
    }

    foreach ( $plural as $pattern ) {
      if ( preg_match( $pattern[0], $last ) ) {
        $plural = preg_replace( $pattern[0], $pattern[1], $last );
        return $string . " " . $plural;
      }
    }

    return $string . " " . $last;
  }
}
