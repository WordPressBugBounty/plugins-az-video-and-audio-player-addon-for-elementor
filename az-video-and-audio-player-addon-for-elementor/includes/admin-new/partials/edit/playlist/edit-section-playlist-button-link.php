<?php
/**
 * Playlist Button Link card. Body lives in the shared
 * common/edit-button-link-box.php partial.
 *
 * @var int $post_id Current playlist post ID (0 for a not-yet-saved playlist).
 */
if (!defined('ABSPATH')) {
  exit;
}

$button_link_type = 'playlist';
include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-button-link-box.php';
