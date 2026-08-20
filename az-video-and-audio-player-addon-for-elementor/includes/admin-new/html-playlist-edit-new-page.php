<?php
/**
 * Playlist edit screen (POC).
 *
 * Same two-column shell as the player edit screen: shared top bar, a live
 * preview on the left, an accordion inspector on the right, shared footer.
 * The chrome partials (edit-topbar, edit-section-card, edit-footer) are
 * reused as-is - they were already written to read variables rather than
 * hardcode player specifics.
 *
 * What's real: the preview renders the actual saved playlist through the
 * existing leanpl_live_preview AJAX action (Live_Preview_Ajax's
 * lean_playlist branch, the same one the classic Display Options metabox
 * uses), and every inspector control re-renders it live because the field
 * names are the `_playlist_{field}` keys that endpoint already reads.
 *
 * Saving is wired too: the Publish/Update button (edit-topbar.php) posts
 * the title + the serialized inspector form to
 * leanpl_admin_new_save_playlist (ajax-actions.php), which writes the
 * `_playlist_*` registry keys through Metabox_Save::save_playlist(). Only
 * keys actually present in the request are touched, so a save never wipes
 * a key this screen does not manage - `_playlist_items` and
 * `_playlist_type` live outside the registry on purpose, which is why the
 * classic metabox remains the way to manage items (and why the list
 * screen's row links still point there). Pro is re-checked server-side
 * per key, so a forged POST can't change a locked field.
 *
 * $post_id, $edit_post_title, and $edit_new_playlist_type come from
 * Settings_Page::render_playlist_edit_new_page().
 */
if (!defined('ABSPATH')) {
  exit;
}

$edit_back_url       = admin_url('admin.php?page=lean-player-playlist');
$edit_trash_redirect = $edit_back_url;
$edit_publish_wired  = true;
$edit_title          = __('Edit Playlist', 'vapfem');
$edit_shortcode      = ($post_id > 0) ? ('[lean_playlist id=' . $post_id . ']') : '[lean_playlist id=...]';

// Overflow menu's Add New lands on this same screen with no ?post= - the
// render method treats that as a brand-new playlist (0-item empty-state
// preview, same panel a saved-but-empty playlist gets).
$edit_add_new_url   = admin_url('admin.php?page=lean-playlist-edit');
$edit_add_new_label = __('Add New Playlist', 'vapfem');
$edit_add_new_is_playlist = true;

// Same preview-link shape the player screen and the list rows use: a plain
// get_permalink() never triggers is_preview(), so the bare preview template
// never swaps in.
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

// Save Draft shows on a brand-new playlist and on an existing draft, not on
// a published one - same rule as WP's own edit screen. Passed to the footer
// partial, which renders the button conditionally.
$edit_is_draft = ($post_id > 0) ? (get_post_status($post_id) === 'draft') : true;

// Publish/Update follows the same rule as the player screen so the two
// screens agree about what the word means; both are wired to a real save
// endpoint now (leanpl_admin_new_save_player / _save_playlist).
$edit_publish_label = ($post_id > 0)
  ? __('Update', 'vapfem')
  : __('Publish', 'vapfem');

$edit_config   = include LEANPL_DIR . '/includes/admin-new/config/playlist-edit-sections.php';
$edit_sections = $edit_config['sections'];

// The Audio card only makes sense for an audio playlist - a video playlist
// never gets a "now playing" mini-player, so the field wouldn't do anything.
// Type is fixed at creation - chosen via the New Playlist picker modal for a
// brand-new one ($edit_new_playlist_type, set in
// Settings_Page::render_playlist_edit_new_page()), read from post meta for
// an existing one - so this is a plain server-side filter either way, not
// something that needs to react live.
$edit_playlist_type = ($post_id > 0) ? (get_post_meta($post_id, '_playlist_type', true) ?: 'video') : $edit_new_playlist_type;
if ('audio' !== $edit_playlist_type) {
  unset($edit_sections['playlist-audio']);
}

