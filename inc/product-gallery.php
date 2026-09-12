<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Product gallery management.
 *
 * Default gallery sources:
 * 1. Product-specific GitHub image bundle under assets/images/products/{slug}/
 * 2. WordPress Media Library gallery
 * 3. Product featured/GitHub primary image as fallback
 *
 * When _pbi_gallery_override=1, the staff-managed WordPress gallery replaces
 * the GitHub bundle completely. This is what makes front-end image editing
 * genuinely useful instead of mixing old/default images with new ones.
 */

function pbi_product_gallery_meta_box(): void {
    add_meta_box(
        'pbi_product_gallery',
        'Product Gallery',
        'pbi_product_gallery_meta_box_html',
        'pbi_product',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'pbi_product_gallery_meta_box');

function pbi_product_gallery_ids(int $post_id): array {
    $raw = (string) get_post_meta($post_id, '_pbi_gallery_ids', true);
    if ($raw === '') return [];
    return array_values(array_filter(array_map('absint', preg_split('/\s*,\s*/', $raw))));
}

function pbi_product_gallery_meta_box_html(WP_Post $post): void {
    wp_nonce_field('pbi_save_product_gallery', 'pbi_product_gallery_nonce');
    $ids = pbi_product_gallery_ids($post->ID);
    $override = get_post_meta($post->ID, '_pbi_gallery_override', true) === '1';

    echo '<p>Select genuine photos/mockups for this product. These images power thumbnails, next/previous controls, zoom and the full-screen viewer.</p>';
    if ($override) echo '<p><strong>Staff gallery override is active:</strong> these WordPress images replace the GitHub/default image bundle.</p>';
    echo '<input type="hidden" id="pbi_gallery_ids" name="pbi_gallery_ids" value="' . esc_attr(implode(',', $ids)) . '">';
    echo '<div id="pbi-gallery-preview" style="display:flex;gap:10px;flex-wrap:wrap;margin:12px 0">';

    foreach ($ids as $id) {
        $thumb = wp_get_attachment_image_url($id, 'thumbnail');
        if (!$thumb) continue;
        echo '<div class="pbi-admin-gallery-item" data-id="' . esc_attr((string) $id) . '" style="position:relative;width:96px;height:96px;border:1px solid #ccd0d4;border-radius:7px;overflow:hidden;background:#fff">';
        echo '<img src="' . esc_url($thumb) . '" alt="" style="width:100%;height:100%;object-fit:cover">';
        echo '<button type="button" class="button-link pbi-remove-gallery-image" aria-label="Remove image" style="position:absolute;right:4px;top:4px;width:24px;height:24px;border-radius:50%;background:#fff;color:#b32d2e;font-size:18px;line-height:22px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.2)">×</button>';
        echo '</div>';
    }

    echo '</div>';
    echo '<p><button type="button" class="button button-primary" id="pbi-add-gallery-images">Add / reorder gallery images</button> ';
    echo '<button type="button" class="button" id="pbi-clear-gallery-images">Clear gallery</button></p>';
    echo '<p class="description">Tip: upload 4–6 distinct views per product. The first selected image becomes the main image. Front-end staff can manage the same gallery at <code>/content-manager/</code>.</p>';
}

function pbi_save_product_gallery(int $post_id): void {
    if (!isset($_POST['pbi_product_gallery_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pbi_product_gallery_nonce'])), 'pbi_save_product_gallery')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $raw = isset($_POST['pbi_gallery_ids']) ? sanitize_text_field(wp_unslash($_POST['pbi_gallery_ids'])) : '';
    $ids = array_values(array_filter(array_map('absint', preg_split('/\s*,\s*/', $raw))));
    update_post_meta($post_id, '_pbi_gallery_ids', implode(',', $ids));
    if ($ids) {
        update_post_meta($post_id, '_pbi_gallery_override', '1');
        set_post_thumbnail($post_id, $ids[0]);
    } else {
        delete_post_meta($post_id, '_pbi_gallery_override');
    }
}
add_action('save_post_pbi_product', 'pbi_save_product_gallery');

function pbi_product_gallery_admin_assets(string $hook): void {
    if (!in_array($hook, ['post.php','post-new.php'], true)) return;
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'pbi_product') return;

    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');

    $js = <<<'JS'
