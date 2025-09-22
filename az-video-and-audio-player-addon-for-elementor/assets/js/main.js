(function($){
"use strict";

    var VideoPlayerJS = function ($scope, $) {

        var nodeList = document.querySelectorAll('.vapfem-player.vapfem-video');

        for (var i = 0; i < nodeList.length; i++) {
            var item = nodeList[i];

            // Validate element exists and has data-settings
            if (!item || !item.getAttribute('data-settings')) {
                console.warn('Invalid player element or missing data-settings:', item);
                continue;
            }

            try {
                var plyrSettings = JSON.parse(item.getAttribute('data-settings'));
            } catch (e) {
                console.error('Failed to parse player settings:', e);
                continue;
            }

            var controls = plyrSettings.controls ? plyrSettings.controls : ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'];
            var settings = plyrSettings.settings ? plyrSettings.settings : ['captions', 'quality', 'speed', 'loop'];
            var seekTime = plyrSettings.seek_time ? parseInt(plyrSettings.seek_time) : 100;
            var volume = parseFloat(plyrSettings.volume) || 1;
            var muted = Boolean(plyrSettings.muted);
            var clickToPlay = Boolean(plyrSettings.clickToPlay);
            var hideControls = Boolean(plyrSettings.hideControls);
            var resetOnEnd = Boolean(plyrSettings.resetOnEnd);
            var keyboard_focused = Boolean(plyrSettings.keyboard_focused);
            var keyboard_global = Boolean(plyrSettings.keyboard_global);
            var tooltips_controls = Boolean(plyrSettings.tooltips_controls);
            var tooltips_seek = Boolean(plyrSettings.tooltips_seek);
            var invertTime = Boolean(plyrSettings.invertTime);
            var fullscreen_enabled = Boolean(plyrSettings.fullscreen_enabled);
            var speed_selected = plyrSettings.speed_selected ? parseFloat(plyrSettings.speed_selected) : 1;
            var quality_default = plyrSettings.quality_default ? parseInt(plyrSettings.quality_default) : 576;
            var ratio = plyrSettings.ratio;
            var debug_mode = Boolean(plyrSettings.debug_mode);

            const player = new Plyr(item, {
                debug: debug_mode,
                controls: controls,
                settings: ['captions', 'quality', 'speed', 'loop'],
                seekTime: seekTime,
                volume: volume,
                muted: muted,
                clickToPlay: clickToPlay,
                hideControls: hideControls,
                resetOnEnd: resetOnEnd,
                keyboard: { focused: keyboard_focused, global: keyboard_global },
                invertTime: invertTime,
                tooltips: { controls: tooltips_controls, seek: tooltips_seek },
                fullscreen: { enabled: fullscreen_enabled, fallback: true, iosNative: false },
                speed: { selected: speed_selected, options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2] },
                quality: { default: quality_default, options: [4320, 2880, 2160, 1440, 1080, 720, 576, 480, 360, 240] },
                ratio: ratio
            });
        }      
    }


    var AudioPlayerJS = function ($scope, $) {

        var nodeList = document.querySelectorAll('.vapfem-player.vapfem-audio');

        for (var i = 0; i < nodeList.length; i++) {
            var item = nodeList[i];

            // Validate element exists and has data-settings
            if (!item || !item.getAttribute('data-settings')) {
                console.warn('Invalid audio player element or missing data-settings:', item);
                continue;
            }

            try {
                var plyrSettings = JSON.parse(item.getAttribute('data-settings'));
            } catch (e) {
                console.error('Failed to parse audio player settings:', e);
                continue;
            }

            var controls = plyrSettings.controls ? plyrSettings.controls : ['play', 'progress', 'mute', 'volume', 'settings'];
            var muted = Boolean(plyrSettings.muted);
            var seekTime = plyrSettings.seek_time ? parseInt(plyrSettings.seek_time) : 100;
            var tooltips_controls = Boolean(plyrSettings.tooltips_controls);
            var tooltips_seek = Boolean(plyrSettings.tooltips_seek);
            var invertTime = Boolean(plyrSettings.invertTime);
            var speed_selected = plyrSettings.speed_selected ? parseFloat(plyrSettings.speed_selected) : 1;
            var debug_mode = Boolean(plyrSettings.debug_mode);

            const player = new Plyr(item, {
                debug: debug_mode,
                controls: controls,
                muted: muted,
                seekTime: seekTime,
                invertTime: invertTime,
                tooltips: { controls: tooltips_controls, seek: tooltips_seek },
                speed: { selected: speed_selected, options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2] },
            });
        }
    }

    // Universal initialization for shortcodes and non-Elementor contexts
    $(document).ready(function() {
        VideoPlayerJS();
        AudioPlayerJS();
    });

    // Run this code under Elementor context (dual compatibility)
    if (typeof elementorFrontend !== 'undefined') {
        $(window).on('elementor/frontend/init', function () {
            elementorFrontend.hooks.addAction( 'frontend/element_ready/vapfem_video_player.default', VideoPlayerJS);
            elementorFrontend.hooks.addAction( 'frontend/element_ready/vapfem_audio_player.default', AudioPlayerJS);
        });
    }

})(jQuery);