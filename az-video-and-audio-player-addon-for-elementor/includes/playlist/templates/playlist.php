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
    <div class="lpl-playlist__player-wrap">
        <?php include __DIR__ . '/player.php'; ?>
    </div>
    <?php include __DIR__ . '/panel.php'; ?>
</div>
