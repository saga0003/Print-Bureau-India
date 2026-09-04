<?php
get_header();
$blog_url=get_permalink((int)get_option('page_for_posts')) ?: home_url('/blog/');
$hero_image=pbi_theme_image_exists('hero.webp') ? pbi_theme_image_url('hero.webp') : '';
$featured=new WP_Query(['post_type'=>'post','posts_per_page'=>1,'ignore_sticky_posts'=>true]);
$latest=new WP_Query(['post_type'=>'post','posts_per_page'=>8,'offset'=>1,'ignore_sticky_posts'=>true]);
$cats=get_categories(['number'=>7,'hide_empty'=>false]);
?>
<section class="pbi-r3-blog-hero">
  <div class="pbi-wrap pbi-r3-blog-hero__grid">
    <div class="pbi-r3-blog-hero__copy">
      <div class="pbi-r3-kicker">Insights & Ideas</div>
      <h1 class="pbi-r3-heading">Print Insights<span class="pbi-dot">.</span></h1>
      <p class="pbi-r3-copy">Ideas, trends & inspiration from the world of print.</p>
      <div class="pbi-r3-blog-points"><div class="pbi-r3-blog-point"><i>◇</i>Expert<br>Perspectives</div><div class="pbi-r3-blog-point"><i>◉</i>Design &<br>Inspiration</div><div class="pbi-r3-blog-point"><i>□</i>Materials &<br>Innovation</div><div class="pbi-r3-blog-point"><i>↗</i>Business<br>Growth</div></div>
    </div>
    <div class="pbi-r3-blog-hero__image"><?php if($hero_image): ?><img src="<?php echo esc_url($hero_image); ?>" alt="Print Bureau India premium printed materials" loading="eager"><?php endif; ?></div>
  </div>
</section>
<section class="pbi-r3-product">
  <div class="pbi-wrap pbi-r3-blog-layout">
    <aside class="pbi-r3-blog-filter">
      <h3>Explore by Category</h3>
      <a class="is-active" href="<?php echo esc_url($blog_url); ?>">▦ &nbsp; All Insights</a>
      <?php foreach($cats as $cat): ?><a href="<?php echo esc_url(get_category_link($cat)); ?>">◇ &nbsp; <?php echo esc_html($cat->name); ?></a><?php endforeach; ?>
      <div class="pbi-r3-blog-filter__rule"></div><h3>Filter by</h3><select aria-label="Sort insights"><option>Popular</option><option>Latest</option></select><select aria-label="Insight type"><option>All topics</option><option>Printing guides</option></select>
    </aside>
    <main>
      <div class="pbi-r3-mobile-search"><input type="search" placeholder="Search insights..." aria-label="Search insights"></div>
      <div class="pbi-r3-mobile-chips"><span>All</span><span>Printing Tips</span><span>Packaging</span><span>Branding</span><span>Sustainability</span></div>
      <?php if($featured->have_posts()):$featured->the_post();$featured_img=get_the_post_thumbnail_url(get_the_ID(),'pbi-hero') ?: (pbi_theme_image_exists('books-catalogs.webp')?pbi_theme_image_url('books-catalogs.webp'):$hero_image); ?>
        <a class="pbi-r3-featured-post" href="<?php the_permalink(); ?>">
          <div class="pbi-r3-featured-post__copy"><div class="pbi-r3-kicker">Featured Insight</div><h2><?php the_title(); ?></h2><p class="pbi-r3-copy"><?php echo esc_html(get_the_excerpt() ?: 'Thoughtful print elevates brand perception, builds trust and leaves a lasting impact.'); ?></p><span class="pbi-link">Read Article ↗</span><div class="pbi-r3-meta" style="margin-top:24px"><?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(max(2,(int)ceil(str_word_count(wp_strip_all_tags(get_the_content()))/220))); ?> min read</div></div>
          <div class="pbi-r3-featured-post__image"><?php if($featured_img): ?><img src="<?php echo esc_url($featured_img); ?>" alt="<?php the_title_attribute(); ?>" loading="eager"><?php endif; ?></div>
        </a>
      <?php wp_reset_postdata(); endif; ?>
      <div class="pbi-r3-section-title"><h2>Latest Articles</h2><a href="<?php echo esc_url($blog_url); ?>">View All ↗</a></div>
      <div class="pbi-r3-post-grid">
        <?php if($latest->have_posts()):while($latest->have_posts()):$latest->the_post();$img=get_the_post_thumbnail_url(get_the_ID(),'pbi-card');if(!$img){$fallbacks=['packaging.webp','stationery.webp','brochures.webp','books-catalogs.webp'];$file=$fallbacks[$latest->current_post%count($fallbacks)];$img=pbi_theme_image_exists($file)?pbi_theme_image_url($file):'';} $pcats=get_the_category(); ?>
          <a class="pbi-r3-post-card" href="<?php the_permalink(); ?>"><div class="pbi-r3-post-card__img"><?php if($img): ?><img src="<?php echo esc_url($img); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy"><?php endif; ?></div><div class="pbi-r3-post-card__body"><div class="pbi-r3-kicker"><?php echo esc_html($pcats[0]->name ?? 'Printing Guide'); ?></div><h3><?php the_title(); ?></h3><div class="pbi-r3-meta"><?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(max(2,(int)ceil(str_word_count(wp_strip_all_tags(get_the_content()))/220))); ?> min read</div></div></a>
        <?php endwhile;wp_reset_postdata();else: ?>
          <?php foreach([['Color Psychology in Print Design','Design & Branding','stationery.webp'],['Packaging That Tells Your Brand Story','Packaging','packaging.webp'],['Choosing the Right Paper for Your Project','Printing Guide','brochures.webp'],['Why Print Still Drives Real Results','Business Growth','books-catalogs.webp']] as [$title,$cat,$file]):$img=pbi_theme_image_exists($file)?pbi_theme_image_url($file):''; ?><div class="pbi-r3-post-card"><div class="pbi-r3-post-card__img"><?php if($img): ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy"><?php endif; ?></div><div class="pbi-r3-post-card__body"><div class="pbi-r3-kicker"><?php echo esc_html($cat); ?></div><h3><?php echo esc_html($title); ?></h3><div class="pbi-r3-meta">Print Bureau India · Guide</div></div></div><?php endforeach; ?>
        <?php endif; ?>
      </div>
    </main>
  </div>
</section>
<?php get_template_part('template-parts/cta'); get_footer(); ?>
