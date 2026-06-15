<?php
/**
 * Config — normalised playlist config.
 *
 * Not self-initialized: request-scoped, created per render by Renderer.
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Config class.
 *
 * Single source for reading and normalising playlist config. No markup, no CSS.
 */
class Config {

    /**
     * Merged config array (preset + options).
     *
     * @var array<string, mixed>
     */
    private $merged = [];

    /**
     * Raw playlist items.
     *
     * @var array<int, array<string, mixed>>
     */
    private $items = [];

    /**
     * Constructor. Loads and merges config.
     *
     * @param int   $playlist_id    Playlist post ID (0 = legacy/POC mode).
     * @param array $attr_overrides Shortcode attribute overrides (final merge layer).
     */
    public function __construct( int $playlist_id = 0, array $attr_overrides = [] ) {
        $this->merged = $this->get_defaults();

        if ( $playlist_id > 0 ) {
            $this->items = $this->build_items_from_post( $playlist_id );

            $raw_type = get_post_meta( $playlist_id, '_playlist_type', true );
            $this->merged['playlist_type'] = in_array( $raw_type, [ 'video', 'audio' ], true ) ? $raw_type : 'video';

            foreach ( leanpl_get_playlist_meta_field_schema() as $field => $def ) {
                $meta_key = '_playlist_' . $field;
                $raw      = get_post_meta( $playlist_id, $meta_key, true );
                if ( $raw !== '' ) {
                    $this->merged[ $field ] = $raw;
                }
            }
        } else {
            $this->items                   = get_option( 'playlist_items' ) ?? [];
            $this->merged['playlist_type'] = 'video';
        }

        if ( ! empty( $attr_overrides ) ) {
            $this->merged = array_merge( $this->merged, $this->clean_attr_overrides( $attr_overrides ) );
        }
    }

    /**
     * Clean and gate shortcode attribute overrides.
     *
     * Type-casts raw shortcode strings to match the type declared in defaults.
     * For free users, silently drops any key in leanpl_get_playlist_pro_shortcode_keys().
     * Pro users pass all keys through. Keys not present in defaults are always dropped.
     *
     * @param array $overrides Raw shortcode attr values (all strings from WP).
     * @return array Cleaned, type-cast, license-gated overrides safe to merge into config.
     */
    private function clean_attr_overrides( array $overrides ): array {
        $defaults = leanpl_get_playlist_defaults();
        $pro_keys = leanpl_get_playlist_pro_shortcode_keys();
        $is_pro   = leanpl_is_pro_active();
        $cleaned  = [];

        foreach ( $overrides as $key => $value ) {
            if ( ! array_key_exists( $key, $defaults ) ) {
                continue;
            }

            if ( ! $is_pro && in_array( $key, $pro_keys, true ) ) {
                continue;
            }

            $default_value = $defaults[ $key ];
            if ( is_bool( $default_value ) ) {
                $cleaned[ $key ] = in_array( strtolower( (string) $value ), [ '1', 'true', 'yes' ], true );
            } elseif ( is_int( $default_value ) ) {
                $cleaned[ $key ] = (int) $value;
            } else {
                $cleaned[ $key ] = (string) $value;
            }
        }

        return $cleaned;
    }

