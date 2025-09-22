<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Elementor Integration Tab -->
<div id="elementor-integration" class="vapfem-tab-content">
    <div class="vapfem-card">
        <div class="vapfem-heading-2 vapfem-admin__main-title"><?php echo esc_html__('Elementor Integration', 'vapfem'); ?></div>
        <p class="vapfem-admin__subtitle"><?php echo esc_html__('Use our dedicated Elementor widgets to display and customize video and audio players anywhere on your website', 'vapfem'); ?></p>

        <!-- Elementor Widgets Section -->
        <div class="vapfem-heading-3 vapfem-admin__section-title"><?php echo esc_html__('Available Widgets', 'vapfem'); ?></div>
        <p class="vapfem-admin__subtitle"><?php echo esc_html__('If you are using Elementor, you have access to two dedicated widgets:', 'vapfem'); ?></p>

        <div class="vapfem-elementor-widgets">
            <!-- Video Player Widget -->
            <div class="vapfem-elementor-widget">
                <div class="vapfem-elementor-widget__header">
                    <div class="vapfem-elementor-widget__icon">📺</div>
                    <div class="vapfem-elementor-widget__info">
                        <div class="vapfem-elementor-widget__name"><?php echo esc_html__('Video Player', 'vapfem'); ?></div>
                        <div class="vapfem-elementor-widget__description"><?php echo esc_html__('Display and customize video players with full styling control', 'vapfem'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Audio Player Widget -->
            <div class="vapfem-elementor-widget">
                <div class="vapfem-elementor-widget__header">
                    <div class="vapfem-elementor-widget__icon">🎵</div>
                    <div class="vapfem-elementor-widget__info">
                        <div class="vapfem-elementor-widget__name"><?php echo esc_html__('Audio Player', 'vapfem'); ?></div>
                        <div class="vapfem-elementor-widget__description"><?php echo esc_html__('Display and customize audio players with full styling control', 'vapfem'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- How to Use Section -->
        <div class="vapfem-heading-3 vapfem-admin__section-title"><?php echo esc_html__('How to Use', 'vapfem'); ?></div>
        <div class="vapfem-elementor-steps">
            <div class="vapfem-elementor-step">
                <div class="vapfem-elementor-step__number">1</div>
                <div class="vapfem-elementor-step__content">
                    <div class="vapfem-elementor-step__title"><?php echo esc_html__('Open Elementor Editor', 'vapfem'); ?></div>
                    <div class="vapfem-elementor-step__description"><?php echo esc_html__('Edit any page or post with Elementor', 'vapfem'); ?></div>
                </div>
            </div>
            <div class="vapfem-elementor-step">
                <div class="vapfem-elementor-step__number">2</div>
                <div class="vapfem-elementor-step__content">
                    <div class="vapfem-elementor-step__title"><?php echo esc_html__('Search for Widgets', 'vapfem'); ?></div>
                    <div class="vapfem-elementor-step__description"><?php echo esc_html__('In the widget panel, search for "Video Player" or "Audio Player". You will see a badge called "LEAN" above the widget title.', 'vapfem'); ?></div>
                </div>
            </div>
            <div class="vapfem-elementor-step">
                <div class="vapfem-elementor-step__number">3</div>
                <div class="vapfem-elementor-step__content">
                    <div class="vapfem-elementor-step__title"><?php echo esc_html__('Drag & Drop', 'vapfem'); ?></div>
                    <div class="vapfem-elementor-step__description"><?php echo esc_html__('Drag the widget to your desired location on the page', 'vapfem'); ?></div>
                </div>
            </div>
            <div class="vapfem-elementor-step">
                <div class="vapfem-elementor-step__number">4</div>
                <div class="vapfem-elementor-step__content">
                    <div class="vapfem-elementor-step__title"><?php echo esc_html__('Configure & Style', 'vapfem'); ?></div>
                    <div class="vapfem-elementor-step__description"><?php echo esc_html__('Set your media source and customize the styling using the Style tab', 'vapfem'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>