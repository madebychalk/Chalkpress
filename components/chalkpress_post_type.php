<?php

abstract class ChalkpressPostType {
  public    $config          = null;
  private   $labels          = null;
  private   $post_name       = null;
  protected $singular        = null;
  protected $plural          = null;
  protected static $defaults = array(
    'description'         => '',
    'public'              => false,
    'menu_position'       => 10,
    'show_ui'             => true,
    'publicly_queryable'  => false,
    'show_in_nav_menus'   => false,
    'show_in_menu'        => true,
    'show_in_admin_bar'   => false,
    'hierarchical'        => false,
    'query_var'           => false,
    'rewrite'             => false,
    'exclude_from_search' => true,
    'supports'            => array( 'title' ),
    'has_archive'         => false
  );

  public function init() {
    $this->post_name = Chalkpress::humanize( get_called_class() );

    $this->config = wp_parse_args($this->configuration(), self::$defaults);
      
    if( is_null($this->singular) ) $this->singular = $this->post_name;
    if( is_null($this->plural)   ) $this->plural   = Chalkpress::pluralize($this->post_name);

    $this->config['labels'] = $this->assign_labels();

    register_post_type($this->post_name, $this->config);
  }

  private function assign_labels() {
    return array(
      'name'               => __( $this->singular ),
      'singular_name'      => __( $this->singular ),
      'add_new'            => __( 'Add New' ),
      'add_new_item'       => __( "Add $this->singular" ),
      'edit'               => __( 'Edit' ),
      'edit_item'          => __( "Edit $this->singular" ),
      'new_item'           => __( "New $this->singular" ),
      'all_items'          => __( "All $this->plural" ),
      'view'               => __( "View $this->plural" ),
      'view_item'          => __( "View $this->singular" ),
      'search_items'       => __( "Search $this->plural" ),
      'not_found'          => __( "No $this->plural Found" ),
      'not_found_in_trash' => __( "No $this->plural Found in the Trash" ), 
      'parent'             => __( "Parent $this->singular" ),
      'parent_item_colon'  => ':',
      'menu_name'          => __( "$this->plural" )
    );
  }

  public function configuration(){
    return array();
  }
}
