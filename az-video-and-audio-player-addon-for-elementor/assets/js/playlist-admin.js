/**
 * Playlist Builder Admin JS
 *
 * Two modes driven by data-mode on .lpl-pla__builder:
 *   playlist — compact ordered list, shown when saved items exist on load
 *   library  — two-column picker, shown on new posts or via "+ Add More"
 *
 * Single source of truth: #lpl-pla-builder-item-list holds .lpl-pla__builder-item-row elements.
 * In library mode the item list is slotted into the right column via CSS flex.
 * Hidden inputs _playlist_items[N][id] live inside each row; reindexed after mutations.
 *
 * Also handles:
 *   badge  — "Change" button on the type badge in the metabox
 */

(function($) {
    'use strict';

    var i18n = (typeof leanplPlaylistAdmin !== 'undefined') ? leanplPlaylistAdmin : {};

    var builder = {

        init: function() {
            this.$root           = $('.lpl-pla__builder');
            this.$itemList       = $('#lpl-pla-builder-item-list');
            this.$playerList     = $('#lpl-pla-builder-player-list');
            this.$countPill      = $('#lpl-pla-builder-selected-count');
            this.$searchInput    = $('#lpl-pla-builder-player-search');
            this.$categoryFilter = $('#lpl-pla-builder-category-filter');
            this.$addAllBtn      = $('#lpl-pla-builder-add-all');
            this.$editItemsBtn      = $('#lpl-pla-builder-edit-items-btn');
            this.$removeAllBtnCol   = $('#lpl-pla-builder-remove-all-btn');
            this.$searchEmpty       = $('#lpl-pla-builder-search-empty');

            this.bindEvents();
            this.filterByType( i18n.playlist_type || 'video' );
            this.reindex();
            this.updateMeta();
        },

        bindEvents: function() {
            // Mode
            this.$editItemsBtn.on('click', this.enterLibraryMode.bind(this));

            // Library: checkbox add/remove
            this.$playerList.on('change', '.lpl-pla__builder-player-checkbox', this.onCheckboxChange.bind(this));

            // Remove button on item rows (works in both modes)
            this.$itemList.on('click', '.lpl-pla__builder-remove-btn', this.onRemoveClick.bind(this));

            // Remove all (right-column btn only)
            this.$removeAllBtnCol.on('click', this.onRemoveAllClick.bind(this));

            // Filters
            this.$categoryFilter.on('change', this.filterPlayers.bind(this));
            this.$searchInput.on('input', this.filterPlayers.bind(this));

            // Add all visible
            this.$addAllBtn.on('click', this.onAddAllClick.bind(this));

            // Drag to reorder
            this.initSortable();
        },

        // ── Mode ─────────────────────────────────────────

        enterLibraryMode: function() {
            this.$root.attr('data-mode', 'library');
        },

        enterPlaylistMode: function() {
            this.$root.attr('data-mode', 'playlist');
        },

        currentMode: function() {
            return this.$root.attr('data-mode');
        },

        // ── Type filter ───────────────────────────────────

        filterByType: function(type) {
            this.$playerList.children('.lpl-pla__builder-player-row').each(function() {
                $(this).toggleClass('lpl-pla__builder-player-row--hidden', $(this).data('type') !== type);
            });
            this.updateSearchEmptyState();
        },

        // ── Checkbox ──────────────────────────────────────

        onCheckboxChange: function(e) {
            var $cb      = $(e.target);
            var playerId = $cb.val();
            var $row     = $cb.closest('.lpl-pla__builder-player-row');

            if ($cb.is(':checked')) {
                this.addItem($row, playerId);
            } else {
                this.removeItemById(playerId);
            }
        },

        // ── Add ───────────────────────────────────────────

        addItem: function($row, playerId) {
            if (this.$itemList.find('[data-player-id="' + playerId + '"]').length) {
                return;
            }

            var title       = $row.data('title');
            var sourceType  = $row.data('source-type') || 'self-hosted';
            var sourceLabel = $row.data('source-label') || '';
            var editUrl     = 'post.php?post=' + playerId + '&action=edit';

            this.$itemList.find('.lpl-pla__builder-items-empty').remove();

            this.$itemList.append(
                '<div class="lpl-pla__builder-item-row" data-player-id="' + this.esc(playerId) + '">' +
                    '<span class="lpl-pla__builder-drag-handle" aria-hidden="true">&#8801;</span>' +
                    '<span class="lpl-pla__builder-item-num"></span>' +
                    '<span class="lpl-pla__builder-item-title">' + this.escHtml(title) + '</span>' +
                    '<span class="lpl-pla__builder-badge lpl-pla__builder-badge--' + this.esc(sourceType) + '">' + this.escHtml(sourceLabel) + '</span>' +
                    '<div class="lpl-pla__builder-item-actions">' +
                        '<a href="' + this.esc(editUrl) + '" target="_blank" class="lpl-pla__builder-edit-link">Edit</a>' +
                        '<button type="button" class="lpl-pla__builder-remove-btn" title="Remove">&#215;</button>' +
                    '</div>' +
                    '<input type="hidden" name="_placeholder" value="' + this.esc(playerId) + '" />' +
                '</div>'
            );

            this.setAddedState(playerId, true);
            this.reindex();
            this.updateMeta();
        },

        // ── Remove ────────────────────────────────────────

        removeItemById: function(playerId) {
            this.$itemList.find('[data-player-id="' + playerId + '"]').remove();
            this.$playerList.find('.lpl-pla__builder-player-checkbox[value="' + playerId + '"]').prop('checked', false);
            this.setAddedState(playerId, false);
            this.reindex();
            this.updateMeta();
            this.toggleItemsEmpty();
        },

        onRemoveClick: function(e) {
            var playerId = $(e.currentTarget).closest('.lpl-pla__builder-item-row').data('player-id');
            this.removeItemById(String(playerId));
        },

        onRemoveAllClick: function() {
            var self = this;
            this.$itemList.find('.lpl-pla__builder-item-row').each(function() {
                var playerId = String($(this).data('player-id'));
                self.$playerList.find('.lpl-pla__builder-player-checkbox[value="' + playerId + '"]').prop('checked', false);
                self.setAddedState(playerId, false);
            });
            this.$itemList.find('.lpl-pla__builder-item-row').remove();
            displayOptions.dirty = true;
            displayOptions.updatePreviewState();
            this.updateMeta();
            this.toggleItemsEmpty();
        },

        // ── Added state ───────────────────────────────────

        setAddedState: function(playerId, added) {
            var $row = this.$playerList.find('.lpl-pla__builder-player-row[data-player-id="' + playerId + '"]');
            $row.toggleClass('lpl-pla__builder-player-row--added', added);
        },

        // ── Filters ───────────────────────────────────────

        filterPlayers: function() {
            var category   = this.$categoryFilter.val();
            var search     = this.$searchInput.val().toLowerCase().trim();
            var activeType = this.$root.find('[name="_playlist_type"]:checked').val() || 'video';

            this.$playerList.find('.lpl-pla__builder-player-row').each(function() {
                var $r    = $(this);
                var cats  = ($r.data('categories') || '').toString();
                var title = ($r.data('title') || '').toString().toLowerCase();
                var type  = $r.data('type');

                var show = type === activeType
                    && (!category || cats.split(',').indexOf(category) !== -1)
                    && (!search   || title.indexOf(search) !== -1);

                $r.toggleClass('lpl-pla__builder-player-row--hidden', !show);
            });

            this.updateSearchEmptyState();
        },

        updateSearchEmptyState: function() {
            var total   = this.$playerList.find('.lpl-pla__builder-player-row').length;
            var visible = this.$playerList.find('.lpl-pla__builder-player-row:not(.lpl-pla__builder-player-row--hidden)').length;
            this.$searchEmpty.toggle(total > 0 && visible === 0);
        },

        onAddAllClick: function() {
            var self = this;
            this.$playerList.find('.lpl-pla__builder-player-row:not(.lpl-pla__builder-player-row--hidden)').each(function() {
                var $row = $(this);
                var $cb  = $row.find('.lpl-pla__builder-player-checkbox');
                if (!$cb.is(':checked')) {
                    $cb.prop('checked', true);
                    self.addItem($row, $cb.val());
                }
            });
        },

        // ── Reindex ───────────────────────────────────────

        reindex: function() {
            this.$itemList.find('.lpl-pla__builder-item-row').each(function(i) {
                $(this).find('input[type="hidden"]').attr('name', '_playlist_items[' + i + '][id]');
                $(this).find('.lpl-pla__builder-item-num').text(i + 1);
            });
            displayOptions.dirty = true;
            displayOptions.updatePreviewState();
        },

        getItemIds: function() {
            var ids = [];
            this.$itemList.find('.lpl-pla__builder-item-row').each(function() {
                ids.push(String($(this).data('player-id')));
            });
            return ids;
        },

        // ── Meta / count ──────────────────────────────────

        updateMeta: function() {
            var count = this.$itemList.find('.lpl-pla__builder-item-row').length;
            this.$countPill.text(count + ' selected');
            this.$removeAllBtnCol.toggle(count > 0);
        },

        // ── Empty state ───────────────────────────────────

        toggleItemsEmpty: function() {
            var hasItems = this.$itemList.find('.lpl-pla__builder-item-row').length > 0;
            if (!hasItems && !this.$itemList.find('.lpl-pla__builder-items-empty').length) {
                this.$itemList.html(
                    '<div class="lpl-pla__builder-items-empty">' +
                        '<p class="lpl-pla__builder-items-empty-title">No players selected yet.</p>' +
                        '<p class="lpl-pla__builder-items-empty-sub">Choose players from the left to build this playlist.</p>' +
                    '</div>'
                );
            }
        },

        // ── Drag ─────────────────────────────────────────

        initSortable: function() {
            var self = this;
            Sortable.create(this.$itemList[0], {
                handle:      '.lpl-pla__builder-item-row',
                filter:      '.lpl-pla__builder-item-actions',
                preventOnFilter: false,
                animation:   150,
                ghostClass:  'lpl-pla__builder-item-row--ghost',
                chosenClass: 'lpl-pla__builder-item-row--chosen',
                onEnd: function() {
                    self.reindex();
                }
            });
        },

        // ── Escape helpers ────────────────────────────────

        escHtml: function(str) {
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(String(str)));
            return d.innerHTML;
        },

        esc: function(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }
    };

    // ─── Display Options ────────────────────────────────────────────────────────

    // ─── Display Options Tabs (vertical) ──────────────────────────────────────

    var vtabs = {
        storageKey: function() {
            return 'lpl_do_tab_' + i18n.post_id;
        },
        activate: function( $btn, $container ) {
            var vtab = $btn.data('vtab');
            $container.find('.lex-vtabs__nav button').removeClass('lex-vtabs__btn--active');
            $btn.addClass('lex-vtabs__btn--active');
            $container.find('.lex-vtab-pane').hide();
            var $pane = $container.find('.lex-vtab-pane[data-vtab="' + vtab + '"]').show();

            // Lazy-init wpColorPicker on color fields that were hidden at page load.
            // wpColorPicker is idempotent — fields already initialized are skipped via the
            // .wp-picker-container wrapper check.
            if ( $.fn.wpColorPicker ) {
                $pane.find('.lex-color-picker').each(function () {
                    var $el = $(this);
                    if ( $el.closest('.wp-picker-container').length ) {
                        return;
                    }
                    $el.wpColorPicker({ defaultColor: this.value || false });
                });
            }
        },
        init: function() {
            var self     = this;
            var savedTab = localStorage.getItem( self.storageKey() );
            var isAudio  = i18n.playlist_type === 'audio';

            $('.lex-vtabs').each(function() {
                var $container = $(this);
                var $nav = $container.find('.lex-vtabs__nav');
                if (!$nav.length) { return; }

                if ( !isAudio ) {
                    $nav.find('button[data-vtab="a-audio"]').hide();
                    $container.find('.lex-vtab-pane[data-vtab="a-audio"]').hide();
                }

                $nav.on('click', 'button', function(e) {
                    self.activate( $(e.currentTarget), $container );
                    localStorage.setItem( self.storageKey(), $(e.currentTarget).data('vtab') );
                });

                var $restore = savedTab
                    ? $nav.find('button[data-vtab="' + savedTab + '"]:visible')
                    : null;
                self.activate( $restore && $restore.length ? $restore : $nav.find('button:visible').first(), $container );
            });
        },
        onTabClick: function(e, $container) {
            this.activate( $(e.currentTarget), $container );
            localStorage.setItem( this.storageKey(), $(e.currentTarget).data('vtab') );
        }
    };

    // ─── Display Options ────────────────────────────────────────────────────────

    var displayOptions = {

        presets: {},
        fields: [
            'skin', 'position', 'show_header', 'header_text', 'show_thumbnails',
            'item_template', 'grid_columns', 'panel_width', 'max_width',
            'playlist_height_mode', 'panel_max_height',
            'thumb_ratio', 'bg_style', 'gradient_css', 'accent_color',
            'show_count', 'count_label',
            'show_numbers', 'play_icon', 'show_duration_badge',
            'show_duration_in_list', 'show_meta',
            'now_playing_style',
            'autoplay_next', 'start_item',
        ],

        dirty: false,

        init: function() {
            var dataEl = document.getElementById('lpl-pla-do-presets');
            if (!dataEl) { return; }
            this.presets = JSON.parse(dataEl.textContent);
            this.dirty = false;
            this.updatePreviewState();

            this.syncPresetDropdown();

            $('#lpl-pla-do-apply').on('click', this.onApply.bind(this));
            $('.lpl-pla__display-options').on('change input', 'input, select:not(#lpl-pla-do-preset)', this.onFieldChange.bind(this));
        },

        getFieldValues: function() {
            return {
                skin:                  $('[name="_playlist_skin"]').val() || '',
                position:              $('[name="_playlist_position"]').val() || '',
                show_header:           $('[name="_playlist_show_header"]').is(':checked') ? '1' : '',
                header_text:           $('[name="_playlist_header_text"]').val() || '',
                show_thumbnails:       $('[name="_playlist_show_thumbnails"]').is(':checked') ? '1' : '',
                item_template:         $('[name="_playlist_item_template"]').val() || '',
                grid_columns:          $('[name="_playlist_grid_columns"]').val() || '',
                panel_width:           $('[name="_playlist_panel_width"]').val() || '',
                max_width:             $('[name="_playlist_max_width"]').val() || '',
                playlist_height_mode:  $('[name="_playlist_playlist_height_mode"]').val() || '',
                panel_max_height:      $('[name="_playlist_panel_max_height"]').val() || '',
                thumb_ratio:           $('[name="_playlist_thumb_ratio"]').val() || '',
                bg_style:              $('[name="_playlist_bg_style"]').val() || '',
                gradient_css:          $('[name="_playlist_gradient_css"]').val() || '',
                accent_color:          $('[name="_playlist_accent_color"]').val() || '',
                show_count:            $('[name="_playlist_show_count"]').is(':checked') ? '1' : '',
                count_label:           $('[name="_playlist_count_label"]').val() || '',
                show_numbers:          $('[name="_playlist_show_numbers"]').is(':checked') ? '1' : '',
                play_icon:             $('[name="_playlist_play_icon"]').val() || '',
                show_duration_badge:   $('[name="_playlist_show_duration_badge"]').is(':checked') ? '1' : '',
                show_duration_in_list: $('[name="_playlist_show_duration_in_list"]').is(':checked') ? '1' : '',
                show_meta:             $('[name="_playlist_show_meta"]').is(':checked') ? '1' : '',
                now_playing_style:     $('[name="_playlist_now_playing_style"]').val() || '',
                autoplay_next:         $('[name="_playlist_autoplay_next"]').is(':checked') ? '1' : '',
                start_item:            $('[name="_playlist_start_item"]').val() || '',
            };
        },

        applyPreset: function(presetId) {
            var preset = this.presets[presetId];
            if (!preset) { return; }

            var before = this.getFieldValues();

            $('[name="_playlist_skin"]').val(preset.skin);
            $('[name="_playlist_position"]').val(preset.position);
            $('[name="_playlist_show_header"]').prop('checked', preset.show_header === '1');
            $('[name="_playlist_header_text"]').val(preset.header_text);
            $('[name="_playlist_show_thumbnails"]').prop('checked', preset.show_thumbnails === '1');
            $('[name="_playlist_item_template"]').val(preset.item_template);
            $('[name="_playlist_grid_columns"]').val(preset.grid_columns);
            $('[name="_playlist_panel_width"]').val(preset.panel_width);
            $('[name="_playlist_max_width"]').val(preset.max_width);
            $('[name="_playlist_playlist_height_mode"]').val(preset.playlist_height_mode);
            $('[name="_playlist_panel_max_height"]').val(preset.panel_max_height);
            $('[name="_playlist_thumb_ratio"]').val(preset.thumb_ratio);
            $('[name="_playlist_bg_style"]').val(preset.bg_style);
            $('[name="_playlist_gradient_css"]').val(preset.gradient_css);
            $('[name="_playlist_accent_color"]').val(preset.accent_color);
            $('[name="_playlist_show_count"]').prop('checked', preset.show_count === '1');
            $('[name="_playlist_count_label"]').val(preset.count_label);
            $('[name="_playlist_show_numbers"]').prop('checked', preset.show_numbers === '1');
            $('[name="_playlist_play_icon"]').val(preset.play_icon);
            $('[name="_playlist_show_duration_badge"]').prop('checked', preset.show_duration_badge === '1');
            $('[name="_playlist_show_duration_in_list"]').prop('checked', preset.show_duration_in_list === '1');
            $('[name="_playlist_show_meta"]').prop('checked', preset.show_meta === '1');
            $('[name="_playlist_now_playing_style"]').val(preset.now_playing_style);
            $('[name="_playlist_autoplay_next"]').prop('checked', preset.autoplay_next === '1');
            $('[name="_playlist_start_item"]').val(preset.start_item);

            var self    = this;
            var changed = this.fields.filter(function(f) {
                return (before[f] || '') !== (preset[f] || '');
            });
            this.flashChangedFields(changed);
        },

        flashChangedFields: function(changed) {
            changed.forEach(function(f) {
                var $tr = $('[name="_playlist_' + f + '"]').closest('tr');
                $tr.addClass('lpl-pla__do-field--changed');
                setTimeout(function() { $tr.removeClass('lpl-pla__do-field--changed'); }, 2000);
            });
        },

        matchPreset: function(values) {
            for (var id in this.presets) {
                var preset = this.presets[id];
                var match = true;
                for (var i = 0; i < this.fields.length; i++) {
                    var f = this.fields[i];
                    if ((values[f] || '') !== (preset[f] || '')) {
                        match = false;
                        break;
                    }
                }
                if (match) { return id; }
            }
            return 'custom';
        },

        syncPresetDropdown: function() {
            var matched = this.matchPreset(this.getFieldValues());
            var $select = $('#lpl-pla-do-preset');
            if (matched === 'custom') {
                $select.find('option[value="custom"]').show();
                $select.val('custom');
            } else {
                $select.find('option[value="custom"]').hide();
                $select.val(matched);
            }
        },

        onApply: function() {
            var presetId = $('#lpl-pla-do-preset').val();
            if (!presetId || presetId === 'custom') { return; }

            var current = this.getFieldValues();
            var hasValues = this.fields.some(function(f) { return current[f] !== ''; });

            if (hasValues && this.matchPreset(current) !== presetId) {
                if (!window.confirm('This will overwrite your current settings. Continue?')) {
                    return;
                }
            }

            this.applyPreset(presetId);
            this.syncPresetDropdown();
            this.dirty = true;
            this.updatePreviewState();
        },

        onFieldChange: function() {
            this.dirty = true;
            this.syncPresetDropdown();
            this.updatePreviewState();
        },

        updatePreviewState: function() {
            var $btn    = $('.lpl-preview-metabox a.preview.button, #preview-action a');
            var $notice = $('.lpl-preview-metabox .lpl-pla__preview-notice');

            if ( this.dirty ) {
                $btn.addClass('lpl-pla__preview-btn--dirty');
                if ( !$notice.length ) {
                    $('.lpl-preview-metabox').append(
                        '<p class="lpl-pla__preview-notice">Save the post first to preview your latest changes.</p>'
                    );
                }
            } else {
                $btn.removeClass('lpl-pla__preview-btn--dirty');
                $notice.remove();
            }
        }
    };

    $(document).ready(function() {
        if ($('body').hasClass('post-type-lean_playlist')) {
            // Edit screen: builder + display options
            if ($('.lpl-pla__builder').length) {
                builder.init();
            }
            if ($('.lpl-pla__display-options').length) {
                vtabs.init();
                displayOptions.init();

                $(document).on('click', '.lpl-preview-metabox a.preview.button, #preview-action a', function(e) {
                    if (!displayOptions.dirty) { return; }
                    e.preventDefault();
                    displayOptions.updatePreviewState();
                });
            }
        }
    });

})(jQuery);
