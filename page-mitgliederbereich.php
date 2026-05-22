<?php
/**
 * Template Name: ÖGKM Mitgliederbereich
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
    exit;
}

bootscore_child_oegkm_require_member_access();

get_header();
$current_user = wp_get_current_user();
$user_id = (int) $current_user->ID;
$profile_url = bootscore_child_oegkm_profile_url();
$logout_url = wp_logout_url(home_url('/'));
$member_name_parts = array_filter([
    bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_title'),
    $current_user->first_name,
    $current_user->last_name,
    bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_title_after'),
]);
$member_name = trim(implode(' ', $member_name_parts));
$member_name = $member_name !== '' ? $member_name : ($current_user->display_name ?: $current_user->user_login);
$member_institution = bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_institution');
$member_address_parts = array_filter([
    bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_street'),
    trim(bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_zip') . ' ' . bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_city')),
    bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_country'),
]);
$member_initial_source = trim($current_user->first_name . ' ' . $current_user->last_name);
$member_initial_source = $member_initial_source !== '' ? $member_initial_source : $member_name;
$member_initial_words = preg_split('/\s+/', $member_initial_source);
$member_initial_letters = [];
foreach ((array) $member_initial_words as $word) {
    if ($word === '') {
        continue;
    }
    $member_initial_letters[] = function_exists('mb_substr') ? mb_substr($word, 0, 1) : substr($word, 0, 1);
    if (count($member_initial_letters) >= 2) {
        break;
    }
}
$member_initials = implode('', $member_initial_letters);
$member_initials = function_exists('mb_strtoupper') ? mb_strtoupper($member_initials ?: 'ÖG') : strtoupper($member_initials ?: 'ÖG');
$member_avatar = get_avatar_data($user_id, ['size' => 160, 'default' => '404']);
$member_has_avatar = !empty($member_avatar['found_avatar']) && !empty($member_avatar['url']);
?>

<main id="primary" <?php post_class('site-main oegkm-members-page'); ?>>
    <section class="oegkm-members-shell oegkm-members-shell--plain">
        <div class="container">
            <?php bootscore_child_oegkm_member_nav('overview'); ?>

            <header class="oegkm-members-section-header" aria-labelledby="oegkm-members-title">
                <p class="oegkm-members-eyebrow"><?php esc_html_e('Mitgliederbereich', 'bootscore-child-oegkm'); ?></p>
                <h1 id="oegkm-members-title"><?php the_title(); ?></h1>
            </header>

            <section class="oegkm-member-vcard" aria-label="<?php esc_attr_e('Mitgliedsprofil', 'bootscore-child-oegkm'); ?>">
                <div class="oegkm-member-vcard__avatar" aria-hidden="true">
                    <?php if ($member_has_avatar) : ?>
                        <img src="<?php echo esc_url($member_avatar['url']); ?>" alt="">
                    <?php else : ?>
                        <span><?php echo esc_html($member_initials); ?></span>
                    <?php endif; ?>
                </div>
                <div class="oegkm-member-vcard__body">
                    <h2><?php echo esc_html($member_name); ?></h2>
                    <?php if ($member_institution !== '') : ?>
                        <p><?php echo esc_html($member_institution); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($member_address_parts)) : ?>
                        <address><?php echo esc_html(implode(', ', $member_address_parts)); ?></address>
                    <?php endif; ?>
                    <?php if ($current_user->user_email !== '') : ?>
                        <a href="mailto:<?php echo esc_attr($current_user->user_email); ?>"><?php echo esc_html($current_user->user_email); ?></a>
                    <?php endif; ?>
                    <div class="oegkm-member-vcard__actions">
                        <a class="oegkm-members-button" href="<?php echo esc_url($profile_url); ?>"><?php esc_html_e('Profil bearbeiten', 'bootscore-child-oegkm'); ?></a>
                        <a class="oegkm-members-textlink" href="<?php echo esc_url($logout_url); ?>"><?php esc_html_e('Abmelden', 'bootscore-child-oegkm'); ?></a>
                    </div>
                </div>
            </section>

            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php if (trim(get_the_content()) !== '') : ?>
                        <div class="oegkm-members-content oegkm-content-flow">
                            <?php the_content(); ?>
                        </div>
                    <?php else : ?>
                        <div class="oegkm-members-grid" aria-label="<?php esc_attr_e('Mitgliederbereich Übersicht', 'bootscore-child-oegkm'); ?>">
                            <article class="oegkm-members-panel">
                                <p class="oegkm-members-panel__label"><?php esc_html_e('Profil', 'bootscore-child-oegkm'); ?></p>
                                <h2><?php esc_html_e('Mitgliedsdaten verwalten', 'bootscore-child-oegkm'); ?></h2>
                                <p><?php esc_html_e('Aktualisieren Sie Ihre Kontaktdaten und ändern Sie bei Bedarf Ihr Passwort.', 'bootscore-child-oegkm'); ?></p>
                                <a href="<?php echo esc_url($profile_url); ?>"><?php esc_html_e('Zum Profil', 'bootscore-child-oegkm'); ?> →</a>
                            </article>

                            <article class="oegkm-members-panel">
                                <p class="oegkm-members-panel__label"><?php esc_html_e('Medienarchiv', 'bootscore-child-oegkm'); ?></p>
                                <h2><?php esc_html_e('Veranstaltungsmedien', 'bootscore-child-oegkm'); ?></h2>
                                <p><?php esc_html_e('Bildergalerien und Videos vergangener Veranstaltungen – nach Veranstaltung und Medienbereich gebündelt.', 'bootscore-child-oegkm'); ?></p>
                                <a href="<?php echo esc_url(bootscore_child_oegkm_member_media_archive_url()); ?>"><?php esc_html_e('Zum Medienarchiv', 'bootscore-child-oegkm'); ?> →</a>
                            </article>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
