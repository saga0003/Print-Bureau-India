<?php
get_header();
the_post();
$product_id=get_the_ID();
$slug=(string)get_post_field('post_name',$product_id);
$sizes=get_post_meta($product_id,'_pbi_sizes',true) ?: 'A4, A5, Custom';
$paper=get_post_meta($product_id,'_pbi_paper',true) ?: 'Premium Matte, Premium Gloss, Custom';
$finish=get_post_meta($product_id,'_pbi_finish',true) ?: 'Matte Lamination, Gloss Lamination, Spot UV';
$turnaround=get_post_meta($product_id,'_pbi_turnaround',true) ?: '3–5 business days';
$main_image=pbi_product_image_url($product_id,'pbi-hero');
$quote_url=home_url('/quote/?product='.rawurlencode(get_the_title()));
$wa=preg_replace('/\D+/','',pbi_contact('whatsapp','919876543210'));
$asset=static function(string $file):string{return pbi_theme_image_exists($file)?pbi_theme_image_url($file):'';};
$sample_images=array_values(array_filter([$main_image,$asset('hero.webp'),$asset('books-catalogs.webp'),$asset('stationery.webp')]));
$is_brochure=$slug==='brochures';
$style_labels=$is_brochure ? [
 ['Bi-Fold Brochure','Clean & classic','brochures.webp'],['Tri-Fold Brochure','Smart & compact','stationery.webp'],['Z-Fold Brochure','Neat & structured','books-catalogs.webp'],['Gate-Fold Brochure','Premium & impactful','packaging.webp'],['Multi-Page Brochure','Detailed & engaging','hero.webp']
] : [
 ['Premium Finish','Refined & tactile',pbi_product_asset_filename($product_id)],['Classic Option','Clean & versatile','stationery.webp'],['Luxury Detail','Made to impress','packaging.webp'],['Brand-led Design','Distinctive & polished','books-catalogs.webp'],['Custom Format','Built around your brief','hero.webp']
];
$size_options=array_filter(array_map('trim',explode(',',$sizes)));
$paper_options=array_filter(array_map('trim',explode(',',$paper)));
$finish_options=array_filter(array_map('trim',explode(',',$finish)));
?>
<section class="pbi-r3-product">
<div class="pbi-wrap pbi-r3-shell">
  <div class="pbi-r3-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>›</span><a href="<?php echo esc_url(get_post_type_archive_link('pbi_product')); ?>">Products</a><span>›</span><span><?php the_title(); ?></span></div>
  <div class="pbi-r3-product__hero">
    <div class="pbi-r3-gallery">
      <div class="pbi-r3-gallery__main">
        <?php if($main_image): ?><img src="<?php echo esc_url($main_image); ?>" alt="<?php the_title_attribute(); ?> by Print Bureau India" fetchpriority="high"><?php endif; ?>
        <span class="pbi-r3-gallery__nav pbi-r3-gallery__nav--prev" aria-hidden="true">‹</span><span class="pbi-r3-gallery__nav pbi-r3-gallery__nav--next" aria-hidden="true">›</span>
      </div>
      <?php if($sample_images): ?><div class="pbi-r3-thumbs"><?php foreach($sample_images as $image): ?><div class="pbi-r3-thumb"><img src="<?php echo esc_url($image); ?>" alt="<?php the_title_attribute(); ?> print sample" loading="lazy"></div><?php endforeach; ?></div><?php endif; ?>
    </div>
    <div class="pbi-r3-product__intro">
      <div class="pbi-r3-kicker">✦ <?php the_title(); ?></div>
      <h1 class="pbi-r3-heading"><?php the_title(); ?><span class="pbi-dot">.</span></h1>
      <p class="pbi-r3-copy"><?php echo esc_html(get_the_excerpt() ?: 'Premium printing that presents your brand beautifully and leaves a lasting impression.'); ?></p>
      <div class="pbi-r3-feature-row"><div class="pbi-r3-feature"><div class="pbi-r3-feature__icon">✦</div>Premium<br>Quality</div><div class="pbi-r3-feature"><div class="pbi-r3-feature__icon">◉</div>Vibrant<br>Printing</div><div class="pbi-r3-feature"><div class="pbi-r3-feature__icon">◇</div>Custom<br>Finishes</div><div class="pbi-r3-feature"><div class="pbi-r3-feature__icon">◷</div>Fast<br>Turnaround</div></div>
    </div>
    <div class="pbi-r3-mobile-actions"><a class="pbi-btn pbi-btn--primary" href="<?php echo esc_url($quote_url); ?>">Get a Quote ↗</a><a class="pbi-btn pbi-btn--outline" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr($wa); ?>?text=<?php echo rawurlencode('Hi, I need a quote for '.get_the_title()); ?>">WhatsApp</a></div>
    <aside class="pbi-r3-card pbi-r3-quick"><h2>Get a Quick Quote</h2><form method="get" action="<?php echo esc_url(home_url('/quote/')); ?>"><input type="hidden" name="product" value="<?php the_title_attribute(); ?>"><div class="pbi-field"><label>Size</label><select name="size"><?php foreach($size_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div><div class="pbi-field"><label><?php echo $is_brochure?'Pages':'Format'; ?></label><select name="format"><option><?php echo $is_brochure?'8 Pages':'Standard'; ?></option><option><?php echo $is_brochure?'16 Pages':'Custom'; ?></option></select></div><div class="pbi-field"><label>Paper</label><select name="paper"><?php foreach($paper_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div><div class="pbi-field"><label>Finish</label><select name="finish"><?php foreach($finish_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div><div class="pbi-field"><label>Quantity</label><input type="number" name="quantity" min="1" value="250"></div><div class="pbi-field"><label>Delivery</label><select name="delivery"><option><?php echo esc_html($turnaround); ?></option><option>Discuss with us</option></select></div><button class="pbi-btn pbi-btn--primary" type="submit">Get Quote ↗</button></form><div class="pbi-r3-secure"><b>◇</b> Secure & hassle-free enquiry</div></aside>
  </div>
  <div class="pbi-r3-section-title"><h2><?php echo $is_brochure?'Popular Brochure Styles':'Popular Options'; ?></h2><a href="<?php echo esc_url($quote_url); ?>">View all styles ↗</a></div>
  <div class="pbi-r3-style-grid"><?php foreach($style_labels as [$label,$sub,$imgfile]):$img=$asset($imgfile); ?><a class="pbi-r3-style-card" href="<?php echo esc_url($quote_url); ?>"><div class="pbi-r3-style-card__image"><?php if($img): ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($label); ?>" loading="lazy"><?php endif; ?></div><div class="pbi-r3-style-card__body"><strong><?php echo esc_html($label); ?></strong><span><?php echo esc_html($sub); ?></span><i>→</i></div></a><?php endforeach; ?></div>
  <?php if(trim(wp_strip_all_tags(get_the_content()))): ?><div class="pbi-product-copy" style="margin-top:28px"><h2><?php the_title(); ?> in Chikmagalur</h2><?php the_content(); ?></div><?php endif; ?>
  <div class="pbi-r3-section-title"><h2>Frequently Asked Questions</h2></div>
  <div class="pbi-r3-faq-grid"><details><summary>What is the minimum order quantity?</summary><p>Minimum quantity depends on the product, material and finishing. Share your requirement and we’ll recommend the most practical run size.</p></details><details><summary>Can you help with design?</summary><p>Yes. We can review your artwork and help prepare a print-ready design when needed.</p></details><details><summary>What file format should I provide?</summary><p>A print-ready PDF is preferred. We can also review JPG, PNG and other common artwork formats.</p></details><details><summary>What is the turnaround time?</summary><p><?php echo esc_html($turnaround); ?> for many standard jobs. Complex finishes and larger runs can take longer.</p></details></div>
</div>
</section>
<?php get_template_part('template-parts/cta'); get_footer(); ?>
