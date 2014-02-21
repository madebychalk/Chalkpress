<?php

class AssetTagHelper extends ChalkpressHelper {
  const BOOLEAN_ATTRIBUTES = "disabled readonly multiple checked autobuffer autoplay controls loop selected hidden scoped async defer reversed ismap seemless muted required autofocus novalidate formnovalidate open pubdate itemscope";

  public function __construct() {
    $this->boolean_attributes = explode(" ", self::BOOLEAN_ATTRIBUTES); 
  }

  public function library_url($path) {
    return ChalkPress::join_paths( get_stylesheet_directory_uri(), "library", $path );
  }

  public function library_path($path) {
    return ChalkPress::join_paths( get_stylesheet_directory(), "library", $path );
  }

  public function image_url($name) {
    return $this->library_url( array('images', $name) );
  }

  public function image_path($name) {
    return $this->library_path( array('images', $name) );
  }

  public function javascript_url($name, $vendor = false) {
    $dir = $vendor ? "vendor" : "js";
    return $this->library_url("$dir/$name");
  }

  public function tag_option($key, $val) {
    if( is_array($val) )
      $val = implode(" ", $val);

    return sprintf('%s="%s"', $key, $val);
  }

  public function tag_options($options = null) {
    $html_content = array();

    if( is_null($options) )
      return ' ';

    if( is_array($options) ) {
      foreach ($options as $key => $val) {
        if( in_array($key, $this->boolean_attributes) )
          $html_content[] = $key;
        else
          $html_content[] = $this->tag_option($key, $val);
      }
      return ' ' . join(" ", $html_content);
    }

    return ' ' . $options;
  }

  public function tag($name, $options, $open = true, $echo = false) {
    $tag = sprintf("<%s%s%s>", $name, $this->tag_options($options), $open ? "" : "/"); 

    if( $echo )
      echo $tag;

    return $tag;
  }

  public function content_tag($name, $options_or_content = null, $content = null) {
    if( is_null($content) ) {
      $content = $options_or_content;
      $options = null;
    } else {
      $options = $options_or_content;
    }

    return sprintf('<%s%s>%s</%s>', $name, $this->tag_options($options), htmlentities($content), $name);
  }

  public function hidden_field_tag($attrs = null, $echo = true) {
    $options = array('type' => 'hidden');

    if( is_array($attrs) )
      $options = array_merge($options, $attrs);

    $tag = $this->tag('input', $options);

    if($echo) echo $tag;

    return $tag;
  }

  public function submit_button_tag($attrs = null, $content = "submit", $echo = true) {
    $options = array('type' => 'submit');

    if( is_array($attrs) )
      $options = array_merge($options, $attrs);

    $tag = $this->content_tag('button', $options, $content);

    if($echo) echo $tag;

    return $tag;
  }
  public function image_tag($src, $attrs = null, $echo = true) {
    $options = array("src" => $this->image_url($src));

    if( is_array($attrs) ) 
      $options = array_merge($options , $attrs);

    $tag = $this->content_tag('img', $options);

    if($echo) echo $tag;

    return $tag;
  }

  public function javascript_tag($src, $attrs = null, $echo = true, $vendor = false) {
    $options = array("src" => $this->javascript_url($src, $vendor));

    if( is_array($attrs) )
      $options = array_merge($options, $attrs);

    $tag = $this->content_tag('script', $options, "");

    if($echo) echo $tag;

    return $tag;
  }

  public function vendor_javascript_tag($src, $attrs = null, $echo = true) {
    return $this->javascript_tag($src, $attrs, $echo, true);
  }

  public function link_tag($rel, $href, $attrs = null, $echo = true) {
    $options = array( "rel" => $rel, "href" => $this->library_url($href) );

    if( is_array($attrs) )
      $options = array_merge($options, $attrs);

    $tag = $this->tag('link', $options);

    if($echo) echo $tag;

    return $tag;
  }

  public function touch_icon_tags($hrefs) {
    if( is_string($hrefs) )
      return $this->link_tag('apple-touch-icon', "images/$hrefs");

    if( is_array($hrefs) ) {
      $links = array();

      foreach($hrefs as $size => $href) {
        $links[] = $this->link_tag( 'apple-touch-icon', "images/$href", array('sizes' => $size) );
        echo "\n";
      }

      return $links;
    }
  }
}
