<?php
get_header();
the_post();

$product_id = get_the_ID();
$slug = (string) get_post_field('post_name', $product_id);
$sizes = get_post_meta($product_id, '_pbi_sizes', true) ?: 'A4, A5, Custom';
$paper = get_post_meta($product_id, '_pbi_paper', true) ?: 'Premium Matte, Premium Gloss, Custom';
$finish = get_post_meta($product_id, '_pbi_finish', true) ?: 'Matte Lamination, Gloss Lamination, Spot UV';
$turnaround = get_post_meta($product_id, '_pbi_turnaround', true) ?: '3–5 business days';
$quote_url = home_url('/quote/?product=' . rawurlencode(get_the_title()));
$wa = preg_replace('/\D+/', '', pbi_contact('whatsapp', '919876543210'));

$gallery = function_exists('pbi_product_gallery_images') ? pbi_product_gallery_images($product_id) : [];
if (!$gallery) {
    $fallback = pbi_product_image_url($product_id, 'full');
    if ($fallback) $gallery[] = ['url'=>$fallback,'thumb'=>$fallback,'alt'=>get_the_title() . ' printing | Print Bureau India'];
}
$gallery_count = count($gallery);
$first_image = $gallery_count ? $gallery[0] : null;

$option_sets = [
    'business-cards' => [
        ['Premium Matte Cards','Smooth & professional'],
        ['Textured Business Cards','Tactile & distinctive'],
        ['Foil / Spot UV Cards','Premium finishing'],
        ['Custom Format Cards','Built around your brand'],
    ],
    'brochures' => [
        ['Bi-Fold Brochures','Clean & classic'],
        ['Tri-Fold Brochures','Smart & compact'],
        ['Corporate Brochures','Premium presentation'],
        ['Multi-Page Brochures','Detailed & engaging'],
    ],
    'flyers-pamphlets' => [
        ['Promotional Flyers','Offers & launches'],
        ['Event Pamphlets','Events & campaigns'],
        ['Admission Leaflets','Schools & colleges'],
        ['Double-Side Flyers','More information, same sheet'],
    ],
    'packaging' => [
        ['Product Boxes','Retail-ready packaging'],
        ['Printed Sleeves','Simple branded upgrade'],
        ['Gift Packaging','Premium presentation'],
        ['Custom Cartons','Built around the product'],
    ],
    'stationery' => [
        ['Letterheads','Professional communication'],
        ['Envelopes','Coordinated branding'],
        ['Folders & Covers','Present documents better'],
        ['Office Stationery Sets','Consistent brand system'],
    ],
    'custom-notebooks-diaries' => [
        ['Custom Notebooks','Schools, teams & events'],
        ['Corporate Diaries','Gifting & daily use'],
        ['Wiro Notebooks','Practical & flexible'],
        ['Hardbound Journals','Premium branded finish'],
    ],
    'books-catalogs' => [
        ['Product Catalogs','Showcase complete ranges'],
        ['Books & Manuals','Long-form content'],
        ['Reports & Prospectuses','Institutional communication'],
        ['Premium Booklets','Compact multi-page print'],
    ],
    'stickers-labels' => [
        ['Product Labels','Packaging & retail'],
        ['Die-Cut Stickers','Custom shapes'],
        ['Bottle & Jar Labels','Food, beverage & cosmetics'],
        ['Transparent Labels','Clean premium applications'],
    ],
    'banners-signage' => [
        ['Flex & Vinyl Banners','Outdoor visibility'],
        ['Posters','Campaigns & announcements'],
        ['Roll-Up Standees','Events & retail'],
        ['Event Backdrops','Large-format branding'],
    ],
    'institutional-printing' => [
        ['Academic Print','Worksheets, booklets & forms'],
        ['ID Materials','Student & staff requirements'],
        ['Reports & Folders','Institutional presentation'],
        ['Recurring Print','Standardised repeat orders'],
    ],
    'certificates' => [
        ['Academic Certificates','Schools & colleges'],
        ['Achievement Awards','Recognition programmes'],
        ['Variable-Name Certificates','Bulk personalised output'],
        ['Premium Certificates','Foil & special finishes'],
    ],
    'invitations' => [
        ['Wedding Invitations','Elegant invitation suites'],
        ['Event Invitations','Functions & celebrations'],
        ['Corporate Invites','Launches & formal events'],
        ['Premium Invite Sets','Cards, envelopes & inserts'],
    ],
    'calendars' => [
        ['Desk Calendars','Year-round desk visibility'],
        ['Wall Calendars','Large branded format'],
        ['Corporate Calendars','Gifting & promotions'],
        ['Annual Planners','Useful branded stationery'],
    ],
];
$style_labels = $option_sets[$slug] ?? [
    ['Premium Finish','Refined & tactile'],
    ['Classic Option','Clean & versatile'],
    ['Luxury Detail','Made to impress'],
    ['Custom Format','Built around your brief'],
];

