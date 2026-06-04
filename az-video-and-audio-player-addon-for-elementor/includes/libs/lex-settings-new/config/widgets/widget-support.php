<?php
/**
 * Customer Support Sidebar Widget
 * 
 * Displays support information and links to help resources
 */

if(leanpl_is_pro_active()){
    $support_url = leanpl_ssot( 'brand', 'support_pro_url' );

} else {
    $support_url = leanpl_ssot( 'brand', 'support_free_url' );
}
?>
<div class="lex-sidebar-widget lex-sidebar-widget--support">
    <div class="lex-sidebar-widget__header">
        <span class="dashicons dashicons-sos"></span>
        <h3><?php echo esc_html__('Customer Support', 'lex-settings'); ?></h3>
    </div>
    <div class="lex-sidebar-widget__content">
        <p><?php echo esc_html__('Having issues? Get help by creating a support request.', 'lex-settings'); ?></p>
        <a href="<?php echo esc_url($support_url); ?>" target="_blank" class="lex-btn lex-btn--primary lex-btn--block">
            <span class="dashicons dashicons-admin-comments"></span>
            <?php echo esc_html__('Create Support Ticket', 'lex-settings'); ?>
        </a>
    </div>
</div>
