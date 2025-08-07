<?php
/**
 * Plugin Name: JL Convert Taxonomy Terms
 * Description: JL Convert Taxonomy Terms plugin allows to move taxonomy terms and its children to another taxonomy with saving taxonomy hierarchy
 * Version: 1.5
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: JL-lovecoding
 * Author URI: https://love-coding.pl/en
 * Text Domain: jlconverttax
 * Domain Path: /languages
 * License: GPLv3
 * 
 * JL Convert Taxonomy Terms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *   
 * JL Convert Taxonomy Terms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with JL Convert Taxonomy Terms. If not, see http://www.gnu.org/licenses/gpl.html.
 */


defined( 'ABSPATH' ) or die( 'hey, you don\'t have an access to read this site' );

/******************************************
 * Adding 'Settings' link to plugin links
 ******************************************/

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'jlconverttax_add_plugin_settings_link');
function jlconverttax_add_plugin_settings_link( $links ) {
    $url = admin_url()."tools.php?page=convert-taxonomy-terms";
    $settings_link = '<a href="'.esc_url( $url ).'">'.esc_html__( "Settings", "jlconverttax" ).'</a>';
    $links[] = $settings_link;
    return $links;
}



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
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                'ajax_nonce'    => wp_create_nonce( 'jlconverttax_ajax_nonce' )
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
        'sanitize_callback' => 'jlconverttax_sanitize_text_input',
        'default' => 'yes'
    );
    $configuration_settins_field_2_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'jlconverttax_sanitize_text_input',
        'default' => 'category'
    );
    $configuration_settins_field_3_arg = array(
        'type' => 'string',
        'sanitize_callback' => 'jlconverttax_sanitize_text_input',
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
    add_settings_section( 'jlconverttax_configuration', 'Settings', 'jlconverttax_section_descriprion', 'jlconverttax-slug' );  // id (Slug-name to identify the section), title, callback, page slug 

    // adding fields for section
    add_settings_field( 'field-1-copy-move', 'Save taxonomy hierarchy', 'jlconverttax_field_1_callback', 'jlconverttax-slug', 'jlconverttax_configuration' );
    add_settings_field( 'field-2-from-to', 'Convert Terms', 'jlconverttax_field_2_callback', 'jlconverttax-slug', 'jlconverttax_configuration' );
    
}


function jlconverttax_section_descriprion() {
    ?>
    <p><?php esc_html_e( "When You choose 'Yes' in 'Save taxonomy hierarchy' section, you must move first-level parent with all its children to new hierarchical taxonomy, to all moved taxonomies was visible.", "jlconverttax") ?></p>
    <p><?php esc_html_e( "You can also move terms to non-hierarchical taxonomy, then all of them will be convert to first-level terms.", "jlconverttax") ?></p>
    </p>
    <p><?php esc_html_e( "If you want to move only some of subcategories to other taxonomy, than choose 'No' in 'Save taxonomy hierarchy' section.", "jlconverttax") ?></p>
    <br>
    <?php
}

function jlconverttax_field_1_callback() {
    $isChecked = get_option( "jlconverttax-save-hierarchy", 'yes' );
    ?>
    <label for="copy"><strong><?php esc_html_e( "Yes", "jlconverttax") ?></strong></label>
    <!-- input name must be the same as option name in register_setting -->
    <input type="radio" name="jlconverttax-save-hierarchy" value="yes" <?php echo esc_html( $isChecked ) === 'yes' ? "checked" : null ?> />
    <label for="move"><strong><?php esc_html_e( "No", "jlconverttax") ?></strong></label>
    <input type="radio" name="jlconverttax-save-hierarchy" value="no" <?php echo esc_html( $isChecked ) === 'no' ? "checked" : null ?> />
    <?php
}

function jlconverttax_field_2_callback() {
    $taxonomies = jlconverttax_get_all_taxonomies();
    $selected_from = get_option( "jlconverttax-from-taxonomy", 'category' );
    $selected_to = get_option( "jlconverttax-to-taxonomy", 'post_tag' );
    if ( !in_array( $selected_from, $taxonomies ) ) {
        update_option( "jlconverttax-from-taxonomy", 'category');
        $selected_from = get_option( "jlconverttax-from-taxonomy", 'category' );
    }
    if ( !in_array( $selected_to, $taxonomies ) ) {
        update_option( "jlconverttax-to-taxonomy", 'post_tag');
        $selected_to = get_option( "jlconverttax-to-taxonomy", 'post_tag' );
    }
    ?>
    <!-- display taxonomies 'from' list -->
    <span><strong><?php esc_html_e( "From", "jlconverttax") ?> </strong></span>
    <select name="jlconverttax-from-taxonomy" id="jlconverttax-from-taxonomy">
        <?php foreach($taxonomies as $taxonomy) : ?>
            <option value="<?php echo esc_attr( $taxonomy ) ?>" <?php echo esc_html( $selected_from === $taxonomy ? 'selected' : null ) ?> ><?php echo esc_html( $taxonomy ) ?></option>
        <?php endforeach; ?>
    </select>
    <!-- display taxonomies 'to' list -->
    <span><strong><?php esc_html_e( "To", "jlconverttax") ?> </strong></span>
    <select name="jlconverttax-to-taxonomy" id="jlconverttax-to-taxonomy">
        <?php foreach($taxonomies as $taxonomy) : ?>
            <option value="<?php echo esc_attr( $taxonomy ) ?>" <?php echo esc_html( $selected_to === $taxonomy ? 'selected' : null ) ?> ><?php echo esc_html( $taxonomy ) ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}



// sanitize input
function jlconverttax_sanitize_text_input( $input ) {
    if ( isset( $input ) ) {
        $input = sanitize_text_field( $input );
    }
    return $input;
}

