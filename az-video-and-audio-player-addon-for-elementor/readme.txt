=== Lean Player - Video and Audio Player for WordPress, Elementor, Block Editor and Classic Editor  ===
Contributors: azplugins
Tags: elementor, player, audio player, video player, media player
Requires at least: 4.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 3.0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress Video Player & Audio Player plugin - simple, lightweight and customizable HTML5, YouTube, Vimeo & mp3 media player that supports all devices

== Description ==
"Lean Player - Video and Audio Player for WordPress, Elementor, Block Editor and Classic Editor" - is a simple, lightweight and customizable HTML5, YouTube, Vimeo & mp3 media player that supports all devices. It supports all the major file formats for audio & video. Included audio & video player widget / addon and shortcode support that has lots of customization options, using those options you can change the player settings how you want.

Version 3.0.0 marks a major milestone: our rebranding checkpoint and major update. We've rebranded our plugin under the LeanPlugins brand and made significant improvements to make it more performance-focused and aligned with our brand philosophy.

👉 [Live Demo](https://demo.leanplugins.com/video-and-audio-player/?utm_source=wordpress.org&utm_medium=desc)
👉 [Purchase Pro](https://leanplugins.com/wordpress-plugins/video-and-audio-player/?utm_source=wordpress.org&utm_medium=desc&utm_campaign=upgrade#pricing)

== Features ==

**Player Management:**
* **Global Player Settings** - Set default behavior once for all players
* **Player Manager** - Create and manage players through admin interface
* **Per-Player Configuration** - Override global settings for individual players
* **Admin Columns** - View all players at a glance with Player Type, Source Type, Source, Autoplay status, and copy-ready Shortcode
* **Preview Player** - Preview your player before publishing directly from the players list or from the player edit page
* **Simple Shortcode** - [lean_player id="123"] to embed anywhere

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
* Loop playback
* Default Playback Speed (0.5x to 4x)
* Time Display Format - Countdown or elapsed time [PRO]
* Skip Amount - Set forward/back jump time (1-60 seconds) [PRO]
* Reset to start when finished

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

**Integration:**
* Elementor widgets (video & audio)
* Block Editor (Gutenberg) support
* Classic Editor support
* Shortcode support everywhere

== Video Player Elementor Widget/Addon Options (All FREE) ==

* Video Type (YouTube/Vimeo/HTML5)
* YouTube Video URL
* Vimeo Video URL
* HTML5 Video (Upload or URL)
* Display Preview Thumbnail
* Autoplay
* Start Muted
* Initial Volume
* Loop
* Click To Play/Pause
* Fullscreen Toggle
* Default Playback Speed
* Picture-in-picture (PIP)
* Keyboard Shortcuts
* Reset To Start After End
* 20+ Design & Styling options

== Audio Player Elementor Widget/Addon Options (All FREE) ==

* Audio Source (Upload, URL, or Streaming URL) - Supported formats: MP3, OGG, WAV, M4A, AAC, and audio streams (MP3, AAC streams)
* Autoplay
* Start Muted
* Initial Volume
* Loop
* Default Playback Speed
* 20+ Styling options

== Changelog ==
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
1. Plugin Activation - Easy installation and activation process
2. YouTube Video Player - Playing YouTube videos with custom controls
3. Vimeo Video Player - Vimeo video integration with player controls
4. HTML5 Video Player - Native HTML5 video player with custom poster
5. Video Color Customization - Customizing player colors to match your brand
6. Multiple Players - Multiple video players on a single page
7. Audio Player Examples - Various audio player implementations
8. All Players list - View and manage all created players
9. Create a new Video / Audio Player - Player creation interface
10. Create a new Video / Audio Player - Player creation interface (part 2)
11. Create a new Video / Audio Player - Player creation interface (part 3)
12. Create a new Video / Audio Player - Player creation interface (part 4)
13. Global options to define common options for all players - Global settings configuration
14. Global options to define common options for all players - Global settings (part 2)
15. Global options to define common options for all players - Global settings (part 3)
16. Elementor Integration - Video player widget in Elementor editor
17. Elementor Integration - Audio player widget in Elementor editor
