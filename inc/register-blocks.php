<?php
if (!defined('ABSPATH')) {
    exit;
}

function bootscore_child_oegkm_register_blocks(): void {
    $blocks_dir = get_stylesheet_directory() . '/blocks';

    if (!is_dir($blocks_dir)) {
        return;
    }

    $directories = glob($blocks_dir . '/*', GLOB_ONLYDIR);

    if (!$directories) {
        return;
    }

    foreach ($directories as $directory) {
        $block_json = $directory . '/block.json';

        if (!file_exists($block_json)) {
            continue;
        }

        register_block_type($directory);
    }
}

add_action('init', 'bootscore_child_oegkm_register_blocks', 20);
