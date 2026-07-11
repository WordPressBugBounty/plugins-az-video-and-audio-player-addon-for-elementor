<?php
/**
 * Item_View — view-model for a single playlist item.
 *
 * Not self-initialized: multi-instance (one per item), needs item + index in constructor.
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Item_View class.
 *
 * View-model for a single item. Templates call methods instead of $item['key'].
 */
class Item_View {

    /**
     * Raw item array.
     *
     * @var array<string, mixed>
     */
    private $item;

    /**
     * 0-based index.
     *
     * @var int
     */
    private $index;

    /**
     * @var bool
     */
    private $auto_thumbnail;

    /**
     * Constructor.
     *
     * @param array<string, mixed> $item           Raw item array.
     * @param int                  $index          0-based index.
     * @param bool                 $auto_thumbnail Whether to auto-derive thumbnail from provider.
     */
    public function __construct( array $item, int $index, bool $auto_thumbnail = false ) {
        $this->item           = $item;
        $this->index          = $index;
        $this->auto_thumbnail = $auto_thumbnail;
    }

    /**
     * Get item classes.
     *
     * @param bool $is_active Whether item is active.
     * @param bool $is_card   Whether item is card layout (grid).
     * @return string
     */
    public function get_classes( bool $is_active, bool $is_card ) {
        $classes = [ 'lpl-playlist__item' ];
        if ( $is_card ) {
            $classes[] = 'lpl-playlist__item--card';
        }
        if ( $is_active ) {
            $classes[] = 'lpl-playlist__item--active';
        }
        $classes[] = 'lpl-playlist__item-' . $this->get_item_id();
        return implode( ' ', array_filter( $classes ) );
    }

    /**
     * Get source JSON for data-lpl-source.
     *
     * @return string
     */
    public function get_source_json() {
        return wp_json_encode( $this->item );
    }

    /**
     * Get the underlying lean_player post ID, folded into get_classes() as
     * `lpl-playlist__item-{id}`.
     *
     * Not unique per DOM node: the same player can appear in a playlist
     * more than once, and every instance shares this ID.
     *
     * @return int
     */
    public function get_item_id() {
        return absint( $this->item['id'] ?? 0 );
    }

    /**
     * Get thumb/poster URL.
     *
     * @return string
     */
    public function get_thumb_url() {
        $poster = $this->item['poster'] ?? '';

        if ( $poster ) {
            return $poster;
        }

        if ( ! $this->auto_thumbnail ) {
            return '';
        }

        $sources = $this->item['sources'] ?? [];
        $src     = $sources[0]['src'] ?? '';

        return leanpl_derive_thumbnail_from_url( $src );
    }

    /**
     * Get thumb width for img (row: 100, grid: 100%).
     *
     * @param bool $is_card Whether item is card layout.
     * @return string
     */
    public function get_thumb_width( bool $is_card ) {
        return $is_card ? '100%' : '100';
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function get_title() {
        return $this->item['title'] ?? '';
    }

    /**
     * Get meta.
     *
     * @return string
     */
    public function get_meta() {
        return $this->item['meta'] ?? '';
    }

    /**
     * Get duration.
     *
     * @return string
     */
    public function get_duration() {
        return $this->item['duration'] ?? '';
    }

    /**
     * Get URL.
     *
     * @return string
     */
    public function get_url() {
        return $this->item['url'] ?? '';
    }

    /**
     * Get item number (1-based).
     *
     * @return int
     */
    public function get_item_number() {
        return $this->index + 1;
    }
}
