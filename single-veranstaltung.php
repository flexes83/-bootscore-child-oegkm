<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main oegkm-event-single-page">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $date_label       = bootscore_child_oegkm_event_date_label(get_the_ID());
        $location         = (string) get_post_meta(get_the_ID(), '_oegkm_event_location', true);
        $registration_url = (string) get_post_meta(get_the_ID(), '_oegkm_event_registration_url', true);
        ?>

        <article <?php post_class('oegkm-event-single'); ?>>
            <section class="oegkm-event-detail-hero oegkm-soft-hero">
                <div class="container">
                    <a class="oegkm-back-link" href="<?php echo esc_url(get_post_type_archive_link('veranstaltung')); ?>">← <?php esc_html_e('Alle Veranstaltungen', 'bootscore-child-oegkm'); ?></a>
                    <div class="oegkm-soft-hero__inner">
                        <h1><?php the_title(); ?></h1>
                        <?php if (has_excerpt()) : ?>
                            <p><?php echo esc_html(get_the_excerpt()); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="oegkm-event-content-section">
                <div class="container">
                    <div class="oegkm-event-detail-layout">
                        <aside class="oegkm-event-facts" aria-label="<?php esc_attr_e('Veranstaltungsdetails', 'bootscore-child-oegkm'); ?>">
                            <?php if ($date_label) : ?>
                                <div class="oegkm-event-fact">
                                    <small><?php esc_html_e('Termin', 'bootscore-child-oegkm'); ?></small>
                                    <strong><?php echo esc_html($date_label); ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if ($location) : ?>
                                <div class="oegkm-event-fact">
                                    <small><?php esc_html_e('Ort', 'bootscore-child-oegkm'); ?></small>
                                    <strong><?php echo esc_html($location); ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if ($registration_url) : ?>
                                <a class="oegkm-event-button oegkm-event-button-wide" href="<?php echo esc_url($registration_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Zur Anmeldung', 'bootscore-child-oegkm'); ?></a>
                            <?php endif; ?>
                        </aside>

                        <div class="oegkm-event-content-wrap">
                            <?php if (has_post_thumbnail()) : ?>
                                <figure class="oegkm-event-featured-image">
                                    <?php the_post_thumbnail('large'); ?>
                                </figure>
                            <?php endif; ?>

                            <div class="oegkm-event-content entry-content">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </article>
    <?php endwhile; ?>
</main>

<?php
get_footer();
