<?php
namespace LeanPL;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Player_Table_Columns {
    private static $instance = null;

    /**
     * Get singleton instance
     * 
     * @return Player_Table_Columns
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Hook into WordPress filters
     */
    public function __construct() {
        add_filter('manage_lean_player_posts_columns', [$this, 'add_column_headers']);
        add_action('manage_lean_player_posts_custom_column', [$this, 'render_column_content'], 10, 2);
        add_action('admin_head', [$this, 'add_column_styles']);
    }

    /**
     * Add custom columns to the posts list table
     * 
     * @param array $columns Existing columns
     * @return array Modified columns array
     */
    public function add_column_headers($columns) {
        // Insert custom columns after 'title' and before 'date'
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            // Insert our columns after 'title'
            if ($key === 'title') {
                $new_columns['lpl_player_type'] = __('Player Type', 'vapfem');
                $new_columns['lpl_source_type'] = __('Source Type', 'vapfem');
                $new_columns['lpl_source'] = __('Source', 'vapfem');
                $new_columns['lpl_autoplay'] = __('Autoplay', 'vapfem');
                $new_columns['lpl_shortcode'] = __('Shortcode', 'vapfem');
            }
        }
        
        return $new_columns;
    }

    /**
     * Populate custom column content
     * 
     * @param string $column_name Column identifier
     * @param int $post_id Post ID
     * @return void
     */
    public function render_column_content($column_name, $post_id) {
        switch ($column_name) {
            case 'lpl_player_type':
                $this->render_player_type($post_id);
                break;
            case 'lpl_source_type':
                $this->render_source_type($post_id);
                break;
            case 'lpl_source':
                $this->render_source($post_id);
                break;
            case 'lpl_autoplay':
                $this->render_autoplay($post_id);
                break;
            case 'lpl_shortcode':
                $this->render_shortcode($post_id);
                break;
        }
    }

    /**
     * Render Player Type column content
     * 
     * @param int $post_id Post ID
     * @return void
     */
    private function render_player_type($post_id) {
        $player_type = Metaboxes::get_field_value($post_id, '_player_type');
        $label = leanpl_get_player_type_label($player_type);
        echo esc_html($label);
    }

    /**
     * Render Source Type column content
     * 
     * @param int $post_id Post ID
     * @return void
     */
    private function render_source_type($post_id) {
        $label = leanpl_get_source_type_label($post_id);
        echo esc_html($label);
    }

    /**
     * Render Source column content
     * 
     * @param int $post_id Post ID
     * @return void
     */
    private function render_source($post_id) {
        $player_type = Metaboxes::get_field_value($post_id, '_player_type');
        
        if ($player_type === 'audio') {
            // Audio source - check source type first
            $audio_source_type = Metaboxes::get_field_value($post_id, '_audio_source_type');
            
            if ($audio_source_type === 'upload') {
                // Uploaded audio file
                $audio_source = Metaboxes::get_field_value($post_id, '_audio_source');
                if ($audio_source) {
                    if (is_numeric($audio_source)) {
                        $filename = leanpl_get_media_filename($audio_source);
                        echo esc_html($filename ?: __('Media ID: ', 'vapfem') . $audio_source);
                    } else {
                        echo esc_html(basename($audio_source));
                    }
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
            } elseif ($audio_source_type === 'link') {
                // External audio URL (CDN/URL)
                $html5_audio_url = Metaboxes::get_field_value($post_id, '_html5_audio_url');
                if ($html5_audio_url) {
                    echo esc_html(basename($html5_audio_url));
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
            } else {
                // Fallback for old data
                $audio_source = Metaboxes::get_field_value($post_id, '_audio_source');
                if ($audio_source) {
                    echo esc_html(basename($audio_source));
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
            }
        } else {
            // Video source
            $video_type = Metaboxes::get_field_value($post_id, '_video_type');
            
            if ($video_type === 'youtube') {
                $youtube_url = Metaboxes::get_field_value($post_id, '_youtube_url');
                if ($youtube_url) {
                    $parsed = leanpl_parse_video_url($youtube_url);
                    $video_id = $parsed['id'] ?: $youtube_url;
                    echo esc_html($video_id);
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
            } elseif ($video_type === 'vimeo') {
                $vimeo_url = Metaboxes::get_field_value($post_id, '_vimeo_url');
                if ($vimeo_url) {
                    $parsed = leanpl_parse_video_url($vimeo_url);
                    $video_id = $parsed['id'] ?: $vimeo_url;
                    echo esc_html($video_id);
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
            } elseif ($video_type === 'html5') {
                $html5_source_type = Metaboxes::get_field_value($post_id, '_html5_source_type');
                
                if ($html5_source_type === 'upload') {
                    $video_source = Metaboxes::get_field_value($post_id, '_video_source');
                    if ($video_source) {
                        if (is_numeric($video_source)) {
                            $filename = leanpl_get_media_filename($video_source);
                            echo esc_html($filename ?: __('Media ID: ', 'vapfem') . $video_source);
                        } else {
                            echo esc_html(basename($video_source));
                        }
                    } else {
                        echo '<span style="color: #999;">—</span>';
                    }
                } elseif ($html5_source_type === 'link') {
                    $html5_url = Metaboxes::get_field_value($post_id, '_html5_video_url');
                    if ($html5_url) {
                        echo esc_html(basename($html5_url));
                    } else {
                        echo '<span style="color: #999;">—</span>';
                    }
                } else {
                    echo '<span style="color: #999;">—</span>';
                }
            } else {
                echo '<span style="color: #999;">—</span>';
            }
        }
    }

    /**
     * Render Autoplay column content
     * 
     * @param int $post_id Post ID
     * @return void
     */
    private function render_autoplay($post_id) {
        $autoplay = Metaboxes::get_field_value($post_id, '_autoplay');
        $is_autoplay = ($autoplay === '1' || $autoplay === 1 || $autoplay === true);
        
        if ($is_autoplay) {
            echo '<span style="color: #46b450;">' . esc_html__('Yes', 'vapfem') . '</span>';
        } else {
            echo '<span style="color: #999;">' . esc_html__('No', 'vapfem') . '</span>';
        }
    }

    /**
     * Render Shortcode column content
     * 
     * @param int $post_id Post ID
     * @return void
     */
    private function render_shortcode($post_id) {
        $shortcode = '[lean_player id="' . esc_attr($post_id) . '"]';
        ?>
            <div class="lpl-shortcode-cell">
            <code class="lpl-shortcode-text" title="<?php echo esc_attr($shortcode); ?>"><?php echo esc_html($shortcode); ?></code>
            <div 
                class="lex-copy-button lex-copy-button--outline" 
                data-lex-copy="<?php echo esc_attr($shortcode); ?>"
            >
                <?php echo esc_html__('Copy', 'vapfem'); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Add CSS styles for table columns
     * 
     * @return void
     */
    public function add_column_styles() {
        $screen = get_current_screen();
        
        // Only add styles on the lean_player post type list page
        if (!$screen || $screen->post_type !== 'lean_player' || $screen->base !== 'edit') {
            return;
        }
        ?>
        <style>
            /* Column width limits - apply only to laptop screens */
            @media (min-width: 1024px) and (max-width: 1919px) {
                /* Limit shortcode column width */
                .wp-list-table .column-lpl_shortcode {
                    width: 250px;
                    max-width: 250px;
                }
                
                /* Limit autoplay column width */
                .wp-list-table .column-lpl_autoplay {
                    width: 70px;
                    max-width: 70px;
                }
                
                /* Limit player type column width */
                .wp-list-table .column-lpl_player_type {
                    width: 90px;
                    max-width: 90px;
                }
                
                /* Limit source type column width */
                .wp-list-table .column-lpl_source_type {
                    width: 110px;
                    max-width: 110px;
                }
            }
            
            /* Shortcode cell container */
            .lpl-shortcode-cell {
                display: flex;
                gap: 5px;
                align-items: center;
                max-width: 100%;
            }
            
            /* Shortcode text with truncation */
            .lpl-shortcode-text {
                display: inline-block;
                max-width: 155px;
                overflow: hidden;
                font-size: 11px;
                background: #e0e0e0;
                padding: 4px 6px;
                border-radius: 3px;
                vertical-align: middle;
            }
            
            /* Copy button - prevent shrinking */
            .lpl-shortcode-cell .lex-copy-button {
                flex-shrink: 0;
            }
        </style>
        <?php
    }
}

Player_Table_Columns::get_instance();

