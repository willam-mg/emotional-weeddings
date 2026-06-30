<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$content_file = $root . '/exports/templates/home-redesign-v1.wp.html';
$wp_load = $root . '/wordpress/wp-load.php';

if (! file_exists($content_file)) {
    fwrite(STDERR, "Missing content file: {$content_file}\n");
    exit(1);
}

if (! file_exists($wp_load)) {
    fwrite(STDERR, "Missing WordPress loader: {$wp_load}\n");
    exit(1);
}

require_once $wp_load;

global $wpdb;

$content = file_get_contents($content_file);
if ($content === false || $content === '') {
    fwrite(STDERR, "Home redesign content is empty.\n");
    exit(1);
}

$updated = $wpdb->update(
    $wpdb->posts,
    [
        'post_content' => $content,
        'post_modified' => current_time('mysql'),
        'post_modified_gmt' => current_time('mysql', true),
    ],
    [
        'ID' => 65,
        'post_type' => 'page',
    ],
    [
        '%s',
        '%s',
        '%s',
    ],
    [
        '%d',
        '%s',
    ]
);

if ($updated === false) {
    fwrite(STDERR, "Database update failed: {$wpdb->last_error}\n");
    exit(1);
}

echo "updated={$updated}\n";
