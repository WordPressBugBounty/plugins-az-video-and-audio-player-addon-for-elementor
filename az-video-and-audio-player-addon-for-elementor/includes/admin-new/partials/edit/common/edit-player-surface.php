<?php
/**
 * Preview stage for the "source picked" left-column state. Empty container -
 * admin-new.js fills it with the real Player_Renderer markup returned by
 * the existing leanpl_live_preview AJAX action (class-live-preview-ajax.php,
 * same endpoint the classic post.php metabox's live preview panel uses) the
 * moment a source is picked, and player-utils.js's autoInit MutationObserver
 * turns that markup into a real Plyr instance - no manual init call here.
 * Nothing is written to the DB by loading this preview.
 */
if (!defined('ABSPATH')) {
  exit;
}
?>
<div data-lpl-preview-target class="lpl-box-border lpl-w-full lpl-min-h-[460px] lpl-shrink-0 lpl-flex lpl-flex-col lpl-justify-start lpl-items-center lpl-rounded-[10px] lpl-overflow-hidden lpl-transition-opacity lpl-duration-150">
  <div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink-soft lpl-font-normal lpl-text-center">
    <?php esc_html_e('Loading preview…', 'vapfem'); ?>
  </div>
</div>
