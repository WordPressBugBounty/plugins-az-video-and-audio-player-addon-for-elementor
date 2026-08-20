<?php
/**
 * Renderer — orchestrator, builds deps and loads templates.
 *
 * Not self-initialized: request-scoped, created per shortcode render by Playlist.
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Renderer class.
 *
 * Orchestrator. Builds all dependencies, passes them to templates. Only class that includes template files.
 */
class Renderer {

    /**
     * Playlist post ID (0 = legacy/POC mode).
     *
     * @var int
     */
    private $playlist_id;

    /**
     * Shortcode attribute overrides (final merge layer).
     *
     * @var array
     */
    private $attr_overrides;

    /**
     * Player post IDs to use instead of the post's saved _playlist_items.
     * Null means "use saved state" - see Config::__construct().
     *
     * @var array|null
     */
    private $item_id_overrides;

    /**
     * Per-item field overrides for the admin-new live preview's staged row
     * edits - see Config::__construct().
     *
     * @var array
     */
    private $track_overrides;

    /**
     * Constructor.
     *
     * @param int        $playlist_id       Playlist post ID (0 = legacy/POC mode).
     * @param array      $attr_overrides    Shortcode attribute overrides.
     * @param array|null $item_id_overrides Player post IDs to use instead of saved
     *                                      _playlist_items - see Config::__construct().
     * @param array      $track_overrides   Per-item field overrides - see Config::__construct().
     */
    public function __construct( int $playlist_id = 0, array $attr_overrides = [], ?array $item_id_overrides = null, array $track_overrides = [] ) {
        $this->playlist_id      = $playlist_id;
        $this->attr_overrides   = $attr_overrides;
        $this->item_id_overrides = $item_id_overrides;
        $this->track_overrides  = $track_overrides;
    }

    /**
     * Render playlist. Self-renders (echoes directly).
     *
     * @return void
     */
    public function render(): void {
        $config     = new Config( $this->playlist_id, $this->attr_overrides, $this->item_id_overrides, $this->track_overrides );
        $first_item = $config->get_first_item();

        if ( ! $first_item ) {
            return;
        }

        $css_helper         = new CSS_Helper( $config );
        $player_markup_data = new Player_Markup_Data( $first_item );

        $auto_thumbnail = $config->is_auto_thumbnail();
        $item_views     = [];
        foreach ( $config->get_playlist_items() as $index => $item ) {
            $item_views[] = new Item_View( $item, $index, $auto_thumbnail );
        }

        $start_index  = $config->get_start_index();
        $template_dir = LEANPL_PLAYLIST_DIR . '/templates/';

        $item_template     = $config->get_item_template();
        $is_card           = ( $item_template === 'grid' );
        $now_playing_style = $config->get_now_playing_style();

        echo '<div class="lpl-playlist-wrap" id="lpl-playlist-' . esc_attr( $this->playlist_id ) . '">';
        include $template_dir . 'playlist.php';
        echo '</div>';
    }
}
