<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Controlled GitHub-managed content sync.
 *
 * This deliberately updates only approved website content/settings from
 * content/managed-content.json. It never deletes posts, media, leads, users,
 * orders, form submissions or other database records.
 */

function pbi_managed_content_file(): string {
    return trailingslashit(get_template_directory()) . 'content/managed-content.json';
}

function pbi_get_managed_content_manifest(): array {
    $file = pbi_managed_content_file();
    if (!is_readable($file)) return [];

    $json = file_get_contents($file);
    if ($json === false || $json === '') return [];

    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function pbi_sync_managed_page(array $page): void {
    $slug = sanitize_title($page['slug'] ?? '');
    if (!$slug) return;

    $existing = get_page_by_path($slug, OBJECT, 'page');
    $postarr = [
        'post_type'   => 'page',
        'post_status' => 'publish',
        'post_name'   => $slug,
        'post_title'  => sanitize_text_field($page['title'] ?? ucwords(str_replace('-', ' ', $slug))),
    ];

    if (!empty($page['content'])) {
        $postarr['post_content'] = wp_kses_post($page['content']);
    }

    if ($existing instanceof WP_Post) {
        $postarr['ID'] = $existing->ID;
        $post_id = wp_update_post(wp_slash($postarr), true);
    } else {
        $post_id = wp_insert_post(wp_slash($postarr), true);
    }

    if (is_wp_error($post_id) || !$post_id) return;

    update_post_meta($post_id, '_pbi_managed_content', '1');
    if (!empty($page['seo_title'])) {
        update_post_meta($post_id, '_pbi_seo_title', sanitize_text_field($page['seo_title']));
    }
    if (!empty($page['meta_description'])) {
        update_post_meta($post_id, '_pbi_meta_description', sanitize_textarea_field($page['meta_description']));
    }
}

function pbi_sync_managed_product(array $product): void {
    $slug = sanitize_title($product['slug'] ?? '');
    if (!$slug) return;

    $existing = get_page_by_path($slug, OBJECT, 'pbi_product');
    if (!$existing && !empty($product['title'])) {
        $ids = get_posts([
            'post_type'      => 'pbi_product',
            'post_status'    => 'any',
            'title'          => sanitize_text_field($product['title']),
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);
        if ($ids) $existing = get_post((int) $ids[0]);
    }

    $postarr = [
        'post_type'    => 'pbi_product',
        'post_status'  => 'publish',
        'post_name'    => $slug,
        'post_title'   => sanitize_text_field($product['title'] ?? ucwords(str_replace('-', ' ', $slug))),
        'post_excerpt' => sanitize_textarea_field($product['excerpt'] ?? ''),
        'post_content' => wp_kses_post($product['content'] ?? ''),
        'menu_order'   => (int) ($product['menu_order'] ?? 0),
    ];

    if ($existing instanceof WP_Post) {
        $postarr['ID'] = $existing->ID;
        $post_id = wp_update_post(wp_slash($postarr), true);
    } else {
        $post_id = wp_insert_post(wp_slash($postarr), true);
    }

    if (is_wp_error($post_id) || !$post_id) return;

    update_post_meta($post_id, '_pbi_managed_content', '1');

    $meta_map = [
        'sizes'       => '_pbi_sizes',
        'paper'       => '_pbi_paper',
        'finish'      => '_pbi_finish',
        'turnaround'  => '_pbi_turnaround',
        'seo_title'   => '_pbi_seo_title',
        'meta_description' => '_pbi_meta_description',
    ];

    foreach ($meta_map as $key => $meta_key) {
        if (!array_key_exists($key, $product)) continue;
        $value = $key === 'meta_description'
            ? sanitize_textarea_field($product[$key])
            : sanitize_text_field($product[$key]);
        update_post_meta($post_id, $meta_key, $value);
    }
}

function pbi_apply_managed_content(bool $force = false): void {
    $file = pbi_managed_content_file();
    if (!is_readable($file)) return;

    $hash = hash_file('sha256', $file);
    if (!$force && $hash && get_option('pbi_managed_content_hash') === $hash) return;

    $manifest = pbi_get_managed_content_manifest();
    if (!$manifest) return;

    if (!empty($manifest['theme_mods']) && is_array($manifest['theme_mods'])) {
        foreach ($manifest['theme_mods'] as $key => $value) {
            if (strpos((string) $key, 'pbi_') !== 0) continue;
            set_theme_mod(sanitize_key($key), is_bool($value) ? $value : sanitize_text_field((string) $value));
        }
    }

    if (!empty($manifest['pages']) && is_array($manifest['pages'])) {
        foreach ($manifest['pages'] as $page) {
            if (is_array($page)) pbi_sync_managed_page($page);
        }
    }

    if (!empty($manifest['products']) && is_array($manifest['products'])) {
        foreach ($manifest['products'] as $product) {
            if (is_array($product)) pbi_sync_managed_product($product);
        }
    }

    if ($hash) update_option('pbi_managed_content_hash', $hash, false);
    update_option('pbi_managed_content_last_sync', time(), false);
}

/* CPTs register at init priority 10, so sync after them. */
add_action('init', static function (): void {
    pbi_apply_managed_content(false);
}, 30);

/* Manual helper for administrators/developers when needed. */
function pbi_force_managed_content_sync(): void {
    if (current_user_can('manage_options')) {
        pbi_apply_managed_content(true);
    }
}
