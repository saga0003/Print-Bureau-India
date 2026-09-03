<?php get_header(); ?>
<section class="pbi-section"><div class="pbi-wrap"><div class="pbi-post-grid"><?php if(have_posts()):while(have_posts()):the_post(); ?><a class="pbi-post-card" href="<?php the_permalink(); ?>"><div class="pbi-post-card__img"><?php if(has_post_thumbnail())the_post_thumbnail('pbi-card'); ?></div><div class="pbi-post-card__body"><h3><?php the_title(); ?></h3><p class="pbi-meta"><?php echo esc_html(get_the_date()); ?></p></div></a><?php endwhile;else:?><p>No content yet.</p><?php endif; ?></div></div></section>
<?php get_footer(); ?>
