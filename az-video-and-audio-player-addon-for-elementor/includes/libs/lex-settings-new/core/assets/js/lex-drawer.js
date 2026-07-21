/**
 * Lex Drawer
 *
 * Non-modal side panel. No backdrop, so the page behind stays interactive —
 * and that single decision drives the rest of the contract:
 *
 *   - aria-modal="false". Focus is NOT trapped: trapping focus in a non-modal
 *     dialog violates WCAG 2.1.2 and contradicts the point of dropping the
 *     backdrop. Tab cycles out into the page.
 *   - ESC closes only when focus is inside the drawer. Otherwise ESC while
 *     typing anywhere else on the page would nuke it.
 *   - inert while closed, toggled instead of hidden/display:none (which would
 *     kill the transition). A drawer can hold a full form inside the post
 *     form; without inert those fields stay tabbable while shut.
 *   - No scroll lock.
 *   - Light-dismiss: a pointer press outside closes it. Overlays the drawer
 *     itself opens (wp.media, the upgrade modal, select2 dropdowns) render
 *     outside .lex-drawer in the DOM, so they must be treated as inside or
 *     picking a file from Quick Add would close the drawer under you.
 *
 * API:
 *   window.lexDrawer.open(idOrEl, opts)   opts: { returnFocusTo: Element }
 *   window.lexDrawer.close(idOrEl)
 *   window.lexDrawer.toggle(idOrEl, opts)
 *   window.lexDrawer.isOpen(idOrEl)
 *
 * Events, on the drawer element, bubbling (detail: { drawerId }):
 *   lexDrawerOpened / lexDrawerClosed
 *
 * Markup triggers, delegated on document so late-injected nodes work:
 *   <button type="button" data-lex-drawer-open="drawer-id"
 *           data-lex-drawer-vtab="quick-add">
 *   <button type="button" data-lex-drawer-close>
 */

(function() {
    'use strict';

    // Must match the transition duration in lex-drawer.css.
    var TRANSITION_MS = 250;

    // A press inside any of these does not count as "outside". Everything after
    // .lex-drawer is an overlay the drawer can spawn, which the browser appends
    // to <body> rather than into the drawer's subtree.
    var KEEP_OPEN_WITHIN = [
        '.lex-drawer',
        '[data-lex-drawer-open]',
        '.media-modal',
        '.media-modal-backdrop',
        '.lex-modal-backdrop',
        '.select2-container',
        '.ui-datepicker',
        '.wp-picker-container'
    ].join(',');

    var returnFocus = {};

    function resolve(idOrEl) {
        if (!idOrEl) { return null; }
        if (typeof idOrEl === 'string') { return document.getElementById(idOrEl); }
        return idOrEl.nodeType === 1 ? idOrEl : null;
    }

    function isOpen(idOrEl) {
        var el = resolve(idOrEl);
        return !!el && el.classList.contains('is-open');
    }

    function emit(el, name) {
        el.dispatchEvent(new CustomEvent(name, {
            bubbles: true,
            detail: { drawerId: el.id }
        }));
    }

    function open(idOrEl, opts) {
        var el = resolve(idOrEl);
        if (!el || isOpen(el)) { return; }

        opts = opts || {};

        if (opts.returnFocusTo) {
            returnFocus[el.id] = opts.returnFocusTo;
        }

        el.removeAttribute('inert');
        el.setAttribute('aria-hidden', 'false');
        el.classList.add('is-open');

        // Focus the container, not the first field: the drawer is non-modal,
        // so the user should be able to Tab straight back out.
        el.focus({ preventScroll: true });

        emit(el, 'lexDrawerOpened');
    }

    function close(idOrEl) {
        var el = resolve(idOrEl);
        if (!el || !isOpen(el)) { return; }

        el.classList.remove('is-open');
        el.setAttribute('aria-hidden', 'true');

        // Move focus out before inert, or the browser blurs to <body> and the
        // trigger loses its place in the tab order.
        var trigger = returnFocus[el.id];
        if (trigger && document.contains(trigger)) {
            trigger.focus({ preventScroll: true });
        } else if (el.contains(document.activeElement)) {
            document.activeElement.blur();
        }
        delete returnFocus[el.id];

        el.setAttribute('inert', '');

        setTimeout(function() {
            emit(el, 'lexDrawerClosed');
        }, TRANSITION_MS);
    }

    function toggle(idOrEl, opts) {
        if (isOpen(idOrEl)) {
            close(idOrEl);
        } else {
            open(idOrEl, opts);
        }
    }

    /**
     * Deep-link to a vtab inside the drawer.
     *
     * A native .click() still fires jQuery handlers, which is what lets this
     * drive vtabs.activate without depending on jQuery here.
     *
     * Ordering is safe: vtabs.init() restores the saved tab at DOM-ready, and
     * this runs later on user interaction, so it always wins.
     */
    function activateVtab(drawer, vtabId) {
        if (!vtabId) { return; }
        var btn = drawer.querySelector('.lex-vtabs__nav button[data-vtab="' + vtabId + '"]');
        if (btn) { btn.click(); }
    }

    document.addEventListener('click', function(e) {
        var openTrigger = e.target.closest('[data-lex-drawer-open]');
        if (openTrigger) {
            e.preventDefault();
            var drawerId = openTrigger.getAttribute('data-lex-drawer-open');
            var drawer   = document.getElementById(drawerId);
            if (!drawer) { return; }

            open(drawer, { returnFocusTo: openTrigger });
            activateVtab(drawer, openTrigger.getAttribute('data-lex-drawer-vtab'));
            return;
        }

        var closeTrigger = e.target.closest('[data-lex-drawer-close]');
        if (closeTrigger) {
            e.preventDefault();
            var owner = closeTrigger.closest('.lex-drawer');
            if (owner) { close(owner); }
        }
    });

    // Light-dismiss. mousedown, not click: a click whose press started inside
    // the drawer and released outside (text selection, a drag) would otherwise
    // read as an outside click and close it.
    document.addEventListener('mousedown', function(e) {
        var openDrawers = document.querySelectorAll('.lex-drawer.is-open');
        if (!openDrawers.length) { return; }

        // closest() needs an Element; a press can land on a text node or on a
        // node already detached from the document.
        var el = e.target instanceof Element ? e.target : null;
        if (el && (!el.isConnected || el.closest(KEEP_OPEN_WITHIN))) { return; }

        Array.prototype.forEach.call(openDrawers, function(drawer) {
            close(drawer);
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') { return; }

        var drawer = document.activeElement && document.activeElement.closest
            ? document.activeElement.closest('.lex-drawer.is-open')
            : null;

        if (drawer) {
            e.preventDefault();
            close(drawer);
        }
    });

    window.lexDrawer = {
        open: open,
        close: close,
        toggle: toggle,
        isOpen: isOpen
    };

})();
