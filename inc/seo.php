<?php
if (!defined('ABSPATH')) { exit; }

function pbi_has_seo_plugin(): bool {
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('SEOPRESS_VERSION') || class_exists('All_in_One_SEO_Pack');
}

function pbi_meta_description(): string {
    if (is_singular()) {
        $custom = get_post_meta(get_queried_object_id(), '_pbi_meta_description', true);
        if ($custom) return wp_strip_all_tags($custom);
        $excerpt = get_the_excerpt();
        if ($excerpt) return wp_trim_words(wp_strip_all_tags($excerpt), 28, '');
    }
    if (is_post_type_archive('pbi_product')) return 'Premium printing services from Print Bureau India — brochures, business cards, packaging, stationery, books, labels, signage and institutional print.';
    return 'Print Bureau India — premium printing, thoughtful details and reliable delivery for brands, businesses and institutions.';
}

function pbi_head_meta(): void {
    if (!pbi_has_seo_plugin()) {
        printf("\n<meta name=\"description\" content=\"%s\">\n", esc_attr(pbi_meta_description()));
        printf("<meta property=\"og:title\" content=\"%s\">\n", esc_attr(wp_get_document_title()));
        printf("<meta property=\"og:description\" content=\"%s\">\n", esc_attr(pbi_meta_description()));
        printf("<meta property=\"og:url\" content=\"%s\">\n", esc_url(home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''))));
        printf("<meta property=\"og:type\" content=\"%s\">\n", is_singular('post') ? 'article' : 'website');
        if (is_singular() && has_post_thumbnail()) printf("<meta property=\"og:image\" content=\"%s\">\n", esc_url(get_the_post_thumbnail_url(null,'full')));
    }
    $org=[
        '@context'=>'https://schema.org','@type'=>'Organization','name'=>'Print Bureau India','url'=>home_url('/'),
        'logo'=>pbi_logo_url('light'),'email'=>pbi_contact('email','hello@printbureauindia.com'),
        'telephone'=>pbi_contact('phone',''),
    ];
    $website=['@context'=>'https://schema.org','@type'=>'WebSite','name'=>'Print Bureau India','url'=>home_url('/')];
    echo '<script type="application/ld+json">'.wp_json_encode($org,JSON_UNESCAPED_SLASHES).'</script>';
    echo '<script type="application/ld+json">'.wp_json_encode($website,JSON_UNESCAPED_SLASHES).'</script>';
    if (is_singular('pbi_product')) {
        $service=['@context'=>'https://schema.org','@type'=>'Service','name'=>get_the_title(),'description'=>pbi_meta_description(),'provider'=>['@type'=>'Organization','name'=>'Print Bureau India','url'=>home_url('/')],'areaServed'=>'India','url'=>get_permalink()];
        echo '<script type="application/ld+json">'.wp_json_encode($service,JSON_UNESCAPED_SLASHES).'</script>';
    }
}
add_action('wp_head','pbi_head_meta',5);

function pbi_seo_meta_boxes(): void {
    foreach(['post','page','pbi_product'] as $screen) add_meta_box('pbi_seo','Print Bureau SEO','pbi_seo_meta_box_html',$screen,'normal','low');
}
add_action('add_meta_boxes','pbi_seo_meta_boxes');
function pbi_seo_meta_box_html(WP_Post $post): void {
    wp_nonce_field('pbi_save_seo','pbi_seo_nonce');
    $d=get_post_meta($post->ID,'_pbi_meta_description',true);
    echo '<p><label><strong>Meta description</strong></label><br><textarea name="pbi_meta_description" style="width:100%" rows="3" maxlength="160">'.esc_textarea($d).'</textarea></p><p class="description">Aim for a clear 140–160 character description. If you use Rank Math/Yoast later, their settings take priority.</p>';
}
function pbi_save_seo(int $post_id): void {
    if(!isset($_POST['pbi_seo_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pbi_seo_nonce'])),'pbi_save_seo'))return;
    if(!current_user_can('edit_post',$post_id))return;
    if(isset($_POST['pbi_meta_description']))update_post_meta($post_id,'_pbi_meta_description',sanitize_textarea_field(wp_unslash($_POST['pbi_meta_description'])));
}
add_action('save_post','pbi_save_seo');
