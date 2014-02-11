<?php

abstract class ChalkpressMenu {
  public $config      = null;
  public $menu_name   = null;
  protected $defaults = array(
    'theme_location'  => '',
    'menu'            => '',
    'container'       => false,
    'container_class' => '',
    'container_id'    => '',
    'menu_class'      => 'vertical-menu',
    'menu_id'         => '',
    'echo'            => true,
    'fallback_cb'     => null,
    'before'          => '',
    'after'           => '',
    'link_before'     => '',
    'link_after'      => '',
    'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
    'depth'           => 0,
    'walker'          => ''
  );

  public function __construct() {
    $this->menu_name = Chalkpress::humanize( get_called_class() );

    $values = is_array( $this->configuration() ) ? $this->configuration() : array();
    $this->config = Chalkpress::extend( $this->defaults, $values );

    if( !isset( $this->config['fallback_cb'] ) )
      $this->config['fallback_cb'] = array($this, 'fallback');
      
    register_nav_menu( get_called_class(), $this->menu_name );
  }

  public function fallback() {
    wp_page_menu(
      array(
        'show_home' => true,
        'menu_class' => 'vertical-menu',
        'include'     => '',
        'exclude'     => '',
        'echo'        => true,
        'link_before' => '',
        'link_after' => ''
      )
    ); 
  }

  public function configuration() {
    return array();
  }
}
