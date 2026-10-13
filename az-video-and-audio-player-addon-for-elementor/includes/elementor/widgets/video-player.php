<?php
use LeanPL\Player_Renderer;

require_once __DIR__ . '/trait-saved-player-extras.php';
require_once __DIR__ . '/video-player/trait-content-controls.php';
require_once __DIR__ . '/video-player/trait-style-controls.php';

class LeanPL_Video_Player extends \Elementor\Widget_Base {

    public function get_name() {
        return "vapfem_video_player";
    }

    public function get_title() {
        return esc_html__( "Video Player", 'vapfem' );
    }

    public function get_icon() {
        return 'az_icon eicon-youtube';
    }

    public function get_categories() {
        return array( 'general' );
    }

    public function get_keywords() {
        return [ 'lean', 'leanpl', 'video', 'player', 'youtube', 'vimeo' ];
    }

    public function get_style_depends() {
        return [
            'plyr',
            'leanpl-main'
        ];
    }
    
    public function get_script_depends() {
        return [
            'plyr',
            'leanpl-main',
            'leanpl-elementor',
        ];
    }

    use LeanPL_Video_Player_Content_Controls;
    use LeanPL_Video_Player_Style_Controls;
    use LeanPL_Saved_Player_Extras;

    protected function _register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( ( $settings['player_source'] ?? 'manual' ) === 'saved' ) {
            $this->render_saved_player( $settings, 'video' );
            return;
        }

        $config = $this->elementor_to_config_adapter($settings);

        $renderer = Player_Renderer::get_instance();
        $renderer->render_video_player($config);
    }

    private function get_saved_players_options() {
        $options = [ '' => esc_html__( '— Select a player —', 'vapfem' ) ];

        $players = get_posts( [
            'post_type'   => 'lean_player',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
            'meta_query'  => [
                [
                    'key'   => '_player_type',
                    'value' => 'video',
                ],
            ],
        ] );

        foreach ( $players as $player ) {
            $options[ $player->ID ] = $player->post_title !== ''
                ? sprintf( '%s (%d)', $player->post_title, $player->ID )
                /* translators: %d: Player post ID, shown when the player has no title */
                : sprintf( esc_html__( 'Player #%d', 'vapfem' ), $player->ID );
        }

        return $options;
    }

    /**
     * Convert Elementor settings to flattened config format
     */
    private function elementor_to_config_adapter($settings) {
        return [
            'video_type'         => $settings['video_type'],
            'video_id'           => $this->get_video_id($settings),
            'poster'         => $this->get_poster_url($settings),
            'sources'            => $this->convert_video_list($settings),
            'bunny_url'          => 'bunny' === $settings['video_type'] ? trim((string) ($settings['bunny_url'] ?? '')) : '',
            'autoplay'           => $settings['autoplay'] === 'true',
            'muted'              => $settings['muted'] === 'true',
            'loop'               => $settings['loop'] === 'true',
            'volume'             => $this->convert_volume($settings),
            'click_to_play'      => $settings['click_to_play'] === 'true',
            'invert_time'        => $settings['invert_time'] === 'true',
            'seek_time'          => intval($settings['seek_time']),
            'hide_controls'      => $settings['hide_controls'] === 'true',
            'reset_on_end'       => $settings['reset_on_end'] === 'true',
            'keyboard_focused'   => $settings['keyboard_focused'] === 'true',
            'keyboard_global'    => $settings['keyboard_global'] === 'true',
            'fullscreen_enabled' => $settings['fullscreen_enabled'] === 'true',
            'tooltips_controls'  => $settings['tooltips_controls'] === 'true',
            'tooltips_seek'      => $settings['tooltips_seek'] === 'true',
            'speed_selected'     => $this->convert_speed($settings),
            'quality_default'    => $settings['quality_default'],
            'ratio'              => $this->get_ratio($settings),
            'preload'            => $settings['preload'] ?? 'metadata',
            'player_layout'      => $settings['player_layout'] ?? '',
            'controls'           => $settings['controls'],
            'debug_mode'         => $settings['debug_mode'] === 'true',
        ];
    }

    /**
     * Get video ID based on video type
     */
    private function get_video_id($settings) {
        switch ($settings['video_type']) {
            case 'youtube':
                return leanpl_embed_id_from_value($settings['youtube_video_id'], 'youtube');
            case 'vimeo':
                return leanpl_embed_id_from_value($settings['vimeo_video_id'], 'vimeo');
            default:
                return '';
        }
    }

    /**
     * Get poster URL from Elementor media control
     */
    private function get_poster_url($settings) {
        $poster = $settings['poster'] ?? [];
        return isset($poster['url']) ? $poster['url'] : '';
    }

    /**
     * Convert Elementor video list to simple format
     */
    private function convert_video_list($settings) {
        $video_list = $settings['video_list'] ?? [];
        $html5_list = [];

        foreach ($video_list as $item) {
            $url = '';
            if ($item['src_type'] === 'upload') {
                $url = isset($item['video_upload']['url']) ? $item['video_upload']['url'] : '';
            } else {
                $url = isset($item['video_link']['url']) ? $item['video_link']['url'] : '';
            }

            if (!empty($url)) {
                $html5_list[] = [
                    'url' => $url,
                    'size' => $item['video_size'] ?? ''
                ];
            }
        }

        return $html5_list;
    }

    /**
     * Convert Elementor volume percentage to decimal
     */
    private function convert_volume($settings) {
        $volume = $settings['volume'] ?? ['size' => 100];
        return floatval($volume['size']) / 100;
    }

    /**
     * Convert Elementor speed format to clean format
     */
    private function convert_speed($settings) {
        $speed = $settings['speed_selected'] ?? 'speed_1';
        return substr($speed, 6); // Remove 'speed_' prefix
    }

    /**
     * Get custom ratio if enabled
     */
    private function get_ratio($settings) {
        $custom_ratio = $settings['custom_ratio'] === 'true';
        return $custom_ratio && !empty($settings['ratio']) ? $settings['ratio'] : '';
    }
}
