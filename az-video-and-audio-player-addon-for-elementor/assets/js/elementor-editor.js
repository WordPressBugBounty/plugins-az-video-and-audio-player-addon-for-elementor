(function ($) {
    "use strict";

    // Every value below must mirror the matching widget's own registered PHP
    // control default exactly (includes/elementor/widgets/audio-player/trait-content-controls.php
    // and includes/elementor/widgets/video-player/trait-content-controls.php).
    // If any PHP default changes, update it here too, or the "untouched"
    // check below will misfire — either stamping over content that was
    // actually entered, or never stamping a genuinely blank widget.
    var WIDGET_DEFAULTS = {
        vapfem_audio_player: {
            controls: [],
            // audio_upload has no explicit PHP default, Elementor's own MEDIA
            // control base default is an empty url. audio_link explicitly
            // defaults to an empty url. Both are genuinely empty by default
            // (no placeholder/sample content), unlike video's fields below.
            isBlankSource: function (getSetting) {
                var upload = getSetting('audio_upload') || {};
                var link = getSetting('audio_link') || {};
                return !upload.url && !link.url;
            }
        },
        vapfem_video_player: {
            controls: [
                'play-large', 'play', 'progress', 'current-time', 'mute',
                'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'
            ],
            // youtube_video_id / vimeo_video_id ship with real sample IDs as
            // their PHP defaults (visible as placeholder-looking demo values
            // in the panel), not empty strings — so "is it empty" doesn't
            // work here. Compare against those exact sample values instead.
            // video_list (HTML5 sources) is deliberately NOT checked: it's
            // only relevant when video_type is 'html5' (non-default), and
            // Elementor's REPEATER control stamps a generated `_id` onto
            // each row at init time, so its live value never deep-equals the
            // literal PHP default array even when genuinely untouched.
            isBlankSource: function (getSetting) {
                return getSetting('video_type') === 'youtube'
                    && getSetting('youtube_video_id') === 'bTqVqk7FSmY'
                    && getSetting('vimeo_video_id') === '76979871';
            }
        }
    };

    function isGenuinelyUntouched(model) {
        var widgetType = model.get('widgetType');
        var config = WIDGET_DEFAULTS[widgetType];
        if (!config) {
            return false;
        }

        var source = model.getSetting('player_source');
        if (source !== '' && source !== undefined && source !== null && source !== 'manual') {
            return false;
        }

        var layout = model.getSetting('player_layout');
        if (layout !== '' && layout !== undefined && layout !== null) {
            return false;
        }

        var actualControls = model.getSetting('controls') || [];
        if (!_.isEqual(actualControls, config.controls)) {
            return false;
        }

        return config.isBlankSource(function (key) {
            return model.getSetting(key);
        });
    }

    function stampDefaults(model) {
        if (!isGenuinelyUntouched(model)) {
            return;
        }
        // player_source first: it's what actually flips the panel over to
        // "Saved Player" mode and hides the manual source fields. Layout
        // still gets set for the case a user later switches back to Manual.
        model.setSetting('player_source', 'saved');
        model.setSetting('player_layout', 'classic');
        model.setSetting('controls', []);
    }

    $(window).on('elementor:init', function () {
        // Elementor's legacy `elementor.channels.data` "element:after:add"
        // Backbone event (the documented pattern in most third-party
        // tutorials) does not fire in this Elementor version (4.x) for
        // element creation — verified empirically, it never triggers here.
        // The reliable modern equivalent is the command bus: every creation
        // path (drag from panel, duplicate, paste, insert-template) funnels
        // through the single `document/elements/create` command, so
        // listening for that command finishing is what actually works.
        $e.commands.on('run:after', function (component, command, args, result) {
            if (command !== 'document/elements/create') {
                return;
            }
            // `result` is the newly created Container; `.model` is the
            // Backbone element model that `getSetting`/`setSetting` live on.
            if (result && result.model) {
                stampDefaults(result.model);
            }
        });
    });
})(jQuery);
