<?php
/**
 * Template Name: ÖGKM Mitglieder-Login
 * Template Post Type: page
 *
 * A branded frontend login page for the OEGKM members area.
 */

if (!defined('ABSPATH')) {
    exit;
}

$login_action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : 'login';
$login_message = '';
$login_error = '';

if (isset($_GET['login'])) {
    $login_status = sanitize_key(wp_unslash($_GET['login']));
    if ($login_status === 'failed') {
        $login_error = __('Die eingegebenen Zugangsdaten konnten nicht zugeordnet werden.', 'bootscore-child-oegkm');
    } elseif ($login_status === 'empty') {
        $login_error = __('Bitte geben Sie Benutzername und Passwort ein.', 'bootscore-child-oegkm');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['oegkm_lostpassword'])) {
    check_admin_referer('oegkm_lostpassword', 'oegkm_lostpassword_nonce');

    $user_login = sanitize_text_field(wp_unslash($_POST['user_login'] ?? ''));
    $_POST['user_login'] = $user_login;
    $result = retrieve_password($user_login);

    if (is_wp_error($result)) {
        $login_error = $result->get_error_message();
        $login_action = 'lostpassword';
    } else {
        $login_message = __('Wenn ein passendes Konto gefunden wurde, erhalten Sie eine E-Mail zum Zurücksetzen des Passworts.', 'bootscore-child-oegkm');
        $login_action = 'login';
    }
}

get_header();

$redirect_to = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : '';
if ($redirect_to === '') {
    $redirect_to = function_exists('bootscore_child_oegkm_members_area_target_url') ? bootscore_child_oegkm_members_area_target_url() : home_url('/mitgliederbereich/');
}

$lost_password_url = add_query_arg('action', 'lostpassword', get_permalink());
$register_url = wp_registration_url();
$guilloche_url = get_stylesheet_directory_uri() . '/assets/img/oegkm-guilloche.png';
?>

