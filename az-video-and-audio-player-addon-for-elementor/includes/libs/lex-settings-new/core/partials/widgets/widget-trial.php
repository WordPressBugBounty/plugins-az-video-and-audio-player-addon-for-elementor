<?php
// Available variants: v1 (default)
$variant = $config['variant'] ?? 'v1';
$allowed_variants = ['v1'];

if (!in_array($variant, $allowed_variants, true)) {
    $variant = 'v1';
}

$widget_classes = 'lex-sidebar-widget--trial lex-sidebar-widget--trial-v1';
?>
<div class="lex-sidebar-widget <?php echo $widget_classes; ?>">
    <div class="lex-sidebar-widget__header">
        <span class="dashicons dashicons-yes-alt"></span>
        <h3><?php echo esc_html__('Try Pro for Free', 'lex-settings'); ?></h3>
    </div>
    <div class="lex-sidebar-widget__content">
        <p><strong><?php echo esc_html__('Test drive all premium features', 'lex-settings'); ?></strong> <?php echo esc_html__('with a 14-day free trial.', 'lex-settings'); ?></p>
        <ul class="lex-feature-list">
            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html__('Full access to all Pro features', 'lex-settings'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html__('No credit card required', 'lex-settings'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html__('No commitment, cancel anytime', 'lex-settings'); ?></li>
            <li><span class="dashicons dashicons-yes"></span> <?php echo esc_html__('Upgrade anytime during trial', 'lex-settings'); ?></li>
        </ul>
        <a href="#" class="lex-btn lex-btn--primary lex-btn--hero lex-btn--cta">
            <?php echo esc_html__('Start Free Trial →', 'lex-settings'); ?>
        </a>
        <p class="lex-sidebar-widget__footer-text"><?php echo esc_html__('See Pro in action first.', 'lex-settings'); ?><br><strong><?php echo esc_html__('Buy only when you\'re convinced.', 'lex-settings'); ?></strong></p>
    </div>
</div>
