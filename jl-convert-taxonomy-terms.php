<?php
/**
 * Plugin Name: JL Convert Taxonomy Terms
 * Description: JL Convert Taxonomy Terms plugin allows to move or copy taxonomy terms and its children to another taxonomy with saving taxonomy hierarchy
 * Version: 1.0.
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: JL
 * Text Domain: jlconverttax
 * Domain Path: /languages
 * License: GPL2
 * 
 * JL Convert Taxonomy Terms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *   
 * JL Convert Taxonomy Terms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with JL Convert Taxonomy Terms. If not, see {URI to Plugin License}.
 */


defined( 'ABSPATH' ) or die( 'hey, you don\'t have an access to read this site' );



/*****************************************
 * Adding new page to admin Tools menu
 *****************************************/

add_action( 'admin_menu', 'jlconverttax_add_new_page' );
function jlconverttax_add_new_page() {
    add_submenu_page(
        'tools.php',                                  // $parent_slug
        'Convert Taxonomy Terms',                     // $page_title
        'Convert Taxonomy Terms',                     // $menu_title
        'manage_options',                             // $capability
        'convert-taxonomy-terms',                     // $menu_slug
        'jlconverttax_page_html_content'              // $function
    );
}





