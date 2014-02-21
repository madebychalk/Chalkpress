<?php

require_once dirname(__FILE__) . '/utilities/chalk_utils.php';

class ChalkPress extends ChalkUtils {
  
  /**
    * @property $menus Array holds the names of registered wordpress menus
    *
    */
  private static $ChalkpressMenu       = array();
  private static $ChalkpressPostType   = array();
  private static $ChalkpressHelper     = array();
  private static $ChalkpressMetabox    = array();

  /**
    * add_notice
    *
    * kicks off ChalkPress functionality
    *
    * @param $cb String|Array name of function or array of class and method to execute after initialization.
    *
    */
  public static function initialize($cb = null) {
      self::require_abstract_components();
      self::chalkpress_require_features();
      self::require_cmb();

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

    self::get_chalkpress_component($class, $type);
  }

  /**
   * get_chalkpress_component
   *
   * optionally create and return componenet
   */

  public static function get_chalkpress_component($class, $type = null) {
    $component_name = strtolower($class);
    $type = ($type) ? $type : get_parent_class($class);

    if( !isset( self::${$type}[$component_name] ) ) {
      self::${$type}[$component_name] = new $class;
      self::${$type}[$component_name]->init();
    }

    return self::${$type}[$component_name];
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

  /**
    * require_helpers
    *
    * loads all the helpers for chalkpress
    * will set up global functions
    */
  public static function require_helpers() {
    self::require_once_dir( self::helpers_path(), array(__CLASS__, 'require_chalkpress_component') );
  }

  /**
    * require_theme_post_types
    *
    * loads all the php files located in the theme post type directory
    */
  public static function require_theme_post_types() {
    self::require_once_dir( self::theme_post_types_path(), array(__CLASS__, 'require_chalkpress_component') );
  }

  /**
    * require_theme_metaboxes
    *
    * loads all the php files located in the theme metabox directory
    */
  public static function require_theme_metaboxes() {
    self::require_once_dir( self::theme_metaboxes_path(), array(__CLASS__, 'require_chalkpress_component') );
  }

  /**
    * require_theme_menus
    *
    * loads all the php files located in the theme menu directory
    */
  public static function require_theme_menus() {
    self::require_once_dir( self::theme_menus_path(), array(__CLASS__, 'require_chalkpress_component') );
  }

  /**
    * display_theme_menu
    *
    * used on the front end to display a menu
    *
    */ 
  public static function display_theme_menu($menu_name) {
    wp_nav_menu( self::get_chalkpress_component($menu_name)->config );
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
    * theme_metabox_path
    *
    * @return String path to the metabox directory
    */
  public static function theme_metaboxes_path() {
    return self::join_paths(get_stylesheet_directory(), 'library', 'metaboxes');
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
    self::require_helpers();

    if( is_dir( self::theme_helpers_path() ) ) 
      self::require_theme_helpers(); 

    if( is_dir( self::theme_menus_path() ) )
      self::require_theme_menus();

    if( is_dir( self::theme_post_types_path() ) )
      self::require_theme_post_types();

    if( is_dir( self::theme_metaboxes_path() ) )
      self::require_theme_metaboxes();
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
    foreach(self::$ChalkpressHelper as $helper) {
      $methods = get_class_methods($helper);
      if( in_array($name, $methods) ) {
        return call_user_func_array(array($helper, $name), $arguments);
      }
    }
  }

}
