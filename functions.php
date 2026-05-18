<?php
if (!defined('ABSPATH')) {
    exit;
}

function bootscore_child_oegkm_theme_version(): string {
    return (string) wp_get_theme()->get('Version');
}

require_once get_stylesheet_directory() . '/inc/events.php';
require_once get_stylesheet_directory() . '/inc/theme-page-header.php';
require_once get_stylesheet_directory() . '/inc/members.php';

add_action('wp_enqueue_scripts', function () {
    $version = bootscore_child_oegkm_theme_version();

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

    wp_enqueue_script('bootscore-child-oegkm-header-search', get_stylesheet_directory_uri() . '/assets/js/header-search.js', [], $version, true);
    wp_enqueue_script('bootscore-child-oegkm-header-scroll', get_stylesheet_directory_uri() . '/assets/js/header-scroll.js', [], $version, true);
    wp_enqueue_script('bootscore-child-oegkm-accordion-frontend', get_stylesheet_directory_uri() . '/assets/js/accordion-frontend.js', [], $version, true);
    wp_enqueue_script('bootscore-child-oegkm-ziele-frontend', get_stylesheet_directory_uri() . '/assets/js/ziele-frontend.js', [], $version, true);
    wp_enqueue_script('bootscore-child-oegkm-disease-slider-frontend', get_stylesheet_directory_uri() . '/assets/js/disease-slider-frontend.js', [], $version, true);

    if (is_page_template('page-mitglieder-medien.php') && file_exists(get_stylesheet_directory() . '/assets/js/member-media-lightbox.js')) {
        wp_enqueue_script('bootscore-child-oegkm-member-media', get_stylesheet_directory_uri() . '/assets/js/member-media-lightbox.js', [], $version, true);
    }
}, 20);

add_action('enqueue_block_editor_assets', function () {
    $version = bootscore_child_oegkm_theme_version();

    wp_enqueue_style('bootscore-child-oegkm-editor', get_stylesheet_directory_uri() . '/assets/css/custom.css', [], $version);

    wp_enqueue_script(
        'bootscore-child-oegkm-accordion-block',
        get_stylesheet_directory_uri() . '/assets/js/accordion-block.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'],
        $version,
        true
    );

    wp_enqueue_script(
        'bootscore-child-oegkm-ziele-block',
        get_stylesheet_directory_uri() . '/assets/js/ziele-block.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        $version,
        true
    );

    wp_enqueue_script(
        'bootscore-child-oegkm-disease-slider-block',
        get_stylesheet_directory_uri() . '/assets/js/disease-slider-block.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        $version,
        true
    );

    wp_enqueue_script(
        'bootscore-child-oegkm-media-cta-block',
        get_stylesheet_directory_uri() . '/blocks/oegkm-media-cta/editor.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        $version,
        true
    );

    wp_enqueue_script(
        'bootscore-child-oegkm-podcast-cta-block',
        get_stylesheet_directory_uri() . '/blocks/oegkm-podcast-cta/editor.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        $version,
        true
    );

    wp_enqueue_script(
        'bootscore-child-oegkm-team-slider-block',
        get_stylesheet_directory_uri() . '/blocks/oegkm-team-slider/editor.js',
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components'],
        $version,
        true
    );
});

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


/**
 * Bootstrap-compatible primary navigation markup.
 */
function bootscore_child_oegkm_is_primary_menu($args): bool {
    return isset($args->theme_location) && $args->theme_location === 'primary';
}

add_filter('nav_menu_css_class', function ($classes, $item, $args, $depth) {
    if (!bootscore_child_oegkm_is_primary_menu($args)) {
        return $classes;
    }

    $classes[] = 'nav-item';

    if (in_array('menu-item-has-children', $classes, true)) {
        $classes[] = 'dropdown';
    }

    return array_values(array_unique($classes));
}, 10, 4);

