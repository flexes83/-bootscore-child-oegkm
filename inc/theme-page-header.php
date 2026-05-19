<?php
if (!defined('ABSPATH')) {
    exit;
}

function bootscore_child_oegkm_page_header_variants(): array {
    return [
        'mint-left' => __('Mint / Guilloche links', 'bootscore-child-oegkm'),
        'mint-right' => __('Mint / Guilloche rechts', 'bootscore-child-oegkm'),
        'blue-lilac' => __('Blau-Lila / Guilloche links', 'bootscore-child-oegkm'),
        'green-soft' => __('Grün weich / Guilloche Mitte', 'bootscore-child-oegkm'),
        'lilac-soft' => __('Lila weich / Guilloche groß', 'bootscore-child-oegkm'),
    ];
}

function bootscore_child_oegkm_get_page_header_data(?int $post_id = null): array {
    $post_id = $post_id ?: get_the_ID();

    $title = $post_id ? trim((string) get_post_meta($post_id, '_oegkm_page_header_title', true)) : '';
    if ($title === '' && $post_id) {
        $title = get_the_title($post_id);
    }

    $intro = $post_id ? trim((string) get_post_meta($post_id, '_oegkm_page_header_intro', true)) : '';
    $variant = $post_id ? (string) get_post_meta($post_id, '_oegkm_page_header_variant', true) : '';
    $hide = $post_id ? (string) get_post_meta($post_id, '_oegkm_hide_page_header', true) : '';
    $hero = $post_id ? (string) get_post_meta($post_id, '_oegkm_page_header_hero', true) : '';
    $variants = bootscore_child_oegkm_page_header_variants();

    if (!isset($variants[$variant])) {
        $variant = 'mint-left';
    }

    return [
        'title' => $title,
        'intro' => $intro,
        'variant' => $variant,
        'hide' => $hide === '1',
        'hero' => $hero === '1',
    ];
}


function bootscore_child_oegkm_render_current_page_header(): void {
    if (!is_page() || is_page_template('page-mitglieder-login.php')) {
        return;
    }

    $post_id = get_queried_object_id();
    if (!$post_id) {
        return;
    }

    $header_data = bootscore_child_oegkm_get_page_header_data($post_id);

    if (!empty($header_data['hide'])) {
        return;
    }

    // Auf der Startseite nur ausgeben, wenn explizit der Hero-Modus aktiviert wurde.
    if (is_front_page() && empty($header_data['hero'])) {
        return;
    }

    bootscore_child_oegkm_render_theme_page_header($header_data);
}

function bootscore_child_oegkm_render_theme_page_header(array $args = []): void {
    $defaults = [
        'title' => '',
        'intro' => '',
        'variant' => 'mint-left',
        'labelledby' => 'oegkm-theme-page-title',
        'hero' => false,
        'hide' => false,
        'subtitle' => '',
    ];

    $args = wp_parse_args($args, $defaults);
    $variants = bootscore_child_oegkm_page_header_variants();

    if (!isset($variants[$args['variant']])) {
        $args['variant'] = 'mint-left';
    }

    if (trim((string) $args['title']) === '') {
        return;
    }

    $guilloche_url = get_stylesheet_directory_uri() . '/assets/img/oegkm-guilloche.png';
    $has_intro = trim((string) $args['intro']) !== '';
    ?>
    <section class="oegkm-theme-page-header oegkm-theme-page-header--<?php echo esc_attr($args['variant']); ?> <?php echo $has_intro ? 'oegkm-theme-page-header--has-intro' : 'oegkm-theme-page-header--no-intro'; ?><?php echo !empty($args['hero']) ? ' oegkm-theme-page-header--hero' : ''; ?>" aria-labelledby="<?php echo esc_attr($args['labelledby']); ?>">
        <div class="oegkm-theme-page-header__shell">
            <img class="oegkm-theme-page-header__guilloche" src="<?php echo esc_url($guilloche_url); ?>" alt="" aria-hidden="true" loading="eager">

            <div class="oegkm-theme-page-header__panel">
                <div class="container oegkm-theme-page-header__inner">
                    <h1 id="<?php echo esc_attr($args['labelledby']); ?>"><?php echo esc_html($args['title']); ?></h1>
                    <?php if (trim((string) $args['subtitle']) !== '') : ?>
                        <p class="oegkm-theme-page-header__subtitle"><?php echo esc_html($args['subtitle']); ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($args['hero'])) : ?>
                    <a class="oegkm-theme-page-header__scroll" href="#primary" aria-label="<?php esc_attr_e('Zum Inhalt springen', 'bootscore-child-oegkm'); ?>">
                        <span aria-hidden="true">⌄</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="oegkm-theme-page-header__divider" aria-hidden="true"></div>

            <?php if ($has_intro) : ?>
                <div class="container oegkm-theme-page-header__intro-wrap">
                    <div class="oegkm-theme-page-header__intro">
                        <?php echo wpautop(wp_kses_post($args['intro'])); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

