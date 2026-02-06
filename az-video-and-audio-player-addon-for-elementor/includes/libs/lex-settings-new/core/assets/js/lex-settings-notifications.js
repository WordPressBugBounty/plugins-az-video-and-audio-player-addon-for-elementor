/**
 * Lex Settings Notification System
 *
 * Reusable notification system for displaying success and error messages
 * at the bottom-center of the screen.
 *
 * @example
 * // Success notification
 * lexSettings.notifications.show('success', 'Settings saved successfully!');
 *
 * @example
 * // Error notification
 * lexSettings.notifications.show('error', 'Failed to save settings');
 *
 * @example
 * // Using translations
 * lexSettings.notifications.show('success', lexSettings.i18n.settingsSaved);
 *
 * @example
 * // In AJAX success handler
 * $.ajax({
 *     success: function(response) {
 *         if (response.success) {
 *             lexSettings.notifications.show('success', response.data.message);
 *         } else {
 *             lexSettings.notifications.show('error', response.data.message);
 *         }
 *     }
 * });
 *
 * NOTE: Remove all example comments from this file before production deployment.
 *       Keep only the essential JSDoc comments for function documentation.
 */

/**
 * Find the Lex Settings global variable dynamically
 * WordPress localizes scripts with instance-specific variable names (e.g., myPluginSettings)
 * This function finds the variable by checking for expected properties
 * 
 * @returns {object|undefined} The settings object or undefined if not found
 */
function getLexSettingsGlobal() {
    // Check common patterns first (for backward compatibility)
    if (typeof window.lexSettings !== 'undefined' && window.lexSettings.ajaxurl) {
        return window.lexSettings;
    }
    
    // Search for variables ending in 'Settings' that have the expected properties
    for (var key in window) {
        if (key.endsWith('Settings') && window[key] && typeof window[key] === 'object') {
            var obj = window[key];
            if (obj.ajaxurl && obj.nonce && obj.ajaxPrefix !== undefined) {
                return obj;
            }
        }
    }
    
    return undefined;
}

