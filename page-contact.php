<?php
get_header();
the_post();
$phone=pbi_contact('phone','+91 98765 43210');
$phone_href=preg_replace('/\s+/','',$phone);
$wa=preg_replace('/\D+/','',pbi_contact('whatsapp','919876543210'));
$email=pbi_contact('email','hello@printbureauindia.com');
$address=pbi_contact('address','Chikmagalur, Karnataka, India');
$hours=pbi_contact('hours','Mon–Sat, 9:30 AM – 7:00 PM');
$hero=pbi_theme_image_exists('hero.webp')?pbi_theme_image_url('hero.webp'):'';
?>
<section class="pbi-r3-contact-hero">
  <div class="pbi-wrap pbi-r3-contact-hero__grid">
    <div class="pbi-r3-contact-hero__copy"><div class="pbi-r3-kicker">✦ Contact Us</div><h1 class="pbi-r3-heading">Let’s Create<br>Something<br>Remarkable<span class="pbi-dot">.</span></h1><p class="pbi-r3-copy">We’re here to bring your ideas to life.</p></div>
    <div class="pbi-r3-contact-hero__image"><?php if($hero): ?><img src="<?php echo esc_url($hero); ?>" alt="Print Bureau India premium print samples" fetchpriority="high"><?php endif; ?></div>
  </div>
  <div class="pbi-wrap pbi-r3-contact-actions">
    <a class="pbi-r3-contact-action" href="tel:<?php echo esc_attr($phone_href); ?>"><i>⌕</i><strong>Call Us</strong><span><?php echo esc_html($phone); ?></span></a>
    <a class="pbi-r3-contact-action" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr($wa); ?>"><i>◉</i><strong>WhatsApp</strong><span>Quick replies during business hours</span></a>
    <a class="pbi-r3-contact-action" href="mailto:<?php echo esc_attr($email); ?>"><i>✉</i><strong>Email Us</strong><span><?php echo esc_html($email); ?></span></a>
    <a class="pbi-r3-contact-action" href="<?php echo esc_url(home_url('/quote/')); ?>"><i>▣</i><strong>Request a Quote</strong><span>Share details of your project</span></a>
  </div>
</section>
<section class="pbi-r3-product">
  <div class="pbi-wrap pbi-r3-contact-main">
    <div class="pbi-form-card">
      <h2>Send Us an Enquiry</h2><p style="margin-top:-5px;color:#657384">Share your project details and we’ll get back to you.</p>
      <?php if(($_GET['lead']??'')==='success'): ?><div class="pbi-notice">Thanks — your enquiry has been received.</div><?php endif; ?>
      <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-lead-form>
        <input type="hidden" name="action" value="pbi_submit_lead"><input type="hidden" name="lead_type" value="Contact enquiry"><?php wp_nonce_field('pbi_submit_lead','pbi_nonce'); get_template_part('template-parts/lead-hidden-fields'); ?>
        <div class="pbi-form-grid"><div class="pbi-field"><label>Full Name *</label><input name="name" required></div><div class="pbi-field"><label>Email Address</label><input type="email" name="email"></div><div class="pbi-field"><label>Phone Number *</label><input name="phone" required></div><div class="pbi-field"><label>Company (Optional)</label><input name="company"></div><div class="pbi-field pbi-field--full"><label>Select a Service</label><input name="product" placeholder="Brochures, packaging, stationery..."></div><div class="pbi-field pbi-field--full"><label>Tell us about your project</label><textarea name="message" rows="5"></textarea></div><div class="pbi-field pbi-field--full"><label>Attach files (optional)</label><input type="file" name="artwork" accept=".pdf,.jpg,.jpeg,.png,.webp"></div></div>
        <div style="display:flex;justify-content:flex-end;margin-top:14px"><button class="pbi-btn pbi-btn--primary" type="submit">Send Enquiry ↗</button></div>
      </form>
    </div>
    <div class="pbi-r3-card pbi-r3-location">
      <h2 style="margin:0 0 12px;font-size:1.1rem">Our Location</h2>
      <div class="pbi-r3-map"><div class="pbi-r3-map-card"><strong>Print Bureau India</strong><span><?php echo esc_html($address); ?></span><a href="https://www.google.com/maps/search/?api=1&query=<?php echo rawurlencode($address); ?>" target="_blank" rel="noopener">Get Directions ↗</a></div></div>
      <div class="pbi-r3-info-pair"><div class="pbi-r3-card pbi-r3-info"><h3>◷ &nbsp; Business Hours</h3><p><?php echo esc_html($hours); ?></p><p>WhatsApp is available for quick enquiries.</p></div><div class="pbi-r3-card pbi-r3-info"><h3>◇ &nbsp; Service Area</h3><p>Premium print support for local businesses, institutions and brands, with delivery discussed per project.</p></div></div>
    </div>
  </div>
</section>
<?php get_template_part('template-parts/cta'); get_footer(); ?>
