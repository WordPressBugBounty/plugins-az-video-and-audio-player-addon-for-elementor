<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="lpl-card">
    <div class="lpl-heading-2 lpl-admin__main-title"><?php echo esc_html__('Audio Player', 'vapfem'); ?></div>
    <p class="lpl-admin__subtitle"><?php echo esc_html__('MP3, WAV, OGG - podcasts, music, audio content', 'vapfem'); ?></p>

    <table class="lpl-shortcode-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Basic Shortcode', 'vapfem'); ?></th>
                <th class="lpl-shortcode-table__action-column"><?php echo esc_html__('Action', 'vapfem'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>[lean_audio url="YOUR_AUDIO_URL"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_audio url=&quot;YOUR_AUDIO_URL&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
        </tbody>
    </table>

    <table class="lpl-shortcode-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Example', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Shortcode', 'vapfem'); ?></th>
                <th class="lpl-shortcode-table__action-column"><?php echo esc_html__('Action', 'vapfem'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo esc_html__('Self Hosted', 'vapfem'); ?></td>
                <td><code>[lean_audio url="https://yoursite.com/audio.mp3"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_audio url=&quot;https://yoursite.com/audio.mp3&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Minimal Controls', 'vapfem'); ?></td>
                <td><code>[lean_audio url="https://example.com/audio.mp3" controls="play,progress"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_audio url=&quot;https://example.com/audio.mp3&quot; controls=&quot;play,progress&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Lower Volume', 'vapfem'); ?></td>
                <td><code>[lean_audio url="https://example.com/audio.mp3" volume="0.3"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_audio url=&quot;https://example.com/audio.mp3&quot; volume=&quot;0.3&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
        </tbody>
    </table>

    <p class="lpl-admin__footer-text--card">
        <?php echo esc_html__('Need more customization? Check the', 'vapfem'); ?>
        <a href="#all-options" class="lpl-admin__tab-link" data-tab="all-options"><?php echo esc_html__('All Shortcode Options', 'vapfem'); ?></a>
        <?php echo esc_html__('tab for the complete list of available parameters.', 'vapfem'); ?>
    </p>
</div>
