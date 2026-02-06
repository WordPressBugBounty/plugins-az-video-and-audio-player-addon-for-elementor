<?php
namespace LeanPL\Admin;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Lex Deactivation Feedback System
 * Version: 2.0.1
 * @textdomain vapfem
 */
class Deactivation_Feedback {

    /**
     * API endpoint URL and authentication key for feedback collection
     */
    const DATACENTER_URL = 'https://qqcwdsrfytuefaoitiqn.supabase.co';
    const DATACENTER_API_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InFxY3dkc3JmeXR1ZWZhb2l0aXFuIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjE5ODAxNDAsImV4cCI6MjA3NzU1NjE0MH0.aiJyKVXWdhnsnV2oHAX0940_TZ6YTd5d3P_w6GXV0m4';
    
    /**
     * Unique prefix for this plugin instance (e.g., 'myplugin')
     * Used to namespace all CSS, JS, and HTML to prevent conflicts
     */
    private $prefix;
    
    /**
     * Plugin basename (e.g., 'my-plugin/my-plugin.php')
     */
    private $plugin_basename;

    /**
     * Plugin slug (e.g., 'my-plugin')
     */
    private $plugin_slug;
    
    /**
     * Plugin version
     */
    private $plugin_version;

    /**
     * Controls visibility of the popup close icon
     */
    private $show_close_icon;

    /**
     * Reason variation type for A/B testing
     */
    private $reason_variation;

    /**
     * Debug mode - prevents actual deactivation for testing
     */
    private $debug_mode;

    /**
     * Plugin installation timestamp in ISO format
     */
    private $installed_time_iso;

