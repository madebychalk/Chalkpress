<?php

abstract class ChalkpressMenu {
  public $config      = null;

  protected static $defaults = array(
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

  public function init() {
    $this->config = wp_parse_args($this->configuration(), self::$defaults); // Chalkpress::extend( $this->defaults, $this->configuration() );
    $this->config['fallback_cb'] = array($this, 'fallback');
      
    register_nav_menu( get_called_class(), Chalkpress::humanize( get_called_class() ) );
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
