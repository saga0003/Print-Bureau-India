<?php get_header(); ?>
<section class="pbi-page-hero"><div class="pbi-wrap"><div class="pbi-kicker">Products</div><h1 class="pbi-title">Printing services across Karnataka<span class="pbi-dot">.</span></h1><p class="pbi-sub">Business printing, marketing print, packaging, books, labels, stationery, notebooks, diaries, banners and institutional print from Chikmagalur for customers across Karnataka.</p></div></section>
<section class="pbi-section"><div class="pbi-wrap"><div class="pbi-category-grid">
<?php $products=pbi_get_products(50); if($products->have_posts()): while($products->have_posts()):$products->the_post(); $pbi_card_image=pbi_product_image_url(get_the_ID(),'pbi-card'); ?>
<a class="pbi-category-card" href="<?php the_permalink(); ?>"><div class="pbi-category-card__visual <?php echo $pbi_card_image?'':'pbi-category-card__visual--fallback'; ?>"><?php if($pbi_card_image): ?><img src="<?php echo esc_url($pbi_card_image); ?>" alt="<?php echo esc_attr(get_the_title().' printing in Karnataka'); ?>" loading="lazy"><?php endif; ?></div><div class="pbi-category-card__body"><strong><?php the_title(); ?></strong><span class="pbi-arrow">↗</span></div></a>
<?php endwhile; wp_reset_postdata(); endif; ?>
</div></div></section>
<?php get_template_part('template-parts/cta'); get_footer(); ?>
