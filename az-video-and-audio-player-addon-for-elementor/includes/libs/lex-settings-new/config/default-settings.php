<?php
/**
 * Default Settings Structure
 * 
 * This file defines the default values for all settings.
 * Organized by example tab namespaces to match the demo tabs.
 * 
 * Data is merged with saved values when rendering forms.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include player defaults
$player_defaults = include LEANPL_DIR . '/includes/player-defaults.php';

// Merge shared and video defaults into flat structure
// Video defaults override shared defaults where keys conflict
$defaults = array_merge(
    $player_defaults['shared'] ?? [],
    $player_defaults['video'] ?? []
);

// Keep audio defaults available if needed
if (isset($player_defaults['audio'])) {
    $defaults['audio'] = $player_defaults['audio'];
}

// Playlist defaults — stored under leanpl_settings['playlist']
$defaults['playlist'] = leanpl_get_playlist_defaults();

return $defaults;