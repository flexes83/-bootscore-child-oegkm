<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OEGKM Veranstaltungen
 *
 * Custom Post Type + Meta-Felder für einfache Veranstaltungspflege.
 */

add_action('init', function () {
    $labels = [
        'name'                  => __('Veranstaltungen', 'bootscore-child-oegkm'),
        'singular_name'         => __('Veranstaltung', 'bootscore-child-oegkm'),
        'menu_name'             => __('Veranstaltungen', 'bootscore-child-oegkm'),
        'name_admin_bar'        => __('Veranstaltung', 'bootscore-child-oegkm'),
        'add_new'               => __('Neu hinzufügen', 'bootscore-child-oegkm'),
        'add_new_item'          => __('Neue Veranstaltung hinzufügen', 'bootscore-child-oegkm'),
        'new_item'              => __('Neue Veranstaltung', 'bootscore-child-oegkm'),
        'edit_item'             => __('Veranstaltung bearbeiten', 'bootscore-child-oegkm'),
        'view_item'             => __('Veranstaltung ansehen', 'bootscore-child-oegkm'),
        'all_items'             => __('Alle Veranstaltungen', 'bootscore-child-oegkm'),
        'search_items'          => __('Veranstaltungen suchen', 'bootscore-child-oegkm'),
        'not_found'             => __('Keine Veranstaltungen gefunden.', 'bootscore-child-oegkm'),
        'not_found_in_trash'    => __('Keine Veranstaltungen im Papierkorb gefunden.', 'bootscore-child-oegkm'),
        'featured_image'        => __('Veranstaltungsbild', 'bootscore-child-oegkm'),
        'set_featured_image'    => __('Veranstaltungsbild festlegen', 'bootscore-child-oegkm'),
        'remove_featured_image' => __('Veranstaltungsbild entfernen', 'bootscore-child-oegkm'),
    ];

    register_post_type('veranstaltung', [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-calendar-alt',
        'has_archive'        => 'veranstaltungen',
        'rewrite'            => [
            'slug'       => 'veranstaltungen',
            'with_front' => false,
        ],
        'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
    ]);
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'oegkm_event_details',
        __('Veranstaltungsdetails', 'bootscore-child-oegkm'),
        'bootscore_child_oegkm_render_event_meta_box',
        'veranstaltung',
        'normal',
        'high'
    );
});

