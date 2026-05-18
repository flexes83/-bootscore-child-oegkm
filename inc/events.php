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
    ?>
    <style>
        .oegkm-event-admin-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:8px}.oegkm-event-admin-field label{display:block;font-weight:600;margin-bottom:6px}.oegkm-event-admin-field input{width:100%}@media(max-width:782px){.oegkm-event-admin-grid{grid-template-columns:1fr}}
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

    if ($start_date && !$end_date) {
        $end_date = $start_date;
    }

    update_post_meta($post_id, '_oegkm_event_start_date', $start_date);
    update_post_meta($post_id, '_oegkm_event_end_date', $end_date);
    update_post_meta($post_id, '_oegkm_event_start_time', $start_time);
    update_post_meta($post_id, '_oegkm_event_end_time', $end_time);
    update_post_meta($post_id, '_oegkm_event_location', $location);
    update_post_meta($post_id, '_oegkm_event_registration_url', $registration_url);
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
