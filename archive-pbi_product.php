<?php get_header(); ?>
<section class="pbi-page-hero"><div class="pbi-wrap"><div class="pbi-kicker">Products</div><h1 class="pbi-title">Find the right print<span class="pbi-dot">.</span></h1><p class="pbi-sub">Start with what you need. We’ll help with the rest.</p></div></section>
<section class="pbi-section"><div class="pbi-wrap"><div class="pbi-category-grid">
<?php if(have_posts()): while(have_posts()):the_post(); $pbi_card_image=pbi_product_image_url(get_the_ID(),'pbi-card'); ?>
<a class="pbi-category-card" href="<?php the_permalink(); ?>"><div class="pbi-category-card__visual <?php echo $pbi_card_image?'':'pbi-category-card__visual--fallback'; ?>"><?php if($pbi_card_image): ?><img src="<?php echo esc_url($pbi_card_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy"><?php endif; ?></div><div class="pbi-category-card__body"><strong><?php the_title(); ?></strong><span class="pbi-arrow">↗</span></div></a>
<?php endwhile; endif; ?>
</div></div></section>
<?php get_template_part('template-parts/cta'); get_footer(); ?>
