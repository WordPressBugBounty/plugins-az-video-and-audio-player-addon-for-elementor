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

/**
 * Speed Registry — SSOT for the playback speeds the plugin offers.
 *
 * Keys   : speed value as a string, matching the stored `speed_selected` /
 *          `speed_options` format. Note PHP casts the integer-like keys
 *          ('1', '2', '4') to int — compare loosely or cast, never assume string.
 * Values : Human-readable label for admin UI.
 *
 * Order here = ascending, which is also the order Plyr renders the speed menu in.
 *
 * YouTube and Vimeo cap playback speed at 0.5x-2x and silently drop anything
 * outside that range, so 4x only ever appears on HTML5 media.
 */
function leanpl_get_speed_registry() {
    return [
        '0.5'  => __( '0.5x (Slow)',      'vapfem' ),
        '0.75' => __( '0.75x',            'vapfem' ),
        '1'    => __( '1x (Normal)',      'vapfem' ),
        '1.25' => __( '1.25x',            'vapfem' ),
        '1.5'  => __( '1.5x',             'vapfem' ),
        '1.75' => __( '1.75x',            'vapfem' ),
        '2'    => __( '2x (Fast)',        'vapfem' ),
        '4'    => __( '4x (Very Fast)',   'vapfem' ),
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
    // Do not use sanitize_text_field() here: it strips %XX sequences (e.g. %20),
    // corrupting file URLs that contain spaces/special chars. YouTube/Vimeo IDs are
    // extracted via strict regex below, and html5 URLs are esc_url()'d on output,
    // so tag-stripping + trim is sufficient and safe while preserving encoding.
    $url = trim(wp_strip_all_tags(wp_unslash($url)));

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
 * Resolve a display title for a YouTube or Vimeo URL via oEmbed.
 *
 * Batch Add creates players from bare URLs with no user-entered title, so a
 * fetch failure must never block creation: every failure path returns the
 * "YouTube Video (ID)" / "Vimeo Video (ID)" fallback instead of an error.
 *
 * Titles cache by video ID for 7 days, mirroring
 * leanpl_derive_thumbnail_from_url(). Failures cache for a day so a dead link
 * in a re-pasted batch does not pay the timeout on every retry.
 *
 * @param string $video_url Full video URL.
 * @return string Title, or empty string if the URL is not YouTube/Vimeo.
 */
function leanpl_fetch_oembed_title( string $video_url ): string {
    if ( empty( $video_url ) ) {
        return '';
    }

    $info = leanpl_parse_video_url( $video_url );

    if ( empty( $info['id'] ) || ! in_array( $info['type'], [ 'youtube', 'vimeo' ], true ) ) {
        return '';
    }

    $is_youtube = $info['type'] === 'youtube';
    $fallback   = $is_youtube
        /* translators: %s: YouTube video ID. */
        ? sprintf( __( 'YouTube Video (%s)', 'vapfem' ), $info['id'] )
        /* translators: %s: Vimeo video ID. */
        : sprintf( __( 'Vimeo Video (%s)', 'vapfem' ), $info['id'] );

    $test_mode = function_exists( 'leanpl_is_test_mode' ) && leanpl_is_test_mode();
    $cache_key = 'leanpl_oembed_title_' . $info['type'] . '_' . $info['id'];

    if ( ! $test_mode ) {
        $cached = get_transient( $cache_key );
        if ( false !== $cached ) {
            return '' !== $cached ? (string) $cached : $fallback;
        }
    }

    // Canonical URL for oEmbed — raw input may be a variant (youtu.be, /embed/, ?t=).
    if ( $is_youtube ) {
        $canonical = 'https://www.youtube.com/watch?v=' . $info['id'];
        $endpoint  = 'https://www.youtube.com/oembed?format=json&url=' . rawurlencode( $canonical );
    } else {
        $canonical = 'https://vimeo.com/' . $info['id'];
        $endpoint  = 'https://vimeo.com/api/oembed.json?url=' . rawurlencode( $canonical );
    }

    $response = wp_remote_get( $endpoint, [ 'timeout' => 5 ] );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        if ( ! $test_mode ) {
            set_transient( $cache_key, '', DAY_IN_SECONDS );
        }
        return $fallback;
    }

    $body  = json_decode( wp_remote_retrieve_body( $response ), true );
    $title = isset( $body['title'] ) ? sanitize_text_field( $body['title'] ) : '';

    if ( ! $test_mode ) {
        set_transient( $cache_key, $title, 7 * DAY_IN_SECONDS );
    }

    return '' !== $title ? $title : $fallback;
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

/**
 * Get source badge data for a player — used for playlist builder badges.
 *
 * Returns the specific source type (youtube, vimeo, self-hosted, external)
 * rather than the generic player type (video/audio).
 *
 * @param int $player_id Player post ID.
 * @return array{type: string, label: string}
 */
function leanpl_get_player_source_badge( $player_id ) {
    $player_type = get_post_meta( $player_id, '_player_type', true ) ?: 'video';

    if ( $player_type === 'audio' ) {
        $audio_source_type = get_post_meta( $player_id, '_audio_source_type', true ) ?: 'upload';
        if ( $audio_source_type === 'link' ) {
            return [
                'type'  => 'external',
                'label' => __( 'External Link', 'vapfem' ),
            ];
        }
        return [
            'type'  => 'self-hosted',
            'label' => __( 'Self Hosted', 'vapfem' ),
        ];
    }

    // Video branch
    $video_type = get_post_meta( $player_id, '_video_type', true ) ?: 'html5';

    if ( $video_type === 'youtube' ) {
        return [
            'type'  => 'youtube',
            'label' => __( 'YouTube', 'vapfem' ),
        ];
    }

    if ( $video_type === 'vimeo' ) {
        return [
            'type'  => 'vimeo',
            'label' => __( 'Vimeo', 'vapfem' ),
        ];
    }

    $html5_source_type = get_post_meta( $player_id, '_html5_source_type', true ) ?: 'upload';
    if ( $html5_source_type === 'link' ) {
        return [
            'type'  => 'external',
            'label' => __( 'External Link', 'vapfem' ),
        ];
    }

    return [
        'type'  => 'self-hosted',
        'label' => __( 'Self Hosted', 'vapfem' ),
    ];
}

/**
 * Input hints for the playlist builder's add surfaces, per playlist type.
 *
 * Kept beside leanpl_create_player_from_url() on purpose: the copy describes
 * exactly what that function accepts, so the accepted-source lists ($audio_exts
 * / $video_exts) and the wording the user reads stay in one place. Audio has no
 * streaming provider — YouTube/Vimeo are rejected with type_mismatch — so the
 * audio strings promise only direct files, and titles from the file name (there
 * is no oEmbed source to fetch from).
 *
 * @param string $playlist_type 'video' or 'audio'.
 * @return array{omnibox: string, qa_url: string, ba_placeholder: string, ba_desc: string}
 */
function leanpl_get_playlist_source_hints( $playlist_type ) {
    if ( $playlist_type === 'audio' ) {
        return [
            'omnibox'        => __( 'Paste a direct audio file link (MP3, WAV, M4A…)', 'vapfem' ),
            'qa_url'         => __( 'Direct audio file URL (MP3, WAV, M4A…)', 'vapfem' ),
            'ba_placeholder' => __( "One URL per line, e.g:\nhttps://example.com/song.mp3\nhttps://example.com/episode.m4a\nhttps://example.com/track.wav", 'vapfem' ),
            'ba_desc'        => __( 'Direct audio file links, one per line. Titles are taken from the file name. Up to 50 per batch.', 'vapfem' ),
        ];
    }

    return [
        'omnibox'        => __( 'Paste a YouTube, Vimeo, or direct media link…', 'vapfem' ),
        'qa_url'         => __( 'YouTube, Vimeo, or direct media file URL', 'vapfem' ),
        'ba_placeholder' => __( "One URL per line, e.g:\nhttps://www.youtube.com/watch?v=bTqVqk7FSmY\nhttps://vimeo.com/22439234\nhttps://example.com/video.mp4", 'vapfem' ),
        'ba_desc'        => __( 'YouTube, Vimeo, or direct media file links. Titles are fetched automatically. Up to 50 per batch.', 'vapfem' ),
    ];
}

/**
 * List of every post meta key that can hold a player's source.
 *
 * Used when swapping a track's source (e.g. YouTube → uploaded MP4): every
 * key here gets deleted first so no stale value from the old source type
 * lingers, then only the freshly detected keys are written back.
 *
 * @return string[] Meta keys.
 */
function leanpl_get_player_source_meta_keys() {
    return [
        '_video_type',
        '_youtube_url',
        '_vimeo_url',
        '_html5_source_type',
        '_html5_video_url',
        '_video_source',
        '_audio_source_type',
        '_html5_audio_url',
        '_audio_source',
    ];
}

/**
 * Detect what kind of media source a URL or attachment is, for a given
 * playlist type.
 *
 * Detection rules:
 *   - YouTube / Vimeo URL (via leanpl_parse_video_url) → video player
 *   - .mp4/.webm/.ogv URL → html5 video via link
 *   - .mp3/.m4a/.aac/.wav URL → audio via link
 *   - .ogg URL → resolved by playlist type (valid for both media kinds)
 *   - attachment_id → decided by attachment MIME type
 *
 * The detected player type must match $playlist_type, otherwise a
 * type_mismatch error is returned. This is the single shared rule set used
 * by both creating a new track and editing an existing one, so the two
 * flows can never drift apart.
 *
 * @param string $url           Media URL. Mutually exclusive with $attachment_id.
 * @param int    $attachment_id Media library attachment ID.
 * @param string $playlist_type 'video' or 'audio'.
 * @return array|WP_Error {
 *     @type string $type Detected player type, 'video' or 'audio'.
 *     @type array  $meta Post meta keys/values to write for this source.
 * }
 */
function leanpl_detect_player_source( $url, $attachment_id, $playlist_type ) {
    $url           = trim( (string) $url );
    $attachment_id = absint( $attachment_id );

    if ( $url === '' && ! $attachment_id ) {
        return new WP_Error( 'missing_source', __( 'Please enter a media URL or choose an upload.', 'vapfem' ) );
    }

    $detected_type = '';
    $source_meta   = [];

    if ( $attachment_id ) {
        $mime = get_post_mime_type( $attachment_id );
        if ( ! $mime ) {
            return new WP_Error( 'invalid_attachment', __( 'That media file could not be found.', 'vapfem' ) );
        }
        if ( strpos( $mime, 'video/' ) === 0 ) {
            $detected_type = 'video';
            $source_meta   = [
                '_video_type'        => 'html5',
                '_html5_source_type' => 'upload',
                '_video_source'      => $attachment_id,
            ];
        } elseif ( strpos( $mime, 'audio/' ) === 0 ) {
            $detected_type = 'audio';
            $source_meta   = [
                '_audio_source_type' => 'upload',
                '_audio_source'      => $attachment_id,
            ];
        } else {
            return new WP_Error( 'unsupported_attachment', __( 'That file is not a video or audio file.', 'vapfem' ) );
        }
    } else {
        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return new WP_Error( 'invalid_url', __( 'That does not look like a valid URL.', 'vapfem' ) );
        }

        $path = (string) wp_parse_url( $url, PHP_URL_PATH );
        $ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

        $audio_exts = [ 'mp3', 'm4a', 'aac', 'wav' ];
        $video_exts = [ 'mp4', 'webm', 'ogv' ];

        if ( in_array( $ext, $audio_exts, true ) ) {
            $detected_type = 'audio';
            $source_meta   = [
                '_audio_source_type' => 'link',
                '_html5_audio_url'   => esc_url_raw( $url ),
            ];
        } elseif ( $ext === 'ogg' ) {
            // Valid for both media kinds — the playlist type decides.
            if ( $playlist_type === 'audio' ) {
                $detected_type = 'audio';
                $source_meta   = [
                    '_audio_source_type' => 'link',
                    '_html5_audio_url'   => esc_url_raw( $url ),
                ];
            } else {
                $detected_type = 'video';
                $source_meta   = [
                    '_video_type'        => 'html5',
                    '_html5_source_type' => 'link',
                    '_html5_video_url'   => esc_url_raw( $url ),
                ];
            }
        } else {
            $info = leanpl_parse_video_url( $url );

            if ( $info['type'] === 'youtube' ) {
                $detected_type = 'video';
                $source_meta   = [
                    '_video_type'  => 'youtube',
                    '_youtube_url' => esc_url_raw( $url ),
                ];
            } elseif ( $info['type'] === 'vimeo' ) {
                $detected_type = 'video';
                $source_meta   = [
                    '_video_type' => 'vimeo',
                    '_vimeo_url'  => esc_url_raw( $url ),
                ];
            } elseif ( in_array( $ext, $video_exts, true ) ) {
                $detected_type = 'video';
                $source_meta   = [
                    '_video_type'        => 'html5',
                    '_html5_source_type' => 'link',
                    '_html5_video_url'   => esc_url_raw( $url ),
                ];
            } else {
                // leanpl_parse_video_url falls back to html5 for anything,
                // so gate on known media extensions to reject junk URLs.
                return new WP_Error( 'unsupported_url', __( 'Unsupported media URL. Use a YouTube, Vimeo, or direct media file link.', 'vapfem' ) );
            }
        }
    }

    if ( $detected_type !== $playlist_type ) {
        $message = $playlist_type === 'audio'
            ? __( 'That URL is a video source, but this is an audio playlist.', 'vapfem' )
            : __( 'That URL is an audio source, but this is a video playlist.', 'vapfem' );
        return new WP_Error( 'type_mismatch', $message );
    }

    return [
        'type' => $detected_type,
        'meta' => $source_meta,
    ];
}

/**
 * Create a published lean_player post from a media URL or attachment.
 *
 * @param array $args {
 *     @type string $url           Media URL. Mutually exclusive with attachment_id.
 *     @type int    $attachment_id Media library attachment ID.
 *     @type string $title         Player title. Optional; falls back to the
 *                                 attachment title or a filename-derived title.
 *     @type int    $poster_id     Optional image attachment ID for _poster.
 *     @type string $duration      Optional manual duration text.
 *     @type string $meta_text     Optional meta text.
 *     @type string $playlist_type 'video' or 'audio'. Default 'video'.
 * }
 * @return array|WP_Error Row data array (id, title, source_type, source_label,
 *                        duration, meta_text) or WP_Error on failure.
 */
function leanpl_create_player_from_url( $args ) {
    $playlist_type = ( isset( $args['playlist_type'] ) && $args['playlist_type'] === 'audio' ) ? 'audio' : 'video';
    $title         = isset( $args['title'] ) ? trim( sanitize_text_field( $args['title'] ) ) : '';
    $url           = isset( $args['url'] ) ? trim( (string) $args['url'] ) : '';
    $attachment_id = isset( $args['attachment_id'] ) ? absint( $args['attachment_id'] ) : 0;

    $detected = leanpl_detect_player_source( $url, $attachment_id, $playlist_type );
    if ( is_wp_error( $detected ) ) {
        return $detected;
    }
    $detected_type = $detected['type'];
    $source_meta   = $detected['meta'];

    // Title is optional: fall back to the attachment title, or a title
    // derived from the URL's filename, or a generic placeholder.
    if ( $title === '' ) {
        if ( $attachment_id ) {
            $title = get_the_title( $attachment_id );
        } else {
            $filename = pathinfo( (string) wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_FILENAME );
            $title    = trim( str_replace( [ '-', '_' ], ' ', $filename ) );
            $title    = $title !== '' ? ucwords( $title ) : '';
        }
        if ( $title === '' ) {
            $title = __( 'Untitled', 'vapfem' );
        }
    }

    $post_id = wp_insert_post( [
        'post_type'   => 'lean_player',
        'post_status' => 'publish',
        'post_title'  => $title,
    ], true );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'insert_failed', __( 'Could not create the player. Please try again.', 'vapfem' ) );
    }

    update_post_meta( $post_id, '_player_type', $detected_type );
    foreach ( $source_meta as $key => $value ) {
        update_post_meta( $post_id, $key, $value );
    }

    $poster_id = isset( $args['poster_id'] ) ? absint( $args['poster_id'] ) : 0;
    if ( $poster_id ) {
        update_post_meta( $post_id, '_poster', $poster_id );
    }

    $duration  = isset( $args['duration'] ) ? sanitize_text_field( $args['duration'] ) : '';
    $meta_text = isset( $args['meta_text'] ) ? sanitize_text_field( $args['meta_text'] ) : '';
    if ( $duration !== '' ) {
        update_post_meta( $post_id, '_duration', $duration );
    }
    if ( $meta_text !== '' ) {
        update_post_meta( $post_id, '_meta_text', $meta_text );
    }

    $badge = leanpl_get_player_source_badge( $post_id );

    return [
        'id'           => $post_id,
        'title'        => $title,
        'source_type'  => $badge['type'],
        'source_label' => $badge['label'],
        'duration'     => $duration,
        'meta_text'    => $meta_text,
    ];
}

