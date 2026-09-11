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

$asset = static function(string $file): string {
    return pbi_theme_image_exists($file) ? pbi_theme_image_url($file) : '';
};

$gallery = function_exists('pbi_product_gallery_images') ? pbi_product_gallery_images($product_id) : [];
if (!$gallery) {
    $fallback = pbi_product_image_url($product_id, 'full');
    if ($fallback) {
        $gallery[] = [
            'url' => $fallback,
            'thumb' => $fallback,
            'alt' => get_the_title() . ' printing | Print Bureau India',
        ];
    }
}

$gallery_count = count($gallery);
$first_image = $gallery_count ? $gallery[0] : null;
$is_brochure = $slug === 'brochures';

$style_labels = $is_brochure ? [
    ['Bi-Fold Brochure', 'Clean & classic', 'brochures.webp'],
    ['Tri-Fold Brochure', 'Smart & compact', 'brochures.webp'],
    ['Z-Fold Brochure', 'Neat & structured', 'brochures.webp'],
    ['Gate-Fold Brochure', 'Premium & impactful', 'brochures.webp'],
    ['Multi-Page Brochure', 'Detailed & engaging', 'brochures.webp'],
] : [
    ['Premium Finish', 'Refined & tactile', pbi_product_asset_filename($product_id)],
    ['Classic Option', 'Clean & versatile', pbi_product_asset_filename($product_id)],
    ['Luxury Detail', 'Made to impress', pbi_product_asset_filename($product_id)],
    ['Brand-led Design', 'Distinctive & polished', pbi_product_asset_filename($product_id)],
    ['Custom Format', 'Built around your brief', pbi_product_asset_filename($product_id)],
];

