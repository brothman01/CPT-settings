<?php
/**
 * CPT Settings
 *
 * @category  WordPressPlugin
 * @package   Extender
 * @author    Ben Rothman <Ben@BenRothman.org>
 * @copyright 2024 Ben Rothman
 * @license   GPL-2.0+ https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
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

    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('init', [$this, 'register_multiple_cpts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_settings_page() {
        add_options_page(
            'CPT Settings',
            'Custom Post Types',
            'manage_options',
            'post-type-settings',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['events']) && check_admin_referer('CPT_Settings_events_nonce', 'CPT_Settings_events_nonce_field')) {
            $events = [];
            foreach ($_POST['events'] as $event) {
                // Sanitize each field individually
                $events[] = [
                    'single_name'  => sanitize_text_field($event['single_name']),
                    'plural_name'  => sanitize_text_field($event['plural_name']),
                    'key'          => sanitize_text_field($event['key']),
                    'description'  => sanitize_text_field($event['description']),
                    'public'       => sanitize_text_field($event['public']),
                    'supports'     => sanitize_text_field($event['supports']),
                    'taxonomies'   => sanitize_text_field($event['taxonomies']),
                    'icon'         => sanitize_text_field($event['icon']),
                ];
            }
            update_option('custom_post_types', $events);
        }

        $events = get_option('custom_post_types', []);
        ?>
        <div class="wrap">
            <h1>Custom Post Type Settings</h1>
            <form method="post" action="">
                <?php wp_nonce_field('CPT_Settings_events_nonce', 'CPT_Settings_events_nonce_field'); ?>
                <table class="wp-list-table widefat fixed striped" style="padding-bottom: 15px; margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th>Name (Singular)</th>
                            <th>Name (Plural)</th>
                            <th>Key</th>
                            <th>Description</th>
                            <th>Public</th>
                            <th>Supports</th>
                            <th>Taxonomies</th>
                            <th>Icon</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="events-container">
                        <?php foreach ($events as $index => $event): ?>
                            <tr class="event">
                                <td>
                                    <input type="text" name="events[<?php echo $index; ?>][single_name]" value="<?php echo esc_attr($event['single_name'] ?? ''); ?>" placeholder="Name (Singular)" style="width: 100%;" />
                                </td>
                                <td>
                                    <input type="text" name="events[<?php echo $index; ?>][plural_name]" value="<?php echo esc_attr($event['plural_name'] ?? ''); ?>" placeholder="Name (Plural)" style="width: 100%;" />
                                </td>
                                <td>
                                    <input type="text" name="events[<?php echo $index; ?>][key]" value="<?php echo esc_attr($event['key'] ?? ''); ?>" placeholder="Key" style="width: 100%;" />
                                </td>
                                <td>
                                    <input type="text" name="events[<?php echo $index; ?>][description]" value="<?php echo esc_attr($event['description'] ?? ''); ?>" placeholder="Description" style="width: 100%;" />
                                </td>
                                <td>
                                    <input type="text" name="events[<?php echo $index; ?>][public]" value="<?php echo esc_attr($event['public'] ?? ''); ?>" placeholder="true/false" />
                                </td>
                                <td>
                                    <input type="text" name="events[<?php echo $index; ?>][supports]" value="<?php echo esc_attr($event['supports'] ?? ''); ?>" placeholder="ie: title, editor, thumbnail" />
                                </td>
                                <td>
                                    <input type="text" name="events[<?php echo $index; ?>][taxonomies]" value="<?php echo esc_attr($event['taxonomies'] ?? ''); ?>" placeholder="ie: category, post_tag" />
                                </td>
                                <td>
                                    <input type="text" name="events[<?php echo $index; ?>][icon]" value="<?php echo esc_attr($event['icon'] ?? ''); ?>" placeholder="ie: dashicons-car" />
                                </td>
                                <td>
                                    <button type="button" class="remove-event button">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" id="add-type" class="button">Add Post Type</button>
                <input type="submit" value="Save Events" class="button button-primary" />
            </form>
        </div>
        <?php
    }

    public function register_multiple_cpts() {
        $events = get_option('custom_post_types', []);
        foreach ($events as $event) {
            $labels = [
                'name'               => $event['plural_name'],
                'singular_name'      => $event['single_name'],
                'add_new'            => 'Add ' . $event['single_name'],
                'add_new_item'       => 'Add New ' . $event['single_name'],
                'edit_item'          => 'Edit ' . $event['single_name'],
                'new_item'           => 'New ' . $event['single_name'],
                'view_item'          => 'View ' . $event['single_name'],
                'search_items'       => 'Search ' . $event['plural_name'],
                'not_found'          => 'No ' . $event['plural_name'] . ' found',
                'not_found_in_trash' => 'No ' . $event['plural_name'] . ' found in Trash',
                'all_items'          => 'All ' . $event['plural_name'],
                'archives'           => $event['plural_name'] . ' Archives',
                'attributes'         => $event['single_name'] . ' Attributes',
                'insert_into_item'   => 'Insert into ' . $event['single_name'],
                'uploaded_to_this_item' => 'Uploaded to this ' . $event['single_name'],
                'featured_image'     => 'Featured Image',
                'set_featured_image' => 'Set featured image',
                'remove_featured_image' => 'Remove featured image',
                'use_featured_image' => 'Use as featured image',
            ];

            $args = [
                'labels'            => $labels,
                'public'            => ('true' === $event['public']),
                'supports'          => explode(',', $event['supports']),
                'taxonomies'        => explode(',', $event['taxonomies']),
                'show_in_rest'      => true,
                'has_archive'       => true,
                'rewrite'           => ['slug' => $event['key']],
                'show_in_menu'      => true,
                'menu_icon'         => $event['icon'],
            ];

            register_post_type($event['key'], $args);
        }
    }

    // Enqueue custom CSS and JS for the admin page
    public function enqueue_assets($hook) {
        // Only enqueue on our plugin's settings page
        if ($hook !== 'settings_page_post-type-settings') {
            return;
        }

        // Enqueue the custom CSS file
        wp_enqueue_style('cpt-settings-style', plugin_dir_url(__FILE__) . 'css/cpt-settings.css');
        
        // Enqueue the custom JS file
        wp_enqueue_script('cpt-settings-js', plugin_dir_url(__FILE__) . 'js/cpt-settings.js', ['jquery'], null, true);
    }
}

// Instantiate the class
new CPT_Settings();
