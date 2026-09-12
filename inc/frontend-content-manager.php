<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Print Bureau front-end Content Manager.
 * A restricted, application-style editing workspace for non-technical staff.
 */

function pbi_content_manager_caps(): array {
    return [
        'read'                        => true,
        'upload_files'                => true,
        'edit_pbi_product'            => true,
        'read_pbi_product'            => true,
        'edit_pbi_products'           => true,
        'edit_others_pbi_products'    => true,
        'edit_published_pbi_products' => true,
        'publish_pbi_products'        => true,
        'read_private_pbi_products'   => true,
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

    $admin = get_role('administrator');
    if ($admin) {
        $admin_caps = array_merge(array_keys($caps), [
            'delete_pbi_product','delete_pbi_products','delete_others_pbi_products',
            'delete_published_pbi_products','delete_private_pbi_products','edit_private_pbi_products',
        ]);
        foreach ($admin_caps as $cap) $admin->add_cap($cap);
    }
}
add_action('init', 'pbi_register_content_manager_role', 5);
add_action('after_switch_theme', 'pbi_register_content_manager_role');

function pbi_ensure_content_manager_page(): void {
    $version = '2026-09-12-v2';
    if (get_option('pbi_content_manager_page_version') === $version) return;

    $page = get_page_by_path('content-manager');
    if (!$page) {
        wp_insert_post([
            'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Content Manager',
            'post_name' => 'content-manager', 'post_content' => '[pbi_content_manager]',
        ]);
    } elseif (strpos((string) $page->post_content, '[pbi_content_manager]') === false) {
        wp_update_post(['ID' => $page->ID, 'post_content' => '[pbi_content_manager]']);
    }
    update_option('pbi_content_manager_page_version', $version, false);
}
add_action('init', 'pbi_ensure_content_manager_page', 35);

function pbi_is_content_manager_page(): bool { return is_page('content-manager'); }

function pbi_content_manager_body_class(array $classes): array {
    if (pbi_is_content_manager_page()) $classes[] = 'pbi-content-manager-app';
    return $classes;
}
add_filter('body_class', 'pbi_content_manager_body_class');

function pbi_content_manager_assets(): void {
    if (!pbi_is_content_manager_page()) return;
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_style('pbi-content-manager', get_template_directory_uri() . '/assets/content-manager.css', ['pbi-product-gallery-v6'], pbi_asset_version('assets/content-manager.css', wp_get_theme()->get('Version')));
    wp_enqueue_script('pbi-content-manager', get_template_directory_uri() . '/assets/content-manager.js', ['jquery','jquery-ui-sortable'], pbi_asset_version('assets/content-manager.js', wp_get_theme()->get('Version')), true);
}
add_action('wp_enqueue_scripts', 'pbi_content_manager_assets', 30);

function pbi_content_manager_robots(array $robots): array {
    if (pbi_is_content_manager_page()) { $robots['noindex'] = true; $robots['nofollow'] = true; }
    return $robots;
}
add_filter('wp_robots', 'pbi_content_manager_robots');

function pbi_content_manager_nocache(): void { if (pbi_is_content_manager_page()) nocache_headers(); }
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
        if (is_array($product) && sanitize_title((string) ($product['slug'] ?? '')) === $slug) return $product;
    }
    return [];
}

