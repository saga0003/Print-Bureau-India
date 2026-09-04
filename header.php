<?php if (!defined('ABSPATH')) { exit; }
$default_theme = get_theme_mod('pbi_default_theme','dark');
?>
<!doctype html>
<html <?php language_attributes(); ?> data-theme="<?php echo esc_attr($default_theme); ?>">
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="pbi-header">
  <div class="pbi-wrap pbi-header__inner">
    <a class="pbi-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Print Bureau India home">
      <span class="pbi-brand__logo" aria-hidden="true">
        <img data-pbi-logo data-dark="<?php echo esc_attr(pbi_logo_url('dark')); ?>" data-light="<?php echo esc_attr(pbi_logo_url('light')); ?>" src="<?php echo esc_url(pbi_logo_url($default_theme)); ?>" alt="">
      </span>
      <span class="screen-reader-text">Print Bureau India</span>
    </a>
    <?php
      if (has_nav_menu('primary')) {
          wp_nav_menu(['theme_location'=>'primary','container'=>'nav','container_class'=>'pbi-nav','menu_class'=>'pbi-nav','fallback_cb'=>false,'depth'=>2]);
      } else {
          pbi_primary_menu_fallback();
      }
    ?>
    <div class="pbi-header__actions">
      <button class="pbi-theme-toggle" type="button" data-theme-toggle aria-label="Switch colour mode"><?php echo pbi_icon('sun'); ?></button>
      <a class="pbi-btn pbi-btn--primary" href="<?php echo esc_url(home_url('/quote/')); ?>">Get Quote <span aria-hidden="true">↗</span></a>
      <button class="pbi-menu-toggle" type="button" data-menu-toggle aria-label="Open menu" aria-expanded="false"><?php echo pbi_icon('menu'); ?></button>
    </div>
  </div>
  <div class="pbi-wrap" data-mobile-nav hidden>
    <nav style="display:grid;gap:4px;padding:10px 0 18px">
      <a href="<?php echo esc_url(get_post_type_archive_link('pbi_product') ?: home_url('/products/')); ?>">Products</a>
      <a href="<?php echo esc_url(home_url('/#work')); ?>">Work</a>
      <a href="<?php echo esc_url(get_permalink((int)get_option('page_for_posts')) ?: home_url('/blog/')); ?>">Blog</a>
      <a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a>
      <a href="<?php echo esc_url(home_url('/quote/')); ?>">Get Quote</a>
    </nav>
  </div>
</header>
<main id="main-content">
