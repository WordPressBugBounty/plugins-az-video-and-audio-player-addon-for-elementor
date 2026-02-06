<?php
if (!defined('ABSPATH')) {
    exit;
}

// Load defaults
$defaults = [];
$defaults_file = dirname(__DIR__) . '/default-settings.php';
if (file_exists($defaults_file)) {
    $loaded_defaults = include $defaults_file;
    if (is_array($loaded_defaults)) {
        $defaults = $loaded_defaults;
    }
}

$settings = \Lex\Settings\V2\Settings::getInstance('leanpl');

// ============================================
// Section: Player Styling
// ============================================
$settings->sectionRenderer->startSection('styling', esc_html__('Player Styling', 'vapfem'), ['disable_save_button' => true]);
$settings->fieldRenderer->render('info', 'styling_info', [
    'content' => esc_html__('Match player colors with your brand. These global styling options apply to all players site-wide, unless you override them at the widget or individual player level.', 'vapfem'),
]);

// Primary Color
$settings->fieldRenderer->render('color', 'primary_color', [
    'label' => esc_html__('Brand Color', 'vapfem'),
    'desc' => esc_html__('Main accent color for player controls, progress bars, and interactive elements to match your brand.', 'vapfem'),
    'default' => $defaults['primary_color'] ?? '#00b3ff',
    'disabled' => true,
    'pro'      => ['onclick' => 'openUpgradeModal'],
]);

$settings->sectionRenderer->endSection();

