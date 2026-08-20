=== Lean Player - Video and Audio Player with Playlist for WordPress, Elementor and Gutenberg ===
Contributors: azplugins
Tags: video player, audio player, playlist, elementor, YouTube player
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Video and audio player with playlist for WordPress. Plays YouTube, Vimeo, HTML5 video, and audio. Works with Elementor, Gutenberg, and Classic Editor.

== Description ==
🎬 [See Players in Action](https://demo.leanplugins.com/video-and-audio-player/?utm_source=wordpress.org&utm_medium=desc)
🎵 [See Playlist Demo](https://demo.leanplugins.com/video-and-audio-player/playlist-demo/?utm_source=wordpress.org&utm_medium=desc)
🌐 [Visit Official Website](https://leanplugins.com/wordpress-plugins/video-and-audio-player/?utm_source=wordpress.org&utm_medium=desc)
⚡ [Upgrade to Pro](https://leanplugins.com/wordpress-plugins/video-and-audio-player/?utm_source=wordpress.org&utm_medium=desc&utm_campaign=upgrade#pricing)

Lean Player is a lightweight video and audio player for WordPress with full playlist support. A redesigned admin experience with instant live preview makes setup effortless. Embed players and playlists anywhere in 3 steps:

1. Create a player or playlist from the admin
2. Copy the shortcode
3. Paste it anywhere — or use the Elementor widget, Block Editor, or Classic Editor

Works with **YouTube**, **Vimeo**, **MP4 video** (upload or direct URL/CDN), and **audio files** (MP3, AAC, OGG, WAV, M4A, FLAC — upload or direct URL/CDN). Plays nicely with **Elementor**, **Block Editor**, and **Classic Editor** on any theme, without slowing your site down.

[Players Demo](https://demo.leanplugins.com/video-and-audio-player/?utm_source=wordpress.org&utm_medium=desc) | [Playlist Demo](https://demo.leanplugins.com/video-and-audio-player/playlist-demo/?utm_source=wordpress.org&utm_medium=desc) | [Pro](https://leanplugins.com/wordpress-plugins/video-and-audio-player/?utm_source=wordpress.org&utm_medium=desc&utm_campaign=upgrade#pricing) | [Support](https://wordpress.org/support/plugin/az-video-and-audio-player-addon-for-elementor/)

[youtube https://www.youtube.com/watch?v=NsJ56JBPuVU]

Lean Player also helps keep media-heavy pages fast. With Media Preload, you choose whether video and audio should load only basic info, wait until the visitor clicks play, or start loading immediately. This is useful for pages with multiple players or playlists.

Link directly to a moment in a video or podcast with Timestamp Links. Drop `[lean_timestamp time="1:30"]Jump to intro[/lean_timestamp]` anywhere in your post content, and clicking it seeks the player to that time and starts playback, no page reload. Great for podcast show notes, course sections, and video reviews.

== Features ==

**Player Management:**
* **Global Player Settings** - Set default behavior once for all players
* **Player Manager** - Create and manage players through admin interface
* **Per-Player Configuration** - Override global settings for individual players
* **Admin Columns** - View all players at a glance with Player Type, Source Type, Source, Autoplay status, and copy-ready Shortcode
* **Preview Player** - Preview your player before publishing directly from the players list or from the player edit page
* **Live Preview** - See your changes reflected instantly on the edit screen as you configure a player or playlist, no save needed
* **Simple Shortcode** - [lean_player id="123"] to embed anywhere
* **Timestamp Links** - [lean_timestamp time="1:30"]Jump to intro[/lean_timestamp] to jump a player to a specific moment from your post content
* **Player Categories** - Group and organize players using categories

**Video Sources:**
* YouTube video support
* Vimeo video support
* HTML5 video (upload or URL/CDN)

**Audio Sources:**
* Supported formats: MP3, OGG, WAV, M4A, AAC, FLAC
* Audio streaming support (MP3, AAC streams)
* Media library upload or direct URL/streaming URL

**Playback Options:**
* Autoplay
* Start Muted
* Initial Volume control (0-100%)
* Loop Playback
* HTML5 Media Preload - Keep pages lighter by controlling when video and audio files start loading
* Starting Playback Speed (0.5x to 4x)
* Pause Other Players - Automatically pause other players when one starts playing (works across standalone players and playlists) [PRO]
* Keyboard Shortcuts - Control playback when the player is focused, with optional global shortcuts for single-player pages
* Time Display Format - Countdown or elapsed time [PRO]
* Skip Forward/Back Amount - Set forward/back jump time (1-60 seconds) [PRO]
* Reset to Start When Finished

**Player Controls:**
* Fullscreen button
* Click to play/pause
* Picture-in-picture (PIP) mode
* Keyboard shortcuts
* Player Layout - Choose from 5 built-in layouts (Classic, Modern, Simple, Floating, Minimal), each with its own styling and control set
* Custom Preset Builder - Build, name, and save your own control-bar preset (free): show/hide and reorder controls (play-large, play, progress, current-time, mute, volume, captions, settings, pip, airplay, fullscreen, download); applying a preset to a player [PRO]
* Auto-Hide Controls - Hide controls during playback [PRO]

**Design & Styling:**
* Display your own preview thumbnail
* Primary Color customization - Match player with your brand colors [PRO]
* Compatible on all mobile & desktop devices
* Very lightweight - no major impact on website speed
* Works with all themes

**Playlist:**
* Works with YouTube, Vimeo, HTML5 video, and audio files
* Shortcode: [lean_playlist id="123"]
* Search players and add them to a playlist from the admin
* Filter players by category and add all from a selected category at once
* Panel position: left, right, top, or bottom of the player
* Panel width, max width, and height (synced or fixed)
* List layout with thumbnails (16:9 or 1:1 shape), duration badge, duration next to title, item numbers, and subtitle/artist text
* Play icon: active item only or always visible
* Playlist header with title and item count
* Light and dark skin
* Solid panel background
* Auto-advance to the next track when one finishes
* Audio now-playing compact view
* Grid layout [PRO]
* Accent color customization [PRO]
* Custom gradient panel background [PRO]
* Play icon hidden variant [PRO]
* Audio now-playing large card [PRO]
* Start from a specific item number [PRO]
* Automatic Thumbnail - auto-fetch thumbnails from YouTube and Vimeo when no custom image is set [PRO]

**Integration:**
* Elementor widgets (video, audio & playlist)
* Block Editor (Gutenberg) support
* Classic Editor support
* Shortcode support everywhere

== Video Player Elementor Widget ==

All player options are available directly in the Elementor panel - no shortcode needed. Widget-specific additions:

* Saved Player mode - pick an existing saved player by name; its settings apply automatically
* Player Accent Color - per-widget brand color override
* Controls Bar Color - per-widget icon and text color
* Per Element Style (Pro) - granular colors for play button, progress bar, volume slider, settings icon, tooltips, settings menu, and timer
* Layout (Pro) - icon size, button spacing, corner radius, progress track height

== Audio Player Elementor Widget ==

All player options are available directly in the Elementor panel - no shortcode needed. Widget-specific additions:

* Saved Player mode - pick an existing saved player by name; its settings apply automatically
* Poster Card Style - background, text, border color, padding, radius, and thumbnail radius
* Player Accent Color - per-widget brand color override
* Controls Bar Color - per-widget icon and text color
* Per Element Style (Pro) - granular colors for play button, progress bar, volume slider, download button, tooltips, settings menu, and timer
* Layout (Pro) - icon size, button spacing, corner radius, progress track height

== Playlist Elementor Widget ==

Embed any saved playlist on an Elementor page without a shortcode. All playlist settings (panel position, layout, colors, auto-advance) are configured in the Playlist Manager, not the widget.

* Select Playlist - pick from your saved playlists by name

== Changelog ==
= Version: 3.3.0 =
* Fixed: Auto-Start accordion in the player metabox missing its collapse arrow.
* Admin Redesign: New "Media Players" & "Playlists" list screens and Edit Player/Edit Playlist screens
* Added: Inline live preview panel added to both Player and Playlist edit screens, reflecting field changes in real time
* Added: Layout picker + full Custom Preset builder (pro) (create, edit, delete presets)
* Updated: Lex Settings framework

= Version: 3.2.2 =
* Fixed: Player controls ran together on themes that reset margins and padding.
* Fixed: Playlist titles truncated and rows too tall on themes with a global clearfix.
* Fixed: Signed CDN links with a `?` in them (S3, CloudFront) never played.
* Fixed: .opus audio never played.
* Fixed: .ogv, .mkv, .mov, .3gp and .m4v used the wrong file type.
* Improved: Plugin styles now load after the theme's styles.

= Version: 3.2.1 =
* Added: Freemius for improving the plugin usage
* Added: Example links under the playlist Quick Add box, so you can see what a valid URL looks like.
* Fixed: Playlist preview page was too narrow on default WordPress themes, cramping the player controls.
* Updated: Minimum requirements declared in the plugin header, so incompatible sites are stopped before activation.
* Updated: Language translation template (.pot) regenerated with the latest strings and a few other minor things

= Version: 3.2.0 =
* Added: Playlist Quick Add - add a playlist item by pasting a URL, with instant append to the list (no page reload). Titles for YouTube and Vimeo links are fetched automatically.
* Added: Bulk Add - create many playlist items at once by pasting one URL per line (up to 50).
* Added: Edit a playlist track without leaving the page - the Edit button now opens a side panel instead of a new browser tab.
* Improved: Playlist builder rebuilt around the item list, with the secondary add paths (details, bulk, existing players, upload) moved into a side panel.
* Added: Available Playback Speeds - choose which speeds appear in the player's settings menu, so you can trim options like 4x that most sites never need.
* Fixed: Playback speed could not be changed in audio playlists. The settings menu was taller than the player area and got clipped, making every speed option unclickable.

= Version: 3.1.10 =
* Fixed: Video URLs with percent-encoded characters (e.g. %20 for spaces) were corrupted when rendering, breaking playback even after the URL saved correctly. The read/parse path no longer strips encoding.

= Version: 3.1.9 =
* Fixed: Audio/Video URL field stripped percent-encoded characters (e.g. %20) on save, corrupting URLs with spaces or special characters and breaking playback.
* Fixed: Custom Thumbnail was ignored on YouTube/Vimeo video players — the provider's own default thumbnail showed instead.

= Version: 3.1.8 =
* Added: Timestamp links - [lean_timestamp time="1:30"]Jump to intro[/lean_timestamp] jumps a player to a specific moment from your post content.
* Improved: Elementor's saved-player dropdown now shows the player ID alongside its title.

= Version: 3.1.7 =
* Fixed: Out-of-range volume values no longer overflow the player's valid range.

= Version: 3.1.6 =
* Added: Live playlist preview in the Elementor editor.

= Version: 3.1.5 =
* Fixed: Fatal error on pages with an Elementor Playlist widget when the Playlist feature was disabled.

= Version: 3.1.4 =
* Improved: Regenerated POT language file to include all current translatable strings.

= Version: 3.1.3 =
* Added: Saved-player mode in the Elementor Video Player widget. Select an existing saved player by name instead of re-entering the URL and settings.
* Added: Saved-player mode in the Elementor Audio Player widget. Same as video: pick a saved player and embed it directly from the widget.
* Fixed: Accent color now applies consistently to the progress track and handle across all player types.
* Fixed: Media library now filters to audio or video files when selecting a source file.
* Fixed: Volume range thumb colour now matches the player accent colour.
* Fixed: Switching from a Vimeo track back to an HTML5 track in a playlist left the video stopped instead of playing.
* Fixed: YouTube and Vimeo players sharing a page all showed the same poster image. The inline poster style is now scoped to each player's ID.
* Improved: Playlist track item shows a loading spinner between click and playback, replacing the static play icon during the load gap.
* Fixed: Plain Vimeo video ID (e.g. 76979871) now accepted in the Vimeo URL field.
* Fixed: Plain YouTube video ID (e.g. bTqVqk7FSmY) now accepted in the YouTube URL field.
* Fixed: Visibility icon in the Preview Player metabox now vertically centered.
* Fixed: Astra theme overriding background on focused speed button in player controls.

= Version: 3.1.2 =
* Fixed: Switching playlist tracks could log a JavaScript console error ("Cannot read properties of undefined") on YouTube and Vimeo items. Playback was unaffected; the error is now suppressed.

= Version: 3.1.1 =
* Added: Video Shape (Aspect Ratio) option per player. Set a player to 16:9, 4:3, 1:1, 9:16, or any width:height in the Video-Only settings; leave it empty for automatic. Available in the player editor, shortcode, and Elementor widget.
* Improved: Video players no longer cause the page to jump while they load. Each player reserves its aspect-ratio space up front, removing layout shift for better Core Web Vitals.
* Added: Stable target ids on rendered output. Post-backed players now render inside <div class="lpl-player-wrap" id="lpl-player-{ID}">, and playlists render inside <div class="lpl-playlist-wrap" id="lpl-playlist-{ID}">. Makes per-instance CSS and JS targeting straightforward.
* Updated: Plyr player engine to 3.8.4 (upstream fixes, hides default Vimeo captions).
* Improved: Player Defaults and the per-player settings now share the same tab and section layout (Behavior, Controls, Video-Only) for a consistent editing experience.
* Improved: Each settings section now shows its media scope ("Applies to all players" or "Applies to video players only"), so it is clear what each option affects.
* Fixed: Opening the settings page without a tab in the URL left the panel blank; it now opens the first tab.

= Version: 3.1.0 =
* Added: Automatic Thumbnail (Pro) - playlist items without a custom poster now auto-fetch the thumbnail from YouTube or Vimeo. Enable once in Settings > Playlist. YouTube is zero-cost; Vimeo uses a cached API call.
* Added: Playlist feature - video and audio playlists with panel position (left/right/top/bottom), list/grid layout, thumbnails, duration badge, item numbers, dark skin, and shortcode [lean_playlist id="123"]
* Added: Pause Other Players (Autopause) - when one player starts, all others pause automatically. Configurable site-wide via Global Player Settings.
* Added: HTML5 Media Preload - control when video/audio loads (Metadata, None, or Auto). Useful for pages with multiple players.
* Added: Keyboard Shortcuts - site-wide and per-player control, with Elementor widget and shortcode override support.
* Added: Player type selection modal - choose Video or Audio when creating a new player.
* Added: Accordion sections in player metabox - Playback tab reorganized into collapsible groups.
* Added: Vertical tab navigation in player and playlist metaboxes.
* Improved: Admin UI consistency - unified design tokens across settings pages and metaboxes.

= Version: 3.0.8 =
Added: Download button styling option to the audio player elementor widget

= Version: 3.0.7 =
Fixed: Play Large button were not showing for very newly created video player

= Version: 3.0.6 =
Added: Support for AAC(p) audio streaming URLs

= Version: 3.0.5 =
Fixed: Widget does not load in the elementor planel
Improved: Assets management, Assets were loading on all pages
- Added: Preview Player feature - Preview players directly from the players list or edit page

= Version: 3.0.4 =
- Added: A new filter hook leanpl/metabox/field_config
- Added: Support for M4A and AAC audio files
- Enhancement: On Elementor editor mode shortcode does not render

= Version: 3.0.3 =
- Fixed: Installed time overwrite on activation issue
- Added: FAQs

= Version: 3.0.2 =
- Fixed: Dual plugin activation issue

= Version: 3.0.0 =
* Major: Rebranded plugin under LeanPlugins brand
* Major: Renamed from "AZ Video & Audio Player" to "Lean Player - Video & Audio Player for WordPress"
* Added: Global Player Settings - Set default behavior for all players across your site
* Added: Player Manager - Create and manage players through intuitive admin interface
* Added: Simple shortcode system - [lean_player id="123"]
* Improved: Performance optimizations and code restructuring
* Improved: Aligned with LeanPlugins brand philosophy
* Note: All existing shortcodes remain backward compatible

= Version: 2.1.5 =
* Added: Dynamic tags support for both audio and video player

= Version: 2.1.4 =
* Fixed: Flush of Unstyled Content (FOUC) issue on admin pages

= Version: 2.1.3 =
* Security: Improved output escaping in admin notices

= Version: 2.1.2 =
* Improved: Cross browser compatibility
* Updated: Language translation file

= Version: 2.1.1 =
* Improved: Cache busting mechanism for assets

= Version: 2.1.0 =
* Added: Shortcode support for Audio & Video Player
* Improved: Code optimization and minor improvements
* Updated: Language translation file

= Version: 2.0.3 =
* Updated the plyr library to latest version

= Version: 1.0.0 =
* Initial Release

== Installation ==
This section describes how to install the "Lean Player - Video and Audio Player for WordPress" plugin and get it working.

= 1) Install =

i. Go to the WordPress Dashboard "Add New Plugin" section.

ii. Search For "Lean Player - Video & Audio Player for WordPress".

iii. Install, then Activate it.

= OR: =

i. Unzip (if it is zipped) and Upload `az-video-and-audio-player-addon-for-elementor` folder to the `/wp-content/plugins/` directory

ii. Activate the plugin through the 'Plugins' menu in WordPress

= 2) Configure =
i. After install and activate the plugin you will get a notice to install Elementor Plugin ( If allready have it then do not show any notice. ).

ii. To install the plugin click on the "Button" Install Elementor.

iii. 2 new addons called "Video Player" & "Audio Player" will be appear in Elementor under the "General" category

iv. Drag and Drop the the desired addon to your page, play with the options and relax!

== FAQ ==

= How do I use the shortcode? =

You can use the simple shortcode format: [lean_player id="123"]

Replace "123" with your player's ID. You can find the shortcode for each player in the Players list in your WordPress admin. The shortcode works in:

* Posts and pages
* Widgets
* Classic Editor
* Block Editor (Gutenberg)
* Anywhere shortcodes are supported

= What video and audio formats are supported? =

**Video Sources:**
* YouTube videos (via URL)
* Vimeo videos (via URL)
* HTML5 video files (MP4, WebM, OGG)

**Audio Sources:**
* Supported formats: MP3, OGG, WAV, M4A, AAC, FLAC
* OPUS is supported via a direct URL/CDN link. WordPress does not allow .opus files in the media library, so it cannot be uploaded.
* Audio streaming support for live streams (MP3, AAC streams)

You can upload files through the WordPress media library, use direct URLs/CDN links, or stream live audio from streaming URLs.

= Does this work with Elementor? =

Yes! The plugin provides two Elementor widgets:

* **Video Player** - Add YouTube, Vimeo, or HTML5 videos
* **Audio Player** - Add audio files (MP3, OGG, WAV, M4A, AAC, FLAC)

Both widgets appear in the "General" category in the Elementor editor. You can customize all player settings directly from the Elementor widget panel.

= Is the player compatible with all devices and browsers? =

Yes, the player is designed to work across:

* All modern browsers (Chrome, Firefox, Safari, Edge)
* Mobile devices (iOS and Android)
* Tablets and desktops
* All WordPress themes

The player is lightweight and won't significantly impact your website's loading speed.

= Can I create multiple players with different settings? =

Yes! You can create unlimited players through the Player Manager in your WordPress admin. Each player can have its own:

* Source (video URL or audio file)
* Autoplay settings
* Volume settings
* Control options
* Styling options

You can also set Global Player Settings to define default behavior for all players, then override those defaults for individual players as needed.

= How do I preview a player before publishing? =

You can preview your player in two ways:

* **From the Players List**: Click "Preview Player" in the row actions next to any player in the All Players list
* **From the Edit Screen**: Use the "Preview Player" button in the publish box when editing a player

The preview opens in a new tab, showing only the player without your site's header, footer, or sidebar for a clean preview experience.

= Does this plugin use any third-party services? =

Yes. This plugin uses the [Freemius](https://freemius.com/) SDK for licensing, seamless upgrades, and optional insights that help us improve the plugin.

Freemius does not collect any data by default. On activation you will see an opt-in screen where you can choose to share non-sensitive diagnostic data. Opting in helps us understand the environments the plugin runs in, catch compatibility issues early, and prioritize the features that matter most. If you opt in, Freemius collects basic site and environment details, your plugin activation events, and your admin name and email address.

Skipping the opt-in is completely fine, the plugin works fully either way and no data is sent during normal usage. Purchasing or activating a Pro license connects to Freemius to validate the license and deliver automatic updates.

On opt-in, a copy of that same non-sensitive diagnostic data is also sent to our own database hosted on Supabase. Having it on hand helps us spot compatibility issues sooner, see which setups need attention, and keep making the plugin better for everyone.

Separately, when you deactivate the plugin you are asked why. This is optional: you can skip it and deactivate right away. If you do choose a reason and submit it, that reason, any comment you add, and basic version details about your plugin, WordPress and PHP install are sent to the same Supabase database. No user name or email is ever included.

**Service Used:** Freemius (https://freemius.com/)
**Privacy Policy:** https://freemius.com/privacy/
**Terms of Service:** https://freemius.com/terms/

**Service Used:** Supabase (https://supabase.com/)
**Privacy Policy:** https://supabase.com/privacy
**Terms of Service:** https://supabase.com/terms

== Screenshots ==
1. Activate Lean Player and open its settings
2. All Players list with player type, source, and shortcode
3. All Playlists list
4. Add a new player, choose YouTube, Vimeo, or HTML5
5. Build a playlist from your existing players
6. Customize the playlist look and feel
7. Video player
8. Video playlist with side panel
9. Video playlist with bottom thumbnail grid
10. Audio players with color and background styling
11. Audio playlist with now playing card
12. Audio playlist, right panel with dark skin
13. Player Defaults, Behavior settings
14. Player Defaults, Controls settings
15. Player Defaults, Video-Only settings
16. Playlist settings
