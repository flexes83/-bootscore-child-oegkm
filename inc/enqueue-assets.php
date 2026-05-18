<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {
    $theme = wp_get_theme();
    $version = $theme->get('Version');

    wp_enqueue_style(
        'bootscore-child-oegkm-fonts',
        get_stylesheet_directory_uri() . '/assets/css/fonts.css',
        [],
        $version
    );

    wp_enqueue_style(
        'bootscore-child-oegkm',
        get_stylesheet_directory_uri() . '/assets/css/custom.css',
        ['bootscore-style', 'bootscore-child-oegkm-fonts'],
        $version
    );

    wp_enqueue_script(
        'bootscore-child-oegkm-header-search',
        get_stylesheet_directory_uri() . '/assets/js/header-search.js',
        [],
        $version,
        true
    );

    wp_enqueue_script(
        'bootscore-child-oegkm-header-scroll',
        get_stylesheet_directory_uri() . '/assets/js/header-scroll.js',
        [],
        $version,
        true
    );

    wp_enqueue_script(
        'bootscore-child-oegkm-mobile-nav',
        get_stylesheet_directory_uri() . '/assets/js/header-mobile-nav.js',
        [],
        $version,
        true
    );

    wp_enqueue_script(
        'bootscore-child-oegkm-member-media',
        get_stylesheet_directory_uri() . '/assets/js/member-media-lightbox.js',
        [],
        $version,
        true
    );
}, 20);

add_action('enqueue_block_editor_assets', function () {
    $theme = wp_get_theme();

    wp_enqueue_style(
        'bootscore-child-oegkm-editor',
        get_stylesheet_directory_uri() . '/assets/css/custom.css',
        [],
        $theme->get('Version')
    );
});
