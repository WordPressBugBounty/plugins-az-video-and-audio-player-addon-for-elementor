<?php
/**
 * Customer Support Sidebar Widget v2
 *
 * Personal variation with direct email contact + support ticket button.
 */

$support_url = function_exists('leanpl_is_pro_active') && leanpl_is_pro_active()
    ? leanpl_ssot( 'brand', 'support_pro_url' )
    : leanpl_ssot( 'brand', 'support_free_url' );
?>
<div class="lex-sidebar-widget lex-sidebar-widget--support">
    <div class="lex-sidebar-widget__header">
        <h3><?php echo esc_html__('Need Help?', 'lex-settings'); ?></h3>
    </div>
    <div class="lex-sidebar-widget__content">
        <p><?php echo esc_html__('Hi! I\'m the developer behind this plugin.', 'lex-settings'); ?></p>
        <p><?php echo esc_html__('Having an issue or a question? Feel free to reach out directly or create a support ticket.', 'lex-settings'); ?></p>

        <div class="lex-sidebar-widget__contact">
            <div class="lex-sidebar-widget__contact-item">
                <span class="dashicons dashicons-email lex-sidebar-widget__contact-icon"></span>
                <strong><?php echo esc_html__('Email:', 'lex-settings'); ?></strong>
                <span class="lex-sidebar-widget__contact-email">helloAlberuni@gmail.com</span>
            </div>
            <?php
            if (isset($widgetRenderer)) {
                $widgetRenderer->renderCopyButton('helloAlberuni@gmail.com', __('Copy Email', 'lex-settings'), [
                    'variant' => 'primary',
                    'icon'    => 'admin-page',
                ]);
            }
            ?>
        </div>

        <a href="<?php echo esc_url($support_url); ?>" target="_blank" class="lex-btn lex-btn--primary lex-btn--block" style="margin-top:12px;">
            <span class="dashicons dashicons-admin-comments"></span>
            <?php echo esc_html__('Create Support Ticket', 'lex-settings'); ?>
        </a>
    </div>
</div>
