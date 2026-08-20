/**
 * Custom Preset Builder - modal controller
 *
 * Opened via the "Custom Preset" card/tile in the Player Layout picker -
 * the classic metabox and global Settings image-select grid, or the
 * admin-new redesign's tile grid (single-player and playlist edit screens).
 * Single modal instance per page. data.fieldName (localized by the PHP
 * injector) is what makes this generic across all four screens instead of
 * hardcoding which layout field is on the page.
 */
(function ($) {
    'use strict';

    var MODAL_ID = 'lpl-custom-preset-modal';
    var data = window.leanplCustomPresetData || {
        fieldName: '_player_layout',
        controls: {}, controlDefaults: {}, layoutControls: {}, presets: {}, playerType: 'video',
        appliedId: '', appliedState: 'empty', appliedName: '', isPro: false,
        ajaxUrl: '', saveNonce: '', newPresetLabel: 'New Preset', deleteConfirmLabel: 'Delete this preset?'
    };

    // Cached reference to the classic metabox/Settings Custom Preset card
    // (the value="__custom_preset__" radio's parent .lex-image-select-card -
    // both surfaces render through the same image-select.php template).
    // Populated at DOM ready, BEFORE applyPreset() can change the sentinel
    // radio's value from "__custom_preset__" to the preset id - syncCardLabel()
    // and applyPreset() both look the card up through this cache afterwards,
    // not by value, so the value change doesn't break the lookup. Empty $()
    // on admin-new, which has no sentinel radio at all (its own single
    // hidden input, [data-lpl-layout-input], carries the value instead).
    var $customPresetCard = $();

    var LeanPLCustomPreset = {
        // lex-settings-modal.js runs on every .lex-modal-backdrop on the page
        // (incl. this one) and calls e.stopPropagation() on clicks inside
        // .lex-modal, to stop backdrop-click-to-close from firing on content
        // clicks. That means anything delegated from the backdrop itself never
        // sees clicks that originate inside .lex-modal - so delegation for
        // in-modal controls has to live on a node INSIDE .lex-modal instead.
        _$content: null,
        _previewPlyr: null,
        _previewTemplate: null,
        _renderTimer: null,
        _editingPresetId: '',
        _initialized: false,

        /**
         * Nudges whichever live-preview listener owns this screen. Two
         * surfaces, two paths:
         *
         * - admin-new (player + playlist edit screens): window.LeanPLAdminNewLayout.refreshPreview()
         *   calls requestFormPreview() directly, which re-serializes the form
         *   (including the preset id in [data-lpl-layout-input]) and re-renders.
         *   Used by savePreset/deletePreset, which need a preview nudge without
         *   changing the field's value; applyPreset already drove the preview
         *   via setValue(), so this is a redundant-but-harmless second call
         *   there (debounce coalesces them).
         * - Classic per-player metabox and global Settings (same image-select.php
         *   markup on both): trigger 'input' (not 'change') on the sentinel
         *   radio so live-preview.js's form-wide delegate picks it up
         *   ($form.on('change input ...', 'input, select, textarea', ...) -
         *   'input' alone is enough), WITHOUT firing the document-level
         *   'change' listener that would clear data.appliedId - critical for
         *   savePreset/deletePreset which must preserve the applied state.
         *
         * No-ops when nothing is applied yet on the classic metabox/Settings
         * surfaces - the sentinel radio only carries a preset id once one has
         * been applied (server-side on page load, or via applyPreset() this
         * page load).
         */
        refreshFieldPreview: function () {
            if ($('[data-lpl-edit-form]').length && window.LeanPLAdminNewLayout && typeof window.LeanPLAdminNewLayout.refreshPreview === 'function') {
                window.LeanPLAdminNewLayout.refreshPreview();
                return;
            }
            $customPresetCard.find('.lex-image-select-card__input').trigger('input');
        },

        init: function () {
            if (this._initialized) {
                return;
            }

            var $modal = $('#' + MODAL_ID);
            if (!$modal.length) {
                return;
            }

            this._initialized = true;

            // Some admin screens (global Settings' vertical-tabs layout) nest this
            // modal inside an ancestor that ends up isolating its stacking context
            // even though nothing in that chain reports a non-auto z-index -
            // it then paints behind the WP admin sidebar/menu regardless of how
            // high z-index goes. Re-parenting to <body> sidesteps it entirely,
            // same fix any portal-style modal needs for this class of bug.
            if ($modal.parent().is('body') === false) {
                $modal.appendTo(document.body);
            }

            this._$content = $modal.find('.lpl-cpm__content');

            // Snapshot the pristine <video>/<audio> markup once - whichever the PHP
            // side rendered for this context. Re-rendering from this template
            // (instead of destroy()-ing and reusing the same node) sidesteps
            // Plyr's destroy() cleanup timing - repeatedly destroy+reinit on one node
            // left the preview blank on rapid toggles.
            var $previewMedia = this._$content.find('#lpl-cpm-preview-media');
            if ($previewMedia.length) {
                this._previewTemplate = $previewMedia[0].outerHTML;
            }

            var self = this;

            this._$content.on('click', '[data-action="new-preset"]', function () {
                self.resetBuilder();
                self.showView('builder');
            });

            this._$content.on('click', '[data-action="back-to-library"]', function () {
                self.showView('library');
            });

            this._$content.on('click', '[data-action="save-preset"]', function () {
                self.savePreset();
            });

            this._$content.on('click', '.lpl-cpm__preset-card', function () {
                self.applyPreset($(this).data('preset-id'));
            });

            // Edit/delete sit inside the card that applies on click - stop the
            // click from bubbling to that handler too.
            this._$content.on('click', '[data-action="edit-preset"]', function (e) {
                e.stopPropagation();
                self.editPreset($(this).closest('.lpl-cpm__preset-card').data('preset-id'));
            });

            this._$content.on('click', '[data-action="delete-preset"]', function (e) {
                e.stopPropagation();
                self.deletePreset($(this).closest('.lpl-cpm__preset-card').data('preset-id'));
            });

            this._$content.on('change', '.lpl-cpm__start-from', function () {
                self.seedControlsFromLayout($(this).val());
            });

            // Toggling a control checkbox re-renders the preview immediately.
            this._$content.on('change', '.lpl-cpm__controls-list .lex-checkbox-item', function () {
                self.renderPreview();
            });

            // lex-settings-core.js's SortableJS init dispatches this on drag-drop
            // reorder (bubbles), for any container - filter to just ours.
            this._$content.on('lex:sortableReorder', '.lpl-cpm__controls-list', function () {
                self.renderPreview();
            });

            this.syncCardLabel();
            this.open();
        },

        // Open/close/backdrop-click/Escape are handled by the framework's own
        // openUpgradeModal()/closeUpgradeModal() (lex-settings-modal.js) - it's
        // generic across any .lex-modal-backdrop, this modal included.
        open: function () {
            this.init();

            if (typeof window.openUpgradeModal !== 'function') {
                return;
            }
            this.showView('library');
            window.openUpgradeModal(MODAL_ID);
        },

        close: function () {
            if (typeof window.closeUpgradeModal === 'function') {
                window.closeUpgradeModal();
            }
        },

        showView: function (view) {
            this._$content.find('.lpl-cpm__view').attr('hidden', true);
            this._$content.find('.lpl-cpm__view[data-view="' + view + '"]').removeAttr('hidden');
        },

        /**
         * Opening the builder is a neutral, uncommitted state - the controls
         * list shows plain Plyr stock order/checked-state (the registry's own
         * order and default flags), NOT whatever layout the "Start from"
         * select happens to show as its first option. Only an explicit pick
         * from that dropdown (seedControlsFromLayout, via its 'change'
         * handler) commits to a layout's own controls recipe.
         */
        resetBuilder: function () {
            this._editingPresetId = '';
            this._$content.find('.lpl-cpm__name-input').val('');
            this._$content.find('.lpl-cpm__start-from').prop('selectedIndex', 0);
            this.resetControlsToRegistryDefaults();
        },

        /**
         * Plain Plyr stock order/checked-state: every control in the
         * registry's own order, checked per its own default flag - not tied
         * to any layout. Shared by resetBuilder() (initial open) and
         * seedControlsFromLayout()'s fallback for layouts with no fixed
         * controls array (Classic/Floating).
         */
        resetControlsToRegistryDefaults: function () {
            var defaults = data.controlDefaults;
            var defaultChecked = Object.keys(data.controls).filter(function (value) {
                return !!defaults[value];
            });
            this.applyControlsList(defaultChecked, Object.keys(data.controls));
        },

        /**
         * "Start from" seeds the checklist, then the user is free to edit it -
         * it is not a binding back to the layout. Modern/Simple/Minimal own a
         * fixed controls array (data.layoutControls); Classic/Floating don't,
         * so those fall back to the registry's own default-checked state.
         */
        seedControlsFromLayout: function (layoutKey) {
            var seedOrder = data.layoutControls[layoutKey];

            if (!seedOrder) {
                // No fixed array for this layout - same registry-natural
                // fallback the initial open state uses.
                this.resetControlsToRegistryDefaults();
            } else {
                this.applyLayoutSeed(seedOrder);
            }
        },

        /**
         * Merges a layout's fixed controls array into the full registry:
         * checked controls take the LAYOUT's own relative sequence (so a
         * layout like Modern - which deliberately puts mute/volume before
         * current-time - keeps that real control-bar order), while every
         * unchecked leftover stays pinned at its natural Plyr/registry
         * position instead of being shoved after the checked block. Walking
         * the registry's own order and, at each slot the layout actually
         * uses, popping the next value off the layout's own sequence is what
         * gets both properties in one pass.
         */
        applyLayoutSeed: function (seedOrder) {
            var seedSet = {};
            seedOrder.forEach(function (value) {
                seedSet[value] = true;
            });

            var queue = seedOrder.slice();
            var merged = Object.keys(data.controls).map(function (key) {
                return seedSet[key] ? queue.shift() : key;
            });

            this.applyControlsList(seedOrder, merged);
        },

        /**
         * Checks/unchecks and reorders the controls list to an exact target
         * state - shared by seedControlsFromLayout (partial: registry defaults
         * or a layout's fixed array) and editPreset (exact: a saved preset's
         * own controls, reopened for editing).
         *
         * @param {Array} checkedValues Which controls end up checked.
         * @param {Array} orderValues   Those controls' order - checked ones not
         *                              in this list keep their current spot;
         *                              anything left over from the registry
         *                              falls in after, in registry order.
         */
        applyControlsList: function (checkedValues, orderValues) {
            var $container = this._$content.find('.lpl-cpm__controls-list');

            $container.find('.lex-sortable-checkbox-item').each(function () {
                var value = $(this).data('value');
                $(this).find('.lex-checkbox-item').prop('checked', checkedValues.indexOf(value) !== -1);
            });

            var $items = $container.find('.lex-sortable-checkbox-item');
            var byValue = {};
            $items.each(function () {
                byValue[$(this).data('value')] = $(this);
            });

            var orderedValues = orderValues.slice();
            Object.keys(data.controls).forEach(function (value) {
                if (orderedValues.indexOf(value) === -1) {
                    orderedValues.push(value);
                }
            });

            orderedValues.forEach(function (value) {
                if (byValue[value]) {
                    $container.append(byValue[value]);
                }
            });

            $container.find('.lex-sortable-order-field').val(orderedValues.join(','));

            this.renderPreview();
        },

        /**
         * Checked controls, in current DOM order (drag-drop already keeps DOM
         * order authoritative - see seedControlsFromLayout/updateSortableOrder).
         */
        getCheckedControls: function () {
            var values = [];
            this._$content.find('.lpl-cpm__controls-list .lex-sortable-checkbox-item').each(function () {
                var $item = $(this);
                if ($item.find('.lex-checkbox-item').prop('checked')) {
                    values.push($item.data('value'));
                }
            });
            return values;
        },

        /**
         * Plyr has no live "update controls" API, and repeatedly destroy()-ing
         * and reinstantiating on the SAME node is timing-sensitive (destroy's
         * DOM restoration isn't guaranteed synchronous, so a fast toggle can
         * instantiate the new Plyr before the old one finished unwrapping,
         * leaving a blank player). Rebuilding the video element itself from a
         * pristine template on every render sidesteps that entirely.
         */
        renderPreview: function () {
            var $container = this._$content.find('.lpl-cpm__builder-preview');
            if (!$container.length || !this._previewTemplate || typeof Plyr === 'undefined') {
                return;
            }

            var self = this;

            // Layout-specific control-bar CSS (main.css) keys off this
            // attribute on the .lpl-player-wrap ancestor - keep it mirrored
            // to the "Start from" select so the preview actually looks like
            // that layout, not stock unstyled Plyr (see render_modal_shell()
            // in the PHP injector for the matching initial-render attribute).
            $container.attr('data-lpl-player-layout', this._$content.find('.lpl-cpm__start-from').val());

            // Dim first, swap the node after the fade lands, then fade back in -
            // a straight destroy-and-swap reads as a blink.
            $container.addClass('is-refreshing');

            clearTimeout(this._renderTimer);
            this._renderTimer = setTimeout(function () {
                if (self._previewPlyr) {
                    try {
                        self._previewPlyr.destroy();
                    } catch (e) {
                        // best-effort - the fresh node below doesn't depend on this succeeding
                    }
                    self._previewPlyr = null;
                }

                $container.html(self._previewTemplate);

                var controls = self.getCheckedControls();
                if (window.leanplUtils && typeof window.leanplUtils.getControls === 'function') {
                    controls = window.leanplUtils.getControls(controls, data.playerType);
                }

                self._previewPlyr = new Plyr($container.find('#lpl-cpm-preview-media')[0], { controls: controls });

                // Let the new .plyr wrapper paint one frame before fading back in.
                requestAnimationFrame(function () {
                    $container.removeClass('is-refreshing');
                });
            }, 150);
        },

        savePreset: function () {
            var $nameField = this._$content.find('.lpl-cpm__name-input');
            var name = (String($nameField.val() || '')).trim();

            // Native required-field validation (browser's own "Please fill
            // out this field" bubble) - the field carries the real `required`
            // attribute (see render_modal_shell()), reportValidity() is what
            // triggers that bubble outside an actual form submit.
            if (!name) {
                $nameField[0].reportValidity();
                return;
            }

            if (!data.ajaxUrl || !data.saveNonce) {
                return;
            }

            var self = this;
            var $saveBtn = this._$content.find('[data-action="save-preset"]');
            $saveBtn.prop('disabled', true);

            $.post(data.ajaxUrl, {
                action: 'leanpl_save_custom_preset',
                nonce: data.saveNonce,
                preset_id: this._editingPresetId,
                name: name,
                start_from: this._$content.find('.lpl-cpm__start-from').val(),
                controls: this.getCheckedControls()
            }).done(function (response) {
                if (response && response.success) {
                    data.presets = response.data.presets;
                    self.renderLibrary();
                    self.showView('library');
                    // Editing the currently-applied preset changes what it
                    // resolves to (layout/controls) without changing the
                    // ref input's value - the preview needs an explicit
                    // nudge, same as applyPreset()'s.
                    if (data.appliedId === self._editingPresetId) {
                        self.refreshFieldPreview();
                    }
                }
            }).always(function () {
                $saveBtn.prop('disabled', false);
            });
        },

        /**
         * Reopens the builder pre-filled with an existing preset's exact
         * state - name, "Start from" set to its stored layout, and the
         * controls list set to precisely its controls array (not seeded
         * with layout/registry defaults the way "+ New Preset" is).
         * Saving afterward reuses the same preset_id (upsert), so this edits
         * in place rather than creating a duplicate.
         */
        editPreset: function (presetId) {
            var preset = data.presets[presetId];
            if (!preset) {
                return;
            }

            this._editingPresetId = presetId;
            this._$content.find('.lpl-cpm__name-input').val(preset.name);

            var $startFrom = this._$content.find('.lpl-cpm__start-from');
            if ($startFrom.find('option[value="' + preset.layout + '"]').length) {
                $startFrom.val(preset.layout);
            }

            this.applyControlsList(preset.controls, preset.controls);
            this.showView('builder');
        },

        /**
         * Deleting an applied preset is not blocked or special-cased - Lock
         * A11 is a live reference (class-player-renderer.php resolves it on
         * every render), so a deleted-but-still-referenced preset falls back
         * to classic + default controls automatically, no cleanup needed
         * here beyond degrading the card to "deleted" if the id being
         * removed is the one currently applied.
         */
        deletePreset: function (presetId) {
            var preset = data.presets[presetId];
            if (!preset || !window.confirm(data.deleteConfirmLabel)) {
                return;
            }

            var self = this;
            $.post(data.ajaxUrl, {
                action: 'leanpl_delete_custom_preset',
                nonce: data.saveNonce,
                preset_id: presetId
            }).done(function (response) {
                if (response && response.success) {
                    data.presets = response.data.presets;
                    if (data.appliedId === presetId) {
                        data.appliedState = 'deleted';
                        data.appliedName = '';
                        // Deleted-but-referenced falls back to classic
                        // server-side (resolve_layout_ref()) - the preview
                        // needs to reflect that fallback immediately.
                        self.refreshFieldPreview();
                    }
                    self.renderLibrary();
                    self.syncCardLabel();
                }
            });
        },

        /**
         * Rebuilds the whole library grid from data.presets - the AJAX
         * response is the new source of truth, so this always fully replaces
         * rather than patching in a single card (keeps runtime state and the
         * server-rendered initial state built from the exact same shape).
         */
        renderLibrary: function () {
            var $grid = this._$content.find('.lpl-cpm__library-grid');
            var presets = data.presets || {};

            $grid.empty();

            Object.keys(presets).forEach(function (presetId) {
                var preset = presets[presetId];
                var $card = $('<div class="lpl-cpm__preset-card"></div>').attr('data-preset-id', presetId);
                $('<span class="lpl-cpm__preset-card-name"></span>').text(preset.name).appendTo($card);

                var $actions = $('<span class="lpl-cpm__preset-card-actions"></span>').appendTo($card);
                $('<button type="button" class="lpl-cpm__preset-card-action" data-action="edit-preset"><span class="dashicons dashicons-edit"></span></button>').appendTo($actions);
                $('<button type="button" class="lpl-cpm__preset-card-action" data-action="delete-preset"><span class="dashicons dashicons-trash"></span></button>').appendTo($actions);

                $grid.append($card);
            });

            var $newCard = $('<button type="button" class="lpl-cpm__new-card" data-action="new-preset"></button>');
            $('<span class="dashicons dashicons-plus-alt2"></span>').appendTo($newCard);
            $('<span></span>').text(data.newPresetLabel).appendTo($newCard);
            $grid.append($newCard);

            this.syncLibrarySelection();
        },

        /**
         * Apply is pure client-side DOM, no reload. Lock A11 is a live
         * reference (not stamp-and-step-away): the ONLY thing this writes is
         * the preset id itself, into the real layout field. Player_Renderer
         * resolves the preset's layout + controls live, on every render -
         * persistence rides the page's normal Save/Update click, no separate
         * save path, and editing the preset later changes every
         * player/playlist still pointing at it (no per-item drift).
         *
         * Two surfaces, two write paths:
         * - admin-new (player + playlist edit screens): window.LeanPLAdminNewLayout.setValue()
         *   writes straight into [data-lpl-layout-input] and fires
         *   requestFormPreview(). The 'change' it triggers momentarily clears
         *   data.appliedId via the document-level listener below; we restore
         *   it immediately after.
         * - Classic per-player metabox and global Settings (same
         *   image-select.php markup on both): mutate the sentinel radio
         *   (value="__custom_preset__" card) in place - give it the field
         *   name, set its value to the preset id, check it (the browser
         *   natively unchecks the sibling layout radios), trigger 'change'
         *   so live-preview.js picks it up. Same 'change' fires the
         *   document-level listener and momentarily clears data.appliedId;
         *   restored below.
         */
        applyPreset: function (presetId) {
            var preset = data.presets[presetId];
            if (!preset) {
                return;
            }

            if (!data.isPro) {
                if (typeof window.openUpgradeModal === 'function') {
                    window.openUpgradeModal();
                }
                return;
            }

            var fieldName = data.fieldName;

            if ($('[data-lpl-edit-form]').length && window.LeanPLAdminNewLayout && typeof window.LeanPLAdminNewLayout.setValue === 'function') {
                // admin-new: single input, no shadow ref. setValue triggers
                // 'change' which fires the document-level listener and
                // momentarily clears data.appliedId - restored below.
                window.LeanPLAdminNewLayout.setValue(presetId);
            } else {
                // Classic metabox / Settings grid: mutate the sentinel radio in place.
                var $sentinel = $customPresetCard.find('.lex-image-select-card__input');
                if ($sentinel.length) {
                    $sentinel.attr('name', fieldName).val(presetId).prop('checked', true).trigger('change');
                }
            }

            data.appliedId = presetId;
            data.appliedState = 'applied';
            data.appliedName = preset.name;
            this.syncCardLabel();
            this.refreshFieldPreview();

            this.close();
        },

        /**
         * The "Custom Preset" tile has no name attribute (§3 - it can never
         * be the actual submitted layout value) so it never gets the built-in
         * selected look the real layout options get natively - a radio
         * :checked on the classic metabox/Settings grid, or aria-selected on
         * admin-new's tile grid (player + playlist edit screens; the latter
         * targeted via [data-lpl-custom-preset-tile], see edit-layout-grid.php).
         * This is that replacement, forcing the same "selected" language on
         * via .lpl-cpm__card--applied either way. Title stays fixed ("Custom
         * Preset") - which preset is applied shows inside the popup itself
         * (see syncLibrarySelection), not as a name swapped into the tile.
         *
         * Only one tile should ever read as selected at a time. applyPreset()
         * never touches the real picker, so whatever was chosen before (or
         * nothing) can still sit there underneath, genuinely selected - but
         * showing that pick's own selected look AND this one's at the same
         * time reads as two competing selections. So while a preset is
         * applied: the classic grid gets .lpl-cpm__real-check-hidden (mutes
         * the native :checked highlight on every other card via CSS), and
         * admin-new's real layout tiles get aria-selected forced to false
         * directly (their own selected look is driven by that attribute, not
         * a CSS class here to mute).
         *
         * No-ops the classic-grid branch entirely when that card is locked
         * (free edition, or pro-unlock didn't fire): a locked card should
         * look exactly like a locked card (plain label, lock icon, PRO
         * badge) - not show an applied state alongside a "you can't use
         * this" lock, which is what an unconditional rewrite here produced
         * on a downgraded/free site. Admin-new's locked state instead drops
         * data-lpl-custom-preset-tile entirely (edit-layout-grid.php), so
         * its selector below simply finds nothing there - same outcome.
         */
        syncCardLabel: function () {
            var appliedClass = data.appliedState === 'applied' ? 'lpl-cpm__card--applied' : (data.appliedState === 'deleted' ? 'lpl-cpm__card--deleted' : '');

            // Classic metabox: $customPresetCard is cached at page load (see
            // the module-scope ready handler below), because applyPreset()
            // changes the sentinel radio's value from "__custom_preset__" to
            // the preset id - a value-based lookup would miss it afterwards.
            if ($customPresetCard.length && !$customPresetCard.hasClass('lex-image-select-card--locked')) {
                $customPresetCard
                    .removeClass('lpl-cpm__card--applied lpl-cpm__card--deleted')
                    .addClass(appliedClass);

                $customPresetCard.closest('.lex-image-select-grid')
                    .toggleClass('lpl-cpm__real-check-hidden', data.appliedState === 'applied');
            }

            var $tile = $('[data-lpl-custom-preset-tile]');
            if ($tile.length) {
                $tile
                    .attr('aria-selected', data.appliedState === 'applied' ? 'true' : 'false')
                    .removeClass('lpl-cpm__card--applied lpl-cpm__card--deleted')
                    .addClass(appliedClass);

                if (data.appliedState === 'applied') {
                    $tile.closest('.lpl-grid').find('[data-lpl-layout-option]').attr('aria-selected', 'false');
                }
            }

            this.syncLibrarySelection();
        },

        /**
         * Mirrors the same checkmark language onto whichever library card
         * matches data.appliedId, so reopening the popup shows at a glance
         * which preset is currently live - not just the grid card.
         */
        syncLibrarySelection: function () {
            // No-op before the modal's own content has ever been bound
            // (init() is lazy - first open() only) - nothing in the popup
            // exists yet for this to sync against.
            if (!this._$content) {
                return;
            }
            this._$content.find('.lpl-cpm__preset-card').each(function () {
                $(this).toggleClass('lpl-cpm__preset-card--applied', $(this).data('preset-id') === data.appliedId);
            });
        }
    };

    // Cache the classic metabox's Custom Preset card, then migrate any
    // server-rendered preset-ref input (page loaded with a preset already
    // applied) onto the real layout field. Runs at DOM ready, before any
    // user interaction, so a plain Update (no modal open, no tile click)
    // still persists the preset id: the real input already holds it, the
    // ref is gone.
    $(function () {
        $customPresetCard = $('input[value="__custom_preset__"]').closest('.lex-image-select-card');
        // Stable hook for CSS (custom-preset-builder.css) - the sentinel
        // radio's own [value="__custom_preset__"] stops matching once a
        // preset is applied (applyPreset() rewrites it to the real preset
        // id), so a value-based selector can't be used for styling.
        $customPresetCard.addClass('lpl-cpm__custom-preset-card');

        var $existingRef = $('.lpl-cpm__preset-ref[name="' + data.fieldName + '"]');
        if (!$existingRef.length) {
            return;
        }
        var migratedValue = $existingRef.val() || '';

        if ($('[data-lpl-edit-form]').length) {
            // admin-new: write straight into the single real input. No
            // 'change' trigger - data.appliedId is already correct from the
            // PHP localizer, and the page-load preview is already rendered.
            // Real tiles' aria-selected is reset here so none of them reads
            // as selected; syncCardLabel() below sets the preset tile's
            // applied look.
            $('[data-lpl-layout-input]').val(migratedValue);
            $('[data-lpl-layout-option]').attr('aria-selected', 'false');
        } else {
            // Classic metabox / Settings grid: mutate the sentinel radio in
            // place - name it, set its value to the preset id, check it
            // (browser unchecks the sibling layout radios natively). No
            // 'change' trigger for the same reason as admin-new.
            var $sentinel = $customPresetCard.find('.lex-image-select-card__input');
            if ($sentinel.length) {
                $sentinel.attr('name', data.fieldName).val(migratedValue).prop('checked', true);
            }
        }

        $existingRef.remove();
        LeanPLCustomPreset.syncCardLabel();
    });

    // The real layout picker (radios on the classic metabox/Settings grid,
    // or admin-new's single hidden input - initLayoutPicker() in
    // admin-new.js fires 'change' on it manually since .val() alone
    // doesn't) lives outside the modal - bound at module scope, not inside
    // init(), so it's live even if the Custom Preset modal is never opened
    // this page load.
    //
    // The preset id now lives in the real layout field itself on every
    // surface (no shadow ref survives DOM-ready), so picking a real layout
    // directly overwrites it cleanly - this listener's job is clearing
    // data.appliedId and calling syncCardLabel() so the preset tile drops
    // its applied look. The .lpl-cpm__preset-ref removal is a no-op once
    // the migration above has run; it only matters for the brief window
    // before DOM-ready if something changes the field earlier than that.
    $(document).on('change', 'input[name="' + data.fieldName + '"]', function () {
        data.appliedId = '';
        data.appliedState = 'empty';
        data.appliedName = '';
        $('.lpl-cpm__preset-ref').remove();
        LeanPLCustomPreset.syncCardLabel();
    });

    window.LeanPLCustomPreset = LeanPLCustomPreset;
})(jQuery);
