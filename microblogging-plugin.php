<?php
/*
Plugin Name: Microblogging Plugin
Description: A simple microblogging plugin with ActivityPub integration.
Version: 1.0
Author: Philip A. Swiderski Jr,
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include the widget class
require_once plugin_dir_path( __FILE__ ) . 'class-microblog-widget.php';
require_once plugin_dir_path( __FILE__ ) . 'activitypub.php';

// Register the widget
function register_microblog_widget() {
    register_widget( 'Microblog_Widget' );
}
add_action( 'widgets_init', 'register_microblog_widget' );

// Create a custom post type for microblog posts
function create_microblog_post_type() {
    register_post_type('microblog', array(
        'labels' => array(
            'name' => __('Microblogs'),
            'singular_name' => __('Microblog')
        ),
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor')
    ));
}
add_action('init', 'create_microblog_post_type');

// Add meta boxes for ActivityPub settings
function add_activitypub_meta_boxes() {
    add_meta_box('activitypub_meta_box', 'ActivityPub Settings', 'activitypub_meta_box_callback', 'microblog', 'side', 'high');
}
add_action('add_meta_boxes', 'add_activitypub_meta_boxes');

function activitypub_meta_box_callback($post) {
    wp_nonce_field('activitypub_meta_box_nonce', 'activitypub_meta_box_nonce');

    $activitypub_status = get_post_meta($post->ID, 'activitypub_status', true);
    ?>
    <label for="activitypub_status">ActivityPub Status: </label>
    <input type="text" id="activitypub_status" name="activitypub_status" value="<?php echo esc_attr($activitypub_status); ?>" size="25" />
    <?php
}

function save_activitypub_meta_box_data($post_id) {
    // Check if nonce is set
    if (!isset($_POST['activitypub_meta_box_nonce'])) {
        return;
    }
    // Verify the nonce
    if (!wp_verify_nonce($_POST['activitypub_meta_box_nonce'], 'activitypub_meta_box_nonce')) {
        return;
    }
    // Check for autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // Check user permissions
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    if (!isset($_POST['activitypub_status'])) {
        return;
    }

    $activitypub_status = sanitize_text_field($_POST['activitypub_status']);
    update_post_meta($post_id, 'activitypub_status', $activitypub_status);
}
add_action('save_post', 'save_activitypub_meta_box_data');

// Function to post to Mastodon
function post_to_mastodon($status, $access_token) {
    $url = 'https://mastodon.social/api/v1/statuses';
    $data = array(
        'status' => $status,
        'sensitive' => false,
        'in_reply_to_id' => null,
        'media_ids' => null,
    );
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Authorization: Bearer $access_token\r\n" . "Content-Type: application/json\r\n",
            'content' => json_encode($data),
        ),
    );
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    return $response;
}

// Function to post to Bluesky
function post_to_bluesky($status, $app_password) {
    // Ensure you have the correct API endpoint
    $url = 'https://bsky.app/api/v1/statuses';
    $data = array(
        'status' => $status,
        'sensitive' => false,
        'in_reply_to_id' => null,
        'media_ids' => null,
    );
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Authorization: Bearer $app_password\r\n" . "Content-Type: application/json\r\n",
            'content' => json_encode($data),
        ),
    );
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    return $response;
}

// Hook into the publish action
function autopost_to_mastodon_and_bluesky($post_id) {
    // Get the post status
    $status = get_post_meta($post_id, 'activitypub_status', true);

    if (!$status) {
        return;
    }

    // Replace these with your actual access tokens or retrieve from a secure location
    $mastodon_access_token = 'YOUR_TOKEN';
    $bluesky_app_password = 'YOUR_APP_PASSWORD';

    // Post to Mastodon
    post_to_mastodon($status, $mastodon_access_token);

    // Post to Bluesky
    post_to_bluesky($status, $bluesky_app_password);
}
add_action('publish_microblog', 'autopost_to_mastodon_and_bluesky');
?>
