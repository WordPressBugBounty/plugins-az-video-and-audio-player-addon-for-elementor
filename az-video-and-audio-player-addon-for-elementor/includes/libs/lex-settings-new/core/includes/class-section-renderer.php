<?php
namespace Lex\Settings\V2\Services;

/**
 * Section Renderer Service
 * 
 * Handles rendering of settings sections.
 */

use Lex\Settings\V2\Settings;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Section Renderer Service Class
 */
class SectionRenderer {

    /**
     * Settings instance
     *
     * @var Settings
     */
    private $settings;

    /** @var string|null Currently open vtab id */
    private $current_vtab = null;

    /** @var array Vtab metadata collected during tab config include */
    private $vtab_registry = [];
    
    /**
     * Constructor
     * 
     * @param Settings $settings Settings instance
     */
    public function __construct(Settings $settings) {
        $this->settings = $settings;
    }
    
    /**
     * Open a vertical tab block. Sections between startVtab/endVtab auto-inherit membership.
     *
     * @param string $id    Vtab id (used as data-vtab attr value and localStorage key)
     * @param string $label Nav button label
     * @param array  $options icon, order, is_pro, badge, tooltip, condition (all future-safe)
     */
    public function startVtab( $id, $label, $options = [] ) {
        $instance_id = $this->settings->getConfig( 'instance_id' );
        $options = apply_filters( 'lex_settings/vtab_options', $options, $id, $label, $instance_id );
        $options = apply_filters( 'lex_settings/vtab_options/' . $id, $options, $label, $instance_id );

        $this->vtab_registry[ $id ] = [
            'id'      => $id,
            'label'   => $label,
            'icon'    => isset( $options['icon'] ) ? $options['icon'] : '',
            'order'   => isset( $options['order'] ) ? (int) $options['order'] : count( $this->vtab_registry ),
            'tab_layout' => isset( $options['tab_layout'] ) ? $options['tab_layout'] : '',
            'options' => $options,
        ];
        $this->current_vtab = $id;
    }

    /** Close the current vtab block. */
    public function endVtab() {
        $this->current_vtab = null;
    }

    /** Return vtab registry sorted by order. */
    public function getVtabRegistry() {
        $registry = array_values( $this->vtab_registry );
        usort( $registry, function( $a, $b ) { return $a['order'] <=> $b['order']; } );
        return $registry;
    }

    /** Reset vtab state between tabs so vtabs from previous tab config don't leak. */
    public function resetVtabRegistry() {
        $this->vtab_registry = [];
        $this->current_vtab  = null;
    }

