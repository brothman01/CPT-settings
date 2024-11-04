<?php
/**
* CPT Maker
*
* @category WordPressPlugin
* @package Extender
* @author Ben Rothman <Ben@BenRothman.org>
* @copyright 2024 Ben Rothman
* @license GPL-2.0+ https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
*
* @wordpress-plugin
* Plugin Name: CPT Maker
* Plugin URI: https://www.benrothman.org
* Description: Just a simple WordPress plugin to add/edit/remove different custom post types (CPTs) from the current WordPress installation.
* Version: 1.4.0
* Author: Ben Rothman
* Author URI: https://www.BenRothman.org
* Text Domain: cpt-maker
* License: GPL-2.0+
**/
 
// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
exit;
}
 
class CPT_Maker {
 
public function __construct() {
add_action('admin_menu', [$this, 'add_settings_page']);
add_action('init', [$this, 'register_multiple_cpts']);
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
 
if (isset($_POST['events']) && check_admin_referer('cpt_maker_events_nonce', 'cpt_maker_events_nonce_field')) {
$events = [];
foreach ($_POST['events'] as $event) {
// Sanitize each field individually
$events[] = [
'single_name' => sanitize_text_field($event['single_name']),
'plural_name' => sanitize_text_field($event['plural_name']),
'key' => sanitize_text_field($event['key']),
'description' => sanitize_text_field($event['description']),
'public' => sanitize_text_field($event['public']),
];
}
update_option('custom_post_types', $events);
}
 
$events = get_option('custom_post_types', []);
?>
<div class="wrap">
<h1>Custom Post Type Settings</h1>
<form method="post" action="">
<?php wp_nonce_field('cpt_maker_events_nonce', 'cpt_maker_events_nonce_field'); ?>
<table class="wp-list-table widefat fixed striped" style="padding-bottom: 15px; margin-bottom: 15px;">
<thead>
<tr>
<th>Name (Singular)</th>
<th>Name (Plural)</th>
<th>Key</th>
<th>Description</th>
<th style="display:none;">Public</th> <!-- Hidden public Column -->
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
<td style="display:none;">
<input type="text" name="events[<?php echo $index; ?>][public]" value="<?php echo esc_attr($event['public'] ?? ''); ?>" />
<span style="color: gray; font-style: italic;">Is this CPT public? 'true' or 'false'</span> <!-- Instructions added here -->
</td>
<td>
<button type="button" class="expand-event button">Expand</button>
<button type="button" class="remove-event button">Remove</button>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<button type="button" id="add-event" class="button">Add Post Type</button>
<input type="submit" value="Save Events" class="button button-primary" />
</form>
</div>
 
<!-- Modal HTML -->
<div id="event-modal" class="modal" style="display:none;">
<div class="modal-content">
<span class="close-modal">&times;</span>
<h2>Event Details</h2>
<form id="modal-form">
<input type="hidden" id="modal-index" />
<div id="modal-details"></div>
<button type="button" id="save-changes" class="button button-primary">Save Post Type</button>
</form>
</div>
</div>
 
<style>
.modal {
display: none;
position: fixed;
z-index: 9999; /* Increased z-index to ensure it's above other elements */
left: 0;
top: 0;
width: 100%;
height: 100%;
overflow: auto;
background-color: rgb(0,0,0);
background-color: rgba(0,0,0,0.8); /* Slightly darker background */
padding-top: 60px;
}
.modal-content {
background-color: #fefefe;
margin: 5% auto;
padding: 20px;
border: 1px solid #888;
width: 80%;
}
.close-modal {
color: #aaa;
float: right;
font-size: 28px;
font-weight: bold;
}
.close-modal:hover,
.close-modal:focus {
color: black;
text-decoration: none;
cursor: pointer;
}
</style>
 
<script>
document.getElementById('add-event').addEventListener('click', function() {
var container = document.getElementById('events-container');
var index = container.children.length;
var newEvent = `
<tr class="event">
<td>
<input type="text" name="events[${index}][single_name]" placeholder="Name (Singular)" style="width: 100%;" />
</td>
<td>
<input type="text" name="events[${index}][plural_name]" placeholder="Name (Plural)" style="width: 100%;" />
</td>
<td>
<input type="text" name="events[${index}][key]" placeholder="Key" style="width: 100%;" />
</td>
<td>
<input type="text" name="events[${index}][description]" placeholder="Description" style="width: 100%;" />
</td>
<td style="display:none;">
<input type="text" name="events[${index}][public]" />
<span style="color: gray; font-style: italic;">Is this CPT public? 'true' or 'false'</span> <!-- Instructions added here -->
</td>
<td>
<button type="button" class="expand-event button">Expand</button>
<button type="button" class="remove-event button">Remove</button>
</td>
</tr>`;
container.insertAdjacentHTML('beforeend', newEvent);
});
 
document.addEventListener('click', function(e) {
if (e.target.classList.contains('remove-event')) {
e.target.closest('tr').remove();
}
if (e.target.classList.contains('expand-event')) {
var row = e.target.closest('tr');
var index = Array.from(row.parentNode.children).indexOf(row);
var singleName = row.querySelector('input[name*="[single_name]"]').value;
var pluralName = row.querySelector('input[name*="[plural_name]"]').value;
var key = row.querySelector('input[name*="[key]"]').value;
var description = row.querySelector('input[name*="[description]"]').value;
var public = row.querySelector('input[name*="[public]"]').value;
 
document.getElementById('modal-index').value = index;
document.getElementById('modal-details').innerHTML = `
<p><strong>Name (Singular):</strong> <input type="text" name="single_name" value="${singleName}" /></p>
<p><strong>Name (Plural):</strong> <input type="text" name="plural_name" value="${pluralName}" /></p>
<p><strong>Key:</strong> <input type="text" name="key" value="${key}" /></p>
<p><strong>Description:</strong> <input type="text" name="description" value="${description}" /></p>
<p>
<strong>Public:</strong> <input type="text" name="public" value="${public}" />
<span style="color: gray; font-style: italic; margin-left: 10px;">Is this CPT public? 'true' or 'false'</span>
</p>
`;
document.getElementById('event-modal').style.display = 'block';
}
if (e.target.classList.contains('close-modal')) {
document.getElementById('event-modal').style.display = 'none';
}
});
 
document.getElementById('save-changes').addEventListener('click', function() {
var index = document.getElementById('modal-index').value;
var row = document.getElementById('events-container').children[index];
 
// Update the row with the new values
row.querySelector('input[name*="[single_name]"]').value = document.querySelector('input[name="single_name"]').value;
row.querySelector('input[name*="[plural_name]"]').value = document.querySelector('input[name*="[plural_name]"]').value;
row.querySelector('input[name*="[key]"]').value = document.querySelector('input[name="key"]').value;
row.querySelector('input[name*="[description]"]').value = document.querySelector('input[name="description"]').value;
row.querySelector('input[name*="[public]"]').value = document.querySelector('input[name="public"]').value;
 
document.getElementById('event-modal').style.display = 'none';
});
 
// Close modal when clicking outside of it
window.onclick = function(event) {
if (event.target == document.getElementById('event-modal')) {
document.getElementById('event-modal').style.display = 'none';
}
};
</script>
<?php
echo '<div class="wrap"><h1>Registered Post Types:</h1>';
echo '<ul style="list-style-type: square; padding-left: 15px;">';
foreach( get_post_types() as $item ) {
echo '<li>' . $item . '</li>';
}
echo '</ul></div>';
}
 
public function register_multiple_cpts() {
$cpt_data = get_option('custom_post_types', []);
 
foreach ($cpt_data as $cpt) {
$this->register_cpt($cpt);
}
}
 
private function register_cpt($cpt) {
$labels = [
'name' => _x($cpt['plural_name'], 'Post type general name', 'textdomain'),
'singular_name' => _x($cpt['single_name'], 'Post type singular name', 'textdomain'),
'menu_name' => _x($cpt['plural_name'], 'Admin Menu text', 'textdomain'),
'name_admin_bar' => _x($cpt['single_name'], 'Add New on Toolbar', 'textdomain'),
'add_new' => __('Add New', 'textdomain'),
'add_new_item' => __('Add New ' . $cpt['single_name'], 'textdomain'),
'new_item' => __('New ' . $cpt['single_name'], 'textdomain'),
'edit_item' => __('Edit ' . $cpt['single_name'], 'textdomain'),
'view_item' => __('View ' . $cpt['single_name'], 'textdomain'),
'all_items' => __('All ' . $cpt['plural_name'], 'textdomain'),
'search_items' => __('Search ' . $cpt['plural_name'], 'textdomain'),
'not_found' => __('No ' . $cpt['plural_name'] . ' found.', 'textdomain'),
'not_found_in_trash' => __('No ' . $cpt['plural_name'] . ' found in Trash.', 'textdomain'),
];
 
$args = [
'labels' => $labels,
'public' => $cpt['public'] == 'true' ? true : false,
'publicly_queryable' => true,
'show_ui' => true,
'show_in_menu' => true,
'query_var' => true,
'rewrite' => ['slug' => $cpt['key']],
'capability_type' => 'post',
'has_archive' => true,
'hierarchical' => false,
'menu_position' => null,
'supports' => ['title', 'editor', 'thumbnail'],
];
 
register_post_type($cpt['key'], $args);
}
 
}
 
$plugin = new CPT_Maker();