<?php
/**
 * Button Link card body, shared by the player and playlist Edit screens:
 * display-only copy boxes (block variant of the code field). Not a form
 * field - the link is copied, never saved.
 *
 * Every player / playlist gets #lpl-open-{type}-{id}. Paste into any
 * button's link field. Below that, the [lean_popup] shortcode for the same
 * item, which shows its own button and works without the Button Links
 * switch.
 *
 * @var int    $post_id          Current post ID (0 for a not-yet-saved item).
 * @var string $button_link_type 'player' or 'playlist'.
 */
if (!defined('ABSPATH')) {
  exit;
}

$button_link_type       = $button_link_type === 'playlist' ? 'playlist' : 'player';
$button_link_id         = $post_id > 0 ? (string) absint($post_id) : '...';
$edit_button_link       = '#lpl-open-' . $button_link_type . '-' . $button_link_id;
$edit_button_share_link = home_url('/' . $edit_button_link);
$edit_button_link_locked = !leanpl_is_pro_active();

$edit_button_link_rows = [
  [ 'text' => $edit_button_link,       'copied' => __('Button link copied!', 'vapfem') ],
  [ 'text' => $edit_button_share_link, 'copied' => __('Share link copied!', 'vapfem') ],
];

// Self-closing (" /]") on purpose: WordPress pairs an unclosed
// [lean_popup] with a later [/lean_popup] on the same page, swallowing
// everything in between. The closing slash stops that.
$edit_popup_shortcode = sprintf(
  '[lean_popup %1$s="%2$s" text="%3$s" /]',
  $button_link_type,
  $button_link_id,
  $button_link_type === 'playlist' ? __('Watch the series', 'vapfem') : __('Watch', 'vapfem')
);
?>
<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
      <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Button Link', 'vapfem'); ?>
      </div>
      <span class="lpl-group lpl-relative lpl-inline-flex lpl-items-center">
        <span data-lpl-bl-tooltip-trigger tabindex="0" aria-describedby="lpl-bl-info-text" class="lpl-inline-flex lpl-items-center [cursor:help]">
          <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('info'); ?>
        </span>
        <span
          id="lpl-bl-info-text"
          data-lpl-bl-tooltip
          role="tooltip"
          class="lpl-pointer-events-none lpl-opacity-0 group-hover:lpl-opacity-100 group-focus-within:lpl-opacity-100 lpl-transition-opacity lpl-fixed lpl-z-[100000] lpl-w-[220px] lpl-p-[10px_12px] lpl-bg-ink lpl-text-[#FFFFFF] lpl-text-[11px]/[1.5] lpl-rounded-[8px] lpl-shadow-lg lpl-text-left lpl-font-normal"
        >
          <?php esc_html_e("Add this link to any button on your site. If this player is already on the page, clicking the button scrolls down to it and plays. If not, it pops open in a window right there. You can also share this link directly - whoever opens it lands right on the player.", 'vapfem'); ?>
        </span>
      </span>
      <?php if ($edit_button_link_locked) : ?>
        <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[3px_7px] lpl-justify-center lpl-items-center lpl-bg-[#6C5CE7] lpl-rounded-[4px]">
          <div class="lpl-text-[9px]/[normal] lpl-box-border lpl-text-[#FFFFFF] lpl-font-bold lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('PRO', 'vapfem'); ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#767D86] lpl-font-normal lpl-text-left">
    <?php esc_html_e("Paste into any button's link field.", 'vapfem'); ?>
  </div>
  <?php foreach ($edit_button_link_rows as $edit_button_link_row) : ?>
    <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-p-[8px_8px_8px_12px] lpl-justify-between lpl-items-start lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
      <div data-lpl-shortcode-text class="lpl-text-[11px]/[1.5] lpl-box-border lpl-min-w-0 lpl-text-ink-mute [font-family:'JetBrains_Mono',monospace] lpl-font-normal lpl-text-left [overflow-wrap:anywhere]">
        <?php echo esc_html($edit_button_link_row['text']); ?>
      </div>
      <?php if ($edit_button_link_locked) : ?>
        <div onclick="openUpgradeModal(); return false;" data-upgrade-source="button-link" class="lpl-btn-icon lpl-btn-icon--xs lpl-btn-icon--plain lpl-shrink-0">
      <?php else : ?>
        <div data-lpl-copy-shortcode data-lpl-copy-success="<?php echo esc_attr($edit_button_link_row['copied']); ?>" class="lpl-btn-icon lpl-btn-icon--xs lpl-btn-icon--plain lpl-shrink-0">
      <?php endif; ?>
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('copy'); ?>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[6px] lpl-pt-[8px] lpl-justify-start lpl-items-center">
    <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Popup Shortcode', 'vapfem'); ?>
    </div>
    <?php if ($edit_button_link_locked) : ?>
      <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[3px_7px] lpl-justify-center lpl-items-center lpl-bg-[#6C5CE7] lpl-rounded-[4px]">
        <div class="lpl-text-[9px]/[normal] lpl-box-border lpl-text-[#FFFFFF] lpl-font-bold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('PRO', 'vapfem'); ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#767D86] lpl-font-normal lpl-text-left">
    <?php esc_html_e('Shows a button that opens this in a popup. Works even with Button Links turned off. Add button="outline" or button="plain" to change its look, or wrap an image to use it as the button.', 'vapfem'); ?>
  </div>
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[8px] lpl-p-[8px_8px_8px_12px] lpl-justify-between lpl-items-start lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
    <div data-lpl-shortcode-text data-lpl-popup-shortcode class="lpl-text-[11px]/[1.5] lpl-box-border lpl-min-w-0 lpl-text-ink-mute [font-family:'JetBrains_Mono',monospace] lpl-font-normal lpl-text-left [overflow-wrap:anywhere]">
      <?php echo esc_html($edit_popup_shortcode); ?>
    </div>
    <?php if ($edit_button_link_locked) : ?>
      <div onclick="openUpgradeModal(); return false;" data-upgrade-source="popup-shortcode" class="lpl-btn-icon lpl-btn-icon--xs lpl-btn-icon--plain lpl-shrink-0">
    <?php else : ?>
      <div data-lpl-copy-shortcode data-lpl-copy-success="<?php esc_attr_e('Popup shortcode copied!', 'vapfem'); ?>" class="lpl-btn-icon lpl-btn-icon--xs lpl-btn-icon--plain lpl-shrink-0">
    <?php endif; ?>
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('copy'); ?>
    </div>
  </div>
</div>
