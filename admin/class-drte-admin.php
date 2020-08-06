<?php
/**
 * Admin-specific functionality
 *
 * @link       https://www.digitalriver.com
 * @since      1.0.0
 *
 * @package    Digital_River_Global_Commerce
 * @subpackage Digital_River_Global_Commerce/admin
 */

class DRTE_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $drte
	 */
	private $drte;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version
	 */
	private $version;

	/**
	 * The plugin name
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string     $plugin_name
	 */
	private $plugin_name = 'digital-river-transactional-email';

	/**
	 * The option name to be used in this plugin
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string     $option_name
	 */
	private $option_name = 'drte';

	/**
	 * Cordial API key
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $drte_cordial_api_key;

	/**
	 * Cordial template key
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $drte_cordial_email_template_key_prefix;

	/**
	 * Digital River Webhook endpoint namespace
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $drte_endpoint_namespace;

	/**
	 * Digital River Webhook endpoint base
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $drte_endpoint_restbase;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $drte
	 * @param      string    $version
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->drte_cordial_api_key = get_option( 'drte_cordial_api_key' );
		$this->drte_cordial_email_template_key_prefix = get_option( 'drte_cordial_email_template_key_prefix' );
		$this->drte_endpoint_namespace = get_option( 'drte_endpoint_namespace' );
		$this->drte_endpoint_restbase = get_option( 'drte_endpoint_restbase' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->drte, DRTE_PLUGIN_URL . 'assets/css/drte-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( $this->drte, DRTE_PLUGIN_URL . 'assets/js/drte-admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-progressbar' ), $this->version, false );

		// transfer drte options from PHP to JS
		wp_localize_script( $this->drte, 'drte_admin_params',
			array(
				'endpoint_namespace' 			=> $this->drte_endpoint_namespace,
				'endpoint_restbase'		  	=> $this->drte_endpoint_restbase,
				'api_key'               	=> $this->drte_cordial_api_key,
				'template_key'			    	=> $this->drte_cordial_email_template_key_prefix
			)
		);
	}

	/**
	 * Add settings menu and link it to settings page.
	 *
	 * @since    1.0.0
	 */
	public function add_settings_page() {
    add_options_page(
        __( 'Digital River Transactional Email Settings', 'textdomain' ),
        __( 'Digital River', 'textdomain' ),
        'manage_options',
        'digital-river-transactional-email',
        array(
            $this,
            'display_settings_page'
        )
    );
	}

	/**
	 * Render settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		// Double check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include_once 'partials/drte-admin-display.php';
	}

	/**
	 * Register settings fields.
	 *
	 * @since    1.0.0
	 */
	public function register_settings_fields() {

		add_settings_section(
			$this->option_name . '_general',
			'',
			array( $this, $this->option_name . '_general_cb' ),
			$this->plugin_name
		);

		add_settings_field(
			$this->option_name . '_endpoint_namespace',
			__( 'Webhook Endpoint Namespace', 'digital-river-global-commerce' ),
			array( $this, $this->option_name . '_endpoint_namespace_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_endpoint_namespace' )
		);

		add_settings_field(
			$this->option_name . '_endpoint_restbase',
			__( 'Webhook Endpoint Base', 'digital-river-global-commerce' ),
			array( $this, $this->option_name . '_endpoint_restbase_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_endpoint_restbase' )
		);

		add_settings_field(
			$this->option_name . '_cordial_api_key',
			__( 'Cordial API Key', 'digital-river-global-commerce' ),
			array( $this, $this->option_name . '_cordial_api_key_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_cordial_api_key' )
		);

		add_settings_field(
			$this->option_name . '_cordial_email_template_key_prefix',
			__( 'Cordial Email Template Key Prefix', 'digital-river-global-commerce' ),
			array( $this, $this->option_name . '_cordial_email_template_key_prefix_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_cordial_email_template_key_prefix' )
		);

		register_setting( $this->plugin_name, $this->option_name . '_endpoint_namespace', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( $this->plugin_name, $this->option_name . '_endpoint_restbase', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( $this->plugin_name, $this->option_name . '_cordial_email_template_key_prefix', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( $this->plugin_name, $this->option_name . '_cordial_api_key', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ) );
	}

	/**
	 * Render the text for the general section.
	 *
	 * @since  1.0.0
	 */
	public function drte_general_cb() {
		return; // No need to print section message
	}

	/**
	 * Render input text field for Cordial API Key.
	 *
	 * @since    1.0.0
	 */
	public function drte_cordial_api_key_cb() {
		$key = get_option( $this->option_name . '_cordial_api_key' );
		echo '<input type="text" class="regular-text" name="' . $this->option_name . '_cordial_api_key' . '" id="' . $this->option_name . '_cordial_api_key' . '" value="' . $key . '">';
	}
	/**
	 * Render input text field for Site ID.
	 *
	 * @since    1.0.0
	 */
	public function drte_cordial_email_template_key_prefix_cb() {
		$key = get_option( $this->option_name . '_cordial_email_template_key_prefix' );
		echo '<input type="text" class="regular-text" name="' . $this->option_name . '_cordial_email_template_key_prefix' . '" id="' . $this->option_name . '_cordial_email_template_key_prefix' . '" value="' . $key . '"> ';
	}
	/**
	 * Render input text field for Webhook endpoint namespace.
	 *
	 * @since    1.0.0
	 */
	public function drte_endpoint_namespace_cb() {
		$key = get_option( $this->option_name . '_endpoint_namespace' );
		echo '<input type="text" class="regular-text" name="' . $this->option_name . '_endpoint_namespace' . '" id="' . $this->option_name . '_endpoint_namespace' . '" value="' . $key . '"> ';
	}
	/**
	 * Render input text field for Webhook endpoint base.
	 *
	 * @since    1.0.0
	 */
	public function drte_endpoint_restbase_cb() {
		$key = get_option( $this->option_name . '_endpoint_restbase' );
		echo '<input type="text" class="regular-text" name="' . $this->option_name . '_endpoint_restbase' . '" id="' . $this->option_name . '_endpoint_restbase' . '" value="' . $key . '"> ';
	}
}
