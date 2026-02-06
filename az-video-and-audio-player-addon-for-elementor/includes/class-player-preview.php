<?php
namespace LeanPL;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Player Preview Class
 * 
 * Handles preview functionality for player posts
 */
class Player_Preview {
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('post_submitbox_misc_actions', [$this, 'render_preview_button']);
        add_filter('the_content', [$this, 'render_preview_content'], 99);
        add_action('wp_head', [$this, 'add_preview_styles']);
        add_filter('post_row_actions', [$this, 'modify_row_actions'], 10, 2);
    }

    /**
     * Render preview button in submit box
     * 
     * @param WP_Post $post Post object
     * @return void
     */
    public function render_preview_button($post) {
        // Only show for lean_player post type
        if ($post->post_type !== 'lean_player') {
            return;
        }

        $is_published = in_array($post->post_status, array('publish', 'draft'));

        $preview_link = get_preview_post_link(
            $post->ID,
            array(
                'preview' => 'true',
                'preview_id' => $post->ID,
                'preview_nonce' => wp_create_nonce('post_preview_' . $post->ID)
            )
        );
        ?>
        <div class="misc-pub-section lpl-preview-metabox">
            <a class="preview button <?php echo $is_published ? '' : 'disabled'; ?>" href="<?php echo esc_url($preview_link); ?>" target="_blank">
                <span class="dashicons dashicons-visibility"></span>
                <?php echo esc_html__('Preview Player', 'vapfem'); ?>
            </a>  

            <?php if(!$is_published): ?>
                <p class="description" style="margin-top: 5px;">
                    <?php echo esc_html__('Publish the player to enable preview.', 'vapfem'); ?>
                </p> 
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render player shortcode in preview mode
     * 
     * @param string $content Post content
     * @return string Modified content with player shortcode
     */
    public function render_preview_content($content) {
        if (
            ! is_preview()
            || get_post_type() !== 'lean_player'
        ) {
            return $content;
        }

        $post_id = get_the_ID();

        // Build shortcode dynamically
        $shortcode = '[lean_player id="' . $post_id . '"]';

        return do_shortcode($shortcode);
    }

    /**
     * Add preview styles to hide header, footer, and sidebar
     * 
     * @return void
     */
    public function add_preview_styles() {
        if (is_preview() && get_post_type() === 'lean_player') {
            echo '<style>
                header, footer, .sidebar { display:none !important; }
            </style>';
        }
    }

    public function modify_row_actions($actions, $post) {
        if ($post->post_type !== 'lean_player') {
            return $actions;
        }

        // Remove "View" action and "Edit" action
        unset($actions['edit']);
        unset($actions['view']);

        // Generate preview link
        $preview_link = get_preview_post_link(
            $post->ID,
            array(
                'preview' => 'true',
                'preview_id' => $post->ID,
                'preview_nonce' => wp_create_nonce('post_preview_' . $post->ID)
            )
        );

        $is_published = in_array($post->post_status, array('publish', 'draft'));

        if($is_published){  
            // Add a link to very first action using array_unshift
            array_unshift($actions, sprintf(
                '<a href="%s" target="_blank" aria-label="%s">%s</a>',
                esc_url($preview_link),
                esc_attr(sprintf(__('Preview Player: %s', 'vapfem'), get_the_title($post->ID))),
                esc_html__('Preview', 'vapfem')
            ));
        }

        return $actions;
    }
}

Player_Preview::get_instance();

