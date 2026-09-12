<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Print Bureau front-end Content Manager.
 *
 * Gives non-technical staff a restricted, front-end workflow for editing
 * product copy, quote specifications, SEO fields and genuine product images.
 * It deliberately does NOT grant theme/plugin/GitHub/settings access.
 */

function pbi_content_manager_caps(): array {
    return [
        'read'                          => true,
        'upload_files'                  => true,
        'edit_pbi_product'              => true,
        'read_pbi_product'              => true,
        'edit_pbi_products'             => true,
        'edit_others_pbi_products'      => true,
        'edit_published_pbi_products'   => true,
        'publish_pbi_products'          => true,
        'read_private_pbi_products'     => true,
    ];
}

function pbi_register_content_manager_role(): void {
    $caps = pbi_content_manager_caps();

    $role = get_role('pbi_content_manager');
    if (!$role) {
        add_role('pbi_content_manager', 'Print Bureau Content Manager', $caps);
        $role = get_role('pbi_content_manager');
    }
    if ($role) {
        foreach ($caps as $cap => $grant) {
            if ($grant) $role->add_cap($cap);
        }
    }

    /* Ensure existing Administrators retain full product access after the CPT
       moves to its own capability set. */
    $admin = get_role('administrator');
    if ($admin) {
        $admin_caps = array_merge(array_keys($caps), [
            'delete_pbi_product',
            'delete_pbi_products',
            'delete_others_pbi_products',
            'delete_published_pbi_products',
            'delete_private_pbi_products',
            'edit_private_pbi_products',
        ]);
        foreach ($admin_caps as $cap) $admin->add_cap($cap);
    }
}
add_action('init', 'pbi_register_content_manager_role', 5);
add_action('after_switch_theme', 'pbi_register_content_manager_role');

function pbi_ensure_content_manager_page(): void {
    $version = '2026-09-12-v1';
    if (get_option('pbi_content_manager_page_version') === $version) return;

    $page = get_page_by_path('content-manager');
    if (!$page) {
        wp_insert_post([
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => 'Content Manager',
            'post_name'    => 'content-manager',
            'post_content' => '[pbi_content_manager]',
        ]);
    } elseif (strpos((string) $page->post_content, '[pbi_content_manager]') === false) {
        wp_update_post([
            'ID'           => $page->ID,
            'post_content' => '[pbi_content_manager]',
        ]);
    }

    update_option('pbi_content_manager_page_version', $version, false);
}
add_action('init', 'pbi_ensure_content_manager_page', 35);

function pbi_is_content_manager_page(): bool {
    return is_page('content-manager');
}

function pbi_content_manager_assets(): void {
    if (!pbi_is_content_manager_page()) return;

    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_style(
        'pbi-content-manager',
        get_template_directory_uri() . '/assets/content-manager.css',
        ['pbi-product-gallery-v6'],
        pbi_asset_version('assets/content-manager.css', wp_get_theme()->get('Version'))
    );
    wp_enqueue_script(
        'pbi-content-manager',
        get_template_directory_uri() . '/assets/content-manager.js',
        ['jquery', 'jquery-ui-sortable'],
        pbi_asset_version('assets/content-manager.js', wp_get_theme()->get('Version')),
        true
    );
}
add_action('wp_enqueue_scripts', 'pbi_content_manager_assets', 30);

function pbi_content_manager_robots(array $robots): array {
    if (pbi_is_content_manager_page()) {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
    }
    return $robots;
}
add_filter('wp_robots', 'pbi_content_manager_robots');

function pbi_content_manager_nocache(): void {
    if (pbi_is_content_manager_page()) nocache_headers();
}
add_action('template_redirect', 'pbi_content_manager_nocache', 1);

function pbi_valid_gallery_ids(string $raw): array {
    $ids = array_values(array_unique(array_filter(array_map('absint', preg_split('/\s*,\s*/', $raw)))));
    $valid = [];
    foreach ($ids as $id) {
        if (get_post_type($id) !== 'attachment') continue;
        if (strpos((string) get_post_mime_type($id), 'image/') !== 0) continue;
        $valid[] = $id;
    }
    return $valid;
}

function pbi_managed_product_manifest_for_slug(string $slug): array {
    if (!function_exists('pbi_get_managed_content_manifest')) return [];
    $manifest = pbi_get_managed_content_manifest();
    foreach (($manifest['products'] ?? []) as $product) {
        if (!is_array($product)) continue;
        if (sanitize_title((string) ($product['slug'] ?? '')) === $slug) return $product;
    }
    return [];
}

