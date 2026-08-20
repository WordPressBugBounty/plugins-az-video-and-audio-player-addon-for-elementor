/**
 * Shared interactions for the redesigned Tailwind admin screens (dropdowns,
 * checkboxes, search, copy-to-clipboard). One file for the whole track, not
 * one per page - see docs/admin-redesign-pencil-porting-process.md.
 */

(function($) {
    'use strict';

    // ── Generic dropdown menu ────────────────────────────────────────────────
    // Convention for every dropdown/menu on this page (and future
    // admin-redesign pages that reuse this file): trigger carries
    // data-lpl-dropdown-trigger="KEY", panel carries data-lpl-dropdown="KEY"
    // and starts with the lpl-hidden class. One handler, no per-menu wiring.
    var $dropdownTriggers = $();

    function closeAllDropdowns() {
        $( '[data-lpl-dropdown]' ).addClass( 'lpl-hidden' );
        $dropdownTriggers.attr( 'aria-expanded', 'false' );
    }

    function initDropdowns() {
        $dropdownTriggers = $( '[data-lpl-dropdown-trigger]' );
        if ( ! $dropdownTriggers.length ) { return; }

        $dropdownTriggers.attr( 'aria-expanded', 'false' );

        $dropdownTriggers.on( 'click', function( e ) {
            e.stopPropagation();

            var key = $( this ).attr( 'data-lpl-dropdown-trigger' );
            var $panel = $( '[data-lpl-dropdown="' + key + '"]' );
            if ( ! $panel.length ) { return; }

            var wasOpen = ! $panel.hasClass( 'lpl-hidden' );
            closeAllDropdowns();
            if ( ! wasOpen ) {
                $panel.removeClass( 'lpl-hidden' );
                $( this ).attr( 'aria-expanded', 'true' );
            }
        } );

        // Buttons living inside a panel that should close it on click
        // (Apply filters, Move to Trash) - an "inside" click, so the
        // outside-click handler below intentionally leaves it alone.
        $( document ).on( 'click', '[data-lpl-dropdown-close]', function( e ) {
            e.stopPropagation();
            closeAllDropdowns();
        } );

        $( document ).on( 'click', function( e ) {
            if ( $( e.target ).closest( '[data-lpl-dropdown], [data-lpl-dropdown-trigger]' ).length ) { return; }
            closeAllDropdowns();
        } );

        $( document ).on( 'keydown', function( e ) {
            if ( e.key === 'Escape' ) { closeAllDropdowns(); }
        } );
    }

    // ── Checkboxes ────────────────────────────────────────────────────────────
    // Convention: data-lpl-checkbox on a div styled purely off aria-checked
    // (see the aria-checked:/group-aria-checked: classes on the element and its
    // check-icon child - no runtime class toggling needed, only the attribute).
    // data-lpl-select-all marks the table header's "check all" box;
    // data-lpl-row-checkbox marks a table row's box. Neither attribute is used
    // outside the table, so filters-panel checkboxes are unaffected by row logic.
    function isChecked( $el ) {
        return $el.attr( 'aria-checked' ) === 'true';
    }

    function setChecked( $el, checked ) {
        $el.attr( 'aria-checked', checked ? 'true' : 'false' );
    }

    // The one definition of "the rows the user is looking at". Selection is a
    // destructive operation on whatever it covers, so select-all, the bulk-bar
    // count and the id payload sent to the server all derive from this and
    // never from the full row set - a row hidden by a filter or by pagination
    // is not on screen, so it is not selectable and cannot be acted on.
    function visibleRows() {
        return $( '[data-lpl-row]' ).not( '.lpl-hidden' );
    }

    function visibleRowCheckboxes() {
        return visibleRows().find( '[data-lpl-row-checkbox]' );
    }

    function syncSelectAll() {
        var $rows = visibleRowCheckboxes();
        var $selectAll = $( '[data-lpl-select-all]' );
        if ( ! $selectAll.length ) { return; }

        var allChecked = $rows.length > 0 && $rows.filter( function() {
            return isChecked( $( this ) );
        } ).length === $rows.length;

        setChecked( $selectAll, allChecked );
    }

    function updateBulkBar() {
        var checkedCount = visibleRowCheckboxes().filter( function() {
            return isChecked( $( this ) );
        } ).length;

        var $bar = $( '[data-lpl-bulkbar]' );
        var $count = $( '[data-lpl-selected-count]' );

        if ( checkedCount > 0 ) {
            $bar.removeClass( 'lpl-hidden' );
            $count.text( checkedCount + ' selected' );
        } else {
            $bar.addClass( 'lpl-hidden' );
        }
    }

    function clearSelection() {
        $( '[data-lpl-row-checkbox]' ).each( function() {
            setChecked( $( this ), false );
        } );
        syncSelectAll();
        updateBulkBar();
    }

    function handleCheckboxToggle( $el ) {
        if ( $el.is( '[data-lpl-select-all]' ) ) {
            var next = ! isChecked( $el );
            setChecked( $el, next );
            // Visible rows only: with a filter applied or on page 2 of a
            // result set, "select all" means the rows under the header it
            // sits in, not every row the server happened to render.
            visibleRowCheckboxes().each( function() {
                setChecked( $( this ), next );
            } );
            updateBulkBar();
            return;
        }

        setChecked( $el, ! isChecked( $el ) );

        if ( $el.is( '[data-lpl-row-checkbox]' ) ) {
            syncSelectAll();
            updateBulkBar();
            return;
        }

        // Filters-panel checkbox: no commit step, the row set follows the
        // toggle immediately and pagination restarts at page 1.
        if ( $el.is( '[data-lpl-filter]' ) ) {
            currentPage = 1;
            applyRowFilters();
        }
    }

    function initCheckboxes() {
        var $checkboxes = $( '[data-lpl-checkbox]' );
        if ( ! $checkboxes.length ) { return; }

        $checkboxes.on( 'click', function() {
            handleCheckboxToggle( $( this ) );
        } );

        $checkboxes.on( 'keydown', function( e ) {
            if ( e.key === ' ' || e.key === 'Enter' ) {
                e.preventDefault();
                handleCheckboxToggle( $( this ) );
            }
        } );

        // Bulk bar's X / "Clear selection". The bar's "Confirm" button is
        // handled by initBulkActions() - it runs a real AJAX action.
        $( document ).on( 'click', '[data-lpl-clear-selection]', clearSelection );

        // Filters panel's "Reset all" / "Clear" - resets checkboxes within
        // that panel only, not the whole page. Re-applies filters afterwards
        // so the row set goes back to "no constraint".
        $( document ).on( 'click', '[data-lpl-checkbox-reset]', function() {
            $( this ).closest( '[data-lpl-dropdown]' ).find( '[data-lpl-checkbox]' ).each( function() {
                setChecked( $( this ), false );
            } );
            applyRowFilters();
        } );
    }

    // ── Sort menu (single-select) + header-column chevron toggles ───────────
    // The sort menu ([data-lpl-sort-option]) and the ID / CREATED / STATUS
    // column-header chevrons ([data-lpl-sort-col]) share one reorder routine
    // (sortRows). The picked option keeps its aria-selected checkmark; a
    // header chevron click flips its column's direction in place.
    var currentSort = 'date-desc';

    function parseSortKey( key ) {
        var parts = key.split( '-' );
        var direction = parts.pop();
        return { field: parts.join( '-' ), direction: direction };
    }

    function rowAttrName( field ) {
        // 'date' (created) and 'updated' map to their own data attr; every
        // other field name maps straight to data-lpl-<field>.
        return field === 'date' ? 'data-lpl-created' : ( 'data-lpl-' + field );
    }

    function rowSortValue( $row, field ) {
        return ( $row.attr( rowAttrName( field ) ) || '' ).toLowerCase();
    }

    function compareRows( a, b, field, direction ) {
        var va = rowSortValue( $( a ), field );
        var vb = rowSortValue( $( b ), field );
        var cmp;

        if ( field === 'id' || field === 'created' || field === 'updated' ) {
            cmp = ( parseInt( va, 10 ) || 0 ) - ( parseInt( vb, 10 ) || 0 );
        } else {
            cmp = va < vb ? -1 : ( va > vb ? 1 : 0 );
        }

        return direction === 'desc' ? -cmp : cmp;
    }

    function sortRows( key ) {
        var parsed = parseSortKey( key );
        var $rows = $( '[data-lpl-row]' );
        if ( ! $rows.length ) { return; }

        var arr = $.makeArray( $rows );
        arr.sort( function( a, b ) {
            return compareRows( a, b, parsed.field, parsed.direction );
        } );

        // Re-append in sorted order. The empty-state div is not a
        // data-lpl-row, so it stays put when present.
        $rows.first().parent().append( arr );

        currentSort = key;

        // New sort order restarts pagination at page 1 of the now-reordered
        // filtered subset.
        currentPage = 1;
        applyRowFilters();
    }

    function selectSortOption( $option ) {
        $( '[data-lpl-sort-option]' ).attr( 'aria-selected', 'false' );
        $option.attr( 'aria-selected', 'true' );
        closeAllDropdowns();
        sortRows( $option.attr( 'data-lpl-sort' ) );
    }

    function initSortMenu() {
        var $options = $( '[data-lpl-sort-option]' );
        if ( ! $options.length ) { return; }

        // Seed currentSort from the server-rendered active option so the
        // initial row order (date DESC) and the menu's pre-highlighted item
        // agree.
        var $active = $options.filter( '[aria-selected="true"]' ).first();
        if ( $active.length ) {
            currentSort = $active.attr( 'data-lpl-sort' ) || currentSort;
        }

        // Whatever the server picked is the default the URL omits.
        DEFAULT_SORT = currentSort;

        $options.on( 'click', function() {
            selectSortOption( $( this ) );
        } );

        $options.on( 'keydown', function( e ) {
            if ( e.key === 'Enter' || e.key === ' ' ) {
                e.preventDefault();
                selectSortOption( $( this ) );
            }
        } );
    }

    function initColumnSortToggles() {
        var $cols = $( '[data-lpl-sort-col]' );
        if ( ! $cols.length ) { return; }

        $cols.attr( 'role', 'button' ).attr( 'tabindex', '0' );

        function toggleFor( $el ) {
            var field = $el.attr( 'data-lpl-sort-col' );
            var parsed = parseSortKey( currentSort );

            // 'date' header toggles the 'date-*' sort key (the data attr is
            // data-lpl-created, but the public key is date-<dir>).
            var sortField = parsed.field === 'created' ? 'date' : parsed.field;

            var newDir;
            if ( sortField === field ) {
                newDir = parsed.direction === 'desc' ? 'asc' : 'desc';
            } else {
                // Defaults: numeric/revision fields newest-first, string
                // fields alphabetically.
                newDir = ( field === 'date' || field === 'id' || field === 'updated' ) ? 'desc' : 'asc';
            }

            var newKey = field + '-' + newDir;
            sortRows( newKey );

            // Reflect in the menu only when this key matches a real option;
            // the ID / STATUS / UPDATED ascending directions don't.
            $( '[data-lpl-sort-option]' ).attr( 'aria-selected', 'false' );
            $( '[data-lpl-sort="' + newKey + '"]' ).attr( 'aria-selected', 'true' );
        }

        $cols.on( 'click', function() { toggleFor( $( this ) ); } );

        $cols.on( 'keydown', function( e ) {
            if ( e.key === 'Enter' || e.key === ' ' ) {
                e.preventDefault();
                toggleFor( $( this ) );
            }
        } );
    }

// ── Search + filters ─────────────────────────────────────────────────────
    // One applyRowFilters() owns toggling lpl-hidden on the row set; nothing
    // else touches that class. Visibility is the AND of three inputs:
    //   1. the search box query (substring match on the row text)
    //   2. the active Filters-panel groups (type / source / status), where an
    //      empty group imposes no constraint
    //   3. the current pagination page (added in Task 6)
    // Hidden rows are unchecked so the bulk bar only ever counts visible
    // checked rows.
    // Filters have no commit step - each checkbox applies on toggle, since
    // filtering is client-side and costs nothing. The panel covers the table
    // while open, so the feedback lives in the panel ([data-lpl-filter-match])
    // and on the trigger badge ([data-lpl-filter-count]).
    // Not a fixed literal: players have a 'source' group, playlists don't, so
    // the groups this page cares about are read off the DOM once rather than
    // hardcoded here.
    var filterState = {};

    function filterGroups() {
        var groups = [];
        $( '[data-lpl-filter]' ).each( function() {
            var group = $( this ).attr( 'data-lpl-filter' );
            if ( group && groups.indexOf( group ) === -1 ) { groups.push( group ); }
        } );
        return groups;
    }

    // Read from the per-screen localizer (ajax-actions.php) so a future screen
    // can use a different page size without a JS edit. Falls back to 20 if the
    // localize silently failed (see the priority-20 note near bulkSettings()).
    var PER_PAGE = ( typeof window.leanplAdminNew !== 'undefined' && window.leanplAdminNew.per_page ) || 20;
    var currentPage = 1;

    function readFilters() {
        var groups = filterGroups();
        filterState = {};
        for ( var g = 0; g < groups.length; g++ ) {
            filterState[ groups[ g ] ] = [];
        }
        $( '[data-lpl-filter]' ).each( function() {
            if ( ! isChecked( $( this ) ) ) { return; }
            var group = $( this ).attr( 'data-lpl-filter' );
            var value = $( this ).attr( 'data-lpl-filter-value' );
            if ( filterState.hasOwnProperty( group ) ) {
                filterState[ group ].push( value );
            }
        } );
    }

    function rowMatchesFilters( $row ) {
        var groups = filterGroups();
        for ( var i = 0; i < groups.length; i++ ) {
            var group = groups[ i ];
            if ( ! filterState[ group ] || ! filterState[ group ].length ) {
                continue;
            }
            var rowValue = $row.attr( 'data-lpl-' + group ) || '';
            if ( filterState[ group ].indexOf( rowValue ) === -1 ) {
                return false;
            }
        }
        return true;
    }

    function updateFilterBadge() {
        var groups = filterGroups();
        var count = 0;
        for ( var i = 0; i < groups.length; i++ ) {
            count += ( filterState[ groups[ i ] ] || [] ).length;
        }
        var $badge = $( '[data-lpl-filter-count]' );
        if ( ! $badge.length ) { return; }
        if ( count > 0 ) {
            $badge.text( count ).removeClass( 'lpl-hidden' );
        } else {
            $badge.text( '0' ).addClass( 'lpl-hidden' );
        }
    }

    // Result readout inside the filters panel. The panel covers most of the
    // table, so this is where the user sees the effect of a toggle. Counts the
    // whole filtered set (search included), not the current page slice.
    function updateFilterMatch( total ) {
        var $el = $( '[data-lpl-filter-match]' );
        if ( ! $el.length ) { return; }
        var noun = total === 1 ? loc( 'noun', 'player' ) : loc( 'noun_plural', 'players' );
        $el.text( total === 1 ? ( '1 ' + noun + ' matches' ) : ( total + ' ' + noun + ' match' ) );
    }

    function updateShowing( total, start, end ) {
        var $el = $( '[data-lpl-showing]' );
        if ( ! $el.length ) { return; }
        if ( total === 0 ) {
            $el.text( 'Showing 0 of 0' );
            return;
        }
        // Range is inclusive on the leading edge, exclusive on the trailing
        // edge (startIdx..endIdx in PHP terms); humans read 1-based.
        var firstVisible = start + 1;
        var lastVisible = Math.min( end, total );
        $el.text( 'Showing ' + firstVisible + '\u2013' + lastVisible + ' of ' + total );
    }

    // Footer pager is rebuilt from the filtered/paged set on every
    // applyRowFilters() so the page buttons always match the current result
    // count, never the grand total of rows the server shipped.
    var PREV_PATH = 'M8.59619 2.93945q-0.08545 0.02734-0.19482 0.1128-0.14014 0.12646-0.53321 0.51611l-1.28857 1.2749q-1.81836 1.82178-1.86279 1.90723-0.04102 0.08203-0.04102 0.24951 0 0.16748 0.04785 0.25977 0.05127 0.08887 1.8628 1.9038 1.81494 1.81152 1.9038 1.8628 0.09229 0.04785 0.2461 0.04785 0.15381 0 0.23584-0.03418 0.08545-0.0376 0.18115-0.1333 0.09912-0.09912 0.1333-0.18115 0.0376-0.08545 0.0376-0.23926 0-0.15381-0.04443-0.23584-0.04102-0.08545-1.62012-1.66797l-1.58252-1.58252 1.58252-1.58252q1.5791-1.58252 1.62012-1.66455 0.04443-0.08545 0.04443-0.23926 0-0.15381-0.0376-0.23584-0.03418-0.08545-0.11621-0.18457-0.16748-0.15381-0.39307-0.16748-0.14014 0-0.18115 0.01367z';
    var NEXT_PATH = 'M5.09619 2.93945q-0.25293 0.08545-0.37939 0.30762-0.04102 0.08545-0.04102 0.25293 0 0.16748 0.04102 0.25293 0.04443 0.08203 1.62353 1.66455l1.58252 1.58252-1.58252 1.58252q-1.5791 1.58252-1.62353 1.66797-0.04102 0.08203-0.04102 0.23584 0 0.15381 0.03418 0.23926 0.0376 0.08203 0.1333 0.18115 0.09912 0.0957 0.18115 0.1333 0.08545 0.03418 0.23926 0.03418 0.15381 0 0.24268-0.04785 0.09229-0.05127 1.90381-1.8628 1.81494-1.81494 1.86279-1.9038 0.05127-0.09229 0.05127-0.25977 0-0.16748-0.04443-0.24951-0.04102-0.08545-1.85938-1.90723l-1.44238-1.42871q-0.33496-0.33496-0.46143-0.41699-0.09912-0.07178-0.19824-0.07178l-0.04102 0q-0.14014 0-0.18115 0.01367z';

    function pagerArrowButton( path, dir, disabled ) {
        var dis = disabled ? ' lpl-opacity-50 lpl-cursor-not-allowed' : '';
        return (
            '<div data-lpl-pager-' + dir + '="1"' + ( disabled ? ' aria-disabled="true"' : '' ) +
            ' class="lpl-btn-icon lpl-btn-icon--sm lpl-btn-icon--outline-lt' + dis + '">' +
                '<svg viewBox="0 0 13.99993896484375 14" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" class="lpl-box-border lpl-w-[15px] lpl-shrink-0 lpl-h-[15px]">' +
                    '<path d="' + path + '" fill="#9AA1AE"></path>' +
                '</svg>' +
            '</div>'
        );
    }

    function pagerNumberButton( page, active ) {
        var fill = active ? 'lpl-btn-icon--indigo' : 'lpl-btn-icon--hover';
        var color = active ? '' : ' lpl-text-[#6A7180]';
        return (
            '<div data-lpl-pager-page="' + page + '" data-lpl-pager-active="' + ( active ? 'true' : 'false' ) + '"' +
                ' class="lpl-btn-icon lpl-btn-icon--sm ' + fill + '">' +
                '<div class="lpl-text-[13px]/[normal] lpl-box-border' + color + ' lpl-font-semibold lpl-text-left [white-space:nowrap]">' + page + '</div>' +
            '</div>'
        );
    }

    // Marker for a truncated run of page numbers. Not a page value, so the
    // click handler (which keys off data-lpl-pager-page) never sees it.
    var PAGER_GAP = 'gap';

    function pagerGapButton() {
        return (
            '<div class="lpl-box-border lpl-w-[32px] lpl-shrink-0 lpl-h-[32px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center">' +
                '<div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#9AA1AE] lpl-font-semibold lpl-text-left [white-space:nowrap]">…</div>' +
            '</div>'
        );
    }

    // Which page numbers to render: always the first and last, a window around
    // the current page, and a gap marker wherever a run was skipped. Without
    // this the server cap (500 rows / 20 per page) would put 25 buttons in the
    // footer. Under 8 pages there is nothing to gain, so show them all.
    function pagerPageList( current, total ) {
        var pages = [];
        var p;

        if ( total <= 7 ) {
            for ( p = 1; p <= total; p++ ) { pages.push( p ); }
            return pages;
        }

        var from = Math.max( 2, current - 1 );
        var to = Math.min( total - 1, current + 1 );

        // Near either end, extend the window the other way so the strip keeps
        // a stable width instead of collapsing to two numbers.
        if ( current <= 3 ) { to = 4; }
        if ( current >= total - 2 ) { from = total - 3; }

        pages.push( 1 );
        if ( from > 2 ) { pages.push( PAGER_GAP ); }
        for ( p = from; p <= to; p++ ) { pages.push( p ); }
        if ( to < total - 1 ) { pages.push( PAGER_GAP ); }
        pages.push( total );

        return pages;
    }

    function renderPager( totalPages ) {
        var $pager = $( '[data-lpl-pager]' );
        if ( ! $pager.length ) { return; }

        if ( totalPages <= 1 ) {
            // Single page: hide prev/next entirely, show no number buttons.
            $pager.empty();
            return;
        }

        var pages = pagerPageList( currentPage, totalPages );
        var html = pagerArrowButton( PREV_PATH, 'prev', currentPage <= 1 );

        for ( var i = 0; i < pages.length; i++ ) {
            html += ( pages[ i ] === PAGER_GAP )
                ? pagerGapButton()
                : pagerNumberButton( pages[ i ], pages[ i ] === currentPage );
        }

        html += pagerArrowButton( NEXT_PATH, 'next', currentPage >= totalPages );

        $pager.html( html );
    }

    function initPager() {
        $( document ).on( 'click', '[data-lpl-pager-prev]:not([aria-disabled="true"])', function() {
            if ( currentPage > 1 ) { currentPage--; applyRowFilters(); }
        } );
        $( document ).on( 'click', '[data-lpl-pager-next]:not([aria-disabled="true"])', function() {
            currentPage++;
            applyRowFilters();
        } );
        $( document ).on( 'click', '[data-lpl-pager-page]', function() {
            currentPage = parseInt( $( this ).attr( 'data-lpl-pager-page' ), 10 ) || 1;
            applyRowFilters();
        } );
    }

    // ── URL state ────────────────────────────────────────────────────────────
    // Filters, sort, search and page live in the URL and nowhere else - not in
    // localStorage, not in user meta. The address bar then always describes the
    // view: back/forward and bookmarks work, a filtered list can be handed to a
    // teammate, and clicking "All Players" in the admin menu is a guaranteed
    // clean slate (no params = no filters). It also means the
    // window.location.reload() every bulk action ends with keeps the view the
    // user was working in.
    //
    // Param names are bare, with two forced exceptions: 'page' belongs to WP's
    // admin menu slug (?page=lean_player-all-players-new) so pagination uses
    // WP's own 'paged', and 'status' belongs to the server-side tab, so the
    // panel's publish/draft filter is 'state'.
    var URL_PARAM = {
        type: 'type',
        source: 'source',
        status: 'state',
        sort: 'sort',
        search: 's',
        page: 'paged'
    };

    // The server-rendered active sort option, re-seeded by initSortMenu(). Kept
    // out of the URL, so the default view has a bare URL.
    var DEFAULT_SORT = currentSort;

    // These values come back from a URL anyone can hand-edit and several of
    // them end up inside a jQuery selector, so they get checked before use.
    var SLUG_RE = /^[a-z0-9-]+$/;

    function urlStateSupported() {
        return !! ( window.history && window.history.replaceState && window.URLSearchParams );
    }

    function writeStateToUrl( query ) {
        if ( ! urlStateSupported() ) { return; }

        var params = new URLSearchParams( window.location.search );
        var groups = filterGroups();
        var i;

        for ( i = 0; i < groups.length; i++ ) {
            var values = filterState[ groups[ i ] ];
            if ( values && values.length ) {
                params.set( URL_PARAM[ groups[ i ] ], values.join( ',' ) );
            } else {
                params.delete( URL_PARAM[ groups[ i ] ] );
            }
        }

        // Defaults are represented by the param being absent, never by
        // spelling out the default value.
        if ( query ) {
            params.set( URL_PARAM.search, query );
        } else {
            params.delete( URL_PARAM.search );
        }

        if ( currentSort && currentSort !== DEFAULT_SORT ) {
            params.set( URL_PARAM.sort, currentSort );
        } else {
            params.delete( URL_PARAM.sort );
        }

        if ( currentPage > 1 ) {
            params.set( URL_PARAM.page, currentPage );
        } else {
            params.delete( URL_PARAM.page );
        }

        var qs = params.toString();
        window.history.replaceState(
            null,
            '',
            window.location.pathname + ( qs ? '?' + qs : '' ) + window.location.hash
        );
    }

    // Runs once at load, before the first applyRowFilters(). Seeds the controls
    // themselves (checkbox aria-checked, search value, sort selection) rather
    // than keeping a parallel copy of the state - readFilters() then picks the
    // filters up the same way it does after a click.
    function restoreStateFromUrl() {
        if ( ! urlStateSupported() ) { return; }

        var params = new URLSearchParams( window.location.search );
        var groups = filterGroups();
        var i;

        for ( i = 0; i < groups.length; i++ ) {
            var raw = params.get( URL_PARAM[ groups[ i ] ] );
            if ( ! raw ) { continue; }

            var values = raw.split( ',' );
            for ( var j = 0; j < values.length; j++ ) {
                var value = values[ j ].trim();
                if ( ! SLUG_RE.test( value ) ) { continue; }
                setChecked(
                    $( '[data-lpl-filter="' + groups[ i ] + '"][data-lpl-filter-value="' + value + '"]' ),
                    true
                );
            }
        }

        var query = params.get( URL_PARAM.search );
        if ( query ) {
            $( '[data-lpl-search-input]' ).val( query );
        }

        // Any field-direction pair is accepted, not just the six menu options:
        // the column-header chevrons produce keys the menu has no entry for
        // (id-asc, status-asc, updated-asc).
        var sort = params.get( URL_PARAM.sort );
        if ( sort && SLUG_RE.test( sort ) ) {
            var parsed = parseSortKey( sort );
            if ( parsed.field && ( parsed.direction === 'asc' || parsed.direction === 'desc' ) ) {
                $( '[data-lpl-sort-option]' ).attr( 'aria-selected', 'false' );
                $( '[data-lpl-sort="' + sort + '"]' ).attr( 'aria-selected', 'true' );
                sortRows( sort );
            }
        }

        // Last, because sortRows() restarts pagination at page 1.
        var page = parseInt( params.get( URL_PARAM.page ), 10 );
        if ( page > 1 ) {
            currentPage = page;
        }
    }

    function applyRowFilters() {
        readFilters();
        var query = ( $( '[data-lpl-search-input]' ).val() || '' ).toString().toLowerCase().trim();

        // First pass: compute the filtered subset (search + filters).
        var filtered = [];
        $( '[data-lpl-row]' ).each( function() {
            var $row = $( this );
            var ok = true;
            if ( query && $row.text().toLowerCase().indexOf( query ) === -1 ) { ok = false; }
            if ( ok && ! rowMatchesFilters( $row ) ) { ok = false; }
            if ( ok ) { filtered.push( this ); }
        } );

        // Pagination slice over the filtered subset.
        var total = filtered.length;
        var totalPages = Math.max( 1, Math.ceil( total / PER_PAGE ) );
        if ( currentPage > totalPages ) { currentPage = totalPages; }
        if ( currentPage < 1 ) { currentPage = 1; }

        var start = ( currentPage - 1 ) * PER_PAGE;
        var end = start + PER_PAGE;
        // Element identity, not an object map: DOM nodes all stringify to the
        // same key ("[object HTMLDivElement]"), so an object lookup would mark
        // every row visible as soon as one row made the page.
        var pageRows = filtered.slice( start, end );

        // Second pass: toggle visibility and uncheck hidden rows.
        $( '[data-lpl-row]' ).each( function() {
            var $row = $( this );
            var isVisible = $.inArray( this, pageRows ) !== -1;
            $row.toggleClass( 'lpl-hidden', ! isVisible );
            if ( ! isVisible ) {
                var $cb = $row.find( '[data-lpl-row-checkbox]' ).first();
                if ( $cb.length && isChecked( $cb ) ) {
                    setChecked( $cb, false );
                }
            }
        } );

        updateShowing( total, start, end );
        renderPager( totalPages );
        updateFilterBadge();
        updateFilterMatch( total );
        syncSelectAll();
        updateBulkBar();

        // Single write point: every filter, search, sort and page change ends
        // up here, so the URL can never drift from the rendered view.
        writeStateToUrl( query );
    }

    function initSearch() {
        var $input = $( '[data-lpl-search-input]' );
        var $rows = $( '[data-lpl-row]' );
        if ( ! $input.length || ! $rows.length ) { return; }

        // A new query always restarts at page 1 of the filtered result.
        $input.on( 'input', function() {
            currentPage = 1;
            applyRowFilters();
        } );
    }

    // The filters panel needs no init of its own: handleCheckboxToggle()
    // applies each filter as it is picked, and the panel's "Done" is a plain
    // close (data-lpl-dropdown-close).

    // ── Copy shortcode ───────────────────────────────────────────────────────
    // Reuses the existing lex-settings-new toast/notification system already
    // loaded on every plugin admin page (leanpl-lex-settings-notifications,
    // gated the same way as our own assets via leanpl_is_our_admin_page()) -
    // window.leanplSettings.notifications.show(type, message). Not the
    // literal "lexSettings" global some of that framework's own code uses;
    // ours localizes under the 'leanpl' instance id.
    function showToast( type, message ) {
        if ( window.leanplSettings && window.leanplSettings.notifications ) {
            window.leanplSettings.notifications.show( type, message );
        }
    }

    // ── Bulk actions ─────────────────────────────────────────────────────────
    // The bulk bar is a two-step picker: [data-lpl-bulk-action] chooses what to
    // do, [data-lpl-bulk-status-value] chooses the target status when that
    // action is "set_status", and [data-lpl-bulk-confirm] fires it. Which
    // actions are offered depends on the tab being viewed - a normal tab shows
    // the "normal"-scoped entries, the Trash tab shows the "trash"-scoped ones
    // (data-lpl-bulk-action-scope). Server side: includes/admin-new/ajax-actions.php.
    var BULK_ENDPOINTS = {
        set_status: 'leanpl_admin_new_bulk_status',
        trash: 'leanpl_admin_new_bulk_trash',
        restore: 'leanpl_admin_new_bulk_restore',
        delete: 'leanpl_admin_new_bulk_delete'
    };

    // Reads the localized strings the PHP localizer writes per screen (see
    // ajax-actions.php); falls back to the current player wording so a failed
    // or missing localize degrades instead of printing "undefined".
    function loc( key, fallback ) {
        var strings = bulkSettings().strings || {};
        return strings[ key ] || fallback;
    }

    // Actions that destroy or hide content get a browser confirm first.
    function bulkConfirmMessage( action ) {
        if ( action === 'trash' ) {
            return loc( 'confirm_trash', 'Move the selected players to the Trash?' );
        }
        if ( action === 'delete' ) {
            return loc( 'confirm_delete', 'Permanently delete the selected players? This cannot be undone.' );
        }
        return '';
    }

    // Success message per action, as a function of how many rows it touched.
    // Written before the reload, read back after it - see queueToast().
    var BULK_MESSAGE = {
        set_status: function( n, status ) {
            var noun = n === 1 ? loc( 'noun', 'player' ) : loc( 'noun_plural', 'players' );
            return n + ' ' + noun + ' set to ' + ( status === 'draft' ? 'Draft' : 'Published' ) + '.';
        },
        trash: function( n ) {
            var noun = n === 1 ? loc( 'noun', 'player' ) : loc( 'noun_plural', 'players' );
            return n + ' ' + noun + ' moved to the Trash.';
        },
        restore: function( n ) {
            var noun = n === 1 ? loc( 'noun', 'player' ) : loc( 'noun_plural', 'players' );
            return n + ' ' + noun + ' restored.';
        },
        delete: function( n ) {
            var noun = n === 1 ? loc( 'noun', 'player' ) : loc( 'noun_plural', 'players' );
            return n + ' ' + noun + ' permanently deleted.';
        }
    };

    var TOAST_KEY = 'leanplAdminNewToast';

    var bulkAction = '';
    var bulkStatus = 'publish';

    function bulkSettings() {
        return ( typeof window.leanplAdminNew !== 'undefined' ) ? window.leanplAdminNew : {};
    }

    // A bulk action ends in a full page reload (statuses, header counts, tab
    // counts and the row set all move at once), which would wipe a toast shown
    // before it. Park the message in sessionStorage and show it on the way back.
    function queueToast( type, message ) {
        try {
            window.sessionStorage.setItem( TOAST_KEY, JSON.stringify( { type: type, message: message } ) );
        } catch ( e ) {}
    }

    function flushQueuedToast() {
        var raw;
        try {
            raw = window.sessionStorage.getItem( TOAST_KEY );
            if ( ! raw ) { return; }
            window.sessionStorage.removeItem( TOAST_KEY );
        } catch ( e ) {
            return;
        }

        var queued;
        try {
            queued = JSON.parse( raw );
        } catch ( e ) {
            return;
        }

        if ( ! queued || ! queued.message ) { return; }

        // admin-new.js only depends on jquery, so the lex notifications script
        // may not have attached its API yet on this tick. Retry briefly rather
        // than dropping the message - a swallowed toast is exactly the failure
        // this whole mechanism exists to avoid.
        var attempts = 0;
        ( function attempt() {
            if ( window.leanplSettings && window.leanplSettings.notifications ) {
                showToast( queued.type || 'success', queued.message );
                return;
            }
            if ( ++attempts > 20 ) { return; }
            window.setTimeout( attempt, 100 );
        } )();
    }

    // Last line of defence: even if some future code path leaves a hidden row
    // checked, an off-screen row never reaches the server.
    function checkedRowIds() {
        var ids = [];
        visibleRows().each( function() {
            var $row = $( this );
            var $cb = $row.find( '[data-lpl-row-checkbox]' ).first();
            if ( $cb.length && isChecked( $cb ) ) {
                ids.push( $row.attr( 'data-lpl-id' ) );
            }
        } );
        return ids;
    }

    // A bulk request can take seconds on a big selection, and it ends in a full
    // reload. Everything the user sees during that window is driven from one
    // flag: the button's aria-disabled drives the spinner and the dimmed look
    // in CSS, gates the click handler, and marks the bar aria-busy for screen
    // readers. On success it is deliberately never cleared - the page is on its
    // way out, and a button that springs back to "Confirm" for the last moments
    // before a reload just invites a second click.
    //
    // One label for every action and every phase. Naming the phase ("Working"
    // then "Reloading") reads as two separate operations for what the user did
    // once, and nothing actionable follows from knowing which phase it is in.
    var BULK_BUSY_LABEL = 'Working…';

    var BULK_TIMEOUT_MS = 30000;

    function setBulkBusy( $button, busy ) {
        if ( busy ) {
            $button.attr( 'aria-disabled', 'true' );
        } else {
            $button.removeAttr( 'aria-disabled' );
        }
        $( '[data-lpl-bulkbar]' ).attr( 'aria-busy', busy ? 'true' : 'false' );
        $( '[data-lpl-bulk-confirm-label]' ).text( busy ? BULK_BUSY_LABEL : 'Confirm' );
    }

    function setBulkAction( key, label ) {
        bulkAction = key;
        $( '[data-lpl-bulk-action-current]' ).attr( 'data-lpl-bulk-action-current', key );
        $( '[data-lpl-bulk-action-label]' ).text( label );
        // The status picker is only meaningful for "set_status".
        $( '[data-lpl-bulk-status-wrapper]' ).toggleClass( 'lpl-hidden', key !== 'set_status' );
    }

    function initBulkActions() {
        var $actions = $( '[data-lpl-bulk-action]' );
        if ( ! $actions.length ) { return; }

        // Only offer the actions that make sense for the current tab.
        var scope = ( bulkSettings().status === 'trash' ) ? 'trash' : 'normal';
        $actions.each( function() {
            var $el = $( this );
            $el.toggleClass( 'lpl-hidden', $el.attr( 'data-lpl-bulk-action-scope' ) !== scope );
        } );

        $actions.on( 'click', function() {
            var $el = $( this );
            setBulkAction( $el.attr( 'data-lpl-bulk-action' ), $el.text().trim() );
        } );

        // Confirm is a div with role="button", so it needs its keys wired by
        // hand. Same aria-disabled gate as the click path.
        $( document ).on( 'keydown', '[data-lpl-bulk-confirm]:not([aria-disabled="true"])', function( e ) {
            if ( e.key === 'Enter' || e.key === ' ' ) {
                e.preventDefault();
                $( this ).trigger( 'click' );
            }
        } );

        $( document ).on( 'click', '[data-lpl-bulk-status-value]', function() {
            var $el = $( this );
            bulkStatus = $el.attr( 'data-lpl-bulk-status-value' );
            $( '[data-lpl-bulk-status-label]' ).text( $el.text().trim() );
        } );

        // The :not() is what makes the busy flag real - without it a second
        // click fires a second POST, and for "delete" that second call comes
        // back with updated: 0 and reports a permission problem for rows the
        // first call already deleted.
        $( document ).on( 'click', '[data-lpl-bulk-confirm]:not([aria-disabled="true"])', function() {
            var settings = bulkSettings();
            var ids = checkedRowIds();

            if ( ! ids.length ) {
                showToast( 'error', loc( 'select_first', 'Select at least one player first.' ) );
                return;
            }

            if ( ! bulkAction ) {
                showToast( 'error', 'Pick an action first.' );
                return;
            }

            var action = BULK_ENDPOINTS[ bulkAction ];
            if ( ! action || ! settings.ajax_url ) {
                showToast( 'error', 'Bulk actions are unavailable on this screen. Reload the page and try again.' );
                return;
            }

            var confirmMessage = bulkConfirmMessage( bulkAction );
            if ( confirmMessage && ! window.confirm( confirmMessage ) ) {
                return;
            }

            var payload = {
                action: action,
                nonce: settings.nonce,
                post_type: settings.post_type,
                ids: ids
            };

            if ( bulkAction === 'set_status' ) {
                payload.status = bulkStatus;
            }

            var $button = $( this );
            setBulkBusy( $button, true );

            $.ajax( {
                url: settings.ajax_url,
                type: 'POST',
                data: payload,
                // Without this a hung request leaves the button busy forever,
                // with no way back except a manual refresh.
                timeout: BULK_TIMEOUT_MS
            } )
                .done( function( response ) {
                    if ( ! response || ! response.success ) {
                        var message = ( response && response.data && response.data.message ) || 'Bulk action failed.';
                        showToast( 'error', message );
                        setBulkBusy( $button, false );
                        return;
                    }

                    var data = response.data || {};
                    var updated = ( typeof data.updated === 'number' ) ? data.updated : ids.length;
                    var failed = ( data.failed && data.failed.length ) ? data.failed.length : 0;

                    if ( ! updated ) {
                        // Nothing went through - stay put so the selection is
                        // still there to retry or adjust.
                        showToast( 'error', loc( 'no_permission', 'Nothing was updated. You may not have permission to edit those players.' ) );
                        setBulkBusy( $button, false );
                        return;
                    }

                    var successMessage = BULK_MESSAGE[ bulkAction ]( updated, bulkStatus );
                    if ( failed ) {
                        successMessage += ' ' + failed + ( failed === 1 ? ' was' : ' were' ) + ' skipped.';
                    }

                    // Statuses, tab counts and the Trash tab all change at once -
                    // a reload is cheaper and more honest than patching the DOM.
                    // The button stays busy through it, unchanged, so the whole
                    // request-plus-reload wait reads as one operation.
                    queueToast( failed ? 'info' : 'success', successMessage );
                    window.location.reload();
                } )
                .fail( function( jqXHR, textStatus ) {
                    // A timeout is not a failure we can speak for: the server
                    // may well have finished the work, so say so rather than
                    // implying nothing happened.
                    showToast(
                        'error',
                        textStatus === 'timeout'
                            ? 'Bulk action timed out. Some players may still have been updated - reload to check.'
                            : 'Bulk action failed.'
                    );
                    setBulkBusy( $button, false );
                } );
        } );
    }

    // ── Row click navigates to Edit ──────────────────────────────────────────
    // Clicking anywhere on a row (title, thumbnail, source, blank space) opens
    // the edit screen, same destination as the row's own pencil icon
    // (data-lpl-row-edit-url, set from $row['edit_url'] in player-row.php).
    // Everything inside the row that already has its own click behavior -
    // checkbox, shortcode copy, the preview/edit links themselves, the "..."
    // menu and its actions - is excluded so this doesn't fight them.
    function initRowClickNavigation() {
        $( document ).on( 'click', '[data-lpl-row]', function( e ) {
            var $row = $( this );
            var url = $row.attr( 'data-lpl-row-edit-url' );
            if ( ! url ) { return; }

            if ( $( e.target ).closest( 'a, [data-lpl-checkbox], [data-lpl-copy-shortcode], [data-lpl-dropdown-trigger], [data-lpl-dropdown], [data-lpl-row-action]' ).length ) {
                return;
            }

            window.location.href = url;
        } );
    }

    // ── Single-row actions (preview/edit are plain links; trash/restore/delete
    // reuse the exact bulk endpoints with a one-id array, so no new AJAX
    // handler exists on the server for this) ─────────────────────────────────
    function initRowActions() {
        $( document ).on( 'click', '[data-lpl-row-action]', function() {
            var $el = $( this );
            var action = $el.attr( 'data-lpl-row-action' );
            var id = $el.attr( 'data-lpl-row-action-id' );
            var redirectUrl = $el.attr( 'data-lpl-row-action-redirect' );
            var settings = bulkSettings();

            var endpoint = BULK_ENDPOINTS[ action ];
            if ( ! endpoint || ! settings.ajax_url ) { return; }

            var confirmMessage = bulkConfirmMessage( action );
            if ( confirmMessage && ! window.confirm( confirmMessage ) ) { return; }

            $.ajax( {
                url: settings.ajax_url,
                type: 'POST',
                data: {
                    action: endpoint,
                    nonce: settings.nonce,
                    post_type: settings.post_type,
                    ids: [ id ]
                },
                timeout: BULK_TIMEOUT_MS
            } ).done( function( response ) {
                if ( ! response || ! response.success || ! response.data || ! response.data.updated ) {
                    showToast( 'error', loc( 'no_permission', 'Nothing was updated. You may not have permission to edit those players.' ) );
                    return;
                }
                queueToast( 'success', BULK_MESSAGE[ action ]( 1 ) );

                // The edit screen's Trash item passes this: the row it just
                // acted on no longer has an edit screen worth reloading into
                // (see edit-topbar.php), so send the user back to the list
                // instead of reloading the same ?post=<id> URL.
                if ( redirectUrl ) {
                    window.location.href = redirectUrl;
                } else {
                    window.location.reload();
                }
            } ).fail( function() {
                showToast( 'error', 'That action failed. Reload and try again.' );
            } );
        } );
    }

    function initCopyShortcode() {
        var $buttons = $( '[data-lpl-copy-shortcode]' );
        if ( ! $buttons.length ) { return; }

        $buttons.on( 'click', function() {
            var text = $( this ).siblings( '[data-lpl-shortcode-text]' ).first().text().trim();

            if ( ! text ) { return; }

            function onCopied() {
                showToast( 'success', 'Shortcode copied!' );
            }

            if ( navigator.clipboard && navigator.clipboard.writeText ) {
                navigator.clipboard.writeText( text ).then( onCopied, function() {
                    showToast( 'error', 'Could not copy shortcode' );
                } );
                return;
            }

            var $temp = $( '<textarea>' ).val( text ).css( { position: 'fixed', left: '-9999px' } );
            $( 'body' ).append( $temp );
            $temp[ 0 ].select();
            try {
                document.execCommand( 'copy' );
                onCopied();
            } catch ( e ) {
                showToast( 'error', 'Could not copy shortcode' );
            }
            $temp.remove();
        } );
    }

    // ── Edit-screen source preview ───────────────────────────────────────────
    // Shared by the Media Library picker and the Add Media / URL path below.
    // Posts straight to leanpl_live_preview (class-live-preview-ajax.php) -
    // the same AJAX action + nonce the classic post.php metabox's live
    // preview panel uses (live-preview.js), localized here as
    // window.leanplAdminNewPreview by Settings_Page::render_player_edit_new_page().
    // That endpoint runs the posted values through the real Player_Renderer
    // with post_id = 0: no post meta read, no DB write, just today's answer
    // to "what would this render as". player-utils.js's autoInit
    // MutationObserver picks up the returned .lpl-player markup and
    // instantiates Plyr on it automatically, same as the classic panel.
    function currentSourceTab() {
        var $active = $( '[data-lpl-source-tab][aria-selected="true"]' );
        return $active.length ? $active.attr( 'data-lpl-source-tab' ) : '';
    }

    // The most recently requested source payload - initPublishButton() sends
    // this same object back to leanpl_admin_new_save_player so Update/Publish
    // persists whatever the preview is currently showing, without a second
    // "what did the user pick" tracking mechanism of its own.
    var lastSourcePayload = null;

    // Poster tracked separately from lastSourcePayload: it's orthogonal to
    // the source (can be set before a source is picked, and survives the
    // user picking a different source afterward), so requestPreview() and
    // initPublishButton() both merge it back in on every call instead of it
    // living inside whichever payload a source pick last built.
    var posterPayload = {};

    function requestPreview( payload ) {
        var settings = window.leanplAdminNewPreview;
        if ( ! settings || ! settings.ajaxUrl ) { return; }

        lastSourcePayload = payload;

        // Mirrors the picked type into edit-section-behavior.php's hidden
        // _player_type input so its video-only rows (Click to Play/Pause,
        // Allow Fullscreen, Auto-Hide Controls) show/hide live as the source
        // changes - admin-new-conditional-fields.js reads this same input.
        if ( payload && payload._player_type ) {
            $( '[data-lpl-player-type-input]' ).val( payload._player_type ).trigger( 'change' );
        }

        $( '[data-lpl-edit-surface="picker"]' ).addClass( 'lpl-hidden' );
        $( '[data-lpl-edit-surface="player"]' ).removeClass( 'lpl-hidden' );

        var $target = $( '[data-lpl-preview-target]' );

        // live_preview_post_type picks which branch of Live_Preview_Ajax
        // renders: the player one (default) builds a config out of the
        // posted source fields, the lean_playlist one renders a real saved
        // playlist by post_ID with the posted _playlist_* keys as overrides.
        var data = $.extend( {
            action: 'leanpl_live_preview',
            nonce: settings.nonce,
            live_preview_post_type: settings.postType || 'lean_player'
        }, payload, posterPayload );

        // Swapping $target's innerHTML tears down the old Plyr instance and
        // drops in a raw, unstyled <video>/<audio> for the instant before
        // player-utils.js's autoInit MutationObserver rebuilds Plyr on it -
        // dimming across the swap (CSS transition on the target, set in
        // edit-player-surface.php) papers over that flash instead of a
        // jarring instant cut.
        $target.addClass( 'lpl-opacity-40' );

        $.post( settings.ajaxUrl, data )
            .done( function( response ) {
                if ( response && response.success && response.data && response.data.html ) {
                    // The min-height only exists to hold the "Loading preview…"
                    // placeholder's spot before this first response lands - once
                    // real markup is in, let the container size to its content
                    // instead of leaving dead space below a shorter playlist panel.
                    $target.removeClass( 'lpl-min-h-[460px]' ).html( response.data.html );
                    // Playlist screen only (no-op selector elsewhere) - stays
                    // hidden until the real preview replaces the placeholder,
                    // so it never sits under a 460px "Loading preview…" box
                    // then jumps up once the shorter real markup lands. Also
                    // stays hidden for an empty playlist (response.data.empty,
                    // class-live-preview-ajax.php) - that mockup has its own
                    // Add Track button inside the panel, so showing this one
                    // too would be redundant.
                    $( '[data-lpl-add-track-row]' ).toggleClass( 'lpl-hidden', !! response.data.empty );
                } else {
                    $target.text( 'Could not load preview.' );
                }
            } )
            .fail( function() {
                $target.text( 'Could not load preview.' );
            } )
            .always( function() {
                $target.removeClass( 'lpl-opacity-40' );
            } );
    }

    // On load, if ?post= named an existing player with a saved source
    // (window.leanplAdminNewPreview.savedSource, built server-side from real
    // post meta - see Settings_Page::render_player_edit_new_page()), replay
    // it through the exact same requestPreview() a live pick uses instead of
    // leaving the screen sitting on the empty source picker. A brand-new
    // player (no ?post=, or one that was never given a source) has nothing
    // to replay and stays on the picker, same as before.
    //
    // savedSource only carries _player_type + source keys, not the rest of
    // the form (layout, colors, behavior) - those have to ride along on this
    // same first call too, same reasoning as the playlist branch below:
    // build_config_from_post() (class-live-preview-ajax.php) reads them
    // straight from $_POST, so omitting them here rendered the just-saved
    // player with those fields back at their bare defaults until the user
    // touched a field and requestFormPreview() sent the full form.
    function initSavedSourcePreview() {
        var settings = window.leanplAdminNewPreview;
        if ( ! settings ) { return; }

        // A playlist has no source to pick - the renderer reads its items
        // off the saved post - so an existing one previews straight from its
        // ID. That first payload also seeds lastSourcePayload, which is what
        // lets requestFormPreview() re-render on every inspector change.
        //
        // The form's current field values have to ride along on this very
        // first call too, not just post_ID: build_playlist_overrides_from_post()
        // (class-live-preview-ajax.php) builds an override for every
        // _playlist_* schema key unconditionally, and an absent POST key
        // reads as '' there - which Config::clean_attr_overrides() then
        // casts to false/'' for every field. Sending post_ID alone silently
        // clobbered the real saved values (blank position, everything
        // hidden) on first paint, self-correcting only once the user
        // touched a field and requestFormPreview() sent the full form.
        if ( settings.postType === 'lean_playlist' ) {
            if ( settings.postId > 0 ) {
                requestPreview( $.extend( { post_ID: settings.postId }, formValues( $( '[data-lpl-edit-form]' ) ) ) );
            }
            return;
        }

        if ( settings.savedPoster ) {
            posterPayload = { _poster: settings.savedPoster };
        }

        if ( ! settings.savedSource || ! settings.savedSource._player_type ) { return; }

        requestPreview( $.extend( {}, settings.savedSource, formValues( $( '[data-lpl-edit-form]' ) ) ) );
    }

    // ── Edit-screen Publish/Update ───────────────────────────────────────────
    // Saves the title (from data-lpl-title-input), the serialized settings
    // form (data-lpl-edit-form), whatever source is currently showing
    // (lastSourcePayload, may be null if nothing's been picked yet), and the
    // poster (posterPayload) to the screen's save endpoint - the player
    // screen posts to leanpl_admin_new_save_player, the playlist screen to
    // leanpl_admin_new_save_playlist. Endpoint is picked from
    // window.leanplAdminNewPreview.postType (set server-side per screen),
    // and the matching saveNonce is localized alongside it. Flips its own
    // label and window.leanplAdminNewPreview.postId in place on success, and
    // pushes ?post=<id> into the URL (history.replaceState, no reload) so a
    // manual page reload afterward hydrates from the now-real post instead
    // of starting over.

    // Convert a form's serializeArray() output (an array of {name, value}
    // objects) into a plain object of name → value, so it can be merged into
    // the save payload the same way lastSourcePayload and posterPayload are.
    function formValues( $form ) {
        var values = {};
        $.each( $form.serializeArray(), function( i, field ) {
            values[ field.name ] = field.value;
        } );
        return values;
    }

    // ── Edit-screen field live preview ───────────────────────────────────────
    // Behavior/Advanced/General field changes (Autoplay, Volume, Track Title,
    // etc.) re-request the preview with the current source payload plus the
    // form's current values, the same merge initPublishButton() does for the
    // real save - so flipping a select or nudging a number field shows up on
    // the player without writing post meta. Only fires once a source exists
    // (lastSourcePayload set): before that the picker is still showing, not
    // the player surface, so there's nothing to re-render.
    var formPreviewTimer = null;

    function requestFormPreview() {
        if ( ! lastSourcePayload ) { return; }

        window.clearTimeout( formPreviewTimer );
        formPreviewTimer = window.setTimeout( function() {
            requestPreview( $.extend( {}, lastSourcePayload, formValues( $( '[data-lpl-edit-form]' ) ) ) );
        }, 300 );
    }

    function initFormLivePreview() {
        var $form = $( '[data-lpl-edit-form]' );
        if ( ! $form.length ) { return; }

        // .lpl-cpm__preset-ref (custom-preset-builder.js) is a hidden input
        // outside this list's own types - its own apply/save/delete flows
        // fire 'change' on it by hand (refreshFieldPreview()) specifically
        // so this delegate picks it up.
        $form.on( 'change', 'select, input[type="text"], input[type="number"], input.lpl-cpm__preset-ref', requestFormPreview );
    }

    // Shared POST to the screen's save endpoint. Both Publish/Update and
    // Save Draft go through this: same action/nonce (chosen by postType),
    // same form + source + poster merge, same disabled/label-in-flight
    // pattern. The caller supplies the success-label swap (Publish flips to
    // 'Update'; Save Draft keeps its own label, leaving the post a draft)
    // and any extra payload fields (Save Draft adds post_status=draft).
    function submitSave( $btn, $label, options ) {
        options = options || {};
        var settings = window.leanplAdminNewPreview;
        if ( ! settings || ! settings.ajaxUrl || $btn.attr( 'aria-disabled' ) === 'true' ) { return; }

        // The player screen posts to leanpl_admin_new_save_player, the
        // playlist screen to leanpl_admin_new_save_playlist - same JS
        // pipeline, server-side handler is chosen by postType, which
        // the page localizer already sets. saveNonce is the matching
        // nonce (separate from the leanpl_live_preview nonce).
        var isPlaylist = ( settings.postType === 'lean_playlist' );
        var action = isPlaylist ? 'leanpl_admin_new_save_playlist' : 'leanpl_admin_new_save_player';

        var title = ( $( '[data-lpl-title-input]' ).val() || '' ).trim();

        // item_ids: JSON-encoded (see the comment above trackDrawerPendingItemIds()
        // below for why a plain array won't do - jQuery drops it from the
        // request entirely when empty). Playlist-only; neither surface
        // exists on the player screen.
        //
        // The Playlist Items card (edit-section-playlist-track-list.php),
        // when rendered, is now authoritative for an existing playlist's
        // order/duplicates/removals - trackListPendingItemIds() reads its
        // rows' DOM order directly, which a checkbox set can't represent.
        // Falls back to the Add Track drawer's Media Hub checked set
        // (trackDrawerPendingItemIds()) only when the card isn't on the page
        // at all - a brand-new, unsaved playlist, which has no saved items
        // for the card to render in the first place.
        //
        var $trackList  = $( '[data-lpl-track-list-rows]' );
        var pendingIds  = ( isPlaylist && $trackList.length ) ? trackListPendingItemIds() : trackDrawerPendingItemIds();
        // track_edits: staged per-track field edits (stageTrackEdit()) -
        // written server-side alongside _playlist_items, see
        // leanpl_admin_new_save_playlist()'s matching handling. Empty {} when
        // nothing was edited this session, same as item_ids being sent
        // unconditionally whenever the playlist screen exists.
        // playlist_type: whatever the New Playlist picker modal chose
        // (settings.playlistType, localized from $edit_new_playlist_type -
        // see Settings_Page::render_playlist_edit_new_page()) or the real
        // saved type for an existing playlist. Sent on every save, but only
        // ever consumed server-side on a post's first-ever save (type is
        // fixed at creation) - see leanpl_admin_new_save_playlist().
        var itemsPayload = isPlaylist ? { item_ids: JSON.stringify( pendingIds ), track_edits: JSON.stringify( TrackEdits ), playlist_type: settings.playlistType || 'video' } : {};

        // formValues() last so current field state wins over lastSourcePayload's
        // stale snapshot - same precedence requestFormPreview() above uses for
        // this exact pair. lastSourcePayload only gets refreshed with current
        // form values when SOME field's own handler calls requestFormPreview()
        // (there's no generic delegated change listener across the whole
        // form) - anything that updates a form field without doing that, like
        // Custom Preset's applyPreset(), would otherwise have its change
        // silently overwritten by the stale snapshot on save.
        var payload = $.extend( {
            action: action,
            nonce: settings.saveNonce,
            post_ID: settings.postId || 0,
            post_title: title
        }, lastSourcePayload || {}, posterPayload, itemsPayload, formValues( $( '[data-lpl-edit-form]' ) ), options.extra || {} );

        // Same "one label for every phase" reasoning as the bulk bar's
        // BULK_BUSY_LABEL: restore whatever the button said before this
        // click on failure, since a failed save hasn't earned the
        // success-swap below.
        var labelBeforeSave = $label.text();

        $btn.attr( 'aria-disabled', 'true' );
        $label.text( options.busyLabel || 'Working…' );

        $.post( settings.ajaxUrl, payload )
            .done( function( response ) {
                if ( ! response || ! response.success ) {
                    $label.text( labelBeforeSave );
                    showToast( 'error', ( response && response.data && response.data.message ) || 'Could not save.' );
                    return;
                }

                settings.postId = response.data.postId;
                if ( response.data.previewUrl ) {
                    enablePreviewButton( response.data.previewUrl );
                }
                // Staged edits just got written server-side (track_edits
                // above) - clear so a later save on this same page load
                // doesn't resend already-persisted patches. No-op for the
                // player screen, which never populates TrackEdits.
                TrackEdits = {};
                // Publish/Update swaps the rendered label to 'Update' (a
                // just-created post is no longer new). Save Draft keeps
                // its own label - the post stays a draft, so the button
                // doesn't earn a swap.
                if ( options.successLabel ) {
                    $label.text( options.successLabel );
                }
                // silent: used by the Add Track drawer's auto-draft-on-
                // first-track (see refreshTrackDrawerPreview()) - that save
                // has no button of its own, and its own "Playlist saved."
                // would immediately stomp the Quick/Bulk Add success toast
                // that's already on screen (showToast() clears whatever's
                // showing before rendering a new one). Errors still surface
                // either way - losing the staged item link silently would
                // be worse than one extra toast.
                if ( ! options.silent ) {
                    showToast( 'success', response.data.message );
                }

                if ( window.history && window.history.replaceState && window.URLSearchParams ) {
                    var params = new URLSearchParams( window.location.search );
                    params.set( 'post', response.data.postId );
                    window.history.replaceState( null, '', window.location.pathname + '?' + params.toString() );
                }
            } )
            .fail( function() {
                $label.text( labelBeforeSave );
                showToast( 'error', 'Could not save.' );
            } )
            .always( function() {
                $btn.removeAttr( 'aria-disabled' );
            } );
    }

    // Both the topbar Publish/Update button and the footer's matching button
    // carry data-lpl-publish-button (and their own data-lpl-publish-label
    // inside), so initPublishButton binds to each in turn rather than the
    // single match the pre-footer version did. One click anywhere triggers
    // one save - the aria-disabled guard in submitSave stops a second click
    // that lands while the first is still in flight.
    // ── Edit-screen Preview button ───────────────────────────────────────────
    // edit-topbar.php renders this disabled (aria-disabled + href="#") for a
    // brand-new, unsaved post - there's nothing to preview yet. Clicking it
    // in that state shows a toast instead of navigating. enablePreviewButton()
    // is called from submitSave()'s success handler once a save returns a
    // real previewUrl, so Publish/Update or Save Draft turns it live without
    // a page reload.
    function initPreviewButton() {
        var $btn = $( '[data-lpl-preview-button]' );
        if ( ! $btn.length ) { return; }

        $btn.on( 'click', function( e ) {
            if ( $btn.attr( 'aria-disabled' ) === 'true' ) {
                e.preventDefault();
                showToast( 'error', 'Publish or save a draft first to preview it.' );
            }
        } );
    }

    function enablePreviewButton( previewUrl ) {
        $( '[data-lpl-preview-button]' )
            .attr( 'href', previewUrl )
            .attr( 'target', '_blank' )
            .attr( 'rel', 'noopener' )
            .removeAttr( 'aria-disabled' );
    }

    function initPublishButton() {
        var $btns = $( '[data-lpl-publish-button]' );
        if ( ! $btns.length ) { return; }

        $btns.each( function() {
            var $btn   = $( this );
            var $label = $btn.find( '[data-lpl-publish-label]' );

            $btn.on( 'click', function() { submitSave( $btn, $label, { successLabel: 'Update' } ); } );
            $btn.on( 'keydown', function( e ) {
                if ( e.key === 'Enter' || e.key === ' ' ) {
                    e.preventDefault();
                    submitSave( $btn, $label, { successLabel: 'Update' } );
                }
            } );
        } );
    }

    // ── Edit-screen Save Draft ───────────────────────────────────────────────
    // Footer-only button (edit-footer.php renders it only when $edit_is_draft
    // is true - brand-new post or existing draft). Posts the same payload as
    // Publish/Update plus post_status=draft, so the server writes the post
    // back as a draft (creating one if needed) without flipping it to
    // publish. Label stays 'Save Draft' on success (no successLabel swap):
    // the post is still a draft, so the button doesn't earn a 'Update' swap
    // the way Publish does. The topbar's Publish/Update button is what moves
    // a draft to published.
    function initSaveDraftButton() {
        var $btns = $( '[data-lpl-save-draft]' );
        if ( ! $btns.length ) { return; }

        $btns.each( function() {
            var $btn   = $( this );
            var $label = $btn.find( '[data-lpl-save-draft-label]' );

            $btn.on( 'click', function() { submitSave( $btn, $label, { busyLabel: 'Saving…', extra: { post_status: 'draft' } } ); } );
            $btn.on( 'keydown', function( e ) {
                if ( e.key === 'Enter' || e.key === ' ' ) {
                    e.preventDefault();
                    submitSave( $btn, $label, { busyLabel: 'Saving…', extra: { post_status: 'draft' } } );
                }
            } );
        } );
    }

    // ── Edit-screen Media Library picker ─────────────────────────────────────
    // data-lpl-source-tab="media-library" opens WP's own media modal
    // (wp_enqueue_media() is called only on this screen - see
    // Settings_Page::render_player_edit_new_page()), filtered to video/audio,
    // single-select. This runs alongside initSourceTabs()'s generic
    // select-this-tab handler, not instead of it - both are bound to the
    // same element. The frame is built once and reused on repeat clicks.
    // The bottom Add Media button opens the same frame when Media Library is
    // the active tab, since there's no URL field to submit for that source.
    function initMediaLibraryPicker() {
        var $tabTrigger = $( '[data-lpl-source-tab="media-library"]' );
        if ( ! $tabTrigger.length || ! window.wp || ! window.wp.media ) { return; }

        var frame = null;

        function openFrame() {
            if ( ! frame ) {
                frame = window.wp.media( {
                    title: 'Select a video or audio file',
                    library: { type: [ 'video', 'audio' ] },
                    button: { text: 'Use this file' },
                    multiple: false
                } );

                frame.on( 'select', function() {
                    var attachment = frame.state().get( 'selection' ).first().toJSON();

                    var payload = ( attachment.type === 'audio' )
                        ? { _player_type: 'audio', _audio_source_type: 'upload', _audio_source: attachment.id }
                        : { _player_type: 'video', _video_type: 'html5', _html5_source_type: 'upload', _video_source: attachment.id };

                    requestPreview( payload );
                } );
            }

            frame.open();
        }

        $tabTrigger.on( 'click', openFrame );

        $( document ).on( 'click', '[data-lpl-add-media]', function() {
            if ( currentSourceTab() === 'media-library' ) { openFrame(); }
        } );
    }

    // ── Edit-screen Poster/Thumbnail picker ──────────────────────────────────
    // edit-section-general.php renders both states up front - data-lpl-poster-empty
    // (upload zone) and data-lpl-poster-set (thumb + filename + Change/Remove
    // row) - and this only ever toggles lpl-hidden between them plus fills in
    // the image/filename, same division of labor as every other two-state
    // control in this file (e.g. data-lpl-edit-surface). data-lpl-poster-picker
    // sits on both the empty zone and the "Change" text, so either opens WP's
    // own media modal (filtered to images, single-select - same wp.media
    // pattern as initMediaLibraryPicker() above); data-lpl-poster-remove clears
    // the pick back to the empty state. Independent of the video/audio source:
    // picking or clearing a poster re-sends whatever source is already showing
    // (if any) through requestPreview() so the live preview picks it up
    // immediately, and posterPayload (merged into every requestPreview()/save
    // call - see above) carries the pick to Publish/Update.
    function initPosterPicker() {
        var $openTriggers = $( '[data-lpl-poster-picker]' );
        if ( ! $openTriggers.length || ! window.wp || ! window.wp.media ) { return; }

        var $empty = $( '[data-lpl-poster-empty]' );
        var $set = $( '[data-lpl-poster-set]' );
        var $image = $( '[data-lpl-poster-image]' );
        var $filename = $( '[data-lpl-poster-filename]' );
        var frame = null;

        function refreshPreviewIfSourced() {
            if ( lastSourcePayload ) {
                requestPreview( lastSourcePayload );
            }
        }

        function openFrame() {
            if ( ! frame ) {
                frame = window.wp.media( {
                    title: 'Select a poster image',
                    library: { type: [ 'image' ] },
                    button: { text: 'Use this image' },
                    multiple: false
                } );

                frame.on( 'select', function() {
                    var attachment = frame.state().get( 'selection' ).first().toJSON();

                    posterPayload = { _poster: attachment.id };
                    $image.attr( 'src', attachment.url );
                    $filename.text( attachment.filename || '' );
                    $empty.addClass( 'lpl-hidden' );
                    $set.removeClass( 'lpl-hidden' );

                    refreshPreviewIfSourced();
                } );
            }

            frame.open();
        }

        $openTriggers.on( 'click', openFrame );
        $openTriggers.on( 'keydown', function( e ) {
            if ( e.key === 'Enter' || e.key === ' ' ) {
                e.preventDefault();
                openFrame();
            }
        } );

        $( document ).on( 'click', '[data-lpl-poster-remove]', function() {
            posterPayload = { _poster: 0 };
            $set.addClass( 'lpl-hidden' );
            $empty.removeClass( 'lpl-hidden' );

            refreshPreviewIfSourced();
        } );
    }

    // ── Edit-screen Add Media (URL path) ─────────────────────────────────────
    // Add Media click for every tab except Media Library (see
    // initMediaLibraryPicker() above for that one): reads the URL field and
    // routes it to the live-preview payload. _video_type isn't just a hint -
    // class-live-preview-ajax.php's process_video_source() uses it to decide
    // between a YouTube/Vimeo embed and a plain HTML5 <video><source>; a
    // YouTube URL submitted as _video_type=html5 renders "No video sources
    // provided" because the html5 branch never promotes a detected YouTube
    // ID into an embed. So the type has to be detected client-side before
    // building the payload, same patterns leanpl_parse_video_url() (PHP)
    // matches - this is what makes "detected automatically" (the help text's
    // promise) actually true for the no-tab-selected generic field, not only
    // for the dedicated YouTube/Vimeo tabs.
    function detectVideoUrlType( url ) {
        if ( /(?:youtube\.com\/|youtu\.be\/)/i.test( url ) ) { return 'youtube'; }
        if ( /vimeo\.com\//i.test( url ) ) { return 'vimeo'; }
        return 'html5';
    }

    function initAddMedia() {
        var $addMedia = $( '[data-lpl-add-media]' );
        var $urlInput = $( '[data-lpl-source-placeholder-target]' );
        if ( ! $addMedia.length ) { return; }

        $addMedia.on( 'click', function() {
            if ( currentSourceTab() === 'media-library' ) { return; }

            var url = ( $urlInput.val() || '' ).trim();
            if ( ! url ) {
                showToast( 'error', 'Paste a media URL first.' );
                return;
            }

            var payload;

            if ( currentSourceTab() === 'audio' ) {
                payload = { _player_type: 'audio', _audio_source_type: 'link', _html5_audio_url: url };
            } else {
                var videoType = detectVideoUrlType( url );
                payload = { _player_type: 'video', _video_type: videoType };
                if ( videoType === 'youtube' ) {
                    payload._youtube_url = url;
                } else if ( videoType === 'vimeo' ) {
                    payload._vimeo_url = url;
                } else {
                    payload._html5_source_type = 'link';
                    payload._html5_video_url = url;
                }
            }

            requestPreview( payload );
        } );
    }

    // ── Playlist edit screen: Add Track drawer ───────────────────────────────
    // data-lpl-track-drawer-open (the floating Add Track button) shows the
    // overlay + drawer from edit-playlist-track-drawer.php; the X icon,
    // either Cancel button, an overlay click, or Escape all close it again.
    // Visual only - see that partial's doc comment - nothing here writes a
    // track anywhere. Drawer starts with both lpl-hidden and
    // lpl-translate-x-full; open/close toggle both, with a forced reflow
    // before dropping translate-x-full so the slide-in actually animates
    // instead of jumping straight to the open position.
    var TRACK_DRAWER_TRANSITION_MS = 200;

    function openTrackDrawer() {
        var $drawer = $( '[data-lpl-track-drawer]' );
        if ( ! $drawer.length ) { return; }

        $( '[data-lpl-track-drawer-overlay]' ).removeClass( 'lpl-hidden' );
        $drawer.removeClass( 'lpl-hidden' );
        $drawer[ 0 ].offsetHeight; // eslint-disable-line no-unused-expressions
        $drawer.removeClass( 'lpl-translate-x-full' ).attr( 'aria-hidden', 'false' );
    }

    function closeTrackDrawer() {
        var $drawer = $( '[data-lpl-track-drawer]' );
        if ( ! $drawer.length || $drawer.hasClass( 'lpl-hidden' ) ) { return; }

        $drawer.addClass( 'lpl-translate-x-full' ).attr( 'aria-hidden', 'true' );
        $( '[data-lpl-track-drawer-overlay]' ).addClass( 'lpl-hidden' );

        window.setTimeout( function() {
            if ( $drawer.hasClass( 'lpl-translate-x-full' ) ) {
                $drawer.addClass( 'lpl-hidden' );
            }
        }, TRACK_DRAWER_TRANSITION_MS );
    }

    function selectTrackDrawerTab( key ) {
        $( '[data-lpl-track-drawer-tab]' ).attr( 'aria-selected', 'false' );
        $( '[data-lpl-track-drawer-tab="' + key + '"]' ).attr( 'aria-selected', 'true' );
        $( '[data-lpl-track-drawer-panel]' ).addClass( 'lpl-hidden' );
        $( '[data-lpl-track-drawer-panel="' + key + '"]' ).removeClass( 'lpl-hidden' );
        $( '[data-lpl-track-drawer-footer]' ).addClass( 'lpl-hidden' );
        $( '[data-lpl-track-drawer-footer="' + key + '"]' ).removeClass( 'lpl-hidden' );
    }

    // Media Hub rows are a real lean_player query (see
    // edit-playlist-track-drawer.php), filtered client-side same as the All
    // Players/Playlists list screens: text search + a single active source
    // filter, both applied together, no server round-trip.
    function applyTrackDrawerMediaFilters() {
        var $list = $( '[data-lpl-track-drawer-media-list]' );
        if ( ! $list.length ) { return; }

        var source = $( '[data-lpl-track-drawer-filter][aria-pressed="true"]' ).attr( 'data-lpl-track-drawer-filter' ) || 'all';
        var query = ( $( '[data-lpl-track-drawer-search]' ).val() || '' ).toString().toLowerCase().trim();
        var visibleCount = 0;

        $list.find( '[data-lpl-track-drawer-media-row]' ).each( function() {
            var $row = $( this );
            var matchesSource = ( source === 'all' ) || ( $row.attr( 'data-lpl-track-drawer-source' ) === source );
            var matchesQuery = ! query || $row.text().toLowerCase().indexOf( query ) !== -1;
            var isVisible = matchesSource && matchesQuery;

            $row.toggleClass( 'lpl-hidden', ! isVisible );
            if ( isVisible ) { visibleCount++; }
        } );

        $( '[data-lpl-track-drawer-media-empty]' ).toggleClass( 'lpl-hidden', visibleCount !== 0 );
    }

    // The drawer's own current checked set, read straight from the DOM
    // rather than tracked in a parallel JS array - the checkbox state IS
    // the pending model, same "DOM is the source of truth" approach
    // applyRowFilters() already uses elsewhere in this file.
    function trackDrawerPendingItemIds() {
        return $( '[data-lpl-track-drawer-media-row][aria-selected="true"]' )
            .map( function() {
                return parseInt( $( this ).attr( 'data-lpl-track-drawer-player-id' ), 10 );
            } )
            .get();
    }

    // Every caller JSON.stringify()'s trackDrawerPendingItemIds()/
    // trackListPendingItemIds() directly rather than through a shared
    // wrapper here - not a plain array: jQuery's own param serializer drops
    // an empty array from the request body entirely (no item_ids key at
    // all, not item_ids=[]), which read server-side as "key absent" -> "use
    // the real saved _playlist_items" instead of "0 items", so unchecking
    // every row in Media Hub would silently do nothing (preview and, on
    // Update, the actual save both keeping the old list). A JSON string is
    // present in the POST body either way. Parsed back with json_decode()
    // server-side in both class-live-preview-ajax.php and
    // leanpl_admin_new_save_playlist().

    // Set once the first track on a brand-new playlist triggers the
    // auto-draft below, so a second track added moments later (before that
    // save's response lands) can't fire a second wp_insert_post - see the
    // guard inside refreshTrackDrawerPreview(). Stays true for the rest of
    // the page load once set; a real settings.postId from then on is what
    // actually prevents any further auto-draft attempts anyway.
    var trackDrawerAutoDraftStarted = false;

    // Re-renders the playlist live preview with the drawer's current
    // (possibly unsaved) checked set via item_ids[] - Live_Preview_Ajax
    // uses that to override _playlist_items for this one render instead of
    // reading the post's real saved value, same staged-until-Update
    // contract every other field on this screen already follows (see that
    // class's own docblock).
    function refreshTrackDrawerPreview() {
        var settings = window.leanplAdminNewPreview;
        // postId = 0 (a brand-new, not-yet-saved playlist) is intentionally
        // allowed through, not gated out: Live_Preview_Ajax::
        // render_playlist_preview() and Config both accept playlist_id 0
        // as long as item_id_overrides is sent, which it always is below -
        // so a track staged before the first Save still updates the
        // preview instead of silently doing nothing until Update/Publish.
        if ( ! settings || settings.postType !== 'lean_playlist' ) { return; }

        var itemIds = trackDrawerPendingItemIds();

        // First track ever staged on a brand-new playlist: silently create
        // it as a real draft now (same endpoint/payload Save Draft uses,
        // just with no button of its own - submitSave()'s $btn/$label
        // calls are safe no-ops on an empty jQuery set), so the
        // track-to-playlist link survives a refresh or closed tab instead
        // of only existing as this tab's staged DOM state until an
        // explicit Save Draft/Publish. Once this fires, settings.postId
        // itself (set inside submitSave()'s success handler) is what stops
        // it from firing again - trackDrawerAutoDraftStarted only guards
        // the narrow window before that response lands.
        if ( ! ( settings.postId > 0 ) && itemIds.length && ! trackDrawerAutoDraftStarted ) {
            trackDrawerAutoDraftStarted = true;
            submitSave( $(), $(), { extra: { post_status: 'draft' }, silent: true } );
        }

        requestPreview( $.extend(
            { post_ID: settings.postId, item_ids: JSON.stringify( itemIds ), track_overrides: JSON.stringify( trackOverridesPayload() ) },
            formValues( $( '[data-lpl-edit-form]' ) )
        ) );
    }

    // Type is fixed at creation for this playlist (see $edit_playlist_type
    // in html-playlist-edit-new-page.php) - localized alongside the rest of
    // leanplAdminNewPreview so Quick Add/Bulk Add/the upload picker all send
    // the same playlist_type leanpl_create_player_from_url() expects,
    // without re-deriving it from the DOM.
    function trackDrawerPlaylistType() {
        var settings = window.leanplAdminNewPreview;
        return ( settings && settings.playlistType === 'audio' ) ? 'audio' : 'video';
    }

    // Media Hub's own PHP row loop keeps this exact label/color pair per
    // source type (edit-playlist-track-drawer.php) - mirrored here so a row
    // built client-side by trackDrawerInsertRow() matches it exactly.
    var TRACK_DRAWER_SOURCE_LABELS = { youtube: 'YouTube', vimeo: 'Vimeo', external: 'External', uploaded: 'Uploaded' };
    var TRACK_DRAWER_SOURCE_COLORS = { youtube: '#EF4444', vimeo: '#1AB7EA', external: '#767D86', uploaded: '#10B981' };

    // Builds one Media Hub row exactly like the server-rendered ones
    // (edit-playlist-track-drawer.php's PHP loop) so a track just created via
    // Quick Add/Bulk Add is indistinguishable from one the page already
    // listed on load, and both filter/select/stage identically afterward.
    // `checked` starts the row selected - a track the user just added
    // obviously belongs in the playlist. Icon markup comes from
    // leanplAdminNewPreview.trackDrawerIcons (localized from the same
    // Admin_New_Icons the PHP row uses) rather than a second copy of the SVG
    // living in this file.
    function trackDrawerInsertRow( data, checked ) {
        var $list = $( '[data-lpl-track-drawer-media-list]' );
        if ( ! $list.length ) { return; }

        var settings = window.leanplAdminNewPreview || {};
        var icons    = settings.trackDrawerIcons || {};

        // leanpl_create_player_from_url() returns the raw badge type
        // ('self-hosted' for an upload) - Media Hub's filter pills use
        // 'uploaded' for that one case, same remap the PHP row loop applies.
        var source = ( data.source_type === 'self-hosted' ) ? 'uploaded' : ( data.source_type || 'external' );
        var label  = TRACK_DRAWER_SOURCE_LABELS[ source ] || data.source_label || '';
        var color  = TRACK_DRAWER_SOURCE_COLORS[ source ] || '#767D86';

        // Re-adding a track already listed (e.g. re-created via Quick Add
        // with the same URL after removing it earlier this session) replaces
        // its row instead of duplicating it.
        $list.find( '[data-lpl-track-drawer-player-id="' + data.id + '"]' ).remove();

        var $checkSlot = $( '<span class="lpl-hidden group-aria-selected:lpl-flex"></span>' ).html( icons.check || '' );
        var $plusSlot  = $( '<span class="lpl-flex group-aria-selected:lpl-hidden lpl-text-[#000000]"></span>' ).html( icons.plus || '' );

        var $indicator = $( '<div class="lpl-box-border lpl-w-[22px] lpl-shrink-0 lpl-h-[22px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-bg-surface [outline:1px_solid_#000000] [outline-offset:-0.5px] group-aria-selected:lpl-bg-brand group-aria-selected:[outline:none] lpl-rounded-[6px] lpl-text-[#FFFFFF]"></div>' )
            .append( $checkSlot, $plusSlot );

        var $title = $( '<div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#000000] lpl-font-medium lpl-text-left [overflow-wrap:anywhere]"></div>' )
            .text( data.title || '' );
        var $titleWrap = $( '<div class="lpl-box-border [flex:1_1_0] lpl-min-w-0 lpl-h-fit lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-start lpl-items-start"></div>' )
            .append( $title );

        var $badgeLabel = $( '<div class="lpl-text-[10px]/[normal] lpl-box-border lpl-font-semibold lpl-tracking-[0.3px] lpl-text-left [white-space:nowrap]"></div>' )
            .text( label )
            .css( 'color', color );
        var $badge = $( '<div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-[20px] lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_8px] lpl-justify-center lpl-items-center lpl-rounded-[4px]"></div>' )
            .css( 'background-color', color + '1A' )
            .append( $badgeLabel );

        var $row = $( '<div data-lpl-track-drawer-media-row class="lpl-group lpl-cursor-pointer lpl-box-border lpl-w-full lpl-min-h-[48px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-p-[8px_5px] lpl-justify-start lpl-items-center lpl-rounded-[8px] hover:lpl-bg-surface"></div>' )
            .attr( 'data-lpl-track-drawer-player-id', data.id )
            .attr( 'data-lpl-track-drawer-source', source )
            .attr( 'aria-selected', checked ? 'true' : 'false' )
            .append( $indicator, $titleWrap, $badge );

        $list.prepend( $row );
        applyTrackDrawerMediaFilters();
    }

    // Clears Quick Add's fields (and its two wp.media picks) back to their
    // empty state - called after a successful submit, and whenever "Add
    // another after saving" is on so the drawer stays open for the next one.
    function resetTrackDrawerQuickAddForm() {
        $( '[data-lpl-track-drawer-url]' ).val( '' );
        $( '[data-lpl-track-drawer-title]' ).val( '' );
        $( '[data-lpl-track-drawer-duration]' ).val( '' );
        $( '[data-lpl-track-drawer-meta-text]' ).val( '' );

        $( '[data-lpl-track-drawer-upload]' ).attr( 'data-attachment-id', '' );
        $( '[data-lpl-track-drawer-upload-preview]' ).text( 'No media selected' ).addClass( 'lpl-text-ink-soft' );

        $( '[data-lpl-track-drawer-poster]' ).attr( 'data-attachment-id', '' );
        $( '[data-lpl-track-drawer-poster-image]' ).attr( 'src', '' );
        $( '[data-lpl-track-drawer-poster-filename]' ).text( '' );
        $( '[data-lpl-track-drawer-poster-set]' ).addClass( 'lpl-hidden' );
        $( '[data-lpl-track-drawer-poster-empty]' ).removeClass( 'lpl-hidden' );
    }

    // ── Add Track drawer: Quick Add's two wp.media pickers ──────────────────
    // Same wp.media pattern as initMediaLibraryPicker()/initPosterPicker()
    // above, scoped to the drawer's own "Or Upload" (source file) and
    // "Custom Thumbnail" (poster) pickers. Each wrapper
    // (data-lpl-track-drawer-upload / -poster) carries its pick as a
    // data-attachment-id attribute, read at submit time - same "state lives
    // on the DOM" convention trackDrawerPendingItemIds() already uses for
    // Media Hub's checked rows.
    function initTrackDrawerMediaPickers() {
        if ( ! window.wp || ! window.wp.media ) { return; }

        var $uploadWrap    = $( '[data-lpl-track-drawer-upload]' );
        var $uploadPreview = $( '[data-lpl-track-drawer-upload-preview]' );
        var uploadFrame    = null;

        if ( $uploadWrap.length ) {
            $( document ).on( 'click', '[data-lpl-track-drawer-upload-select]', function() {
                if ( ! uploadFrame ) {
                    uploadFrame = window.wp.media( {
                        title: 'Select media',
                        library: { type: trackDrawerPlaylistType() === 'audio' ? [ 'audio' ] : [ 'video' ] },
                        button: { text: 'Use this file' },
                        multiple: false
                    } );

                    uploadFrame.on( 'select', function() {
                        var attachment = uploadFrame.state().get( 'selection' ).first().toJSON();
                        $uploadWrap.attr( 'data-attachment-id', attachment.id );
                        $uploadPreview.text( attachment.filename || attachment.title || '' ).removeClass( 'lpl-text-ink-soft' );
                        // Mutually exclusive with the URL field, same as the
                        // classic metabox's onQaMediaChange().
                        $( '[data-lpl-track-drawer-url]' ).val( '' );
                    } );
                }

                uploadFrame.open();
            } );

            $( document ).on( 'click', '[data-lpl-track-drawer-upload-remove]', function() {
                $uploadWrap.attr( 'data-attachment-id', '' );
                $uploadPreview.text( 'No media selected' ).addClass( 'lpl-text-ink-soft' );
            } );
        }

        // Two-state empty/set swap, same pattern (and same data- attribute
        // naming) as initPosterPicker() uses for the player edit screen's
        // poster field - see edit-playlist-track-drawer.php's
        // data-lpl-track-drawer-poster-empty/-set markup.
        var $posterWrap     = $( '[data-lpl-track-drawer-poster]' );
        var $posterEmpty    = $( '[data-lpl-track-drawer-poster-empty]' );
        var $posterSet      = $( '[data-lpl-track-drawer-poster-set]' );
        var $posterImage    = $( '[data-lpl-track-drawer-poster-image]' );
        var $posterFilename = $( '[data-lpl-track-drawer-poster-filename]' );
        var posterFrame     = null;

        if ( $posterWrap.length ) {
            $( document ).on( 'click', '[data-lpl-track-drawer-poster-select]', function() {
                if ( ! posterFrame ) {
                    posterFrame = window.wp.media( {
                        title: 'Select a thumbnail image',
                        library: { type: [ 'image' ] },
                        button: { text: 'Use this image' },
                        multiple: false
                    } );

                    posterFrame.on( 'select', function() {
                        var attachment = posterFrame.state().get( 'selection' ).first().toJSON();
                        $posterWrap.attr( 'data-attachment-id', attachment.id );
                        $posterImage.attr( 'src', attachment.url );
                        $posterFilename.text( attachment.filename || attachment.title || '' );
                        $posterEmpty.addClass( 'lpl-hidden' );
                        $posterSet.removeClass( 'lpl-hidden' );
                    } );
                }

                posterFrame.open();
            } );

            $( document ).on( 'click', '[data-lpl-track-drawer-poster-remove]', function() {
                $posterWrap.attr( 'data-attachment-id', '' );
                $posterImage.attr( 'src', '' );
                $posterFilename.text( '' );
                $posterSet.addClass( 'lpl-hidden' );
                $posterEmpty.removeClass( 'lpl-hidden' );
            } );
        }
    }

    // ── Add Track drawer: Quick Add submit ───────────────────────────────────
    // Posts to leanpl_playlist_quick_add (includes/playlist/ajax-actions.php) -
    // the same endpoint the classic metabox's builder already uses, reused
    // directly rather than duplicated. On success the new track lands as a
    // checked Media Hub row (trackDrawerInsertRow()) and the live preview
    // re-requests with the drawer's now-updated checked set
    // (refreshTrackDrawerPreview()) - nothing is written to _playlist_items
    // until Update/Publish/Save Draft, same staged contract Media Hub's own
    // row clicks already follow.
    function initTrackDrawerQuickAdd() {
        var $submit = $( '[data-lpl-track-drawer-submit="quick"]' );
        if ( ! $submit.length ) { return; }

        $submit.on( 'click', function() {
            var settings = window.leanplAdminNewPreview;
            if ( ! settings || ! settings.ajaxUrl || $submit.attr( 'aria-disabled' ) === 'true' ) { return; }

            var url          = ( $( '[data-lpl-track-drawer-url]' ).val() || '' ).trim();
            var attachmentId = $( '[data-lpl-track-drawer-upload]' ).attr( 'data-attachment-id' ) || '';

            if ( ! url && ! attachmentId ) {
                showToast( 'error', 'Enter a media URL or choose an upload.' );
                return;
            }

            var $label      = $submit.find( '[data-lpl-track-drawer-submit-label]' );
            var labelBefore = $label.text();

            $submit.attr( 'aria-disabled', 'true' );
            $label.text( 'Adding…' );

            $.post( settings.ajaxUrl, {
                action:        'leanpl_playlist_quick_add',
                nonce:         settings.trackDrawerNonce,
                url:           url,
                attachment_id: attachmentId,
                title:         ( $( '[data-lpl-track-drawer-title]' ).val() || '' ).trim(),
                duration:      ( $( '[data-lpl-track-drawer-duration]' ).val() || '' ).trim(),
                meta_text:     ( $( '[data-lpl-track-drawer-meta-text]' ).val() || '' ).trim(),
                poster_id:     $( '[data-lpl-track-drawer-poster]' ).attr( 'data-attachment-id' ) || '',
                playlist_type: trackDrawerPlaylistType()
            } ).done( function( response ) {
                if ( ! response || ! response.success ) {
                    showToast( 'error', ( response && response.data && response.data.message ) || 'Something went wrong.' );
                    return;
                }

                trackDrawerInsertRow( response.data, true );
                refreshTrackDrawerPreview();

                // Playlist Items card only exists for an already-saved
                // playlist (see $post_id > 0 in edit-section-playlist-track-list.php) -
                // a brand-new one has no card yet, so this is skipped and the
                // track stays Media-Hub-only until the first save, same as
                // before this card-sync existed.
                if ( $( '[data-lpl-track-list-rows]' ).length ) {
                    trackListInsertRow( $.extend( {}, response.data, {
                        type: trackDrawerPlaylistType()
                    } ) );
                    notifyTrackListChanged();
                }

                resetTrackDrawerQuickAddForm();
                showToast( 'success', 'Track added. Click Update to save.' );

                if ( ! $( '[data-lpl-track-drawer-footer="quick"] [data-lpl-track-drawer-add-another]' ).is( ':checked' ) ) {
                    closeTrackDrawer();
                }
            } ).fail( function() {
                showToast( 'error', 'Request failed. Please try again.' );
            } ).always( function() {
                $submit.attr( 'aria-disabled', 'false' );
                $label.text( labelBefore );
            } );
        } );
    }

    // ── Add Track drawer: Bulk Add submit ────────────────────────────────────
    // Posts to leanpl_playlist_batch_add, same reuse-not-duplicate reasoning
    // as Quick Add above. Partial success is the normal outcome (matches the
    // endpoint's own docblock): every added track still lands as a checked
    // Media Hub row, and only the failed lines are put back in the textarea
    // for retry - same UX as the classic metabox's Bulk Add
    // (playlist-admin.js's onBatchAddSubmit()).
    function initTrackDrawerBulkAdd() {
        var $submit = $( '[data-lpl-track-drawer-submit="bulk"]' );
        if ( ! $submit.length ) { return; }

        $submit.on( 'click', function() {
            var settings = window.leanplAdminNewPreview;
            if ( ! settings || ! settings.ajaxUrl || $submit.attr( 'aria-disabled' ) === 'true' ) { return; }

            var $textarea = $( '[data-lpl-track-drawer-bulk-urls]' );
            var urls      = ( $textarea.val() || '' ).trim();

            if ( ! urls ) {
                showToast( 'error', 'Paste at least one URL, one per line.' );
                return;
            }

            var $label      = $submit.find( '[data-lpl-track-drawer-submit-label]' );
            var labelBefore = $label.text();

            $submit.attr( 'aria-disabled', 'true' );
            $label.text( 'Adding…' );

            $.post( settings.ajaxUrl, {
                action:        'leanpl_playlist_batch_add',
                nonce:         settings.trackDrawerNonce,
                urls:          urls,
                playlist_type: trackDrawerPlaylistType()
            } ).done( function( response ) {
                if ( ! response || ! response.success ) {
                    showToast( 'error', ( response && response.data && response.data.message ) || 'Something went wrong.' );
                    return;
                }

                var added  = response.data.added || [];
                var failed = response.data.failed || [];

                var $trackListRows = $( '[data-lpl-track-list-rows]' );

                $.each( added, function( i, data ) {
                    trackDrawerInsertRow( data, true );
                    // No poster field on Bulk Add (see initTrackDrawerBulkAdd()'s
                    // own doc comment above) - type still comes from
                    // trackDrawerPlaylistType(), same as Quick Add.
                    if ( $trackListRows.length ) {
                        trackListInsertRow( $.extend( {}, data, { type: trackDrawerPlaylistType() } ) );
                    }
                } );

                if ( added.length ) {
                    refreshTrackDrawerPreview();
                    // One notify for the whole batch, not one per row - same
                    // "single refresh after the loop" pattern refreshTrackDrawerPreview()
                    // above already follows, so bulk-adding N tracks doesn't
                    // fire N preview requests.
                    if ( $trackListRows.length ) { notifyTrackListChanged(); }
                }

                // Keep only the rejected lines, so the user can fix and
                // retry without re-adding what already succeeded.
                $textarea.val( $.map( failed, function( f ) { return f.url; } ).join( '\n' ) );

                var summary = added.length + ( added.length === 1 ? ' track added' : ' tracks added' );
                if ( failed.length ) {
                    summary += ', ' + failed.length + ' failed';

                    // Surface why, not just how many - the reason (e.g. "audio
                    // URL in a video playlist") is what tells the user whether
                    // retrying is worth it. Distinct reasons are deduped so a
                    // batch that fails the same way N times doesn't repeat itself.
                    // Backend reasons already end in a period, so they become
                    // the summary's terminator instead of appending another.
                    var reasons = [];
                    $.each( failed, function( i, f ) {
                        if ( f.reason && $.inArray( f.reason, reasons ) === -1 ) { reasons.push( f.reason ); }
                    } );
                    if ( reasons.length === 1 ) {
                        showToast( 'info', summary + ': ' + reasons[ 0 ] );
                        return;
                    }
                    if ( reasons.length > 1 ) {
                        showToast( 'info', summary + ': ' + reasons[ 0 ] + ' (+' + ( reasons.length - 1 ) + ' more)' );
                        return;
                    }
                }
                showToast( failed.length ? 'info' : 'success', summary + '.' );

                if ( ! failed.length && ! $( '[data-lpl-track-drawer-footer="bulk"] [data-lpl-track-drawer-add-another]' ).is( ':checked' ) ) {
                    closeTrackDrawer();
                }
            } ).fail( function() {
                showToast( 'error', 'Request failed. Please try again.' );
            } ).always( function() {
                $submit.attr( 'aria-disabled', 'false' );
                $label.text( labelBefore );
            } );
        } );
    }

    function initTrackDrawer() {
        // Guards the whole function on whether the drawer exists on this
        // page at all (same $post_id > 0 gate as the drawer partial itself -
        // a brand-new unsaved playlist has neither). The click binding
        // itself is delegated, not bound to this one static match: the
        // empty-state preview mockup (class-live-preview-ajax.php) injects
        // its own [data-lpl-track-drawer-open] button after this runs, and a
        // direct .on() here would never see it.
        if ( ! $( '[data-lpl-track-drawer-open]' ).length ) { return; }

        $( document ).on( 'click', '[data-lpl-track-drawer-open]', openTrackDrawer );

        $( document ).on( 'click', '[data-lpl-track-drawer-close], [data-lpl-track-drawer-cancel], [data-lpl-track-drawer-overlay]', function() {
            closeTrackDrawer();
        } );

        $( document ).on( 'keydown', function( e ) {
            if ( e.key === 'Escape' ) { closeTrackDrawer(); }
        } );

        $( document ).on( 'click', '[data-lpl-track-drawer-tab]', function() {
            selectTrackDrawerTab( $( this ).attr( 'data-lpl-track-drawer-tab' ) );
        } );

        // Quick Add's "Additional Info" disclosure (Title/Duration/Meta
        // Text/Custom Thumbnail) - deliberately its own tiny toggle, not the
        // page's data-lpl-accordion-trigger mechanism (that one is global
        // and exclusive, see the markup comment in
        // edit-playlist-track-drawer.php for why reusing it would fight the
        // edit screen's own cards).
        $( document ).on( 'click', '[data-lpl-track-drawer-more-toggle]', function() {
            var $trigger  = $( this );
            var expanded  = $trigger.attr( 'aria-expanded' ) === 'true';
            $trigger.attr( 'aria-expanded', expanded ? 'false' : 'true' );
            $( '[data-lpl-track-drawer-more]' ).toggleClass( 'lpl-hidden', expanded );
        } );
        $( document ).on( 'keydown', '[data-lpl-track-drawer-more-toggle]', function( e ) {
            if ( e.key === 'Enter' || e.key === ' ' ) {
                e.preventDefault();
                $( this ).trigger( 'click' );
            }
        } );

        // Add Another is a real <input type="checkbox"> now (wrapped in its
        // own <label>) - the browser handles the toggle-on-click (box or
        // text) and the peer-checked: visual state on its own, no JS needed.
        // initTrackDrawerQuickAdd()/initTrackDrawerBulkAdd() read its
        // .is(':checked') at submit time.

        // Staged, not persisted: flips the row's checked look and re-requests
        // the live preview with the drawer's current checked set (see
        // refreshTrackDrawerPreview()). Nothing writes to _playlist_items
        // until Update/Publish/Save Draft actually saves the screen.
        //
        // Checking/unchecking an existing Media Hub row (as opposed to
        // adding a brand-new one via Quick/Bulk Add, which trackListInsertRow()
        // already handles) also needs to mirror into the Playlist Items
        // card, same reasoning as that gap - otherwise a track picked here
        // shows as staged in the preview but never actually appears in the
        // card until a reload. Media Hub's own markup has no duration/meta_text
        // (see edit-playlist-track-drawer.php's row loop), so those are
        // fetched via the existing leanpl_playlist_get_player endpoint - same
        // reuse-not-duplicate approach the card's own inline edit panel
        // already uses for prefill.
        $( document ).on( 'click', '[data-lpl-track-drawer-media-row]', function() {
            var $row = $( this );
            var nowSelected = $row.attr( 'aria-selected' ) !== 'true';
            $row.attr( 'aria-selected', nowSelected ? 'true' : 'false' );
            refreshTrackDrawerPreview();
            showToast( 'success', nowSelected ? 'Track added. Click Update to save.' : 'Track removed. Click Update to save.' );

            if ( ! $( '[data-lpl-track-list-rows]' ).length ) { return; }

            var playerId = $row.attr( 'data-lpl-track-drawer-player-id' );

            if ( ! nowSelected ) {
                // A single checkbox can't represent duplicate playlist
                // entries - unchecking removes every card row pointing at
                // this player ID, same all-or-nothing semantics the checkbox
                // itself has.
                $( '[data-lpl-track-row][data-lpl-track-player-id="' + playerId + '"]' ).remove();
                $( '[data-lpl-track-list-empty]' ).toggleClass( 'lpl-hidden', $( '[data-lpl-track-row]' ).length !== 0 );
                notifyTrackListChanged();
                return;
            }

            // source_type/source_label come straight off this row's own DOM
            // (title/badge in the 2nd/3rd child, same 3-child structure both
            // the PHP loop and trackDrawerInsertRow() build) rather than a
            // second fetch - only duration/meta_text need the AJAX round trip.
            var source = $row.attr( 'data-lpl-track-drawer-source' ) || 'external';
            var rowData = {
                id:           playerId,
                // .trim(): the PHP-rendered row (unlike trackDrawerInsertRow()'s
                // JS-built one) indents its text nodes, so a raw .text() here
                // would carry that whitespace straight into the card row.
                title:        $row.children().eq( 1 ).find( 'div' ).first().text().trim(),
                source_type:  ( source === 'uploaded' ) ? 'self-hosted' : source,
                source_label: $row.children().eq( 2 ).find( 'div' ).first().text().trim(),
                type:         trackDrawerPlaylistType()
            };

            var settings = window.leanplAdminNewPreview;
            if ( ! settings || ! settings.ajaxUrl ) {
                trackListInsertRow( rowData );
                notifyTrackListChanged();
                return;
            }

            $.post( settings.ajaxUrl, {
                action:    'leanpl_playlist_get_player',
                nonce:     settings.trackDrawerNonce,
                player_id: playerId
            } ).done( function( response ) {
                // Row may have been unchecked again before this landed -
                // trust the row's current state, not the click that started
                // the request.
                if ( $row.attr( 'aria-selected' ) !== 'true' ) { return; }

                if ( response && response.success ) {
                    rowData.duration  = response.data.duration;
                    rowData.meta_text = response.data.meta_text;
                }
                trackListInsertRow( rowData );
                notifyTrackListChanged();
            } ).fail( function() {
                if ( $row.attr( 'aria-selected' ) !== 'true' ) { return; }
                trackListInsertRow( rowData );
                notifyTrackListChanged();
            } );
        } );

        // Source filter pills: single-select, "All" and the four source
        // pills are mutually exclusive.
        $( document ).on( 'click', '[data-lpl-track-drawer-filter]', function() {
            $( '[data-lpl-track-drawer-filter]' ).attr( 'aria-pressed', 'false' );
            $( this ).attr( 'aria-pressed', 'true' );
            applyTrackDrawerMediaFilters();
        } );

        $( document ).on( 'input', '[data-lpl-track-drawer-search]', function() {
            applyTrackDrawerMediaFilters();
        } );
    }

    // ── Playlist edit screen: Playlist Items card ────────────────────────────
    // Drag reorder, inline edit, duplicate, and remove for an existing
    // playlist's tracks (edit-section-playlist-track-list.php). Everything
    // here is DOM-only/staged, same "nothing writes until Update/Publish"
    // contract the Add Track drawer already follows - persistence into
    // _playlist_items is a later task, not this one.

    // This card's own staged order - DOM order of [data-lpl-track-row]
    // player-ids, duplicates included. Same "DOM is the source of truth"
    // approach trackDrawerPendingItemIds() uses for Media Hub's checked set,
    // but this one carries order and duplicates too, which a checkbox set
    // can't represent.
    function trackListPendingItemIds() {
        return $( '[data-lpl-track-list-rows] [data-lpl-track-row]' )
            .map( function() {
                return parseInt( $( this ).attr( 'data-lpl-track-player-id' ), 10 );
            } )
            .get();
    }

    // Mirrors refreshTrackDrawerPreview()'s item_ids-override pattern
    // (requestPreview() + formValues(), same merge), sourced from this
    // card's own row order instead of Media Hub's checked set. No auto-draft
    // concern to replicate here - reorder/duplicate/remove only ever touch
    // an already-saved playlist's existing rows (this card renders nothing
    // for a brand-new, unsaved one - see $post_id > 0 in the partial).
    function refreshTrackListPreview() {
        var settings = window.leanplAdminNewPreview;
        if ( ! settings || settings.postType !== 'lean_playlist' ) { return; }

        requestPreview( $.extend(
            { post_ID: settings.postId, item_ids: JSON.stringify( trackListPendingItemIds() ), track_overrides: JSON.stringify( trackOverridesPayload() ) },
            formValues( $( '[data-lpl-edit-form]' ) )
        ) );
    }

    // Single event every card mutation (drag reorder, duplicate, remove,
    // inline edit save) fires into, plus the one the Add Track drawer's
    // Quick/Bulk Add success handlers use to push a newly staged track into
    // this same card (see trackListInsertRow() below) - one place says "the
    // staged track set changed" instead of every call site remembering to
    // call refreshTrackListPreview() itself.
    function notifyTrackListChanged() {
        $( document ).trigger( 'leanpl:track-list:changed' );
    }

    // The reverse direction of trackListInsertRow(): the card's own remove/
    // duplicate/drag/add-via-drawer all change which player IDs are staged,
    // but Media Hub's checked state (aria-selected) is separate DOM that
    // nothing was updating - remove a track from the card and its Media Hub
    // row stayed checked, still showing it as "in the playlist" next time the
    // drawer opened. Recomputes every checked row from the card's current
    // set on every leanpl:track-list:changed rather than patching one row at
    // a time, since duplicate/reorder/remove can all change membership in
    // ways a single row patch can't express (e.g. removing the only
    // remaining duplicate of an id should uncheck it, removing one of two
    // shouldn't).
    function syncTrackDrawerMediaSelection() {
        var $list = $( '[data-lpl-track-drawer-media-list]' );
        if ( ! $list.length ) { return; }

        var stagedIds = {};
        $.each( trackListPendingItemIds(), function( i, id ) { stagedIds[ id ] = true; } );

        $list.find( '[data-lpl-track-drawer-media-row]' ).each( function() {
            var $row = $( this );
            var id   = parseInt( $row.attr( 'data-lpl-track-drawer-player-id' ), 10 );
            $row.attr( 'aria-selected', stagedIds[ id ] ? 'true' : 'false' );
        } );
    }

    // aria-expanded on the row itself (not just its panel's lpl-hidden) is
    // what the row's own highlight styling reacts to (aria-expanded:lpl-bg-surface
    // in the partial/trackListInsertRow() below) - same aria-driven-CSS
    // convention initAccordions() already uses for its trigger chevrons.
    function closeAllTrackEditPanels( $except ) {
        $( '[data-lpl-track-edit-panel]' ).not( $except || [] ).addClass( 'lpl-hidden' ).each( function() {
            $( this ).closest( '[data-lpl-track-row]' ).attr( 'aria-expanded', 'false' );
        } );
    }

    // Prefills one row's edit panel from leanpl_playlist_get_player's
    // response (same shape leanpl_get_player_edit_data() returns) - same
    // two-state poster swap initTrackDrawerMediaPickers() uses for the Add
    // Track drawer's own poster field.
    function trackEditFieldsFromData( $panel, data ) {
        $panel.find( '[data-lpl-track-edit-url]' ).val( data.url || '' );
        $panel.find( '[data-lpl-track-edit-title]' ).val( data.title || '' );
        $panel.find( '[data-lpl-track-edit-duration]' ).val( data.duration || '' );
        $panel.find( '[data-lpl-track-edit-meta-text]' ).val( data.meta_text || '' );

        // This panel has no "Or Upload" picker (only Paste a link) - an
        // uploaded-source track's real source is attachment_id, not url, so
        // it's stashed here and falls back into Save Changes' payload
        // whenever the url field is left blank, instead of the save
        // silently losing the track's actual source.
        $panel.attr( 'data-lpl-track-edit-source-attachment-id', data.attachment_id || '' );

        var $posterWrap  = $panel.find( '[data-lpl-track-edit-poster]' );
        var $posterEmpty = $panel.find( '[data-lpl-track-edit-poster-empty]' );
        var $posterSet   = $panel.find( '[data-lpl-track-edit-poster-set]' );

        if ( data.poster_id ) {
            $posterWrap.attr( 'data-attachment-id', data.poster_id );
            $panel.find( '[data-lpl-track-edit-poster-image]' ).attr( 'src', data.poster_url || '' );
            $panel.find( '[data-lpl-track-edit-poster-filename]' ).text( ( data.poster_url || '' ).split( '/' ).pop() );
            $posterEmpty.addClass( 'lpl-hidden' );
            $posterSet.removeClass( 'lpl-hidden' );
        } else {
            $posterWrap.attr( 'data-attachment-id', '' );
            $posterEmpty.removeClass( 'lpl-hidden' );
            $posterSet.addClass( 'lpl-hidden' );
        }
    }

    // Same raw badge-type keys leanpl_get_player_source_badge() returns
    // (and edit-section-playlist-track-list.php's own $track_list_source_colors
    // mirrors) - not TRACK_DRAWER_SOURCE_COLORS above, which remaps
    // 'self-hosted' to 'uploaded' for Media Hub's filter pills only.
    var TRACK_LIST_SOURCE_COLORS = { youtube: '#EF4444', vimeo: '#1AB7EA', external: '#767D86', 'self-hosted': '#10B981' };

    // Updates one row's title/badge/meta/duration in place from
    // leanpl_playlist_update_player's response. The row has no thumbnail to
    // refresh - Custom Thumbnail/Duration/Meta Text in the edit panel still
    // set the track's real meta, they just aren't mirrored into this list's
    // row display anymore (title + source badge only).
    function trackListApplyRowData( $row, data ) {
        $row.find( '[data-lpl-track-title]' ).text( data.title || '' ).attr( 'title', data.title || '' );

        if ( data.source_label ) {
            $row.find( '[data-lpl-track-source-label]' ).text( data.source_label );
            var color = TRACK_LIST_SOURCE_COLORS[ data.source_type ] || '#767D86';
            $row.find( '[data-lpl-track-source-badge]' ).css( 'background-color', color + '1A' );
            $row.find( '[data-lpl-track-source-label]' ).css( 'color', color );
        }
    }

    // ── Playlist edit screen: staged (not yet saved) per-track field edits ──
    // Row edit panels have no Save Changes button anymore - every field
    // stages itself into here on input instead, keyed by player ID. Shape
    // mirrors leanpl_update_player_from_url()'s own args (url/attachment_id/
    // title/duration/meta_text/poster_id), the same shape the old
    // immediate-write Save Changes click used to send - only the timing
    // changed, not the payload. Actually written on Update/Publish
    // (submitSave()'s track_edits, leanpl_admin_new_save_playlist()'s
    // matching handler), same staged-until-save contract _playlist_items
    // already has.
    var TrackEdits = {};

    // Same field-gathering logic the old Save Changes handler used, just
    // called on every input instead of a button click.
    function trackEditPanelValues( $panel ) {
        var url = ( $panel.find( '[data-lpl-track-edit-url]' ).val() || '' ).trim();
        return {
            // Falls back to the source attachment_id trackEditFieldsFromData()
            // stashed on open, but only when url is blank - an uploaded track
            // left untouched must keep its real source instead of staging
            // "no source" (empty url, no attachment_id).
            url:           url,
            attachment_id: url ? '' : ( $panel.attr( 'data-lpl-track-edit-source-attachment-id' ) || '' ),
            title:         ( $panel.find( '[data-lpl-track-edit-title]' ).val() || '' ).trim(),
            duration:      ( $panel.find( '[data-lpl-track-edit-duration]' ).val() || '' ).trim(),
            meta_text:     ( $panel.find( '[data-lpl-track-edit-meta-text]' ).val() || '' ).trim(),
            poster_id:     $panel.find( '[data-lpl-track-edit-poster]' ).attr( 'data-attachment-id' ) || ''
        };
    }

    var trackEditPreviewTimer = null;

    // Stages the panel's current values, patches the row's own title/meta
    // display immediately (trackListApplyRowData() - no source_label key
    // here, so the row's source badge deliberately stays whatever it was;
    // re-detecting source from a staged URL isn't wired into the preview
    // path, see trackOverridesPayload() below), and debounces a live-preview
    // refresh the same way requestFormPreview() does for the player screen's
    // own fields, so rapid typing doesn't fire an AJAX call per keystroke.
    function stageTrackEdit( $row ) {
        var playerId = $row.attr( 'data-lpl-track-player-id' );
        var $panel   = $row.find( '[data-lpl-track-edit-panel]' ).first();
        if ( ! playerId || ! $panel.length ) { return; }

        TrackEdits[ playerId ] = trackEditPanelValues( $panel );
        trackListApplyRowData( $row, TrackEdits[ playerId ] );

        window.clearTimeout( trackEditPreviewTimer );
        trackEditPreviewTimer = window.setTimeout( function() {
            notifyTrackListChanged();
        }, 400 );
    }

    // Preview-safe subset of TrackEdits: title/duration/meta_text/poster_id
    // only. The "Paste a link" (url/attachment_id) field stages and saves
    // like everything else, it just doesn't reach the live preview - see
    // class-live-preview-ajax.php's post_track_overrides() for why
    // (re-detecting source type from an unsaved URL isn't wired into that
    // path).
    function trackOverridesPayload() {
        var overrides = {};
        $.each( TrackEdits, function( playerId, patch ) {
            overrides[ playerId ] = {
                title:     patch.title,
                duration:  patch.duration,
                meta_text: patch.meta_text,
                poster_id: patch.poster_id
            };
        } );
        return overrides;
    }

    // Static twin of edit-section-playlist-track-list.php's inline edit
    // panel markup (lines 139-219 there) - every field starts blank/closed,
    // same as a fresh page-load row before initTrackList()'s toggle handler
    // lazy-fetches real values, so this never needs to interpolate anything
    // user-controlled. Only the "Paste a link" placeholder varies, same
    // audio/video split as $track_list_url_placeholder in that file.
    function trackListEditPanelHtml( type ) {
        var urlPlaceholder = ( type === 'audio' )
            ? 'https://example.com/podcast-ep12.mp3'
            : 'https://youtube.com/watch?v=dQw4w9WgXcQ';

        return '' +
            '<div data-lpl-track-edit-panel class="lpl-hidden lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[10px] lpl-p-[4px_0px_14px_26px] lpl-justify-start lpl-items-start">' +
                '<div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">' +
                    '<label class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">Paste a link</label>' +
                    '<div class="lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">' +
                        '<input type="text" data-lpl-track-edit-url placeholder="' + urlPlaceholder + '" class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />' +
                    '</div>' +
                '</div>' +
                '<div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">' +
                    '<label class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">Title</label>' +
                    '<div class="lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">' +
                        '<input type="text" data-lpl-track-edit-title class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />' +
                    '</div>' +
                '</div>' +
                '<div class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-start">' +
                    '<div class="lpl-box-border [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">' +
                        '<label class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">Duration</label>' +
                        '<div class="lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">' +
                            '<input type="text" data-lpl-track-edit-duration placeholder="e.g. 3:45" class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />' +
                        '</div>' +
                    '</div>' +
                    '<div class="lpl-box-border [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">' +
                        '<label class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">Meta Text</label>' +
                        '<div class="lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">' +
                            '<input type="text" data-lpl-track-edit-meta-text placeholder="e.g. Episode 12" class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-[#2D2D2D] lpl-font-normal lpl-w-full lpl-border-0 lpl-bg-transparent lpl-p-0 focus:lpl-outline-none [appearance:none]" />' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div data-lpl-track-edit-poster data-attachment-id="" class="lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-[6px] lpl-justify-start lpl-items-start">' +
                    '<div class="lpl-text-[12px]/[normal] lpl-box-border lpl-text-ink lpl-font-semibold lpl-text-left [white-space:nowrap]">Custom Thumbnail</div>' +
                    '<div data-lpl-track-edit-poster-empty data-lpl-track-edit-poster-select class="lpl-cursor-pointer lpl-box-border lpl-w-full lpl-h-[34px] lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_10px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">' +
                        '<div class="lpl-text-[13px]/[normal] lpl-box-border lpl-text-ink-soft lpl-font-normal lpl-text-left [white-space:nowrap]">No image selected</div>' +
                    '</div>' +
                    '<div data-lpl-track-edit-poster-set class="lpl-hidden lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[8px] lpl-justify-start lpl-items-center lpl-bg-[#FFFFFF] [outline:1px_solid_var(--lpl-line-strong)] [outline-offset:-0.5px] lpl-rounded-[7px]">' +
                        '<div class="lpl-relative lpl-box-border lpl-w-[44px] lpl-h-[44px] lpl-shrink-0 lpl-rounded-[6px] lpl-overflow-hidden [outline:1px_solid_var(--lpl-line)] [outline-offset:-0.5px]">' +
                            '<img src="" alt="" class="lpl-absolute lpl-top-0 lpl-left-0 lpl-w-full lpl-h-full lpl-object-cover" data-lpl-track-edit-poster-image />' +
                        '</div>' +
                        '<div class="lpl-box-border lpl-min-w-0 [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[4px] lpl-justify-center lpl-items-start">' +
                            '<div class="lpl-text-[13px]/[normal] lpl-box-border lpl-w-full lpl-text-ink lpl-font-medium lpl-truncate" data-lpl-track-edit-poster-filename></div>' +
                            '<div class="lpl-box-border lpl-flex lpl-flex-row lpl-gap-[12px] lpl-justify-start lpl-items-center">' +
                                '<div data-lpl-track-edit-poster-select role="button" tabindex="0" class="lpl-cursor-pointer lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#4F46E5] lpl-font-medium">Change</div>' +
                                '<div data-lpl-track-edit-poster-remove role="button" tabindex="0" class="lpl-cursor-pointer lpl-text-[12px]/[normal] lpl-box-border lpl-text-[#DC2626] lpl-font-medium">Remove</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
    }

    // Builds one Playlist Items card row exactly like the server-rendered
    // ones (edit-section-playlist-track-list.php's PHP loop), same
    // reuse-the-server-markup approach trackDrawerInsertRow() already uses
    // for Media Hub rows - so a track just added via the drawer's Quick Add/
    // Bulk Add is indistinguishable from one the card already listed on
    // load, and drag/duplicate/remove/toggle all work on it identically
    // (those handlers are delegated on $(document) in initTrackList(), not
    // bound per-row, so a row built here needs no extra wiring).
    //
    // `data` is whatever leanpl_playlist_quick_add/leanpl_playlist_batch_add
    // returned (id/title/source_type/source_label/duration/meta_text) plus
    // one field the caller adds locally since neither endpoint sends it:
    // type ('video'|'audio', from trackDrawerPlaylistType() - the response
    // has no per-track player_type). Only used for the edit panel's "Paste
    // a link" placeholder - the row itself has no thumbnail to pick an icon
    // for.
    function trackListInsertRow( data ) {
        var $rows = $( '[data-lpl-track-list-rows]' );
        if ( ! $rows.length ) { return; }

        var settings = window.leanplAdminNewPreview || {};
        var icons    = settings.trackListIcons || {};

        var color = TRACK_LIST_SOURCE_COLORS[ data.source_type ] || '#767D86';

        var $title = $( '<div data-lpl-track-title class="lpl-text-[13px]/[normal] lpl-box-border lpl-w-full lpl-text-ink lpl-font-semibold lpl-text-left lpl-truncate"></div>' )
            .text( data.title || '' )
            .attr( 'title', data.title || '' );

        var $badgeLabel = $( '<div data-lpl-track-source-label class="lpl-text-[9px]/[normal] lpl-box-border lpl-font-semibold lpl-tracking-[0.3px] lpl-text-left [white-space:nowrap]"></div>' )
            .text( data.source_label || '' )
            .css( 'color', color );
        var $badge = $( '<div data-lpl-track-source-badge class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-h-[16px] lpl-flex lpl-flex-row lpl-gap-0 lpl-p-[0px_6px] lpl-justify-center lpl-items-center lpl-rounded-[4px]"></div>' )
            .css( 'background-color', color + '1A' )
            .append( $badgeLabel );

        var $toggle = $( '<div data-lpl-track-toggle role="button" tabindex="0" class="lpl-cursor-pointer lpl-box-border lpl-min-w-0 [flex:1_1_0] lpl-flex lpl-flex-row lpl-gap-[10px] lpl-justify-start lpl-items-center"></div>' )
            .append(
                $( '<div class="lpl-box-border lpl-min-w-0 [flex:1_1_0] lpl-flex lpl-flex-col lpl-gap-[3px] lpl-justify-start lpl-items-start"></div>' ).append(
                    $title,
                    $( '<div class="lpl-box-border lpl-w-full lpl-flex lpl-flex-row lpl-gap-[6px] lpl-justify-start lpl-items-center"></div>' ).append( $badge )
                )
            );

        var $handle = $( '<div data-lpl-track-drag-handle class="lpl-cursor-grab lpl-box-border lpl-w-[16px] lpl-shrink-0 lpl-h-[16px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-text-line-strong"></div>' )
            .html( icons[ 'grip-vertical' ] || '' );

        var $duplicateBtn = $( '<div data-lpl-track-duplicate role="button" tabindex="0" aria-label="Duplicate" class="lpl-cursor-pointer lpl-box-border lpl-w-[26px] lpl-shrink-0 lpl-h-[26px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-text-ink-soft hover:lpl-bg-surface lpl-rounded-[6px]"></div>' )
            .html( icons.copy || '' );
        var $removeBtn = $( '<div data-lpl-track-remove role="button" tabindex="0" aria-label="Remove" class="lpl-cursor-pointer lpl-box-border lpl-w-[26px] lpl-shrink-0 lpl-h-[26px] lpl-flex lpl-flex-row lpl-gap-0 lpl-justify-center lpl-items-center lpl-text-ink-soft hover:lpl-bg-surface lpl-rounded-[6px]"></div>' )
            .html( icons.x || '' );
        var $actions = $( '<div class="lpl-box-border lpl-w-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[2px] lpl-justify-start lpl-items-center"></div>' )
            .append( $duplicateBtn, $removeBtn );

        var $head = $( '<div class="lpl-box-border lpl-w-full lpl-h-fit lpl-shrink-0 lpl-flex lpl-flex-row lpl-gap-[10px] lpl-p-[8px_0px] lpl-justify-start lpl-items-center"></div>' )
            .append( $handle, $toggle, $actions );

        // Closed/unfetched, same as a fresh page-load row - opening it lazily
        // prefills via leanpl_playlist_get_player, same as every other row
        // (see the data-lpl-track-toggle click handler in initTrackList()).
        // Static markup only (no dynamic values to escape - every field
        // starts blank, same as edit-section-playlist-track-list.php's own
        // panel does for a page-load row), so a plain HTML string is safe
        // here unlike the fields above.
        var $panel = $( trackListEditPanelHtml( data.type ) );

        var $row = $( '<div data-lpl-track-row aria-expanded="false" class="lpl-group lpl-box-border lpl-w-full lpl-shrink-0 lpl-flex lpl-flex-col lpl-gap-0 lpl-justify-start lpl-items-start [border-width:0px_0px_1px_0px] [border-style:solid] [border-color:var(--lpl-line)] [margin:0px_0px_-0.5px_0px] [&:last-child]:[border-width:0px] aria-expanded:lpl-bg-surface aria-expanded:lpl-rounded-[8px] aria-expanded:lpl-pr-[10px] aria-expanded:[border-bottom-width:0px]"></div>' )
            .attr( 'data-lpl-track-player-id', data.id )
            .append( $head, $panel );

        $rows.append( $row );
        $( '[data-lpl-track-list-empty]' ).addClass( 'lpl-hidden' );
    }

    function initTrackList() {
        var $rows = $( '[data-lpl-track-list-rows]' );
        if ( ! $rows.length ) { return; }

        // The one listener for notifyTrackListChanged() - every mutation
        // below (and trackListInsertRow(), called from the Add Track
        // drawer's Quick/Bulk Add success handlers) fires that event instead
        // of calling refreshTrackListPreview() directly.
        $( document ).on( 'leanpl:track-list:changed', function() {
            refreshTrackListPreview();
            syncTrackDrawerMediaSelection();
        } );

        // handle scopes drag-start to the grip icon only, so clicks on the
        // toggle/duplicate/remove targets below never accidentally start a
        // drag - no filter/preventOnFilter needed the way playlist-admin.js's
        // whole-row handle requires. ghostClass reuses an existing Tailwind
        // utility (lpl-opacity-40, already used for the preview-swap dim
        // above) rather than a new CSS class of its own.
        if ( window.Sortable ) {
            Sortable.create( $rows[ 0 ], {
                handle:      '[data-lpl-track-drag-handle]',
                animation:   150,
                ghostClass:  'lpl-opacity-40',
                onEnd: function() {
                    notifyTrackListChanged();
                }
            } );
        }

        // Row click (anywhere but drag handle/actions, see
        // data-lpl-track-toggle's scope in the partial) opens that row's
        // inline edit panel and lazy-fetches its real values via
        // leanpl_playlist_get_player - only once per row (data-lpl-track-edit-loaded),
        // same reuse-existing-endpoint approach as the Add Track drawer.
        $( document ).on( 'click', '[data-lpl-track-toggle]', function( e ) {
            e.preventDefault();

            var $row   = $( this ).closest( '[data-lpl-track-row]' );
            var $panel = $row.find( '[data-lpl-track-edit-panel]' ).first();
            if ( ! $panel.length ) { return; }

            var wasOpen = ! $panel.hasClass( 'lpl-hidden' );
            closeAllTrackEditPanels( $panel );

            if ( wasOpen ) {
                $panel.addClass( 'lpl-hidden' );
                $row.attr( 'aria-expanded', 'false' );
                return;
            }

            $panel.removeClass( 'lpl-hidden' );
            $row.attr( 'aria-expanded', 'true' );

            var settings  = window.leanplAdminNewPreview;
            var playerId  = $panel.closest( '[data-lpl-track-row]' ).attr( 'data-lpl-track-player-id' );
            if ( ! settings || ! settings.ajaxUrl || $panel.attr( 'data-lpl-track-edit-loaded' ) === 'true' ) { return; }

            $.post( settings.ajaxUrl, {
                action:    'leanpl_playlist_get_player',
                nonce:     settings.trackDrawerNonce,
                player_id: playerId
            } ).done( function( response ) {
                if ( ! response || ! response.success ) {
                    showToast( 'error', ( response && response.data && response.data.message ) || 'Could not load track.' );
                    return;
                }
                trackEditFieldsFromData( $panel, response.data );
                $panel.attr( 'data-lpl-track-edit-loaded', 'true' );
            } ).fail( function() {
                showToast( 'error', 'Request failed. Please try again.' );
            } );
        } );

        // No more Cancel/Save Changes buttons - every field stages itself on
        // input (stageTrackEdit(), defined above initTrackList()) instead of
        // writing on an explicit click. Closing the panel is just re-clicking
        // the row (data-lpl-track-toggle above); staged data already lives in
        // TrackEdits regardless of whether the panel is open, so there's
        // nothing to lose by closing it.
        $( document ).on( 'input change', '[data-lpl-track-edit-url], [data-lpl-track-edit-title], [data-lpl-track-edit-duration], [data-lpl-track-edit-meta-text]', function() {
            stageTrackEdit( $( this ).closest( '[data-lpl-track-row]' ) );
        } );

        // Same two-state empty/set wp.media poster picker as
        // initTrackDrawerMediaPickers() - scoped per-row via .closest()
        // since every row carries its own [data-lpl-track-edit-poster]. Also
        // stages (poster_id is one of stageTrackEdit()'s tracked fields) -
        // same as the text fields above, this used to only take effect on
        // Save Changes.
        $( document ).on( 'click', '[data-lpl-track-edit-poster-select]', function() {
            if ( ! window.wp || ! window.wp.media ) { return; }

            var $wrap     = $( this ).closest( '[data-lpl-track-edit-poster]' );
            var $empty    = $wrap.find( '[data-lpl-track-edit-poster-empty]' );
            var $set      = $wrap.find( '[data-lpl-track-edit-poster-set]' );
            var $image    = $wrap.find( '[data-lpl-track-edit-poster-image]' );
            var $filename = $wrap.find( '[data-lpl-track-edit-poster-filename]' );
            var $row      = $wrap.closest( '[data-lpl-track-row]' );

            var frame = window.wp.media( {
                title: 'Select a thumbnail image',
                library: { type: [ 'image' ] },
                button: { text: 'Use this image' },
                multiple: false
            } );

            frame.on( 'select', function() {
                var attachment = frame.state().get( 'selection' ).first().toJSON();
                $wrap.attr( 'data-attachment-id', attachment.id );
                $image.attr( 'src', attachment.url );
                $filename.text( attachment.filename || attachment.title || '' );
                $empty.addClass( 'lpl-hidden' );
                $set.removeClass( 'lpl-hidden' );
                stageTrackEdit( $row );
            } );

            frame.open();
        } );

        $( document ).on( 'click', '[data-lpl-track-edit-poster-remove]', function() {
            var $wrap = $( this ).closest( '[data-lpl-track-edit-poster]' );
            $wrap.attr( 'data-attachment-id', '' );
            $wrap.find( '[data-lpl-track-edit-poster-image]' ).attr( 'src', '' );
            $wrap.find( '[data-lpl-track-edit-poster-filename]' ).text( '' );
            $wrap.find( '[data-lpl-track-edit-poster-set]' ).addClass( 'lpl-hidden' );
            $wrap.find( '[data-lpl-track-edit-poster-empty]' ).removeClass( 'lpl-hidden' );
            stageTrackEdit( $wrap.closest( '[data-lpl-track-row]' ) );
        } );

        // Duplicate: clones the row's markup (same player_id, a second
        // playlist entry pointing at the same lean_player post) right after
        // itself. The clone's edit panel is reset to closed/unfetched rather
        // than carrying over the original's cached values, so opening it
        // fetches fresh rather than showing stale/shared field state.
        $( document ).on( 'click', '[data-lpl-track-duplicate]', function() {
            var $row   = $( this ).closest( '[data-lpl-track-row]' );
            var $clone = $row.clone( false );

            $clone.removeAttr( 'data-lpl-track-row-overflow' ).removeClass( 'lpl-hidden' );

            var $clonePanel = $clone.find( '[data-lpl-track-edit-panel]' );
            $clonePanel.addClass( 'lpl-hidden' ).removeAttr( 'data-lpl-track-edit-loaded' );
            $clonePanel.find( 'input[type="text"]' ).val( '' );
            $clonePanel.find( '[data-lpl-track-edit-poster]' ).attr( 'data-attachment-id', '' );
            $clonePanel.find( '[data-lpl-track-edit-poster-image]' ).attr( 'src', '' );
            $clonePanel.find( '[data-lpl-track-edit-poster-filename]' ).text( '' );
            $clonePanel.find( '[data-lpl-track-edit-poster-set]' ).addClass( 'lpl-hidden' );
            $clonePanel.find( '[data-lpl-track-edit-poster-empty]' ).removeClass( 'lpl-hidden' );

            $row.after( $clone );
            notifyTrackListChanged();
        } );

        // Remove: DOM-only, same staged contract as everything else on this
        // card - the row (and any duplicates of it) only actually leave
        // _playlist_items once Update/Publish saves the screen.
        $( document ).on( 'click', '[data-lpl-track-remove]', function() {
            $( this ).closest( '[data-lpl-track-row]' ).remove();
            notifyTrackListChanged();
            $( '[data-lpl-track-list-empty]' ).toggleClass( 'lpl-hidden', $( '[data-lpl-track-row]' ).length !== 0 );
        } );

        // "Show N more": reveals the rows the partial pre-collapsed past the
        // 5th (data-lpl-track-row-overflow) rather than re-deriving the
        // threshold client-side.
        $( document ).on( 'click', '[data-lpl-track-list-more-toggle]', function( e ) {
            e.preventDefault();
            var $toggle  = $( this );
            var expanded = $toggle.attr( 'aria-expanded' ) === 'true';
            $toggle.attr( 'aria-expanded', expanded ? 'false' : 'true' );
            $( '[data-lpl-track-row-overflow]' ).toggleClass( 'lpl-hidden', expanded );
        } );
    }

    // ── "New Playlist" video/audio type picker modal ─────────────────────────
    // Every playlist-creation entry point (the All Playlists list screen's
    // topbar + empty-state "Add Playlist" links, the edit screen's overflow
    // "Add New Playlist") carries data-lpl-add-playlist-trigger instead of
    // navigating straight to its href - see add-playlist-type-modal.php for
    // why (type is fixed at creation and has no picker on the edit screen
    // itself, so this modal is the only place it's ever chosen). Picking a
    // card creates the real playlist post right then (leanpl_admin_new_create_playlist,
    // with _playlist_type already set) and only navigates to the trigger's
    // own href + &post={id} once that succeeds - the edit screen never has
    // to handle a "type chosen but no post yet" state at all.
    function initAddPlaylistModal() {
        var $overlay = $( '[data-lpl-add-playlist-overlay]' );
        if ( ! $overlay.length ) { return; }

        var pendingHref = '';

        function closeModal() {
            $overlay.addClass( 'lpl-hidden' );
        }

        $( document ).on( 'click', '[data-lpl-add-playlist-trigger]', function( e ) {
            e.preventDefault();
            pendingHref = $( this ).attr( 'href' ) || '';
            $overlay.removeClass( 'lpl-hidden' );
        } );

        $( document ).on( 'click', '[data-lpl-add-playlist-close]', closeModal );

        // Overlay click closes only when the click landed on the backdrop
        // itself, not something inside the panel bubbling up to it.
        $overlay.on( 'click', function( e ) {
            if ( e.target === this ) { closeModal(); }
        } );

        $( document ).on( 'keydown', function( e ) {
            if ( e.key === 'Escape' && ! $overlay.hasClass( 'lpl-hidden' ) ) { closeModal(); }
        } );

        // Reuses the leanpl_admin_new nonce/ajax_url every screen this modal
        // appears on already has localized for bulk actions
        // (leanpl_admin_new_enqueue_localize()) - the list screen has no
        // leanplAdminNewPreview object (only the edit screen localizes that
        // one), so this can't reuse the edit screen's own save nonce.
        $( document ).on( 'click', '[data-lpl-add-playlist-type]', function( e ) {
            e.preventDefault();

            var $cards   = $( '[data-lpl-add-playlist-type]' );
            var type     = $( this ).attr( 'data-lpl-add-playlist-type' );
            var settings = window.leanplAdminNew;
            if ( ! settings || ! settings.ajax_url || $cards.attr( 'aria-disabled' ) === 'true' ) { return; }

            $cards.attr( 'aria-disabled', 'true' );

            $.post( settings.ajax_url, {
                action:        'leanpl_admin_new_create_playlist',
                nonce:         settings.nonce,
                playlist_type: type
            } ).done( function( response ) {
                if ( ! response || ! response.success ) {
                    showToast( 'error', ( response && response.data && response.data.message ) || 'Could not create playlist.' );
                    $cards.removeAttr( 'aria-disabled' );
                    return;
                }

                var separator = pendingHref.indexOf( '?' ) > -1 ? '&' : '?';
                window.location.href = pendingHref + separator + 'post=' + response.data.postId;
            } ).fail( function() {
                showToast( 'error', 'Request failed. Please try again.' );
                $cards.removeAttr( 'aria-disabled' );
            } );
        } );
    }

    // ── Edit-screen title field ──────────────────────────────────────────────
    // data-lpl-title-input is a real, always-editable <input> (see
    // .lpl-admin input[type="text"] in tailwind-admin.src.css for why it
    // needs no per-field border/padding classes of its own). The pencil
    // icon next to it is not a separate "enter edit mode" action - it just
    // focuses the input, same as clicking the field itself would.
    function initTitleField() {
        var $trigger = $( '[data-lpl-title-focus]' );
        if ( ! $trigger.length ) { return; }

        $trigger.on( 'click', function() {
            $( '[data-lpl-title-input]' ).trigger( 'focus' ).select();
        } );
    }

    // ── Edit-screen accordion cards ──────────────────────────────────────────
    // data-lpl-accordion-trigger="KEY" / data-lpl-accordion="KEY". aria-expanded
    // on the trigger drives both the chevron rotation and body visibility via
    // CSS (group-aria-expanded: / [aria-expanded] variants in the markup) -
    // this only ever flips the attribute and the lpl-hidden class, same
    // division of labor as every other aria-driven control in this file.
    // Exclusive: opening one card closes every other one, so at most one
    // card's fields are on screen at a time.
    function initAccordions() {
        var $triggers = $( '[data-lpl-accordion-trigger]' );
        if ( ! $triggers.length ) { return; }

        function toggle( $trigger ) {
            var key = $trigger.attr( 'data-lpl-accordion-trigger' );
            var expanded = $trigger.attr( 'aria-expanded' ) === 'true';

            $triggers.not( $trigger ).attr( 'aria-expanded', 'false' );
            $( '[data-lpl-accordion]' ).not( '[data-lpl-accordion="' + key + '"]' ).addClass( 'lpl-hidden' );

            $trigger.attr( 'aria-expanded', expanded ? 'false' : 'true' );
            $( '[data-lpl-accordion="' + key + '"]' ).toggleClass( 'lpl-hidden', expanded );
        }

        $triggers.on( 'click', function() {
            toggle( $( this ) );
        } );

        $triggers.on( 'keydown', function( e ) {
            if ( e.key === 'Enter' || e.key === ' ' ) {
                e.preventDefault();
                toggle( $( this ) );
            }
        } );
    }

    // ── Edit-screen source picker tabs ───────────────────────────────────────
    // data-lpl-source-tab="KEY" - single-select via aria-selected, same
    // pattern as the sort menu. Each tab carries its own URL-field copy as
    // data attrs (data-lpl-source-show-url/-url-label/-placeholder/-help,
    // written by config/player-edit-source-tabs.php), so a click swaps the
    // label/placeholder/help text and shows or hides the URL field to match
    // the picked source - Media Library has none, the rest each have their
    // own. No source is actually fetched; this only ever moves text and
    // toggles lpl-hidden, same as every other control in this file.
    function initSourceTabs() {
        var $tabs = $( '[data-lpl-source-tab]' );
        if ( ! $tabs.length ) { return; }

        var $urlField = $( '[data-lpl-source-url-field]' );
        var $urlLabel = $( '[data-lpl-source-url-label-target]' );
        var $placeholder = $( '[data-lpl-source-placeholder-target]' );
        var $help = $( '[data-lpl-source-help-target]' );
        var $or = $( '[data-lpl-source-or]' );

        function select( $tab ) {
            $tabs.attr( 'aria-selected', 'false' );
            $tab.attr( 'aria-selected', 'true' );

            var showUrl = $tab.attr( 'data-lpl-source-show-url' ) === '1';

            $or.addClass( 'lpl-hidden' );
            $urlField.toggleClass( 'lpl-hidden', ! showUrl );

            if ( showUrl ) {
                $urlLabel.text( $tab.attr( 'data-lpl-source-url-label' ) || '' );
                $placeholder.attr( 'placeholder', $tab.attr( 'data-lpl-source-placeholder' ) || '' ).val( '' );
                $help.text( $tab.attr( 'data-lpl-source-help' ) || '' );
            }
        }

        // Media Library isn't a mode to switch into inline (it opens a modal -
        // see initMediaLibraryPicker(), bound separately to the same element),
        // so clicking it never paints it as the selected tab.
        function isSelectable( $tab ) {
            return $tab.attr( 'data-lpl-source-tab' ) !== 'media-library';
        }

        $tabs.on( 'click', function() {
            if ( isSelectable( $( this ) ) ) { select( $( this ) ); }
        } );

        $tabs.on( 'keydown', function( e ) {
            if ( ( e.key === 'Enter' || e.key === ' ' ) && isSelectable( $( this ) ) ) {
                e.preventDefault();
                select( $( this ) );
            }
        } );
    }

    // ── Edit-screen layout picker ────────────────────────────────────────────
    // data-lpl-layout-option="KEY" - single-select via aria-selected, same
    // pattern as the sort menu and source picker tabs above. The Custom
    // Preset add-tile has no data-lpl-layout-option so it's never selectable.
    // Not wired to save (no name= attribute on the tiles themselves - see
    // edit-section-layout-branding.php) but data-lpl-layout-input's hidden
    // _player_layout mirrors the pick so requestFormPreview() can show it.
    //
    // window.LeanPLAdminNewLayout is exposed so custom-preset-builder.js's
    // applyPreset() can drive the exact same path a real tile click does -
    // write straight into [data-lpl-layout-input] and fire requestFormPreview() -
    // instead of maintaining a parallel shadow input. No second input means
    // native form serialization, change listeners, and save/validate code all
    // work for free.
    function initLayoutPicker() {
        var $options = $( '[data-lpl-layout-option]' );
        if ( ! $options.length ) { return; }

        function setLayoutValue( value, $selectedOption ) {
            $options.attr( 'aria-selected', 'false' );
            if ( $selectedOption && $selectedOption.length ) {
                $selectedOption.attr( 'aria-selected', 'true' );
            }
            // .trigger('change') (not just .val()) - custom-preset-builder.js
            // listens for this to clear an applied preset when the user picks
            // a real layout tile directly, same as it does for the classic
            // metabox/Settings radios.
            $( '[data-lpl-layout-input]' ).val( value ).trigger( 'change' );
            requestFormPreview();
        }

        function select( $option ) {
            setLayoutValue( $option.attr( 'data-lpl-layout-option' ), $option );
        }

        $options.on( 'click', function() {
            select( $( this ) );
        } );

        $options.on( 'keydown', function( e ) {
            if ( e.key === 'Enter' || e.key === ' ' ) {
                e.preventDefault();
                select( $( this ) );
            }
        } );

        // Exposed for custom-preset-builder.js: setValue writes a preset id
        // straight into the single real input (no $selectedOption, so all
        // real tiles go aria-selected=false - syncCardLabel() handles the
        // preset tile's own applied look). refreshPreview is a separate
        // entry point for savePreset/deletePreset, which need to nudge the
        // live preview without changing the field's value.
        window.LeanPLAdminNewLayout = {
            setValue: function( value ) {
                setLayoutValue( value, null );
            },
            refreshPreview: function() {
                requestFormPreview();
            }
        };
    }

    // ── Edit-screen toggle switches ──────────────────────────────────────────
    // data-lpl-switch - aria-checked driven, same convention as
    // data-lpl-checkbox (docs/admin-redesign-pencil-porting-process.md step
    // 7). Not called out by name in the plan's interactivity list, but it's
    // the same category of DOM-attribute-only change, and leaving Advanced's
    // Storage toggle inert would be the one dead control on the page.
    function initSwitches() {
        var $switches = $( '[data-lpl-switch]' );
        if ( ! $switches.length ) { return; }

        function toggle( $el ) {
            var next = $el.attr( 'aria-checked' ) !== 'true';
            $el.attr( 'aria-checked', next ? 'true' : 'false' );

            // Write the on/off state into the switch's hidden input (a
            // hidden input always submits, so both 1 and 0 arrive explicitly).
            var target = $el.attr( 'data-lpl-switch-target' );
            if ( target ) {
                $( '[data-lpl-edit-form] input[type="hidden"][name="' + target + '"]' ).val( next ? '1' : '0' );
            }

            requestFormPreview();
        }

        $switches.on( 'click', function() {
            toggle( $( this ) );
        } );

        $switches.on( 'keydown', function( e ) {
            if ( e.key === 'Enter' || e.key === ' ' ) {
                e.preventDefault();
                toggle( $( this ) );
            }
        } );
    }

    // ── Player Accent Color picker ───────────────────────────────────────────
    // Same wp-color-picker widget as lex-settings-new's color field
    // ── Edit-screen skin toggle (playlist) ────────────────────────────────────
    // "Live Preview" label row above the playlist preview has a Light/Dark
    // segmented toggle (data-lpl-skin-toggle) that's a visual shortcut for
    // the Appearance card's _playlist_skin select (default = Light, dark =
    // Dark). Clicking a button writes the matching value into the select
    // and fires requestFormPreview() so the preview re-renders with the
    // chosen skin; the toggle's own active styling is driven entirely by
    // aria-pressed on the option divs, which the admin CSS already styles
    // via the aria-pressed variant patterns used elsewhere in this file.
    //
    // Dark is pro-locked (PLAYLIST-FREE-VS-PRO.md Lock 17): the option div
    // carries data-lpl-skin-toggle-option-locked when free, same as the
    // <option disabled> in the Appearance card's select. Clicking it opens
    // the upgrade modal instead of switching skin.
    function initSkinToggle() {
        var $toggle = $( '[data-lpl-skin-toggle]' );
        if ( ! $toggle.length ) { return; }

        var $options = $toggle.find( '[data-lpl-skin-toggle-option]' );
        var $skinSelect = $( '[data-lpl-edit-form] select[name="_playlist_skin"]' );

        function applyVisualState( value ) {
            $options.each( function() {
                var $opt = $( this );
                var isActive = ( $opt.attr( 'data-lpl-skin-toggle-option' ) === value );
                $opt.attr( 'aria-pressed', isActive ? 'true' : 'false' );
            } );
        }

        // Sync the toggle's initial visual state from the select's current
        // value (the saved value or the default the form rendered with).
        if ( $skinSelect.length ) {
            applyVisualState( $skinSelect.val() || 'default' );

            // Keep the toggle in sync if the Appearance card's select is
            // changed directly (e.g. via the accordion, not the toggle).
            $skinSelect.on( 'change', function() {
                applyVisualState( $( this ).val() || 'default' );
            } );
        } else {
            applyVisualState( 'default' );
        }

        $options.on( 'click', function() {
            if ( $( this ).is( '[data-lpl-skin-toggle-option-locked]' ) ) {
                if ( 'function' === typeof window.openUpgradeModal ) {
                    window.openUpgradeModal();
                }
                return;
            }

            var value = $( this ).attr( 'data-lpl-skin-toggle-option' );
            if ( $skinSelect.length ) {
                $skinSelect.val( value ).trigger( 'change' );
            }
            applyVisualState( value );
            requestFormPreview();
        } );
    }

    // (lex-settings-core.js's initColorPickers) - defaultColor: false is what
    // makes the widget's own "Clear" button reset the input to '' (inherit)
    // instead of snapping back to a default color. Picking/clearing through
    // the widget's UI does NOT bubble a native 'change' up to the form (the
    // "Clear" button in particular only fires wpColorPicker's own clear
    // callback), so live preview is wired directly through the change/clear
    // options instead of relying on initFormLivePreview()'s delegated
    // listener - same reasoning as lex-settings-core.js's colorPickerChange.
    function initColorPicker() {
        var $field = $( '.lex-color-picker' );
        if ( ! $field.length || ! $.fn.wpColorPicker ) { return; }

        $field.wpColorPicker( {
            defaultColor: false,
            disabled: $field.prop( 'disabled' ),
            change: requestFormPreview,
            clear: requestFormPreview
        } );
    }

    $( function() {
        initAddPlaylistModal();
        initDropdowns();
        initCheckboxes();
        initSortMenu();
        initColumnSortToggles();
        initSearch();
        initPager();
        initBulkActions();
        initRowActions();
        initRowClickNavigation();
        initCopyShortcode();
        initTitleField();
        initAccordions();
        initSourceTabs();
        initMediaLibraryPicker();
        initPosterPicker();
        initAddMedia();
        initTrackDrawer();
        initTrackDrawerMediaPickers();
        initTrackDrawerQuickAdd();
        initTrackList();
        initTrackDrawerBulkAdd();
        initSavedSourcePreview();
        initPreviewButton();
        initPublishButton();
        initSaveDraftButton();
        initLayoutPicker();
        initSwitches();
        initColorPicker();
        initSkinToggle();
        initFormLivePreview();

        // Seed the controls from the URL first, then apply the initial
        // filter/page slice so the footer counter and pager match the real row
        // count instead of the unsliced server render.
        restoreStateFromUrl();
        applyRowFilters();

        // Show the message a bulk action parked before its reload.
        flushQueuedToast();
    } );

})(jQuery);
