<?php
/**
 * OEGKM members area helpers.
 *
 * Native WordPress users + custom role + theme templates.
 */

if (!defined('ABSPATH')) {
    exit;
}

const OEGKM_MEMBER_ROLE = 'oegkm_member';

add_action('after_switch_theme', 'bootscore_child_oegkm_add_member_role');
add_action('init', 'bootscore_child_oegkm_add_member_role');

function bootscore_child_oegkm_add_member_role(): void {
    if (!get_role(OEGKM_MEMBER_ROLE)) {
        add_role(
            OEGKM_MEMBER_ROLE,
            __('ÖGKM Mitglied', 'bootscore-child-oegkm'),
            [
                'read' => true,
            ]
        );
    }
}

function bootscore_child_oegkm_user_is_member(?WP_User $user = null): bool {
    $user = $user ?: wp_get_current_user();

    if (!$user || empty($user->ID)) {
        return false;
    }

    if (user_can($user, 'manage_options')) {
        return true;
    }

    return in_array(OEGKM_MEMBER_ROLE, (array) $user->roles, true);
}

function bootscore_child_oegkm_login_page_url(string $redirect_to = ''): string {
    $page = get_page_by_path('login');
    $url = $page ? get_permalink($page) : home_url('/login/');

    if ($redirect_to !== '') {
        $url = add_query_arg('redirect_to', rawurlencode($redirect_to), $url);
    }

    return $url;
}

function bootscore_child_oegkm_profile_url(): string {
    $page = get_page_by_path('mein-profil');
    return $page ? get_permalink($page) : home_url('/mein-profil/');
}

function bootscore_child_oegkm_members_area_target_url(): string {
    $configured = function_exists('bootscore_child_oegkm_member_area_url') ? bootscore_child_oegkm_member_area_url() : '';

    if ($configured && $configured !== '#') {
        return $configured;
    }

    $page = get_page_by_path('mitgliederbereich');
    return $page ? get_permalink($page) : home_url('/mitgliederbereich/');
}

function bootscore_child_oegkm_header_member_url(): string {
    if (is_user_logged_in()) {
        return bootscore_child_oegkm_members_area_target_url();
    }

    return bootscore_child_oegkm_login_page_url(bootscore_child_oegkm_members_area_target_url());
}

function bootscore_child_oegkm_member_directory_url(): string {
    $page = get_page_by_path('mitgliederbereich/mitgliederliste');

    if ($page) {
        return get_permalink($page);
    }

    $page = get_page_by_path('mitgliederliste');
    return $page ? get_permalink($page) : home_url('/mitgliederbereich/mitgliederliste/');
}

function bootscore_child_oegkm_require_member_access(): void {
    if (!is_user_logged_in()) {
        wp_safe_redirect(bootscore_child_oegkm_login_page_url(get_permalink()));
        exit;
    }

    if (!bootscore_child_oegkm_user_is_member()) {
        status_header(403);
        get_header();
        ?>
        <main id="primary" class="site-main oegkm-members-page">
            <section class="oegkm-members-shell">
                <div class="container">
                    <div class="oegkm-members-message oegkm-members-message--error">
                        <p class="oegkm-members-eyebrow"><?php esc_html_e('Mitgliederbereich', 'bootscore-child-oegkm'); ?></p>
                        <h1><?php esc_html_e('Kein Zugriff', 'bootscore-child-oegkm'); ?></h1>
                        <p><?php esc_html_e('Ihr Benutzerkonto ist derzeit nicht für den Mitgliederbereich freigeschaltet.', 'bootscore-child-oegkm'); ?></p>
                    </div>
                </div>
            </section>
        </main>
        <?php
        get_footer();
        exit;
    }
}

add_action('wp_login_failed', function () {
    $referer = wp_get_referer();

    if ($referer && strpos($referer, 'wp-login.php') === false) {
        wp_safe_redirect(add_query_arg('login', 'failed', $referer));
        exit;
    }
});

