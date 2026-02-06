<?php
/**
 * Media Field Template
 * 
 * Renders a media field with WordPress Media Library integration.
 * Stores attachment ID and shows preview with URL.
 * 
 * Expected variables:
 * - $field (array): Normalized field configuration
 * - $value (mixed): Current value for this field (attachment ID)
 * - $load_defaults_only (bool): If true, return defaults and exit
 */

// ============================================
// TYPE DEFAULTS
// ============================================
$field_defaults = [
    'input_classes'   => ['regular-text'],
    'wrapper_classes' => ['lex-media-wrapper'],
    'button_text'     => __('Select File', 'lex-settings'),
    'remove_text'     => __('Remove', 'lex-settings'),
    'button_classes'  => ['button'],
    'remove_classes'  => ['button'],
    'library_type'    => ['image'], // Default to images
    'preview_readonly' => true,
    'placeholder'     => '', // Placeholder text for preview input when empty
    'disabled'        => false,
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
$media_id = !empty($value) ? absint($value) : '';
$media_url = $media_id ? wp_get_attachment_url($media_id) : '';
$is_disabled = $field['disabled'];
$has_tooltip = !empty($field['tooltip']);
$button_text = $field['button_text'];
$remove_text = $field['remove_text'];
$button_classes = $field['button_classes'];
$remove_classes = $field['remove_classes'];
$library_type = $field['library_type'];
$preview_readonly = $field['preview_readonly'];
$placeholder = $field['placeholder'];

// Build class strings
$input_class = $fieldRenderer->classnames($field['input_classes']);
$wrapper_class = $fieldRenderer->classnames($field['wrapper_classes']);
$button_class = $fieldRenderer->classnames($button_classes);
$remove_class = $fieldRenderer->classnames($remove_classes);

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--media'], $field['classes']);
$tr_class = $fieldRenderer->classnames($tr_classes);

// Prepare library type for JavaScript (JSON encode)
$library_type_json = json_encode($library_type);

// Check if this is an image field
$is_image_field = in_array('image', $library_type, true);

// Get image preview URL if it's an image
$preview_image_url = '';
if ($is_image_field && $media_id) {
    $preview_image_url = wp_get_attachment_image_url($media_id, 'thumbnail');
}

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
                type="hidden"
                id="<?php echo esc_attr($input_id); ?>"
                <?php if (empty($field['pro'])): ?>name="<?php echo esc_attr($input_name); ?>"<?php endif; ?>
                value="<?php echo esc_attr($media_id); ?>"
                data-media-field="true"
                data-library-type="<?php echo esc_attr($library_type_json); ?>"
            />
            <?php if ($is_image_field): ?>
                <div class="lex-media__preview-image-wrapper" id="<?php echo esc_attr($input_id); ?>_preview_wrapper" style="<?php echo $preview_image_url ? '' : 'display:none;'; ?>">
                    <img 
                        src="<?php echo esc_url($preview_image_url); ?>" 
                        alt="Preview" 
                        class="lex-media__preview-image lex-media__preview-image--clickable"
                        id="<?php echo esc_attr($input_id); ?>_preview_image"
                        data-media-preview="true"
                        data-target-input="<?php echo esc_attr($input_id); ?>"
                        style="cursor: pointer;"
                        title="<?php echo esc_attr__('Click to change image', 'lex-settings'); ?>"
                    />
                </div>
            <?php endif; ?>
            <input
                type="text"
                id="<?php echo esc_attr($input_id); ?>_preview"
                class="lex-media__preview <?php echo $input_class; ?>"
                value="<?php echo esc_url($media_url); ?>"
                placeholder="<?php echo esc_attr($placeholder); ?>"
                style="width:70%; margin-right:10px;"
                readonly
                <?php if ($is_disabled): ?>disabled<?php endif; ?>
            />

            <div class="lex-media__buttons">
                <button
                    type="button"
                    class="lex-media__button <?php echo $button_class; ?>"
                    id="<?php echo esc_attr($input_id); ?>_button"
                    data-media-button="true"
                    data-target-input="<?php echo esc_attr($input_id); ?>"
                    <?php if ($is_disabled): ?>disabled<?php endif; ?>
                >
                    <?php echo esc_html($button_text); ?>
                </button>
                <button
                    type="button"
                    class="lex-media__remove <?php echo $remove_class; ?>"
                    id="<?php echo esc_attr($input_id); ?>_remove"
                    data-media-remove="true"
                    data-target-input="<?php echo esc_attr($input_id); ?>"
                    <?php if ($is_disabled || !$media_id): ?>disabled<?php endif; ?>
                >
                    <?php echo esc_html($remove_text); ?>
                </button>
            </div>
        </div>
        
        <?php echo $fieldRenderer->renderDescription($field); ?>
    </td>
</tr>

