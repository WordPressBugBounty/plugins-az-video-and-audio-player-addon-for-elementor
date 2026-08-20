<?php
namespace LeanPL;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Player Preview Class
 * 
 * Handles preview functionality for player posts
 */
class Player_Preview {
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('post_submitbox_misc_actions', [$this, 'render_preview_button']);
        add_filter('template_include', [$this, 'swap_preview_template']);
        add_filter('post_row_actions', [$this, 'modify_row_actions'], 10, 2);
        add_action('edit_form_after_title', [$this, 'render_live_preview_panel']);
    }

    /**
     * Render preview button in submit box
     * 
     * @param WP_Post $post Post object
     * @return void
     */
    public function render_preview_button($post) {
        if ( ! in_array( $post->post_type, [ 'lean_player', 'lean_playlist' ], true ) ) {
            return;
        }

        $is_published = in_array( $post->post_status, [ 'publish', 'draft' ], true );
        $button_text  = esc_html__( 'Live Preview', 'vapfem' );

        $preview_link = get_preview_post_link(
            $post->ID,
            [
                'preview'       => 'true',
                'preview_id'    => $post->ID,
                'preview_nonce' => wp_create_nonce( 'post_preview_' . $post->ID ),
            ]
        );

        // Both post types: button triggers the always-visible inline panel
        // injected below the title (render_live_preview_panel()) instead of
        // opening a new tab - same trigger the panel's own "View Live
        // Preview" button uses (live-preview.js).
        $is_inline_trigger = in_array( $post->post_type, [ 'lean_player', 'lean_playlist' ], true );
        ?>
        <div class="misc-pub-section lpl-preview-metabox">
            <a
                class="preview button <?php echo esc_attr( $is_published ? '' : 'disabled' ); ?>"
                href="<?php echo esc_url( $preview_link ); ?>"
                <?php echo $is_inline_trigger ? '' : ' target="_blank"'; ?>
                <?php if ( $is_inline_trigger ) : ?>
                    id="lpl-preview-toggle"
                    aria-controls="lpl-live-preview-panel"
                <?php endif; ?>
            >
                <span class="dashicons dashicons-visibility"></span>
                <?php echo esc_html( $button_text ); ?>
            </a>

            <?php if ( ! $is_published ) : ?>
                <p class="description" style="margin-top: 5px;">
                    <?php echo esc_html__( 'Publish first to enable preview.', 'vapfem' ); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Always-visible panel injected right below the title field on the
     * lean_player / lean_playlist edit screen. Starts as just a "View Live
     * Preview" call to action (no AJAX call yet, so opening the editor never
     * fires one unasked) - clicking it, or the Publish-box "Live Preview"
     * button (render_preview_button(), same trigger), renders the real
     * player/playlist via AJAX and engages live refresh on further field
     * changes (live-preview.js).
     *
     * @param WP_Post $post Post object.
     * @return void
     */
    public function render_live_preview_panel( $post ) {
        if ( ! isset( $post->post_type ) || ! in_array( $post->post_type, [ 'lean_player', 'lean_playlist' ], true ) ) {
            return;
        }

        wp_localize_script( 'leanpl-live-preview', 'leanplLivePreviewData', [
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'leanpl_live_preview' ),
            'postType' => $post->post_type,
        ] );
        ?>
        <div id="lpl-live-preview-panel" class="lpl-live-preview-panel">
            <div class="lpl-live-preview-panel__inner" id="lpl-live-preview-panel-inner">
                <button type="button" id="lpl-preview-trigger" class="button button-primary">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php esc_html_e( 'View Live Preview', 'vapfem' ); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Swap in a bare, theme-agnostic template for lean_player/lean_playlist
     * previews instead of the active theme's single template. Renders the
     * real shortcode with no theme header/footer/sidebar involved at all,
     * so it looks the same on every theme rather than depending on a CSS
     * hack to hide whatever chrome that theme happens to use.
     *
     * @param string $template Template path WP would otherwise load.
     * @return string
     */
    public function swap_preview_template( $template ) {
        if ( ! is_preview() || ! in_array( get_post_type(), [ 'lean_player', 'lean_playlist' ], true ) ) {
            return $template;
        }

        return LEANPL_DIR . '/includes/templates/preview-bare.php';
    }

    public function modify_row_actions($actions, $post) {
        if ( ! in_array( $post->post_type, [ 'lean_player', 'lean_playlist' ], true ) ) {
            return $actions;
        }

        // Remove "View" action and "Edit" action
        unset($actions['edit']);
        unset($actions['view']);

        // Generate preview link
        $preview_link = get_preview_post_link(
            $post->ID,
            [
                'preview'       => 'true',
                'preview_id'    => $post->ID,
                'preview_nonce' => wp_create_nonce( 'post_preview_' . $post->ID ),
            ]
        );

        $is_published = in_array( $post->post_status, [ 'publish', 'draft' ], true );

        if ( $is_published ) {
            $aria_label = $post->post_type === 'lean_playlist'
                /* translators: %s is the playlist title */
                ? sprintf( __( 'Preview Playlist: %s', 'vapfem' ), get_the_title( $post->ID ) )
                /* translators: %s is the player title */
                : sprintf( __( 'Preview Player: %s', 'vapfem' ), get_the_title( $post->ID ) );

            array_unshift( $actions, sprintf(
                '<a href="%s" target="_blank" aria-label="%s">%s</a>',
                esc_url( $preview_link ),
                esc_attr( $aria_label ),
                esc_html__( 'Preview', 'vapfem' )
            ) );
        }

        return $actions;
    }
}

Player_Preview::get_instance();

