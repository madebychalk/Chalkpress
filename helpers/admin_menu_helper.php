<?php

class AdminMenuHelper {

  private function get_slug($section) {
    return $section[2];
  }

  private function get_title($section) {
    return $section[0];
  }

  private function set_title(&$section, $title) {
    $section[0] = $title;
  }

  private function get_admin_menu_item_index($slug, $item) {
    global $submenu;

    foreach( $submenu[$slug] as $index => $item_props ) {
      if( $this->get_title($item_props) == $item ) {
        return $index;
      }
    }
  }

  private function get_admin_menu_section_index($section) {
    global $menu;

    $sec = null;

    foreach($menu as $index => $section_props) {
      if( preg_match("/^" . $section . "/", $section_props[0]) ) {
        return $index;
      }
    }
  }

  public function rename_admin_menu_section($section, $title) {
    $sec = &$this->get_admin_menu_section($section);
    $this->set_title($sec, $title);
  }

  public function rename_admin_menu_item($section, $item, $title) {
    $item = &$this->get_admin_menu_item($section, $item);
    $this->set_title($item, $title);
  }

  public function remove_admin_menu_item($section, $item) {
    global $submenu;

    $sec = $this->get_admin_menu_section($section);
    $slug = $this->get_slug($sec);
    $index = $this->get_admin_menu_item_index($slug, $item);
    
    unset($submenu[$slug][$index]);
  }

  public function remove_admin_menu_section($section) {
    global $menu;

    $index = $this->get_admin_menu_section_index($section);
    unset($menu[$index]);
  }

  public function &get_admin_menu_section($section) {
    global $menu;

    $index = $this->get_admin_menu_section_index($section);
    return $menu[$index];
  }

  public function &get_admin_menu_item($section, $item) {
    global $submenu;

    $sec =  $this->get_admin_menu_section($section);
    $slug = $this->get_slug($sec);
    $index = $this->get_admin_menu_item_index($slug, $item); 

    return $submenu[$slug][$index];
  }
}


