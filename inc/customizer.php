<?php
if (!defined('ABSPATH')) { exit; }

function pbi_customize_register(WP_Customize_Manager $wp_customize): void {
    $wp_customize->add_section('pbi_brand', ['title' => 'Print Bureau — Brand & Contact', 'priority' => 30]);
    $wp_customize->add_setting('pbi_default_theme', ['default'=>'dark','sanitize_callback'=>fn($v)=>in_array($v,['dark','light'],true)?$v:'dark']);
    $wp_customize->add_control('pbi_default_theme', ['label'=>'Default colour mode','section'=>'pbi_brand','type'=>'select','choices'=>['dark'=>'Dark','light'=>'Light']]);

    $wp_customize->add_setting('pbi_prefer_github_assets', [
        'default' => true,
        'sanitize_callback' => static fn($value) => (bool) $value,
    ]);
    $wp_customize->add_control('pbi_prefer_github_assets', [
        'label' => 'Prefer GitHub-managed website images',
        'description' => 'When enabled, files placed in assets/images/ in GitHub automatically override matching homepage/product images after sync. WordPress Media Library remains the fallback.',
        'section' => 'pbi_brand',
        'type' => 'checkbox',
    ]);

    foreach ([
        'pbi_logo_dark' => 'Logo shown on DARK background (use white-text logo)',
        'pbi_logo_light' => 'Logo shown on LIGHT background (use dark-text logo)',
        'pbi_hero_image' => 'Homepage hero image (WordPress fallback)',
    ] as $id=>$label) {
        $wp_customize->add_setting($id, ['sanitize_callback'=>'esc_url_raw']);
        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, $id, ['label'=>$label,'section'=>'pbi_brand']));
    }

    $settings = [
        'phone'=>['Phone','+91 98765 43210','sanitize_text_field'],
        'whatsapp'=>['WhatsApp number (digits incl. country code)','919876543210','sanitize_text_field'],
        'email'=>['Email','hello@printbureauindia.com','sanitize_email'],
        'address'=>['Address','Chikmagalur, Karnataka, India','sanitize_text_field'],
        'hours'=>['Business hours','Mon–Sat, 9:30 AM – 7:00 PM','sanitize_text_field'],
        'lead_webhook'=>['Optional CRM / Odoo webhook URL','','esc_url_raw'],
        'hero_title'=>['Homepage headline','Print that Creates Impact.','sanitize_text_field'],
        'hero_subtitle'=>['Homepage subline','Premium printing. Thoughtful details. Lasting impressions.','sanitize_text_field'],
    ];
    foreach ($settings as $key=>$cfg) {
        $id='pbi_'.$key;
        $wp_customize->add_setting($id,['default'=>$cfg[1],'sanitize_callback'=>$cfg[2]]);
        $wp_customize->add_control($id,['label'=>$cfg[0],'section'=>'pbi_brand','type'=>'text']);
    }
}
add_action('customize_register','pbi_customize_register');
