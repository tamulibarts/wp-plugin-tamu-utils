<?php
/*
Plugin Name: TAMU CLA WordPress Utilities
Plugin URI: https://github.com/tamulibarts/wordpress-tamu-utilities
Version: 0.1
Description: Plugin that contains various utlities for TAMU CLA WordPress Installations
Author: Joseph Rafferty <jrafferty@tamu.edu>
*/


// Load our utilities

// User utility replaces the built-in user add screens with one customized for NetIDs
add_action(
    'plugins_loaded',
    ['TAMU_Utils\Admin\Users', 'instance']
);

// SMTP utility configures PHPMailer for our environment's SMTP server
add_action(
    'plugins_loaded',
    ['TAMU_Utils\Infrastructure\SMTP', 'instance']
);

// Enables CAS as the login and logout methods
add_action(
    'plugins_loaded',
    ['TAMU_Utils\Infrastructure\CAS', 'instance']
);

// Department Admin role adds customizer access to editor
register_activation_hook( __FILE__, ['TAMU_Utils\Roles\DepartmentAdmin', 'instance'] );


// Custom autoloader
spl_autoload_register(function ($class) {
    if(!class_exists($class))
       include_once __DIR__ . "/inc/" . str_replace('\\', '/', $class) . ".class.php";
});


?>
