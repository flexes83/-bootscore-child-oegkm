<?php
/**
 * Template Name: ÖGKM Mitglieder-Medienarchiv
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
    exit;
}

bootscore_child_oegkm_require_member_access();

get_header();

$today = current_time('Y-m-d');
$archive_url = get_permalink();
$selected_event_id = isset($_GET['media_event']) ? absint($_GET['media_event']) : 0;
$preview_limit = 36;

$normalize_media_image = static function ($image): array {
    $image_id = 0;
    $full_url = '';
    $thumb_url = '';
    $alt = '';

    if (is_array($image)) {
        $image_id = (int) ($image['ID'] ?? $image['id'] ?? 0);
        $full_url = (string) ($image['url'] ?? '');
        $alt = (string) ($image['alt'] ?? $image['title'] ?? '');
    } elseif (is_numeric($image)) {
        $image_id = (int) $image;
    } elseif (is_string($image)) {
        $full_url = $image;
        $image_id = attachment_url_to_postid($full_url);
    }

    if ($image_id) {
        $full_url = (string) (wp_get_attachment_image_url($image_id, 'full') ?: $full_url);
        $thumb_url = (string) (wp_get_attachment_image_url($image_id, 'medium_large') ?: $full_url);
        $alt = $alt ?: (string) get_post_meta($image_id, '_wp_attachment_image_alt', true);
    } else {
        $thumb_url = $full_url;
    }

    return [
        'id' => $image_id,
        'full' => $full_url,
        'thumb' => $thumb_url,
        'alt' => $alt,
    ];
};

$get_youtube_id = static function (string $value): string {
    $value = html_entity_decode(wp_unslash($value), ENT_QUOTES, get_bloginfo('charset') ?: 'UTF-8');
    $candidate = $value;

    if (preg_match('~src=["\']([^"\']+)["\']~i', $value, $src_match)) {
        $candidate = $src_match[1];
    }

    if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?[^"\']*v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $candidate, $matches)) {
        return $matches[1];
    }

    $query = (string) wp_parse_url($candidate, PHP_URL_QUERY);
    if ($query !== '') {
        parse_str($query, $params);
        if (!empty($params['v']) && is_string($params['v']) && preg_match('~^[A-Za-z0-9_-]{6,}$~', $params['v'])) {
            return $params['v'];
        }
    }

    return '';
};

$find_video_value = static function ($value) use (&$find_video_value): string {
    if (is_string($value)) {
        return trim($value);
    }

    if (!is_array($value)) {
        return '';
    }

    foreach (['url', 'video_url', 'embed', 'oembed', 'html', 'value'] as $key) {
        if (!empty($value[$key])) {
            $found = $find_video_value($value[$key]);
            if ($found !== '') {
                return $found;
            }
        }
    }

    foreach ($value as $item) {
        $found = $find_video_value($item);
        if ($found !== '' && preg_match('~(?:youtube|youtu\.be|vimeo|iframe)~i', $found)) {
            return $found;
        }
    }

    return '';
};

$get_event_media_totals = static function (array $sections): array {
    $images = 0;
    $videos = 0;

    foreach ($sections as $section) {
        $section_images = $section['images'] ?? [];
        $section_videos = $section['videos'] ?? [];
        $images += is_array($section_images) ? count($section_images) : 0;
        $videos += is_array($section_videos) ? count($section_videos) : 0;
    }

    return [$images, $videos];
};

$events = get_posts([
    'post_type' => 'veranstaltung',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
    'fields' => 'all',
]);

$events = array_values(array_filter($events, static function ($event) use ($today): bool {
    $sections = bootscore_child_oegkm_get_event_media_sections((int) $event->ID);
    if (!$sections) {
        return false;
    }

    $end_date = (string) get_post_meta((int) $event->ID, '_oegkm_event_end_date', true);
    if ($end_date !== '') {
        return $end_date < $today;
    }

    return true;
}));

usort($events, static function ($a, $b): int {
    $date_a = (string) get_post_meta((int) $a->ID, '_oegkm_event_start_date', true);
    $date_b = (string) get_post_meta((int) $b->ID, '_oegkm_event_start_date', true);

    if (!$date_a && preg_match('/(20\d{2})/', get_the_title($a), $match_a)) {
        $date_a = $match_a[1] . '-01-01';
    }
    if (!$date_b && preg_match('/(20\d{2})/', get_the_title($b), $match_b)) {
        $date_b = $match_b[1] . '-01-01';
    }

    return strcmp($date_b, $date_a);
});

$selected_event = null;
if ($selected_event_id) {
    foreach ($events as $event) {
        if ((int) $event->ID === $selected_event_id) {
            $selected_event = $event;
            break;
        }
    }
}
?>

<main id="primary" <?php post_class('site-main oegkm-members-page oegkm-media-archive-page'); ?>>
    <section class="oegkm-members-shell oegkm-members-shell--media-archive">
        <div class="container">
            <?php bootscore_child_oegkm_member_nav('media'); ?>

            <header class="oegkm-media-archive-intro" aria-labelledby="oegkm-media-title">
                <p class="oegkm-members-eyebrow"><?php esc_html_e('Mitgliederbereich', 'bootscore-child-oegkm'); ?></p>
                <h1 id="oegkm-media-title"><?php the_title(); ?></h1>
                <p><?php esc_html_e('Bildergalerien und Videomitschnitte vergangener Veranstaltungen.', 'bootscore-child-oegkm'); ?></p>
            </header>

            <?php if ($selected_event) : ?>
                <?php
                $sections = bootscore_child_oegkm_get_event_media_sections((int) $selected_event->ID);
                [$total_images, $total_videos] = $get_event_media_totals($sections);
                ?>
                <div class="oegkm-media-archive-toolbar">
                    <a class="oegkm-media-back" href="<?php echo esc_url($archive_url); ?>">← <?php esc_html_e('Zurück zur Übersicht', 'bootscore-child-oegkm'); ?></a>
                </div>

                <article class="oegkm-media-event-detail">
                    <header class="oegkm-media-event-detail__header">
                        <p class="oegkm-members-panel__label"><?php echo esc_html(bootscore_child_oegkm_event_date_label((int) $selected_event->ID)); ?></p>
                        <h2><?php echo esc_html(get_the_title($selected_event)); ?></h2>
                        <div class="oegkm-event-media-card__counts">
                            <?php if ($total_images) : ?>
                                <span><?php echo esc_html(sprintf(_n('%d Bild', '%d Bilder', $total_images, 'bootscore-child-oegkm'), $total_images)); ?></span>
                            <?php endif; ?>
                            <?php if ($total_videos) : ?>
                                <span><?php echo esc_html(sprintf(_n('%d Video', '%d Videos', $total_videos, 'bootscore-child-oegkm'), $total_videos)); ?></span>
                            <?php endif; ?>
                        </div>
                    </header>

                    <div class="oegkm-event-media-sections">
                        <?php foreach ($sections as $section_index => $section) :
                            $section_title = $section['title'] ?? sprintf(__('Medienbereich %d', 'bootscore-child-oegkm'), $section_index + 1);
                            $section_description = $section['description'] ?? '';
                            $images = isset($section['images']) && is_array($section['images']) ? $section['images'] : [];
                            $videos = isset($section['videos']) && is_array($section['videos']) ? $section['videos'] : [];
                            $group_id = 'event-' . (int) $selected_event->ID . '-section-' . ($section_index + 1);
                            ?>
                            <section class="oegkm-event-media-section">
                                <header class="oegkm-event-media-section__header">
                                    <div>
                                        <h3><?php echo esc_html($section_title); ?></h3>
                                        <?php if ($section_description) : ?>
                                            <p><?php echo wp_kses_post($section_description); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="oegkm-event-media-section__meta">
                                        <?php if ($images) : ?>
                                            <span><?php echo esc_html(sprintf(_n('%d Bild', '%d Bilder', count($images), 'bootscore-child-oegkm'), count($images))); ?></span>
                                        <?php endif; ?>
                                        <?php if ($videos) : ?>
                                            <span><?php echo esc_html(sprintf(_n('%d Video', '%d Videos', count($videos), 'bootscore-child-oegkm'), count($videos))); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </header>

                                <?php if ($images) : ?>
                                    <div class="oegkm-event-media-gallery" aria-label="<?php echo esc_attr(sprintf(__('Bildergalerie %s', 'bootscore-child-oegkm'), $section_title)); ?>">
                                        <?php foreach ($images as $image_index => $image) :
                                            $media_image = $normalize_media_image($image);
                                            if (!$media_image['full']) {
                                                continue;
                                            }
                                            $is_deferred = $image_index >= $preview_limit;
                                            $caption = $media_image['alt'] ?: sprintf(__('Bild %d aus %s', 'bootscore-child-oegkm'), $image_index + 1, $section_title);
                                            ?>
                                            <a class="oegkm-event-media-gallery__item <?php echo $is_deferred ? 'is-deferred' : ''; ?>" href="<?php echo esc_url($media_image['full']); ?>" data-oegkm-lightbox="<?php echo esc_attr($group_id); ?>" data-caption="<?php echo esc_attr($caption); ?>" <?php echo $is_deferred ? 'hidden' : ''; ?>>
                                                <?php if ($is_deferred) : ?>
                                                    <img data-src="<?php echo esc_url($media_image['thumb']); ?>" alt="<?php echo esc_attr($caption); ?>" loading="lazy" decoding="async">
                                                <?php else : ?>
                                                    <img src="<?php echo esc_url($media_image['thumb']); ?>" alt="<?php echo esc_attr($caption); ?>" loading="lazy" decoding="async">
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>

                                    <?php if (count($images) > $preview_limit) : ?>
                                        <button class="oegkm-event-media-more" type="button" data-oegkm-show-gallery>
                                            <?php echo esc_html(sprintf(__('%d weitere Bilder anzeigen', 'bootscore-child-oegkm'), count($images) - $preview_limit)); ?>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($videos) : ?>
                                    <div class="oegkm-event-media-videos">
                                        <?php foreach ($videos as $video_index => $video) :
                                            $video_title = $video['title'] ?? sprintf(__('Video %d', 'bootscore-child-oegkm'), $video_index + 1);
                                            $video_url = $find_video_value($video['url'] ?? '');
                                            if ($video_url === '') {
                                                $video_url = $find_video_value($video);
                                            }
                                            $video_description = $video['description'] ?? '';
                                            $youtube_id = $get_youtube_id($video_url);
                                            $embed_html = '';
                                            if (!$youtube_id && $video_url !== '') {
                                                $embed_html = stripos($video_url, '<iframe') !== false ? $video_url : (string) wp_oembed_get($video_url);
                                            }
                                            if (!$youtube_id && $embed_html === '') {
                                                continue;
                                            }
                                            $thumbnail = $youtube_id ? 'https://img.youtube.com/vi/' . rawurlencode($youtube_id) . '/hqdefault.jpg' : '';
                                            ?>
                                            <article class="oegkm-event-media-video">
                                                <?php if ($youtube_id) : ?>
                                                    <button class="oegkm-video-placeholder" type="button" data-youtube-id="<?php echo esc_attr($youtube_id); ?>" aria-label="<?php echo esc_attr(sprintf(__('Video laden: %s', 'bootscore-child-oegkm'), $video_title)); ?>">
                                                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($video_title); ?>" loading="lazy" decoding="async">
                                                        <span class="oegkm-video-placeholder__play" aria-hidden="true">▶</span>
                                                    </button>
                                                <?php else : ?>
                                                    <div class="oegkm-event-media-video__embed">
                                                        <?php echo wp_kses($embed_html, [
                                                            'iframe' => [
                                                                'src' => true,
                                                                'title' => true,
                                                                'width' => true,
                                                                'height' => true,
                                                                'frameborder' => true,
                                                                'allow' => true,
                                                                'allowfullscreen' => true,
                                                                'loading' => true,
                                                                'referrerpolicy' => true,
                                                            ],
                                                        ]); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="oegkm-event-media-video__body">
                                                    <h4><?php echo esc_html($video_title); ?></h4>
                                                    <?php if ($video_description) : ?>
                                                        <p><?php echo wp_kses_post($video_description); ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php else : ?>
                <div class="oegkm-event-media-list oegkm-event-media-list--overview">
                    <?php if ($events) : ?>
                        <?php foreach ($events as $event) :
                            $sections = bootscore_child_oegkm_get_event_media_sections((int) $event->ID);
                            [$total_images, $total_videos] = $get_event_media_totals($sections);
                            $event_url = add_query_arg('media_event', (int) $event->ID, $archive_url);
                            ?>
                            <a class="oegkm-event-media-overview-card" href="<?php echo esc_url($event_url); ?>">
                                <span class="oegkm-event-media-overview-card__body">
                                    <span class="oegkm-members-panel__label"><?php echo esc_html(bootscore_child_oegkm_event_date_label((int) $event->ID)); ?></span>
                                    <span class="oegkm-event-media-overview-card__title"><?php echo esc_html(get_the_title($event)); ?></span>
                                    <span class="oegkm-event-media-card__counts">
                                        <?php if ($total_images) : ?>
                                            <span><?php echo esc_html(sprintf(_n('%d Bild', '%d Bilder', $total_images, 'bootscore-child-oegkm'), $total_images)); ?></span>
                                        <?php endif; ?>
                                        <?php if ($total_videos) : ?>
                                            <span><?php echo esc_html(sprintf(_n('%d Video', '%d Videos', $total_videos, 'bootscore-child-oegkm'), $total_videos)); ?></span>
                                        <?php endif; ?>
                                    </span>
                                </span>
                                <span class="oegkm-event-media-overview-card__arrow" aria-hidden="true">→</span>
                            </a>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="oegkm-members-message">
                            <p class="oegkm-members-eyebrow"><?php esc_html_e('Medienarchiv', 'bootscore-child-oegkm'); ?></p>
                            <h2><?php esc_html_e('Noch keine Veranstaltungsmedien vorhanden.', 'bootscore-child-oegkm'); ?></h2>
                            <p><?php esc_html_e('Sobald Medienbereiche in vergangenen Veranstaltungen gepflegt wurden, erscheinen sie hier automatisch.', 'bootscore-child-oegkm'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