function pbi_handle_content_manager_save(): void {
    if (!pbi_is_content_manager_page() || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !is_user_logged_in()) return;

    $action = isset($_POST['pbi_cm_action']) ? sanitize_key(wp_unslash($_POST['pbi_cm_action'])) : '';
    $post_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    if (!$post_id || get_post_type($post_id) !== 'pbi_product' || !current_user_can('edit_post', $post_id)) {
        wp_die('You do not have permission to edit this product.', 'Access denied', ['response' => 403]);
    }
    check_admin_referer('pbi_cm_save_' . $post_id, 'pbi_cm_nonce');

    if ($action === 'reset_gallery') {
        delete_post_meta($post_id, '_pbi_gallery_override');
        delete_post_meta($post_id, '_pbi_gallery_ids');
        delete_post_thumbnail($post_id);
        wp_safe_redirect(add_query_arg(['product'=>$post_id,'updated'=>'gallery-reset'], home_url('/content-manager/')));
        exit;
    }

    if ($action === 'reset_content') {
        delete_post_meta($post_id, '_pbi_content_override');
        $manifest_product = pbi_managed_product_manifest_for_slug((string) get_post_field('post_name', $post_id));
        if ($manifest_product && function_exists('pbi_sync_managed_product')) pbi_sync_managed_product($manifest_product);
        wp_safe_redirect(add_query_arg(['product'=>$post_id,'updated'=>'content-reset'], home_url('/content-manager/')));
        exit;
    }

    if ($action !== 'save') return;

    $title = isset($_POST['product_title']) ? sanitize_text_field(wp_unslash($_POST['product_title'])) : '';
    $excerpt = isset($_POST['product_excerpt']) ? sanitize_textarea_field(wp_unslash($_POST['product_excerpt'])) : '';
    $content = isset($_POST['product_content']) ? wp_kses_post(wp_unslash($_POST['product_content'])) : '';
    wp_update_post(wp_slash([
        'ID'=>$post_id,
        'post_title'=>$title ?: get_the_title($post_id),
        'post_excerpt'=>$excerpt,
        'post_content'=>$content,
    ]));

    $text_meta = [
        'sizes'=>'_pbi_sizes','paper'=>'_pbi_paper','finish'=>'_pbi_finish','turnaround'=>'_pbi_turnaround',
        'seo_title'=>'_pbi_seo_title','meta_description'=>'_pbi_meta_description',
    ];
    foreach ($text_meta as $field => $meta_key) {
        $raw = isset($_POST[$field]) ? wp_unslash($_POST[$field]) : '';
        $value = $field === 'meta_description' ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
        update_post_meta($post_id, $meta_key, $value);
    }
    update_post_meta($post_id, '_pbi_content_override', '1');

    $gallery_ids = pbi_valid_gallery_ids(isset($_POST['gallery_ids']) ? sanitize_text_field(wp_unslash($_POST['gallery_ids'])) : '');
    if ($gallery_ids) {
        update_post_meta($post_id, '_pbi_gallery_ids', implode(',', $gallery_ids));
        update_post_meta($post_id, '_pbi_gallery_override', '1');
        set_post_thumbnail($post_id, $gallery_ids[0]);
    } else {
        delete_post_meta($post_id, '_pbi_gallery_ids');
        delete_post_meta($post_id, '_pbi_gallery_override');
        delete_post_thumbnail($post_id);
    }

    update_post_meta($post_id, '_pbi_cm_last_editor', get_current_user_id());
    update_post_meta($post_id, '_pbi_cm_last_edit_time', time());

    wp_safe_redirect(add_query_arg(['product'=>$post_id,'updated'=>'1'], home_url('/content-manager/')));
    exit;
}
add_action('template_redirect', 'pbi_handle_content_manager_save', 5);

function pbi_cm_staff_gallery_ids(int $post_id): array {
    return function_exists('pbi_product_gallery_ids') ? pbi_product_gallery_ids($post_id) : [];
}

function pbi_cm_product_health(int $post_id): array {
    $post = get_post($post_id);
    if (!$post) return ['score'=>0,'label'=>'Needs work','items'=>[]];

    $gallery_count = function_exists('pbi_product_gallery_images') ? count(pbi_product_gallery_images($post_id)) : 0;
    $checks = [
        'Short description' => strlen(trim((string) $post->post_excerpt)) >= 60,
        'Full description'  => strlen(trim(wp_strip_all_tags((string) $post->post_content))) >= 180,
        'Product images'    => $gallery_count >= 4,
        'Sizes / formats'   => trim((string) get_post_meta($post_id, '_pbi_sizes', true)) !== '',
        'Paper / material'  => trim((string) get_post_meta($post_id, '_pbi_paper', true)) !== '',
        'Finishes'          => trim((string) get_post_meta($post_id, '_pbi_finish', true)) !== '',
        'SEO title'         => strlen(trim((string) get_post_meta($post_id, '_pbi_seo_title', true))) >= 35,
        'Meta description'  => strlen(trim((string) get_post_meta($post_id, '_pbi_meta_description', true))) >= 90,
    ];
    $complete = count(array_filter($checks));
    $score = (int) round(($complete / max(1, count($checks))) * 100);
    $label = $score >= 88 ? 'Ready' : ($score >= 65 ? 'Good' : 'Needs attention');
    return ['score'=>$score,'label'=>$label,'items'=>$checks,'gallery_count'=>$gallery_count];
}

