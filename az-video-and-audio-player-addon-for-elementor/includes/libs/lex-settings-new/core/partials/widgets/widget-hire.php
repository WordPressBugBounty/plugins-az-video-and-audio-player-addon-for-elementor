<?php
/**
 * Hire/Developer Services Sidebar Widget
 */
?>
<div class="lex-sidebar-widget lex-sidebar-widget--hire">
    <div class="lex-sidebar-widget__header">
        <div class="lex-sidebar-widget__badge"><?php echo esc_html__('7+ Years Experience', 'lex-settings'); ?></div>
        <h3><?php echo esc_html__('Need a WordPress Developer?', 'lex-settings'); ?></h3>
    </div>
    <div class="lex-sidebar-widget__content">
        <p><?php echo esc_html__('Hi! I\'m the developer behind this plugin.', 'lex-settings'); ?></p>
        <p><?php echo esc_html__('I help businesses, educators, and content creators with high quality WordPress development. From small fixes, feature enhancements to complete custom websites. Let\'s talk about your project goals.', 'lex-settings'); ?></p>
        
        <div class="lex-sidebar-widget__contact">
            <h4 class="lex-sidebar-widget__contact-heading"><?php echo esc_html__('Contact', 'lex-settings'); ?></h4>
            
            <div class="lex-sidebar-widget__contact-item">
                <span class="dashicons dashicons-email lex-sidebar-widget__contact-icon"></span>
                <strong><?php echo esc_html__('Email:', 'lex-settings'); ?></strong>
                <span class="lex-sidebar-widget__contact-email">helloAlberuni@gmail.com</span>
            </div>
            <?php 
            // Use widgetRenderer service if available (from WidgetRenderer::render())
            if (isset($widgetRenderer)) {
                $widgetRenderer->renderCopyButton('helloAlberuni@gmail.com', __('Copy Email', 'lex-settings'), [
                    'variant' => 'primary',
                    'icon' => 'admin-page'
                ]);
            }
            ?>
            
            <div class="lex-sidebar-widget__contact-item lex-sidebar-widget__contact-item--highlight">
                <span class="dashicons dashicons-calendar-alt lex-sidebar-widget__contact-icon"></span>
                <strong><?php echo esc_html__('Free 30 minute Discovery Call Available', 'lex-settings'); ?></strong>
            </div>
        </div>
    </div>
</div>

