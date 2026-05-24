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
     * Register custom post types and taxonomies
     */
    public function register_custom_posts() {
        $this->register_player_post_type();
        $this->register_player_category_taxonomy();

        if ( leanpl_get_option( 'playlist.enabled', true ) ) {
            $this->register_playlist_post_type();
        }
    }

    /**
     * Register lean_player custom post type
     */
    private function register_player_post_type() {
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
            'labels'              => $labels,
            'public'              => false,  // Not publicly accessible
            'publicly_queryable'  => true,   // No single pages (true for preview functionality)
            'show_ui'             => true,   // Show in admin
            'show_in_menu'        => false,  // Managed via custom menu
            'query_var'           => false,  // No query vars
            'rewrite'             => false,  // No permalinks
            'capability_type'     => 'post',
            'has_archive'         => false,  // No archive
            'hierarchical'        => false,
            'menu_position'       => null,
            'menu_icon'           => 'dashicons-video-alt3',
            'supports'            => ['title'], // Title only support
            'exclude_from_search' => true,   // Exclude from search results
            'show_in_rest'        => false,  // Disable REST API (also helps disable Gutenberg)
        ];

        register_post_type('lean_player', $args);
    }

    /**
     * Register lean_player_cat taxonomy for organizing players
     */
    private function register_player_category_taxonomy() {
        register_taxonomy('lean_player_cat', 'lean_player', [
            'labels' => [
                'name'                  => esc_html__('Categories', 'vapfem'),
                'singular_name'         => esc_html__('Category', 'vapfem'),
                'menu_name'             => esc_html__('Categories', 'vapfem'),
                'all_items'             => esc_html__('All Categories', 'vapfem'),
                'edit_item'             => esc_html__('Edit Category', 'vapfem'),
                'view_item'             => esc_html__('View Category', 'vapfem'),
                'update_item'           => esc_html__('Update Category', 'vapfem'),
                'add_new_item'          => esc_html__('Add New Category', 'vapfem'),
                'new_item_name'         => esc_html__('New Category Name', 'vapfem'),
                'search_items'          => esc_html__('Search Categories', 'vapfem'),
                'filter_items_list'     => esc_html__('Filter categories list', 'vapfem'),
                'items_list_navigation' => esc_html__('Categories list navigation', 'vapfem'),
                'items_list'            => esc_html__('Categories list', 'vapfem'),
                'not_found'             => esc_html__('No categories found.', 'vapfem'),
            ],
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'hierarchical' => false,
        ]);
    }

    /**
     * Register lean_playlist custom post type
     */
    private function register_playlist_post_type() {
        $labels = [
            'name'                  => esc_html_x('Playlists', 'Post type general name', 'vapfem'),
            'singular_name'         => esc_html_x('Playlist', 'Post type singular name', 'vapfem'),
            'menu_name'             => esc_html_x('Playlists', 'Admin Menu text', 'vapfem'),
            'name_admin_bar'        => esc_html_x('Playlist', 'Add New on Toolbar', 'vapfem'),
            'add_new'               => esc_html__('Add New', 'vapfem'),
            'add_new_item'          => esc_html__('Add New Playlist', 'vapfem'),
            'new_item'              => esc_html__('New Playlist', 'vapfem'),
            'edit_item'             => esc_html__('Edit Playlist', 'vapfem'),
            'view_item'             => esc_html__('View Playlist', 'vapfem'),
            'all_items'             => esc_html__('All Playlists', 'vapfem'),
            'search_items'          => esc_html__('Search Playlists', 'vapfem'),
            'not_found'             => esc_html__('No playlists found.', 'vapfem'),
            'not_found_in_trash'    => esc_html__('No playlists found in Trash.', 'vapfem'),
            'filter_items_list'     => esc_html__('Filter playlists list', 'vapfem'),
            'items_list_navigation' => esc_html__('Playlists list navigation', 'vapfem'),
            'items_list'            => esc_html__('Playlists list', 'vapfem'),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'query_var'           => false,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => null,
            'menu_icon'           => 'dashicons-playlist-video',
            'supports'            => ['title'],
            'exclude_from_search' => true,
            'show_in_rest'        => false,
        ];

        register_post_type('lean_playlist', $args);
    }

    /**
     * Disable Gutenberg editor for our post types
     *
     * @param bool   $use_block_editor Whether to use block editor
     * @param string $post_type        Post type name
     * @return bool
     */
    public function disable_gutenberg($use_block_editor, $post_type) {
        if ( in_array( $post_type, [ 'lean_player', 'lean_playlist' ], true ) ) {
            return false;
        }
        return $use_block_editor;
    }
}

Custom_Posts::get_instance();
