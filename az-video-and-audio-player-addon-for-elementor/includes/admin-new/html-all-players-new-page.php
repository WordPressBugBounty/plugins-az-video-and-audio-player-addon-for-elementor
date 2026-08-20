<?php
// Prevent direct access
if (!defined('ABSPATH')) {
  exit;
}

use LeanPL\Admin_New\All_Players_Data;

$data = new All_Players_Data();
$counts = $data->get_counts();
$current_status = isset($_GET['status']) ? sanitize_key(wp_unslash($_GET['status'])) : 'all';
if (!in_array($current_status, ['all', 'publish', 'draft', 'future', 'trash'], true)) {
  $current_status = 'all';
}

$columns = [
  'title'     => ['label' => __('TITLE', 'vapfem'),     'width' => 'lpl-w-[320px]'],
  'source'    => ['label' => __('SOURCE', 'vapfem'),    'width' => 'lpl-w-[150px] lpl-shrink-0 lpl-ml-auto'],
  'created'   => ['label' => __('CREATED', 'vapfem'),   'width' => 'lpl-w-[132px] lpl-shrink-0', 'sortable' => 'date'],
  'status'    => ['label' => __('STATUS', 'vapfem'),    'width' => 'lpl-w-[118px] lpl-shrink-0', 'sortable' => 'status'],
  'shortcode' => ['label' => __('SHORTCODE', 'vapfem'), 'width' => 'lpl-w-[190px] lpl-shrink-0'],
  'actions'   => ['label' => __('ACTIONS', 'vapfem'),   'width' => 'lpl-w-[108px] lpl-shrink-0', 'align' => 'right'],
];

?>
<div class="lpl-admin lpl-all-players-new-page">
  <?php
  \Lex\Settings\V2\Settings::getInstance( 'leanpl' )->getTemplate( 'header' );
  ?>
