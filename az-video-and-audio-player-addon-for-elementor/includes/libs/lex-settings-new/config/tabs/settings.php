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
$sr = $settings->sectionRenderer;

// ============================================
// VTAB: Player Defaults (parent submenu)
// ============================================
$sr->startVtab('player-defaults', esc_html__('Player Defaults', 'vapfem'), [
    'icon'     => leanpl_ssot( 'icons', 'source_svg' ),
    'expanded' => true,
]);
$sr->endVtab();

// ============================================
// VTAB: Layout & Branding
// ============================================
$sr->startVtab('styling', esc_html__('Layout & Branding', 'vapfem'), [
    'icon'   => leanpl_ssot( 'icons', 'appearance_svg' ),
    'parent' => 'player-defaults',
]);

$sr->startSection('styling', esc_html__('Player Appearance', 'vapfem'));
$settings->fieldRenderer->render('info', 'styling_info', [
    'content' => esc_html__('Match player colors with your brand. These global styling options apply to all players site-wide (single players and players inside playlists), unless you override them at the widget, individual player, or playlist level.', 'vapfem'),
]);

// Player Accent Color
$settings->fieldRenderer->render('color', 'primary_color', [
    'label' => esc_html__('Player Accent Color', 'vapfem'),
    'desc' => esc_html__('Sets the accent color for controls, progress bars, and interactive elements across all video, audio, and playlist players site-wide.', 'vapfem'),
    'default' => $defaults['primary_color'] ?? '#00b3ff',
]);

// Player Layout (control-bar layout)
$settings->fieldRenderer->render('image-select', 'player_layout', [
    'label' => esc_html__('Player Layout', 'vapfem'),
    'desc' => esc_html__('Sets the default control-bar layout for all video and audio players site-wide, unless overridden on an individual player. Classic (stock Plyr) is used automatically if nothing is selected here.', 'vapfem'),
    'default' => $defaults['player_layout'] ?? '',
    'columns' => 3,
    'options' => array_merge(
        array_map(
            function ( $value, $layout ) {
                return [ 'value' => $value, 'label' => $layout['label'], 'image' => $layout['image'] ];
            },
            array_keys( leanpl_get_player_layouts() ),
            leanpl_get_player_layouts()
        ),
        [
            [
                'value' => '__custom_preset__',
                'label' => esc_html__('Custom Preset', 'vapfem'),
                'launcher' => 'LeanPLCustomPreset.open',
            ],
        ]
    ),
]);

$sr->endSection();
$sr->endVtab();

// ============================================
// VTAB: Behavior
// ============================================
$sr->startVtab('behavior', esc_html__('Behavior', 'vapfem'), [
    'icon'   => leanpl_ssot( 'icons', 'behavior_svg' ),
    'parent' => 'player-defaults',
]);

$sr->startSection('auto_start', esc_html__('Auto-Start', 'vapfem'), [
    'accordion' => true,
    'exclusive' => 'player-settings',
    'meta'      => esc_html__('- Applies to all players', 'vapfem'),
]);
$settings->fieldRenderer->render('info', 'shared_info', [
    'content' => __('Set once, applies to every player on your site. Override individually when you need a one-off change.<br><strong>Note:</strong> Autoplay and Loop are ignored for playlist players. Playlists handle track flow themselves.', 'vapfem'),
]);

// Auto Play
$settings->fieldRenderer->render('checkbox', 'autoplay', [
    'label'          => esc_html__('Autoplay', 'vapfem'),
    'tooltip'        => __('<strong>If autoplay does not work, enable Start Muted.</strong><br>Most browsers block autoplay with sound on.', 'vapfem'),
    'tooltip_width'  => 'wide',
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc'           => __('Tries to start playing when the page loads. Most browsers block autoplay with sound on. <a href="https://developer.mozilla.org/en-US/docs/Web/Media/Guides/Autoplay" target="_blank" rel="noopener noreferrer">Learn more about autoplay policies</a>.', 'vapfem'),
    'default'        => $defaults['autoplay'] ?? null,
]);

