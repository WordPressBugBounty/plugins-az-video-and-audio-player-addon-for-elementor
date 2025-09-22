/**
 * Admin JavaScript for Video & Audio Player
 * 
 * Table of contents:
 * ==================
 * 1. Tab Navigation
 * 2. Copy to Clipboard
 * 3. Utility Functions
 * 4. Initialization
 */

(function($) {
    'use strict';

    /**
     * VAPFEM Admin Object
     */
    var VAPFEMAdmin = {
        
        /**
         * Initialize all admin functionality
         */
        init: function() {
            this.initTabNavigation();
            this.initCopyToClipboard();
            this.initTabLinks();
            this.initUrlPersistence();
        },

        /**
         * 1. Tab Navigation
         * Handle tab switching functionality
         */
        initTabNavigation: function() {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                var $this = $(this);
                var targetTab = $this.data('tab');
                
                // Switch to the target tab
                VAPFEMAdmin.switchToTab(targetTab);
                
                // Update URL hash without page reload
                var newHash = '#' + targetTab;
                if (window.location.hash !== newHash) {
                    // Use replaceState to avoid adding to browser history
                    history.replaceState(null, null, newHash);
                }
            });
        },

        /**
         * Tab link functionality (for reference links)
         */
        initTabLinks: function() {
            $('.vapfem-admin__tab-link').on('click', function(e) {
                e.preventDefault();
                
                var $this = $(this);
                var targetTab = $this.data('tab');
                
                // Switch to the target tab
                VAPFEMAdmin.switchToTab(targetTab);
                
                // Update URL hash without page reload
                var newHash = '#' + targetTab;
                if (window.location.hash !== newHash) {
                    // Use replaceState to avoid adding to browser history
                    history.replaceState(null, null, newHash);
                }
            });
        },

        /**
         * URL Persistence
         * Handle URL hash changes and tab persistence
         */
        initUrlPersistence: function() {
            // Handle initial page load
            this.handleHashChange();
            
            // Handle browser back/forward buttons
            $(window).on('hashchange', function() {
                VAPFEMAdmin.handleHashChange();
            });
        },

        /**
         * Handle hash change events
         */
        handleHashChange: function() {
            var hash = window.location.hash;
            
            if (hash) {
                // Check if it's a tab hash
                var tabHash = hash.replace('#support', '').replace('#', '');
                var validTabs = ['video-player', 'audio-player', 'elementor-integration', 'all-options'];
                
                if (validTabs.indexOf(tabHash) !== -1) {
                    VAPFEMAdmin.switchToTab(tabHash);
                    
                    // If there's a #support hash, scroll to support section
                    if (hash.indexOf('#support') !== -1) {
                        setTimeout(function() {
                            VAPFEMAdmin.scrollToElement('.vapfem-admin-support', 20);
                        }, 100);
                    }
                }
            } else {
                // If no hash, default to video-player tab
                VAPFEMAdmin.switchToTab('video-player');
            }
        },

        /**
         * Switch to a specific tab
         * @param {string} targetTab - Tab to switch to
         */
        switchToTab: function(targetTab) {
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $('.nav-tab[data-tab="' + targetTab + '"]').addClass('nav-tab-active');
            
            // Show target content
            $('.vapfem-tab-content').removeClass('vapfem-tab-content--active');
            $('#' + targetTab).addClass('vapfem-tab-content--active');
        },

        /**
         * 2. Copy to Clipboard
         * Handle copy functionality for shortcodes
         */
        initCopyToClipboard: function() {
            $('.vapfem-admin-copy-btn, .vapfem-copy-button').on('click', function() {
                var $btn = $(this);
                var textToCopy = $btn.data('copy');
                var originalText = $btn.text();

                VAPFEMAdmin.copyToClipboard(textToCopy, $btn, originalText);
            });
        },

        /**
         * Copy text to clipboard with fallback
         * @param {string} text - Text to copy
         * @param {jQuery} $button - Button element
         * @param {string} originalText - Original button text
         */
        copyToClipboard: function(text, $button, originalText) {
            // Create temporary textarea
            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();

            try {
                // Try modern clipboard API first
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text).then(function() {
                        VAPFEMAdmin.showCopySuccess($button, originalText);
                    }).catch(function() {
                        // Fallback to execCommand
                        VAPFEMAdmin.fallbackCopy(text, $button, originalText);
                    });
                } else {
                    // Fallback to execCommand
                    VAPFEMAdmin.fallbackCopy(text, $button, originalText);
                }
            } catch (err) {
                console.error('Copy failed:', err);
                VAPFEMAdmin.showCopyError($button, originalText);
            }

            $temp.remove();
        },

        /**
         * Fallback copy method using execCommand
         * @param {string} text - Text to copy
         * @param {jQuery} $button - Button element
         * @param {string} originalText - Original button text
         */
        fallbackCopy: function(text, $button, originalText) {
            try {
                var success = document.execCommand('copy');
                if (success) {
                    VAPFEMAdmin.showCopySuccess($button, originalText);
                } else {
                    VAPFEMAdmin.showCopyError($button, originalText);
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
                VAPFEMAdmin.showCopyError($button, originalText);
            }
        },

        /**
         * Show copy success feedback
         * @param {jQuery} $button - Button element
         * @param {string} originalText - Original button text
         */
        showCopySuccess: function($button, originalText) {
            $button.addClass('vapfem-copy-button--copied').text('Copied!');
            
            setTimeout(function() {
                $button.removeClass('vapfem-copy-button--copied').text(originalText);
            }, 2000);
        },

        /**
         * Show copy error feedback
         * @param {jQuery} $button - Button element
         * @param {string} originalText - Original button text
         */
        showCopyError: function($button, originalText) {
            $button.text('Select & Copy');
            
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        },

        /**
         * 3. Utility Functions
         * Helper functions for admin functionality
         */

        /**
         * Show notification message
         * @param {string} message - Message to show
         * @param {string} type - Type of notification (success, error, warning, info)
         */
        showNotification: function(message, type) {
            type = type || 'info';
            
            var $notification = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            
            $('.wrap').first().prepend($notification);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notification.fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        },

        /**
         * Smooth scroll to element
         * @param {string} selector - CSS selector
         * @param {number} offset - Offset from top
         */
        scrollToElement: function(selector, offset) {
            offset = offset || 0;
            var $target = $(selector);
            
            if ($target.length) {
                $('html, body').animate({
                    scrollTop: $target.offset().top - offset
                }, 500);
            }
        },

        /**
         * Debounce function
         * @param {Function} func - Function to debounce
         * @param {number} wait - Wait time in milliseconds
         * @param {boolean} immediate - Execute immediately
         */
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };

    /**
     * 4. Initialization
     * Initialize when document is ready
     */
    $(document).ready(function() {
        VAPFEMAdmin.init();
    });

    /**
     * Make VAPFEMAdmin available globally for debugging
     */
    window.VAPFEMAdmin = VAPFEMAdmin;

})(jQuery);