<?php

declare(strict_types=1);

/**
 * Local redesign assets for Emotional Weddings.
 */

add_action('wp_enqueue_scripts', static function (): void {
    if (! is_front_page() && ! is_page('home')) {
        return;
    }

    wp_enqueue_style(
        'emotional-weddings-home-v1',
        content_url('mu-plugins/emotional-weddings-redesign/home-v1.css'),
        [],
        '2026-06-30'
    );
});
