/**
 * Upgrade Modal JavaScript
 * 
 * Handles opening, closing, and interactions for the upgrade modal
 */

(function() {
    'use strict';

    // ============================================
    // MODAL STATE
    // ============================================
    
    let modals = [];
    
    // ============================================
    // INITIALIZE
    // ============================================
    
    function init() {
        // Find all modal backdrops
        const modalElements = document.querySelectorAll('.lex-modal-backdrop');
        
        if (modalElements.length === 0) {
            console.warn('No upgrade modal elements found');
            return;
        }
        
        modalElements.forEach(function(modal) {
            modals.push(modal);
            setupEventListeners(modal);
        });
    }
    
    // ============================================
    // EVENT LISTENERS
    // ============================================
    
    function setupEventListeners(modal) {
        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeUpgradeModal();
            }
        });
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                closeUpgradeModal();
            }
        });
        
        // Prevent modal content clicks from closing
        const modalContent = modal.querySelector('.lex-modal');
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
    
    // ============================================
    // UPGRADE SOURCE LABEL
    // ============================================

    // Which locked feature opened the upgrade modal travels to the CTA link as
    // utm_content, so analytics can tell one lock from another. A capture-phase
    // listener remembers the nearest [data-upgrade-source] of every click; it
    // runs before any inline onclick on the clicked element, so the label is
    // known by the time openUpgradeModal() is called.
    const SOURCE_WINDOW_MS = 1000;
    let lastSource = '';
    let lastSourceAt = 0;

    document.addEventListener('click', function(e) {
        const labelled = e.target.closest ? e.target.closest('[data-upgrade-source]') : null;
        lastSource = labelled ? labelled.getAttribute('data-upgrade-source') : '';
        lastSourceAt = Date.now();
    }, true);

    function slugifySource(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/^_+/, '')
            .replace(/[_.\s]+/g, '-')
            .replace(/[^a-z0-9-]/g, '')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    function resolveSource(explicitSource) {
        let source = explicitSource;
        if (!source && lastSource && Date.now() - lastSourceAt <= SOURCE_WINDOW_MS) {
            source = lastSource;
        }
        return slugifySource(source) || 'other';
    }

    // Rewrite the CTA href from its original value on every open, so labels
    // from earlier opens never stack. Links that aren't http(s) are left alone.
    function tagUpgradeLink(modal, source) {
        const cta = modal.querySelector('.lex-upgrade-cta-btn');
        if (!cta) {
            return;
        }

        if (!cta.hasAttribute('data-base-href')) {
            cta.setAttribute('data-base-href', cta.getAttribute('href') || '');
        }

        let url;
        try {
            url = new URL(cta.getAttribute('data-base-href'), window.location.href);
        } catch (err) {
            return;
        }

        if (url.protocol !== 'http:' && url.protocol !== 'https:') {
            return;
        }

        url.searchParams.set('utm_content', source);
        cta.setAttribute('href', url.toString());
    }

    // ============================================
    // OPEN MODAL
    // ============================================

    window.openUpgradeModal = function(modalId, source) {
        let modal;

        if (modalId) {
            modal = document.getElementById(modalId);
        } else {
            // Find the default modal
            modal = document.getElementById('lex-upgrade-modal') || document.querySelector('.lex-modal-backdrop');
        }

        if (!modal) {
            console.error('Modal not found');
            return;
        }

        // Only the upgrade modal carries a pricing link. Other modals that
        // reuse this opener (e.g. a preset library) are left untouched.
        if (modal.id === 'lex-upgrade-modal') {
            tagUpgradeLink(modal, resolveSource(source));
        }

        // Add open class
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Focus management
        const closeButton = modal.querySelector('[class*="close"]');
        if (closeButton) {
            setTimeout(() => closeButton.focus(), 100);
        }
        
        // Trigger custom event
        document.dispatchEvent(new CustomEvent('lexModalOpened', {
            detail: { modalId: modal.id }
        }));
    };
    
    // ============================================
    // CLOSE MODAL
    // ============================================
    
    window.closeUpgradeModal = function() {
        // Close all open modals
        const openModals = document.querySelectorAll('.lex-modal-backdrop.is-open');
        
        openModals.forEach(function(modal) {
            // Add closing class for animation
            modal.classList.add('is-closing');
            
            // Wait for animation to complete before hiding
            setTimeout(function() {
                modal.classList.remove('is-open');
                modal.classList.remove('is-closing');
                modal.setAttribute('aria-hidden', 'true');
                
                // Trigger custom event
                document.dispatchEvent(new CustomEvent('lexModalClosed', {
                    detail: { modalId: modal.id }
                }));
            }, 200); // Match CSS transition duration
        });
        
        // Restore body scroll after animation
        setTimeout(function() {
            document.body.style.overflow = '';
        }, 200);
    };
    
    // ============================================
    // AUTO-INITIALIZE ON DOM READY
    // ============================================
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();

// ============================================
// HELPER: TRIGGER MODAL FROM PRO FEATURES
// ============================================

/**
 * Automatically trigger modal when clicking on disabled PRO features
 * Usage: Add class 'lex-trigger-upgrade' to any element
 */
document.addEventListener('DOMContentLoaded', function() {
    // Find all PRO sections
    const proSections = document.querySelectorAll('.lex-pro-section, .lex-settings-section--pro, [class*="pro"]');
    
    proSections.forEach(function(section) {
        section.addEventListener('click', function(e) {
            // Check if clicking on disabled input or select
            const target = e.target;
            if ((target.tagName === 'INPUT' || target.tagName === 'SELECT' || target.tagName === 'TEXTAREA') && target.disabled) {
                e.preventDefault();
                openUpgradeModal();
            }
        });
    });
    
    // Manual triggers
    const triggers = document.querySelectorAll('.lex-trigger-upgrade, [data-action="upgrade"]');
    triggers.forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            openUpgradeModal();
        });
    });
    
    // PRO badges as triggers
    const proBadges = document.querySelectorAll('.lex-pro-badge');
    proBadges.forEach(function(badge) {
        badge.style.cursor = 'pointer';
        badge.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openUpgradeModal();
        });
    });
});


