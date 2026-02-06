/**
 * Lex Copy to Clipboard Component
 * 
 * PURPOSE: Reusable copy-to-clipboard functionality
 * 
 * HOW IT WORKS:
 * - Activation: data-lex-copy attribute (required)
 * - Styling: CSS classes (lex-copy-button, lex-copy-button--light, etc.)
 * 
 * USAGE:
 * <button data-lex-copy="text-to-copy" class="lex-copy-button">
 *     Copy
 * </button>
 */

(function($) {
    'use strict';

    // ============================================
    // CONSTANTS
    // ============================================
    
    const SUCCESS_MESSAGE = 'Copied!';
    const ERROR_MESSAGE = '<span class="dashicons dashicons-warning"></span> Select & Copy';
    const FEEDBACK_DURATION = 2000; // milliseconds
    const COPIED_CLASS = 'lex-copy-button--copied';
    const ACTIVATION_ATTRIBUTE = 'data-lex-copy';

    // ============================================
    // LEX COPY COMPONENT
    // ============================================
    
    const LexCopy = {
        
        /**
         * Start the copy functionality
         */
        init: function() {
            this.setupClickHandlers();
        },

        /**
         * Setup click handlers for copy buttons
         */
        setupClickHandlers: function() {
            var self = this;
            
            // Listen for clicks on any element with data-lex-copy attribute
            $(document).on('click', '[' + ACTIVATION_ATTRIBUTE + ']', function(event) {
                event.preventDefault();
                
                var $button = $(this);
                var textToCopy = $button.attr(ACTIVATION_ATTRIBUTE);
                
                // Check if text exists
                if (!textToCopy || textToCopy.trim() === '') {
                    console.warn('Lex Copy: No text found in data-lex-copy attribute');
                    return;
                }
                
                // Get original button HTML to restore later
                var originalHtml = $button.html();
                
                // Copy the text
                self.copyText(textToCopy, $button, originalHtml);
            });
        },

        /**
         * Copy text to clipboard
         * 
         * @param {string} text - Text to copy
         * @param {jQuery} $button - Button element
         * @param {string} originalHtml - Original button HTML to restore
         */
        copyText: function(text, $button, originalHtml) {
            var self = this;
            
            // Try modern clipboard API first
            if (this.canUseClipboardAPI()) {
                this.copyWithModernAPI(text, $button, originalHtml);
            } else {
                // Use fallback method
                this.copyWithFallback(text, $button, originalHtml);
            }
        },

        /**
         * Check if we can use the modern Clipboard API
         * 
         * @return {boolean} True if clipboard API is available
         */
        canUseClipboardAPI: function() {
            return navigator.clipboard !== undefined && window.isSecureContext === true;
        },

        /**
         * Copy using modern Clipboard API
         * 
         * @param {string} text - Text to copy
         * @param {jQuery} $button - Button element
         * @param {string} originalHtml - Original button HTML
         */
        copyWithModernAPI: function(text, $button, originalHtml) {
            var self = this;
            
            navigator.clipboard.writeText(text)
                .then(function() {
                    // Success - show feedback
                    self.showSuccess($button, originalHtml);
                })
                .catch(function(error) {
                    // Failed - try fallback
                    console.warn('Lex Copy: Clipboard API failed, trying fallback', error);
                    self.copyWithFallback(text, $button, originalHtml);
                });
        },

        /**
         * Copy using fallback method (execCommand)
         * 
         * @param {string} text - Text to copy
         * @param {jQuery} $button - Button element
         * @param {string} originalHtml - Original button HTML
         */
        copyWithFallback: function(text, $button, originalHtml) {
            var self = this;
            
            // Create temporary textarea element
            var $textarea = $('<textarea>');
            
            // Add to page (hidden)
            $textarea.css({
                position: 'fixed',
                left: '-9999px',
                top: '-9999px'
            });
            
            $('body').append($textarea);
            
            // Set text and select it
            $textarea.val(text);
            $textarea.select();
            $textarea[0].setSelectionRange(0, text.length);
            
            try {
                // Try to copy
                var copied = document.execCommand('copy');
                
                if (copied) {
                    // Success - show feedback
                    this.showSuccess($button, originalHtml);
                } else {
                    // Failed - show error
                    this.showError($button, originalHtml);
                }
            } catch (error) {
                // Error - show error message
                console.error('Lex Copy: Fallback copy failed', error);
                this.showError($button, originalHtml);
            } finally {
                // Always remove temporary textarea
                $textarea.remove();
            }
        },

        /**
         * Show success feedback on button
         * 
         * @param {jQuery} $button - Button element
         * @param {string} originalHtml - Original button HTML to restore
         */
        showSuccess: function($button, originalHtml) {
            var self = this;
            
            // Update button appearance
            $button.addClass(COPIED_CLASS);
            $button.html(SUCCESS_MESSAGE);
            
            // Restore after delay
            setTimeout(function() {
                $button.removeClass(COPIED_CLASS);
                $button.html(originalHtml);
            }, FEEDBACK_DURATION);
        },

        /**
         * Show error feedback on button
         * 
         * @param {jQuery} $button - Button element
         * @param {string} originalHtml - Original button HTML to restore
         */
        showError: function($button, originalHtml) {
            var self = this;
            
            // Show error message
            $button.html(ERROR_MESSAGE);
            
            // Restore after delay
            setTimeout(function() {
                $button.html(originalHtml);
            }, FEEDBACK_DURATION);
        }
    };

    // ============================================
    // INITIALIZATION
    // ============================================
    
    // Start when page is ready
    $(document).ready(function() {
        LexCopy.init();
    });

    // ============================================
    // PUBLIC API
    // ============================================
    
    // Make LexCopy available globally for other code to use
    if (typeof window.LexSettings === 'undefined') {
        window.LexSettings = {};
    }
    window.LexSettings.copy = LexCopy;

})(jQuery);
