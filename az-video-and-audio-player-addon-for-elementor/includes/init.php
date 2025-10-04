<?php
namespace VAPFEM;
if (!defined('ABSPATH')) {
    exit;
}

use \Elementor\Plugin as Plugin;


class Elementor_Init {

	const VERSION = "2.1.2";
	const MINIMUM_ELEMENTOR_VERSION = "2.0.0";
	const MINIMUM_PHP_VERSION = "5.6";

	private static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;

	}

	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init' ] );

		// Load shortcodes regardless of Elementor
		$this->init_shortcodes();

		// Load admin menu
		$this->init_admin_menu();
	}

	public function init() {

		// Check if Elementor installed and activated (for widget functionality only)
		if ( ! did_action( 'elementor/loaded' ) ) {
			// Elementor not available - widgets won't work but shortcodes will
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );

			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
		}

		// load text domain
		load_plugin_textdomain( 'vapfem', false, VAPFEM_DIR . '/languages/' );

		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );

		add_action( "elementor/frontend/after_enqueue_styles", [ $this, 'widget_styles' ] );

		// Elementor dashboard panel style
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'editor_scripts' ] );

	}

	// widget styles
	function widget_styles() {
		wp_enqueue_style( "plyr", VAPFEM_URI . '/assets/css/plyr.css' );
		wp_enqueue_style( "vapfem-main", VAPFEM_URI . '/assets/css/main.css' );
	}


	function editor_scripts() {
		wp_enqueue_style( "vapfem-editor", VAPFEM_URI . '/assets/css/editor.css' );
	}

	// initialize widgets
	public function init_widgets() {
		require_once( VAPFEM_DIR . '/includes/widgets/video-player.php' );
		require_once( VAPFEM_DIR . '/includes/widgets/audio-player.php' );

		// Register widget
		Plugin::instance()->widgets_manager->register_widget_type( new \VAPFEM_Video_Player() );
		Plugin::instance()->widgets_manager->register_widget_type( new \VAPFEM_Audio_Player() );
	}


	public function admin_notice_minimum_php_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'vapfem' ),
			'<strong>' . esc_html__( 'Video & Audio Player', 'vapfem' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'vapfem' ) . '</strong>',
			self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	public function admin_notice_minimum_elementor_version() {
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
		/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'vapfem' ),
			'<strong>' . esc_html__( 'Video & Audio Player', 'vapfem' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'vapfem' ) . '</strong>',
			self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}


	/**
	 * Initialize shortcodes (independent of Elementor)
	 */
	public function init_shortcodes() {
		require_once( VAPFEM_DIR . '/includes/functions.php' );

		// Load assets manager first
		require_once( VAPFEM_DIR . '/includes/class-assets-manager.php' );

		// Load shared renderer
		require_once( VAPFEM_DIR . '/includes/class-player-renderer.php' );

		// Load shortcodes
		require_once( VAPFEM_DIR . '/includes/class-video-shortcode.php' );
		require_once( VAPFEM_DIR . '/includes/class-audio-shortcode.php' );
	}

	/**
	 * Initialize admin menu
	 */
	public function init_admin_menu() {
		if ( is_admin() ) {
			require_once( VAPFEM_DIR . '/includes/class-menu.php' );
			require_once( VAPFEM_DIR . '/includes/class-settings-page.php' );
		}
	}

}

Elementor_Init::instance();