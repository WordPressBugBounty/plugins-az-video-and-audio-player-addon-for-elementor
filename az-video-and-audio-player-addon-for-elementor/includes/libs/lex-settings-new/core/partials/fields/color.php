<?php
/**
 * Color Picker Field Template
 * 
 * Renders a color input field row.
 * 
 * Expected variables:
 * - $field (array): Normalized field configuration
 * - $value (mixed): Current value for this field
 * - $load_defaults_only (bool): If true, return defaults and exit
 */

// ============================================
// TYPE DEFAULTS
// ============================================
$field_defaults = [
    'input_classes' => [],
    'disabled'      => false,
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
// Ensure color input always has a valid hex value (HTML5 requirement)
// Default to black (#000000) if value is empty
$input_value = !empty($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) ? $value : '#000000';
$is_disabled = $field['disabled'];
$has_tooltip = !empty($field['tooltip']);

// Build class string for input
$input_class = $fieldRenderer->classnames($field['input_classes']);

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--color'], $field['classes']);
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
        <input
            type="text"
            id="<?php echo esc_attr($input_id); ?>"
            <?php if (empty($field['pro'])): ?>name="<?php echo esc_attr($input_name); ?>"<?php endif; ?>
            value="<?php echo esc_attr($input_value); ?>"
            class="lex-color-picker <?php echo esc_attr($input_class); ?>"
            data-color-picker="true"
            <?php if ($is_disabled): ?>disabled<?php endif; ?>
        />
        
        <?php echo $fieldRenderer->renderDescription($field); ?>
    </td>
</tr>

