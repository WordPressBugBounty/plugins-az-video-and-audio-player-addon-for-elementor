/**
 * ═══════════════════════════════════════════
 * ═══ LEX SETTINGS CORE FUNCTIONALITY
 * ═══════════════════════════════════════════
 * 
 * PURPOSE: Core settings page functionality including:
 * - Tab switching and navigation
 * - AJAX save/reset actions
 * - Form handling
 * - Action buttons (Export, Import, Reset, etc.)
 */

// --------------------------------------------
// ─── GLOBAL LOG FUNCTION (Public API)
// --------------------------------------------

/**
 * Global logging function for Lex Settings framework
 * Available throughout the entire framework as a public API
 * 
 * @param {string} message - Log message
 * @param {*} data - Optional data to log
 * 
 * @example
 * lexLog('Settings saved');
 * lexLog('User action', { userId: 123, action: 'save' });
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

window.lexLog = function(message, data) {
    var lexSettings = getLexSettingsGlobal();
    // Check if lexSettings exists and debug mode is enabled
    if (!lexSettings || !lexSettings.debugMode) {
        return;
    }
    
    if (data !== undefined) {
        console.log('[Lex Settings] ' + message, data);
    } else {
        console.log('[Lex Settings] ' + message);
    }
};

// Also expose via LexSettings namespace for backward compatibility
if (typeof window.LexSettings === 'undefined') {
    window.LexSettings = {};
}
window.LexSettings.log = window.lexLog;

;(function($) {
    'use strict';
    
    // Get the dynamic settings global
    var lexSettings = getLexSettingsGlobal();
    
    if (!lexSettings) {
        console.warn('Lex Settings: Localized parameters not found.');
        return;
    }

    // --------------------------------------------
    // ─── CONSTANTS
    // --------------------------------------------
    
    const SCROLL_DURATION = 300;
    const SCROLL_OFFSET = 32;
    const RELOAD_DELAY = 1000;

    // --------------------------------------------
    // ─── MAIN OBJECT
    // --------------------------------------------
    
    const lexSettingsCore = {
        
        // Store media frames to reuse across function calls
        mediaFrames: {},
        
        /**
         * Initialize all functionality
         */
        init() {
            // Tab switching - event handlers
            $('body').on('click', '.lex-settings-tabs__tab, .lex-nav-header__item, .lex-nav-header__dropdown-item', this.onTabClick.bind(this));
            
            // Handle initial hash on page load (runs once, not an event handler)
            this.handleInitialHash();

            // Listen for hash changes (e.g., from menu links or browser back/forward)
            $(window).on('hashchange', () => {
                this.handleInitialHash();
            });

            // AJAX handlers
            $('body').on('click', '.lex-action-btn, .lex-settings-section__save-btn, button[data-action]', this.onActionButtonClick.bind(this));
            $('body').on('submit', '.lex-settings-form', this.onFormSubmit.bind(this));

            // Action buttons
            $('body').on('click', '.lex-btn--reset-section', this.handleResetSection.bind(this));
            // Note: Custom action buttons (like #lex-test-connection, #lex-clear-transients) 
            // are now handled by the generic AJAX system via onActionButtonClick -> executeAction -> plugin's wp_ajax_* handlers

            // Color picker initialization is handled by handleInitialHash()
            // which runs after tabs are set up, ensuring proper timing

            // Initialize sortable checkboxes
            this.initSortableCheckboxes();
            
            // Initialize master checkbox functionality
            this.initMasterCheckboxes();
            
            lexLog('Lex Settings Core script initialized');
            
            // Debug: Log all current settings on page load
            if (lexSettings.debugMode && lexSettings.allSettings) {
                lexLog('Current all settings:', lexSettings.allSettings);
            }
        },

        // ────────────────────────────────────────
        // ─── Tab Switching
        // ────────────────────────────────────────
        
        /**
         * Handle initial URL hash on page load
         * 
         * WHY: Runs once on initialization, not an event handler
         * Checks if URL has hash (#tab-name) and activates that tab
         */
        handleInitialHash() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                const $targetTab = $(`.lex-settings-tabs__tab[data-tab="${hash}"], .lex-nav-header__item[data-tab="${hash}"], .lex-nav-header__dropdown-item[data-tab="${hash}"]`);
                if ($targetTab.length) {
                    // Activate tab without smooth scroll on page load
                    this.activateTab($targetTab, false);
                }
            }
            
            // Initialize color pickers immediately (no delay needed - WordPress color picker is synchronous)
            this.initColorPickers();
            
            // Initialize media fields
            this.initMediaFields();
            
            // Initialize Select2 on multi-select fields
            this.initSelect2();
        },

        /**
         * Activate a tab - can be called with or without smooth scroll
         * 
         * @param {jQuery} $tabElement The tab element to activate
         * @param {boolean} shouldScroll Whether to trigger smooth scroll
         */
        activateTab($tabElement, shouldScroll) {
            const targetTab = $tabElement.data('tab');
            if (!targetTab) return;

            const $tabContents = $('.lex-settings-tabs__content');

            // Remove active class from ALL navigation items (both styles)
            $('.lex-settings-tabs__tab').removeClass('lex-settings-tabs__tab--active');
            $('.lex-nav-header__item').removeClass('lex-nav-header__item--active');

            // Hide all tab contents
            $tabContents.removeClass('lex-settings-tabs__content--active');

            // Add active class to clicked item based on its type
            if ($tabElement.hasClass('lex-settings-tabs__tab')) {
                $tabElement.addClass('lex-settings-tabs__tab--active');
            } else if ($tabElement.hasClass('lex-nav-header__item')) {
                $tabElement.addClass('lex-nav-header__item--active');
            } else if ($tabElement.hasClass('lex-nav-header__dropdown-item')) {
                // Mark the "More" button as active when dropdown item is clicked
                const $moreButton = $('.lex-nav-header__dropdown-toggle');
                if ($moreButton.length) {
                    $moreButton.addClass('lex-nav-header__item--active');
                }
            }

            // Show target tab content
            const $targetContent = $('#lex-tab-' + targetTab + ', #tab-' + targetTab);
            if ($targetContent.length) {
                $targetContent.addClass('lex-settings-tabs__content--active');

                // Initialize color pickers immediately after tab is shown (no delay needed)
                this.initColorPickers();
                
                // Initialize media fields after tab is shown
                this.initMediaFields();
                
                // Initialize Select2 after tab is shown
                this.initSelect2();

                // Smooth scroll ONLY when shouldScroll is true (user clicks)
                if (shouldScroll) {
                    this.scrollToContent();
                }
            }

            // Update URL hash
            window.location.hash = targetTab;
        },

        /**
         * Calculate scroll offset and scroll to main content
         */
        scrollToContent() {
            const $mainContent = $('.lex-main-content');
            if (!$mainContent.length) return;

            // Account for both WordPress admin bar and navigation header
            const $adminBar = $('#wpadminbar');
            const $navHeader = $('.lex-nav-header__container');
            const adminBarHeight = $adminBar.length ? $adminBar.outerHeight() : SCROLL_OFFSET;
            const navHeaderHeight = $navHeader.length ? $navHeader.outerHeight() : 0;
            const offset = adminBarHeight + navHeaderHeight + SCROLL_OFFSET;

            // Smooth scroll using jQuery animate with proper offset
            $('html, body').animate({
                scrollTop: $mainContent.offset().top - offset
            }, SCROLL_DURATION);
        },

        // ────────────────────────────────────────
        // ─── Event Handlers
        // ────────────────────────────────────────
        
        onTabClick(event) {
            event.preventDefault();
            this.activateTab($(event.currentTarget), true);
        },

        onActionButtonClick(event) {
            event.preventDefault();
            event.stopPropagation();

            const $button = $(event.currentTarget);
            
            // Skip reset section buttons - they have their own handler
            if ($button.hasClass('lex-btn--reset-section')) {
                return;
            }
            
            const action = $button.data('action') || 'save';
            const buttonText = $button.text().trim() || 'Save';
            const confirmMessage = $button.data('confirm');

            lexLog('Button clicked', {
                action: action,
                buttonText: buttonText,
                confirmMessage: confirmMessage
            });

            // Show confirmation dialog if needed
            if (confirmMessage) {
                if (!confirm(confirmMessage)) {
                    lexLog('Action cancelled by user');
                    return;
                }
            }

            // Store original text for restoration
            const originalText = $button.html();
            const originalButtonText = $button.text().trim();
            $button.data('original-text', originalButtonText);
            
            // Disable button during processing (shows loading spinner)
            this.disableButton($button);

            // Execute the action via AJAX
            this.executeAction(action, buttonText, $button, originalText);
        },

        onFormSubmit(event) {
            event.preventDefault();
            return false;
        },

        handleResetSection(event) {
            event.preventDefault();
            event.stopPropagation(); // Prevent bubbling to onActionButtonClick
            
            if (confirm('Reset this section to default values?')) {
                const $button = $(event.currentTarget);
                const originalText = $button.html();
                
                // Disable button during processing
                this.disableButton($button);
                
                // Execute the reset action (reuses existing AJAX logic)
                this.executeAction('reset', 'Reset Section', $button, originalText);
            }
            // If cancelled, function ends here and nothing happens
        },

        // ────────────────────────────────────────
        // ─── AJAX Operations
        // ────────────────────────────────────────
        
        /**
         * Execute action via AJAX
         * 
         * Handles both built-in actions (save/reset) and custom plugin actions.
         * Custom actions route directly to plugin's wp_ajax_* handlers.
         * 
         * @param {string} action Action type ('save', 'reset', or custom plugin action)
         * @param {string} buttonText Button text for logging
         * @param {jQuery} $button Button element
         * @param {string} originalText Original button HTML
         */
        executeAction(action, buttonText, $button, originalText) {
            lexLog('=== Starting executeAction ===', { action, buttonText });

            // Get current active tab
            const $activeTab = $('.lex-settings-tabs__content--active');
            
            if ($activeTab.length === 0) {
                lexLog('ERROR: No active tab found!');
                lexSettings.notifications.show('error', 'No active tab found');
                this.resetButton($button, originalText);
                return;
            }

            const tabId = $activeTab.attr('id').replace('lex-tab-', '');
            lexLog('Tab ID:', tabId);

            // Handle built-in actions (save/reset/reset_all/export_settings/import_settings)
            if (action === 'save' || action === 'reset') {
                this.executeBuiltInAction(action, tabId, $button, originalText);
                return;
            }
            
            if (action === 'reset_all') {
                this.executeResetAll($button, originalText);
                return;
            }
            
            if (action === 'export_settings') {
                this.executeExportSettings($button, originalText);
                return;
            }
            
            if (action === 'import_settings') {
                this.executeImportSettings($button, originalText);
                return;
            }

            // Handle custom plugin actions - route directly to plugin's AJAX handler
            this.executeCustomAction(action, tabId, $button, originalText);
        },

        /**
         * Execute built-in actions (save/reset)
         * 
         * @param {string} action Action type ('save' or 'reset')
         * @param {string} tabId Current tab ID
         * @param {jQuery} $button Button element
         * @param {string} originalText Original button HTML
         */
        executeBuiltInAction(action, tabId, $button, originalText) {
            const $activeTab = $('.lex-settings-tabs__content--active');
            const formData = this.collectTabFormData($activeTab);
            const fieldKeys = Object.keys(formData);
            // Use dynamic AJAX prefix from localized data
            const ajaxAction = lexSettings.ajaxPrefix + (action === 'save' ? 'save_tab' : 'reset_tab');
            
            // Debug: Log fields to reset and their default values when debug mode is on
            if (action === 'reset' && lexSettings.debugMode) {
                lexLog('Fields to reset:', fieldKeys);
                lexLog('Total fields to reset:', fieldKeys.length);
                
                // Get default values for each field (predicted values before reset)
                if (lexSettings.defaultSettings) {
                    const resetValues = {};
                    fieldKeys.forEach(key => {
                        resetValues[key] = this.getDefaultValue(key, lexSettings.defaultSettings);
                    });
                    lexLog('Predicted reset values (expected defaults before reset):', resetValues);
                }
            }
            
            const ajaxData = {
                action: ajaxAction,
                tab_id: tabId,
                nonce: lexSettings.nonce
            };

            if (action === 'save') {
                ajaxData.field_data = JSON.stringify(formData);
            } else if (action === 'reset') {
                ajaxData.field_keys = JSON.stringify(fieldKeys);
            }

            lexLog('Built-in action AJAX data prepared:', ajaxData);

            $.ajax({
                url: lexSettings.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: (response) => {
                    this.handleAjaxSuccess(response, action, $button, originalText);
                },
                error: (xhr, status, error) => {
                    this.handleAjaxError(xhr, status, error, $button, originalText);
                }
            });
        },

        /**
         * Execute reset all settings action
         * 
         * @param {jQuery} $button Button element
         * @param {string} originalText Original button HTML
         */
        executeResetAll($button, originalText) {
            const ajaxData = {
                action: lexSettings.ajaxPrefix + 'reset_all',
                nonce: lexSettings.nonce
            };

            lexLog('Reset all AJAX data prepared:', ajaxData);

            $.ajax({
                url: lexSettings.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: (response) => {
                    this.handleAjaxSuccess(response, 'reset_all', $button, originalText);
                },
                error: (xhr, status, error) => {
                    this.handleAjaxError(xhr, status, error, $button, originalText);
                }
            });
        },

        /**
         * Execute export settings action
         * 
         * @param {jQuery} $button Button element
         * @param {string} originalText Original button HTML
         */
        executeExportSettings($button, originalText) {
            const ajaxData = {
                action: lexSettings.ajaxPrefix + 'export_settings',
                nonce: lexSettings.nonce
            };

            lexLog('Export settings AJAX data prepared:', ajaxData);

            // Disable button during processing
            this.disableButton($button);

            $.ajax({
                url: lexSettings.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: (response) => {
                    this.handleAjaxSuccess(response, 'export_settings', $button, originalText);
                },
                error: (xhr, status, error) => {
                    this.handleAjaxError(xhr, status, error, $button, originalText);
                }
            });
        },

        /**
         * Execute import settings action
         * 
         * @param {jQuery} $button Button element
         * @param {string} originalText Original button HTML
         */
        executeImportSettings($button, originalText) {
            const $importFile = $('#lex-import-file');
            
            // Validate file was selected
            if (!$importFile.length || !$importFile[0].files.length) {
                lexSettings.notifications.show('error', lexSettings.i18n.selectFileToImport);
                this.resetButton($button, originalText);
                return;
            }

            const file = $importFile[0].files[0];
            
            // Validate file type
            if (!file.name.toLowerCase().endsWith('.json')) {
                lexSettings.notifications.show('error', 'Invalid file type. Please select a JSON file.');
                this.resetButton($button, originalText);
                return;
            }

            // Prepare FormData for file upload
            const importAction = lexSettings.ajaxPrefix + 'import_settings';
            const formData = new FormData();
            formData.append('action', importAction);
            formData.append('nonce', lexSettings.nonce);
            formData.append('import_file', file);

            lexLog('Import settings AJAX data prepared:', {
                action: importAction,
                filename: file.name,
                filesize: file.size
            });

            // Disable button during processing
            this.disableButton($button);

            $.ajax({
                url: lexSettings.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,  // Required for FormData
                contentType: false,   // Required for FormData
                success: (response) => {
                    this.handleAjaxSuccess(response, 'import_settings', $button, originalText);
                },
                error: (xhr, status, error) => {
                    this.handleAjaxError(xhr, status, error, $button, originalText);
                }
            });
        },

        /**
         * Execute custom plugin action
         * 
         * Routes directly to plugin's wp_ajax_* handler.
         * Plugin must register: add_action('wp_ajax_my_action', 'my_handler');
         * 
         * @param {string} action Custom action name (plugin's AJAX action)
         * @param {string} tabId Current tab ID
         * @param {jQuery} $button Button element
         * @param {string} originalText Original button HTML
         */
        executeCustomAction(action, tabId, $button, originalText) {
            lexLog('Executing custom action:', action);

            // Collect form data (plugins may need it)
            const $activeTab = $('.lex-settings-tabs__content--active');
            const formData = this.collectTabFormData($activeTab);

            // Prepare AJAX data - route directly to plugin's AJAX handler
            // Plugin registers: add_action('wp_ajax_' + action, 'handler_function')
            const ajaxData = {
                action: action,  // Direct AJAX action name (wp_ajax_*)
                tab_id: tabId,
                nonce: lexSettings.nonce,  // Framework provides nonce
                form_data: JSON.stringify(formData)  // Optional: pass form data
            };

            lexLog('Custom action AJAX data:', ajaxData);

            $.ajax({
                url: lexSettings.ajaxurl,
                type: 'POST',
                data: ajaxData,
                success: (response) => {
                    this.handleAjaxSuccess(response, action, $button, originalText);
                },
                error: (xhr, status, error) => {
                    this.handleAjaxError(xhr, status, error, $button, originalText);
                }
            });
        },

        /**
         * Handle AJAX success response
         * 
         * @param {object} response AJAX response
         * @param {string} action Action type
         * @param {jQuery} $button Button element
         * @param {string} originalText Original button HTML
         */
        handleAjaxSuccess(response, action, $button, originalText) {
            lexLog('AJAX success response received:', response);
            
            if (response.success) {
                lexLog('Server response success:', response.data.message);
                
                // Show info notification if no change occurred (for both save and reset)
                if (response.data.changed === false) {
                    lexSettings.notifications.show('info', response.data.message);
                } else {
                    lexSettings.notifications.show('success', response.data.message);
                }

                // Update form values on successful save
                if (action === 'save') {
                    lexLog('Save completed successfully');
                } else if (action === 'reset') {
                    lexLog('Reset completed, reloading page');
                    
                    // Debug: Log actual reset values returned from server
                    if (lexSettings.debugMode && response.data.reset_values) {
                        lexLog('Actual reset values (from server after save):', response.data.reset_values);
                    }
                    
                    // Skip reload in debug mode to allow inspection
                    if (!lexSettings.debugMode) {
                        setTimeout(() => {
                            location.reload();
                        }, RELOAD_DELAY);
                    } else {
                        lexLog('Debug mode: Skipping page reload. Form values may need manual refresh.');
                    }
                } else if (action === 'reset_all') {
                    lexLog('Reset all completed');
                    
                    // Only reload if values actually changed
                    if (response.data.changed) {
                        lexLog('Settings changed, reloading page');
                        setTimeout(() => {
                            location.reload();
                        }, RELOAD_DELAY);
                    } else {
                        lexLog('No changes detected, skipping page reload');
                    }
                } else if (action === 'export_settings') {
                    lexLog('Export completed, triggering download');
                    
                    // Trigger file download
                    const jsonData = JSON.stringify(response.data.data, null, 2);
                    const blob = new Blob([jsonData], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = response.data.filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                    
                    lexLog('File download triggered:', response.data.filename);
                } else if (action === 'import_settings') {
                    lexLog('Import completed, reloading page');
                    
                    // Always reload page after import to show updated settings
                    setTimeout(() => {
                        location.reload();
                    }, RELOAD_DELAY);
                }
            } else {
                lexLog('Server response error:', response.data);
                lexSettings.notifications.show('error', response.data.message);
            }

            this.resetButton($button, originalText);
        },

        /**
         * Handle AJAX error response
         * 
         * @param {object} xhr XHR object
         * @param {string} status Status text
         * @param {string} error Error message
         * @param {jQuery} $button Button element
         * @param {string} originalText Original button HTML
         */
        handleAjaxError(xhr, status, error, $button, originalText) {
            lexLog('AJAX error occurred:', {
                status: status,
                error: error,
                responseText: xhr.responseText,
                responseJSON: xhr.responseJSON
            });

            let errorMessage = 'Request failed';
            if (xhr.responseJSON && xhr.responseJSON.data) {
                errorMessage = xhr.responseJSON.data.message;
            }
            lexSettings.notifications.show('error', errorMessage);

            this.resetButton($button, originalText);
        },

        // ────────────────────────────────────────
        // ─── Form Data Collection
        // ────────────────────────────────────────
        
        /**
         * Collect form data from a specific tab
         * 
         * @param {jQuery} $tabElement Tab element to collect data from
         * @returns {object} Form data object
         */
        collectTabFormData($tabElement) {
            const formData = {};
            const $inputs = $tabElement.find('input, select, textarea');

            $inputs.each((index, element) => {
                this.processFormInput($(element), formData);
            });

            return formData;
        },

        /**
         * Get default value for a field key (supports dot notation)
         * 
         * @param {string} key Field key (flat or dot notation)
         * @param {object} defaults Default settings object
         * @returns {*} Default value or null
         */
        getDefaultValue(key, defaults) {
            if (!defaults || typeof defaults !== 'object') {
                return null;
            }
            
            // Check if key contains dot notation
            if (key.indexOf('.') === -1) {
                // Flat key - direct lookup
                return defaults.hasOwnProperty(key) ? defaults[key] : null;
            }
            
            // Dot notation - traverse nested object
            const keys = key.split('.');
            let value = defaults;
            
            for (let i = 0; i < keys.length; i++) {
                if (value && typeof value === 'object' && value.hasOwnProperty(keys[i])) {
                    value = value[keys[i]];
                } else {
                    return null;
                }
            }
            
            return value;
        },

        /**
         * Process a single form input and add to formData object
         * 
         * @param {jQuery} $input Input element
         * @param {object} formData Form data object to update
         */
        processFormInput($input, formData) {
            const name = $input.attr('name');
            if (!name) return;

            const inputType = $input.attr('type');
            const tagName = $input.prop('tagName') ? $input.prop('tagName').toLowerCase() : '';

            if (inputType === 'checkbox') {
                this.processCheckboxInput($input, formData);
            } else if (inputType === 'radio') {
                this.processRadioInput($input, formData);
            } else if (tagName === 'select' && $input.prop('multiple')) {
                // Multi-select: handle name[] pattern
                this.processMultiSelectInput($input, formData);
            } else {
                // Text, number, single select, textarea
                formData[name] = $input.val();
            }
        },

        /**
         * Handle checkbox input processing
         * 
         * @param {jQuery} $input Checkbox input element
         * @param {object} formData Form data object to update
         */
        processCheckboxInput($input, formData) {
            const name = $input.attr('name');
            const checked = $input.prop('checked');
            const value = $input.val();
            const isArrayField = name && name.endsWith('[]');

            lexLog('Processing checkbox:', {
                name: name,
                checked: checked,
                value: value,
                is_array: isArrayField
            });

            if (isArrayField) {
                // Array checkbox field
                const arrayKey = name.slice(0, -2); // Remove [] from end

                if (!formData[arrayKey]) {
                    formData[arrayKey] = [];
                }

                if (checked) {
                    formData[arrayKey].push(value);
                }
            } else {
                // Single checkbox field
                formData[name] = checked ? '1' : '0';
            }
        },

        /**
         * Handle radio button input processing
         * 
         * @param {jQuery} $input Radio input element
         * @param {object} formData Form data object to update
         */
        processRadioInput($input, formData) {
            const name = $input.attr('name');
            const checked = $input.prop('checked');
            const value = $input.val() || '';

            lexLog('Processing radio button:', {
                name: name,
                value: value,
                checked: checked
            });

            if (checked && value !== '') {
                formData[name] = value;
            }
        },

        /**
         * Handle multi-select input processing
         * 
         * @param {jQuery} $input Select input element with multiple attribute
         * @param {object} formData Form data object to update
         */
        processMultiSelectInput($input, formData) {
            const name = $input.attr('name');
            const values = $input.val() || []; // jQuery .val() returns array for multi-select
            const isArrayField = name && name.endsWith('[]');

            lexLog('Processing multi-select:', {
                name: name,
                values: values,
                is_array: isArrayField
            });

            if (isArrayField) {
                // Remove [] from name for formData key
                const arrayKey = name.slice(0, -2);
                formData[arrayKey] = Array.isArray(values) ? values : [];
            } else {
                // Fallback: use name as-is
                formData[name] = Array.isArray(values) ? values : [];
            }
        },

        // ────────────────────────────────────────
        // ─── Color Picker Initialization
        // ────────────────────────────────────────
        
        /**
         * Initialize WordPress color picker on all color fields
         * 
         * WHY SIMPLE: WordPress color picker handles most things internally:
         * - Skips already-initialized fields automatically
         * - Works fine with hidden elements (no visibility check needed)
         * 
         * We need to:
         * 1. Find visible color picker fields
         * 2. Initialize them with wpColorPicker()
         * 3. Pass the disabled state from the input element
         * 4. Manually disable the button element (WordPress doesn't always handle this properly)
         */
        initColorPickers() {
            // Early return if WordPress color picker is not available
            if (!$.fn.wpColorPicker) {
                return;
            }

            // Find all visible color picker fields
            // Note: We filter by visibility to avoid initializing hidden fields unnecessarily
            const $fields = $('.lex-color-picker').filter(function () {
                return $(this).is(':visible');
            });

            // Initialize each field
            $fields.each(function () {
                const $el = $(this);
                const isDisabled = $el.prop('disabled');

                // Initialize WordPress color picker
                // - defaultColor: Use field value if valid, otherwise false (no default color)
                // - disabled: Pass the disabled state from the input element
                $el.wpColorPicker({
                    defaultColor: this.value || false, // false = no default color
                    disabled: isDisabled,
                });

                // Additional safety: Manually disable button if field is disabled
                // WordPress doesn't always properly disable the button element when disabled option is passed
                if (isDisabled) {
                    const $pickerButton = $el.closest('.wp-picker-container').find('.wp-color-result');
                    if ($pickerButton.length) {
                        $pickerButton
                            .prop('disabled', true)
                            .css('pointer-events', 'none')
                            .css('cursor', 'not-allowed')
                            .off('click');
                    }
                }
            });
        },

        /**
         * Initialize WordPress Media fields
         * 
         * Creates wp.media instances for fields with data-media-field="true"
         * Stores attachment ID and updates preview URL
         * Includes remove button functionality
         */
        initMediaFields() {
            // Early return if wp.media is not available
            if (typeof wp === 'undefined' || !wp.media) {
                return;
            }

            // Find all media field buttons that haven't been initialized
            $('[data-media-button="true"]').not('.lex-media-field-initialized').each(function() {
                const $button = $(this);
                const targetInputId = $button.data('target-input');
                const $hiddenInput = $('#' + targetInputId);
                const $previewInput = $('#' + targetInputId + '_preview');
                const $removeButton = $('#' + targetInputId + '_remove');
                const $previewImageWrapper = $('#' + targetInputId + '_preview_wrapper');
                const $previewImage = $('#' + targetInputId + '_preview_image');
                
                if (!$hiddenInput.length || !$previewInput.length) {
                    lexLog('Media field: Target inputs not found', targetInputId);
                    return;
                }

                // Mark as initialized to prevent duplicate handlers
                $button.addClass('lex-media-field-initialized');
                if ($removeButton.length) {
                    $removeButton.addClass('lex-media-field-initialized');
                }

                // Get library type from hidden input data attribute
                let libraryType = ['image']; // Default to images
                const libraryTypeData = $hiddenInput.data('library-type');
                if (libraryTypeData) {
                    try {
                        libraryType = typeof libraryTypeData === 'string' 
                            ? JSON.parse(libraryTypeData) 
                            : libraryTypeData;
                    } catch (e) {
                        lexLog('Error parsing library type', e);
                    }
                }

                // Create unique frame key
                const frameKey = targetInputId;

                // Handle select button click
                $button.on('click', function(e) {
                    e.preventDefault();

                    // Reuse existing frame if available
                    if (lexSettingsCore.mediaFrames[frameKey]) {
                        lexSettingsCore.mediaFrames[frameKey].open();
                        return;
                    }

                    // Create new media frame
                    lexSettingsCore.mediaFrames[frameKey] = wp.media({
                        title: 'Select Media File',
                        button: {
                            text: 'Use this file'
                        },
                        library: {
                            type: libraryType
                        },
                        multiple: false
                    });

                    // Pre-select current attachment if one exists
                    const currentAttachmentId = $hiddenInput.val();
                    if (currentAttachmentId) {
                        const attachment = wp.media.attachment(currentAttachmentId);
                        lexSettingsCore.mediaFrames[frameKey].on('open', function() {
                            attachment.fetch().done(function() {
                                const selection = lexSettingsCore.mediaFrames[frameKey].state().get('selection');
                                selection.add([attachment]);
                            });
                        });
                    }

                    // Handle selection
                    lexSettingsCore.mediaFrames[frameKey].on('select', function() {
                        const attachment = lexSettingsCore.mediaFrames[frameKey].state().get('selection').first().toJSON();
                        
                        // Store attachment ID in hidden input
                        $hiddenInput.val(attachment.id).trigger('change');
                        
                        // Update preview URL
                        $previewInput.val(attachment.url);
                        
                        // Update image preview if it's an image field
                        if ($previewImageWrapper.length && $previewImage.length) {
                            if (attachment.type === 'image' && attachment.sizes && attachment.sizes.thumbnail) {
                                $previewImage.attr('src', attachment.sizes.thumbnail.url);
                                $previewImageWrapper.show();
                            } else if (attachment.type === 'image') {
                                // Fallback to full image if thumbnail not available
                                $previewImage.attr('src', attachment.url);
                                $previewImageWrapper.show();
                            } else {
                                $previewImageWrapper.hide();
                            }
                        }
                        
                        // Enable remove button
                        if ($removeButton.length) {
                            $removeButton.prop('disabled', false);
                        }
                    });

                    // Open the frame
                    lexSettingsCore.mediaFrames[frameKey].open();
                });

                // Handle preview image click (opens media library)
                if ($previewImage.length && $previewImageWrapper.length) {
                    $previewImage.on('click', function(e) {
                        e.preventDefault();
                        
                        // Trigger the button click to open media library
                        $button.trigger('click');
                    });
                }

                // Handle remove button click
                if ($removeButton.length) {
                    $removeButton.on('click', function(e) {
                        e.preventDefault();
                        
                        // Clear hidden input (attachment ID)
                        $hiddenInput.val('').trigger('change');
                        
                        // Clear preview URL
                        $previewInput.val('');
                        
                        // Hide image preview
                        if ($previewImageWrapper.length) {
                            $previewImageWrapper.hide();
                        }
                        
                        // Disable remove button
                        $removeButton.prop('disabled', true);
                    });
                }

                // Enable/disable remove button based on initial value
                if ($removeButton.length) {
                    const currentValue = $hiddenInput.val();
                    if (!currentValue || currentValue === '') {
                        $removeButton.prop('disabled', true);
                    }
                }
            });
        },

        /**
         * Initialize Select2 on multi-select fields
         * 
         * WHY: Enhances native multi-select dropdowns with better UX
         * - Search/filter functionality
         * - Better styling
         * - Optgroup support
         */
        initSelect2() {
            // Early return if Select2 is not available
            if (!$.fn.select2) {
                return;
            }

            // Find all multi-select fields that haven't been initialized
            $('select[multiple]').not('.select2-hidden-accessible').each(function() {
                const $select = $(this);
                
                // Skip if already initialized
                if ($select.data('select2')) {
                    return;
                }
                
                // Initialize Select2
                // Check data attributes for per-field configuration for future use
                const closeOnSelect = $select.data('close-on-select') === true; // Default false (keep open)
                
                $select.select2({
                    width: '75%',
                    closeOnSelect: false,  // Keep dropdown open after selection (default: false)
                    theme: 'lex'
                });
            });
        },

        // ────────────────────────────────────────
        // ─── Utilities
        // ────────────────────────────────────────

        /**
         * Reset button to original state
         * 
         * @param {jQuery} $button Button element
         * @param {string} originalText Original button HTML
         */
        resetButton($button, originalText) {
            // Use stored original HTML if available, otherwise use passed parameter
            const textToRestore = $button.data('original-html') || originalText;
            $button
                .prop('disabled', false)
                .css('opacity', '1')
                .html(textToRestore)
                .removeData('original-html')
                .removeData('original-text');
        },

        /**
         * Disable button during processing with loading indicator
         * 
         * @param {jQuery} $button Button element
         */
        disableButton($button) {
            // Store original HTML if not already stored
            if (!$button.data('original-html')) {
                $button.data('original-html', $button.html());
            }
            
            // Get loading type from button data attribute
            // Options: 'text-only' (text changes only) or 'spinner-text' (spinner + text, default)
            const loadingType = $button.data('loading-type') || 'spinner-text';
            
            // Get action type to determine loading text
            const action = $button.data('action') || 'save';
            
            // Map actions to loading text
            const loadingTexts = {
                'save': lexSettings.i18n?.saving || 'Saving...',
                'reset': lexSettings.i18n?.resetting || 'Resetting...',
                'reset_all': lexSettings.i18n?.resetting || 'Resetting...',
                'export_settings': lexSettings.i18n?.exporting || 'Exporting...',
                'import_settings': lexSettings.i18n?.importing || 'Importing...',
            };
            
            // Get button text - prefer stored original text, otherwise extract from button
            const originalButtonText = $button.data('original-text') || $button.text().trim() || 'Processing...';
            
            // Build loading HTML based on loading type
            let loadingHtml;
            if (loadingType === 'text-only') {
                // Text-only: just change text (e.g., "Save" → "Saving...")
                loadingHtml = loadingTexts[action] || lexSettings.i18n?.processing || 'Processing...';
            } else {
                // Spinner-text: spinner + original text (e.g., spinner + "Save Changes")
                loadingHtml = '<span class="spinner is-active" style="float: none; margin: 0 5px 0 0; vertical-align: middle;"></span> ' + originalButtonText;
            }
            
            // Add loading state and disable
            $button
                .prop('disabled', true)
                .css('opacity', '0.7')
                .html(loadingHtml);
        },

        /**
         * Initialize sortable checkbox functionality
         * Enables drag-and-drop reordering of checkbox items
         */
        initSortableCheckboxes() {
            const self = this;
            let draggedElement = null;

            // Initialize all sortable containers
            $('.lex-sortable-checkbox-container').each(function() {
                const $container = $(this);
                const $items = $container.find('.lex-sortable-checkbox-item');

                $items.each(function() {
                    const $item = $(this);

                    // Drag start
                    $item.on('dragstart', function(e) {
                        draggedElement = this;
                        $(this).addClass('dragging');
                        e.originalEvent.dataTransfer.effectAllowed = 'move';
                        e.originalEvent.dataTransfer.setData('text/html', this.innerHTML);
                    });

                    // Drag end
                    $item.on('dragend', function() {
                        $(this).removeClass('dragging');
                        $(this).closest('.lex-sortable-checkbox-container').find('.lex-sortable-checkbox-item').removeClass('drag-over');
                        draggedElement = null;
                    });

                    // Drag over
                    $item.on('dragover', function(e) {
                        if (e.preventDefault) {
                            e.preventDefault();
                        }
                        e.originalEvent.dataTransfer.dropEffect = 'move';
                        return false;
                    });

                    // Drag enter
                    $item.on('dragenter', function(e) {
                        if (draggedElement && draggedElement !== this) {
                            $(this).addClass('drag-over');
                        }
                    });

                    // Drag leave
                    $item.on('dragleave', function() {
                        $(this).removeClass('drag-over');
                    });

                    // Drop
                    $item.on('drop', function(e) {
                        if (e.stopPropagation) {
                            e.stopPropagation();
                        }

                        if (draggedElement && draggedElement !== this) {
                            const $dragged = $(draggedElement);
                            const $target = $(this);
                            const $container = $target.parent();

                            if ($dragged.index() < $target.index()) {
                                $target.after($dragged);
                            } else {
                                $target.before($dragged);
                            }

                            // Update hidden order field with current DOM order
                            self.updateSortableOrder($container);
                            
                            lexLog('Checkbox order updated');
                        }

                        $(this).removeClass('drag-over');
                        return false;
                    });
                });
                
                // Initialize order field on page load
                self.updateSortableOrder($container);
            });
        },

        /**
         * Update the hidden order field for a sortable container
         * Stores the order of all items (checked and unchecked) as comma-separated values
         * 
         * @param {jQuery} $container The container element
         */
        updateSortableOrder($container) {
            const $orderInput = $container.find('.lex-sortable-order-field');
            if ($orderInput.length) {
                const order = [];
                $container.find('.lex-sortable-checkbox-item').each(function() {
                    const value = $(this).attr('data-value');
                    if (value) {
                        order.push(value);
                    }
                });
                $orderInput.val(order.join(','));
                lexLog('Order field updated:', order);
            }
        },

        /**
         * Initialize master checkbox functionality for multiple checkbox fields
         * Simple select all / deselect all functionality
         */
        initMasterCheckboxes() {
            // Handle master checkbox click - select/deselect all
            $('.lex-checkbox-master').on('change', function() {
                const $master = $(this);
                const fieldName = $master.data('field-name');
                const isChecked = $master.prop('checked');
                const $checkboxes = $('input.lex-checkbox-item[data-master-field="' + fieldName + '"]:not(:disabled)');
                
                $checkboxes.prop('checked', isChecked);
                
                lexLog('Master checkbox toggled:', { field: fieldName, checked: isChecked });
            });
        }
    };

    // --------------------------------------------
    // ─── INITIALIZATION
    // --------------------------------------------
    
    $(document).ready(function() {
        lexSettingsCore.init();
    });
    
})(jQuery);