function pbi_handle_content_manager_save(): void {
    if (!pbi_is_content_manager_page() || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return;
    if (!is_user_logged_in()) return;

    $action = isset($_POST['pbi_cm_action']) ? sanitize_key(wp_unslash($_POST['pbi_cm_action'])) : '';
    $post_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    if (!$post_id || get_post_type($post_id) !== 'pbi_product' || !current_user_can('edit_post', $post_id)) {
        wp_die('You do not have permission to edit this product.', 'Access denied', ['response' => 403]);
    }

    check_admin_referer('pbi_cm_save_' . $post_id, 'pbi_cm_nonce');

    if ($action === 'reset_gallery') {
        delete_post_meta($post_id, '_pbi_gallery_override');
        delete_post_meta($post_id, '_pbi_gallery_ids');
        wp_safe_redirect(add_query_arg(['product' => $post_id, 'updated' => 'gallery-reset'], home_url('/content-manager/')));
        exit;
    }

    if ($action === 'reset_content') {
        delete_post_meta($post_id, '_pbi_content_override');
        $manifest_product = pbi_managed_product_manifest_for_slug((string) get_post_field('post_name', $post_id));
        if ($manifest_product && function_exists('pbi_sync_managed_product')) {
            pbi_sync_managed_product($manifest_product);
        }
        wp_safe_redirect(add_query_arg(['product' => $post_id, 'updated' => 'content-reset'], home_url('/content-manager/')));
        exit;
    }

    if ($action !== 'save') return;

    $title = isset($_POST['product_title']) ? sanitize_text_field(wp_unslash($_POST['product_title'])) : '';
    $excerpt = isset($_POST['product_excerpt']) ? sanitize_textarea_field(wp_unslash($_POST['product_excerpt'])) : '';
    $content = isset($_POST['product_content']) ? wp_kses_post(wp_unslash($_POST['product_content'])) : '';

    $postarr = [
        'ID'           => $post_id,
        'post_title'   => $title ?: get_the_title($post_id),
        'post_excerpt' => $excerpt,
        'post_content' => $content,
    ];
    wp_update_post(wp_slash($postarr));

    $text_meta = [
        'sizes'            => '_pbi_sizes',
        'paper'            => '_pbi_paper',
        'finish'           => '_pbi_finish',
        'turnaround'       => '_pbi_turnaround',
        'seo_title'        => '_pbi_seo_title',
        'meta_description' => '_pbi_meta_description',
    ];
    foreach ($text_meta as $field => $meta_key) {
        $raw = isset($_POST[$field]) ? wp_unslash($_POST[$field]) : '';
        $value = $field === 'meta_description' ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
        update_post_meta($post_id, $meta_key, $value);
    }
    update_post_meta($post_id, '_pbi_content_override', '1');

    $gallery_raw = isset($_POST['gallery_ids']) ? sanitize_text_field(wp_unslash($_POST['gallery_ids'])) : '';
    $gallery_ids = pbi_valid_gallery_ids($gallery_raw);
    update_post_meta($post_id, '_pbi_gallery_ids', implode(',', $gallery_ids));

    if ($gallery_ids) {
        update_post_meta($post_id, '_pbi_gallery_override', '1');
        set_post_thumbnail($post_id, $gallery_ids[0]);
    } elseif (!empty($_POST['gallery_override_enabled'])) {
        update_post_meta($post_id, '_pbi_gallery_override', '1');
        delete_post_thumbnail($post_id);
    }

    wp_safe_redirect(add_query_arg(['product' => $post_id, 'updated' => '1'], home_url('/content-manager/')));
    exit;
}
add_action('template_redirect', 'pbi_handle_content_manager_save', 5);

function pbi_cm_gallery_preview(int $post_id): string {
    $ids = function_exists('pbi_product_gallery_ids') ? pbi_product_gallery_ids($post_id) : [];
    $html = '<input type="hidden" id="pbi-cm-gallery-ids" name="gallery_ids" value="' . esc_attr(implode(',', $ids)) . '">';
    $html .= '<input type="hidden" name="gallery_override_enabled" value="1">';
    $html .= '<div class="pbi-cm-gallery-grid" id="pbi-cm-gallery-grid">';
    foreach ($ids as $id) {
        $src = wp_get_attachment_image_url($id, 'medium');
        if (!$src) continue;
        $html .= '<div class="pbi-cm-gallery-item" data-id="' . esc_attr((string) $id) . '">';
        $html .= '<img src="' . esc_url($src) . '" alt="">';
        $html .= '<button type="button" class="pbi-cm-remove-image" aria-label="Remove image">×</button>';
        $html .= '<span class="pbi-cm-drag" aria-hidden="true">⋮⋮</span>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

function pbi_cm_status_notice(): string {
    $updated = isset($_GET['updated']) ? sanitize_key(wp_unslash($_GET['updated'])) : '';
    if (!$updated) return '';
    $messages = [
        '1'             => 'Changes saved successfully.',
        'gallery-reset' => 'The product gallery is using the GitHub/default images again.',
        'content-reset' => 'Product text and SEO were reset to the managed website defaults.',
    ];
    return isset($messages[$updated]) ? '<div class="pbi-cm-notice">' . esc_html($messages[$updated]) . '</div>' : '';
}

function pbi_content_manager_shortcode(): string {
    ob_start();
    echo '<section class="pbi-cm-page"><div class="pbi-wrap">';

    if (!is_user_logged_in()) {
        echo '<div class="pbi-cm-login-card">';
        echo '<div class="pbi-v4-eyebrow">Print Bureau Content Manager</div>';
        echo '<h1>Staff Login</h1><p>Sign in to update product images, text, specifications and SEO. No coding is required.</p>';
        wp_login_form([
            'redirect'       => home_url('/content-manager/'),
            'remember'       => true,
            'label_username' => 'Username or Email',
            'label_password' => 'Password',
            'label_log_in'   => 'Sign In',
        ]);
        echo '</div></div></section>';
        return ob_get_clean();
    }

    if (!current_user_can('edit_pbi_products') && !current_user_can('manage_options')) {
        echo '<div class="pbi-cm-login-card"><h1>Access not enabled</h1><p>This account does not have Print Bureau Content Manager permission.</p></div>';
        echo '</div></section>';
        return ob_get_clean();
    }

    echo '<div class="pbi-cm-topbar">';
    echo '<div><div class="pbi-v4-eyebrow">Front-End Content Manager</div><h1>Website Products</h1><p>Update product pages without opening the WordPress editor or touching code.</p></div>';
    echo '<div class="pbi-cm-topbar-actions"><a class="pbi-btn pbi-btn--outline" href="' . esc_url(home_url('/products/')) . '" target="_blank" rel="noopener">View Website ↗</a><a class="pbi-btn pbi-btn--outline" href="' . esc_url(wp_logout_url(home_url('/content-manager/'))) . '">Log Out</a></div>';
    echo '</div>';
    echo pbi_cm_status_notice();

    $post_id = isset($_GET['product']) ? absint($_GET['product']) : 0;
    if (!$post_id) {
        $products = get_posts([
            'post_type'      => 'pbi_product',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => ['menu_order' => 'ASC', 'title' => 'ASC'],
        ]);
        echo '<div class="pbi-cm-product-grid">';
        foreach ($products as $product) {
            if (!current_user_can('edit_post', $product->ID)) continue;
            $img = pbi_product_image_url($product->ID, 'pbi-card');
            echo '<article class="pbi-cm-product-card">';
            echo '<div class="pbi-cm-product-card__image">';
            if ($img) echo '<img src="' . esc_url($img) . '" alt="' . esc_attr($product->post_title) . '">';
            echo '</div><div class="pbi-cm-product-card__body"><h2>' . esc_html($product->post_title) . '</h2>';
            echo '<div class="pbi-cm-product-card__actions"><a class="pbi-btn pbi-btn--primary" href="' . esc_url(add_query_arg('product', $product->ID, home_url('/content-manager/'))) . '">Edit Product</a><a class="pbi-btn pbi-btn--outline" href="' . esc_url(get_permalink($product)) . '" target="_blank" rel="noopener">View ↗</a></div></div></article>';
        }
        echo '</div>';
    } else {
        $product = get_post($post_id);
        if (!$product || $product->post_type !== 'pbi_product' || !current_user_can('edit_post', $post_id)) {
            echo '<div class="pbi-cm-login-card"><h2>Product unavailable</h2></div>';
        } else {
            $seo_title = (string) get_post_meta($post_id, '_pbi_seo_title', true);
            $meta_description = (string) get_post_meta($post_id, '_pbi_meta_description', true);
            $gallery_override = get_post_meta($post_id, '_pbi_gallery_override', true) === '1';

            echo '<div class="pbi-cm-editor-head"><div><a href="' . esc_url(home_url('/content-manager/')) . '">← All Products</a><h2>Edit ' . esc_html($product->post_title) . '</h2></div><a class="pbi-btn pbi-btn--outline" href="' . esc_url(get_permalink($post_id)) . '" target="_blank" rel="noopener">View Product ↗</a></div>';
            echo '<form class="pbi-cm-editor" method="post">';
            wp_nonce_field('pbi_cm_save_' . $post_id, 'pbi_cm_nonce');
            echo '<input type="hidden" name="product_id" value="' . esc_attr((string) $post_id) . '"><input type="hidden" name="pbi_cm_action" value="save">';

            echo '<div class="pbi-cm-main">';
            echo '<div class="pbi-cm-panel"><h3>Basic Information</h3>';
            echo '<label>Product name<input type="text" name="product_title" value="' . esc_attr($product->post_title) . '" required></label>';
            echo '<label>Short description<textarea name="product_excerpt" rows="4">' . esc_textarea($product->post_excerpt) . '</textarea></label>';
            echo '<label>Full description</label>';
            wp_editor($product->post_content, 'pbi_cm_product_content', [
                'textarea_name' => 'product_content',
                'media_buttons' => false,
                'textarea_rows' => 11,
                'teeny'         => false,
                'quicktags'     => false,
                'tinymce'       => [
                    'toolbar1' => 'bold italic bullist numlist link unlink undo redo',
                    'toolbar2' => '',
                ],
            ]);
            echo '</div>';

            echo '<div class="pbi-cm-panel"><div class="pbi-cm-panel-title"><div><h3>Product Images</h3><p>Upload 4–6 real views. Drag to reorder. The first image becomes the main product image.</p></div><button type="button" class="pbi-btn pbi-btn--primary" id="pbi-cm-add-images">+ Add Images</button></div>';
            if (!$gallery_override) echo '<div class="pbi-cm-default-note">Currently using the GitHub/default gallery. Adding images here will switch this product to staff-managed images.</div>';
            echo pbi_cm_gallery_preview($post_id);
            echo '<p class="pbi-cm-help">Recommended: landscape images around 1200 × 900 px or larger. JPG, PNG and WebP are suitable.</p>';
            echo '</div>';

            echo '<div class="pbi-cm-panel"><h3>Quote Specifications</h3><div class="pbi-cm-field-grid">';
            foreach ([
                'sizes' => ['Sizes / Formats', '_pbi_sizes'],
                'paper' => ['Paper / Material', '_pbi_paper'],
                'finish' => ['Finishes / Options', '_pbi_finish'],
                'turnaround' => ['Turnaround', '_pbi_turnaround'],
            ] as $field => [$label, $meta]) {
                echo '<label>' . esc_html($label) . '<input type="text" name="' . esc_attr($field) . '" value="' . esc_attr((string) get_post_meta($post_id, $meta, true)) . '"></label>';
            }
            echo '</div></div>';

            echo '<div class="pbi-cm-panel"><h3>Google / SEO</h3><label>SEO page title<input type="text" name="seo_title" maxlength="70" value="' . esc_attr($seo_title) . '"></label><label>Meta description<textarea name="meta_description" rows="4" maxlength="170">' . esc_textarea($meta_description) . '</textarea></label><p class="pbi-cm-help">Keep the title specific to the product and Karnataka. Write the description naturally for people, not as a keyword list.</p></div>';
            echo '</div>';

            echo '<aside class="pbi-cm-savebar"><div><strong>Ready to publish?</strong><span>Changes go live immediately after saving.</span></div><button class="pbi-btn pbi-btn--primary" type="submit">Save Changes</button></aside>';
            echo '</form>';

            echo '<div class="pbi-cm-reset-row">';
            echo '<form method="post" onsubmit="return confirm(\'Use the GitHub/default images for this product again?\');">';
            wp_nonce_field('pbi_cm_save_' . $post_id, 'pbi_cm_nonce');
            echo '<input type="hidden" name="product_id" value="' . esc_attr((string) $post_id) . '"><input type="hidden" name="pbi_cm_action" value="reset_gallery"><button class="pbi-cm-link-button" type="submit">Reset images to website defaults</button></form>';
            echo '<form method="post" onsubmit="return confirm(\'Reset product text, specifications and SEO to the GitHub-managed defaults?\');">';
            wp_nonce_field('pbi_cm_save_' . $post_id, 'pbi_cm_nonce');
            echo '<input type="hidden" name="product_id" value="' . esc_attr((string) $post_id) . '"><input type="hidden" name="pbi_cm_action" value="reset_content"><button class="pbi-cm-link-button" type="submit">Reset text/SEO to website defaults</button></form>';
            echo '</div>';
        }
    }

    echo '</div></section>';
    return ob_get_clean();
}
add_shortcode('pbi_content_manager', 'pbi_content_manager_shortcode');

/** Floating front-end edit control on product pages for permitted staff. */
function pbi_frontend_edit_product_button(): void {
    if (!is_singular('pbi_product') || !is_user_logged_in()) return;
    $post_id = get_queried_object_id();
    if (!$post_id || !current_user_can('edit_post', $post_id)) return;
    $url = add_query_arg('product', $post_id, home_url('/content-manager/'));
    echo '<a class="pbi-front-edit-button" href="' . esc_url($url) . '">✎ Edit this product</a>';
}
add_action('wp_footer', 'pbi_frontend_edit_product_button', 20);
