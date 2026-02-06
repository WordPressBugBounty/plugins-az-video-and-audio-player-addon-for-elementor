<?php
if (!defined('ABSPATH')) {
    exit;
}

$settings = \Lex\Settings\V2\Settings::getInstance('leanpl');
?>
<div class="lpl-card">
    <div class="lpl-heading-2 lpl-admin__main-title"><?php echo esc_html__('Quick Start Guide', 'vapfem'); ?></div>
    <p class="lpl-admin__subtitle"><?php echo esc_html__('Welcome to the new and improved Video & Audio Player (Lean Player)! Get started in minutes.', 'vapfem'); ?></p>

    <!-- Rebranding Notice -->
    <div class="lpl-info-block lpl-info-block--note lex-mt-0 lex-mb-6">
        <div class="lpl-heading-3 lex-mb-3"><?php echo esc_html__('We\'ve Rebranded!', 'vapfem'); ?></div>
        <p><strong><?php echo esc_html__('AZ Video & Audio Player is now "Lean Player"', 'vapfem'); ?></strong></p>
        <p><?php echo esc_html__('Version 3.0.0 marks a major milestone: our rebranding checkpoint and major update. We\'ve rebranded our plugin under the LeanPlugins brand and renamed it from "AZ Video & Audio Player" to "Lean Player - Video & Audio Player for WordPress".', 'vapfem'); ?></p>
        <p><?php echo esc_html__('This update includes significant improvements and restructuring to make the plugin more performance-focused and aligned with our LeanPlugins brand philosophy. The plugin now supports WordPress, Elementor, Block Editor, and Classic Editor.', 'vapfem'); ?></p>
        <p><strong><?php echo esc_html__('Full name:', 'vapfem'); ?></strong> <?php echo esc_html__('Lean Player - Video and Audio Player for WordPress, Elementor, Block Editor and Classic Editor', 'vapfem'); ?></p>
    </div>

    <!-- What's New Section -->
    <div class="lpl-info-block lpl-info-block--success lex-mt-0">
        <div class="lpl-heading-3 lex-mb-3"><?php echo esc_html__('What\'s New in Version 3.0.0', 'vapfem'); ?></div>
        <p><?php echo esc_html__('Version 3.0.0 is our rebranding checkpoint and major update. We\'re excited to introduce improvements that make creating and managing players easier than ever:', 'vapfem'); ?></p>
        <ul>
            <li><strong><?php echo esc_html__('Global Player Settings:', 'vapfem'); ?></strong> <?php echo esc_html__('Set default behavior once for all players across your site. No need to configure every player individually!', 'vapfem'); ?></li>
            <li><strong><?php echo esc_html__('Simple Player Creation:', 'vapfem'); ?></strong> <?php echo esc_html__('Create video and audio players through an intuitive interface without touching shortcode attributes.', 'vapfem'); ?></li>
            <li><strong><?php echo esc_html__('One-Click Shortcode:', 'vapfem'); ?></strong> <?php echo esc_html__('After creating a player, just copy and paste a simple shortcode anywhere on your site.', 'vapfem'); ?></li>
            <li><strong><?php echo esc_html__('Backward Compatible:', 'vapfem'); ?></strong> <?php echo esc_html__('Existing shortcodes continue to work perfectly!', 'vapfem'); ?></li>
        </ul>
    </div>

    <!-- How It Works Section -->
    <div class="lpl-heading-2 lex-mt-6 lex-mb-0"><?php echo esc_html__('How It Works (3 Simple Steps)', 'vapfem'); ?></div>

    <div style="display: grid; gap: 20px;" class="lex-mb-6">
        <div class="lpl-info-block lpl-info-block--note">
            <div class="lpl-heading-3 lex-mb-3"><?php echo esc_html__('Step 1: Configure Global Defaults (Optional)', 'vapfem'); ?></div>
            <p><?php echo esc_html__('Go to', 'vapfem'); ?> <strong><?php echo esc_html__('Lean Player → Settings', 'vapfem'); ?></strong> <?php echo esc_html__('to set default behavior for all players. This is optional but saves time!', 'vapfem'); ?></p>
            <p><?php echo esc_html__('Examples: Default volume, autoplay settings, player controls, and more.', 'vapfem'); ?></p>
        </div>

        <div class="lpl-info-block lpl-info-block--note lex-mt-0">
            <div class="lpl-heading-3 lex-mb-3"><?php echo esc_html__('Step 2: Create Your Player', 'vapfem'); ?></div>
            <p><?php echo esc_html__('Go to', 'vapfem'); ?> <strong><?php echo esc_html__('Lean Player → Add New Player', 'vapfem'); ?></strong></p>
            <ul>
                <li><?php echo esc_html__('Choose player type (Video or Audio)', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Add your media URL (YouTube, Vimeo, or direct file)', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Customize player settings (or use global defaults)', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Click Publish', 'vapfem'); ?></li>
            </ul>
        </div>

        <div class="lpl-info-block lpl-info-block--note lex-mt-0">
            <div class="lpl-heading-3 lex-mb-3"><?php echo esc_html__('Step 3: Use the Shortcode', 'vapfem'); ?></div>
            <p><?php echo esc_html__('After creating your player, copy the shortcode and paste it anywhere on your site:', 'vapfem'); ?></p>
            <code style="background: #f0f0f0; border-radius: 4px; display: inline-block;" class="lex-py-2 lex-px-3">[lean_player id="123"]</code>
            <p class="lex-mt-3"><?php echo esc_html__('Or use PHP in your templates:', 'vapfem'); ?></p>
            <code style="background: #f0f0f0; border-radius: 4px; display: inline-block;" class="lex-py-2 lex-px-3"><?php echo esc_html('<?php echo do_shortcode(\'[lean_player id="123"]\'); ?>'); ?></code>
            <p class="lex-mt-3"><?php echo esc_html__('Works in posts, pages, widgets, and more!', 'vapfem'); ?></p>
        </div>
    </div>

    <!-- Two Approaches Section -->
    <div class="lpl-heading-2 lex-mt-6"><?php echo esc_html__('Two Ways to Create Players', 'vapfem'); ?></div>
    <p class="lpl-admin__subtitle lex-mb-4"><?php echo esc_html__('Plus: Elementor integration for page builder users', 'vapfem'); ?></p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;" class="lex-mb-6">
        <div class="lpl-info-block lpl-info-block--success lex-mt-0">
            <div class="lpl-heading-3 lex-mb-3"><?php echo esc_html__('Recommended: Use Player Manager', 'vapfem'); ?></div>
            <p><strong><?php echo esc_html__('Best for:', 'vapfem'); ?></strong> <?php echo esc_html__('Most users, especially those who want a simple interface', 'vapfem'); ?></p>
            <ul>
                <li><?php echo esc_html__('Visual interface, no code needed', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Inherit global settings automatically', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Manage all players in one place', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Simple shortcode:', 'vapfem'); ?> <code>[lean_player id="123"]</code></li>
            </ul>
            <p class="lex-mt-4">
                <a href="<?php echo admin_url('post-new.php?post_type=lean_player'); ?>" class="button button-primary"><?php echo esc_html__('Create Player Now', 'vapfem'); ?></a>
            </p>
        </div>

        <div class="lpl-info-block lpl-info-block--outline lex-mt-0">
            <div class="lpl-heading-3 lex-mb-3"><?php echo esc_html__('Direct Shortcodes', 'vapfem'); ?></div>
            <p><strong><?php echo esc_html__('Good for:', 'vapfem'); ?></strong> <?php echo esc_html__('Quick embed or programmatic use', 'vapfem'); ?></p>
            <ul>
                <li><?php echo esc_html__('No admin interface needed', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Configure everything via shortcode attributes', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Perfect for dynamic/programmatic content', 'vapfem'); ?></li>
                <li><?php echo esc_html__('Use:', 'vapfem'); ?> <code>[lean_video]</code> <?php echo esc_html__('or', 'vapfem'); ?> <code>[lean_audio]</code></li>
            </ul>
            <p class="lex-mt-4">
                <a href="#all-options" class="button lpl-admin__tab-link"><?php echo esc_html__('View Shortcode Options', 'vapfem'); ?></a>
            </p>
        </div>
    </div>

    <!-- Elementor Integration -->
    <div class="lpl-info-block lpl-info-block--note">
        <div class="lpl-heading-3 lex-mb-3"><?php echo esc_html__('Using with Elementor?', 'vapfem'); ?></div>
        <p><?php echo esc_html__('Our Elementor widgets continue to work perfectly! You can still:', 'vapfem'); ?></p>
        <ul>
            <li><?php echo esc_html__('Drag and drop Video Player or Audio Player widgets', 'vapfem'); ?></li>
            <li><?php echo esc_html__('Configure settings visually in Elementor', 'vapfem'); ?></li>
        </ul>
    </div>

    <!-- What's Coming Next -->
    <div class="lpl-info-block lpl-info-block--success">
        <div class="lpl-heading-2 lex-mb-3"><?php echo esc_html__('What\'s Coming Next', 'vapfem'); ?></div>
        <p><?php echo esc_html__('Version 3.0.0 is just the beginning! We\'re considering the following enhancements for future updates:', 'vapfem'); ?></p>
        <ul>
            <li><?php echo esc_html__('Player width setting', 'vapfem'); ?></li>
            <li><?php echo esc_html__('Video start time option', 'vapfem'); ?></li>
            <li><?php echo esc_html__('More styling options', 'vapfem'); ?></li>
            <li><?php echo esc_html__('More customization options', 'vapfem'); ?></li>
            <li><?php echo esc_html__('Subtitle and caption support', 'vapfem'); ?></li>
            <li><?php echo esc_html__('Playlist support', 'vapfem'); ?></li>
        </ul>
        <p><?php echo esc_html__('Stay tuned for more exciting features!', 'vapfem'); ?></p>
    </div>
</div>