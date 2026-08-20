<?php
namespace LeanPL;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Injects the Custom Preset modal shell next to the player_layout field.
 *
 * Listens on the generic lex_settings/after_field_render hook (fires for every
 * field, every type) and no-ops unless the field being rendered is one of the
 * three layout pickers this plugin has: the per-player metabox (_player_layout),
 * global Settings (player_layout), or the playlist per-item override
 * (_playlist_player_layout). The admin-new redesign renders its layout grid
 * through its own partial (edit-layout-grid.php) rather than the lex_settings
 * field renderer, so those two screens call maybe_inject() directly instead of
 * relying on the hook - see html-player-edit-new-page.php /
 * html-playlist-edit-new-page.php.
 */
class Custom_Preset_Injector {

    private static $instance = null;

    /** Modal shell is a single instance per page - guard against printing it twice. */
    private $modal_printed = false;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'lex_settings/after_field_render', [ $this, 'maybe_inject' ], 10, 3 );
    }

    /**
     * @param string $field_key    Field key as passed to FieldRenderer::render() -
     *                              doubles as the literal meta key (post meta, if
     *                              it starts with '_') or site option key (else)
     *                              the resolved value/preset-ref both read and
     *                              write, so every layout picker this plugin has
     *                              works off the same code path with no per-field
     *                              special-casing.
     * @param array  $field_config Merged/filtered field config (unused here).
     * @param array  $context      Original $properties - carries 'post_id' for metabox fields.
     */
    public function maybe_inject( $field_key, $field_config, $context ) {
        if ( ! in_array( $field_key, [ '_player_layout', 'player_layout', '_playlist_player_layout' ], true ) ) {
            return;
        }

        $post_id = ( is_array( $context ) && ! empty( $context['post_id'] ) ) ? (int) $context['post_id'] : 0;

        $presets = get_option( Custom_Preset_Ajax::OPTION_KEY, [] );
        if ( ! is_array( $presets ) ) {
            $presets = [];
        }

        $this->render_preset_ref_input( $field_key, $post_id );

        if ( ! $this->modal_printed ) {
            // Global Settings has no single player/playlist to be contextual
            // about - video. Metabox/admin-new: match the actual thing being
            // edited, so the preview (and "Start from" seed) reflects buttons
            // that can actually show up.
            $player_type = 'video';
            if ( $post_id > 0 ) {
                $type_meta_key = ( '_playlist_player_layout' === $field_key ) ? '_playlist_type' : '_player_type';
                $stored_type   = get_post_meta( $post_id, $type_meta_key, true );
                if ( in_array( $stored_type, [ 'video', 'audio' ], true ) ) {
                    $player_type = $stored_type;
                }
            }

            $applied = $this->resolve_applied_preset( $field_key, $post_id, $presets );
            $this->localize_builder_data( $field_key, $presets, $player_type, $applied );
            $this->render_modal_shell( $post_id, $presets, $player_type, $applied );
            $this->modal_printed = true;
        }
    }

    /**
     * 'empty' (a real layout, or nothing chosen), 'applied' (a preset id
     * that's still in the library), or 'deleted' (a preset id no longer
     * there). Lock A11 is a live reference, not stamp-and-step-away: the
     * layout field itself IS the applied-preset pointer, there is no
     * separate breadcrumb key to drift out of sync with it.
     */
    private function resolve_applied_preset( $field_key, $post_id, $presets ) {
        $raw = $this->get_raw_value( $field_key, $post_id );

        if ( strpos( (string) $raw, 'preset_' ) !== 0 ) {
            return [ 'id' => '', 'state' => 'empty', 'name' => '' ];
        }

        if ( isset( $presets[ $raw ] ) ) {
            return [ 'id' => $raw, 'state' => 'applied', 'name' => $presets[ $raw ]['name'] ?? '' ];
        }

        return [ 'id' => $raw, 'state' => 'deleted', 'name' => '' ];
    }

    /**
     * Meta keys (the per-player/per-playlist pickers) start with '_' and live
     * on $post_id; the site-wide picker ('player_layout') has no post to be
     * on and lives in the options table instead - same rule both callers
     * below share, so the two never drift into checking $post_id instead.
     */
    private function get_raw_value( $field_key, $post_id ) {
        return ( 0 === strpos( $field_key, '_' ) )
            ? get_post_meta( $post_id, $field_key, true )
            : leanpl_get_option( $field_key, '' );
    }

    /**
     * The ONLY thing "apply" writes: a hidden input carrying the preset id
     * into the layout field itself (Lock A11, live reference - Player_Renderer
     * resolves the preset's layout + controls live on every render, see
     * class-player-renderer.php). Only rendered when the current value
     * actually IS a preset reference - if it's a real layout, this input must
     * not exist at all, or its (even empty) value would win over the real
     * pick's value at save time (both share the same field name; last
     * non-radio write wins).
     */
    private function render_preset_ref_input( $field_key, $post_id ) {
        $raw = $this->get_raw_value( $field_key, $post_id );

        if ( strpos( (string) $raw, 'preset_' ) !== 0 ) {
            return;
        }
        ?>
        <input type="hidden" class="lpl-cpm__preset-ref" name="<?php echo esc_attr( $field_key ); ?>" value="<?php echo esc_attr( $raw ); ?>" />
        <?php
    }

    /**
     * Hands the builder its static data: the full controls registry (key =>
     * label, in registry order), per layout that owns a fixed controls array
     * (Modern/Simple/Minimal) that array's video list - the seed for "Start
     * from" (Classic/Floating have no entry: JS falls back to the registry's
     * own 'default' flags), the saved preset library, and what JS needs to
     * call the save AJAX action itself.
     */
    private function localize_builder_data( $field_key, $presets, $player_type, $applied ) {
        $registry = function_exists( 'leanpl_get_controls_registry' ) ? leanpl_get_controls_registry() : [];

        $controls = [];
        $defaults = [];
        foreach ( $registry as $control_key => $control ) {
            $controls[ $control_key ] = $control['label'];
            $defaults[ $control_key ] = ! empty( $control['default'] );
        }

        $layout_controls = [];
        foreach ( leanpl_get_player_layouts() as $layout_key => $layout ) {
            if ( ! empty( $layout['controls'][ $player_type ] ) ) {
                $layout_controls[ $layout_key ] = $layout['controls'][ $player_type ];
            }
        }

        wp_localize_script( 'leanpl-custom-preset-builder', 'leanplCustomPresetData', [
            'fieldName'       => $field_key,
            'controls'        => $controls,
            'controlDefaults' => $defaults,
            'layoutControls'  => $layout_controls,
            'presets'         => $presets,
            'playerType'      => $player_type,
            'appliedId'       => $applied['id'],
            'appliedState'    => $applied['state'],
            'appliedName'     => $applied['name'],
            'isPro'           => leanpl_is_pro_active(),
            'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
            'saveNonce'       => wp_create_nonce( 'leanpl_custom_preset_admin' ),
            'newPresetLabel'  => __( 'New Preset', 'vapfem' ),
            'deleteConfirmLabel' => __( 'Delete this preset?', 'vapfem' ),
        ] );
    }

    private function render_modal_shell( $post_id, $presets, $player_type, $applied ) {
        $registry = function_exists( 'leanpl_get_controls_registry' ) ? leanpl_get_controls_registry() : [];
        $is_pro   = leanpl_is_pro_active();
        ?>
        <div id="lpl-custom-preset-modal" class="lex-modal-backdrop lpl-cpm-backdrop" aria-hidden="true" data-post-id="<?php echo esc_attr( $post_id ); ?>">
            <div class="lex-modal lpl-cpm" role="dialog" aria-modal="true" aria-labelledby="lpl-cpm-title">
                <div class="lex-modal__header">
                    <h3 id="lpl-cpm-title"><?php esc_html_e( 'Custom Presets', 'vapfem' ); ?></h3>
                    <button type="button" class="lex-modal__close" aria-label="<?php echo esc_attr__( 'Close', 'vapfem' ); ?>" onclick="closeUpgradeModal()">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="lex-modal__content lpl-cpm__content">
                    <div class="lpl-cpm__view" data-view="library">
                        <div class="lpl-cpm__library-grid">
                            <?php foreach ( $presets as $preset_id => $preset ) : ?>
                                <?php $card_class = ( $preset_id === $applied['id'] ) ? 'lpl-cpm__preset-card lpl-cpm__preset-card--applied' : 'lpl-cpm__preset-card'; ?>
                                <div class="<?php echo esc_attr( $card_class ); ?>" data-preset-id="<?php echo esc_attr( $preset_id ); ?>">
                                    <span class="lpl-cpm__preset-card-name"><?php echo esc_html( $preset['name'] ?? '' ); ?></span>
                                    <span class="lpl-cpm__preset-card-actions">
                                        <button type="button" class="lpl-cpm__preset-card-action" data-action="edit-preset" aria-label="<?php echo esc_attr__( 'Edit', 'vapfem' ); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button type="button" class="lpl-cpm__preset-card-action" data-action="delete-preset" aria-label="<?php echo esc_attr__( 'Delete', 'vapfem' ); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                            <button type="button" class="lpl-cpm__new-card" data-action="new-preset">
                                <span class="dashicons dashicons-plus-alt2"></span>
                                <span><?php esc_html_e( 'New Preset', 'vapfem' ); ?></span>
                            </button>
                        </div>
                        <?php if ( ! $is_pro ) : ?>
                            <div class="lpl-cpm__notice">
                                <p><?php esc_html_e( "You're exploring the Custom Preset builder for demonstration purposes. Assigning to a player: PRO only.", 'vapfem' ); ?></p>
                                <button type="button" class="button button-primary lpl-cpm__notice-cta" onclick="openUpgradeModal(); return false;">
                                    <?php esc_html_e( 'Upgrade to PRO', 'vapfem' ); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="lpl-cpm__view" data-view="builder" hidden>
                        <div class="lpl-cpm__builder">
                            <div class="lpl-cpm__builder-top">
                                <p class="lpl-cpm__field">
                                    <label for="lpl-cpm-name"><?php esc_html_e( 'Name', 'vapfem' ); ?></label>
                                    <input type="text" id="lpl-cpm-name" class="lpl-cpm__name-input" placeholder="<?php esc_attr_e( 'My Preset', 'vapfem' ); ?>" required />
                                </p>
                                <p class="lpl-cpm__field">
                                    <label for="lpl-cpm-start-from"><?php esc_html_e( 'Start from', 'vapfem' ); ?></label>
                                    <select id="lpl-cpm-start-from" class="lpl-cpm__start-from">
                                        <?php foreach ( leanpl_get_player_layouts() as $layout_key => $layout ) : ?>
                                            <option value="<?php echo esc_attr( $layout_key ); ?>"><?php echo esc_html( $layout['label'] ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </p>
                            </div>
                            <div class="lpl-cpm__builder-bottom">
                                <div class="lpl-cpm__builder-preview-col">
                                    <span class="lpl-cpm__field-label"><?php esc_html_e( 'Player Preview', 'vapfem' ); ?></span>
                                    <?php
                                    // Layout-specific control-bar styling (Modern's rewind/play/fast-forward
                                    // row, Simple's minimal bar, etc.) is driven entirely by CSS keyed off
                                    // data-lpl-player-layout on a .lpl-player-wrap ancestor (main.css) - the
                                    // real front-end markup always carries both (class-player-renderer.php).
                                    // Without them here the preview renders stock unstyled Plyr regardless of
                                    // which controls are checked. JS keeps the attribute's value in sync with
                                    // the "Start from" select on every re-render (renderPreview()).
                                    $preview_wrap_class = 'lpl-cpm__builder-preview lpl-player-wrap ' . ( $player_type === 'audio' ? 'lpl-audio' : 'lpl-video' );
                                    ?>
                                    <div class="<?php echo esc_attr( $preview_wrap_class ); ?>" data-lpl-player-layout="<?php echo esc_attr( array_key_first( leanpl_get_player_layouts() ) ); ?>">
                                        <?php if ( $player_type === 'audio' ) : ?>
                                            <audio id="lpl-cpm-preview-media" class="lpl-cpm__preview-media">
                                                <source src="https://download.samplelib.com/mp3/sample-15s.mp3" type="audio/mp3" />
                                            </audio>
                                        <?php else : ?>
                                            <video id="lpl-cpm-preview-media" class="lpl-cpm__preview-media" playsinline>
                                                <source src="https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4" type="video/mp4" />
                                            </video>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="lpl-cpm__field lpl-cpm__builder-controls-col">
                                    <span class="lpl-cpm__field-label"><?php esc_html_e( 'Controls', 'vapfem' ); ?></span>
                                    <div class="lex-sortable-checkbox-container lpl-cpm__controls-list">
                                        <input type="hidden" class="lex-sortable-order-field lpl-cpm__controls-order" value="<?php echo esc_attr( implode( ',', array_keys( $registry ) ) ); ?>" />
                                        <?php foreach ( $registry as $control_key => $control ) : ?>
                                            <div class="lex-sortable-checkbox-item" data-value="<?php echo esc_attr( $control_key ); ?>" draggable="true">
                                                <span class="lex-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'vapfem' ); ?>">⋮⋮</span>
                                                <label>
                                                    <input type="checkbox" class="lex-checkbox-item" <?php checked( ! empty( $control['default'] ) ); ?> />
                                                    <?php echo esc_html( $control['label'] ); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="lpl-cpm__builder-actions">
                                <button type="button" class="button" data-action="back-to-library"><?php esc_html_e( 'Back', 'vapfem' ); ?></button>
                                <button type="button" class="button button-primary" data-action="save-preset"><?php esc_html_e( 'Save Preset', 'vapfem' ); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
