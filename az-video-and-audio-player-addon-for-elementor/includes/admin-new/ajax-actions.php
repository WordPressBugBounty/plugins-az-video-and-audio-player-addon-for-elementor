<?php
namespace LeanPL\Admin_New;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Bulk-action AJAX endpoints for the "All Players (New)" screen.
 *
 * Thin layer: validate request (nonce, capability, input), call the WP post
 * API, respond with JSON. No business logic lives here. Conventions (nonce
 * name, cap checks, JSON shape) mirror includes/playlist/ajax-actions.php.
 *
 * @package LeanPL\Admin_New
 */

/**
 * Shared guard for every endpoint: nonce + the ids and post_type POST args,
 * already sanitized. Dies with a JSON error when the nonce is bad or the
 * post_type is missing/unrecognised.
 *
 * @return array{ids:int[],post_type:string}|void Sanitised ids and post type,
 *         or wp_send_json_error() on failure.
 */
function leanpl_admin_new_guard() {
    check_ajax_referer( 'leanpl_admin_new', 'nonce' );

    $post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';
    if ( ! in_array( $post_type, [ 'lean_player', 'lean_playlist' ], true ) ) {
        wp_send_json_error( [ 'message' => __( 'Invalid post type.', 'vapfem' ) ], 400 );
    }

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $raw = isset( $_POST['ids'] ) ? (array) wp_unslash( $_POST['ids'] ) : [];
    $ids = array_filter( array_map( 'absint', $raw ) );

    return [
        'ids'       => $ids,
        'post_type' => $post_type,
    ];
}

