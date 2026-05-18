<?php
/**
 * One-time importer for OEGKM members from a CSV user export.
 *
 * Dry run:
 *   php wp-content/themes/bootscore-child-oegkm-v1.8/scripts/import-oegkm-users.php --file=/absolute/path/user-export.csv --dry-run
 *
 * Import / update:
 *   php wp-content/themes/bootscore-child-oegkm-v1.8/scripts/import-oegkm-users.php --file=/absolute/path/user-export.csv --write
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This importer is CLI only.\n");
}

$options = getopt('', [
    'file:',
    'dry-run',
    'write',
    'create-users::',
    'update-existing::',
    'role::',
    'limit::',
    'send-reset::',
]);

$dry_run = !isset($options['write']);
$file = isset($options['file']) ? (string) $options['file'] : '';
$create_users = bool_option($options, 'create-users', true);
$update_existing = bool_option($options, 'update-existing', true);
$send_reset = bool_option($options, 'send-reset', false);
$role = isset($options['role']) ? sanitize_key_like((string) $options['role']) : 'oegkm_member';
$limit = isset($options['limit']) ? max(0, (int) $options['limit']) : 0;

if (!$file || !is_file($file)) {
    fwrite(STDERR, "Missing CSV file. Use --file=/absolute/path/user-export.csv\n");
    exit(1);
}

$wp_load = find_wp_load(__DIR__);
if (!$wp_load) {
    fwrite(STDERR, "Could not find wp-load.php. Place this script inside the WordPress installation or run it from there.\n");
    exit(1);
}

require_once $wp_load;

if (!function_exists('wp_insert_user')) {
    fwrite(STDERR, "WordPress did not load correctly.\n");
    exit(1);
}

if (!get_role($role)) {
    fwrite(STDERR, "Role `{$role}` does not exist. Is the OEGKM theme active?\n");
    exit(1);
}

$rows = read_csv_assoc($file);

log_line('OEGKM user/member import');
log_line('Mode: ' . ($dry_run ? 'DRY RUN' : 'WRITE'));
log_line('CSV rows: ' . count($rows));
log_line('Create missing users: ' . ($create_users ? 'yes' : 'no'));
log_line('Update existing users: ' . ($update_existing ? 'yes' : 'no'));
log_line('Target role: ' . $role);
log_line('Send reset mails to new users: ' . ($send_reset ? 'yes' : 'no'));
log_line(str_repeat('-', 72));

$stats = [
    'rows' => 0,
    'created' => 0,
    'updated' => 0,
    'skipped' => 0,
    'invalid_email' => 0,
    'would_create' => 0,
    'would_update' => 0,
    'hidden_directory' => 0,
    'reset_sent' => 0,
    'errors' => 0,
];

foreach ($rows as $index => $row) {
    if ($limit > 0 && $stats['rows'] >= $limit) {
        break;
    }

    $stats['rows']++;

    $email = sanitize_email((string) ($row['user_email'] ?? ''));
    if (!$email || !is_email($email)) {
        $stats['invalid_email']++;
        log_line(sprintf('[skip] Row %d: invalid email `%s`', $index + 2, (string) ($row['user_email'] ?? '')));
        continue;
    }

    $first_name = sanitize_text_field((string) ($row['first_name'] ?? ''));
    $last_name = sanitize_text_field((string) ($row['last_name'] ?? ''));
    $display_name = trim((string) ($row['display_name'] ?? ''));
    if ($display_name === '') {
        $display_name = trim($first_name . ' ' . $last_name);
    }
    if ($display_name === '') {
        $display_name = $email;
    }

    $existing = get_user_by('email', $email);
    $user_id = $existing ? (int) $existing->ID : 0;

    $login = sanitize_user((string) ($row['user_login'] ?? ''), true);
    if ($login === '') {
        $login = sanitize_user(current(explode('@', $email)), true);
    }
    if ($login === '') {
        $login = 'member';
    }

    $hide_in_directory = is_truthy((string) ($row['privacy'] ?? '')) ? '1' : '';

    $meta = [
        'oegkm_member_title' => sanitize_text_field((string) ($row['titel_vor'] ?? '')),
        'oegkm_member_title_after' => sanitize_text_field((string) ($row['titel_nach'] ?? '')),
        'oegkm_member_institution' => sanitize_text_field((string) ($row['krankenhaus'] ?? '')),
        'oegkm_member_addition' => sanitize_text_field((string) ($row['zusatz'] ?? '')),
        'oegkm_member_department' => sanitize_text_field((string) ($row['abteilung'] ?? '')),
        'oegkm_member_street' => sanitize_text_field((string) ($row['strasse'] ?? '')),
        'oegkm_member_zip' => sanitize_text_field((string) ($row['plz'] ?? '')),
        'oegkm_member_city' => sanitize_text_field((string) ($row['ort'] ?? '')),
        'oegkm_member_country' => sanitize_text_field((string) ($row['land'] ?? '')),
        'oegkm_member_website' => esc_url_raw((string) ($row['website'] ?? '')),
        'oegkm_member_type' => sanitize_text_field((string) ($row['mitgliedsart'] ?? '')),
        'oegkm_member_hide_directory' => $hide_in_directory,
        'oegkm_legacy_user_login' => sanitize_text_field((string) ($row['user_login'] ?? '')),
        'oegkm_legacy_source_user_id' => sanitize_text_field((string) ($row['source_user_id'] ?? '')),
        'oegkm_legacy_role' => sanitize_text_field((string) ($row['role'] ?? '')),
        'oegkm_user_imported_from_csv' => '1',
    ];

    if ($hide_in_directory === '1') {
        $stats['hidden_directory']++;
    }

    if ($user_id) {
        if (!$update_existing) {
            $stats['skipped']++;
            log_line("[skip] Existing user #{$user_id}: {$email}");
            continue;
        }

        $stats[$dry_run ? 'would_update' : 'updated']++;
        log_line('[user] ' . ($dry_run ? 'Would update' : 'Updating') . " #{$user_id}: {$display_name} <{$email}>");

        if (!$dry_run) {
            $result = wp_update_user([
                'ID' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'display_name' => $display_name,
                'user_email' => $email,
                'user_url' => $meta['oegkm_member_website'],
            ]);

            if (is_wp_error($result)) {
                $stats['errors']++;
                log_line('  [error] ' . $result->get_error_message());
                continue;
            }

            $user = new WP_User($user_id);
            if (!$user->has_cap($role)) {
                $user->add_role($role);
            }

            foreach ($meta as $key => $value) {
                update_user_meta($user_id, $key, $value);
            }
        }

        continue;
    }

    if (!$create_users) {
        $stats['skipped']++;
        log_line("[missing] User not found and create-users disabled: {$email}");
        continue;
    }

    $unique_login = make_unique_login($login);
    $password = wp_generate_password(24, true, true);

    $stats[$dry_run ? 'would_create' : 'created']++;
    log_line('[user] ' . ($dry_run ? 'Would create' : 'Creating') . " {$display_name} <{$email}> as {$unique_login}");

    if (!$dry_run) {
        $user_id = wp_insert_user([
            'user_login' => $unique_login,
            'user_pass' => $password,
            'user_email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $display_name,
            'nickname' => $display_name,
            'user_url' => $meta['oegkm_member_website'],
            'role' => $role,
        ]);

        if (is_wp_error($user_id)) {
            $stats['errors']++;
            log_line('  [error] ' . $user_id->get_error_message());
            continue;
        }

        foreach ($meta as $key => $value) {
            update_user_meta((int) $user_id, $key, $value);
        }

        update_user_meta((int) $user_id, 'oegkm_needs_password_reset', '1');

        if ($send_reset) {
            $reset = retrieve_password($unique_login);
            if ($reset === true) {
                $stats['reset_sent']++;
            } elseif (is_wp_error($reset)) {
                log_line('  [reset mail error] ' . $reset->get_error_message());
            }
        }
    }
}

log_line(str_repeat('-', 72));
foreach ($stats as $key => $value) {
    log_line(str_pad($key, 22) . ': ' . $value);
}
log_line('Done.');

function bool_option(array $options, string $key, bool $default): bool {
    if (!array_key_exists($key, $options)) {
        return $default;
    }
    $value = $options[$key];
    if ($value === false || $value === null || $value === '') {
        return true;
    }
    return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
}

function is_truthy(string $value): bool {
    return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
}

function sanitize_key_like(string $value): string {
    return preg_replace('/[^a-z0-9_\-]/', '', strtolower($value)) ?: '';
}

function read_csv_assoc(string $file): array {
    $handle = fopen($file, 'rb');
    if (!$handle) {
        return [];
    }

    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        return [];
    }

    if (isset($headers[0])) {
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
    }

    $rows = [];
    while (($data = fgetcsv($handle)) !== false) {
        $row = [];
        foreach ($headers as $index => $header) {
            $row[(string) $header] = $data[$index] ?? '';
        }
        $rows[] = $row;
    }

    fclose($handle);
    return $rows;
}

function make_unique_login(string $base): string {
    $base = sanitize_user($base, true);
    if ($base === '') {
        $base = 'member';
    }

    $login = $base;
    $i = 2;

    while (username_exists($login)) {
        $login = $base . '-' . $i;
        $i++;
    }

    return $login;
}

function find_wp_load(string $start): ?string {
    $dir = realpath($start);
    while ($dir && $dir !== dirname($dir)) {
        $candidate = $dir . DIRECTORY_SEPARATOR . 'wp-load.php';
        if (is_file($candidate)) {
            return $candidate;
        }
        $dir = dirname($dir);
    }

    $cwd = getcwd();
    if ($cwd) {
        $candidate = $cwd . DIRECTORY_SEPARATOR . 'wp-load.php';
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function log_line(string $message): void {
    fwrite(STDOUT, $message . PHP_EOL);
}
