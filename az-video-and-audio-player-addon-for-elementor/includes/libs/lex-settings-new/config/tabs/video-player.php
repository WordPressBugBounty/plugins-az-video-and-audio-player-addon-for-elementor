<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="lpl-card">
    <div class="lpl-heading-2 lpl-admin__main-title"><?php echo esc_html__('Video Player', 'vapfem'); ?></div>
    <p class="lpl-admin__subtitle"><?php echo esc_html__('YouTube, Vimeo, HTML5 (MP4, WebM)', 'vapfem'); ?></p>

    <table class="lpl-shortcode-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Basic Shortcode', 'vapfem'); ?></th>
                <th class="lpl-shortcode-table__action-column"><?php echo esc_html__('Action', 'vapfem'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>[lean_video url="YOUR_VIDEO_URL"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_video url=&quot;YOUR_VIDEO_URL&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
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
                <td><?php echo esc_html__('YouTube', 'vapfem'); ?></td>
                <td><code>[lean_video url="https://www.youtube.com/watch?v=bTqVqk7FSmY"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_video url=&quot;https://www.youtube.com/watch?v=bTqVqk7FSmY&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Vimeo', 'vapfem'); ?></td>
                <td><code>[lean_video url="https://vimeo.com/76979871"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_video url=&quot;https://vimeo.com/76979871&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Self Hosted', 'vapfem'); ?></td>
                <td><code>[lean_video url="https://yoursite.com/video.mp4"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_video url=&quot;https://yoursite.com/video.mp4&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Minimal Controls', 'vapfem'); ?></td>
                <td><code>[lean_video url="https://vimeo.com/123456789" controls="play,progress" hide_controls="true"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_video url=&quot;https://vimeo.com/123456789&quot; controls=&quot;play,progress&quot; hide_controls=&quot;true&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Multi Quality', 'vapfem'); ?></td>
                <td><code>[lean_video sources="video-720p.mp4|720,video-1080p.mp4|1080" quality_default="720"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_video sources=&quot;video-720p.mp4|720,video-1080p.mp4|1080&quot; quality_default=&quot;720&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
        </tbody>
    </table>

    <p class="lpl-admin__footer-text--card">
        <?php echo esc_html__('Need more customization? Check the', 'vapfem'); ?>
        <a href="#all-options" class="lpl-admin__tab-link" data-tab="all-options"><?php echo esc_html__('All Shortcode Options', 'vapfem'); ?></a>
        <?php echo esc_html__('tab for the complete list of available parameters.', 'vapfem'); ?>
    </p>
</div>
