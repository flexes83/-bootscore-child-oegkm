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
$profile_url = bootscore_child_oegkm_profile_url();
$logout_url = wp_logout_url(home_url('/'));
?>

<main id="primary" <?php post_class('site-main oegkm-members-page'); ?>>
    <section class="oegkm-members-shell oegkm-members-shell--plain">
        <div class="container">
            <?php bootscore_child_oegkm_member_nav('overview'); ?>

            <header class="oegkm-members-section-header" aria-labelledby="oegkm-members-title">
                <p class="oegkm-members-eyebrow"><?php esc_html_e('Mitgliederbereich', 'bootscore-child-oegkm'); ?></p>
                <h1 id="oegkm-members-title"><?php the_title(); ?></h1>
                <p>
                    <?php
                    printf(
                        esc_html__('Willkommen, %s. Hier finden Sie geschützte Inhalte und Services der ÖGKM.', 'bootscore-child-oegkm'),
                        esc_html($current_user->display_name ?: $current_user->user_login)
                    );
                    ?>
                </p>
                <div class="oegkm-members-section-header__actions">
                    <a class="oegkm-members-button" href="<?php echo esc_url($profile_url); ?>"><?php esc_html_e('Mein Profil', 'bootscore-child-oegkm'); ?></a>
                    <a class="oegkm-members-textlink" href="<?php echo esc_url($logout_url); ?>"><?php esc_html_e('Abmelden', 'bootscore-child-oegkm'); ?></a>
                </div>
            </header>
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
