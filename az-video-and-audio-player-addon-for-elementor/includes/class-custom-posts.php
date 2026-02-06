<?php
namespace LeanPL;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Custom_Posts {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('init', [$this, 'register_custom_posts']);
        add_filter('use_block_editor_for_post_type', [$this, 'disable_gutenberg'], 10, 2);
    }

    /**
     * Register custom post type for players
     */
    public function register_custom_posts() {
        $labels = [
            'name'                  => esc_html_x('Lean Player', 'Post type general name', 'vapfem'),
            'singular_name'         => esc_html_x('Lean Player', 'Post type singular name', 'vapfem'),
            'menu_name'             => esc_html_x('Lean Player', 'Admin Menu text', 'vapfem'),
            'name_admin_bar'        => esc_html_x('Player', 'Add New on Toolbar', 'vapfem'),
            'add_new'               => esc_html__('Add New', 'vapfem'),
            'add_new_item'          => esc_html__('Add New Player', 'vapfem'),
            'new_item'              => esc_html__('New Player', 'vapfem'),
            'edit_item'             => esc_html__('Edit Player', 'vapfem'),
            'view_item'             => esc_html__('View Player', 'vapfem'),
            'all_items'             => esc_html__('All Players', 'vapfem'),
            'search_items'          => esc_html__('Search Players', 'vapfem'),
            'parent_item_colon'     => esc_html__('Parent Players:', 'vapfem'),
            'not_found'             => esc_html__('No players found.', 'vapfem'),
            'not_found_in_trash'    => esc_html__('No players found in Trash.', 'vapfem'),
            'featured_image'        => esc_html__('Player Featured Image', 'vapfem'),
            'set_featured_image'    => esc_html__('Set featured image', 'vapfem'),
            'remove_featured_image' => esc_html__('Remove featured image', 'vapfem'),
            'use_featured_image'    => esc_html__('Use as featured image', 'vapfem'),
            'archives'              => esc_html__('Player archives', 'vapfem'),
            'insert_into_item'      => esc_html__('Insert into player', 'vapfem'),
            'uploaded_to_this_item' => esc_html__('Uploaded to this player', 'vapfem'),
            'filter_items_list'     => esc_html__('Filter players list', 'vapfem'),
            'items_list_navigation' => esc_html__('Players list navigation', 'vapfem'),
            'items_list'            => esc_html__('Players list', 'vapfem'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false, // Not publicly accessible
            'publicly_queryable' => true, // No single pages (true for preview functionality)
            'show_ui'            => true, // Show in admin
            'show_in_menu'       => false, // Show in admin menu
            'query_var'          => false, // No query vars
            'rewrite'            => false, // No permalinks
            'capability_type'    => 'post',
            'has_archive'        => false, // No archive
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'           => 'dashicons-video-alt3',
            'supports'           => ['title'], // Title only support
            'exclude_from_search' => true, // Exclude from search results
            'show_in_rest'       => false, // Disable REST API (also helps disable Gutenberg)
        ];

        register_post_type('lean_player', $args);
    }

    /**
     * Disable Gutenberg editor for player post type
     *
     * @param bool   $use_block_editor Whether to use block editor
     * @param string $post_type        Post type name
     * @return bool
     */
    public function disable_gutenberg($use_block_editor, $post_type) {
        if ('lean_player' === $post_type) {
            return false;
        }
        return $use_block_editor;
    }
}

Custom_Posts::get_instance();