<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('oegkm_theme_options', [
        'title'    => __('OEGKM Theme Options', 'bootscore-child-oegkm'),
        'priority' => 30,
    ]);

    $settings = [
        'member_area_label' => __('Mitgliederbereich', 'bootscore-child-oegkm'),
        'member_area_url'   => '#',
        'search_page_url'   => home_url('/'),
        'footer_text'       => __('Österreichische Gesellschaft für Knochen- und Mineralstoffwechsel', 'bootscore-child-oegkm'),
        'privacy_url'       => home_url('/datenschutz/'),
        'imprint_url'       => home_url('/impressum/'),
    ];

    foreach ($settings as $key => $default) {
        $wp_customize->add_setting('oegkm_' . $key, [
            'default'           => $default,
            'sanitize_callback' => 'wp_kses_post',
        ]);
    }

    $controls = [
        'member_area_label' => __('Mitgliederbereich Label', 'bootscore-child-oegkm'),
        'member_area_url'   => __('Mitgliederbereich URL', 'bootscore-child-oegkm'),
        'search_page_url'   => __('Such-URL', 'bootscore-child-oegkm'),
        'footer_text'       => __('Footer Text', 'bootscore-child-oegkm'),
        'privacy_url'       => __('Datenschutz URL', 'bootscore-child-oegkm'),
        'imprint_url'       => __('Impressum URL', 'bootscore-child-oegkm'),
    ];

    foreach ($controls as $key => $label) {
        $type = strpos($key, 'url') !== false ? 'url' : 'text';
        $wp_customize->add_control('oegkm_' . $key, [
            'label'   => $label,
            'section' => 'oegkm_theme_options',
            'type'    => $type,
        ]);
    }
});

function bootscore_child_oegkm_member_area_label(): string {
    return (string) get_theme_mod('oegkm_member_area_label', __('Mitgliederbereich', 'bootscore-child-oegkm'));
}

function bootscore_child_oegkm_member_area_url(): string {
    return esc_url((string) get_theme_mod('oegkm_member_area_url', '#'));
}

function bootscore_child_oegkm_search_url(): string {
    return esc_url((string) get_theme_mod('oegkm_search_page_url', home_url('/')));
}

function bootscore_child_oegkm_footer_text(): string {
    return wp_kses_post((string) get_theme_mod('oegkm_footer_text', __('Österreichische Gesellschaft für Knochen- und Mineralstoffwechsel', 'bootscore-child-oegkm')));
}

function bootscore_child_oegkm_privacy_url(): string {
    return esc_url((string) get_theme_mod('oegkm_privacy_url', home_url('/datenschutz/')));
}

function bootscore_child_oegkm_imprint_url(): string {
    return esc_url((string) get_theme_mod('oegkm_imprint_url', home_url('/impressum/')));
}
