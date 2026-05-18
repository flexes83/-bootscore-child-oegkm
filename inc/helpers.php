<?php
if (!defined('ABSPATH')) {
    exit;
}

function bootscore_child_oegkm_default_logo_markup(): string {
    $logo_url = get_stylesheet_directory_uri() . '/assets/img/logo-oegkm.svg';
    return '<a class="custom-logo-link custom-logo-link--fallback" href="' . esc_url(home_url('/')) . '" rel="home" aria-label="' . esc_attr(get_bloginfo('name')) . '">'
        . '<img class="custom-logo custom-logo--fallback" src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '">'
        . '</a>';
}

function bootscore_child_oegkm_footer_logo_markup(): string {
    $logo_url = get_stylesheet_directory_uri() . '/assets/img/logo-oegkm-light.svg';
    return '<a class="custom-logo-link custom-logo-link--footer" href="' . esc_url(home_url('/')) . '" rel="home" aria-label="' . esc_attr(get_bloginfo('name')) . '">'
        . '<img class="custom-logo custom-logo--footer" src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '">'
        . '</a>';
}