function pbi_cm_status_notice(): string {
    $updated = isset($_GET['updated']) ? sanitize_key(wp_unslash($_GET['updated'])) : '';
    if (!$updated) return '';
    $messages = [
        '1'=>'Changes saved successfully.',
        'gallery-reset'=>'Default website images restored.',
        'content-reset'=>'Text, specifications and SEO restored to website defaults.',
    ];
    if (!isset($messages[$updated])) return '';
    return '<div class="pbi-cm-toast" role="status"><span class="pbi-cm-toast__icon">✓</span><div><strong>Saved</strong><span>' . esc_html($messages[$updated]) . '</span></div></div>';
}

function pbi_cm_gallery_preview(int $post_id): string {
    $ids = pbi_cm_staff_gallery_ids($post_id);
    $html = '<input type="hidden" id="pbi-cm-gallery-ids" name="gallery_ids" value="' . esc_attr(implode(',', $ids)) . '">';
    $html .= '<div class="pbi-cm-gallery-grid" id="pbi-cm-gallery-grid">';
    foreach ($ids as $i => $id) {
        $src = wp_get_attachment_image_url($id, 'medium');
        if (!$src) continue;
        $html .= '<div class="pbi-cm-gallery-item" data-id="' . esc_attr((string) $id) . '">';
        $html .= '<img src="' . esc_url($src) . '" alt="">';
        if ($i === 0) $html .= '<span class="pbi-cm-main-badge">Main</span>';
        $html .= '<button type="button" class="pbi-cm-remove-image" aria-label="Remove image">×</button>';
        $html .= '<span class="pbi-cm-drag" aria-hidden="true">⋮⋮</span>';
        $html .= '</div>';
    }
    $html .= '</div>';

    if (!$ids && function_exists('pbi_product_github_gallery_images')) {
        $defaults = pbi_product_github_gallery_images($post_id);
        if ($defaults) {
            $html .= '<div class="pbi-cm-default-gallery" id="pbi-cm-default-gallery"><div class="pbi-cm-default-gallery__head"><span>Website default gallery</span><em>' . esc_html((string) count($defaults)) . ' images currently live</em></div><div class="pbi-cm-default-gallery__grid">';
            foreach (array_slice($defaults, 0, 6) as $image) {
                $html .= '<div><img src="' . esc_url($image['thumb'] ?: $image['url']) . '" alt=""></div>';
            }
            $html .= '</div></div>';
        }
    }
    return $html;
}

function pbi_cm_sidebar(string $active = 'products'): string {
    $user = wp_get_current_user();
    $logo = pbi_logo_url('dark');
    $html = '<aside class="pbi-cm-sidebar">';
    $html .= '<a class="pbi-cm-brand" href="' . esc_url(home_url('/content-manager/')) . '"><span class="pbi-cm-brand__mark">';
    if ($logo) $html .= '<img src="' . esc_url($logo) . '" alt="Print Bureau">';
    else $html .= 'PB';
    $html .= '</span><span><strong>Print Bureau</strong><small>Content Manager</small></span></a>';
    $html .= '<nav class="pbi-cm-side-nav" aria-label="Content manager navigation">';
    $html .= '<a class="' . ($active === 'products' ? 'is-active' : '') . '" href="' . esc_url(home_url('/content-manager/')) . '"><i>▦</i><span>Products</span></a>';
    $html .= '<a href="' . esc_url(home_url('/products/')) . '" target="_blank" rel="noopener"><i>↗</i><span>View website</span></a>';
    $html .= '</nav>';
    $html .= '<div class="pbi-cm-side-help"><span>Need help?</span><p>Edit only what you need. Website defaults stay available as a safe fallback.</p></div>';
    $html .= '<div class="pbi-cm-user"><span class="pbi-cm-user__avatar">' . esc_html(strtoupper(substr($user->display_name ?: $user->user_login, 0, 1))) . '</span><span><strong>' . esc_html($user->display_name ?: $user->user_login) . '</strong><small>Content Manager</small></span><a href="' . esc_url(wp_logout_url(home_url('/content-manager/'))) . '" aria-label="Log out">↪</a></div>';
    $html .= '</aside>';
    return $html;
}

