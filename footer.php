<?php if (!defined('ABSPATH')) { exit; } ?>
</main>
<footer class="pbi-footer">
  <div class="pbi-wrap">
    <div class="pbi-footer__grid">
      <div>
        <a class="pbi-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Print Bureau India home">
          <span class="pbi-brand__logo" aria-hidden="true"><img data-pbi-logo data-dark="<?php echo esc_attr(pbi_logo_url('dark')); ?>" data-light="<?php echo esc_attr(pbi_logo_url('light')); ?>" src="<?php echo esc_url(pbi_logo_url('dark')); ?>" alt=""></span>
          <span class="screen-reader-text">Print Bureau India</span>
        </a>
        <p class="pbi-sub" style="font-size:.95rem;max-width:360px">Premium printing. Thoughtful details. Reliable delivery.</p>
      </div>
      <div><h4>Print</h4><a href="<?php echo esc_url(get_post_type_archive_link('pbi_product') ?: home_url('/products/')); ?>">Products</a><a href="<?php echo esc_url(home_url('/quote/')); ?>">Get a Quote</a><a href="<?php echo esc_url(home_url('/#work')); ?>">Featured Work</a></div>
      <div><h4>Learn</h4><a href="<?php echo esc_url(get_permalink((int)get_option('page_for_posts')) ?: home_url('/blog/')); ?>">Insights</a><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy</a></div>
      <div><h4>Reach us</h4><a href="tel:<?php echo esc_attr(preg_replace('/\s+/','',pbi_contact('phone','+91 98765 43210'))); ?>"><?php echo esc_html(pbi_contact('phone','+91 98765 43210')); ?></a><a href="mailto:<?php echo esc_attr(pbi_contact('email','hello@printbureauindia.com')); ?>"><?php echo esc_html(pbi_contact('email','hello@printbureauindia.com')); ?></a><a href="https://wa.me/<?php echo esc_attr(preg_replace('/\D+/','',pbi_contact('whatsapp','919876543210'))); ?>" target="_blank" rel="noopener">WhatsApp</a></div>
    </div>
    <div class="pbi-footer__bottom"><span>© <?php echo esc_html(date_i18n('Y')); ?> Print Bureau India.</span><span>Built for speed, search and enquiries.</span></div>
  </div>
</footer>
<div class="pbi-mobile-bar" aria-label="Quick actions">
  <a href="tel:<?php echo esc_attr(preg_replace('/\s+/','',pbi_contact('phone','+91 98765 43210'))); ?>">Call</a>
  <a href="https://wa.me/<?php echo esc_attr(preg_replace('/\D+/','',pbi_contact('whatsapp','919876543210'))); ?>" target="_blank" rel="noopener">WhatsApp</a>
  <a href="<?php echo esc_url(home_url('/quote/')); ?>">Get Quote</a>
</div>
<?php wp_footer(); ?>
</body>
</html>
