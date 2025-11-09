<?php
/**
 * Plugin Name: WPX Memberships
 * Description: New signups are set to "Pending Student" and require admin approval to become TutorLMS students. Blocks course access until approved.
 * Version:     1.0.0
 * Author:      Ahmad Bilal
 * License:     GPLv2 or later
 * Domain:      wpxseodigital.com
 */

if ( ! defined('ABSPATH') ) exit;

class WPX_Memberships {

    const PENDING_ROLE = 'tutor_pending_student';
    const STUDENT_ROLE = 'tutor_student'; // TutorLMS student role slug
    const META_STATUS  = 'tutor_approval_status'; // pending|approved

    public function __construct() {
        register_activation_hook( __FILE__, array($this, 'on_activate') );
        register_deactivation_hook( __FILE__, array($this, 'on_deactivate') );

        add_action( 'user_register', array($this, 'set_user_pending_role'), 20, 1 );
        add_action( 'admin_menu', array($this, 'register_admin_menu') );
        add_action( 'admin_init', array($this, 'handle_approve_action') );
        add_action( 'template_redirect', array($this, 'block_pending_access') );
        add_filter( 'manage_users_columns', array($this, 'add_user_col_status') );
        add_filter( 'manage_users_custom_column', array($this, 'render_user_col_status'), 10, 3 );
    }

    public function on_activate() {
        if ( ! get_role(self::PENDING_ROLE) ) {
            add_role( self::PENDING_ROLE, 'Pending Student', array( 'read' => true ) );
        }
        if ( ! get_role(self::STUDENT_ROLE) ) {
            add_role( self::STUDENT_ROLE, 'Tutor Student', array( 'read' => true ) );
        }
    }

    public function on_deactivate() {
        // Intentionally left blank to avoid removing roles used by users.
    }

    /**
     * Every new user -> Pending Student + meta status = pending
     */
    public function set_user_pending_role( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) return;

