<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

// $post_id and $edit_post_title come from Settings_Page::render_player_edit_new_page()
// (the includer) - real values when ?post= names an existing player, 0 /
// 'Untitled Player' for a brand-new one. The right-column accordion cards are
// wired to real post meta (see includes/admin-new/class-metabox-save.php);
// the footer actions are wired too (Trash, Save Draft, Update/Publish).
$edit_back_url        = admin_url('admin.php?page=lean-player');
$edit_trash_redirect  = $edit_back_url;
$edit_publish_wired   = true;
$edit_title           = __('Edit Player', 'vapfem');
$edit_shortcode   = ($post_id > 0) ? ('[lean_player id=' . $post_id . ']') : '[lean_player id=...]';

// Overflow menu's Add New lands on this same screen with no ?post= - the
// render method treats that as a brand-new player (no source picked yet).
$edit_add_new_url   = admin_url('admin.php?page=lean-player-edit');
$edit_add_new_label = __('Add New Player', 'vapfem');
// A plain get_permalink() never triggers is_preview(), so the bare preview
// template (Player_Preview::swap_preview_template()) never swaps in - same
// preview-link shape class-all-players-data.php's row-level preview_url
// already uses.
$edit_preview_url = ($post_id > 0) ? get_preview_post_link($post_id, [
  'preview'       => 'true',
  'preview_id'    => $post_id,
  'preview_nonce' => wp_create_nonce('post_preview_' . $post_id),
]) : '#';

if ($post_id > 0) {
  $edit_status_object = get_post_status_object(get_post_status($post_id));
  $edit_status_name   = $edit_status_object ? $edit_status_object->label : get_post_status($post_id);
  $edit_status_label  = $edit_status_name . ' · ' . get_the_date('M j, Y', $post_id);
} else {
  $edit_status_label = __('Not published yet', 'vapfem');
}

// Save Draft shows on a brand-new player and on an existing draft, not on a
// published one - same rule as WP's own edit screen. Passed to the footer
// partial, which renders the button conditionally.
$edit_is_draft = ($post_id > 0) ? (get_post_status($post_id) === 'draft') : true;

// Publish creates a player, Update edits one that already exists - so the
// label follows whether ?post= named a real player, not its post status.
// A draft still says Update: the button is editing something that's already
// there. admin-new.js flips this same label to 'Update' in place once a
// Publish click succeeds, no reload needed.
$edit_publish_label = ($post_id > 0)
  ? __('Update', 'vapfem')
  : __('Publish', 'vapfem');

$edit_config   = include LEANPL_DIR . '/includes/admin-new/config/player-edit-sections.php';
$edit_sections = $edit_config['sections'];

// Which of the two left-column views the screen opens on is decided here,
// server-side, from the saved source ($edit_saved_source, built from real
// post meta by Settings_Page::render_player_edit_new_page()). An existing
// player with a source opens straight on the preview and never ships the
// picker markup at all - it used to render the picker and let admin-new.js
// hide it a moment later, which flashed the "Add media from different
// sources" panel on every load of an already-configured player. A brand-new
// player (no ?post=) and an existing one that was never given a source both
// still need the picker, so they get it. After that, swapping between the
// two is live client-side state (data-lpl-edit-surface, admin-new.js),
// driven by the user picking a source.
// See docs/admin-redesign-pencil-porting-process.md.
$edit_has_saved_source = ($post_id > 0 && !empty($edit_saved_source['_player_type']));

// The Source card (edit-section-source.php) only makes sense once there's
// a real source to show/edit - a brand-new player gets the picker instead,
// same condition as $edit_has_saved_source above.
if (!$edit_has_saved_source) {
  unset($edit_sections['source']);
}

$edit_source_tabs           = include LEANPL_DIR . '/includes/admin-new/config/player-edit-source-tabs.php';
$edit_source_active_tab     = '';
$edit_source_show_url_field = true;
$edit_source_show_or        = true;
$edit_source_url_label      = __('PASTE A MEDIA URL', 'vapfem');
$edit_source_url_placeholder = 'https://example.com/video.mp4';
$edit_source_help_text      = __("Paste a YouTube, Vimeo, direct video/audio link, or audio stream URL, we'll detect the source automatically.", 'vapfem');

?>
<div class="lpl-admin lpl-player-edit-new-page lpl-box-border lpl-w-full lpl-h-fit lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start lpl-bg-canvas lpl-overflow-hidden">
  <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-topbar.php'; ?>
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[24px] lpl-p-[24px] lpl-justify-start lpl-items-start">
    <div class="lpl-box-border [flex:1_1_0] lpl-min-w-0 lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[12px] lpl-justify-start lpl-items-start">
      <?php
      // "Live Preview" label, same row this screen's playlist counterpart
      // uses (html-playlist-edit-new-page.php) minus the Light/Dark skin
      // toggle - single players have no skin field to shortcut here.
      ?>
      <div data-lpl-preview-label-row class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
        <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Live Preview', 'vapfem'); ?>
        </div>
      </div>
      <?php if (!$edit_has_saved_source) : ?>
        <div data-lpl-edit-surface="picker" class="lpl-box-border lpl-w-full">
          <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/player/edit-source-picker.php'; ?>
        </div>
      <?php endif; ?>
      <div data-lpl-edit-surface="player" class="<?php echo $edit_has_saved_source ? '' : 'lpl-hidden '; ?>lpl-box-border lpl-w-full">
        <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-player-surface.php'; ?>
      </div>
    </div>
    <form data-lpl-edit-form class="lpl-box-border [flex:0_0_400px] 2xl:[flex:0_0_480px] lpl-min-w-0 lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[12px] lpl-justify-start lpl-items-start">
      <?php foreach ($edit_sections as $section_key => $section) : ?>
        <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-section-card.php'; ?>
      <?php endforeach; ?>
    </form>
  </div>
  <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-footer.php'; ?>
  <?php
  // This screen renders its own layout-grid partial (edit-layout-grid.php),
  // not the lex_settings field renderer the injector normally listens on
  // (lex_settings/after_field_render), so it never fires here on its own -
  // call it directly to print the same modal shell / preset-ref input /
  // localized data the classic metabox and global Settings get.
  \LeanPL\Custom_Preset_Injector::get_instance()->maybe_inject('_player_layout', [], ['post_id' => $post_id]);
  ?>
</div>
