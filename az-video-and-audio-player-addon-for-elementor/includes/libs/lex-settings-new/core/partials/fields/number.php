<?php
/**
 * Number Field Template
 * 
 * Renders a number input field row.
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
    'input_classes' => ['small-text'],
    'min'           => null,
    'max'           => null,
    'step'          => 1,
    'placeholder'   => '',
    'disabled'      => false,
    'readonly'      => false,
    'unit'          => '', // e.g., 'minutes', 'requests/hour'
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
$min = $field['min'];
$max = $field['max'];
$step = $field['step'];
$unit = $field['unit'];

// Check if field is inherited
$is_inherited = $fieldRenderer->isInherited($field);
$inherited_title = $fieldRenderer->getInheritedTitle($field);

// Build class string for input
$input_class = $fieldRenderer->classnames($field['input_classes']);
if ($is_inherited && empty($input_value)) {
    $input_class .= ' lex-field--inherited';
}

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--number'], $field['classes']);
if ($is_inherited && empty($input_value)) {
    $tr_classes[] = 'lex-field-row--inherited';
}
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
            type="number"
            id="<?php echo esc_attr($input_id); ?>"
            <?php if (empty($field['pro'])): ?>name="<?php echo esc_attr($input_name); ?>"<?php endif; ?>
            value="<?php echo esc_attr($input_value); ?>"
            class="<?php echo esc_attr($input_class); ?>"
            <?php if ($inherited_title): ?>title="<?php echo esc_attr($inherited_title); ?>"<?php endif; ?>
            <?php if ($min !== null): ?>min="<?php echo esc_attr($min); ?>"<?php endif; ?>
            <?php if ($max !== null): ?>max="<?php echo esc_attr($max); ?>"<?php endif; ?>
            <?php if ($step !== null): ?>step="<?php echo esc_attr($step); ?>"<?php endif; ?>
            <?php if ($placeholder): ?>placeholder="<?php echo esc_attr($placeholder); ?>"<?php endif; ?>
            <?php if ($is_disabled): ?>disabled<?php endif; ?>
            <?php if ($is_readonly): ?>readonly<?php endif; ?>
        />
        <?php if ($unit): ?>
            <span class="description"><?php echo $unit; ?></span>
        <?php endif; ?>
        
        <?php echo $fieldRenderer->renderDescription($field); ?>
    </td>
</tr>

