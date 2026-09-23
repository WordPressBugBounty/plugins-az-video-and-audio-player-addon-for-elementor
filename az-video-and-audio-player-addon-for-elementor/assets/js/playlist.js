// Depends on leanplUtils (player-utils.js) for three shared concerns:
//   autoInit   — stamped DOM-ready + MutationObserver boot (same pattern as main.js)
//   build*Config / playerRegistry — shared config builder and autopause registry
//   emit       — shared CustomEvent helper (keeps the leanpl: prefix in one place)
// These are not duplicated here intentionally; player-utils.js is the utility layer
// and this file is a consumer of it.
(function ($) {
    "use strict";

    if (typeof leanpl_params === 'undefined') {
        return;
    }

    var debug = !!(leanpl_params && leanpl_params.debugMode);



    function log() {
        if (debug) { console.log.apply(console, ['[LPL Playlist]'].concat([].slice.call(arguments))); }
    }
    function warn() {
        if (debug) { console.warn.apply(console, ['[LPL Playlist]'].concat([].slice.call(arguments))); }
    }

    // ─── Boot / Initialization ──────────────────────────────────────────────────
    // One guarded init, two triggers (DOM ready + MutationObserver).
    // See leanplUtils.autoInit. Works identically under shortcodes, Elementor,
    // Gutenberg, and AJAX/popup-injected DOM. The element stamp in autoInit
    // guarantees initPlaylist runs at most once per element.

    var INIT_STAMP = 'lpl-auto-init';

    window.LeanPL = window.LeanPL || {};
    window.LeanPL.playlist = {
        initAll: function (root) {
            var scope = root || document;
            var nodes = scope.querySelectorAll('.lpl-playlist');
            for (var i = 0; i < nodes.length; i++) {
                if (!nodes[i].classList.contains(INIT_STAMP)) {
                    nodes[i].classList.add(INIT_STAMP);
                    initPlaylist($(nodes[i]));
                }
            }
        },
        init: function (el) {
            if (el.classList.contains(INIT_STAMP)) { return; }
            el.classList.add(INIT_STAMP);
            initPlaylist($(el));
        },
        get: function (el) {
            return el.__leanplPlaylist || null;
        },
        destroy: function (el) {
            var inst = el.__leanplPlaylist;
            if (inst) {
                inst.player.destroy();
                inst.$playlist.off('click', '.lpl-playlist__item');
                inst.$playlist.off('keydown', '.lpl-playlist__item');
                el.__leanplPlaylist = null;
                el.classList.remove(INIT_STAMP);
            }
        }
    };

    // leanplUtils.autoInit handles the stamping/scanning contract (lpl-auto-init guard,
    // DOM ready, MutationObserver). Duplicating that logic here would diverge over time.
    leanplUtils.autoInit('.lpl-playlist', function (el) {
        initPlaylist($(el));
    });

    function initPlaylist($playlist) {
        var $playerEl = $playlist.find('.lpl-playlist__player');

        if (!$playerEl.length) {
            return;
        }

        var config = $playlist.data('lpl-playlist-config');
        if (!config) {
            config = {};

            log('initPlaylist', 'no config found, using default');
        } else {
            log('initPlaylist', 'config found', config);
        }

        var $items = $playlist.find('.lpl-playlist__item');
        log('initPlaylist', $items.length + ' items');

        var $playerWrap = $playlist.find('.lpl-playlist__player-wrap');
        var $loading    = getOrCreateLoading($playerWrap, $playlist);
        showLoading($loading);

        var rawPlayerConfig = {};
        try {
            rawPlayerConfig = JSON.parse( $playlist.attr('data-lpl-player-config') || '{}' );
        } catch (e) {}

        var isAudio   = config.playlist_type === 'audio';
        var commonCfg = leanplUtils.buildCommonConfig( rawPlayerConfig );
        var plyrConfig = isAudio
            ? leanplUtils.buildAudioConfig( rawPlayerConfig, commonCfg )
            : leanplUtils.buildVideoConfig( rawPlayerConfig, commonCfg );

        var player = new Plyr( $playerEl[0], plyrConfig );
        leanplUtils.playerRegistry.register(player);

        var emitDetail = function (extra) {
            return Object.assign({ source: 'playlist', playerType: isAudio ? 'audio' : 'video', player: player, el: $playlist[0] }, extra || {});
        };
        player.on('ready', function () { leanplUtils.emit($playlist[0], 'playlist:ready', emitDetail()); });
        player.on('play',  function () { leanplUtils.emit($playlist[0], 'playlist:play',  emitDetail()); });
        player.on('pause', function () { leanplUtils.emit($playlist[0], 'playlist:pause', emitDetail()); });
        player.on('ended', function () { leanplUtils.emit($playlist[0], 'playlist:ended', emitDetail()); });

        if (player.ready) {
            hideLoading($loading);
            scrollActiveItemIntoView($playlist, config);
        } else {
            player.once('ready', function () {
                hideLoading($loading);
                scrollActiveItemIntoView($playlist, config);
            });
        }

        // Apply CSS variable theming from config.
        // Cascade: per-playlist accent_color overrides the global brand_color.
        var effectiveAccent = config.accent_color || config.brand_color || '';
        if (effectiveAccent) {
            $playlist[0].style.setProperty('--lpl-playlist-accent', effectiveAccent);
            $playlist[0].style.setProperty('--plyr-color-main', effectiveAccent);
        }
        if (config.bg_color) {
            $playlist[0].style.setProperty('--lpl-playlist-bg', config.bg_color);
            // Derive text color from bg luminance so all color-mix() tokens resolve correctly.
            var textColor = deriveTextColor(config.bg_color);
            $playlist[0].style.setProperty('--lpl-playlist-text', textColor);
        }

        if (config.playlist_height_mode === 'sync' && config.playlist_type === 'video') {
            syncPanelHeightToPlayer($playlist, player);
        }

        $playlist.on('click',   '.lpl-playlist__item', handleItemClick.bind(null, $playlist, player, $loading));
        $playlist.on('keydown', '.lpl-playlist__item', handleItemKeydown.bind(null, $playlist, player, $loading));

        // 'playing' only (not 'play') so thumbnail icon and loading overlay change
        // in the same JS tick → single browser render pass → no visual desync
        player.on('playing', markItemPlaying.bind(null, $playlist));
        player.on('pause ended', unmarkItemPlaying.bind(null, $playlist));

        if (config.autoplay_next) {
            player.on('ended', playNextIfAny.bind(null, $playlist, player, $loading));
        }

        // Internal instance ref. Double-underscore = not public API. Access via LeanPL.playlist.get(el).
        $playlist[0].__leanplPlaylist = { player: player, $playlist: $playlist, config: config };

    }

    function getOrCreateLoading($playerWrap, $playlist) {
        var $loading = $playlist.find('.lpl-playlist__loading');

        if (!$loading.length) {
            $loading = $('<div class="lpl-playlist__loading" aria-hidden="true"><span class="lpl-playlist__loading-spinner"></span></div>');
            $playerWrap.prepend($loading);
        }

        return $loading;
    }

    function handleItemClick($playlist, player, $loading, e) {
        selectItem($(e.currentTarget), $playlist, player, $loading);
    }

    function handleItemKeydown($playlist, player, $loading, e) {
        if (e.key !== 'Enter' && e.key !== ' ') {
            return;
        }

        e.preventDefault();

        var $item = $(e.target).closest('.lpl-playlist__item');

        if (!$item.length) {
            return;
        }

        selectItem($item, $playlist, player, $loading);
    }

    function selectItem($item, $playlist, player, $loading) {
        if ($item.hasClass('lpl-playlist__item--active')) {
            log('select', 'toggle play');
            player.togglePlay();
            return;
        }

        var source = $item.data('lpl-source');

        if (!source) {
            warn('select', 'no source');
            return;
        }

        log('select', 'load new source', source);

        setActiveItem($item, $playlist);
        showLoading($loading);

        applyFocusPreventScroll();

        // Sync the now-playing header if present (audio playlists).
        syncNowPlaying($playlist, source);

        var isAudioTrack = ($playlist.data('lpl-playlist-config') || {}).playlist_type === 'audio';
        leanplUtils.emit($playlist[0], 'playlist:trackchange', {
            source: 'playlist',
            playerType: isAudioTrack ? 'audio' : 'video',
            player: player,
            el: $playlist[0],
            trackSource: source
        });

        player.source = source;

        var trackSrc = source.sources && source.sources[0] && source.sources[0].src;
        if ( trackSrc ) {
            player.download = trackSrc;
        }

        // YouTube/Vimeo embeds need a short delay before play(): Plyr's 'ready' fires before
        // the iframe player is ready to accept play(). Without delay, play() is ignored when
        // switching back from HTML5 (e.g. YT → Vimeo → HTML5 → YT). HTML5 plays immediately.
        var provider = source.sources && source.sources[0] && source.sources[0].provider;
        var isEmbed = provider === 'youtube' || provider === 'vimeo';

        var playFn = function () {
            // Plyr's play() returns a Promise for HTML5 media, but can return
            // undefined/null for embeds or when the media is not ready to play
            // (e.g. YouTube/Vimeo mid-load, autoplay blocked). Guard before .catch
            // so a missing Promise never throws "Cannot read properties of undefined".
            var p = player.play();
            if (p && typeof p.catch === 'function') {
                p.catch(function () {});
            }
        };

        // When switching from an embed (Vimeo/YouTube) to HTML5, Plyr fires 'ready' twice:
        // the 2nd ready (~180ms after the 1st) calls player.stop() internally, killing any
        // play() that was triggered by the 1st ready. Waiting 400ms from the 1st ready clears
        // both ready cycles for HTML5. Embeds need 200ms for the iframe player to accept play().
        player.once('ready', function () {
            setTimeout(playFn, isEmbed ? 200 : 400);
        });

        player.once('playing', function () {
            log('playing');
            hideLoading($loading);
        });

        player.once('error', function () {
            warn('error');
            hideLoading($loading);
            getActiveItem($playlist).removeClass('lpl-playlist__item--loading');
        });
    }

    function markItemPlaying($playlist) {
        getActiveItem($playlist)
            .removeClass('lpl-playlist__item--loading')
            .addClass('lpl-playlist__item--playing');
    }

    function unmarkItemPlaying($playlist) {
        getActiveItem($playlist).removeClass('lpl-playlist__item--playing');
    }

    function playNextIfAny($playlist, player, $loading) {
        var $active = getActiveItem($playlist);
        var $next   = $active.next('.lpl-playlist__item');

        if (!$next.length) {
            return;
        }

        var source = $next.data('lpl-source');
        if (!source) {
            return;
        }

        log('ended', 'autoplay next');
        selectItem($next, $playlist, player, $loading);
    }

    function syncPanelHeightToPlayer($playlist, player) {
        function setMaxHeight() {
            var h = player.elements.container.getBoundingClientRect().height;
            $playlist.css('--lpl-playlist-panel-max-height', h + 'px');
        }

        var ro = new ResizeObserver(setMaxHeight);
        player.on('ready', function () {
            ro.observe(player.elements.container);
            setMaxHeight();
        });
    }

    // ─── Now Playing ────────────────────────────────────────────────────────────

    /**
     * Update the now-playing header when the active track changes.
     * No-op for video playlists (header element is absent).
     *
     * @param {jQuery} $playlist   Root playlist element.
     * @param {object} source      Plyr source object (title, meta, poster in sources[0]).
     */
    function syncNowPlaying($playlist, source) {
        var $nowPlaying = $playlist.find('.lpl-playlist__now-playing');
        if (!$nowPlaying.length) {
            return;
        }

        var title  = source.title  || '';
        var meta   = source.meta   || '';
        var poster = (source.sources && source.sources[0] && source.sources[0].poster) || source.poster || '';

        $nowPlaying.find('.lpl-playlist__now-playing-title').text(title);

        var $metaEl = $nowPlaying.find('.lpl-playlist__now-playing-meta');
        $metaEl.text(meta).toggle(!!meta);

        var $thumb = $nowPlaying.find('.lpl-playlist__now-playing-thumb');
        if (poster) {
            if ($thumb.length) {
                $thumb.find('img').attr('src', poster);
            } else {
                $nowPlaying.prepend('<div class="lpl-playlist__now-playing-thumb"><img src="' + escapeAttr(poster) + '" alt="" /></div>');
            }
        } else {
            $thumb.remove();
        }
    }

    // ─── Color Theming ──────────────────────────────────────────────────────────

    /**
     * Derive accessible text color (#fff or #111) from a background hex color
     * using sRGB relative luminance (WCAG formula).
     *
     * @param  {string} bgHex  Background hex color (e.g. '#111111').
     * @return {string}        '#ffffff' for dark backgrounds, '#111111' for light.
     */
    function deriveTextColor(bgHex) {
        var rgb = hexToRgb(bgHex);
        if (!rgb) {
            return '#ffffff';
        }
        var lum = 0.2126 * linearize(rgb.r) + 0.7152 * linearize(rgb.g) + 0.0722 * linearize(rgb.b);
        return lum > 0.35 ? '#111111' : '#ffffff';
    }

    /**
     * Convert a hex color string to {r, g, b} (0-1 range).
     *
     * @param  {string}      hex  Hex color (3 or 6 digits, with or without #).
     * @return {object|null}      {r, g, b} or null on parse error.
     */
    function hexToRgb(hex) {
        var clean = hex.replace(/^#/, '');
        if (clean.length === 3) {
            clean = clean[0] + clean[0] + clean[1] + clean[1] + clean[2] + clean[2];
        }
        var int = parseInt(clean, 16);
        if (isNaN(int)) {
            return null;
        }
        return {
            r: ((int >> 16) & 255) / 255,
            g: ((int >> 8)  & 255) / 255,
            b: ( int        & 255) / 255
        };
    }

    /**
     * Linearize an sRGB channel value for luminance calculation.
     *
     * @param  {number} c  Channel value (0–1).
     * @return {number}    Linear value.
     */
    function linearize(c) {
        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    }

    // ─── DOM Helpers ────────────────────────────────────────────────────────────

    function getActiveItem($playlist) {
        return $playlist.find('.lpl-playlist__item--active');
    }

    function scrollActiveItemIntoView($playlist, config) {
        var startItem = config && config.start_item ? parseInt( config.start_item, 10 ) : 1;
        if ( startItem <= 1 ) { return; }

        var $active = getActiveItem($playlist);
        if (!$active.length) { return; }
        var $panel = $playlist.find('.lpl-playlist__panel-scroll');
        if (!$panel.length) { return; }

        var panelRect  = $panel[0].getBoundingClientRect();
        var activeRect = $active[0].getBoundingClientRect();
        var isAbove    = activeRect.top < panelRect.top;
        var isBelow    = activeRect.bottom > panelRect.bottom;

        if ( isAbove || isBelow ) {
            var offsetInPanel = $active[0].offsetTop - $panel[0].offsetTop;
            $panel.scrollTop( offsetInPanel - ( $panel[0].clientHeight / 2 ) + ( $active[0].clientHeight / 2 ) );
        }
    }

    function setActiveItem($item, $playlist) {
        // Also clear --playing here: switching items means the previously playing
        // item has stopped. markItemPlaying re-adds it on the new item's 'playing'
        // event. Without this, the old item keeps its pause icon, because the
        // 'pause' tick fires too late, by then unmarkItemPlaying reads the NEW
        // active item and never clears --playing off the old one.
        $playlist
            .find('.lpl-playlist__item')
            .removeClass('lpl-playlist__item--active lpl-playlist__item--playing lpl-playlist__item--loading')
            .attr('aria-pressed', 'false');

        $item
            .addClass('lpl-playlist__item--active lpl-playlist__item--loading')
            .attr('aria-pressed', 'true');
    }

    function showLoading($loading) {
        $loading
            .addClass('lpl-playlist__loading--visible')
            .attr('aria-hidden', 'false');
    }

    function hideLoading($loading) {
        $loading
            .removeClass('lpl-playlist__loading--visible')
            .attr('aria-hidden', 'true');
    }

    /**
     * Escape a string for use in an HTML attribute value.
     *
     * @param  {string} str  Raw string.
     * @return {string}      Escaped string.
     */
    function escapeAttr(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // When switching from YouTube/Vimeo to HTML5, Plyr replaces the iframe with a <video> element
    // and focuses it for keyboard controls. The browser scrolls focused elements into view by default,
    // causing the page to jump to the top. We temporarily patch focus() to use preventScroll: true
    // so the page stays put. Restored after 200ms.
    function applyFocusPreventScroll() {
        var origFocus = HTMLElement.prototype.focus;
        HTMLElement.prototype.focus = function (opts) {
            var merged = Object.assign({}, opts, { preventScroll: true });
            origFocus.call(this, merged);
        };
        log('focus preventScroll patch applied');
        setTimeout(function () {
            HTMLElement.prototype.focus = origFocus;
        }, 200);
    }

})(jQuery);
