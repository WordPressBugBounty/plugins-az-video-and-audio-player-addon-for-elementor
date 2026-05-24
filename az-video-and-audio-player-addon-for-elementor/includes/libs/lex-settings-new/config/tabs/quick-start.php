<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="lpl-qs-page">

    <h2 class="lpl-qs-page__heading"><?php esc_html_e( 'Get started in seconds.', 'vapfem' ); ?></h2>

    <div class="lpl-qs-cards lpl-qs-grid-3">

        <!-- Card: Quick embed -->
        <div class="lpl-qs-card" data-card="quick">
            <div class="lpl-qs-card__header">
                <span class="lpl-qs-card__icon"></span>
                <h3 class="lpl-qs-card__title"><?php esc_html_e( 'Quick embed', 'vapfem' ); ?></h3>
            </div>
            <p class="lpl-qs-card__desc"><?php esc_html_e( 'You have the video/audio URL and want to embed quickly.', 'vapfem' ); ?></p>
            <span class="lpl-qs-card__cta"><?php esc_html_e( 'Show me how', 'vapfem' ); ?> →</span>
            <div class="lpl-qs-card__check">✓</div>
        </div>

        <!-- Card: Reuse -->
        <div class="lpl-qs-card" data-card="reuse">
            <div class="lpl-qs-card__header">
                <span class="lpl-qs-card__icon"></span>
                <h3 class="lpl-qs-card__title"><?php esc_html_e( 'Save once, embed anywhere', 'vapfem' ); ?></h3>
            </div>
            <p class="lpl-qs-card__desc"><?php esc_html_e( 'You have a few videos/audios and want to reuse them across different places.', 'vapfem' ); ?></p>
            <span class="lpl-qs-card__cta"><?php esc_html_e( 'Show me how', 'vapfem' ); ?> →</span>
            <div class="lpl-qs-card__check">✓</div>
        </div>

        <!-- Card: Playlist -->
        <div class="lpl-qs-card" data-card="playlist">
            <div class="lpl-qs-card__header">
                <span class="lpl-qs-card__icon"></span>
                <h3 class="lpl-qs-card__title"><?php esc_html_e( 'Create a playlist', 'vapfem' ); ?></h3>
            </div>
            <p class="lpl-qs-card__desc"><?php esc_html_e( 'You want to group multiple videos or audios into a playlist and embed it.', 'vapfem' ); ?></p>
            <span class="lpl-qs-card__cta"><?php esc_html_e( 'Show me how', 'vapfem' ); ?> →</span>
            <div class="lpl-qs-card__check">✓</div>
        </div>

    </div>

    <!-- Panel: Quick embed -->
    <div class="lpl-qs-panel" id="lpl-qs-panel-quick">

        <h3 class="lpl-qs-step-heading"><?php esc_html_e( 'Step 1: Copy a shortcode', 'vapfem' ); ?></h3>

        <div class="lpl-qs-step-body">

                <?php /* YouTube — open by default */ ?>
                <div class="lex-settings-section lex-settings-section--accordion lex-settings-section--collapsible lpl-qs-accordion-section"
                     data-collapsible="true" data-section-id="qs-youtube" data-instance-id="leanpl"
                     data-accordion-group="qs-sources">
                    <div class="lex-settings-section__title">
                        <span><?php esc_html_e( 'YouTube', 'vapfem' ); ?></span>
                        <span class="dashicons dashicons-arrow-down-alt2 lex-settings-section__chevron"></span>
                    </div>
                    <div class="lpl-qs-accordion-body">
                        <div class="lpl-qs-code-row">
                            <span class="lpl-qs-row-label"><?php esc_html_e( 'Full URL', 'vapfem' ); ?></span>
                            <code>[lean_video url="https://www.youtube.com/watch?v=bTqVqk7FSmY"]</code>
                            <button class="lpl-qs-btn lpl-qs-btn--copy lex-copy-button" data-lex-copy='[lean_video url="https://www.youtube.com/watch?v=bTqVqk7FSmY"]'><?php esc_html_e( 'Copy', 'vapfem' ); ?></button>
                        </div>
                        <div class="lpl-qs-code-row">
                            <span class="lpl-qs-row-label"><?php esc_html_e( 'Video ID only', 'vapfem' ); ?></span>
                            <code>[lean_video url="bTqVqk7FSmY" type="youtube"]</code>
                            <button class="lpl-qs-btn lpl-qs-btn--copy lex-copy-button" data-lex-copy='[lean_video url="bTqVqk7FSmY" type="youtube"]'><?php esc_html_e( 'Copy', 'vapfem' ); ?></button>
                        </div>
                    </div>
                </div>

                <?php /* Vimeo — collapsed */ ?>
                <div class="lex-settings-section lex-settings-section--accordion lex-settings-section--collapsible lex-settings-section--collapsed lpl-qs-accordion-section"
                     data-collapsible="true" data-section-id="qs-vimeo" data-instance-id="leanpl"
                     data-accordion-group="qs-sources">
                    <div class="lex-settings-section__title">
                        <span><?php esc_html_e( 'Vimeo', 'vapfem' ); ?></span>
                        <span class="dashicons dashicons-arrow-down-alt2 lex-settings-section__chevron"></span>
                    </div>
                    <div class="lpl-qs-accordion-body">
                        <div class="lpl-qs-code-row">
                            <span class="lpl-qs-row-label"><?php esc_html_e( 'Full URL', 'vapfem' ); ?></span>
                            <code>[lean_video url="https://vimeo.com/76979871"]</code>
                            <button class="lpl-qs-btn lpl-qs-btn--copy lex-copy-button" data-lex-copy='[lean_video url="https://vimeo.com/76979871"]'><?php esc_html_e( 'Copy', 'vapfem' ); ?></button>
                        </div>
                        <div class="lpl-qs-code-row">
                            <span class="lpl-qs-row-label"><?php esc_html_e( 'Video ID only', 'vapfem' ); ?></span>
                            <code>[lean_video url="76979871" type="vimeo"]</code>
                            <button class="lpl-qs-btn lpl-qs-btn--copy lex-copy-button" data-lex-copy='[lean_video url="76979871" type="vimeo"]'><?php esc_html_e( 'Copy', 'vapfem' ); ?></button>
                        </div>
                    </div>
                </div>

                <?php /* HTML5 / MP4 — collapsed */ ?>
                <div class="lex-settings-section lex-settings-section--accordion lex-settings-section--collapsible lex-settings-section--collapsed lpl-qs-accordion-section"
                     data-collapsible="true" data-section-id="qs-html5" data-instance-id="leanpl"
                     data-accordion-group="qs-sources">
                    <div class="lex-settings-section__title">
                        <span><?php esc_html_e( 'HTML5 / MP4', 'vapfem' ); ?></span>
                        <span class="dashicons dashicons-arrow-down-alt2 lex-settings-section__chevron"></span>
                    </div>
                    <div class="lpl-qs-accordion-body">
                        <div class="lpl-qs-code-row">
                            <span class="lpl-qs-row-label"><?php esc_html_e( 'File URL', 'vapfem' ); ?></span>
                            <code>[lean_video url="https://files.vidstack.io/sprite-fight/720p.mp4"]</code>
                            <button class="lpl-qs-btn lpl-qs-btn--copy lex-copy-button" data-lex-copy='[lean_video url="https://files.vidstack.io/sprite-fight/720p.mp4"]'><?php esc_html_e( 'Copy', 'vapfem' ); ?></button>
                        </div>
                    </div>
                </div>

                <?php /* Audio — collapsed */ ?>
                <div class="lex-settings-section lex-settings-section--accordion lex-settings-section--collapsible lex-settings-section--collapsed lpl-qs-accordion-section"
                     data-collapsible="true" data-section-id="qs-audio" data-instance-id="leanpl"
                     data-accordion-group="qs-sources">
                    <div class="lex-settings-section__title">
                        <span><?php esc_html_e( 'Audio (MP3, WAV, OGG)', 'vapfem' ); ?></span>
                        <span class="dashicons dashicons-arrow-down-alt2 lex-settings-section__chevron"></span>
                    </div>
                    <div class="lpl-qs-accordion-body">
                        <div class="lpl-qs-code-row">
                            <span class="lpl-qs-row-label"><?php esc_html_e( 'File URL', 'vapfem' ); ?></span>
                            <code>[lean_audio url="https://www.w3schools.com/html/horse.mp3"]</code>
                            <button class="lpl-qs-btn lpl-qs-btn--copy lex-copy-button" data-lex-copy='[lean_audio url="https://www.w3schools.com/html/horse.mp3"]'><?php esc_html_e( 'Copy', 'vapfem' ); ?></button>
                        </div>
                    </div>
                </div>

                <p class="lpl-qs-source-hint"><?php esc_html_e( 'YouTube/Vimeo: pass the full URL or just the ID. For ID-only, add type="youtube" or type="vimeo". HTML5/Audio: paste a direct file URL.', 'vapfem' ); ?></p>

        </div>

        <h3 class="lpl-qs-step-heading"><?php esc_html_e( 'Step 2: Add it to any page', 'vapfem' ); ?></h3>
        <p class="lpl-qs-step-desc"><?php esc_html_e( 'Works immediately.', 'vapfem' ); ?></p>

        <div class="lpl-qs-note">
            <?php esc_html_e( 'Using Elementor? Drag the Video Player or Audio Player widget directly onto the page. No shortcode needed.', 'vapfem' ); ?>
        </div>
        <div class="lpl-qs-note lpl-qs-note--info">
            <?php esc_html_e( 'Want more control? See the', 'vapfem' ); ?>
            <a href="#all-options" class="lpl-admin__tab-link" data-tab="all-options"><?php esc_html_e( 'All Options', 'vapfem' ); ?></a>
            <?php esc_html_e( 'tab.', 'vapfem' ); ?>
        </div>
    </div>

    <!-- Panel: Reuse -->
    <div class="lpl-qs-panel" id="lpl-qs-panel-reuse">
        <h4 class="lpl-qs-panel__title"><?php esc_html_e( 'Save once, embed anywhere', 'vapfem' ); ?></h4>

        <div class="lpl-qs-tint-block lpl-qs-how-it-works">
            <p><?php esc_html_e( 'Create a saved player in the Player Manager. It gets its own shortcode:', 'vapfem' ); ?> <strong>[lean_player id="123"]</strong></p>
            <p><?php esc_html_e( 'Paste that shortcode on as many pages as you need. When you update the player, every embed reflects the change automatically.', 'vapfem' ); ?></p>
        </div>

        <p class="lpl-qs-panel__hint"><?php esc_html_e( 'Create your first saved player:', 'vapfem' ); ?></p>
        <div class="lpl-qs-actions">
            <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=lean_player' ) ); ?>" class="lpl-qs-btn lpl-qs-btn--primary"><?php esc_html_e( 'Create a Video / Audio Player', 'vapfem' ); ?></a>
        </div>
    </div>

    <!-- Panel: Playlist -->
    <div class="lpl-qs-panel" id="lpl-qs-panel-playlist">
        <h4 class="lpl-qs-panel__title"><?php esc_html_e( 'Create a playlist', 'vapfem' ); ?></h4>

        <div class="lpl-qs-playlist-steps lpl-qs-grid-3">
            <div class="lpl-qs-playlist-step lpl-qs-tint-block">
                <div class="lpl-qs-num-badge lpl-qs-num-badge--sm">1</div>
                <h4><?php esc_html_e( 'Create your players', 'vapfem' ); ?></h4>
                <p><?php esc_html_e( 'Each video or audio file needs a saved player first. Create them in Player Manager.', 'vapfem' ); ?></p>
            </div>
            <div class="lpl-qs-playlist-step lpl-qs-tint-block">
                <div class="lpl-qs-num-badge lpl-qs-num-badge--sm">2</div>
                <h4><?php esc_html_e( 'Build the playlist', 'vapfem' ); ?></h4>
                <p><?php esc_html_e( 'Go to Playlist Manager, create a new playlist, and add your players to it.', 'vapfem' ); ?></p>
            </div>
            <div class="lpl-qs-playlist-step lpl-qs-tint-block">
                <div class="lpl-qs-num-badge lpl-qs-num-badge--sm">3</div>
                <h4><?php esc_html_e( 'Embed it anywhere', 'vapfem' ); ?></h4>
                <p><?php esc_html_e( 'Copy the playlist shortcode and paste it into any post, page, or widget.', 'vapfem' ); ?></p>
            </div>
        </div>

        <div class="lpl-qs-code-block">
            <div class="lpl-qs-code-label"><?php esc_html_e( 'Playlist shortcode', 'vapfem' ); ?></div>
            <div class="lpl-qs-code-row">
                <code>[lean_playlist id="YOUR_PLAYLIST_ID"]</code>
                <button class="lpl-qs-btn lpl-qs-btn--copy lex-copy-button" data-lex-copy='[lean_playlist id="YOUR_PLAYLIST_ID"]'><?php esc_html_e( 'Copy', 'vapfem' ); ?></button>
            </div>
        </div>

        <div class="lpl-qs-actions">
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=lean_playlist' ) ); ?>" class="lpl-qs-btn lpl-qs-btn--primary"><?php esc_html_e( 'Go to Playlist Manager', 'vapfem' ); ?></a>
            <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=lean_player' ) ); ?>" class="lpl-qs-btn lpl-qs-btn--secondary"><?php esc_html_e( 'Create a Player first', 'vapfem' ); ?></a>
        </div>

        <div class="lpl-qs-note lpl-qs-note--info">
            <?php esc_html_e( 'Using Elementor? The Playlist Widget lets you drag and drop a playlist directly onto any page. No shortcode needed.', 'vapfem' ); ?>
        </div>
    </div>

    <!-- Footer strip -->
    <div class="lpl-qs-footer-strip">
        <span><?php esc_html_e( 'Want to set global defaults for all players?', 'vapfem' ); ?></span>
        <a href="#settings" class="lpl-admin__tab-link" data-tab="settings"><?php esc_html_e( 'Open Settings →', 'vapfem' ); ?></a>
    </div>

</div>

<script>
( function () {
    var cards  = document.querySelectorAll( '.lpl-qs-card' );
    var panels = document.querySelectorAll( '.lpl-qs-panel' );

    function selectCard( id ) {
        cards.forEach( function ( c ) { c.classList.remove( 'is-active' ); } );
        panels.forEach( function ( p ) { p.classList.remove( 'is-visible' ); } );

        var card  = document.querySelector( '.lpl-qs-card[data-card="' + id + '"]' );
        var panel = document.getElementById( 'lpl-qs-panel-' + id );
        if ( card )  { card.classList.add( 'is-active' ); }
        if ( panel ) { panel.classList.add( 'is-visible' ); }
    }

    cards.forEach( function ( card ) {
        card.addEventListener( 'click', function () {
            selectCard( card.dataset.card );
        } );
    } );
}() );
</script>