    /**
     * Build playlist items array from a lean_playlist post's _playlist_items meta.
     *
     * Reads each referenced lean_player post and builds the standard item array
     * with title, meta, duration, type, sources, and poster.
     *
     * @param int $playlist_id Playlist post ID.
     * @return array<int, array<string, mixed>>
     */
    private function build_items_from_post( int $playlist_id ) {
        $saved_items = get_post_meta( $playlist_id, '_playlist_items', true );

        if ( empty( $saved_items ) || ! is_array( $saved_items ) ) {
            return [];
        }

        $items = [];

        foreach ( $saved_items as $entry ) {
            $player_id = absint( $entry['id'] ?? 0 );

            if ( $player_id < 1 || ! in_array( get_post_status( $player_id ), [ 'publish', 'draft' ], true ) ) {
                continue;
            }

            $item = $this->build_single_item( $player_id );

            if ( $item ) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Build a single playlist item from a lean_player post.
     *
     * @param int $player_id Player post ID.
     * @return array<string, mixed>|null Item array or null if no valid source.
     */
    private function build_single_item( int $player_id ) {
        $player_type = get_post_meta( $player_id, '_player_type', true ) ?: 'video';
        $title       = get_the_title( $player_id );
        $duration    = get_post_meta( $player_id, '_duration', true ) ?: '';
        $meta_text   = get_post_meta( $player_id, '_meta_text', true ) ?: '';

        // Build sources array
        $sources = $this->build_sources( $player_id, $player_type );

        if ( empty( $sources ) ) {
            return null;
        }

        // Build poster URL
        $poster_id  = get_post_meta( $player_id, '_poster', true );
        $poster_url = '';
        if ( $poster_id ) {
            $poster_url = wp_get_attachment_url( (int) $poster_id ) ?: '';
        }

        return [
            'title'    => $title,
            'meta'     => $meta_text,
            'duration' => $duration,
            'type'     => $player_type,
            'sources'  => $sources,
            'poster'   => $poster_url,
        ];
    }

    /**
     * Build sources array for a player post.
     *
     * @param int    $player_id   Player post ID.
     * @param string $player_type Player type (video or audio).
     * @return array<int, array<string, string>>
     */
    private function build_sources( int $player_id, string $player_type ) {
        if ( $player_type === 'audio' ) {
            return $this->build_audio_sources( $player_id );
        }

        return $this->build_video_sources( $player_id );
    }

    /**
     * Build video sources array.
     *
     * @param int $player_id Player post ID.
     * @return array<int, array<string, string>>
     */
    private function build_video_sources( int $player_id ) {
        $video_type = get_post_meta( $player_id, '_video_type', true ) ?: 'html5';

        if ( $video_type === 'youtube' ) {
            $url = get_post_meta( $player_id, '_youtube_url', true );
            return $url ? [ [ 'src' => $url, 'provider' => 'youtube' ] ] : [];
        }

        if ( $video_type === 'vimeo' ) {
            $url = get_post_meta( $player_id, '_vimeo_url', true );
            return $url ? [ [ 'src' => $url, 'provider' => 'vimeo' ] ] : [];
        }

        // HTML5 video
        $source_type = get_post_meta( $player_id, '_html5_source_type', true ) ?: 'upload';

        if ( $source_type === 'link' ) {
            $url = get_post_meta( $player_id, '_html5_video_url', true );
            return $url ? [ [ 'src' => $url ] ] : [];
        }

        // Upload
        $attachment_id = get_post_meta( $player_id, '_video_source', true );
        if ( $attachment_id ) {
            $url = wp_get_attachment_url( (int) $attachment_id );
            return $url ? [ [ 'src' => $url ] ] : [];
        }

        return [];
    }

    /**
     * Build audio sources array.
     *
     * @param int $player_id Player post ID.
     * @return array<int, array<string, string>>
     */
    private function build_audio_sources( int $player_id ) {
        $source_type = get_post_meta( $player_id, '_audio_source_type', true ) ?: 'upload';

        if ( $source_type === 'link' ) {
            $url = get_post_meta( $player_id, '_html5_audio_url', true );
            return $url ? [ [ 'src' => $url ] ] : [];
        }

        // Upload
        $attachment_id = get_post_meta( $player_id, '_audio_source', true );
        if ( $attachment_id ) {
            $url = wp_get_attachment_url( (int) $attachment_id );
            return $url ? [ [ 'src' => $url ] ] : [];
        }

        return [];
    }

    /**
     * Get default config values.
     *
     * @return array<string, mixed>
     */
    private function get_defaults() {
        return leanpl_resolve_playlist_custom_config();
    }

    /**
     * Get position (right, left, bottom).
     *
     * @return string
     */
    public function get_position() {
        return $this->merged['position'] ?? 'right';
    }

    /**
     * Get item template (row or grid).
     *
     * @return string
     */
    public function get_item_template() {
        $position = $this->get_position();
        $template = ( $this->merged['item_template'] ?? 'list' ) === 'grid' ? 'grid' : 'list';
        if ( $position === 'left' || $position === 'right' ) {
            return 'list';
        }
        return $template;
    }

    /**
     * Get grid columns (2–6).
     *
     * @return int
     */
    public function get_grid_columns() {
        return max( 2, min( 6, (int) ( $this->merged['grid_columns'] ?? 4 ) ) );
    }

    /**
     * Get panel width.
     *
     * @return string
     */
    public function get_panel_width() {
        $val = $this->merged['panel_width'] ?? $this->merged['sidebar_width'] ?? '';
        return ( $val !== '' ) ? $val : '360px';
    }

    /**
     * Get max width.
     *
     * @return string
     */
    public function get_max_width() {
        $raw = $this->merged['max_width'] ?? '100%';
        return ( $raw === '' || $raw === null ) ? 'none' : $raw;
    }

    /**
     * Get panel max height.
     *
     * @return string
     */
    public function get_panel_max_height() {
        $val = $this->merged['panel_max_height'] ?? '';
        return ( $val !== '' ) ? $val : '360px';
    }

    /**
     * Get playlist height mode.
     *
     * @return string
     */
    public function get_playlist_height_mode() {
        return $this->merged['playlist_height_mode'] ?? 'sync';
    }

    /**
     * Get start item (1-based).
     *
     * @return int
     */
    public function get_start_item() {
        if ( leanpl_is_pro_active() && isset( $_GET['start_item'] ) ) {
            return max( 1, (int) $_GET['start_item'] );
        }
        return (int) ( $this->merged['start_item'] ?? 1 );
    }

    /**
     * Get start index (0-based).
     *
     * @return int
     */
    public function get_start_index() {
        return max( 0, $this->get_start_item() - 1 );
    }

    /**
     * Get total items count.
     *
     * @return int
     */
    public function get_total_items() {
        return count( $this->items );
    }

    /**
     * Get header text.
     *
     * @return string
     */
    public function get_header_text() {
        return $this->merged['header_text'] ?? 'Playlist';
    }

    /**
     * Get count label.
     *
     * @return string
     */
    public function get_count_label() {
        return $this->merged['count_label'] ?? $this->merged['video_count_label'] ?? 'Videos';
    }

    /**
     * Get feature flags (show_* booleans).
     *
     * @return array<string, bool>
     */
    public function get_feature_flags() {
        $play_icon = in_array( $this->merged['play_icon'] ?? '', [ 'always', 'active_only', 'hidden' ], true )
            ? $this->merged['play_icon']
            : 'active_only';
        return [
            'show_header'          => (bool) ( $this->merged['show_header'] ?? false ),
            'show_count'           => (bool) ( $this->merged['show_count'] ?? $this->merged['show_video_count'] ?? false ),
            'show_thumbnails'       => (bool) ( $this->merged['show_thumbnails'] ?? false ),
            'play_icon'             => $play_icon,
            'show_duration_badge'   => (bool) ( $this->merged['show_duration_badge'] ?? false ),
            'show_duration_in_list' => (bool) ( $this->merged['show_duration_in_list'] ?? $this->merged['show_duration_in_row'] ?? false ),
            'show_numbers'          => (bool) ( $this->merged['show_numbers'] ?? $this->merged['show_item_numbers'] ?? false ),
            'show_meta'             => (bool) ( $this->merged['show_meta'] ?? true ),
        ];
    }

    /**
     * Get playlist type (video or audio).
     *
     * @return string
     */
    public function get_playlist_type() {
        return $this->merged['playlist_type'] ?? 'video';
    }

    /**
     * Get accent color.
     *
     * @return string
     */
    public function get_accent_color() {
        return $this->merged['accent_color'] ?? '';
    }

    /**
     * Get JS config for data-lpl-playlist-config.
     *
     * @return array<string, mixed>
     */
    public function get_skin() {
        $skin = $this->merged['skin'] ?? 'default';
        return in_array( $skin, [ 'default', 'dark' ], true ) ? $skin : 'default';
    }

    public function get_now_playing_style() {
        return ( $this->merged['now_playing_style'] ?? 'compact' ) === 'large' ? 'large' : 'compact';
    }

    public function get_thumb_ratio() {
        return ( $this->merged['thumb_ratio'] ?? '16/9' ) === '1/1' ? '1/1' : '16/9';
    }

    public function get_bg_style() {
        return $this->merged['bg_style'] ?? '';
    }

    public function get_gradient_css() {
        return $this->merged['gradient_css'] ?? '';
    }

    public function get_js_config() {
        $merged_player = \LeanPL\Config_Merger::get_instance()->merge( [] );

        return [
            'autoplay_next'        => ! empty( $this->merged['autoplay_next'] ),
            'playlist_height_mode' => $this->get_playlist_height_mode(),
            'playlist_type'        => $this->get_playlist_type(),
            'accent_color'         => $this->get_accent_color(),
            'brand_color'          => $merged_player['primary_color'] ?? '',
            'skin'                 => $this->get_skin(),
            'start_item'           => $this->get_start_item(),
        ];
    }

    /**
     * Get merged Plyr config for the playlist player.
     *
     * Inherits all global settings via Config_Merger. Overrides autoplay, loop,
     * and reset_on_end. The playlist JS handles track flow itself, and these
     * three options would either no-op or break autoplay_next.
     *
     * @return array<string, mixed>
     */
    public function get_plyr_config(): array {
        $merged = \LeanPL\Config_Merger::get_instance()->merge( [] );
        return array_merge( $merged, [
            'autoplay'     => false,
            'loop'         => false,
            'reset_on_end' => false,
        ] );
    }

    /**
     * Get preload value from merged player config.
     *
     * @return string 'metadata', 'auto', or 'none'
     */
    public function get_preload(): string {
        $merged = \LeanPL\Config_Merger::get_instance()->merge( [] );

        return in_array( $merged['preload'] ?? '', [ 'auto', 'metadata', 'none' ], true )
            ? $merged['preload']
            : 'metadata';
    }

    /**
     * Get playlist items.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get_playlist_items() {
        return $this->items;
    }

    /**
     * Get first item (for player).
     *
     * @return array<string, mixed>|null
     */
    public function get_first_item() {
        $start_index = $this->get_start_index();
        return $this->items[ $start_index ] ?? $this->items[0] ?? null;
    }

    public function is_auto_thumbnail(): bool {
        return ! empty( $this->merged['auto_thumbnail'] );
    }
}
