<?php
namespace LeanPL;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CRUD for the leanpl_custom_presets option. Nonce + capability pattern
 * matches includes/playlist/ajax-actions.php rather than inventing one.
 */
class Custom_Preset_Ajax {

    const OPTION_KEY = 'leanpl_custom_presets';

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_ajax_leanpl_save_custom_preset', [ $this, 'save_preset' ] );
        add_action( 'wp_ajax_leanpl_delete_custom_preset', [ $this, 'delete_preset' ] );
    }

    /**
     * Resolve a raw `player_layout` value that may be a Custom Preset
     * reference (Lock A11 - live reference, not stamp-and-step-away) into
     * its effective layout key + controls. Single source of truth for this
     * resolution: class-player-renderer.php (single player) and
     * playlist/class-config.php (playlist embedded player) both call this
     * instead of duplicating the `preset_` prefix check + option lookup, so
     * a playlist's embedded player and a standalone player resolve a
     * Custom Preset identically - including in live preview, which reads
     * whatever the picker currently holds via the same field.
     *
     * @param string $value Raw player_layout value - a real layout key, a
     *                       `preset_...` id, or ''.
     * @return array{layout:string, controls:?array} `controls` is null when
     *         `$value` isn't a preset reference, or the preset has none -
     *         caller falls through to its own layout -> controls lookup.
     */
    public static function resolve_layout_ref( string $value ): array {
        if ( 0 !== strpos( $value, 'preset_' ) ) {
            return [ 'layout' => $value, 'controls' => null ];
        }

        $presets = get_option( self::OPTION_KEY, [] );
        $preset  = ( is_array( $presets ) && isset( $presets[ $value ] ) ) ? $presets[ $value ] : null;

        // Deleted preset: rendering must not depend on a preset that no
        // longer exists (see resolve_player_layout()'s docblock).
        if ( ! $preset ) {
            return [ 'layout' => 'classic', 'controls' => null ];
        }

        return [
            'layout'   => is_string( $preset['layout'] ?? null ) ? $preset['layout'] : '',
            'controls' => ! empty( $preset['controls'] ) ? $preset['controls'] : null,
        ];
    }

    /**
     * Upsert: an empty/unknown preset_id creates a new preset, a known one
     * overwrites it in place (same id, same position in the library).
     */
    public function save_preset() {
        check_ajax_referer( 'leanpl_custom_preset_admin', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'You are not allowed to manage presets.', 'vapfem' ) ], 403 );
        }

        $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        if ( $name === '' ) {
            wp_send_json_error( [ 'message' => __( 'Preset name is required.', 'vapfem' ) ] );
        }

        $registry = leanpl_get_controls_registry();
        $raw_controls = isset( $_POST['controls'] ) ? (array) wp_unslash( $_POST['controls'] ) : [];
        $controls = [];
        foreach ( $raw_controls as $control ) {
            $control = sanitize_key( $control );
            if ( isset( $registry[ $control ] ) && ! in_array( $control, $controls, true ) ) {
                $controls[] = $control;
            }
        }

        if ( empty( $controls ) ) {
            wp_send_json_error( [ 'message' => __( 'Pick at least one control.', 'vapfem' ) ] );
        }

        $layout = $this->resolve_layout( isset( $_POST['start_from'] ) ? sanitize_key( wp_unslash( $_POST['start_from'] ) ) : '' );

        $presets = get_option( self::OPTION_KEY, [] );
        if ( ! is_array( $presets ) ) {
            $presets = [];
        }

        $preset_id = isset( $_POST['preset_id'] ) ? sanitize_key( wp_unslash( $_POST['preset_id'] ) ) : '';
        if ( $preset_id === '' || ! isset( $presets[ $preset_id ] ) ) {
            $preset_id = 'preset_' . substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 12 );
        }

        $presets[ $preset_id ] = [
            'name'     => $name,
            'layout'   => $layout,
            'controls' => $controls,
            'created'  => isset( $presets[ $preset_id ]['created'] ) ? $presets[ $preset_id ]['created'] : time(),
        ];

        update_option( self::OPTION_KEY, $presets, false );

        wp_send_json_success( [
            'preset_id' => $preset_id,
            'presets'   => $presets,
        ] );
    }

    /**
     * Deleting an applied preset is deliberately not a special case: the
     * breadcrumb (_applied_custom_preset / applied_custom_preset) still
     * points at this id, the stamped player_layout/controls meta it wrote
     * keep rendering exactly as before (stamp-and-step-away), and the badge
     * just degrades to "(deleted)" next time that field renders - resolved
     * client-side too, from the same presets map this response updates.
     */
    public function delete_preset() {
        check_ajax_referer( 'leanpl_custom_preset_admin', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'You are not allowed to manage presets.', 'vapfem' ) ], 403 );
        }

        $preset_id = isset( $_POST['preset_id'] ) ? sanitize_key( wp_unslash( $_POST['preset_id'] ) ) : '';

        $presets = get_option( self::OPTION_KEY, [] );
        if ( ! is_array( $presets ) ) {
            $presets = [];
        }

        unset( $presets[ $preset_id ] );
        update_option( self::OPTION_KEY, $presets, false );

        wp_send_json_success( [
            'presets' => $presets,
        ] );
    }

    /**
     * "Seed, then decouple" (PLAYER-FREE-VS-PRO.md / Lock A11): Modern/Simple/
     * Minimal's controls array is a full-override contract in
     * Player_Renderer::resolve_layout_controls(), not a starting point. A
     * preset can only claim one of those layouts if picking it changes nothing
     * about the controls the user actually built - which is never true here,
     * since "Start from" only ever seeds the checklist. So only classic/
     * floating (no fixed controls array) may be stored as-is; every other
     * pick collapses to classic, which renders the builder's exact list.
     */
    private function resolve_layout( $start_from ) {
        $layouts = leanpl_get_player_layouts();
        if ( isset( $layouts[ $start_from ] ) && empty( $layouts[ $start_from ]['controls'] ) ) {
            return $start_from;
        }
        return 'classic';
    }
}
