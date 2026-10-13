<?php
/**
 * Access card body, shared by the player and playlist Edit screens: one
 * "Who can watch" select plus the one extra box the chosen option needs
 * (roles, membership levels, or a password).
 *
 * Roles and levels are checkbox lists without a name; admin-new.js
 * (initCsvCheckboxGroups) writes the ticked values, comma-separated, into
 * the named hidden input, because the save request carries one value per
 * field name.
 *
 * Not part of the field registry (the classic metabox has no Access
 * fields, and the registry mirrors it). Whatever saves these fields hooks
 * leanpl/admin_new/player_saved and leanpl/admin_new/playlist_saved.
 *
 * Locked edition: the select stays clickable so every choice is visible,
 * but it has no name (nothing is sent on save) and every choice except
 * Everyone carries a "(PRO)" suffix, which is the only locked marker (no
 * badge next to the label). Picking one opens the upgrade popup
 * and snaps back to Everyone (admin-new.js, initAccessUpsell). The extra
 * boxes aren't rendered at all.
 *
 * @var int    $post_id      Current post ID (0 for a not-yet-saved item).
 * @var string $access_scope 'player' or 'playlist'.
 * @var bool   $access_locked Whether the edition locks this card.
 */
if (!defined('ABSPATH')) {
  exit;
}

$access_who      = ($post_id > 0) ? (string) get_post_meta($post_id, '_access_who', true) : '';
$access_who      = array_key_exists($access_who, leanpl_admin_new_access_who_options()) ? $access_who : 'everyone';
$access_roles    = ($post_id > 0) ? array_filter(array_map('trim', explode(',', (string) get_post_meta($post_id, '_access_roles', true)))) : [];
$access_levels   = ($post_id > 0) ? array_filter(array_map('trim', explode(',', (string) get_post_meta($post_id, '_access_levels', true)))) : [];
$access_password = ($post_id > 0) ? (string) get_post_field('post_password', $post_id) : '';
$access_groups   = leanpl_get_access_level_groups();

// Every choice is always listed. "Members of a level" with no membership
// plugin active shows a note naming the plugins with built-in levels and
// pointing everyone else to roles, instead of disappearing. The locked
// edition always shows Everyone: no lock applies there, whatever a past
// install stored.
$access_options = leanpl_admin_new_access_who_options();
if ($access_locked) {
  $access_who = 'everyone';
}

