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
 */
return [
  [
    'key'            => 'media-library',
    'label'          => __('Media Library', 'vapfem'),
    'show_url_field' => false,
  ],
  [
    'key'             => 'youtube',
    'label'           => __('YouTube', 'vapfem'),
    'show_url_field'  => true,
    'url_label'       => __('YouTube URL or ID', 'vapfem'),
    'url_placeholder' => 'https://www.youtube.com/watch?v=bTqVqk7FSmY',
    'help_text'       => __('Paste a YouTube link or 11-character video ID.', 'vapfem'),
    'default_value'   => 'https://www.youtube.com/watch?v=bTqVqk7FSmY',
  ],
  [
    'key'             => 'youtube-shorts',
    'label'           => __('YouTube Shorts', 'vapfem'),
    'show_url_field'  => true,
    'url_label'       => __('YouTube Shorts URL', 'vapfem'),
    'url_placeholder' => 'https://www.youtube.com/shorts/egK37yP_bAk',
    'help_text'       => __('Paste a youtube.com/shorts/... link.', 'vapfem'),
    'default_value'   => 'https://www.youtube.com/shorts/egK37yP_bAk',
  ],
  [
    'key'             => 'vimeo',
    'label'           => __('Vimeo', 'vapfem'),
    'show_url_field'  => true,
    'url_label'       => __('Vimeo URL or ID', 'vapfem'),
    'url_placeholder' => 'https://vimeo.com/22439234',
    'help_text'       => __('Paste a Vimeo link or numeric video ID.', 'vapfem'),
  ],
  [
    'key'             => 'audio',
    'label'           => __('Audio', 'vapfem'),
    'show_url_field'  => true,
    'url_label'       => __('Audio URL', 'vapfem'),
    'url_placeholder' => 'https://example.com/audio.mp3',
    'help_text'       => __('Paste a direct audio file URL (MP3, WAV, or OGG).', 'vapfem'),
  ],
];
