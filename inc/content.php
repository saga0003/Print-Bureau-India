<?php
if (!defined('ABSPATH')) { exit; }

function pbi_register_content_types(): void {
    register_post_type('pbi_product', [
        'labels' => ['name' => 'Products', 'singular_name' => 'Product', 'add_new_item' => 'Add Product', 'edit_item' => 'Edit Product'],
        'public' => true,
        'menu_icon' => 'dashicons-products',
        'has_archive' => 'products',
        'rewrite' => ['slug' => 'products', 'with_front' => false],
        'supports' => ['title','editor','excerpt','thumbnail','page-attributes'],
        'show_in_rest' => true,
        'menu_position' => 21,
    ]);
    register_taxonomy('pbi_product_category', ['pbi_product'], [
        'labels' => ['name' => 'Product Categories', 'singular_name' => 'Product Category'],
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'product-category'],
    ]);
    register_post_type('pbi_portfolio', [
        'labels' => ['name' => 'Portfolio', 'singular_name' => 'Project'],
        'public' => true,
        'menu_icon' => 'dashicons-format-gallery',
        'has_archive' => 'work',
        'rewrite' => ['slug' => 'work'],
        'supports' => ['title','editor','excerpt','thumbnail'],
        'show_in_rest' => true,
        'menu_position' => 22,
    ]);
}
add_action('init', 'pbi_register_content_types');

function pbi_product_meta_box(): void {
    add_meta_box('pbi_product_specs', 'Product Quote Defaults', 'pbi_product_meta_box_html', 'pbi_product', 'normal', 'high');
}
add_action('add_meta_boxes', 'pbi_product_meta_box');

function pbi_product_meta_box_html(WP_Post $post): void {
    wp_nonce_field('pbi_save_product_specs', 'pbi_product_specs_nonce');
    $fields = [
        'sizes' => ['Sizes', 'A4, A5, Custom'],
        'paper' => ['Paper / Material', '130 GSM Gloss, 170 GSM Matte, Premium Custom'],
        'finish' => ['Finishes', 'Matte Lamination, Gloss Lamination, Spot UV'],
        'turnaround' => ['Turnaround', '3–5 business days'],
        'short_label' => ['Short card label', 'Premium print'],
    ];
    echo '<table class="form-table">';
    foreach ($fields as $key => $config) {
        $value = get_post_meta($post->ID, '_pbi_' . $key, true);
        printf('<tr><th><label for="pbi_%1$s">%2$s</label></th><td><input class="regular-text" id="pbi_%1$s" name="pbi_%1$s" value="%3$s" placeholder="%4$s"></td></tr>', esc_attr($key), esc_html($config[0]), esc_attr($value), esc_attr($config[1]));
    }
    echo '</table><p><strong>Tip:</strong> Use the Featured Image as the product card and hero image. That makes image replacement easy from WordPress.</p>';
}

function pbi_save_product_specs(int $post_id): void {
    if (!isset($_POST['pbi_product_specs_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pbi_product_specs_nonce'])), 'pbi_save_product_specs')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    foreach (['sizes','paper','finish','turnaround','short_label'] as $key) {
        if (isset($_POST['pbi_' . $key])) update_post_meta($post_id, '_pbi_' . $key, sanitize_text_field(wp_unslash($_POST['pbi_' . $key])));
    }
}
add_action('save_post_pbi_product', 'pbi_save_product_specs');

function pbi_seed_site(): void {
    pbi_register_content_types();
    $pages = [
        'Home' => ['slug' => 'home'],
        'Contact' => ['slug' => 'contact'],
        'Get a Quote' => ['slug' => 'quote'],
        'Blog' => ['slug' => 'blog'],
    ];
    $created = [];
    foreach ($pages as $title => $cfg) {
        $existing = get_page_by_path($cfg['slug']);
        if ($existing) { $created[$cfg['slug']] = $existing->ID; continue; }
        $created[$cfg['slug']] = wp_insert_post(['post_type' => 'page','post_status' => 'publish','post_title' => $title,'post_name' => $cfg['slug']]);
    }
    if (!empty($created['home']) && !is_wp_error($created['home'])) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $created['home']);
    }
    if (!empty($created['blog']) && !is_wp_error($created['blog'])) update_option('page_for_posts', (int) $created['blog']);

    $products = [
        'Business Cards' => 'Premium cards designed to make the first impression count.',
        'Brochures' => 'Brochures that inform, impress and move customers to act.',
        'Packaging' => 'Premium packaging for products, gifts and retail brands.',
        'Stationery' => 'Letterheads, envelopes and coordinated business stationery.',
        'Books & Catalogs' => 'Books, catalogs, reports and premium bound documents.',
        'Stickers & Labels' => 'Custom labels and stickers for products, events and campaigns.',
        'Banners & Signage' => 'High-impact large-format print and signage.',
        'Institutional Printing' => 'Certificates, notebooks, ID materials and institutional print.',
    ];
    foreach ($products as $title => $excerpt) {
        $exists = get_posts(['post_type'=>'pbi_product','post_status'=>'any','title'=>$title,'numberposts'=>1,'fields'=>'ids']);
        if ($exists) continue;
        wp_insert_post(['post_type'=>'pbi_product','post_status'=>'publish','post_title'=>$title,'post_excerpt'=>$excerpt,'post_content'=>'']);
    }
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'pbi_seed_site');

function pbi_get_products(int $limit = 8): WP_Query {
    return new WP_Query(['post_type'=>'pbi_product','post_status'=>'publish','posts_per_page'=>$limit,'orderby'=>['menu_order'=>'ASC','date'=>'ASC']]);
}
