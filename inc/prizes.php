<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * OEGKM Preise & Stipendien
 *
 * Custom Post Type + Meta-Felder für Übersicht, Single und Archiv.
 */

add_action('init', function () {
    $labels = [
        'name'                  => __('Preise & Stipendien', 'bootscore-child-oegkm'),
        'singular_name'         => __('Preis & Stipendium', 'bootscore-child-oegkm'),
        'menu_name'             => __('Preise & Stipendien', 'bootscore-child-oegkm'),
        'name_admin_bar'        => __('Preis & Stipendium', 'bootscore-child-oegkm'),
        'add_new'               => __('Neu hinzufügen', 'bootscore-child-oegkm'),
        'add_new_item'          => __('Neuen Eintrag hinzufügen', 'bootscore-child-oegkm'),
        'new_item'              => __('Neuer Eintrag', 'bootscore-child-oegkm'),
        'edit_item'             => __('Eintrag bearbeiten', 'bootscore-child-oegkm'),
        'view_item'             => __('Eintrag ansehen', 'bootscore-child-oegkm'),
        'all_items'             => __('Alle Preise & Stipendien', 'bootscore-child-oegkm'),
        'search_items'          => __('Preise & Stipendien suchen', 'bootscore-child-oegkm'),
        'not_found'             => __('Keine Preise oder Stipendien gefunden.', 'bootscore-child-oegkm'),
        'not_found_in_trash'    => __('Keine Preise oder Stipendien im Papierkorb gefunden.', 'bootscore-child-oegkm'),
        'featured_image'        => __('Bild', 'bootscore-child-oegkm'),
        'set_featured_image'    => __('Bild festlegen', 'bootscore-child-oegkm'),
        'remove_featured_image' => __('Bild entfernen', 'bootscore-child-oegkm'),
    ];

    register_post_type('preis_stipendium', [
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-awards',
        'has_archive'        => 'preise-stipendien',
        'rewrite'            => [
            'slug'       => 'preise-stipendien',
            'with_front' => false,
        ],
        'supports'           => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
    ]);
});

add_action('add_meta_boxes', function () {
    add_meta_box(
        'oegkm_prize_details',
        __('Details', 'bootscore-child-oegkm'),
        'bootscore_child_oegkm_render_prize_meta_box',
        'preis_stipendium',
        'normal',
        'high'
    );
});

