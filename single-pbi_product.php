<?php get_header(); the_post();
$sizes=get_post_meta(get_the_ID(),'_pbi_sizes',true) ?: 'A4, A5, Custom';
$paper=get_post_meta(get_the_ID(),'_pbi_paper',true) ?: 'Premium Matte, Premium Gloss, Custom';
$finish=get_post_meta(get_the_ID(),'_pbi_finish',true) ?: 'Matte Lamination, Gloss Lamination, Spot UV';
$turnaround=get_post_meta(get_the_ID(),'_pbi_turnaround',true) ?: '3–5 business days';
?>
<section class="pbi-page-hero"><div class="pbi-wrap"><div class="pbi-breadcrumbs"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>›</span><a href="<?php echo esc_url(get_post_type_archive_link('pbi_product')); ?>">Products</a><span>›</span><span><?php the_title(); ?></span></div></div></section>
<section class="pbi-section--tight"><div class="pbi-wrap pbi-product-layout">
  <div class="pbi-product-media"><?php if(has_post_thumbnail()): the_post_thumbnail('pbi-hero',['fetchpriority'=>'high']); else: ?><div class="pbi-art"><div class="pbi-art__slab"></div><div class="pbi-art__book"><strong><?php echo esc_html(get_the_title()); ?><br>Printed<br>Beautifully.</strong></div><div class="pbi-art__box"></div><div class="pbi-art__card"></div><div class="pbi-art__sheet"></div></div><?php endif;?></div>
  <aside class="pbi-product-side">
    <div class="pbi-kicker">Premium print</div><h1><?php the_title(); ?><span class="pbi-dot">.</span></h1><p class="pbi-sub"><?php echo esc_html(get_the_excerpt() ?: 'Thoughtful print, premium materials and a finish that makes the right impression.'); ?></p>
    <div class="pbi-actions"><a class="pbi-btn pbi-btn--primary" href="<?php echo esc_url(home_url('/quote/?product='.rawurlencode(get_the_title()))); ?>">Get a Quote ↗</a><a class="pbi-btn pbi-btn--outline" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr(preg_replace('/\D+/','',pbi_contact('whatsapp','919876543210'))); ?>?text=<?php echo rawurlencode('Hi, I need a quote for '.get_the_title()); ?>">WhatsApp</a></div>
    <div class="pbi-icons-row"><div class="pbi-mini-feature">✦<b>Premium Quality</b></div><div class="pbi-mini-feature">◈<b>Custom Finish</b></div><div class="pbi-mini-feature">⌁<b>Design Support</b></div><div class="pbi-mini-feature">◷<b><?php echo esc_html($turnaround); ?></b></div></div>
    <div class="pbi-spec-grid"><div class="pbi-field"><label>Size</label><select><option><?php echo esc_html(trim(explode(',',$sizes)[0])); ?></option></select></div><div class="pbi-field"><label>Paper</label><select><option><?php echo esc_html(trim(explode(',',$paper)[0])); ?></option></select></div><div class="pbi-field"><label>Finish</label><select><option><?php echo esc_html(trim(explode(',',$finish)[0])); ?></option></select></div><div class="pbi-field"><label>Quantity</label><input type="number" min="1" value="250"></div></div>
  </aside>
</div></section>
<section class="pbi-section"><div class="pbi-wrap"><div class="pbi-heading-row"><h2>Options</h2></div><div class="pbi-card-row">
<?php foreach(array_slice(array_filter(array_map('trim',explode(',',$finish))),0,4) as $option): ?><div class="pbi-option-card"><div class="pbi-option-card__img"></div><div class="pbi-option-card__body"><strong><?php echo esc_html($option); ?></strong><small>Premium finish</small></div></div><?php endforeach; ?>
</div></div></section>
<section class="pbi-section--tight"><div class="pbi-wrap"><div class="pbi-heading-row"><h2>FAQs</h2></div><div class="pbi-faq"><details><summary>What file formats do you accept?</summary><p>PDF is preferred. We can also review AI, PSD, PNG and JPG files.</p></details><details><summary>Can you help with design?</summary><p>Yes. Share your requirement and we can help prepare a print-ready design.</p></details><details><summary>What is the turnaround time?</summary><p><?php echo esc_html($turnaround); ?> for many standard jobs; complex finishes may take longer.</p></details></div></div></section>
<?php get_template_part('template-parts/cta'); get_footer(); ?>
