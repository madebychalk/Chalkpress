<?php

abstract class ChalkpressMetabox {
  public $config = null;
  public $class_name = null;

  protected static $defaults = array(
    'id'         => null,
    'title'      => null,
    'pages'      => array( 'page', ),
    'context'    => 'normal',
    'priority'   => 'high',
    'show_names' => true,
    'cmb_styles' => false,
    'save_as'    => null,
    'notify'     => null,
    'subject'    => '[Website] New Submission',
    'redirect'   => null,
    'fields'     => array(),
    'wrapper'    => '<div class="control"><label for="%s">%s%s</label></div>'
  );

  protected static $prefix = "chlkprss_";

  public function init() {
    $this->config = wp_parse_args($this->configuration(), self::$defaults);
    $this->class_name = strtolower( Chalkpress::underscore( get_called_class() ) );

    $this->config['id'] = uniqid( self::$prefix );
    $this->config['title'] = Chalkpress::humanize( get_called_class() );
    
    add_filter( 'cmb_meta_boxes', array( $this, 'add_metabox'  ) );

    add_action( 'wp_ajax_' . self::$prefix . $this->class_name . '_submit',        array( $this, 'save_metabox' ) );
    add_action( 'wp_ajax_nopriv_' . self::$prefix . $this->class_name . '_submit', array( $this, 'save_metabox' ) );
  }

  public function add_metabox($mb) {
    foreach($this->config['fields'] as &$field) {
      $field['id'] = self::$prefix . $this->class_name . '_' . $field['id'];
    }

    $mb[] = $this->config;
    return $mb;
  }

  public function configuration(){
    return array();
  }

  public function save_metabox() {
    if ( !wp_verify_nonce( $_REQUEST[self::$prefix . $this->class_name . '_nonce'], self::$prefix . $this->class_name . '_submission' ) ) {
      exit("Oops! Something went wrong. Please complete the form and try again.");
    }

    if( ! is_null( $this->config['save_as'] ) ) {
      $vals = $this->insert_post();

      if( ! is_null( $this->config['notify'] ) )
        $this->notify_user($vals);
    }

    if( ! is_null( $this->config['redirect'] ) )
      header("Location: " . Chalkpress::get_page_permalink( $this->config['redirect'] ) );
    else
      header("Location: " . home_url() );
    exit();
  }

  private function notify_user($vals = null) {
    $to = $this->config['notify'];

    if( is_array( $to ) )
      $to = implode(", ", $to);

    $message = 'A new ' . $this->config['title'] . ' has been submitted.'
      . "\n"
      . "You should log in and check it out.";


    if( is_array($vals) ) {
      $message .= "\n\n";

      foreach( $vals as $k => $v ) {
        $message .= $k . ': ' . $v . "\n";  
      };
       
    }
    $mail = wp_mail($to, $this->config['subject'], $message);

    if(!$mail) {
      echo 'There was a problem sending your message. Please try again.';
      exit();
    }
  }

  private function insert_post() {
    $new_post = array(
      'post_title'  => 'Submission' . ' ' . date("Y-m-d H:i:s"),
      'post_status' => 'private',
      'post_type'   => $this->config['save_as']
    );

    $tmp = array();
    $pid = wp_insert_post($new_post);
    foreach( $this->config['fields'] as $field ) {
      $tmp[$field['name']] = $_REQUEST[$field['id']];
      add_post_meta($pid, $field['id'], $_REQUEST[$field['id']], true);
    }

    return $tmp;
  }

  public static function get_prefix() {
    return self::$prefix;
  }

  public function field($id) {
    $types = cmb_Meta_Box_types::get();
    $config = wp_list_filter( $this->config['fields'], array('id' => $id) );
    $config = array_shift( $config );

    cmb_Meta_box::$field &= $config;
    $config['repeatable'] = isset( $config['repeatable'] ) && $config['repeatable'];

    ob_start();

      call_user_func( array( $types, $config['type'] ), $config, '' );
      $field = ob_get_contents();

    ob_end_clean();

    return sprintf( $this->config['wrapper'], $id, $config['name'], $field );
  }

  public function fields() {
    $fields = array();
    foreach( $this->config['fields'] as $field ) {
      $fields[] = $this->field( $field['id'] );
    }

    return $fields;
  }

  public function insertion_form() {
    $form = sprintf( $this->form_tag(), implode("\n", $this->fields()) );
  
    echo $form;
  }

  private function form_tag() {
    $form = sprintf('<form method="post" action="%s">', admin_url('admin-ajax.php') );
    $form .= wp_nonce_field(self::$prefix . $this->class_name . '_submission', self::$prefix . $this->class_name . '_nonce', true, false);
    $form .= Chalkpress::hidden_field_tag(array('name' => 'action', 'value' => self::$prefix . $this->class_name . '_submit' ), false );
    $form .= '%s';
    $form .= Chalkpress::submit_button_tag(array('class' => 'button button-tertiary'), "submit", false );
    $form .= '</form>';

    return $form;
  }

}
