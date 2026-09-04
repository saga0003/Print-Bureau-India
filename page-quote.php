<?php
get_header();
the_post();
$pre=isset($_GET['product'])?sanitize_text_field(wp_unslash($_GET['product'])):'';
$qsize=isset($_GET['size'])?sanitize_text_field(wp_unslash($_GET['size'])):'';
$qpaper=isset($_GET['paper'])?sanitize_text_field(wp_unslash($_GET['paper'])):'';
$qfinish=isset($_GET['finish'])?sanitize_text_field(wp_unslash($_GET['finish'])):'';
$qqty=isset($_GET['quantity'])?max(1,(int)$_GET['quantity']):250;
$wa=preg_replace('/\D+/','',pbi_contact('whatsapp','919876543210'));
?>
<section class="pbi-r3-product">
<div class="pbi-wrap pbi-r3-quote">
  <div class="pbi-r3-quote__top"><div><div class="pbi-r3-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>›</span><span>Get Quote</span></div><h1 class="pbi-r3-heading">Get Your Quote<span class="pbi-dot">.</span></h1><p class="pbi-r3-copy">Premium printing. Thoughtful details. Tailored for your brand.</p></div></div>
  <div class="pbi-r3-steps"><div class="pbi-r3-step is-active"><div class="pbi-r3-step__n">1</div><div><strong>Product & Specs</strong><span>Choose product & configure</span></div></div><div class="pbi-r3-step"><div class="pbi-r3-step__n">2</div><div><strong>Artwork</strong><span>Upload & review artwork</span></div></div><div class="pbi-r3-step"><div class="pbi-r3-step__n">3</div><div><strong>Delivery</strong><span>Enter delivery details</span></div></div><div class="pbi-r3-step"><div class="pbi-r3-step__n">4</div><div><strong>Review & Submit</strong><span>Review and send enquiry</span></div></div></div>

  <div class="pbi-r3-quote-layout">
    <form class="pbi-r3-card pbi-r3-quote-panel" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-lead-form enctype="multipart/form-data">
      <input type="hidden" name="action" value="pbi_submit_lead"><input type="hidden" name="lead_type" value="Quote request"><input type="hidden" name="product" value="<?php echo esc_attr($pre); ?>"><?php wp_nonce_field('pbi_submit_lead','pbi_nonce'); get_template_part('template-parts/lead-hidden-fields'); ?>
      <h2>1. Choose Your Product</h2>
      <div class="pbi-r3-product-picker">
        <?php $products=pbi_get_products(6); if($products->have_posts()):while($products->have_posts()):$products->the_post();$sel=$pre===get_the_title();$img=pbi_product_image_url(get_the_ID(),'pbi-card'); ?>
          <button type="button" class="pbi-r3-product-choice <?php echo $sel?'is-selected':''; ?>" data-product-choice data-product="<?php the_title_attribute(); ?>"><div class="pbi-r3-product-choice__img"><?php if($img): ?><img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"><?php endif; ?></div><strong><?php the_title(); ?></strong><small>Premium & professional</small></button>
        <?php endwhile;wp_reset_postdata();endif; ?>
      </div>

      <h2 style="margin-top:28px">2. Configure Specifications</h2>
      <div class="pbi-r3-spec-grid">
        <div class="pbi-field"><label>Size</label><input name="size" value="<?php echo esc_attr($qsize); ?>" placeholder="A4 / A5 / Custom" data-sync-summary></div>
        <div class="pbi-field"><label>Paper / Material</label><input name="paper" value="<?php echo esc_attr($qpaper); ?>" placeholder="Premium matte / gloss" data-sync-summary></div>
        <div class="pbi-field"><label>Printing</label><select name="printing"><option>Full colour</option><option>Single colour</option><option>Discuss with us</option></select></div>
        <div class="pbi-field"><label>Finishing</label><input name="finish" value="<?php echo esc_attr($qfinish); ?>" placeholder="Matte / gloss / custom" data-sync-summary></div>
        <div class="pbi-field"><label>Quantity</label><input type="number" min="1" name="quantity" value="<?php echo esc_attr((string)$qqty); ?>" data-sync-summary></div>
        <div class="pbi-field"><label>Turnaround</label><select name="turnaround"><option>Standard turnaround</option><option>Priority / urgent</option><option>Discuss with us</option></select></div>
      </div>

      <h2 style="margin-top:28px">3. Upload Your Artwork</h2>
      <div class="pbi-r3-upload"><label class="pbi-r3-upload__drop" for="pbi-artwork"><div><strong>☁ &nbsp; Drag & drop your file here, or browse</strong><span>PDF, JPG, PNG or WebP · max 15 MB</span><input id="pbi-artwork" type="file" name="artwork" accept=".pdf,.jpg,.jpeg,.png,.webp" style="display:block;margin:12px auto 0;max-width:100%"></div></label><div class="pbi-r3-upload__guide"><strong>Artwork Guidelines</strong><div>✓ Print-ready PDF preferred</div><div>✓ Include bleed where required</div><div>✓ High-resolution artwork gives the best result</div></div></div>

      <h2 style="margin-top:28px">4. Contact & Project Details</h2>
      <div class="pbi-r3-spec-grid"><div class="pbi-field"><label>Name *</label><input name="name" required></div><div class="pbi-field"><label>Phone *</label><input name="phone" required></div><div class="pbi-field"><label>Email</label><input type="email" name="email"></div><div class="pbi-field"><label>Company</label><input name="company"></div><div class="pbi-field" style="grid-column:1/-1"><label>Anything else?</label><textarea name="message" rows="4"></textarea></div></div>
      <button class="pbi-btn pbi-btn--primary" style="width:100%;margin-top:16px" type="submit">Review & Submit Enquiry ↗</button>
    </form>

    <aside class="pbi-r3-card pbi-r3-summary">
      <h2>Quote Summary</h2>
      <div class="pbi-r3-summary__product"><div class="pbi-r3-summary__thumb"><?php if(pbi_theme_image_exists('brochures.webp')): ?><img src="<?php echo esc_url(pbi_theme_image_url('brochures.webp')); ?>" alt="Selected Print Bureau product"><?php endif; ?></div><div><strong data-summary-product><?php echo esc_html($pre ?: 'Select a product'); ?></strong><div class="pbi-r3-meta">Custom print enquiry</div></div></div>
      <dl><div><dt>Size</dt><dd data-summary="size"><?php echo esc_html($qsize ?: '—'); ?></dd></div><div><dt>Paper</dt><dd data-summary="paper"><?php echo esc_html($qpaper ?: '—'); ?></dd></div><div><dt>Finishing</dt><dd data-summary="finish"><?php echo esc_html($qfinish ?: '—'); ?></dd></div><div><dt>Quantity</dt><dd data-summary="quantity"><?php echo esc_html((string)$qqty); ?></dd></div></dl>
      <div class="pbi-r3-final-quote"><span class="pbi-r3-meta">Pricing</span><strong>Confirmed after review</strong><p class="pbi-r3-meta">We won’t invent an estimate. Final pricing depends on material, quantity, finishing, artwork and delivery.</p></div>
      <a class="pbi-btn pbi-btn--primary" style="width:100%;margin-top:16px" href="#main-content" onclick="this.closest('aside').previousElementSibling.querySelector('[name=name]').focus();return false;">Complete Request ↗</a>
      <div class="pbi-r3-trust-row"><div class="pbi-r3-trust"><i>◇</i>Premium Quality</div><div class="pbi-r3-trust"><i>▣</i>Clear Communication</div><div class="pbi-r3-trust"><i>◉</i>Expert Support</div></div>
      <a class="pbi-btn pbi-btn--outline" style="width:100%;margin-top:15px" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr($wa); ?>">Need help? WhatsApp us</a>
    </aside>
  </div>
</div>
</section>
<?php get_footer(); ?>
