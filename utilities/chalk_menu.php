<?php

class ChalkMenu {
  public $menu = array();

  public function set_menu_params() {
    $this->menu['menu'] = get_class($this);
    $this->menu['fallback_cb'] = array($this, 'fallback');
  }

  public function fallback() {
    wp_page_menu(
      array(
        'show_home' => true,
        'menu_class' => 'nav top-nav clearfix',      // adding custom nav class
        'include'     => '',
        'exclude'     => '',
        'echo'        => true,
        'link_before' => '',                            // before each link
        'link_after' => ''                             // after each link
      )
    ); 
  }
}