function bootscore_child_oegkm_render_prize_meta_box(WP_Post $post): void {
    wp_nonce_field('oegkm_save_prize_details', 'oegkm_prize_details_nonce');

    $date            = get_post_meta($post->ID, '_oegkm_prize_date', true);
    $deadline        = get_post_meta($post->ID, '_oegkm_prize_deadline', true);
    $type            = get_post_meta($post->ID, '_oegkm_prize_type', true);
    $amount          = get_post_meta($post->ID, '_oegkm_prize_amount', true);
    $application_url = get_post_meta($post->ID, '_oegkm_prize_application_url', true);
    ?>
    <style>
        .oegkm-prize-admin-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:8px}.oegkm-prize-admin-field label{display:block;font-weight:600;margin-bottom:6px}.oegkm-prize-admin-field input,.oegkm-prize-admin-field select{width:100%}@media(max-width:782px){.oegkm-prize-admin-grid{grid-template-columns:1fr}}
    </style>
    <div class="oegkm-prize-admin-grid">
        <p class="oegkm-prize-admin-field">
            <label for="oegkm_prize_date"><?php esc_html_e('Datum', 'bootscore-child-oegkm'); ?> *</label>
            <input type="date" id="oegkm_prize_date" name="oegkm_prize_date" value="<?php echo esc_attr($date); ?>">
        </p>
        <p class="oegkm-prize-admin-field">
            <label for="oegkm_prize_deadline"><?php esc_html_e('Einreichfrist', 'bootscore-child-oegkm'); ?></label>
            <input type="date" id="oegkm_prize_deadline" name="oegkm_prize_deadline" value="<?php echo esc_attr($deadline); ?>">
        </p>
        <p class="oegkm-prize-admin-field">
            <label for="oegkm_prize_type"><?php esc_html_e('Art', 'bootscore-child-oegkm'); ?></label>
            <select id="oegkm_prize_type" name="oegkm_prize_type">
                <option value=""><?php esc_html_e('Bitte wählen', 'bootscore-child-oegkm'); ?></option>
                <option value="Preis" <?php selected($type, 'Preis'); ?>><?php esc_html_e('Preis', 'bootscore-child-oegkm'); ?></option>
                <option value="Stipendium" <?php selected($type, 'Stipendium'); ?>><?php esc_html_e('Stipendium', 'bootscore-child-oegkm'); ?></option>
                <option value="Förderung" <?php selected($type, 'Förderung'); ?>><?php esc_html_e('Förderung', 'bootscore-child-oegkm'); ?></option>
            </select>
        </p>
        <p class="oegkm-prize-admin-field">
            <label for="oegkm_prize_amount"><?php esc_html_e('Dotierung / Betrag', 'bootscore-child-oegkm'); ?></label>
            <input type="text" id="oegkm_prize_amount" name="oegkm_prize_amount" value="<?php echo esc_attr($amount); ?>" placeholder="<?php esc_attr_e('z. B. EUR 5.000', 'bootscore-child-oegkm'); ?>">
        </p>
        <p class="oegkm-prize-admin-field">
            <label for="oegkm_prize_application_url"><?php esc_html_e('Einreichlink', 'bootscore-child-oegkm'); ?></label>
            <input type="url" id="oegkm_prize_application_url" name="oegkm_prize_application_url" value="<?php echo esc_attr($application_url); ?>" placeholder="https://">
        </p>
    </div>
    <?php
}

