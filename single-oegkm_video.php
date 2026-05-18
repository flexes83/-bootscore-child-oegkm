<?php
if (!defined('ABSPATH')) { exit; }
bootscore_child_oegkm_require_member_access();
get_header();
$embed = function_exists('get_field') ? (string) get_field('oegkm_video_embed') : '';
?>
<main id="primary" class="site-main oegkm-members-page">
<?php while (have_posts()) : the_post(); ?>
  <section class="oegkm-members-hero oegkm-members-hero--compact" aria-labelledby="oegkm-video-single-title"><div class="container oegkm-members-hero__inner"><div>
    <p class="oegkm-members-eyebrow"><?php esc_html_e('Video', 'bootscore-child-oegkm'); ?></p><h1 id="oegkm-video-single-title"><?php the_title(); ?></h1><?php if (has_excerpt()) : ?><p class="oegkm-members-hero__intro"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
  </div></div></section>
  <section class="oegkm-members-shell"><div class="container">
    <?php bootscore_child_oegkm_member_nav('videos'); ?>
    <?php if ($embed) : ?><div class="oegkm-member-video-embed"><?php echo $embed; ?></div><?php endif; ?>
    <?php if (trim(get_the_content()) !== '') : ?><div class="oegkm-members-content oegkm-content-flow"><?php the_content(); ?></div><?php endif; ?>
  </div></section>
<?php endwhile; ?>
</main>
<?php get_footer();
