<?php
/**
 * Playlist builder AJAX endpoints.
 *
 * Thin layer: validate request (nonce, capability, input), call the
 * shared creator in functions-player.php, respond with JSON.
 * No business logic lives here.
 *
 * @package LeanPL
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Quick Add: create one lean_player from a URL or attachment and
 * return its builder row data.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_playlist_ajax_quick_add() {
    check_ajax_referer( 'leanpl_playlist_admin', 'nonce' );

    if ( ! current_user_can( 'publish_posts' ) ) {
        wp_send_json_error( [ 'message' => __( 'You are not allowed to create players.', 'vapfem' ) ], 403 );
    }

    // esc_url_raw(), not sanitize_text_field(): the latter strips %XX
    // sequences and corrupts URLs with encoded characters (e.g. %20).
    $url   = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
    $title = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';

    // Batch Add already fetches the oEmbed title for a titleless URL; Quick
    // Add never did, so the creator fell back to the URL filename and every
    // YouTube link came in as "Watch". The omnibox has no title field at all,
    // which makes that fallback the common case rather than an edge one.
    if ( $title === '' && $url !== '' ) {
        $title = leanpl_fetch_oembed_title( $url );
    }

    $result = leanpl_create_player_from_url( [
        'url'           => $url,
        'attachment_id' => isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0,
        'title'         => $title,
        'poster_id'     => isset( $_POST['poster_id'] ) ? absint( $_POST['poster_id'] ) : 0,
        'duration'      => isset( $_POST['duration'] ) ? sanitize_text_field( wp_unslash( $_POST['duration'] ) ) : '',
        'meta_text'     => isset( $_POST['meta_text'] ) ? sanitize_text_field( wp_unslash( $_POST['meta_text'] ) ) : '',
        'playlist_type' => isset( $_POST['playlist_type'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist_type'] ) ) : 'video',
    ] );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    wp_send_json_success( $result );
}
add_action( 'wp_ajax_leanpl_playlist_quick_add', 'leanpl_playlist_ajax_quick_add' );

/**
 * Batch Add: create one lean_player per pasted URL and return the
 * builder row data for each, alongside the lines that failed.
 *
 * Partial success is the normal outcome: bad lines are reported, good lines
 * still create players. The only all-or-nothing case is the over-cap error,
 * which is checked before anything is created.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_playlist_ajax_batch_add() {
    check_ajax_referer( 'leanpl_playlist_admin', 'nonce' );

    if ( ! current_user_can( 'publish_posts' ) ) {
        wp_send_json_error( [ 'message' => __( 'You are not allowed to create players.', 'vapfem' ) ], 403 );
    }

    $max = 50;

    // Not sanitize_textarea_field(): it strips %XX sequences, which corrupts
    // URLs containing encoded characters (e.g. %20). Each line is sanitized
    // with esc_url_raw() below, the correct sanitizer for a URL.
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $raw           = isset( $_POST['urls'] ) ? (string) wp_unslash( $_POST['urls'] ) : '';
    $playlist_type = isset( $_POST['playlist_type'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist_type'] ) ) : 'video';

    $lines = preg_split( '/\R/', $raw );
    $lines = array_filter( array_map( 'trim', (array) $lines ), 'strlen' );
    $lines = array_values( array_unique( $lines ) );

    if ( empty( $lines ) ) {
        wp_send_json_error( [ 'message' => __( 'Paste at least one URL, one per line.', 'vapfem' ) ] );
    }

    if ( count( $lines ) > $max ) {
        wp_send_json_error( [
            'message' => sprintf(
                /* translators: 1: number of URLs pasted. 2: maximum allowed per batch. */
                __( 'Too many URLs: %1$d pasted, %2$d is the maximum per batch. Nothing was added.', 'vapfem' ),
                count( $lines ),
                $max
            ),
        ] );
    }

    $added  = [];
    $failed = [];

    foreach ( $lines as $line ) {
        $url = esc_url_raw( $line );

        $result = leanpl_create_player_from_url( [
            'url' => $url,
            // Empty for direct file URLs — the creator derives a title from
            // the filename in that case.
            'title'         => leanpl_fetch_oembed_title( $url ),
            'playlist_type' => $playlist_type,
        ] );

        if ( is_wp_error( $result ) ) {
            // Report the line as the user typed it, not the sanitized form —
            // the JS puts failed lines back in the textarea for retry.
            $failed[] = [
                'url'    => $line,
                'reason' => $result->get_error_message(),
            ];
            continue;
        }

        $added[] = $result;
    }

    wp_send_json_success( [
        'added'  => $added,
        'failed' => $failed,
    ] );
}
add_action( 'wp_ajax_leanpl_playlist_batch_add', 'leanpl_playlist_ajax_batch_add' );

/**
 * Edit drawer: fetch one track's current data so the panel can be
 * prefilled with authoritative values, not stale row data.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_playlist_ajax_get_player() {
    check_ajax_referer( 'leanpl_playlist_admin', 'nonce' );

    $player_id = isset( $_POST['player_id'] ) ? absint( $_POST['player_id'] ) : 0;

    if ( ! current_user_can( 'edit_post', $player_id ) ) {
        wp_send_json_error( [ 'message' => __( 'You are not allowed to edit this track.', 'vapfem' ) ], 403 );
    }

    $result = leanpl_get_player_edit_data( $player_id );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    wp_send_json_success( $result );
}
add_action( 'wp_ajax_leanpl_playlist_get_player', 'leanpl_playlist_ajax_get_player' );

/**
 * Edit drawer: save changes to an existing track and return its
 * refreshed row data, in the same shape Quick Add returns for a new one.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_playlist_ajax_update_player() {
    check_ajax_referer( 'leanpl_playlist_admin', 'nonce' );

    $player_id = isset( $_POST['player_id'] ) ? absint( $_POST['player_id'] ) : 0;

    if ( ! current_user_can( 'edit_post', $player_id ) ) {
        wp_send_json_error( [ 'message' => __( 'You are not allowed to edit this track.', 'vapfem' ) ], 403 );
    }

    // esc_url_raw(), not sanitize_text_field(): the latter strips %XX
    // sequences and corrupts URLs with encoded characters (e.g. %20).
    $url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';

    $result = leanpl_update_player_from_url( $player_id, [
        'url'           => $url,
        'attachment_id' => isset( $_POST['attachment_id'] ) ? absint( $_POST['attachment_id'] ) : 0,
        'title'         => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
        'poster_id'     => isset( $_POST['poster_id'] ) ? absint( $_POST['poster_id'] ) : 0,
        'duration'      => isset( $_POST['duration'] ) ? sanitize_text_field( wp_unslash( $_POST['duration'] ) ) : '',
        'meta_text'     => isset( $_POST['meta_text'] ) ? sanitize_text_field( wp_unslash( $_POST['meta_text'] ) ) : '',
        'playlist_type' => isset( $_POST['playlist_type'] ) ? sanitize_text_field( wp_unslash( $_POST['playlist_type'] ) ) : 'video',
    ] );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    wp_send_json_success( $result );
}
add_action( 'wp_ajax_leanpl_playlist_update_player', 'leanpl_playlist_ajax_update_player' );
