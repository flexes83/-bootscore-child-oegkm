<?php
if (!defined('ABSPATH')) { exit; }
bootscore_child_oegkm_require_member_access();
get_header();
$images = function_exists('get_field') ? (array) get_field('oegkm_gallery_images') : [];
?>
<main id="primary" class="site-main oegkm-members-page">
<?php while (have_posts()) : the_post(); ?>
  <section class="oegkm-members-hero oegkm-members-hero--compact" aria-labelledby="oegkm-gallery-single-title"><div class="container oegkm-members-hero__inner"><div>
    <p class="oegkm-members-eyebrow"><?php esc_html_e('Bildergalerie', 'bootscore-child-oegkm'); ?></p><h1 id="oegkm-gallery-single-title"><?php the_title(); ?></h1><?php if (has_excerpt()) : ?><p class="oegkm-members-hero__intro"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?>
  </div></div></section>
  <section class="oegkm-members-shell"><div class="container">
    <?php bootscore_child_oegkm_member_nav('galleries'); ?>
    <?php if (trim(get_the_content()) !== '') : ?><div class="oegkm-members-content oegkm-content-flow"><?php the_content(); ?></div><?php endif; ?>
    <?php if ($images) : ?><div class="oegkm-member-gallery-grid"><?php foreach ($images as $image) : $full=$image['url']??''; $thumb=$image['sizes']['large']??$full; $alt=$image['alt']??''; ?><a href="<?php echo esc_url($full); ?>" target="_blank" rel="noopener" class="oegkm-member-gallery-item"><img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($alt); ?>"></a><?php endforeach; ?></div><?php endif; ?>
  </div></section>
<?php endwhile; ?>
</main>
<?php get_footer();
