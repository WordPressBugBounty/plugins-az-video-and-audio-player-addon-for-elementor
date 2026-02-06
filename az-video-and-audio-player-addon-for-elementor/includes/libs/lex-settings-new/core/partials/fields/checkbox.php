<?php
/**
 * Checkbox Field Template
 * 
 * Renders checkbox field(s) in a fieldset.
 * Supports single checkbox or multiple checkboxes.
 * 
 * Expected variables:
 * - $field (array): Normalized field configuration
 * - $value (mixed): Current value for this field (bool for single, array for multiple)
 * - $load_defaults_only (bool): If true, return defaults and exit
 */

// ============================================
// TYPE DEFAULTS
// ============================================
$field_defaults = [
    'options'       => [], // For multiple checkboxes: ['value' => 'Label', ...]
    'checkbox_label' => '', // For single checkbox: label text after checkbox
    'disabled'      => false,
    'is_multiple'   => false, // true for multiple checkboxes
    'is_sortable'   => false, // true to enable drag-and-drop sorting
    'max_height'    => '', // Max height for sortable container (e.g., '300px', '20rem')
];

// Return early if just loading defaults
if (isset($load_defaults_only) && $load_defaults_only) {
    return $field_defaults;
}

// ============================================
// SAFETY CHECK
// ============================================
if (!isset($field) || !isset($value)) {
    echo $fieldRenderer->renderFieldFallback($field['key'] ?? null, 'Field template: missing required variables');
    return;
}

if (!isset($field['name']) || !isset($field['id'])) {
    echo $fieldRenderer->renderFieldFallback($field['key'] ?? null, 'Field metadata incomplete (missing name or id)');
    return;
}

// ============================================
// HELPER VARIABLES
// ============================================
$input_id = $field['id'];
$input_name = $field['name'];
$current_value = $value;
$is_disabled = $field['disabled'];
$has_tooltip = !empty($field['tooltip']);
$is_multiple = $field['is_multiple'];
$is_sortable = $field['is_sortable'] ?? false;
$max_height = $field['max_height'] ?? '';
$options = $field['options'] ?? [];
$checkbox_label = $field['checkbox_label'] ?? '';

// If sortable, check for saved order field (preserves all items, checked and unchecked)
$saved_order = null;
if ($is_sortable && $is_multiple) {
    // Use __ prefix to mark as internal framework field (won't appear in Global Options)
    $order_key = '__' . $field['key'] . '_order';
    $saved_order_raw = $fieldRenderer->settings->dataManager->get($order_key);

    // For metabox support for sorting
    global $pagenow;

    // post.php represents the post edit page
    if($pagenow === 'post.php') {
        $saved_order_raw = get_post_meta(get_the_id(), $order_key, true);
    }
    
    if (!empty($saved_order_raw)) {
        // Order is stored as comma-separated string or array
        if (is_string($saved_order_raw)) {
            $saved_order = array_map('trim', explode(',', $saved_order_raw));
        } elseif (is_array($saved_order_raw)) {
            $saved_order = $saved_order_raw;
        }
    }
}

// Reorder options based on saved order
if ($is_sortable && $is_multiple && !empty($saved_order) && is_array($saved_order)) {
    $ordered_options = [];
    
    // First, add options in the saved order
    foreach ($saved_order as $saved_value) {
        $saved_value = trim($saved_value);
        if (isset($options[$saved_value])) {
            $ordered_options[$saved_value] = $options[$saved_value];
        }
    }
    
    // Then, add any remaining options that weren't in saved order (for new options)
    foreach ($options as $option_value => $option_label) {
        if (!isset($ordered_options[$option_value])) {
            $ordered_options[$option_value] = $option_label;
        }
    }
    
    $options = $ordered_options;
}

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--checkbox'], $field['classes']);
$tr_class = $fieldRenderer->classnames($tr_classes);