function bootscore_child_oegkm_render_event_meta_box(WP_Post $post): void {
    wp_nonce_field('oegkm_save_event_details', 'oegkm_event_details_nonce');

    $start_date       = get_post_meta($post->ID, '_oegkm_event_start_date', true);
    $end_date         = get_post_meta($post->ID, '_oegkm_event_end_date', true);
    $start_time       = get_post_meta($post->ID, '_oegkm_event_start_time', true);
    $end_time         = get_post_meta($post->ID, '_oegkm_event_end_time', true);
    $location         = get_post_meta($post->ID, '_oegkm_event_location', true);
    $registration_url = get_post_meta($post->ID, '_oegkm_event_registration_url', true);
    $flyer_url        = get_post_meta($post->ID, '_oegkm_event_flyer_url', true);
    $program_label    = get_post_meta($post->ID, '_oegkm_event_program_label', true);
    $program_title    = get_post_meta($post->ID, '_oegkm_event_program_title', true);
    $program_text     = get_post_meta($post->ID, '_oegkm_event_program_text', true);
    $bottom_image_id  = get_post_meta($post->ID, '_oegkm_event_bottom_image_id', true);
    $tabs             = bootscore_child_oegkm_event_tabs($post->ID);

    if (!$program_label) {
        $program_label = __('Programm', 'bootscore-child-oegkm');
    }

    ?>
    <style>
        .oegkm-event-admin-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:8px}.oegkm-event-admin-field label{display:block;font-weight:600;margin-bottom:6px}.oegkm-event-admin-field input,.oegkm-event-admin-field textarea{width:100%}.oegkm-event-admin-field--full{grid-column:1/-1}.oegkm-event-admin-tabs{display:grid;gap:18px}.oegkm-event-admin-tab{border:1px solid #dcdcde;background:#fff;padding:16px}.oegkm-event-admin-tab h4{margin:0 0 12px}.oegkm-event-admin-section{display:grid;grid-template-columns:minmax(180px,.8fr) minmax(0,1.6fr);gap:12px;margin-top:12px;padding-top:12px;border-top:1px solid #f0f0f1}.oegkm-event-admin-help{margin:4px 0 0;color:#646970}.oegkm-event-admin-editor .wp-editor-wrap{max-width:100%}.oegkm-event-admin-editor .wp-editor-area{min-height:110px}@media(max-width:782px){.oegkm-event-admin-grid{grid-template-columns:1fr}.oegkm-event-admin-field--full{grid-column:auto}.oegkm-event-admin-section{grid-template-columns:1fr}}
    </style>
    <div class="oegkm-event-admin-grid">
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_start_date"><?php esc_html_e('Startdatum', 'bootscore-child-oegkm'); ?> *</label>
            <input type="date" id="oegkm_event_start_date" name="oegkm_event_start_date" value="<?php echo esc_attr($start_date); ?>">
        </p>
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_end_date"><?php esc_html_e('Enddatum', 'bootscore-child-oegkm'); ?></label>
            <input type="date" id="oegkm_event_end_date" name="oegkm_event_end_date" value="<?php echo esc_attr($end_date); ?>">
        </p>
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_start_time"><?php esc_html_e('Beginn', 'bootscore-child-oegkm'); ?></label>
            <input type="time" id="oegkm_event_start_time" name="oegkm_event_start_time" value="<?php echo esc_attr($start_time); ?>">
        </p>
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_end_time"><?php esc_html_e('Ende', 'bootscore-child-oegkm'); ?></label>
            <input type="time" id="oegkm_event_end_time" name="oegkm_event_end_time" value="<?php echo esc_attr($end_time); ?>">
        </p>
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_location"><?php esc_html_e('Ort', 'bootscore-child-oegkm'); ?></label>
            <input type="text" id="oegkm_event_location" name="oegkm_event_location" value="<?php echo esc_attr($location); ?>" placeholder="<?php esc_attr_e('z. B. Wien / Online', 'bootscore-child-oegkm'); ?>">
        </p>
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_registration_url"><?php esc_html_e('Anmeldelink', 'bootscore-child-oegkm'); ?></label>
            <input type="url" id="oegkm_event_registration_url" name="oegkm_event_registration_url" value="<?php echo esc_attr($registration_url); ?>" placeholder="https://">
        </p>
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_flyer_url"><?php esc_html_e('Flyer-Download URL', 'bootscore-child-oegkm'); ?></label>
            <input type="url" id="oegkm_event_flyer_url" name="oegkm_event_flyer_url" value="<?php echo esc_attr($flyer_url); ?>" placeholder="https://">
        </p>
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_bottom_image_id"><?php esc_html_e('Abschlussbild Attachment-ID', 'bootscore-child-oegkm'); ?></label>
            <input type="number" id="oegkm_event_bottom_image_id" name="oegkm_event_bottom_image_id" value="<?php echo esc_attr($bottom_image_id); ?>" min="0" step="1">
        </p>
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_program_label"><?php esc_html_e('Programm Label', 'bootscore-child-oegkm'); ?></label>
            <input type="text" id="oegkm_event_program_label" name="oegkm_event_program_label" value="<?php echo esc_attr($program_label); ?>">
        </p>
        <p class="oegkm-event-admin-field">
            <label for="oegkm_event_program_title"><?php esc_html_e('Programm Überschrift', 'bootscore-child-oegkm'); ?></label>
            <input type="text" id="oegkm_event_program_title" name="oegkm_event_program_title" value="<?php echo esc_attr($program_title); ?>">
        </p>
        <p class="oegkm-event-admin-field oegkm-event-admin-field--full">
            <label for="oegkm_event_program_text"><?php esc_html_e('Programm Text / Liste', 'bootscore-child-oegkm'); ?></label>
            <textarea id="oegkm_event_program_text" name="oegkm_event_program_text" rows="7"><?php echo esc_textarea($program_text); ?></textarea>
        </p>
        <div class="oegkm-event-admin-field oegkm-event-admin-field--full">
            <label><?php esc_html_e('Tabsektion', 'bootscore-child-oegkm'); ?></label>
            <p class="oegkm-event-admin-help"><?php esc_html_e('Leere Überschriften und Texte werden nicht ausgegeben. Zusätzliche Bereiche erscheinen automatisch, wenn bereits mehr Inhalte gespeichert sind.', 'bootscore-child-oegkm'); ?></p>
            <div class="oegkm-event-admin-tabs">
                <?php foreach ($tabs as $tab_index => $tab) : ?>
                    <?php
                    $tab_title = isset($tab['title']) ? (string) $tab['title'] : '';
                    $sections = isset($tab['sections']) && is_array($tab['sections']) ? $tab['sections'] : [];
                    $section_count = max(4, count($sections));
                    ?>
                    <div class="oegkm-event-admin-tab">
                        <h4><?php echo esc_html(sprintf(__('Tab %d', 'bootscore-child-oegkm'), $tab_index + 1)); ?></h4>
                        <label for="oegkm_event_tabs_<?php echo esc_attr($tab_index); ?>_title"><?php esc_html_e('Tabtitel', 'bootscore-child-oegkm'); ?></label>
                        <input type="text" id="oegkm_event_tabs_<?php echo esc_attr($tab_index); ?>_title" name="oegkm_event_tabs[<?php echo esc_attr($tab_index); ?>][title]" value="<?php echo esc_attr($tab_title); ?>">

                        <?php for ($section_index = 0; $section_index < $section_count; $section_index++) : ?>
                            <?php
                            $section = $sections[$section_index] ?? [];
                            $section_heading = isset($section['heading']) ? (string) $section['heading'] : '';
                            $section_body = isset($section['body']) ? (string) $section['body'] : '';
                            ?>
                            <div class="oegkm-event-admin-section">
                                <p>
                                    <label for="oegkm_event_tabs_<?php echo esc_attr($tab_index); ?>_<?php echo esc_attr($section_index); ?>_heading"><?php echo esc_html(sprintf(__('Abschnitt %d Überschrift', 'bootscore-child-oegkm'), $section_index + 1)); ?></label>
                                    <input type="text" id="oegkm_event_tabs_<?php echo esc_attr($tab_index); ?>_<?php echo esc_attr($section_index); ?>_heading" name="oegkm_event_tabs[<?php echo esc_attr($tab_index); ?>][sections][<?php echo esc_attr($section_index); ?>][heading]" value="<?php echo esc_attr($section_heading); ?>">
                                </p>
                                <p>
                                    <label for="oegkm_event_tabs_<?php echo esc_attr($tab_index); ?>_<?php echo esc_attr($section_index); ?>_body"><?php echo esc_html(sprintf(__('Abschnitt %d Text', 'bootscore-child-oegkm'), $section_index + 1)); ?></label>
                                    <span class="oegkm-event-admin-editor">
                                        <?php
                                        wp_editor($section_body, 'oegkm_event_tabs_' . $tab_index . '_' . $section_index . '_body', [
                                            'textarea_name' => 'oegkm_event_tabs[' . $tab_index . '][sections][' . $section_index . '][body]',
                                            'textarea_rows' => 5,
                                            'media_buttons' => false,
                                            'tinymce' => [
                                                'toolbar1' => 'bold,link,bullist',
                                                'toolbar2' => '',
                                                'block_formats' => 'Paragraph=p',
                                            ],
                                            'quicktags' => [
                                                'buttons' => 'strong,link,ul,li',
                                            ],
                                        ]);
                                        ?>
                                    </span>
                                </p>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}

