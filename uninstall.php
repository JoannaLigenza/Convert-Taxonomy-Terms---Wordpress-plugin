<?php
/**
 * Trigger this file when user uninstall plugin
 * 
 * @package jlconverttax
 *
*/

// security check - prevent access from outside of wordpress
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

function jlconverttax_delete_settings() {
    delete_option( 'jlconverttax-save-hierarchy' );
    delete_option( 'jlconverttax-from-taxonomy' );
    delete_option( 'jlconverttax-to-taxonomy' );
    delete_option( 'jlconverttax-checked-categories' );
}

jlconverttax_delete_settings();



