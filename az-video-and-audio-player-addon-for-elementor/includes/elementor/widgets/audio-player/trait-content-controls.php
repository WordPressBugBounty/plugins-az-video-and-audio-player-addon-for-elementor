<?php
use Elementor\Modules\DynamicTags\Module as TagsModule;

trait LeanPL_Audio_Player_Content_Controls {

    protected function register_content_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__( 'General Options', 'vapfem' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Player Source: Manual (legacy, default) or a saved player from the
        // Player Manager. Default 'manual' keeps every existing widget rendering
        // exactly as before (older widgets have no stored value and fall back here).
        $this->add_control(
            'player_source',
            [
                'label' => esc_html__( 'Player Source', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'manual',
                'options' => [
                    'manual' => esc_html__( 'Manual (set source here)', 'vapfem' ),
                    'saved'  => esc_html__( 'Saved Player', 'vapfem' ),
                ],
                'description' => esc_html__( 'Pick a saved player from the Player Manager, or set the source manually below.', 'vapfem' ),
            ]
        );

        // Saved player picker. Lists only audio-type saved players.
        $this->add_control(
            'saved_player_id',
            [
                'label' => esc_html__( 'Select Player', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_saved_players_options(),
                'label_block' => true,
                'description' => esc_html__( "The saved player's own settings apply. Styling below still works.", 'vapfem' ),
                'condition' => [
                    'player_source' => 'saved',
                ],
            ]
        );

        $this->add_control(
            'src_type',
            [
                'label' => esc_html__( 'Audio Upload or URL', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'upload',
                'options' => [
                    'upload' => esc_html__( 'Upload Audio', 'vapfem' ),
                    'link' => esc_html__( 'Audio Link', 'vapfem' ),
                ],
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'audio_upload',
            array(
                'label' => esc_html__( 'Upload Audio', 'vapfem' ),
                'type'  => \Elementor\Controls_Manager::MEDIA,
                'media_type' => 'audio',
                'condition' => array(
                    'player_source' => 'manual',
                    'src_type' => 'upload',
                ),
            )
        );

        $this->add_control(
            'audio_link',
            [
                'label' => esc_html__( 'Audio Link', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://example.com/music-name.mp3', 'vapfem' ),
                'show_external' => false,
                'default' => [
                    'url' => '',
                    'is_external' => false,
                    'nofollow' => false,
                ],
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        TagsModule::URL_CATEGORY,
                    ],
                ],
                'condition' => [
                    'player_source' => 'manual',
                    'src_type'    =>  'link',
                ]
            ]
        );

        $this->add_control(
            'poster',
            [
                'label'     => esc_html__( 'Poster Image', 'vapfem' ),
                'type'      => \Elementor\Controls_Manager::MEDIA,
                'separator' => 'before',
                'dynamic'   => [
                    'active'     => true,
                    'categories' => [ TagsModule::MEDIA_CATEGORY ],
                ],
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'audio_title',
            [
                'label'       => esc_html__( 'Track Title', 'vapfem' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__( 'Shown alongside the poster image.', 'vapfem' ),
                'dynamic'     => [ 'active' => true ],
                'condition'   => [
                    'player_source' => 'manual',
                    'poster[url]!'  => '',
                ],
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => esc_html__( 'Autoplay', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Note: Mobile browsers don\'t allow autoplay for Audio. Some desktop or laptop browsers also automatically block videos from automatically playing or may automatically mute the audio.', 'vapfem'),
                'label_on' => esc_html__( 'Yes', 'vapfem' ),
                'label_off' => esc_html__( 'No', 'vapfem' ),
                'return_value' => 'true',
                'default' => '',
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'muted',
            [
                'label' => esc_html__( 'Start Muted', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Enable this to start playback muted. This is also usefull if you experience autoplay is not working from your browser.', 'vapfem'),
                'label_on' => esc_html__( 'Yes', 'vapfem' ),
                'label_off' => esc_html__( 'No', 'vapfem' ),
                'return_value' => 'true',
                'default' => '',
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'loop',
            [
                'label' => esc_html__( 'Loop Playback', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Loop the current media. ', 'vapfem'),
                'label_on' => esc_html__( 'Yes', 'vapfem' ),
                'label_off' => esc_html__( 'No', 'vapfem' ),
                'return_value' => 'true',
                'default' => '',
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'invert_time',
            [
                'label' => esc_html__( 'Display Time As Countdown', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Display the current time as a countdown rather than an incremental counter.', 'vapfem'),
                'label_on' => esc_html__( 'Yes', 'vapfem' ),
                'label_off' => esc_html__( 'No', 'vapfem' ),
                'return_value' => 'true',
                'default' => 'true',
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'seek_time',
            [
                'label' => esc_html__( 'Skip Forward/Back Amount', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'description' => esc_html__('The time, in seconds, to seek when a user hits fast forward or rewind.', 'vapfem'),
                'min' => 5,
                'max' => 100,
                'step' => 1,
                'default' => 10,
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'tooltips_seek',
            [
                'label' => esc_html__( 'Display Seek Tooltip', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Display a seek tooltip to indicate on click where the media would seek to.', 'vapfem'),
                'label_on' => esc_html__( 'Yes', 'vapfem' ),
                'label_off' => esc_html__( 'No', 'vapfem' ),
                'return_value' => 'true',
                'default' => 'true',
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'keyboard_focused',
            [
                'label' => esc_html__( 'Keyboard Shortcuts', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Allow viewers to control playback with keyboard keys (Space, arrow keys, M, F). Works when the player is focused.', 'vapfem'),
                'label_on' => esc_html__( 'Yes', 'vapfem' ),
                'label_off' => esc_html__( 'No', 'vapfem' ),
                'return_value' => 'true',
                'default' => 'true',
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'keyboard_global',
            [
                'label' => esc_html__( 'Global Keyboard Shortcuts', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Works from anywhere on the page - only use with one player per page.', 'vapfem'),
                'label_on' => esc_html__( 'Yes', 'vapfem' ),
                'label_off' => esc_html__( 'No', 'vapfem' ),
                'return_value' => 'true',
                'default' => 'false',
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'speed_selected',
            [
                'label' => esc_html__( 'Starting Playback Speed', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'speed_1',
                'options' => [
                    'speed_.5'  => esc_html__( '0.5', 'vapfem' ),
                    'speed_.75' => esc_html__( '0.75', 'vapfem' ),
                    'speed_1' => esc_html__( '1', 'vapfem' ),
                    'speed_1.25' => esc_html__( '1.25', 'vapfem' ),
                    'speed_1.5' => esc_html__( '1.5', 'vapfem' ),
                ],
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'preload',
            [
                'label'       => esc_html__( 'HTML5 Media Preload', 'vapfem' ),
                'description' => __( '<strong>Metadata</strong><br>Loads only basic media details when the page opens, such as duration. The actual audio/video starts loading when the visitor presses play. Recommended for most sites.<br><br><strong>None</strong><br>Does not load the media until the visitor presses play. Best when a page has many players or you want to save bandwidth.<br><br><strong>Auto</strong><br>Tells the browser to start loading the media early, before the visitor presses play. Use only when this media is important and most visitors are likely to play it.', 'vapfem' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'default'     => 'metadata',
                'options'     => [
                    'metadata' => esc_html__( 'Metadata (Recommended)', 'vapfem' ),
                    'none'     => esc_html__( 'None', 'vapfem' ),
                    'auto'     => esc_html__( 'Auto', 'vapfem' ),
                ],
                'separator'   => 'before',
                'condition'   => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'controls',
            [
                'label' => esc_html__( 'Control Options', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'description'   =>  esc_html__('Add/Remove your prefered audio control options'),
                'multiple' => true,
                'options' => [
                    'play' => esc_html__( 'Play Icon', 'vapfem' ),
                    'progress' => esc_html__( 'Progress Bar', 'vapfem' ),
                    'mute' => esc_html__( 'Mute Icon', 'vapfem' ),
                    'volume' => esc_html__( 'Volume Bar', 'vapfem' ),
                    'settings' => esc_html__( 'Settings Icon', 'vapfem' ),
                    'airplay' => esc_html__( 'Airplay Icon', 'vapfem' ),
                    'download' => esc_html__( 'Download Button', 'vapfem' ),
                ],
                'default' => [ 'play', 'progress', 'mute', 'volume', 'settings' ],
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'debug_section',
            [
                'label' => esc_html__( 'Debugging', 'vapfem' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'debug_mode',
            [
                'label' => esc_html__( 'Debug Mode', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Enable it when the player does not work properly. When debug is enable, the browser will show the informations about this player in the browser console. This is helpful for developer.', 'vapfem'),
                'label_on' => esc_html__( 'Yes', 'vapfem' ),
                'label_off' => esc_html__( 'No', 'vapfem' ),
                'return_value' => 'true',
                'default' => 'false',
            ]
        );

        $this->end_controls_section();
    }
}
