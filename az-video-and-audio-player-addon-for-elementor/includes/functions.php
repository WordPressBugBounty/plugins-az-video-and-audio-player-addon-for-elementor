<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function vapfem_get_player_defaults(){
    return include VAPFEM_DIR . '/includes/player-defaults.php';
}

function vapfem_get_video_defaults(){
    $defaults = include VAPFEM_DIR . '/includes/player-defaults.php';
    return array_merge($defaults['shared'], $defaults['video']);
}

function vapfem_get_audio_defaults(){
    $defaults = include VAPFEM_DIR . '/includes/player-defaults.php';
    return array_merge($defaults['shared'], $defaults['audio']);
}

/**
 * Shortcode Converter Functions
 * Shared functionality for both video and audio shortcodes
 */

/**
 * Convert yes/no strings to boolean with smart validation
 */
function vapfem_yes_no_to_bool($value) {
    $value = strtolower(sanitize_text_field($value));

    // Valid yes values
    if (in_array($value, ['yes', '1', 'true', 'on'])) {
        return true;
    }

    // Valid no values
    if (in_array($value, ['no', '0', 'false', 'off'])) {
        return false;
    }

    // Invalid value - log and return null to use renderer default
    return null;
}

/**
 * Convert and clamp volume to 0-1 range with validation
 */
function vapfem_float_clamp_0_1($value) {
    $float = floatval($value);

    // Clamp to valid range
    if ($float < 0 || $float > 1) {
        $float = max(0, min(1, $float));
    }

    return $float;
}

/**
 * Convert comma-separated string to array for controls
 * Example: "play,fullscreen" → ['play', 'fullscreen']
 */
function vapfem_convert_comma_to_array($value) {
    if (empty($value)) {
        return null; // Let renderer use defaults
    }

    $sanitized = sanitize_text_field(wp_unslash($value));
    $array = array_map('trim', explode(',', $sanitized));

    // Remove empty values
    $array = array_filter($array);

    if (empty($array)) {
        return null;
    }

    return $array;
}

/**
 * Convert sources string to HTML5 video list format
 * Example: "video1.mp4|720,video2.mp4|1080" → [['url' => 'video1.mp4', 'size' => '720'], ...]
 */
function vapfem_convert_sources_to_html5_list($value) {
    if (empty($value)) {
        return null; // Let renderer use defaults
    }

    $sanitized = sanitize_text_field(wp_unslash($value));
    $sources = array_map('trim', explode(',', $sanitized));
    $html5_list = [];

    foreach ($sources as $source) {
        if (empty($source)) continue;

        // Parse format: "url|size" or just "url"
        $parts = explode('|', $source);
        $url = trim($parts[0]);
        $size = isset($parts[1]) ? trim($parts[1]) : '';

        if (!empty($url)) {
            $html5_list[] = [
                'url' => esc_url($url),
                'size' => $size
            ];
        }
    }

    if (empty($html5_list)) {
        return null;
    }

    return $html5_list;
}

/**
 * Convert string to float with validation
 */
function vapfem_convert_to_float($value) {
    $float = floatval(sanitize_text_field(wp_unslash($value)));
    return $float;
}

/**
 * Convert string to integer with validation
 */
function vapfem_convert_to_int($value) {
    $int = intval(sanitize_text_field(wp_unslash($value)));
    return $int;
}

/**
 * Convert HTML5 video list from array to comma-separated string
 *
 * @param array $sources Array of ['url' => ..., 'size' => ...] items
 * @return string Comma-separated string of URL|size pairs
 */
function vapfem_convert_html5_video_list_arr_to_str($sources) {
    $pairs = [];
    foreach ($sources as $video) {
        if (!empty($video['url'])) {
            $size_part = !empty($video['size']) ? '|' . $video['size'] : '';
            $pairs[] = $video['url'] . $size_part;
        }
    }
    return implode(',', $pairs);
}