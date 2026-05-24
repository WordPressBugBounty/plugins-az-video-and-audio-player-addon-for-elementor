<?php
/**
 * Playlist Table Columns
 *
 * Adds custom columns (Items count, Shortcode) to the lean_playlist list table.
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Playlist_Table_Columns {

    /**
     * Single instance
     *
     * @var Playlist_Table_Columns|null
     */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return Playlist_Table_Columns
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
        add_filter( 'manage_lean_playlist_posts_columns', [ $this, 'add_column_headers' ] );
        add_action( 'manage_lean_playlist_posts_custom_column', [ $this, 'render_column_content' ], 10, 2 );
        add_action( 'admin_head', [ $this, 'add_column_styles' ] );
    }

    /**
     * Add custom columns after title
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function add_column_headers( $columns ) {
        $new_columns = [];

        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;

            if ( $key === 'title' ) {
                $new_columns['lpl_playlist_type']     = esc_html__( 'Type', 'vapfem' );
                $new_columns['lpl_items_count']       = esc_html__( 'Items', 'vapfem' );
                $new_columns['lpl_playlist_shortcode'] = esc_html__( 'Shortcode', 'vapfem' );
            }
        }

        return $new_columns;
    }

    /**
     * Render custom column content
     *
     * @param string $column_name Column identifier
     * @param int    $post_id     Post ID
     */
    public function render_column_content( $column_name, $post_id ) {
        switch ( $column_name ) {
            case 'lpl_playlist_type':
                $this->render_playlist_type( $post_id );
                break;
            case 'lpl_items_count':
                $this->render_items_count( $post_id );
                break;
            case 'lpl_playlist_shortcode':
                $this->render_shortcode( $post_id );
                break;
        }
    }

    /**
     * Render Type column
     *
     * @param int $post_id Post ID
     */
    private function render_playlist_type( $post_id ) {
        $type = get_post_meta( $post_id, '_playlist_type', true ) ?: 'video';
        echo $type === 'audio' ? esc_html__( 'Audio', 'vapfem' ) : esc_html__( 'Video', 'vapfem' );
    }

    /**
     * Render Items count column
     *
     * @param int $post_id Post ID
     */
    private function render_items_count( $post_id ) {
        $items = get_post_meta( $post_id, '_playlist_items', true );
        $count = is_array( $items ) ? count( $items ) : 0;
        echo esc_html( $count );
    }

    /**
     * Render Shortcode column with copy button
     *
     * @param int $post_id Post ID
     */
    private function render_shortcode( $post_id ) {
        $shortcode = '[lean_playlist id="' . esc_attr( $post_id ) . '"]';
        ?>
        <div class="lpl-shortcode-cell">
            <code class="lpl-shortcode-text" title="<?php echo esc_attr( $shortcode ); ?>">
                <?php echo esc_html( $shortcode ); ?>
            </code>
            <div
                class="lex-copy-button lex-copy-button--outline"
                data-lex-copy="<?php echo esc_attr( $shortcode ); ?>"
            >
                <?php echo esc_html__( 'Copy', 'vapfem' ); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Add CSS styles for table columns
     */
    public function add_column_styles() {
        $screen = get_current_screen();

        if ( ! $screen || $screen->post_type !== 'lean_playlist' || $screen->base !== 'edit' ) {
            return;
        }
        ?>
        <style>
            @media (min-width: 1024px) and (max-width: 1919px) {
                .wp-list-table .column-lpl_playlist_type {
                    width: 80px;
                    max-width: 80px;
                }
                .wp-list-table .column-lpl_items_count {
                    width: 70px;
                    max-width: 70px;
                }
                .wp-list-table .column-lpl_playlist_shortcode {
                    width: 280px;
                    max-width: 280px;
                }
            }
        </style>
        <?php
    }
}

Playlist_Table_Columns::get_instance();
