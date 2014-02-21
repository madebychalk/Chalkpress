<?php

class MetaboxHelper extends ChalkpressHelper {
  public function get_meta($class, $id, $pid = null, $single = true) {
    $pid = ($pid) ? $pid : get_the_ID();
    $class_name = strtolower( Chalkpress::underscore($class) ) . '_';

    return get_post_meta($pid, ChalkpressMetabox::get_prefix() . $class_name . $id, $single);
  }

}
