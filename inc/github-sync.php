<?php
/**
 * GitHub sync for Print Bureau Premium.
 *
 * Keeps the installed theme code aligned with the public main branch of
 * saga0003/Print-Bureau-India without requiring Hostinger Git deployment.
 */
if (!defined('ABSPATH')) { exit; }

const PBI_GITHUB_REPO   = 'saga0003/Print-Bureau-India';
const PBI_GITHUB_BRANCH = 'main';
const PBI_SYNC_OPTION   = 'pbi_github_sync_state';
const PBI_SYNC_CRON     = 'pbi_github_sync_cron';

function pbi_github_sync_defaults() {
    return array(
        'auto'         => true,
        'last_sha'     => '',
        'last_checked' => 0,
        'last_synced'  => 0,
        'last_error'   => '',
        'backup_path'  => '',
    );
}

function pbi_github_sync_state() {
    $saved = get_option(PBI_SYNC_OPTION, array());
    return wp_parse_args(is_array($saved) ? $saved : array(), pbi_github_sync_defaults());
}

function pbi_github_sync_save($patch) {
    $state = pbi_github_sync_state();
    $state = array_merge($state, $patch);
    update_option(PBI_SYNC_OPTION, $state, false);
    return $state;
}

add_filter('cron_schedules', function($schedules) {
    if (!isset($schedules['pbi_ten_minutes'])) {
        $schedules['pbi_ten_minutes'] = array(
            'interval' => 10 * MINUTE_IN_SECONDS,
            'display'  => __('Every 10 minutes (Print Bureau GitHub sync)', 'print-bureau-premium'),
        );
    }
    return $schedules;
});

function pbi_github_sync_schedule() {
    if (!wp_next_scheduled(PBI_SYNC_CRON)) {
        wp_schedule_event(time() + 90, 'pbi_ten_minutes', PBI_SYNC_CRON);
    }
}
add_action('after_switch_theme', 'pbi_github_sync_schedule');
add_action('init', 'pbi_github_sync_schedule');

add_action(PBI_SYNC_CRON, function() {
    $state = pbi_github_sync_state();
    if (!empty($state['auto'])) {
        pbi_github_sync_run(false);
    }
});

function pbi_github_latest_sha() {
    $url = 'https://api.github.com/repos/' . PBI_GITHUB_REPO . '/commits/' . rawurlencode(PBI_GITHUB_BRANCH);
    $response = wp_remote_get($url, array(
        'timeout' => 12,
        'headers' => array(
            'Accept'     => 'application/vnd.github+json',
            'User-Agent' => 'Print-Bureau-India-WordPress-Sync',
        ),
    ));

    if (is_wp_error($response)) return $response;

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        return new WP_Error('pbi_github_http', sprintf('GitHub returned HTTP %d while checking for updates.', $code));
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($body) || empty($body['sha'])) {
        return new WP_Error('pbi_github_response', 'GitHub did not return a valid commit SHA.');
    }

    return sanitize_text_field($body['sha']);
}

function pbi_github_sync_find_source($temp_dir) {
    $candidates = glob(trailingslashit($temp_dir) . '*', GLOB_ONLYDIR);
    if (!$candidates) {
        return new WP_Error('pbi_sync_extract', 'The downloaded archive did not contain a repository folder.');
    }

    foreach ($candidates as $candidate) {
        if (is_file(trailingslashit($candidate) . 'style.css') && is_file(trailingslashit($candidate) . 'functions.php')) {
            $style = file_get_contents(trailingslashit($candidate) . 'style.css');
            if ($style !== false && strpos($style, 'Theme Name: Print Bureau Premium') !== false) {
                return $candidate;
            }
        }
    }

    return new WP_Error('pbi_sync_validate', 'The GitHub archive did not contain a valid Print Bureau Premium theme.');
}

