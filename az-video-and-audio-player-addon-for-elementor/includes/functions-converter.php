<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Shortcode Converter Functions
 * Shared functionality for both video and audio shortcodes
 */

/**
 * Normalize boolean values with smart validation
 * Handles:
 * - Boolean: true/false
 * - String: 'true'/'false', '1'/'0', 'yes'/'no', 'on'/'off'
 * - Numeric: 1/0
 * 
 * @param mixed $value Value to normalize
 * @return bool|null Normalized boolean or null if invalid/empty
 */
function leanpl_normalize_boolean($value) {
    // Already boolean - return as-is
    if (is_bool($value)) {
        return $value;
    }

    // Null or empty string - return null (let caller decide fallback)
    if ($value === null || $value === '') {
        return null;
    }

    // String values
    if (is_string($value)) {
        $lower = strtolower(trim($value));

        // True values
        if (in_array($lower, ['true', '1', 'yes', 'on'], true)) {
            return true;
        }

        // False values
        if (in_array($lower, ['false', '0', 'no', 'off'], true)) {
            return false;
        }
    }

    // Numeric values
    if (is_numeric($value)) {
        if ((int)$value === 1) {
            return true;
        }
        if ((int)$value === 0) {
            return false;
        }
    }

    // Invalid value - return null
    return null;
}

/**
 * Convert and clamp volume to 0-1 range with validation
 * Supports both 0-1 (legacy) and 0-100 (new) formats
 */
function leanpl_float_clamp_0_1($value) {
    $float = floatval($value);

    // If value is > 1, assume it's 0-100 format and convert to 0-1
    if ($float > 1) {
        $float = $float / 100;
    }

    // Clamp to valid range (0-1)
    if ($float < 0 || $float > 1) {
        $float = max(0, min(1, $float));
    }

    return $float;
}

/**
 * Convert comma-separated string to array for controls
 * Example: "play,fullscreen" → ['play', 'fullscreen']
 */
function leanpl_convert_comma_to_array($value) {
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
function leanpl_convert_sources_to_html5_list($value) {
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
function leanpl_convert_to_float($value) {
    $float = floatval(sanitize_text_field(wp_unslash($value)));
    return $float;
}

/**
 * Convert string to integer with validation
 */
function leanpl_convert_to_int($value) {
    $int = intval(sanitize_text_field(wp_unslash($value)));
    return $int;
}

/**
 * Convert HTML5 video list from array to comma-separated string
 *
 * @param array $sources Array of ['url' => ..., 'size' => ...] items
 * @return string Comma-separated string of URL|size pairs
 */
function leanpl_convert_html5_video_list_arr_to_str($sources) {
    $pairs = [];
    foreach ($sources as $video) {
        if (!empty($video['url'])) {
            $size_part = !empty($video['size']) ? '|' . $video['size'] : '';
            $pairs[] = $video['url'] . $size_part;
        }
    }
    return implode(',', $pairs);
}

/**
 * Parse comma-separated string into array
 * 
 * @param string $value Comma-separated values
 * @return array Array of trimmed values
 */
function leanpl_parse_comma_separated($value) {
    // Return empty array if value is empty or not a string
    if (empty($value) || !is_string($value)) {
        return [];
    }
    
    // Split by comma and trim whitespace
    $parts = explode(',', $value);
    
    // Trim each part and filter out empty values
    $result = [];
    foreach ($parts as $part) {
        $trimmed = trim($part);

        if (!empty($trimmed)) {
            $result[] = $trimmed;
        }
    }
    
    return $result;
}