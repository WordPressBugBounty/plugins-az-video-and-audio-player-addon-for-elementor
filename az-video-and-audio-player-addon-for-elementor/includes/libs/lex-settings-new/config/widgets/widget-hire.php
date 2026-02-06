<?php
/**
 * Hire/Developer Services Sidebar Widget
 */
?>
<div class="lex-sidebar-widget lex-sidebar-widget--hire">
    <div class="lex-sidebar-widget__header">
        <h3><?php echo esc_html__('Need a WordPress Developer?', 'lex-settings'); ?></h3>
    </div>
    <div class="lex-sidebar-widget__content">
        <p><?php echo esc_html__('Hi! I\'m the developer behind this plugin.', 'lex-settings'); ?></p>
        <p><?php echo esc_html__('I help businesses, educators, and content creators with high quality WordPress development. From small fixes, feature enhancements to complete custom websites. Let\'s talk about your project goals.', 'lex-settings'); ?></p>
        
        <div class="lex-sidebar-widget__contact">            
            <div class="lex-sidebar-widget__contact-item">
                <strong><?php echo esc_html__('Email Me:', 'lex-settings'); ?></strong>
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
        </div>
    </div>
</div>

