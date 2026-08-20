<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Lean Player's nav header, shared by all three framework-rendered admin
 * screens: Settings, Media Players, Playlist. Registered as this plugin's
 * 'template_override_path' (see includes/admin/class-menu.php), so the
 * framework's getTemplate('header', ...) resolves to this file instead of
 * core/partials/header.php on every one of those pages.
 *
 * Markup-only, self-sufficient: this plugin's admin-new pages aren't part of
 * the framework's generic tab system, so - unlike core/partials/header.php -
 * this override determines the active page and nav links itself rather than
 * consuming $main_tabs/$dropdown_tabs. See
 * includes/libs/lex-settings-new/core/docs/template-overrides.md.
 *
 * Nav items use data-nav-key (not data-tab) so lex-settings-core.js's
 * hash-fallback activateTab() no-ops on them: with no data-tab it reads
 * undefined and returns before touching any active classes, leaving the
 * server-rendered active state alone.
 */

$leanpl_current_page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

$leanpl_active_map = [
    'lean_player-all-players-new' => 'players',
    'lean-player-playlist'        => 'playlists',
    'lean-player'                 => 'players',
    'lean-player-settings'        => 'settings',
];
$leanpl_active = isset( $leanpl_active_map[ $leanpl_current_page ] ) ? $leanpl_active_map[ $leanpl_current_page ] : '';

$leanpl_nav_links = [
    'players'   => [ 'label' => __( 'Media Players', 'vapfem' ), 'url' => admin_url( 'admin.php?page=lean-player' ), 'icon' => 'dashicons-media-video' ],
    'playlists' => [ 'label' => __( 'Playlists', 'vapfem' ),  'url' => admin_url( 'admin.php?page=lean-player-playlist' ), 'icon' => 'dashicons-playlist-video' ],
    'settings'  => [ 'label' => __( 'Settings', 'vapfem' ),  'url' => admin_url( 'admin.php?page=lean-player-settings' ), 'icon' => 'dashicons-admin-generic' ],
];
?>
<style>
/* Scoped to this partial only - do not touch lex-settings-new core framework CSS. */
.lex-nav-header--admin-new .lex-nav-header__nav {
    flex: 1;
    justify-content: center;
    margin-left: 0;
}

.lex-nav-header--admin-new .lex-nav-header__actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.lex-nav-header--admin-new .lex-nav-header__help {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    font-size: var(--lex-font-size-base);
    font-weight: 600;
    color: var(--lex-color-text);
    text-decoration: none;
    border-radius: var(--lex-radius-md);
    white-space: nowrap;
    transition: all 0.15s ease;
}

.lex-nav-header--admin-new .lex-nav-header__help:hover {
    background-color: var(--wp-color-gray-2);
}

/* Browser default :visited link color otherwise beats the framework's
   plain-class color rule (a:visited has higher specificity than .lex-nav-header__item). */
.lex-nav-header--admin-new .lex-nav-header__item:visited,
.lex-nav-header--admin-new .lex-nav-header__help:visited {
    color: var(--lex-color-text);
}

.lex-nav-header--admin-new .lex-nav-header__upgrade .dashicons {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    line-height: 1;
    width: 18px;
    height: 18px;
}

@media screen and (max-width: 600px) {
    .lex-nav-header--admin-new .lex-nav-header__action-label {
        display: none;
    }
}
</style>
<div class="lex-nav-header lex-nav-header--admin-new">
  <div class="lex-nav-header__container">
    <div class="lex-nav-header__brand">
      <div class="lex-nav-header__logo">
        <span class="dashicons dashicons-video-alt3"></span>
      </div>
      <span class="lex-nav-header__name"><?php esc_html_e( 'Lean Player', 'vapfem' ); ?></span>
      <span class="lex-nav-header__version"><?php echo esc_html( leanpl_get_version() ); ?></span>
    </div>

    <nav class="lex-nav-header__nav">
      <?php foreach ( $leanpl_nav_links as $leanpl_nav_key => $leanpl_nav_link ): ?>
        <a href="<?php echo esc_url( $leanpl_nav_link['url'] ); ?>"
          class="lex-nav-header__item<?php echo $leanpl_nav_key === $leanpl_active ? ' lex-nav-header__item--active' : ''; ?>"
          data-nav-key="<?php echo esc_attr( $leanpl_nav_key ); ?>">
          <span class="dashicons <?php echo esc_attr( $leanpl_nav_link['icon'] ); ?>"></span><?php echo esc_html( $leanpl_nav_link['label'] ); ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="lex-nav-header__actions">
      <?php if ( ! leanpl_is_pro_active() ): ?>
        <a href="<?php echo esc_url( leanpl_get_upgrade_url( [ 'utm_medium' => 'admin-new-nav' ] ) ); ?>"
          class="lex-btn lex-btn--primary lex-nav-header__upgrade"
          target="_blank" rel="noopener noreferrer">
          <span class="dashicons dashicons-star-filled"></span><span class="lex-nav-header__action-label"><?php esc_html_e( 'Upgrade to Pro', 'vapfem' ); ?></span>
        </a>
      <?php else: ?>
        <a href="<?php echo esc_url( leanpl_ssot( 'brand', 'support_pro_url' ) ); ?>"
          class="lex-nav-header__help"
          target="_blank" rel="noopener noreferrer">
          <?php esc_html_e( 'Need Help?', 'vapfem' ); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
/* lex-settings-core.js delegates clicks on .lex-nav-header__item to its tab
   switcher (body-level, hash-based) - built for the Settings page's in-page
   tabs. Our nav items are real page links, so intercept here (bubbling
   reaches this element before it reaches body) and navigate directly. */
(function() {
    document.querySelectorAll('.lex-nav-header--admin-new .lex-nav-header__item[href]').forEach(function(el) {
        var href = el.getAttribute('href');
        if (!/^https?:\/\//i.test(href)) {
            return;
        }
        el.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.location.href = href;
        });
    });
})();
</script>
