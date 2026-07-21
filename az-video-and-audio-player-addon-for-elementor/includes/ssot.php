<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [

	// -------- Nav icons --------
	// Full <svg> strings. Consumed directly via leanpl_ssot('icons', 'key').
	'icons' => [
		'source_svg'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M15 10l4.553-2.277A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'behavior_svg'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75"/><path d="M10 8l6 4-6 4V8z" fill="currentColor"/></svg>',
		'appearance_svg' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 3a9 9 0 100 18c1.105 0 2-.895 2-2 0-.524-.21-1.024-.586-1.414A1.99 1.99 0 0113 16c0-1.105.895-2 2-2h2a4 4 0 004-4 7 7 0 00-7-7H12z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/><circle cx="7.5" cy="10.5" r="1" fill="currentColor"/><circle cx="11.5" cy="6.5" r="1" fill="currentColor"/><circle cx="16.5" cy="8.5" r="1" fill="currentColor"/></svg>',
		'playlist_svg'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M4 6h11M4 12h11M4 18h7" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/><path d="M16 14l5 3-5 3v-6z" fill="currentColor"/></svg>',
		'layout_svg'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.75"/><rect x="13" y="3" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.75"/><rect x="3" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.75"/><rect x="13" y="13" width="8" height="8" rx="1.5" stroke="currentColor" stroke-width="1.75"/></svg>',
		'header_svg'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M4 6h16M4 10h10M4 14h16M4 18h10" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>',
		'items_svg'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9 6h11M9 12h11M9 18h11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/><circle cx="5" cy="6" r="1.25" fill="currentColor"/><circle cx="5" cy="12" r="1.25" fill="currentColor"/><circle cx="5" cy="18" r="1.25" fill="currentColor"/></svg>',
		'audio_svg'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M9 18V6l12-2v12" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><circle cx="6" cy="18" r="3" stroke="currentColor" stroke-width="1.75"/><circle cx="18" cy="16" r="3" stroke="currentColor" stroke-width="1.75"/></svg>',
		'controls_svg'   => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M4 6h16M4 12h10M4 18h16" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>',
		'video_svg'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="3" y="6" width="14" height="12" rx="2" stroke="currentColor" stroke-width="1.75"/><path d="M17 10l4-2v8l-4-2" stroke="currentColor" stroke-width="1.75"/></svg>',
		'add_track_svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75"/><path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>',
		'batch_add_svg'  => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect x="4" y="4" width="12" height="12" rx="1.5" stroke="currentColor" stroke-width="1.75"/><path d="M9 9h9a2 2 0 012 2v9" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>',
		'upload_svg'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 16V4M8 8l4-4 4 4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/></svg>',
		'edit_svg'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M4 20l4.5-1 11-11a2.121 2.121 0 00-3-3l-11 11L4 20z" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>',
	],

	// -------- Brand URLs --------
	// Plain strings. utm + scroll args still composed by leanpl_get_upgrade_url().
	'brand' => [
		'upgrade_base_url' => 'https://leanplugins.com/wordpress-plugins/video-and-audio-player/',
		'support_pro_url'  => 'https://leanplugins.com/contact/',
		'support_free_url' => 'https://wordpress.org/support/plugin/az-video-and-audio-player-addon-for-elementor/',
	],

];