add_action('save_post_preis_stipendium', function (int $post_id) {
    if (!isset($_POST['oegkm_prize_details_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['oegkm_prize_details_nonce'])), 'oegkm_save_prize_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $date            = isset($_POST['oegkm_prize_date']) ? sanitize_text_field(wp_unslash($_POST['oegkm_prize_date'])) : '';
    $deadline        = isset($_POST['oegkm_prize_deadline']) ? sanitize_text_field(wp_unslash($_POST['oegkm_prize_deadline'])) : '';
    $type            = isset($_POST['oegkm_prize_type']) ? sanitize_text_field(wp_unslash($_POST['oegkm_prize_type'])) : '';
    $amount          = isset($_POST['oegkm_prize_amount']) ? sanitize_text_field(wp_unslash($_POST['oegkm_prize_amount'])) : '';
    $application_url = isset($_POST['oegkm_prize_application_url']) ? esc_url_raw(wp_unslash($_POST['oegkm_prize_application_url'])) : '';

    update_post_meta($post_id, '_oegkm_prize_date', $date);
    update_post_meta($post_id, '_oegkm_prize_deadline', $deadline);
    update_post_meta($post_id, '_oegkm_prize_type', $type);
    update_post_meta($post_id, '_oegkm_prize_amount', $amount);
    update_post_meta($post_id, '_oegkm_prize_application_url', $application_url);
});

add_action('pre_get_posts', function (WP_Query $query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->is_post_type_archive('preis_stipendium')) {
        $query->set('posts_per_page', 12);
        $query->set('meta_key', '_oegkm_prize_date');
        $query->set('orderby', 'meta_value');
        $query->set('order', 'ASC');
    }
});

function bootscore_child_oegkm_prize_date_label(?int $post_id = null): string {
    $post_id = $post_id ?: get_the_ID();
    $date    = (string) get_post_meta($post_id, '_oegkm_prize_date', true);

    if (!$date) {
        return '';
    }

    $timestamp   = strtotime($date);
    $date_format = get_option('date_format') ?: 'd.m.Y';

    return $timestamp ? wp_date($date_format, $timestamp) : $date;
}

function bootscore_child_oegkm_prize_deadline_label(?int $post_id = null): string {
    $post_id  = $post_id ?: get_the_ID();
    $deadline = (string) get_post_meta($post_id, '_oegkm_prize_deadline', true);

    if (!$deadline) {
        return '';
    }

    $timestamp   = strtotime($deadline);
    $date_format = get_option('date_format') ?: 'd.m.Y';

    return $timestamp ? wp_date($date_format, $timestamp) : $deadline;
}

function bootscore_child_oegkm_prize_year(?int $post_id = null): string {
    $post_id = $post_id ?: get_the_ID();
    $date    = (string) get_post_meta($post_id, '_oegkm_prize_date', true);

    if (!$date) {
        return '';
    }

    $timestamp = strtotime($date);

    return $timestamp ? wp_date('Y', $timestamp) : substr($date, 0, 4);
}

function bootscore_child_oegkm_render_prize_thumbnail(int $post_id): void {
    if (has_post_thumbnail($post_id)) {
        ?>
        <a class="oegkm-prize-card__image" href="<?php echo esc_url(get_permalink($post_id)); ?>" aria-label="<?php echo esc_attr(get_the_title($post_id)); ?>">
            <?php echo get_the_post_thumbnail($post_id, 'large'); ?>
        </a>
        <?php
        return;
    }

    $guilloche_index = ($post_id % 7) + 1;
    $guilloche_url   = get_stylesheet_directory_uri() . '/assets/img/oegkm-guilloche-' . $guilloche_index . '.jpg';
    $logo_url        = get_stylesheet_directory_uri() . '/assets/img/logo-oegkm.svg';
    ?>
    <a
        class="oegkm-prize-card__placeholder"
        href="<?php echo esc_url(get_permalink($post_id)); ?>"
        aria-label="<?php echo esc_attr(get_the_title($post_id)); ?>"
        style="--oegkm-prize-placeholder-image:url('<?php echo esc_url($guilloche_url); ?>');"
    >
        <span class="oegkm-prize-card__placeholder-mark" aria-hidden="true">
            <img src="<?php echo esc_url($logo_url); ?>" alt="">
        </span>
    </a>
    <?php
}

function bootscore_child_oegkm_render_prize_card(int $post_id): void {
    $date_label     = bootscore_child_oegkm_prize_date_label($post_id);
    $deadline_label = bootscore_child_oegkm_prize_deadline_label($post_id);
    ?>
    <article <?php post_class('oegkm-prize-card', $post_id); ?>>
        <?php bootscore_child_oegkm_render_prize_thumbnail($post_id); ?>

        <div class="oegkm-prize-card__body">
            <div class="oegkm-prize-card__meta">
                <?php if ($deadline_label) : ?>
                    <span><?php echo esc_html(sprintf(__('Einreichungsschluss: %s', 'bootscore-child-oegkm'), $deadline_label)); ?></span>
                <?php elseif ($date_label) : ?>
                    <time datetime="<?php echo esc_attr(get_post_meta($post_id, '_oegkm_prize_date', true)); ?>"><?php echo esc_html($date_label); ?></time>
                <?php endif; ?>
            </div>

            <h3><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>

            <?php if (has_excerpt($post_id)) : ?>
                <p><?php echo esc_html(get_the_excerpt($post_id)); ?></p>
            <?php else : ?>
                <p><?php echo esc_html(wp_trim_words(get_the_content(null, false, $post_id), 32)); ?></p>
            <?php endif; ?>

            <a class="oegkm-prize-card__button" href="<?php echo esc_url(get_permalink($post_id)); ?>">
                <?php esc_html_e('Mehr erfahren', 'bootscore-child-oegkm'); ?> <span aria-hidden="true">→</span>
            </a>
        </div>
    </article>
    <?php
}
