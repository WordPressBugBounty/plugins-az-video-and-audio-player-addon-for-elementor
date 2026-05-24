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
$settings->sectionRenderer->startSection('styling', esc_html__('Player Styling', 'vapfem'));
$settings->fieldRenderer->render('info', 'styling_info', [
    'content' => esc_html__('Match player colors with your brand. These global styling options apply to all players site-wide (single players and players inside playlists), unless you override them at the widget, individual player, or playlist level.', 'vapfem'),
]);

// Player Accent Color
$settings->fieldRenderer->render('color', 'primary_color', [
    'label' => esc_html__('Player Accent Color', 'vapfem'),
    'desc' => esc_html__('Sets the accent color for controls, progress bars, and interactive elements across all video, audio, and playlist players site-wide.', 'vapfem'),
    'default' => $defaults['primary_color'] ?? '#00b3ff',
]);

$settings->sectionRenderer->endSection();

$settings->sectionRenderer->renderSubmitButtons();

