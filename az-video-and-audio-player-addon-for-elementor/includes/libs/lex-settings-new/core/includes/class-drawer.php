<?php
/**
 * Drawer renderer for the lex framework.
 *
 * A non-modal side panel: no backdrop, no scroll lock, no focus trap. The
 * page behind it stays interactive, which is the whole point — see
 * lex-drawer.js for the a11y contract that follows from that.
 *
 * FieldRenderer::render() echoes, so a single render() taking content as a
 * string can't work without output buffering. Bookends instead:
 *
 *   Drawer::render_open( [ 'id' => 'my-drawer', 'title' => 'Add tracks' ] );
 *   // consumer echoes freely
 *   Drawer::render_close();
 *
 * Triggers are delegated on document, so markup injected after DOM-ready
 * still works:
 *
 *   <button type="button" data-lex-drawer-open="my-drawer">Open</button>
 *   <button type="button" data-lex-drawer-close>Close</button>
 *
 * @package Lex\Settings\V2\Services
 */

namespace Lex\Settings\V2\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Drawer {

	/**
	 * Open a drawer: wrapper, header, and the opening tag of the body.
	 *
	 * @param array $args {
	 *     @type string $id    Required. DOM id, also the JS API handle.
	 *     @type string $title Header title. Empty renders no title element.
	 *     @type string $width CSS width, e.g. '560px'. Defaults to the
	 *                         component's --lex-drawer-width.
	 *     @type string $side  'right' (default) or 'left'.
	 *     @type string $class Extra classes on the drawer element.
	 *     @type string $close_label aria-label for the close button.
	 * }
	 * @return void Echoes.
	 */
	public static function render_open( array $args ): void {
		$id = isset( $args['id'] ) ? (string) $args['id'] : '';
		if ( $id === '' ) {
			return;
		}

		$title       = isset( $args['title'] ) ? (string) $args['title'] : '';
		$width       = isset( $args['width'] ) ? (string) $args['width'] : '';
		$side        = ( isset( $args['side'] ) && $args['side'] === 'left' ) ? 'left' : 'right';
		$extra_class = isset( $args['class'] ) ? (string) $args['class'] : '';
		$close_label = isset( $args['close_label'] ) ? (string) $args['close_label'] : __( 'Close', 'vapfem' );

		$title_id = $id . '-title';

		$classes = 'lex-drawer lex-drawer--' . $side;
		if ( $extra_class !== '' ) {
			$classes .= ' ' . $extra_class;
		}

		// Inline width overrides the component default per instance.
		$style = $width !== '' ? ' style="--lex-drawer-width:' . esc_attr( $width ) . ';"' : '';

		// inert while closed is non-negotiable: the drawer can sit inside a
		// form and hold a full set of fields. Without it they stay tabbable.
		printf(
			'<div id="%s" class="%s" role="dialog" aria-modal="false" %s aria-hidden="true" tabindex="-1" inert%s>',
			esc_attr( $id ),
			esc_attr( $classes ),
			$title !== '' ? 'aria-labelledby="' . esc_attr( $title_id ) . '"' : '',
			$style // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);

		echo '<div class="lex-drawer__header">';

		if ( $title !== '' ) {
			// span, not a heading: wp-admin styles .postbox h2 and friends, and
			// a drawer rendered inside a metabox would inherit that over ours.
			// aria-labelledby carries the accessible name either way.
			printf(
				'<span class="lex-drawer__title" id="%s">%s</span>',
				esc_attr( $title_id ),
				esc_html( $title )
			);
		}

		printf(
			'<button type="button" class="lex-drawer__close" data-lex-drawer-close aria-label="%s">&times;</button>',
			esc_attr( $close_label )
		);

		echo '</div>';
		echo '<div class="lex-drawer__body">';
	}

	/**
	 * Close the body and the drawer opened by render_open().
	 *
	 * @return void Echoes.
	 */
	public static function render_close(): void {
		echo '</div></div>';
	}
}