$access_row_class = 'lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start';
$access_label_class = 'lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-semibold lpl-text-left [white-space:nowrap]';
$access_hint_class  = 'lpl-text-[12px]/[1.5] lpl-box-border lpl-text-[#767D86] lpl-font-normal lpl-text-left';
$access_warn_class  = 'lpl-text-[12px]/[1.5] lpl-box-border lpl-w-full lpl-p-[8px_12px] lpl-rounded-[8px] lpl-bg-[#FCEFD6] lpl-text-[#B45309] lpl-font-medium lpl-text-left';
$access_check_class = 'lpl-inline-flex lpl-items-center lpl-gap-[6px] lpl-text-[13px]/[normal] lpl-text-ink';
$access_name = function ($name) use ($access_locked) {
  return $access_locked ? '' : ' name="' . esc_attr($name) . '"';
};
?>
<div class="lpl-box-border lpl-w-full lpl-flex lpl-flex-col lpl-gap-[14px]" data-lpl-access-box>

  <div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-between lpl-items-center">
    <div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center">
      <div class="<?php echo esc_attr($access_label_class); ?>">
        <?php esc_html_e('Who can watch', 'vapfem'); ?>
      </div>
    </div>
    <select<?php echo $access_name('_access_who'); ?><?php echo $access_locked ? ' data-lpl-access-upsell' : ''; ?> class="lpl-h-[36px]">
      <?php foreach ($access_options as $access_value => $access_label) :
        if ($access_locked && $access_value !== 'everyone') {
          $access_label .= ' (' . __('PRO', 'vapfem') . ')';
        }
      ?>
        <option value="<?php echo esc_attr($access_value); ?>"<?php selected($access_who, $access_value); ?>><?php echo esc_html($access_label); ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php if (!$access_locked) : ?>

    <div data-lpl-show-if="_access_who:logged_in" class="<?php echo $access_who === 'logged_in' ? '' : 'lpl-hidden '; ?><?php echo esc_attr($access_row_class); ?>">
      <div class="<?php echo esc_attr($access_label_class); ?>"><?php esc_html_e('Limit to roles', 'vapfem'); ?></div>
      <div class="<?php echo esc_attr($access_hint_class); ?>"><?php esc_html_e('Leave all unticked to allow any logged-in user. Using a membership plugin? Tick the role it gives your members.', 'vapfem'); ?></div>
      <input type="hidden" name="_access_roles" value="<?php echo esc_attr(implode(',', $access_roles)); ?>" data-lpl-csv-input="_access_roles">
      <div class="lpl-flex lpl-flex-wrap lpl-gap-x-[14px] lpl-gap-y-[6px]">
        <?php foreach (wp_roles()->get_names() as $access_role => $access_role_name) : ?>
          <label class="<?php echo esc_attr($access_check_class); ?>">
            <input type="checkbox" value="<?php echo esc_attr($access_role); ?>" data-lpl-csv-for="_access_roles"<?php checked(in_array($access_role, $access_roles, true)); ?>>
            <?php echo esc_html(translate_user_role($access_role_name)); ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div data-lpl-show-if="_access_who:level" class="<?php echo $access_who === 'level' ? '' : 'lpl-hidden '; ?><?php echo esc_attr($access_row_class); ?>">
      <input type="hidden" name="_access_levels" value="<?php echo esc_attr(implode(',', $access_levels)); ?>" data-lpl-csv-input="_access_levels">
      <?php
      // Non-empty when the saved levels belong to a membership plugin that
      // is no longer active, so nobody but editors can watch right now.
      $access_level_warning = (string) apply_filters('leanpl/access/level_warning', '', $post_id);
      ?>
      <?php if ($access_level_warning !== '') : ?>
        <div class="<?php echo esc_attr($access_warn_class); ?>" role="status"><?php echo esc_html($access_level_warning); ?></div>
      <?php elseif (empty($access_groups)) : ?>
        <div class="<?php echo esc_attr($access_hint_class); ?>">
          <?php esc_html_e('Install Paid Memberships Pro or MemberPress to pick levels here. Using a different membership plugin? Choose "Logged-in users" and tick your members\' role.', 'vapfem'); ?>
        </div>
      <?php endif; ?>
      <?php foreach ($access_groups as $access_group) : ?>
        <div class="<?php echo esc_attr($access_label_class); ?>"><?php echo esc_html($access_group['label']); ?></div>
        <?php if (!empty($access_group['levels'])) : ?>
          <div class="<?php echo esc_attr($access_hint_class); ?>"><?php esc_html_e('Tick who can watch.', 'vapfem'); ?></div>
        <?php endif; ?>
        <div class="lpl-flex lpl-flex-wrap lpl-gap-x-[14px] lpl-gap-y-[6px]">
          <?php if (empty($access_group['levels'])) : ?>
            <span class="<?php echo esc_attr($access_hint_class); ?>"><?php esc_html_e('No levels yet. Add one in your membership plugin first.', 'vapfem'); ?></span>
          <?php endif; ?>
          <?php foreach ($access_group['levels'] as $access_level_key => $access_level_name) : ?>
            <label class="<?php echo esc_attr($access_check_class); ?>">
              <input type="checkbox" value="<?php echo esc_attr($access_level_key); ?>" data-lpl-csv-for="_access_levels"<?php checked(in_array((string) $access_level_key, $access_levels, true)); ?>>
              <?php echo esc_html($access_level_name); ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div data-lpl-show-if="_access_who:password" class="<?php echo $access_who === 'password' ? '' : 'lpl-hidden '; ?><?php echo esc_attr($access_row_class); ?>">
      <label class="<?php echo esc_attr($access_label_class); ?>" for="lpl-access-password"><?php esc_html_e('Password', 'vapfem'); ?></label>
      <div class="lpl-box-border lpl-w-full lpl-h-[36px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_12px] lpl-justify-start lpl-items-center lpl-bg-surface [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[8px]">
        <input type="text" id="lpl-access-password" name="_access_password"
          value="<?php echo esc_attr($access_password); ?>"
          placeholder="<?php esc_attr_e('Enter a password', 'vapfem'); ?>"
          autocomplete="off"
          class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none placeholder:lpl-text-label-secondary [appearance:none] [-webkit-appearance:none]" />
      </div>
      <div class="<?php echo esc_attr($access_hint_class); ?>"><?php esc_html_e('Visitors who enter it can watch in that browser for 10 days. Changing it locks everyone out again.', 'vapfem'); ?></div>
    </div>

  <?php endif; ?>

  <?php if ($access_scope === 'playlist') : ?>
    <div class="<?php echo esc_attr($access_hint_class); ?>">
      <?php esc_html_e('This setting covers the whole playlist. To lock only some videos or tracks, open that player and change its own "Who can watch". Locked ones show a lock icon in Playlist Items.', 'vapfem'); ?>
    </div>
  <?php else : ?>
    <div class="<?php echo esc_attr($access_hint_class); ?>">
      <?php esc_html_e('This applies everywhere this player is shown, including inside playlists. Site admins and editors can always watch.', 'vapfem'); ?>
    </div>
  <?php endif; ?>
</div>