/**
 * Update an existing lean_player post from builder edit-form args.
 *
 * Same args shape as leanpl_create_player_from_url(), plus the player ID.
 * Returns the same row-data shape the creator returns, so the JS refresh
 * path is identical for "added" and "edited" rows.
 *
 * Unlike create, an empty poster/duration/meta value here clears the
 * existing value rather than being skipped, because editing must be able
 * to remove a value the track already has.
 *
 * @param int   $player_id Existing lean_player post ID.
 * @param array $args      Same shape as leanpl_create_player_from_url().
 * @return array|WP_Error Row data array or WP_Error on failure.
 */
function leanpl_update_player_from_url( $player_id, $args ) {
    $player_id = absint( $player_id );

    if ( ! $player_id || get_post_type( $player_id ) !== 'lean_player' ) {
        return new WP_Error( 'invalid_player', __( 'That track could not be found.', 'vapfem' ) );
    }

    $playlist_type = ( isset( $args['playlist_type'] ) && $args['playlist_type'] === 'audio' ) ? 'audio' : 'video';
    $title         = isset( $args['title'] ) ? trim( sanitize_text_field( $args['title'] ) ) : '';
    $url           = isset( $args['url'] ) ? trim( (string) $args['url'] ) : '';
    $attachment_id = isset( $args['attachment_id'] ) ? absint( $args['attachment_id'] ) : 0;

    $detected = leanpl_detect_player_source( $url, $attachment_id, $playlist_type );
    if ( is_wp_error( $detected ) ) {
        return $detected;
    }
    $detected_type = $detected['type'];
    $source_meta   = $detected['meta'];

    if ( $title === '' ) {
        $title = get_the_title( $player_id );
    }

    wp_update_post( [
        'ID'         => $player_id,
        'post_title' => $title,
    ] );

    update_post_meta( $player_id, '_player_type', $detected_type );

    // Source swap is clean: delete every known source key, then write
    // only the newly detected ones (prevents stale _youtube_url etc.
    // lingering after switching a track from YouTube to an MP4).
    foreach ( leanpl_get_player_source_meta_keys() as $key ) {
        delete_post_meta( $player_id, $key );
    }
    foreach ( $source_meta as $key => $value ) {
        update_post_meta( $player_id, $key, $value );
    }

    // Poster / duration / meta: empty value clears it (create only ever
    // sets non-empty values, but edit must be able to remove one).
    $poster_id = isset( $args['poster_id'] ) ? absint( $args['poster_id'] ) : 0;
    if ( $poster_id ) {
        update_post_meta( $player_id, '_poster', $poster_id );
    } else {
        delete_post_meta( $player_id, '_poster' );
    }

    $duration  = isset( $args['duration'] ) ? sanitize_text_field( $args['duration'] ) : '';
    $meta_text = isset( $args['meta_text'] ) ? sanitize_text_field( $args['meta_text'] ) : '';

    if ( $duration !== '' ) {
        update_post_meta( $player_id, '_duration', $duration );
    } else {
        delete_post_meta( $player_id, '_duration' );
    }

    if ( $meta_text !== '' ) {
        update_post_meta( $player_id, '_meta_text', $meta_text );
    } else {
        delete_post_meta( $player_id, '_meta_text' );
    }

    $badge = leanpl_get_player_source_badge( $player_id );

    return [
        'id'           => $player_id,
        'title'        => $title,
        'source_type'  => $badge['type'],
        'source_label' => $badge['label'],
        'duration'     => $duration,
        'meta_text'    => $meta_text,
    ];
}

