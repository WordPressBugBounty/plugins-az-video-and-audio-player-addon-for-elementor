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
// Section 1: Shared Options (Video & Audio)
// ============================================
$settings->sectionRenderer->startSection('shared', esc_html__('Shared Options (Video & Audio)', 'vapfem'));
$settings->fieldRenderer->render('info', 'shared_info', [
    'content' => esc_html__('Set default behavior that applies to all video and audio players across your site, unless you override them at the widget or individual player level.', 'vapfem'),
]);

// Auto Play
$settings->fieldRenderer->render('checkbox', 'autoplay', [
    'label' => esc_html__('Autoplay', 'vapfem'),
    'desc' => __('Try to start videos and audio automatically when the page loads. <strong>If autoplay does not work, turn on "Muted" below.</strong> Most browsers only allow autoplay when sound is off. <a href="https://developer.mozilla.org/en-US/docs/Web/Media/Guides/Autoplay" target="_blank" rel="noopener noreferrer">Learn more about autoplay policies</a>.', 'vapfem'),
    'checkbox_label' => esc_html__('Yes - Start playing automatically when the page loads', 'vapfem'),
    'default' => $defaults['autoplay'] ?? null,
]);

// Muted
$settings->fieldRenderer->render('checkbox', 'muted', [
    'label' => esc_html__('Start Muted', 'vapfem'),
    'desc' => __('Start all players with sound off. Required for autoplay in modern browsers.', 'vapfem'),
    'checkbox_label' => esc_html__('Yes - Start all players with sound off (muted)', 'vapfem'),
    'default' => $defaults['muted'] ?? null,
]);

// Initial Volume
$settings->fieldRenderer->render('number', 'volume', [
    'label' => esc_html__('Initial Volume', 'vapfem'),
    'desc' => esc_html__('Choose how loud the player starts, from 0% (mute) to 100% (full volume). Viewers can still change the volume while watching.', 'vapfem'),
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'unit' => '%',
    'default' => isset($defaults['volume']) ? ($defaults['volume'] * 100) : 100,
]);

// Loop
$settings->fieldRenderer->render('checkbox', 'loop', [
    'label' => esc_html__('Loop', 'vapfem'),
    'tooltip' => esc_html__('Automatically restart from the beginning when finished', 'vapfem'),
    'tooltip_width' => 'compact',
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc' => esc_html__('Play again automatically when finished', 'vapfem'),
    'default' => $defaults['loop'] ?? null,
]);

// Playback Speed
$settings->fieldRenderer->render('select', 'speed_selected', [
    'label' => esc_html__('Default Playback Speed', 'vapfem'),
    'desc' => __('Set the initial playback speed when players load. Many users now prefer listening to podcasts and videos at faster speeds (1.2x or 1.5x). We recommend setting 1.25x as the global default—this will apply to all video and audio players site-wide, unless you override it at the widget or individual player level.', 'vapfem'),
    'options' => [
        '0.5' => esc_html__('0.5x (Slow)', 'vapfem'),
        '0.75' => esc_html__('0.75x', 'vapfem'),
        '1' => esc_html__('1x (Normal)', 'vapfem'),
        '1.25' => esc_html__('1.25x', 'vapfem'),
        '1.5' => esc_html__('1.5x', 'vapfem'),
        '1.75' => esc_html__('1.75x', 'vapfem'),
        '2' => esc_html__('2x (Fast)', 'vapfem'),
        '4' => esc_html__('4x (Very Fast)', 'vapfem'),
    ],
    'default' => $defaults['speed_selected'] ?? null,
]);

// Controls
$settings->fieldRenderer->render('checkbox', 'controls', [
    'label' => esc_html__('Player Controls', 'vapfem'),
    'desc' => __('Choose which buttons appear on the player (play, progress bar, volume, etc.). Check a control to show it, uncheck to hide it, and drag to change the order they appear in the control bar.', 'vapfem'),
    'is_multiple' => true,
    'is_sortable' => true,
    'max_height' => '300px',
    'options' => [
        'play-large' => esc_html__('Play Large (Video Only)', 'vapfem'),
        'restart' => esc_html__('Restart', 'vapfem'),
        'rewind' => esc_html__('Rewind', 'vapfem'),
        'play' => esc_html__('Play', 'vapfem'),
        'fast-forward' => esc_html__('Fast Forward', 'vapfem'),
        'progress' => esc_html__('Progress Bar', 'vapfem'),
        'current-time' => esc_html__('Current Time', 'vapfem'),
        'duration' => esc_html__('Duration', 'vapfem'),
        'mute' => esc_html__('Mute', 'vapfem'),
        'volume' => esc_html__('Volume', 'vapfem'),
        'captions' => esc_html__('Caption (Video Only)', 'vapfem'),
        'settings' => esc_html__('Settings Icon', 'vapfem'),
        'pip' => esc_html__('PIP (Video Only)', 'vapfem'),
        'airplay' => esc_html__('Airplay', 'vapfem'),
        'fullscreen' => esc_html__('Fullscreen (Video Only)', 'vapfem'),
        'download' => esc_html__('Download', 'vapfem'),
    ],
    'default' => $defaults['controls'] ?? null,
    'disabled' => true,
    'pro'      => [ 'onclick' => 'openUpgradeModal'],
]);

