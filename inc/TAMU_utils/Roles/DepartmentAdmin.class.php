<?php

namespace TAMU_Utils\Roles;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class DepartmentAdmin {
    public static function instance() {
        new self();
    }

    public function __construct() {
        $this->setDeptAdminRole();
    }

    public function setDeptAdminRole() {
        $appendable_caps = Array(
          "edit_theme_options"
        );
    
        $all_roles = get_editable_roles();
        $set_caps = false;
    
        $editor_caps = array_keys( $all_roles["editor"]["capabilities"] );
        $desired_caps = array_merge( ( $editor_caps ?: Array() ), $appendable_caps);
    
        // Check if role is installed in site
        if( array_key_exists( "tamudeptadmin", $all_roles ) ) {
          // Verify that this role has, at minimum, the same as an editor.
          $diffs = array_diff( $all_roles["tamudeptadmin"]["capabilities"], $desired_caps );
          if( count($diffs) > 0 ) {
            $set_caps = true;
          }
        } else {
          add_role( "tamudeptadmin", "Department Administrator", $desired_caps );
        }
    }
}

?>