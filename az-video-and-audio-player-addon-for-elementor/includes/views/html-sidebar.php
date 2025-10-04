<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Right Sidebar -->
<div class="vapfem-layout__sidebar">
    <!-- Support Section -->
    <div class="vapfem-card vapfem-admin__support">
        <div class="vapfem-heading-2"><?php echo esc_html__('Customer Support', 'vapfem'); ?></div>
        <div class="vapfem-margin-block--20">
            <?php echo esc_html__('Having issues with the plugin? Create a support ticket in our official forum.', 'vapfem'); ?>
        </div>
        <a href="https://wordpress.org/support/plugin/az-video-and-audio-player-addon-for-elementor/" target="_blank" class="vapfem-primary-button">
            <?php echo esc_html__('Create Support Ticket', 'vapfem'); ?>
        </a>
    </div>

    <!-- Service Section -->
    <div class="vapfem-card vapfem-admin__service vapfem-service--sidebar">
        <div class="vapfem-service__badge"><?php echo esc_html__('7+ Years Experience', 'vapfem'); ?></div>
        <div class="vapfem-service__heading"><?php echo esc_html__('Need a WordPress Developer?', 'vapfem'); ?></div>
        <div class="vapfem-margin-block--20">
            <?php echo esc_html__('Hi! I\'m the developer behind this plugin.', 'vapfem'); ?>
            <br>
            <br>
            <?php echo esc_html__('I help businesses, educators, and content creators with high quality WordPress development. From small fixes, feature enhancements to complete custom websites. Let\'s talk about your project goals.', 'vapfem'); ?>
        </div>

        <div class="contact-section">
            <div class="vapfem-service__contact-heading"><?php echo esc_html__('Contact', 'vapfem'); ?></div>
            <div class="vapfem-service__contact-item">
                <span class="vapfem-service__contact-icon">📧</span>
                <strong><?php echo esc_html__('Email:', 'vapfem'); ?></strong><span style="color: #E53E3E;">&nbsp;helloAlberuni@gmail.com</span>
            </div>
            <button class="vapfem-copy-button" data-copy="helloAlberuni@gmail.com"><?php echo esc_html__('Copy Email', 'vapfem'); ?></button>
            <br>
            <br>
            <div class="vapfem-service__contact-item">
                <span class="vapfem-service__contact-icon">📅</span>
                <strong><?php echo esc_html__('Free 15-30 minute Discovery Call Available', 'vapfem'); ?></strong>
            </div>
        </div>
    </div>
</div>