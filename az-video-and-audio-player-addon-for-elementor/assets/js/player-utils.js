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

    var DEFAULT_SPEED_OPTIONS = [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2, 4];

    /**
     * Build Plyr's speed.options array from the speed_options setting.
     *
     * Values arrive from PHP as strings, so they are coerced and filtered.
     * An empty or missing list falls back to the full default set — the merger
     * treats [] as "inherit", so an empty speed menu should never be reachable.
     *
     * The selected speed is always included: Plyr renders the menu with nothing
     * checked if speed.selected is absent from speed.options.
     */
    function getSpeedOptions(settings, selected) {
        var raw = Array.isArray(settings.speed_options) ? settings.speed_options : [];

        var options = raw
            .map(Number)
            .filter(function (value) {
                return isFinite(value) && value > 0;
            });

        if (!options.length) {
            options = DEFAULT_SPEED_OPTIONS.slice();
        }

        if (isFinite(selected) && selected > 0 && options.indexOf(selected) === -1) {
            options.push(selected);
        }

        return options.sort(function (a, b) {
            return a - b;
        });
    }

    function isDebugModeEnabled(settings) {
        var playerDebugMode = getBooleanSetting(settings, 'debug_mode', false);
        var envDebugMode = (typeof leanpl_params !== 'undefined' && leanpl_params.debugMode) || false;
        return playerDebugMode || envDebugMode;
    }

    function buildCommonConfig(settings) {
        var debugMode = isDebugModeEnabled(settings);
        var storageEnabled = getBooleanSetting(settings, 'storage_enabled', true);
        var speedSelected = getNumberSetting(settings, 'speed_selected', 1);

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
                selected: speedSelected,
                options:  getSpeedOptions(settings, speedSelected)
            },
            storage: {
                enabled: storageEnabled,
                key: 'plyr'
            },
            autopause: getBooleanSetting(settings, 'autopause', true),
            debug: debugMode
        };
    }

    var VIDEO_DEFAULT_CONTROLS = ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'];
    var AUDIO_DEFAULT_CONTROLS = ['play', 'progress', 'mute', 'volume', 'settings'];

    /**
     * Resolve the Plyr controls array for a player type. Shared by the real
     * player-boot config builders below and anything else (e.g. the Custom
     * Preset builder's live preview) that needs the same "explicit list, or
     * this type's default" rule without duplicating the default arrays.
     *
     * @param {Array|undefined} controls   Explicit control-name list, or falsy.
     * @param {string}          playerType 'video' | 'audio'.
     * @return {Array}
     */
    function getControls(controls, playerType) {
        if (controls && controls.length) {
            return controls;
        }
        return playerType === 'audio' ? AUDIO_DEFAULT_CONTROLS.slice() : VIDEO_DEFAULT_CONTROLS.slice();
    }

    function buildVideoConfig(settings, commonConfig) {
        return Object.assign({}, commonConfig, {
            controls:     getControls(settings.controls, 'video'),
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
            ratio: settings.ratio || '',
            poster: settings.poster || ''
        });
    }

    function buildAudioConfig(settings, commonConfig) {
        return Object.assign({}, commonConfig, {
            controls: getControls(settings.controls, 'audio')
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

    /**
     * Resolve which Plyr instance a [lean_timestamp] link should control.
     *
     *   - No target id      → first registered player (page-wide default).
     *   - Target id set      → the saved player's wrapper (#lpl-player-{id}).
     *   - Target id set but
     *     nothing found      → warn and do nothing (no surprising fallback).
     *
     * No playlist targeting: a playlist id can only ever resolve to whichever
     * track happens to be currently loaded, not the specific track/time the
     * link author meant — silently seeking the wrong content is worse than
     * not supporting it.
     *
     * @param {string|null} targetId Raw `data-lpl-player` value, or null.
     * @return {Object|null} Plyr instance, or null.
     */
    function resolveTimestampPlayer(targetId) {
        if (!targetId) {
            var players = playerRegistry.all();
            return players.length ? players[0] : null;
        }

        var wrapper = document.getElementById('lpl-player-' + targetId);
        if (wrapper && wrapper.__leanplPlayer) {
            return wrapper.__leanplPlayer;
        }

        console.warn('[LeanPL] lean_timestamp: no player found for id "' + targetId + '"');
        return null;
    }

    /**
     * Scroll a timestamp target's wrapper into view when it's off screen.
     * `player.elements.container` is Plyr's own wrapper; walk up to the
     * plugin's `.lpl-player-wrap` / `.lpl-playlist-wrap` so the whole card
     * (poster, title, controls) ends up centered, not just the Plyr chrome.
     *
     * @param {Object} player Plyr instance
     */
    function scrollTimestampTargetIntoView(player) {
        var container = player.elements && player.elements.container;
        var target = (container && container.closest)
            ? (container.closest('.lpl-player-wrap, .lpl-playlist-wrap') || container)
            : container;

        if (!target || typeof target.getBoundingClientRect !== 'function') {
            return;
        }

        var rect = target.getBoundingClientRect();
        var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
        var inView = rect.top >= 0 && rect.bottom <= viewportHeight;

        if (!inView) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    /**
     * Play a player, retrying once (muted) if the browser's autoplay policy
     * rejects the initial attempt. Cheap insurance for the case where the
     * timestamp click itself doesn't count as a strong enough user gesture.
     *
     * @param {Object} player Plyr instance
     */
    function playTimestampTarget(player) {
        var playPromise = player.play();

        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(function (err) {
                if (err && err.name === 'NotAllowedError') {
                    player.muted = true;
                    player.play().catch(function () {});
                }
            });
        }
    }

    /**
     * Seek a player to `secs` and play it.
     *
     * @param {Object} player Plyr instance
     * @param {number} secs   Target time in seconds
     */
    function seekTimestampPlayer(player, secs) {
        if (player.duration && secs > player.duration) {
            console.warn('[LeanPL] lean_timestamp: time ' + secs + 's exceeds player duration ' + player.duration + 's');
            return;
        }

        // YouTube mutes itself on a pre-play seek (Plyr's autoplay-policy
        // workaround). The click is a user gesture, so restore sound
        // afterwards, unless the player was deliberately configured muted.
        var wasConfiguredMuted = !!(player.config && player.config.muted);

        function restoreMute() {
            if (!wasConfiguredMuted) {
                player.muted = false;
            }
        }

        if (player.duration) {
            player.currentTime = secs;
            playTimestampTarget(player);
            restoreMute();
        } else {
            playTimestampTarget(player);          // triggers load
            player.once('canplay', function () {  // seek once seekable
                player.currentTime = secs;
                restoreMute();
            });
        }
    }

    /**
     * [lean_timestamp] click handler. Delegated at the document level so it
     * works regardless of when the shortcode's markup enters the DOM.
     */
    document.addEventListener('click', function (e) {
        var el = e.target.closest ? e.target.closest('.lpl-timestamp') : null;
        if (!el) {
            return;
        }

        // Real <a href="#">, not a <button>: always suppress the navigation,
        // even if the seek itself can't proceed below (bad time, no player).
        e.preventDefault();

        var secs = parseInt(el.getAttribute('data-lpl-time'), 10);
        if (isNaN(secs)) {
            return;
        }

        var player = resolveTimestampPlayer(el.getAttribute('data-lpl-player'));
        if (!player) {
            return;
        }

        scrollTimestampTargetIntoView(player);
        seekTimestampPlayer(player, secs);
    });

    /**
     * Universal element auto-initializer.
     *
     * One guarded init contract, many safe triggers. The same pattern slick
     * and owl carousel use to work identically under Gutenberg, raw
     * shortcodes, and AJAX-injected DOM.
     *
     * The init runs at most once per element: the element is stamped with the
     * class `lpl-auto-init` (mirrors slick's `.slick-initialized` guard). Every
     * trigger below funnels through the same guarded scan, so re-entry from
     * a MutationObserver firing or document ready is always harmless.
     *
     * Triggers:
     *   1. DOM ready            → shortcode + any DOM already present at load.
     *   2. MutationObserver on <body> → AJAX, popups, tabs, lazy-load, and any
     *      future page builder, with zero builder-specific code.
     *
     * @param {string}   selector  CSS selector for the root element(s).
     * @param {function} initFn    Called once per element as initFn(element).
     *                             `element` is a raw DOM node.
     */
    var INIT_STAMP = 'lpl-auto-init';

    function autoInit(selector, initFn) {
        // Idempotent per-element scan. Safe to call any number of times.
        function scan(root) {
            var scope = root && root.querySelectorAll ? root : document;
            var nodes = scope.querySelectorAll(selector);

            for (var i = 0; i < nodes.length; i++) {
                var el = nodes[i];
                if (el.classList.contains(INIT_STAMP)) {
                    continue;
                }
                el.classList.add(INIT_STAMP);
                initFn(el);
            }

            // A MutationObserver-added node may itself match the selector
            // (not just its descendants), so check the root too.
            if (root && root.nodeType === 1 && root.matches && root.matches(selector)) {
                if (!root.classList.contains(INIT_STAMP)) {
                    root.classList.add(INIT_STAMP);
                    initFn(root);
                }
            }
        }

        // ── Trigger 1: DOM ready ────────────────────────────────────────────
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () { scan(document); });
        } else {
            scan(document);
        }

        // ── Trigger 2: MutationObserver for late/dynamic DOM ────────────────
        // Covers AJAX content, popups, tabs, lazy-load — anything that injects
        // matching DOM after load with no document.ready or element_ready.
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function (mutations) {
                for (var m = 0; m < mutations.length; m++) {
                    var added = mutations[m].addedNodes;
                    for (var n = 0; n < added.length; n++) {
                        if (added[n].nodeType === 1) {
                            scan(added[n]);
                        }
                    }
                }
            });

            function startObserver() {
                if (document.body) {
                    observer.observe(document.body, { childList: true, subtree: true });
                }
            }

            if (document.body) {
                startObserver();
            } else {
                document.addEventListener('DOMContentLoaded', startObserver);
            }
        }
    }

    function getFailureMessages() {
        var fallback = {
            unavailable: 'This audio could not be loaded. May be the station  is offline.',
            dropped:     'The connection was lost. This can happen with live streams.',
            retry:       'Try again'
        };
        var bundle = (typeof leanpl_params !== 'undefined' && leanpl_params.i18n) || {};
        var out = {};
        for (var key in fallback) {
            if (fallback.hasOwnProperty(key)) {
                out[key] = (bundle[key] !== undefined && bundle[key] !== '') ? bundle[key] : fallback[key];
            }
        }
        return out;
    }

    /**
     * Pick the real cause of a load failure.
     *
     * The browser collapses every failure reason (blocked insecure address,
     * unsupported format, wrong link, offline station, silent hang) into one
     * vague error code, so there is only one meaningful distinction left to
     * make ourselves: did it ever actually play? A mid-stream disconnect is
     * a different, more specific story worth telling apart from every other
     * "never loaded" case.
     *
     * @param {HTMLElement} media      The <audio>/<video> element. Unused; kept
     *                                 for API stability.
     * @param {boolean}     hadSuccess True when playback succeeded before dying.
     * @return {string} One of unavailable|dropped.
     */
    function classifyPlaybackFailure(media, hadSuccess) {
        return hadSuccess ? 'dropped' : 'unavailable';
    }

    /**
     * Watch an HTML5 Plyr instance and explain load failures to the visitor.
     *
     * Record-then-reveal: with preload="metadata" the error usually fires
     * long before any click, so the failure is remembered whenever it
     * happens and only revealed on the first play attempt. A 10 second
     * timer catches the silent hang where the server accepts the connection
     * and then sends nothing at all (no error event ever fires).
     *
     * YouTube/Vimeo are left alone (guard on player.isHTML5): embeds render
     * their own failure state inside their iframe.
     *
     * @param {Object}      player  Plyr instance.
     * @param {HTMLElement} element The original <audio>/<video> element.
     * @return {Object} { reset, destroy } — reset clears state + notice.
     */
    function watchPlaybackFailure(player, element) {
        var noop = { reset: function () {}, destroy: function () {} };

        if (!player || typeof player.on !== 'function') {
            return noop;
        }
        try {
            if (!player.isHTML5) {
                return noop;
            }
        } catch (e) {
            return noop;
        }

        var playAttempted = false;
        var hadSuccess = false;
        var recordedKind = null;
        var timer = null;
        var notice = null;
        var destroyed = false;

        function getWrap() {
            var base = element || player.media || (player.elements && player.elements.container);
            if (base && base.closest) {
                var wrap = base.closest('.lpl-player-wrap');
                if (wrap) {
                    return wrap;
                }
            }
            if (player.elements && player.elements.container && player.elements.container.closest) {
                return player.elements.container.closest('.lpl-player-wrap');
            }
            return null;
        }

        function removeNotice() {
            if (notice && notice.parentNode) {
                notice.parentNode.removeChild(notice);
            }
            notice = null;
        }

        function showNotice(kind) {
            if (destroyed) {
                return;
            }
            var messages = getFailureMessages();
            var text = messages[kind] || messages.unavailable;
            var wrap = getWrap();
            if (!wrap || !wrap.parentNode) {
                return;
            }
            if (!notice) {
                notice = document.createElement('div');
                notice.className = 'lpl-player-notice';
                notice.setAttribute('role', 'status');
                notice.setAttribute('aria-live', 'polite');

                var p = document.createElement('p');
                p.className = 'lpl-player-notice__text';
                notice.appendChild(p);

                var btn = document.createElement('button');
                btn.setAttribute('type', 'button');
                btn.className = 'lpl-player-notice__retry';
                btn.textContent = messages.retry;
                btn.addEventListener('click', handleRetry);
                notice.appendChild(btn);
            }
            var textEl = notice.querySelector('.lpl-player-notice__text');
            if (textEl) {
                textEl.textContent = text;
            }
            var retryBtn = notice.querySelector('.lpl-player-notice__retry');
            if (retryBtn) {
                retryBtn.textContent = messages.retry;
            }
            if (!notice.parentNode) {
                wrap.insertAdjacentElement('afterend', notice);
            }
        }

        function clearTimer() {
            if (timer) {
                clearTimeout(timer);
                timer = null;
            }
        }

        function startTimer() {
            clearTimer();
            timer = setTimeout(function () {
                timer = null;
                if (destroyed || hadSuccess || recordedKind) {
                    return;
                }
                recordedKind = 'unavailable';
                if (playAttempted) {
                    showNotice(recordedKind);
                }
            }, 7000);
        }

        function handleRetry() {
            recordedKind = null;
            removeNotice();
            try {
                // load() re-runs the browser's own resource-selection algorithm
                // against the current <source> children and starts a fresh
                // fetch, independent of Plyr entirely. Plyr's own `source`
                // setter is not usable here: its getter returns media.currentSrc
                // (a plain string) but the setter requires a { sources, type }
                // object, so reassigning the getter's output back into it is
                // rejected as an invalid source and does nothing.
                if (player.media && typeof player.media.load === 'function') {
                    player.media.load();
                }
            } catch (e) {}
            playAttempted = true;
            startTimer();
            try {
                var p = player.play();
                if (p && typeof p.catch === 'function') {
                    p.catch(function () {});
                }
            } catch (e) {}
        }

        player.on('error', function () {
            try {
                if (!player.isHTML5) {
                    return;
                }
            } catch (e) {
                return;
            }
            var media = player.media;
            if (media && media.error && typeof console !== 'undefined' && console.debug) {
                try {
                    var params = (typeof leanpl_params !== 'undefined' && leanpl_params.debugMode);
                    if (params) {
                        console.debug('[LeanPL] playback error code: ' + media.error.code);
                    }
                } catch (e) {}
            }
            recordedKind = classifyPlaybackFailure(media, hadSuccess);
            if (playAttempted) {
                showNotice(recordedKind);
            }
        });

        player.on('play', function () {
            playAttempted = true;
            // Only a kind already recorded from a genuine earlier 'error'
            // (see preload="metadata" note above) is revealed here; a fresh
            // play attempt starts the timer instead of guessing a failure.
            if (recordedKind) {
                showNotice(recordedKind);
                clearTimer();
            } else {
                startTimer();
            }
        });

        player.on('playing', function () {
            hadSuccess = true;
            clearTimer();
            recordedKind = null;
            removeNotice();
        });

        function reset() {
            playAttempted = false;
            hadSuccess = false;
            recordedKind = null;
            clearTimer();
            removeNotice();
        }

        return {
            reset: reset,
            destroy: function () {
                destroyed = true;
                clearTimer();
                removeNotice();
            }
        };
    }

    function emit(el, name, detail) {
        el.dispatchEvent(new CustomEvent('leanpl:' + name, { bubbles: true, detail: detail }));
    }

    // Expose public API on window.LeanPL.
    window.LeanPL = window.LeanPL || {};
    window.LeanPL.players = playerRegistry;

    return {
        getBooleanSetting:  getBooleanSetting,
        getNumberSetting:   getNumberSetting,
        getIntegerSetting:  getIntegerSetting,
        getControls:        getControls,
        buildCommonConfig:  buildCommonConfig,
        buildVideoConfig:   buildVideoConfig,
        buildAudioConfig:   buildAudioConfig,
        playerRegistry:     playerRegistry,
        autoInit:           autoInit,
        emit:               emit,
        watchPlaybackFailure:    watchPlaybackFailure,
        classifyPlaybackFailure: classifyPlaybackFailure
    };

})();