/**
 * Set the post_status of one or more players.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_admin_new_bulk_status() {
    $guard     = leanpl_admin_new_guard();
    $ids       = $guard['ids'];
    $post_type = $guard['post_type'];

    $status = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';
    if ( ! in_array( $status, [ 'publish', 'draft' ], true ) ) {
        wp_send_json_error( [ 'message' => __( 'Invalid status.', 'vapfem' ) ], 400 );
    }

    $updated = 0;
    $failed  = [];

    foreach ( $ids as $id ) {
        if ( get_post_type( $id ) !== $post_type ) {
            $failed[] = $id;
            continue;
        }

        if ( ! current_user_can( 'edit_post', $id ) ) {
            $failed[] = $id;
            continue;
        }

        $result = wp_update_post( [
            'ID'          => $id,
            'post_status' => $status,
        ], true );

        if ( is_wp_error( $result ) ) {
            $failed[] = $id;
            continue;
        }

        $updated++;
    }

    wp_send_json_success( [
        'updated' => $updated,
        'failed'  => array_values( $failed ),
    ] );
}
add_action( 'wp_ajax_leanpl_admin_new_bulk_status', __NAMESPACE__ . '\\leanpl_admin_new_bulk_status' );

/**
 * Move one or more players to the trash.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_admin_new_bulk_trash() {
    $guard     = leanpl_admin_new_guard();
    $ids       = $guard['ids'];
    $post_type = $guard['post_type'];

    $updated = 0;
    $failed  = [];

    foreach ( $ids as $id ) {
        if ( get_post_type( $id ) !== $post_type ) {
            $failed[] = $id;
            continue;
        }

        if ( ! current_user_can( 'delete_post', $id ) ) {
            $failed[] = $id;
            continue;
        }

        if ( wp_trash_post( $id ) ) {
            $updated++;
        } else {
            $failed[] = $id;
        }
    }

    wp_send_json_success( [
        'updated' => $updated,
        'failed'  => array_values( $failed ),
    ] );
}
add_action( 'wp_ajax_leanpl_admin_new_bulk_trash', __NAMESPACE__ . '\\leanpl_admin_new_bulk_trash' );

/**
 * Restore trashed players.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_admin_new_bulk_restore() {
    $guard     = leanpl_admin_new_guard();
    $ids       = $guard['ids'];
    $post_type = $guard['post_type'];

    $updated = 0;
    $failed  = [];

    foreach ( $ids as $id ) {
        if ( get_post_type( $id ) !== $post_type ) {
            $failed[] = $id;
            continue;
        }

        if ( ! current_user_can( 'delete_post', $id ) ) {
            $failed[] = $id;
            continue;
        }

        if ( wp_untrash_post( $id ) ) {
            $updated++;
        } else {
            $failed[] = $id;
        }
    }

    wp_send_json_success( [
        'updated' => $updated,
        'failed'  => array_values( $failed ),
    ] );
}
add_action( 'wp_ajax_leanpl_admin_new_bulk_restore', __NAMESPACE__ . '\\leanpl_admin_new_bulk_restore' );

/**
 * Permanently delete trashed players.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_admin_new_bulk_delete() {
    $guard     = leanpl_admin_new_guard();
    $ids       = $guard['ids'];
    $post_type = $guard['post_type'];

    $updated = 0;
    $failed  = [];

    foreach ( $ids as $id ) {
        if ( get_post_type( $id ) !== $post_type ) {
            $failed[] = $id;
            continue;
        }

        if ( ! current_user_can( 'delete_post', $id ) ) {
            $failed[] = $id;
            continue;
        }

        $result = wp_delete_post( $id, true );
        if ( $result ) {
            $updated++;
        } else {
            $failed[] = $id;
        }
    }

    wp_send_json_success( [
        'updated' => $updated,
        'failed'  => array_values( $failed ),
    ] );
}
add_action( 'wp_ajax_leanpl_admin_new_bulk_delete', __NAMESPACE__ . '\\leanpl_admin_new_bulk_delete' );

/**
 * Creates a real (draft) lean_playlist post with its type fixed immediately,
 * before the edit screen ever loads - the "New Playlist" video/audio picker
 * modal's card click (initAddPlaylistModal() in admin-new.js) calls this,
 * then navigates to ?page=lean-playlist-edit&post={id} with the returned ID.
 *
 * Reuses the leanpl_admin_new nonce/ajax_url every screen this modal can
 * appear on already has localized (leanpl_admin_new_enqueue_localize()) -
 * the list screen has no leanplAdminNewPreview object (that one's only
 * localized on the edit screen itself), so this can't reuse
 * leanpl_admin_new_save_playlist's own nonce the way the edit screen's own
 * Save Draft does.
 *
 * _playlist_type is set once, here, and never touched again by
 * leanpl_admin_new_save_playlist() past this point (type is fixed at
 * creation, no picker on the edit screen itself) - same one-time-write
 * contract, just moved earlier since the post now exists before the edit
 * screen's first save does.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_admin_new_create_playlist() {
    check_ajax_referer( 'leanpl_admin_new', 'nonce' );

    if ( ! current_user_can( 'publish_posts' ) ) {
        wp_send_json_error( [ 'message' => __( 'You are not allowed to create playlists.', 'vapfem' ) ], 403 );
    }

    $type = isset( $_POST['playlist_type'] ) ? sanitize_key( wp_unslash( $_POST['playlist_type'] ) ) : 'video';
    if ( ! in_array( $type, [ 'video', 'audio' ], true ) ) {
        $type = 'video';
    }

    $post_id = wp_insert_post( [
        'post_type'   => 'lean_playlist',
        'post_title'  => __( 'Untitled Playlist', 'vapfem' ),
        'post_status' => 'draft',
    ], true );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( [ 'message' => __( 'Could not create the playlist. Please try again.', 'vapfem' ) ] );
    }

    update_post_meta( $post_id, '_playlist_type', $type );

    wp_send_json_success( [ 'postId' => $post_id ] );
}
add_action( 'wp_ajax_leanpl_admin_new_create_playlist', __NAMESPACE__ . '\\leanpl_admin_new_create_playlist' );

/**
 * Publish/Update for the Edit Player (New) screen (Settings_Page::render_player_edit_new_page()).
 * Creates or updates a lean_player post's title and, if the request carries
 * them, the picked source and/or poster (same field names as
 * class-live-preview-ajax.php's $_POST contract). Also accepts an optional
 * post_status (draft|publish, default publish) so the footer's Save Draft
 * can land here too - same handler, status from the request.
 *
 * Deliberately does NOT route through Metabox_Save::save_metabox() (hooked
 * to save_post): that handler blanks every field in
 * Metaboxes::get_field_definitions() that isn't present in $_POST, correct
 * for the classic metabox's full-form submit but wrong here - this screen's
 * save is intentionally partial (only title + source + poster are wired;
 * Layout & Branding / Behavior / Advanced are still static mockups), and
 * reusing that handler unmodified would silently wipe every other real
 * setting on an existing post the moment someone re-saves through this
 * screen. So this only ever touches the handful of keys it actually knows
 * about, and only when the request includes them.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_admin_new_save_player() {
    check_ajax_referer( 'leanpl_admin_new_save_player', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => __( 'You are not allowed to save this.', 'vapfem' ) ], 403 );
    }

    $post_id = isset( $_POST['post_ID'] ) ? absint( $_POST['post_ID'] ) : 0;

    if ( $post_id > 0 && ( get_post_type( $post_id ) !== 'lean_player' || ! current_user_can( 'edit_post', $post_id ) ) ) {
        wp_send_json_error( [ 'message' => __( 'You are not allowed to edit this player.', 'vapfem' ) ], 403 );
    }

    $title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
    if ( '' === $title ) {
        $title = __( 'Untitled Player', 'vapfem' );
    }

    // Footer "Save Draft" posts post_status=draft; Update/Publish posts
    // nothing (default 'publish'). Allowed set is intentionally tiny - this
    // screen never trashes or schedules, and a forged future/private status
    // has no business landing here.
    $post_status = isset( $_POST['post_status'] )
        ? sanitize_key( wp_unslash( $_POST['post_status'] ) )
        : 'publish';
    if ( ! in_array( $post_status, [ 'draft', 'publish' ], true ) ) {
        $post_status = 'publish';
    }

    $postarr = [
        'post_type'   => 'lean_player',
        'post_title'  => $title,
        'post_status' => $post_status,
    ];

    if ( $post_id > 0 ) {
        $postarr['ID'] = $post_id;
        $result = wp_update_post( $postarr, true );
    } else {
        $result = wp_insert_post( $postarr, true );
    }

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    $saved_post_id = $result;
    $was_existing  = ( $post_id > 0 );

    // "Player saved." for draft (matches the footer's Save Draft path);
    // existing/published keeps "updated"/"published".
    if ( 'draft' === $post_status ) {
        $message = __( 'Player saved.', 'vapfem' );
    } elseif ( $was_existing ) {
        $message = __( 'Player updated.', 'vapfem' );
    } else {
        $message = __( 'Player published.', 'vapfem' );
    }

    $text_keys = [ '_player_type', '_video_type', '_html5_source_type', '_audio_source_type' ];
    $url_keys  = [ '_youtube_url', '_vimeo_url', '_html5_video_url', '_html5_audio_url' ];
    $id_keys   = [ '_video_source', '_audio_source' ];

    $has_source_field = false;
    foreach ( array_merge( $text_keys, $url_keys, $id_keys ) as $key ) {
        if ( isset( $_POST[ $key ] ) ) {
            $has_source_field = true;
            break;
        }
    }

    if ( $has_source_field ) {
        foreach ( $text_keys as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $saved_post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }

        foreach ( $url_keys as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                // wp_unslash()-only, matching class-live-preview-ajax.php's
                // post_raw() - sanitize_text_field() strips %XX sequences
                // and corrupts file URLs with spaces/special chars.
                $value = wp_unslash( $_POST[ $key ] );
                update_post_meta( $saved_post_id, $key, esc_url_raw( is_string( $value ) ? trim( $value ) : '' ) );
            }
        }

        foreach ( $id_keys as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $saved_post_id, $key, absint( $_POST[ $key ] ) );
            }
        }
    }

    // Poster is independent of the source fields above (a poster can be set
    // before a source is picked), so it's saved unconditionally rather than
    // gated behind $has_source_field.
    if ( isset( $_POST['_poster'] ) ) {
        update_post_meta( $saved_post_id, '_poster', absint( $_POST['_poster'] ) );
    }

    // Layout picker's hidden input (edit-layout-grid.php) isn't in
    // leanpl_admin_new_registry() - player_layout is structural, not a
    // per-type config value, so it doesn't belong in that flat registry.
    // Validated against the same SSOT the picker's tiles are built from.
    // Empty is a valid pick too - the grid's first tile ("Inherit", see
    // player-edit-sections.php) submits '' to explicitly clear back to the
    // global Settings -> Player Layout value, not just "nothing posted".
    // Whole grid is pro (Lock A18) - re-checked here the same way
    // Metabox_Save re-checks every other locked key, so a forged POST
    // can't set it on free even though the grid has no `name` attribute
    // client-side to submit it with in the first place.
    $edit_layout_field_filtered = apply_filters(
        'leanpl/metabox/field_config',
        [ 'field_name' => '_player_layout', 'pro' => [ 'onclick' => 'openUpgradeModal' ] ],
        '_player_layout',
        $saved_post_id
    );
    if ( isset( $_POST['_player_layout'] ) && empty( $edit_layout_field_filtered['pro'] ) ) {
        $layout_key = sanitize_key( wp_unslash( $_POST['_player_layout'] ) );
        // A Custom Preset reference ('preset_...', Lock A11) is also a valid
        // value here - it's what the Custom Preset tile writes instead of a
        // real layout key, see Custom_Preset_Injector's render_preset_ref_input().
        if ( '' === $layout_key || array_key_exists( $layout_key, leanpl_get_player_layouts() ) || 0 === strpos( $layout_key, 'preset_' ) ) {
            update_post_meta( $saved_post_id, '_player_layout', $layout_key );
        }
    }

    // Save the General / Behavior / Advanced cards. Only keys actually
    // present in the request are touched (absent means skip, not blank), and
    // pro-locked keys are ignored server-side - see Metabox_Save.
    Metabox_Save::get_instance()->save( $saved_post_id );

    wp_send_json_success( [
        'postId'     => $saved_post_id,
        'message'    => $message,
        // Preview link exists only once there's a real post - the topbar
        // Preview button (edit-topbar.php) starts disabled for a brand-new,
        // unsaved player and admin-new.js enables it using this URL once the
        // first save succeeds, same preview-link shape as the page's own
        // initial render.
        'previewUrl' => get_preview_post_link( $saved_post_id, [
            'preview'       => 'true',
            'preview_id'    => $saved_post_id,
            'preview_nonce' => wp_create_nonce( 'post_preview_' . $saved_post_id ),
        ] ),
    ] );
}
add_action( 'wp_ajax_leanpl_admin_new_save_player', __NAMESPACE__ . '\\leanpl_admin_new_save_player' );

/**
 * Ask a media URL what kind of thing it actually is, for the Edit Player
 * screen's Add Media button. A browser can't ask another site's server this
 * question itself, so it asks ours instead.
 *
 * Only called client-side (initAddMedia() in admin-new.js) when the
 * instant, no-network checks already there - YouTube/Vimeo by name, a known
 * file extension - come back empty. That is almost always just a live
 * stream, whose address has no file ending to read
 * (e.g. ".../groovesalad-128-mp3" - a hyphen, not a dot). See
 * leanpl_probe_media_kind() (functions-player.php) for the HEAD-request
 * mechanics, its caching, and its SSRF protection.
 *
 * While already asking, this also flags two problems worth catching before
 * the player is saved rather than after a visitor hits them: a dead link
 * (the address answered "not found"), and an insecure stream on a secure
 * site (browsers silently block http:// media on an https:// page - this is
 * a plain scheme comparison, not something the probe's own response can
 * see, since the station answers our server just fine either way).
 *
 * @return void Sends JSON and exits.
 */