jQuery(function($){
  const $field = $('#pbi_gallery_ids');
  const $preview = $('#pbi-gallery-preview');

  const ids = () => $preview.find('.pbi-admin-gallery-item').map(function(){ return $(this).data('id'); }).get();
  const sync = () => $field.val(ids().join(','));

  $preview.sortable({ items: '.pbi-admin-gallery-item', update: sync });

  $('#pbi-add-gallery-images').on('click', function(e){
    e.preventDefault();
    const frame = wp.media({
      title: 'Choose product gallery images',
      button: { text: 'Use selected images' },
      multiple: true,
      library: { type: 'image' }
    });

    frame.on('select', function(){
      const selected = frame.state().get('selection');
      selected.each(function(model){
        const a = model.toJSON();
        if ($preview.find('[data-id="'+a.id+'"]').length) return;
        const src = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
        $preview.append(
          '<div class="pbi-admin-gallery-item" data-id="'+a.id+'" style="position:relative;width:96px;height:96px;border:1px solid #ccd0d4;border-radius:7px;overflow:hidden;background:#fff">' +
            '<img src="'+src+'" alt="" style="width:100%;height:100%;object-fit:cover">' +
            '<button type="button" class="button-link pbi-remove-gallery-image" aria-label="Remove image" style="position:absolute;right:4px;top:4px;width:24px;height:24px;border-radius:50%;background:#fff;color:#b32d2e;font-size:18px;line-height:22px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.2)">×</button>' +
          '</div>'
        );
      });
      sync();
    });
    frame.open();
  });

  $preview.on('click', '.pbi-remove-gallery-image', function(e){
    e.preventDefault();
    $(this).closest('.pbi-admin-gallery-item').remove();
    sync();
  });

  $('#pbi-clear-gallery-images').on('click', function(e){
    e.preventDefault();
    $preview.empty();
    sync();
  });
});
JS;
    wp_add_inline_script('jquery-ui-sortable', $js);
}
add_action('admin_enqueue_scripts', 'pbi_product_gallery_admin_assets');

function pbi_product_github_gallery_images(int $post_id): array {
    if (!pbi_prefer_github_assets()) return [];

    $slug = sanitize_file_name((string) get_post_field('post_name', $post_id));
    if ($slug === '') return [];

    $dir = trailingslashit(get_template_directory()) . 'assets/images/products/' . $slug;
    if (!is_dir($dir)) return [];

    $files = [];
    foreach (['webp','avif','jpg','jpeg','png'] as $ext) {
        $found = glob(trailingslashit($dir) . '*.' . $ext);
        if ($found) $files = array_merge($files, $found);
    }
    if (!$files) return [];

    natsort($files);
    $base_url = trailingslashit(get_template_directory_uri()) . 'assets/images/products/' . rawurlencode($slug) . '/';
    $title = get_the_title($post_id);
    $images = [];

    foreach ($files as $path) {
        $filename = basename($path);
        $label = pathinfo($filename, PATHINFO_FILENAME);
        $label = trim(preg_replace('/^[0-9]+[-_\s]*/', '', $label));
        $label = trim(str_replace(['-','_'], ' ', $label));
        $alt = trim($title . ' printing' . ($label ? ' - ' . $label : '') . ' | Print Bureau India');
        $url = $base_url . rawurlencode($filename);
        $images[] = ['url' => $url, 'thumb' => $url, 'alt' => $alt, 'source' => 'github'];
    }

    return $images;
}

function pbi_product_media_gallery_images(int $post_id): array {
    $images = [];
    foreach (pbi_product_gallery_ids($post_id) as $id) {
        $full = wp_get_attachment_image_url($id, 'full');
        if (!$full) continue;
        $thumb = wp_get_attachment_image_url($id, 'medium') ?: $full;
        $alt = trim((string) get_post_meta($id, '_wp_attachment_image_alt', true));
        if ($alt === '') $alt = get_the_title($post_id) . ' printing | Print Bureau India';
        $images[] = ['url' => $full, 'thumb' => $thumb, 'alt' => $alt, 'source' => 'media'];
    }
    return $images;
}

function pbi_product_gallery_images(int $post_id): array {
    $override = get_post_meta($post_id, '_pbi_gallery_override', true) === '1';

    if ($override) {
        $images = pbi_product_media_gallery_images($post_id);
        if (!$images) {
            $featured = get_the_post_thumbnail_url($post_id, 'full');
            if ($featured) {
                $images[] = [
                    'url' => $featured,
                    'thumb' => $featured,
                    'alt' => get_the_title($post_id) . ' printing | Print Bureau India',
                    'source' => 'featured',
                ];
            }
        }
    } else {
        $images = array_merge(
            pbi_product_github_gallery_images($post_id),
            pbi_product_media_gallery_images($post_id)
        );

        $primary = pbi_product_image_url($post_id, 'full');
        if ($primary) {
            array_unshift($images, [
                'url' => $primary,
                'thumb' => $primary,
                'alt' => get_the_title($post_id) . ' printing | Print Bureau India',
                'source' => 'primary',
            ]);
        }
    }

    $seen = [];
    $unique = [];
    foreach ($images as $image) {
        $key = strtolower((string) ($image['url'] ?? ''));
        if ($key === '' || isset($seen[$key])) continue;
        $seen[$key] = true;
        $unique[] = $image;
    }

    return $unique;
}
