<?php
/**
 * Data helpers for the player edit screen (wiring phase).
 *
 * The card partials are static markup: labels, layout and Tailwind classes
 * all live in includes/admin-new/partials/edit/{player,playlist}/edit-section-{key}.php. These
 * three functions supply only what markup cannot express on its own - the
 * field registry (through the pro filter), the lock question, and the
 * option list for a native <select>.
 *
 * Attributes (name, value, min, max, step) are written literally in the
 * markup, never wrapped by a helper here.
 *
 * @package LeanPL\Admin_New
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The flat field registry, every entry run through the
 * leanpl/metabox/field_config filter exactly as
 * Metaboxes::build_base_args() does. On the pro build that filter strips
 * 'pro' + 'disabled' (Pro_Settings_Unlocker), which is what turns a locked
 * row into a saveable one. Result is cached per request.
 *
 * @return array<string,array> Keyed by meta key.
 */
function leanpl_admin_new_registry() {
    static $fields = null;

    if ( null !== $fields ) {
        return $fields;
    }

    $config = include LEANPL_DIR . '/includes/admin-new/config/player-edit-sections.php';
    $raw    = isset( $config['fields'] ) ? $config['fields'] : [];

    $fields = [];
    foreach ( $raw as $field_name => $field_config ) {
        $fields[ $field_name ] = apply_filters(
            'leanpl/metabox/field_config',
            array_merge( [ 'field_name' => $field_name ], $field_config ),
            $field_name,
            0
        );
    }

    return $fields;
}

/**
 * Whether a registry key is pro-locked in the current edition.
 *
 * @param string $key Meta key.
 * @return bool
 */
function leanpl_admin_new_is_locked( $key ) {
    $fields = leanpl_admin_new_registry();
    return isset( $fields[ $key ] ) && ! empty( $fields[ $key ]['pro'] );
}

/**
 * Echo the <option> list for a select field, marking the stored value
 * selected. Comparison is loose-string so int keys from
 * leanpl_get_speed_registry() ('1' -> 1) match their stored string value.
 *
 * @param string $key     Meta key.
 * @param int    $post_id Post ID (0 for a brand-new player).
 * @return void
 */
function leanpl_admin_new_options( $key, $post_id = 0 ) {
    $fields  = leanpl_admin_new_registry();
    $field   = isset( $fields[ $key ] ) ? $fields[ $key ] : [];
    $options = isset( $field['options'] ) ? $field['options'] : [];
    $stored  = ( $post_id > 0 ) ? get_post_meta( $post_id, $key, true ) : '';

    foreach ( $options as $value => $label ) {
        $selected = ( (string) $value === (string) $stored );
        echo '<option value="' . esc_attr( $value ) . '"' . ( $selected ? ' selected' : '' ) . '>' . esc_html( $label ) . '</option>';
    }
}
