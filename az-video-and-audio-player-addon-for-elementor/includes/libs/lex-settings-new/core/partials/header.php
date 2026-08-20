<?php
/**
 * Settings Page Header
 *
 * Markup-only. All data prep (tab splitting, brand info, logo markup) lives
 * in Services\Menu::getNavHeaderArgs() and arrives here as extracted $args.
 *
 * @var array  $main_tabs      Tabs without 'dropdown' set.
 * @var array  $dropdown_tabs  Tabs with 'dropdown' set.
 * @var string $plugin_name    Page title.
 * @var string $plugin_version Framework/plugin version.
 * @var string $logo_html      Pre-built logo markup (SVG or dashicon span).
 * @var string $dropdown_label Label for the dropdown toggle.
 */

if (!isset($main_tabs) || !isset($dropdown_tabs)) {
    return;
}

if (!empty($main_tabs) || !empty($dropdown_tabs)) {
    ?>

    <div class="lex-nav-header">
        <div class="lex-nav-header__container">
            <div class="lex-nav-header__brand">
                <div class="lex-nav-header__logo">
                    <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-built HTML from Menu::getNavHeaderArgs(), same value trusted before the refactor. ?>
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
    ?>
    <div class="lex-nav-header">
        <div class="lex-nav-header__container">
            <div class="lex-nav-header__brand">
                <div class="lex-nav-header__logo">
                    <?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-built HTML from Menu::getNavHeaderArgs(), same value trusted before the refactor. ?>
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
