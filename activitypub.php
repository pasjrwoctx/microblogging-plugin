<?php
// Ensure this file is included only once
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ActivityPub integration function
function activitypub_send_update($post_id) {
    $post = get_post($post_id);
    if ($post->post_type != 'microblog') {
        return;
    }

    $status = get_post_meta($post_id, 'activitypub_status', true);
    if (!$status) {
        return;
    }

    // Replace with your actual ActivityPub endpoint and user URL
    $activitypub_endpoint = 'https://mastodon.social/api/v1/statuses';
    $activitypub_endpoint = 'https://bsky.app/api/v1/statuses';
    $actor_url = 'https://Your_Site/@You';

    // Create the ActivityPub message
    $activitypub_message = array(
        '@context' => 'https://www.w3.org/ns/activitystreams',
        'type' => 'Create',
        'actor' => $actor_url,
        'object' => array(
            'type' => 'Note',
            'content' => $status,
            'published' => date('c', strtotime($post->post_date)),
        ),
    );

    // Set up the HTTP request options
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type: application/ld+json\r\n",
            'content' => json_encode($activitypub_message),
        ),
    );
    $context = stream_context_create($options);
    $response = @file_get_contents($activitypub_endpoint, false, $context);

    // Handle the response
    if ($response === FALSE) {
        error_log('Error posting to ActivityPub: ' . $http_response_header[0]);
    } else {
        // Log the successful request
        error_log('ActivityPub post successful: ' . $response);
    }
}
add_action('publish_microblog', 'activitypub_send_update');
?>
