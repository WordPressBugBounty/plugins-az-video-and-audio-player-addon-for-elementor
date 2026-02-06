<?php
namespace Lex\Settings\V2\Services;

/**
 * AJAX Handler Service
 * 
 * Handles all AJAX requests for settings operations.
 */

use Lex\Settings\V2\Settings;
use Lex\Settings\V2\Utilities\Security;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Handler Service Class
 */
class AjaxHandler {
    
    /**
     * Settings instance
     * 
     * @var Settings
     */
    private $settings;
    
    /**
     * Constructor
     * 
     * @param Settings $settings Settings instance
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
        $this->registerHooks();
    }
    
    /**
     * Register WordPress AJAX hooks
     * 
     * @return void
     */
    public function registerHooks() {
        $prefix = $this->settings->getConfig('ajax_prefix');
        
        add_action("wp_ajax_{$prefix}save_tab", [$this, 'handleSaveTab']);
        add_action("wp_ajax_{$prefix}reset_tab", [$this, 'handleResetTab']);
        add_action("wp_ajax_{$prefix}reset_all", [$this, 'handleResetAll']);
        add_action("wp_ajax_{$prefix}export_settings", [$this, 'handleExport']);
        add_action("wp_ajax_{$prefix}import_settings", [$this, 'handleImport']);
    }
    
