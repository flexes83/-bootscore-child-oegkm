<?php
/**
 * One-time importer for OEGKM member media from exported JSON.
 *
 * Put this file e.g. into:
 *   wp-content/themes/bootscore-child-oegkm/scripts/import-oegkm-member-media.php
 *
 * Run from the WordPress root or from the script directory:
 *   php wp-content/themes/bootscore-child-oegkm/scripts/import-oegkm-member-media.php --file=/absolute/path/oegkm-medienimport-aus-bildergalerien.json --dry-run
 *   php wp-content/themes/bootscore-child-oegkm/scripts/import-oegkm-member-media.php --file=/absolute/path/oegkm-medienimport-aus-bildergalerien.json --write --replace-media=1 --create-events=1 --status=publish
 *
 * The script creates/updates posts of post type `veranstaltung` and writes the ACF repeater
 * `oegkm_event_media_sections` directly on the event post.
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
    'create-events::',
    'replace-media::',
    'import-images::',
    'status::',
    'limit::',
    'skip-images',
]);

$dry_run       = !isset($options['write']);
$create_events = bool_option($options, 'create-events', true);
$replace_media = bool_option($options, 'replace-media', false);
$import_images = !isset($options['skip-images']) && bool_option($options, 'import-images', true);
$post_status   = isset($options['status']) ? sanitize_key_like((string) $options['status']) : 'publish';
$limit         = isset($options['limit']) ? max(0, (int) $options['limit']) : 0;
$file          = isset($options['file']) ? (string) $options['file'] : '';

if (!$file || !is_file($file)) {
    fwrite(STDERR, "Missing JSON file. Use --file=/absolute/path/oegkm-medienimport-aus-bildergalerien.json\n");
    exit(1);
}

$wp_load = find_wp_load(__DIR__);
if (!$wp_load) {
    fwrite(STDERR, "Could not find wp-load.php. Place this script inside the WordPress installation or run it from there.\n");
    exit(1);
}

require_once $wp_load;

if (!function_exists('update_field')) {
    fwrite(STDERR, "ACF is not active or update_field() is unavailable. Aborting.\n");
    exit(1);
}

if (!post_type_exists('veranstaltung')) {
    fwrite(STDERR, "Post type `veranstaltung` does not exist. Is the OEGKM theme active?\n");
    exit(1);
}

if ($import_images && !$dry_run) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
}

$raw = file_get_contents($file);
$data = json_decode((string) $raw, true);
if (!is_array($data)) {
    fwrite(STDERR, "Invalid JSON file.\n");
    exit(1);
}

log_line('OEGKM member media import');
log_line('Mode: ' . ($dry_run ? 'DRY RUN' : 'WRITE'));
log_line('Create missing events: ' . ($create_events ? 'yes' : 'no'));
log_line('Replace existing media sections: ' . ($replace_media ? 'yes' : 'no'));
log_line('Import images to media library: ' . ($import_images ? 'yes' : 'no'));
log_line('Default event status: ' . $post_status);
log_line('JSON entries: ' . count($data));
log_line(str_repeat('-', 72));

$stats = [
    'events_found' => 0,
    'events_created' => 0,
    'events_missing' => 0,
    'sections' => 0,
    'images_total' => 0,
    'images_imported' => 0,
    'images_reused' => 0,
    'images_failed' => 0,
    'videos' => 0,
    'updated_events' => 0,
];

$processed = 0;
foreach ($data as $event_entry) {
    if ($limit > 0 && $processed >= $limit) {
        break;
    }
    $processed++;

    $event_title = trim((string) ($event_entry['event_title'] ?? ''));
    if ($event_title === '') {
        log_line('[skip] Event without title');
        continue;
    }

    $event_id = find_event_post_id($event_title);
    if ($event_id) {
        $stats['events_found']++;
        log_line("[event] Found #{$event_id}: {$event_title}");
    } elseif ($create_events) {
        $event_id = create_event_post($event_title, $post_status, $dry_run);
        if ($event_id) {
            $stats['events_created']++;
            log_line('[event] ' . ($dry_run ? 'Would create' : 'Created') . " #{$event_id}: {$event_title}");
        }
    } else {
        $stats['events_missing']++;
        log_line("[missing] Event not found and create-events disabled: {$event_title}");
        continue;
    }

    if (!$event_id) {
        $stats['events_missing']++;
        continue;
    }

    $sections = [];
    $existing_sections = [];
    if (!$replace_media) {
        $existing_sections = get_field('oegkm_event_media_sections', $event_id);
        if (!is_array($existing_sections)) {
            $existing_sections = [];
        }
    }

    foreach (($event_entry['sections'] ?? []) as $section_entry) {
        $title = trim((string) ($section_entry['title'] ?? ''));
        if ($title === '') {
            $title = 'Medien';
        }

        $images = [];
        foreach (($section_entry['images'] ?? []) as $img) {
            $url = is_array($img) ? (string) ($img['href'] ?? $img['url'] ?? '') : (string) $img;
            $url = trim($url);
            if ($url === '') {
                continue;
            }
            $stats['images_total']++;

            if (!$import_images) {
                continue;
            }

            if ($dry_run) {
                $images[] = 0;
                continue;
            }

            $attachment_id = find_or_import_attachment($url, $event_id);
            if ($attachment_id > 0) {
                $images[] = $attachment_id;
                if (get_post_meta($attachment_id, '_oegkm_imported_from_url', true) === $url) {
                    $stats['images_imported']++;
                } else {
                    $stats['images_reused']++;
                }
            } else {
                $stats['images_failed']++;
                log_line("  [image failed] {$url}");
            }
        }

        $videos = [];
        foreach (($section_entry['videos'] ?? []) as $video) {
            if (!is_array($video)) {
                continue;
            }
            $url = trim((string) ($video['src'] ?? $video['url'] ?? ''));
            if ($url === '') {
                continue;
            }
            $url = normalize_youtube_embed_to_watch_url($url);
            $videos[] = [
                'title' => trim((string) ($video['title'] ?? '')),
                'url' => $url,
                'description' => trim((string) ($video['description'] ?? '')),
            ];
            $stats['videos']++;
        }

        $stats['sections']++;
        log_line("  [section] {$title}: " . count($images) . ' images, ' . count($videos) . ' videos');

        $sections[] = [
            'title' => $title,
            'description' => trim((string) ($section_entry['description'] ?? '')),
            'images' => array_values(array_filter($images)),
            'videos' => $videos,
        ];
    }

    if ($dry_run) {
        continue;
    }

    $new_value = $replace_media ? $sections : array_merge($existing_sections, $sections);
    update_field('oegkm_event_media_sections', $new_value, $event_id);
    $stats['updated_events']++;
}

log_line(str_repeat('-', 72));
foreach ($stats as $key => $value) {
    log_line(str_pad($key, 20) . ': ' . $value);
}
log_line('Done.');

function find_wp_load(string $start_dir): ?string {
    $dir = $start_dir;
    for ($i = 0; $i < 10; $i++) {
        $candidate = $dir . '/wp-load.php';
        if (is_file($candidate)) {
            return $candidate;
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            break;
        }
        $dir = $parent;
    }
    $cwd = getcwd();
    if ($cwd) {
        $dir = $cwd;
        for ($i = 0; $i < 10; $i++) {
            $candidate = $dir . '/wp-load.php';
            if (is_file($candidate)) {
                return $candidate;
            }
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }
    }
    return null;
}

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

function sanitize_key_like(string $value): string {
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9_\-]/', '', $value) ?: 'publish';
    return $value;
}

function log_line(string $message): void {
    echo $message . PHP_EOL;
}

function find_event_post_id(string $title): int {
    $exact = get_page_by_title($title, OBJECT, 'veranstaltung');
    if ($exact instanceof WP_Post) {
        return (int) $exact->ID;
    }

    $slug = sanitize_title($title);
    $posts = get_posts([
        'post_type' => 'veranstaltung',
        'name' => $slug,
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
        'numberposts' => 1,
        'fields' => 'ids',
    ]);
    if (!empty($posts)) {
        return (int) $posts[0];
    }

    // Fuzzy fallback: same year + contains key phrase.
    $year = extract_year($title);
    if ($year) {
        $candidates = get_posts([
            'post_type' => 'veranstaltung',
            'post_status' => ['publish', 'draft', 'pending', 'private', 'future'],
            's' => (string) $year,
            'numberposts' => 10,
            'fields' => 'ids',
        ]);
        foreach ($candidates as $id) {
            $candidate_title = get_the_title((int) $id);
            if (stripos($candidate_title, (string) $year) !== false) {
                return (int) $id;
            }
        }
    }

    return 0;
}

function create_event_post(string $title, string $status, bool $dry_run): int {
    if ($dry_run) {
        return -abs(crc32($title));
    }

    $post_id = wp_insert_post([
        'post_type' => 'veranstaltung',
        'post_title' => $title,
        'post_name' => sanitize_title($title),
        'post_status' => $status,
        'post_content' => '',
    ], true);

    if (is_wp_error($post_id)) {
        log_line('[error] Could not create event: ' . $post_id->get_error_message());
        return 0;
    }

    $year = extract_year($title);
    if ($year) {
        update_post_meta((int) $post_id, '_oegkm_event_start_date', $year . '-01-01');
        update_post_meta((int) $post_id, '_oegkm_event_end_date', $year . '-01-01');
    }

    return (int) $post_id;
}

function extract_year(string $text): ?string {
    if (preg_match('/\b(20\d{2})\b/', $text, $m)) {
        return $m[1];
    }
    return null;
}

function find_or_import_attachment(string $url, int $post_id): int {
    $existing = find_attachment_by_url($url);
    if ($existing) {
        return $existing;
    }

    $attachment_id = media_sideload_image($url, $post_id, null, 'id');
    if (is_wp_error($attachment_id)) {
        log_line('  [media_sideload_image] ' . $attachment_id->get_error_message());
        return 0;
    }

    update_post_meta((int) $attachment_id, '_oegkm_imported_from_url', esc_url_raw($url));
    update_post_meta((int) $attachment_id, '_source_url', esc_url_raw($url));
    return (int) $attachment_id;
}

function find_attachment_by_url(string $url): int {
    $id = attachment_url_to_postid($url);
    if ($id) {
        return (int) $id;
    }

    $posts = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => '_source_url',
                'value' => $url,
                'compare' => '=',
            ],
            [
                'key' => '_oegkm_imported_from_url',
                'value' => $url,
                'compare' => '=',
            ],
        ],
        'numberposts' => 1,
        'fields' => 'ids',
    ]);
    if (!empty($posts)) {
        return (int) $posts[0];
    }

    $filename = basename((string) parse_url($url, PHP_URL_PATH));
    if ($filename) {
        $posts = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            's' => pathinfo($filename, PATHINFO_FILENAME),
            'numberposts' => 5,
            'fields' => 'ids',
        ]);
        foreach ($posts as $post_id) {
            $file = get_attached_file((int) $post_id);
            if ($file && basename($file) === $filename) {
                return (int) $post_id;
            }
        }
    }

    return 0;
}

function normalize_youtube_embed_to_watch_url(string $url): string {
    $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (preg_match('~youtube\.com/embed/([^?&/]+)~i', $url, $m)) {
        return 'https://www.youtube.com/watch?v=' . $m[1];
    }
    return $url;
}
