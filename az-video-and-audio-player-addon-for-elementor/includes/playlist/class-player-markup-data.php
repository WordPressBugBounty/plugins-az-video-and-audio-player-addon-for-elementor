<?php
/**
 * Player_Markup_Data — data for player markup (provider, embed_id, etc.).
 *
 * Not self-initialized: needs first item in constructor, created per render by Renderer.
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Player_Markup_Data class.
 *
 * Data for player markup — detect player type and expose clean accessors. No markup.
 */
class Player_Markup_Data {

    /**
     * Raw item array.
     *
     * @var array<string, mixed>|null
     */
    private $item;

    /**
     * First source from item.
     *
     * @var array<string, mixed>|null
     */
    private $first_source;

    /**
     * Constructor.
     *
     * @param array<string, mixed>|null $item Raw item array.
     */
    public function __construct( $item ) {
        $this->item         = $item;
        $this->first_source = $item['sources'][0] ?? null;
    }

    /**
     * Whether source is embed (YouTube/Vimeo).
     *
     * @return bool
     */
    public function is_embed() {
        return isset( $this->first_source['provider'] );
    }

    /**
     * Get provider (youtube, vimeo, or empty).
     *
     * @return string
     */
    public function get_provider() {
        return $this->first_source['provider'] ?? '';
    }

    /**
     * Get embed ID (YouTube ID or Vimeo ID/URL).
     *
     * @return string
     */
    public function get_embed_id() {
        return $this->first_source['src'] ?? '';
    }

    /**
     * Get source URL for HTML5.
     *
     * @return string
     */
    public function get_source_url() {
        return $this->first_source['src'] ?? $this->first_source['url'] ?? '';
    }

    /**
     * Get source extension from URL (e.g. mp4).
     *
     * @return string
     */
    public function get_source_extension() {
        $url = $this->get_source_url();
        if ( empty( $url ) ) {
            return '';
        }
        $path = wp_parse_url( $url, PHP_URL_PATH );
        return $path ? pathinfo( $path, PATHINFO_EXTENSION ) : '';
    }

    /**
     * Get poster URL.
     *
     * @return string
     */
    public function get_poster() {
        return $this->item['poster'] ?? '';
    }

    /**
     * Whether item has valid source.
     *
     * @return bool
     */
    public function has_valid_source() {
        return ! empty( $this->item ) && ! empty( $this->get_source_url() );
    }
}
