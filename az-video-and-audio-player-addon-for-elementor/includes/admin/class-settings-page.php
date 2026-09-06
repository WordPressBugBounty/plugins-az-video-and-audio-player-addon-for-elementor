<?php
namespace LeanPL;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings Page Class
 *
 * Handles routing and rendering of admin pages
 */
class Settings_Page {

    /**
     * Single instance
     */
    private static $_instance = null;

    /**
     * Get instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize
     */
    public function init() {
        // Any initialization if needed
    }

    /**
     * Render hire me page
     */
    public function render_hire_me_page() {
        include LEANPL_DIR . '/includes/views/html-hire-page.php';
    }

    /**
     * Render All Players (New) page - custom Tailwind markup, not the native
     * post list table.
     *
     * The top-level menu (`page=lean-player`) and the list submenu
     * (`page=lean_player-all-players-new`) share this one callback. A `post`
     * param on either URL means "open that player in the editor", so this
     * defers to the edit-screen renderer instead of the list - the same
     * branch `lean-player-edit` reaches directly. Without `post`, it's the
     * list as before.
     */
    public function render_all_players_new_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'vapfem'));
        }

        if (isset($_GET['post'])) {
            $this->render_player_edit_new_page();
            return;
        }

        include LEANPL_DIR . '/includes/admin-new/html-all-players-new-page.php';
    }

    /**
     * Render All Playlists (New) page - custom Tailwind markup, not the native
     * post list table.
     */
    public function render_all_playlists_new_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'vapfem'));
        }

        if (!leanpl_get_option('playlist.enabled', true)) {
            include LEANPL_DIR . '/includes/admin-new/html-playlist-disabled-page.php';
            return;
        }

        include LEANPL_DIR . '/includes/admin-new/html-all-playlists-new-page.php';
    }

    /**
     * Render Edit Playlist page - POC. Same two-column shell as the player
     * edit screen (preview left, inspector right), reusing its chrome
     * partials. Preview is real (the existing leanpl_live_preview AJAX
     * action's lean_playlist branch); saving is wired through
     * leanpl_admin_new_save_playlist - see ajax-actions.php.
     */
    public function render_playlist_edit_new_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'vapfem'));
        }

        // ?post= identifies a real, previously-saved playlist. Falls back to
        // 0 on anything invalid or not ours - the template treats 0 as
        // "nothing to preview yet", never as an error.
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        if ($post_id > 0 && (get_post_type($post_id) !== 'lean_playlist' || !current_user_can('edit_post', $post_id))) {
            $post_id = 0;
        }

        $edit_post_title = ($post_id > 0) ? get_the_title($post_id) : '';
        if ($edit_post_title === '') {
            $edit_post_title = __('Untitled Playlist', 'vapfem');
        }

        // Fallback default for the rare case this screen is reached with no
        // ?post= at all (a direct/bookmarked URL, not the normal flow): the
        // New Playlist picker modal (add-playlist-type-modal.php) now
        // creates the real post - with _playlist_type already set - via
        // leanpl_admin_new_create_playlist() *before* ever navigating here,
        // so every normal arrival already has a real ?post= and this branch
        // never runs for it. An existing post's type is fixed at creation
        // and read from its own post meta below instead.
        $edit_new_playlist_type = 'video';

        // The Add Track drawer's Quick Add/Bulk Add tabs open wp.media
        // (Select/Upload Media, Custom Thumbnail) the same way the player
        // edit screen does, so it needs the same enqueue.
        wp_enqueue_media();

        // Same localized object the player screen uses, so admin-new.js's
        // preview code needs one extra field rather than a second pipeline:
        // postType picks Live_Preview_Ajax's lean_playlist branch, which
        // renders from the real post + whatever overrides the inspector
        // sends. That endpoint writes nothing to the DB.
        //
        // saveNonce is the dedicated nonce for leanpl_admin_new_save_playlist
        // (the only endpoint that does write on this screen), separate from
        // the preview nonce above - same shape as the player screen's pair.
        //
        // trackDrawerNonce is for leanpl_playlist_quick_add/leanpl_playlist_batch_add
        // (includes/playlist/ajax-actions.php) - the same endpoints the
        // classic metabox's Quick Add/Bulk Add already use, reused here as-is
        // rather than duplicated, so a separate nonce action name
        // ('leanpl_playlist_admin') is required.
        //
        // trackDrawerIcons: a Quick Add/Bulk Add row built client-side
        // (trackDrawerInsertRow() in admin-new.js) needs the same check/plus
        // icon markup the server-rendered rows use (Admin_New_Icons::get()) -
        // localized here rather than duplicated as a second copy of the SVG
        // in JS.
        wp_localize_script('leanpl-admin-new', 'leanplAdminNewPreview', [
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('leanpl_live_preview'),
            'saveNonce' => wp_create_nonce('leanpl_admin_new_save_playlist'),
            'trackDrawerNonce' => wp_create_nonce('leanpl_playlist_admin'),
            'trackDrawerIcons' => [
                'check' => \LeanPL\Admin_New\Admin_New_Icons::get('check'),
                'plus'  => \LeanPL\Admin_New\Admin_New_Icons::get('plus'),
            ],
            // trackListIcons: a track added via the drawer needs to also land
            // as a Playlist Items card row (trackListInsertRow() in
            // admin-new.js) - same "localize instead of duplicating the SVG
            // in JS" reasoning as trackDrawerIcons above, just the icon set
            // edit-section-playlist-track-list.php's row markup uses instead.
            'trackListIcons' => [
                'grip-vertical' => \LeanPL\Admin_New\Admin_New_Icons::get('grip-vertical'),
                'copy'          => \LeanPL\Admin_New\Admin_New_Icons::get('copy'),
                'x'             => \LeanPL\Admin_New\Admin_New_Icons::get('x'),
            ],
            'postId'   => $post_id,
            'postType' => 'lean_playlist',
            // Existing playlist: its real saved type (fixed at creation).
            // Brand-new: whatever the New Playlist picker modal chose, via
            // $edit_new_playlist_type above - sent back as playlist_type in
            // every save payload (submitSave() in admin-new.js), which
            // leanpl_admin_new_save_playlist() writes to _playlist_type
            // once, only on the post's first-ever save.
            'playlistType' => ($post_id > 0) ? (get_post_meta($post_id, '_playlist_type', true) ?: 'video') : $edit_new_playlist_type,
        ]);

        include LEANPL_DIR . '/includes/admin-new/html-playlist-edit-new-page.php';
    }

    /**
     * Render Edit Player (New) page - custom Tailwind markup, visual-phase
     * port of the post.php metabox, with title + picked-source save wired to
     * a real post (see ajax-actions.php's leanpl_admin_new_save_player()).
     * Everything else in the accordion (Layout & Branding, Behavior,
     * Advanced) is still a static mockup - see
     * includes/admin-new/html-player-edit-new-page.php.
     */
    public function render_player_edit_new_page() {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'vapfem'));
        }

        // Only this page's Media Library tab opens the picker, so it's
        // enqueued here rather than for every leanpl_is_our_admin_page() screen.
        wp_enqueue_media();

        // ?post= identifies a real, previously-saved player. Falls back to 0
        // (a brand-new, unsaved player) on anything invalid or not ours -
        // the rest of this method and the template it includes both treat 0
        // as "nothing to load yet", never as an error.
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        if ($post_id > 0 && (get_post_type($post_id) !== 'lean_player' || !current_user_can('edit_post', $post_id))) {
            $post_id = 0;
        }

        $edit_post_title = ($post_id > 0) ? get_the_title($post_id) : '';
        if ($edit_post_title === '') {
            $edit_post_title = __('Untitled Player', 'vapfem');
        }

        // Same field names class-live-preview-ajax.php's build_config_from_post()
        // reads from $_POST. Forwarding the real saved values under those same
        // keys lets the page replay the exact requestPreview() AJAX call the
        // picker already uses live, instead of a second server-side renderer -
        // render_player_shortcode() can't run in wp-admin at all (it
        // early-returns there by design), which is the whole reason
        // Live_Preview_Ajax exists as a separate path in the first place.
        // Independent of source: a poster can be set (and previewed/saved)
        // before a video/audio source ever is, so this reads separately from
        // the $edit_saved_source block below rather than living inside it.
        $edit_poster_id = ($post_id > 0) ? (int) get_post_meta($post_id, '_poster', true) : 0;

        $edit_saved_source = [];
        if ($post_id > 0) {
            $player_type = get_post_meta($post_id, '_player_type', true);
            if (in_array($player_type, ['video', 'audio'], true)) {
                $edit_saved_source['_player_type'] = $player_type;
                $source_keys = [
                    '_video_type', '_youtube_url', '_vimeo_url',
                    '_html5_source_type', '_html5_video_url', '_video_source',
                    '_audio_source_type', '_audio_source', '_html5_audio_url',
                ];
                foreach ($source_keys as $meta_key) {
                    $value = get_post_meta($post_id, $meta_key, true);
                    if ($value !== '') {
                        $edit_saved_source[$meta_key] = $value;
                    }
                }
            }
        }

        // Reuses the exact same AJAX action + nonce the classic post.php
        // metabox's live preview panel already uses (class-live-preview-ajax.php,
        // registered by Live_Preview_Ajax) - same renderer, same markup, no
        // second preview pipeline. That endpoint doesn't touch post meta or
        // the DB at all for the player path, so calling it here writes nothing.
        // saveNonce is a separate, dedicated nonce for the one endpoint that
        // does write (leanpl_admin_new_save_player).
        wp_localize_script('leanpl-admin-new', 'leanplAdminNewPreview', [
            'ajaxUrl'     => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('leanpl_live_preview'),
            'saveNonce'   => wp_create_nonce('leanpl_admin_new_save_player'),
            // For leanpl_admin_new_detect_media - the Add Media button's
            // fallback probe when a pasted URL is otherwise unrecognised
            // (chiefly a live stream address).
            'detectNonce' => wp_create_nonce('leanpl_detect_media'),
            'postId'      => $post_id,
            'savedSource' => $edit_saved_source,
            'savedPoster' => $edit_poster_id,
        ]);

        include LEANPL_DIR . '/includes/admin-new/html-player-edit-new-page.php';
    }
}

// Initialize the settings page
Settings_Page::instance();
