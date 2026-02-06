<?php
/**
 * Select Field Template
 * 
 * Renders a select dropdown field row.
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
    'options'       => [], // ['value' => 'Label', ...] or ['Group' => ['value' => 'Label', ...]] for optgroups
    'disabled'      => false,
    'is_multiple'   => false, // true for multi-select
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
$options = $field['options'] ?? [];
$is_multiple = $field['is_multiple'] ?? false;

// Detect if options contain optgroups (nested arrays)
$has_optgroups = false;
if (!empty($options)) {
    $first_option = reset($options);
    $has_optgroups = is_array($first_option);
}

// For multi-select, name should be array
if ($is_multiple) {
    $input_name = $input_name . '[]';
}

// Normalize current value for multi-select
if ($is_multiple && !is_array($current_value)) {
    $current_value = ($current_value === '' || $current_value === null) ? [] : [$current_value];
}

// Check if field is inherited
$is_inherited = $fieldRenderer->isInherited($field);
$inherited_title = $fieldRenderer->getInheritedTitle($field);

// Build class string for select
$input_class = $fieldRenderer->classnames($field['input_classes']);
// Check if field is inherited and value is empty (empty string/array means inherit)
$is_empty_value = false;
if ($is_multiple) {
    $is_empty_value = (empty($current_value) || (is_array($current_value) && count($current_value) === 0));
} else {
    $is_empty_value = ($current_value === '' || $current_value === null);
}
if ($is_inherited && $is_empty_value) {
    $input_class .= ' lex-field--inherited';
}

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--select'], $field['classes']);
if ($is_inherited && $is_empty_value) {
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
        <select
            id="<?php echo esc_attr($input_id); ?>"
            <?php if (empty($field['pro'])): ?>name="<?php echo esc_attr($input_name); ?>"<?php endif; ?>
            <?php if ($input_class): ?>class="<?php echo esc_attr($input_class); ?>"<?php endif; ?>
            <?php if ($inherited_title): ?>title="<?php echo esc_attr($inherited_title); ?>"<?php endif; ?>
            <?php if ($is_disabled): ?>disabled<?php endif; ?>
            <?php if ($is_multiple): ?>multiple<?php endif; ?>
        >
            <?php if ($has_optgroups): ?>
                <?php // Render with optgroups ?>
                <?php foreach ($options as $group_label => $group_options): ?>
                    <optgroup label="<?php echo esc_attr($group_label); ?>">
                        <?php foreach ($group_options as $option_value => $option_label): ?>
                            <?php
                            $is_selected = false;
                            if ($is_multiple) {
                                $is_selected = is_array($current_value) && in_array($option_value, $current_value);
                            } else {
                                $is_selected = ($option_value == $current_value);
                            }
                            ?>
                            <option value="<?php echo esc_attr($option_value); ?>"<?php echo $is_selected ? ' selected' : ''; ?>><?php echo wp_kses_post(trim($option_label)); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            <?php else: ?>
                <?php // Render flat options (no optgroups) ?>
                <?php foreach ($options as $option_value => $option_label): ?>
                    <?php
                    $is_selected = false;
                    if ($is_multiple) {
                        $is_selected = is_array($current_value) && in_array($option_value, $current_value);
                    } else {
                        $is_selected = ($option_value == $current_value);
                    }
                    ?>
                    <option value="<?php echo esc_attr($option_value); ?>"<?php echo $is_selected ? ' selected' : ''; ?>><?php echo esc_html(trim($option_label)); ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        
        <?php echo $fieldRenderer->renderDescription($field); ?>
    </td>
</tr>

