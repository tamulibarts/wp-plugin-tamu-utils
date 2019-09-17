<?php

namespace TAMU_Utils\Infrastructure;

class CAS {

    protected $cas_server;
    protected $cas_enabled = false;

    public static function instance() {
        new self();
    }

    public function __construct() {
        if( defined('CAS_SERVER') && !empty(CAS_SERVER) ) {
            $this->cas_enabled = true;
            $this->cas_server  = CAS_SERVER;
        }
        if($this->cas_enabled) {
            add_action('wp_authenticate', array($this, 'authenticate'), 10, 2);
            add_filter('show_password_fields', '__return_false');
            add_action('wp_logout', array($this, 'logout'));
        }
    }

    public function authenticate( &$username, &$password ) {
        $cas_response = $this->get_cas_ticket();

        if ($cas_response !== false) {
            $cas_user_id = $cas_response;
        }

        $wp_user = get_user_by( 'login', $cas_user_id );

        if ( !$wp_user ) {
            $email = get_bloginfo( 'admin_email' );
            $url = get_bloginfo( 'url' );
            $message = sprintf( "Your NetID is valid, but you have not been granted access to this site. Please contact the <a href=\"mailto:%s\">site administrator</a>.", $email );
            $title = 'No Account on This Site';
            $args = array( 'back_link' => $url, 'response' => 403 );
            wp_die( $message, $title, $args );
        } else {
            $wp_username = $wp_user->user_login;
            wp_set_auth_cookie( $wp_user->ID );
            wp_redirect( admin_url() );
            die();
        }

    }

    public function get_cas_ticket($requested_url_option = false){
        if ($requested_url_option == false) {
            $requested_url = get_bloginfo('wpurl')."/wp-login.php";
        } else {
            $requested_url = $requested_url_option;
        }

        $cas_login = "https://".$this->cas_server."/cas/login?service=".urlencode($requested_url);

        if ( !isset($_GET['ticket']) || (empty($_GET['ticket'])) ) {
            wp_redirect( $cas_login );
            exit();
        }

        $cas_ticket = $_GET['ticket'];
        $cas_validate_url = "https://".$this->cas_server."/cas/validate?ticket=".$cas_ticket."&service=".urlencode($requested_url);
       
        $lines = file( $cas_validate_url );
        $cas_response = rtrim( $lines[0] );

        if ( $cas_response != "no" ) {
            $cas_user_id = rtrim( $lines[1] );
            return $cas_user_id;
        } else {
            wp_redirect( $requested_url );
            exit();
        }

        return false;
    }

    public function logout(){
        wp_destroy_current_session();
        wp_clear_auth_cookie();

        $message = sprintf("
            <p>
                You have been logged out of <a href=\"%s\">%s</a>. You can login again <a href=\"%s\">here</a>.
            </p>
            <p>
                Would you also like to <a href=\"%s\">log out of TAMU CAS</a>?
            </p>",
            get_bloginfo('url'),
            get_bloginfo('name'),
            wp_login_url(), 
            'https://'.$this->cas_server.'/cas/logout');
        wp_die( $message, "Logged Out", array( 'response' => 200 ) );
        exit();
    }
}