?>
<div class="lpl-admin lpl-playlist-edit-new-page lpl-box-border lpl-w-full lpl-h-fit lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start lpl-bg-canvas lpl-overflow-hidden">
  <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-topbar.php'; ?>
  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[24px] lpl-p-[24px] lpl-justify-start lpl-items-start">
    <div class="lpl-box-border [flex:1_1_0] lpl-min-w-0 lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[12px] lpl-justify-start lpl-items-start">
      <?php
      // "Live Preview" label + Light/Dark skin toggle, sitting right above the
      // preview surface. The toggle is a visual shortcut for the Appearance
      // card's _playlist_skin select (default = Light, dark = Dark): clicking
      // a button sets the hidden select's value and triggers the same
      // requestFormPreview() the form fields use, so the preview re-renders
      // with the chosen skin without a full save.
      //
      // Dark is pro-locked (PLAYLIST-FREE-VS-PRO.md Lock 17) - same
      // pro_options split as the Appearance card's Skin select. Clicking it
      // on free opens the upgrade modal instead of switching (initSkinToggle()
      // in admin-new.js reads data-lpl-skin-toggle-option-locked).
      $edit_skin_pro_opts       = leanpl_admin_new_playlist_registry()['_playlist_skin']['pro_options'] ?? [];
      $edit_skin_toggle_dark_locked = in_array('dark', $edit_skin_pro_opts, true);
      ?>
      <div data-lpl-preview-label-row class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
        <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]">
          <?php esc_html_e('Live Preview', 'vapfem'); ?>
        </div>
        <div data-lpl-skin-toggle class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[4px] lpl-p-[4px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_#C6CBD1] [outline-offset:-0.5px] lpl-rounded-[8px]">
          <div data-lpl-skin-toggle-option="default" class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[8px_12px] lpl-justify-center lpl-items-center lpl-bg-[#00000000] aria-pressed:lpl-bg-[#000000] lpl-rounded-[8px]">
            <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#767D86] group-aria-pressed:lpl-text-[#FFFFFF] lpl-font-semibold lpl-text-left [white-space:nowrap]">
              <?php esc_html_e('Light', 'vapfem'); ?>
            </div>
          </div>
          <div data-lpl-skin-toggle-option="dark"<?php echo $edit_skin_toggle_dark_locked ? ' data-lpl-skin-toggle-option-locked="true"' : ''; ?> class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-p-[8px_12px] lpl-justify-center lpl-items-center lpl-bg-[#00000000] aria-pressed:lpl-bg-[#000000] lpl-rounded-[8px]">
            <div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#767D86] group-aria-pressed:lpl-text-[#FFFFFF] lpl-font-semibold lpl-text-left [white-space:nowrap]">
              <?php esc_html_e('Dark', 'vapfem'); ?>
            </div>
            <?php if ($edit_skin_toggle_dark_locked) : ?>
              <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('gem'); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php if ($post_id > 0) : ?>
        <div data-lpl-edit-surface="player" class="lpl-box-border lpl-w-full">
          <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-player-surface.php'; ?>
        </div>
      <?php else : ?>
        <?php
        // A playlist can't preview itself before it exists - the renderer
        // takes a real playlist ID and reads its items from post meta - but
        // there's nothing playlist-specific about the "0 items" panel
        // itself, so this reuses Live_Preview_Ajax's real empty-state markup
        // (same one a saved-but-empty playlist gets over AJAX) with a bare
        // default Config rather than showing a second, bespoke placeholder.
        // Only header text/count label/CSS classes are read here, never
        // real items, so a playlist_id-less Config is safe.
        //
        // data-lpl-preview-target wrapper matches edit-player-surface.php's:
        // .lpl-playlist__player--empty and .lpl-playlist__empty-state are
        // only styled as its descendants (tailwind-admin.src.css), since
        // that's the attribute the AJAX path always injects this same
        // markup into.
        ?>
        <div data-lpl-preview-target class="lpl-box-border lpl-w-full lpl-min-h-[460px] lpl-shrink-0 lpl-flex lpl-flex-col lpl-justify-start lpl-items-center lpl-rounded-[10px] lpl-overflow-hidden">
          <?php
          $edit_empty_config = new \LeanPL\Playlist\Config();
          echo \LeanPL\Live_Preview_Ajax::get_instance()->render_playlist_empty_state( $edit_empty_config );
          ?>
        </div>
      <?php endif; ?>
      <?php
      // Also no longer gated on $post_id > 0, same reasoning as the drawer
      // include below: the empty-state panel's own "Add Track" button only
      // exists while the preview has 0 items, so once refreshTrackDrawerPreview()
      // lands a non-empty response - whether that's an existing playlist's
      // first load, or a brand-new one right after its first staged track
      // silently auto-drafts it (see refreshTrackDrawerPreview() in
      // admin-new.js) - this is the only remaining way to open the drawer
      // for another track. Starts hidden either way: requestPreview()'s
      // response.data.empty toggle (admin-new.js) is what reveals it, so it
      // never sits under a still-loading/still-empty panel then jumps.
      ?>
      <div data-lpl-add-track-row class="lpl-hidden lpl-box-border lpl-w-full lpl-flex lpl-flex-row lpl-justify-center lpl-items-center">
        <div data-lpl-track-drawer-open class="lpl-cursor-pointer lpl-btn lpl-btn--md lpl-btn--indigo lpl-shadow-lg">
          <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('plus'); ?>
          <div class="lpl-box-border lpl-text-left [white-space:nowrap]">
            <?php esc_html_e('Add Track', 'vapfem'); ?>
          </div>
        </div>
      </div>
      <?php
      // Drawer include and its data-lpl-track-drawer-open triggers (the
      // empty-state panel's own "Add Track" button above, plus the floating
      // one just above) are no longer gated on $post_id > 0: Quick/Bulk Add
      // already create real lean_player posts independent of whether this
      // playlist post exists yet, the drawer's own "existing items" query
      // already guards itself on $post_id > 0 internally, and
      // leanpl_admin_new_save_playlist() already writes the drawer's staged
      // item_ids off whatever post ID the save just produced - new or
      // existing - so a track staged before the first Save still lands in
      // _playlist_items once Publish/Save Draft runs.
      ?>
      <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/playlist/edit-playlist-track-drawer.php'; ?>
    </div>
    <form data-lpl-edit-form class="lpl-box-border [flex:0_0_400px] 2xl:[flex:0_0_480px] lpl-min-w-0 lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[12px] lpl-justify-start lpl-items-start">
      <?php foreach ($edit_sections as $section_key => $section) : ?>
        <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-section-card.php'; ?>
      <?php endforeach; ?>
    </form>
  </div>
  <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-footer.php'; ?>
  <?php // Inside .lpl-admin on purpose - every --lpl-* CSS variable the modal's
  // classes read (surface/ink/line-strong/brand) is scoped to that wrapper,
  // same as everything else on this screen. ?>
  <?php include LEANPL_DIR . '/includes/admin-new/partials/edit/playlist/add-playlist-type-modal.php'; ?>
  <?php
  // Same reasoning as html-player-edit-new-page.php: this screen renders its
  // own layout-grid partial rather than the lex_settings field renderer the
  // injector normally listens on, so it never fires here on its own.
  \LeanPL\Custom_Preset_Injector::get_instance()->maybe_inject('_playlist_player_layout', [], ['post_id' => $post_id]);
  ?>
</div>
