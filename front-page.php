<?php get_header(); ?>
<section class="pbi-hero">
  <div class="pbi-wrap pbi-hero__grid">
    <div class="pbi-hero__copy">
      <div class="pbi-kicker">Premium print solutions</div>
      <h1 class="pbi-title"><?php echo esc_html(pbi_contact('hero_title','Print that Creates Impact.')); ?></h1>
      <p class="pbi-sub"><?php echo esc_html(pbi_contact('hero_subtitle','Premium printing. Thoughtful details. Lasting impressions.')); ?></p>
      <div class="pbi-actions">
        <a class="pbi-btn pbi-btn--primary" href="<?php echo esc_url(home_url('/quote/')); ?>">Get a Quote ↗</a>
        <a class="pbi-btn pbi-btn--outline" href="#work">Explore Work ↗</a>
      </div>
    </div>
    <?php $hero = get_theme_mod('pbi_hero_image'); ?>
    <?php if ($hero): ?>
      <div style="border-radius:32px;overflow:hidden;box-shadow:var(--pbi-shadow)"><img src="<?php echo esc_url($hero); ?>" alt="Premium Print Bureau India printed products" fetchpriority="high" style="width:100%;height:520px;object-fit:cover"></div>
    <?php else: ?>
      <div class="pbi-art" aria-label="Premium print products illustration">
        <div class="pbi-art__slab"></div><div class="pbi-art__book"><strong>Ideas.<br>Printed<br>Beautifully.</strong></div><div class="pbi-art__box"></div><div class="pbi-art__card"></div><div class="pbi-art__sheet"></div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="pbi-section pbi-section--tight">
  <div class="pbi-wrap">
    <div class="pbi-heading-row"><h2>What do you need printed?</h2><a class="pbi-link" href="<?php echo esc_url(get_post_type_archive_link('pbi_product') ?: home_url('/products/')); ?>">View all ↗</a></div>
    <div class="pbi-category-grid">
      <?php $products=pbi_get_products(8); if($products->have_posts()): while($products->have_posts()):$products->the_post(); ?>
        <a class="pbi-category-card" href="<?php the_permalink(); ?>">
          <div class="pbi-category-card__visual <?php echo has_post_thumbnail()?'':'pbi-category-card__visual--fallback'; ?>"><?php if(has_post_thumbnail()) the_post_thumbnail('pbi-card',['loading'=>'lazy']); ?></div>
          <div class="pbi-category-card__body"><strong><?php the_title(); ?></strong><span class="pbi-arrow">↗</span></div>
        </a>
      <?php endwhile; wp_reset_postdata(); else: ?>
        <?php foreach(['Business Cards','Brochures','Packaging','Stationery','Books & Catalogs','Stickers & Labels','Banners & Signage','Institutional Printing'] as $label): ?>
          <a class="pbi-category-card" href="<?php echo esc_url(home_url('/quote/?product='.rawurlencode($label))); ?>"><div class="pbi-category-card__visual pbi-category-card__visual--fallback"></div><div class="pbi-category-card__body"><strong><?php echo esc_html($label); ?></strong><span class="pbi-arrow">↗</span></div></a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="pbi-section" id="work">
  <div class="pbi-wrap">
    <div class="pbi-heading-row"><h2>Featured Work</h2><a class="pbi-link" href="<?php echo esc_url(get_post_type_archive_link('pbi_portfolio') ?: home_url('/work/')); ?>">View all work ↗</a></div>
    <div class="pbi-work-grid">
      <?php $work=new WP_Query(['post_type'=>'pbi_portfolio','posts_per_page'=>6]); if($work->have_posts()): while($work->have_posts()):$work->the_post(); ?>
        <a class="pbi-work-card" href="<?php the_permalink(); ?>"><?php if(has_post_thumbnail()): the_post_thumbnail('pbi-card',['loading'=>'lazy']); else: ?><div class="pbi-work-card__fallback"><span><?php the_title(); ?></span></div><?php endif; ?></a>
      <?php endwhile; wp_reset_postdata(); else: ?>
        <?php foreach(['Packaging that feels premium','Brochures that sell the story','Business cards that get remembered'] as $label): ?><div class="pbi-work-card"><div class="pbi-work-card__fallback"><span><?php echo esc_html($label); ?></span></div></div><?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="pbi-section--tight"><div class="pbi-wrap"><div class="pbi-process"><div class="pbi-process__item"><div class="pbi-process__num">01</div><strong>Share Details</strong></div><div class="pbi-process__item"><div class="pbi-process__num">02</div><strong>Design & Proof</strong></div><div class="pbi-process__item"><div class="pbi-process__num">03</div><strong>Print & Deliver</strong></div></div></div></section>
<section class="pbi-section--tight"><div class="pbi-wrap"><div class="pbi-proof"><div class="pbi-proof__item"><strong>Quality</strong><span>Premium materials</span></div><div class="pbi-proof__item"><strong>Fast</strong><span>Clear turnaround</span></div><div class="pbi-proof__item"><strong>Flexible</strong><span>Custom solutions</span></div><div class="pbi-proof__item"><strong>Reliable</strong><span>Human support</span></div></div></div></section>
<?php get_template_part('template-parts/cta'); ?>
<?php get_footer(); ?>
