=== Lean Player - Video and Audio Player with Playlist for WordPress, Elementor and Gutenberg ===
Contributors: azplugins
Tags: video player, audio player, playlist, elementor, YouTube player
Requires at least: 4.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 3.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Video and audio player with playlist for WordPress. Plays YouTube, Vimeo, HTML5 video, and audio. Works with Elementor, Gutenberg, and Classic Editor.

== Description ==
Lean Player is a video and audio player plugin for WordPress. It plays YouTube videos, Vimeo videos, HTML5 video files, and audio files (MP3, AAC, OGG, WAV, M4A). You can embed players anywhere using a shortcode, the Elementor widget, the Block Editor, or the Classic Editor.

Version 3.1 adds a full playlist feature. You can create a video or audio playlist, choose where the track list sits (left, right, top, or bottom of the player), show thumbnails and duration, and let it auto-advance through items. The playlist works with YouTube, Vimeo, HTML5 video, and audio files, and embeds with [lean_playlist id="123"]. A working playlist is free. A small set of styling and power-user options require Pro.

Lean Player also helps keep media-heavy pages fast. With Media Preload, you choose whether video and audio should load only basic info, wait until the visitor clicks play, or start loading immediately. This is useful for pages with multiple players or playlists.

👉 [Players Demo](https://demo.leanplugins.com/video-and-audio-player/?utm_source=wordpress.org&utm_medium=desc)
👉 [Playlist Demo](https://demo.leanplugins.com/video-and-audio-player/playlist-demo/?utm_source=wordpress.org&utm_medium=desc)
👉 [Purchase Pro](https://leanplugins.com/wordpress-plugins/video-and-audio-player/?utm_source=wordpress.org&utm_medium=desc&utm_campaign=upgrade#pricing)

== Features ==

**Player Management:**
* **Global Player Settings** - Set default behavior once for all players
* **Player Manager** - Create and manage players through admin interface
* **Per-Player Configuration** - Override global settings for individual players
* **Admin Columns** - View all players at a glance with Player Type, Source Type, Source, Autoplay status, and copy-ready Shortcode
* **Preview Player** - Preview your player before publishing directly from the players list or from the player edit page
* **Simple Shortcode** - [lean_player id="123"] to embed anywhere
* **Player Categories** - Group and organize players using categories

**Video Sources:**
* YouTube video support
* Vimeo video support
* HTML5 video (upload or URL/CDN)

**Audio Sources:**
* Supported formats: MP3, OGG, WAV, M4A, AAC
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
* Custom Player Controls - Show/hide and reorder controls (play-large, play, progress, current-time, mute, volume, captions, settings, pip, airplay, fullscreen, download) [PRO]
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
* Elementor widgets (video & audio)
* Block Editor (Gutenberg) support
* Classic Editor support
* Shortcode support everywhere

== Video Player Elementor Widget/Addon Options (All FREE) ==

* Video Type (YouTube/Vimeo/HTML5)
* YouTube Video URL
* Vimeo Video URL
* HTML5 Video File (Upload or URL)
* Preview Thumbnail
* Autoplay
* Start Muted
* Initial Volume
* Loop Playback
* HTML5 Media Preload - Choose Metadata, None, or Auto loading
* Click Video to Play/Pause
* Fullscreen Button
* Starting Playback Speed
* Picture-in-picture (PIP)
* Keyboard Shortcuts
* Reset to Start When Finished
* 20+ Design & Styling options

== Audio Player Elementor Widget/Addon Options (All FREE) ==

* Audio Upload or URL - Supported formats: MP3, OGG, WAV, M4A, AAC, and audio streams (MP3, AAC streams)
* Autoplay
* Start Muted
* Initial Volume
* Loop Playback
* HTML5 Media Preload - Choose Metadata, None, or Auto loading
* Starting Playback Speed
* Keyboard Shortcuts
* 20+ Styling options

== Changelog ==
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
* Supported formats: MP3, OGG, WAV, M4A, AAC
* Audio streaming support for live streams (MP3, AAC streams)

You can upload files through the WordPress media library, use direct URLs/CDN links, or stream live audio from streaming URLs.

= Does this work with Elementor? =

Yes! The plugin provides two Elementor widgets:

* **Video Player** - Add YouTube, Vimeo, or HTML5 videos
* **Audio Player** - Add audio files (MP3, OGG, WAV, M4A, AAC)

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

Yes. When you deactivate this plugin, a feedback modal appears. If you choose to submit feedback, these 5 pieces of information are sent to our server:

* Your deactivation reason (from the options provided)
* Your optional comment (if you write one)
* Plugin version
* WordPress version
* PHP version

This feedback helps us understand real-world issues and prioritize fixes. You can click "Skip & Deactivate" to skip feedback entirely.

Service Used: Supabase (https://supabase.com/)
Privacy Policy: https://supabase.com/privacy
Terms of Service: https://supabase.com/terms

The feedback is only sent when you click "Submit & Deactivate". Nothing is collected during normal plugin usage.

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
