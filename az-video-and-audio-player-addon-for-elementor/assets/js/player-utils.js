window.leanplUtils = (function () {
    "use strict";

    function getBooleanSetting(settings, key, defaultValue) {
        return settings[key] !== undefined ? Boolean(settings[key]) : defaultValue;
    }

    function getNumberSetting(settings, key, defaultValue) {
        return settings[key] !== undefined ? parseFloat(settings[key]) : defaultValue;
    }

    function getIntegerSetting(settings, key, defaultValue) {
        return settings[key] !== undefined ? parseInt(settings[key], 10) : defaultValue;
    }

    function isDebugModeEnabled(settings) {
        var playerDebugMode = getBooleanSetting(settings, 'debug_mode', false);
        var envDebugMode = (typeof leanpl_params !== 'undefined' && leanpl_params.debugMode) || false;
        return playerDebugMode || envDebugMode;
    }

    function buildCommonConfig(settings) {
        var debugMode = isDebugModeEnabled(settings);
        var storageEnabled = getBooleanSetting(settings, 'storage_enabled', true);

        // Disable storage in debug mode
        if (debugMode) {
            storageEnabled = false;
        }

        return {
            autoplay:   getBooleanSetting(settings, 'autoplay', false),
            muted:      getBooleanSetting(settings, 'muted', false),
            volume:     getNumberSetting(settings, 'volume', 1),
            seekTime:   getIntegerSetting(settings, 'seek_time', 10),
            invertTime: getBooleanSetting(settings, 'invert_time', true),
            tooltips: {
                controls: getBooleanSetting(settings, 'tooltips_controls', false),
                seek:     getBooleanSetting(settings, 'tooltips_seek', true)
            },
            speed: {
                selected: getNumberSetting(settings, 'speed_selected', 1),
                options:  [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2, 4]
            },
            storage: {
                enabled: storageEnabled,
                key: 'plyr'
            },
            autopause: getBooleanSetting(settings, 'autopause', true),
            debug: debugMode
        };
    }

    function buildVideoConfig(settings, commonConfig) {
        var defaultControls = ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'];

        return Object.assign({}, commonConfig, {
            controls:     settings.controls || defaultControls,
            settings:     ['captions', 'quality', 'speed', 'loop'],
            clickToPlay:  getBooleanSetting(settings, 'click_to_play', true),
            hideControls: getBooleanSetting(settings, 'hide_controls', false),
            resetOnEnd:   getBooleanSetting(settings, 'reset_on_end', false),
            keyboard: {
                focused: getBooleanSetting(settings, 'keyboard_focused', true),
                global:  getBooleanSetting(settings, 'keyboard_global', false)
            },
            fullscreen: {
                enabled:   getBooleanSetting(settings, 'fullscreen_enabled', true),
                fallback:  true,
                iosNative: false
            },
            quality: {
                default: getIntegerSetting(settings, 'quality_default', 576),
                options: [4320, 2880, 2160, 1440, 1080, 720, 576, 480, 360, 240]
            },
            ratio: settings.ratio || ''
        });
    }

    function buildAudioConfig(settings, commonConfig) {
        var defaultControls = ['play', 'progress', 'mute', 'volume', 'settings'];

        return Object.assign({}, commonConfig, {
            controls: settings.controls || defaultControls
        });
    }

    /**
     * Player registry. Every Plyr instance created by this plugin (single
     * players and playlist players) registers itself here. Public API:
     *
     *   window.LeanPL.players.all()       // array of all live Plyr instances
     *   window.LeanPL.players.pauseAll()  // pause every live player
     *
     * Autopause is implemented as one consumer of this registry: when a player
     * with autopause enabled starts playing, all other autopause-enabled
     * players are paused.
     */
    var playerRegistry = (function () {
        var registry = [];

        function register(player) {
            if (!player || typeof player.on !== 'function') {
                return;
            }

            registry.push(player);

            player.on('play', function () {
                if (!player.config.autopause) {
                    return;
                }
                for (var i = 0; i < registry.length; i++) {
                    var other = registry[i];
                    if (other === player) continue;
                    if (other.config && other.config.autopause && other.playing) {
                        other.pause();
                    }
                }
            });

            player.on('destroy', function () {
                var idx = registry.indexOf(player);
                if (idx > -1) registry.splice(idx, 1);
            });
        }

        function all() {
            return registry.slice();
        }

        function pauseAll() {
            for (var i = 0; i < registry.length; i++) {
                if (registry[i].playing) registry[i].pause();
            }
        }

        return {
            register: register,
            all:      all,
            pauseAll: pauseAll
        };
    })();

    // Expose public API on window.LeanPL.
    window.LeanPL = window.LeanPL || {};
    window.LeanPL.players = playerRegistry;

    return {
        getBooleanSetting:  getBooleanSetting,
        getNumberSetting:   getNumberSetting,
        getIntegerSetting:  getIntegerSetting,
        buildCommonConfig:  buildCommonConfig,
        buildVideoConfig:   buildVideoConfig,
        buildAudioConfig:   buildAudioConfig,
        playerRegistry:     playerRegistry
    };

})();
