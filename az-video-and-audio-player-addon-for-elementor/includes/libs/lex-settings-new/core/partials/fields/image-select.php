<?php
/**
 * Image Select Field Template
 *
 * Renders a grid of selectable image cards. Single-select, backed by native
 * radio inputs so the value is collected with zero extra JS and keyboard
 * navigation works out of the box.
 *
 * Unlike `radio`/`select`, whose `options` is a `value => label` map, this type
 * needs several properties per option, so `options` is a list of arrays:
 *
 *   'options' => [
 *       ['value' => 'default', 'label' => 'Default', 'image' => 'https://…'],
 *       ['value' => 'modern',  'label' => 'Modern',  'image' => '…', 'tooltip' => 'A sleek look.'],
 *       ['value' => 'ambient', 'label' => 'Ambient', 'pro'   => true],
 *   ]
 *
 * Per option: `value` and `label` are required, `image`, `tooltip` and `pro`
 * are optional. An option with no `image` falls back to a placeholder icon.
 * PRO options can also be declared field-wide via `pro_options` (a list of
 * values), matching the `radio` field type.
 *
 * Field-wide options:
 * - `columns` (int, default 2): grid columns on desktop.
 * - `aspect_ratio` (string, default '16 / 9'): CSS aspect-ratio value for the
 *   image frame. Must match your preview images' actual ratio, or
 *   object-fit: cover will crop/misalign them - see lex-image-select-card__frame.
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
    'options'      => [], // list of ['value' => …, 'label' => …, 'image' => …, 'tooltip' => …, 'pro' => bool]
    'pro_options'  => [], // list of option values to lock, alternative to per-option 'pro'
    'disabled'     => false,
    'columns'      => 2,        // grid columns on desktop
    'aspect_ratio' => '16 / 9', // CSS aspect-ratio value, match your preview images
];

// Return early if just loading defaults
if (isset($load_defaults_only) && $load_defaults_only) {
    return $field_defaults;
}

// ============================================
// SAFETY CHECK
// ============================================
if (!isset($field) || !isset($value)) {
    echo wp_kses_post($fieldRenderer->renderFieldFallback($field['key'] ?? null, 'Field template: missing required variables'));
    return;
}

if (!isset($field['name']) || !isset($field['id'])) {
    echo wp_kses_post($fieldRenderer->renderFieldFallback($field['key'] ?? null, 'Field metadata incomplete (missing name or id)'));
    return;
}

// ============================================
// HELPER VARIABLES
// ============================================
$input_id      = $field['id'];
$input_name    = $field['name'];
$current_value = $value;
$is_disabled   = $field['disabled'];
$has_tooltip   = !empty($field['tooltip']);
$options       = is_array($field['options']) ? $field['options'] : [];
$pro_options   = is_array($field['pro_options'] ?? null) ? $field['pro_options'] : [];
$columns       = max(1, (int) $field['columns']);
$aspect_ratio  = $field['aspect_ratio'];

// Check if field is inherited (metabox "Use Global Option" support)
$is_inherited    = $fieldRenderer->isInherited($field);
$inherited_title = $fieldRenderer->getInheritedTitle($field);
$is_empty_value  = ($current_value === '' || $current_value === null);
$show_inherited  = ($is_inherited && $is_empty_value);

// Build class string for the grid
$grid_classes = ['lex-image-select-grid'];
if ($show_inherited) {
    $grid_classes[] = 'lex-field--inherited';
}
$grid_class = $fieldRenderer->classnames($grid_classes);

// Build class string for tr
$tr_classes = array_merge(['lex-field', 'lex-field-type--image-select'], $field['classes']);
if ($show_inherited) {
    $tr_classes[] = 'lex-field-row--inherited';
}
$tr_class = $fieldRenderer->classnames($tr_classes);

// ============================================
// RENDER HTML
// ============================================
?>
<tr class="<?php echo esc_attr($tr_class); ?>"<?php echo ($field['pro'] && isset($field['pro']['onclick'])) ? ' onclick="' . esc_attr($field['pro']['onclick']) . '()"' : ''; ?>>
    <th scope="row">
        <?php $fieldRenderer->renderLabel($field); ?>
        <?php if ($has_tooltip): ?>
            <?php $fieldRenderer->renderTooltip($field); ?>
        <?php endif; ?>
        <?php $fieldRenderer->renderLabelDescription($field); ?>
    </th>
    <td>
        <fieldset class="lex-image-select">
            <div
                class="<?php echo esc_attr($grid_class); ?>"
                style="--lex-image-select-columns: <?php echo esc_attr($columns); ?>; --lex-image-select-aspect-ratio: <?php echo esc_attr($aspect_ratio); ?>;"
                <?php if ($inherited_title): ?>title="<?php echo esc_attr($inherited_title); ?>"<?php endif; ?>
            >
                <?php
                $rendered = 0;
                foreach ($options as $option):
                    if (!is_array($option)) {
                        continue; // skip malformed option, don't break the grid
                    }

                    $opt_value = $option['value'] ?? '';
                    if ($opt_value === '') {
                        continue; // a card with no value can never be selected
                    }

                    $opt_label    = $option['label'] ?? '';
                    $opt_tooltip  = $option['tooltip'] ?? '';
                    $opt_image    = $option['image'] ?? '';
                    $opt_launcher = $option['launcher'] ?? '';
                    // esc_url() drops data: URIs (not in wp_allowed_protocols()), so
                    // inline previews are escaped as a plain attribute instead.
                    $is_data_image = (0 === stripos($opt_image, 'data:image/'));
                    $is_checked  = ($opt_value == $current_value);
                    $is_locked   = !empty($option['pro']) || in_array($opt_value, $pro_options);
                    $has_launcher = ($opt_launcher !== '' && !$is_locked);

                    // First card carries the field id so the <th> label targets it.
                    $opt_id = ($rendered === 0) ? $input_id : $input_id . '_' . $rendered;

                    $card_classes = ['lex-image-select-card'];
                    if ($is_locked) {
                        $card_classes[] = 'lex-image-select-card--locked';
                    }
                    $card_class = $fieldRenderer->classnames($card_classes);

                    $rendered++;
                    ?>
                    <div class="<?php echo esc_attr($card_class); ?>"<?php
                        if ($is_locked) {
                            echo ' onclick="openUpgradeModal(); return false;"';
                        } elseif ($has_launcher) {
                            echo ' onclick="' . esc_attr($opt_launcher) . '(this); return false;"';
                        }
                    ?>>
                        <label class="lex-image-select-card__control" for="<?php echo esc_attr($opt_id); ?>">
                            <input
                                type="radio"
                                class="lex-image-select-card__input"
                                id="<?php echo esc_attr($opt_id); ?>"
                                <?php if (empty($field['pro']) && !$is_locked && !$has_launcher): ?>name="<?php echo esc_attr($input_name); ?>"<?php endif; ?>
                                value="<?php echo esc_attr($opt_value); ?>"
                                <?php echo $is_checked ? 'checked' : ''; ?>
                                <?php echo ($is_disabled || $is_locked) ? 'disabled' : ''; ?>
                            />
                            <span class="lex-image-select-card__frame">
                                <?php if ($is_locked): ?>
                                    <span class="lex-image-select-card__lock">
                                        <span class="dashicons dashicons-lock"></span>
                                    </span>
                                <?php elseif ($opt_image !== ''): ?>
                                    <img
                                        class="lex-image-select-card__image"
                                        src="<?php echo $is_data_image ? esc_attr($opt_image) : esc_url($opt_image); ?>"
                                        alt=""
                                    />
                                <?php else: ?>
                                    <span class="lex-image-select-card__placeholder">
                                        <span class="dashicons dashicons-format-image"></span>
                                    </span>
                                <?php endif; ?>
                                <span class="lex-image-select-card__check">
                                    <span class="dashicons dashicons-yes"></span>
                                </span>
                            </span>
                        </label>
                        <div class="lex-image-select-card__meta">
                            <label class="lex-image-select-card__title" for="<?php echo esc_attr($opt_id); ?>"><?php echo wp_kses_post($opt_label); ?></label>
                            <?php if (!empty($opt_tooltip)): ?>
                                <span class="lex-tooltip lex-tooltip--compact">
                                    <span class="dashicons dashicons-editor-help"></span>
                                    <span class="lex-tooltip__text"><?php echo wp_kses_post($opt_tooltip); ?></span>
                                </span>
                            <?php endif; ?>
                            <?php if ($is_locked): ?>
                                <span class="lex-pro-badge">PRO</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php echo wp_kses_post($fieldRenderer->renderDescription($field)); ?>
        </fieldset>
    </td>
</tr>
