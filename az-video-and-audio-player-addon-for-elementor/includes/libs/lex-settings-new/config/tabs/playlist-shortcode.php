<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="lpl-card">
    <div class="lpl-heading-2 lpl-admin__main-title"><?php echo esc_html__('Playlist', 'vapfem'); ?></div>
    <p class="lpl-admin__subtitle"><?php echo esc_html__('Embed a saved playlist by its ID', 'vapfem'); ?></p>

    <table class="lpl-shortcode-table">
        <thead>
            <tr>
                <th><?php echo esc_html__('Basic Shortcode', 'vapfem'); ?></th>
                <th class="lpl-shortcode-table__action-column"><?php echo esc_html__('Action', 'vapfem'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>[lean_playlist id="YOUR_PLAYLIST_ID"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_playlist id=&quot;YOUR_PLAYLIST_ID&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
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
                <td><?php echo esc_html__('Default Layout', 'vapfem'); ?></td>
                <td><code>[lean_playlist id="123"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_playlist id=&quot;123&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Bottom Panel', 'vapfem'); ?></td>
                <td><code>[lean_playlist id="123" position="bottom"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_playlist id=&quot;123&quot; position=&quot;bottom&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Dark Skin', 'vapfem'); ?></td>
                <td><code>[lean_playlist id="123" skin="dark"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_playlist id=&quot;123&quot; skin=&quot;dark&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
            <tr>
                <td><?php echo esc_html__('Autoplay Next', 'vapfem'); ?></td>
                <td><code>[lean_playlist id="123" autoplay_next="true"]</code></td>
                <td><button class="lex-copy-button" data-lex-copy="[lean_playlist id=&quot;123&quot; autoplay_next=&quot;true&quot;]"><?php echo esc_html__('Copy', 'vapfem'); ?></button></td>
            </tr>
        </tbody>
    </table>

    <div class="lpl-info-block lpl-info-block--note">
        <p>
            <?php echo esc_html__('Create and manage playlists under Lean Player -> Playlists.', 'vapfem'); ?>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=lean_playlist')); ?>" class="button"><?php echo esc_html__('Create Playlist', 'vapfem'); ?></a>
        </p>
    </div>

    <p class="lpl-admin__footer-text--card">
        <?php echo esc_html__('Need more customization? Check the', 'vapfem'); ?>
        <a href="#all-options" class="lpl-admin__tab-link" data-tab="all-options"><?php echo esc_html__('All Shortcode Options', 'vapfem'); ?></a>
        <?php echo esc_html__('tab for the complete list of available parameters.', 'vapfem'); ?>
    </p>
</div>
