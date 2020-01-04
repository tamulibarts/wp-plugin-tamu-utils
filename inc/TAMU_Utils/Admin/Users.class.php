<?php

namespace TAMU_Utils\Admin;

class Users {
    public static function instance() {
      new self();
    }

    public function __construct() {
      add_action( 'admin_menu',     array( $this, 'enqueue_custom_adduser_form_slug' ) );
      add_action( 'admin_footer',   array( $this, 'redirect_to_custom_adduser_form' ) );
    }
  
    public function redirect_to_custom_adduser_form() {
        ?>
          <script type="text/javascript">jQuery('a[href*="user-new.php"]').attr("href", "<?php echo get_admin_url( get_current_blog_id(), "admin.php?page=users-add_tamu" ); ?>");</script>
        <?php
      }
    
    
    
      public function enqueue_custom_adduser_form_slug() {
        add_submenu_page (
          null,
          __('Add New User'),
          null,
          'create_users',
          'users-add_tamu',
          array($this, 'custom_adduser_form_content')
        );
      }
    
    
    
      public function custom_adduser_form_content() {
        $errors = new \WP_Error();
        $messages = array();
    
        if( !isset($_POST['action']) || ( 'adduser' != $_POST['action'] ) || !check_admin_referer( 'add_netid_users' ) ) {
          // $errors->add("adduser", "Sorry, an unknown error occured", null);
          goto end;
        }
    
        if( !isset($_POST['role']) || !$GLOBALS['wp_roles']->is_role( $_POST['role'] ) ) {
          $errors->add("adduser", "Please select a valid role", null);
          goto end;
        }
    
        if( !isset($_POST['netids']) ) {
          $errors->add("adduser", "Please enter at least one NetID", null);
          goto end;
        }
    
        $netids = $_POST['netids'];
        $netids = str_replace(',', ' ', $netids); // allow delim by comma
        $netids = str_replace(';', ' ', $netids); // allow demin by semicolon
        $netids = preg_replace('/\s{2,}/', ' ', $netids); // consolidate multiple spaces to avoid an empty value
        if( strpos($netids, ' ') > 0 ) {
          $netids = explode(' ', $netids);
        } else {
          $netids = array($netids);
        }
    
        foreach($netids as $netid) {
          $person = new TamuPerson($netid);
          if( !($person->validate()) ) {
            $errors->add("adduser", "The NetID \"$netid\" is invalid", $netid);
            continue;
          }
            // create or add the user
          if( username_exists( $netid ) ) {
            $errors->add("adduser", "The NetID \"$netid\" already exists on this site", $netid);
            continue;
          }
          $userdata = array(
            'user_login'   => $netid,
            'user_email'   => $person->email,
            'first_name'   => $person->first_name,
            'last_name'    => $person->last_name,
            'display_name' => $person->display_name,
            'user_pass'    => wp_generate_password( ),
            'role'         => $_POST['role']
          );
    
          $userID = wp_insert_user( $userdata );
          if( is_wp_error( $userID ) ) {
            $errors->add($userID->get_error_code(), "<b>$person->email</b>: " . $userID->get_error_message(), null);
            continue;
          }
    
          if( is_multisite() ) {
            $e = add_user_to_blog( get_current_blog_id(), $userID, $_POST['role'] );
            if( is_wp_error( $e ) ) {
              $errors->add($e->get_error_code(), "<b>$person->email</b>: " . $e->get_error_message(), null);
              continue;
            }
          }
    
          $messages[] = $person->display_name . " ($netid) successfully added with a role of " . $_POST['role'];
          wp_mail( $person->email, "You've been added to: " . get_bloginfo(), "Howdy $person->display_name,\r\n\r\nYou've been given access to the website " . get_bloginfo() . " (" . get_bloginfo('url') . "). You may log in and start working at:\r\n\r\n" . get_bloginfo('wpurl') . "/wp-admin/\r\n\r\n" );
        }
        end:
        ?>
        <div class="wrap">
        <h1 id="add-new-user">Add New Texas A&amp;M Users</h1>
    
        <?php if ( isset($errors) && is_wp_error( $errors ) && !empty($errors->get_error_codes()) ) : ?>
          <div class="error">
            <ul>
            <?php
              foreach ( $errors->get_error_messages() as $err )
                echo "<li>$err</li>\n";
            ?>
            </ul>
          </div>
        <?php endif;
    
          if ( ! empty( $messages ) ) {
            foreach ( $messages as $msg )
              echo '<div id="message" class="updated notice is-dismissible"><p>' . $msg . '</p></div>';
          }
    
          echo '<h2 id="add-user">' . __( 'Add Users by NetID' ) . '</h2>';
    
          echo '<p>' . __( 'Enter one or more NetIDs of the users you want to add to this site. These users will be sent a welcome email when successfully added.' ) . '</p>';
            $label = __('NetID(s)');
            $type  = 'text';
    
          ?>
          <form method="post" name="adduser" id="adduser" class="validate" novalidate="novalidate"<?php
    
            do_action( 'user_new_form_tag' );
          ?>>
            <input name="action" type="hidden" value="adduser" />
    
            <table class="form-table">
              <tr class="form-field form-required">
                <th scope="row"><label for="adduser-netids"><?php echo $label; ?></label></th>
                <td><input name="netids" type="<?php echo $type; ?>" id="adduser-netids" class="wp-suggest-user" value="" /></td>
              </tr>
              <tr class="form-field">
                <th scope="row"><label for="adduser-role"><?php _e('Role'); ?></label></th>
                <td><select name="role" id="adduser-role">
                  <?php wp_dropdown_roles( get_option('default_role') ); ?>
                  </select>
                </td>
              </tr>
            </table>
            <?php
            wp_nonce_field( 'add_netid_users' );
            do_action( 'user_new_form', 'add-existing-user' );
            submit_button( __( 'Add User(s)' ), 'primary', 'adduser', true, array( 'id' => 'addusersub' ) ); ?>
          </form>
        <?php
      }
}

?>