/**
 * Read a lean_player post's current data for the builder's edit panel.
 *
 * The inverse of leanpl_detect_player_source(): reads the meta keys that
 * function writes, and rebuilds the url / attachment_id pair the edit form
 * needs to prefill. Exactly one of url / attachment_id is populated,
 * mirroring how the source was originally stored.
 *
 * @param int $player_id Existing lean_player post ID.
 * @return array|WP_Error {
 *     @type int    $id
 *     @type string $title
 *     @type string $url
 *     @type int    $attachment_id
 *     @type string $attachment_url
 *     @type int    $poster_id
 *     @type string $poster_url
 *     @type string $duration
 *     @type string $meta_text
 * } or WP_Error on failure.
 */
function leanpl_get_player_edit_data( $player_id ) {
    $player_id = absint( $player_id );

    if ( ! $player_id || get_post_type( $player_id ) !== 'lean_player' ) {
        return new WP_Error( 'invalid_player', __( 'That track could not be found.', 'vapfem' ) );
    }

    $player_type = get_post_meta( $player_id, '_player_type', true ) ?: 'video';

    $url           = '';
    $attachment_id = 0;

    if ( $player_type === 'audio' ) {
        $source_type = get_post_meta( $player_id, '_audio_source_type', true ) ?: 'upload';
        if ( $source_type === 'link' ) {
            $url = (string) get_post_meta( $player_id, '_html5_audio_url', true );
        } else {
            $attachment_id = absint( get_post_meta( $player_id, '_audio_source', true ) );
        }
    } else {
        $video_type = get_post_meta( $player_id, '_video_type', true ) ?: 'html5';
        if ( $video_type === 'youtube' ) {
            $url = (string) get_post_meta( $player_id, '_youtube_url', true );
        } elseif ( $video_type === 'vimeo' ) {
            $url = (string) get_post_meta( $player_id, '_vimeo_url', true );
        } else {
            $source_type = get_post_meta( $player_id, '_html5_source_type', true ) ?: 'upload';
            if ( $source_type === 'link' ) {
                $url = (string) get_post_meta( $player_id, '_html5_video_url', true );
            } else {
                $attachment_id = absint( get_post_meta( $player_id, '_video_source', true ) );
            }
        }
    }

    $attachment_url = $attachment_id ? (string) wp_get_attachment_url( $attachment_id ) : '';

    $poster_id  = absint( get_post_meta( $player_id, '_poster', true ) );
    $poster_url = $poster_id ? (string) wp_get_attachment_url( $poster_id ) : '';

    return [
        'id'             => $player_id,
        // Raw title, not get_the_title(): the latter runs the_title display
        // filters (wptexturize turns " - " into an "&#8211;" entity), which
        // would show literally in the edit input and round-trip back into the
        // DB on save. WP's own edit screens read post_title raw for the same
        // reason; the builder row does too (esc_html( $player->post_title )).
        'title'          => (string) get_post_field( 'post_title', $player_id ),
        'url'            => $url,
        'attachment_id'  => $attachment_id,
        'attachment_url' => $attachment_url,
        'poster_id'      => $poster_id,
        'poster_url'     => $poster_url,
        'duration'       => (string) ( get_post_meta( $player_id, '_duration', true ) ?: '' ),
        'meta_text'      => (string) ( get_post_meta( $player_id, '_meta_text', true ) ?: '' ),
    ];
}

