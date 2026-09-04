<?php get_header();
$hero=pbi_hero_image_url();
$quote=home_url('/quote/');
$products_url=get_post_type_archive_link('pbi_product') ?: home_url('/products/');
$blog_url=get_permalink((int)get_option('page_for_posts')) ?: home_url('/blog/');
$featured=[
 ['file'=>'brochures.webp','title'=>'Brochures','sub'=>'Ideas, presented beautifully.','url'=>home_url('/products/brochures/')],
 ['file'=>'business-cards.webp','title'=>'Business Cards','sub'=>'Premium first impressions.','url'=>home_url('/products/business-cards/')],
 ['file'=>'packaging.webp','title'=>'Packaging','sub'=>'Made to be remembered.','url'=>home_url('/products/packaging/')],
 ['file'=>'books-catalogs.webp','title'=>'Books & Catalogs','sub'=>'Beautifully bound stories.','url'=>home_url('/products/books-catalogs/')],
 ['file'=>'stationery.webp','title'=>'Stationery','sub'=>'Everyday brand consistency.','url'=>home_url('/products/stationery/')],
 ['file'=>'stickers-labels.webp','title'=>'Stickers & Labels','sub'=>'Small format. Strong impact.','url'=>home_url('/products/stickers-labels/')],
];
?>
<section class="pbi-v4-home-hero">
  <div class="pbi-wrap pbi-v4-home-hero__grid">
    <div class="pbi-v4-home-hero__copy">
      <div class="pbi-v4-eyebrow">Premium Print Solutions</div>
      <h1><?php echo esc_html(pbi_contact('hero_title','Print that Creates Impact.')); ?></h1>
      <p><?php echo esc_html(pbi_contact('hero_subtitle','Premium printing. Thoughtful details. Lasting impressions.')); ?></p>
      <div class="pbi-v4-home-actions"><a class="pbi-v4-btn pbi-v4-btn--primary" href="<?php echo esc_url($quote); ?>">Get a Quote <span>↗</span></a><a class="pbi-v4-btn pbi-v4-btn--ghost" href="#work">Explore Work <span>↗</span></a></div>
      <div class="pbi-v4-home-points"><span>✦ Premium quality</span><span>◇ Custom finishes</span><span>◷ Reliable turnaround</span></div>
    </div>
    <div class="pbi-v4-home-hero__visual"><?php if($hero): ?><img src="<?php echo esc_url($hero); ?>" alt="Premium Print Bureau India printed products" fetchpriority="high"><?php endif; ?></div>
  </div>
</section>

<section class="pbi-v4-strip">
  <div class="pbi-wrap pbi-v4-strip__inner"><span>Business Cards</span><i></i><span>Brochures</span><i></i><span>Packaging</span><i></i><span>Books</span><i></i><span>Labels</span><i></i><span>Large Format</span></div>
</section>

<section class="pbi-v4-section" id="solutions"><div class="pbi-wrap">
  <div class="pbi-v4-section-head"><div><div class="pbi-v4-eyebrow">Products</div><h2>Everything your brand needs in print.</h2></div><a href="<?php echo esc_url($products_url); ?>">View all products ↗</a></div>
  <div class="pbi-v4-product-grid"><?php foreach($featured as $item):$img=pbi_theme_image_exists($item['file'])?pbi_theme_image_url($item['file']):''; ?><a class="pbi-v4-product-card" href="<?php echo esc_url($item['url']); ?>"><div class="pbi-v4-product-card__image"><?php if($img): ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($item['title']); ?> printing" loading="lazy"><?php endif; ?><span class="pbi-v4-product-card__arrow">↗</span></div><div class="pbi-v4-product-card__body"><strong><?php echo esc_html($item['title']); ?></strong><span><?php echo esc_html($item['sub']); ?></span></div></a><?php endforeach; ?></div>
</div></section>

<section class="pbi-v4-featured" id="work"><div class="pbi-wrap">
  <div class="pbi-v4-section-head"><div><div class="pbi-v4-eyebrow">Featured Work</div><h2>Print that feels considered.</h2></div><a href="<?php echo esc_url($products_url); ?>">Explore capabilities ↗</a></div>
  <div class="pbi-v4-work-grid">
    <?php $work=[['packaging.webp','Packaging that feels premium','Packaging'],['brochures.webp','Brochures that tell the story','Brochures'],['business-cards.webp','Business cards worth keeping','Business Cards']]; foreach($work as [$file,$title,$label]):$img=pbi_theme_image_exists($file)?pbi_theme_image_url($file):''; ?>
    <a class="pbi-v4-work-card" href="<?php echo esc_url(home_url('/quote/?product='.rawurlencode($label))); ?>"><?php if($img): ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy"><?php endif; ?><span class="pbi-v4-work-card__shade"></span><div><small><?php echo esc_html($label); ?></small><strong><?php echo esc_html($title); ?></strong><span>Explore project ↗</span></div></a>
    <?php endforeach; ?>
  </div>
</div></section>

<section class="pbi-v4-process"><div class="pbi-wrap">
  <div class="pbi-v4-process__intro"><div class="pbi-v4-eyebrow">How it works</div><h2>Simple from brief to delivery.</h2><p>Clear communication, careful proofing and dependable production — without unnecessary friction.</p></div>
  <div class="pbi-v4-process__steps"><div><b>01</b><strong>Share your brief</strong><span>Tell us the product, quantity and deadline.</span></div><div><b>02</b><strong>Review & proof</strong><span>We confirm material, finish and artwork.</span></div><div><b>03</b><strong>Print & deliver</strong><span>We produce carefully and keep you updated.</span></div></div>
</div></section>

<section class="pbi-v4-insights"><div class="pbi-wrap pbi-v4-insights__card"><div><div class="pbi-v4-eyebrow">Print Insights</div><h2>Make better print decisions.</h2><p>Guides on paper, finishing, packaging, artwork and the details that make print work harder.</p><a class="pbi-v4-btn pbi-v4-btn--ghost" href="<?php echo esc_url($blog_url); ?>">Explore Insights <span>↗</span></a></div><div class="pbi-v4-insights__visual"><?php $insight=pbi_theme_image_exists('books-catalogs.webp')?pbi_theme_image_url('books-catalogs.webp'):$hero; if($insight): ?><img src="<?php echo esc_url($insight); ?>" alt="Print Bureau India print insights" loading="lazy"><?php endif; ?></div></div></section>

<?php get_template_part('template-parts/cta'); get_footer(); ?>