    /**
     * Start a settings section
     *
     * @param string $id Section identifier
     * @param string $title Section title
     * @param array $options Section options (is_pro, disable_save_button, collapsed, collapsible, accordion, exclusive, summary_labels)
     * @return void Outputs HTML directly
     */
    public function startSection($id, $title, $options = []) {
        // Allow plugins to filter section options before rendering
        // Hook: lex_settings/section_options (generic) and lex_settings/section_options/{section_id} (specific)
        $instance_id = $this->settings->getConfig('instance_id');
        $options = apply_filters(
            'lex_settings/section_options',
            $options,
            $id,
            $title,
            $instance_id
        );
        // Also provide section-specific hook for granular control
        $options = apply_filters(
            'lex_settings/section_options/' . $id,
            $options,
            $title,
            $instance_id
        );

        $is_pro      = ! empty( $options['is_pro'] );
        $collapsed   = ! empty( $options['collapsed'] );
        $collapsible = $collapsed || ! empty( $options['collapsible'] );
        $accordion   = ! empty( $options['accordion'] );
        $exclusive   = ! empty( $options['exclusive'] ) ? sanitize_html_class( $options['exclusive'] ) : '';
        $no_title    = ! empty( $options['no_title'] );
        $disable_save_button = isset( $options['disable_save_button'] )
            ? $options['disable_save_button']
            : ( $is_pro ? true : null );
        $summary_labels = ! empty( $options['summary_labels'] ) && is_array( $options['summary_labels'] )
            ? $options['summary_labels']
            : [];
        // Generic always-visible meta label beside the section title. Styled
        // exactly like summary_labels, but unlike summary (which only shows when
        // collapsed) this stays visible in both collapsed and expanded states.
        // Multi-purpose: scope hints ("Video & Audio"), status tags, etc.
        $meta = ! empty( $options['meta'] ) ? $options['meta'] : '';

        // If disable_save_button is not specified, auto-sync with is_pro value
        if ( $disable_save_button === null ) {
            $disable_save_button = $is_pro;
        }

        // Auto-inherit vtab from open startVtab() block; explicit override allowed via options
        $vtab = $this->current_vtab;
        if ( isset( $options['vtab'] ) ) {
            $vtab = $options['vtab'];
        }

        // Build section classes
        $section_classes = 'lex-settings-section lex-settings-section--' . esc_attr( $id );
        if ( $is_pro ) {
            $section_classes .= ' lex-settings-section--pro';
        }
        if ( $collapsible ) {
            $section_classes .= ' lex-settings-section--collapsible';
        }
        if ( $collapsed ) {
            $section_classes .= ' lex-settings-section--collapsed';
        }
        if ( $accordion ) {
            $section_classes .= ' lex-settings-section--accordion';
        }
        if ( $vtab ) {
            $section_classes .= ' lex-settings-section--vtab-member';
        }

        $vtab_attr = $vtab ? ' data-vtab="' . esc_attr( $vtab ) . '"' : '';

        // Build collapsible attributes (data-section-id and data-instance-id used by JS for localStorage key)
        $collapsible_attr = $collapsible
            ? ' data-collapsible="true" data-section-id="' . esc_attr( $id ) . '" data-instance-id="' . esc_attr( $instance_id ) . '"'
            : '';
        $exclusive_attr = $exclusive ? ' data-accordion-group="' . esc_attr( $exclusive ) . '"' : '';

        // Build chevron HTML for collapsible sections
        $chevron_html = $collapsible ? '<span class="dashicons dashicons-arrow-down-alt2 lex-settings-section__chevron"></span>' : '';

        // Build PRO badge HTML
        $pro_badge_html = $is_pro ? '<span class="lex-pro-badge lex-pro-badge--medium">PRO</span>' : '';

        // Build save button HTML
        $save_button_html = '';
        if (!$disable_save_button) {
            $save_button_html = sprintf(
                '<button type="button" class="lex-btn lex-btn--primary lex-settings-section__save-btn lex-action-btn lex-btn--gray-v1-b2" data-action="save" data-section="%s" data-loading-type="text-only">%s</button>',
                esc_attr($id),
                esc_html__('Save', 'lex-settings')
            );
        }

        // Build summary HTML
        $summary_html = '';
        if ( ! empty( $summary_labels ) ) {
            $labels_escaped = implode( ', ', array_map( 'esc_html', $summary_labels ) );
            $summary_html   = '<span class="lex-settings-section__summary">' . $labels_escaped . '</span>';
        }

        // Build meta HTML. Always-visible plain-text label beside the title;
        // works for accordion and non-accordion sections alike.
        $meta_html = $meta
            ? '<span class="lex-settings-section__meta">' . esc_html( $meta ) . '</span>'
            : '';

        $title_html = $no_title ? '' : <<<TITLE
            <div class="lex-settings-section__title">
                <span>{$title}</span>
                {$meta_html}
                {$summary_html}
                {$chevron_html}
                {$pro_badge_html}
                {$save_button_html}
            </div>
        TITLE;

        echo <<<HTML
        <div class="{$section_classes}"{$collapsible_attr}{$vtab_attr}{$exclusive_attr}>
            {$title_html}
            <table class="form-table lex-form-table" role="presentation">
        HTML;
    }
    
    /**
     * End a settings section
     * 
     * @return void Outputs HTML directly
     */
    public function endSection() {
        echo <<<HTML
        </table> <!-- End of section table -->
        </div> <!-- End of section div -->
        HTML;
    }
    
    /**
     * Start a PRO grouped section
     * 
     * @param array $options Group options (id, title, padding_bottom)
     * @return void Outputs HTML directly
     */
    public function startProGroup($options = []) {
        $group_id = isset($options['id']) ? $options['id'] : 'pro-group';
        $title = isset($options['title']) ? $options['title'] : 'Premium Features';
        $padding_bottom = isset($options['padding_bottom']) ? $options['padding_bottom'] : false;
        
        $label_id = $group_id . '-label';
        $td_style = $padding_bottom ? '' : ' style="padding-bottom: 0;"';

        $title_escaped = wp_kses_post($title);
        $label_id_escaped = esc_attr($label_id);
        $td_style_escaped = $td_style ? ' style="' . esc_attr($td_style) . '"' : '';

        echo <<<HTML
        <tr>
            <td colspan="2" {$td_style_escaped}>
                <div class="lex-pro-section" role="group" aria-labelledby="{$label_id_escaped}">
                    <div class="lex-pro-section__header" id="{$label_id_escaped}">
                        <span>{$title_escaped}</span>
                        <span class="lex-pro-badge">PRO</span>
                    </div>
                    <table class="lex form-table" style="margin: 0;">
        HTML;
    }

