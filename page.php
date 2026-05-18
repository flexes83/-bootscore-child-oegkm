<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) :
    while (have_posts()) :
        the_post();

        ?>
        <main id="primary" <?php post_class('site-main oegkm-page-main'); ?>>
            <div class="container oegkm-page-content-container">
                <?php the_content(); ?>
            </div>
        </main>
        <?php
    endwhile;
endif;

get_footer();
