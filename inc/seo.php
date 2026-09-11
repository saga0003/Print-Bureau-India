<?php
if (!defined('ABSPATH')) { exit; }

function pbi_has_seo_plugin(): bool {
    return defined('WPSEO_VERSION') || defined('RANK_MATH_VERSION') || defined('SEOPRESS_VERSION') || class_exists('All_in_One_SEO_Pack');
}

function pbi_is_production_domain(): bool {
    $host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    return in_array($host, ['printbureauindia.com','www.printbureauindia.com'], true);
}

/* Preview/staging copies must never compete with the canonical live domain. */
add_filter('wp_robots', function(array $robots): array {
    if (!pbi_is_production_domain()) {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
        $robots['noarchive'] = true;
    }
    return $robots;
});

function pbi_custom_seo_title(int $post_id): string {
    return trim((string) get_post_meta($post_id, '_pbi_seo_title', true));
}

function pbi_meta_description(): string {
    if (is_singular()) {
        $custom = get_post_meta(get_queried_object_id(), '_pbi_meta_description', true);
        if ($custom) return wp_strip_all_tags($custom);
        $excerpt = get_the_excerpt();
        if ($excerpt) return wp_trim_words(wp_strip_all_tags($excerpt), 28, '');
    }
    if (is_post_type_archive('pbi_product')) {
        return 'Printing services in Chikmagalur and across Karnataka for business cards, brochures, flyers, stickers, packaging, books, banners, stationery, notebooks, diaries and institutional print.';
    }
    return 'Print Bureau India — premium printing services in Chikmagalur and across Karnataka for brands, businesses, events, schools, colleges and institutions.';
}

function pbi_document_title(string $title): string {
    if (pbi_has_seo_plugin()) return $title;

    if (is_singular()) {
        $custom = pbi_custom_seo_title((int) get_queried_object_id());
        if ($custom) return $custom;
    }

    if (is_post_type_archive('pbi_product')) {
        return 'Printing Services & Products in Karnataka | Print Bureau India';
    }

    return $title;
}
add_filter('pre_get_document_title', 'pbi_document_title', 20);

function pbi_canonical_url(): string {
    if (is_singular()) return (string) get_permalink();
    if (is_post_type_archive('pbi_product')) return (string) get_post_type_archive_link('pbi_product');
    if (is_home()) return (string) get_permalink((int) get_option('page_for_posts'));
    if (is_front_page()) return (string) home_url('/');
    return '';
}

function pbi_karnataka_service_areas(): array {
    return [
        ['@type' => 'City', 'name' => 'Chikkamagaluru'],
        ['@type' => 'City', 'name' => 'Bengaluru'],
        ['@type' => 'City', 'name' => 'Mysuru'],
        ['@type' => 'City', 'name' => 'Mangaluru'],
        ['@type' => 'City', 'name' => 'Hassan'],
        ['@type' => 'City', 'name' => 'Shivamogga'],
        ['@type' => 'City', 'name' => 'Udupi'],
        ['@type' => 'City', 'name' => 'Davanagere'],
        ['@type' => 'AdministrativeArea', 'name' => 'Karnataka'],
        ['@type' => 'Country', 'name' => 'India'],
    ];
}

function pbi_product_karnataka_copy(int $post_id): string {
    $title = get_the_title($post_id);
    if (!$title) return '';

    return sprintf(
        'Print Bureau India handles %1$s requirements from Chikmagalur (Chikkamagaluru) for businesses, schools, colleges, institutions, events and organisations across Karnataka. We regularly accept enquiries from Bengaluru, Mysuru, Mangaluru, Hassan, Shivamogga, Udupi, Davanagere and other Karnataka locations. Share your quantity, size, material, finishing requirements and delivery location for a suitable print specification and quote.',
        strtolower($title)
    );
}

function pbi_product_faq_schema(int $post_id): array {
    $title = get_the_title($post_id);
    $turnaround = get_post_meta($post_id, '_pbi_turnaround', true) ?: '3–5 business days';

    return [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'What is the minimum order quantity?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Minimum quantity depends on the product, material and finishing. Share your requirement and Print Bureau India will recommend a practical run size.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Can Print Bureau India help with design?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. Print Bureau India can review supplied artwork and help prepare a print-ready design when required.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is the turnaround time for ' . $title . '?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $turnaround . ' for many standard jobs. Complex finishes and larger runs can take longer.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Do you handle ' . strtolower($title) . ' orders across Karnataka?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. Print Bureau India accepts enquiries from businesses, institutions and organisations across Karnataka. Production and dispatch are confirmed based on quantity, specifications and delivery location.',
                ],
            ],
        ],
    ];
}

function pbi_breadcrumb_schema(): array {
    $items = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => home_url('/'),
        ],
    ];

    if (is_singular('pbi_product')) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Products',
            'item' => get_post_type_archive_link('pbi_product'),
        ];
        $items[] = [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => get_the_title(),
            'item' => get_permalink(),
        ];
    }

    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

