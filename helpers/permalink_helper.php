<?php

class PermalinkHelper extends ChalkpressHelper {

  public function get_slug($pid) {
    $p = get_post($pid);
    return $p->post_name;
  }

  public function get_the_slug($echo = false) {
    global $post;

    if(!$echo)
      return $post->post_name;

    echo $post->post_name;
  }

  public function the_slug() {
    get_the_slug(true);
  }

  public function get_page_id_by_slug($page_slug) {
    $page = get_page_by_path($page_slug);
    if ($page)
      return $page->ID;

    return null;
  }

  public function get_page_permalink($page_slug) {
    $id = Chalkpress::get_page_id_by_slug($page_slug);

    if( !is_null($id) ) 
      return get_permalink( $id );
  }
}
