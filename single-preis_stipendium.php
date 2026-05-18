<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main oegkm-prize-single-page">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $date_label       = bootscore_child_oegkm_prize_date_label(get_the_ID());
        $deadline_label   = bootscore_child_oegkm_prize_deadline_label(get_the_ID());
        $type             = (string) get_post_meta(get_the_ID(), '_oegkm_prize_type', true);
        $amount           = (string) get_post_meta(get_the_ID(), '_oegkm_prize_amount', true);
        $application_url  = (string) get_post_meta(get_the_ID(), '_oegkm_prize_application_url', true);
        $archive_url      = get_post_type_archive_link('preis_stipendium');
        ?>

        <article <?php post_class('oegkm-prize-single'); ?>>
            <section class="oegkm-prize-detail-hero oegkm-soft-hero">
                <div class="container">
                    <?php if ($archive_url) : ?>
                        <a class="oegkm-back-link" href="<?php echo esc_url($archive_url); ?>">← <?php esc_html_e('Alle Preise & Stipendien', 'bootscore-child-oegkm'); ?></a>
                    <?php endif; ?>
                    <div class="oegkm-soft-hero__inner">
                        <?php if ($type) : ?>
                            <p class="oegkm-prize-single__kicker"><?php echo esc_html($type); ?></p>
                        <?php endif; ?>
                        <h1><?php the_title(); ?></h1>
                        <?php if (has_excerpt()) : ?>
                            <p><?php echo esc_html(get_the_excerpt()); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="oegkm-prize-content-section">
                <div class="container">
                    <div class="oegkm-prize-detail-layout">
                        <aside class="oegkm-prize-facts" aria-label="<?php esc_attr_e('Details', 'bootscore-child-oegkm'); ?>">
                            <?php if ($date_label) : ?>
                                <div class="oegkm-prize-fact">
                                    <small><?php esc_html_e('Datum', 'bootscore-child-oegkm'); ?></small>
                                    <strong><?php echo esc_html($date_label); ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if ($deadline_label) : ?>
                                <div class="oegkm-prize-fact">
                                    <small><?php esc_html_e('Einreichfrist', 'bootscore-child-oegkm'); ?></small>
                                    <strong><?php echo esc_html($deadline_label); ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if ($amount) : ?>
                                <div class="oegkm-prize-fact">
                                    <small><?php esc_html_e('Dotierung', 'bootscore-child-oegkm'); ?></small>
                                    <strong><?php echo esc_html($amount); ?></strong>
                                </div>
                            <?php endif; ?>

                            <?php if ($application_url) : ?>
                                <a class="oegkm-prize-button oegkm-prize-button-wide" href="<?php echo esc_url($application_url); ?>" target="_blank" rel="noopener">
                                    <?php esc_html_e('Zur Einreichung', 'bootscore-child-oegkm'); ?> <?php echo bootscore_child_oegkm_button_arrow_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </a>
                            <?php endif; ?>
                        </aside>

                        <div class="oegkm-prize-content-wrap">
                            <figure class="oegkm-prize-featured-image">
                                <?php bootscore_child_oegkm_render_prize_thumbnail(get_the_ID()); ?>
                            </figure>

                            <div class="oegkm-prize-content entry-content">
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