add_filter('nav_menu_link_attributes', function ($atts, $item, $args, $depth) {
    if (!bootscore_child_oegkm_is_primary_menu($args)) {
        return $atts;
    }

    $classes = isset($atts['class']) ? explode(' ', (string) $atts['class']) : [];
    $classes[] = $depth > 0 ? 'dropdown-item' : 'nav-link';

    if ($depth === 0 && in_array('menu-item-has-children', (array) $item->classes, true)) {
        $classes[] = 'dropdown-toggle';
        $atts['href'] = '#';
        $atts['role'] = 'button';
        $atts['data-bs-toggle'] = 'dropdown';
        $atts['aria-expanded'] = 'false';
    }

    $atts['class'] = implode(' ', array_values(array_unique(array_filter($classes))));

    return $atts;
}, 10, 4);

add_filter('nav_menu_submenu_css_class', function ($classes, $args, $depth) {
    if (!bootscore_child_oegkm_is_primary_menu($args)) {
        return $classes;
    }

    $classes[] = 'dropdown-menu';
    return array_values(array_unique($classes));
}, 10, 3);

add_filter('block_categories_all', function ($categories) {
    array_unshift($categories, [
        'slug'  => 'oegkm',
        'title' => __('OEGKM', 'bootscore-child-oegkm'),
        'icon'  => null,
    ]);
    return $categories;
});

add_action('init', function () {
    register_block_pattern_category('oegkm-sections', [
        'label' => __('OEGKM Sections', 'bootscore-child-oegkm'),
    ]);

    $blocks_dir = get_stylesheet_directory() . '/blocks';
    foreach (glob($blocks_dir . '/*', GLOB_ONLYDIR) ?: [] as $directory) {
        if (file_exists($directory . '/block.json')) {
            register_block_type($directory);
        }
    }

    if (function_exists('register_block_style')) {
        register_block_style('core/group', ['name' => 'oegkm-gradient-mint', 'label' => __('OEGKM Gradient Mint', 'bootscore-child-oegkm')]);
        register_block_style('core/group', ['name' => 'oegkm-gradient-lilac', 'label' => __('OEGKM Gradient Lilac', 'bootscore-child-oegkm')]);
        register_block_style('core/group', ['name' => 'oegkm-gradient-sun', 'label' => __('OEGKM Gradient Sun', 'bootscore-child-oegkm')]);
        register_block_style('core/group', ['name' => 'oegkm-gradient-soft', 'label' => __('OEGKM Gradient Soft', 'bootscore-child-oegkm')]);
        register_block_style('core/group', ['name' => 'oegkm-gradient-animated', 'label' => __('OEGKM Gradient Animated', 'bootscore-child-oegkm')]);
    }
});

add_action('customize_register', function ($wp_customize) {
    $wp_customize->add_section('oegkm_theme_options', [
        'title'    => __('OEGKM Theme Options', 'bootscore-child-oegkm'),
        'priority' => 30,
    ]);

    $settings = [
        'member_area_label' => __('Mitgliederbereich', 'bootscore-child-oegkm'),
        'member_area_url'   => '/login',
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

function bootscore_child_oegkm_default_logo_markup(): string {
    $logo_url = get_stylesheet_directory_uri() . '/assets/img/logo-oegkm.svg';
    return '<a class="custom-logo-link custom-logo-link--fallback" href="' . esc_url(home_url('/')) . '" rel="home" aria-label="' . esc_attr(get_bloginfo('name')) . '">' . '<img class="custom-logo custom-logo--fallback" src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '">' . '</a>';
}

function bootscore_child_oegkm_footer_logo_markup(): string {
    $logo_url = get_stylesheet_directory_uri() . '/assets/img/logo-oegkm-light.svg';
    return '<a class="custom-logo-link custom-logo-link--footer" href="' . esc_url(home_url('/')) . '" rel="home" aria-label="' . esc_attr(get_bloginfo('name')) . '">' . '<img class="custom-logo custom-logo--footer" src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '">' . '</a>';
}


add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'oegkm-navigation-fix',
        get_stylesheet_directory_uri() . '/assets/css/navigation-fix.css',
        [],
        filemtime(get_stylesheet_directory() . '/assets/css/navigation-fix.css')
    );
}, 999);