function pbi_head_meta(): void {
    if (!pbi_has_seo_plugin()) {
        printf("\n<meta name=\"description\" content=\"%s\">\n", esc_attr(pbi_meta_description()));
        printf("<meta property=\"og:title\" content=\"%s\">\n", esc_attr(wp_get_document_title()));
        printf("<meta property=\"og:description\" content=\"%s\">\n", esc_attr(pbi_meta_description()));
        printf("<meta property=\"og:url\" content=\"%s\">\n", esc_url(pbi_canonical_url() ?: home_url('/')));
        printf("<meta property=\"og:type\" content=\"%s\">\n", is_singular('post') ? 'article' : 'website');

        $og_image = '';
        if (is_singular('pbi_product') && function_exists('pbi_product_gallery_images')) {
            $gallery = pbi_product_gallery_images((int) get_queried_object_id());
            if (!empty($gallery[0]['url'])) $og_image = (string) $gallery[0]['url'];
        }
        if (!$og_image && is_singular() && has_post_thumbnail()) {
            $og_image = (string) get_the_post_thumbnail_url(null, 'full');
        }
        if ($og_image) {
            printf("<meta property=\"og:image\" content=\"%s\">\n", esc_url($og_image));
            printf("<meta name=\"twitter:card\" content=\"summary_large_image\">\n");
        }

        $canonical = pbi_canonical_url();
        if ($canonical && pbi_is_production_domain()) {
            printf("<link rel=\"canonical\" href=\"%s\">\n", esc_url($canonical));
        }
    }

    $org = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Print Bureau India',
        'url' => home_url('/'),
        'logo' => pbi_logo_url('light'),
        'email' => pbi_contact('email', 'hello@printbureauindia.com'),
        'telephone' => pbi_contact('phone', ''),
        'areaServed' => pbi_karnataka_service_areas(),
    ];

    $website = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => 'Print Bureau India',
        'url' => home_url('/'),
    ];

    echo '<script type="application/ld+json">' . wp_json_encode($org, JSON_UNESCAPED_SLASHES) . '</script>';
    echo '<script type="application/ld+json">' . wp_json_encode($website, JSON_UNESCAPED_SLASHES) . '</script>';

    if (is_singular('pbi_product')) {
        $post_id = (int) get_queried_object_id();
        $gallery = function_exists('pbi_product_gallery_images') ? pbi_product_gallery_images($post_id) : [];
        $service = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => get_the_title($post_id) . ' Printing',
            'serviceType' => get_the_title($post_id) . ' printing service',
            'description' => pbi_meta_description(),
            'provider' => [
                '@type' => 'Organization',
                'name' => 'Print Bureau India',
                'url' => home_url('/'),
            ],
            'areaServed' => pbi_karnataka_service_areas(),
            'url' => get_permalink($post_id),
        ];

        if (!empty($gallery[0]['url'])) {
            $service['image'] = array_values(array_map(
                static fn($item) => $item['url'],
                array_slice($gallery, 0, 6)
            ));
        }

        echo '<script type="application/ld+json">' . wp_json_encode($service, JSON_UNESCAPED_SLASHES) . '</script>';
        echo '<script type="application/ld+json">' . wp_json_encode(pbi_breadcrumb_schema(), JSON_UNESCAPED_SLASHES) . '</script>';
        echo '<script type="application/ld+json">' . wp_json_encode(pbi_product_faq_schema($post_id), JSON_UNESCAPED_SLASHES) . '</script>';
    }
}
add_action('wp_head', 'pbi_head_meta', 5);

function pbi_seo_meta_boxes(): void {
    foreach (['post','page','pbi_product'] as $screen) {
        add_meta_box('pbi_seo', 'Print Bureau SEO', 'pbi_seo_meta_box_html', $screen, 'normal', 'low');
    }
}
add_action('add_meta_boxes', 'pbi_seo_meta_boxes');

function pbi_seo_meta_box_html(WP_Post $post): void {
    wp_nonce_field('pbi_save_seo', 'pbi_seo_nonce');
    $t = get_post_meta($post->ID, '_pbi_seo_title', true);
    $d = get_post_meta($post->ID, '_pbi_meta_description', true);

    echo '<p><label><strong>SEO title</strong></label><br><input type="text" name="pbi_seo_title" style="width:100%" maxlength="70" value="' . esc_attr($t) . '" placeholder="Page keyword | Print Bureau India"></p>';
    echo '<p><label><strong>Meta description</strong></label><br><textarea name="pbi_meta_description" style="width:100%" rows="3" maxlength="160">' . esc_textarea($d) . '</textarea></p>';
    echo '<p class="description">These theme fields are used when a dedicated SEO plugin is not active. If Rank Math/Yoast/SEOPress is installed later, the plugin takes priority.</p>';
}

function pbi_save_seo(int $post_id): void {
    if (!isset($_POST['pbi_seo_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pbi_seo_nonce'])), 'pbi_save_seo')) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['pbi_seo_title'])) {
        update_post_meta($post_id, '_pbi_seo_title', sanitize_text_field(wp_unslash($_POST['pbi_seo_title'])));
    }
    if (isset($_POST['pbi_meta_description'])) {
        update_post_meta($post_id, '_pbi_meta_description', sanitize_textarea_field(wp_unslash($_POST['pbi_meta_description'])));
    }
}
add_action('save_post', 'pbi_save_seo');