// Time Display Format
$settings->fieldRenderer->render('select', 'invert_time', [
    'label' => esc_html__('Time Display Format', 'vapfem'),
    'desc' => __('Choose what the time display shows:<br>• <strong>Remaining Time (Countdown):</strong> counts down how much time is left (for example, "-2:30")<br>• <strong>Elapsed Time:</strong> counts up how much has already played (for example, "2:30").<br>Default: Remaining time.', 'vapfem'),
    'options' => [
        '1' => esc_html__('Remaining Time (Countdown)', 'vapfem'),
        '0' => esc_html__('Elapsed Time (Incremental)', 'vapfem'),
    ],
    'default' => $defaults['invert_time'] ?? '1',
    'disabled' => true,
    'pro' => ['onclick' => 'openUpgradeModal'],
]);

// Seek Time
$settings->fieldRenderer->render('number', 'seek_time', [
    'label' => esc_html__('Skip Amount (Forward / Back)', 'vapfem'),
    'desc' => esc_html__('How many seconds to skip forward or backward when viewers use keyboard shortcuts (arrow keys) for fast forward or rewind. For example, 10 seconds means each press of the arrow key jumps 10 seconds. Note: Clicking on the progress bar will still jump directly to that position.', 'vapfem'),
    'min' => 1,
    'max' => 60,
    'step' => 1,
    'unit' => 'Seconds',
    'default' => $defaults['seek_time'] ?? null,
    'disabled' => true,
    'pro' => ['onclick' => 'openUpgradeModal'],
]);

$settings->sectionRenderer->endSection();

// ============================================
// Section 2: Video-Only Options
// ============================================
$settings->sectionRenderer->startSection('video_only', esc_html__('Video-Only Options', 'vapfem'));
$settings->fieldRenderer->render('info', 'video_info', [
    'content' => esc_html__('Set default behavior that applies to all video players across your site, unless you override them at the widget or individual player level.', 'vapfem'),
]);

// Fullscreen enabled
$settings->fieldRenderer->render('checkbox', 'fullscreen_enabled', [
    'label' => esc_html__('Fullscreen Button', 'vapfem'),
    'tooltip' => esc_html__('Show a button to expand video to fullscreen', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc' => esc_html__('Show a fullscreen button on the player', 'vapfem'),
    'default' => $defaults['fullscreen_enabled'] ?? null,
]);

// Click to play
$settings->fieldRenderer->render('checkbox', 'click_to_play', [
    'label' => esc_html__('Click to Play / Pause', 'vapfem'),
    'tooltip' => esc_html__('Allow clicking directly on video to play or pause', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc' => esc_html__('Allow clicking on the video to play or pause', 'vapfem'),
    'default' => $defaults['click_to_play'] ?? null,
]);

// Auto Hide Control
$settings->fieldRenderer->render('checkbox', 'hide_controls', [
    'label' => esc_html__('Auto-Hide Controls', 'vapfem'),
    'tooltip' => esc_html__('Hide controls during playback; show on hover or tap', 'vapfem'),
    'tooltip_width' => 'compact',
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc' => esc_html__('Hide controls while playing, show on hover or tap', 'vapfem'),
    'default' => $defaults['hide_controls'] ?? null,
    'disabled' => true,
    'pro' => ['onclick' => 'openUpgradeModal'],
]);

// Reset on End
$settings->fieldRenderer->render('checkbox', 'reset_on_end', [
    'label' => esc_html__('Reset to Start When Finished', 'vapfem'),
    'tooltip' => esc_html__('Reset video to beginning when playback ends', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc' => __('Reset video to the beginning after it finishes', 'vapfem'),
    'default' => $defaults['reset_on_end'] ?? null,
]);

$settings->sectionRenderer->endSection();

// Render Save & Reset buttons
$settings->sectionRenderer->renderSubmitButtons();
