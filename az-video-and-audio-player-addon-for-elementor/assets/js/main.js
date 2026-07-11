(function($){
"use strict";

    var getBooleanSetting = leanplUtils.getBooleanSetting;
    var getNumberSetting  = leanplUtils.getNumberSetting;
    var getIntegerSetting = leanplUtils.getIntegerSetting;
    var buildCommonConfig = leanplUtils.buildCommonConfig;
    var buildVideoConfig  = leanplUtils.buildVideoConfig;
    var buildAudioConfig  = leanplUtils.buildAudioConfig;
    var playerRegistry    = leanplUtils.playerRegistry;
    var INIT_STAMP        = 'lpl-auto-init';

    window.LeanPL = window.LeanPL || {};
    window.LeanPL.player = {
        initAll: function (root) {
            var scope = root || document;
            var videos = scope.querySelectorAll('.lpl-player.lpl-player--video');
            var audios  = scope.querySelectorAll('.lpl-player.lpl-player--audio');
            for (var i = 0; i < videos.length; i++) {
                if (!videos[i].classList.contains(INIT_STAMP)) {
                    videos[i].classList.add(INIT_STAMP);
                    initVideoPlayer(videos[i]);
                }
            }
            for (var i = 0; i < audios.length; i++) {
                if (!audios[i].classList.contains(INIT_STAMP)) {
                    audios[i].classList.add(INIT_STAMP);
                    initAudioPlayer(audios[i]);
                }
            }
        },
        init: function (el) {
            if (el.classList.contains(INIT_STAMP)) { return; }
            el.classList.add(INIT_STAMP);
            if (el.classList.contains('lpl-player--video')) {
                initVideoPlayer(el);
            } else if (el.classList.contains('lpl-player--audio')) {
                initAudioPlayer(el);
            }
        },
        get: function (el) {
            return el.__leanplPlayer || null;
        },
        destroy: function (el) {
            var player = el.__leanplPlayer;
            if (player) {
                player.destroy();
                el.__leanplPlayer = null;
                el.classList.remove(INIT_STAMP);
            }
        }
    };

    // One guarded init, two triggers (DOM ready + MutationObserver).
    // See leanplUtils.autoInit. The element stamp guarantees
    // each player is initialized at most once regardless of how many triggers
    // fire — shortcode, Elementor frontend/editor, Gutenberg, or AJAX DOM.
    leanplUtils.autoInit('.lpl-player.lpl-player--video', initVideoPlayer);
    leanplUtils.autoInit('.lpl-player.lpl-player--audio', initAudioPlayer);

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
     */
    function logConfigIfDebug(playerType, config) {
        if (config.debug) {
            var typeLabel = playerType.charAt(0).toUpperCase() + playerType.slice(1);
            console.log('[LEANPL ' + typeLabel + ' Player Config]', config);
        }
    }

    /**
     * Initialize a single video player element.
     * Called once per element by leanplUtils.autoInit.
     *
     * @param {HTMLElement} element Video player root element.
     */
    function initVideoPlayer(element) {
        var settings = parsePlayerSettings(element);

        if (!settings) {
            return;
        }

        // Capture the wrapper before Plyr mutates the DOM: for YouTube/Vimeo,
        // Plyr replaces `element` itself with its own iframe markup, so
        // `element` can end up detached from the tree. The wrapper never is.
        var wrapper = element.closest('.lpl-player-wrap');

        var commonConfig = buildCommonConfig(settings);
        var videoConfig = buildVideoConfig(settings, commonConfig);

        logConfigIfDebug('video', videoConfig);

        var player = new Plyr(element, videoConfig);
        playerRegistry.register(player);
        element.__leanplPlayer = player;
        if (wrapper) {
            wrapper.__leanplPlayer = player;
        }

        player.on('ready', function () { leanplUtils.emit(element, 'player:ready', { source: 'video', playerType: 'video', player: player, el: element }); });
        player.on('play',  function () { leanplUtils.emit(element, 'player:play',  { source: 'video', playerType: 'video', player: player, el: element }); });
        player.on('pause', function () { leanplUtils.emit(element, 'player:pause', { source: 'video', playerType: 'video', player: player, el: element }); });
        player.on('ended', function () { leanplUtils.emit(element, 'player:ended', { source: 'video', playerType: 'video', player: player, el: element }); });
    }

    /**
     * Initialize a single audio player element.
     * Called once per element by leanplUtils.autoInit.
     *
     * @param {HTMLElement} element Audio player root element.
     */
    function initAudioPlayer(element) {
        var settings = parsePlayerSettings(element);

        if (!settings) {
            return;
        }

        var wrapper = element.closest('.lpl-player-wrap');

        var commonConfig = buildCommonConfig(settings);
        var audioConfig = buildAudioConfig(settings, commonConfig);

        logConfigIfDebug('audio', audioConfig);

        var player = new Plyr(element, audioConfig);
        playerRegistry.register(player);
        element.__leanplPlayer = player;
        if (wrapper) {
            wrapper.__leanplPlayer = player;
        }

        player.on('ready', function () { leanplUtils.emit(element, 'player:ready', { source: 'audio', playerType: 'audio', player: player, el: element }); });
        player.on('play',  function () { leanplUtils.emit(element, 'player:play',  { source: 'audio', playerType: 'audio', player: player, el: element }); });
        player.on('pause', function () { leanplUtils.emit(element, 'player:pause', { source: 'audio', playerType: 'audio', player: player, el: element }); });
        player.on('ended', function () { leanplUtils.emit(element, 'player:ended', { source: 'audio', playerType: 'audio', player: player, el: element }); });
    }

})(jQuery);