// Muted
$settings->fieldRenderer->render('checkbox', 'muted', [
    'label'          => esc_html__('Start Muted', 'vapfem'),
    'tooltip'        => __('Starts all players with sound off.<br><br>Required for autoplay in most modern browsers.', 'vapfem'),
    'tooltip_width'  => 'wide',
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc'           => esc_html__('Start all players with sound off', 'vapfem'),
    'default'        => $defaults['muted'] ?? null,
]);

$sr->endSection();

$sr->startSection('playback', esc_html__('Playback', 'vapfem'), [
    'accordion' => true,
    'exclusive' => 'player-settings',
    'meta'      => esc_html__('- Applies to all players', 'vapfem'),
]);

// Loop
$settings->fieldRenderer->render('checkbox', 'loop', [
    'label'          => esc_html__('Loop Playback', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc'           => esc_html__('Restart automatically when playback finishes', 'vapfem'),
    'default'        => $defaults['loop'] ?? null,
]);

$sr->endSection();

$sr->startSection('behavior_tooltips', esc_html__('Tooltips', 'vapfem'), [
    'accordion' => true,
    'exclusive' => 'player-settings',
    'meta'      => esc_html__('- Applies to all players', 'vapfem'),
]);

// Control Button Tooltips
$settings->fieldRenderer->render('checkbox', 'tooltips_controls', [
    'label'          => esc_html__('Control Button Tooltips', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc'           => esc_html__('Hover over a button (play, mute, fullscreen) → shows its name as a little bubble ("Play", "Mute"). Works on video and audio.', 'vapfem'),
    'default'        => $defaults['tooltips_controls'] ?? null,
]);

$sr->endSection();
$sr->endVtab();

// ============================================
// VTAB: Playback Controls
// ============================================
$sr->startVtab('playback-controls', esc_html__('Playback Controls', 'vapfem'), [
    'icon'   => leanpl_ssot( 'icons', 'controls_svg' ),
    'parent' => 'player-defaults',
]);

$sr->startSection('advanced', esc_html__('Loading', 'vapfem'), [
    'accordion' => true,
    'exclusive' => 'player-settings',
    'meta'      => esc_html__('- Applies to all players', 'vapfem'),
]);

// Media Preload
$settings->fieldRenderer->render('select', 'preload', [
    'label'         => esc_html__('HTML5 Media Preload', 'vapfem'),
    'desc'          => esc_html__('How much media loads before the visitor presses play', 'vapfem'),
    'tooltip'       => __('<ul><li><strong>Metadata (Recommended)</strong><br>Loads only duration and basic info. Media loads on play.</li><li><strong>None</strong><br>Nothing loads until the visitor presses play. Best for pages with many players.</li><li><strong>Auto</strong><br>Browser starts loading early, before play. Use only when most visitors will play this media.</li></ul>', 'vapfem'),
    'tooltip_width' => 'xl',
    'options'       => [
        'metadata' => esc_html__('Metadata (Recommended)', 'vapfem'),
        'none'     => esc_html__('None', 'vapfem'),
        'auto'     => esc_html__('Auto', 'vapfem'),
    ],
    'default'       => $defaults['preload'] ?? 'metadata',
]);

// Autopause (Pro)
$settings->fieldRenderer->render('checkbox', 'autopause', [
    'label'          => esc_html__('Pause Other Players', 'vapfem'),
    'tooltip'        => __('When one player starts, all other players on the same page pause automatically.<br><br>Recommended when you have multiple players on the same page.', 'vapfem'),
    'tooltip_width'  => 'wide',
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc'           => esc_html__('Pause other players when one starts playing', 'vapfem'),
    'default'        => $defaults['autopause'] ?? null,
    'disabled'       => true,
    'pro'            => ['onclick' => 'openUpgradeModal'],
]);

$sr->endSection();

$sr->startSection('volume_speed', esc_html__('Volume & Speed', 'vapfem'), [
    'accordion' => true,
    'exclusive' => 'player-settings',
    'meta'      => esc_html__('- Applies to all players', 'vapfem'),
]);

// Initial Volume
$settings->fieldRenderer->render('number', 'volume', [
    'label'   => esc_html__('Initial Volume', 'vapfem'),
    'tooltip' => esc_html__('How loud players start, from 0% (silent) to 100% (full). Viewers can still adjust during playback.', 'vapfem'),
    'desc'    => esc_html__('Starting volume level for all players', 'vapfem'),
    'min'     => 0,
    'max'     => 100,
    'step'    => 1,
    'unit'    => '%',
    'default' => isset($defaults['volume']) ? ($defaults['volume'] * 100) : 100,
]);

// Playback Speed
$settings->fieldRenderer->render('select', 'speed_selected', [
    'label'         => esc_html__('Starting Playback Speed', 'vapfem'),
    'desc'          => esc_html__('Speed players use when they first load', 'vapfem'),
    'tooltip'       => __('Sets the default speed for all players site-wide.<br><br>Override per player or widget as needed.<br><br><strong>Popular choices:</strong> 1.25x or 1.5x for podcasts and lessons.', 'vapfem'),
    'tooltip_width' => 'wide',
    'options'       => leanpl_get_speed_registry(),
    'default'       => $defaults['speed_selected'] ?? null,
]);

// Available Playback Speeds
$settings->fieldRenderer->render('checkbox', 'speed_options', [
    'label'         => esc_html__('Available Playback Speeds', 'vapfem'),
    'desc'          => __('<strong>Uncheck all = use the default set.</strong> Choose which speeds visitors can pick from the player settings menu.', 'vapfem'),
    'tooltip'       => __('Controls which speeds appear in the player\'s settings menu.<br><br>Trim the list to keep the menu short — most sites never need 4x.<br><br><strong>Note</strong><br>YouTube and Vimeo only support 0.5x to 2x. Speeds outside that range are hidden automatically on those players.', 'vapfem'),
    'tooltip_width' => 'wide',
    'is_multiple'   => true,
    'options'       => leanpl_get_speed_registry(),
    'default'       => $defaults['speed_options'] ?? null,
]);

$sr->endSection();

$sr->startSection('controls_time_tooltips', esc_html__('Time & Seeking', 'vapfem'), [
    'accordion'      => true,
    'exclusive'      => 'player-settings',
    'meta'           => esc_html__('- Applies to all players', 'vapfem'),
]);

// Time Display Format
$settings->fieldRenderer->render('select', 'invert_time', [
    'label'         => esc_html__('Time Display Format', 'vapfem'),
    'tooltip'       => __('<ul><li><strong>Remaining Time (Countdown)</strong><br>Counts down from total. Example: -2:30</li><li><strong>Elapsed Time</strong><br>Counts up from zero. Example: 2:30</li></ul>', 'vapfem'),
    'tooltip_width' => 'wide',
    'desc'          => esc_html__('Choose what the time counter shows during playback.', 'vapfem'),
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
    'label'         => esc_html__('Skip Forward/Back Amount', 'vapfem'),
    'tooltip'       => __('Seconds to skip per keyboard shortcut press.<br><ul><li>Arrow right / left — skip forward or back</li><li>Example: 10 sec means each press jumps 10 seconds</li></ul><strong>Note:</strong> Clicking the progress bar always jumps directly to that position.', 'vapfem'),
    'tooltip_width' => 'wide',
    'desc'          => esc_html__('Seconds to skip per keyboard shortcut press.', 'vapfem'),
    'min' => 1,
    'max' => 60,
    'step' => 1,
    'unit' => 'Seconds',
    'default' => $defaults['seek_time'] ?? null,
    'disabled' => true,
    'pro' => ['onclick' => 'openUpgradeModal'],
]);

$sr->endSection();

$sr->startSection('controls_keyboard', esc_html__('Keyboard', 'vapfem'), [
    'accordion'      => true,
    'exclusive'      => 'player-settings',
    'meta'           => esc_html__('- Applies to all players', 'vapfem'),
]);

// Keyboard Shortcuts
$settings->fieldRenderer->render('checkbox', 'keyboard_focused', [
    'label'             => esc_html__('Keyboard Shortcuts', 'vapfem'),
    'tooltip'           => __('Control playback with keyboard keys:<br><ul><li><strong>Space</strong> — play / pause</li><li><strong>Arrow keys</strong> — seek forward / back</li><li><strong>M</strong> — mute</li><li><strong>F</strong> — fullscreen</li></ul>Works when the player is focused (clicked or tabbed into).', 'vapfem'),
    'tooltip_width'     => 'wide',
    'tooltip_position'  => 'below',
    'checkbox_label'    => esc_html__('Yes', 'vapfem'),
    'desc'              => esc_html__('Enable keyboard shortcuts when the player is focused', 'vapfem'),
    'default'           => $defaults['keyboard_focused'] ?? null,
]);

// Global Keyboard Shortcuts
$settings->fieldRenderer->render('checkbox', 'keyboard_global', [
    'label'          => esc_html__('Global Keyboard Shortcuts', 'vapfem'),
    'tooltip'        => __('Keyboard shortcuts work even when the player is not focused.<br><br><strong>Caution:</strong> Only enable this if you have one player per page.<br>With multiple players, all of them respond to the same keys at once.', 'vapfem'),
    'tooltip_width'  => 'wide',
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc'           => esc_html__('Enable keyboard shortcuts from anywhere on the page', 'vapfem'),
    'default'        => $defaults['keyboard_global'] ?? null,
]);

$sr->endSection();
$sr->endVtab();

// ============================================
// VTAB: Video-Only
// ============================================
$sr->startVtab('video', esc_html__('Video-Only', 'vapfem'), [
    'icon'   => leanpl_ssot( 'icons', 'video_svg' ),
    'parent' => 'player-defaults',
]);

$sr->startSection('video_only', esc_html__('Video-Only', 'vapfem'), [
    'accordion' => true,
    'exclusive' => 'player-settings',
    'meta'      => esc_html__('- Applies to video players only', 'vapfem'),
]);
$settings->fieldRenderer->render('info', 'video_info', [
    'content' => __('Set once, applies to every video player on your site. Override individually when you need a one-off change.<br><strong>Note:</strong> Reset to Start When Finished is ignored for playlist players. They auto-advance instead.', 'vapfem'),
]);

// Click to play
$settings->fieldRenderer->render('checkbox', 'click_to_play', [
    'label' => esc_html__('Click Video to Play/Pause', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc' => esc_html__('Allow clicking on the video to play or pause', 'vapfem'),
    'default' => $defaults['click_to_play'] ?? null,
]);

// Fullscreen enabled
$settings->fieldRenderer->render('checkbox', 'fullscreen_enabled', [
    'label' => esc_html__('Allow Fullscreen', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc' => esc_html__('Let viewers expand the video to fullscreen.', 'vapfem'),
    'tooltip' => __('<strong>What it does</strong><br>Lets viewers open the video in fullscreen.<br><br><strong>On:</strong> the fullscreen button, double-click on the video, and the F key all work.<br><strong>Off:</strong> all three are disabled, fullscreen is fully turned off.<br><br><strong>Note:</strong> the Player Controls list only sets whether the button shows in the bar. To fully disable fullscreen, turn this off.', 'vapfem'),
    'tooltip_width' => 'wide',
    'default' => $defaults['fullscreen_enabled'] ?? null,
]);

// Auto Hide Control
$settings->fieldRenderer->render('checkbox', 'hide_controls', [
    'label' => esc_html__('Auto-Hide Controls', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc' => esc_html__('Hide controls while playing, show on hover or tap', 'vapfem'),
    'default' => $defaults['hide_controls'] ?? null,
    'disabled' => true,
    'pro' => ['onclick' => 'openUpgradeModal'],
]);

// Reset on End
$settings->fieldRenderer->render('checkbox', 'reset_on_end', [
    'label' => esc_html__('Reset to Start When Finished', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'desc' => esc_html__('Video-only. Reset to the beginning after playback ends. Has no effect on audio players.', 'vapfem'),
    'default' => $defaults['reset_on_end'] ?? null,
]);

$sr->endSection();
$sr->endVtab();

// ============================================
// VTAB: Playlist
// ============================================
$sr->startVtab('playlist', esc_html__('Playlist', 'vapfem'), [
    'icon' => leanpl_ssot( 'icons', 'playlist_svg' ),
]);

$playlist_defaults = leanpl_get_playlist_defaults();

$sr->startSection('playlist_general', esc_html__('General', 'vapfem'));

$settings->fieldRenderer->render('checkbox', 'playlist.enabled', [
    'label'          => esc_html__('Enable Playlist Feature', 'vapfem'),
    'desc'           => esc_html__('Disable if you don\'t need the playlist feature. When disabled, the Playlists menu, playlist shortcode, and playlist-related fields will be hidden.', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'default'        => $playlist_defaults['enabled'] ?? null,
]);

$settings->fieldRenderer->render('checkbox', 'playlist.auto_thumbnail', [
    'label'          => esc_html__('Automatic Thumbnail', 'vapfem'),
    'desc'           => esc_html__('Use the video provider\'s thumbnail for playlist items when no custom image is uploaded. Supports YouTube and Vimeo. Private or deleted videos will show no thumbnail.', 'vapfem'),
    'checkbox_label' => esc_html__('Yes', 'vapfem'),
    'default'        => $playlist_defaults['auto_thumbnail'] ?? false,
    'disabled'       => true,
    'pro'            => ['onclick' => 'openUpgradeModal'],
]);

$sr->endSection();
$sr->endVtab();

// ============================================
// VTAB: Import/Export
// ============================================
$sr->startVtab('import-export', esc_html__('Import/Export', 'vapfem'), [
    'icon' => leanpl_ssot( 'icons', 'import_export_svg' ),
]);

$sr->startSection('export-settings', esc_html__('Export Settings', 'vapfem'), ['disable_save_button' => true]);

$settings->fieldRenderer->render('action-buttons', 'export_settings', [
    'label' => esc_html__('Export Settings', 'vapfem'),
    'buttons' => [
        [
            'id' => 'lex-export-settings',
            'action' => 'export_settings',
            'text' => esc_html__('Export Settings', 'vapfem'),
            'icon' => 'download',
            'desc' => esc_html__('Download your current settings as a JSON file for backup.', 'vapfem'),
            'class' => 'lex-btn--secondary',
        ]
    ],
    'layout' => 'vertical',
]);

$sr->endSection();

$sr->startSection('import-settings', esc_html__('Import Settings', 'vapfem'), ['disable_save_button' => true]);
?>
<tr class="lex-field--file lex-field--import-file">
    <th scope="row">
        <label for="lex-import-file"><?php echo esc_html__('Upload Settings File', 'vapfem'); ?></label>
    </th>
    <td>
        <input type="file" id="lex-import-file" accept=".json" />
        <p class="description"><?php echo esc_html__('Select a previously exported settings JSON file.', 'vapfem'); ?></p>
        <br />
        <button type="button" class="lex-btn lex-btn--secondary lex-action-btn" id="lex-import-settings" data-action="import_settings">
            <span class="dashicons dashicons-upload"></span>
            <?php echo esc_html__('Import Settings', 'vapfem'); ?>
        </button>
    </td>
</tr>
<?php
$sr->endSection();
$sr->endVtab();

// Render Save & Reset buttons
$sr->renderSubmitButtons();
