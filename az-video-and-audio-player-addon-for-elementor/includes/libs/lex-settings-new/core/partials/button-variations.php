<?php
/**
 * Button Variations Section
 * 
 * Static HTML showcasing button styles and variations.
 * This is a reference section, not a form field.
 */
?>

<!-- ============================================
        SECTION: BUTTON VARIATIONS
        ============================================ -->
<div class="lex-settings-section lex-settings-section--button-variations">
    <h2 class="lex-settings-section__title">
        <span><?php echo esc_html__('Button Variations', 'lex-settings'); ?></span>
    </h2>

    <table class="form-table" role="presentation">
        <!-- Primary Buttons -->
        <tr>
            <th scope="row"><?php echo esc_html__('Primary Buttons', 'lex-settings'); ?></th>
            <td>
                <button type="button" class="lex-btn lex-btn--primary"><?php echo esc_html__('Primary Button', 'lex-settings'); ?></button>
                <button type="button" class="lex-btn lex-btn--primary" style="margin-left: 8px;">
                    <span class="dashicons dashicons-saved"></span>
                    <?php echo esc_html__('With Icon', 'lex-settings'); ?>
                </button>
                <button type="button" class="lex-btn lex-btn--primary lex-btn--hero" style="margin-left: 8px;"><?php echo esc_html__('Hero Size', 'lex-settings'); ?></button>
                <p class="description"><?php echo esc_html__('Primary action buttons for main interactions.', 'lex-settings'); ?></p>
            </td>
        </tr>

        <!-- Secondary Buttons -->
        <tr>
            <th scope="row"><?php echo esc_html__('Secondary Buttons', 'lex-settings'); ?></th>
            <td>
                <button type="button" class="lex-btn lex-btn--secondary"><?php echo esc_html__('Secondary Button', 'lex-settings'); ?></button>
                <button type="button" class="lex-btn lex-btn--secondary" style="margin-left: 8px;">
                    <span class="dashicons dashicons-download"></span>
                    <?php echo esc_html__('Export Data', 'lex-settings'); ?>
                </button>
                <button type="button" class="lex-btn lex-btn--secondary lex-btn--hero" style="margin-left: 8px;"><?php echo esc_html__('Hero Size', 'lex-settings'); ?></button>
                <p class="description"><?php echo esc_html__('Secondary actions and alternative options.', 'lex-settings'); ?></p>
            </td>
        </tr>

        <!-- Small Buttons -->
        <tr>
            <th scope="row"><?php echo esc_html__('Small Buttons', 'lex-settings'); ?></th>
            <td>
                <button type="button" class="lex-btn lex-btn--small lex-btn--primary"><?php echo esc_html__('Small Primary', 'lex-settings'); ?></button>
                <button type="button" class="lex-btn lex-btn--small lex-btn--secondary" style="margin-left: 8px;"><?php echo esc_html__('Small Secondary', 'lex-settings'); ?></button>
                <p class="description"><?php echo esc_html__('Compact buttons for inline actions.', 'lex-settings'); ?></p>
            </td>
        </tr>

        <!-- Gray/Reset Buttons -->
        <tr>
            <th scope="row"><?php echo esc_html__('Utility Buttons', 'lex-settings'); ?></th>
            <td>
                <button type="button" class="lex-btn lex-btn--gray-v1-b2">
                    <span class="dashicons dashicons-image-rotate"></span>
                    <?php echo esc_html__('Reset', 'lex-settings'); ?>
                </button>
                <button type="button" class="lex-btn lex-btn--secondary" style="margin-left: 8px;">
                    <span class="dashicons dashicons-trash"></span>
                    <?php echo esc_html__('Clear Cache', 'lex-settings'); ?>
                </button>
                <button type="button" class="lex-btn lex-btn--secondary" style="margin-left: 8px;">
                    <span class="dashicons dashicons-database"></span>
                    <?php echo esc_html__('Optimize DB', 'lex-settings'); ?>
                </button>
                <p class="description"><?php echo esc_html__('Utility actions like reset, clear, optimize.', 'lex-settings'); ?></p>
            </td>
        </tr>

        <!-- Icon Variations -->
        <tr>
            <th scope="row"><?php echo esc_html__('Icon Positions', 'lex-settings'); ?></th>
            <td>
                <button type="button" class="lex-btn lex-btn--primary">
                    <span class="dashicons dashicons-yes"></span>
                    <?php echo esc_html__('Icon Left', 'lex-settings'); ?>
                </button>
                <button type="button" class="lex-btn lex-btn--primary" style="margin-left: 8px;">
                    <?php echo esc_html__('Icon Right', 'lex-settings'); ?>
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
                <p class="description"><?php echo esc_html__('Dashicons can be placed before or after text.', 'lex-settings'); ?></p>
            </td>
        </tr>

        <!-- Link-style Buttons -->
        <tr>
            <th scope="row"><?php echo esc_html__('Link-style Actions', 'lex-settings'); ?></th>
            <td>
                <a href="#" class="lex-btn lex-btn--primary lex-btn--cta">
                    <?php echo esc_html__('Upgrade to Pro →', 'lex-settings'); ?>
                </a>
                <a href="#" style="margin-left: 16px; color: #2271b1; text-decoration: none;">
                    <?php echo esc_html__('Learn More →', 'lex-settings'); ?>
                </a>
                <p class="description"><?php echo esc_html__('Link buttons for navigation and CTAs.', 'lex-settings'); ?></p>
            </td>
        </tr>
    </table>
</div>

