<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function leanpl_get_player_defaults(){
    return include LEANPL_DIR . '/includes/player-defaults.php';
}

function leanpl_get_video_defaults(){
    $defaults = include LEANPL_DIR . '/includes/player-defaults.php';
    return array_merge($defaults['shared'], $defaults['video']);
}

function leanpl_get_audio_defaults(){
    $defaults = include LEANPL_DIR . '/includes/player-defaults.php';
    return array_merge($defaults['shared'], $defaults['audio']);
}

/**
 * Controls Registry — SSOT for all valid Plyr control slugs.
 *
 * Keys   : Plyr control slug (passed directly to Plyr's `controls` array)
 * Values : [
 *   'label'      => Human-readable label for admin UI
 *   'video_only' => bool — only valid for video players
 *   'audio_only' => bool — only valid for audio players
 *   'default'    => bool — included in the default controls set
 * ]
 *
 * Order here = default order in the metabox checkbox list.
 */
function leanpl_get_controls_registry() {
    return [
        'play-large' => [
            'label'      => __( 'Play Large (Video Only)',   'vapfem' ),
            'video_only' => true,
            'audio_only' => false,
            'default'    => true,
        ],
        'restart' => [
            'label'      => __( 'Restart',      'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => false,
        ],
        'rewind' => [
            'label'      => __( 'Rewind',       'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => false,
        ],
        'play' => [
            'label'      => __( 'Play',         'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => true,
        ],
        'fast-forward' => [
            'label'      => __( 'Fast Forward', 'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => false,
        ],
        'progress' => [
            'label'      => __( 'Progress Bar', 'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => true,
        ],
        'current-time' => [
            'label'      => __( 'Current Time', 'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => true,
        ],
        'duration' => [
            'label'      => __( 'Duration',     'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => false,
        ],
        'mute' => [
            'label'      => __( 'Mute',         'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => true,
        ],
        'volume' => [
            'label'      => __( 'Volume',       'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => true,
        ],
        'captions' => [
            'label'      => __( 'Captions (Video Only)',     'vapfem' ),
            'video_only' => true,
            'audio_only' => false,
            'default'    => true,
        ],
        'settings' => [
            'label'      => __( 'Settings',     'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => true,
        ],
        'pip' => [
            'label'      => __( 'PIP (Video Only)',          'vapfem' ),
            'video_only' => true,
            'audio_only' => false,
            'default'    => true,
        ],
        'airplay' => [
            'label'      => __( 'Airplay',      'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => true,
        ],
        'download' => [
            'label'      => __( 'Download',     'vapfem' ),
            'video_only' => false,
            'audio_only' => false,
            'default'    => false,
        ],
        'fullscreen' => [
            'label'      => __( 'Fullscreen (Video Only)',   'vapfem' ),
            'video_only' => true,
            'audio_only' => false,
            'default'    => true,
        ],
    ];
}

function leanpl_get_playlist_defaults() {
    return include LEANPL_DIR . '/includes/playlist/playlist-defaults.php';
}

require_once LEANPL_DIR . '/includes/playlist/playlist-meta-field-schema.php';

function leanpl_resolve_playlist_custom_config() {
    $default      = leanpl_get_playlist_defaults();
    $saved_custom = leanpl_get_option( 'playlist', [] );
    if ( ! is_array( $saved_custom ) ) {
        $saved_custom = [];
    }
    return array_merge( $default, $saved_custom );
}

/**
 * Parse video URL to extract type, ID, and create HTML5 sources if needed
 * 
 * Shared utility function for video URL parsing
 * Used by both Video_Shortcode and Player_Shortcode classes
 * 
 * @param string $url Video URL
 * @return array Video info with type, id, and sources
 */
function leanpl_parse_video_url($url) {
    $url = sanitize_text_field(wp_unslash($url));

    // YouTube patterns
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        return [
            'type' => 'youtube',
            'id' => $matches[1],
            'sources' => []
        ];
    }

    // YouTube plain 11-char ID
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url)) {
        return [
            'type' => 'youtube',
            'id' => $url,
            'sources' => []
        ];
    }

    // Vimeo patterns
    if (preg_match('/(?:vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/(?:[^\/]*)\/videos\/|)(\d+)(?:|\/\?))/', $url, $matches)) {
        return [
            'type' => 'vimeo',
            'id' => $matches[1],
            'sources' => []
        ];
    }

    // Vimeo plain numeric ID
    if (preg_match('/^\d+$/', $url)) {
        return [
            'type' => 'vimeo',
            'id' => $url,
            'sources' => []
        ];
    }

    // HTML5 - direct video file
    if (preg_match('/\.(mp4|webm|ogg)$/i', $url)) {
        return [
            'type' => 'html5',
            'id' => '',
            'sources' => [
                [
                    'url' => esc_url($url),
                    'size' => '' // No quality specified for single file
                ]
            ]
        ];
    }

    // Default to HTML5 if no pattern matches
    return [
        'type' => 'html5',
        'id' => '',
        'sources' => [
            [
                'url' => esc_url($url),
                'size' => ''
            ]
        ]
    ];
}

/**
 * Get media filename from attachment ID
 * 
 * @param int $attachment_id Media attachment ID
 * @return string Filename or empty string
 */
function leanpl_get_media_filename($attachment_id) {
    if (!$attachment_id || !is_numeric($attachment_id)) {
        return '';
    }
    
    $file_path = get_attached_file($attachment_id);
    if (!$file_path) {
        return '';
    }
    
    return basename($file_path);
}

/**
 * Get human-readable player type label
 * 
 * @param string $player_type 'video' or 'audio'
 * @return string 'Video' or 'Audio'
 */
function leanpl_get_player_type_label($player_type) {
    if ($player_type === 'audio') {
        return __('Audio', 'vapfem');
    }
    return __('Video', 'vapfem');
}

/**
 * Get source type label for a player
 * 
 * @param int $post_id Post ID
 * @return string Source type label (YouTube, Vimeo, HTML5 Video, Audio File)
 */
function leanpl_get_source_type_label($post_id) {
    $player_type = \LeanPL\Metaboxes::get_field_value($post_id, '_player_type');
    
    if ($player_type === 'audio') {
        // Check if uploaded or external
        $audio_source_type = \LeanPL\Metaboxes::get_field_value($post_id, '_audio_source_type');
        
        if ($audio_source_type === 'upload') {
            return __('Uploaded File', 'vapfem');
        } elseif ($audio_source_type === 'link') {
            return __('External File', 'vapfem');
        }
        
        // Fallback for old data that might not have source type
        return __('Audio File', 'vapfem');
    }
    
    // For video, check video type
    $video_type = \LeanPL\Metaboxes::get_field_value($post_id, '_video_type');
    
    switch ($video_type) {
        case 'youtube':
            return __('YouTube', 'vapfem');
        case 'vimeo':
            return __('Vimeo', 'vapfem');
        case 'html5':
            // Check if uploaded or external
            $html5_source_type = \LeanPL\Metaboxes::get_field_value($post_id, '_html5_source_type');
            
            if ($html5_source_type === 'upload') {
                return __('Uploaded File', 'vapfem');
            } elseif ($html5_source_type === 'link') {
                return __('External File', 'vapfem');
            }
            
            // Fallback for old data
            return __('HTML5 Video', 'vapfem');
        default:
            return __('YouTube', 'vapfem'); // Default fallback
    }
}

/** 
 * Get youtube video thumbnail URL
 * @param string $video_id YouTube video ID
 * @return string YouTube video thumbnail URL
 */
function leanpl_get_youtube_video_thumbnail_url($video_id) {
    $thumbnail_url = '';
    $quality = '0';

    if(empty($video_id)) {
        return $thumbnail_url;
    }

    $thumbnail_url = 'https://img.youtube.com/vi/' . $video_id . '/' . $quality . '.jpg';

    return $thumbnail_url;
}

/** 
 * Get vimeo video thumbnail URL
 * @param string $video_id Vimeo video ID
 * @return string Vimeo video thumbnail URL
 */
function leanpl_get_vimeo_video_thumbnail_url($video_id) {
    $thumbnail_url = '';

    if(empty($video_id)) {
        return $thumbnail_url;
    }

    $thumbnail_url = 'https://i.vimeocdn.com/video/' . $video_id . '_640.jpg';

    return $thumbnail_url;
}

/**
 * Derive thumbnail URL from a YouTube or Vimeo video URL.
 *
 * YouTube: direct URL, zero HTTP cost.
 * Vimeo: oEmbed fetch cached by video ID for 7 days.
 *
 * @param string $video_url Full video URL.
 * @return string Thumbnail URL or empty string.
 */
function leanpl_derive_thumbnail_from_url( string $video_url ): string {
    if ( empty( $video_url ) ) {
        return '';
    }

    // Bare Vimeo numeric ID (no domain) — normalise to full URL so regex matches.
    if ( ctype_digit( $video_url ) ) {
        $video_url = 'https://vimeo.com/' . $video_url;
    }

    $info = leanpl_parse_video_url( $video_url );

    if ( empty( $info['type'] ) || empty( $info['id'] ) ) {
        return '';
    }

    if ( $info['type'] === 'youtube' ) {
        return leanpl_get_youtube_video_thumbnail_url( $info['id'] );
    }

    if ( $info['type'] === 'vimeo' ) {
        $test_mode = function_exists( 'leanpl_is_test_mode' ) && leanpl_is_test_mode();
        $cache_key = 'leanpl_vimeo_thumb_' . $info['id'];

        if ( ! $test_mode ) {
            $cached = get_transient( $cache_key );
            if ( false !== $cached ) {
                return (string) $cached;
            }
        }

        // Always use canonical URL for oEmbed — raw input may be bare ID or variant URL.
        $canonical = 'https://vimeo.com/' . $info['id'];
        $response  = wp_remote_get(
            'https://vimeo.com/api/oembed.json?url=' . rawurlencode( $canonical ),
            [ 'timeout' => 5 ]
        );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            if ( ! $test_mode ) {
                set_transient( $cache_key, '', DAY_IN_SECONDS );
            }
            return '';
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        $url  = $body['thumbnail_url'] ?? '';

        if ( ! $test_mode ) {
            set_transient( $cache_key, $url, 7 * DAY_IN_SECONDS );
        }

        return $url;
    }

    return '';
}

/**
 * Get video thumbnail URL
 * @param int $player_id Player ID
 * @return string Video thumbnail URL
 */
function leanpl_get_video_thumbnail_url($player_id) {
    $thumbnail_url = '';

    if(empty($player_id)) {
        return $thumbnail_url;
    }

    // Generate native thumbnail for youtube and vimeo first
    $video_type = get_post_meta($player_id, '_video_type', true);
    if($video_type === 'youtube') {
        $video_id = get_post_meta($player_id, '_youtube_url', true);

        // Parse only if it's a valid URL
        if(filter_var($video_id, FILTER_VALIDATE_URL)) {
            $video_info = leanpl_parse_video_url($video_id);
            $video_id = $video_info['id'];
        }

        if(!empty($video_id)) {
            $thumbnail_url = leanpl_get_youtube_video_thumbnail_url($video_id);
        }
    } elseif($video_type === 'vimeo') {
        $video_id = get_post_meta($player_id, '_vimeo_url', true);

        // Parse only if it's a valid URL
        if(filter_var($video_id, FILTER_VALIDATE_URL)) {
            $video_info = leanpl_parse_video_url($video_id);
            $video_id = $video_info['id'];
        }

        if(!empty($video_id)) {
            $thumbnail_url = leanpl_get_vimeo_video_thumbnail_url($video_id);
        }
    }

    // No matter what video type is if poster image is set, use it
    $poster_image = get_post_meta($player_id, '_poster', true);
    if( !empty($poster_image) ) {
        $thumbnail_url = wp_get_attachment_image_url($poster_image, 'thumbnail');
    }
    
    return $thumbnail_url;
}

/**
 * Get MIME type for video file based on extension
 *
 * @param string $file_extension File extension (e.g., 'mp4', 'webm', 'ogg')
 * @return string MIME type (e.g., 'video/mp4', 'video/webm', 'video/ogg')
 */
function leanpl_get_video_mime_type($file_extension) {
    $ext = strtolower($file_extension);
    $mime_map = [
        'mp4'  => 'video/mp4',
        'webm' => 'video/webm',
        'ogg'  => 'video/ogg',
        'ogv'  => 'video/ogg',
    ];
    return isset($mime_map[$ext]) ? $mime_map[$ext] : 'video/' . $ext;
}

/**
 * Get MIME type for audio file based on extension
 *
 * @param string $file_extension File extension (e.g., 'mp3', 'm4a', 'ogg')
 * @return string MIME type (e.g., 'audio/mpeg', 'audio/mp4', 'audio/ogg')
 */
function leanpl_get_audio_mime_type($file_extension) {
    // Normalize extension to lowercase
    $ext = strtolower($file_extension);
    
    // Map file extensions to correct MIME types
    $mime_map = [
        'mp3' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'wav' => 'audio/wav',
        'm4a' => 'audio/mp4',
        'aac' => 'audio/aac',
        'aacp' => 'audio/aac',
    ];
    
    // Return mapped MIME type or default to audio/{extension}
    return isset($mime_map[$ext]) ? $mime_map[$ext] : 'audio/' . $ext;
}

/**
 * Get playlist preset
 * @param string $preset_id Preset ID
 * @return array Preset array
 */
function leanpl_get_playlist_preset($preset_id) {
    if(empty($preset_id)) {
        return [];
    }

    $preset_path = LEANPL_DIR . '/includes/playlist/pressets/' . $preset_id . '.php';
    if(file_exists($preset_path)) {
        return include $preset_path;
    }

    return [];
}