add_action('add_meta_boxes', function () {
    add_meta_box(
        'oegkm_page_header_settings',
        __('ÖGKM Seitenheader', 'bootscore-child-oegkm'),
        'bootscore_child_oegkm_page_header_meta_box',
        'page',
        'normal',
        'high'
    );
});

function bootscore_child_oegkm_page_header_meta_box(WP_Post $post): void {
    wp_nonce_field('oegkm_page_header_save', 'oegkm_page_header_nonce');

    $title = (string) get_post_meta($post->ID, '_oegkm_page_header_title', true);
    $intro = (string) get_post_meta($post->ID, '_oegkm_page_header_intro', true);
    $variant = (string) get_post_meta($post->ID, '_oegkm_page_header_variant', true);
    $hide_header = (string) get_post_meta($post->ID, '_oegkm_hide_page_header', true);
    $hero_header = (string) get_post_meta($post->ID, '_oegkm_page_header_hero', true);
    $variants = bootscore_child_oegkm_page_header_variants();

    if (!isset($variants[$variant])) {
        $variant = 'mint-left';
    }
    ?>
    <p>
        <label for="oegkm_page_header_title"><strong><?php esc_html_e('Header-Titel', 'bootscore-child-oegkm'); ?></strong></label><br>
        <input type="text" id="oegkm_page_header_title" name="oegkm_page_header_title" value="<?php echo esc_attr($title); ?>" class="widefat" placeholder="<?php echo esc_attr(get_the_title($post)); ?>">
    </p>
    <p>
        <label for="oegkm_page_header_intro"><strong><?php esc_html_e('Introtext', 'bootscore-child-oegkm'); ?></strong></label><br>
        <textarea id="oegkm_page_header_intro" name="oegkm_page_header_intro" class="widefat" rows="4"><?php echo esc_textarea($intro); ?></textarea>
    </p>
    <p>
        <label for="oegkm_page_header_variant"><strong><?php esc_html_e('Gradient-/Guillochen-Variante', 'bootscore-child-oegkm'); ?></strong></label><br>
        <select id="oegkm_page_header_variant" name="oegkm_page_header_variant" class="widefat">
            <?php foreach ($variants as $key => $label) : ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($variant, $key); ?>><?php echo esc_html($label); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p style="margin-top:18px;">
        <label>
            <input type="checkbox" name="oegkm_page_header_hero" value="1" <?php checked($hero_header, '1'); ?>>
            <?php esc_html_e('Als großen Hero-Header darstellen', 'bootscore-child-oegkm'); ?>
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="oegkm_hide_page_header" value="1" <?php checked($hide_header, '1'); ?>>
            <?php esc_html_e('Seitenheader auf dieser Seite deaktivieren', 'bootscore-child-oegkm'); ?>
        </label>
    </p>
    <p class="description"><?php esc_html_e('Wenn kein Header-Titel gepflegt ist, wird automatisch der Seitentitel verwendet. Der Hero-Modus ist besonders für die Startseite gedacht.', 'bootscore-child-oegkm'); ?></p>
    <?php
}

add_action('save_post_page', function (int $post_id) {
    if (!isset($_POST['oegkm_page_header_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['oegkm_page_header_nonce'])), 'oegkm_page_header_save')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    $title = isset($_POST['oegkm_page_header_title']) ? sanitize_text_field(wp_unslash($_POST['oegkm_page_header_title'])) : '';
    $intro = isset($_POST['oegkm_page_header_intro']) ? wp_kses_post(wp_unslash($_POST['oegkm_page_header_intro'])) : '';
    $variant = isset($_POST['oegkm_page_header_variant']) ? sanitize_key(wp_unslash($_POST['oegkm_page_header_variant'])) : 'mint-left';
    $hide_header = isset($_POST['oegkm_hide_page_header']) ? '1' : '';
    $hero_header = isset($_POST['oegkm_page_header_hero']) ? '1' : '';

    if (!isset(bootscore_child_oegkm_page_header_variants()[$variant])) {
        $variant = 'mint-left';
    }

    update_post_meta($post_id, '_oegkm_page_header_title', $title);
    update_post_meta($post_id, '_oegkm_page_header_intro', $intro);
    update_post_meta($post_id, '_oegkm_page_header_variant', $variant);
    update_post_meta($post_id, '_oegkm_hide_page_header', $hide_header);
    update_post_meta($post_id, '_oegkm_page_header_hero', $hero_header);
});
