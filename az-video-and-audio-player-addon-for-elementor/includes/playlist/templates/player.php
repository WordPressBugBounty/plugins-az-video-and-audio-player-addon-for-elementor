<?php
/**
 * Playlist – Player markup.
 *
 * Expects: $config, $player_markup_data, $item_views, $start_index.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$is_audio_playlist = $config->get_playlist_type() === 'audio';

// Now-playing header — audio playlists only.
if ( $is_audio_playlist ) :
    $first_item_view = $item_views[ $start_index ] ?? ( $item_views[0] ?? null );
    $first_poster    = $first_item_view ? $first_item_view->get_thumb_url() : '';
    $first_title     = $first_item_view ? $first_item_view->get_title() : '';
    $first_meta      = $first_item_view ? $first_item_view->get_meta() : '';
?>
<div class="lpl-playlist__now-playing">
    <?php if ( ( $now_playing_style ?? 'compact' ) === 'large' ) : ?>
    <span class="lpl-playlist__now-playing-label">Now Playing</span>
    <?php endif; ?>
    <?php if ( $first_poster ) : ?>
    <div class="lpl-playlist__now-playing-thumb">
        <img src="<?php echo esc_url( $first_poster ); ?>" alt="" />
    </div>
    <?php endif; ?>
    <div class="lpl-playlist__now-playing-info">
        <span class="lpl-playlist__now-playing-title"><?php echo esc_html( $first_title ); ?></span>
        <?php if ( $first_meta ) : ?>
        <span class="lpl-playlist__now-playing-meta"><?php echo esc_html( $first_meta ); ?></span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php if ( ! $player_markup_data->has_valid_source() ) : ?>
    <div class="lpl-playlist__player" id="lpl-player"></div>
<?php elseif ( $player_markup_data->is_embed() && $player_markup_data->get_provider() === 'youtube' ) : ?>
    <div
        class="lpl-playlist__player"
        id="lpl-player"
        data-plyr-provider="youtube"
        data-plyr-embed-id="<?php echo esc_attr( $player_markup_data->get_embed_id() ); ?>"
    ></div>
<?php elseif ( $player_markup_data->is_embed() && $player_markup_data->get_provider() === 'vimeo' ) : ?>
    <div
        class="lpl-playlist__player"
        id="lpl-player"
        data-plyr-provider="vimeo"
        data-plyr-embed-id="<?php echo esc_attr( $player_markup_data->get_embed_id() ); ?>"
    ></div>
<?php elseif ( $is_audio_playlist ) : ?>
    <audio class="lpl-playlist__player" id="lpl-player" preload="<?php echo esc_attr( $config->get_preload() ); ?>">
        <source src="<?php echo esc_url( $player_markup_data->get_source_url() ); ?>">
    </audio>
<?php else : ?>
    <video class="lpl-playlist__player" id="lpl-player" preload="<?php echo esc_attr( $config->get_preload() ); ?>"<?php echo $player_markup_data->get_poster() ? ' poster="' . esc_url( $player_markup_data->get_poster() ) . '"' : ''; ?>>
        <source src="<?php echo esc_url( $player_markup_data->get_source_url() ); ?>" type="<?php echo esc_attr( leanpl_get_video_mime_type( $player_markup_data->get_source_extension() ) ); ?>">
    </video>
<?php endif; ?>
