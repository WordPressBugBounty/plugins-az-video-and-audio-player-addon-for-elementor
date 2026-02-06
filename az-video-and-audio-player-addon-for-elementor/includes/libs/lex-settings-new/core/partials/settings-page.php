<?php
/**
 * Settings Page Template
 * 
 * Main wrapper template for the settings page.
 * 
 * @var \Lex\Settings\V2\Settings $settings
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include header template
include $settings->getConfig('framework_path') . '/partials/header.php';
?>

<div class="lex-layout-container">
    <div class="lex-main-content">
        <form method="post" action="#" class="lex-settings-form">
            <?php 
            $nonce_action = $settings->getConfig('nonce_action');
            wp_nonce_field($nonce_action, $nonce_action . '_field'); 
            ?>

            <?php
            // Get registered tabs
            $tabs = $settings->getTabs();
            
            if (!empty($tabs)) {
                // Loop through registered tabs and render each
                $first_tab = true;
                $config_path = $settings->getConfig('config_path');
                
                foreach ($tabs as $tab_id => $tab_config) {
                    $active_class = $first_tab ? ' lex-settings-tabs__content--active' : '';
                    
                    // Priority 1: User's custom file
                    $user_file = $config_path . '/tabs/' . $tab_id . '.php';
                    
                    // Priority 2: Demo file
                    $demo_file = $config_path . '/examples/tabs/' . $tab_id . '.php';
                    ?>
                    
                    <div class="lex-settings-tabs__content<?php echo esc_attr($active_class); ?>" id="lex-tab-<?php echo esc_attr($tab_id); ?>">
                        <?php
                        if (file_exists($user_file)) {
                            // User's custom file (takes priority)
                            include $user_file;
                        } elseif (file_exists($demo_file)) {
                            // Demo file (fallback)
                            include $demo_file;
                        } else {
                            // No content found - show message
                            ?>
                            <div class="lex-tab-empty">
                                <p class="description">
                                    <?php 
                                    printf(
                                        esc_html__('Tab contents are not set yet. Create %s to add settings for this tab.', 'lex-settings'),
                                        '<code>config/tabs/' . esc_html($tab_id) . '.php</code>'
                                    ); 
                                    ?>
                                </p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    
                    <?php
                    $first_tab = false;
                }
                
                // Output registered inline styles after all tab content files have been included
                $inline_styles = $settings->assetManager->getInlineStyles();
                
                if (!empty(trim($inline_styles))) {
                    echo '<style id="lex-settings-dynamic-styles">' . "\n";
                    echo trim($inline_styles) . "\n";
                    echo '</style>' . "\n";
                    
                    // Clear registered styles after outputting them
                    $settings->assetManager->clearInlineStyles();
                }
            } else {
                // No tabs registered - show message
                ?>
                <div class="notice notice-info">
                    <p>
                        <?php 
                        echo esc_html__('No tabs registered yet. Use ', 'lex-settings');
                        echo '<code>$settings->registerTab()</code>';
                        echo esc_html__(' to register tabs.', 'lex-settings');
                        ?>
                    </p>
                </div>
                <?php
            }
            ?>

        </form>
    </div>

    <!-- Sidebar -->
    <aside class="lex-sidebar">
        <?php
        // Load widget configuration from config folder
        $config_path = $settings->getConfig('config_path');
        $widgets_config_file = $config_path . '/widgets.php';
        
        if (file_exists($widgets_config_file)) {
            $widgets_config = include $widgets_config_file;
            
            // Render widgets in the order defined in config
            foreach ($widgets_config as $widget_id => $widget_data) {
                if (isset($widget_data['enabled']) && $widget_data['enabled'] === false) {
                    continue;
                }
                $settings->widgetRenderer->render($widget_id, $widget_data);
            }
        } else {
            // Fallback: show instruction
            ?>
            <div class="lex-sidebar-widget">
                <div class="lex-sidebar-widget__header">
                    <span class="dashicons dashicons-info"></span>
                    <h3><?php echo esc_html__('Widget Configuration', 'lex-settings'); ?></h3>
                </div>
                <div class="lex-sidebar-widget__content">
                    <p class="description">
                        <?php 
                        printf(
                            esc_html__('Create %s to configure sidebar widgets.', 'lex-settings'),
                            '<code>config/widgets.php</code>'
                        ); 
                        ?>
                    </p>
                </div>
            </div>
            <?php
        }
        ?>
    </aside>
</div>
