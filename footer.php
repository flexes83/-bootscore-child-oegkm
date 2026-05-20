<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<footer id="colophon" class="oegkm-site-footer">
    <div class="container">
        <div class="oegkm-footer-inner">
            <div class="oegkm-footer-branding">
                <div class="oegkm-footer-logo">
                    <a class="custom-logo-link custom-logo-link--footer" href="<?php echo esc_url(home_url('/')); ?>" rel="home" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>"><img class="custom-logo custom-logo--footer" src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/logo-oegkm-light.svg'); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"></a>
                </div>
                <div class="oegkm-footer-text">
                    <?php echo bootscore_child_oegkm_footer_text(); ?>
                </div>
            </div>

            <div class="oegkm-footer-links">
                <?php if (has_nav_menu('footer')) : ?>
                    <nav aria-label="<?php esc_attr_e('Footer Navigation', 'bootscore-child-oegkm'); ?>">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'footer',
                            'container'      => false,
                            'menu_class'     => 'oegkm-footer-menu',
                            'fallback_cb'    => false,
                            'depth'          => 1,
                        ]);
                        ?>
                    </nav>
                <?php else : ?>
                    <div class="oegkm-inline-links">
                        <a href="<?php echo bootscore_child_oegkm_statutes_url(); ?>"><?php esc_html_e('Statuten', 'bootscore-child-oegkm'); ?></a>
                        <a href="<?php echo bootscore_child_oegkm_privacy_url(); ?>"><?php esc_html_e('Datenschutz', 'bootscore-child-oegkm'); ?></a>
                        <a href="<?php echo bootscore_child_oegkm_imprint_url(); ?>"><?php esc_html_e('Impressum', 'bootscore-child-oegkm'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
