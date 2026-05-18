<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
?>
<main id="primary" class="site-main oegkm-homepage">
    <?php while (have_posts()) : the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; ?>
</main>
<?php get_footer(); ?>