        $user->set_role( self::PENDING_ROLE );
        update_user_meta( $user_id, self::META_STATUS, 'pending' );
    }

    /**
     * Admin menu page
     */
    public function register_admin_menu() {
        add_menu_page(
            'TutorLMS Approvals',
            'Tutor Approvals',
            'list_users',
            'wpx-approvals',
            array($this, 'render_admin_page'),
            'dashicons-yes-alt',
            58
        );
    }

    /**
     * Approvals table
     */
    public function render_admin_page() {
        if ( ! current_user_can('list_users') ) {
            wp_die('You do not have permission to view this page.');
        }

        $args = array(
            'role'    => self::PENDING_ROLE,
            'orderby' => 'registered',
            'order'   => 'DESC',
            'number'  => 100,
        );
        $users = get_users( $args );
        ?>
        <div class="wrap">
            <h1>TutorLMS Pending Student Approvals</h1>
            <?php if ( isset($_GET['approved']) && $_GET['approved'] === '1' ) : ?>
                <div class="updated notice"><p>User approved successfully.</p></div>
            <?php endif; ?>
            <?php if ( isset($_GET['error']) ) : ?>
                <div class="error notice"><p><?php echo esc_html($_GET['error']); ?></p></div>
            <?php endif; ?>

            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Approve</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty($users) ) : ?>
                    <tr><td colspan="5">No pending students found.</td></tr>
                <?php else : ?>
                    <?php foreach ( $users as $u ) : ?>
                        <tr>
                            <td><?php echo esc_html( $u->display_name ); ?> (ID: <?php echo (int)$u->ID; ?>)</td>
                            <td><?php echo esc_html( $u->user_email ); ?></td>
                            <td><?php echo esc_html( $u->user_registered ); ?></td>
                            <td><?php echo esc_html( get_user_meta($u->ID, self::META_STATUS, true) ?: 'pending' ); ?></td>
                            <td>
                                <?php
                                $approve_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'page'   => 'wpx-approvals',
                                            'action' => 'approve',
                                            'uid'    => $u->ID,
                                        ),
                                        admin_url('admin.php')
                                    ),
                                    'wpx_approve_'.$u->ID
                                );
                                ?>
                                <a class="button button-primary" href="<?php echo esc_url($approve_url); ?>">Approve</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Handle approve action
     */
    public function handle_approve_action() {
        if ( ! is_admin() ) return;
        if ( ! isset($_GET['page']) || $_GET['page'] !== 'wpx-approvals' ) return;
        if ( ! isset($_GET['action']) || $_GET['action'] !== 'approve' ) return;

        if ( ! current_user_can('edit_users') ) {
            wp_redirect( add_query_arg('error', rawurlencode('Permission denied'), admin_url('admin.php?page=wpx-approvals')) );
            exit;
        }

        $uid = isset($_GET['uid']) ? absint($_GET['uid']) : 0;
        if ( ! $uid || ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'wpx_approve_'.$uid ) ) {
            wp_redirect( add_query_arg('error', rawurlencode('Invalid request'), admin_url('admin.php?page=wpx-approvals')) );
            exit;
        }

        $user = get_userdata($uid);
        if ( ! $user ) {
            wp_redirect( add_query_arg('error', rawurlencode('User not found'), admin_url('admin.php?page=wpx-approvals')) );
            exit;
        }

        // Switch role to TutorLMS student
        $user->set_role( self::STUDENT_ROLE );
        update_user_meta( $uid, self::META_STATUS, 'approved' );

        // Notify user
        $subject = 'Your TutorLMS account has been approved';
        $message = "Hi {$user->display_name},\n\nYour student account has been approved. You can now access your courses.\n\nThanks!";
        wp_mail( $user->user_email, $subject, $message );

        wp_redirect( add_query_arg('approved', '1', admin_url('admin.php?page=wpx-approvals')) );
        exit;
    }

    /**
     * Block TutorLMS access for pending students
     */
    public function block_pending_access() {
        if ( ! is_user_logged_in() ) return;

        $user = wp_get_current_user();
        if ( ! in_array( self::PENDING_ROLE, (array) $user->roles, true ) ) return;

        $blocked_types = array( 'courses', 'tutor_course', 'tutor_lesson', 'tutor_quiz', 'lesson', 'tutor_assignments' );

        if ( is_singular( $blocked_types ) || $this->is_tutor_archive() ) {
            $redirect = home_url('/');
            $redirect = apply_filters( 'wpx_pending_redirect_url', $redirect );

            if ( ! headers_sent() ) {
                wp_safe_redirect( add_query_arg( 'pending_notice', '1', $redirect ) );
                exit;
            }
        }
    }

    private function is_tutor_archive() {
        if ( function_exists('tutor') ) {
            return is_post_type_archive('courses') || is_post_type_archive('tutor_course');
        }
        return false;
    }

    public function add_user_col_status( $columns ) {
        $columns['wpx_status'] = 'Tutor Status';
        return $columns;
    }

    public function render_user_col_status( $output, $column_name, $user_id ) {
        if ( 'wpx_status' === $column_name ) {
            $val = get_user_meta( $user_id, self::META_STATUS, true );
            if ( empty($val) ) $val = '—';
            return esc_html( ucfirst($val) );
        }
        return $output;
    }
}

new WPX_Memberships();

/**
 * Optional: Front-end notice for redirected pending users
 */
add_action('wp', function () {
    if ( isset($_GET['pending_notice']) && $_GET['pending_notice'] === '1' ) {
        add_action('wp_footer', function () {
            ?>
            <div style="position:fixed;left:50%;transform:translateX(-50%);bottom:20px;background:#fffae6;border:1px solid #f0c36d;padding:12px 16px;border-radius:6px;z-index:9999;">
                Your account is pending approval. You’ll get access to courses once an admin approves your profile.
            </div>
            <?php
        });
    }
});
