<?php
/**
 * CSS Border Field Template
 * 
 * Renders a CSS border editor with text input and optional visual controls.
 * Used for border property (width + style + color).
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
    'wrapper_classes' => ['lex-css-input-wrapper', 'lex-css-input-wrapper--visual-default'],
    'placeholder'     => 'e.g. 1px solid #ddd',
    'disabled'        => false,
    'mode'            => 'text', // 'text' or 'visual'
    'show_toggle'     => true,
    'visual_values'   => [ // Default values for visual mode
        'width' => '',
        'style' => '',
        'color' => '#dddddd',
    ],
    'style_options'   => [
        ''       => __('Select', 'lex-settings'),
        'none'   => 'none',
        'solid'  => 'solid',
        'dashed' => 'dashed',
        'dotted' => 'dotted',
        'double' => 'double',
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
$visual_values = $field['visual_values'];
$style_options = $field['style_options'];

// Build class string for input
$input_class = $fieldRenderer->classnames($field['input_classes']);

// Build class string for wrapper
$wrapper_class = $fieldRenderer->classnames($field['wrapper_classes']);

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field--css-value', 'lex-field-type--border'], $field['classes']);
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
        <div class="<?php echo $wrapper_class; ?>">
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

            <div class="lex-css-input-wrapper__border-wrapper" aria-hidden="true">
                <div class="lex-css-input-wrapper__border-visual">
                    <label class="lex-css-input-wrapper__border-field">
                        <span class="lex-css-input-wrapper__border-field-label">Width</span>
                        <input type="text" class="lex-css-input-wrapper__border-input width" value="<?php echo $visual_values['width']; ?>" placeholder="e.g. 1px" aria-label="Border width value" />
                    </label>
                    <label class="lex-css-input-wrapper__border-field">
                        <span class="lex-css-input-wrapper__border-field-label">Style</span>
                        <select class="lex-css-input-wrapper__border-select style" aria-label="Border style value">
                            <?php foreach ($style_options as $option_value => $option_label): ?>
                                <option value="<?php echo $option_value; ?>"<?php echo ($option_value == $visual_values['style']) ? ' selected' : ''; ?>>
                                    <?php echo $option_label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="lex-css-input-wrapper__border-field">
                        <span class="lex-css-input-wrapper__border-field-label">Color</span>
                        <input type="color" class="lex-css-input-wrapper__border-input color" value="<?php echo esc_attr($visual_values['color'] ?? ''); ?>" aria-label="Border color value" />
                    </label>
                </div>
            </div>
        </div>
        
        <?php echo $fieldRenderer->renderDescription($field); ?>
    </td>
</tr>

