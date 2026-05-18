<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

bootscore_child_oegkm_render_theme_page_header([
    'title' => __('Preise und Stipendien', 'bootscore-child-oegkm'),
    'intro' => __('Die ÖGKM vergibt Preise und Stipendien zur Förderung von Forschung, Nachwuchs und wissenschaftlichem Austausch im Bereich Knochen- und Mineralstoffwechsel. Entdecken Sie aktuelle Ausschreibungen und Fördermöglichkeiten.', 'bootscore-child-oegkm'),
    'variant' => 'mint-left',
    'labelledby' => 'oegkm-prizes-title',
]);

$today = current_time('Y-m-d');
$current_year = current_time('Y');

$current_prizes = new WP_Query([
    'post_type'      => 'preis_stipendium',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
    'meta_key'       => '_oegkm_prize_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => [
        [
            'key'     => '_oegkm_prize_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATE',
        ],
    ],
]);

$past_prizes = new WP_Query([
    'post_type'      => 'preis_stipendium',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'meta_key'       => '_oegkm_prize_date',
    'orderby'        => 'meta_value',
    'order'          => 'DESC',
    'meta_query'     => [
        [
            'key'     => '_oegkm_prize_date',
            'value'   => $today,
            'compare' => '<',
            'type'    => 'DATE',
        ],
    ],
]);
?>

<main id="primary" class="site-main oegkm-prizes-page">
    <section class="oegkm-prizes-section" aria-labelledby="oegkm-prizes-current-title">
        <div class="container">
            <div class="oegkm-prizes-list-wrap">
                <h2 id="oegkm-prizes-current-title"><?php echo esc_html(sprintf(__('Preise und Stipendien %s', 'bootscore-child-oegkm'), $current_year)); ?></h2>

                <?php if ($current_prizes->have_posts()) : ?>
                    <div class="oegkm-prizes-list">
                        <?php while ($current_prizes->have_posts()) : $current_prizes->the_post(); ?>
                            <?php bootscore_child_oegkm_render_prize_card(get_the_ID()); ?>
                        <?php endwhile; ?>
                    </div>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <div class="oegkm-prizes-empty">
                        <h3><?php esc_html_e('Aktuell sind keine laufenden Ausschreibungen eingetragen.', 'bootscore-child-oegkm'); ?></h3>
                        <p><?php esc_html_e('Vergangene Preise und Stipendien finden Sie weiter unten im Archiv.', 'bootscore-child-oegkm'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="oegkm-prizes-archive-section" aria-labelledby="oegkm-prizes-archive-title">
        <div class="container">
            <div class="oegkm-prizes-list-wrap">
                <h2 id="oegkm-prizes-archive-title"><?php esc_html_e('Preise und Stipendien Ausschreibungsarchiv', 'bootscore-child-oegkm'); ?></h2>

                <?php if ($past_prizes->have_posts()) : ?>
                    <div class="oegkm-prizes-archive">
                        <div class="oegkm-prizes-list oegkm-prizes-list--archive">
                        <?php while ($past_prizes->have_posts()) : $past_prizes->the_post(); ?>
                            <?php bootscore_child_oegkm_render_prize_card(get_the_ID()); ?>
                        <?php endwhile; ?>
                        </div>
                    </div>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <div class="oegkm-prizes-empty">
                        <h3><?php esc_html_e('Noch keine archivierten Einträge vorhanden.', 'bootscore-child-oegkm'); ?></h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
