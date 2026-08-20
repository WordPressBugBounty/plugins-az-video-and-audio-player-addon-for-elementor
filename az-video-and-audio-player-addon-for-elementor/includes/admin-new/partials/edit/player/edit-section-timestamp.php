<?php
/**
 * Timestamp Link Shortcode card: display-only copy box (block variant of
 * the code field). Not a form field - the shortcode is copied, never saved.
 *
 * Real tag is [lean_timestamp] (see Timestamp_Shortcode::render_timestamp_shortcode),
 * not the placeholder "player_timestamp" this card used to show. player="{id}"
 * targets this specific player's wrapper; time/link text are left for the
 * user to fill in.
 *
 * @var int $post_id Current player post ID (0 for a not-yet-saved player).
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_timestamp_shortcode = $post_id > 0
  ? sprintf('[lean_timestamp time="0:00" player="%d"]Jump to moment[/lean_timestamp]', $post_id)
  : '[lean_timestamp time="0:00" player="..."]Jump to moment[/lean_timestamp]';
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
      <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Timestamp Link', 'vapfem'); ?>
      </div>
    </div>
  </div>
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-p-[8px_8px_8px_12px] lpl-justify-between lpl-items-start lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
    <div data-lpl-shortcode-text class="lpl-text-[11px]/[1.5] lpl-box-border lpl-min-w-0 lpl-text-ink-mute [font-family:'JetBrains_Mono',monospace] lpl-font-normal lpl-text-left [overflow-wrap:anywhere]">
      <?php echo esc_html($edit_timestamp_shortcode); ?>
    </div>
    <div data-lpl-copy-shortcode class="lpl-btn-icon lpl-btn-icon--xs lpl-btn-icon--plain lpl-shrink-0">
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('copy'); ?>
    </div>
  </div>
</div>