function leanpl_admin_new_detect_media() {
    check_ajax_referer( 'leanpl_detect_media', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => __( 'Permission denied.', 'vapfem' ) ], 403 );
    }

    $url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';

    if ( '' === $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
        wp_send_json_success( [
            'kind'    => 'unknown',
            'warning' => null,
        ] );
    }

    $probed  = leanpl_probe_media_kind( $url );
    $warning = null;

    if ( $probed['dead'] ) {
        $warning = 'dead';
    } elseif ( leanpl_is_insecure_media_url( $url ) ) {
        $warning = 'insecure';
    }

    wp_send_json_success( [
        'kind'    => $probed['kind'],
        'warning' => $warning,
    ] );
}
add_action( 'wp_ajax_leanpl_admin_new_detect_media', __NAMESPACE__ . '\\leanpl_admin_new_detect_media' );

/**
 * Publish/Update for the Edit Playlist (New) screen
 * (Settings_Page::render_playlist_edit_new_page()). Creates or updates a
 * lean_playlist post's title and persists every _playlist_* registry key the
 * inspector form sent, plus _playlist_items from whichever surface staged it
 * client-side - the Playlist Items card's row order or the Add Track
 * drawer's Media Hub checked set (item_ids, a JSON-encoded ID array,
 * validated against real lean_player posts below - see the item_ids block
 * further down for which surface wins and why). Also accepts an
 * optional post_status
 * (draft|publish, default publish) so the footer's Save Draft can land here
 * too - same handler, status from the request.
 *
 * Mirrors leanpl_admin_new_save_player but with no source/poster branch: a
 * playlist has no single source to pick. _playlist_type is left alone,
 * matching that screen's "type is fixed at creation" rule.
 *
 * Same partial-save contract as the player handler: absent means skip, not
 * blank, so a save through this screen never wipes a key that was not part
 * of the request. Pro gating for the _playlist_* registry is re-checked
 * server-side via Metabox_Save::save_playlist(), which calls
 * leanpl_admin_new_playlist_is_locked() per key rather than trusting the
 * render-time name= strip.
 *
 * @return void Sends JSON and exits.
 */
