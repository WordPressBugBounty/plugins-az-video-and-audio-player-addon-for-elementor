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
    // OPEN MODAL
    // ============================================
    
    window.openUpgradeModal = function(modalId) {
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


