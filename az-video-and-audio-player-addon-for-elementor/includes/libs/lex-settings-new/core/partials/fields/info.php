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
    'variant' => 'default', // 'default' = plain description, 'notice' = styled info box
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
$info_type = $field['variant'] ?? 'default';

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
        <?php if ( $info_type === 'notice' ) : ?>
        <div class="lex-notice">
            <svg class="lex-notice__icon" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M10 2a8 8 0 1 0 0 16A8 8 0 0 0 10 2Zm0 12a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V7a1 1 0 0 1 2 0v3Z"/>
            </svg>
            <span><?php echo wp_kses_post($content); ?></span>
        </div>
        <?php else : ?>
        <p class="description">
            <?php echo wp_kses_post($content); ?>
        </p>
        <?php endif; ?>
    </td>
</tr>

