<?php
/**
 * Action Buttons Field Template
 * 
 * Renders one or more action buttons in a table row.
 * These buttons trigger JavaScript actions (AJAX calls, file operations, etc.).
 * 
 * Expected variables:
 * - $field (array): Normalized field configuration
 * - $value (mixed): Not used for action buttons (always empty)
 * - $load_defaults_only (bool): If true, return defaults and exit
 */

// ============================================
// TYPE DEFAULTS
// ============================================
$field_defaults = [
    'buttons'        => [], // Array of button configurations
    'layout'         => 'vertical', // 'vertical' or 'horizontal'
    'button_spacing' => '16px', // Spacing between buttons
];

// Return early if just loading defaults
if (isset($load_defaults_only) && $load_defaults_only) {
    return $field_defaults;
}

// ============================================
// SAFETY CHECK
// ============================================
if (!isset($field)) {
    echo $fieldRenderer->renderFieldFallback($field['key'] ?? null, 'Field template: missing required variables');
    return;
}

// ============================================
// HELPER VARIABLES
// ============================================
$buttons = $field['buttons'] ?? [];
$layout = $field['layout'] ?? 'vertical';
$button_spacing = $field['button_spacing'] ?? '16px';
$has_tooltip = !empty($field['tooltip']);

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--action-buttons'], $field['classes']);
$tr_class = $fieldRenderer->classnames($tr_classes);

// ============================================
// RENDER HTML
// ============================================
?>
<tr class="<?php echo $tr_class; ?>">
    <th scope="row">
        <?php $fieldRenderer->renderLabel($field); ?>
        <?php if ($has_tooltip): ?>
            <?php  $fieldRenderer->renderTooltip($field); ?>
        <?php endif; ?>
        <?php $fieldRenderer->renderLabelDescription($field); ?>
    </th>
    <td>
        <?php if (!empty($buttons)): ?>
            <?php foreach ($buttons as $index => $button): ?>
                <?php
                // Button configuration
                $btn_id = $button['id'] ?? '';
                $btn_action = $button['action'] ?? '';
                $btn_text = $button['text'] ?? 'Action';
                $btn_icon = $button['icon'] ?? '';
                $btn_desc = $button['desc'] ?? '';
                $btn_class = $button['class'] ?? 'lex-btn--secondary';
                $btn_confirm = $button['confirm'] ?? '';
                $btn_disabled = $button['disabled'] ?? false;
                
                // Build button classes
                $btn_classes = ['lex-btn', $btn_class, 'lex-action-btn'];
                $btn_class_string = implode(' ', $btn_classes);
                
                // Build data attributes
                $data_attrs = '';
                if ($btn_action) {
                    $data_attrs .= ' data-action="' . esc_attr($btn_action) . '"';
                }
                if ($btn_confirm) {
                    $data_attrs .= ' data-confirm="' . esc_attr($btn_confirm) . '"';
                }
                
                // Add spacing for vertical layout (except first button)
                $style = '';
                if ($layout === 'vertical' && $index > 0) {
                    $style = ' style="margin-top: ' . $button_spacing . ';"';
                } elseif ($layout === 'horizontal' && $index > 0) {
                    $style = ' style="margin-left: ' . $button_spacing . ';"';
                }
                ?>
                
                <button
                    type="button"
                    <?php if ($btn_id): ?>id="<?php echo esc_attr($btn_id); ?>"<?php endif; ?>
                    class="<?php echo $btn_class_string; ?>"
                    <?php echo $data_attrs; ?>
                    <?php if ($btn_disabled): ?>disabled<?php endif; ?>
                    <?php echo $style; ?>
                >
                    <?php if ($btn_icon): ?>
                        <span class="dashicons dashicons-<?php echo esc_attr($btn_icon); ?>"></span>
                    <?php endif; ?>
                    <?php echo $btn_text; ?>
                </button>
                
                <?php if ($btn_desc): ?>
                    <p class="description"><?php echo $btn_desc; ?></p>
                <?php endif; ?>
                
                <?php if ($layout === 'vertical'): ?>
                    <br />
                <?php endif; ?>
                
            <?php endforeach; ?>
        <?php else: ?>
            <p class="description" style="color: #d63638;"><?php echo esc_html__('No buttons configured.', 'lex-settings'); ?></p>
        <?php endif; ?>
    </td>
</tr>

