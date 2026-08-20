<?php
/**
 * Shared edit-screen footer. Player and playlist edit screens both
 * `include` this file after setting the variables below in scope -
 * this partial reads them, it never hardcodes anything screen-specific.
 *
 * Expected variables:
 * @var string $edit_status_label   Status line text, e.g. "Published · Jul 4, 2026".
 * @var string $edit_publish_label  'Publish' or 'Update' - mirrors the topbar
 *                                  button; admin-new.js flips this topbar label
 *                                  to 'Update' in place after a successful save.
 * @var int    $post_id             The post being edited (0 for a brand-new one).
 * @var bool   $edit_publish_wired  Whether the Publish/Update button should be
 *                                  live. False greys it out (no save endpoint).
 * @var string $edit_trash_redirect Where Move to Trash lands after trashing -
 *                                  each screen's own list.
 * @var bool   $edit_is_draft       Whether the Save Draft button should show.
 *                                  True for a brand-new post and for an
 *                                  existing draft; false for a published post
 *                                  (matching WP's own rule - drafts save,
 *                                  published posts only Update).
 *
 * Three actions, all wired:
 *  - Move to Trash: reuses the list screen's data-lpl-row-action mechanism
 *    (initRowActions() in admin-new.js, backed by leanpl_admin_new_bulk_trash),
 *    same as the topbar overflow's Trash. Only renders when a real post is
 *    being edited ($post_id > 0).
 *  - Save Draft: posts to the screen's save endpoint with post_status=draft
 *    (admin-new.js initSaveDraftButton). Hidden unless $edit_is_draft.
 *  - Update/Publish: shares the topbar's data-lpl-publish-button hook
 *    (admin-new.js initPublishButton binds to every match); the label below
 *    matches the topbar via $edit_publish_label.
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<div
  class="lpl-box-border lpl-w-full lpl-h-[63.5px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_24px] lpl-justify-between lpl-items-center lpl-bg-[#FFFFFF] [border-width:1px_0px_0px_0px] [border-style:solid] [border-color:var(--lpl-line)] [margin:-0.5px_0px_0px_0px]"
>
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink-mute lpl-font-normal lpl-text-left [white-space:nowrap]">
    <?php echo esc_html($edit_status_label); ?>
  </div>
  <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-center">
    <?php if ($post_id > 0) : ?>
    <div
      data-lpl-row-action="trash" data-lpl-row-action-id="<?php echo esc_attr((string) $post_id); ?>"
      data-lpl-row-action-redirect="<?php echo esc_url($edit_trash_redirect); ?>"
      class="lpl-cursor-pointer lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink-soft lpl-font-normal lpl-text-left [white-space:nowrap]">
      <?php esc_html_e('Move to Trash', 'vapfem'); ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($edit_is_draft)) : ?>
    <div <?php echo $edit_publish_wired ? 'data-lpl-save-draft role="button" tabindex="0"' : 'aria-disabled="true"'; ?>
      class="lpl-btn lpl-btn--sm lpl-btn--outline aria-disabled:lpl-opacity-60 aria-disabled:lpl-pointer-events-none">
      <div data-lpl-save-draft-label class="lpl-box-border lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Save Draft', 'vapfem'); ?>
      </div>
    </div>
    <?php endif; ?>
    <div <?php echo $edit_publish_wired ? 'data-lpl-publish-button role="button" tabindex="0"' : 'aria-disabled="true" title="' . esc_attr__('Saving is not wired up on this screen yet.', 'vapfem') . '"'; ?>
      class="lpl-btn lpl-btn--sm lpl-btn--indigo aria-disabled:lpl-opacity-60 aria-disabled:lpl-pointer-events-none">
      <div data-lpl-publish-label class="lpl-box-border lpl-text-left [white-space:nowrap]">
        <?php echo esc_html($edit_publish_label); ?>
      </div>
    </div>
  </div>
</div>