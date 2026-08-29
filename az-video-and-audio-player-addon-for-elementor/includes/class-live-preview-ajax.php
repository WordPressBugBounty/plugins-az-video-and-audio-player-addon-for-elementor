<?php
namespace LeanPL;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Renders the lean_player / lean_playlist metabox's CURRENT (possibly
 * unsaved) field values through the real renderer, for the inline live
 * preview panel (class-player-preview.php). Deliberately does not touch
 * Metabox_Save / Playlist_Metaboxes_Save or post meta at all - reads
 * straight from $_POST. Player path mirrors class-player-shortcode.php's
 * config-building block, calling Player_Renderer with $post_id = 0 so
 * nothing is written to the DB. Playlist path builds an $attr_overrides
 * array for \LeanPL\Playlist\Config's existing shortcode-attribute-override
 * layer (Config::clean_attr_overrides() already type-casts and pro-gates
 * against leanpl_get_playlist_defaults()). Items default to whatever is
 * currently saved on the real playlist post, same as every other field
 * here, unless the caller sends an explicit item_ids - the admin-new Add
 * Track drawer's Media Hub stages checkbox add/remove in JS only (no write)
 * until Update/Publish, and sends its current pending list on every
 * preview request via item_ids so the preview matches what Update would
 * actually save. Same renderers, same markup, same CSS as the frontend
 * either way - this is a scratch render, not a second rendering pipeline.
 */
