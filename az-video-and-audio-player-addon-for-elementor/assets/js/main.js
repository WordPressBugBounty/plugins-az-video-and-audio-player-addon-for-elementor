(function($){
"use strict";

    var getBooleanSetting = leanplUtils.getBooleanSetting;
    var getNumberSetting  = leanplUtils.getNumberSetting;
    var getIntegerSetting = leanplUtils.getIntegerSetting;
    var buildCommonConfig = leanplUtils.buildCommonConfig;
    var buildVideoConfig  = leanplUtils.buildVideoConfig;
    var buildAudioConfig  = leanplUtils.buildAudioConfig;
    var autopauseManager  = leanplUtils.autopauseManager;

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
     * Log player configuration if debug mode is enabled
     * 
     * @param {string} playerType Type of player ('video' or 'audio')
     * @param {Object} config Player configuration
     * @param {Object} settings Player settings
     */
    function logConfigIfDebug(playerType, config) {
        if (config.debug) {
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
            
            logConfigIfDebug('video', videoConfig);
            
            var player = new Plyr(element, videoConfig);
            autopauseManager.register(player);
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
            
            logConfigIfDebug('audio', audioConfig);
            
            var player = new Plyr(element, audioConfig);
            autopauseManager.register(player);
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
