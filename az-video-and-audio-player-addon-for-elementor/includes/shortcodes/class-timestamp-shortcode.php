<?php
namespace LeanPL\Shortcodes;

/**
 * Timestamp Shortcode Class
 * Handles [lean_timestamp] shortcode functionality
 * Renders a clickable link that seeks a same-page player to a specific time
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Timestamp_Shortcode {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize shortcode
     */
    public function init() {
        add_action('init', [$this, 'register_shortcode']);
    }

    /**
     * Register timestamp shortcode
     */
    public function register_shortcode() {
        add_shortcode('lean_timestamp', [$this, 'render_timestamp_shortcode']);
    }

    /**
     * Timestamp shortcode handler
     *
     * Usage:
     * [lean_timestamp time="1:30"]Jump to intro[/lean_timestamp]
     * [lean_timestamp time="90"]Jump to intro[/lean_timestamp]
     * [lean_timestamp time="1:30" player="123"]Jump to intro[/lean_timestamp]
     *
     * @param array       $atts    Shortcode attributes
     * @param string|null $content Link text
     * @return string Shortcode output
     */
    public function render_timestamp_shortcode($atts, $content = null) {
        $atts = shortcode_atts(['time' => '', 'player' => ''], $atts, 'lean_timestamp');
        $content = $content === null ? '' : $content;

        $seconds = $this->parse_time_to_seconds($atts['time']);

        // Unparseable/negative time: render as plain text, no dead link.
        if ($seconds === null) {
            return wp_kses_post($content);
        }

        $player_id = absint($atts['player']);
        $player_attr = $player_id > 0 ? sprintf(' data-lpl-player="%d"', $player_id) : '';

        // <a>, not <button>: jumping to a moment is navigation, not a form
        // action, and themes reset button chrome far more aggressively than
        // inline link styling. href="#" is a real, focusable anchor target;
        // the click handler calls preventDefault() so it never scrolls/jumps.
        return sprintf(
            '<a href="#" class="lpl-timestamp" data-lpl-time="%d"%s>%s</a>',
            $seconds,
            $player_attr,
            wp_kses_post($content)
        );
    }

    /**
     * Parse a "time" attribute into whole seconds.
     *
     * Accepts raw seconds ("90"), "mm:ss", or "hh:mm:ss". Returns null for
     * anything negative or unparseable.
     *
     * @param string $time Raw "time" attribute value
     * @return int|null
     */
    private function parse_time_to_seconds($time) {
        $time = trim((string) $time);

        if ($time === '') {
            return null;
        }

        if (is_numeric($time)) {
            $seconds = (float) $time;
            return $seconds >= 0 ? (int) round($seconds) : null;
        }

        $parts = explode(':', $time);

        if (count($parts) < 2 || count($parts) > 3) {
            return null;
        }

        foreach ($parts as $part) {
            if (!ctype_digit($part)) {
                return null;
            }
        }

        $parts = array_map('intval', $parts);

        if (count($parts) === 2) {
            list($minutes, $seconds) = $parts;
            $hours = 0;
        } else {
            list($hours, $minutes, $seconds) = $parts;
        }

        if ($minutes > 59 || $seconds > 59) {
            return null;
        }

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }
}

// Initialize the timestamp shortcode
Timestamp_Shortcode::get_instance();
