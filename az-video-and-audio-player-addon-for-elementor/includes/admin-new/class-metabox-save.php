<?php
namespace LeanPL\Admin_New;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Save handler for the Edit Player (New) screen.
 *
 * Deliberately a separate class from LeanPL\Metabox_Save rather than a flag
 * on it: that handler blanks every field missing from $_POST (correct for a
 * full-form submit, destructive here), and trusts the render-time missing
 * name= attribute alone for pro gating. This screen's request is partial by
 * design and is served over AJAX, so this class diverges in two ways:
 *
 * 1. Absent means skip, not blank. A key missing from $_POST is left alone
 *    (so a Pro value survives a free-edition save, and a save through this
 *    screen never wipes keys it does not manage).
 * 2. Pro is re-checked server-side. The missing name= on locked rows is UI
 *    convention, not a lock; a forged POST must not change a locked key.
 *
 * Everything else carries over from the original: '' still means Inherit,
 * invalid select values fall back to '' rather than being stored, and the
 * per-type sanitize switch is unchanged. save_number() adds a clamp to the
 * registry's min/max, which the original has no equivalent of.
 *
 * Two entry points now: save() for lean_player (called by
 * leanpl_admin_new_save_player) and save_playlist() for lean_playlist
 * (called by leanpl_admin_new_save_playlist). Both share the per-type
 * dispatch in save_single_field(); only the registry + lock predicate
 * differ, which is why each method names its own.
 *
 * @package LeanPL\Admin_New
 */
class Metabox_Save {
    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * No constructor hooks: the AJAX handler (leanpl_admin_new_save_player)
     * calls save() directly after it has verified the
     * leanpl_admin_new_save_player nonce.
     */
    public function __construct() {}

    /**
     * Persist every registry key present in $_POST (and not pro-locked).
     *
     * @param int $post_id Post ID.
     * @return void
     */
    public function save( $post_id ) {
        if ( ! $this->should_save( $post_id ) ) {
            return;
        }

        foreach ( leanpl_admin_new_registry() as $field_name => $field_config ) {
            if ( leanpl_admin_new_is_locked( $field_name ) ) {
                continue;
            }
            if ( ! isset( $_POST[ $field_name ] ) ) {
                continue;
            }
            $this->save_single_field( $post_id, $field_name, $field_config );
        }
    }

    /**
     * Persist every playlist registry key present in $_POST (and not
     * pro-locked). The playlist counterpart to save(), pulled out rather
     * than parameterised: the two registries and lock predicates live in
     * different functions (leanpl_admin_new_playlist_registry /
     * leanpl_admin_new_playlist_is_locked), and threading those through
     * save() would obscure the player path that already works. Same
     * per-type dispatch, same absent-means-skip, same pro re-check.
     *
     * Called by leanpl_admin_new_save_playlist() after the nonce + post
     * type + capability checks have already run there.
     *
     * @param int $post_id Playlist post ID.
     * @return void
     */
    public function save_playlist( $post_id ) {
        if ( ! $this->should_save_playlist( $post_id ) ) {
            return;
        }

        foreach ( leanpl_admin_new_playlist_registry() as $field_name => $field_config ) {
            if ( leanpl_admin_new_playlist_is_locked( $field_name ) ) {
                continue;
            }
            if ( ! isset( $_POST[ $field_name ] ) ) {
                continue;
            }
            $this->save_single_field( $post_id, $field_name, $field_config );
        }
    }

    /**
     * Post-type and capability guard. DOING_AUTOSAVE and the metabox nonce
     * are dropped: they do not apply to this AJAX entry point.
     *
     * @param int $post_id Post ID.
     * @return bool
     */
    private function should_save( $post_id ) {
        if ( get_post_type( $post_id ) !== 'lean_player' ) {
            return false;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Playlist counterpart to should_save. Same guards, different post type.
     *
     * @param int $post_id Playlist post ID.
     * @return bool
     */
    private function should_save_playlist( $post_id ) {
        if ( get_post_type( $post_id ) !== 'lean_playlist' ) {
            return false;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }

        return true;
    }

    /**
     * Dispatch one field by type.
     *
     * @param int    $post_id      Post ID.
     * @param string $field_name   Meta key.
     * @param array  $field_config Registry entry.
     * @return void
     */
    private function save_single_field( $post_id, $field_name, $field_config ) {
        $type = isset( $field_config['type'] ) ? $field_config['type'] : 'text';

        switch ( $type ) {
            case 'select':
                $this->save_select( $post_id, $field_name, $field_config );
                break;
            case 'switch':
                $this->save_switch( $post_id, $field_name, $field_config );
                break;
            case 'number':
                $this->save_number( $post_id, $field_name, $field_config );
                break;
            case 'media':
                $this->save_media( $post_id, $field_name );
                break;
            default:
                $this->save_text( $post_id, $field_name );
        }
    }

    /**
     * Select field, unified inheritance logic. Empty means Inherit; an
     * invalid value falls back to '' rather than being stored.
     *
     * @param int    $post_id      Post ID.
     * @param string $field_name   Meta key.
     * @param array  $field_config Registry entry.
     * @return void
     */
    private function save_select( $post_id, $field_name, $field_config ) {
        $raw_value = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );

        if ( '' === $raw_value ) {
            update_post_meta( $post_id, $field_name, '' );
            return;
        }

        $options = isset( $field_config['options'] ) ? $field_config['options'] : [];
        if ( isset( $options[ $raw_value ] ) ) {
            update_post_meta( $post_id, $field_name, $raw_value );
        } else {
            update_post_meta( $post_id, $field_name, '' );
        }
    }

    /**
     * Switch field (hidden input always submits, so 1 and 0 both arrive).
     * Validates against the registry options, falling back to the default.
     *
     * @param int    $post_id      Post ID.
     * @param string $field_name   Meta key.
     * @param array  $field_config Registry entry.
     * @return void
     */
    private function save_switch( $post_id, $field_name, $field_config ) {
        $raw_value = sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) );
        $options   = isset( $field_config['options'] ) ? $field_config['options'] : [];