// sanitize array
function jlconverttax_sanitize_categories( $input ) {
    if ( is_array( $input ) ) {
        foreach ($input as $input_element) {
            $input_element = intval( $input_element );
        }
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
        <div style="font-size: 20px; margin-top: 20px"> <?php esc_html_e( "You don't have permission to manage this page", "jlconverttax" ); ?> </div>
        <?php
        return;
    }

    ?>
    <div class="wrap">
        <h2><?php esc_html_e( 'Convert Taxonomy Terms') ?></h2>
        <form action="options.php" method="post">
            <?php
            // outpus settings fields (without this there is error after clicking save settings button)
            settings_fields( 'jlconverttax_options' );                        // A settings group name. This should match the group name used in register_setting()
            // output setting sections and their fields
            do_settings_sections( 'jlconverttax-slug' );                      // The slug name of settings sections you want to output.

            $choosen_taxonomy = get_option( "jlconverttax-from-taxonomy", 'category' );
            ?>
            <br><br>
            <div><strong><?php esc_html_e( "Choose terms that you want to move from ", "jlconverttax") ?>
                <span class="from-option"><?php echo esc_html( $choosen_taxonomy ); ?></span>
                <?php esc_html_e( "to ", "jlconverttax") ?>
                <span class="to-option"><?php echo esc_html( get_option( "jlconverttax-to-taxonomy", "post_tag") ); ?></span>
            </strong></div>
            <br>
            <div class="display-category">
                <?php jlconverttax_display_taxonomy( esc_html( $choosen_taxonomy ) ); ?>
            </div>
            <?php
            // output save settings button
            submit_button( __('Convert Terms'), 'primary', 'submit', true );     // Button text, button type, button id, wrap, any other attribute
            ?>
            <p><?php esc_html_e( "If after taxonomy converting some pages doesn't display, go to Settings -> Permalinks and press button 'Save settings'.", "jlconverttax") ?></p>
            <p id="jlconverttax_info"></p>
        </form>
    </div>
    <?php
}



/*****************************
 * Load categories by ajax
 *****************************/

add_action( 'wp_ajax_load_categories_by_ajax', 'jlconverttax_load_categories_by_ajax' );
function jlconverttax_load_categories_by_ajax() {
    check_ajax_referer( 'jlconverttax_ajax_nonce', '_ajax_nonce' );
    $from_taxonomy = sanitize_text_field( $_POST['from-category'] );
    $to_taxonomy = sanitize_text_field( $_POST['to-category'] );
    if ( current_user_can( 'manage_options' ) ) {
        update_option( "jlconverttax-from-taxonomy", $from_taxonomy );
        update_option( "jlconverttax-to-taxonomy", $to_taxonomy );
        $url = esc_url( admin_url()."tools.php?page=convert-taxonomy-terms" );
        echo json_encode( $url );
    } else {
        echo json_encode( 'no' );
    }
    die();
}


/**************************
 * Display taxonomy terms
 **************************/

function jlconverttax_display_taxonomy( $taxonomy_name ) {
    $display_taxonomy_name = $taxonomy_name === 'post_tag' ? 'tags' : $taxonomy_name;
    ?> <h3><?php echo esc_html( ucfirst( $display_taxonomy_name ) ) ?></h3> <?php
    jlconverttax_display_all_taxonomies( 0, $taxonomy_name );
}

function jlconverttax_get_taxonomy( $parent = 0, $taxonomy_name = 'category' ) {
    $categories = get_terms( array(
        'taxonomy'   => $taxonomy_name,
        'hide_empty' => false,
        'parent'     => $parent
    ));
    
    return $categories;
}

function jlconverttax_display_all_taxonomies( $parent, $taxonomy_name ) {
    $categories = jlconverttax_get_taxonomy( $parent, $taxonomy_name );
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
        <br>
        <?php
        $parent = $category->term_id;
        $subcategories = jlconverttax_get_taxonomy( intval( $parent ), $taxonomy_name );
        if ( !empty( $subcategories ) ) {
            jlconverttax_display_all_taxonomies( $parent, $taxonomy_name );
        }
    }
}



/******************************************************************************
 * Do action after submiting 'Convert Terms' button (after options has update)
 ******************************************************************************/

add_action('updated_option', function( $option_name, $old_value, $value ) {
    $change_terms = get_option( "jlconverttax-checked-categories", 1 );
    $convert_from = get_option( "jlconverttax-from-taxonomy" );
    $convert_to = get_option( "jlconverttax-to-taxonomy" );
    $save_hierarchy = get_option( "jlconverttax-save-hierarchy");
    if ( !empty( $change_terms ) && !empty( $convert_from ) && !empty( $convert_to ) && !empty( $save_hierarchy ) ) {
        foreach($change_terms as $term_id) {
            // change term taxonomy
            $term_parent = 0;
            if ( is_taxonomy_hierarchical( $convert_to ) && $save_hierarchy === 'yes' ) {
                $term_parent = get_term($term_id)->parent;
            }
            $table = 'wp_term_taxonomy';
            $data = array(
                'taxonomy' => $convert_to,
            );
            $where = array('term_id' => $term_id);
            global $wpdb;
            $updated = $wpdb->update( $table, $data, $where );
            if ( $updated ) {
                // update term in new term context
                wp_update_term($term_id, $convert_to, array( 'parent' => $term_parent ));       // wp_update_term($term_id, $term_taxonomy (new changed taxonomy - new context), $args);
            } else {
                // error;
            }
        }
        update_option( "jlconverttax-checked-categories", "" );
    }    
}, 10, 3);


/***********************
 * Load translations
 ***********************/
add_action( 'plugins_loaded', 'jlconverttax_load_textdomain' );
function jlconverttax_load_textdomain() {
    load_plugin_textdomain( 'jlconverttax', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}