<main id="primary" <?php post_class('site-main oegkm-login-page'); ?>>
    <section class="oegkm-login-hero" aria-labelledby="oegkm-login-title">
        <div class="container oegkm-login-hero__inner">
            <img class="oegkm-login-hero__guilloche oegkm-login-hero__guilloche--one" src="<?php echo esc_url($guilloche_url); ?>" alt="" aria-hidden="true" loading="eager">
            <img class="oegkm-login-hero__guilloche oegkm-login-hero__guilloche--two" src="<?php echo esc_url($guilloche_url); ?>" alt="" aria-hidden="true" loading="eager">

            <div class="oegkm-login-hero__copy">
                <p class="oegkm-login-hero__eyebrow"><?php esc_html_e('Mitgliederbereich', 'bootscore-child-oegkm'); ?></p>
                <h1 id="oegkm-login-title"><?php the_title(); ?></h1>
                <div class="oegkm-login-hero__intro">
                    <?php
                    if (has_excerpt()) {
                        the_excerpt();
                    } else {
                        esc_html_e('Melden Sie sich an, um geschützte Inhalte, Informationen und Services der ÖGKM zu nutzen.', 'bootscore-child-oegkm');
                    }
                    ?>
                </div>
            </div>

            <div class="oegkm-login-card" aria-label="<?php esc_attr_e('Loginformular', 'bootscore-child-oegkm'); ?>">
                <?php if ($login_message) : ?>
                    <div class="oegkm-members-alert oegkm-members-alert--success"><?php echo wp_kses_post($login_message); ?></div>
                <?php endif; ?>
                <?php if ($login_error) : ?>
                    <div class="oegkm-members-alert oegkm-members-alert--error"><?php echo wp_kses_post($login_error); ?></div>
                <?php endif; ?>

                <?php if (is_user_logged_in()) : ?>
                    <?php $current_user = wp_get_current_user(); ?>
                    <div class="oegkm-login-card__status-icon" aria-hidden="true">✓</div>
                    <h2><?php esc_html_e('Sie sind angemeldet', 'bootscore-child-oegkm'); ?></h2>
                    <p>
                        <?php
                        printf(
                            esc_html__('Angemeldet als %s.', 'bootscore-child-oegkm'),
                            esc_html($current_user->display_name ?: $current_user->user_login)
                        );
                        ?>
                    </p>
                    <div class="oegkm-login-card__actions">
                        <a class="oegkm-login-button" href="<?php echo esc_url($redirect_to); ?>">
                            <span><?php esc_html_e('Zum Mitgliederbereich', 'bootscore-child-oegkm'); ?></span>
                            <span aria-hidden="true">→</span>
                        </a>
                        <a class="oegkm-login-link" href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>">
                            <?php esc_html_e('Abmelden', 'bootscore-child-oegkm'); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <?php if ($login_action === 'lostpassword') : ?>
                        <h2><?php esc_html_e('Passwort zurücksetzen', 'bootscore-child-oegkm'); ?></h2>
                        <p class="oegkm-login-card__text"><?php esc_html_e('Geben Sie Ihre E-Mail-Adresse oder Ihren Benutzernamen ein. Sie erhalten anschließend einen Link zum Zurücksetzen des Passworts.', 'bootscore-child-oegkm'); ?></p>
                        <form method="post" class="oegkm-lostpassword-form">
                            <?php wp_nonce_field('oegkm_lostpassword', 'oegkm_lostpassword_nonce'); ?>
                            <input type="hidden" name="oegkm_lostpassword" value="1">
                            <p>
                                <label for="oegkm-user-login"><?php esc_html_e('E-Mail oder Benutzername', 'bootscore-child-oegkm'); ?></label>
                                <input id="oegkm-user-login" type="text" name="user_login" autocomplete="username" required>
                            </p>
                            <p class="login-submit">
                                <button class="button button-primary" type="submit"><?php esc_html_e('Link anfordern', 'bootscore-child-oegkm'); ?></button>
                            </p>
                        </form>
                        <div class="oegkm-login-card__footer">
                            <a href="<?php echo esc_url(get_permalink()); ?>"><?php esc_html_e('Zurück zum Login', 'bootscore-child-oegkm'); ?></a>
                        </div>
                    <?php else : ?>
                        <h2><?php esc_html_e('Login', 'bootscore-child-oegkm'); ?></h2>
                        <p class="oegkm-login-card__text"><?php esc_html_e('Bitte melden Sie sich mit Ihren Zugangsdaten an.', 'bootscore-child-oegkm'); ?></p>

                        <?php
                        wp_login_form([
                            'echo'           => true,
                            'redirect'       => $redirect_to,
                            'form_id'        => 'oegkm-member-loginform',
                            'label_username' => __('E-Mail oder Benutzername', 'bootscore-child-oegkm'),
                            'label_password' => __('Passwort', 'bootscore-child-oegkm'),
                            'label_remember' => __('Angemeldet bleiben', 'bootscore-child-oegkm'),
                            'label_log_in'   => __('Einloggen', 'bootscore-child-oegkm'),
                            'remember'       => true,
                        ]);
                        ?>

                        <div class="oegkm-login-card__footer">
                            <a href="<?php echo esc_url($lost_password_url); ?>"><?php esc_html_e('Passwort vergessen?', 'bootscore-child-oegkm'); ?></a>
                            <?php if (get_option('users_can_register')) : ?>
                                <span aria-hidden="true">·</span>
                                <a href="<?php echo esc_url($register_url); ?>"><?php esc_html_e('Registrieren', 'bootscore-child-oegkm'); ?></a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <?php if (trim(get_the_content()) !== '') : ?>
                <section class="oegkm-login-content">
                    <div class="container oegkm-page-content-container">
                        <?php the_content(); ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endwhile; ?>
    <?php endif; ?>
</main>

<?php
get_footer();
