/**
 * Live Preview panel - lean_player / lean_playlist edit screen.
 *
 * The panel injected right below the title (class-player-preview.php) is
 * always visible, starting as a "View Live Preview" call to action - no
 * AJAX call fires just from opening the editor. Two triggers, same action:
 * that in-panel button, and the Publish-box "Live Preview" button.
 * Clicking either engages the panel: the current #post form is serialized
 * and POSTed to the leanpl_live_preview AJAX action (class-live-preview-ajax.php),
 * which runs those UNSAVED values through the real renderer (Player_Renderer,
 * or \LeanPL\Playlist\Renderer for a playlist) - same markup, same CSS as the
 * frontend. Once engaged, further field changes (debounced) auto-refresh the
 * preview. The returned fragment is swapped straight into the panel;
 * leanplUtils.autoInit's MutationObserver (player-utils.js) picks up the
 * freshly-inserted .lpl-player element(s) and instantiates Plyr automatically -
 * no manual init call needed here, for either post type.
 */
(function ($) {
    'use strict';

    var data = window.leanplLivePreviewData || { ajaxUrl: '', nonce: '', postType: 'lean_player' };
    var DEBOUNCE_MS = 400;

    $(function () {
        var $publishBtn = $('#lpl-preview-toggle');
        var $triggerBtn = $('#lpl-preview-trigger');
        var $panel = $('#lpl-live-preview-panel');
        var $inner = $('#lpl-live-preview-panel-inner');
        var $form = $('#post');

        if (!$panel.length || !$inner.length || !$form.length) {
            return;
        }

        var engaged = false;
        var refreshTimer = null;
        var currentRequest = null;
        var FADE_MS = 150;

        // "Shown" gates every AJAX call - not just engaged (user clicked a
        // trigger at least once), but also actually visible right now. A
        // field edit while the panel is scrolled out of view or hidden
        // still fires change/input events; this keeps that from spending a
        // request on a render nobody's looking at.
        function isShown() {
            return engaged && $panel.is(':visible');
        }

        function renderFragment(html) {
            $inner.addClass('lpl-live-preview-panel__inner--has-content').html(html);
        }

        function renderError(message) {
            $inner
                .addClass('lpl-live-preview-panel__inner--has-content')
                .html($('<p>', { class: 'lpl-live-preview-panel__error', text: message }));
        }

        // Dim first, swap content after the dim transition lands, fade back
        // in - a straight content swap reads as a flash/flicker, not a
        // spinner (same crossfade pattern as the Custom Preset builder's own
        // live preview, custom-preset-builder.js renderPreview()).
        function applyFragment(paint) {
            setTimeout(function () {
                paint();
                $inner.removeClass('is-loading');
            }, FADE_MS);
        }

        function refresh() {
            if (!isShown() || !data.ajaxUrl || !data.nonce) {
                return;
            }

            if (currentRequest) {
                currentRequest.abort();
            }

            $inner.addClass('is-loading');

            var postData = $form.serializeArray();
            postData.push({ name: 'action', value: 'leanpl_live_preview' });
            postData.push({ name: 'nonce', value: data.nonce });
            postData.push({ name: 'live_preview_post_type', value: data.postType });

            currentRequest = $.post(data.ajaxUrl, $.param(postData))
                .done(function (response) {
                    if (response && response.success) {
                        applyFragment(function () { renderFragment(response.data.html); });
                    } else {
                        var message = (response && response.data && response.data.message) || 'Preview failed.';
                        applyFragment(function () { renderError(message); });
                    }
                })
                .fail(function (xhr) {
                    if (xhr.statusText === 'abort') {
                        return;
                    }
                    applyFragment(function () { renderError('Preview failed.'); });
                })
                .always(function () {
                    currentRequest = null;
                });
        }

        function scheduleRefresh() {
            if (!isShown()) {
                return;
            }
            clearTimeout(refreshTimer);
            refreshTimer = setTimeout(refresh, DEBOUNCE_MS);
        }

        function engage() {
            engaged = true;
            refresh();
        }

        if ($triggerBtn.length) {
            $triggerBtn.on('click', function (e) {
                e.preventDefault();
                engage();
            });
        }

        if ($publishBtn.length && !$publishBtn.hasClass('disabled')) {
            $publishBtn.on('click', function (e) {
                e.preventDefault();
                engage();
                $panel[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        }

        // Any change/input anywhere in the metabox form refreshes the
        // preview once engaged - broad delegation is deliberate, this panel
        // should react to every field it renders from without having to
        // name each one (see task list for the follow-up polish pass).
        // lex:colorPickerChange - wpColorPicker fields (see admin.js) never
        // dispatch a real 'change'/'input' DOM event on pick/clear, so they
        // fire this custom event instead.
        $form.on('change input lex:colorPickerChange', 'input, select, textarea', scheduleRefresh);
    });
})(jQuery);