class Live_Preview_Ajax {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_ajax_leanpl_live_preview', [ $this, 'render_preview' ] );
    }

    public function render_preview() {
        check_ajax_referer( 'leanpl_live_preview', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [ 'message' => __( 'You are not allowed to preview this.', 'vapfem' ) ], 403 );
        }

        $post_type = $this->post_str( 'live_preview_post_type', 'lean_player' );

        if ( $post_type === 'lean_playlist' ) {
            $this->render_playlist_preview();
            return;
        }

        $this->render_player_preview();
    }

    private function render_player_preview() {
        $player_type = $this->post_str( '_player_type', 'video' );
        if ( ! in_array( $player_type, [ 'video', 'audio' ], true ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid player type.', 'vapfem' ) ] );
        }

        $config = $this->build_config_from_post( $player_type );

        $renderer = Player_Renderer::get_instance();

        ob_start();
        if ( $player_type === 'audio' ) {
            $renderer->render_audio_player( $config, 0 );
        } else {
            $renderer->render_video_player( $config, 0 );
        }
        $html = ob_get_clean();

        wp_send_json_success( [ 'html' => $html ] );
    }

    private function render_playlist_preview() {
        // 0 is a valid, supported value here, not just "existing playlist
        // by ID": the admin-new Add Track drawer previews a not-yet-saved
        // playlist this same way, staging tracks via item_id_overrides
        // before the playlist post itself is ever created - see
        // Config::__construct()'s item_id_overrides handling, which is
        // honored regardless of playlist_id.
        $playlist_id = $this->post_id();

        $overrides         = $this->build_playlist_overrides_from_post();
        $item_id_overrides = $this->post_item_ids();
        $track_overrides   = $this->post_track_overrides();

        // Built once here (Renderer builds its own identical copy internally
        // when it actually renders) just to check item count up front: a
        // real 0-item playlist correctly renders nothing on the frontend
        // (Renderer::render() returns immediately when get_first_item() is
        // empty - untouched by this change), but that same empty response
        // read as failure on the admin preview side (blank html -> "Could
        // not load preview."). render_playlist_empty_state() below is the
        // preview-only distinction between "broken" and "this playlist,
        // empty" - it never runs for a real frontend render.
        $config = new \LeanPL\Playlist\Config( $playlist_id, $overrides, $item_id_overrides, $track_overrides );

        if ( ! $config->get_first_item() ) {
            // 'empty' tells admin-new.js to hide the floating Add Track
            // button below the preview (data-lpl-add-track-row) - this
            // markup already has its own Add Track button inside the
            // empty-state panel, so showing both would be redundant.
            wp_send_json_success( [
                'html'  => $this->render_playlist_empty_state( $config ),
                'empty' => true,
            ] );
        }

        $renderer = new \LeanPL\Playlist\Renderer( $playlist_id, $overrides, $item_id_overrides, $track_overrides );

        ob_start();
        $renderer->render();
        $html = ob_get_clean();

        wp_send_json_success( [
            'html'  => $html,
            'empty' => false,
        ] );
    }

    /**
     * Preview-only mockup for a playlist with 0 (possibly still-unsaved)
     * items: the same .lpl-playlist root/player-wrap/panel structure and
     * CSS_Helper-derived skin/classes a populated preview would use, with a
     * placeholder block standing in for the player and an empty-state
     * message standing in for the item list, instead of the panel simply
     * not existing. Covers both a brand-new playlist and an existing one
     * just emptied out via the Add Track drawer's Media Hub - both are
     * "0 items" to Config, so there is only this one code path.
     *
     * [data-lpl-preview-target]-scoped CSS for .lpl-playlist__player--empty
     * and .lpl-playlist__empty-state lives in tailwind-admin.src.css -
     * that attribute only exists on this admin preview panel, so it can
     * never reach the frontend.
     *
     * The placeholder block is deliberately NOT also .lpl-playlist__player:
     * playlist.js's autoInit('.lpl-playlist', ...) still fires on this
     * markup (same root class a populated preview uses), and its
     * initPlaylist() unconditionally calls `new Plyr()` on whatever it
     * finds via that class - which would throw on a plain empty <div>.
     * Leaving the class off means initPlaylist() hits its own existing
     * "no player element" early return instead.
     *
     * Public: also called directly (not via AJAX) by the playlist edit
     * screen's own template for a brand-new, not-yet-saved playlist - see
     * html-playlist-edit-new-page.php. Same "0 items" markup either way,
     * just a different caller.
     *
     * @param \LeanPL\Playlist\Config $config
     * @return string
     */
    public function render_playlist_empty_state( \LeanPL\Playlist\Config $config ) {
        $css_helper = new \LeanPL\Playlist\CSS_Helper( $config );

        ob_start();
        ?>
        <div
            class="<?php echo esc_attr( $css_helper->get_root_classes() ); ?>"
            style="<?php echo esc_attr( $css_helper->get_inline_style() ); ?>"
            data-skin="<?php echo esc_attr( $css_helper->get_data_skin() ); ?>"
        >
            <div class="lpl-playlist__player-area">
                <div class="lpl-playlist__player-wrap">
                    <div class="lpl-playlist__player--empty">
                        <p class="lpl-playlist__player-empty-message">
                            <?php esc_html_e( 'Player preview will load here.', 'vapfem' ); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="lpl-playlist__panel">
                <div class="lpl-playlist__panel-header">
                    <span class="lpl-playlist__panel-header-title"><?php echo esc_html( $config->get_header_text() ); ?></span>
                    <span class="lpl-playlist__panel-header-count"><?php echo esc_html( '0 ' . $config->get_count_label() ); ?></span>
                </div>
                <div class="lpl-playlist__panel-scroll">
                    <div class="lpl-playlist__empty-state">
                        <p class="lpl-playlist__empty-state-message">
                            <?php esc_html_e( 'No items in the playlist. Use the Add Track button to add playlist items.', 'vapfem' ); ?>
                        </p>
                        <?php
                        // Opens the same drawer the floating button below the
                        // preview does (edit-playlist-track-drawer.php - that
                        // markup is stable, outside this AJAX-swapped target).
                        // initTrackDrawer()'s open-trigger binding is
                        // delegated (see admin-new.js), so a trigger injected
                        // here after page load still works.
                        ?>
                        <div data-lpl-track-drawer-open class="lpl-cursor-pointer lpl-btn lpl-btn--md lpl-btn--indigo">
                            <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'plus' ); ?>
                            <div class="lpl-box-border lpl-text-left [white-space:nowrap]">
                                <?php esc_html_e( 'Add Track', 'vapfem' ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * item_ids - the Add Track drawer's currently staged (unsaved) list of
     * player IDs, sent JSON-encoded (trackDrawerPendingItemIdsJson() in
     * admin-new.js) rather than as a plain item_ids array: jQuery's own
     * param serializer drops an empty array from the request body entirely,
     * which would read here as "not sent" and silently fall back to the
     * saved value even though the drawer really does have 0 items checked.
     * A JSON string is present in the POST body either way.
     *
     * Absent means "not sent at all" (every caller except the drawer,
     * including the classic metabox's own preview call) -> null, so
     * Config::__construct() falls back to the post's real saved
     * _playlist_items. Present-but-empty ("[]") is a real override (drawer
     * removed every item) and must still reach Config as [], not null.
     *
     * @return array<int>|null
     */
    private function post_item_ids() {
        if ( ! isset( $_POST['item_ids'] ) || ! is_string( $_POST['item_ids'] ) ) {
            return null;
        }

        $decoded = json_decode( wp_unslash( $_POST['item_ids'] ), true );

        return is_array( $decoded ) ? array_map( 'absint', $decoded ) : null;
    }

    /**
     * track_overrides - the Playlist Items card's staged (unsaved) per-track
     * field edits, sent JSON-encoded (same "empty array survives, empty
     * string doesn't" reasoning as item_ids above) whenever the row edit
     * panel's title/duration/meta text/thumbnail fields have been touched
     * this session. Only the fields explicitly present in a given track's
     * patch override that field - see Config::build_single_item(), which
     * uses array_key_exists() rather than isset() so a field staged as
     * cleared (empty string) is distinguishable from one never touched.
     *
     * The "Paste a link" (source URL) field is deliberately NOT included
     * here - re-detecting source type from an unsaved URL isn't wired into
     * the preview path, only into the real save (leanpl_admin_new_save_playlist()).
     * A staged URL edit still saves correctly on Update/Publish, it just
     * doesn't show in the live preview until then.
     *
     * @return array<int, array<string, mixed>> Player ID => field patch.
     */
    private function post_track_overrides() {
        if ( ! isset( $_POST['track_overrides'] ) || ! is_string( $_POST['track_overrides'] ) ) {
            return [];
        }

        $decoded = json_decode( wp_unslash( $_POST['track_overrides'] ), true );
        if ( ! is_array( $decoded ) ) {
            return [];
        }

        $overrides = [];

        foreach ( $decoded as $raw_player_id => $fields ) {
            $player_id = absint( $raw_player_id );
            if ( ! $player_id || ! is_array( $fields ) ) {
                continue;
            }

            $clean = [];
            if ( array_key_exists( 'title', $fields ) ) {
                $clean['title'] = sanitize_text_field( (string) $fields['title'] );
            }
            if ( array_key_exists( 'duration', $fields ) ) {
                $clean['duration'] = sanitize_text_field( (string) $fields['duration'] );
            }
            if ( array_key_exists( 'meta_text', $fields ) ) {
                $clean['meta_text'] = sanitize_text_field( (string) $fields['meta_text'] );
            }
            if ( array_key_exists( 'poster_id', $fields ) ) {
                $clean['poster_id'] = absint( $fields['poster_id'] );
            }

            if ( $clean ) {
                $overrides[ $player_id ] = $clean;
            }
        }

        return $overrides;
    }

    /**
     * Every schema field is included even when empty, unconditionally -
     * matching build_config_from_post()'s own approach for the player path.
     * An unchecked checkbox or cleared field must actively override the
     * saved value (so the preview reflects "just unchecked", not the stale
     * DB value), not be silently omitted from the overrides array.
     *
     * @return array Raw string values keyed by field name (no _playlist_
     *               prefix) - Config::clean_attr_overrides() does the actual
     *               type-casting and pro-gating against leanpl_get_playlist_defaults().
     */
    private function build_playlist_overrides_from_post() {
        $overrides = [ 'playlist_type' => $this->post_str( '_playlist_type', 'video' ) ];

        foreach ( array_keys( leanpl_get_playlist_meta_field_schema() ) as $field ) {
            $overrides[ $field ] = $this->post_str( '_playlist_' . $field );
        }

        return $overrides;
    }

    /**
     * Mirrors class-player-shortcode.php's render_player_shortcode() config
     * block key-for-key, sourced from $_POST instead of Metaboxes::get_field_value()
     * / get_post_meta(). Deliberately unconditional for most keys (matching
     * the shortcode) - Config_Merger::merge_value() already treats '' / null
     * / [] as "not set, inherit from global/default", so passing '' through
     * is safe and correct.
     *
     * @param string $player_type 'video' or 'audio'.
     * @return array Config array ready for Player_Renderer.
     */
    private function build_config_from_post( $player_type ) {
        $config = [];

        $config['autoplay']         = $this->post_str( '_autoplay' );
        $config['muted']            = $this->post_str( '_muted' );
        $config['loop']             = $this->post_str( '_loop' );
        $config['volume']           = $this->post_str( '_volume' );
        $config['invert_time']      = $this->post_str( '_invert_time' );
        $config['seek_time']        = $this->post_str( '_seek_time' );
        $config['tooltips_seek']    = $this->post_str( '_tooltips_seek' );
        $config['speed_selected']   = $this->post_str( '_speed_selected' );
        $config['storage_enabled']  = $this->post_str( '_storage_enabled' );
        $config['video_type']       = $this->post_str( '_video_type', 'youtube' );
        $config['click_to_play']    = $this->post_str( '_click_to_play' );
        $config['fullscreen_enabled'] = $this->post_str( '_fullscreen_enabled' );
        $config['hide_controls']    = $this->post_str( '_hide_controls' );
        $config['reset_on_end']     = $this->post_str( '_reset_on_end' );
        $config['tooltips_controls'] = $this->post_str( '_tooltips_controls' );
        $config['preload']          = $this->post_str( '_preload' );

        $ratio = $this->post_str( '_ratio' );
        if ( $ratio !== '' ) {
            $config['ratio'] = $ratio;
        }

        $portrait_max_height = $this->post_str( '_portrait_max_height' );
        if ( $portrait_max_height !== '' ) {
            $config['portrait_max_height'] = $portrait_max_height;
        }

        $primary_color = $this->post_str( '_primary_color' );
        if ( $primary_color !== '' ) {
            $config['per_player_primary_color'] = $primary_color;
        }

        $player_layout = $this->post_str( '_player_layout' );
        if ( $player_layout !== '' ) {
            $config['player_layout'] = $player_layout;
        }

        if ( $player_type === 'video' ) {
            $this->process_video_source( $config );
        } else {
            $this->process_audio_source( $config );
        }

        $config['poster'] = $this->process_poster();

        if ( $player_type === 'audio' ) {
            $custom_title = $this->post_str( '_audio_title' );
            if ( ! empty( $config['poster'] ) ) {
                $config['audio_title'] = $custom_title !== '' ? $custom_title : get_the_title( $this->post_id() );
            } elseif ( $custom_title !== '' ) {
                $config['audio_title'] = $custom_title;
            }
            $config['audio_title_enabled'] = $this->post_str( '_audio_title_enabled' ) !== '0';
        }

        return $config;
    }

    private function process_poster() {
        $poster = $this->post_str( '_poster' );
        if ( $poster === '' ) {
            return '';
        }
        if ( is_numeric( $poster ) ) {
            return wp_get_attachment_image_url( $poster, 'full' ) ?: '';
        }
        return esc_url_raw( $poster );
    }

    private function process_video_source( &$config ) {
        $video_type = $config['video_type'];

        if ( $video_type === 'youtube' ) {
            $url = $this->post_raw( '_youtube_url' );
            if ( $url !== '' ) {
                $info = leanpl_parse_video_url( $url );
                $config['video_id'] = $info['id'];
                $config['sources']  = [];
            }
            return;
        }

        if ( $video_type === 'vimeo' ) {
            $url = $this->post_raw( '_vimeo_url' );
            if ( $url !== '' ) {
                $info = leanpl_parse_video_url( $url );
                $config['video_id'] = $info['id'];
                $config['sources']  = [];
            }
            return;
        }

        // html5
        $html5_source_type = $this->post_str( '_html5_source_type', 'link' );
        $video_url = '';

        if ( $html5_source_type === 'upload' ) {
            $video_source = $this->post_raw( '_video_source' );
            if ( $video_source !== '' ) {
                $video_url = is_numeric( $video_source ) ? wp_get_attachment_url( $video_source ) : esc_url_raw( $video_source );
            }
        } else {
            $html5_video_url = $this->post_raw( '_html5_video_url' );
            if ( $html5_video_url !== '' ) {
                $video_url = esc_url_raw( $html5_video_url );
            }
        }

        if ( $video_url ) {
            $info = leanpl_parse_video_url( $video_url );
            $config['video_id'] = $info['id'];
            $config['sources']  = $info['sources'];
        }
    }

    private function process_audio_source( &$config ) {
        $audio_source_type = $this->post_str( '_audio_source_type', 'upload' );
        $audio_url = '';

        if ( $audio_source_type === 'upload' ) {
            $audio_source = $this->post_raw( '_audio_source' );
            if ( $audio_source !== '' ) {
                $audio_url = is_numeric( $audio_source ) ? wp_get_attachment_url( $audio_source ) : esc_url_raw( $audio_source );
            }
        } else {
            $html5_audio_url = $this->post_raw( '_html5_audio_url' );
            if ( $html5_audio_url !== '' ) {
                $audio_url = esc_url_raw( $html5_audio_url );
            }
        }

        if ( $audio_url ) {
            $config['url'] = $audio_url;
        }
    }

    private function post_id() {
        return isset( $_POST['post_ID'] ) ? absint( $_POST['post_ID'] ) : 0;
    }

    /**
     * sanitize_text_field()'d string read - for select/text option-like
     * fields. Never use this for URL-bearing fields - see post_raw().
     */
    private function post_str( $key, $default = '' ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return $default;
        }
        $value = wp_unslash( $_POST[ $key ] );
        return is_string( $value ) ? sanitize_text_field( $value ) : $default;
    }

    /**
     * wp_unslash()-only read for URL-bearing fields, deliberately skipping
     * sanitize_text_field() - it strips %XX sequences (e.g. %20), corrupting
     * file URLs with spaces/special chars (same reasoning as
     * leanpl_parse_video_url()'s own docblock in functions-player.php).
     * esc_url_raw() downstream is what actually sanitizes these.
     */
    private function post_raw( $key ) {
        if ( ! isset( $_POST[ $key ] ) ) {
            return '';
        }
        $value = wp_unslash( $_POST[ $key ] );
        return is_string( $value ) ? trim( $value ) : '';
    }
}
