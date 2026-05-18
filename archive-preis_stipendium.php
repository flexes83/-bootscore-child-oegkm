<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

bootscore_child_oegkm_render_theme_page_header([
    'title' => __('Preise & Stipendien', 'bootscore-child-oegkm'),
    'intro' => __('Aktuelle Ausschreibungen, Fördermöglichkeiten und Auszeichnungen der ÖGKM im Überblick. Vergangene Einträge finden Sie im Archiv.', 'bootscore-child-oegkm'),
    'variant' => 'mint-left',
    'labelledby' => 'oegkm-prizes-title',
]);

$today = current_time('Y-m-d');

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
                <h2 id="oegkm-prizes-current-title"><?php esc_html_e('Aktuelle Ausschreibungen', 'bootscore-child-oegkm'); ?></h2>

                <?php if ($current_prizes->have_posts()) : ?>
                    <div class="oegkm-prizes-grid">
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
                <h2 id="oegkm-prizes-archive-title"><?php esc_html_e('Archiv', 'bootscore-child-oegkm'); ?></h2>

                <?php if ($past_prizes->have_posts()) : ?>
                    <div class="oegkm-prizes-archive">
                        <?php
                        $current_year = '';
                        while ($past_prizes->have_posts()) :
                            $past_prizes->the_post();
                            $year = bootscore_child_oegkm_prize_year(get_the_ID());
                            if ($year && $year !== $current_year) :
                                if ($current_year) {
                                    echo '</div>';
                                }
                                $current_year = $year;
                                ?>
                                <h3 class="oegkm-prizes-archive__year"><?php echo esc_html($current_year); ?></h3>
                                <div class="oegkm-prizes-grid oegkm-prizes-grid--archive">
                            <?php endif; ?>
                            <?php bootscore_child_oegkm_render_prize_card(get_the_ID()); ?>
                        <?php endwhile; ?>
                        <?php if ($current_year) : ?>
                            </div>
                        <?php endif; ?>
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
