<?php
namespace LeanPL;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Demo Players Class
 * 
 * Creates demo players automatically for new plugin installations
 * to help users get started quickly with common use cases.
 */
class Demo_Players {
    
    /**
     * Create all demo players
     * 
     * @return void
     */
    public static function create_demo_players() {
        $demos = self::get_demo_configurations();
        
        foreach ($demos as $demo) {
            self::create_single_player($demo);
        }
    }
    
    /**
     * Get demo player configurations
     * 
     * Returns array of 6 demo players covering common use cases.
     * All demos use FREE features only.
     * 
     * @return array Array of demo configurations
     */
    private static function get_demo_configurations() {
        return [
            [
                'title' => __('Demo: YouTube Video Player', 'vapfem'),
                'meta' => [
                    '_player_type' => 'video',
                    '_video_type' => 'youtube',
                    '_youtube_url' => 'https://www.youtube.com/watch?v=bTqVqk7FSmY',
                ]
            ],
            [
                'title' => __('Demo: Vimeo Video Player', 'vapfem'),
                'meta' => [
                    '_player_type' => 'video',
                    '_video_type' => 'vimeo',
                    '_vimeo_url' => 'https://vimeo.com/76979871',
                ]
            ],
            [
                'title' => __('Demo: HTML5 Video Player', 'vapfem'),
                'meta' => [
                    '_player_type' => 'video',
                    '_video_type' => 'html5',
                    '_html5_source_type' => 'link',
                    '_html5_video_url' => 'https://cdn.plyr.io/static/demo/View_From_A_Blue_Moon_Trailer-576p.mp4',
                ]
            ],
            [
                'title' => __('Demo: YouTube Video Player - Auto Play (Muted)', 'vapfem'),
                'meta' => [
                    '_player_type' => 'video',
                    '_video_type' => 'youtube',
                    '_youtube_url' => 'https://www.youtube.com/watch?v=bTqVqk7FSmY',
                    '_autoplay' => '1',
                    '_muted' => '1',
                ]
            ],
            [
                'title' => __('Demo: Audio / Podcast Player - Speed 1.25', 'vapfem'),
                'meta' => [
                    '_player_type' => 'audio',
                    '_audio_source_type' => 'link',
                    '_html5_audio_url' => 'https://download.samplelib.com/mp3/sample-15s.mp3',
                    '_speed_selected' => '1.25',
                    '_volume' => 80,
                ]
            ]
        ];
    }
    
    /**
     * Create a single demo player
     * 
     * @param array $config Player configuration with 'title' and 'meta' keys
     * @return int|WP_Error Post ID on success, WP_Error on failure
     */
    private static function create_single_player($config) {
        // Get current user ID, fallback to admin (ID 1)
        $author_id = get_current_user_id();
        if (!$author_id) {
            $author_id = 1;
        }

        // For old user make post status draft
        if (get_option('vapfem_installed_time')) {
            $post_status = 'draft';
        } else {
            $post_status = 'publish';
        }
        
        $post_id = wp_insert_post([
            'post_title' => $config['title'],
            'post_type' => 'lean_player',
            'post_status' => $post_status,
            'post_author' => $author_id,
        ]);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Add all meta fields
            foreach ($config['meta'] as $key => $value) {
                update_post_meta($post_id, $key, $value);
            }
        }
        
        return $post_id;
    }
}

