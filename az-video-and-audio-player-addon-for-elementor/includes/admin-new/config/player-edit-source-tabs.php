<?php
if (!defined('ABSPATH')) {
  exit;
}
/**
 * Source-picker tabs: label + per-tab URL-field copy for edit-source-picker.php.
 * Visual phase - static display values, not read from a real post. Media
 * Library has no URL field (Add Media is the only action); the other three
 * each get their own label/placeholder/help text, not one shared copy block.
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
