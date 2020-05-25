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


/*****************************
 * Adding styles and scripts
 *****************************/

add_action( 'admin_enqueue_scripts', 'jlconverttax_enqueue_scripts' );
function jlconverttax_enqueue_scripts() {
    // load only on 'convert-taxonomy-terms' page
    if ( get_current_screen()->id === 'tools_page_convert-taxonomy-terms' ) {
        // wp_enqueue_style( 'styles', plugins_url( 'styles.css', __FILE__ ) );
        wp_enqueue_script( 'jlconverttax_script', plugins_url( 'public/js/jlconverttax_script.js', __FILE__ ), array( 'jquery' ), true );
        wp_localize_script( 'jlconverttax_script', 'jlconverttax_script_ajax_object',
            array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            )
        );
    }
}


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
    $configuration_settins_field_4_arg = array(
        'type' => 'array',
        'sanitize_callback' => 'jlconverttax_sanitize_categories',
    );
    register_setting( 'jlconverttax_options', 'jlconverttax-save-hierarchy', $configuration_settins_field_1_arg);    // option group, option name, args
    register_setting( 'jlconverttax_options', 'jlconverttax-from-taxonomy', $configuration_settins_field_2_arg);
    register_setting( 'jlconverttax_options', 'jlconverttax-to-taxonomy', $configuration_settins_field_3_arg);    
    register_setting( 'jlconverttax_options', 'jlconverttax-checked-categories', $configuration_settins_field_4_arg);    


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


/*******************************
 * Adding content to menu page
 *******************************/

function jlconverttax_page_html_content() {
    if ( ! current_user_can( 'manage_options' ) ) {
        ?>
        <div style="font-size: 20px; margin-top: 20px"> <?php echo esc_html( "You don't have permission to manage this page" ); ?> </div>
        <?php
        return;
    }

    ?>
    <div class="wrap">
        <h2><?php echo esc_html( 'Privacy Policy & Cookie Info') ?></h2>
        <form action="options.php" method="post">
            <?php
            // outpus settings fields (without this there is error after clicking save settings button)
            settings_fields( 'jlconverttax_options' );                        // A settings group name. This should match the group name used in register_setting()
            // output setting sections and their fields
            do_settings_sections( 'jlconverttax-slug' );                      // The slug name of settings sections you want to output.

            $choosen_taxonomy = get_option( "jlconverttax-from-taxonomy", 'category' );
            ?>
            <br>
            <div>Choose terms that you want to move from 
                <span class="from-option"><?php echo esc_html( $choosen_taxonomy ) ?></span>
                to
                <span class="to-option"><?php echo get_option( "jlconverttax-to-taxonomy") ?></span>
            </div>
            <br>
            <div class="display-category">
                <?php jlconverttax_display_hierarchical_taxonomy( esc_html( $choosen_taxonomy ) ); ?>
            </div>
            <?php
            // output save settings button
            submit_button( 'Convert Terms', 'primary', 'submit', true );     // Button text, button type, button id, wrap, any other attribute           
            ?>
        </form>
    </div>
    <?php
}


/*****************************
 * Load categories by ajax
 *****************************/

add_action( 'wp_ajax_load_categories_by_ajax', 'jlconverttax_load_categories_by_ajax' );
function jlconverttax_load_categories_by_ajax() {
    $taxonomy_name = $_POST['category'];
    // display categories
    ob_start();
    if ( is_taxonomy_hierarchical( $taxonomy_name ) ) {
        jlconverttax_display_hierarchical_taxonomy( $taxonomy_name );
    } else {
        jlconverttax_display_non_hierarchical_taxonomy( $taxonomy_name );
    }
    $taxonomy = ob_get_clean();    // returns the content of the last opened buffer, in this case -> $func function (from get_template_part);
    echo json_encode( $taxonomy );
    die();
}


/*******************************
 * Display categories by ajax
 *******************************/

function jlconverttax_display_hierarchical_taxonomy( $taxonomy_name ) {
    echo '<h3>'.$taxonomy_name.'</h3>';
    jlconverttax_display_all_categories( 0, $taxonomy_name );
}

function jlconverttax_get_category( $parent = 0, $taxonomy_name  ) {
    $categories = get_terms( array(
        'taxonomy'   => $taxonomy_name,
        'hide_empty' => false,
        'parent'     => $parent
    ));
    
    return $categories;
}

function jlconverttax_display_all_categories( $parent, $taxonomy_name ) {
    $categories = jlconverttax_get_category( $parent, $taxonomy_name );
    foreach($categories as $category) {
        $category_name = $category->name;
        $ancesors = get_ancestors( intval( $category->term_id ), $taxonomy_name, 'taxonomy' ) ;
        $ancesors = count( $ancesors );
        $margin = 20* $ancesors;
        ?>
        <div style="margin-left: <?php echo intval( $margin )."px" ?>">
            <input type="checkbox" name="jlconverttax-checked-categories[]" value="<?php echo intval( $category->term_id ) ?>">
            <label for="jlconverttax-checked-categories"><?php echo esc_html( $category_name ) ?></label>
        </div>
        <?php echo ' ... '; print_r($category->term_id);
        echo "<br>";
        $parent = $category->term_id;
        $subcategories = jlconverttax_get_category( intval( $parent ), $taxonomy_name );
        if ( !empty( $subcategories ) ) {
            jlconverttax_display_all_categories( $parent, $taxonomy_name );
        }
    }
}


function jlconverttax_display_non_hierarchical_taxonomy( $taxonomy_name ) {
    $tags = get_terms( array(
        'taxonomy'   => $taxonomy_name,
        'hide_empty' => false,
    ));
    $display_taxonomy_name = $taxonomy_name === 'post_tag' ? 'tags' : $taxonomy_name;
    echo '<h3>'.$display_taxonomy_name.'</h3>';

    foreach($tags as $tag) {
        $input_name = $tag->name;
        ?>
        <div>
            <input type="checkbox" name="checked-tags[]" value="<?php echo esc_html( $input_name ) ?>">
            <label for="checked-tags"><?php echo esc_html( $input_name ) ?></label>
        </div>
        <br>
        <?php
    }
}




