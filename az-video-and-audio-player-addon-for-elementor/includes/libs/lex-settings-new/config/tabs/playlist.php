<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$playlist_defaults = leanpl_get_playlist_defaults();
$settings          = \Lex\Settings\V2\Settings::getInstance( 'leanpl' );

$settings->sectionRenderer->startSection( 'playlist_general', esc_html__( 'General', 'vapfem' ) );

$settings->fieldRenderer->render( 'checkbox', 'playlist.enabled', [
    'label'          => esc_html__( 'Enable Playlist Feature', 'vapfem' ),
    'desc'           => esc_html__( 'Disable if you don\'t need the playlist feature. When disabled, the Playlists menu, playlist shortcode, and playlist-related fields will be hidden.', 'vapfem' ),
    'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
    'default'        => $playlist_defaults['enabled'] ?? null,
] );

$settings->fieldRenderer->render( 'checkbox', 'playlist.auto_thumbnail', [
    'label'          => esc_html__( 'Automatic Thumbnail', 'vapfem' ),
    'desc'           => esc_html__( 'Use the video provider\'s thumbnail when no custom image is uploaded. Supports YouTube and Vimeo. Private or deleted videos will show no thumbnail.', 'vapfem' ),
    'checkbox_label' => esc_html__( 'Yes', 'vapfem' ),
    'default'        => $playlist_defaults['auto_thumbnail'] ?? false,
    'disabled'       => true,
    'pro'            => [ 'onclick' => 'openUpgradeModal', 'badge_position' => false ],
] );

$settings->sectionRenderer->endSection();

$settings->sectionRenderer->renderSubmitButtons();
