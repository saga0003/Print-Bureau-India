<?php
if (!defined('ABSPATH')) { exit; }

function pbi_frontend_edit_button_assets(): void {
    if (!is_singular('pbi_product') || !is_user_logged_in()) return;
    $post_id = get_queried_object_id();
    if (!$post_id || !current_user_can('edit_post', $post_id)) return;

    wp_enqueue_style(
        'pbi-content-manager',
        get_template_directory_uri() . '/assets/content-manager.css',
        ['pbi-product-gallery-v6'],
        pbi_asset_version('assets/content-manager.css', wp_get_theme()->get('Version'))
    );
}
add_action('wp_enqueue_scripts', 'pbi_frontend_edit_button_assets', 31);
