<?php
/**
 * Textarea Field Template
 * 
 * Renders a textarea field row.
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
    'input_classes' => ['large-text'],
    'rows'          => 3,
    'placeholder'   => '',
    'disabled'      => false,
    'readonly'      => false,
    'code'          => false, // true to add 'code' class for monospace font
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
$input_value = $value;
$is_disabled = $field['disabled'];
$is_readonly = $field['readonly'];
$has_tooltip = !empty($field['tooltip']);
$placeholder = $field['placeholder'];
$rows = $field['rows'];

// Build class string for textarea
$input_classes = $field['input_classes'];
if ($field['code']) {
    $input_classes[] = 'code';
}
$input_class = $fieldRenderer->classnames($input_classes);

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--textarea'], $field['classes']);
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
        <textarea
            id="<?php echo esc_attr($input_id); ?>"
            <?php if (empty($field['pro'])): ?>name="<?php echo esc_attr($input_name); ?>"<?php endif; ?>
            rows="<?php echo esc_attr($rows); ?>"
            class="<?php echo esc_attr($input_class); ?>"
            <?php if ($placeholder): ?>placeholder="<?php echo esc_attr($placeholder); ?>"<?php endif; ?>
            <?php if ($is_disabled): ?>disabled<?php endif; ?>
            <?php if ($is_readonly): ?>readonly<?php endif; ?>
        ><?php echo esc_textarea($input_value); ?></textarea>
        
        <?php echo $fieldRenderer->renderDescription($field); ?>
    </td>
</tr>

