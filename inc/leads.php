<?php
if (!defined('ABSPATH')) { exit; }

function pbi_register_leads(): void {
    register_post_type('pbi_lead', [
        'labels'=>['name'=>'Leads','singular_name'=>'Lead','menu_name'=>'Leads'],
        'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'menu_icon'=>'dashicons-phone','supports'=>['title'],'menu_position'=>23,
    ]);
}
add_action('init','pbi_register_leads');

function pbi_submit_lead(): void {
    if (!isset($_POST['pbi_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pbi_nonce'])), 'pbi_submit_lead')) {
        wp_safe_redirect(add_query_arg('lead','invalid',wp_get_referer() ?: home_url('/'))); exit;
    }
    if (!empty($_POST['website'])) { wp_safe_redirect(home_url('/')); exit; }
    $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    $key='pbi_lead_rate_'.md5($ip);
    if (get_transient($key)) { wp_safe_redirect(add_query_arg('lead','wait',wp_get_referer() ?: home_url('/'))); exit; }
    set_transient($key,1,30);

    $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    if (!$name || !$phone) { wp_safe_redirect(add_query_arg('lead','missing',wp_get_referer() ?: home_url('/'))); exit; }

    $allowed = ['lead_type','product','quantity','size','paper','finish','message','company','source','medium','campaign','content','term','referrer','landing_page','current_page','device','artwork_name'];
    $data=['name'=>$name,'phone'=>$phone,'email'=>$email];
    foreach($allowed as $field){ $data[$field]=sanitize_textarea_field(wp_unslash($_POST[$field] ?? '')); }
    $data['submitted_at']=current_time('mysql');

    $lead_id=wp_insert_post(['post_type'=>'pbi_lead','post_status'=>'publish','post_title'=>trim(($data['lead_type'] ?: 'Website lead').' — '.$name)]);
    if (!is_wp_error($lead_id)) foreach($data as $k=>$v) update_post_meta($lead_id,'_pbi_'.$k,$v);

    $admin_email=get_option('admin_email');
    wp_mail($admin_email,'New Print Bureau website lead: '.$name,"Name: {$name}\nPhone: {$phone}\nEmail: {$email}\nProduct: {$data['product']}\nMessage: {$data['message']}\nSource: {$data['source']} / {$data['medium']}\nPage: {$data['current_page']}");

    $webhook=get_theme_mod('pbi_lead_webhook');
    if ($webhook) wp_remote_post($webhook,['timeout'=>4,'headers'=>['Content-Type'=>'application/json'],'body'=>wp_json_encode($data),'data_format'=>'body']);

    $redirect=sanitize_text_field(wp_unslash($_POST['redirect_to'] ?? ''));
    $target=$redirect ? wp_validate_redirect($redirect,home_url('/')) : (wp_get_referer() ?: home_url('/'));
    wp_safe_redirect(add_query_arg('lead','success',$target)); exit;
}
add_action('admin_post_nopriv_pbi_submit_lead','pbi_submit_lead');
add_action('admin_post_pbi_submit_lead','pbi_submit_lead');

function pbi_lead_columns(array $columns): array {
    return ['cb'=>$columns['cb'],'title'=>'Lead','phone'=>'Phone','product'=>'Product','source'=>'Source','date'=>$columns['date']];
}
add_filter('manage_pbi_lead_posts_columns','pbi_lead_columns');
function pbi_lead_column(string $column,int $post_id): void {
    $map=['phone'=>'phone','product'=>'product','source'=>'source'];
    if(isset($map[$column])) echo esc_html((string)get_post_meta($post_id,'_pbi_'.$map[$column],true));
}
add_action('manage_pbi_lead_posts_custom_column','pbi_lead_column',10,2);
