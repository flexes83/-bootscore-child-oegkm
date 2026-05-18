<?php
if (!defined('ABSPATH')) { exit; }
bootscore_child_oegkm_require_member_access();
get_header();
?>
<main id="primary" class="site-main oegkm-members-page">
  <section class="oegkm-members-hero oegkm-members-hero--compact" aria-labelledby="oegkm-gallery-title">
    <div class="container oegkm-members-hero__inner"><div>
      <p class="oegkm-members-eyebrow"><?php esc_html_e('Mitgliederbereich', 'bootscore-child-oegkm'); ?></p>
      <h1 id="oegkm-gallery-title"><?php esc_html_e('Bildergalerien', 'bootscore-child-oegkm'); ?></h1>
      <p class="oegkm-members-hero__intro"><?php esc_html_e('Geschützte Bildsammlungen und Eindrücke aus der ÖGKM.', 'bootscore-child-oegkm'); ?></p>
    </div></div>
  </section>
  <section class="oegkm-members-shell"><div class="container">
    <?php bootscore_child_oegkm_member_nav('galleries'); ?>
    <?php if (have_posts()) : ?><div class="oegkm-member-list-grid">
      <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('oegkm-member-card'); ?>>
          <a class="oegkm-member-card__image" href="<?php the_permalink(); ?>"><?php if (has_post_thumbnail()) : the_post_thumbnail('large'); else : ?><span></span><?php endif; ?></a>
          <div class="oegkm-member-card__body"><p class="oegkm-members-panel__label"><?php echo esc_html(get_the_date()); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php if (has_excerpt()) : ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?></div>
        </article>
      <?php endwhile; ?>
    </div><?php the_posts_pagination(); else : ?><div class="oegkm-members-message"><h2><?php esc_html_e('Noch keine Galerien vorhanden.', 'bootscore-child-oegkm'); ?></h2></div><?php endif; ?>
  </div></section>
</main>
<?php get_footer();