$use_map = [
    'brochures' => ['Company profiles','Product catalogues','School & college admissions','Hotel & resort brochures','Real-estate projects','Healthcare information','Event brochures','Corporate presentations'],
    'flyers-pamphlets' => ['Promotional campaigns','Admissions','Events & launches','Local distribution','Menus & offers','Product handouts','Awareness campaigns','Retail promotions'],
    'business-cards' => ['Professionals','Sales teams','Startups','Corporate teams','Consultants','Retail businesses','Events & networking','Premium personal branding'],
    'packaging' => ['Retail products','Food products','Cosmetics','Gifting','E-commerce','Product launches','Corporate kits','Premium presentation'],
    'stationery' => ['Corporate offices','Schools & colleges','Hotels','Hospitals','Professional firms','Startups','Administrative use','Brand kits'],
    'custom-notebooks-diaries' => ['Schools & colleges','Corporate gifting','Employee onboarding','Conferences','Training programmes','Annual planners','Promotional campaigns','Custom journals'],
    'books-catalogs' => ['Product catalogues','Books','Annual reports','Prospectuses','Training manuals','Magazines','Institutional reports','Booklets'],
    'stickers-labels' => ['Product packaging','Bottles & jars','Retail labels','Logo stickers','QR labels','Event branding','Promotional stickers','Custom shapes'],
    'banners-signage' => ['Events','Retail displays','Institutions','Campaigns','Outdoor promotions','Directional signage','Standees','Exhibitions'],
    'institutional-printing' => ['Schools','Colleges','Training centres','Offices','Forms & records','Exam material','Prospectuses','Recurring print'],
    'certificates' => ['Schools & colleges','Training programmes','Awards','Events','Corporate recognition','Participation','Achievements','Premium presentation'],
    'calendars' => ['Corporate gifting','Retail promotions','Institutions','Annual branding','Desk calendars','Wall calendars','Personalised calendars','Client gifting'],
    'invitations' => ['Weddings','Corporate events','Launches','School events','College events','Celebrations','Premium invites','Custom event suites'],
];
$popular_uses = $use_map[$slug] ?? ['Business communication','Brand promotion','Institutional use','Events','Retail','Corporate requirements','Custom campaigns','Bulk printing'];

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
            <button class="pbi-r3-thumb<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" role="listitem" data-gallery-thumb data-index="<?php echo esc_attr((string) $index); ?>" data-full="<?php echo esc_url($image['url']); ?>" data-alt="<?php echo esc_attr($image['alt']); ?>" aria-label="View image <?php echo esc_attr((string) ($index + 1)); ?> of <?php echo esc_attr((string) $gallery_count); ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>">
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
            <figure>
              <img src="<?php echo esc_url($first_image['url']); ?>" alt="<?php echo esc_attr($first_image['alt']); ?>" data-gallery-lightbox-image>
              <figcaption><span data-gallery-caption><?php echo esc_html($first_image['alt']); ?></span><span data-gallery-counter>1 / <?php echo esc_html((string) $gallery_count); ?></span></figcaption>
            </figure>
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
        <div class="pbi-field"><label>Size</label><select name="size"><?php foreach ($size_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
        <div class="pbi-field"><label><?php echo $is_brochure ? 'Pages' : 'Format'; ?></label><select name="format"><option><?php echo $is_brochure ? '8 Pages' : 'Standard'; ?></option><option><?php echo $is_brochure ? '16 Pages' : 'Custom'; ?></option></select></div>
        <div class="pbi-field"><label>Paper</label><select name="paper"><?php foreach ($paper_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
        <div class="pbi-field"><label>Finish</label><select name="finish"><?php foreach ($finish_options as $v): ?><option><?php echo esc_html($v); ?></option><?php endforeach; ?></select></div>
        <div class="pbi-field"><label>Quantity</label><input type="number" name="quantity" min="1" value="250"></div>
        <div class="pbi-field"><label>Delivery</label><select name="delivery"><option><?php echo esc_html($turnaround); ?></option><option>Discuss with us</option></select></div>
        <button class="pbi-btn pbi-btn--primary" type="submit">Get Quote ↗</button>
      </form>
      <a class="pbi-product-whatsapp" target="_blank" rel="noopener" href="https://wa.me/<?php echo esc_attr($wa); ?>?text=<?php echo rawurlencode('Hi, I need a quote for ' . get_the_title()); ?>">WhatsApp us about <?php the_title(); ?></a>
      <div class="pbi-r3-secure"><b>◇</b> Secure & hassle-free enquiry</div>
    </aside>
  </div>

  <div class="pbi-product-benefits" aria-label="Print Bureau service benefits">
    <div><b>✓</b><span><strong>Artwork Review</strong><small>We check files before print</small></span></div>
    <div><b>◇</b><span><strong>Custom Quantities</strong><small>Small, bulk & repeat jobs</small></span></div>
    <div><b>▱</b><span><strong>Karnataka Delivery</strong><small>Dispatch based on job & location</small></span></div>
    <div><b>◉</b><span><strong>Human Support</strong><small>Talk to us before ordering</small></span></div>
  </div>

  <nav class="pbi-product-tabs" aria-label="Product information">
    <a href="#overview">Overview</a><a href="#specifications">Specifications</a><a href="#uses">Popular Uses</a><a href="#options">Options</a><a href="#faq">FAQs</a>
  </nav>

  <section class="pbi-product-detail" id="overview">
    <div class="pbi-product-detail__copy">
      <div class="pbi-v4-eyebrow"><?php the_title(); ?> Printing</div>
      <h2><?php the_title(); ?> that works for your requirement</h2>
      <?php if (trim(wp_strip_all_tags(get_the_content()))): ?><?php the_content(); ?><?php else: ?><p>Tell us how the print will be used, your preferred size, quantity and finish. We will help narrow the specification before production.</p><?php endif; ?>
      <a class="pbi-btn pbi-btn--primary" href="<?php echo esc_url($quote_url); ?>">Get a Quote ↗</a>
    </div>
    <div class="pbi-product-popular" id="uses">
      <h3>Popular Uses</h3>
      <ul><?php foreach ($popular_uses as $use): ?><li><?php echo esc_html($use); ?></li><?php endforeach; ?></ul>
    </div>
  </section>

  <section class="pbi-product-specs" id="specifications">
    <div><small>Sizes / Formats</small><strong><?php echo esc_html($sizes); ?></strong></div>
    <div><small>Paper / Material</small><strong><?php echo esc_html($paper); ?></strong></div>
    <div><small>Finishes</small><strong><?php echo esc_html($finish); ?></strong></div>
    <div><small>Typical Turnaround</small><strong><?php echo esc_html($turnaround); ?></strong></div>
  </section>

  <div class="pbi-r3-section-title" id="options"><h2><?php echo $is_brochure ? 'Popular Brochure Styles' : 'Popular Options'; ?></h2><a href="<?php echo esc_url($quote_url); ?>">View all options ↗</a></div>
  <div class="pbi-r3-style-grid">
    <?php foreach ($style_labels as [$label, $sub, $imgfile]): $img = $asset($imgfile); ?>
      <a class="pbi-r3-style-card" href="<?php echo esc_url($quote_url); ?>"><div class="pbi-r3-style-card__image"><?php if ($img): ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($label . ' - ' . get_the_title()); ?>" loading="lazy"><?php endif; ?></div><div class="pbi-r3-style-card__body"><strong><?php echo esc_html($label); ?></strong><span><?php echo esc_html($sub); ?></span><i>→</i></div></a>
    <?php endforeach; ?>
  </div>

  <?php if ($statewide_intro): ?>
    <section class="pbi-product-statewide">
      <div class="pbi-v4-eyebrow">Karnataka Printing Service</div>
      <h2><?php the_title(); ?> Printing Across Karnataka</h2>
      <p><?php echo esc_html($statewide_intro); ?></p>
      <div class="pbi-product-statewide__locations" aria-label="Major Karnataka service locations"><span>Chikmagalur / Chikkamagaluru</span><span>Bengaluru</span><span>Mysuru</span><span>Mangaluru</span><span>Hassan</span><span>Shivamogga</span><span>Udupi</span><span>Davanagere</span></div>
    </section>
  <?php endif; ?>

  <?php
  $related = new WP_Query([
      'post_type' => 'pbi_product', 'post_status' => 'publish', 'posts_per_page' => 6,
      'post__not_in' => [$product_id], 'orderby' => ['menu_order' => 'ASC', 'date' => 'ASC'],
  ]);
  if ($related->have_posts()): ?>
    <div class="pbi-r3-section-title"><h2>More Printing Products</h2><a href="<?php echo esc_url(get_post_type_archive_link('pbi_product')); ?>">View all products ↗</a></div>
    <div class="pbi-product-related">
      <?php while ($related->have_posts()): $related->the_post(); $related_image = pbi_product_image_url(get_the_ID(), 'pbi-card'); ?>
        <a href="<?php the_permalink(); ?>"><div><?php if ($related_image): ?><img src="<?php echo esc_url($related_image); ?>" alt="<?php echo esc_attr(get_the_title() . ' printing in Karnataka'); ?>" loading="lazy"><?php endif; ?></div><strong><?php the_title(); ?></strong></a>
      <?php endwhile; wp_reset_postdata(); ?>
    </div>
  <?php endif; ?>

  <div class="pbi-r3-section-title" id="faq"><h2>Frequently Asked Questions</h2></div>
  <div class="pbi-r3-faq-grid">
    <details><summary>What is the minimum order quantity?</summary><p>Minimum quantity depends on the product, material and finishing. Share your requirement and we’ll recommend the most practical run size.</p></details>
    <details><summary>Can you help with design?</summary><p>Yes. We can review your artwork and help prepare a print-ready design when needed.</p></details>
    <details><summary>What file format should I provide?</summary><p>A print-ready PDF is preferred. We can also review JPG, PNG and other common artwork formats.</p></details>
    <details><summary>What is the turnaround time?</summary><p><?php echo esc_html($turnaround); ?> for many standard jobs. Complex finishes and larger runs can take longer.</p></details>
    <details><summary>Do you handle <?php echo esc_html(strtolower(get_the_title())); ?> orders across Karnataka?</summary><p>Yes, we accept enquiries from businesses, institutions and organisations across Karnataka. Share your quantity, specifications and delivery location so we can confirm the most suitable production and dispatch plan.</p></details>
  </div>
</div>
</section>
<?php get_template_part('template-parts/cta'); get_footer(); ?>
