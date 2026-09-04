<?php
/**
 * Print Bureau Premium theme bootstrap.
 */
if (!defined('ABSPATH')) { exit; }

/*
 * Hostinger/shared-hosting compatibility for the built-in GitHub updater.
 * WordPress' unzip_file() requires the filesystem API to be initialised
 * before the sync code runs. On this installation we use the direct method
 * because the theme directory is owned/writable by the PHP process.
 */
if (!defined('FS_METHOD')) {
    define('FS_METHOD', 'direct');
}
require_once ABSPATH . 'wp-admin/includes/file.php';
WP_Filesystem();

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/theme-assets.php';
require_once get_template_directory() . '/inc/content.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/leads.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/github-sync.php';
