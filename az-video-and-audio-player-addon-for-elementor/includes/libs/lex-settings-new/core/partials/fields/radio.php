<?php
/**
 * Radio Field Template
 * 
 * Renders radio button field in a fieldset.
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
    'options'       => [], // ['value' => 'Label', ...]
    'disabled'      => false,
    'inline'        => false, // true for horizontal layout
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
$input_name = $field['name'];
$current_value = $value;
$is_disabled = $field['disabled'];
$has_tooltip = !empty($field['tooltip']);
$options = $field['options'] ?? [];
$is_inline = $field['inline'];

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--radio'], $field['classes']);
if ($is_inline) {
    $tr_classes[] = 'lex-field--inline';
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
        <fieldset>
            <?php foreach ($options as $option_value => $option_label): ?>
                <?php
                $is_checked = ($option_value == $current_value);
                $is_pro = isset($field['pro_options']) && in_array($option_value, $field['pro_options']);
                $label_class = $is_pro ? ' class="lex-field--pro-label"' : '';
                ?>
                <label<?php echo $label_class; ?>>
                    <input
                        type="radio"
                        <?php if (empty($field['pro'])): ?>name="<?php echo esc_attr($input_name); ?>"<?php endif; ?>
                        value="<?php echo esc_attr($option_value); ?>"
                        <?php echo $is_checked ? 'checked' : ''; ?>
                        <?php echo ($is_disabled || $is_pro) ? 'disabled' : ''; ?>
                    />
                    <?php echo wp_kses_post($option_label); ?>
                    <?php if ($is_pro): ?>
                        <span class="lex-pro-badge">PRO</span>
                    <?php endif; ?>
                </label><?php echo $is_inline ? '' : '<br />'; ?>
                
            <?php endforeach; ?>
            
            <?php echo $fieldRenderer->renderDescription($field); ?>
        </fieldset>
    </td>
</tr>

