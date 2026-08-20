<?php
/**
 * Playlist – Root wrapper.
 *
 * Expects: $config, $css_helper, $player_markup_data, $item_views, $start_index, $item_template, $is_card.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div
    class="<?php echo esc_attr( $css_helper->get_root_classes() ); ?>"
    style="<?php echo esc_attr( $css_helper->get_inline_style() ); ?>"
    data-skin="<?php echo esc_attr( $css_helper->get_data_skin() ); ?>"
    data-lpl-playlist-config="<?php echo esc_attr( wp_json_encode( $config->get_js_config() ) ); ?>"
    data-lpl-player-config="<?php echo esc_attr( wp_json_encode( $config->get_plyr_config() ) ); ?>"
>
    <?php
    // lpl-player-wrap + lpl-video/lpl-audio + data-lpl-player-layout mirror
    // class-player-renderer.php's single-player wrap exactly - that trio is
    // what main.css's [data-lpl-player-layout="..."] rules key off, on or
    // off .lpl-player-wrap. Without it, the picker in the playlist
    // inspector saves a value but no layout CSS ever applies.
    $player_layout      = $config->get_player_layout();
    $is_audio_playlist  = $config->get_playlist_type() === 'audio';
    ?>
    <?php // .lpl-playlist__player-area is the grid item (grid-area: player,
          // see playlist.css) - it exists only to hold that grid slot.
          // .lpl-playlist__player-wrap is nested inside it and keeps every
          // other rule (sizing, skin, position padding/radius) unchanged. ?>
    <div class="lpl-playlist__player-area">
        <div
            class="lpl-playlist__player-wrap lpl-player-wrap <?php echo $is_audio_playlist ? 'lpl-audio' : 'lpl-video'; ?>"
            data-lpl-player-layout="<?php echo esc_attr( $player_layout ); ?>"
        >
            <?php include __DIR__ . '/player.php'; ?>
        </div>
    </div>
    <?php include __DIR__ . '/panel.php'; ?>
</div>
