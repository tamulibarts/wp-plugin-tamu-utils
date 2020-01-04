<?php
namespace TAMU_Utils\Admin;
class TamuPerson {

  public $netid = '';
  public $first_name = '';
  public $last_name = '';
  public $email = '';
  public $display_name = '';
  public $is_valid = false;

  public function __construct($netid) {
    $this->netid = $netid;
  }

  public function validate() {
    if( isset($this->netid) && defined('MQS_HOST') && defined('MQS_IDENTIFIER') && defined('MQS_SHARED_SECRET') ) {
      $uri = "https://" . MQS_HOST . "/rest/directory/netid/" . $this->netid . "/";
      $parsed_uri = parse_url($uri);

      $date = gmstrftime("%a, %d %b %Y %T") . " GMT";

      # Calculate signature and authorization header
      $auth_string = $parsed_uri['path'] . "\n" . $date . "\n" . MQS_IDENTIFIER;

      $signature = base64_encode( hash_hmac('sha256', $auth_string, MQS_SHARED_SECRET, true) );

      $opts = array(
        'http'=>array(
          'method'=>"GET",
          'header'=>"Date: $date\r\n" .
                    "Authorization: TAM " . MQS_IDENTIFIER . ":" . $signature . "\r\n"
        )
      );

      $context = stream_context_create($opts);

      $response = file_get_contents($uri, false, $context);
      if( $response ) {
        $response_object = json_decode($response);
        // echo "<pre>"; print_r($response_object);
        if( !empty((array)$response_object) ) {
          if( isset( $response_object->givenName[0] ) ) $this->first_name = $response_object->givenName[0];
          if( isset( $response_object->sn[0] ) )        $this->last_name  = $response_object->sn[0];
          if( isset( $response_object->mail[0] ) )      $this->email = $response_object->mail[0];
          if( isset( $response_object->cn[0] ) )      $this->display_name = $response_object->cn[0];
          else $this->display_name = $this->first_name . " " . $this->last_name;
          return true;
        }
        else return false;
      } else {
        return false;
      }

    } else {
      if( !isset($this->netid) ) wp_die('Cannot validate a Tamu person without a netid set!');
      if( !defined('MQS_HOST') || !defined('MQS_IDENTIFIER') || !defined('MQS_SHARED_SECRET') ) wp_die('Please check your MQS settings in wp-config.php.');
    }

  }

}

?>
