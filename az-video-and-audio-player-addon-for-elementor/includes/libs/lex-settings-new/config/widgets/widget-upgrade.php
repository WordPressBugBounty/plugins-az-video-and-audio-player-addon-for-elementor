<?php
// Available variants: gradient (default), solid, outline, subtle
$variant = $config['variant'] ?? 'gradient';
$variant_class_map = [
    'gradient' => 'lex-sidebar-widget--pro',
    'solid'    => 'lex-sidebar-widget--pro-solid',
    'outline'  => 'lex-sidebar-widget--pro-outline',
    'subtle'   => 'lex-sidebar-widget--pro-subtle',
];

if (!isset($variant_class_map[$variant])) {
    $variant = 'gradient';
}

$widget_class = $variant_class_map[$variant];
?>
<div class="lex-sidebar-widget <?php echo $widget_class; ?>">
    <div class="lex-sidebar-widget__header">
        <span class="dashicons dashicons-star-filled"></span>
        <h3><?php echo esc_html__('Upgrade to Pro', 'lex-settings'); ?></h3>
    </div>
    <div class="lex-sidebar-widget__content">
        <p><?php echo esc_html__('Unlock powerful features to supercharge your bookmarks:', 'lex-settings'); ?></p>
        <ul class="lex-feature-list">
            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html__('Custom button colors', 'lex-settings'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html__('Grid & compact views', 'lex-settings'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html__('Bookmark categories', 'lex-settings'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html__('Priority support', 'lex-settings'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html__('Lifetime updates', 'lex-settings'); ?></li>
        </ul>
        <a href="#" class="lex-btn lex-btn--primary lex-btn--hero lex-btn--cta">
            <?php echo esc_html__('Get Pro Now →', 'lex-settings'); ?>
        </a>
        <p class="lex-sidebar-widget__footer-text"><?php echo esc_html__('30-day money-back guarantee.', 'lex-settings'); ?><br><strong><?php echo esc_html__('No questions asked.', 'lex-settings'); ?></strong></p>
    </div>
</div>