        if ( isset( $options[ $raw_value ] ) ) {
            update_post_meta( $post_id, $field_name, $raw_value );
        } else {
            $default = isset( $field_config['default'] ) ? $field_config['default'] : '1';
            update_post_meta( $post_id, $field_name, $default );
        }
    }

    /**
     * Number field, clamped to the registry's min/max. Empty/invalid stores
     * '' so the frontend falls through to the global/default value.
     *
     * @param int    $post_id      Post ID.
     * @param string $field_name   Meta key.
     * @param array  $field_config Registry entry.
     * @return void
     */
    private function save_number( $post_id, $field_name, $field_config ) {
        $raw_value = wp_unslash( $_POST[ $field_name ] );
        $value     = $this->sanitize( 'number', $raw_value );

        if ( null === $value ) {
            update_post_meta( $post_id, $field_name, '' );
            return;
        }

        $value = (float) $value;
        if ( isset( $field_config['min'] ) ) {
            $value = max( $value, (float) $field_config['min'] );
        }
        if ( isset( $field_config['max'] ) ) {
            $value = min( $value, (float) $field_config['max'] );
        }

        update_post_meta( $post_id, $field_name, $value );
    }

    /**
     * Media field: store the attachment id (0 clears the pick).
     *
     * @param int    $post_id    Post ID.
     * @param string $field_name Meta key.
     * @return void
     */
    private function save_media( $post_id, $field_name ) {
        update_post_meta( $post_id, $field_name, absint( $_POST[ $field_name ] ) );
    }

    /**
     * Text field. Empty is stored as '' (explicit clear / inherit).
     *
     * @param int    $post_id    Post ID.
     * @param string $field_name Meta key.
     * @return void
     */
    private function save_text( $post_id, $field_name ) {
        $value = $this->sanitize( 'text', wp_unslash( $_POST[ $field_name ] ) );
        update_post_meta( $post_id, $field_name, ( null !== $value ) ? $value : '' );
    }

    /**
     * Per-type sanitize switch, carried over from LeanPL\Metabox_Save.
     *
     * @param string $type      Field type.
     * @param mixed  $raw_value Raw value.
     * @return mixed Sanitized value, or null if invalid/empty.
     */
    private function sanitize( $type, $raw_value ) {
        if ( null === $raw_value || '' === $raw_value ) {
            return null;
        }

        switch ( $type ) {
            case 'text':
            case 'select':
            case 'radio':
            case 'password':
            case 'css-border':
            case 'css-box-model':
                return sanitize_text_field( wp_unslash( $raw_value ) );

            case 'textarea':
                return sanitize_textarea_field( wp_unslash( $raw_value ) );

            case 'number':
                $value = floatval( wp_unslash( $raw_value ) );
                if ( is_numeric( $raw_value ) ) {
                    return $value;
                }
                return null;

            case 'checkbox':
                return ( '1' === $raw_value || 1 === $raw_value || true === $raw_value ) ? '1' : '0';

            case 'color':
                $value = sanitize_text_field( wp_unslash( $raw_value ) );
                if ( preg_match( '/^#[0-9A-Fa-f]{6}$/', $value ) ) {
                    return $value;
                }
                return null;

            case 'media':
                $media_id = intval( wp_unslash( $raw_value ) );
                return ( $media_id > 0 ) ? $media_id : null;

            case 'file':
                if ( is_numeric( $raw_value ) ) {
                    return absint( $raw_value );
                }
                return esc_url_raw( wp_unslash( $raw_value ) );

            case 'url':
                return esc_url_raw( wp_unslash( $raw_value ) );

            case 'image-select':
                return sanitize_key( wp_unslash( $raw_value ) );

            default:
                return sanitize_text_field( wp_unslash( $raw_value ) );
        }
    }
}
