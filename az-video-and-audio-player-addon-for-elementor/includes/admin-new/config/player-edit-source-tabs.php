<?php
if (!defined('ABSPATH')) {
  exit;
}
/**
 * Source-picker tabs: label + per-tab URL-field copy for edit-source-picker.php.
 * Visual phase - static display values, not read from a real post. Media
 * Library has no URL field (Add Media is the only action); the other tabs
 * each get their own label/placeholder/help text, not one shared copy block.
 *
 * 'default_value', when set, is written into the URL field itself (not just
 * the placeholder) when that tab is clicked - see data-lpl-source-default-value
 * in edit-source-picker.php and initSourceTabs() in admin-new.js. Each tab's
 * default_value is its own distinct example (docs/demo-urls.txt) so switching
 * tabs always shows an example that actually matches the source just picked -
 * a leftover Shorts URL sitting under the plain "YouTube" tab reads as a bug,
 * not a demo, even though a Shorts link is technically also valid input there.
 *
 * 'tab_bg' / 'tab_line' / 'tab_text' are the tab pill's resting (unselected)
 * soft background/outline/label colors - see data-lpl-source-tab's inline
 * style in edit-source-picker.php and the [data-lpl-source-tab] rules in
 * tailwind-admin.src.css. One soft color per source family, not per tab:
 * 'youtube' and 'youtube-shorts' share the exact same three values on
 * purpose, since Shorts is still YouTube, not a different source. The
 * selected tab ignores all three and always renders the same dark/white
 * look regardless of source - only the resting color varies.
 */
return [
  [
    'key'            => 'media-library',
    'label'          => __('Media Library', 'vapfem'),
    'show_url_field' => false,
    'tab_bg'         => '#F7F8F9',
    'tab_line'       => '#AEB4BC',
    'tab_text'       => '#4B5563',
  ],
  [
    'key'             => 'youtube',
    'label'           => __('YouTube', 'vapfem'),
    'show_url_field'  => true,
    'url_label'       => __('YouTube URL or ID', 'vapfem'),
    'url_placeholder' => 'https://www.youtube.com/watch?v=bTqVqk7FSmY',
    'help_text'       => __('Paste a YouTube link or 11-character video ID.', 'vapfem'),
    'default_value'   => 'https://www.youtube.com/watch?v=bTqVqk7FSmY',
    'tab_bg'          => '#FFF4F4',
    'tab_line'        => '#E8A3A3',
    'tab_text'        => '#D93025',
  ],
  [
    'key'             => 'youtube-shorts',
    'label'           => __('YouTube Shorts', 'vapfem'),
    'show_url_field'  => true,
    'url_label'       => __('YouTube Shorts URL', 'vapfem'),
    'url_placeholder' => 'https://www.youtube.com/shorts/egK37yP_bAk',
    'help_text'       => __('Paste a youtube.com/shorts/... link.', 'vapfem'),
    'default_value'   => 'https://www.youtube.com/shorts/egK37yP_bAk',
    // Same three values as 'youtube' above - Shorts is the same source family.
    'tab_bg'          => '#FFF4F4',
    'tab_line'        => '#E8A3A3',
    'tab_text'        => '#D93025',
  ],
  [
    'key'             => 'vimeo',
    'label'           => __('Vimeo', 'vapfem'),
    'show_url_field'  => true,
    'url_label'       => __('Vimeo URL or ID', 'vapfem'),
    'url_placeholder' => 'https://vimeo.com/22439234',
    'help_text'       => __('Paste a Vimeo link or numeric video ID.', 'vapfem'),
    'default_value'   => 'https://vimeo.com/22439234',
    'tab_bg'          => '#F0FAFD',
    'tab_line'        => '#7FC7DE',
    'tab_text'        => '#0F7EA6',
  ],
  [
    'key'             => 'audio',
    'label'           => __('Audio', 'vapfem'),
    'show_url_field'  => true,
    'url_label'       => __('Audio URL', 'vapfem'),
    'url_placeholder' => 'https://example.com/audio.mp3',
    'help_text'       => __('Paste a direct audio file URL (MP3, WAV, or OGG).', 'vapfem'),
    'default_value'   => 'https://archive.org/download/monkeys_paw_librivox/monkeyspaw_jacobs.mp3',
    'tab_bg'          => '#FAF9FF',
    'tab_line'        => '#B7A7E8',
    'tab_text'        => '#6D28D9',
  ],
  [
    'key'             => 'audio-live-stream',
    'label'           => __('Audio Stream', 'vapfem'),
    'show_url_field'  => true,
    'url_label'       => __('Live Stream URL', 'vapfem'),
    'url_placeholder' => 'https://ice1.somafm.com/groovesalad-128-mp3',
    'help_text'       => __('Paste a radio stream URL. Icecast, Shoutcast and any other server with a direct stream URL all work. On an HTTPS site the stream URL must be HTTPS too, or the browser will block it.', 'vapfem'),
    'default_value'   => 'https://ice1.somafm.com/groovesalad-128-mp3',
    // Same three values as 'audio' above - a live stream is still the
    // audio source family, just with no fixed duration.
    'tab_bg'          => '#FAF9FF',
    'tab_line'        => '#B7A7E8',
    'tab_text'        => '#6D28D9',
  ],
];