$secondary_fields = [
    'business-cards' => ['Sides', ['Single Side','Double Side']],
    'brochures' => ['Pages', ['8 Pages','16 Pages','24 Pages','Custom']],
    'flyers-pamphlets' => ['Print', ['Single Side','Double Side','Folded']],
    'packaging' => ['Structure', ['Folding Carton','Sleeve','Gift Box','Custom']],
    'stationery' => ['Requirement', ['Letterhead','Envelope','Folder','Stationery Set']],
    'custom-notebooks-diaries' => ['Pages', ['80 Pages','120 Pages','160 Pages','Custom']],
    'books-catalogs' => ['Pages', ['24 Pages','48 Pages','96 Pages','Custom']],
    'stickers-labels' => ['Shape', ['Round','Square','Rectangular','Custom Die-cut']],
    'banners-signage' => ['Application', ['Indoor','Outdoor','Standee','Backdrop']],
    'institutional-printing' => ['Requirement', ['Academic','Administrative','Branded','Custom']],
    'certificates' => ['Personalisation', ['Standard','Variable Names','Premium']],
    'invitations' => ['Suite', ['Card Only','Card + Envelope','Full Suite','Custom']],
    'calendars' => ['Format', ['Desk','Wall','Table','Custom']],
];
[$secondary_label,$secondary_options] = $secondary_fields[$slug] ?? ['Format',['Standard','Custom']];

