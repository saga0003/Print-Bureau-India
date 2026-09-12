<?php
if (!defined('ABSPATH')) { exit; }

/** Admin-only helper for creating restricted front-end staff accounts. */
function pbi_add_content_manager_user_page(): void {
    add_users_page(
        'Print Bureau Staff Login',
        'Print Bureau Staff Login',
        'create_users',
        'pbi-content-manager-user',
        'pbi_render_content_manager_user_page'
    );
}
add_action('admin_menu', 'pbi_add_content_manager_user_page');

function pbi_render_content_manager_user_page(): void {
    if (!current_user_can('create_users')) wp_die('Access denied.');

    $created = isset($_GET['created']) ? absint($_GET['created']) : 0;
    $suggested_password = wp_generate_password(18, true, true);
    ?>
    <div class="wrap" style="max-width:760px">
        <h1>Print Bureau Staff Login</h1>
        <p>Create a restricted account for a non-technical staff member. This user can update Print Bureau product pages and upload product images from the front-end Content Manager, but cannot change themes, plugins, GitHub Sync or site settings.</p>

        <?php if ($created): $user = get_userdata($created); ?>
            <div class="notice notice-success"><p><strong>Staff login created successfully.</strong><?php if ($user): ?> Username: <code><?php echo esc_html($user->user_login); ?></code>. Share the username and the password you entered with the staff member securely.<?php endif; ?></p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:24px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:24px">
            <?php wp_nonce_field('pbi_create_content_manager_user', 'pbi_user_nonce'); ?>
            <input type="hidden" name="action" value="pbi_create_content_manager_user">
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="pbi_staff_name">Staff name</label></th>
                    <td><input class="regular-text" id="pbi_staff_name" name="staff_name" type="text" required placeholder="e.g. Arun"></td>
                </tr>
                <tr>
                    <th><label for="pbi_staff_username">Login ID / Username</label></th>
                    <td><input class="regular-text" id="pbi_staff_username" name="staff_username" type="text" required autocomplete="off" placeholder="e.g. printbureau.editor"><p class="description">Use letters, numbers, dots, hyphens or underscores. Do not use “admin”.</p></td>
                </tr>
                <tr>
                    <th><label for="pbi_staff_email">Email</label></th>
                    <td><input class="regular-text" id="pbi_staff_email" name="staff_email" type="email" required autocomplete="off"></td>
                </tr>
                <tr>
                    <th><label for="pbi_staff_password">Password</label></th>
                    <td><input class="regular-text" id="pbi_staff_password" name="staff_password" type="text" minlength="12" required autocomplete="new-password" value="<?php echo esc_attr($suggested_password); ?>"><p class="description">Copy this password before creating the user, or replace it with your own strong password (minimum 12 characters).</p></td>
                </tr>
            </table>
            <?php submit_button('Create Content Manager Login'); ?>
        </form>

        <p style="margin-top:18px"><strong>Staff login page:</strong> <a href="<?php echo esc_url(home_url('/content-manager/')); ?>" target="_blank" rel="noopener"><?php echo esc_html(home_url('/content-manager/')); ?></a></p>
    </div>
    <?php
}

function pbi_create_content_manager_user(): void {
    if (!current_user_can('create_users')) wp_die('Access denied.', 'Access denied', ['response' => 403]);
    check_admin_referer('pbi_create_content_manager_user', 'pbi_user_nonce');

    $name = isset($_POST['staff_name']) ? sanitize_text_field(wp_unslash($_POST['staff_name'])) : '';
    $username = isset($_POST['staff_username']) ? sanitize_user(wp_unslash($_POST['staff_username']), true) : '';
    $email = isset($_POST['staff_email']) ? sanitize_email(wp_unslash($_POST['staff_email'])) : '';
    $password = isset($_POST['staff_password']) ? (string) wp_unslash($_POST['staff_password']) : '';

    if (!$username || strtolower($username) === 'admin' || !validate_username($username)) {
        wp_die('Please enter a valid username. Do not use “admin”.', 'Invalid username', ['response' => 400]);
    }
    if (username_exists($username)) wp_die('That username already exists.', 'Username unavailable', ['response' => 400]);
    if (!$email || !is_email($email)) wp_die('Please enter a valid email address.', 'Invalid email', ['response' => 400]);
    if (email_exists($email)) wp_die('That email address is already connected to a WordPress account.', 'Email already used', ['response' => 400]);
    if (strlen($password) < 12) wp_die('The password must contain at least 12 characters.', 'Password too short', ['response' => 400]);

    $user_id = wp_insert_user([
        'user_login'   => $username,
        'user_pass'    => $password,
        'user_email'   => $email,
        'display_name' => $name ?: $username,
        'role'         => 'pbi_content_manager',
    ]);

    if (is_wp_error($user_id)) wp_die(esc_html($user_id->get_error_message()), 'Unable to create user', ['response' => 400]);

    wp_safe_redirect(add_query_arg(['page' => 'pbi-content-manager-user', 'created' => (int) $user_id], admin_url('users.php')));
    exit;
}
add_action('admin_post_pbi_create_content_manager_user', 'pbi_create_content_manager_user');

/** Keep restricted staff in the front-end workspace rather than wp-admin. */
function pbi_is_content_manager_user(?WP_User $user = null): bool {
    $user = $user ?: wp_get_current_user();
    return $user && in_array('pbi_content_manager', (array) $user->roles, true);
}

function pbi_content_manager_login_redirect(string $redirect_to, string $requested_redirect_to, $user): string {
    if ($user instanceof WP_User && pbi_is_content_manager_user($user)) {
        return home_url('/content-manager/');
    }
    return $redirect_to;
}
add_filter('login_redirect', 'pbi_content_manager_login_redirect', 20, 3);

function pbi_content_manager_block_admin(): void {
    if (!is_user_logged_in() || !pbi_is_content_manager_user()) return;
    if (wp_doing_ajax()) return;
    wp_safe_redirect(home_url('/content-manager/'));
    exit;
}
add_action('admin_init', 'pbi_content_manager_block_admin', 20);

function pbi_hide_admin_bar_for_content_manager(bool $show): bool {
    return pbi_is_content_manager_user() ? false : $show;
}
add_filter('show_admin_bar', 'pbi_hide_admin_bar_for_content_manager');
