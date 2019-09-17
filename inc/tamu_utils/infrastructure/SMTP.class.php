<?php

namespace TAMU_Utils\Infrastructure;

class SMTP {
  public static function instance() {
    new self();
  }

  public function __construct() {
    add_action( 'phpmailer_init', array( $this, 'send_smtp_email' ) );
  }

  // SMTP Authentication
  public function send_smtp_email( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = SMTP_HOST;
    $phpmailer->Port       = SMTP_PORT;
    $phpmailer->From       = SMTP_FROM;
    $phpmailer->FromName   = SMTP_NAME;
  }
}

?>