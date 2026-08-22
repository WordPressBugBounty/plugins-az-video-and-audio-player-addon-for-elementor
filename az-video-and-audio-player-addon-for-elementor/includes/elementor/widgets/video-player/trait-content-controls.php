<?php
use Elementor\Modules\DynamicTags\Module as TagsModule;

trait LeanPL_Video_Player_Content_Controls {

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
                    'saved'  => esc_html__( 'Saved Player (Recommended)', 'vapfem' ),
                    'manual' => esc_html__( 'Manual (set source here)', 'vapfem' ),
                ],
                'description' => esc_html__( 'We recommend picking a Saved Player from the Player Manager: it can be reused across widgets and pages, and updated in one place. Manual sets the source directly on this widget instead.', 'vapfem' ),
            ]
        );

        // Saved player picker. Lists only video-type saved players.
        $this->add_control(
            'saved_player_id',
            [
                'label' => esc_html__( 'Select Player', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_saved_players_options(),
                'label_block' => true,
                'description' => esc_html__( 'The saved player\'s own settings apply. Styling below still works.', 'vapfem' ),
                'condition' => [
                    'player_source' => 'saved',
                ],
            ]
        );

        $this->add_control(
            'video_type',
            [
                'label' => esc_html__( 'Video Type', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'youtube',
                'options' => [
                    'youtube'  => esc_html__( 'Youtube', 'vapfem' ),
                    'vimeo' => esc_html__( 'Vimeo', 'vapfem' ),
                    'html5' => esc_html__( 'HTML5', 'vapfem' ),
                ],
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'youtube_video_id',
            [
                'label' => esc_html__( 'Youtube Video ID', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'bTqVqk7FSmY', 'vapfem' ),
                'placeholder' => esc_html__( 'Put your video id here', 'vapfem' ),
                'separator' => 'before',
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        TagsModule::TEXT_CATEGORY,
                    ],
                ],
                'condition' => [
                    'player_source' => 'manual',
                    'video_type'    =>  'youtube',
                ]
            ]
        );


        $this->add_control(
            'vimeo_video_id',
            [
                'label' => esc_html__( 'Vimeo Video ID', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( '76979871', 'vapfem' ),
                'placeholder' => esc_html__( 'Put your video id here', 'vapfem' ),
                'separator' => 'before',
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        TagsModule::TEXT_CATEGORY,
                    ],
                ],
                'condition' => [
                    'player_source' => 'manual',
                    'video_type'    =>  'vimeo',
                ]
            ]
        );

        $this->add_control(
            'custom_poster',
            [
                'label' => esc_html__( 'Add Custom Poster', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
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
            'poster',
            [
                'label' => esc_html__( 'Custom Poster For Video', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        TagsModule::MEDIA_CATEGORY,
                    ],
                ],
                'condition' => [
                    'custom_poster'    =>  'true',
                ]
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'src_type',
            [
                'label' => esc_html__( 'Video Source', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'link',
                'options' => [
                    'upload' => esc_html__( 'Upload Video', 'vapfem' ),
                    'link' => esc_html__( 'Put Video Link', 'vapfem' ),
                ],
            ]
        );
        $repeater->add_control(
            'video_upload',
            [
                'label' => esc_html__( 'Upload Video', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'dynamic' => [
                    'active' => true,
                    'categories' => [
                        TagsModule::MEDIA_CATEGORY,
                    ],
                ],
                'media_type' => 'video',
                'condition' => [
                    'src_type'    =>  'upload',
                ]
            ]
        );


        $repeater->add_control(
            'video_link',
            [
                'label' => esc_html__( 'Video Link', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__( 'https://your-link.com', 'vapfem' ),
                'show_external' => false,
                'default' => [
                    'url' => 'https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4',
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
                    'src_type'    =>  'link',
                ]
            ]
        );

        $repeater->add_control(
            'video_size',
            [
                'label' => esc_html__( 'Video Size', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__( 'Select', 'vapfem' ),
                    '240' => esc_html__( '240', 'vapfem' ),
                    '360' => esc_html__( '360', 'vapfem' ),
                    '480' => esc_html__( '480', 'vapfem' ),
                    '576' => esc_html__( '576', 'vapfem' ),
                    '720' => esc_html__( '720', 'vapfem' ),
                    '1080' => esc_html__( '1080', 'vapfem' ),
                    '1440' => esc_html__( '1440', 'vapfem' ),
                    '2160' => esc_html__( '2160', 'vapfem' ),
                    '2880' => esc_html__( '2880', 'vapfem' ),
                    '4320'  => esc_html__( '4320', 'vapfem' ),
                ],
            ]
        );

        $this->add_control(
            'video_list',
            [
                'label' => esc_html__( 'Video List', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'video_link' => 'https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4',
                        'video_size' => esc_html__( '576', 'vapfem' ),
                    ],
                    [
                        'video_link' => 'ttps://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-720p.mp4',
                        'video_size' => esc_html__( '720', 'vapfem' ),
                    ],
                    [
                        'video_link' => 'ttps://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-1080p.mp4',
                        'video_size' => esc_html__( '1080', 'vapfem' ),
                    ],
                ],
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                    'video_type'    =>  'html5',
                ]
            ]
        );

        $this->add_control(
            'autoplay',
            [
                'label' => esc_html__( 'Autoplay', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => __('Autoplay varies for each user by an intelligent system of the browsers. If you experience Autoplay does not work from your browser. Enable the "Start Muted" option below. <br><br>Muted autoplay is always allowed.', 'vapfem'),
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
            'volume',
            [
                'label' => esc_html__( 'Initial Volume', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' =>1,
                    ],
                ],
                'default' => [
                    'unit' => '%',
                    'size' => 100,
                ],
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                ],
            ]
        );

        $this->add_control(
            'click_to_play',
            [
                'label' => esc_html__( 'Click Video to Play/Pause', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description'   => esc_html__('Click (or tap) of the video container will toggle play/pause.','vapfem'),
                'label_on' => esc_html__( 'Enable', 'vapfem' ),
                'label_off' => esc_html__( 'Disable', 'vapfem' ),
                'return_value' => 'true',
                'default' => 'true',
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
            'hide_controls',
            [
                'label' => esc_html__( 'Hide Control Icons After 2s', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Hide video controls automatically after 2s of no mouse or focus movement,', 'vapfem'),
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
            'reset_on_end',
            [
                'label' => esc_html__( 'Reset to Start When Finished', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Back to start after end of playing', 'vapfem'),
                'label_on' => esc_html__( 'Yes', 'vapfem' ),
                'label_off' => esc_html__( 'No', 'vapfem' ),
                'return_value' => 'true',
                'default' => 'false',
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                    'video_type' => 'html5'
                ]
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
                'description' => esc_html__('Works from anywhere on the page — only use with one player per page.', 'vapfem'),
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
            'tooltips_controls',
            [
                'label' => esc_html__( 'Control Button Tooltips', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Display control labels as tooltips on :hover & :focus', 'vapfem'),
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
            'fullscreen_enabled',
            [
                'label' => esc_html__( 'Fullscreen Button', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'description' => esc_html__('Enable fullscreen when double click on the player', 'vapfem'),
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
                    'video_type'    => 'html5'
                ]
            ]
        );

        $this->add_control(
            'quality_default',
            [
                'label' => esc_html__( 'Starting Video Quality', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '576',
                'options' => [
                    '240' => esc_html__( '240', 'vapfem' ),
                    '360' => esc_html__( '360', 'vapfem' ),
                    '480' => esc_html__( '480', 'vapfem' ),
                    '576' => esc_html__( '576', 'vapfem' ),
                    '720' => esc_html__( '720', 'vapfem' ),
                    '1080' => esc_html__( '1080', 'vapfem' ),
                    '1440' => esc_html__( '1440', 'vapfem' ),
                    '2160' => esc_html__( '2160', 'vapfem' ),
                    '2880' => esc_html__( '2880', 'vapfem' ),
                    '4320'  => esc_html__( '4320', 'vapfem' ),
                ],
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                    'video_type' => 'html5'
                ]
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
                    'video_type' => 'html5'
                ],
            ]
        );
        $this->add_control(
            'custom_ratio',
            [
                'label' => esc_html__( 'Enable Custom Ratio', 'vapfem' ),
                'description' => esc_html__( 'Force an aspect ratio.', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
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
            'ratio',
            [
                'label' => esc_html__( 'Ratio', 'vapfem' ),
                'description' => esc_html__( 'The format is \'w:h\' - e.g. 16:9 or 4:3 or other', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder'   =>  esc_html__( '16:9', 'vapfem' ),
                'condition' => [
                    'player_source' => 'manual',
                    'custom_ratio' => 'true'
                ]

            ]
        );

        $this->add_control(
            'player_layout',
            [
                'label'       => esc_html__( 'Player Layout (Recommended)', 'vapfem' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'description' => esc_html__( 'Per-widget layout override. Inherit follows the site default (Settings → Layout & Branding).', 'vapfem' ),
                'default'     => '',
                'options'     => array_merge(
                    [ '' => esc_html__( 'Inherit (site-wide default)', 'vapfem' ) ],
                    wp_list_pluck( leanpl_get_player_layouts(), 'label' )
                ),
                'separator'   => 'before',
                'condition'   => [
                    'player_source' => 'manual',
                ],
            ]
        );

        // Always visible, not gated — informational pointer to the Custom
        // Preset builder, which can't be embedded inside Elementor's own
        // panel (its popup UI is wired to WP admin's field-render hooks,
        // not portable here). Custom Preset building is free; applying one
        // is the actual Pro-gated action (Lock A11), regardless of surface.
        $this->add_control(
            'player_layout_custom_preset_note',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw'  => sprintf(
                    '<div class="leanpl-tip-note"><span class="leanpl-tip-note__icon">💡</span><span>%s %s</span></div>',
                    esc_html__( 'Need a fully custom bar? Build a Custom Preset', 'vapfem' ),
                    sprintf(
                        '<a href="%s" target="_blank" rel="noopener">%s</a> (%s).',
                        esc_url( admin_url( 'admin.php?page=lean-player-settings#settings' ) ),
                        esc_html__( 'here', 'vapfem' ),
                        esc_html__( 'Pro to apply', 'vapfem' )
                    )
                ),
                'content_classes' => 'leanpl-tip-note-wrap',
            ]
        );

        $this->add_control(
            'controls',
            [
                'label' => esc_html__( 'Control Options (Legacy)', 'vapfem' ),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'description'   =>  esc_html__('Legacy control picker; only applies while Layout is Inherit.', 'vapfem'),
                'multiple' => true,
                'options' => [
                    'play-large'  => esc_html__( 'Play Large', 'vapfem' ),
                    'play' => esc_html__( 'Play', 'vapfem' ),
                    'progress' => esc_html__( 'Progress Bar', 'vapfem' ),
                    'current-time' => esc_html__( 'Current Time', 'vapfem' ),
                    'mute' => esc_html__( 'Mute', 'vapfem' ),
                    'volume' => esc_html__( 'Volume', 'vapfem' ),
                    'captions' => esc_html__( 'Caption', 'vapfem' ),
                    'settings' => esc_html__( 'Settings Icon', 'vapfem' ),
                    'pip' => esc_html__( 'PIP', 'vapfem' ),
                    'airplay' => esc_html__( 'Airplay', 'vapfem' ),
                    'fullscreen' => esc_html__( 'Fullscreen', 'vapfem' ),
                ],
                'default' => [ 'play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen' ],
                'separator' => 'before',
                'condition' => [
                    'player_source' => 'manual',
                    'player_layout' => '',
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
