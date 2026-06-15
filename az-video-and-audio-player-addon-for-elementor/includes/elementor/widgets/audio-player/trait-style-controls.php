<?php
trait LeanPL_Audio_Player_Style_Controls {

    protected function register_style_controls() {

        // --- Colors (free: Player Accent Color + pro controls) ---
        $this->start_controls_section(
            'style_colors_section',
            [
                'label' => esc_html__( 'Colors', 'vapfem' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'primary_color',
            [
                'label'     => esc_html__( 'Player Accent Color', 'vapfem' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .plyr' => '--plyr-color-main: {{VALUE}}; --plyr-range-fill-background: {{VALUE}}; --plyr-range-thumb-background: {{VALUE}};',
                ],
            ]
        );

        $this->add_control( 'heading_colors_controls', [ 'label' => esc_html__( 'Controls', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_control(
            'pv_control_color',
            [
                'label'       => esc_html__( 'Controls Bar Color', 'vapfem' ),
                'type'        => \Elementor\Controls_Manager::COLOR,
                'description' => __( 'Colors all icons and text inside the controls bar, including the timer. Use Timer Color in Per Element Style to override just the timer.', 'vapfem' ),
                'selectors'   => [ '{{WRAPPER}} .plyr' => '--plyr-audio-control-color: {{VALUE}};' ],
            ]
        );

        $this->end_controls_section();

        // --- Poster Card (free) ---
        $this->start_controls_section(
            'style_poster_card_section',
            [
                'label' => esc_html__( 'Poster Card', 'vapfem' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control( 'heading_card_colors', [ 'label' => esc_html__( 'Colors', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING ] );
        $this->add_control(
            'card_bg_color',
            [
                'label'     => esc_html__( 'Background', 'vapfem' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .lpl-player-wrap.lpl-audio.has-poster' => '--lpl-audio-bg: {{VALUE}};' ],
            ]
        );
        $this->add_control(
            'card_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'vapfem' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .lpl-player-wrap.lpl-audio.has-poster' => '--lpl-audio-text: {{VALUE}};' ],
            ]
        );
        $this->add_control(
            'card_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'vapfem' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .lpl-player-wrap.lpl-audio.has-poster' => '--lpl-audio-border: {{VALUE}};' ],
            ]
        );

        $this->add_control( 'heading_card_spacing', [ 'label' => esc_html__( 'Spacing', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_control(
            'card_padding',
            [
                'label'      => esc_html__( 'Padding', 'vapfem' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors'  => [ '{{WRAPPER}} .lpl-player-wrap.lpl-audio.has-poster' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
            ]
        );

        $this->add_control( 'heading_card_shape', [ 'label' => esc_html__( 'Shape', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_control(
            'card_radius',
            [
                'label'       => esc_html__( 'Card Radius', 'vapfem' ),
                'type'        => \Elementor\Controls_Manager::SLIDER,
                'size_units'  => [ 'px' ],
                'range'       => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'   => [ '{{WRAPPER}} .lpl-player-wrap.lpl-audio.has-poster' => '--lpl-audio-radius: {{SIZE}}{{UNIT}};' ],
            ]
        );
        $this->add_control(
            'card_thumb_radius',
            [
                'label'      => esc_html__( 'Thumbnail Radius', 'vapfem' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
                'selectors'  => [ '{{WRAPPER}} .lpl-audio-poster' => 'border-radius: {{SIZE}}{{UNIT}};' ],
            ]
        );

        $this->end_controls_section();

        // --- Per Element Style (Pro) ---
        $this->start_controls_section(
            'style_detailed_colors_section',
            [
                'label' => esc_html__( 'Per Element Style (Pro)', 'vapfem' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_pro_note_control( 'style_detailed_colors_section' );

        // Play Button popover
        $this->add_control( 'popover_play', [ 'label' => esc_html__( 'Play Button', 'vapfem' ), 'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes' ] );
        $this->start_popover();
        $this->add_control( 'heading_play_normal', [ 'label' => esc_html__( 'Normal', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'condition' => [ 'popover_play' => 'yes' ] ] );
        $this->add_control( 'play_icon_bg_color',  [ 'label' => esc_html__( 'BG Color', 'vapfem' ),   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_play' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="play"]'     => 'background-color:{{VALUE}}' ] ] );
        $this->add_control( 'play_icon_color',     [ 'label' => esc_html__( 'Icon Color', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_play' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="play"] svg' => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'heading_play_hover', [ 'label' => esc_html__( 'Hover', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'popover_play' => 'yes' ] ] );
        $this->add_control( 'play_icon_hover_bg_color', [ 'label' => esc_html__( 'BG Color', 'vapfem' ),   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_play' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="play"]:hover'     => 'background-color:{{VALUE}}' ] ] );
        $this->add_control( 'play_icon_hover_color',    [ 'label' => esc_html__( 'Icon Color', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_play' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="play"]:hover svg' => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'heading_play_border', [ 'label' => esc_html__( 'Border', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'popover_play' => 'yes' ] ] );
        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'play_icon_border', 'condition' => [ 'popover_play' => 'yes' ], 'selector' => '{{WRAPPER}} .plyr__control[data-plyr="play"]' ] );
        $this->end_popover();

        // Progress Bar popover
        $this->add_control( 'popover_progress', [ 'label' => esc_html__( 'Progress Bar', 'vapfem' ), 'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes' ] );
        $this->start_popover();
        $this->add_control( 'heading_progress_vars', [ 'label' => esc_html__( 'Colors', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'condition' => [ 'popover_progress' => 'yes' ] ] );
        $this->add_control( 'pa_range_fill',        [ 'label' => esc_html__( 'Fill (Progress + Volume)', 'vapfem' ),   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_progress' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-range-fill-background: {{VALUE}};' ] ] );
        $this->add_control( 'pa_progress_buffered', [ 'label' => esc_html__( 'Buffered', 'vapfem' ),                   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_progress' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-audio-progress-buffered-background: {{VALUE}};' ] ] );
        $this->add_control( 'pa_range_thumb',       [ 'label' => esc_html__( 'Handle (Progress + Volume)', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_progress' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-range-thumb-background: {{VALUE}};' ] ] );
        $this->add_control( 'heading_progress_overrides', [ 'label' => esc_html__( 'Browser Overrides', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'popover_progress' => 'yes' ] ] );
        $this->add_control( 'pbar_pointer_color', [ 'label' => esc_html__( 'Handle', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_progress' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__progress__container input[type=range]::-webkit-slider-thumb' => 'background:{{VALUE}}', '{{WRAPPER}} .plyr__progress__container input[type=range]::-moz-range-thumb' => 'background:{{VALUE}}', '{{WRAPPER}} .plyr__progress__container input[type=range]::-ms-thumb' => 'background:{{VALUE}}' ] ] );
        $this->add_control(
            'pbar_color_1',
            [
                'label'       => esc_html__( 'Track', 'vapfem' ),
                'type'        => \Elementor\Controls_Manager::COLOR,
                'condition'   => [ 'popover_progress' => 'yes' ],
                'description' => __( 'Use RGBA with some transparency so the buffered layer underneath stays visible, e.g. <code>rgba(255,255,255,0.3)</code>.', 'vapfem' ),
                'selectors'   => [
                    '{{WRAPPER}} .plyr__progress input[type=range]::-webkit-slider-runnable-track' => 'background-color:{{VALUE}}',
                    '{{WRAPPER}} .plyr__progress input[type=range]::-moz-range-track'              => 'background-color:{{VALUE}}',
                    '{{WRAPPER}} .plyr__progress input[type=range]::-ms-track'                     => 'background-color:{{VALUE}}',
                ],
            ]
        );
        $this->add_control( 'pbar_color_2',      [ 'label' => esc_html__( 'Played', 'vapfem' ),  'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_progress' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__progress__container input[type=range]' => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'pbar_buffer_color', [ 'label' => esc_html__( 'Buffered', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_progress' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr--audio .plyr__progress__buffer' => 'color:{{VALUE}}' ] ] );
        $this->end_popover();

        // Volume popover
        $this->add_control( 'popover_volume', [ 'label' => esc_html__( 'Volume', 'vapfem' ), 'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes' ] );
        $this->start_popover();
        $this->add_control( 'heading_volume_icon', [ 'label' => esc_html__( 'Icon', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'condition' => [ 'popover_volume' => 'yes' ] ] );
        $this->add_control( 'volume_icon_bg_color',       [ 'label' => esc_html__( 'BG Color', 'vapfem' ),   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_volume' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="mute"]'          => 'background-color:{{VALUE}}' ] ] );
        $this->add_control( 'volume_icon_color',          [ 'label' => esc_html__( 'Icon Color', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_volume' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="mute"] svg'      => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'volume_icon_hover_bg_color', [ 'label' => esc_html__( 'Hover BG', 'vapfem' ),   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_volume' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="mute"]:hover'     => 'background-color:{{VALUE}}' ] ] );
        $this->add_control( 'volume_icon_hover_color',    [ 'label' => esc_html__( 'Hover Icon', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_volume' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="mute"]:hover svg' => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'heading_volume_slider', [ 'label' => esc_html__( 'Slider', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'popover_volume' => 'yes' ] ] );
        $this->add_control( 'vbar_color',           [ 'label' => esc_html__( 'Played', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_volume' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__volume input[type=range]' => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'vbar_remaining_color', [ 'label' => esc_html__( 'Empty', 'vapfem' ),  'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_volume' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__volume input[type=range]::-webkit-slider-runnable-track' => 'background-color:{{VALUE}}', '{{WRAPPER}} .plyr__volume input[type=range]::-moz-range-track' => 'background-color:{{VALUE}}', '{{WRAPPER}} .plyr__volume input[type=range]::-ms-track' => 'background-color:{{VALUE}}' ] ] );
        $this->add_control( 'vbar_pointer_color',   [ 'label' => esc_html__( 'Handle', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_volume' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__volume input[type=range]::-webkit-slider-thumb' => 'background:{{VALUE}}', '{{WRAPPER}} .plyr__volume input[type=range]::-moz-range-thumb' => 'background:{{VALUE}}', '{{WRAPPER}} .plyr__volume input[type=range]::-ms-thumb' => 'background:{{VALUE}}' ] ] );
        $this->add_control( 'heading_volume_border', [ 'label' => esc_html__( 'Border', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'popover_volume' => 'yes' ] ] );
        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'volume_icon_border', 'condition' => [ 'popover_volume' => 'yes' ], 'selector' => '{{WRAPPER}} .plyr__control[data-plyr="mute"]' ] );
        $this->end_popover();

        // Settings Icon popover
        $this->add_control( 'popover_settings', [ 'label' => esc_html__( 'Settings Icon', 'vapfem' ), 'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes' ] );
        $this->start_popover();
        $this->add_control( 'heading_settings_normal', [ 'label' => esc_html__( 'Normal', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'condition' => [ 'popover_settings' => 'yes' ] ] );
        $this->add_control( 'settings_icon_bg_color',  [ 'label' => esc_html__( 'BG Color', 'vapfem' ),   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_settings' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="settings"]'     => 'background-color:{{VALUE}}' ] ] );
        $this->add_control( 'settings_icon_color',     [ 'label' => esc_html__( 'Icon Color', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_settings' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="settings"] svg' => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'heading_settings_hover', [ 'label' => esc_html__( 'Hover', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'popover_settings' => 'yes' ] ] );
        $this->add_control( 'settings_icon_hover_bg_color', [ 'label' => esc_html__( 'BG Color', 'vapfem' ),   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_settings' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="settings"]:hover'     => 'background-color:{{VALUE}}' ] ] );
        $this->add_control( 'settings_icon_hover_color',    [ 'label' => esc_html__( 'Icon Color', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_settings' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="settings"]:hover svg' => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'heading_settings_border', [ 'label' => esc_html__( 'Border', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'popover_settings' => 'yes' ] ] );
        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'settings_icon_border', 'condition' => [ 'popover_settings' => 'yes' ], 'selector' => '{{WRAPPER}} .plyr__control[data-plyr="settings"]' ] );
        $this->end_popover();

        // Download Button popover (audio-only)
        $this->add_control( 'popover_download', [ 'label' => esc_html__( 'Download Button', 'vapfem' ), 'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes' ] );
        $this->start_popover();
        $this->add_control( 'heading_download_normal', [ 'label' => esc_html__( 'Normal', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'condition' => [ 'popover_download' => 'yes' ] ] );
        $this->add_control( 'download_icon_bg_color',  [ 'label' => esc_html__( 'BG Color', 'vapfem' ),   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_download' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="download"]'     => 'background-color:{{VALUE}}' ] ] );
        $this->add_control( 'download_icon_color',     [ 'label' => esc_html__( 'Icon Color', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_download' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="download"] svg' => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'heading_download_hover', [ 'label' => esc_html__( 'Hover', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'popover_download' => 'yes' ] ] );
        $this->add_control( 'download_icon_hover_bg_color', [ 'label' => esc_html__( 'BG Color', 'vapfem' ),   'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_download' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="download"]:hover'     => 'background-color:{{VALUE}}' ] ] );
        $this->add_control( 'download_icon_hover_color',    [ 'label' => esc_html__( 'Icon Color', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_download' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__control[data-plyr="download"]:hover svg' => 'color:{{VALUE}}' ] ] );
        $this->add_control( 'heading_download_border', [ 'label' => esc_html__( 'Border', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before', 'condition' => [ 'popover_download' => 'yes' ] ] );
        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), [ 'name' => 'download_icon_border', 'condition' => [ 'popover_download' => 'yes' ], 'selector' => '{{WRAPPER}} .plyr__control[data-plyr="download"]' ] );
        $this->end_popover();

        // Tooltips popover
        $this->add_control( 'popover_tooltips', [ 'label' => esc_html__( 'Tooltips', 'vapfem' ), 'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes' ] );
        $this->start_popover();
        $this->add_control( 'pa_tooltip_bg',    [ 'label' => esc_html__( 'Background', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_tooltips' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-tooltip-background: {{VALUE}};' ] ] );
        $this->add_control( 'pa_tooltip_color', [ 'label' => esc_html__( 'Text Color', 'vapfem' ),  'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_tooltips' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-tooltip-color: {{VALUE}};' ] ] );
        $this->end_popover();

        // Settings Menu popover
        $this->add_control( 'popover_menu', [ 'label' => esc_html__( 'Settings Menu', 'vapfem' ), 'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes' ] );
        $this->start_popover();
        $this->add_control( 'pa_menu_bg',    [ 'label' => esc_html__( 'Background', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_menu' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-menu-background: {{VALUE}};' ] ] );
        $this->add_control( 'pa_menu_color', [ 'label' => esc_html__( 'Text Color', 'vapfem' ),  'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_menu' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-menu-color: {{VALUE}};' ] ] );
        $this->end_popover();

        // Other popover
        $this->add_control( 'popover_other', [ 'label' => esc_html__( 'Other', 'vapfem' ), 'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes' ] );
        $this->start_popover();
        $this->add_control( 'timer_color', [ 'label' => esc_html__( 'Timer Color', 'vapfem' ), 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => [ 'popover_other' => 'yes' ], 'selectors' => [ '{{WRAPPER}} .plyr__controls .plyr__time' => 'color:{{VALUE}}' ] ] );
        $this->end_popover();

        $this->end_controls_section();

        // --- Layout (Pro) ---
        $this->start_controls_section(
            'style_layout_section',
            [
                'label' => esc_html__( 'Layout (Pro)', 'vapfem' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_pro_note_control( 'style_layout_section' );

        $this->add_control( 'heading_layout_controls', [ 'label' => esc_html__( 'Controls', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_control( 'pa_control_icon_size', [ 'label' => esc_html__( 'Icon Size (Controls + Menu)', 'vapfem' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 10, 'max' => 40 ] ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-control-icon-size: {{SIZE}}{{UNIT}};' ] ] );
        $this->add_control(
            'pa_control_spacing',
            [
                'label'       => esc_html__( 'Global Spacing', 'vapfem' ),
                'type'        => \Elementor\Controls_Manager::SLIDER,
                'description' => __( 'Base spacing token used throughout the player - control bar padding, gaps between buttons, captions padding, tooltip offset, and menu item spacing all scale with this value.', 'vapfem' ),
                'size_units'  => [ 'px' ],
                'range'       => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
                'selectors'   => [ '{{WRAPPER}} .plyr' => '--plyr-control-spacing: {{SIZE}}{{UNIT}};' ],
            ]
        );
        $this->add_control( 'pa_control_radius', [ 'label' => esc_html__( 'Corner Radius', 'vapfem' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 0, 'max' => 30 ] ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-control-radius: {{SIZE}}{{UNIT}};' ] ] );

        $this->add_control( 'heading_layout_progress', [ 'label' => esc_html__( 'Progress Bar', 'vapfem' ), 'type' => \Elementor\Controls_Manager::HEADING, 'separator' => 'before' ] );
        $this->add_control( 'pa_range_track_height', [ 'label' => esc_html__( 'Track Height (Progress + Volume)', 'vapfem' ), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => [ 'px' ], 'range' => [ 'px' => [ 'min' => 1, 'max' => 20 ] ], 'selectors' => [ '{{WRAPPER}} .plyr' => '--plyr-range-track-height: {{SIZE}}{{UNIT}};' ] ] );

        $this->end_controls_section();
    }

    private function add_pro_note_control( $section_id ) {
        if ( leanpl_is_pro_active() ) {
            return;
        }

        $url = leanpl_get_upgrade_url( [ 'utm_medium' => 'elementor-style' ] );

        $this->add_control(
            $section_id . '_pro_note',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw'  => sprintf(
                    '<div class="leanpl-pro-note">%s <a href="%s" target="_blank" rel="noopener">%s</a></div>',
                    esc_html__( 'This styling is a Pro feature.', 'vapfem' ),
                    esc_url( $url ),
                    esc_html__( 'Upgrade to Pro', 'vapfem' )
                ),
                'content_classes' => 'leanpl-pro-note-wrap',
            ]
        );
    }
}
