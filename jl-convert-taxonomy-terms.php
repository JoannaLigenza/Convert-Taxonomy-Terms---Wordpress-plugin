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


/*******************************************************
 * Adding settings and sections to page in admin menu
 *******************************************************/

add_action( 'admin_init', 'jlconverttax_add_new_settings' );
function jlconverttax_add_new_settings() {
    // register settings
    
    $configuration_settins_field_1_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'jlconverttax_sanitize_radio',
        'default' => 'yes'
    );
    $configuration_settins_field_2_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'jlconverttax_sanitize_radio',
        'default' => 'category'
    );
    $configuration_settins_field_3_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'jlconverttax_sanitize_radio',
        'default' => 'post_tag'
    );
    register_setting( 'jlconverttax_options', 'jlconverttax-save-hierarchy', $configuration_settins_field_1_arg);    // option group, option name, args
    register_setting( 'jlconverttax_options', 'jlconverttax-from-taxonomy', $configuration_settins_field_2_arg);
    register_setting( 'jlconverttax_options', 'jlconverttax-to-taxonomy', $configuration_settins_field_3_arg);       


    // adding sections
    add_settings_section( 'jlconverttax_configuration', 'Settings', null, 'jlconverttax-slug' );  // id (Slug-name to identify the section), title, callback, page slug 

    // adding fields for section
    add_settings_field( 'field-1-copy-move', 'Save hierarchy', 'jlconverttax_field_1_callback', 'jlconverttax-slug', 'jlconverttax_configuration' );
    add_settings_field( 'field-2-from-to', 'Choose taxonomy', 'jlconverttax_field_2_callback', 'jlconverttax-slug', 'jlconverttax_configuration' );
    
}


function jlconverttax_field_1_callback() {
    $isChecked = get_option( "jlconverttax-save-hierarchy", 'yes' );
    ?>
    <label for="copy"><strong>Yes</strong></label>
    <!-- input name must be the same as option name in register_setting -->
    <input type="radio" name="jlconverttax-save-hierarchy" value="yes" <?php echo esc_html( $isChecked ) === 'yes' ? "checked" : null ?> />
    <label for="move"><strong>No</strong></label>
    <input type="radio" name="jlconverttax-save-hierarchy" value="no" <?php echo esc_html( $isChecked ) === 'no' ? "checked" : null ?> />
    <?php
}

function jlconverttax_field_2_callback() {
    $taxonomies = jlconverttax_get_all_taxonomies();
    $selected_from = get_option( "jlconverttax-from-taxonomy", 'category' );
    $selected_to = get_option( "jlconverttax-to-taxonomy", 'post_tag' );
    ?>
    <!-- display taxonomies 'from' list -->
    <span><strong>From </strong></span>
    <select name="jlconverttax-from-taxonomy" id="jlconverttax-from-taxonomy">
        <?php foreach($taxonomies as $taxonomy) : 
            $exclude_array = ['nav_menu', 'link_category', 'post_format' ];
            if ( in_array( $taxonomy, $exclude_array ) ) {
                continue;
            } ?>
            <option value="<?php echo esc_attr( $taxonomy ) ?>" <?php echo $selected_from === $taxonomy ? 'selected' : null ?> ><?php echo esc_html( $taxonomy ) ?></option>
        <?php endforeach; ?>
    </select>
    <!-- display taxonomies 'to' list -->
    <span><strong>To </strong></span>
    <select name="jlconverttax-to-taxonomy" id="jlconverttax-to-taxonomy">
        <?php foreach($taxonomies as $taxonomy) : 
            $exclude_array = ['nav_menu', 'link_category', 'post_format' ];
            if ( in_array( $taxonomy, $exclude_array ) ) {
                continue;
            } ?>
            <option value="<?php echo esc_attr( $taxonomy ) ?>" <?php echo $selected_to === $taxonomy ? 'selected' : null ?> ><?php echo esc_html( $taxonomy ) ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}



// sanitize input
function jlconverttax_sanitize_radio( $input ) {
    if ( isset( $input ) ) {
        $input = sanitize_text_field( $input );
    }
    return $input;
}

// sanitize array
function jlconverttax_sanitize_categories( $input ) {
    if ( isset( $input ) ) {
        $input = array_map('intval', $input);
    }
    return $input;
}

function jlconverttax_get_all_taxonomies() {
    $taxonomies = get_taxonomies();
    return $taxonomies;
}


