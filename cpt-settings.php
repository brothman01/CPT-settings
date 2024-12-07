<?php
/**
 * CPT Settings
 *
 * @category  WordPressPlugin
 * @package   Extender
 * @author    Ben Rothman <Ben@BenRothman.org>
 * @copyright 2024 Ben Rothman
 * @license   GPL-2.0+ https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 * @link      https://www.benrothman.org
 * 
 * @wordpress-plugin
 * Plugin Name: CPT Settings
 * Plugin URI:  https://www.benrothman.org
 * Description: Just a simple WordPress plugin to add a settings page to WordPress where CPTs can be added/edited/removed.
 * Version:     1.4.0
 * Author:      Ben Rothman
 * Author URI:  https://www.BenRothman.org
 * Text Domain: cpt-maker
 * License:     GPL-2.0+
 **/

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPT_Settings {

    /**
     * Constructor for the CPT_Settings class.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'init', array( $this, 'register_multiple_cpts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Add the settings page to the WordPress admin menu.
     */
    public function add_settings_page() {
        add_options_page(
            'CPT Settings',
            'Custom Post Types',
            'manage_options',
            'post-type-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Render the settings page HTML.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_POST['types'] ) && check_admin_referer( 'CPT_Settings_types_nonce', 'CPT_Settings_types_nonce_field' ) ) {
            $types = array();
            foreach ( $_POST['types'] as $cpt ) {
                // Sanitize each field individually.
                $cpts[] = array(
                    'single_name'  => sanitize_text_field( $cpt['single_name'] ),
                    'plural_name'  => sanitize_text_field( $cpt['plural_name'] ),
                    'key'          => sanitize_text_field( $cpt['key'] ),
                    'description'  => sanitize_text_field( $cpt['description'] ),
                    'public'       => sanitize_text_field( $cpt['public'] ),
                    'supports' => array('title', 'editor', 'thumbnail'),
                    'taxonomies'   => sanitize_text_field( $cpt['taxonomies'] ),
                    'icon'         => sanitize_text_field( $cpt['icon'] ),
                    'rewrite' => array('slug' => 'jam'), // Optional, for custom URL structure
                    'hierarchical' => false, // This ensures that this post type is not hierarchical like pages
                    'show_in_menu' => true, // Ensure it's accessible in the WordPress admin menu
                    'show_in_nav_menus' => true, // Allow it in the navigation menus
                    'exclude_from_search' => false, // Allow it to show up in searches
                    'publicly_queryable' => true, // Important for single view to work correctly
                    'public' => true,
                    'has_archive' => true,
                    'show_ui' => true,
                    'show_in_rest' => true, // Enable Gutenberg editor support if needed
                );
            }
            update_option( 'custom_post_types', $cpts );
        }

        $types = get_option( 'custom_post_types', array() );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Custom Post Type Settings', 'cpt-maker' ); ?></h1>
            <h2>Add, Create or Remove CPTs (Custom Post Types) below! Don't forget to save, happy WordPressing!</h2>
            <form method="post" id="cpt-form" action="">
                <?php wp_nonce_field( 'CPT_Settings_types_nonce', 'CPT_Settings_types_nonce_field' ); ?>


                <table class="wp-list-table widefat fixed striped" style="padding-bottom: 15px; margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Name (Singular)', 'cpt-maker' ); ?></th>
                            <th><?php esc_html_e( 'Name (Plural)', 'cpt-maker' ); ?></th>
                            <th><?php esc_html_e( 'Key', 'cpt-maker' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'cpt-maker' ); ?></th>
                            <th><?php esc_html_e( 'Public', 'cpt-maker' ); ?></th>
                            <th><?php esc_html_e( 'Supports', 'cpt-maker' ); ?></th>
                            <th><?php esc_html_e( 'Taxonomies', 'cpt-maker' ); ?></th>
                            <th><?php esc_html_e( 'Icon', 'cpt-maker' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'cpt-maker' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="types-container">
                        <?php foreach ( $types as $index => $type ) : ?>
                            <tr class="type">
                                <td>
                                    <input type="text" name="types[<?php echo esc_attr( $index ); ?>][single_name]" value="<?php echo esc_attr( $type['single_name'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Name (Singular)', 'cpt-maker' ); ?>" style="width: 100%;" />
                                </td>
                                <td>
                                    <input type="text" name="types[<?php echo esc_attr( $index ); ?>][plural_name]" value="<?php echo esc_attr( $type['plural_name'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Name (Plural)', 'cpt-maker' ); ?>" style="width: 100%;" />
                                </td>
                                <td>
                                    <input type="text" name="types[<?php echo esc_attr( $index ); ?>][key]" value="<?php echo esc_attr( $type['key'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Key', 'cpt-maker' ); ?>" style="width: 100%;" />
                                </td>
                                <td>
                                    <input type="text" name="types[<?php echo esc_attr( $index ); ?>][description]" value="<?php echo esc_attr( $type['description'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Description', 'cpt-maker' ); ?>" style="width: 100%;" />
                                </td>
                                <td>
                                    <input type="text" name="types[<?php echo esc_attr( $index ); ?>][public]" value="<?php echo esc_attr( $type['public'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'true/false', 'cpt-maker' ); ?>" />
                                </td>
                                <td>
                                    <input type="text" name="types[<?php echo esc_attr( $index ); ?>][supports]" value="<?php echo esc_attr( $type['supports'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'ie: title, editor, thumbnail', 'cpt-maker' ); ?>" />
                                </td>
                                <td>
                                    <input type="text" name="types[<?php echo esc_attr( $index ); ?>][taxonomies]" value="<?php echo esc_attr( $type['taxonomies'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'ie: category, post_tag', 'cpt-maker' ); ?>" />
                                </td>
                                <td>
                                    <input type="text" name="types[<?php echo esc_attr( $index ); ?>][icon]" value="<?php echo esc_attr( $type['icon'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'ie: dashicons-car', 'cpt-maker' ); ?>" />
                                </td>
                                <td>
                                    <button type="button" class="remove-type button"><?php esc_html_e( 'Remove', 'cpt-maker' ); ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" id="add-type" class="button"><?php esc_html_e( 'Add Post Type', 'cpt-maker' ); ?></button>
                <input type="submit" value="<?php esc_attr_e( 'Save Types', 'cpt-maker' ); ?>" class="button button-primary" />
            </form>
        </div>
        <?php
    }

    /**
     * Register multiple custom post types.
     */
    public function register_multiple_cpts() {
        $types = get_option( 'custom_post_types', array() );
        foreach ( $types as $type ) {
            $labels = array(
                'name'               => $type['plural_name'],
                'singular_name'      => $type['single_name'],
                'add_new'            => 'Add ' . $type['single_name'],
                'add_new_item'       => 'Add New ' . $type['single_name'],
                'edit_item'          => 'Edit ' . $type['single_name'],
                'new_item'           => 'New ' . $type['single_name'],
                'view_item'          => 'View ' . $type['single_name'],
                'search_items'       => 'Search ' . $type['plural_name'],
                'not_found'          => 'No ' . $type['plural_name'] . ' found',
                'not_found_in_trash' => 'No ' . $type['plural_name'] . ' found in Trash',
                'all_items'          => 'All ' . $type['plural_name'],
                'archives'           => $type['plural_name'] . ' Archives',
                'attributes'         => $type['single_name'] . ' Attributes',
                'insert_into_item'   => 'Insert into ' . $type['single_name'],
                'uploaded_to_this_item' => 'Uploaded to this ' . $type['single_name'],
                'featured_image'     => 'Featured Image',
                'set_featured_image' => 'Set featured image',
                'remove_featured_image' => 'Remove featured image',
                'use_featured_image' => 'Use as featured image',
            );
    
            $args = array(
                'labels'            => $labels,
                'public'            => ! empty( $type['public'] ) ? ( 'true' === $type['public'] ) : true,
                'supports'          => ! empty( $type['supports'] ) ? explode( ',', $type['supports'] ) : array( 'title', 'editor' ),
                'taxonomies'        => ! empty( $type['taxonomies'] ) ? explode( ',', $type['taxonomies'] ) : array(),
                'show_in_rest'      => true,
                'has_archive'       => true,
                'rewrite'           => array( 'slug' => $type['key'] ),
                'show_in_menu'      => true,
                'menu_icon'         => ! empty( $type['icon'] ) ? $type['icon'] : 'dashicons-admin-post',
            );
    
            register_post_type( $type['key'], $args );
        }
    }

    /**
     * Enqueue custom CSS and JS for the admin page.
     */
    public function enqueue_assets( $hook ) {
        // Only enqueue on our plugin's settings page.
        if ( $hook !== 'settings_page_post-type-settings' ) {
            return;
        }

        // Enqueue the custom CSS file.
        wp_enqueue_style( 'cpt-settings-style', plugin_dir_url( __FILE__ ) . 'css/cpt-settings.css' );
        
        // Enqueue the custom JS file.
        wp_enqueue_script( 'cpt-settings-js', plugin_dir_url( __FILE__ ) . 'js/cpt-settings.js', array( 'jquery' ), null, true );
    }
}

// Instantiate the class.
new CPT_Settings();
