<?php
namespace LeanPL\Admin_New;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shared data-provider logic for the "All Players (New)" and "All Playlists
 * (New)" screens. One capped WP_Query (500 posts), pagination/filter/sort
 * done client-side against the returned row set. See
 * docs/admin-redesign-pencil-porting-process.md step 12.
 *
 * @package LeanPL\Admin_New
 */
abstract class Admin_New_Data_Base {

    /**
     * Maximum number of rows fetched by a single query. Filtering, sorting and
     * pagination happen client-side against this set.
     */
    const MAX_ROWS = 500;

    /**
     * @return string The post type this data provider queries.
     */
    abstract protected function get_post_type();

    /**
     * @return string The post meta key holding 'video' | 'audio' for a row.
     */
    abstract protected function get_type_meta_key();

    /**
     * Build a single flat row array from a WP_Post.
     *
     * @param \WP_Post $post
     * @return array
     */
    abstract protected function build_row( $post );

    /**
     * Map a tab key to the WP_Query post_status argument.
     *
     * @param string $status Tab key: 'all', 'publish', 'draft', 'future', 'trash'.
     * @return string|array Status value(s) for WP_Query. Unknown keys fall back to 'all'.
     */
    protected function map_status( $status ) {
        switch ( $status ) {
            case 'publish':
                return 'publish';
            case 'draft':
                return 'draft';
            case 'future':
                return 'future';
            case 'trash':
                return 'trash';
            case 'all':
            default:
                return [ 'publish', 'draft', 'future', 'pending', 'private' ];
        }
    }

    /**
     * Get the rows for a given status tab.
     *
     * @param string $status Tab key.
     * @return array Flat row arrays (see build_row()). Empty array on failure.
     */
    public function get_rows( $status = 'any' ) {
        $query_status = ( $status === 'any' || $status === '' ) ? $this->map_status( 'all' ) : $this->map_status( $status );

        $query = new \WP_Query( [
            'post_type'      => $this->get_post_type(),
            'post_status'    => $query_status,
            'posts_per_page' => self::MAX_ROWS,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => false,
        ] );

        $rows = [];
        foreach ( $query->posts as $post ) {
            $rows[] = $this->build_row( $post );
        }

        return $rows;
    }

    /**
     * Human-readable status label for the row pill.
     *
     * @param string $status WP post status.
     * @return string
     */
    protected function get_status_label( $status ) {
        switch ( $status ) {
            case 'publish':
                return __( 'Public', 'vapfem' );
            case 'draft':
                return __( 'Draft', 'vapfem' );
            case 'future':
                return __( 'Scheduled', 'vapfem' );
            case 'trash':
                return __( 'Trash', 'vapfem' );
            case 'pending':
                return __( 'Pending', 'vapfem' );
            case 'private':
                return __( 'Private', 'vapfem' );
            default:
                return ucfirst( $status );
        }
    }

    /**
     * Pill colour pair for a given post status.
     *
     * @param string $status WP post status.
     * @return array{bg:string,text:string}
     */
    protected function get_status_pill_classes( $status ) {
        switch ( $status ) {
            case 'publish':
                return [
                    'bg'   => 'lpl-bg-[#DEF7E7]',
                    'text' => 'lpl-text-[#15A34A]',
                ];
            case 'draft':
                return [
                    'bg'   => 'lpl-bg-[#FCEFD6]',
                    'text' => 'lpl-text-[#B45309]',
                ];
            default:
                return [
                    'bg'   => 'lpl-bg-[#EEF0F3]',
                    'text' => 'lpl-text-[#6A7180]',
                ];
        }
    }

    /**
     * Get the counts for the header subtitle and the status tabs.
     *
     * One ids-only WP_Query for the audio count, video derived by
     * subtraction, rather than a second full get_rows('all') just to split
     * video from audio.
     *
     * @return array{all:int,publish:int,draft:int,future:int,trash:int,video:int,audio:int}
     */
    public function get_counts() {
        $post_type = $this->get_post_type();
        $counts    = wp_count_posts( $post_type );

        $arr = [
            'all'     => (int) ( $counts->publish + $counts->draft + $counts->future + $counts->pending + ( isset( $counts->private ) ? $counts->private : 0 ) ),
            'publish' => (int) $counts->publish,
            'draft'   => (int) $counts->draft,
            'future'  => (int) $counts->future,
            'trash'   => (int) $counts->trash,
            'video'   => 0,
            'audio'   => 0,
        ];

        $audio_query = new \WP_Query( [
            'post_type'      => $post_type,
            'post_status'    => $this->map_status( 'all' ),
            'posts_per_page' => self::MAX_ROWS,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'meta_key'       => $this->get_type_meta_key(),
            'meta_value'     => 'audio',
        ] );

        $arr['audio'] = count( $audio_query->posts );
        $arr['video'] = max( 0, $arr['all'] - $arr['audio'] );

        return $arr;
    }
}
