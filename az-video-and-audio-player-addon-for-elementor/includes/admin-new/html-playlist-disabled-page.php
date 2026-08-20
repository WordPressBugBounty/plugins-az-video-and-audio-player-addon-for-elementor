<?php
/**
 * Playlists page when the playlist feature is turned off.
 *
 * Reached at admin.php?page=lean-player-playlist while playlist.enabled is
 * false. class-menu.php still registers this slug (as a URL-only page - no
 * menu row) so the URL keeps resolving here instead of WP's generic "not
 * allowed" wp_die() - see the register_url_only_page() call in
 * Menu::add_menu_items(). Settings_Page::render_all_playlists_new_page()
 * is what branches here instead of html-all-playlists-new-page.php.
 */
if (!defined('ABSPATH')) {
  exit;
}

// Same hash the Settings screen's tab switcher reads on load
// (lex-settings-core.js handleInitialHash()) - lands the user on the
// Settings tab. The Playlist vtab itself has no URL-addressable state
// (vtab selection is localStorage-only), so the copy below tells the user
// where to click once they land.
$leanpl_settings_url = admin_url('admin.php?page=lean-player-settings#settings');
?>
<div class="lpl-admin lpl-playlist-disabled-page">
  <?php
  \Lex\Settings\V2\Settings::getInstance( 'leanpl' )->getTemplate( 'header' );
  ?>
  <div class="lpl-box-border lpl-w-full lpl-max-w-page lpl-mx-auto lpl-h-fit lpl-flex lpl-flex-col lpl-gap-0 lpl-p-[0px_36px_36px_36px] min-[1600px]:lpl-px-0 lpl-justify-start lpl-items-start lpl-overflow-hidden">
    <div class="lpl-box-border lpl-w-full lpl-min-h-[460px] [box-shadow:0px_10px_30px_0px_#1B22330F] lpl-flex lpl-flex-col lpl-gap-[16px] lpl-justify-center lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_#E3E6EB] [outline-offset:-0.5px] lpl-rounded-[16px] lpl-p-[24px]">
      <div class="lpl-box-border lpl-w-[52px] lpl-shrink-0 lpl-h-[52px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line)] [outline-offset:-0.5px] lpl-rounded-[14px]">
        <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('list-video'); ?>
      </div>
      <div class="lpl-box-border lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-center">
        <div class="lpl-text-[15px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-center">
          <?php esc_html_e('Playlist feature is disabled', 'vapfem'); ?>
        </div>
        <div class="lpl-text-[13px]/[1.5] lpl-box-border lpl-max-w-[360px] lpl-text-ink-mute lpl-font-normal lpl-text-center">
          <?php esc_html_e('Turn it back on from Settings to create and manage playlists.', 'vapfem'); ?>
        </div>
      </div>
      <a href="<?php echo esc_url($leanpl_settings_url); ?>"
        class="lpl-btn lpl-btn--md lpl-btn--outline">
        <div class="lpl-box-border lpl-text-left [white-space:nowrap]">
          <?php
          /* translators: breadcrumb-style path to the Playlist enable toggle, shown as button text. */
          esc_html_e('Settings → Playlist → Enable Playlist Feature', 'vapfem');
          ?>
        </div>
      </a>
    </div>
  </div>
</div>
