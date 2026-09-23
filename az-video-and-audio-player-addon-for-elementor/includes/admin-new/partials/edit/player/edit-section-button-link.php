<?php
/**
 * Button Link card. Body lives in the shared common/edit-button-link-box.php
 * partial.
 *
 * @var int $post_id Current player post ID (0 for a not-yet-saved player).
 */
if (!defined('ABSPATH')) {
  exit;
}

$button_link_type = 'player';
include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-button-link-box.php';
