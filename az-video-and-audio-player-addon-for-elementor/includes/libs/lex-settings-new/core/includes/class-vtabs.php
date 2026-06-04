<?php
/**
 * Vtabs nav renderer for the lex-vtabs framework.
 *
 * Consumers declare a tab schema and call render_nav() / render_nav_flat().
 * The framework owns every markup decision (wrapper, group labels, dividers,
 * buttons), so three admin surfaces stop hand-rolling the same HTML.
 *
 * Tab schema (per tab):
 *   [ 'id' => 'p-source', 'label' => 'Source', 'icon' => '<svg>...</svg>' ]
 *
 * Group schema (for render_nav):
 *   [ 'label' => 'Setup', 'tabs' => [ ...tabs ] ]
 *
 * Future per-tab options (active, disabled, badge, locked, tooltip) can be
 * added as optional keys without breaking existing consumers.
 *
 * @package Lex\Settings\V2\Services
 */

namespace Lex\Settings\V2\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Vtabs {

	/**
	 * Render a flat tab list (no grouping).
	 *
	 * @param array $tabs Flat tab schema.
	 * @return string HTML for the .lex-vtabs__nav block.
	 */
	public static function render_nav_flat( array $tabs ): string {
		return self::render_nav( [ [ 'label' => '', 'tabs' => $tabs ] ] );
	}

	/**
	 * Render the vertical-tab sidebar from a grouped schema.
	 *
	 * @param array $groups Grouped tab schema.
	 * @return string HTML for the .lex-vtabs__nav block.
	 */
	public static function render_nav( array $groups ): string {
		// Filter to groups that actually have tabs. An empty group would
		// otherwise emit a lonely label + trailing divider.
		$groups = array_values( array_filter( $groups, function ( $g ) {
			return ! empty( $g['tabs'] ) && is_array( $g['tabs'] );
		} ) );

		$out  = '<div class="lex-vtabs__nav">';
		$last = count( $groups ) - 1;

		foreach ( $groups as $i => $group ) {
			$label = isset( $group['label'] ) ? (string) $group['label'] : '';
			if ( $label !== '' ) {
				$out .= '<div class="lex-vtabs__group-label">' . esc_html( $label ) . '</div>';
			}

			foreach ( $group['tabs'] as $tab ) {
				if ( empty( $tab['id'] ) ) {
					continue;
				}
				$out .= sprintf(
					'<button type="button" data-vtab="%s">%s<span>%s</span></button>',
					esc_attr( $tab['id'] ),
					isset( $tab['icon'] ) ? $tab['icon'] : '',
					esc_html( isset( $tab['label'] ) ? $tab['label'] : '' )
				);
			}

			if ( $i < $last ) {
				$out .= '<div class="lex-vtabs__divider"></div>';
			}
		}

		$out .= '</div>';
		return $out;
	}
}
