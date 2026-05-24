/**
 * Admin JavaScript for Video & Audio Player
 */

(function($) {
    'use strict';

    // ── Type-selection modal factory ─────────────────────────────────────────
    // Used by both player and playlist list-table pages.
    // config: { modalId, postType, paramName }
    function createTypeModal( config ) {
        var $modal    = null;
        var $backdrop = null;
        var addNewHref = null;

        return {
            init: function() {
                $modal    = $( '#' + config.modalId );
                $backdrop = $( '#' + config.modalId + '-backdrop' );

                if ( ! $modal.length ) { return; }

                var self = this;

                $( document ).on( 'click', 'a.page-title-action', function( e ) {
                    var href = $( this ).attr( 'href' ) || '';
                    if ( href.indexOf( 'post_type=' + config.postType ) !== -1 && href.indexOf( 'post-new.php' ) !== -1 ) {
                        e.preventDefault();
                        addNewHref = href;
                        self.open();
                    }
                } );

                $modal.on( 'click', '.lpl-pla__builder-modal-card', function( e ) {
                    e.preventDefault();
                    var type = $( this ).data( 'type' );
                    var url  = addNewHref || ( 'post-new.php?post_type=' + config.postType );
                    url += ( url.indexOf( '?' ) !== -1 ? '&' : '?' ) + config.paramName + '=' + type;
                    window.location.href = url;
                } );

                $backdrop.on( 'click', this.close.bind( this ) );
                $( '#' + config.modalId + '-close' ).on( 'click', this.close.bind( this ) );

                $( document ).on( 'keydown.lpl-modal-' + config.modalId, function( e ) {
                    if ( e.key === 'Escape' ) { self.close(); }
                } );
            },

            open: function() {
                $modal.removeAttr( 'hidden' );
                $( '#' + config.modalId + '-close' ).trigger( 'focus' );
            },

            close: function() {
                $modal.attr( 'hidden', '' );
            }
        };
    }

    $(document).ready(function() {
        // Only run on our post types
        if (!$('body').hasClass('post-type-lean_player') && !$('body').hasClass('post-type-lean_playlist')) {
            return;
        }

        // Init type-selection modals (each guards itself with a DOM length check).
        var playerModal   = createTypeModal( { modalId: 'lpl-plr-builder-modal', postType: 'lean_player',   paramName: 'player_type'   } );
        var playlistModal = createTypeModal( { modalId: 'lpl-pla-builder-modal', postType: 'lean_playlist', paramName: 'playlist_type' } );
        playerModal.init();
        playlistModal.init();

        // Auto-open player modal when redirected from sidebar "Add New Player" link.
        if ( window.location.search.indexOf( 'open_modal=1' ) !== -1 ) {
            playerModal.open();
        }
        
        // Fix sticky positioning by ensuring parent has proper height
        function fixStickyPositioning() {
            var $postBody = $('#post-body.metabox-holder.columns-2');
            var $container1 = $('#postbox-container-1');
            
            if ($postBody.length && $container1.length) {
                // Force post-body to have height based on its content
                var container2Height = $('#postbox-container-2').outerHeight() || 0;
                var container1Height = $container1.outerHeight() || 0;
                var maxHeight = Math.max(container1Height, container2Height);
                
                if (maxHeight > 0) {
                    $postBody.css('min-height', maxHeight + 'px');
                }
            }
        }
        
        // Run on load and after a short delay to ensure content is rendered
        fixStickyPositioning();
        setTimeout(fixStickyPositioning, 100);
        setTimeout(fixStickyPositioning, 500);
        // Map field names to their conditional requirements
        var conditionalFields = {
            '_youtube_url': { showIf: '_video_type', showValue: 'youtube' },
            '_vimeo_url': { showIf: '_video_type', showValue: 'vimeo' },
            '_html5_source_type': { showIf: '_video_type', showValue: 'html5' },
            '_video_source': { showIf: '_html5_source_type', showValue: 'upload' },
            '_html5_video_url': { showIf: '_html5_source_type', showValue: 'link' },
            '_audio_source': { showIf: '_audio_source_type', showValue: 'upload' },
            '_html5_audio_url': { showIf: '_audio_source_type', showValue: 'link' }
        };
        
        // Add conditional classes to field rows
        function setupConditionalFields() {
            $.each(conditionalFields, function(fieldName, condition) {
                var $field = $('input[name="' + fieldName + '"], textarea[name="' + fieldName + '"], select[name="' + fieldName + '"]');
                if ($field.length) {
                    var $tr = $field.closest('tr');
                    if ($tr.length && !$tr.hasClass('lpl-conditional-field')) {
                        $tr.addClass('lpl-conditional-field')
                           .attr('data-show-if', condition.showIf)
                           .attr('data-show-value', condition.showValue);
                    }
                }
            });
        }
        
        // Update conditional sections and fields visibility
        function updateConditionalElements() {
            var playerType = $('select[name="_player_type"]').val() ||
                           $('input[name="_player_type"]:checked').val() ||
                           $('input[name="_player_type"][type="hidden"]').val();
            var videoType = $('select[name="_video_type"]').val() || 
                          $('input[name="_video_type"]:checked').val();
            var html5SourceType = $('select[name="_html5_source_type"]').val() || 
                                $('input[name="_html5_source_type"]:checked').val();
            var audioSourceType = $('select[name="_audio_source_type"]').val() || 
                                $('input[name="_audio_source_type"]:checked').val();
            
            // Update conditional sections
            $('.lpl-conditional-section').each(function() {
                var $section = $(this);
                var showIf = $section.data('show-if');
                var showValue = $section.data('show-value');
                var shouldShow = false;
                
                if (showIf === '_player_type') {
                    shouldShow = playerType === showValue;
                } else if (showIf === '_video_type') {
                    shouldShow = videoType === showValue;
                } else if (showIf === '_html5_source_type') {
                    // For HTML5 source type sections, also check that video_type is 'html5'
                    shouldShow = (videoType === 'html5') && (html5SourceType === showValue);
                } else if (showIf === '_audio_source_type') {
                    // For audio source type sections, also check that player_type is 'audio'
                    shouldShow = (playerType === 'audio') && (audioSourceType === showValue);
                }
                
                if (shouldShow) {
                    $section.show();
                } else {
                    $section.hide();
                }
            });
            
            // Update conditional field rows (tr elements)
            $('tr.lpl-conditional-field').each(function() {
                var $field = $(this);
                var showIf = $field.data('show-if');
                var showValue = $field.data('show-value');
                var shouldShow = false;
                
                if (showIf === '_player_type') {
                    shouldShow = playerType === showValue;
                } else if (showIf === '_video_type') {
                    shouldShow = videoType === showValue;
                } else if (showIf === '_html5_source_type') {
                    // For HTML5 source type fields, also check that video_type is 'html5'
                    shouldShow = (videoType === 'html5') && (html5SourceType === showValue);
                } else if (showIf === '_audio_source_type') {
                    // For audio source type fields, also check that player_type is 'audio'
                    shouldShow = (playerType === 'audio') && (audioSourceType === showValue);
                }
                
                if (shouldShow) {
                    $field.show();
                } else {
                    $field.hide();
                }
            });
        }
        
        // Setup conditional fields on page load
        setupConditionalFields();
        
        // Initial state
        updateConditionalElements();
        
        // Watch for changes to video type, HTML5 source type, and audio source type
        $(document).on('change', 'select[name="_video_type"], input[name="_video_type"]', function() {
            updateConditionalElements();
        });
        
        $(document).on('change', 'select[name="_html5_source_type"], input[name="_html5_source_type"]', function() {
            updateConditionalElements();
        });
        
        $(document).on('change', 'select[name="_audio_source_type"], input[name="_audio_source_type"]', function() {
            updateConditionalElements();
        });

        // ── Playlist metabox conditional fields ───────────────────────────────
        function updatePlaylistConditionals() {
            var showThumbs = $('input[name="_playlist_show_thumbnails"]').is(':checked');
            $('input[name="_playlist_thumb_ratio"], select[name="_playlist_thumb_ratio"]')
                .closest('tr').toggle( showThumbs );
            $('input[name="_playlist_show_duration_badge"]')
                .closest('tr').toggle( showThumbs );
            $('tr.lex-field-info--notice').toggle( showThumbs );
        }

        if ( $('input[name="_playlist_show_thumbnails"]').length ) {
            updatePlaylistConditionals();
            $(document).on( 'change', 'input[name="_playlist_show_thumbnails"]', updatePlaylistConditionals );
        }

        // ── Player metabox vertical tabs ──────────────────────────────────────
        var $vtabs = $('.lpl-player-vtabs');
        if ( $vtabs.length ) {
            var vtabStorageKey = 'lpl_player_vtab_' + ( $vtabs.data('storage-suffix') || $('input#post_ID').val() || 'new' );
            var savedVtab      = localStorage.getItem( vtabStorageKey );
            var $vtabNav       = $vtabs.find('.lex-vtabs__nav');

            function activateVtab( $btn ) {
                var vtab  = $btn.data('vtab');
                $vtabNav.find('button').removeClass('lex-vtabs__btn--active');
                $btn.addClass('lex-vtabs__btn--active');
                $vtabs.find('.lex-vtab-pane').hide();
                var $pane = $vtabs.find('.lex-vtab-pane[data-vtab="' + vtab + '"]').show();

                if ( $.fn.wpColorPicker ) {
                    $pane.find('.lex-color-picker').each(function() {
                        var $el = $(this);
                        if ( $el.closest('.wp-picker-container').length ) { return; }
                        $el.wpColorPicker({ defaultColor: this.value || false });
                    });
                }
            }

            $vtabNav.on('click', 'button', function(e) {
                activateVtab( $(e.currentTarget) );
                localStorage.setItem( vtabStorageKey, $(e.currentTarget).data('vtab') );
            });

            function applyPlayerTypeToTabs( playerType ) {
                var isVideo    = playerType === 'video';
                var $videoBtn  = $vtabNav.find('button[data-vtab="p-video"]');
                var $videoPane = $vtabs.find('.lex-vtab-pane[data-vtab="p-video"]');
                $videoBtn.toggle( isVideo );
                $videoPane.toggle( isVideo );
                if ( !isVideo && $videoBtn.hasClass('lex-vtabs__btn--active') ) {
                    activateVtab( $vtabNav.find('button:visible').first() );
                }
            }

            var initialType = $vtabs.data('player-type') || 'video';
            applyPlayerTypeToTabs( initialType );

            var $restore = savedVtab
                ? $vtabNav.find('button[data-vtab="' + savedVtab + '"]:visible')
                : null;
            activateVtab( $restore && $restore.length ? $restore : $vtabNav.find('button:visible').first() );
        }
    });

})(jQuery);