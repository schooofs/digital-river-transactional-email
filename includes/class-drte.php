<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.digitalriver.com/company/
 * @since      1.0.0
 *
 * @package    Digital_River_Transactional_Email
 * @subpackage Digital_River_Transactional_Email/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Digital_River_Transactional_Email
 * @subpackage Digital_River_Transactional_Email/includes
 * @author     Digital River <schan@digitalriver.com>
 */
class DRTE {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      DRTE_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'DRTE_VERSION' ) ) {
			$this->version = DRTE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'digital-river-transactional-email';

		$this->load_dependencies();
		//$this->start_api_handler();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - DRTE_Loader. Orchestrates the hooks of the plugin.
	 * - DRTE_i18n. Defines internationalization functionality.
	 * - DRTE_Admin. Defines all hooks for the admin area.
	 * - DRTE_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-drte-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-drte-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-drte-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-drte-post-types.php';

		/**
		 * Load ACF blocks functions.
		 */

		 define( 'MF_ACF_PATH', plugin_dir_path( dirname( __FILE__ ) ) . '/includes/acf/' );
		 define( 'MF_ACF_URL', plugin_dir_url( dirname( __FILE__ ) ) . '/includes/acf/' );
		 // Include the ACF plugin.
		 include_once( MF_ACF_PATH . 'acf.php' );

		 // Customize the url setting to fix incorrect asset URLs.
		 add_filter('acf/settings/url', 'mf_acf_settings_url');
		 function mf_acf_settings_url( $url ) {
		     return MF_ACF_URL;
		 }

		 // (Optional) Hide the ACF admin menu item.
		 add_filter('acf/settings/show_admin', 'mf_acf_settings_show_admin');
		 function mf_acf_settings_show_admin( $show_admin ) {
		     return false;
		 }


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-drte-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-drte-cordial.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-drte-rest-controller.php';

		require_once  plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-drte-rest-controller.php' ;

		$this->loader = new DRTE_Loader();


	}



	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the DRTE_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new DRTE_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new DRTE_Admin( $this->get_plugin_name(), $this->get_version() );

		new DRTE_Post_Types();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_settings_fields' );

		$this->loader->add_action( 'acf/init', $plugin_admin, 'register_acf_field_groups');
		//$this->loader->add_action( 'acf/save_post', $plugin_admin, 'drte_acf_save_postss' );
		//$this->loader->add_action( 'save_post', $plugin_admin, 'drte_save_post' );
		$this->loader->add_filter( 'acf/save_post', $plugin_admin, 'drte_acf_save_post', 99, 2 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new DRTE_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'drte_register_endpoints' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    DRTE_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
