/**
 * Playlist Builder Admin JS
 *
 * The omnibox is the fast path: paste a link, hit Add. Everything else lives
 * behind secondary links — upload opens wp.media directly, the rest deep-link
 * into the drawer (Add with details / Bulk Add / Browse existing players).
 *
 * Single source of truth: #lpl-pla-builder-item-list holds .lpl-pla__builder-item-row elements.
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
            this.$removeAllBtnCol   = $('#lpl-pla-builder-remove-all-btn');
            this.$searchEmpty       = $('#lpl-pla-builder-search-empty');

            // Omnibox: the primary add path
            this.$omniUrl    = $('#lpl-pla-omnibox-url');
            this.$omniAdd    = $('#lpl-pla-omnibox-add');
            this.$omniError  = $('#lpl-pla-omnibox-error');
            this.$uploadBtn  = $('#lpl-pla-builder-upload-btn');

            // Quick Add form (lex-rendered fields; media pickers owned by lex-settings-core)
            this.$qaUrl          = $('#lex__qa_url');
            this.$qaAttachmentId = $('#lex__qa_media');
            this.$qaMediaRemove  = $('#lex__qa_media_remove');
            this.$qaTitle        = $('#lex__qa_title');
            this.$qaThumbId      = $('#lex__qa_poster');
            this.$qaThumbRemove  = $('#lex__qa_poster_remove');
            this.$qaDuration     = $('#lex__qa_duration');
            this.$qaMetaText     = $('#lex__qa_meta_text');
            this.$qaSubmit       = $('#lpl-pla-qa-submit');
            this.$qaError        = $('#lpl-pla-qa-error');

            // Batch Add form
            this.$baUrls     = $('#lex__ba_urls');
            this.$baSubmit   = $('#lpl-pla-ba-submit');
            this.$baError    = $('#lpl-pla-ba-error');
            this.$baFailures = $('#lpl-pla-ba-failures');

            // Edit Track form (same lex-rendered fields as Quick Add, own _ed_* ids)
            this.$edUrl          = $('#lex__ed_url');
            this.$edAttachmentId = $('#lex__ed_media');
            this.$edMediaPreview = $('#lex__ed_media_preview');
            this.$edMediaRemove  = $('#lex__ed_media_remove');
            this.$edTitle        = $('#lex__ed_title');
            this.$edDuration     = $('#lex__ed_duration');
            this.$edMetaText     = $('#lex__ed_meta_text');
            this.$edThumbId      = $('#lex__ed_poster');
            this.$edThumbPreview = $('#lex__ed_poster_preview');
            this.$edThumbRemove  = $('#lex__ed_poster_remove');
            this.$edThumbPreviewWrapper = $('#lex__ed_poster_preview_wrapper');
            this.$edThumbPreviewImage   = $('#lex__ed_poster_preview_image');
            this.$edSubmit       = $('#lpl-pla-ed-submit');
            this.$edError        = $('#lpl-pla-ed-error');
            this.$edFullEditor   = $('#lpl-pla-ed-full-editor');
            this.editingId       = null;

            this.bindEvents();
            this.filterPlayers();
            this.reindex();
            this.updateMeta();
        },

        bindEvents: function() {
            // Omnibox
            this.$omniAdd.on('click', this.onOmniboxAdd.bind(this));
            this.$omniUrl.on('keydown', this.onOmniboxKeydown.bind(this));
            this.$omniUrl.on('paste', this.onOmniboxPaste.bind(this));
            this.$uploadBtn.on('click', this.onUploadClick.bind(this));

            // Enter in any builder text field must never submit the post.
            // Delegated, so it also covers the drawer's QA/BA fields.
            this.$root.on('keydown', 'input[type="text"]', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                }
            });

            // A collapsed metabox display:none's .inside, which would take the
            // fixed-positioned drawer with it — leaving it open but invisible,
            // with no way to close it.
            $(document).on('postbox-toggled', function(_, postbox) {
                if ($(postbox).find('#lpl-pla-builder-drawer').length && window.lexDrawer) {
                    window.lexDrawer.close('lpl-pla-builder-drawer');
                }
            });

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

            // Quick Add: URL and upload are mutually exclusive.
            // lex-settings-core triggers 'change' on the hidden input on both select and remove.
            this.$qaAttachmentId.on('change', this.onQaMediaChange.bind(this));
            this.$qaUrl.on('input', this.onQaUrlInput.bind(this));
            this.$qaSubmit.on('click', this.onQuickAddSubmit.bind(this));

            // Batch Add
            this.$baSubmit.on('click', this.onBatchAddSubmit.bind(this));

            // Edit Track: the row's Edit button also opens the drawer via its
            // data-lex-drawer-open attribute (handled by lex-drawer.js) — this
            // fetches the track's data and prefills the panel.
            this.$itemList.on('click', '.lpl-pla__builder-edit-link', this.onEditClick.bind(this));
            this.$edSubmit.on('click', this.onEditSubmit.bind(this));
            // URL and upload are mutually exclusive here too, same as Quick Add.
            this.$edAttachmentId.on('change', this.onEdMediaChange.bind(this));
            this.$edUrl.on('input', this.onEdUrlInput.bind(this));

            // Drag to reorder
            this.initSortable();
        },

        // ── Omnibox ───────────────────────────────────────

        showOmniError: function(message) {
            this.$omniError.text(message).show();
        },

        // A paste or a typed value holding more than one link is a Bulk Add
        // job. Detect it here rather than silently mangling the input.
        isMultiUrl: function(text) {
            return /[\n\r]/.test(text) || (text.trim().split(/\s+/).filter(Boolean).length > 1);
        },

        onOmniboxKeydown: function(e) {
            if (e.key !== 'Enter' && e.keyCode !== 13) {
                return;
            }
            // Enter in a lone text input submits the form — and saves the post.
            e.preventDefault();
            this.onOmniboxAdd();
        },

        onOmniboxPaste: function(e) {
            var clip = e.originalEvent && e.originalEvent.clipboardData;
            if (!clip) {
                return;
            }

            var text = clip.getData('text') || '';
            if (!this.isMultiUrl(text)) {
                return;
            }

            e.preventDefault();
            this.showOmniError('Multiple links detected — use Bulk Add.');
        },

        onOmniboxAdd: function() {
            var self = this;
            var url  = this.$omniUrl.val().trim();

            this.$omniError.hide();

            if (!url) {
                this.showOmniError('Paste a media link first.');
                return;
            }

            // Same class of input, arriving by typing rather than paste.
            if (this.isMultiUrl(url)) {
                this.showOmniError('Multiple links detected — use Bulk Add.');
                return;
            }

            this.$omniAdd.prop('disabled', true);

            $.post(i18n.ajax_url, {
                action:        'leanpl_playlist_quick_add',
                nonce:         i18n.nonce,
                url:           url,
                playlist_type: i18n.playlist_type
            }).done(function(response) {
                if (response.success) {
                    self.applyQuickAddResult(response.data);
                    self.$omniUrl.val('');
                } else {
                    self.showOmniError((response.data && response.data.message) || 'Something went wrong.');
                }
            }).fail(function() {
                self.showOmniError('Request failed. Please try again.');
            }).always(function() {
                self.$omniAdd.prop('disabled', false);
            });
        },

        // ── Upload ────────────────────────────────────────

        onUploadClick: function() {
            var self = this;

            if (!window.wp || !window.wp.media) {
                return;
            }

            if (!this.mediaFrame) {
                this.mediaFrame = wp.media({
                    title:    'Select media',
                    library:  { type: i18n.playlist_type === 'audio' ? 'audio' : 'video' },
                    button:   { text: 'Add to playlist' },
                    multiple: false
                });

                this.mediaFrame.on('select', function() {
                    var attachment = self.mediaFrame.state().get('selection').first().toJSON();
                    self.addFromAttachment(attachment.id);
                });
            }

            this.mediaFrame.open();
        },

        addFromAttachment: function(attachmentId) {
            var self = this;

            this.$omniError.hide();

            $.post(i18n.ajax_url, {
                action:        'leanpl_playlist_quick_add',
                nonce:         i18n.nonce,
                attachment_id: attachmentId,
                playlist_type: i18n.playlist_type
            }).done(function(response) {
                if (response.success) {
                    self.applyQuickAddResult(response.data);
                } else {
                    self.showOmniError((response.data && response.data.message) || 'Something went wrong.');
                }
            }).fail(function() {
                self.showOmniError('Request failed. Please try again.');
            });
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

            this.$itemList.find('.lpl-pla__builder-items-empty').remove();

            this.$itemList.append(
                '<div class="lpl-pla__builder-item-row" data-player-id="' + this.esc(playerId) + '">' +
                    '<span class="lpl-pla__builder-drag-handle" aria-hidden="true">&#8801;</span>' +
                    '<span class="lpl-pla__builder-item-num"></span>' +
                    '<span class="lpl-pla__builder-item-title">' + this.escHtml(title) + '</span>' +
                    '<span class="lpl-pla__builder-badge lpl-pla__builder-badge--' + this.esc(sourceType) + '">' + this.escHtml(sourceLabel) + '</span>' +
                    '<div class="lpl-pla__builder-item-actions">' +
                        '<button type="button" class="lpl-pla__builder-edit-link" data-lex-drawer-open="lpl-pla-builder-drawer" data-lex-drawer-vtab="edit-track">Edit</button>' +
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
            this.notifyItemsChanged();
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
            var activeType = i18n.playlist_type || 'video';

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

        // ── Quick Add ─────────────────────────────────────

        onQaMediaChange: function() {
            // Upload chosen → clear the URL field (mutually exclusive).
            // Remove also fires change with an empty value; nothing to clear then.
            if (this.$qaAttachmentId.val()) {
                this.$qaUrl.val('');
            }
        },

        onQaUrlInput: function() {
            // URL typed → clear any chosen upload via the lex remove button.
            // The remove handler empties the hidden input, so the change
            // handler above sees '' and does not loop.
            if (this.$qaUrl.val().trim() !== '' && this.$qaAttachmentId.val()) {
                this.$qaMediaRemove.trigger('click');
            }
        },

        showQaError: function(message) {
            this.$qaError.text(message).show();
        },

        onQuickAddSubmit: function() {
            var self  = this;
            var url   = this.$qaUrl.val().trim();
            var attId = this.$qaAttachmentId.val();
            var title = this.$qaTitle.val().trim();

            this.$qaError.hide();

            if (!url && !attId) {
                this.showQaError('Enter a media URL or choose an upload.');
                return;
            }

            this.$qaSubmit.prop('disabled', true);

            $.post(i18n.ajax_url, {
                action:        'leanpl_playlist_quick_add',
                nonce:         i18n.nonce,
                url:           url,
                attachment_id: attId,
                title:         title,
                poster_id:     this.$qaThumbId.val(),
                duration:      this.$qaDuration.val(),
                meta_text:     this.$qaMetaText.val(),
                playlist_type: i18n.playlist_type
            }).done(function(response) {
                if (response.success) {
                    self.applyQuickAddResult(response.data);
                    self.resetQuickAddForm();
                } else {
                    self.showQaError((response.data && response.data.message) || 'Something went wrong.');
                }
            }).fail(function() {
                self.showQaError('Request failed. Please try again.');
            }).always(function() {
                self.$qaSubmit.prop('disabled', false);
            });
        },

        // The success path shared by the omnibox, the upload link, and Quick
        // Add: all three hit the same endpoint and land the same row.
        applyQuickAddResult: function(data) {
            this.addItemFromData(data);
            this.insertLibraryRow(data);
            this.toast('success', 'Track added to playlist.');
        },

        resetQuickAddForm: function() {
            this.$qaUrl.val('');
            this.$qaTitle.val('');
            this.$qaDuration.val('');
            this.$qaMetaText.val('');
            // Lex remove handlers clear hidden input, preview, image wrapper,
            // and re-disable themselves. trigger('click') fires them even
            // while the button carries the disabled attribute; the handler
            // is a no-op when nothing is selected.
            this.$qaMediaRemove.trigger('click');
            this.$qaThumbRemove.trigger('click');
            this.$qaError.hide();
        },

        // ── Batch Add ─────────────────────────────────────

        showBaError: function(message) {
            this.$baError.text(message).show();
        },

        onBatchAddSubmit: function() {
            var self = this;
            var urls = this.$baUrls.val().trim();

            this.$baError.hide();
            this.$baFailures.hide().empty();

            if (!urls) {
                this.showBaError('Paste at least one URL, one per line.');
                return;
            }

            this.$baSubmit.prop('disabled', true);

            $.post(i18n.ajax_url, {
                action:        'leanpl_playlist_batch_add',
                nonce:         i18n.nonce,
                urls:          urls,
                playlist_type: i18n.playlist_type
            }).done(function(response) {
                // Over-cap and nonce/cap failures land here: nothing was created,
                // so the textarea is left exactly as the user pasted it.
                if (!response.success) {
                    self.showBaError((response.data && response.data.message) || 'Something went wrong.');
                    return;
                }

                var added  = response.data.added || [];
                var failed = response.data.failed || [];

                $.each(added, function(_, data) {
                    self.addItemFromData(data);
                    self.insertLibraryRow(data);
                });

                self.renderBatchFailures(failed);

                // Keep only the rejected lines, so the user can fix and retry
                // without re-adding what already succeeded.
                self.$baUrls.val($.map(failed, function(f) { return f.url; }).join('\n'));

                self.toast(
                    failed.length ? 'info' : 'success',
                    self.batchSummary(added.length, failed.length)
                );
            }).fail(function() {
                self.showBaError('Request failed. Please try again.');
            }).always(function() {
                self.$baSubmit.prop('disabled', false);
            });
        },

        batchSummary: function(addedCount, failedCount) {
            var summary = addedCount + (addedCount === 1 ? ' track added' : ' tracks added');
            if (failedCount) {
                summary += ', ' + failedCount + ' failed';
            }
            return summary + '.';
        },

        renderBatchFailures: function(failed) {
            if (!failed.length) {
                return;
            }

            var self = this;
            var html = $.map(failed, function(f) {
                return '<li>' + self.escHtml(f.url) +
                    '<strong>' + self.escHtml(f.reason) + '</strong></li>';
            }).join('');

            this.$baFailures.html(html).show();
        },

        // ── Edit Track ────────────────────────────────────

        showEdError: function(message) {
            this.$edError.text(message).show();
        },

        onEdMediaChange: function() {
            // Upload chosen → clear the URL field (mutually exclusive).
            // Remove also fires change with an empty value; nothing to clear then.
            if (this.$edAttachmentId.val()) {
                this.$edUrl.val('');
            }
        },

        onEdUrlInput: function() {
            // URL typed → clear any chosen upload via the lex remove button.
            if (this.$edUrl.val().trim() !== '' && this.$edAttachmentId.val()) {
                this.$edMediaRemove.trigger('click');
            }
        },

        // Row's Edit button clicked: remember which track, fetch its current
        // data (not the row's cached data, which can be stale), then prefill.
        onEditClick: function(e) {
            var self     = this;
            var playerId = $(e.currentTarget).closest('.lpl-pla__builder-item-row').data('player-id');

            this.editingId = playerId;
            this.$edError.hide();
            this.resetEditForm();
            this.$edSubmit.prop('disabled', true);

            $.post(i18n.ajax_url, {
                action:    'leanpl_playlist_get_player',
                nonce:     i18n.nonce,
                player_id: playerId
            }).done(function(response) {
                if (response.success) {
                    self.fillEditForm(response.data);
                } else {
                    self.showEdError((response.data && response.data.message) || 'Could not load this track.');
                }
            }).fail(function() {
                self.showEdError('Request failed. Please try again.');
            }).always(function() {
                self.$edSubmit.prop('disabled', false);
            });
        },

        fillEditForm: function(d) {
            this.$edUrl.val(d.url || '');
            this.$edTitle.val(d.title || '');
            this.$edDuration.val(d.duration || '');
            this.$edMetaText.val(d.meta_text || '');

            this.setMediaField(this.$edAttachmentId, this.$edMediaPreview, this.$edMediaRemove, d.attachment_id, d.attachment_url);
            this.setMediaField(this.$edThumbId, this.$edThumbPreview, this.$edThumbRemove, d.poster_id, d.poster_url, this.$edThumbPreviewWrapper, this.$edThumbPreviewImage);

            this.$edFullEditor.attr('href', 'post.php?post=' + d.id + '&action=edit');
        },

        resetEditForm: function() {
            this.$edUrl.val('');
            this.$edTitle.val('');
            this.$edDuration.val('');
            this.$edMetaText.val('');
            this.clearMediaField(this.$edAttachmentId, this.$edMediaPreview, this.$edMediaRemove);
            this.clearMediaField(this.$edThumbId, this.$edThumbPreview, this.$edThumbRemove, this.$edThumbPreviewWrapper);
            this.$edFullEditor.attr('href', '#');
        },

        // Fill a lex media field the same way its own wp.media picker does on
        // selection, so nothing downstream (remove button, preview) can tell
        // the difference between a user pick and a prefill. $previewWrapper /
        // $previewImage are only passed for image fields (the poster).
        setMediaField: function($hidden, $preview, $remove, id, url, $previewWrapper, $previewImage) {
            if (!id) {
                this.clearMediaField($hidden, $preview, $remove, $previewWrapper);
                return;
            }
            $hidden.val(id).trigger('change');
            $preview.val(url || '');
            $remove.prop('disabled', false);
            if ($previewWrapper && $previewWrapper.length && $previewImage && $previewImage.length) {
                $previewImage.attr('src', url || '');
                $previewWrapper.show();
            }
        },

        clearMediaField: function($hidden, $preview, $remove, $previewWrapper) {
            $hidden.val('').trigger('change');
            $preview.val('');
            $remove.prop('disabled', true);
            if ($previewWrapper && $previewWrapper.length) {
                $previewWrapper.hide();
            }
        },

        onEditSubmit: function() {
            var self = this;

            this.$edError.hide();
            this.$edSubmit.prop('disabled', true);

            $.post(i18n.ajax_url, {
                action:        'leanpl_playlist_update_player',
                nonce:         i18n.nonce,
                player_id:     this.editingId,
                url:           this.$edUrl.val().trim(),
                attachment_id: this.$edAttachmentId.val(),
                title:         this.$edTitle.val().trim(),
                poster_id:     this.$edThumbId.val(),
                duration:      this.$edDuration.val(),
                meta_text:     this.$edMetaText.val(),
                playlist_type: i18n.playlist_type
            }).done(function(response) {
                if (!response.success) {
                    self.showEdError((response.data && response.data.message) || 'Something went wrong.');
                    return;
                }
                self.refreshRow(response.data);
                self.refreshLibraryRow(response.data);
                self.toast('success', 'Track updated.');
                if (window.lexDrawer) {
                    window.lexDrawer.close('lpl-pla-builder-drawer');
                }
            }).fail(function() {
                self.showEdError('Request failed. Please try again.');
            }).always(function() {
                self.$edSubmit.prop('disabled', false);
            });
        },

        // Update the builder row in place — same title + badge fields Quick
        // Add sets on a new row, just applied to the existing one.
        refreshRow: function(data) {
            var $row = this.$itemList.find('[data-player-id="' + data.id + '"]');
            $row.find('.lpl-pla__builder-item-title').text(data.title);
            $row.find('.lpl-pla__builder-badge')
                .attr('class', 'lpl-pla__builder-badge lpl-pla__builder-badge--' + this.esc(data.source_type))
                .text(data.source_label);
        },

        // The Existing Players list carries its own copy of title/badge/
        // duration/meta as data-* attributes (used for search + Add All) —
        // keep it in sync too.
        refreshLibraryRow: function(data) {
            var $row = this.$playerList.find('.lpl-pla__builder-player-row[data-player-id="' + data.id + '"]');
            if (!$row.length) {
                return;
            }
            $row.attr({
                'data-title':        data.title,
                'data-source-type':  data.source_type,
                'data-source-label': data.source_label,
                'data-duration':     data.duration || '',
                'data-meta-text':    data.meta_text || ''
            });
            // Also update jQuery's data cache: addItem() and filterPlayers()
            // read via $row.data(), which caches on first read (init filtering)
            // and ignores later .attr() writes. Without this, re-adding an
            // edited row or searching by its new title uses the stale value.
            $row.data({
                'title':        data.title,
                'source-type':  data.source_type,
                'source-label': data.source_label,
                'duration':     data.duration || '',
                'meta-text':    data.meta_text || ''
            });
            $row.find('.lpl-pla__builder-player-title').text(data.title);
            $row.find('.lpl-pla__builder-badge')
                .attr('class', 'lpl-pla__builder-badge lpl-pla__builder-badge--' + this.esc(data.source_type))
                .text(data.source_label);
        },

        // ── Shared add-from-AJAX helpers ──────────────────

        addItemFromData: function(data) {
            // Fake row object carrying the data attrs addItem() reads.
            var $fake = $('<div>').data({
                'title':        data.title,
                'source-type':  data.source_type,
                'source-label': data.source_label
            });
            this.addItem($fake, String(data.id));
        },

        insertLibraryRow: function(data) {
            // Newly created player should also appear (checked) in the
            // Existing Players list so uncheck-to-remove works immediately.
            this.$playerList.find('.lpl-pla__builder-picker-empty--no-players').remove();
            this.$playerList.prepend(
                '<label class="lpl-pla__builder-player-row lpl-pla__builder-player-row--added"' +
                    ' data-player-id="' + this.esc(data.id) + '"' +
                    ' data-categories=""' +
                    ' data-title="' + this.esc(data.title) + '"' +
                    ' data-type="' + this.esc(i18n.playlist_type || 'video') + '"' +
                    ' data-source-type="' + this.esc(data.source_type) + '"' +
                    ' data-source-label="' + this.esc(data.source_label) + '"' +
                    ' data-duration="' + this.esc(data.duration || '') + '"' +
                    ' data-meta-text="' + this.esc(data.meta_text || '') + '"' +
                '>' +
                    '<input type="checkbox" class="lpl-pla__builder-player-checkbox" value="' + this.esc(data.id) + '" checked />' +
                    '<span class="lpl-pla__builder-player-title">' + this.escHtml(data.title) + '</span>' +
                    '<span class="lpl-pla__builder-badge lpl-pla__builder-badge--' + this.esc(data.source_type) + '">' + this.escHtml(data.source_label) + '</span>' +
                '</label>'
            );
        },

        toast: function(type, message) {
            var api = (window.lexSettings && window.lexSettings.notifications) || null;
            if (!api) {
                // The lex notifications script attaches its API to an
                // instance-named global (e.g. leanPlayerSettings).
                for (var key in window) {
                    if (key.slice(-8) === 'Settings' && window[key] && window[key].notifications) {
                        api = window[key].notifications;
                        break;
                    }
                }
            }
            if (api) {
                api.show(type, message);
            }
        },

        notifyItemsChanged: function() {
            $(document).trigger('leanpl:playlist:items-changed');
        },

        // ── Reindex ───────────────────────────────────────

        reindex: function() {
            this.$itemList.find('.lpl-pla__builder-item-row').each(function(i) {
                $(this).find('input[type="hidden"]').attr('name', '_playlist_items[' + i + '][id]');
                $(this).find('.lpl-pla__builder-item-num').text(i + 1);
            });
            displayOptions.dirty = true;
            displayOptions.updatePreviewState();
            this.notifyItemsChanged();
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
                        '<p class="lpl-pla__builder-items-empty-title">No tracks yet.</p>' +
                        '<p class="lpl-pla__builder-items-empty-sub">Paste a link above, or use the links below to upload, bulk add, or browse existing players.</p>' +
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
        storageKey: function( $container ) {
            var tabId = ( $container && $container.data('tab') ) || 'default';
            return 'lpl_do_tab_' + tabId + '_' + i18n.post_id;
        },
        activate: function( $btn, $container ) {
            var vtab = $btn.data('vtab');
            $container.find('.lex-vtabs__nav button').removeClass('lex-vtabs__btn--active');
            $btn.addClass('lex-vtabs__btn--active');
            $container.find('.lex-vtab-pane').hide();
            var $pane = $container.find('.lex-vtab-pane[data-vtab="' + vtab + '"]').show();

            // The builder drawer's title doubles as "which mode am I in":
            // Edit Track while that panel is active, Add tracks otherwise.
            // The nav (Quick Add / Bulk Add / Existing Players) belongs to the
            // add flow only — hide it while editing so it can't throw the user
            // out of the edit they're doing.
            if ( $container.data('tab') === 'builder-drawer' ) {
                var isEdit = vtab === 'edit-track';
                $('#lpl-pla-builder-drawer-title').text( isEdit ? 'Edit track' : 'Add tracks' );
                $container.find('.lex-vtabs__nav-wrap').toggle( !isEdit );
            }

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
            var self    = this;
            var isAudio = i18n.playlist_type === 'audio';

            $('.lex-vtabs').each(function() {
                var $container = $(this);
                var $nav = $container.find('.lex-vtabs__nav');
                if (!$nav.length) { return; }

                var savedTab = localStorage.getItem( self.storageKey( $container ) );

                if ( !isAudio ) {
                    $nav.find('button[data-vtab="a-audio"]').hide();
                    $container.find('.lex-vtab-pane[data-vtab="a-audio"]').hide();
                }

                // Edit Track only opens from a row's Edit button (lex-drawer's
                // activateVtab() finds and clicks this hidden nav button) — it
                // must never be reachable by clicking through the tabs.
                if ( $container.data('tab') === 'builder-drawer' ) {
                    $nav.find('button[data-vtab="edit-track"]').hide();
                }

                $nav.on('click', 'button', function(e) {
                    self.activate( $(e.currentTarget), $container );
                    localStorage.setItem( self.storageKey( $container ), $(e.currentTarget).data('vtab') );
                });

                var $restore = savedTab
                    ? $nav.find('button[data-vtab="' + savedTab + '"]:visible')
                    : null;
                self.activate( $restore && $restore.length ? $restore : $nav.find('button:visible').first(), $container );
            });
        },
        onTabClick: function(e, $container) {
            this.activate( $(e.currentTarget), $container );
            localStorage.setItem( this.storageKey( $container ), $(e.currentTarget).data('vtab') );
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
            // Edit screen: builder + display options.
            // vtabs.init() binds every .lex-vtabs on the screen, including the
            // builder's — it can't be gated on the display-options metabox.
            vtabs.init();

            if ($('.lpl-pla__builder').length) {
                builder.init();
            }
            if ($('.lpl-pla__display-options').length) {
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
