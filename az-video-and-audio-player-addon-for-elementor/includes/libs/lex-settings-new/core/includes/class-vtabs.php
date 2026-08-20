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
 * A tab may carry a 'children' array of the same shape (id/label/icon) to
 * render a submenu. Icons are optional per child. A parent with children
 * becomes a non-navigating expand/collapse toggle; only children (and
 * childless top-level tabs) carry data-vtab and activate a content panel.
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

		$out  = '<div class="lex-vtabs__nav-wrap"><div class="lex-vtabs__nav">';
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

				$children = ! empty( $tab['children'] ) && is_array( $tab['children'] ) ? $tab['children'] : [];

				if ( empty( $children ) ) {
					$out .= sprintf(
						'<button type="button" data-vtab="%s">%s<span>%s</span></button>',
						esc_attr( $tab['id'] ),
						isset( $tab['icon'] ) ? $tab['icon'] : '',
						esc_html( isset( $tab['label'] ) ? $tab['label'] : '' )
					);
					continue;
				}

				// Expanded: submenu starts open and can't be collapsed — no chevron,
				// no toggle affordance. 'is-open' is baked into the markup itself so
				// children are visible even before JS runs.
				$expanded    = ! empty( $tab['expanded'] );
				$item_class  = 'lex-vtabs__item lex-vtabs__item--has-children' . ( $expanded ? ' lex-vtabs__item--expanded is-open' : '' );
				$chevron     = $expanded ? '' : '<span class="lex-vtabs__chevron"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></span>';
				$aria_attr   = $expanded ? '' : ' aria-expanded="false"';

				$out .= '<div class="' . esc_attr( $item_class ) . '">';
				$out .= sprintf(
					'<button type="button" class="lex-vtabs__parent" data-vtab-parent="%s"%s>%s<span>%s</span>%s</button>',
					esc_attr( $tab['id'] ),
					$aria_attr,
					isset( $tab['icon'] ) ? $tab['icon'] : '',
					esc_html( isset( $tab['label'] ) ? $tab['label'] : '' ),
					$chevron
				);

				$out .= '<div class="lex-vtabs__children">';
				foreach ( $children as $child ) {
					if ( empty( $child['id'] ) ) {
						continue;
					}
					$out .= sprintf(
						'<button type="button" data-vtab="%s" data-vtab-parent-id="%s">%s<span>%s</span></button>',
						esc_attr( $child['id'] ),
						esc_attr( $tab['id'] ),
						! empty( $child['icon'] ) ? $child['icon'] : '<span class="lex-vtabs__child-arrow" aria-hidden="true">↳</span>',
						esc_html( isset( $child['label'] ) ? $child['label'] : '' )
					);
				}
				$out .= '</div></div>';
			}

			if ( $i < $last ) {
				$out .= '<div class="lex-vtabs__divider"></div>';
			}
		}

		$out .= '</div></div>';
		return $out;
	}
}
