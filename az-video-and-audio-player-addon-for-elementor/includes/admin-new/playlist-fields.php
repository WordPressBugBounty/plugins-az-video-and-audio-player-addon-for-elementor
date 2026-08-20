<?php
/**
 * Data helpers for the playlist edit screen (POC phase).
 *
 * Mirrors includes/admin-new/fields.php, which does the same job for the
 * player screen. Kept as a separate pair of functions rather than
 * generalising the player ones: the two screens read different config
 * files and different meta prefixes, and a shared helper would have to take
 * the config path as an argument at every call site in the card partials -
 * more noise in the markup than the duplication costs here. Worth merging
 * if a third edit screen ever shows up.
 *
 * As in fields.php, attributes (name, value, min, max, step) are written
 * literally in the card partials, never wrapped by a helper.
 *
 * @package LeanPL\Admin_New
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The flat playlist field registry, every entry run through the
 * leanpl/metabox/field_config filter exactly as leanpl_admin_new_registry()
 * does for the player screen. On the pro build that filter strips 'pro' +
 * 'disabled' (Pro_Settings_Unlocker), which is what turns a locked row into
 * a saveable one. Result is cached per request.
 *
 * @return array<string,array> Keyed by meta key.
 */
function leanpl_admin_new_playlist_registry() {
    static $fields = null;

    if ( null !== $fields ) {
        return $fields;
    }

    $config = include LEANPL_DIR . '/includes/admin-new/config/playlist-edit-sections.php';
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
 * Whether a playlist registry key is pro-locked in the current edition.
 *
 * @param string $key Meta key.
 * @return bool
 */
function leanpl_admin_new_playlist_is_locked( $key ) {
    $fields = leanpl_admin_new_playlist_registry();
    return isset( $fields[ $key ] ) && ! empty( $fields[ $key ]['pro'] );
}

/**
 * Echo the <option> list for a playlist select field, marking the resolved
 * value selected.
 *
 * @param string $key     Meta key, e.g. '_playlist_skin'.
 * @param int    $post_id Playlist ID (0 for a brand-new playlist).
 * @return void
 */
function leanpl_admin_new_playlist_options( $key, $post_id = 0 ) {
    $fields  = leanpl_admin_new_playlist_registry();
    $field   = isset( $fields[ $key ] ) ? $fields[ $key ] : [];
    $options = isset( $field['options'] ) ? $field['options'] : [];
    $stored  = leanpl_admin_new_playlist_value( $key, $post_id );

    foreach ( $options as $value => $label ) {
        $selected = ( (string) $value === (string) $stored );
        echo '<option value="' . esc_attr( $value ) . '"' . ( $selected ? ' selected' : '' ) . '>' . esc_html( $label ) . '</option>';
    }
}

/**
 * The resolved value for a playlist field, for inputs that render their
 * value as an attribute (number/text fields) instead of an option list.
 *
 * Falls back to the field's hardcoded default when unset, same as the
 * classic metabox's $val - these fields have no global Settings-page
 * counterpart to inherit from.
 *
 * @param string $key     Meta key, e.g. '_playlist_position'.
 * @param int    $post_id Playlist ID (0 for a brand-new playlist).
 * @return string Resolved value.
 */
function leanpl_admin_new_playlist_value( $key, $post_id = 0 ) {
    $stored = ( $post_id > 0 ) ? get_post_meta( $post_id, $key, true ) : '';
    $stored = is_scalar( $stored ) ? (string) $stored : '';

    if ( $stored !== '' ) {
        return $stored;
    }

    $field    = str_replace( '_playlist_', '', $key );
    $defaults = leanpl_get_playlist_defaults();

    if ( ! isset( $defaults[ $field ] ) ) {
        return '';
    }

    // (string) false is '', not '0' - would read as unset/inherit again and
    // defeat the fallback for boolean defaults like show_thumbnails.
    if ( is_bool( $defaults[ $field ] ) ) {
        return $defaults[ $field ] ? '1' : '0';
    }

    return (string) $defaults[ $field ];
}
