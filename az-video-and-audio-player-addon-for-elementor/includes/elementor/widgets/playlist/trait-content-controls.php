<?php

trait LeanPL_Playlist_Content_Controls {

    protected function register_content_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'Playlist', 'vapfem' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'playlist_id',
            [
                'label'       => esc_html__( 'Select Playlist', 'vapfem' ),
                'type'        => \Elementor\Controls_Manager::SELECT2,
                'options'     => $this->get_playlist_options(),
                'label_block' => true,
                'description' => esc_html__( 'Pick a playlist from the Playlist Manager.', 'vapfem' ),
            ]
        );

        $this->end_controls_section();
    }
}