<div
  class="lpl-box-border lpl-w-full lpl-max-w-page lpl-mx-auto lpl-h-fit lpl-flex lpl-flex-col lpl-gap-0 lpl-p-[0px_36px_36px_36px] min-[1600px]:lpl-px-0 lpl-justify-start lpl-items-start lpl-overflow-hidden">
  <div
    class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_0px_22px_0px] lpl-justify-between lpl-items-center">
    <div
      class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[5px] lpl-justify-start lpl-items-start">
      <div
        class="lpl-text-[20px]/[normal] lpl-box-border lpl-text-[#1B2233] lpl-font-medium lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Media Players', 'vapfem'); ?>
      </div>
      <div
        class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#6A7180] lpl-font-normal lpl-text-left [white-space:nowrap]">
        <?php echo esc_html(sprintf(
          /* translators: 1: total players, 2: video count, 3: audio count. */
          __('%1$d players · %2$d video · %3$d audio', 'vapfem'),
          $counts['all'],
          $counts['video'],
          $counts['audio']
        )); ?>
      </div>
    </div>
    <a href="<?php echo esc_url(admin_url('admin.php?page=lean-player-edit')); ?>"
      class="lpl-btn lpl-btn--md lpl-btn--indigo [box-shadow:0px_6px_16px_0px_#0000001F]">
      <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'plus' ); ?>
      <div class="lpl-box-border lpl-text-left [white-space:nowrap]">
        <?php esc_html_e('Add Player', 'vapfem'); ?>
      </div>
    </a>
  </div>
  <div
    class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 [box-shadow:0px_10px_30px_0px_#1B22330F] lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start lpl-bg-[#FFFFFF] [outline:1px_solid_#E3E6EB] [outline-offset:-0.5px] lpl-rounded-[16px] lpl-overflow-hidden">
    <?php
    $page_slug = 'lean_player-all-players-new';
    $search_placeholder = __('Search players', 'vapfem');
    $filter_groups = [
      ['heading' => __('MEDIA TYPE', 'vapfem'), 'filter' => 'type', 'options' => [
        ['value' => 'video', 'label' => __('Video', 'vapfem')],
        ['value' => 'audio', 'label' => __('Audio', 'vapfem')],
      ]],
      ['heading' => __('SOURCE', 'vapfem'), 'filter' => 'source', 'options' => [
        ['value' => 'youtube', 'label' => __('YouTube', 'vapfem')],
        ['value' => 'vimeo', 'label' => __('Vimeo', 'vapfem')],
        ['value' => 'self-hosted', 'label' => __('Media Library', 'vapfem')],
        ['value' => 'external', 'label' => __('External File', 'vapfem')],
      ]],
      ['heading' => __('STATUS', 'vapfem'), 'filter' => 'status', 'options' => [
        ['value' => 'publish', 'label' => __('Published', 'vapfem')],
        ['value' => 'draft', 'label' => __('Draft', 'vapfem')],
      ]],
    ];
    $toolbar_row_class = 'lpl-relative lpl-box-border lpl-w-full lpl-h-[69.5px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[16px_20px] lpl-justify-between lpl-items-center [border-width:0px_0px_1px_0px] [border-style:solid] [border-color:#EEF0F3] [margin:0px_0px_-0.5px_0px]';
    $toolbar_left_class = 'lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center';
    $toolbar_right_class = 'lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[10px] lpl-justify-start lpl-items-center';
    include LEANPL_DIR . '/includes/admin-new/partials/list/list-toolbar.php';
    ?>
    <?php include LEANPL_DIR . '/includes/admin-new/partials/list/list-bulk-bar.php'; ?>
    <div
      class="lpl-box-border lpl-w-full lpl-h-[42.5px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[13px_20px] lpl-justify-start lpl-items-center [border-width:0px_0px_1px_0px] [border-style:solid] [border-color:#EEF0F3] [margin:0px_0px_-0.5px_0px]">
      <div
        class="lpl-box-border lpl-w-[44px] lpl-shrink-0 lpl-h-fit lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-start lpl-items-start">
        <div data-lpl-checkbox data-lpl-select-all role="checkbox" tabindex="0" aria-checked="false"
          class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-[17px] lpl-shrink-0 lpl-h-[17px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-[#FFFFFF] [outline:1.5px_solid_#E3E6EB] [outline-offset:-0.75px] aria-checked:lpl-bg-[#4F46E5] aria-checked:[outline:none] lpl-rounded-[5px]">
          <?php echo \LeanPL\Admin_New\Admin_New_Icons::get( 'checkbox-check' ); ?>
        </div>
      </div>
      <?php foreach ($columns as $leanpl_col_key => $leanpl_col): ?>
        <?php if ($leanpl_col_key === 'title'): ?>
          <div class="lpl-text-[11px]/[normal] lpl-box-border <?php echo esc_attr($leanpl_col['width']); ?> lpl-text-[#9AA1AE] lpl-font-bold lpl-tracking-[0.6px] lpl-text-left">
            <?php echo esc_html($leanpl_col['label']); ?>
          </div>
        <?php elseif (!empty($leanpl_col['sortable'])): ?>
          <div data-lpl-sort-col="<?php echo esc_attr($leanpl_col['sortable']); ?>"
            class="lpl-box-border <?php echo esc_attr($leanpl_col['width']); ?> lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[5px] lpl-justify-start lpl-items-center lpl-cursor-pointer">
            <div class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-[#9AA1AE] lpl-font-bold lpl-tracking-[0.6px] lpl-text-left [white-space:nowrap]">
              <?php echo esc_html($leanpl_col['label']); ?>
            </div>
            <?php echo \LeanPL\Admin_New\Admin_New_Icons::get('header-sort-arrow'); ?>
          </div>
        <?php elseif (!empty($leanpl_col['align']) && $leanpl_col['align'] === 'right'): ?>
          <div class="lpl-text-[11px]/[normal] lpl-box-border <?php echo esc_attr($leanpl_col['width']); ?> lpl-text-[#9AA1AE] lpl-font-bold lpl-tracking-[0.6px] lpl-text-right">
            <?php echo esc_html($leanpl_col['label']); ?>
          </div>
        <?php else: ?>
          <div class="lpl-box-border <?php echo esc_attr($leanpl_col['width']); ?> lpl-h-fit lpl-flex lpl-flex-row lpl-gap-[5px] lpl-justify-start lpl-items-center">
            <div class="lpl-text-[11px]/[normal] lpl-box-border lpl-text-[#9AA1AE] lpl-font-bold lpl-tracking-[0.6px] lpl-text-left [white-space:nowrap]">
              <?php echo esc_html($leanpl_col['label']); ?>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php
    $rows = $data->get_rows($current_status);
    if (empty($rows)):
      ?>
      <div
        class="lpl-box-border lpl-w-full lpl-h-[160px] lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[10px] lpl-justify-center lpl-items-center lpl-p-[14px_20px] [border-width:0px_0px_1px_0px] [border-style:solid] [border-color:#EEF0F3] [margin:0px_0px_-0.5px_0px]">
        <div class="lpl-text-[15px]/[normal] lpl-box-border lpl-text-[#6A7180] lpl-font-semibold lpl-text-center">
          <?php esc_html_e('No players found', 'vapfem'); ?>
        </div>
        <a href="<?php echo esc_url(admin_url('admin.php?page=lean-player-edit')); ?>"
          class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#4F46E5] lpl-font-semibold lpl-text-center [white-space:nowrap] ![text-decoration:none]">
          <?php esc_html_e('Add Player', 'vapfem'); ?>
        </a>
      </div>
      <?php
    else:
      foreach ($rows as $row):
        include LEANPL_DIR . '/includes/admin-new/partials/list/player-row.php';
      endforeach;
    endif;
    ?>
    <?php include LEANPL_DIR . '/includes/admin-new/partials/list/list-footer.php'; ?>
  </div>
</div>
</div>