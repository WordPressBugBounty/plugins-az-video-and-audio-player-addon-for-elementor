<?php
/**
 * Customer Support Sidebar Widget
 * 
 * Displays support information and links to help resources
 */
?>
<div class="lex-sidebar-widget lex-sidebar-widget--support">
    <div class="lex-sidebar-widget__header">
        <span class="dashicons dashicons-sos"></span>
        <h3><?php echo esc_html__('Customer Support', 'lex-settings'); ?></h3>
    </div>
    <div class="lex-sidebar-widget__content">
        <p><?php echo esc_html__('Having issues with the plugin? Create a support ticket in our official forum.', 'lex-settings'); ?></p>
        <a href="#" target="_blank" class="lex-btn lex-btn--primary lex-btn--block">
            <span class="dashicons dashicons-admin-comments"></span>
            <?php echo esc_html__('Create Support Ticket', 'lex-settings'); ?>
        </a>
    </div>
</div>
