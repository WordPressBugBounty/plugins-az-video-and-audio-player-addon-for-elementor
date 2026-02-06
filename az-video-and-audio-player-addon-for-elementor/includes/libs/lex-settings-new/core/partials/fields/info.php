<?php
/**
 * Info Field Template
 * 
 * Renders an informational message field.
 * Displays content with minimal styling - no input field.
 * 
 * Expected variables:
 * - $field (array): Normalized field configuration
 * - $value (mixed): Current value (not used for info fields)
 * - $load_defaults_only (bool): If true, return defaults and exit
 */

// ============================================
// TYPE DEFAULTS
// ============================================
$field_defaults = [
    'content' => '', // Text content (can include <br> tags or other allowed HTML)
    'type' => 'default', // Future: 'default', 'warning', 'success', 'info', etc.
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
$content = $field['content'] ?? '';
$info_type = $field['type'] ?? 'default';

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--info'], $field['classes']);
if ($info_type !== 'default') {
    $tr_classes[] = 'lex-field-info--' . $info_type;
}
$tr_class = $fieldRenderer->classnames($tr_classes);

// ============================================
// RENDER HTML
// ============================================
?>
<tr class="<?php echo esc_attr($tr_class); ?>">
    <td colspan="2">
        <p class="description">
            <?php echo wp_kses_post($content); ?>
        </p>
    </td>
</tr>

