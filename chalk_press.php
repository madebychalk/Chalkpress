<?php

require_once dirname(__FILE__) . '/utilities/chalk_utils.php';
require_once dirname(__FILE__) . '/utilities/chalk_menu.php';

class ChalkPress extends ChalkUtils {
  
  /**
    * @property $menus Array holds the names of registered wordpress menus
    *
    */
  private static $menus       = array();
  private static $post_types  = array();
  private static $helpers     = array();

  /**
    * add_notice
    *
    * kicks off ChalkPress functionality
    *
    * @param $cb String|Array name of function or array of class and method to execute after initialization.
    *
    */
  public static function initialize($cb = null) {
      self::require_helpers();
      self::require_cmb();
      self::require_abstract_components();

      self::chalkpress_require_features();

      if( is_callable($cb) ) {
        call_user_func($cb);
      }
  }


  public static function require_abstract_components() {
    self::require_once_dir( self::abstract_components_path() );
  }

  /**
    * require_chalkpress_component
    *
    * load custom classes and register component
    */

  public static function require_chalkpress_component($php_txt) {
    $class = self::get_php_classes($php_txt);

    if( is_array( $class ) ) $class = $class[0];

    eval("?>$php_txt");

    $type = get_parent_class($class);

    switch($type) {
      case 'ChalkpressPostType':
        self::get_post_type($class);
        break;
    }
  }

  /**
    * require_theme_menus
    *
    * loads all the php files located in the theme menu directory
    * executes the add_menus method for each file.
    */
  public static function require_theme_menus() {
    self::get_dir( self::theme_menus_path(), array(__CLASS__, 'register_theme_menu'), false );
  }

  /**
    * register_menu
    *
    * registers a menu for use within wordpress
    *
    * @param $menu Array a valid wp menu configuration object
    * @param $name String name under which the menu will be registered
    */
  public static function register_theme_menu($php_txt) {
    $classes = self::get_php_classes($php_txt);

    eval("?>$php_txt");

    if( is_array($classes) ) {
      foreach($classes as $class) {
        self::get_theme_menu($class);
      }
    } else {
      self:get_theme_menu($classes);
    }
  }

  /**
    * get_theme_menu
    *
    * returns or instantiates a new menu location
    *
    * @param $class_name Class used to create the menu
    */
  public static function get_theme_menu($class_name) {
    $menu_name = self::humanize($class_name);

    if( !isset(self::$menus[$menu_name]) ) {
      self::$menus[$menu_name] = new $class_name;
      self::$menus[$menu_name]->set_menu_params();

      register_nav_menu( $class_name, $menu_name );
    }

    return self::$menus[$menu_name];
  }

  /**
    * display_theme_menu
    *
    * used on the front end to display a menu
    *
    * @param $menu_name Name of the menu to display, same string as shown in wp-admin
    */ 
  public static function display_theme_menu($menu_name) {
    wp_nav_menu( self::$menus[$menu_name]->menu );
  }

  /**
    * require_helpers
    *
    * loads all the helpers for chalkpress
    * will set up global functions
    */
  public static function require_helpers() {
    self::get_dir( self::helpers_path(), array(__CLASS__, 'register_helper') );
  }

  /*
   * register_helpers
   *
   * generates static methods that call the helper methods
   *
   * @param $classes Array || String classes from which to generate methods
   */
  public static function register_helper($php_txt) {
    $classes = self::get_php_classes($php_txt);

    eval("?>$php_txt");

    if( is_array($classes) ) {
      foreach($classes as $class) {
        self::get_helper($class);
      }
    } else {
      self::get_helper($classes);
    }
  }

  /**
    * require_cmb
    *
    * loads cmb dependency in chalkpress
    * cmb is in a submodule git submodule init
    * if you are getting not found errors
    */
  public static function require_cmb() {
    require_once self::join_paths( self::vendor_path(), 'metabox', 'init.php');
  }