function leanpl_admin_new_save_playlist() {
    check_ajax_referer( 'leanpl_admin_new_save_playlist', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( [ 'message' => __( 'You are not allowed to save this.', 'vapfem' ) ], 403 );
    }

    $post_id = isset( $_POST['post_ID'] ) ? absint( $_POST['post_ID'] ) : 0;

    if ( $post_id > 0 && ( get_post_type( $post_id ) !== 'lean_playlist' || ! current_user_can( 'edit_post', $post_id ) ) ) {
        wp_send_json_error( [ 'message' => __( 'You are not allowed to edit this playlist.', 'vapfem' ) ], 403 );
    }

    $title = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
    if ( '' === $title ) {
        $title = __( 'Untitled Playlist', 'vapfem' );
    }

    // Footer "Save Draft" posts post_status=draft; Update/Publish posts
    // nothing (default 'publish'). Same allowed set + reasoning as the
    // player handler above.
    $post_status = isset( $_POST['post_status'] )
        ? sanitize_key( wp_unslash( $_POST['post_status'] ) )
        : 'publish';
    if ( ! in_array( $post_status, [ 'draft', 'publish' ], true ) ) {
        $post_status = 'publish';
    }

    $postarr = [
        'post_type'   => 'lean_playlist',
        'post_title'  => $title,
        'post_status' => $post_status,
    ];

    if ( $post_id > 0 ) {
        $postarr['ID'] = $post_id;
        $result = wp_update_post( $postarr, true );
    } else {
        $result = wp_insert_post( $postarr, true );
    }

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    $saved_post_id = $result;
    $was_existing  = ( $post_id > 0 );

    // _playlist_type: fixed at creation, chosen via the New Playlist picker
    // modal (add-playlist-type-modal.php) before the edit screen ever
    // loaded - written once here, on the post's first-ever save, and never
    // again (an update never re-sends a different value the UI could act on
    // anyway, since there's no picker on the edit screen itself). Lives
    // outside Metabox_Save's registry on purpose - see this handler's own
    // "only keys actually present" contract above; a dedicated one-time
    // write here keeps that contract intact for every other _playlist_* key.
    if ( ! $was_existing ) {
        $new_playlist_type = isset( $_POST['playlist_type'] ) ? sanitize_key( wp_unslash( $_POST['playlist_type'] ) ) : 'video';
        if ( ! in_array( $new_playlist_type, [ 'video', 'audio' ], true ) ) {
            $new_playlist_type = 'video';
        }
        update_post_meta( $saved_post_id, '_playlist_type', $new_playlist_type );
    }

    // "Playlist saved." for draft (matches the footer's Save Draft path);
    // existing/published keeps "updated"/"published".
    if ( 'draft' === $post_status ) {
        $message = __( 'Playlist saved.', 'vapfem' );
    } elseif ( $was_existing ) {
        $message = __( 'Playlist updated.', 'vapfem' );
    } else {
        $message = __( 'Playlist published.', 'vapfem' );
    }

    // _playlist_player_layout: structural, not a per-type config value (same
    // reasoning as _player_layout on the player screen above), so it isn't in
    // Metabox_Save's flat registry either - validated here against the same
    // SSOT the picker's tiles are built from, plus Custom Preset references
    // ('preset_...', Lock A11 - see Custom_Preset_Injector's
    // render_preset_ref_input()). Empty is a valid pick too - the grid's
    // "Inherit" tile submits '' to explicitly fall back to the global
    // Settings -> Player Layout value. Whole grid is pro-lockable
    // (playlist-edit-sections.php's $playlist_layout_locked) - re-checked
    // here the same way Metabox_Save re-checks every other locked key, so a
    // forged POST can't set it on free even though the grid drops the
    // input's `name` client-side when locked.
    $playlist_layout_field_filtered = apply_filters(
        'leanpl/metabox/field_config',
        [ 'field_name' => '_playlist_player_layout', 'pro' => [ 'onclick' => 'openUpgradeModal' ] ],
        '_playlist_player_layout',
        $saved_post_id
    );
    if ( isset( $_POST['_playlist_player_layout'] ) && empty( $playlist_layout_field_filtered['pro'] ) ) {
        $playlist_layout_key = sanitize_key( wp_unslash( $_POST['_playlist_player_layout'] ) );
        if ( '' === $playlist_layout_key || array_key_exists( $playlist_layout_key, leanpl_get_player_layouts() ) || 0 === strpos( $playlist_layout_key, 'preset_' ) ) {
            update_post_meta( $saved_post_id, '_playlist_player_layout', $playlist_layout_key );
        }
    }

    // Persist the Layout/Appearance/Header/List Items/Audio/Behavior cards.
    // Only keys actually present in the request are touched (absent means
    // skip, not blank), and pro-locked keys are ignored server-side - see
    // Metabox_Save::save_playlist(). _playlist_type is deliberately not in
    // the registry, so this screen never wipes it.
    Metabox_Save::get_instance()->save_playlist( $saved_post_id );

    // _playlist_items: DOM-only staging, never written until this save runs
    // - same "nothing persists until Update/Save Draft" contract every other
    // field on this screen already follows (see class-live-preview-ajax.php's
    // docblock for the matching live-preview side of that same staging).
    // item_ids is JSON-encoded (a plain item_ids[] array would vanish from
    // the request entirely once empty, since jQuery's param serializer drops
    // empty arrays, which would read as "absent" here and silently skip
    // clearing _playlist_items).
    //
    // Two staged sources feed this, picked client-side by submitSave()
    // (admin-new.js): the Playlist Items card's row order
    // (edit-section-playlist-track-list.php / trackListPendingItemIds()) for
    // an already-saved playlist, or the Add Track drawer's Media Hub checked
    // set (trackDrawerPendingItemIds()) when that card isn't rendered yet
    // (a brand-new, unsaved playlist has no saved items for it to show).
    // The card's order is NOT deduplicated: Duplicate stages the same
    // player_id twice on purpose, and a duplicate is a legitimate repeated
    // _playlist_items entry, not a bug - only invalid/nonexistent ids are
    // dropped below, real duplicates are kept in whatever order the client
    // sent. Sent unconditionally by submitSave() whenever either surface
    // exists on the page (post_id > 0) - so it reflects reality even when
    // the user never touched either one this visit. Absent (neither
    // rendered) means skip, same partial-save contract as every other key on
    // this handler.
    if ( isset( $_POST['item_ids'] ) && is_string( $_POST['item_ids'] ) ) {
        $decoded_item_ids = json_decode( wp_unslash( $_POST['item_ids'] ), true );
        $raw_item_ids     = is_array( $decoded_item_ids ) ? array_map( 'absint', $decoded_item_ids ) : [];
        $items            = [];

        foreach ( $raw_item_ids as $item_player_id ) {
            // Re-validated server-side, not trusted from the client: only a
            // real lean_player post can end up in _playlist_items.
            if ( $item_player_id > 0 && get_post_type( $item_player_id ) === 'lean_player' ) {
                $items[] = [ 'id' => $item_player_id ];
            }
        }

        update_post_meta( $saved_post_id, '_playlist_items', $items );
    }

    // track_edits: per-track field edits (title/url/attachment_id/duration/
    // meta_text/poster_id) staged client-side in the Playlist Items card's
    // row edit panels - same "nothing writes until Update/Save Draft"
    // contract as _playlist_items above (see class-live-preview-ajax.php's
    // post_track_overrides() for the matching live-preview side, which
    // previews title/duration/meta/thumbnail but not a staged URL change -
    // that only takes effect here, on the real save).
    //
    // Each track's CURRENT saved values are fetched first (leanpl_get_player_edit_data())
    // and the staged patch merged on top, then written through
    // leanpl_update_player_from_url() - the same function the row panel's
    // old immediate-write Save Changes button used, reused as-is rather than
    // duplicated. Reusing it (instead of a partial per-field updater) is
    // deliberate: that function requires SOME url or attachment_id to detect
    // a source from, and a raw patch containing only e.g. duration has
    // neither - merging onto the fetched current state guarantees one is
    // always present.
    if ( isset( $_POST['track_edits'] ) && is_string( $_POST['track_edits'] ) ) {
        $decoded_edits = json_decode( wp_unslash( $_POST['track_edits'] ), true );

        if ( is_array( $decoded_edits ) ) {
            $edit_playlist_type = get_post_meta( $saved_post_id, '_playlist_type', true ) ?: 'video';

            foreach ( $decoded_edits as $raw_edit_player_id => $edit_patch ) {
                $edit_player_id = absint( $raw_edit_player_id );

                if ( ! $edit_player_id || ! is_array( $edit_patch )
                    || get_post_type( $edit_player_id ) !== 'lean_player'
                    || ! current_user_can( 'edit_post', $edit_player_id )
                ) {
                    continue;
                }

                $current = leanpl_get_player_edit_data( $edit_player_id );
                if ( is_wp_error( $current ) ) {
                    continue;
                }

                leanpl_update_player_from_url( $edit_player_id, [
                    'url'           => array_key_exists( 'url', $edit_patch ) ? esc_url_raw( (string) $edit_patch['url'] ) : $current['url'],
                    'attachment_id' => array_key_exists( 'attachment_id', $edit_patch ) ? absint( $edit_patch['attachment_id'] ) : $current['attachment_id'],
                    'title'         => array_key_exists( 'title', $edit_patch ) ? sanitize_text_field( (string) $edit_patch['title'] ) : $current['title'],
                    'poster_id'     => array_key_exists( 'poster_id', $edit_patch ) ? absint( $edit_patch['poster_id'] ) : $current['poster_id'],
                    'duration'      => array_key_exists( 'duration', $edit_patch ) ? sanitize_text_field( (string) $edit_patch['duration'] ) : $current['duration'],
                    'meta_text'     => array_key_exists( 'meta_text', $edit_patch ) ? sanitize_text_field( (string) $edit_patch['meta_text'] ) : $current['meta_text'],
                    'playlist_type' => $edit_playlist_type,
                    'probe'         => true,
                ] );
            }
        }
    }

    wp_send_json_success( [
        'postId'     => $saved_post_id,
        'message'    => $message,
        'previewUrl' => get_preview_post_link( $saved_post_id, [
            'preview'       => 'true',
            'preview_id'    => $saved_post_id,
            'preview_nonce' => wp_create_nonce( 'post_preview_' . $saved_post_id ),
        ] ),
    ] );
}
add_action( 'wp_ajax_leanpl_admin_new_save_playlist', __NAMESPACE__ . '\\leanpl_admin_new_save_playlist' );