add_action('save_post_veranstaltung', function (int $post_id) {
    if (!isset($_POST['oegkm_event_details_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['oegkm_event_details_nonce'])), 'oegkm_save_event_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $start_date       = isset($_POST['oegkm_event_start_date']) ? sanitize_text_field(wp_unslash($_POST['oegkm_event_start_date'])) : '';
    $end_date         = isset($_POST['oegkm_event_end_date']) ? sanitize_text_field(wp_unslash($_POST['oegkm_event_end_date'])) : '';
    $start_time       = isset($_POST['oegkm_event_start_time']) ? sanitize_text_field(wp_unslash($_POST['oegkm_event_start_time'])) : '';
    $end_time         = isset($_POST['oegkm_event_end_time']) ? sanitize_text_field(wp_unslash($_POST['oegkm_event_end_time'])) : '';
    $location         = isset($_POST['oegkm_event_location']) ? sanitize_text_field(wp_unslash($_POST['oegkm_event_location'])) : '';
    $registration_url = isset($_POST['oegkm_event_registration_url']) ? esc_url_raw(wp_unslash($_POST['oegkm_event_registration_url'])) : '';
    $flyer_url        = isset($_POST['oegkm_event_flyer_url']) ? esc_url_raw(wp_unslash($_POST['oegkm_event_flyer_url'])) : '';
    $program_label    = isset($_POST['oegkm_event_program_label']) ? sanitize_text_field(wp_unslash($_POST['oegkm_event_program_label'])) : '';
    $program_title    = isset($_POST['oegkm_event_program_title']) ? sanitize_text_field(wp_unslash($_POST['oegkm_event_program_title'])) : '';
    $program_text     = isset($_POST['oegkm_event_program_text']) ? sanitize_textarea_field(wp_unslash($_POST['oegkm_event_program_text'])) : '';
    $bottom_image_id  = isset($_POST['oegkm_event_bottom_image_id']) ? absint($_POST['oegkm_event_bottom_image_id']) : 0;
    $tabs_input       = isset($_POST['oegkm_event_tabs']) && is_array($_POST['oegkm_event_tabs']) ? wp_unslash($_POST['oegkm_event_tabs']) : [];

    if ($start_date && !$end_date) {
        $end_date = $start_date;
    }

    update_post_meta($post_id, '_oegkm_event_start_date', $start_date);
    update_post_meta($post_id, '_oegkm_event_end_date', $end_date);
    update_post_meta($post_id, '_oegkm_event_start_time', $start_time);
    update_post_meta($post_id, '_oegkm_event_end_time', $end_time);
    update_post_meta($post_id, '_oegkm_event_location', $location);
    update_post_meta($post_id, '_oegkm_event_registration_url', $registration_url);
    update_post_meta($post_id, '_oegkm_event_flyer_url', $flyer_url);
    update_post_meta($post_id, '_oegkm_event_program_label', $program_label);
    update_post_meta($post_id, '_oegkm_event_program_title', $program_title);
    update_post_meta($post_id, '_oegkm_event_program_text', $program_text);
    update_post_meta($post_id, '_oegkm_event_bottom_image_id', $bottom_image_id);

    update_post_meta($post_id, '_oegkm_event_tabs_json', wp_json_encode(bootscore_child_oegkm_sanitize_event_tabs($tabs_input), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
});

add_action('pre_get_posts', function (WP_Query $query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_post_type_archive('veranstaltung')) {
        $today = current_time('Y-m-d');

        $query->set('posts_per_page', 12);
        $query->set('meta_key', '_oegkm_event_start_date');
        $query->set('orderby', 'meta_value');
        $query->set('order', 'ASC');
        $query->set('meta_query', [
            [
                'key'     => '_oegkm_event_end_date',
                'value'   => $today,
                'compare' => '>=',
                'type'    => 'DATE',
            ],
        ]);
    }
});

function bootscore_child_oegkm_event_date_label(?int $post_id = null): string {
    $post_id = $post_id ?: get_the_ID();

    $start_date = (string) get_post_meta($post_id, '_oegkm_event_start_date', true);
    $end_date   = (string) get_post_meta($post_id, '_oegkm_event_end_date', true);
    $start_time = (string) get_post_meta($post_id, '_oegkm_event_start_time', true);
    $end_time   = (string) get_post_meta($post_id, '_oegkm_event_end_time', true);

    if (!$start_date) {
        return '';
    }

    $start_ts = strtotime($start_date);
    $end_ts   = $end_date ? strtotime($end_date) : $start_ts;

    $date_format = get_option('date_format') ?: 'd.m.Y';
    $time_format = get_option('time_format') ?: 'H:i';

    $start_label = $start_ts ? wp_date($date_format, $start_ts) : $start_date;
    $end_label   = $end_ts ? wp_date($date_format, $end_ts) : $end_date;

    if ($end_date && $end_date !== $start_date) {
        $label = $start_label . ' – ' . $end_label;
    } else {
        $label = $start_label;
    }

    if ($start_time) {
        $label .= ', ' . esc_html($start_time);
        if ($end_time) {
            $label .= ' – ' . esc_html($end_time);
        }
        $label .= ' Uhr';
    }

    return $label;
}

function bootscore_child_oegkm_event_date_range_label(?int $post_id = null): string {
    $post_id = $post_id ?: get_the_ID();

    $start_date = (string) get_post_meta($post_id, '_oegkm_event_start_date', true);
    $end_date   = (string) get_post_meta($post_id, '_oegkm_event_end_date', true);

    if (!$start_date) {
        return '';
    }

    $start_ts = strtotime($start_date);
    $end_ts   = $end_date ? strtotime($end_date) : $start_ts;

    if (!$start_ts) {
        return $start_date;
    }

    if (!$end_ts || !$end_date || $end_date === $start_date) {
        return wp_date('j. F Y', $start_ts);
    }

    if (wp_date('Y-m', $start_ts) === wp_date('Y-m', $end_ts)) {
        return wp_date('j.', $start_ts) . '-' . wp_date('j. F Y', $end_ts);
    }

    if (wp_date('Y', $start_ts) === wp_date('Y', $end_ts)) {
        return wp_date('j. F', $start_ts) . ' - ' . wp_date('j. F Y', $end_ts);
    }

    return wp_date('j. F Y', $start_ts) . ' - ' . wp_date('j. F Y', $end_ts);
}

function bootscore_child_oegkm_event_month_badge(?int $post_id = null): array {
    $post_id    = $post_id ?: get_the_ID();
    $start_date = (string) get_post_meta($post_id, '_oegkm_event_start_date', true);
    $timestamp  = $start_date ? strtotime($start_date) : false;

    if (!$timestamp) {
        return ['day' => '', 'month' => ''];
    }

    return [
        'day'   => wp_date('d', $timestamp),
        'month' => wp_date('M', $timestamp),
    ];
}

function bootscore_child_oegkm_default_event_tabs(): array {
    return [
        [
            'title' => 'Allgemeine Infos / Organisation',
            'sections' => [
                [
                    'heading' => 'Öffnungszeiten & Registratur',
                    'body' => "Donnerstag, 28. Mai          09:30 - 17:30 Uhr\nFreitag, 29. Mai                  08:00 - 17:30 Uhr\nSamstag, 30. Mai                08:00 - 13:00 Uhr",
                ],
                [
                    'heading' => 'Tagungsbüro',
                    'body' => "MAW - Medizinische Ausstellungs- und Werbegesellschaft\nCarmen Zavarsky\nFreyung 6/3, 1010 Wien\nT: +43 1 536 63-23\nE-Mail: osteoporose@media.co.at",
                ],
                [
                    'heading' => 'Fachausstellung',
                    'body' => "MAW - Medizinische Ausstellungs- und Werbegesellschaft\nIris Bobal\nFreyung 6, 1010 Wien\nT: +43 1 536 63-48\nF: +43 1 535 60 16\nE-Mail: maw@media.co.at",
                ],
                [
                    'heading' => 'Diplomfortbildungsprogramm (DFP)',
                    'body' => 'Für die Veranstaltung werden im Rahmen des Diplomfortbildungsprogramms der Österreichischen Ärztekammer Fortbildungspunkte entsprechend den Vortragseinheiten angesucht.',
                ],
            ],
        ],
        ['title' => 'Veranstalter', 'sections' => [['heading' => 'Veranstalter', 'body' => 'Informationen ergänzen.']]],
        ['title' => 'Kongressort / Anfahrt', 'sections' => [['heading' => 'Kongressort / Anfahrt', 'body' => 'Informationen ergänzen.']]],
        ['title' => 'Teilnahmegebühren', 'sections' => [['heading' => 'Teilnahmegebühren', 'body' => 'Informationen ergänzen.']]],
        ['title' => 'Aussteller, Sponsoren, Interessenten', 'sections' => [['heading' => 'Aussteller, Sponsoren, Interessenten', 'body' => 'Informationen ergänzen.']]],
    ];
}

function bootscore_child_oegkm_sanitize_event_tabs(array $tabs_input): array {
    $tabs = [];

    foreach ($tabs_input as $tab_input) {
        if (!is_array($tab_input)) {
            continue;
        }

        $title = isset($tab_input['title']) ? sanitize_text_field((string) $tab_input['title']) : '';
        $sections_input = isset($tab_input['sections']) && is_array($tab_input['sections']) ? $tab_input['sections'] : [];
        $sections = [];

        foreach ($sections_input as $section_input) {
            if (!is_array($section_input)) {
                continue;
            }

            $heading = isset($section_input['heading']) ? sanitize_text_field((string) $section_input['heading']) : '';
            $body = isset($section_input['body']) ? bootscore_child_oegkm_sanitize_event_tab_body((string) $section_input['body']) : '';

            if ($heading === '' && $body === '') {
                continue;
            }

            $sections[] = [
                'heading' => $heading,
                'body' => $body,
            ];
        }

        if ($title === '' && !$sections) {
            continue;
        }

        $tabs[] = [
            'title' => $title,
            'sections' => $sections,
        ];
    }

    return $tabs ?: bootscore_child_oegkm_default_event_tabs();
}

function bootscore_child_oegkm_sanitize_event_tab_body(string $body): string {
    return wp_kses($body, [
        'a' => [
            'href' => true,
            'title' => true,
            'target' => true,
            'rel' => true,
        ],
        'strong' => [],
        'b' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'p' => [],
        'br' => [],
    ]);
}

function bootscore_child_oegkm_event_tabs(?int $post_id = null): array {
    $post_id = $post_id ?: get_the_ID();
    $json = (string) get_post_meta($post_id, '_oegkm_event_tabs_json', true);
    $tabs = $json ? json_decode($json, true) : null;

    return is_array($tabs) && $tabs ? $tabs : bootscore_child_oegkm_default_event_tabs();
}