    /**
     * Constructor
     * 
     * @param array $args Configuration array for deactivation feedback
     */
    public function __construct($args = []) {
        // Parse arguments with defaults
        $defaults = [
            'plugin_basename' => '',
            'plugin_slug' => '',
            'plugin_version' => '',
            'prefix' => 'lex',
            'show_close_icon' => true,
            'reason_variation' => 'low-friction',
            'debug_mode' => false,
            'installed_time_iso' => '',
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Validate required parameters
        if (empty($args['plugin_basename']) || empty($args['plugin_slug']) || empty($args['plugin_version']) || empty($args['prefix'])) {
            wp_die(
                esc_html__('Deactivation Feedback requires plugin_basename, plugin_slug, plugin_version, and prefix.', 'vapfem'),
                esc_html__('Missing Required Parameters', 'vapfem')
            );
        }
        
        // Validate and sanitize prefix (only alphanumeric and underscore, max 20 chars)
        $prefix = substr(preg_replace('/[^a-z0-9_]/', '', strtolower($args['prefix'])), 0, 20);
        if (empty($prefix)) {
            wp_die(
                esc_html__('Prefix must contain at least one alphanumeric character.', 'vapfem'),
                esc_html__('Invalid Prefix', 'vapfem')
            );
        }
        
        // Validate reason_variation
        $valid_variations = ['low-friction', 'medium-friction', 'high-friction', 'feature-focused'];
        if (!in_array($args['reason_variation'], $valid_variations, true)) {
            $args['reason_variation'] = 'low-friction';
        }
        
        // Set properties
        $this->prefix = $prefix;
        $this->plugin_basename = sanitize_text_field($args['plugin_basename']);
        $this->plugin_slug = sanitize_key($args['plugin_slug']);
        $this->plugin_version = sanitize_text_field($args['plugin_version']);
        $this->show_close_icon = (bool) $args['show_close_icon'];
        $this->reason_variation = sanitize_key($args['reason_variation']);
        $this->debug_mode = (bool) $args['debug_mode'];
        $this->installed_time_iso = sanitize_text_field($args['installed_time_iso']);
        
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Enqueue inline assets only on plugins page
     */
    public function enqueue_assets($hook) {
        // Only load on plugins.php page
        if ($hook !== 'plugins.php') {
            return;
        }
        
        // Enqueue jQuery (dependency)
        wp_enqueue_script('jquery');
        
        // Add inline JavaScript with unique handle
        $js_handle = $this->prefix . '_deactivation_feedback';
        wp_add_inline_script('jquery', $this->get_javascript(), 'after');
        
        // Add inline CSS
        wp_add_inline_style('wp-admin', $this->get_css());
        
        // Pass data to JavaScript with prefixed variable name
        wp_localize_script('jquery', $this->prefix . 'DeactivationFeedback', [
            'prefix' => $this->prefix,
            'pluginBasename' => $this->plugin_basename,
            'pluginSlug' => $this->plugin_slug,
            'pluginVersion' => $this->plugin_version,
            'reasonVariation' => $this->reason_variation,
            'debugMode' => $this->debug_mode,
            'apiUrl' => self::DATACENTER_URL,
            'apiKey' => self::DATACENTER_API_KEY,
            'wpVersion' => get_bloginfo('version'),
            'phpVersion' => PHP_VERSION,
            'installedTime' => $this->installed_time_iso,
            'modalHtml' => $this->get_modal_html(),
            'cookieName' => $this->prefix . '_feedback',
            'i18n' => [
                'sending' => __('Deactivating...', 'vapfem')
            ]
        ]);
    }
    
    /**
     * Get translatable deactivation reasons based on variation
     */
    private function get_reasons() {
        $variations = [
            'low-friction' => [
                'no-longer-needed' => [
                    'text' => __('I no longer need the plugin', 'vapfem'),
                    'input_placeholder' => false
                ],
                'just-testing' => [
                    'text' => __('I was just testing it out', 'vapfem'),
                    'input_placeholder' => false
                ],
                'temporary' => [
                    'text' => __("It's a temporary deactivation", 'vapfem'),
                    'input_placeholder' => false
                ],
                'switching-plugin' => [
                    'text' => __("I'm switching to a different plugin", 'vapfem'),
                    'input_placeholder' => __('Please share which plugin (optional)', 'vapfem')
                ],
                'not-working' => [
                    'text' => __("I couldn't get the plugin to work", 'vapfem'),
                    'input_placeholder' => __("We're sorry to hear. Can you let us know what didn't work? (optional)", 'vapfem')
                ]
            ],
            'medium-friction' => [
                'temporary' => [
                    'text' => __("It's a temporary deactivation", 'vapfem'),
                    'input_placeholder' => false
                ],
                'no-longer-needed' => [
                    'text' => __('I no longer need the plugin', 'vapfem'),
                    'input_placeholder' => false
                ],
                'switching-plugin' => [
                    'text' => __("I'm switching to a different plugin", 'vapfem'),
                    'input_placeholder' => __('Please share which plugin (optional)', 'vapfem')
                ],
                'not-working' => [
                    'text' => __("I couldn't get the plugin to work", 'vapfem'),
                    'input_placeholder' => __("We're sorry to hear. Can you let us know what didn't work? (optional)", 'vapfem')
                ],
                'missing-features' => [
                    'text' => __("The plugin doesn't have the features I need", 'vapfem'),
                    'input_placeholder' => __('What features are you looking for? (optional)', 'vapfem')
                ]
            ],
            'high-friction' => [
                'custom-reason' => [
                    'text' => __("I'm deactivating because", 'vapfem'),
                    'input_placeholder' => __('Help us to improve with a quick feedback (optional)', 'vapfem')
                ],
                'missing-features' => [
                    'text' => __("The plugin doesn't have the features I need", 'vapfem'),
                    'input_placeholder' => __('What features are you looking for? (optional)', 'vapfem')
                ],
                'not-working' => [
                    'text' => __("I couldn't get the plugin to work", 'vapfem'),
                    'input_placeholder' => __("We're sorry to hear. Can you let us know what didn't work? (optional)", 'vapfem')
                ],
                'switching-plugin' => [
                    'text' => __("I'm switching to a different plugin", 'vapfem'),
                    'input_placeholder' => __('Please share which plugin (optional)', 'vapfem')
                ],
                'temporary' => [
                    'text' => __("It's a temporary deactivation", 'vapfem'),
                    'input_placeholder' => false
                ]
            ],
            'feature-focused' => [
                'missing-features' => [
                    'text' => __("The plugin doesn't have the features I need", 'vapfem'),
                    'input_placeholder' => __('What features are you looking for? (optional)', 'vapfem')
                ],
                'no-longer-needed' => [
                    'text' => __('I no longer need the plugin', 'vapfem'),
                    'input_placeholder' => false
                ],
                'switching-plugin' => [
                    'text' => __("I'm switching to a different plugin", 'vapfem'),
                    'input_placeholder' => __('Please share which plugin (optional)', 'vapfem')
                ],
                'not-working' => [
                    'text' => __("I couldn't get the plugin to work", 'vapfem'),
                    'input_placeholder' => __("We're sorry to hear. Can you let us know what didn't work? (optional)", 'vapfem')
                ],
                'temporary' => [
                    'text' => __("It's a temporary deactivation", 'vapfem'),
                    'input_placeholder' => false
                ]
            ]
        ];
        
        return $variations[$this->reason_variation] ?? $variations['low-friction'];
    }
    
    /**
     * Build reasons HTML in PHP
     */
    private function build_reasons_html() {
        $html = '';
        $index = 0;
        $p = $this->prefix;
        
        foreach ($this->get_reasons() as $key => $data) {
            $checked = $index === 0 ? 'checked' : '';
            $selected = $index === 0 ? ' selected' : '';
            $text = esc_html($data['text']);
            
            $html .= '<div class="' . $p . '-deactivation-option' . $selected . '">' . "\n";
            $html .= '    <label>' . "\n";
            $html .= '        <input type="radio" name="' . $p . '_deactivation_reason_key" value="' . esc_attr($key) . '" data-text="' . esc_attr($data['text']) . '" ' . $checked . '>' . "\n";
            $html .= '        <span class="' . $p . '-deactivation-reason-text">' . $text . '</span>' . "\n";
            $html .= '    </label>' . "\n";
            
            if ($data['input_placeholder']) {
                $display = $index === 0 ? '' : ' style="display:none;"';
                $placeholder = esc_attr($data['input_placeholder']);
                $html .= '    <input type="text" class="' . $p . '-deactivation-comment-input" placeholder="' . $placeholder . '"' . $display . '>' . "\n";
            }
            
            $html .= '</div>' . "\n";
            $index++;
        }
        
        return $html;
    }
    
    /**
     * Get complete modal HTML template
     */
    private function get_modal_html() {
        $reasons_html = $this->build_reasons_html();
        $p = $this->prefix;
        
        $title = esc_html__('Quick Feedback', 'vapfem');
        $description = esc_html__('If you have a moment, please share why you are deactivating this plugin:', 'vapfem');
        $submit_btn = esc_html__('Submit & Deactivate', 'vapfem');
        $skip_link = esc_html__('Skip & Deactivate', 'vapfem');
        
        $close_icon_html = $this->show_close_icon
            ? '<span class="' . $p . '-deactivation-close">&times;</span>'
            : '';

        return <<<HTML
<div class="{$p}-deactivation-overlay"></div>
<div class="{$p}-deactivation-modal">
    <div class="{$p}-deactivation-header">
        {$close_icon_html}
        <h2>
            <span class="dashicons dashicons-testimonial"></span>
            {$title}
        </h2>
        <p>{$description}</p>
    </div>
    <form class="{$p}-deactivation-form">
        <div class="{$p}-deactivation-options">
{$reasons_html}
        </div>
        <div class="{$p}-deactivation-footer">
            <button type="submit" class="button button-primary button-large">
                {$submit_btn}
            </button>
            <a href="#" class="{$p}-deactivation-skip">{$skip_link}</a>
        </div>
    </form>
</div>
HTML;
    }
    
    /**
     * Get inline JavaScript code
     */
    private function get_javascript() {
        $var_name = $this->prefix . 'DeactivationFeedback';
        
        return <<<JS
(function($) {
    'use strict';

    var config = window.{$var_name};
    if (!config) {
        console.error('Deactivation feedback config not found');
        return;
    }

    var deactivateLink = '';
    var pluginBasename = config.pluginBasename;
    var prefix = config.prefix;
    var pendingRequest = null;
    var isDeactivating = false;
    
    // Custom logging function
    function lexLog(message, data) {
        if (!config.debugMode) {
            return;
        }
        
        if (data !== undefined) {
            console.log('[' + prefix + ' Feedback] ' + message, data);
        } else {
            console.log('[' + prefix + ' Feedback] ' + message);
        }
    }
    
    // Cookie helper functions
    function setCookie(name, value, days) {
        var expires = '';
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + value + expires + '; path=/';
    }
    
    function getCookie(name) {
        var target = name + '=';
        var cookiesArr = document.cookie.split(';');

        for (var i = 0; i < cookiesArr.length; i++) {
            var cookieStr = cookiesArr[i].trim();
            if (cookieStr.substring(0, target.length) === target) {
                return cookieStr.substring(target.length);
            }
        }
        return null;
    }
    
    lexLog('Script loaded. Plugin basename:', pluginBasename);
    
    // Intercept deactivate link click
    $(document).on('click', 'tr[data-plugin="' + pluginBasename + '"] .deactivate a', function(e) {
        e.preventDefault();
        lexLog('Deactivate link clicked');

        var href = $(this).attr('href');
        if (!href) {
            return;
        }

        deactivateLink = href;

        // Check cookies (skip check in debug mode)
        if (!config.debugMode) {
            var pluginCookie = getCookie(config.cookieName);
            
            if (pluginCookie) {
                lexLog('Cookie exists, skipping modal');
                window.location.href = deactivateLink;
                return;
            }
        }

        showModal();
    });
    
    function showModal() {
        $('body').append(config.modalHtml);
        
        setTimeout(function() {
            $('.' + prefix + '-deactivation-overlay, .' + prefix + '-deactivation-modal').addClass('active');
            $('.' + prefix + '-deactivation-option.selected .' + prefix + '-deactivation-comment-input').focus();
        }, 100);
    }
    
    // Make entire option box clickable
    $(document).on('click', '.' + prefix + '-deactivation-option', function(e) {
        if ($(e.target).hasClass(prefix + '-deactivation-comment-input')) {
            return;
        }
        $(this).find('input[name="' + prefix + '_deactivation_reason_key"]').prop('checked', true).trigger('change');
    });
    
    // Handle reason selection
    $(document).on('change', '.' + prefix + '-deactivation-modal input[name="' + prefix + '_deactivation_reason_key"]', function() {
        var selectedOption = $(this).closest('.' + prefix + '-deactivation-option');
        
        $('.' + prefix + '-deactivation-option').removeClass('selected');
        selectedOption.addClass('selected');
        
        $('.' + prefix + '-deactivation-modal .' + prefix + '-deactivation-comment-input').hide();
        selectedOption.find('.' + prefix + '-deactivation-comment-input').show().focus();
    });
    
    // Close modal (X button)
    $(document).on('click', '.' + prefix + '-deactivation-close', function(e) {
        e.preventDefault();
        closeModal();
    });
    
    // Skip & Deactivate
    $(document).on('click', '.' + prefix + '-deactivation-skip', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (isDeactivating) {
            return;
        }
        isDeactivating = true;
        
        if (pendingRequest && pendingRequest.abort) {
            pendingRequest.abort();
            pendingRequest = null;
        }
        
        // Set cookie (skip in debug mode)
        if (!config.debugMode) {
            setCookie(config.cookieName, Date.now(), 1);
        }
        
        window.location.href = deactivateLink;
    });
    
    // Submit form
    $(document).on('submit', '.' + prefix + '-deactivation-form', function(e) {
        e.preventDefault();
        
        var selectedReason = $('.' + prefix + '-deactivation-modal input[name="' + prefix + '_deactivation_reason_key"]:checked');
        var reasonKey = selectedReason.val();
        var reasonText = selectedReason.data('text');
        var userComment = selectedReason.closest('.' + prefix + '-deactivation-option').find('.' + prefix + '-deactivation-comment-input').val() || '';
        
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text(config.i18n.sending);
        
        sendToDataCenter(reasonKey, reasonText, userComment);
    });
    
    function sendToDataCenter(reasonKey, reasonText, userComment) {
        lexLog('Sending to datacenter...');
        
        var payload = {
            plugin_slug: config.pluginSlug,
            plugin_version: config.pluginVersion,
            reason_variation: config.reasonVariation,
            reason_key: reasonKey,
            reason_text: reasonText,
            user_comment: userComment,
            wp_version: config.wpVersion,
            php_version: config.phpVersion,
            installed_time: config.installedTime
        };
        
        lexLog('Payload:', payload);
        
        pendingRequest = $.ajax({
            url: config.apiUrl + '/rest/v1/deactivation_feedback',
            type: 'POST',
            timeout: 5000,
            headers: {
                'apikey': config.apiKey,
                'Authorization': 'Bearer ' + config.apiKey,
                'Content-Type': 'application/json',
                'Prefer': 'return=minimal'
            },
            data: JSON.stringify(payload),
            success: function(response) {
                lexLog('Success!', response);
                pendingRequest = null;
                
                // Set cookie (skip in debug mode)
                if (!config.debugMode) {
                    setCookie(config.cookieName, Date.now(), 1);
                }
                
                proceedWithDeactivation();
            },
            error: function(xhr, status, error) {
                lexLog('Error:', {status: status, error: error, xhr: xhr});
                pendingRequest = null;
                proceedWithDeactivation();
            }
        });
    }
    
    function proceedWithDeactivation() {
        if (isDeactivating) {
            return;
        }
        isDeactivating = true;
        
        if (config.debugMode) {
            lexLog('DEBUG MODE - Deactivation prevented! Remove debug_mode to actually deactivate.');
            closeModal();
            alert('DEBUG MODE: Deactivation prevented. Check console for logs.');
            return;
        }
        
        closeModal();
        window.location.href = deactivateLink;
    }
    
    function closeModal() {
        $('.' + prefix + '-deactivation-overlay, .' + prefix + '-deactivation-modal').removeClass('active');
        $('.' + prefix + '-deactivation-overlay, .' + prefix + '-deactivation-modal').remove();
    }
    
})(jQuery);
JS;
    }
    
    /**
     * Get inline CSS code
     */
    private function get_css() {
        $p = $this->prefix;
        
        return <<<CSS
/* {$p} Deactivation Feedback Modal */
.{$p}-deactivation-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 99999;
    opacity: 0;
    transition: opacity 0.3s ease;
    display: none;
}

.{$p}-deactivation-overlay.active {
    display: block;
    opacity: 1;
}

.{$p}-deactivation-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    z-index: 100000;
    opacity: 0;
    transition: all 0.3s ease;
    display: none;
}

.{$p}-deactivation-modal.active {
    display: block;
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

.{$p}-deactivation-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e5e5e5;
    position: relative;
}

.{$p}-deactivation-header h2 {
    margin: 0 0 10px 0;
    font-size: 18px;
    line-height: 1.4;
    display: flex;
    align-items: center;
    gap: 8px;
}

.{$p}-deactivation-header h2 .dashicons {
    color: #2271b1;
}

.{$p}-deactivation-header p {
    margin: 0;
    color: #646970;
    font-size: 13px;
}

.{$p}-deactivation-close {
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 28px;
    font-weight: 300;
    color: #999;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
}

.{$p}-deactivation-close:hover {
    color: #333;
}

.{$p}-deactivation-options {
    padding: 20px 24px;
    max-height: 400px;
    overflow-y: auto;
}

.{$p}-deactivation-option {
    margin-bottom: 12px;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: all 0.2s ease;
    background: #fafafa;
    cursor: pointer;
}

.{$p}-deactivation-option:hover {
    border-color: #2271b1;
    background: #fff;
}

.{$p}-deactivation-option.selected {
    border-color: #2271b1;
    background: #f0f6fc;
}

.{$p}-deactivation-option label {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    cursor: pointer;
    margin: 0;
}

.{$p}-deactivation-option input[type="radio"] {
    margin: 3px 0 0 0;
    cursor: pointer;
}

.{$p}-deactivation-option .{$p}-deactivation-reason-text {
    flex: 1;
    font-size: 14px;
    line-height: 1.5;
    color: #1d2327;
}

.{$p}-deactivation-option .{$p}-deactivation-comment-input {
    width: 100%;
    margin-top: 10px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 13px;
    box-sizing: border-box;
    cursor: text;
}

.{$p}-deactivation-option .{$p}-deactivation-comment-input:focus {
    box-shadow: none;
}

.{$p}-deactivation-footer {
    padding: 16px 24px;
    border-top: 1px solid #e5e5e5;
    background: #fafafa;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 0 0 4px 4px;
}

.{$p}-deactivation-footer button {
    margin: 0;
}

.{$p}-deactivation-skip {
    color: #646970;
    text-decoration: none;
    font-size: 13px;
}

.{$p}-deactivation-skip:hover {
    color: #2271b1;
}

.{$p}-deactivation-footer button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
CSS;
    }
}