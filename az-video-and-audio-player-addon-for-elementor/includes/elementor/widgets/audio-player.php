<?php
use LeanPL\Player_Renderer;
use LeanPL\Shortcodes\Player_Shortcode;
use Elementor\Modules\DynamicTags\Module as TagsModule;

require_once __DIR__ . '/audio-player/trait-content-controls.php';
require_once __DIR__ . '/audio-player/trait-style-controls.php';

class LeanPL_Audio_Player extends Elementor\Widget_Base {

    use LeanPL_Audio_Player_Content_Controls;
    use LeanPL_Audio_Player_Style_Controls;

    public function get_name() {
        return "vapfem_audio_player";
    }

    public function get_title() {
        return esc_html__( "Audio Player", 'vapfem' );
    }

    public function get_icon() {
        return 'az_icon eicon-headphones';
    }

    public function get_categories() {
        return array( 'general' );
    }

    public function get_keywords() {
        return [ 'lean', 'leanpl', 'audio', 'player', 'podcast', 'music' ];
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

    protected function _register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Saved Player mode: delegate entirely to the shortcode renderer.
        // The widget owns all validation so render_error() never reaches the frontend.
        if ( ( $settings['player_source'] ?? 'manual' ) === 'saved' ) {
            $post_id = absint( $settings['saved_player_id'] ?? 0 );

            if ( ! $post_id ) {
                if ( leanpl_is_elementor_editor() ) {
                    echo '<div class="lpl-error" style="padding:10px;background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;border-radius:4px;">'
                        . esc_html__( 'Select a saved player.', 'vapfem' ) . '</div>';
                }
                return;
            }

            $post = get_post( $post_id );
            if ( ! $post || $post->post_type !== 'lean_player' ) {
                if ( leanpl_is_elementor_editor() ) {
                    echo '<div class="lpl-error" style="padding:10px;background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;border-radius:4px;">'
                        . esc_html__( 'Player not found.', 'vapfem' ) . '</div>';
                }
                return;
            }

            echo Player_Shortcode::get_instance()->render_player_shortcode( [ 'id' => $post_id ], null );
            return;
        }

        // Manual mode: build config from widget settings and render directly.
        $config   = $this->elementor_to_audio_config_adapter( $settings );
        $renderer = Player_Renderer::get_instance();
        $renderer->render_audio_player( $config );
    }

    /**
     * Build the dropdown list of saved audio players for the widget panel.
     * Returns an array of [ post_id => title ] with a blank first option.
     */
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
                    'value' => 'audio',
                ],
            ],
        ] );

        foreach ( $players as $player ) {
            $title = $player->post_title !== ''
                ? sprintf( '%s (%d)', $player->post_title, $player->ID )
                /* translators: %d: Player post ID, shown when the player has no title */
                : sprintf( esc_html__( 'Player #%d', 'vapfem' ), $player->ID );

            $options[ $player->ID ] = $title;
        }

        return $options;
    }

    /**
     * Convert Elementor settings to flattened audio config format
     */
    private function elementor_to_audio_config_adapter($settings) {
        return [
            'url'                => $this->get_audio_source($settings),
            'autoplay'           => $settings['autoplay'] === 'true',
            'muted'              => $settings['muted'] === 'true',
            'loop'               => $settings['loop'] === 'true',
            'invert_time'        => $settings['invert_time'] === 'true',
            'seek_time'          => intval($settings['seek_time']),
            'tooltips_seek'      => $settings['tooltips_seek'] === 'true',
            'keyboard_focused'   => $settings['keyboard_focused'] === 'true',
            'keyboard_global'    => $settings['keyboard_global'] === 'true',
            'speed_selected'     => $this->convert_speed($settings),
            'preload'            => $settings['preload'],
            'controls'           => $settings['controls'],
            'debug_mode'         => $settings['debug_mode'] === 'true',
            'poster'             => $settings['poster']['url'] ?? '',
            'audio_title'        => $settings['audio_title'] ?? '',
        ];
    }

    /**
     * Get audio source URL based on source type
     */
    private function get_audio_source($settings) {
        if ($settings['src_type'] === 'upload') {
            return isset($settings['audio_upload']['url']) ? $settings['audio_upload']['url'] : '';
        } else {
            return isset($settings['audio_link']['url']) ? $settings['audio_link']['url'] : '';
        }
    }

    /**
     * Convert Elementor speed format to clean format
     */
    private function convert_speed($settings) {
        $speed = $settings['speed_selected'] ?? 'speed_1';
        return substr($speed, 6); // Remove 'speed_' prefix
    }
}
