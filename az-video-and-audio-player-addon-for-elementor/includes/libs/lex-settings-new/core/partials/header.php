<?php
/**
 * Settings Page Header
 * 
 * Auto-generates navigation from registered tabs, or shows message if none registered.
 * 
 * @var \Lex\Settings\V2\Settings $settings
 */

// Ensure $settings is available (passed from settings-page.php)
if (!isset($settings)) {
    return;
}

// Get registered tabs (if any)
$tabs = $settings->getTabs();

if (!empty($tabs)) {
    // Separate main tabs and dropdown tabs
    $main_tabs = [];
    $dropdown_tabs = [];
    
    foreach ($tabs as $tab_id => $tab_config) {
        if (!empty($tab_config['dropdown'])) {
            $dropdown_tabs[$tab_id] = $tab_config;
        } else {
            $main_tabs[$tab_id] = $tab_config;
        }
    }
    
    // Get plugin info from instance
    $plugin_name = $settings->getConfig('page_title') ?: __('Settings', 'lex-settings');
    $plugin_version = $settings->getConfig('version') ?: '1.0.0';
    $logo = $settings->getConfig('logo');
    
    // Render logo: SVG or dashicon
    if (!empty($logo) && strpos($logo, '<svg') !== false) {
        // SVG logo - use as-is
        $logo_html = $logo;
    } else {
        // Dashicon - ensure base class exists
        $dashicon_class = !empty($logo) ? $logo : 'dashicons-admin-settings';
        if (strpos($dashicon_class, 'dashicons') === false) {
            $dashicon_class = 'dashicons ' . $dashicon_class;
        }
        $logo_html = '<span class="' . esc_attr($dashicon_class) . '"></span>';
    }
    ?>
    
    <div class="lex-nav-header">
        <div class="lex-nav-header__container">
            <div class="lex-nav-header__brand">
                <div class="lex-nav-header__logo">
                    <?php echo $logo_html; ?>
                </div>
                <span class="lex-nav-header__name"><?php echo esc_html($plugin_name); ?></span>
                <span class="lex-nav-header__version"><?php echo esc_html($plugin_version); ?></span>
            </div>
            
            <nav class="lex-nav-header__nav">
                <?php
                $first_tab = true;
                foreach ($main_tabs as $tab_id => $tab_config) {
                    $active_class = $first_tab ? ' lex-nav-header__item--active' : '';
                    $icon_html = !empty($tab_config['icon']) 
                        ? '<span class="dashicons ' . esc_attr($tab_config['icon']) . '"></span> ' 
                        : '';
                    ?>
                    <a href="#lex-tab-<?php echo esc_attr($tab_id); ?>" 
                       class="lex-nav-header__item<?php echo $active_class; ?>" 
                       data-tab="<?php echo esc_attr($tab_id); ?>">
                        <?php echo $icon_html . esc_html($tab_config['label']); ?>
                    </a>
                    <?php
                    $first_tab = false;
                }
                
                // Render dropdown if needed
                if (!empty($dropdown_tabs)) {
                    // Get dropdown label from config, default to "More"
                    $dropdown_label = $settings->getConfig('dropdown_label') ?: __('More', 'lex-settings');
                    ?>
                    <div class="lex-nav-header__dropdown">
                        <span class="lex-nav-header__item lex-nav-header__dropdown-toggle">
                            <?php echo esc_html($dropdown_label); ?>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </span>
                        <div class="lex-nav-header__dropdown-menu">
                            <?php foreach ($dropdown_tabs as $tab_id => $tab_config) { 
                                $icon_html = !empty($tab_config['icon']) 
                                    ? '<span class="dashicons ' . esc_attr($tab_config['icon']) . '" style="font-size: 16px; width: 16px; height: 16px; margin-right: 6px;"></span>' 
                                    : '';
                                ?>
                                <a href="#lex-tab-<?php echo esc_attr($tab_id); ?>" 
                                   class="lex-nav-header__dropdown-item" 
                                   data-tab="<?php echo esc_attr($tab_id); ?>">
                                    <?php echo $icon_html . esc_html($tab_config['label']); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </nav>
        </div>
    </div>
    
    <?php
} else {
    // No tabs registered - show message
    $plugin_name = $settings->getConfig('page_title') ?: __('Settings', 'lex-settings');
    $logo = $settings->getConfig('logo');
    
    // Render logo: SVG or dashicon
    if (!empty($logo) && strpos($logo, '<svg') !== false) {
        // SVG logo - use as-is
        $logo_html = $logo;
    } else {
        // Dashicon - ensure base class exists
        $dashicon_class = !empty($logo) ? $logo : 'dashicons-admin-settings';
        if (strpos($dashicon_class, 'dashicons') === false) {
            $dashicon_class = 'dashicons ' . $dashicon_class;
        }
        $logo_html = '<span class="' . esc_attr($dashicon_class) . '"></span>';
    }
    ?>
    <div class="lex-nav-header">
        <div class="lex-nav-header__container">
            <div class="lex-nav-header__brand">
                <div class="lex-nav-header__logo">
                    <?php echo $logo_html; ?>
                </div>
                <span class="lex-nav-header__name"><?php echo esc_html($plugin_name); ?></span>
            </div>
        </div>
    </div>
    <div class="notice notice-info">
        <p>
            <?php 
            echo esc_html__('No tabs registered yet. Use ', 'lex-settings');
            echo '<code>$settings->register_tab()</code>';
            echo esc_html__(' to register tabs.', 'lex-settings');
            ?>
        </p>
    </div>
    <?php
}
?>
