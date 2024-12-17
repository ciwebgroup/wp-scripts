<?php
// Include the WordPress blog headers to bootstrap WordPress.
require_once('./wp-blog-header.php'); // Update this path to your WordPress installation.

if (!defined('ABSPATH')) {
    exit('This script must be run in a WordPress environment.');
}

// Filepath to your JSON file.
$jsonFilePath = __DIR__ . '/import.json';

if (!file_exists($jsonFilePath)) {
    exit("JSON file not found: $jsonFilePath\n");
}

// Load the JSON file and decode it.
$jsonData = json_decode(file_get_contents($jsonFilePath), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    exit("Error decoding JSON: " . json_last_error_msg() . "\n");
}

foreach ($jsonData as $post) {
    // Validate necessary fields.
    if (empty($post['slug']) || empty($post['type'])) {
        echo "Skipping: Missing required 'slug' or 'type' field in JSON data.\n";
        continue;
    }

    // Use WP_Query to check for an existing post of the given slug and type.
    $queryArgs = [
        'name'        => $post['slug'],
        'post_type'   => $post['type'],
        'post_status' => 'any', // Include drafts, published, etc.
        'fields'      => 'ids', // We only need the post ID.
    ];

    $query = new WP_Query($queryArgs);
    $existingPostId = $query->have_posts() ? $query->posts[0] : 0;

    // Prepare post data.
    $postArgs = [
        'ID'            => $existingPostId, // Use existing ID if found; otherwise, this creates a new post.
        'post_title'    => $post['title']['rendered'] ?? '',
        'post_content'  => $post['content']['rendered'] ?? '',
        'post_excerpt'  => $post['excerpt']['rendered'] ?? '',
        'post_status'   => $post['status'] ?? 'draft',
        'post_name'     => $post['slug'] ?? '',
        'post_type'     => $post['type'] ?? 'post',
        'post_author'   => $post['author'] ?? 1, // Default to admin if no author ID is provided.
        'post_date'     => $post['date'] ?? current_time('mysql'),
        'post_date_gmt' => $post['date_gmt'] ?? current_time('mysql', 1),
        'meta_input'    => $post['meta'] ?? [],
    ];

    // Upsert the post: Insert or update.
    $postId = wp_insert_post($postArgs);

    if (is_wp_error($postId)) {
        echo "Error upserting post for slug '{$post['slug']}': " . $postId->get_error_message() . "\n";
        continue;
    }

    $action = $existingPostId ? 'Updated' : 'Created';
    echo "$action post with ID $postId for slug '{$post['slug']}'\n";

    // Set the categories if provided.
    if (!empty($post['categories'])) {
        wp_set_post_categories($postId, $post['categories']);
    }

    // Set the tags if provided.
    if (!empty($post['tags'])) {
        wp_set_post_tags($postId, array_map('intval', $post['tags']));
    }

    // Set the featured image if provided.
    if (!empty($post['featured_media'])) {
        set_post_thumbnail($postId, $post['featured_media']);
    }

    // Add custom taxonomies (city, service) if present.
    if (!empty($post['city'])) {
        wp_set_object_terms($postId, $post['city'], 'city');
    }

    if (!empty($post['service'])) {
        wp_set_object_terms($postId, $post['service'], 'service');
    }
}

echo "Import completed.\n";
