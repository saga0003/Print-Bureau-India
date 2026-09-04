<?php if (!defined('ABSPATH')) { exit; }
$default_theme=get_theme_mod('pbi_default_theme','dark');
$products_url=get_post_type_archive_link('pbi_product') ?: home_url('/products/');
$blog_url=get_permalink((int)get_option('page_for_posts')) ?: home_url('/blog/');
?>
<!doctype html>
<html <?php language_attributes(); ?> data-theme="<?php echo esc_attr($default_theme); ?>">
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="pbi-header pbi-v4-header">
  <div class="pbi-wrap pbi-header__inner">
    <a class="pbi-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Print Bureau India home">
      <span class="pbi-brand__logo" aria-hidden="true"><img data-pbi-logo data-dark="<?php echo esc_attr(pbi_logo_url('dark')); ?>" data-light="<?php echo esc_attr(pbi_logo_url('light')); ?>" src="<?php echo esc_url(pbi_logo_url($default_theme)); ?>" alt=""></span>
      <span class="screen-reader-text">Print Bureau India</span>
    </a>
    <nav class="pbi-v4-nav" aria-label="Primary navigation">
      <a href="<?php echo esc_url($products_url); ?>">Products <span>⌄</span></a>
      <a href="<?php echo esc_url(home_url('/#solutions')); ?>">Solutions <span>⌄</span></a>
      <a href="<?php echo esc_url(home_url('/#work')); ?>">Work</a>
      <a href="<?php echo esc_url(home_url('/#about')); ?>">About</a>
      <a href="<?php echo esc_url($blog_url); ?>">Resources <span>⌄</span></a>
    </nav>
    <div class="pbi-header__actions">
      <a class="pbi-btn pbi-btn--primary pbi-v4-header-quote" href="<?php echo esc_url(home_url('/quote/')); ?>">Get Quote <span aria-hidden="true">↗</span></a>
      <button class="pbi-menu-toggle" type="button" data-menu-toggle aria-label="Open menu" aria-expanded="false"><?php echo pbi_icon('menu'); ?></button>
    </div>
  </div>
  <div class="pbi-wrap pbi-v4-mobile-nav" data-mobile-nav hidden>
    <nav>
      <a href="<?php echo esc_url($products_url); ?>">Products</a>
      <a href="<?php echo esc_url(home_url('/#solutions')); ?>">Solutions</a>
      <a href="<?php echo esc_url(home_url('/#work')); ?>">Work</a>
      <a href="<?php echo esc_url(home_url('/#about')); ?>">About</a>
      <a href="<?php echo esc_url($blog_url); ?>">Resources</a>
      <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a>
      <a class="pbi-v4-mobile-nav__quote" href="<?php echo esc_url(home_url('/quote/')); ?>">Get Quote ↗</a>
    </nav>
  </div>
</header>
<main id="main-content">
