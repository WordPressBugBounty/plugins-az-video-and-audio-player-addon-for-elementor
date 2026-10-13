<?php
use LeanPL\Shortcodes\Player_Shortcode;
use Elementor\Modules\DynamicTags\Module as TagsModule;

/**
 * Saved Player mode extras shared by the Video and Audio Player widgets:
 * the Edit Player button, the "Source Override" section (the override box
 * with its poster and, for audio, title), and the saved-player render that
 * applies them.
 */
trait LeanPL_Saved_Player_Extras {

    /**
     * The Edit Player button, right under the saved player picker.
     *
     * @param string $player_type 'video' or 'audio'.
     */
    protected function register_saved_player_extras($player_type) {
        $this->add_control(
            'edit_saved_player',
            [
                'type'        => \Elementor\Controls_Manager::BUTTON,
                'text'        => esc_html__('Edit Player', 'vapfem'),
                'event'       => 'leanpl:editSavedPlayer',
                'description' => esc_html__('Opens in a new tab. Reload this page after saving to see your changes.', 'vapfem'),
                'condition'   => [
                    'player_source'    => 'saved',
                    'saved_player_id!' => '',
                ],
            ]
        );
    }

    /**
     * The "Source Override" section, shown in Saved Player mode once a
     * player is picked: without one there is nothing to override.
     *
     * The poster and title boxes show for every saved player, not only when
     * the override has a value: Elementor conditions can't see a value that
     * comes from a dynamic tag, so a condition on the override would hide
     * them exactly when an ACF field fills it.
     *
     * @param string $player_type 'video' or 'audio'.
     */
    protected function register_source_override_section($player_type) {
        $is_audio = 'audio' === $player_type;

        $this->start_controls_section(
            'source_override_section',
            [
                'label'     => esc_html__('Source Override', 'vapfem'),
                'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'player_source'    => 'saved',
                    'saved_player_id!' => '',
                ],
            ]
        );

        $this->add_control(
            'saved_source_override',
            [
                'label'       => $is_audio
                    ? esc_html__('Audio Source Override (optional)', 'vapfem')
                    : esc_html__('Video Source Override (optional)', 'vapfem'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => $is_audio
                    ? esc_html__('e.g. https://example.com/episode.mp3', 'vapfem')
                    : esc_html__('e.g. https://vimeo.com/22439234', 'vapfem'),
                'description' => $is_audio
                    ? esc_html__('Keeps this player\'s look and settings but plays this audio file instead. Leave empty to play the saved audio.', 'vapfem')
                    : esc_html__('Keeps this player\'s look and settings but plays this video instead. YouTube, Vimeo, Bunny or a video file link, or an ID. Leave empty to play the saved video.', 'vapfem'),
                'dynamic'     => [
                    'active'     => true,
                    'categories' => [
                        TagsModule::TEXT_CATEGORY,
                        TagsModule::URL_CATEGORY,
                    ],
                ],
            ]
        );

        $this->add_control(
            'saved_override_poster',
            [
                'label'       => esc_html__('Poster for the Override', 'vapfem'),
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'label_block' => true,
                'description' => esc_html__('Used only when the box above has a link. Pick Featured Image from the dynamic tags to use each post\'s image.', 'vapfem'),
                'dynamic'     => [
                    'active'     => true,
                    'categories' => [
                        TagsModule::IMAGE_CATEGORY,
                        TagsModule::MEDIA_CATEGORY,
                    ],
                ],
            ]
        );

        if ($is_audio) {
            $this->add_control(
                'saved_override_title',
                [
                    'label'       => esc_html__('Track Title for the Override', 'vapfem'),
                    'type'        => \Elementor\Controls_Manager::TEXT,
                    'label_block' => true,
                    'description' => esc_html__('Shown with the poster. Pick Post Title from the dynamic tags to use each post\'s title.', 'vapfem'),
                    'dynamic'     => [
                        'active' => true,
                    ],
                ]
            );
        }

        $this->end_controls_section();
    }

    /**
     * Render the widget in Saved Player mode. The widget owns all validation
     * so render_error() never reaches the frontend.
     *
     * @param array  $settings    Widget settings for display.
     * @param string $player_type 'video' or 'audio'.
     */
    protected function render_saved_player($settings, $player_type) {
        $post_id = absint($settings['saved_player_id'] ?? 0);

        if (!$post_id) {
            if (leanpl_is_elementor_editor()) {
                echo '<div class="lpl-error" style="padding:10px;background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;border-radius:4px;">'
                    . esc_html__('Select a saved player.', 'vapfem') . '</div>';
            }
            return;
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'lean_player') {
            if (leanpl_is_elementor_editor()) {
                echo '<div class="lpl-error" style="padding:10px;background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;border-radius:4px;">'
                    . esc_html__('Player not found.', 'vapfem') . '</div>';
            }
            return;
        }

        $is_audio = 'audio' === $player_type;
        $value    = trim((string) ($settings['saved_source_override'] ?? ''));
        $note     = '';
        $source   = null;

        if ('' !== $value) {
            if (!apply_filters('leanpl/player/source_override_allowed', true, $post_id)) {
                $note = $is_audio
                    ? __('This player is members-only, so its audio source can\'t be overridden here. Showing its own saved audio.', 'vapfem')
                    : __('This player is members-only, so its video source can\'t be overridden here. Showing its own saved video.', 'vapfem');
            } else {
                $source = leanpl_source_override_from_value($value, $player_type, (string) get_post_meta($post_id, '_video_type', true));
                if (null === $source) {
                    $shown = wp_html_excerpt($value, 60, '...');
                    $note  = $is_audio
                        /* translators: %s: what was typed or pulled into the Audio Source Override box */
                        ? sprintf(__('Couldn\'t read "%s" as an audio link. Showing the saved player\'s own audio.', 'vapfem'), $shown)
                        /* translators: %s: what was typed or pulled into the Video Source Override box */
                        : sprintf(__('Couldn\'t read "%s" as a video link or ID. Showing the saved player\'s own video.', 'vapfem'), $shown);
                }
            }
        }

        if ($note && leanpl_is_elementor_editor()) {
            echo '<div class="lpl-override-note" style="padding:10px;margin-bottom:8px;background:#fff8e5;border:1px solid #f0c33c;color:#5c4400;border-radius:4px;">'
                . esc_html($note) . '</div>';
        }

        $shortcode = Player_Shortcode::get_instance();

        if ($source) {
            $poster           = (string) ($settings['saved_override_poster']['url'] ?? '');
            $source['poster'] = '' !== $poster ? esc_url_raw($poster) : null;
            if ($is_audio) {
                $source['audio_title'] = trim((string) ($settings['saved_override_title'] ?? ''));
            }
            echo $shortcode->render_with_source($post_id, $source); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer output, escaped inside.
            return;
        }

        echo $shortcode->render_player_shortcode(['id' => $post_id], null); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Renderer output, escaped inside.
    }
}
