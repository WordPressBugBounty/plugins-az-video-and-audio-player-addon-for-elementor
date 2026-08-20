<?php
namespace LeanPL\Admin_New;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Data provider for the "All Playlists (New)" screen. Shared query/status/pill
 * logic lives in Admin_New_Data_Base.
 *
 * @package LeanPL\Admin_New
 */
class All_Playlists_Data extends Admin_New_Data_Base {

    protected function get_post_type() {
        return 'lean_playlist';
    }

    protected function get_type_meta_key() {
        return '_playlist_type';
    }

    /**
     * Build a single flat row array from a WP_Post.
     *
     * @param \WP_Post $post
     * @return array
     */
    protected function build_row( $post ) {
        $id         = (int) $post->ID;
        $type       = get_post_meta( $id, '_playlist_type', true ) ?: 'video';
        $type_label = leanpl_get_player_type_label( $type );

        $status_label = $this->get_status_label( $post->post_status );
        $pill_classes = $this->get_status_pill_classes( $post->post_status );

        // _playlist_items has two on-disk shapes: [['id'=>N],...] from the metabox
        // save, and a flat [N,...] from the preview save path. count() is correct
        // for both, so no normalisation is needed here. Anything that later reads
        // item *ids* does need to handle both shapes.
        $items      = get_post_meta( $id, '_playlist_items', true );
        $item_count = is_array( $items ) ? count( $items ) : 0;

        $shortcode = sprintf( '[lean_playlist id="%d"]', $id );

        $is_previewable = in_array( $post->post_status, [ 'publish', 'draft' ], true );
        $preview_url    = $is_previewable ? get_preview_post_link( $id, [
            'preview'       => 'true',
            'preview_id'    => $id,
            'preview_nonce' => wp_create_nonce( 'post_preview_' . $id ),
        ] ) : '';

        return [
            'id'                => $id,
            'title'             => get_the_title( $post ),
            'edit_url'          => admin_url( 'admin.php?page=lean-playlist-edit&post=' . $id ),
            'preview_url'       => $preview_url,
            'is_previewable'    => $is_previewable,
            'status'            => $post->post_status,
            'status_label'      => $status_label,
            'type'              => $type,
            'type_label'        => $type_label,
            'item_count'        => $item_count,
            /* translators: %d: number of items in the playlist. */
            'item_count_label'  => sprintf( _n( '%d item', '%d items', $item_count, 'vapfem' ), $item_count ),
            'created'           => date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ),
            'created_ts'        => strtotime( $post->post_date ),
            'updated_ts'        => strtotime( $post->post_modified ),
            'shortcode'         => $shortcode,
            'pill_classes'      => $pill_classes['bg'],
            'pill_text_classes' => $pill_classes['text'],
        ];
    }
}