  /*
   * get_helper
   *
   * return reference or try to instantiate a helper
   *
   * @param $class_name String name of the class to find/instantiate
   *
   * @return Class
   */
  public static function get_helper($class_name) {
    if( !isset(self::$helpers[$class_name]) ) {
      self::$helpers[$class_name] = new $class_name;
    }
    return self::$helpers[$class_name];
  }

  /**
    * require_theme_post_types
    *
    * loads all the php files located in the theme post type directory
    * exectutes the add_post_types method for each file
    */
  public static function require_theme_post_types() {
    self::get_dir( self::theme_post_types_path(), array(__CLASS__, 'require_chalkpress_component') );
  }

  /*
   * get_post_type
   *
   * return reference or try to instantiate a post_type
   *
   * @param $class_name String name of the class to find/instantiate
   *
   * @return Class
   */
  public static function get_post_type($class_name) {
    $post_type_name = strtolower($class_name);

    if( !isset(self::$post_types[$post_type_name]) )
      self::$post_types[$post_type_name] = new $class_name;

    return self::$post_types[$post_type_name];
  }

  /**
    * require_theme_helpers
    *
    * loads all the php files located in the theme helper directory
    */
  public static function require_theme_helpers() {
    $helpers_path = self::theme_helpers_path();
    self::require_once_dir("$helpers_path");
  }

  /**
    * helpers_path
    *
    * @return String path to chalkpress helpers
    */
  public static function helpers_path() {
    return self::join_paths(get_stylesheet_directory(), 'library', 'chalkpress', 'helpers');
  }

  /**
    * theme_helpers_path
    *
    * @return String path to the theme helpers directory
    */
  public static function theme_helpers_path() {
    return self::join_paths(get_stylesheet_directory(), 'library', 'helpers');
  }

  /**
    * theme_templates_path
    *
    * @return String path to the theme templates directory
    */
  public static function theme_templates_path() {
    return self::join_paths(get_stylesheet_directory(), 'library', 'templates');
  }

  /**
    * theme_menus_path
    *
    * @return String path to the theme menus directory
    */
  public static function theme_menus_path() {
    return self::join_paths(get_stylesheet_directory(), 'library', 'menus');
  }

  /**
    * theme_post_types_path
    *
    * @return String path to the post types directory
    */
  public static function theme_post_types_path() {
    return self::join_paths(get_stylesheet_directory(), 'library', 'post_types');
  }

  /**
    * vendor_path
    *
    * @return String path to the chalkpress vendor directory
    */
  public static function vendor_path() {
    return self::join_paths(dirname(__FILE__), 'vendor');
  }

  
  /**
    * abstract_components_path
    *
    * @return String path to the chalkpress components directory
    */
  public static function abstract_components_path() {
    return self::join_paths(dirname(__FILE__), 'components');
  }

  /**
    * chalkpress_require_features
    * 
    * Checks for directories and requires files if they are found
    *
    * @return Bool 
    */
  public static function chalkpress_require_features() {

    if( is_dir( self::theme_helpers_path() ) ) 
      self::require_theme_helpers(); 

    if( is_dir( self::theme_menus_path() ) )
      self::require_theme_menus();

    if( is_dir( self::theme_post_types_path() ) )
      self::require_theme_post_types();
  }
  
  /**
   * __callStatic magic method 
   *
   * This method is executed if a non-existant static method is called
   * on the ChalkPress class, checks to see if a helper method exists with the
   * same name and executes it
   *
   *
   * @param $name String name of the method that was requested
   * @param $arguments Array arguments passed into the method call
   *
   * @return nada
   */
  public static function __callStatic($name, $arguments) {
    foreach(self::$helpers as $helper) {
      $methods = get_class_methods($helper);
      if( in_array($name, $methods) ) {
        return call_user_func_array(array($helper, $name), $arguments);
      }
    }
  }

}
