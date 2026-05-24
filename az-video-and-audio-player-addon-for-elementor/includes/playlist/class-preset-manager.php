<?php
/**
 * Preset_Manager — preset loading/validation (stub).
 *
 * Self-initialized if used as shared service; otherwise optional.
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Preset_Manager class.
 *
 * Stub for future preset management.
 */
class Preset_Manager {

    /**
     * Instance.
     *
     * @var Preset_Manager|null
     */
    private static $instance = null;

    /**
     * Get instance.
     *
     * @return Preset_Manager
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        // Future: preset loading, validation.
    }
}
