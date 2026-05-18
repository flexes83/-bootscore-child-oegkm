<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', function () {
    add_theme_support('editor-styles');
    add_editor_style('assets/css/custom.css');

    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 240,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    register_nav_menus([
        'primary' => __('Hauptnavigation', 'bootscore-child-oegkm'),
        'footer'  => __('Footer Navigation', 'bootscore-child-oegkm'),
    ]);
});

add_action('init', function () {
    register_block_pattern_category('oegkm-sections', [
        'label' => __('OEGKM Sections', 'bootscore-child-oegkm'),
    ]);

    if (function_exists('register_block_style')) {
        register_block_style('core/group', [
            'name'  => 'oegkm-gradient-mint',
            'label' => __('OEGKM Gradient Mint', 'bootscore-child-oegkm'),
        ]);
        register_block_style('core/group', [
            'name'  => 'oegkm-gradient-lilac',
            'label' => __('OEGKM Gradient Lilac', 'bootscore-child-oegkm'),
        ]);
        register_block_style('core/group', [
            'name'  => 'oegkm-gradient-sun',
            'label' => __('OEGKM Gradient Sun', 'bootscore-child-oegkm'),
        ]);
        register_block_style('core/group', [
            'name'  => 'oegkm-gradient-soft',
            'label' => __('OEGKM Gradient Soft', 'bootscore-child-oegkm'),
        ]);
        register_block_style('core/group', [
            'name'  => 'oegkm-gradient-animated',
            'label' => __('OEGKM Gradient Animated', 'bootscore-child-oegkm'),
        ]);
    }
});
