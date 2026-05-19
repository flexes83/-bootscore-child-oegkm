<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<?php while (have_posts()) : the_post(); ?>
    <?php
    $deadline_label     = bootscore_child_oegkm_prize_deadline_label(get_the_ID());
    $has_future_deadline = bootscore_child_oegkm_prize_deadline_is_future(get_the_ID());

    bootscore_child_oegkm_render_theme_page_header([
        'title'      => get_the_title(),
        'intro'      => has_excerpt() ? get_the_excerpt() : '',
        'variant'    => 'mint-left',
        'labelledby' => 'oegkm-prize-single-title',
        'subtitle'   => ($has_future_deadline && $deadline_label) ? sprintf(__('Einreichungsschluss: %s', 'bootscore-child-oegkm'), $deadline_label) : '',
    ]);
    ?>

<main id="primary" class="site-main oegkm-prize-single-page">
    <article <?php post_class('oegkm-prize-single'); ?>>
        <section class="oegkm-prize-content-section">
            <div class="oegkm-prize-content entry-content">
                <?php the_content(); ?>
            </div>
        </section>
    </article>
</main>

<?php endwhile; ?>

<?php
get_footer();
