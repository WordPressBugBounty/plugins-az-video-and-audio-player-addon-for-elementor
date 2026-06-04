<?php
/**
 * Playlist Metaboxes
 *
 * Registers and renders metaboxes for the lean_playlist post type:
 * - Main metabox: Playlist type selector + collapsible item picker + sortable selected items list
 * - Shortcode sidebar metabox
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Playlist_Metaboxes {

    /**
     * Single instance
     *
     * @var Playlist_Metaboxes|null
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Playlist_Metaboxes
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
        add_action( 'add_meta_boxes', [ $this, 'add_metaboxes' ] );
        add_action( 'save_post_lean_playlist', [ $this, 'save_metabox' ] );
        add_action( 'admin_footer', [ $this, 'render_type_modal' ] );
        add_action( 'wp_ajax_leanpl_save_for_preview', [ $this, 'ajax_save_for_preview' ] );
    }

    /**
     * Register metaboxes for lean_playlist
     */
    public function add_metaboxes() {
        add_meta_box(
            'leanpl_playlist_metabox',
            esc_html__( 'Playlist Builder', 'vapfem' ),
            [ $this, 'render_items_metabox' ],
            'lean_playlist',
            'normal',
            'high'
        );

        add_meta_box(
            'leanpl_playlist_display_options',
            esc_html__( 'Look & Feel', 'vapfem' ),
            [ $this, 'render_display_options_metabox' ],
            'lean_playlist',
            'normal',
            'default'
        );

        add_meta_box(
            'leanpl_playlist_shortcode',
            esc_html__( 'Shortcode', 'vapfem' ),
            [ $this, 'render_shortcode_metabox' ],
            'lean_playlist',
            'side',
            'default'
        );
    }

    /**
     * Get source type data for a player — used for badges.
     *
     * Returns the specific source type (youtube, vimeo, self-hosted, external)
     * rather than the generic player type (video/audio).
     *
     * @param int $player_id Player post ID.
     * @return array{type: string, label: string}
     */
    private function get_source_type_data( $player_id ) {
        $player_type = get_post_meta( $player_id, '_player_type', true ) ?: 'video';

        if ( $player_type === 'audio' ) {
            $audio_source_type = get_post_meta( $player_id, '_audio_source_type', true ) ?: 'upload';
            if ( $audio_source_type === 'link' ) {
                return [
                    'type'  => 'external',
                    'label' => __( 'External Link', 'vapfem' ),
                ];
            }
            return [
                'type'  => 'self-hosted',
                'label' => __( 'Self Hosted', 'vapfem' ),
            ];
        }

        // Video branch
        $video_type = get_post_meta( $player_id, '_video_type', true ) ?: 'html5';

        if ( $video_type === 'youtube' ) {
            return [
                'type'  => 'youtube',
                'label' => __( 'YouTube', 'vapfem' ),
            ];
        }

        if ( $video_type === 'vimeo' ) {
            return [
                'type'  => 'vimeo',
                'label' => __( 'Vimeo', 'vapfem' ),
            ];
        }

        $html5_source_type = get_post_meta( $player_id, '_html5_source_type', true ) ?: 'upload';
        if ( $html5_source_type === 'link' ) {
            return [
                'type'  => 'external',
                'label' => __( 'External Link', 'vapfem' ),
            ];
        }

        return [
            'type'  => 'self-hosted',
            'label' => __( 'Self Hosted', 'vapfem' ),
        ];
    }

    /**
     * Save playlist type meta on post save.
     *
     * @param int $post_id Post ID.
     */
    public function save_metabox( $post_id ) {
        // Verify nonce
        if (
            ! isset( $_POST['leanpl_playlist_metabox_nonce'] ) ||
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['leanpl_playlist_metabox_nonce'] ) ), 'leanpl_save_playlist_metabox' )
        ) {
            return;
        }

        // Bail on autosave / bulk edit
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save playlist type
        $playlist_type = isset( $_POST['_playlist_type'] )
            ? sanitize_key( wp_unslash( $_POST['_playlist_type'] ) )
            : 'video';

        if ( ! in_array( $playlist_type, [ 'video', 'audio' ], true ) ) {
            $playlist_type = 'video';
        }

        update_post_meta( $post_id, '_playlist_type', $playlist_type );

        foreach ( leanpl_get_playlist_meta_field_schema() as $field => $def ) {
            $sanitize    = $def['sanitize'];
            $is_checkbox = ! empty( $def['checkbox'] );
            $meta_key    = '_playlist_' . $field;

            if ( $is_checkbox ) {
                // Unchecked checkboxes send nothing — store '0' explicitly so
                // get_post_meta returns '0' (not '') and the default fallback never kicks in.
                $raw = isset( $_POST[ $meta_key ] ) ? '1' : '0';
                update_post_meta( $post_id, $meta_key, $raw );
            } else {
                $is_nullable  = ! empty( $def['nullable'] );
                $in_post      = isset( $_POST[ $meta_key ] );
                $raw          = $in_post
                    ? call_user_func( $sanitize, wp_unslash( $_POST[ $meta_key ] ) )
                    : '';

                if ( $is_nullable && $in_post ) {
                    // Field is present but intentionally empty — save '' explicitly
                    // so get_post_meta returns '' and the default fallback never kicks in.
                    update_post_meta( $post_id, $meta_key, $raw );
                } else {
                    $is_zero_type = in_array( $sanitize, [ 'absint' ], true );
                    if ( $raw === '' || ( $is_zero_type && $raw === 0 ) ) {
                        delete_post_meta( $post_id, $meta_key );
                    } else {
                        update_post_meta( $post_id, $meta_key, $raw );
                    }
                }
            }
        }
    }

    /**
     * AJAX: save Display Options + builder items before opening preview.
     */
    public function ajax_save_for_preview() {
        $post_id = absint( $_POST['post_id'] ?? 0 );

        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( 'forbidden' );
        }

        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'leanpl_save_for_preview' ) ) {
            wp_send_json_error( 'invalid_nonce' );
        }

        // Save playlist type
        $playlist_type = isset( $_POST['playlist_type'] ) ? sanitize_key( wp_unslash( $_POST['playlist_type'] ) ) : 'video';
        if ( ! in_array( $playlist_type, [ 'video', 'audio' ], true ) ) {
            $playlist_type = 'video';
        }
        update_post_meta( $post_id, '_playlist_type', $playlist_type );

        // Save Display Options fields
        $fields = isset( $_POST['fields'] ) && is_array( $_POST['fields'] ) ? wp_unslash( $_POST['fields'] ) : [];

        foreach ( leanpl_get_playlist_meta_field_schema() as $field => $def ) {
            $sanitize = $def['sanitize'];
            $meta_key = '_playlist_' . $field;
            $raw      = isset( $fields[ $field ] ) ? call_user_func( $sanitize, $fields[ $field ] ) : '';

            $is_zero_type = in_array( $sanitize, [ 'absint' ], true );
            if ( $raw === '' || ( $is_zero_type && $raw === 0 ) ) {
                delete_post_meta( $post_id, $meta_key );
            } else {
                update_post_meta( $post_id, $meta_key, $raw );
            }
        }

        // Save builder items
        $raw_items = isset( $_POST['items'] ) && is_array( $_POST['items'] ) ? $_POST['items'] : [];
        $items     = array_values( array_filter( array_map( 'absint', $raw_items ) ) );

        if ( empty( $items ) ) {
            delete_post_meta( $post_id, '_playlist_items' );
        } else {
            update_post_meta( $post_id, '_playlist_items', $items );
        }

        wp_send_json_success();
    }

    /**
     * Render the playlist items metabox
     *
     * @param \WP_Post $post Post object
     */
    public function render_items_metabox( $post ) {
        wp_nonce_field( 'leanpl_save_playlist_metabox', 'leanpl_playlist_metabox_nonce' );

        // Get saved playlist type. On new posts honour ?playlist_type query param.
        $saved_type    = get_post_meta( $post->ID, '_playlist_type', true );
        $is_new        = $post->post_status === 'auto-draft';
        $url_type      = isset( $_GET['playlist_type'] ) ? sanitize_key( wp_unslash( $_GET['playlist_type'] ) ) : '';
        if ( $is_new && $url_type && in_array( $url_type, [ 'video', 'audio' ], true ) ) {
            $playlist_type = $url_type;
        } else {
            $playlist_type = $saved_type ?: 'video';
        }
        // Get saved items
        $saved_items = get_post_meta( $post->ID, '_playlist_items', true );
        if ( ! is_array( $saved_items ) ) {
            $saved_items = [];
        }
        $saved_ids = wp_list_pluck( $saved_items, 'id' );

        // Get all published players (both types — JS filters by type)
        $players = get_posts( [
            'post_type'      => 'lean_player',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );

        // Get categories for filtering
        $categories = get_terms( [
            'taxonomy'   => 'lean_player_cat',
            'hide_empty' => false,
        ] );

        // Build player data for JS (categories per player)
        $player_categories = [];
        foreach ( $players as $player ) {
            $terms = wp_get_object_terms( $player->ID, 'lean_player_cat', [ 'fields' => 'slugs' ] );
            $player_categories[ $player->ID ] = is_array( $terms ) ? implode( ',', $terms ) : '';
        }

        $initial_mode = empty( $saved_ids ) ? 'library' : 'playlist';

        wp_localize_script( 'leanpl-playlist-admin', 'leanplPlaylistAdmin', [
            'playlist_type'         => $playlist_type,
            'initial_mode'          => $initial_mode,
            'add_new_url'           => esc_url( admin_url( 'post-new.php?post_type=lean_player' ) ),
            'ajax_url'               => admin_url( 'admin-ajax.php' ),
            'save_for_preview_nonce' => wp_create_nonce( 'leanpl_save_for_preview' ),
            'post_id'                => $post->ID,
        ] );

        ?>
        <div class="lpl-pla__builder" data-mode="<?php echo esc_attr( $initial_mode ); ?>">


            <!-- Playlist mode header -->
            <div class="lpl-pla__builder-playlist-header">
                <button type="button" class="button lpl-pla__builder-add-more-btn" id="lpl-pla-builder-edit-items-btn">
                    <?php esc_html_e( '+ Add More', 'vapfem' ); ?>
                </button>
            </div>

            <!-- Type stored as hidden input for JS filtering only -->
            <input type="hidden" name="_playlist_type" value="<?php echo esc_attr( $playlist_type ); ?>" />

            <!-- Library mode: two-column layout -->
            <div class="lpl-pla__builder-columns">

                <!-- Left: Available Players -->
                <div class="lpl-pla__builder-col lpl-pla__builder-col--left">
                    <div class="lpl-pla__builder-col-header">
                        <span class="lpl-pla__builder-col-title"><?php esc_html_e( 'Available Players', 'vapfem' ); ?></span>
                    </div>
                    <div class="lpl-pla__builder-search-wrap">
                        <input
                            type="text"
                            class="lpl-pla__builder-search"
                            id="lpl-pla-builder-player-search"
                            placeholder="<?php echo esc_attr__( 'Search by player title...', 'vapfem' ); ?>"
                        />
                    </div>
                    <div class="lpl-pla__builder-filter-row">
                        <select class="lpl-pla__builder-category-filter" id="lpl-pla-builder-category-filter">
                            <option value=""><?php esc_html_e( 'All categories', 'vapfem' ); ?></option>
                            <?php if ( ! is_wp_error( $categories ) ) : ?>
                                <?php foreach ( $categories as $cat ) : ?>
                                    <option value="<?php echo esc_attr( $cat->slug ); ?>">
                                        <?php echo esc_html( $cat->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <button type="button" class="button lpl-pla__builder-add-all" id="lpl-pla-builder-add-all">
                            <?php esc_html_e( 'Add all from selected category', 'vapfem' ); ?>
                        </button>
                    </div>
                    <div class="lpl-pla__builder-player-list" id="lpl-pla-builder-player-list">
                        <?php if ( empty( $players ) ) : ?>
                            <div class="lpl-pla__builder-left-empty lpl-pla__builder-left-empty--no-players">
                                <p class="lpl-pla__builder-left-empty-title">
                                    <?php echo $playlist_type === 'audio'
                                        ? esc_html__( 'No audio players found.', 'vapfem' )
                                        : esc_html__( 'No video players found.', 'vapfem' ); ?>
                                </p>
                                <p class="lpl-pla__builder-left-empty-sub"><?php esc_html_e( 'Create a player first, then return here.', 'vapfem' ); ?></p>
                                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=lean_player' ) ); ?>" target="_blank" class="button button-primary lpl-pla__builder-add-new-btn">
                                    <?php esc_html_e( 'Add New Player', 'vapfem' ); ?>
                                </a>
                            </div>
                        <?php else : ?>
                            <?php foreach ( $players as $player ) :
                                $source_data = $this->get_source_type_data( $player->ID );
                                $player_type = get_post_meta( $player->ID, '_player_type', true ) ?: 'video';
                                $is_checked  = in_array( $player->ID, $saved_ids, true );
                                $cats_attr   = isset( $player_categories[ $player->ID ] ) ? $player_categories[ $player->ID ] : '';
                                $duration    = get_post_meta( $player->ID, '_duration', true );
                                $meta_text   = get_post_meta( $player->ID, '_meta_text', true );
                            ?>
                                <label
                                    class="lpl-pla__builder-player-row<?php echo esc_attr( $is_checked ? ' lpl-pla__builder-player-row--added' : '' ); ?>"
                                    data-player-id="<?php echo esc_attr( $player->ID ); ?>"
                                    data-categories="<?php echo esc_attr( $cats_attr ); ?>"
                                    data-title="<?php echo esc_attr( $player->post_title ); ?>"
                                    data-type="<?php echo esc_attr( $player_type ); ?>"
                                    data-source-type="<?php echo esc_attr( $source_data['type'] ); ?>"
                                    data-source-label="<?php echo esc_attr( $source_data['label'] ); ?>"
                                    data-duration="<?php echo esc_attr( $duration ); ?>"
                                    data-meta-text="<?php echo esc_attr( $meta_text ); ?>"
                                >
                                    <input type="checkbox" class="lpl-pla__builder-player-checkbox" value="<?php echo esc_attr( $player->ID ); ?>" <?php checked( $is_checked ); ?> />
                                    <span class="lpl-pla__builder-player-title"><?php echo esc_html( $player->post_title ); ?></span>
                                    <span class="lpl-pla__builder-badge lpl-pla__builder-badge--<?php echo esc_attr( $source_data['type'] ); ?>"><?php echo esc_html( $source_data['label'] ); ?></span>
                                </label>
                            <?php endforeach; ?>
                            <div class="lpl-pla__builder-left-empty lpl-pla__builder-left-empty--search" id="lpl-pla-builder-search-empty" style="display:none;">
                                <p class="lpl-pla__builder-left-empty-title"><?php esc_html_e( 'No players match your search.', 'vapfem' ); ?></p>
                                <p class="lpl-pla__builder-left-empty-sub"><?php esc_html_e( 'Try another keyword or category.', 'vapfem' ); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: item list lives here in library mode -->
                <div class="lpl-pla__builder-col lpl-pla__builder-col--right">
                    <div class="lpl-pla__builder-col-header">
                        <span class="lpl-pla__builder-col-title"><?php esc_html_e( 'Selected Playlist Items', 'vapfem' ); ?></span>
                        <div class="lpl-pla__builder-col-header-right">
                            <span class="lpl-pla__builder-count-pill" id="lpl-pla-builder-selected-count">
                                <?php echo esc_html( count( $saved_ids ) . ' ' . __( 'selected', 'vapfem' ) ); ?>
                            </span>
                            <button type="button" class="lpl-pla__builder-remove-all-btn" id="lpl-pla-builder-remove-all-btn"<?php echo empty( $saved_ids ) ? ' style="display:none;"' : ''; ?>>
                                <?php esc_html_e( 'Remove all', 'vapfem' ); ?>
                            </button>
                        </div>
                    </div>
                    <!-- Item list: always rendered here, left col collapses in playlist mode -->
                    <div class="lpl-pla__builder-item-list" id="lpl-pla-builder-item-list">
                        <?php if ( empty( $saved_ids ) ) : ?>
                            <div class="lpl-pla__builder-items-empty">
                                <p class="lpl-pla__builder-items-empty-title"><?php esc_html_e( 'No players selected yet.', 'vapfem' ); ?></p>
                                <p class="lpl-pla__builder-items-empty-sub"><?php esc_html_e( 'Choose players from the left to build this playlist.', 'vapfem' ); ?></p>
                            </div>
                        <?php else : ?>
                            <?php
                            $index = 0;
                            foreach ( $saved_items as $item ) :
                                $player_id   = absint( $item['id'] );
                                $player      = get_post( $player_id );
                                if ( ! $player || $player->post_status !== 'publish' ) {
                                    continue;
                                }
                                $source_data = $this->get_source_type_data( $player_id );
                                $edit_url    = get_edit_post_link( $player_id, 'raw' );
                            ?>
                                <div class="lpl-pla__builder-item-row" data-player-id="<?php echo esc_attr( $player_id ); ?>">
                                    <span class="lpl-pla__builder-drag-handle" aria-hidden="true">&#8801;</span>
                                    <span class="lpl-pla__builder-item-num"><?php echo esc_html( $index + 1 ); ?></span>
                                    <span class="lpl-pla__builder-item-title"><?php echo esc_html( $player->post_title ); ?></span>
                                    <span class="lpl-pla__builder-badge lpl-pla__builder-badge--<?php echo esc_attr( $source_data['type'] ); ?>">
                                        <?php echo esc_html( $source_data['label'] ); ?>
                                    </span>
                                    <div class="lpl-pla__builder-item-actions">
                                        <a href="<?php echo esc_url( $edit_url ); ?>" target="_blank" class="lpl-pla__builder-edit-link">
                                            <?php esc_html_e( 'Edit', 'vapfem' ); ?>
                                        </a>
                                        <button type="button" class="lpl-pla__builder-remove-btn" title="<?php echo esc_attr__( 'Remove', 'vapfem' ); ?>">&#215;</button>
                                    </div>
                                    <input type="hidden" name="_playlist_items[<?php echo esc_attr( $index ); ?>][id]" value="<?php echo esc_attr( $player_id ); ?>" />
                                </div>
                            <?php
                                $index++;
                            endforeach;
                            ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- .lpl-pla__builder-columns -->
        </div><!-- .lpl-pla__builder -->
        <?php
    }

    /**
     * Render the shortcode sidebar metabox
     *
     * @param \WP_Post $post Post object
     */
    public function render_shortcode_metabox( $post ) {
        if ( ! $post->ID || $post->post_status === 'auto-draft' ) {
            ?>
            <p class="description">
                <?php echo esc_html__( 'Save the playlist to generate the shortcode.', 'vapfem' ); ?>
            </p>
            <?php
            return;
        }

        $shortcode = '[lean_playlist id="' . esc_attr( $post->ID ) . '"]';
        ?>
        <div class="lpl-shortcode-metabox">
            <div class="lex-field" style="display: flex; gap: 5px; margin-bottom: 10px; margin-top: 15px;">
                <input
                    type="text"
                    readonly
                    value="<?php echo esc_attr( $shortcode ); ?>"
                    class="regular-text"
                    onclick="this.select();"
                    style="flex: 1;"
                />
                <button
                    type="button"
                    class="lex-copy-button"
                    data-lex-copy="<?php echo esc_attr( $shortcode ); ?>"
                    style="width: 70px; flex-shrink: 0;"
                >
                    <?php echo esc_html__( 'Copy', 'vapfem' ); ?>
                </button>
            </div>

            <p class="description">
                <span class="dashicons dashicons-info" style=""></span>
                <?php echo esc_html__( 'Paste this shortcode into any page, post, or widget to display the playlist.', 'vapfem' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render the Display Options sidebar metabox.
     *
     * @param \WP_Post $post Post object
     */
    public function render_display_options_metabox( $post ) {
        $defaults = leanpl_get_playlist_defaults();
        $settings = \Lex\Settings\V2\Settings::getInstance( 'leanpl' );
        $fr       = $settings->fieldRenderer;

        $val = [];
        foreach ( leanpl_get_playlist_meta_field_schema() as $field => $def ) {
            $meta_key  = '_playlist_' . $field;
            $is_nullable = ! empty( $def['nullable'] );

            if ( $is_nullable ) {
                // get_post_meta() returns [] when key absent, [''] when explicitly saved empty.
                $all = get_post_meta( $post->ID, $meta_key );
                $val[ $field ] = ! empty( $all ) ? $all[0] : ( $defaults[ $field ] ?? '' );
            } else {
                $saved         = get_post_meta( $post->ID, $meta_key, true );
                $val[ $field ] = $saved !== '' ? $saved : ( $defaults[ $field ] ?? '' );
            }
        }

        $playlist_type = get_post_meta( $post->ID, '_playlist_type', true ) ?: 'video';
        $preset_fields = array_keys( leanpl_get_playlist_meta_field_schema() );
        $preset_ids    = $playlist_type === 'audio'
            ? [ 'audio-style-1', 'audio-style-2', 'audio-style-3', 'audio-style-4' ]
            : [ 'video-style-1', 'video-style-2', 'video-style-3', 'video-style-4', 'video-style-5' ];
        $presets_js    = [];
        $presets_menu  = [];
        foreach ( $preset_ids as $preset_id ) {
            $preset = leanpl_get_playlist_preset( $preset_id );
            if ( empty( $preset ) ) { continue; }
            $entry = [];
            foreach ( $preset_fields as $f ) {
                $entry[ $f ] = isset( $preset[ $f ] ) ? (string) $preset[ $f ] : ( (string) ( $defaults[ $f ] ?? '' ) );
            }
            $presets_js[ $preset_id ]   = $entry;
            $presets_menu[ $preset_id ] = $preset['label'] ?? $preset_id;
        }
        ?>
        <div class="lpl-pla__display-options">

            <script id="lpl-pla-do-presets" type="application/json"><?php echo wp_json_encode( $presets_js ); ?></script>

            <p class="lpl-pla__display-options-preset-instruction">
                <span class="dashicons dashicons-info-outline" aria-hidden="true"></span>
                <?php esc_html_e( 'Choose a ready-made style as your starting point, hit Apply, then tweak anything you like.', 'vapfem' ); ?>
            </p>

            <div class="lpl-pla__display-options-preset-row">
                <label for="lpl-pla-do-preset" class="lpl-pla__display-options-preset-label"><?php esc_html_e( 'Quick Start:', 'vapfem' ); ?></label>
                <select id="lpl-pla-do-preset" class="lpl-pla__display-options-preset-select">
                    <option value="" disabled selected><?php esc_html_e( 'Choose a style...', 'vapfem' ); ?></option>
                    <?php foreach ( $presets_menu as $pid => $plabel ) : ?>
                        <option value="<?php echo esc_attr( $pid ); ?>"><?php echo esc_html( $plabel ); ?></option>
                    <?php endforeach; ?>
                    <option value="custom" style="display:none;"><?php esc_html_e( 'Custom', 'vapfem' ); ?></option>
                </select>
                <button type="button" id="lpl-pla-do-apply" class="button"><?php esc_html_e( 'Apply', 'vapfem' ); ?></button>
            </div>

            <hr class="lpl-pla__display-options-divider">

            <div class="lex-vtabs lex-vtabs--apple" data-tab="playlist-do" data-storage-suffix="<?php echo esc_attr( $post->ID ); ?>">

                <?php
                $tabs = [
                    [
                        'id'    => 'a-layout',
                        'label' => __( 'Layout', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'layout_svg' ),
                    ],
                    [
                        'id'    => 'a-appearance',
                        'label' => __( 'Appearance', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'appearance_svg' ),
                    ],
                    [
                        'id'    => 'a-header',
                        'label' => __( 'Header', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'header_svg' ),
                    ],
                    [
                        'id'    => 'a-items',
                        'label' => __( 'List Items', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'items_svg' ),
                    ],
                    [
                        'id'    => 'a-audio',
                        'label' => __( 'Audio', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'audio_svg' ),
                    ],
                    [
                        'id'    => 'a-behavior',
                        'label' => __( 'Behavior', 'vapfem' ),
                        'icon'  => leanpl_ssot( 'icons', 'behavior_svg' ),
                    ],
                ];
                echo \Lex\Settings\V2\Services\Vtabs::render_nav_flat( $tabs );
                ?>

                <div class="lex-vtabs__content">

                    <div class="lex-vtab-pane" data-vtab="a-layout">
                        <table class="form-table" style="margin-top:0;">
                            <?php
                            $fr->render( 'select', '_playlist_position', [
                                'label'   => esc_html__( 'Where should the playlist appear?', 'vapfem' ),
                                'value'   => $val['position'],
                                'options' => [
                                    'right'  => __( 'Right', 'vapfem' ),
                                    'left'   => __( 'Left', 'vapfem' ),
                                    'bottom' => __( 'Bottom', 'vapfem' ),
                                    'top'    => __( 'Top', 'vapfem' ),
                                ],
                            ] );
                            $fr->render( 'radio', '_playlist_item_template', [
                                'label'       => esc_html__( 'How should items look?', 'vapfem' ),
                                'desc'        => esc_html__( 'List shows one item per row. Grid shows items as cards. Grid only applies when position is Bottom or Top.', 'vapfem' ),
                                'value'       => $val['item_template'],
                                'options'     => [
                                    'list' => __( 'List', 'vapfem' ),
                                    'grid' => __( 'Grid', 'vapfem' ),
                                ],
                                'pro_options' => [ 'grid' ],
                                'pro'         => [ 'onclick' => 'openUpgradeModal', 'badge_position' => false ],
                            ] );
                            $fr->render( 'number', '_playlist_grid_columns', [
                                'label'    => esc_html__( 'How many columns?', 'vapfem' ),
                                'desc'     => esc_html__( 'Only applies when layout is Grid.', 'vapfem' ),
                                'value'    => $val['grid_columns'] ?: 4,
                                'min'      => 2,
                                'max'      => 6,
                                'step'     => 1,
                                'disabled' => true,
                                'pro'      => [ 'onclick' => 'openUpgradeModal' ],
                            ] );
                            $fr->render( 'text', '_playlist_panel_width', [
                                'label' => esc_html__( 'Panel width', 'vapfem' ),
                                'desc'  => esc_html__( 'Width of the playlist panel when position is Left or Right. Example: 360px or 30%.', 'vapfem' ),
                                'value' => $val['panel_width'],
                            ] );
                            $fr->render( 'text', '_playlist_max_width', [
                                'label' => esc_html__( 'Max width', 'vapfem' ),
                                'desc'  => esc_html__( 'Maximum width of the entire playlist (player + panel). Use 100% to fill the container, or a fixed value like 900px.', 'vapfem' ),
                                'value' => $val['max_width'],
                            ] );
                            $fr->render( 'select', '_playlist_playlist_height_mode', [
                                'label'   => esc_html__( 'Panel height mode', 'vapfem' ),
                                'desc'    => esc_html__( 'Sync makes the panel the same height as the player. Fixed lets you set a maximum height manually.', 'vapfem' ),
                                'value'   => $val['playlist_height_mode'],
                                'options' => [
                                    'sync'  => __( 'Sync: follow player height', 'vapfem' ),
                                    'fixed' => __( 'Fixed: set a max height', 'vapfem' ),
                                ],
                            ] );
                            $fr->render( 'text', '_playlist_panel_max_height', [
                                'label' => esc_html__( 'Panel max height', 'vapfem' ),
                                'desc'  => esc_html__( 'Only applies when Panel height mode is Fixed. Example: 400px.', 'vapfem' ),
                                'value' => $val['panel_max_height'],
                            ] );
                            ?>
                        </table>
                    </div>

                    <div class="lex-vtab-pane" data-vtab="a-appearance">
                        <?php
                        $settings->sectionRenderer->startSection( 'playlist-appearance', esc_html__( 'Appearance', 'vapfem' ), [
                            'disable_save_button' => true,
                            'accordion'           => true,
                            'is_pro'              => true,
                            'summary_labels'      => [ esc_html__( 'Skin', 'vapfem' ), esc_html__( 'Accent Color', 'vapfem' ), esc_html__( 'Background', 'vapfem' ) ],
                        ] );
                        $fr->render( 'select', '_playlist_skin', [
                            'label'    => esc_html__( 'Skin', 'vapfem' ),
                            'desc'     => esc_html__( 'Light is best for light-background pages. Dark works well embedded in dark layouts.', 'vapfem' ),
                            'value'    => $val['skin'],
                            'options'  => [
                                'default' => __( 'Light', 'vapfem' ),
                                'dark'    => __( 'Dark', 'vapfem' ),
                            ],
                            'disabled' => true,
                            'pro'      => [ 'onclick' => 'openUpgradeModal', 'badge_position' => false ],
                        ] );
                        $fr->render( 'color', '_playlist_accent_color', [
                            'label'    => esc_html__( 'Accent color', 'vapfem' ),
                            'value'    => $val['accent_color'],
                            'disabled' => true,
                            'pro'      => [ 'onclick' => 'openUpgradeModal', 'badge_position' => false ],
                        ] );
                        $fr->render( 'select', '_playlist_bg_style', [
                            'label'    => esc_html__( 'Background style', 'vapfem' ),
                            'desc'     => esc_html__( 'Controls the background of the playlist panel.', 'vapfem' ),
                            'value'    => $val['bg_style'],
                            'options'  => [
                                ''         => __( 'Solid (use skin color)', 'vapfem' ),
                                'gradient' => __( 'Custom gradient', 'vapfem' ),
                            ],
                            'disabled' => true,
                            'pro'      => [ 'onclick' => 'openUpgradeModal', 'badge_position' => false ],
                        ] );
                        $fr->render( 'textarea', '_playlist_gradient_css', [
                            'label'       => esc_html__( 'Gradient CSS', 'vapfem' ),
                            'desc'        => esc_html__( 'Only applies when Background style is Custom gradient. Paste a value from cssgradient.io.', 'vapfem' ),
                            'value'       => $val['gradient_css'],
                            'rows'        => 2,
                            'placeholder' => 'linear-gradient(135deg, #1a1a2e, #16213e)',
                            'disabled'    => true,
                            'pro'         => [ 'onclick' => 'openUpgradeModal', 'badge_position' => false ],
                        ] );
                        $settings->sectionRenderer->endSection();
                        ?>
                    </div>

                    <div class="lex-vtab-pane" data-vtab="a-header">
                        <table class="form-table" style="margin-top:0;">
                            <?php
                            $fr->render( 'checkbox', '_playlist_show_header', [
                                'label'          => esc_html__( 'Show playlist header?', 'vapfem' ),
                                'value'          => $val['show_header'],
                                'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
                            ] );
                            $fr->render( 'text', '_playlist_header_text', [
                                'label'       => esc_html__( 'Title text', 'vapfem' ),
                                'desc'        => esc_html__( 'Only shown when "Show playlist title?" is on.', 'vapfem' ),
                                'value'       => $val['header_text'],
                                'placeholder' => esc_html__( 'e.g. Playlist, Up Next, Episodes...', 'vapfem' ),
                            ] );
                            $fr->render( 'checkbox', '_playlist_show_count', [
                                'label'          => esc_html__( 'Show item count?', 'vapfem' ),
                                'value'          => $val['show_count'],
                                'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
                            ] );
                            $fr->render( 'text', '_playlist_count_label', [
                                'label'       => esc_html__( 'Count label', 'vapfem' ),
                                'desc'        => esc_html__( 'The word after the number, e.g. "Videos" shows as "4 Videos". Only shown when "Show item count?" is on.', 'vapfem' ),
                                'value'       => $val['count_label'],
                                'placeholder' => esc_html__( 'e.g. Videos, Tracks, Episodes...', 'vapfem' ),
                            ] );
                            ?>
                        </table>
                    </div>

                    <div class="lex-vtab-pane" data-vtab="a-items">
                        <table class="form-table" style="margin-top:0;">
                            <?php
                            $fr->render( 'checkbox', '_playlist_show_thumbnails', [
                                'label'          => esc_html__( 'Show thumbnails?', 'vapfem' ),
                                'value'          => $val['show_thumbnails'],
                                'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
                            ] );
                            $fr->render( 'info', '_playlist_auto_thumbnail_notice', [
                                'variant' => 'notice',
                                'content' => __( '<strong>Pro:</strong> Auto-fetch thumbnails from YouTube and Vimeo when no custom image is set.<br>Enable in <strong>Settings &rsaquo; Playlist &rsaquo; Automatic Thumbnail</strong>.', 'vapfem' ),
                            ] );
                            $fr->render( 'select', '_playlist_thumb_ratio', [
                                'label'   => esc_html__( 'Thumbnail shape', 'vapfem' ),
                                'value'   => $val['thumb_ratio'],
                                'options' => [
                                    '16/9' => __( '16:9 (Widescreen)', 'vapfem' ),
                                    '1/1'  => __( '1:1 (Square)', 'vapfem' ),
                                ],
                            ] );
                            $fr->render( 'checkbox', '_playlist_show_duration_badge', [
                                'label'          => esc_html__( 'Show duration badge on thumbnail?', 'vapfem' ),
                                'desc'           => esc_html__( 'Only applies when thumbnails are shown.', 'vapfem' ),
                                'value'          => $val['show_duration_badge'],
                                'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
                            ] );
                            $fr->render( 'checkbox', '_playlist_show_duration_in_list', [
                                'label'          => esc_html__( 'Show duration next to title?', 'vapfem' ),
                                'value'          => $val['show_duration_in_list'],
                                'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
                            ] );
                            $fr->render( 'checkbox', '_playlist_show_numbers', [
                                'label'          => esc_html__( 'Show item numbers?', 'vapfem' ),
                                'value'          => $val['show_numbers'],
                                'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
                            ] );
                            $fr->render( 'checkbox', '_playlist_show_meta', [
                                'label'          => esc_html__( 'Show meta text (channel, author, artist)?', 'vapfem' ),
                                'value'          => $val['show_meta'],
                                'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
                            ] );
                            $fr->render( 'radio', '_playlist_play_icon', [
                                'label'       => esc_html__( 'Play icon', 'vapfem' ),
                                'desc'        => esc_html__( 'Controls where the play/pause icon appears. "Active item only" shows it only on the current item. "Always" shows it on all items.', 'vapfem' ),
                                'value'       => $val['play_icon'],
                                'options'     => [
                                    'active_only' => __( 'Active item only', 'vapfem' ),
                                    'always'      => __( 'Always', 'vapfem' ),
                                    'hidden'      => __( 'Hidden', 'vapfem' ),
                                ],
                                'pro_options' => [ 'hidden' ],
                                'pro'         => [ 'onclick' => 'openUpgradeModal', 'badge_position' => false ],
                            ] );
                            ?>
                        </table>
                    </div>

                    <div class="lex-vtab-pane" data-vtab="a-audio">
                        <table class="form-table" style="margin-top:0;">
                            <?php
                            $fr->render( 'radio', '_playlist_now_playing_style', [
                                'label'       => esc_html__( 'Now playing display', 'vapfem' ),
                                'desc'        => esc_html__( 'Only affects audio playlists. Compact shows a small strip with title and thumbnail. Large shows a portrait card above the controls.', 'vapfem' ),
                                'value'       => $val['now_playing_style'],
                                'options'     => [
                                    'compact' => __( 'Compact', 'vapfem' ),
                                    'large'   => __( 'Large', 'vapfem' ),
                                ],
                                'pro_options' => [ 'large' ],
                                'pro'         => [ 'onclick' => 'openUpgradeModal', 'badge_position' => false ],
                            ] );
                            ?>
                        </table>
                    </div>

                    <div class="lex-vtab-pane" data-vtab="a-behavior">
                        <table class="form-table" style="margin-top:0;">
                            <?php
                            $fr->render( 'checkbox', '_playlist_autoplay_next', [
                                'label'          => esc_html__( 'Automatically play next item?', 'vapfem' ),
                                'desc'           => esc_html__( 'When the current item finishes, the next one starts automatically.', 'vapfem' ),
                                'value'          => $val['autoplay_next'],
                                'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
                            ] );
                            $fr->render( 'number', '_playlist_start_item', [
                                'label'    => esc_html__( 'Start from which item?', 'vapfem' ),
                                'desc'     => esc_html__( 'The item number that is active when the playlist first loads. 1 = first item.', 'vapfem' ),
                                'value'    => $val['start_item'] ?: 1,
                                'min'      => 1,
                                'step'     => 1,
                                'disabled' => true,
                                'pro'      => [ 'onclick' => 'openUpgradeModal' ],
                            ] );
                            ?>
                        </table>
                    </div>

                </div><!-- .lex-vtabs__content -->
            </div><!-- .lex-vtabs -->

        </div>
        <?php
    }

    /**
     * Render the "What kind of playlist?" modal.
     * Output only on the lean_playlist list table screen.
     */
    public function render_type_modal() {
        $screen = get_current_screen();
        if ( ! $screen || ! in_array( $screen->id, [ 'edit-lean_playlist', 'lean_playlist' ], true ) ) {
            return;
        }
        leanpl_render_type_modal_html( [
            'modal_id'    => 'lpl-pla-builder-modal',
            'backdrop_id' => 'lpl-pla-builder-modal-backdrop',
            'close_id'    => 'lpl-pla-builder-modal-close',
            'title'       => __( 'What kind of playlist?', 'vapfem' ),
            'sub'         => __( 'Choose once. This determines which players appear in the builder.', 'vapfem' ),
            'cards'       => [
                [
                    'id'    => 'lpl-pla-builder-type-video',
                    'type'  => 'video',
                    'svg'   => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="1.75" fill="none"/><path d="M10 9l5 3-5 3V9z" fill="currentColor"/></svg>',
                    'title' => __( 'Video Playlist', 'vapfem' ),
                    'desc'  => __( 'YouTube, Vimeo, or self-hosted video files', 'vapfem' ),
                ],
                [
                    'id'    => 'lpl-pla-builder-type-audio',
                    'type'  => 'audio',
                    'svg'   => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 18V6l12-2v12" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="1.75" fill="none"/><circle cx="18" cy="16" r="3" stroke="currentColor" stroke-width="1.75" fill="none"/></svg>',
                    'title' => __( 'Audio Playlist', 'vapfem' ),
                    'desc'  => __( 'Music, podcasts, or audiobooks', 'vapfem' ),
                ],
            ],
        ] );
    }
}

Playlist_Metaboxes::get_instance();