// ============================================
// RENDER HTML
// ============================================
?>
<tr class="<?php echo $tr_class; ?>"<?php echo ($field['pro'] && isset($field['pro']['onclick'])) ? ' onclick="' . $field['pro']['onclick'] . '()"' : ''; ?>>
    <th scope="row">
        <?php $fieldRenderer->renderLabel($field); ?>
        <?php if ($has_tooltip): ?>
            <?php  $fieldRenderer->renderTooltip($field); ?>
        <?php endif; ?>
        <?php $fieldRenderer->renderLabelDescription($field); ?>
    </th>
    <td>
        <fieldset>
            <?php if ($is_multiple && !empty($options)): ?>
                <?php // Multiple checkboxes ?>
                <?php 
                $container_class = $is_sortable ? 'lex-sortable-checkbox-container' : '';
                $item_class = $is_sortable ? 'lex-sortable-checkbox-item' : '';
                $container_style = '';
                if (!empty($max_height)) {
                    $container_style = ' style="max-height: ' . esc_attr($max_height) . '; overflow-y: auto;"';
                }
                
                $master_checkbox_id = $input_id . '_select_all';
                ?>
                <div class="lex-checkbox-master-wrapper">
                    <label for="<?php echo esc_attr($master_checkbox_id); ?>" class="lex-checkbox-master-label">
                        <input
                            type="checkbox"
                            id="<?php echo esc_attr($master_checkbox_id); ?>"
                            class="lex-checkbox-master"
                            data-field-name="<?php echo esc_attr($input_name); ?>"
                        />
                        <span class="lex-checkbox-master-text"><?php esc_html_e('Select All', 'lex-settings'); ?></span>
                    </label>
                </div>
                <div class="<?php echo $container_class; ?>" data-field-name="<?php echo esc_attr($input_name); ?>"<?php echo $container_style; ?>>
                    <?php if ($is_sortable): ?>
                        <?php 
                        // Use __ prefix to mark as internal framework field (won't appear in Global Options)
                        $order_field_name = '__' . $input_name . '_order';
                        $order_field_id = $input_id . '_order';
                        $current_order = implode(',', array_keys($options));
                        ?>
                        <input 
                            type="hidden" 
                            name="<?php echo esc_attr($order_field_name); ?>" 
                            id="<?php echo esc_attr($order_field_id); ?>" 
                            value="<?php echo esc_attr($current_order); ?>"
                            class="lex-sortable-order-field"
                        />
                    <?php endif; ?>
                    <?php 
                    foreach ($options as $option_value => $option_label): ?>
                        <?php
                        $is_checked = is_array($current_value) && in_array($option_value, $current_value);
                        $is_pro = isset($field['pro_options']) && in_array($option_value, $field['pro_options']);
                        $label_class = $is_pro ? ' class="lex-field--pro-label"' : '';
                        ?>
                        <div class="<?php echo $item_class; ?>" data-value="<?php echo esc_attr($option_value); ?>"<?php echo $is_sortable ? ' draggable="true"' : ''; ?>>
                            <?php if ($is_sortable): ?>
                                <span class="lex-drag-handle" title="<?php esc_attr_e('Drag to reorder', 'lex-settings'); ?>">⋮⋮</span>
                            <?php endif; ?>
                            <label<?php echo $label_class; ?>>
                                <input
                                    type="checkbox"
                                    <?php if (empty($field['pro'])): ?>name="<?php echo esc_attr($input_name); ?>[]"<?php endif; ?>
                                    value="<?php echo esc_attr($option_value); ?>"
                                    class="lex-checkbox-item"
                                    data-master-field="<?php echo esc_attr($input_name); ?>"
                                    <?php echo $is_checked ? 'checked' : ''; ?>
                                    <?php echo ($is_disabled || $is_pro) ? 'disabled' : ''; ?>
                                />
                                <?php echo wp_kses_post($option_label); ?>
                                <?php if ($is_pro): ?>
                                    <span class="lex-pro-badge">PRO</span>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <?php // Single checkbox ?>
                <label>
                    <input
                        type="checkbox"
                        id="<?php echo esc_attr($input_id); ?>"
                        <?php if (empty($field['pro'])): ?>name="<?php echo esc_attr($input_name); ?>"<?php endif; ?>
                        value="1"
                        <?php echo ($current_value === '1' || $current_value === true || $current_value === 1) ? 'checked' : ''; ?>
                        <?php if ($is_disabled): ?>disabled<?php endif; ?>
                    />
                    <?php echo wp_kses_post($checkbox_label); ?>
                </label>
            <?php endif; ?>
            
            <?php echo $fieldRenderer->renderDescription($field); ?>
        </fieldset>
    </td>
</tr>