add_filter('authenticate', function ($user, $username, $password) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $user;
    }

    if (empty($username) || empty($password)) {
        $referer = wp_get_referer();

        if ($referer && strpos($referer, 'wp-login.php') === false) {
            wp_safe_redirect(add_query_arg('login', 'empty', $referer));
            exit;
        }
    }

    return $user;
}, 30, 3);

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_oegkm_member_profile',
        'title' => __('ÖGKM Mitgliedsdaten', 'bootscore-child-oegkm'),
        'fields' => [
            [
                'key' => 'field_oegkm_member_title',
                'label' => __('Titel', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_title',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_institution',
                'label' => __('Institution / Praxis', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_institution',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_phone',
                'label' => __('Telefon', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_phone',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_street',
                'label' => __('Straße', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_street',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_zip',
                'label' => __('PLZ', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_zip',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_city',
                'label' => __('Ort', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_city',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_title_after',
                'label' => __('Titel nachgestellt', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_title_after',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_addition',
                'label' => __('Zusatz', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_addition',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_department',
                'label' => __('Abteilung', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_department',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_country',
                'label' => __('Land', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_country',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_website',
                'label' => __('Website', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_website',
                'type' => 'url',
            ],
            [
                'key' => 'field_oegkm_member_type',
                'label' => __('Mitgliedsart', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_type',
                'type' => 'text',
            ],
            [
                'key' => 'field_oegkm_member_hide_directory',
                'label' => __('Nicht im Mitgliederverzeichnis anzeigen', 'bootscore-child-oegkm'),
                'name' => 'oegkm_member_hide_directory',
                'type' => 'true_false',
                'ui' => 1,
                'message' => __('Eintrag im Mitgliederverzeichnis ausblenden', 'bootscore-child-oegkm'),
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'user_form',
                    'operator' => '==',
                    'value' => 'all',
                ],
            ],
        ],
        'position' => 'normal',
        'style' => 'default',
        'active' => true,
    ]);
});


function bootscore_child_oegkm_member_is_hidden_from_directory(int $user_id): bool {
    return in_array((string) get_user_meta($user_id, 'oegkm_member_hide_directory', true), ['1', 'on', 'true', 'yes'], true);
}

function bootscore_child_oegkm_member_meta(int $user_id, string $key): string {
    return (string) get_user_meta($user_id, $key, true);
}

/**
 * Brand the native WordPress password reset screen.
 * The reset link from WordPress mails still points to wp-login.php?action=rp,
 * so we style that screen instead of replacing the secure core flow.
 */
add_action('login_enqueue_scripts', function (): void {
    $logo_url = get_stylesheet_directory_uri() . '/assets/img/logo-oegkm.svg';
    $guilloche_url = get_stylesheet_directory_uri() . '/assets/img/oegkm-guilloche.png';
    ?>
    <style>
        :root{
            --oegkm-login-petrol:#075f63;
            --oegkm-login-petrol-dark:#064f52;
            --oegkm-login-green:#20c995;
            --oegkm-login-mint:#d8fff0;
            --oegkm-login-lime:#d5f992;
            --oegkm-login-text:#155b63;
        }
        body.login{
            min-height:100vh;
            background:
                radial-gradient(circle at 78% 12%, rgba(213,249,146,.72), transparent 34%),
                radial-gradient(circle at 10% 25%, rgba(207,219,255,.7), transparent 42%),
                linear-gradient(110deg, rgba(235,246,248,.98) 0%, rgba(215,253,236,.94) 100%);
            color:var(--oegkm-login-text);
            font-family:inherit;
        }
        body.login::before,
        body.login::after{
            content:"";
            position:fixed;
            z-index:0;
            pointer-events:none;
            background-image:url('<?php echo esc_url($guilloche_url); ?>');
            background-repeat:no-repeat;
            background-size:contain;
            opacity:.18;
            mix-blend-mode:multiply;
        }
        body.login::before{
            width:min(920px, 72vw);
            height:min(920px, 72vw);
            left:-12vw;
            bottom:-22vw;
            transform:rotate(-8deg);
        }
        body.login::after{
            width:min(780px, 58vw);
            height:min(780px, 58vw);
            right:-16vw;
            top:4vw;
            transform:scaleX(-1) rotate(10deg);
        }
        #login{
            position:relative;
            z-index:1;
            width:min(520px, calc(100% - 2rem));
            padding:clamp(3rem, 8vh, 5.5rem) 0 2rem;
        }
        .login h1 a{
            width:190px;
            height:76px;
            margin:0 auto 2rem;
            background-image:url('<?php echo esc_url($logo_url); ?>');
            background-size:contain;
            background-position:center;
            background-repeat:no-repeat;
        }
        .login form{
            margin-top:0;
            padding:clamp(2rem, 4vw, 3rem);
            border:1px solid rgba(16,91,99,.15);
            border-radius:2rem;
            background:rgba(255,255,255,.78);
            box-shadow:0 28px 80px rgba(9,66,73,.16);
            backdrop-filter:blur(24px);
            -webkit-backdrop-filter:blur(24px);
        }
        .login .message,
        .login .notice,
        .login .success{
            margin:0 0 1.2rem;
            padding:1rem 1.15rem;
            border:1px solid rgba(16,91,99,.14);
            border-left:4px solid var(--oegkm-login-green);
            border-radius:1rem;
            background:rgba(255,255,255,.78);
            color:rgba(21,91,99,.86);
            box-shadow:none;
        }
        .login label{
            color:var(--oegkm-login-text);
            font-weight:700;
            font-size:.95rem;
        }
        .login form .input,
        .login input[type="text"],
        .login input[type="password"]{
            width:100%;
            min-height:3.25rem;
            margin-top:.45rem;
            padding:.85rem 1.05rem;
            border:1px solid rgba(21,91,99,.26);
            border-radius:999px;
            background:#fff;
            color:var(--oegkm-login-text);
            box-shadow:none;
            font-size:1.05rem;
            outline:none;
        }
        .login form .input:focus,
        .login input[type="text"]:focus,
        .login input[type="password"]:focus{
            border-color:rgba(21,91,99,.7);
            box-shadow:0 0 0 .22rem rgba(33,201,149,.14);
        }
        .login .button.wp-generate-pw,
        .login .button.button-secondary{
            min-height:2.75rem;
            padding:.65rem 1.05rem;
            border:1px solid rgba(21,91,99,.22);
            border-radius:999px;
            background:#fff;
            color:var(--oegkm-login-text);
            font-weight:700;
            box-shadow:none;
        }
        .login .button-primary{
            min-height:3rem;
            padding:.7rem 1.25rem;
            border:0;
            border-radius:999px;
            background:var(--oegkm-login-petrol);
            color:#fff;
            font-weight:800;
            box-shadow:0 14px 30px rgba(7,95,99,.2);
            text-shadow:none;
        }
        .login .button-primary:hover,
        .login .button-primary:focus{
            background:var(--oegkm-login-petrol-dark);
            color:#fff;
            box-shadow:0 18px 38px rgba(7,95,99,.24);
        }
        .login #nav,
        .login #backtoblog,
        .login .privacy-policy-page-link,
        .login .language-switcher{
            text-align:center;
            color:var(--oegkm-login-text);
        }
        .login #nav a,
        .login #backtoblog a,
        .login .privacy-policy-page-link a{
            color:var(--oegkm-login-text);
            font-weight:700;
            text-decoration:underline;
            text-underline-offset:.18em;
        }
        .login .wp-hide-pw{
            color:var(--oegkm-login-text);
        }
        .login .pw-weak label{
            font-weight:600;
        }
        @media (max-width:575.98px){
            .login form{border-radius:1.45rem;padding:1.5rem;}
            .login h1 a{width:155px;height:62px;}
        }
    </style>
    <?php
});

add_filter('login_headerurl', function (): string {
    return home_url('/');
});

add_filter('login_headertext', function (): string {
    return get_bloginfo('name');
});

/**
 * Member content post types: galleries and videos.
 * These are intentionally lightweight and protected on the frontend templates.
 */
add_action('init', function (): void {
    register_post_type('oegkm_gallery', [
        'labels' => [
            'name' => __('Mitglieder-Galerien', 'bootscore-child-oegkm'),
            'singular_name' => __('Mitglieder-Galerie', 'bootscore-child-oegkm'),
            'add_new_item' => __('Neue Galerie hinzufügen', 'bootscore-child-oegkm'),
            'edit_item' => __('Galerie bearbeiten', 'bootscore-child-oegkm'),
        ],
        'public' => true,
        'exclude_from_search' => true,
        'show_in_rest' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-format-gallery',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'has_archive' => 'mitgliederbereich/bildergalerien',
        'rewrite' => ['slug' => 'mitgliederbereich/bildergalerien', 'with_front' => false],
    ]);

    register_post_type('oegkm_video', [
        'labels' => [
            'name' => __('Mitglieder-Videos', 'bootscore-child-oegkm'),
            'singular_name' => __('Mitglieder-Video', 'bootscore-child-oegkm'),
            'add_new_item' => __('Neues Video hinzufügen', 'bootscore-child-oegkm'),
            'edit_item' => __('Video bearbeiten', 'bootscore-child-oegkm'),
        ],
        'public' => true,
        'exclude_from_search' => true,
        'show_in_rest' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-video-alt3',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'page-attributes'],
        'has_archive' => 'mitgliederbereich/videos',
        'rewrite' => ['slug' => 'mitgliederbereich/videos', 'with_front' => false],
    ]);
});

add_action('acf/init', function (): void {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_oegkm_member_gallery',
        'title' => __('Galerie-Inhalte', 'bootscore-child-oegkm'),
        'fields' => [
            [
                'key' => 'field_oegkm_gallery_event',
                'label' => __('Zugehörige Veranstaltung', 'bootscore-child-oegkm'),
                'name' => 'oegkm_media_event',
                'type' => 'post_object',
                'post_type' => ['veranstaltung'],
                'return_format' => 'id',
                'ui' => 1,
                'required' => 1,
            ],
            [
                'key' => 'field_oegkm_gallery_images',
                'label' => __('Bilder', 'bootscore-child-oegkm'),
                'name' => 'oegkm_gallery_images',
                'type' => 'gallery',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'insert' => 'append',
                'library' => 'all',
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'oegkm_gallery']]],
        'position' => 'normal',
        'active' => true,
    ]);

    acf_add_local_field_group([
        'key' => 'group_oegkm_member_video',
        'title' => __('Video-Inhalte', 'bootscore-child-oegkm'),
        'fields' => [
            [
                'key' => 'field_oegkm_video_event',
                'label' => __('Zugehörige Veranstaltung', 'bootscore-child-oegkm'),
                'name' => 'oegkm_media_event',
                'type' => 'post_object',
                'post_type' => ['veranstaltung'],
                'return_format' => 'id',
                'ui' => 1,
                'required' => 1,
            ],
            [
                'key' => 'field_oegkm_video_embed',
                'label' => __('YouTube/Vimeo URL', 'bootscore-child-oegkm'),
                'name' => 'oegkm_video_embed',
                'type' => 'oembed',
                'width' => 640,
                'height' => 360,
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'oegkm_video']]],
        'position' => 'normal',
        'active' => true,
    ]);
});

function bootscore_child_oegkm_member_gallery_archive_url(): string {
    $url = get_post_type_archive_link('oegkm_gallery');
    return $url ?: home_url('/mitgliederbereich/bildergalerien/');
}

function bootscore_child_oegkm_member_video_archive_url(): string {
    $url = get_post_type_archive_link('oegkm_video');
    return $url ?: home_url('/mitgliederbereich/videos/');
}


function bootscore_child_oegkm_member_media_archive_url(): string {
    $page = get_page_by_path('mitgliederbereich/medien');

    if ($page) {
        return get_permalink($page);
    }

    $page = get_page_by_path('medien');
    return $page ? get_permalink($page) : home_url('/mitgliederbereich/medien/');
}

function bootscore_child_oegkm_get_member_media_for_event(int $event_id, string $post_type = ''): array {
    $post_types = $post_type ? [$post_type] : ['oegkm_gallery', 'oegkm_video'];

    return get_posts([
        'post_type' => $post_types,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => [[
            'key' => 'oegkm_media_event',
            'value' => $event_id,
            'compare' => '=',
        ]],
    ]);
}

function bootscore_child_oegkm_event_has_member_media(int $event_id): bool {
    return !empty(bootscore_child_oegkm_get_member_media_for_event($event_id));
}



/**
 * Event media sections
 * Media is maintained directly on the Veranstaltung post to keep large events manageable.
 */
add_action('acf/init', function (): void {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_oegkm_event_member_media_sections',
        'title' => __('Mitglieder-Medien zur Veranstaltung', 'bootscore-child-oegkm'),
        'fields' => [
            [
                'key' => 'field_oegkm_event_media_sections',
                'label' => __('Medienbereiche', 'bootscore-child-oegkm'),
                'name' => 'oegkm_event_media_sections',
                'type' => 'repeater',
                'instructions' => __('Hier können Bildergalerien und Videos für den Mitgliederbereich direkt an der Veranstaltung gepflegt werden, z. B. Tag 1, Tag 2 oder Abendveranstaltung.', 'bootscore-child-oegkm'),
                'layout' => 'block',
                'button_label' => __('Medienbereich hinzufügen', 'bootscore-child-oegkm'),
                'collapsed' => 'field_oegkm_event_media_section_title',
                'sub_fields' => [
                    [
                        'key' => 'field_oegkm_event_media_section_title',
                        'label' => __('Titel des Bereichs', 'bootscore-child-oegkm'),
                        'name' => 'title',
                        'type' => 'text',
                        'placeholder' => __('z. B. Tag 1, Tag 2, Abendveranstaltung', 'bootscore-child-oegkm'),
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_oegkm_event_media_section_description',
                        'label' => __('Kurze Beschreibung', 'bootscore-child-oegkm'),
                        'name' => 'description',
                        'type' => 'textarea',
                        'rows' => 2,
                        'new_lines' => 'br',
                    ],
                    [
                        'key' => 'field_oegkm_event_media_section_images',
                        'label' => __('Bildergalerie', 'bootscore-child-oegkm'),
                        'name' => 'images',
                        'type' => 'gallery',
                        'return_format' => 'array',
                        'preview_size' => 'medium',
                        'insert' => 'append',
                        'library' => 'all',
                    ],
                    [
                        'key' => 'field_oegkm_event_media_section_videos',
                        'label' => __('Videos', 'bootscore-child-oegkm'),
                        'name' => 'videos',
                        'type' => 'repeater',
                        'layout' => 'row',
                        'button_label' => __('Video hinzufügen', 'bootscore-child-oegkm'),
                        'collapsed' => 'field_oegkm_event_media_video_title',
                        'sub_fields' => [
                            [
                                'key' => 'field_oegkm_event_media_video_title',
                                'label' => __('Videotitel', 'bootscore-child-oegkm'),
                                'name' => 'title',
                                'type' => 'text',
                            ],
                            [
                                'key' => 'field_oegkm_event_media_video_url',
                                'label' => __('YouTube-/Vimeo-URL', 'bootscore-child-oegkm'),
                                'name' => 'url',
                                'type' => 'oembed',
                                'width' => 640,
                                'height' => 360,
                            ],
                            [
                                'key' => 'field_oegkm_event_media_video_description',
                                'label' => __('Beschreibung', 'bootscore-child-oegkm'),
                                'name' => 'description',
                                'type' => 'textarea',
                                'rows' => 2,
                                'new_lines' => 'br',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'location' => [[['param' => 'post_type', 'operator' => '==', 'value' => 'veranstaltung']]],
        'position' => 'normal',
        'menu_order' => 20,
        'active' => true,
    ]);
});

function bootscore_child_oegkm_get_event_media_sections(int $event_id): array {
    if (!function_exists('get_field')) {
        return [];
    }

    $sections = get_field('oegkm_event_media_sections', $event_id);
    if (!is_array($sections)) {
        return [];
    }

    return array_values(array_filter($sections, static function ($section): bool {
        $images = $section['images'] ?? [];
        $videos = $section['videos'] ?? [];
        return (!empty($images) && is_array($images)) || (!empty($videos) && is_array($videos));
    }));
}

function bootscore_child_oegkm_event_has_section_media(int $event_id): bool {
    return !empty(bootscore_child_oegkm_get_event_media_sections($event_id));
}

function bootscore_child_oegkm_member_nav(string $active = ''): void {
    $items = [
        'overview' => [__('Übersicht', 'bootscore-child-oegkm'), bootscore_child_oegkm_members_area_target_url()],
        'members' => [__('Mitgliederliste', 'bootscore-child-oegkm'), bootscore_child_oegkm_member_directory_url()],
        'media' => [__('Medienarchiv', 'bootscore-child-oegkm'), bootscore_child_oegkm_member_media_archive_url()],
        'profile' => [__('Mein Profil', 'bootscore-child-oegkm'), bootscore_child_oegkm_profile_url()],
    ];
    ?>
    <nav class="oegkm-member-subnav" aria-label="<?php esc_attr_e('Navigation im Mitgliederbereich', 'bootscore-child-oegkm'); ?>">
        <?php foreach ($items as $key => [$label, $url]) : ?>
            <a class="<?php echo $active === $key ? 'is-active' : ''; ?>" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
    </nav>
    <?php
}
