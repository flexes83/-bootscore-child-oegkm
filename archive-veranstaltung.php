<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

bootscore_child_oegkm_render_theme_page_header([
    'title' => __('Veranstaltungskalender der ÖGKM', 'bootscore-child-oegkm'),
    'intro' => __('Entdecken Sie aktuelle Veranstaltungen der ÖGKM – von Kongressen und Fortbildungen bis zu wissenschaftlichen Meetings. Nutzen Sie die Gelegenheit zum fachlichen Austausch und zur Vernetzung.', 'bootscore-child-oegkm'),
    'variant' => 'mint-left',
    'labelledby' => 'oegkm-events-title',
]);
?>

<main id="primary" class="site-main oegkm-events-page oegkm-events-page--calendar">
    <section class="oegkm-events-calendar-section" aria-label="<?php esc_attr_e('Anstehende Veranstaltungen', 'bootscore-child-oegkm'); ?>">
        <div class="container">
            <div class="oegkm-events-calendar-list-wrap">
                <h2><?php esc_html_e('Anstehende Veranstaltungen', 'bootscore-child-oegkm'); ?></h2>

                <?php if (have_posts()) : ?>
                    <div class="oegkm-events-calendar-list">
                        <?php while (have_posts()) : the_post(); ?>
                            <?php
                            $date_label = bootscore_child_oegkm_event_date_label(get_the_ID());
                            $location   = (string) get_post_meta(get_the_ID(), '_oegkm_event_location', true);
                            ?>
                            <article <?php post_class('oegkm-event-calendar-item'); ?>>
                                <?php if (has_post_thumbnail()) : ?>
                                    <a class="oegkm-event-calendar-item__image" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(get_the_title()); ?>">
                                        <?php the_post_thumbnail('large'); ?>
                                    </a>
                                <?php endif; ?>

                                <div class="oegkm-event-calendar-item__content">
                                    <?php if ($date_label) : ?>
                                        <div class="oegkm-event-calendar-item__date"><?php echo esc_html($date_label); ?></div>
                                    <?php endif; ?>

                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

                                    <?php if ($location) : ?>
                                        <div class="oegkm-event-calendar-item__location"><?php echo esc_html($location); ?></div>
                                    <?php endif; ?>

                                    <?php if (has_excerpt()) : ?>
                                        <p><?php echo esc_html(get_the_excerpt()); ?></p>
                                    <?php else : ?>
                                        <p><?php echo esc_html(wp_trim_words(get_the_content(null, false, get_the_ID()), 28)); ?></p>
                                    <?php endif; ?>

                                    <a class="oegkm-event-calendar-item__button" href="<?php the_permalink(); ?>">
                                        <?php esc_html_e('Mehr erfahren', 'bootscore-child-oegkm'); ?> <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>

                    <div class="oegkm-events-pagination">
                        <?php the_posts_pagination([
                            'mid_size'  => 2,
                            'prev_text' => __('Zurück', 'bootscore-child-oegkm'),
                            'next_text' => __('Weiter', 'bootscore-child-oegkm'),
                        ]); ?>
                    </div>
                <?php else : ?>
                    <div class="oegkm-events-empty">
                        <h2><?php esc_html_e('Aktuell sind keine kommenden Veranstaltungen eingetragen.', 'bootscore-child-oegkm'); ?></h2>
                        <p><?php esc_html_e('Bitte schauen Sie zu einem späteren Zeitpunkt wieder vorbei.', 'bootscore-child-oegkm'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
