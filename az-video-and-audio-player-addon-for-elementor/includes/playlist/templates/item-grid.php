<?php
/**
 * Playlist – Grid layout.
 *
 * Expects: $item_view, $is_active, $is_card.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>
<div
    class="<?php echo esc_attr( $item_view->get_classes( $is_active, $is_card ) ); ?>"
    role="button"
    tabindex="0"
    itemscope
    itemtype="https://schema.org/VideoObject"
    data-lpl-source="<?php echo esc_attr( $item_view->get_source_json() ); ?>"
    aria-label="<?php echo esc_attr( $item_view->get_title() ); ?>"
    aria-pressed="<?php echo esc_attr( $is_active ? 'true' : 'false' ); ?>"
>
    <span class="lpl-playlist__item-thumb-wrap" itemprop="thumbnail" aria-hidden="true">
        <?php if ( $item_view->get_thumb_url() ) : ?>
        <img
            class="lpl-playlist__item-thumb"
            src="<?php echo esc_url( $item_view->get_thumb_url() ); ?>"
            alt=""
            loading="lazy"
            width="100%"
            decoding="async"
        >
        <?php endif; ?>
        <span class="lpl-playlist__item-thumb-overlay" aria-hidden="true">
            <svg class="lpl-playlist__item-thumb-overlay-icon lpl-playlist__item-thumb-overlay-icon--pause" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
            </svg>
            <svg class="lpl-playlist__item-thumb-overlay-icon lpl-playlist__item-thumb-overlay-icon--play" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8 5v14l11-7z"/>
            </svg>
        </span>
        <?php if ( $item_view->get_duration() ) : ?>
        <span class="lpl-playlist__item-duration-badge" aria-label="<?php echo esc_attr( sprintf( 'Duration: %s', $item_view->get_duration() ) ); ?>">
            <?php echo esc_html( $item_view->get_duration() ); ?>
        </span>
        <?php endif; ?>
    </span>
    <span class="lpl-playlist__item-info">
        <span class="lpl-playlist__item-number" aria-hidden="true">
            <?php echo esc_html( (string) $item_view->get_item_number() ); ?>
        </span>
        <span class="lpl-playlist__item-icon-wrap" aria-hidden="true">
            <svg class="lpl-playlist__item-icon lpl-playlist__item-icon--play" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
            </svg>
            <svg class="lpl-playlist__item-icon lpl-playlist__item-icon--pause" viewBox="0 0 24 24">
                <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z"/>
            </svg>
        </span>
        <span class="lpl-playlist__item-content">
            <meta itemprop="name" content="<?php echo esc_attr( $item_view->get_title() ); ?>">
            <?php if ( $item_view->get_url() ) : ?>
            <meta itemprop="url" content="<?php echo esc_url( $item_view->get_url() ); ?>">
            <?php endif; ?>
            <span class="lpl-playlist__item-title" itemprop="name">
                <?php echo esc_html( $item_view->get_title() ); ?>
            </span>
            <?php if ( $item_view->get_meta() ) : ?>
            <span class="lpl-playlist__item-meta">
                <?php echo esc_html( $item_view->get_meta() ); ?>
            </span>
            <?php endif; ?>
        </span>
        <?php if ( $item_view->get_duration() ) : ?>
        <span class="lpl-playlist__item-duration" aria-hidden="true">
            <?php echo esc_html( $item_view->get_duration() ); ?>
        </span>
        <?php endif; ?>
    </span>
</div>