$size_options = array_filter(array_map('trim', explode(',', $sizes)));
$paper_options = array_filter(array_map('trim', explode(',', $paper)));
$finish_options = array_filter(array_map('trim', explode(',', $finish)));
$statewide_intro = function_exists('pbi_product_karnataka_copy') ? pbi_product_karnataka_copy($product_id) : '';
?>
<section class="pbi-r3-product">
<div class="pbi-wrap pbi-r3-shell">
  <div class="pbi-r3-breadcrumbs">
    <a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span>›</span>
    <a href="<?php echo esc_url(get_post_type_archive_link('pbi_product')); ?>">Products</a><span>›</span>
    <span><?php the_title(); ?></span>
  </div>

  <div class="pbi-r3-product__hero">
    <div class="pbi-r3-gallery" data-product-gallery>
      <div class="pbi-r3-gallery__main" data-gallery-stage>
        <?php if ($first_image): ?>
          <button class="pbi-gallery-zoom-button" type="button" data-gallery-open aria-label="Zoom <?php the_title_attribute(); ?> image">
            <img src="<?php echo esc_url($first_image['url']); ?>" alt="<?php echo esc_attr($first_image['alt']); ?>" fetchpriority="high" data-gallery-main data-gallery-index="0">
            <span class="pbi-gallery-zoom-hint" aria-hidden="true">⌕ Zoom</span>
          </button>
        <?php endif; ?>
        <?php if ($gallery_count > 1): ?>
          <button class="pbi-r3-gallery__nav pbi-r3-gallery__nav--prev" type="button" data-gallery-prev aria-label="Previous image">‹</button>
          <button class="pbi-r3-gallery__nav pbi-r3-gallery__nav--next" type="button" data-gallery-next aria-label="Next image">›</button>
        <?php endif; ?>
      </div>

      <?php if ($gallery_count > 1): ?>
        <div class="pbi-r3-thumbs" role="list" aria-label="<?php the_title_attribute(); ?> gallery">
          <?php foreach ($gallery as $index => $image): ?>
            <button class="pbi-r3-thumb<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" role="listitem" data-gallery-thumb data-index="<?php echo esc_attr((string)$index); ?>" data-full="<?php echo esc_url($image['url']); ?>" data-alt="<?php echo esc_attr($image['alt']); ?>" aria-label="View image <?php echo esc_attr((string)($index+1)); ?> of <?php echo esc_attr((string)$gallery_count); ?>" aria-current="<?php echo $index===0?'true':'false'; ?>">
              <img src="<?php echo esc_url($image['thumb'] ?: $image['url']); ?>" alt="" loading="lazy">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($gallery_count > 0): ?>
        <div class="pbi-gallery-lightbox" data-gallery-lightbox hidden aria-hidden="true">
          <div class="pbi-gallery-lightbox__backdrop" data-gallery-close></div>
          <div class="pbi-gallery-lightbox__dialog" role="dialog" aria-modal="true" aria-label="<?php the_title_attribute(); ?> image viewer">
            <button class="pbi-gallery-lightbox__close" type="button" data-gallery-close aria-label="Close image viewer">×</button>
            <?php if ($gallery_count > 1): ?>
              <button class="pbi-gallery-lightbox__nav pbi-gallery-lightbox__nav--prev" type="button" data-gallery-prev aria-label="Previous image">‹</button>
              <button class="pbi-gallery-lightbox__nav pbi-gallery-lightbox__nav--next" type="button" data-gallery-next aria-label="Next image">›</button>
            <?php endif; ?>
            <figure><img src="<?php echo esc_url($first_image['url']); ?>" alt="<?php echo esc_attr($first_image['alt']); ?>" data-gallery-lightbox-image><figcaption><span data-gallery-caption><?php echo esc_html($first_image['alt']); ?></span><span data-gallery-counter>1 / <?php echo esc_html((string)$gallery_count); ?></span></figcaption></figure>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="pbi-r3-product__intro">
      <div class="pbi-r3-kicker">✦ <?php the_title(); ?></div>
      <h1 class="pbi-r3-heading"><?php the_title(); ?><span class="pbi-dot">.</span></h1>
      <p class="pbi-r3-copy"><?php echo esc_html(get_the_excerpt() ?: 'Premium printing that presents your brand beautifully and leaves a lasting impression.'); ?></p>
      <div class="pbi-r3-feature-row">
        <div class="pbi-r3-feature"><div class="pbi-r3-feature__icon">✦</div>Premium<br>Quality</div>
        <div class="pbi-r3-feature"><div class="pbi-r3-feature__icon">◉</div>Vibrant<br>Printing</div>
        <div class="pbi-r3-feature"><div class="pbi-r3-feature__icon">◇</div>Custom<br>Finishes</div>
        <div class="pbi-r3-feature"><div class="pbi-r3-feature__icon">◷</div>Fast<br>Turnaround</div>
      </div>
    </div>

    <div class="pbi-r3-mobile-actions">
      <a class="pbi-btn pbi-btn--primary" href="<?php echo esc_url($quote_url); ?>">Get a Quote ↗</a>
      <a class="pbi-btn pbi-btn--outline" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr($wa); ?>?text=<?php echo rawurlencode('Hi, I need a quote for ' . get_the_title()); ?>">WhatsApp</a>
    </div>

    <aside class="pbi-r3-card pbi-r3-quick">
      <h2>Get a Quick Quote</h2>
      <form method="get" action="<?php echo esc_url(home_url('/quote/')); ?>">
        <input type="hidden" name="product" value="<?php the_title_attribute(); ?>">
        <div class="pbi-field"><label>Size</label><select name="size"><?php foreach($size_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
        <div class="pbi-field"><label><?php echo esc_html($secondary_label); ?></label><select name="format"><?php foreach($secondary_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
        <div class="pbi-field"><label>Paper / Material</label><select name="paper"><?php foreach($paper_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
        <div class="pbi-field"><label>Finish</label><select name="finish"><?php foreach($finish_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
        <div class="pbi-field"><label>Quantity</label><input type="number" name="quantity" min="1" value="250"></div>
        <div class="pbi-field"><label>Delivery</label><select name="delivery"><option><?php echo esc_html($turnaround); ?></option><option>Discuss with us</option></select></div>
        <button class="pbi-btn pbi-btn--primary" type="submit">Get Quote ↗</button>
      </form>
      <div class="pbi-r3-secure"><b>◇</b> Secure & hassle-free enquiry</div>
    </aside>
  </div>

  <div class="pbi-r3-section-title"><h2>Popular <?php the_title(); ?> Options</h2><a href="<?php echo esc_url($quote_url); ?>">Discuss your requirement ↗</a></div>
  <div class="pbi-r3-style-grid">
    <?php foreach($style_labels as $index => [$label,$sub]): $image = $gallery_count ? $gallery[$index % $gallery_count] : null; ?>
      <a class="pbi-r3-style-card" href="<?php echo esc_url($quote_url); ?>">
        <div class="pbi-r3-style-card__image"><?php if($image): ?><img src="<?php echo esc_url($image['thumb'] ?: $image['url']); ?>" alt="<?php echo esc_attr($label . ' - ' . get_the_title()); ?>" loading="lazy"><?php endif; ?></div>
        <div class="pbi-r3-style-card__body"><strong><?php echo esc_html($label); ?></strong><span><?php echo esc_html($sub); ?></span><i>→</i></div>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (trim(wp_strip_all_tags(get_the_content()))): ?>
    <section class="pbi-product-copy pbi-product-seo-copy"><h2><?php the_title(); ?> Printing in Chikmagalur & Karnataka</h2><?php the_content(); ?></section>
  <?php endif; ?>

  <?php if ($statewide_intro): ?>
    <section class="pbi-product-statewide">
      <div class="pbi-v4-eyebrow">Karnataka Printing Service</div><h2><?php the_title(); ?> Printing Across Karnataka</h2><p><?php echo esc_html($statewide_intro); ?></p>
      <div class="pbi-product-statewide__locations" aria-label="Major Karnataka service locations"><span>Chikmagalur / Chikkamagaluru</span><span>Bengaluru</span><span>Mysuru</span><span>Mangaluru</span><span>Hassan</span><span>Shivamogga</span><span>Udupi</span><span>Davanagere</span></div>
    </section>
  <?php endif; ?>

  <div class="pbi-r3-section-title"><h2>Frequently Asked Questions</h2></div>
  <div class="pbi-r3-faq-grid">
    <details><summary>What is the minimum order quantity?</summary><p>Minimum quantity depends on the product, material and finishing. Share your requirement and we’ll recommend the most practical run size.</p></details>
    <details><summary>Can you help with design?</summary><p>Yes. We can review your artwork and help prepare a print-ready design when needed.</p></details>
    <details><summary>What file format should I provide?</summary><p>A print-ready PDF is preferred. We can also review JPG, PNG and other common artwork formats.</p></details>
    <details><summary>What is the turnaround time?</summary><p><?php echo esc_html($turnaround); ?> for many standard jobs. Complex finishes and larger runs can take longer.</p></details>
    <details><summary>Do you handle <?php echo esc_html(strtolower(get_the_title())); ?> orders across Karnataka?</summary><p>Yes, we accept enquiries from businesses, institutions and organisations across Karnataka. Share your quantity, specifications and delivery location so we can confirm the most suitable production and dispatch plan.</p></details>
  </div>
</div>
</section>
<?php
get_template_part('template-parts/cta');
get_footer();
?>
