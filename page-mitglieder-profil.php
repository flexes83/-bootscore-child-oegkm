<?php
/**
 * Template Name: ÖGKM Mitgliederprofil
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
    exit;
}

bootscore_child_oegkm_require_member_access();

$current_user = wp_get_current_user();
$user_id = (int) $current_user->ID;
$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['oegkm_profile_action'])) {
    check_admin_referer('oegkm_update_profile', 'oegkm_profile_nonce');

    $action = sanitize_key(wp_unslash($_POST['oegkm_profile_action']));

    if ($action === 'profile') {
        $first_name = sanitize_text_field(wp_unslash($_POST['first_name'] ?? ''));
        $last_name = sanitize_text_field(wp_unslash($_POST['last_name'] ?? ''));
        $display_name = trim($first_name . ' ' . $last_name);
        $email = sanitize_email(wp_unslash($_POST['user_email'] ?? ''));

        if (!$email || !is_email($email)) {
            $errors[] = __('Bitte geben Sie eine gültige E-Mail-Adresse ein.', 'bootscore-child-oegkm');
        } else {
            $update = wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $display_name ?: $current_user->display_name,
                'user_email' => $email,
            ]);

            if (is_wp_error($update)) {
                $errors[] = $update->get_error_message();
            } else {
                $meta_fields = [
                    'oegkm_member_title',
                    'oegkm_member_institution',
                    'oegkm_member_phone',
                    'oegkm_member_street',
                    'oegkm_member_zip',
                    'oegkm_member_city',
                    'oegkm_member_title_after',
                    'oegkm_member_addition',
                    'oegkm_member_department',
                    'oegkm_member_country',
                    'oegkm_member_website',
                    'oegkm_member_type',
                ];

                foreach ($meta_fields as $field) {
                    $value = wp_unslash($_POST[$field] ?? '');
                    if ($field === 'oegkm_member_website') {
                        update_user_meta($user_id, $field, esc_url_raw($value));
                    } else {
                        update_user_meta($user_id, $field, sanitize_text_field($value));
                    }
                }

                update_user_meta($user_id, 'oegkm_member_hide_directory', isset($_POST['oegkm_member_hide_directory']) ? '1' : '');

                $messages[] = __('Ihre Profildaten wurden gespeichert.', 'bootscore-child-oegkm');
                $current_user = wp_get_current_user();
            }
        }
    }

    if ($action === 'password') {
        $pass1 = (string) wp_unslash($_POST['pass1'] ?? '');
        $pass2 = (string) wp_unslash($_POST['pass2'] ?? '');

        if ($pass1 === '' || $pass2 === '') {
            $errors[] = __('Bitte füllen Sie beide Passwortfelder aus.', 'bootscore-child-oegkm');
        } elseif ($pass1 !== $pass2) {
            $errors[] = __('Die eingegebenen Passwörter stimmen nicht überein.', 'bootscore-child-oegkm');
        } elseif (strlen($pass1) < 10) {
            $errors[] = __('Das Passwort sollte mindestens 10 Zeichen lang sein.', 'bootscore-child-oegkm');
        } else {
            wp_set_password($pass1, $user_id);
            wp_set_auth_cookie($user_id, true);
            $messages[] = __('Ihr Passwort wurde geändert.', 'bootscore-child-oegkm');
        }
    }
}

get_header();
?>

<main id="primary" <?php post_class('site-main oegkm-members-page oegkm-profile-page'); ?>>
    <section class="oegkm-members-shell oegkm-members-shell--plain">
        <div class="container">
            <?php bootscore_child_oegkm_member_nav('profile'); ?>

            <h1 class="screen-reader-text"><?php the_title(); ?></h1>

            <?php foreach ($messages as $message) : ?>
                <div class="oegkm-members-alert oegkm-members-alert--success"><?php echo esc_html($message); ?></div>
            <?php endforeach; ?>
            <?php foreach ($errors as $error) : ?>
                <div class="oegkm-members-alert oegkm-members-alert--error"><?php echo esc_html($error); ?></div>
            <?php endforeach; ?>

            <div class="oegkm-profile-layout">
                <form class="oegkm-profile-form" method="post">
                    <?php wp_nonce_field('oegkm_update_profile', 'oegkm_profile_nonce'); ?>
                    <input type="hidden" name="oegkm_profile_action" value="profile">

                    <div class="oegkm-profile-form__head">
                        <p class="oegkm-members-panel__label"><?php esc_html_e('Stammdaten', 'bootscore-child-oegkm'); ?></p>
                        <h2><?php esc_html_e('Persönliche Daten', 'bootscore-child-oegkm'); ?></h2>
                    </div>

                    <div class="oegkm-form-grid">
                        <label>
                            <span><?php esc_html_e('Titel', 'bootscore-child-oegkm'); ?></span>
                            <input type="text" name="oegkm_member_title" value="<?php echo esc_attr(bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_title')); ?>">
                        </label>
                        <label>
                            <span><?php esc_html_e('Vorname', 'bootscore-child-oegkm'); ?></span>
                            <input type="text" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>">
                        </label>
                        <label>
                            <span><?php esc_html_e('Nachname', 'bootscore-child-oegkm'); ?></span>
                            <input type="text" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>">
                        </label>
                        <label>
                            <span><?php esc_html_e('E-Mail', 'bootscore-child-oegkm'); ?></span>
                            <input type="email" name="user_email" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                        </label>
                        <label class="oegkm-form-grid__wide">
                            <span><?php esc_html_e('Institution / Praxis', 'bootscore-child-oegkm'); ?></span>
                            <input type="text" name="oegkm_member_institution" value="<?php echo esc_attr(bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_institution')); ?>">
                        </label>
                        <label>
                            <span><?php esc_html_e('Telefon', 'bootscore-child-oegkm'); ?></span>
                            <input type="text" name="oegkm_member_phone" value="<?php echo esc_attr(bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_phone')); ?>">
                        </label>
                        <label>
                            <span><?php esc_html_e('Straße', 'bootscore-child-oegkm'); ?></span>
                            <input type="text" name="oegkm_member_street" value="<?php echo esc_attr(bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_street')); ?>">
                        </label>
                        <label>
                            <span><?php esc_html_e('PLZ', 'bootscore-child-oegkm'); ?></span>
                            <input type="text" name="oegkm_member_zip" value="<?php echo esc_attr(bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_zip')); ?>">
                        </label>
                        <label>
                            <span><?php esc_html_e('Ort', 'bootscore-child-oegkm'); ?></span>
                            <input type="text" name="oegkm_member_city" value="<?php echo esc_attr(bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_city')); ?>">
                        </label>
                    </div>

                    <button class="oegkm-members-button" type="submit"><?php esc_html_e('Daten speichern', 'bootscore-child-oegkm'); ?></button>
                </form>

                <form class="oegkm-profile-form oegkm-profile-form--side" method="post">
                    <?php wp_nonce_field('oegkm_update_profile', 'oegkm_profile_nonce'); ?>
                    <input type="hidden" name="oegkm_profile_action" value="password">

                    <div class="oegkm-profile-form__head">
                        <p class="oegkm-members-panel__label"><?php esc_html_e('Sicherheit', 'bootscore-child-oegkm'); ?></p>
                        <h2><?php esc_html_e('Passwort ändern', 'bootscore-child-oegkm'); ?></h2>
                    </div>

                    <label>
                        <span><?php esc_html_e('Neues Passwort', 'bootscore-child-oegkm'); ?></span>
                        <input type="password" name="pass1" autocomplete="new-password">
                    </label>
                    <label>
                        <span><?php esc_html_e('Neues Passwort wiederholen', 'bootscore-child-oegkm'); ?></span>
                        <input type="password" name="pass2" autocomplete="new-password">
                    </label>

                    <button class="oegkm-members-button" type="submit"><?php esc_html_e('Passwort ändern', 'bootscore-child-oegkm'); ?></button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();
