<!-- ============================================
     UPGRADE TO PRO MODAL
     
     Simple & clean modal for promoting PRO features
     
     Header: Title left, Close button right
     Content: Centered paragraph + button
     ============================================ -->

<style>
.lex-upgrade-pro-notice {
    margin-bottom: 20px;
    padding: 12px;
    background: #f0f9ff;
    border-left: 3px solid #492cdd;
    border-radius: 4px;
    font-size: 14px;
}

.lex-upgrade-heading {
    text-align: center;
    font-size: 18px;
    font-weight: 600;
    margin: 20px 0;
}

.lex-upgrade-description {
    text-align: center;
    margin-bottom: 24px;
}

.lex-upgrade-cta-container {
    text-align: center;
    margin: 24px 0;
}

.lex-upgrade-cta-btn {
    display: inline-block;
    padding: 16px 40px;
    font-size: 17px;
    font-weight: bold;
    border: 2px solid #492cdd;
    background: #492cdd;
    color: #fff;
    transition: all 0.2s ease;
    text-decoration: none;
    border-radius: 4px;
}

.lex-upgrade-cta-btn:hover,
.lex-upgrade-cta-btn:focus {
    border-color: #5a3ced;
    background: #5a3ced;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(73, 44, 221, 0.35);
    color: #fff;
}

.lex-upgrade-guarantee {
    margin-top: 8px;
    font-size: 13px;
    opacity: 0.75;
    text-align: center;
}
</style>

<div id="lex-upgrade-modal" class="lex-modal-backdrop" aria-hidden="true">
    <div class="lex-modal" role="dialog" aria-modal="true">
        
        <!-- Header: Title left, Close right -->
        <div class="lex-modal__header">
            <h3><?php echo esc_html__('You\'re one step away', 'lex-settings'); ?> 🚀</h3>
            <button class="lex-modal__close" aria-label="<?php echo esc_attr__('Close', 'lex-settings'); ?>" onclick="closeUpgradeModal()">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>

        <!-- Content: Centered paragraph + button -->
        <div class="lex-modal__content">
            <p class="lex-upgrade-description">
                <?php echo esc_html__('Unlock all premium features, priority support, and regular updates, plus everything we build next.', 'lex-settings'); ?>
            </p>

            <div class="lex-upgrade-cta-container">
                <?php
                // Neutral default; each plugin sets url/target via lex_settings/upgrade_cta,
                // guarded by instance_id. Keeps this core partial plugin-agnostic.
                $cta = apply_filters( 'lex_settings/upgrade_cta', [
                    'url'    => '#',
                    'target' => '_blank',
                    'label'  => __( 'View Pricing & Upgrade', 'lex-settings' ),
                ], $this->settings->getConfig( 'instance_id' ) );

                // _blank without noopener is a security smell; add it automatically.
                $rel = ( '_blank' === ( $cta['target'] ?? '' ) ) ? ' rel="noopener"' : '';
                ?>
                <a href="<?php echo esc_url( $cta['url'] ); ?>"<?php echo ! empty( $cta['target'] ) ? ' target="' . esc_attr( $cta['target'] ) . '"' : ''; ?><?php echo $rel; ?> class="lex-upgrade-cta-btn">
                    <?php echo esc_html( $cta['label'] ); ?>
                </a>
            </div>
            
            <p class="lex-upgrade-guarantee"><?php echo esc_html__('14-day money-back guarantee', 'lex-settings'); ?></p>
        </div>
        
    </div>
</div>







