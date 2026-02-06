<?php
if (!defined('ABSPATH')) {
    exit;
}

$settings = \Lex\Settings\V2\Settings::getInstance('leanpl');

// ═══════════════════════════════════════════════════════════
// SECTION: Export Settings
// Purpose: Export current settings as JSON file
// ═══════════════════════════════════════════════════════════
$settings->sectionRenderer->startSection('export-settings', esc_html__('Export Settings', 'vapfem'), ['disable_save_button' => true]);

// Export Button
$settings->fieldRenderer->render('action-buttons', 'export_settings', [
    'label' => esc_html__('Export Settings', 'vapfem'),
    'buttons' => [
        [
            'id' => 'lex-export-settings',
            'action' => 'export_settings',
            'text' => esc_html__('Export Settings', 'vapfem'),
            'icon' => 'download',
            'desc' => esc_html__('Download your current settings as a JSON file for backup.', 'vapfem'),
            'class' => 'lex-btn--secondary',
        ]
    ],
    'layout' => 'vertical',
]);

$settings->sectionRenderer->endSection();

// ═══════════════════════════════════════════════════════════
// SECTION: Import Settings
// Purpose: Import settings from JSON file
// ═══════════════════════════════════════════════════════════
$settings->sectionRenderer->startSection('import-settings', esc_html__('Import Settings', 'vapfem'), ['disable_save_button' => true]);

// Import Pattern - File upload field with import button
?>
<tr class="lex-field--file lex-field--import-file">
    <th scope="row">
        <label for="lex-import-file"><?php echo esc_html__('Upload Settings File', 'vapfem'); ?></label>
    </th>
    <td>
        <input type="file" id="lex-import-file" accept=".json" />
        <p class="description"><?php echo esc_html__('Select a previously exported settings JSON file.', 'vapfem'); ?></p>
        <br />
        <button type="button" class="lex-btn lex-btn--secondary lex-action-btn" id="lex-import-settings" data-action="import_settings">
            <span class="dashicons dashicons-upload"></span>
            <?php echo esc_html__('Import Settings', 'vapfem'); ?>
        </button>
    </td>
</tr>
<?php

$settings->sectionRenderer->endSection();
