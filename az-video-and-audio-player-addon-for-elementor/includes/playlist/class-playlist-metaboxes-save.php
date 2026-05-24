<?php
/**
 * Playlist Metabox Save Handler
 *
 * Handles saving playlist items from the metabox.
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Playlist_Metaboxes_Save {

    /**
     * Single instance
     *
     * @var Playlist_Metaboxes_Save|null
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Playlist_Metaboxes_Save
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'save_post', [ $this, 'save_metabox' ] );
    }

    /**
     * Save playlist metabox data
     *
     * @param int $post_id Post ID
     */
    public function save_metabox( $post_id ) {
        // Post type check
        if ( get_post_type( $post_id ) !== 'lean_playlist' ) {
            return;
        }

        // Autosave check
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Nonce check
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce value doesn't need sanitization
        if ( ! isset( $_POST['leanpl_playlist_metabox_nonce'] ) ||
             ! wp_verify_nonce( wp_unslash( $_POST['leanpl_playlist_metabox_nonce'] ), 'leanpl_save_playlist_metabox' ) ) {
            return;
        }

        // Permission check
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Build clean items array from POST data
        $items = [];
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Each value is sanitized with absint below
        $raw_items = isset( $_POST['_playlist_items'] ) ? wp_unslash( $_POST['_playlist_items'] ) : [];

        if ( is_array( $raw_items ) ) {
            foreach ( $raw_items as $item ) {
                if ( isset( $item['id'] ) && absint( $item['id'] ) > 0 ) {
                    $items[] = [
                        'id' => absint( $item['id'] ),
                    ];
                }
            }
        }

        update_post_meta( $post_id, '_playlist_items', $items );
    }
}

Playlist_Metaboxes_Save::get_instance();