    /**
     * End a PRO grouped section
     * 
     * @return void Outputs HTML directly
     */
    public function endProGroup() {
        echo <<<HTML
                </table>
                </div>
            </td>
        </tr>
        HTML;
    }
    
    /**
     * Render a PRO section with overlay
     * 
     * @param string $section_title Section title
     * @param array $overlay_config Overlay configuration (icon, title, description, button_text)
     * @return void Outputs HTML directly
     */
    public function renderProSectionOverlay($section_title, $overlay_config = []) {
        // Defaults
        $defaults = [
            'icon'        => 'lock',
            'title'       => 'Unlock Premium Features',
            'description' => 'Upgrade to Pro to access this feature.',
            'button_text' => 'Upgrade to Pro →',
            'onclick'     => 'openUpgradeModal(); return false;',
        ];
        
        $config = array_merge($defaults, $overlay_config);
        
        // Escape values for HTML output
        $icon_escaped = esc_attr($config['icon']);
        $title_escaped = wp_kses_post($config['title']);
        $description_escaped = wp_kses_post($config['description']);
        $button_text_escaped = wp_kses_post($config['button_text']);
        $onclick_escaped = esc_attr($config['onclick']);
        $section_title_escaped = wp_kses_post($section_title);
        
        echo <<<HTML
        <div class="lex-settings-section lex-settings-section--pro">
            <div class="lex-settings-section__title">
                <span>{$section_title_escaped}</span>
                <span class="lex-pro-badge">PRO</span>
            </div>
            <div class="lex-settings-section__overlay">
                <div class="lex-settings-section__overlay-content">
                    <span class="dashicons dashicons-{$icon_escaped}"></span>
                    <h3>{$title_escaped}</h3>
                    <p>{$description_escaped}</p>
                    <a href="#" class="lex-btn lex-btn--primary" onclick="{$onclick_escaped}">{$button_text_escaped}</a>
                </div>
            </div>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label>Primary Color</label>
                    </th>
                    <td>
                        <input type="color" value="#2271b1" disabled />
                        <p class="description">Your brand\'s primary color.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label>Secondary Color</label>
                    </th>
                    <td>
                        <input type="color" value="#d63638" disabled />
                        <p class="description">Your brand\'s secondary color.</p>
                    </td>
                </tr>
            </table>
        </div>
        HTML;
    }
    
    /**
     * Render submit buttons
     * 
     * @param array $options Button options
     * @return void Outputs HTML directly
     */
    public function renderSubmitButtons($options = []) {
        $defaults = [
            'save_text' => 'Save Changes',
            'reset_text' => 'Reset Settings',
            'show_reset' => true,
            'show_save' => true,
            'reset_class' => 'lex-btn lex-btn--secondary lex-btn--reset-section lex-action-btn',
            'save_class' => 'lex-btn lex-btn--primary lex-action-btn'
        ];
        
        $args = array_merge($defaults, $options);
        
        // Build reset button HTML
        $reset_button_html = '';
        if ($args['show_reset']) {
            $reset_button_html = sprintf(
                '<span class="lex-tooltip lex-tooltip--compact lex-tooltip--center">
                    <button type="button" class="%s" data-action="reset">
                        <span class="dashicons dashicons-image-rotate"></span>
                        %s
                    </button>
                    <span class="lex-tooltip__text">Reset current section\'s settings</span>
                </span>',
                esc_attr($args['reset_class']),
                esc_html($args['reset_text'])
            );
        }
        
        // Build save button HTML
        $save_button_html = '';
        if ($args['show_save']) {
            $save_button_html = sprintf(
                '<button type="button" class="%s" data-action="save">
                    <span class="dashicons dashicons-yes"></span>
                    %s
                </button>',
                esc_attr($args['save_class']),
                esc_html($args['save_text'])
            );
        }
        
        echo '<p class="submit">';
        echo wp_kses_post($reset_button_html);
        echo wp_kses_post($save_button_html);
        echo '</p> <!-- End of submit p -->';
    }
}

