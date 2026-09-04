<?php
if (!defined('ABSPATH')) { exit; }

function pbi_theme_setup(): void {
    load_theme_textdomain('print-bureau-premium', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', ['height' => 500, 'width' => 500, 'flex-height' => true, 'flex-width' => true]);
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    register_nav_menus([
        'primary' => __('Primary navigation', 'print-bureau-premium'),
        'footer'  => __('Footer navigation', 'print-bureau-premium'),
    ]);
    add_image_size('pbi-card', 900, 650, true);
    add_image_size('pbi-hero', 1600, 1100, true);
}
add_action('after_setup_theme', 'pbi_theme_setup');

function pbi_enqueue_assets(): void {
    $version = wp_get_theme()->get('Version');
    wp_enqueue_style('pbi-style', get_stylesheet_uri(), [], $version);
    wp_enqueue_script('pbi-theme', get_template_directory_uri() . '/assets/theme.js', [], $version, true);
    wp_localize_script('pbi-theme', 'PBI_THEME', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'quoteUrl' => home_url('/quote/'),
        'themeDefault' => get_theme_mod('pbi_default_theme', 'dark'),
    ]);
}
add_action('wp_enqueue_scripts', 'pbi_enqueue_assets');

function pbi_body_classes(array $classes): array {
    $classes[] = 'pbi-theme';
    return $classes;
}
add_filter('body_class', 'pbi_body_classes');

/**
 * Return the logo intended for the requested page background.
 * $surface = dark  -> white-text logo
 * $surface = light -> dark-text logo
 */
function pbi_logo_url(string $surface = 'dark'): string {
    $for_dark_surface = get_theme_mod('pbi_logo_dark');
    $for_light_surface = get_theme_mod('pbi_logo_light');

    if ($surface === 'dark' && $for_dark_surface) {
        return esc_url($for_dark_surface);
    }
    if ($surface === 'light' && $for_light_surface) {
        return esc_url($for_light_surface);
    }

    $fallback = $surface === 'dark'
        ? get_template_directory_uri() . '/Logo/SVG/Print%20Bureau%20Transparent%20White%20BG.svg'
        : get_template_directory_uri() . '/Logo/SVG/Print%20Bureau%20Transparent%20BG.svg';

    return esc_url($fallback);
}

function pbi_contact(string $key, string $default = ''): string {
    return (string) get_theme_mod('pbi_' . $key, $default);
}

function pbi_icon(string $name): string {
    $icons = [
        'arrow' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'sun' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
        'menu' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
    ];
    return $icons[$name] ?? '';
}

function pbi_primary_menu_fallback(): void {
    $items = [
        'Products' => get_post_type_archive_link('pbi_product') ?: home_url('/products/'),
        'Work' => home_url('/#work'),
        'Blog' => get_permalink((int) get_option('page_for_posts')) ?: home_url('/blog/'),
        'About' => home_url('/#about'),
        'Contact' => home_url('/contact/'),
    ];
    echo '<div class="pbi-nav">';
    foreach ($items as $label => $url) {
        printf('<a href="%s">%s</a>', esc_url($url), esc_html($label));
    }
    echo '</div>';
}

function pbi_menu_link_class(array $atts, WP_Post $item, stdClass $args): array {
    if (($args->theme_location ?? '') === 'primary') {
        $atts['class'] = trim(($atts['class'] ?? '') . ' pbi-nav__link');
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'pbi_menu_link_class', 10, 3);
