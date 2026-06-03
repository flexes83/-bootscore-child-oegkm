<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<?php while (have_posts()) : the_post(); ?>
    <?php
    $post_id          = get_the_ID();
    $date_label       = function_exists('bootscore_child_oegkm_event_date_range_label') ? bootscore_child_oegkm_event_date_range_label($post_id) : bootscore_child_oegkm_event_date_label($post_id);
    $location         = trim((string) get_post_meta($post_id, '_oegkm_event_location', true));
    $registration_url = trim((string) get_post_meta($post_id, '_oegkm_event_registration_url', true));
    $flyer_url        = trim((string) get_post_meta($post_id, '_oegkm_event_flyer_url', true));
    $program_label    = trim((string) get_post_meta($post_id, '_oegkm_event_program_label', true));
    $program_title    = trim((string) get_post_meta($post_id, '_oegkm_event_program_title', true));
    $program_text     = trim((string) get_post_meta($post_id, '_oegkm_event_program_text', true));
    $bottom_image_id  = absint(get_post_meta($post_id, '_oegkm_event_bottom_image_id', true));
    $tabs             = function_exists('bootscore_child_oegkm_event_tabs') ? bootscore_child_oegkm_event_tabs($post_id) : [];
    $subtitle_parts   = array_filter([$date_label, $location]);
    $subtitle         = implode(', ', $subtitle_parts);
    $intro            = has_excerpt() ? get_the_excerpt() : '';
    $arrow_svg        = function_exists('bootscore_child_oegkm_button_arrow_svg') ? bootscore_child_oegkm_button_arrow_svg() : '';
    $download_svg     = '<svg class="oegkm-button-arrow" viewBox="0 0 20 20" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg"><path d="M10 3v9m0 0 3.5-3.5M10 12 6.5 8.5M4 16h12" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    $event_label      = function_exists('bootscore_child_oegkm_event_oegkm_label') ? bootscore_child_oegkm_event_oegkm_label($post_id) : '';

    if (!$program_label) {
        $program_label = __('Programm', 'bootscore-child-oegkm');
    }

    $program_lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $program_text)), 'strlen'));

    bootscore_child_oegkm_render_theme_page_header([
        'title'      => get_the_title(),
        'subtitle'   => $subtitle,
        'intro'      => $intro,
        'variant'    => 'blue-lilac',
        'labelledby' => 'oegkm-event-single-title',
        'before_title' => $event_label,
    ]);
    ?>

    <main id="primary" class="site-main oegkm-event-single-page">
        <article <?php post_class('oegkm-event-single'); ?>>
            <?php if ($flyer_url) : ?>
                <div class="oegkm-event-single-actions">
                    <a class="oegkm-event-single-button oegkm-event-single-button--download" href="<?php echo esc_url($flyer_url); ?>">
                        <span><?php esc_html_e('Flyer herunterladen', 'bootscore-child-oegkm'); ?></span>
                        <?php echo $download_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php if (has_post_thumbnail() || $program_title || $program_lines || $registration_url) : ?>
                <section class="oegkm-event-single-program<?php echo has_post_thumbnail() ? '' : ' oegkm-event-single-program--no-image'; ?>" aria-label="<?php esc_attr_e('Programm', 'bootscore-child-oegkm'); ?>">
                    <div class="oegkm-event-single-program__inner">
                        <?php if (has_post_thumbnail()) : ?>
                            <figure class="oegkm-event-single-program__image">
                                <?php the_post_thumbnail('large'); ?>
                            </figure>
                        <?php endif; ?>

                        <div class="oegkm-event-single-program__content">
                            <p class="oegkm-event-single-kicker"><?php echo esc_html($program_label); ?></p>
                            <?php if ($program_title) : ?>
                                <h2 id="oegkm-event-single-program-title"><?php echo esc_html($program_title); ?></h2>
                            <?php endif; ?>

                            <?php if ($program_lines) : ?>
                                <div class="oegkm-event-single-program__text">
                                    <?php foreach ($program_lines as $line) : ?>
                                        <p><?php echo esc_html(ltrim($line, "-–• \t")); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($registration_url) : ?>
                                <a class="oegkm-event-single-button" href="<?php echo esc_url($registration_url); ?>" target="_blank" rel="noopener">
                                    <span><?php esc_html_e('Jetzt anmelden', 'bootscore-child-oegkm'); ?></span>
                                    <?php echo $arrow_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($tabs) : ?>
                <section class="oegkm-event-single-tabs" aria-labelledby="oegkm-event-single-tabs-title">
                    <div class="container">
                        <div class="oegkm-event-single-tabs__intro">
                            <p class="oegkm-event-single-kicker"><?php esc_html_e('Weitere Informationen', 'bootscore-child-oegkm'); ?></p>
                            <h2 id="oegkm-event-single-tabs-title"><?php esc_html_e('Organisatorische Details und weitere Informationen', 'bootscore-child-oegkm'); ?></h2>
                        </div>

                        <div class="oegkm-event-single-tabs__layout">
                            <div class="oegkm-event-single-tabs__nav" role="tablist" aria-label="<?php esc_attr_e('Veranstaltungsinformationen', 'bootscore-child-oegkm'); ?>">
                                <?php foreach ($tabs as $index => $tab) : ?>
                                    <?php
                                    $tab_title = isset($tab['title']) ? trim((string) $tab['title']) : '';
                                    if ($tab_title === '') {
                                        continue;
                                    }
                                    $is_active = $index === 0;
                                    ?>
                                    <button
                                        class="oegkm-event-single-tabs__tab<?php echo $is_active ? ' is-active' : ''; ?>"
                                        type="button"
                                        role="tab"
                                        id="oegkm-event-tab-<?php echo esc_attr($index); ?>"
                                        aria-controls="oegkm-event-panel-<?php echo esc_attr($index); ?>"
                                        aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                                        data-oegkm-event-tab="<?php echo esc_attr($index); ?>"
                                    >
                                        <?php echo esc_html($tab_title); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <div class="oegkm-event-single-tabs__panels">
                                <?php foreach ($tabs as $index => $tab) : ?>
                                    <?php
                                    $sections = isset($tab['sections']) && is_array($tab['sections']) ? $tab['sections'] : [];
                                    $is_active = $index === 0;
                                    ?>
                                    <div
                                        class="oegkm-event-single-tabs__panel<?php echo $is_active ? ' is-active' : ''; ?>"
                                        id="oegkm-event-panel-<?php echo esc_attr($index); ?>"
                                        role="tabpanel"
                                        aria-labelledby="oegkm-event-tab-<?php echo esc_attr($index); ?>"
                                        data-oegkm-event-panel="<?php echo esc_attr($index); ?>"
                                        <?php echo $is_active ? '' : 'hidden'; ?>
                                    >
                                        <?php foreach ($sections as $section) : ?>
                                            <?php
                                            $section_heading = isset($section['heading']) ? trim((string) $section['heading']) : '';
                                            $section_body    = isset($section['body']) ? trim(bootscore_child_oegkm_normalize_event_tab_body((string) $section['body'])) : '';
                                            ?>
                                            <div class="oegkm-event-single-tabs__section">
                                                <?php if ($section_heading) : ?>
                                                    <h3><?php echo esc_html($section_heading); ?></h3>
                                                <?php endif; ?>
                                                <?php if ($section_body) : ?>
                                                    <div class="oegkm-event-single-tabs__text">
                                                        <?php echo wpautop(bootscore_child_oegkm_sanitize_event_tab_body($section_body)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($bottom_image_id) : ?>
                <figure class="oegkm-event-single-bottom-image">
                    <?php echo wp_get_attachment_image($bottom_image_id, 'full'); ?>
                </figure>
            <?php endif; ?>
        </article>
    </main>
<?php endwhile; ?>

<?php
get_footer();