function pbi_cm_dashboard(): string {
    $products = get_posts(['post_type'=>'pbi_product','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>['menu_order'=>'ASC','title'=>'ASC']]);
    $editable = array_values(array_filter($products, static fn($p) => current_user_can('edit_post', $p->ID)));
    $custom_images = 0; $seo_ready = 0; $attention = 0;
    foreach ($editable as $product) {
        if (get_post_meta($product->ID, '_pbi_gallery_override', true) === '1') $custom_images++;
        $health = pbi_cm_product_health($product->ID);
        if ($health['score'] >= 88) $seo_ready++;
        if ($health['score'] < 65) $attention++;
    }

    ob_start();
    ?>
    <div class="pbi-cm-workspace">
      <header class="pbi-cm-workspace-header">
        <div><span class="pbi-cm-kicker">Website management</span><h1>Products</h1><p>Keep every printing page accurate, visual and search-ready.</p></div>
        <div class="pbi-cm-header-actions"><a class="pbi-cm-button pbi-cm-button--ghost" href="<?php echo esc_url(home_url('/products/')); ?>" target="_blank" rel="noopener">Preview website ↗</a></div>
      </header>
      <?php echo pbi_cm_status_notice(); ?>

      <section class="pbi-cm-stats" aria-label="Product status overview">
        <article><span class="pbi-cm-stat-icon pbi-cm-stat-icon--teal">▦</span><div><strong><?php echo esc_html((string) count($editable)); ?></strong><small>Live product pages</small></div></article>
        <article><span class="pbi-cm-stat-icon pbi-cm-stat-icon--blue">◫</span><div><strong><?php echo esc_html((string) $custom_images); ?></strong><small>Staff-managed galleries</small></div></article>
        <article><span class="pbi-cm-stat-icon pbi-cm-stat-icon--green">✓</span><div><strong><?php echo esc_html((string) $seo_ready); ?></strong><small>Pages fully ready</small></div></article>
        <article><span class="pbi-cm-stat-icon pbi-cm-stat-icon--amber">!</span><div><strong><?php echo esc_html((string) $attention); ?></strong><small>Need attention</small></div></article>
      </section>

      <div class="pbi-cm-toolbar">
        <div class="pbi-cm-search"><span>⌕</span><input type="search" id="pbi-cm-product-search" placeholder="Search products…" autocomplete="off"></div>
        <div class="pbi-cm-filter-chips"><button type="button" class="is-active" data-cm-filter="all">All</button><button type="button" data-cm-filter="ready">Ready</button><button type="button" data-cm-filter="attention">Needs attention</button></div>
      </div>

      <div class="pbi-cm-product-grid" id="pbi-cm-product-grid">
      <?php foreach ($editable as $product):
          $img = pbi_product_image_url($product->ID, 'pbi-card');
          $health = pbi_cm_product_health($product->ID);
          $gallery_override = get_post_meta($product->ID, '_pbi_gallery_override', true) === '1';
          $content_override = get_post_meta($product->ID, '_pbi_content_override', true) === '1';
          $filter_state = $health['score'] >= 88 ? 'ready' : ($health['score'] < 65 ? 'attention' : 'good');
      ?>
        <article class="pbi-cm-product-card" data-title="<?php echo esc_attr(strtolower($product->post_title)); ?>" data-state="<?php echo esc_attr($filter_state); ?>">
          <a class="pbi-cm-product-card__visual" href="<?php echo esc_url(add_query_arg('product', $product->ID, home_url('/content-manager/'))); ?>">
            <?php if ($img): ?><img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($product->post_title); ?>"><?php endif; ?>
            <span class="pbi-cm-health-ring" style="--score:<?php echo esc_attr((string) $health['score']); ?>"><b><?php echo esc_html((string) $health['score']); ?></b><small>%</small></span>
          </a>
          <div class="pbi-cm-product-card__body">
            <div class="pbi-cm-product-card__meta"><span class="pbi-cm-state pbi-cm-state--<?php echo esc_attr($filter_state); ?>"><?php echo esc_html($health['label']); ?></span><span><?php echo esc_html((string) $health['gallery_count']); ?> images</span></div>
            <h2><?php echo esc_html($product->post_title); ?></h2>
            <p><?php echo esc_html(wp_trim_words($product->post_excerpt ?: 'Product page ready to edit.', 15)); ?></p>
            <div class="pbi-cm-source-row"><span class="<?php echo $gallery_override ? 'is-custom' : ''; ?>">◫ <?php echo $gallery_override ? 'Custom images' : 'Default images'; ?></span><span class="<?php echo $content_override ? 'is-custom' : ''; ?>">✎ <?php echo $content_override ? 'Custom copy' : 'Default copy'; ?></span></div>
            <div class="pbi-cm-product-card__actions"><a class="pbi-cm-button pbi-cm-button--primary" href="<?php echo esc_url(add_query_arg('product', $product->ID, home_url('/content-manager/'))); ?>">Edit product</a><a class="pbi-cm-icon-button" href="<?php echo esc_url(get_permalink($product)); ?>" target="_blank" rel="noopener" aria-label="Open product page">↗</a></div>
          </div>
        </article>
      <?php endforeach; ?>
      </div>
      <div class="pbi-cm-empty-state" id="pbi-cm-empty-state" hidden><span>⌕</span><h3>No products found</h3><p>Try a different search or filter.</p></div>
    </div>
    <?php
    return ob_get_clean();
}

function pbi_cm_editor(int $post_id): string {
    $product = get_post($post_id);
    if (!$product || $product->post_type !== 'pbi_product' || !current_user_can('edit_post', $post_id)) {
        return '<div class="pbi-cm-workspace"><div class="pbi-cm-empty-state"><h2>Product unavailable</h2></div></div>';
    }

    $health = pbi_cm_product_health($post_id);
    $seo_title = (string) get_post_meta($post_id, '_pbi_seo_title', true);
    $meta_description = (string) get_post_meta($post_id, '_pbi_meta_description', true);
    $gallery_override = get_post_meta($post_id, '_pbi_gallery_override', true) === '1';
    $content_override = get_post_meta($post_id, '_pbi_content_override', true) === '1';
    $main_image = pbi_product_image_url($post_id, 'pbi-card');
    $last_time = (int) get_post_meta($post_id, '_pbi_cm_last_edit_time', true);
    $last_editor_id = (int) get_post_meta($post_id, '_pbi_cm_last_editor', true);
    $last_editor = $last_editor_id ? get_userdata($last_editor_id) : null;

    ob_start();
    ?>
    <div class="pbi-cm-workspace pbi-cm-workspace--editor">
      <header class="pbi-cm-editor-top">
        <div class="pbi-cm-editor-title">
          <a class="pbi-cm-back" href="<?php echo esc_url(home_url('/content-manager/')); ?>">← Products</a>
          <div class="pbi-cm-editor-title-row">
            <?php if ($main_image): ?><span class="pbi-cm-editor-thumb"><img src="<?php echo esc_url($main_image); ?>" alt=""></span><?php endif; ?>
            <div><span class="pbi-cm-kicker">Editing product page</span><h1><?php echo esc_html($product->post_title); ?></h1><div class="pbi-cm-title-meta"><span class="pbi-cm-state pbi-cm-state--<?php echo $health['score'] >= 88 ? 'ready' : ($health['score'] < 65 ? 'attention' : 'good'); ?>"><?php echo esc_html($health['label']); ?> · <?php echo esc_html((string) $health['score']); ?>%</span><span><?php echo $content_override ? 'Custom content' : 'Website default content'; ?></span><span><?php echo $gallery_override ? 'Custom images' : 'Website default images'; ?></span></div></div>
          </div>
        </div>
        <div class="pbi-cm-header-actions"><a class="pbi-cm-button pbi-cm-button--ghost" href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank" rel="noopener">Preview ↗</a></div>
      </header>
      <?php echo pbi_cm_status_notice(); ?>

      <form class="pbi-cm-editor" id="pbi-cm-editor-form" method="post">
        <?php wp_nonce_field('pbi_cm_save_' . $post_id, 'pbi_cm_nonce'); ?>
        <input type="hidden" name="product_id" value="<?php echo esc_attr((string) $post_id); ?>">
        <input type="hidden" name="pbi_cm_action" value="save">

        <div class="pbi-cm-commandbar">
          <div class="pbi-cm-unsaved" id="pbi-cm-unsaved"><span></span><b>All changes saved</b></div>
          <div><span class="pbi-cm-shortcut">Ctrl/⌘ + S</span><button class="pbi-cm-button pbi-cm-button--primary" id="pbi-cm-save-button" type="submit">Save changes</button></div>
        </div>

        <div class="pbi-cm-editor-layout">
          <nav class="pbi-cm-editor-nav" aria-label="Product editor sections">
            <button class="is-active" type="button" data-cm-tab="content"><i>✎</i><span><strong>Content</strong><small>Title & descriptions</small></span></button>
            <button type="button" data-cm-tab="images"><i>◫</i><span><strong>Images</strong><small>Gallery & main image</small></span><em><?php echo esc_html((string) $health['gallery_count']); ?></em></button>
            <button type="button" data-cm-tab="specs"><i>≡</i><span><strong>Specifications</strong><small>Sizes, paper & finish</small></span></button>
            <button type="button" data-cm-tab="seo"><i>⌕</i><span><strong>Google / SEO</strong><small>Search appearance</small></span></button>
            <button type="button" data-cm-tab="review"><i>✓</i><span><strong>Review & publish</strong><small>Health & reset tools</small></span></button>
          </nav>

          <main class="pbi-cm-editor-panels">
            <section class="pbi-cm-editor-panel is-active" data-cm-panel="content">
              <div class="pbi-cm-panel-heading"><div><span class="pbi-cm-section-number">01</span><h2>Product content</h2><p>Write for customers first. Clear, useful copy performs better than keyword stuffing.</p></div></div>
              <div class="pbi-cm-form-card">
                <label class="pbi-cm-field"><span>Product name <em>Required</em></span><input type="text" name="product_title" value="<?php echo esc_attr($product->post_title); ?>" required data-cm-track></label>
                <label class="pbi-cm-field"><span>Short description <small data-count-for="product_excerpt"></small></span><textarea name="product_excerpt" rows="4" maxlength="280" data-cm-track data-char-counter="product_excerpt"><?php echo esc_textarea($product->post_excerpt); ?></textarea><small class="pbi-cm-field-help">This appears near the top of the product page. Keep it specific and easy to scan.</small></label>
                <div class="pbi-cm-field"><span>Full description</span><div class="pbi-cm-editor-note">Use short paragraphs, real product options and practical use cases.</div>
                  <?php wp_editor($product->post_content, 'pbi_cm_product_content', [
                      'textarea_name'=>'product_content','media_buttons'=>false,'textarea_rows'=>13,'teeny'=>false,'quicktags'=>false,
                      'tinymce'=>['toolbar1'=>'formatselect bold italic bullist numlist link unlink undo redo','toolbar2'=>''],
                  ]); ?>
                </div>
              </div>
            </section>

            <section class="pbi-cm-editor-panel" data-cm-panel="images">
              <div class="pbi-cm-panel-heading"><div><span class="pbi-cm-section-number">02</span><h2>Product gallery</h2><p>Use 4–6 strong, different views. Drag images to set the order; the first image becomes the main image.</p></div><span class="pbi-cm-source-badge <?php echo $gallery_override ? 'is-custom' : ''; ?>"><?php echo $gallery_override ? 'Staff-managed gallery' : 'Website default gallery'; ?></span></div>
              <div class="pbi-cm-form-card">
                <button type="button" class="pbi-cm-upload-zone" id="pbi-cm-add-images"><span class="pbi-cm-upload-zone__icon">＋</span><strong>Add product images</strong><small>Choose images from Media Library or upload new files</small><em>JPG · PNG · WebP</em></button>
                <?php echo pbi_cm_gallery_preview($post_id); ?>
                <div class="pbi-cm-image-guidance"><div><b>1</b><span><strong>Main image</strong><small>Clean overall product view</small></span></div><div><b>2</b><span><strong>Detail</strong><small>Paper, finish or texture</small></span></div><div><b>3</b><span><strong>Context</strong><small>Show how the product is used</small></span></div><div><b>4</b><span><strong>Variation</strong><small>Another format or angle</small></span></div></div>
              </div>
            </section>

            <section class="pbi-cm-editor-panel" data-cm-panel="specs">
              <div class="pbi-cm-panel-heading"><div><span class="pbi-cm-section-number">03</span><h2>Quote specifications</h2><p>These values populate the customer-facing quick quote controls.</p></div></div>
              <div class="pbi-cm-form-card"><div class="pbi-cm-field-grid">
                <?php foreach ([
                    'sizes'=>['Sizes / formats','_pbi_sizes','A4, A5, Custom'],
                    'paper'=>['Paper / material','_pbi_paper','130 GSM Gloss, 170 GSM Matte, Custom'],
                    'finish'=>['Finishes / options','_pbi_finish','Matte, Gloss, Foil, Custom'],
                    'turnaround'=>['Turnaround','_pbi_turnaround','3–5 business days'],
                ] as $field => [$label,$meta,$placeholder]): ?>
                  <label class="pbi-cm-field"><span><?php echo esc_html($label); ?></span><input type="text" name="<?php echo esc_attr($field); ?>" value="<?php echo esc_attr((string) get_post_meta($post_id, $meta, true)); ?>" placeholder="<?php echo esc_attr($placeholder); ?>" data-cm-track><small class="pbi-cm-field-help"><?php echo $field === 'turnaround' ? 'Write a customer-friendly time estimate.' : 'Separate options with commas.'; ?></small></label>
                <?php endforeach; ?>
              </div></div>
              <div class="pbi-cm-tip-card"><span>✦</span><div><strong>Keep quote choices practical</strong><p>Only list options you can actually produce. Fewer clear choices usually convert better than a long technical list.</p></div></div>
            </section>

            <section class="pbi-cm-editor-panel" data-cm-panel="seo">
              <div class="pbi-cm-panel-heading"><div><span class="pbi-cm-section-number">04</span><h2>Google / SEO</h2><p>Control how this page can appear in search results.</p></div></div>
              <div class="pbi-cm-seo-layout">
                <div class="pbi-cm-form-card">
                  <label class="pbi-cm-field"><span>SEO page title <small data-count-for="seo_title"></small></span><input type="text" name="seo_title" maxlength="70" value="<?php echo esc_attr($seo_title); ?>" data-cm-track data-char-counter="seo_title"></label>
                  <label class="pbi-cm-field"><span>Meta description <small data-count-for="meta_description"></small></span><textarea name="meta_description" rows="5" maxlength="170" data-cm-track data-char-counter="meta_description"><?php echo esc_textarea($meta_description); ?></textarea></label>
                  <div class="pbi-cm-keyword-tip"><strong>Recommended pattern</strong><code><?php echo esc_html($product->post_title); ?> Printing in Karnataka | Print Bureau India</code><p>Mention the product and Karnataka naturally. Chikmagalur can be included when it reads well.</p></div>
                </div>
                <aside class="pbi-cm-google-preview"><span>Google preview</span><div class="pbi-cm-google-url">printbureauindia.com › products › <?php echo esc_html((string) get_post_field('post_name', $post_id)); ?></div><h3 id="pbi-cm-seo-preview-title"><?php echo esc_html($seo_title ?: ($product->post_title . ' | Print Bureau India')); ?></h3><p id="pbi-cm-seo-preview-description"><?php echo esc_html($meta_description ?: wp_trim_words($product->post_excerpt, 26)); ?></p></aside>
              </div>
            </section>

            <section class="pbi-cm-editor-panel" data-cm-panel="review">
              <div class="pbi-cm-panel-heading"><div><span class="pbi-cm-section-number">05</span><h2>Review & publish</h2><p>Check page quality before saving your final changes.</p></div></div>
              <div class="pbi-cm-review-grid">
                <div class="pbi-cm-form-card">
                  <div class="pbi-cm-health-large"><div class="pbi-cm-health-dial" style="--score:<?php echo esc_attr((string) $health['score']); ?>"><strong><?php echo esc_html((string) $health['score']); ?></strong><span>%</span></div><div><h3><?php echo esc_html($health['label']); ?></h3><p>Page readiness based on content, images, specifications and SEO.</p></div></div>
                  <div class="pbi-cm-checklist">
                    <?php foreach ($health['items'] as $label => $done): ?><div class="<?php echo $done ? 'is-done' : ''; ?>"><i><?php echo $done ? '✓' : '·'; ?></i><span><?php echo esc_html($label); ?></span></div><?php endforeach; ?>
                  </div>
                </div>
                <div class="pbi-cm-form-card pbi-cm-publish-card">
                  <span class="pbi-cm-kicker">Publishing</span><h3>Changes go live immediately</h3><p>Save when you are ready. You can preview the public page before or after publishing.</p><button class="pbi-cm-button pbi-cm-button--primary pbi-cm-button--wide" type="submit">Save & publish changes</button><a class="pbi-cm-button pbi-cm-button--ghost pbi-cm-button--wide" href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank" rel="noopener">Open live page ↗</a>
                  <?php if ($last_time): ?><small class="pbi-cm-last-edit">Last staff edit <?php echo esc_html(human_time_diff($last_time, current_time('timestamp')) . ' ago'); ?><?php echo $last_editor ? ' by ' . esc_html($last_editor->display_name) : ''; ?></small><?php endif; ?>
                </div>
              </div>
              <div class="pbi-cm-danger-zone"><div><span>Restore defaults</span><p>Use these only if you want to discard staff-managed content or images.</p></div><div class="pbi-cm-danger-actions"></div></div>
            </section>
          </main>

          <aside class="pbi-cm-inspector">
            <div class="pbi-cm-inspector-card"><span class="pbi-cm-kicker">Page health</span><div class="pbi-cm-mini-health"><strong><?php echo esc_html((string) $health['score']); ?>%</strong><span><?php echo esc_html($health['label']); ?></span></div><div class="pbi-cm-progress"><i style="width:<?php echo esc_attr((string) $health['score']); ?>%"></i></div><p><?php echo esc_html((string) $health['gallery_count']); ?> gallery images · <?php echo $content_override ? 'Custom copy' : 'Default copy'; ?></p></div>
            <?php if ($main_image): ?><div class="pbi-cm-inspector-card pbi-cm-preview-card"><span class="pbi-cm-kicker">Current main image</span><img src="<?php echo esc_url($main_image); ?>" alt=""><a href="<?php echo esc_url(get_permalink($post_id)); ?>" target="_blank" rel="noopener">View live product ↗</a></div><?php endif; ?>
            <div class="pbi-cm-inspector-card"><span class="pbi-cm-kicker">Safe editing</span><ul><li>Staff changes survive GitHub syncs</li><li>Default images remain recoverable</li><li>No code or theme access required</li></ul></div>
          </aside>
        </div>
      </form>

      <div class="pbi-cm-reset-forms" id="pbi-cm-reset-forms">
        <form method="post" data-reset-kind="images" onsubmit="return confirm('Restore the website default images for this product?');">
          <?php wp_nonce_field('pbi_cm_save_' . $post_id, 'pbi_cm_nonce'); ?><input type="hidden" name="product_id" value="<?php echo esc_attr((string) $post_id); ?>"><input type="hidden" name="pbi_cm_action" value="reset_gallery"><button type="submit">Restore default images</button>
        </form>
        <form method="post" data-reset-kind="content" onsubmit="return confirm('Restore the website default text, specifications and SEO for this product?');">
          <?php wp_nonce_field('pbi_cm_save_' . $post_id, 'pbi_cm_nonce'); ?><input type="hidden" name="product_id" value="<?php echo esc_attr((string) $post_id); ?>"><input type="hidden" name="pbi_cm_action" value="reset_content"><button type="submit">Restore default text & SEO</button>
        </form>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

function pbi_content_manager_shortcode(): string {
    ob_start();
    echo '<section class="pbi-cm-page">';

    if (!is_user_logged_in()) {
        $logo = pbi_logo_url('dark');
        echo '<div class="pbi-cm-login-shell"><div class="pbi-cm-login-brand"><div class="pbi-cm-login-brand__inner">';
        if ($logo) echo '<img src="' . esc_url($logo) . '" alt="Print Bureau">';
        echo '<span class="pbi-cm-kicker">Private staff workspace</span><h1>Keep the website beautiful without touching code.</h1><p>Update product images, content, specifications and SEO from one simple workspace.</p><div class="pbi-cm-login-features"><span>✓ Product galleries</span><span>✓ Search content</span><span>✓ Quote options</span><span>✓ Safe publishing</span></div></div></div>';
        echo '<div class="pbi-cm-login-panel"><div class="pbi-cm-login-card"><span class="pbi-cm-kicker">Print Bureau Content Manager</span><h2>Welcome back</h2><p>Sign in with your staff account to continue.</p>';
        wp_login_form(['redirect'=>home_url('/content-manager/'),'remember'=>true,'label_username'=>'Username or email','label_password'=>'Password','label_log_in'=>'Sign in']);
        echo '<small>Restricted staff access · No code or WordPress technical settings</small></div></div></div></section>';
        return ob_get_clean();
    }

    if (!current_user_can('edit_pbi_products') && !current_user_can('manage_options')) {
        echo '<div class="pbi-cm-login-shell"><div class="pbi-cm-login-panel"><div class="pbi-cm-login-card"><h2>Access not enabled</h2><p>This account does not have Print Bureau Content Manager permission.</p></div></div></div></section>';
        return ob_get_clean();
    }

    $post_id = isset($_GET['product']) ? absint($_GET['product']) : 0;
    echo '<div class="pbi-cm-app-shell">';
    echo pbi_cm_sidebar('products');
    echo $post_id ? pbi_cm_editor($post_id) : pbi_cm_dashboard();
    echo '</div></section>';
    return ob_get_clean();
}
add_shortcode('pbi_content_manager', 'pbi_content_manager_shortcode');

function pbi_frontend_edit_product_button(): void {
    if (!is_singular('pbi_product') || !is_user_logged_in()) return;
    $post_id = get_queried_object_id();
    if (!$post_id || !current_user_can('edit_post', $post_id)) return;
    $url = add_query_arg('product', $post_id, home_url('/content-manager/'));
    echo '<a class="pbi-front-edit-button" href="' . esc_url($url) . '"><span>✎</span><strong>Edit product</strong></a>';
}
add_action('wp_footer', 'pbi_frontend_edit_product_button', 20);
