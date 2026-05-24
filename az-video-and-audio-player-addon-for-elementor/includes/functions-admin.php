<?php
/**
 * Shared admin utility functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the type-selection modal HTML.
 *
 * @param array $args {
 *   @type string $modal_id    ID for the modal root element.
 *   @type string $backdrop_id ID for the backdrop element.
 *   @type string $close_id    ID for the close button.
 *   @type string $title       Modal heading text.
 *   @type string $sub         Modal subheading text.
 *   @type array  $cards       Each card: [ 'id', 'type', 'svg', 'title', 'desc' ].
 * }
 */
function leanpl_render_type_modal_html( $args ) {
	$modal_id    = isset( $args['modal_id'] )    ? $args['modal_id']    : '';
	$backdrop_id = isset( $args['backdrop_id'] ) ? $args['backdrop_id'] : '';
	$close_id    = isset( $args['close_id'] )    ? $args['close_id']    : '';
	$title       = isset( $args['title'] )       ? $args['title']       : '';
	$sub         = isset( $args['sub'] )         ? $args['sub']         : '';
	$cards       = isset( $args['cards'] )       ? $args['cards']       : [];
	?>
	<div class="lpl-pla__builder-modal"
	     id="<?php echo esc_attr( $modal_id ); ?>"
	     role="dialog"
	     aria-modal="true"
	     aria-labelledby="<?php echo esc_attr( $modal_id . '-title' ); ?>"
	     hidden>
		<div class="lpl-pla__builder-modal-backdrop" id="<?php echo esc_attr( $backdrop_id ); ?>"></div>
		<div class="lpl-pla__builder-modal-box">
			<button type="button"
			        class="lpl-pla__builder-modal-close"
			        id="<?php echo esc_attr( $close_id ); ?>"
			        aria-label="<?php esc_attr_e( 'Close', 'vapfem' ); ?>">&#215;</button>
			<h2 class="lpl-pla__builder-modal-title"
			    id="<?php echo esc_attr( $modal_id . '-title' ); ?>">
				<?php echo esc_html( $title ); ?>
			</h2>
			<p class="lpl-pla__builder-modal-sub">
				<?php echo esc_html( $sub ); ?>
			</p>
			<div class="lpl-pla__builder-modal-cards">
				<?php foreach ( $cards as $card ) : ?>
					<a class="lpl-pla__builder-modal-card"
					   id="<?php echo esc_attr( $card['id'] ); ?>"
					   href="#"
					   data-type="<?php echo esc_attr( $card['type'] ); ?>">
						<span class="lpl-pla__builder-modal-card-icon" aria-hidden="true">
							<?php echo $card['svg']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted inline SVG ?>
						</span>
						<span class="lpl-pla__builder-modal-card-title">
							<?php echo esc_html( $card['title'] ); ?>
						</span>
						<span class="lpl-pla__builder-modal-card-desc">
							<?php echo esc_html( $card['desc'] ); ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	<?php
}