(function($) {
    'use strict';

    /**
     * Private variable to track auto-dismiss timeout
     * @type {number|null}
     */
    var currentTimeout = null;

    /**
     * Notification API implementation
     * Defined separately so it can be attached to lexSettings whenever it becomes available
     */
    var notificationsAPI = {
        /**
         * Show a notification at the bottom-center of the screen
         *
         * @param {string} type - Notification type: 'success', 'error', or 'info'
         * @param {string} message - Message text to display (will be escaped for security)
         */
        show: function(type, message) {
            try {
                // Validate and normalize type
                if (type !== 'success' && type !== 'error' && type !== 'info') {
                    type = 'success'; // Default to success if invalid type
                }

                // Escape message to prevent XSS
                var escapedMessage = $('<div>').text(message).html();

                // Remove any existing notification
                removeExistingNotification();

                // Create and show new notification
                var $notice = createNotification(type, escapedMessage);
                setupDismissHandler($notice);
                setupAutoDismiss($notice);

                // Append to body and trigger animation
                $('body').append($notice);
                setTimeout(function() {
                    $notice.addClass('lex-notice-bottom--visible');
                }, 10);

            } catch (error) {
                // Error handling - log using global lexLog function
                if (typeof window.lexLog === 'function') {
                    lexLog('Error showing notification:', error);
                } else {
                    // Fallback if lexLog not available yet
                    console.error('[Lex Settings Notifications] Error showing notification:', error);
                }
            }
        }
    };

    /**
     * Initialize notifications object on lexSettings
     * Handles case where lexSettings might not exist yet (localized to different script)
     * This function can be called multiple times safely
     */
    function initializeNotifications() {
        // Find the dynamic settings global
        var lexSettings = getLexSettingsGlobal();
        
        if (!lexSettings) {
            // If not found, try to create a fallback (for backward compatibility)
            if (typeof window.lexSettings === 'undefined') {
                window.lexSettings = {};
            }
            lexSettings = window.lexSettings;
        }

        // Always set notifications (safe to overwrite with same implementation)
        // This ensures it persists even if lexSettings gets recreated by WordPress localization
        lexSettings.notifications = notificationsAPI;
    }

    /**
     * Get or create notifications API
     * @returns {Object} The notifications API object
     */
    function getNotificationsAPI() {
        // Initialize if needed
        initializeNotifications();
        
        // Get the settings global
        var lexSettings = getLexSettingsGlobal();
        if (!lexSettings && typeof window.lexSettings !== 'undefined') {
            lexSettings = window.lexSettings;
        }
        
        return lexSettings ? lexSettings.notifications : notificationsAPI;
    }

    /**
     * Remove existing notification and clear any pending timeouts
     * @private
     */
    function removeExistingNotification() {
        // Clear any pending auto-dismiss timeout
        if (currentTimeout !== null) {
            clearTimeout(currentTimeout);
            currentTimeout = null;
        }

        // Remove existing notification element
        $('.lex-notice-bottom').remove();
    }

    /**
     * Create notification DOM element
     *
     * @param {string} type - Notification type: 'success', 'error', or 'info'
     * @param {string} message - Escaped message text
     * @returns {jQuery} jQuery object containing the notification element
     * @private
     */
    function createNotification(type, message) {
        // Determine icon based on type
        var icon = type === 'error' ? '×' : (type === 'info' ? 'ℹ' : '✓');

        // Get dismiss label from i18n or use default
        var lexSettings = getLexSettingsGlobal();
        var dismissLabel = (lexSettings && lexSettings.i18n && lexSettings.i18n.dismissNotice) 
            ? lexSettings.i18n.dismissNotice 
            : 'Dismiss';

        // Create notification markup
        var $notice = $('<div class="lex-notice-bottom lex-notice-bottom--' + type + '" role="alert" aria-live="polite" aria-atomic="true">' +
            '<div class="lex-notice-bottom__content">' +
            '<span class="lex-notice-bottom__icon">' + icon + '</span>' +
            '<span class="lex-notice-bottom__message">' + message + '</span>' +
            '</div>' +
            '<button type="button" class="lex-notice-bottom__dismiss" aria-label="' + dismissLabel + '">×</button>' +
            '</div>');

        return $notice;
    }

    /**
     * Setup dismiss button click handler
     *
     * @param {jQuery} $notice - jQuery object containing the notification element
     * @private
     */
    function setupDismissHandler($notice) {
        $notice.find('.lex-notice-bottom__dismiss').on('click', function() {
            // Clear timeout if notification is manually dismissed
            if (currentTimeout !== null) {
                clearTimeout(currentTimeout);
                currentTimeout = null;
            }

            // Hide and remove notification
            $notice.removeClass('lex-notice-bottom--visible');
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $notice.remove();
                });
            }, 100);
        });
    }

    /**
     * Setup auto-dismiss after 4 seconds
     *
     * @param {jQuery} $notice - jQuery object containing the notification element
     * @private
     */
    function setupAutoDismiss($notice) {
        // Clear any existing timeout
        if (currentTimeout !== null) {
            clearTimeout(currentTimeout);
        }

        // Set new timeout for auto-dismiss
        currentTimeout = setTimeout(function() {
            $notice.removeClass('lex-notice-bottom--visible');
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $notice.remove();
                });
            }, 300);
            currentTimeout = null;
        }, 4000);
    }

    // Initialize immediately when script loads
    initializeNotifications();

    // Also initialize when DOM is ready (in case lexSettings gets localized later)
    $(document).ready(function() {
        initializeNotifications();
    });

    // Additional safeguard: re-initialize after a short delay to catch late localization
    // This handles cases where WordPress localizes lexSettings after our script runs
    setTimeout(function() {
        initializeNotifications();
    }, 100);

})(jQuery);

