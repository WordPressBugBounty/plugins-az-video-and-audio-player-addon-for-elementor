<?php
/**
 * Bare frontend template for lean_player / lean_playlist previews.
 *
 * Swapped in via Player_Preview::swap_preview_template() (template_include)
 * whenever is_preview() is true for one of these post types, in place of
 * whatever single template the active theme would otherwise use. It never
 * calls get_header()/get_footer(), so no theme chrome (header, footer,
 * sidebar, widgets) is involved - the player renders identically on any
 * theme instead of depending on that theme's markup/selectors.
 *
 * wp_head()/wp_footer() still run, so plugin-enqueued player CSS/JS and any
 * other plugin hooked into those actions keep working as expected.
 */
if (!defined('ABSPATH')) {
  exit;
}

$lpl_preview_post_id   = get_the_ID();
$lpl_preview_post_type = get_post_type();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  .lpl-preview-body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px;
    box-sizing: border-box;
    background: #F1F3F5;
  }
  .lpl-preview-stage {
    width: 100%;
    max-width: 900px;
  }
</style>
<?php wp_head(); ?>
</head>
<body <?php body_class('lpl-preview-body'); ?>>
<?php
// Required for the admin bar to render via its primary path
// (wp_admin_bar_render() hooks wp_body_open at priority 0). Without this,
// WP falls back to rendering it from wp_footer instead - back-compat for
// pre-5.2 themes only, not something a template should rely on.
wp_body_open();
?>
<div class="lpl-preview-stage">
<?php
if ('lean_playlist' === $lpl_preview_post_type) {
  echo do_shortcode('[lean_playlist id="' . (int) $lpl_preview_post_id . '"]');
} else {
  echo do_shortcode('[lean_player id="' . (int) $lpl_preview_post_id . '"]');
}
?>
</div>
<?php wp_footer(); ?>
</body>
</html>
