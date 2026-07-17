<?php
namespace LeanPL;

/**
 * Handles saving metabox data for lean_player post type
 * 
 * Responsibilities:
 * - Validate and sanitize POST data
 * - Save field values to post meta
 * - Handle different field types (checkbox, select, text, etc.)
 * - Handle inheritance for fields with global options
 */
class Metabox_Save {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('save_post', [$this, 'save_metabox']);
    }

    /**
     * Main save handler - validates and saves all metabox fields
     * 
     * @param int $post_id Post ID
     * @return void
     */
    public function save_metabox($post_id) {
        if (!$this->should_save($post_id)) {
            return;
        }

        $fields = Metaboxes::get_field_definitions();

        foreach ($fields as $field_name => $field_config) {
            $this->save_single_field($post_id, $field_name, $field_config);
        }

        // Save special fields (__controls_order)
        if(isset($_POST['___controls_order'])) {
            update_post_meta($post_id, '___controls_order', $_POST['___controls_order']);
        }

        // Save playlist fields (duration, meta_text)
        if ( leanpl_get_option( 'playlist.enabled', true ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized with sanitize_text_field below
            $duration  = isset( $_POST['_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['_duration'] ) ) : '';
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized with sanitize_text_field below
            $meta_text = isset( $_POST['_meta_text'] ) ? sanitize_text_field( wp_unslash( $_POST['_meta_text'] ) ) : '';

            update_post_meta( $post_id, '_duration', $duration );
            update_post_meta( $post_id, '_meta_text', $meta_text );
        }
    }

    /**
     * Check if we should proceed with saving
     * 
     * @param int $post_id Post ID
     * @return bool True if should save, false otherwise
     */
    private function should_save($post_id) {
        // Post type check
        if (get_post_type($post_id) !== 'lean_player') {
            return false;
        }

        // Autosave check
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        // Nonce check
        if (!isset($_POST['leanpl_metabox_nonce']) || 
            !wp_verify_nonce($_POST['leanpl_metabox_nonce'], 'leanpl_save_metabox')) {
            return false;
        }

        // Permission check
        if (!current_user_can('edit_post', $post_id)) {
            return false;
        }

        return true;
    }

    /**
     * Save a single field based on its type
     * 
     * @param int $post_id Post ID
     * @param string $field_name Field name
     * @param array $field_config Field configuration
     * @return void
     */
    private function save_single_field($post_id, $field_name, $field_config) {
        $field_type = $field_config['type'];

        if ($field_type === 'checkbox') {
            $this->save_checkbox_field($post_id, $field_name);
            return;
        }

        if ($this->is_select_with_inheritance($field_config)) {
            $this->save_select_with_inheritance($post_id, $field_name, $field_config);
            return;
        }

        $this->save_regular_field($post_id, $field_name, $field_config);
    }

    /**
     * Save checkbox field
     * 
     * Checkboxes are '1' if checked, '0' if unchecked
     * 
     * @param int $post_id Post ID
     * @param string $field_name Field name
     * @return void
     */
    private function save_checkbox_field($post_id, $field_name) {
        // Process multiple checkbox field
        if(isset($_POST[$field_name]) && is_array($_POST[$field_name])) {
            $checkbox_value_multiple = array_map('sanitize_text_field', wp_unslash($_POST[$field_name]));

            update_post_meta($post_id, $field_name, $checkbox_value_multiple);
        } else {
            // Save single checkbox field
            $checkbox_value_single = isset($_POST[$field_name]) ? '1' : '0';
            update_post_meta($post_id, $field_name, $checkbox_value_single);
        }
    }

    /**
     * Check if field is a select field with inheritance support
     * 
     * @param array $field_config Field configuration
     * @return bool True if select field has inheritance (empty key option)
     */
    private function is_select_with_inheritance($field_config) {
        return $field_config['type'] === 'select' && isset($field_config['options']['']);
    }

    /**
     * Save select field with inheritance support
     * 
     * Empty string value means "Use Global Option"
     * 
     * @param int $post_id Post ID
     * @param string $field_name Field name
     * @param array $field_config Field configuration
     * @return void
     */
    private function save_select_with_inheritance($post_id, $field_name, $field_config) {
        if (!isset($_POST[$field_name])) {
            update_post_meta($post_id, $field_name, '');
            return;
        }

        $raw_value = sanitize_text_field(wp_unslash($_POST[$field_name]));

        // Empty string means "Use Global Option"
        if ($raw_value === '') {
            update_post_meta($post_id, $field_name, '');
            return;
        }

        // Validate that the value is a valid option
        $valid_options = $field_config['options'] ?? [];
        if (isset($valid_options[$raw_value])) {
            update_post_meta($post_id, $field_name, $raw_value);
        } else {
            // Invalid value, save as empty to inherit
            update_post_meta($post_id, $field_name, '');
        }
    }

    /**
     * Save regular field (text, number, media, etc.)
     * 
     * @param int $post_id Post ID
     * @param string $field_name Field name
     * @param array $field_config Field configuration
     * @return void
     */
    private function save_regular_field($post_id, $field_name, $field_config) {
        if (!isset($_POST[$field_name])) {
            update_post_meta($post_id, $field_name, '');
            return;
        }

        $raw_value = $_POST[$field_name];
        $sanitized_value = $this->sanitize($field_config['sanitize'] ?? $field_config['type'], $raw_value);
        
        $final_value = ($sanitized_value !== null) ? $sanitized_value : '';
        update_post_meta($post_id, $field_name, $final_value);
    }

    /**
     * Sanitize field value based on type
     * 
     * @param string $type Field type
     * @param mixed $raw_value Raw value to sanitize
     * @return mixed Sanitized value or null if invalid/empty
     */
    private function sanitize($type, $raw_value) {
        if ($raw_value === null || $raw_value === '') {
            return null;
        }

        switch ($type) {
            case 'text':
            case 'select':
            case 'radio':
            case 'password':
            case 'css-border':
            case 'css-box-model':
                return sanitize_text_field(wp_unslash($raw_value));

            case 'textarea':
                return sanitize_textarea_field(wp_unslash($raw_value));

            case 'number':
                $value = floatval(wp_unslash($raw_value));
                if (is_numeric($raw_value)) {
                    return $value;
                }
                return null;

            case 'checkbox':
                return ($raw_value === '1' || $raw_value === 1 || $raw_value === true) ? '1' : '0';

            case 'color':
                $value = sanitize_text_field(wp_unslash($raw_value));
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                    return $value;
                }
                return null;

            case 'media':
                $media_id = intval(wp_unslash($raw_value));
                return ($media_id > 0) ? $media_id : null;

            case 'file':
                if (is_numeric($raw_value)) {
                    return absint($raw_value);
                }
                return esc_url_raw(wp_unslash($raw_value));

            case 'url':
                return esc_url_raw(wp_unslash($raw_value));

            default:
                return sanitize_text_field(wp_unslash($raw_value));
        }
    }
}

Metabox_Save::get_instance();