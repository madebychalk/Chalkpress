<?php

class AssetTagHelper {
  function AssetTagHelper() {}

  public function library_url($path) {
    return ChalkPress::join_paths( get_stylesheet_directory_uri(), "library", $path );
  }

  public function image_url($name) {
    return $this->library_url( array('images', $name) );
  }

  public function javascript_url($name, $vendor = false) {
    $dir = $vendor ? "vendor/js" : "js";
    return $this->library_url("$dir/$name");
  }

  public function tag_options($options = null) {
    $html_content = array();

    if( is_null($options) ) {
      return false;
    } elseif( is_array($options) ) {
      foreach ($options as $key => $val) {
        $html_content[] = $key . "=" . "\"". $val . "\""; 
      }
    }

    return join(" ", $html_content);
  }

  public function content_tag($name, $options = null, $content = null) {
    $html_content = '<' . $name;

    if( $attrs = $this->tag_options($options) )
      $html_content .= " " . $attrs;

    if( is_null($content) )
      return $html_content .= "/>";

    $html_content .= ">";
    $html_content .= htmlentities($content);

    return $html_content .= "</" . $name . ">";
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

    $tag = $this->content_tag('link', $options);

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
