<?php
if (!defined('ABSPATH')) { exit; }

function pbi_enqueue_product_ui_v7(): void {
    if (!is_singular('pbi_product')) return;
    $relative = 'assets/product-ui-v7.css';
    $path = trailingslashit(get_template_directory()) . $relative;
    if (!is_file($path)) return;
    wp_enqueue_style(
        'pbi-product-ui-v7',
        get_template_directory_uri() . '/assets/product-ui-v7.css',
        ['pbi-product-gallery-v6'],
        pbi_asset_version($relative, wp_get_theme()->get('Version'))
    );
}
add_action('wp_enqueue_scripts', 'pbi_enqueue_product_ui_v7', 35);
