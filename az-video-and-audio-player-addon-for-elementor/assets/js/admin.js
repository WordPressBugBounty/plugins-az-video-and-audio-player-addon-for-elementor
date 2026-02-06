/**
 * Admin JavaScript for Video & Audio Player
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Only run on lean_player post type
        if (!$('body').hasClass('post-type-lean_player')) {
            return;
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
                           $('input[name="_player_type"]:checked').val();
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
        
        // Watch for changes to player type, video type, HTML5 source type, and audio source type
        $(document).on('change', 'select[name="_player_type"], input[name="_player_type"]', function() {
            updateConditionalElements();
        });
        
        $(document).on('change', 'select[name="_video_type"], input[name="_video_type"]', function() {
            updateConditionalElements();
        });
        
        $(document).on('change', 'select[name="_html5_source_type"], input[name="_html5_source_type"]', function() {
            updateConditionalElements();
        });
        
        $(document).on('change', 'select[name="_audio_source_type"], input[name="_audio_source_type"]', function() {
            updateConditionalElements();
        });
    });
    
})(jQuery);