/**
 * Per-screen config for the admin-new.js localizer: which post type a
 * screen's bulk actions operate on, plus the noun/confirm/error strings its
 * toasts and confirms use. One map keyed by page slug so a new screen is a
 * new entry here, not a new localize call - only one screen renders at a
 * time, so they all write to the same leanplAdminNew global.
 *
 * @return array<string,array{post_type:string,strings:array<string,string>}>
 */
function leanpl_admin_new_screens() {
    return [
        'lean_player-all-players-new'   => [
            'post_type' => 'lean_player',
            'strings'   => [
                'noun'           => __( 'player', 'vapfem' ),
                'noun_plural'    => __( 'players', 'vapfem' ),
                'confirm_trash'  => __( 'Move the selected players to the Trash?', 'vapfem' ),
                'confirm_delete' => __( 'Permanently delete the selected players? This cannot be undone.', 'vapfem' ),
                'select_first'   => __( 'Select at least one player first.', 'vapfem' ),
                'no_permission'  => __( 'Nothing was updated. You may not have permission to edit those players.', 'vapfem' ),
            ],
        ],
        // Edit screen's overflow-menu Trash item reuses this same
        // initRowActions()/bulk_trash pipeline (see edit-topbar.php) - only
        // 'confirm_trash' and 'no_permission' are actually read for a single
        // row, but the full string set is included for parity with the list
        // screens above rather than a partial one that would break if the
        // toast copy (BULK_MESSAGE) ever needed 'noun' here too.
        //
        // The top-level `lean-player` slug also reaches this same edit screen
        // when given a `post` param - leanpl_admin_new_enqueue_localize()
        // below resolves that case to this key before the lookup, since one
        // page slug can't map to two different string sets (list vs. edit).
        'lean-player-edit'              => [
            'post_type' => 'lean_player',
            'strings'   => [
                'noun'           => __( 'player', 'vapfem' ),
                'noun_plural'    => __( 'players', 'vapfem' ),
                'confirm_trash'  => __( 'Move this player to the Trash?', 'vapfem' ),
                'confirm_delete' => __( 'Permanently delete this player? This cannot be undone.', 'vapfem' ),
                'select_first'   => __( 'Select at least one player first.', 'vapfem' ),
                'no_permission'  => __( 'Nothing was updated. You may not have permission to edit this player.', 'vapfem' ),
            ],
        ],
        'lean-player-playlist'         => [
            'post_type' => 'lean_playlist',
            'strings'   => [
                'noun'           => __( 'playlist', 'vapfem' ),
                'noun_plural'    => __( 'playlists', 'vapfem' ),
                'confirm_trash'  => __( 'Move the selected playlists to the Trash?', 'vapfem' ),
                'confirm_delete' => __( 'Permanently delete the selected playlists? This cannot be undone.', 'vapfem' ),
                'select_first'   => __( 'Select at least one playlist first.', 'vapfem' ),
                'no_permission'  => __( 'Nothing was updated. You may not have permission to edit those playlists.', 'vapfem' ),
            ],
        ],

        // Singular wording, unlike the list screen above: the only bulk-ish
        // action the edit screen offers is its overflow menu's Trash, which
        // acts on the one playlist being edited.
        'lean-playlist-edit'    => [
            'post_type' => 'lean_playlist',
            'strings'   => [
                'noun'           => __( 'playlist', 'vapfem' ),
                'noun_plural'    => __( 'playlists', 'vapfem' ),
                'confirm_trash'  => __( 'Move this playlist to the Trash?', 'vapfem' ),
                'confirm_delete' => __( 'Permanently delete this playlist? This cannot be undone.', 'vapfem' ),
                'select_first'   => __( 'Select at least one playlist first.', 'vapfem' ),
                'no_permission'  => __( 'Nothing was updated. You may not have permission to edit this playlist.', 'vapfem' ),
            ],
        ],
    ];
}

