<?php
/**
 * Playlist – Panel markup.
 *
 * Expects: $config, $item_views, $start_index, $item_template, $is_card.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div class="lpl-playlist__panel">
    <div class="lpl-playlist__panel-header">
        <span class="lpl-playlist__panel-header-title">
            <?php echo esc_html( $config->get_header_text() ); ?>
        </span>
        <span class="lpl-playlist__panel-header-count">
            <?php echo esc_html( $config->get_total_items() . ' ' . $config->get_count_label() ); ?>
        </span>
    </div>
    <div class="lpl-playlist__panel-scroll">
        <div
            class="lpl-playlist__items-wrap lpl-playlist__items-wrap--<?php echo esc_attr( $item_template ); ?>"
            role="list"
            aria-label="<?php echo esc_attr( $config->get_header_text() ); ?>"
        >
            <?php
            foreach ( $item_views as $index => $item_view ) :
                $is_active = ( (int) $index === $start_index );
                include __DIR__ . '/item-' . $item_template . '.php';
            endforeach;
            ?>
        </div>
    </div>
</div>
