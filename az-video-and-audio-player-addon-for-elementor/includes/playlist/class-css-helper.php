<?php
/**
 * CSS_Helper — feature classes and inline styles.
 *
 * Not self-initialized: needs Config in constructor, created per render by Renderer.
 *
 * @package LeanPL\Playlist
 */

namespace LeanPL\Playlist;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * CSS_Helper class.
 *
 * All CSS-related output — feature classes and inline style string.
 */
class CSS_Helper {

    /**
     * Config instance.
     *
     * @var Config
     */
    private $config;

    /**
     * Constructor.
     *
     * @param Config $config Config instance.
     */
    public function __construct( Config $config ) {
        $this->config = $config;
    }

    /**
     * Get feature classes (space-separated).
     *
     * @return string
     */
    public function get_feature_classes() {
        $flags   = $this->config->get_feature_flags();
        $classes = array_filter( [
            ! $flags['show_header'] ? 'lpl--hide-header' : '',
            ! $flags['show_count'] ? 'lpl--hide-count' : '',
            ! $flags['show_thumbnails'] ? 'lpl--hide-thumbnails' : '',
            $flags['play_icon'] === 'hidden'      ? 'lpl--hide-play-icon'       : '',
            $flags['play_icon'] === 'active_only' ? 'lpl--play-icon-active-only' : '',
            $flags['play_icon'] === 'always'      ? 'lpl--play-icon-always'      : '',
            ! $flags['show_duration_badge'] ? 'lpl--hide-duration' : '',
            ! $flags['show_duration_in_list'] ? 'lpl--hide-duration-in-list' : '',
            ! $flags['show_numbers'] ? 'lpl--hide-numbers' : '',
            ! $flags['show_meta'] ? 'lpl--hide-meta' : '',
            'lpl--now-playing-' . $this->config->get_now_playing_style(),
            $this->config->get_thumb_ratio() === '1/1'         ? 'lpl--thumb-square'       : '',
            $this->config->get_bg_style()    === 'gradient'    ? 'lpl--bg-gradient'         : '',
        ] );
        return implode( ' ', $classes );
    }

    public function get_data_skin() {
        return $this->config->get_skin();
    }

    /**
     * Get inline style string.
     *
     * @return string
     */
    public function get_inline_style() {
        $max_width = esc_attr( $this->config->get_max_width() );

        $style = sprintf(
            '--lpl-playlist-max-width: %s; max-width: %s; --lpl-playlist-panel-width: %s;',
            $max_width,
            $max_width,
            esc_attr( $this->config->get_panel_width() )
        );

        if ( $this->config->get_playlist_height_mode() === 'fixed' ) {
            $style .= ' --lpl-playlist-panel-max-height: ' . esc_attr( $this->config->get_panel_max_height() ) . ';';
        }

        $accent = $this->config->get_accent_color();
        if ( $accent ) {
            $style .= ' --lpl-playlist-accent:' . esc_attr( sanitize_hex_color( $accent ) ) . ';';
        }

        $gradient = $this->config->get_gradient_css();
        if ( $this->config->get_bg_style() === 'gradient' && $gradient !== '' ) {
            $style .= ' background:' . esc_attr( $gradient ) . ';';
        }

        return $style;
    }

    /**
     * Get root classes (position, style, grid-cols + feature classes).
     *
     * @return string
     */
    public function get_root_classes() {
        return sprintf(
            'lpl-playlist lpl-playlist--%s lpl--position-%s lpl--template-%s lpl--grid-cols-%d %s',
            esc_attr( $this->config->get_playlist_type() ),
            esc_attr( $this->config->get_position() ),
            esc_attr( $this->config->get_item_template() ),
            (int) $this->config->get_grid_columns(),
            esc_attr( $this->get_feature_classes() )
        );
    }
}
