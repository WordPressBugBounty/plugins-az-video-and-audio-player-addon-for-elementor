<?php
/**
 * CSS Box Model Field Template
 * 
 * Renders a CSS box model editor with text input and optional visual mode.
 * Used for margin, padding, and other 4-sided CSS properties.
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
    'input_classes'   => ['lex-css-input-wrapper__input'],
    'wrapper_classes' => ['lex-css-input-wrapper'],
    'placeholder'     => 'e.g. 10px or 10px 20px',
    'disabled'        => false,
    'mode'            => 'text', // 'text' or 'visual'
    'show_toggle'     => true,
    'visual_label'    => __('Content', 'lex-settings'), // Text shown in center of box visual
    'visual_values'   => [ // Default values for visual mode
        'top'    => '20px',
        'right'  => '20px',
        'bottom' => '20px',
        'left'   => '20px',
    ],
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
$has_tooltip = !empty($field['tooltip']);
$placeholder = $field['placeholder'];
$show_toggle = $field['show_toggle'];
$visual_label = $field['visual_label'];
$visual_values = $field['visual_values'];

// Build class string for input
$input_class = $fieldRenderer->classnames($field['input_classes']);

// Build class string for wrapper
$wrapper_classes = array_merge($field['wrapper_classes'], ['data-mode' => $field['mode']]);
$wrapper_class = $fieldRenderer->classnames($wrapper_classes);

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field--css-value', 'lex-field-type--box-model'], $field['classes']);
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
        <div class="<?php echo $wrapper_class; ?>" data-mode="<?php echo $field['mode']; ?>">
            <input
                type="text"
                id="<?php echo esc_attr($input_id); ?>"
                <?php if (empty($field['pro'])): ?>name="<?php echo esc_attr($input_name); ?>"<?php endif; ?>
                value="<?php echo esc_attr($input_value); ?>"
                class="<?php echo esc_attr($input_class); ?>"
                <?php if ($placeholder): ?>placeholder="<?php echo esc_attr($placeholder); ?>"<?php endif; ?>
                <?php if ($is_disabled): ?>disabled<?php endif; ?>
            />

            <?php if ($show_toggle): ?>
            <button type="button" class="lex-css-input-wrapper__mode-btn lex-css-input-wrapper__toggle" aria-expanded="false" aria-label="Toggle visual mode" title="Toggle visual mode">
                <span class="dashicons dashicons-visibility"></span>
            </button>
            <?php endif; ?>

            <div class="lex-css-input-wrapper__visual-wrapper" aria-hidden="true">
                <div class="lex-css-input-wrapper__visual">
                    <input class="lex-css-input-wrapper__visual-input top" type="text" value="<?php echo $visual_values['top']; ?>" aria-label="Top spacing value">
                    <input class="lex-css-input-wrapper__visual-input right" type="text" value="<?php echo $visual_values['right']; ?>" aria-label="Right spacing value">
                    <input class="lex-css-input-wrapper__visual-input bottom" type="text" value="<?php echo $visual_values['bottom']; ?>" aria-label="Bottom spacing value">
                    <input class="lex-css-input-wrapper__visual-input left" type="text" value="<?php echo $visual_values['left']; ?>" aria-label="Left spacing value">
                    
                    <div class="lex-css-input-wrapper__connector top"></div>
                    <div class="lex-css-input-wrapper__connector right"></div>
                    <div class="lex-css-input-wrapper__connector bottom"></div>
                    <div class="lex-css-input-wrapper__connector left"></div>
                    
                    <div class="lex-css-input-wrapper__visual-content"><?php echo $visual_label; ?></div>
                </div>
            </div>
        </div>
        
        <?php echo $fieldRenderer->renderDescription($field); ?>
    </td>
</tr>

