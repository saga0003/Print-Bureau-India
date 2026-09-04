<?php
/**
 * GitHub-first theme asset helpers.
 *
 * The theme can use branded images committed under assets/images/ directly.
 * If a GitHub image is not present, WordPress Media Library / Featured Images
 * remain the fallback. This lets ChatGPT/GitHub drive most visual updates while
 * still preserving an easy WordPress override path.
 */
if (!defined('ABSPATH')) { exit; }

function pbi_prefer_github_assets(): bool {
    return (bool) get_theme_mod('pbi_prefer_github_assets', true);
}

function pbi_theme_image_path(string $filename): string {
    return trailingslashit(get_template_directory()) . 'assets/images/' . basename($filename);
}

function pbi_theme_image_url(string $filename): string {
    return trailingslashit(get_template_directory_uri()) . 'assets/images/' . rawurlencode(basename($filename));
}

function pbi_theme_image_exists(string $filename): bool {
    return is_file(pbi_theme_image_path($filename));
}

function pbi_product_asset_filename(int $post_id): string {
    $slug = (string) get_post_field('post_name', $post_id);
    $map = [
        'business-cards'        => 'business-cards.webp',
        'brochures'             => 'brochures.webp',
        'packaging'             => 'packaging.webp',
        'stationery'            => 'stationery.webp',
        'books-catalogs'        => 'books-catalogs.webp',
        'invitations'           => 'invitations.webp',
        'stickers-labels'       => 'stickers-labels.webp',
        'banners-signage'       => 'large-format.webp',
        'large-format'          => 'large-format.webp',
        'institutional-printing'=> 'institutional-printing.webp',
    ];

    return $map[$slug] ?? ($slug ? sanitize_file_name($slug) . '.webp' : '');
}

function pbi_product_image_url(int $post_id, string $size = 'pbi-card'): string {
    $asset = pbi_product_asset_filename($post_id);

    if (pbi_prefer_github_assets() && $asset && pbi_theme_image_exists($asset)) {
        return pbi_theme_image_url($asset);
    }

    $featured = get_the_post_thumbnail_url($post_id, $size);
    if ($featured) {
        return (string) $featured;
    }

    if (!$featured && $asset && pbi_theme_image_exists($asset)) {
        return pbi_theme_image_url($asset);
    }

    return '';
}

function pbi_hero_image_url(): string {
    if (pbi_prefer_github_assets() && pbi_theme_image_exists('hero.webp')) {
        return pbi_theme_image_url('hero.webp');
    }

    $custom = (string) get_theme_mod('pbi_hero_image', '');
    if ($custom) {
        return $custom;
    }

    if (pbi_theme_image_exists('hero.webp')) {
        return pbi_theme_image_url('hero.webp');
    }

    return '';
}

function pbi_print_product_image(int $post_id, string $size = 'pbi-card', array $attrs = []): void {
    $url = pbi_product_image_url($post_id, $size);
    if (!$url) { return; }

    $defaults = [
        'alt'     => get_the_title($post_id),
        'loading' => 'lazy',
    ];
    $attrs = array_merge($defaults, $attrs);

    $html_attrs = '';
    foreach ($attrs as $key => $value) {
        if ($value === null || $value === false) { continue; }
        $html_attrs .= ' ' . esc_attr($key) . '="' . esc_attr((string) $value) . '"';
    }

    echo '<img src="' . esc_url($url) . '"' . $html_attrs . '>';
}
