<?php
/**
 * Access card. Body lives in the shared common/edit-access-box.php partial.
 *
 * @var int $post_id Current player post ID (0 for a not-yet-saved player).
 */
if (!defined('ABSPATH')) {
  exit;
}

$access_scope  = 'player';
$access_locked = !leanpl_is_pro_active();
include LEANPL_DIR . '/includes/admin-new/partials/edit/common/edit-access-box.php';
