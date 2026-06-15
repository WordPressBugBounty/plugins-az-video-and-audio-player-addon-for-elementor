<?php
use LeanPL\Playlist\Playlist;

require_once __DIR__ . '/playlist/trait-content-controls.php';

class LeanPL_Playlist_Widget extends \Elementor\Widget_Base {

    use LeanPL_Playlist_Content_Controls;

    public function get_name() {
        return 'lpl_playlist';
    }

    public function get_title() {
        return esc_html__( 'Playlist', 'vapfem' );
    }

    public function get_icon() {
        return 'az_icon eicon-play';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_keywords() {
        return [ 'lean', 'leanpl', 'playlist', 'player', 'video', 'audio' ];
    }

    public function get_style_depends() {
        return [ 'plyr', 'leanpl-main', 'leanpl-playlist' ];
    }

    public function get_script_depends() {
        return [ 'plyr', 'leanpl-player-utils', 'leanpl-main', 'leanpl-playlist', 'leanpl-elementor' ];
    }

    protected function _register_controls() {
        $this->register_content_controls();
    }

    protected function render() {
        $settings    = $this->get_settings_for_display();
        $playlist_id = absint( $settings['playlist_id'] ?? 0 );

        if ( ! $playlist_id ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div style="padding:16px;background:#f0f0f0;border-radius:4px;color:#555;">'
                    . esc_html__( 'Select a playlist to preview it here.', 'vapfem' )
                    . '</div>';
            }
            return;
        }

        $post = get_post( $playlist_id );
        if ( ! $post || $post->post_type !== 'lean_playlist' ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div style="padding:16px;background:#f8d7da;border-radius:4px;color:#721c24;">'
                    . esc_html__( 'Playlist not found.', 'vapfem' )
                    . '</div>';
            }
            return;
        }

        // @future: render live playlist preview inside the Elementor editor
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            echo '<div style="padding:20px;background:#f0f0f0;border-radius:6px;color:#555;text-align:center;line-height:1.6;">'
                . '<strong style="display:block;margin-bottom:6px;">' . esc_html( get_the_title( $playlist_id ) ) . '</strong>'
                . '<span style="font-size:12px;">' . esc_html__( 'Live preview in the editor is on our roadmap. The playlist renders correctly on the frontend.', 'vapfem' ) . '</span>'
                . '</div>';
            return;
        }

        echo Playlist::get_instance()->render_shortcode( [ 'id' => $playlist_id ] );
    }

    private function get_playlist_options() {
        $options   = [ '' => esc_html__( '— Select —', 'vapfem' ) ];
        $playlists = get_posts( [
            'post_type'   => 'lean_playlist',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ] );

        foreach ( $playlists as $playlist ) {
            $options[ $playlist->ID ] = $playlist->post_title !== '' ? $playlist->post_title : sprintf( esc_html__( 'Playlist #%d', 'vapfem' ), $playlist->ID );
        }

        return $options;
    }
}