    /**
     * Handle save tab AJAX request
     * 
     * @return void Sends JSON response
     */
    public function handleSaveTab() {
        // Verify security
        $nonce_action = $this->settings->getConfig('nonce_action');
        $nonce_name = isset($_POST['nonce']) ? 'nonce' : ($nonce_action . '_field');
        $nonce = isset($_POST[$nonce_name]) ? sanitize_text_field(wp_unslash($_POST[$nonce_name])) : '';
        Security::verifyRequest(
            $nonce,
            $nonce_name,
            $nonce_action,
            $this->settings->getConfig('capability')
        );
        
        // Get tab ID and field data
        $tab_id = isset($_POST['tab_id']) ? sanitize_text_field(wp_unslash($_POST['tab_id'])) : '';
        $field_data = isset($_POST['field_data']) ? json_decode(wp_unslash($_POST['field_data']), true) : [];
        
        if ($this->settings->getConfig('debug_mode')) {
            error_log('[Lex Settings] AJAX save_tab handler called');
            error_log('[Lex Settings] POST data: ' . print_r($_POST, true));
            error_log('[Lex Settings] Parsed tab_id: ' . $tab_id);
            error_log('[Lex Settings] Parsed field_data: ' . print_r($field_data, true));
        }
        
        if (empty($tab_id) || !is_array($field_data)) {
            wp_send_json_error(['message' => __('Invalid data provided', 'lex-settings')]);
        }
        
        // Save the settings
        $result = $this->settings->dataManager->save($field_data);
        
        if ($result['success']) {
            $message = $result['changed'] 
                ? __('Settings saved successfully', 'lex-settings')
                : __('No changes detected. Settings are already saved.', 'lex-settings');
            
            wp_send_json_success([
                'message' => $message,
                'tab_id' => $tab_id,
                'changed' => $result['changed']
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to save settings', 'lex-settings')]);
        }
    }
    
    /**
     * Handle reset tab AJAX request
     * 
     * @return void Sends JSON response
     */
    public function handleResetTab() {
        // Verify security
        $nonce_action = $this->settings->getConfig('nonce_action');
        $nonce_name = isset($_POST['nonce']) ? 'nonce' : ($nonce_action . '_field');
        $nonce = isset($_POST[$nonce_name]) ? sanitize_text_field(wp_unslash($_POST[$nonce_name])) : '';
        Security::verifyRequest(
            $nonce,
            $nonce_name,
            $nonce_action,
            $this->settings->getConfig('capability')
        );
        
        // Get tab ID and field keys
        $tab_id = isset($_POST['tab_id']) ? sanitize_text_field(wp_unslash($_POST['tab_id'])) : '';
        $field_keys = isset($_POST['field_keys']) ? json_decode(wp_unslash($_POST['field_keys']), true) : [];
        
        if (empty($tab_id) || !is_array($field_keys)) {
            wp_send_json_error(['message' => __('Invalid data provided', 'lex-settings')]);
        }
        
        // Reset the settings
        $result = $this->settings->dataManager->reset($field_keys);
        
        if ($result['success']) {
            $message = $result['changed'] 
                ? __('Settings reset successfully', 'lex-settings')
                : __('Settings are already at default values', 'lex-settings');
            
            wp_send_json_success([
                'message' => $message,
                'tab_id' => $tab_id,
                'changed' => $result['changed']
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to reset settings', 'lex-settings')]);
        }
    }
    
    /**
     * Handle reset all AJAX request
     * 
     * @return void Sends JSON response
     */
    public function handleResetAll() {
        // Verify security
        $nonce_action = $this->settings->getConfig('nonce_action');
        $nonce_name = isset($_POST['nonce']) ? 'nonce' : ($nonce_action . '_field');
        $nonce = isset($_POST[$nonce_name]) ? sanitize_text_field(wp_unslash($_POST[$nonce_name])) : '';
        Security::verifyRequest(
            $nonce,
            $nonce_name,
            $nonce_action,
            $this->settings->getConfig('capability')
        );
        
        // Reset all settings
        $result = $this->settings->dataManager->resetAll();
        
        if ($result['success']) {
            $message = $result['changed'] 
                ? __('All settings have been reset to default values', 'lex-settings')
                : __('Settings are already at default values', 'lex-settings');
            
            wp_send_json_success([
                'message' => $message,
                'changed' => $result['changed']
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to reset all settings', 'lex-settings')]);
        }
    }
    
    /**
     * Handle export AJAX request
     * 
     * @return void Sends JSON response
     */
    public function handleExport() {
        // Verify security
        $nonce_action = $this->settings->getConfig('nonce_action');
        $nonce_name = isset($_POST['nonce']) ? 'nonce' : ($nonce_action . '_field');
        $nonce = isset($_POST[$nonce_name]) ? sanitize_text_field(wp_unslash($_POST[$nonce_name])) : '';
        Security::verifyRequest(
            $nonce,
            $nonce_name,
            $nonce_action,
            $this->settings->getConfig('capability')
        );
        
        // Export settings
        $result = $this->settings->dataManager->export();
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => __('Settings exported successfully', 'lex-settings'),
                'data' => $result['data'],
                'filename' => $result['filename']
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to export settings', 'lex-settings')]);
        }
    }
    
    /**
     * Handle import AJAX request
     * 
     * @return void Sends JSON response
     */
    public function handleImport() {
        // Verify security
        $nonce_action = $this->settings->getConfig('nonce_action');
        $nonce_name = isset($_POST['nonce']) ? 'nonce' : ($nonce_action . '_field');
        $nonce = isset($_POST[$nonce_name]) ? sanitize_text_field(wp_unslash($_POST[$nonce_name])) : '';
        Security::verifyRequest(
            $nonce,
            $nonce_name,
            $nonce_action,
            $this->settings->getConfig('capability')
        );
        
        // Check if file was uploaded
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $error_message = __('File upload failed', 'lex-settings');
            
            if (isset($_FILES['import_file']['error'])) {
                switch ($_FILES['import_file']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error_message = __('File is too large', 'lex-settings');
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error_message = __('File upload was incomplete', 'lex-settings');
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error_message = __('No file was uploaded', 'lex-settings');
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error_message = __('Missing temporary folder', 'lex-settings');
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error_message = __('Failed to write file to disk', 'lex-settings');
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $error_message = __('File upload stopped by extension', 'lex-settings');
                        break;
                }
            }
            
            wp_send_json_error(['message' => $error_message]);
            return;
        }
        
        // Validate file type (must be JSON)
        $file_name = $_FILES['import_file']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($file_ext !== 'json') {
            wp_send_json_error(['message' => __('Invalid file type. Please upload a JSON file.', 'lex-settings')]);
            return;
        }
        
        // Read file content
        $file_path = $_FILES['import_file']['tmp_name'];
        $file_content = file_get_contents($file_path);
        
        if ($file_content === false) {
            wp_send_json_error(['message' => __('Failed to read uploaded file', 'lex-settings')]);
            return;
        }
        
        // Parse JSON
        $import_data = json_decode($file_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            /* translators: %s is the JSON error message */
            wp_send_json_error([
                'message' => sprintf(__('Invalid JSON file: %s', 'lex-settings'), json_last_error_msg())
            ]);
            return;
        }
        
        // Import settings
        $result = $this->settings->dataManager->import($import_data);
        
        if ($result['success']) {
            wp_send_json_success([
                'message' => $result['message'],
                'imported_count' => $result['imported_count'],
                'reload' => true
            ]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    }
}

