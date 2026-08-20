/**
 * Generic parent -> child row visibility for the admin-new edit screens
 * (player + playlist). A row with data-lpl-show-if="<field>:<value1>,<value2>"
 * (semicolon-separated for multiple ANDed conditions) is revealed only when
 * the named form field currently holds one of the listed values. <field> is
 * the exact form control name attribute - works the same for a <select>, a
 * text/number input, or a switch's hidden input.
 *
 * Every data-lpl-show-if row starts with lpl-hidden already in its class
 * list server-side (see the card partials) - this file only ever removes
 * or re-adds that class, it never decides the row's initial state. That
 * matters: two earlier versions animated the collapse (measured max-height,
 * then a single CSS attribute driving max-height+opacity together) and both
 * left rows occupying empty space on first page load before this script had
 * run. Starting hidden in the markup means there's nothing to race - a
 * child row simply isn't in the layout until JS confirms its condition.
 *
 * Separate from admin-new.js on purpose: this is a self-contained concern
 * (row visibility only, never touches the live-preview AJAX payload) that
 * both edit screens can opt into by adding the attribute + lpl-hidden, with
 * no changes needed here. A screen with no data-lpl-show-if rows is a no-op.
 */
(function ($) {
  'use strict';

  function fieldValue(name) {
    var $el = $('[data-lpl-edit-form] [name="' + name + '"]').first();
    return $el.length ? String($el.val()) : null;
  }

  function conditionMet(spec) {
    return spec.split(';').every(function (clause) {
      var parts = clause.split(':');
      var field = parts[0];
      var allowed = parts[1].split(',');
      var current = fieldValue(field);
      return current !== null && allowed.indexOf(current) !== -1;
    });
  }

  function evaluateAll() {
    $('[data-lpl-edit-form] [data-lpl-show-if]').each(function () {
      var $row = $(this);
      $row.toggleClass('lpl-hidden', !conditionMet($row.attr('data-lpl-show-if')));
    });
  }

  $(function () {
    var $form = $('[data-lpl-edit-form]');
    if (!$form.length || !$form.find('[data-lpl-show-if]').length) { return; }

    evaluateAll();

    // Selects/text/number fire native 'change' already. Hidden inputs don't
    // fire it on their own - included here for fields like _player_type
    // (edit-section-behavior.php) that admin-new.js updates via
    // .val().trigger('change') when the picked source changes.
    $form.on('change', 'select, input[type="text"], input[type="number"], input[type="hidden"]', evaluateAll);

    // Switches don't dispatch 'change' - admin-new.js's toggle() sets the
    // hidden input's value directly via jQuery .val(). Listen on the same
    // click/keydown triggers, deferred so admin-new.js's own handler (bound
    // earlier - this file is enqueued after it) has already updated the
    // value before we re-read it.
    $form.on('click keydown', '[data-lpl-switch]', function (e) {
      if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') { return; }
      setTimeout(evaluateAll, 0);
    });
  });
})(jQuery);