function pbi_github_sync_run($force = false) {
    if (get_transient('pbi_github_sync_lock')) {
        return new WP_Error('pbi_sync_locked', 'A Print Bureau sync is already running.');
    }
    set_transient('pbi_github_sync_lock', 1, 3 * MINUTE_IN_SECONDS);

    $state = pbi_github_sync_state();
    $sha = pbi_github_latest_sha();
    $now = time();

    if (is_wp_error($sha)) {
        pbi_github_sync_save(array('last_checked' => $now, 'last_error' => $sha->get_error_message()));
        delete_transient('pbi_github_sync_lock');
        return $sha;
    }

    pbi_github_sync_save(array('last_checked' => $now));

    if (!$force && !empty($state['last_sha']) && hash_equals((string) $state['last_sha'], (string) $sha)) {
        pbi_github_sync_save(array('last_error' => ''));
        delete_transient('pbi_github_sync_lock');
        return array('status' => 'current', 'sha' => $sha);
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

    $archive_url = 'https://github.com/' . PBI_GITHUB_REPO . '/archive/refs/heads/' . PBI_GITHUB_BRANCH . '.zip';
    $zip = download_url($archive_url, 30);
    if (is_wp_error($zip)) {
        pbi_github_sync_save(array('last_error' => $zip->get_error_message()));
        delete_transient('pbi_github_sync_lock');
        return $zip;
    }

    $temp_dir = trailingslashit(get_temp_dir()) . 'pbi-sync-' . wp_generate_uuid4();
    if (!wp_mkdir_p($temp_dir)) {
        @unlink($zip);
        $error = new WP_Error('pbi_sync_temp', 'WordPress could not create a temporary update folder.');
        pbi_github_sync_save(array('last_error' => $error->get_error_message()));
        delete_transient('pbi_github_sync_lock');
        return $error;
    }

    $unzipped = unzip_file($zip, $temp_dir);
    @unlink($zip);
    if (is_wp_error($unzipped)) {
        pbi_github_sync_save(array('last_error' => $unzipped->get_error_message()));
        delete_transient('pbi_github_sync_lock');
        return $unzipped;
    }

    $source = pbi_github_sync_find_source($temp_dir);
    if (is_wp_error($source)) {
        pbi_github_sync_save(array('last_error' => $source->get_error_message()));
        delete_transient('pbi_github_sync_lock');
        return $source;
    }

    WP_Filesystem();
    global $wp_filesystem;
    if (!$wp_filesystem) {
        $error = new WP_Error('pbi_sync_filesystem', 'WordPress could not initialise its filesystem API.');
        pbi_github_sync_save(array('last_error' => $error->get_error_message()));
        delete_transient('pbi_github_sync_lock');
        return $error;
    }

    $destination = get_stylesheet_directory();
    $backup = trailingslashit(WP_CONTENT_DIR) . 'upgrade/pbi-premium-last-backup';

    if ($wp_filesystem->exists($backup)) {
        $wp_filesystem->delete($backup, true);
    }
    wp_mkdir_p($backup);
    $backup_result = copy_dir($destination, $backup);
    if (is_wp_error($backup_result)) {
        $wp_filesystem->delete($temp_dir, true);
        $error = new WP_Error('pbi_sync_backup', 'Update cancelled because WordPress could not create a safety backup: ' . $backup_result->get_error_message());
        pbi_github_sync_save(array('last_error' => $error->get_error_message()));
        delete_transient('pbi_github_sync_lock');
        return $error;
    }

    $copy_result = copy_dir($source, $destination);
    if (is_wp_error($copy_result)) {
        copy_dir($backup, $destination);
        $wp_filesystem->delete($temp_dir, true);
        $error = new WP_Error('pbi_sync_copy', 'GitHub sync failed and the previous theme files were restored: ' . $copy_result->get_error_message());
        pbi_github_sync_save(array('last_error' => $error->get_error_message()));
        delete_transient('pbi_github_sync_lock');
        return $error;
    }

    /*
     * GitHub stores internal SEO notes and planning under /research, but those
     * files must never be deployed into the public WordPress theme directory.
     */
    $research_dir = trailingslashit($destination) . 'research';
    if ($wp_filesystem->exists($research_dir)) {
        $wp_filesystem->delete($research_dir, true);
    }

    $wp_filesystem->delete($temp_dir, true);
    wp_clean_themes_cache(true);
    pbi_github_sync_save(array(
        'last_sha'    => $sha,
        'last_synced' => time(),
        'last_error'  => '',
        'backup_path' => $backup,
    ));

    delete_transient('pbi_github_sync_lock');
    return array('status' => 'updated', 'sha' => $sha);
}

add_action('admin_menu', function() {
    add_theme_page(
        __('Print Bureau GitHub Sync', 'print-bureau-premium'),
        __('GitHub Sync', 'print-bureau-premium'),
        'update_themes',
        'pbi-github-sync',
        'pbi_github_sync_admin_page'
    );
});

function pbi_github_sync_time($timestamp) {
    if (empty($timestamp)) { return 'Never'; }
    return wp_date(get_option('date_format') . ' ' . get_option('time_format'), (int) $timestamp);
}

function pbi_github_sync_admin_page() {
    if (!current_user_can('update_themes')) { return; }
    $state = pbi_github_sync_state();
    $notice = isset($_GET['pbi_sync']) ? sanitize_key(wp_unslash($_GET['pbi_sync'])) : '';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Print Bureau GitHub Sync', 'print-bureau-premium'); ?></h1>
        <p>Updates pushed to <strong><?php echo esc_html(PBI_GITHUB_REPO); ?></strong> on <strong><?php echo esc_html(PBI_GITHUB_BRANCH); ?></strong> can be pulled into WordPress automatically. Internal files under <code>/research</code> stay in GitHub and are removed from the public WordPress theme after every sync.</p>

        <?php if ($notice === 'updated') : ?>
            <div class="notice notice-success is-dismissible"><p>Print Bureau was synced successfully from GitHub.</p></div>
        <?php elseif ($notice === 'current') : ?>
            <div class="notice notice-info is-dismissible"><p>WordPress is already on the latest GitHub version.</p></div>
        <?php elseif ($notice === 'error') : ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html($state['last_error'] ?: 'The GitHub sync failed.'); ?></p></div>
        <?php endif; ?>

        <table class="widefat striped" style="max-width:900px;margin:20px 0">
            <tbody>
                <tr><td style="width:220px"><strong>Repository</strong></td><td><?php echo esc_html(PBI_GITHUB_REPO); ?></td></tr>
                <tr><td><strong>Branch</strong></td><td><?php echo esc_html(PBI_GITHUB_BRANCH); ?></td></tr>
                <tr><td><strong>Automatic sync</strong></td><td><?php echo !empty($state['auto']) ? '<span style="color:#0a7d44;font-weight:700">ON</span>' : '<span style="color:#9a6700;font-weight:700">OFF</span>'; ?></td></tr>
                <tr><td><strong>Installed GitHub commit</strong></td><td><code><?php echo esc_html($state['last_sha'] ? substr($state['last_sha'], 0, 12) : 'Not recorded yet'); ?></code></td></tr>
                <tr><td><strong>Last checked</strong></td><td><?php echo esc_html(pbi_github_sync_time($state['last_checked'])); ?></td></tr>
                <tr><td><strong>Last successful sync</strong></td><td><?php echo esc_html(pbi_github_sync_time($state['last_synced'])); ?></td></tr>
                <tr><td><strong>Safety backup</strong></td><td><?php echo esc_html($state['backup_path'] ?: 'Created before the first update'); ?></td></tr>
            </tbody>
        </table>

        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('pbi_github_sync_now'); ?>
                <input type="hidden" name="action" value="pbi_github_sync_now">
                <?php submit_button(__('Sync from GitHub Now', 'print-bureau-premium'), 'primary', 'submit', false); ?>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('pbi_github_sync_toggle'); ?>
                <input type="hidden" name="action" value="pbi_github_sync_toggle">
                <input type="hidden" name="enable" value="<?php echo !empty($state['auto']) ? '0' : '1'; ?>">
                <?php submit_button(!empty($state['auto']) ? __('Turn Automatic Sync Off', 'print-bureau-premium') : __('Turn Automatic Sync On', 'print-bureau-premium'), 'secondary', 'submit', false); ?>
            </form>
        </div>

        <p style="max-width:900px;margin-top:18px;color:#646970"><strong>How it works:</strong> WordPress checks GitHub approximately every 10 minutes using WP-Cron. The “Sync from GitHub Now” button is immediate. Theme/code/image updates are backed up first; managed website content is applied separately and customer leads, users and uploaded artwork are not deleted.</p>
    </div>
    <?php
}

add_action('admin_post_pbi_github_sync_now', function() {
    if (!current_user_can('update_themes')) { wp_die('You do not have permission to update themes.'); }
    check_admin_referer('pbi_github_sync_now');
    $result = pbi_github_sync_run(true);
    $status = is_wp_error($result) ? 'error' : (($result['status'] ?? '') === 'current' ? 'current' : 'updated');
    wp_safe_redirect(add_query_arg('pbi_sync', $status, admin_url('themes.php?page=pbi-github-sync')));
    exit;
});

add_action('admin_post_pbi_github_sync_toggle', function() {
    if (!current_user_can('update_themes')) { wp_die('You do not have permission to update themes.'); }
    check_admin_referer('pbi_github_sync_toggle');
    $enable = !empty($_POST['enable']);
    pbi_github_sync_save(array('auto' => $enable));
    wp_safe_redirect(admin_url('themes.php?page=pbi-github-sync'));
    exit;
});
