<?php
namespace LeanPL\Admin_New;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Data provider for the "All Players (New)" screen. Shared query/status/pill
 * logic lives in Admin_New_Data_Base.
 *
 * @package LeanPL\Admin_New
 */
class All_Players_Data extends Admin_New_Data_Base {

    protected function get_post_type() {
        return 'lean_player';
    }

    protected function get_type_meta_key() {
        return '_player_type';
    }

    /**
     * Build a single flat row array from a WP_Post.
     *
     * @param \WP_Post $post
     * @return array
     */
    protected function build_row( $post ) {
        $id         = (int) $post->ID;
        $type       = get_post_meta( $id, '_player_type', true ) ?: 'video';
        $type_label = $type === 'audio' ? __( 'Audio', 'vapfem' ) : __( 'Video', 'vapfem' );

        $badge        = leanpl_get_player_source_badge( $id );
        $source_key   = $badge['type'];
        $source_label = $badge['label'];
        $format_label = $this->get_format_label( $id, $type, $source_key );

        $status_label = $this->get_status_label( $post->post_status );
        $pill_classes = $this->get_status_pill_classes( $post->post_status );

        $shortcode = sprintf( '[lean_player id="%d"]', $id );

        $is_previewable = in_array( $post->post_status, [ 'publish', 'draft' ], true );
        $preview_url    = $is_previewable ? get_preview_post_link( $id, [
            'preview'       => 'true',
            'preview_id'    => $id,
            'preview_nonce' => wp_create_nonce( 'post_preview_' . $id ),
        ] ) : '';

        return [
            'id'                => $id,
            'title'             => get_the_title( $post ),
            'edit_url'          => admin_url( 'admin.php?page=lean-player-edit&post=' . $id ),
            'preview_url'       => $preview_url,
            'is_previewable'    => $is_previewable,
            'status'            => $post->post_status,
            'status_label'      => $status_label,
            'type'              => $type,
            'type_label'        => $type_label,
            'format_label'      => $format_label,
            'source_key'        => $source_key,
            'source_label'      => $source_label,
            'thumb_url'         => leanpl_get_video_thumbnail_url( $id ),
            'created'           => date_i18n( get_option( 'date_format' ), strtotime( $post->post_date ) ),
            'created_ts'        => strtotime( $post->post_date ),
            'updated_ts'        => strtotime( $post->post_modified ),
            'shortcode'         => $shortcode,
            'pill_classes'      => $pill_classes['bg'],
            'pill_text_classes' => $pill_classes['text'],
        ];
    }

    /**
     * Format label: the file extension for self-hosted/external sources, or an
     * em dash for YouTube/Vimeo where there is no local file.
     *
     * @param int    $id         Player post ID.
     * @param string $type       'video' or 'audio'.
     * @param string $source_key Source badge type (youtube/vimeo/self-hosted/external).
     * @return string Upper-case extension or an em dash.
     */
    private function get_format_label( $id, $type, $source_key ) {
        if ( $source_key === 'youtube' || $source_key === 'vimeo' ) {
            return '—';
        }

        if ( $type === 'audio' ) {
            $source_type = get_post_meta( $id, '_audio_source_type', true ) ?: 'upload';
            if ( $source_type === 'upload' ) {
                $att_id = get_post_meta( $id, '_audio_source', true );
                $file   = leanpl_get_media_filename( $att_id );
            } else {
                $file = get_post_meta( $id, '_html5_audio_url', true );
            }
        } else {
            $source_type = get_post_meta( $id, '_html5_source_type', true ) ?: 'upload';
            if ( $source_type === 'upload' ) {
                $att_id = get_post_meta( $id, '_video_source', true );
                $file   = leanpl_get_media_filename( $att_id );
            } else {
                $file = get_post_meta( $id, '_html5_video_url', true );
            }
        }

        $ext = leanpl_get_url_extension( $file );
        if ( $ext === '' ) {
            return '—';
        }

        return strtoupper( $ext );
    }
}
