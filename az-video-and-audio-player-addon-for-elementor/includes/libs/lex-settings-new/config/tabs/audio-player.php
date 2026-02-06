<?php
if (!defined('ABSPATH')) {
    exit;
}

$settings = \Lex\Settings\V2\Settings::getInstance('leanpl');
?>
<div class="lpl-card">
    <div class="lpl-heading-2 lpl-admin__main-title"><?php echo esc_html__('Audio Player', 'vapfem'); ?></div>
    <p class="lpl-admin__subtitle"><?php echo esc_html__('Supports HTML5 audio files (MP3, WAV, OGG) for podcasts, music, and audio content', 'vapfem'); ?></p>

    <!-- Quick Start Section -->
    <div class="lpl-heading-3 lpl-admin__section-title"><?php echo esc_html__('Quick Start', 'vapfem'); ?></div>

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

    <!-- Demo Shortcodes Section -->
    <div class="lpl-heading-3 lpl-admin__section-title"><?php echo esc_html__('Demo Shortcodes', 'vapfem'); ?></div>
    <p class="lpl-admin__subtitle"><?php echo esc_html__('Test the audio player by copying these ready-to-use shortcodes', 'vapfem'); ?></p>

    <table class="lpl-shortcode-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Audio Type', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Demo Shortcode', 'vapfem'); ?></th>
                <th class="lpl-shortcode-table__action-column"><?php echo esc_html__('Action', 'vapfem'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo esc_html__('HTML5 Audio:', 'vapfem'); ?></td>
                <td><code>[lean_audio url="https://download.samplelib.com/mp3/sample-15s.mp3"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_audio url=&quot;https://download.samplelib.com/mp3/sample-15s.mp3&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
        </tbody>
    </table>

    <!-- Advanced Examples Section -->
    <div class="lpl-heading-3 lpl-admin__section-title"><?php echo esc_html__('Other Examples', 'vapfem'); ?></div>

    <table class="lpl-shortcode-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Example Type', 'vapfem'); ?></th>
                <th><?php echo esc_html__('Advanced Shortcode', 'vapfem'); ?></th>
                <th class="lpl-shortcode-table__action-column"><?php echo esc_html__('Action', 'vapfem'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo __('Self Hosted / CDN:', 'vapfem'); ?></td>
                <td><code>[lean_audio url="https://yoursite.com/wp-content/uploads/2025/05/podcast-1.mp3"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_audio url=&quot;https://yoursite.com/wp-content/uploads/2025/05/podcast-1.mp3&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Minimal Controls:', 'vapfem'); ?></td>
                <td><code>[lean_audio
     url="https://example.com/audio.mp3"
     controls="play,progress"
]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_audio url=&quot;https://example.com/audio.mp3&quot; controls=&quot;play,progress&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Lower Volume on Initial Load:', 'vapfem'); ?></td>
                <td><code>[lean_audio
     url="https://example.com/audio.mp3"
     volume="0.3"
]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_audio url=&quot;https://example.com/audio.mp3&quot; volume=&quot;0.3&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('No Download:', 'vapfem'); ?></td>
                <td><code>[lean_audio
     url="https://example.com/audio.mp3"
     controls="play,progress,volume,mute"
]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_audio url=&quot;https://example.com/audio.mp3&quot; controls=&quot;play,progress,volume,mute&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
        </tbody>
    </table>

    <!-- Footer Text Professional Card Style -->
    <p class="lpl-admin__footer-text--card">
        <?php echo esc_html__('Need more customization? Check the 👉', 'vapfem'); ?>
        <a href="#all-options" class="lpl-admin__tab-link" data-tab="all-options"><?php echo esc_html__('All Shortcode Options', 'vapfem'); ?></a>
        <?php echo esc_html__(' tab for complete list of available parameters.', 'vapfem'); ?>
    </p>
</div>