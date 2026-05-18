<?php
/**
 * Template Name: ÖGKM Mitgliederliste
 * Template Post Type: page
 */

if (!defined('ABSPATH')) {
    exit;
}

bootscore_child_oegkm_require_member_access();

get_header();

$search = isset($_GET['member_search']) ? sanitize_text_field(wp_unslash($_GET['member_search'])) : '';
$type = isset($_GET['member_type']) ? sanitize_text_field(wp_unslash($_GET['member_type'])) : '';

$meta_query = [
    'relation' => 'AND',
    [
        'relation' => 'OR',
        [
            'key' => 'oegkm_member_hide_directory',
            'compare' => 'NOT EXISTS',
        ],
        [
            'key' => 'oegkm_member_hide_directory',
            'value' => ['1', 'on', 'true', 'yes'],
            'compare' => 'NOT IN',
        ],
    ],
];

if ($type !== '') {
    $meta_query[] = [
        'key' => 'oegkm_member_type',
        'value' => $type,
        'compare' => '=',
    ];
}

$query_args = [
    'role__in' => [OEGKM_MEMBER_ROLE],
    'number' => 500,
    'orderby' => 'meta_value',
    'meta_key' => 'last_name',
    'order' => 'ASC',
    'meta_query' => $meta_query,
];

if ($search !== '') {
    $query_args['search'] = '*' . esc_attr($search) . '*';
    $query_args['search_columns'] = ['user_login', 'user_email', 'display_name'];
}

$user_query = new WP_User_Query($query_args);
$members = $user_query->get_results();

if ($search !== '') {
    $members = array_values(array_filter($members, static function (WP_User $user) use ($search): bool {
        $needle = mb_strtolower($search);
        $haystack = mb_strtolower(implode(' ', [
            $user->display_name,
            $user->first_name,
            $user->last_name,
            get_user_meta($user->ID, 'oegkm_member_title', true),
            get_user_meta($user->ID, 'oegkm_member_institution', true),
            get_user_meta($user->ID, 'oegkm_member_city', true),
            get_user_meta($user->ID, 'oegkm_member_type', true),
        ]));

        return str_contains($haystack, $needle);
    }));
}

$member_types = [];
$type_query = new WP_User_Query([
    'role__in' => [OEGKM_MEMBER_ROLE],
    'number' => 500,
    'fields' => ['ID'],
]);
foreach ($type_query->get_results() as $type_user) {
    $value = trim((string) get_user_meta((int) $type_user->ID, 'oegkm_member_type', true));
    if ($value !== '') {
        $member_types[$value] = $value;
    }
}
natcasesort($member_types);
?>

<main id="primary" <?php post_class('site-main oegkm-members-page oegkm-members-directory-page'); ?>>
    <section class="oegkm-members-shell">
        <div class="container">
            <?php bootscore_child_oegkm_member_nav('members'); ?>

            <header class="oegkm-members-section-header">
                <p class="oegkm-members-eyebrow"><?php esc_html_e('Mitgliederbereich', 'bootscore-child-oegkm'); ?></p>
                <h1><?php the_title(); ?></h1>
                <p><?php esc_html_e('Verzeichnis der ÖGKM-Mitglieder. Ausgeblendete Profile werden hier nicht angezeigt.', 'bootscore-child-oegkm'); ?></p>
            </header>

            <form class="oegkm-member-directory-filter" method="get">
                <label>
                    <span><?php esc_html_e('Suche', 'bootscore-child-oegkm'); ?></span>
                    <input type="search" name="member_search" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Name, Institution oder Ort', 'bootscore-child-oegkm'); ?>">
                </label>
                <label>
                    <span><?php esc_html_e('Mitgliedsart', 'bootscore-child-oegkm'); ?></span>
                    <select name="member_type">
                        <option value=""><?php esc_html_e('Alle', 'bootscore-child-oegkm'); ?></option>
                        <?php foreach ($member_types as $member_type) : ?>
                            <option value="<?php echo esc_attr($member_type); ?>" <?php selected($type, $member_type); ?>><?php echo esc_html($member_type); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button class="oegkm-members-button" type="submit"><?php esc_html_e('Filtern', 'bootscore-child-oegkm'); ?></button>
            </form>

            <div class="oegkm-member-directory-count">
                <?php printf(esc_html__('%d Mitglieder gefunden', 'bootscore-child-oegkm'), count($members)); ?>
            </div>

            <?php if ($members) : ?>
                <div class="oegkm-member-directory-grid">
                    <?php foreach ($members as $member) :
                        $user_id = (int) $member->ID;
                        $title = bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_title');
                        $title_after = bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_title_after');
                        $institution = bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_institution');
                        $department = bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_department');
                        $city = bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_city');
                        $country = bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_country');
                        $website = bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_website');
                        $member_type = bootscore_child_oegkm_member_meta($user_id, 'oegkm_member_type');
                        $name = trim(sprintf('%s %s %s %s', $title, $member->first_name ?: '', $member->last_name ?: $member->display_name, $title_after));
                        ?>
                        <article class="oegkm-member-card">
                            <div class="oegkm-member-card__initial" aria-hidden="true"><?php echo esc_html(mb_substr($member->last_name ?: $member->display_name, 0, 1)); ?></div>
                            <div class="oegkm-member-card__body">
                                <?php if ($member_type) : ?>
                                    <p class="oegkm-member-card__type"><?php echo esc_html($member_type); ?></p>
                                <?php endif; ?>
                                <h2><?php echo esc_html($name); ?></h2>
                                <?php if ($institution) : ?>
                                    <p class="oegkm-member-card__institution"><?php echo esc_html($institution); ?></p>
                                <?php endif; ?>
                                <?php if ($department) : ?>
                                    <p><?php echo esc_html($department); ?></p>
                                <?php endif; ?>
                                <?php if ($city || $country) : ?>
                                    <p class="oegkm-member-card__location"><?php echo esc_html(trim($city . ($city && $country ? ', ' : '') . $country)); ?></p>
                                <?php endif; ?>
                                <?php if ($website) : ?>
                                    <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener"><?php esc_html_e('Website öffnen', 'bootscore-child-oegkm'); ?> →</a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="oegkm-members-message">
                    <p><?php esc_html_e('Keine Mitglieder gefunden.', 'bootscore-child-oegkm'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
get_footer();
