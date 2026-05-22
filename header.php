<?php
if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="oegkm-site-header">
    <div class="container">
        <nav class="navbar navbar-expand-xl oegkm-navbar" aria-label="<?php esc_attr_e('Hauptnavigation', 'bootscore-child-oegkm'); ?>">
            <a class="custom-logo-link custom-logo-link--header oegkm-header-brand" href="<?php echo esc_url(home_url('/')); ?>" rel="home" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <img class="custom-logo custom-logo--header" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/logo-oegkm.svg'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
            </a>

            <button class="navbar-toggler oegkm-navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#oegkmMainNavigation" aria-controls="oegkmMainNavigation" aria-expanded="false" aria-label="<?php esc_attr_e('Navigation umschalten', 'bootscore-child-oegkm'); ?>">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse oegkm-navbar-collapse" id="oegkmMainNavigation">
                <div class="oegkm-primary-nav">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container'      => false,
                        'menu_class'     => 'navbar-nav oegkm-nav-menu',
                        'fallback_cb'    => false,
                        'depth'          => 2,
                    ]);
                    ?>
                </div>

                <div class="oegkm-header-actions">
                    <div class="oegkm-search-shell" data-oegkm-search-shell>
                        <button class="oegkm-search-toggle" type="button" aria-expanded="false" aria-controls="oegkm-header-search" aria-label="<?php esc_attr_e('Suche öffnen', 'bootscore-child-oegkm'); ?>">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <circle cx="11" cy="11" r="6.5"></circle>
                                <line x1="16" y1="16" x2="21" y2="21"></line>
                            </svg>
                        </button>
                        <form class="oegkm-search-form" id="oegkm-header-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                            <label class="screen-reader-text" for="oegkm-search-input"><?php esc_html_e('Suche nach:', 'bootscore-child-oegkm'); ?></label>
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <circle cx="11" cy="11" r="6.5"></circle>
                                <line x1="16" y1="16" x2="21" y2="21"></line>
                            </svg>
                            <input id="oegkm-search-input" type="search" name="s" placeholder="<?php esc_attr_e('Suche', 'bootscore-child-oegkm'); ?>">
                        </form>
                    </div>
                    <?php
                    $oegkm_header_member_url = function_exists('bootscore_child_oegkm_header_member_url') ? bootscore_child_oegkm_header_member_url() : bootscore_child_oegkm_member_area_url();
                    ?>
                    <a class="oegkm-member-button" href="<?php echo esc_url($oegkm_header_member_url); ?>">
                        <span><?php echo esc_html(bootscore_child_oegkm_member_area_label()); ?></span>
                        <span aria-hidden="true">→</span>
                    </a>
                    <?php if (is_user_logged_in()) : ?>
                        <a class="oegkm-header-logout-link" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>"><?php esc_html_e('Abmelden', 'bootscore-child-oegkm'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </div>
</header>
<?php
if (function_exists('bootscore_child_oegkm_render_current_page_header')) {
    bootscore_child_oegkm_render_current_page_header();
}
?>
