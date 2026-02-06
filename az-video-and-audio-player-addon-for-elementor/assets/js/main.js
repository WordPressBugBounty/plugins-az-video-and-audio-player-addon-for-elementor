(function($){
"use strict";

    // Initialize on document ready
    $(document).ready(function() {
        initializeAllPlayers();
    });

    // Setup Elementor integration
    $(window).on('elementor/frontend/init', function () {
        // initializeDemoButtons();
        elementorFrontend.hooks.addAction('frontend/element_ready/vapfem_video_player.default', initializeVideoPlayers);
        elementorFrontend.hooks.addAction('frontend/element_ready/vapfem_audio_player.default', initializeAudioPlayers);

        // For shortcodes
        elementorFrontend.hooks.addAction('frontend/element_ready/widget', initializeAllPlayers);
    });

    /**
     * Parse player settings from data attribute
     * 
     * @param {HTMLElement} element Player element
     * @returns {Object|null} Parsed settings or null if invalid
     */
    function parsePlayerSettings(element) {
        if (!element || !element.getAttribute('data-settings')) {
            console.warn('Invalid player element or missing data-settings:', element);
            return null;
        }

        try {
            return JSON.parse(element.getAttribute('data-settings'));
        } catch (e) {
            console.error('Failed to parse player settings:', e);
            return null;
        }
    }

    /**
     * Get boolean value from settings with default
     * 
     * @param {Object} settings Settings object
     * @param {string} key Setting key
     * @param {boolean} defaultValue Default value if not set
     * @returns {boolean}
     */
    function getBooleanSetting(settings, key, defaultValue) {
        return settings[key] !== undefined ? Boolean(settings[key]) : defaultValue;
    }

    /**
     * Get number value from settings with default
     * 
     * @param {Object} settings Settings object
     * @param {string} key Setting key
     * @param {number} defaultValue Default value if not set
     * @returns {number}
     */
    function getNumberSetting(settings, key, defaultValue) {
        return settings[key] !== undefined ? parseFloat(settings[key]) : defaultValue;
    }

    /**
     * Get integer value from settings with default
     * 
     * @param {Object} settings Settings object
     * @param {string} key Setting key
     * @param {number} defaultValue Default value if not set
     * @returns {number}
     */
    function getIntegerSetting(settings, key, defaultValue) {
        return settings[key] !== undefined ? parseInt(settings[key], 10) : defaultValue;
    }

    /**
     * Check if debug mode is enabled
     * 
     * @param {Object} settings Player settings
     * @returns {boolean}
     */
    function isDebugModeEnabled(settings) {
        var playerDebugMode = getBooleanSetting(settings, 'debug_mode', false);
        var envDebugMode = (typeof leanpl_params !== 'undefined' && leanpl_params.debugMode) || false;
        return playerDebugMode || envDebugMode;
    }

    /**
     * Build common player configuration
     * 
     * @param {Object} settings Player settings
     * @returns {Object} Common config object
     */
    function buildCommonConfig(settings) {
        var debugMode = isDebugModeEnabled(settings);
        var storageEnabled = getBooleanSetting(settings, 'storage_enabled', true);
        
        // Disable storage in debug mode
        if (debugMode) {
            storageEnabled = false;
        }

        return {
            autoplay: getBooleanSetting(settings, 'autoplay', false),
            storage: { enabled: storageEnabled, key: 'plyr' },
            debug: debugMode,
            volume: getNumberSetting(settings, 'volume', 1),
            muted: getBooleanSetting(settings, 'muted', false),
            seekTime: getIntegerSetting(settings, 'seek_time', 10),
            invertTime: getBooleanSetting(settings, 'invertTime', false),
            tooltips: {
                controls: getBooleanSetting(settings, 'tooltips_controls', false),
                seek: getBooleanSetting(settings, 'tooltips_seek', true)
            },
            speed: {
                selected: getNumberSetting(settings, 'speed_selected', 1),
                options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2, 4]
            }
        };
    }

    /**
     * Build video-specific configuration
     * 
     * @param {Object} settings Player settings
     * @param {Object} commonConfig Common config object
     * @returns {Object} Complete video config
     */
    function buildVideoConfig(settings, commonConfig) {
        var defaultControls = ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'];
        var controls = settings.controls || defaultControls;
        var defaultSettings = ['captions', 'quality', 'speed', 'loop'];
        
        var videoConfig = {
            autoplay: commonConfig.autoplay,
            storage: commonConfig.storage,
            debug: commonConfig.debug,
            controls: controls,
            settings: defaultSettings,
            volume: commonConfig.volume,
            muted: commonConfig.muted,
            seekTime: commonConfig.seekTime,
            clickToPlay: getBooleanSetting(settings, 'clickToPlay', true),
            hideControls: getBooleanSetting(settings, 'hideControls', false),
            resetOnEnd: getBooleanSetting(settings, 'resetOnEnd', false),
            keyboard: {
                focused: getBooleanSetting(settings, 'keyboard_focused', true),
                global: getBooleanSetting(settings, 'keyboard_global', false)
            },
            invertTime: commonConfig.invertTime,
            tooltips: commonConfig.tooltips,
            fullscreen: {
                enabled: getBooleanSetting(settings, 'fullscreen_enabled', true),
                fallback: true,
                iosNative: false
            },
            speed: commonConfig.speed,
            quality: {
                default: getIntegerSetting(settings, 'quality_default', 576),
                options: [4320, 2880, 2160, 1440, 1080, 720, 576, 480, 360, 240]
            },
            ratio: settings.ratio || ''
        };

        return videoConfig;
    }

    /**
     * Build audio-specific configuration
     * 
     * @param {Object} settings Player settings
     * @param {Object} commonConfig Common config object
     * @returns {Object} Complete audio config
     */
    function buildAudioConfig(settings, commonConfig) {
        var defaultControls = ['play', 'progress', 'mute', 'volume', 'settings'];
        var controls = settings.controls || defaultControls;
        
        var audioConfig = {
            autoplay: commonConfig.autoplay,
            storage: commonConfig.storage,
            debug: commonConfig.debug,
            controls: controls,
            volume: commonConfig.volume,
            muted: commonConfig.muted,
            seekTime: commonConfig.seekTime,
            invertTime: commonConfig.invertTime,
            tooltips: commonConfig.tooltips,
            speed: commonConfig.speed
        };

        return audioConfig;
    }

    /**
     * Log player configuration if debug mode is enabled
     * 
     * @param {string} playerType Type of player ('video' or 'audio')
     * @param {Object} config Player configuration
     * @param {Object} settings Player settings
     */
    function logConfigIfDebug(playerType, config, settings) {
        if (isDebugModeEnabled(settings)) {
            var typeLabel = playerType.charAt(0).toUpperCase() + playerType.slice(1);
            console.log('[LEANPL ' + typeLabel + ' Player Config]', config);
        }
    }

    /**
     * Initialize video players
     * 
     * @param {jQuery} $scope Optional scope element (for Elementor)
     * @param {jQuery} $ jQuery instance
     */
    function initializeVideoPlayers($scope, $) {
        var playerElements = document.querySelectorAll('.lpl-player.lpl-player--video');

        for (var i = 0; i < playerElements.length; i++) {
            var element = playerElements[i];
            var settings = parsePlayerSettings(element);

            if (!settings) {
                continue;
            }

            var commonConfig = buildCommonConfig(settings);
            var videoConfig = buildVideoConfig(settings, commonConfig);
            
            logConfigIfDebug('video', videoConfig, settings);
            
            var player = new Plyr(element, videoConfig);
        }
    }

    /**
     * Initialize audio players
     * 
     * @param {jQuery} $scope Optional scope element (for Elementor)
     * @param {jQuery} $ jQuery instance
     */
    function initializeAudioPlayers($scope, $) {
        var playerElements = document.querySelectorAll('.lpl-player.lpl-player--audio');

        for (var i = 0; i < playerElements.length; i++) {
            var element = playerElements[i];
            var settings = parsePlayerSettings(element);

            if (!settings) {
                continue;
            }

            var commonConfig = buildCommonConfig(settings);
            var audioConfig = buildAudioConfig(settings, commonConfig);
            
            logConfigIfDebug('audio', audioConfig, settings);
            
            var player = new Plyr(element, audioConfig);
        }
    }

    /**
     * Initialize all players (for shortcodes and non-Elementor contexts)
     */
    function initializeAllPlayers() {
        initializeVideoPlayers();
        initializeAudioPlayers();
    }

})(jQuery);