/**
 * Localise the bulk-action nonce / AJAX url / post type / strings for
 * admin-new.js on whichever of our "New" admin screens is rendering. Lives
 * here (not in the assets manager) because every other localize in this
 * codebase lives in the feature class that owns the screen.
 *
 * @return void
 */
function leanpl_admin_new_enqueue_localize() {
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    $page    = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
    $screens = leanpl_admin_new_screens();

    // The top-level `lean-player` slug renders the edit screen instead of the
    // list whenever a `post` param is present (Settings_Page::render_all_players_new_page()) -
    // resolve to the edit screen's map entry in that case, since $page alone
    // can't distinguish the two.
    if ( 'lean-player' === $page && isset( $_GET['post'] ) ) {
        $page = 'lean-player-edit';
    }

    if ( ! isset( $screens[ $page ] ) ) {
        return;
    }

    $status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'all';
    if ( ! in_array( $status, [ 'all', 'publish', 'draft', 'future', 'trash' ], true ) ) {
        $status = 'all';
    }

    wp_localize_script( 'leanpl-admin-new', 'leanplAdminNew', array_merge( [
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'leanpl_admin_new' ),
        'status'   => $status,
        'per_page' => 20,
    ], $screens[ $page ] ) );
}
// Priority 20: Assets_Manager::register_all() runs at the default 10, so the
// 'leanpl-admin-new' handle does not exist yet at that point and
// wp_localize_script() would silently no-op.
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\leanpl_admin_new_enqueue_localize', 20 );