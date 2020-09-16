<?php
/**
 * Admin-specific functionality
 *
 * @link       https://www.digitalriver.com
 * @since      1.0.0
 *
 * @package    Digital_River_Transactional_Email
 * @subpackage Digital_River_Transactional_Email/admin
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
		$this->drte_endpoint_namespace = get_option( 'drte_endpoint_namespace' );
		$this->drte_endpoint_restbase = get_option( 'drte_endpoint_restbase' );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, DRTE_PLUGIN_URL . 'admin/css/drte-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
	//	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		$suffix = "";
		wp_enqueue_script( $this->plugin_name, DRTE_PLUGIN_URL . 'admin/js/drte-admin' . $suffix . '.js', array( 'jquery', 'jquery-ui-progressbar' ), $this->version, false );

		// transfer drte options from PHP to JS
		wp_localize_script( $this->plugin_name, 'drte_admin_params',
			array(
				'endpoint_namespace' 			=> $this->drte_endpoint_namespace,
				'endpoint_restbase'		  	=> $this->drte_endpoint_restbase
			)
		);

		if ( 'dr_client' == get_post_type() ) {
			wp_dequeue_script( 'autosave' );
		}
	}

	/**
	 * Add channel/clientName to options.
	 *
	 * @since    1.0.0
	 */
	function drte_acf_save_post( $post_id ) {

		$channel = $_POST['acf']['field_5f48aa65a5837'];

		if ( get_option( $channel ) !== false ) {
		    update_option( $channel, $post_id );
		} else {
		    add_option( $channel, $post_id, null, "no" );
		}

	}
	function drte_save_post($post_id) {
	    // If this is a revision, get real post ID
	    if ( $parent_id = wp_is_post_revision( $post_id ) )
	        $post_id = $parent_id;
	}

	/**
	 * Add settings menu and link it to settings page.
	 *
	 * @since    1.0.0
	 */
	public function register_acf_field_groups() {
		if( function_exists('acf_add_local_field_group') ):

			acf_add_local_field_group(array(
				'key' => 'group_5f44d25bd6296',
				'title' => 'Client Detail',
				'fields' => array(
					array(
						'key' => 'field_5f48aa65a5837',
						'label' => 'Client Name',
						'name' => 'client_name',
						'type' => 'text',
						'instructions' => 'Client Name must match DR Webhook json file brand value under metadata.',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5f44d386b17b3',
						'label' => 'Cordial API Key',
						'name' => 'cordial_api_key',
						'type' => 'text',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => '',
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'maxlength' => '',
					),
					array(
						'key' => 'field_5f44d52bb17b9',
						'label' => 'Cordial Email Message Keys',
						'name' => 'cordial_email_message_keys',
						'type' => 'group',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array(
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'layout' => 'row',
						'sub_fields' => array(
							array(
								'key' => 'field_5f44d554b17ba',
								'label' => 'Order Confirmation',
								'name' => 'order_confirmation',
								'type' => 'text',
								'instructions' => '',
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => 'dr-webhook-transitional-email-order-created',
								'placeholder' => 'dr-webhook-transitional-email-order-created',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
							),
							array(
								'key' => 'field_5f44d5a7b17bb',
								'label' => 'Order Refunded',
								'name' => 'order_refunded',
								'type' => 'text',
								'instructions' => '',
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => 'dr-webhook-transitional-email-order-refunded',
								'placeholder' => 'dr-webhook-transitional-email-order-refunded',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
							),
							array(
								'key' => 'field_5f44d5f4b17bc',
								'label' => 'Order Shipped',
								'name' => 'order_shipped',
								'type' => 'text',
								'instructions' => '',
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => 'dr-webhook-transitional-email-fulfillment-created-shipped',
								'placeholder' => 'dr-webhook-transitional-email-fulfillment-created-shipped',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
							),
							array(
								'key' => 'field_5f44d607b17bd',
								'label' => 'Order Cancelled',
								'name' => 'order_cancelled',
								'type' => 'text',
								'instructions' => '',
								'required' => 1,
								'conditional_logic' => 0,
								'wrapper' => array(
									'width' => '',
									'class' => '',
									'id' => '',
								),
								'default_value' => 'dr-webhook-transitional-email-fulfillment-created-canceled',
								'placeholder' => 'dr-webhook-transitional-email-fulfillment-created-canceled',
								'prepend' => '',
								'append' => '',
								'maxlength' => '',
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'dr_client',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'acf_after_title',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'field',
				'hide_on_screen' => '',
				'active' => true,
				'description' => '',
			));

			endif;
	}
	/**
	 * Add settings menu and link it to settings page.
	 *
	 * @since    1.0.0
	 */
	public function add_settings_page() {
		add_submenu_page(
		    'edit.php?post_type=dr_client',
		    __( 'Digital River Webhook Endpoint Setting', 'menu-test' ),
		    __( 'Setting', 'menu-test' ),
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
			__( 'Endpoint Namespace', 'digital-river-global-commerce' ),
			array( $this, $this->option_name . '_endpoint_namespace_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_endpoint_namespace' )
		);

		add_settings_field(
			$this->option_name . '_endpoint_restbase',
			__( 'Endpoint Base', 'digital-river-global-commerce' ),
			array( $this, $this->option_name . '_endpoint_restbase_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_endpoint_restbase' )
		);
		add_settings_field(
			$this->option_name . '_complete_endpoint',
			__( 'Endpoint Output', 'digital-river-global-commerce' ),
			array( $this, $this->option_name . '_complete_endpoint_cb' ),
			$this->plugin_name,
			$this->option_name . '_general',
			array( 'label_for' => $this->option_name . '_complete_endpoint' )
		);
		register_setting( $this->plugin_name, $this->option_name . '_endpoint_namespace', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( $this->plugin_name, $this->option_name . '_endpoint_restbase', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( $this->plugin_name, $this->option_name . '_complete_endpoint', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ) );
	}

	/**
	 * Render the text for the general section.
	 *
	 * @since  1.0.0
	 */
	public function drte_general_cb() {
		echo 'Register Endpoint for Digital River Transitional Email Webhook';
	}

	/**
	 * Render input text field for Webhook endpoint namespace.
	 *
	 * @since    1.0.0
	 */
	public function drte_endpoint_namespace_cb() {
		$key = (get_option( $this->option_name . '_endpoint_namespace' ) === null) ? "drwebhook/v1" : get_option( $this->option_name . '_endpoint_namespace' );
		echo '<input type="text" class="regular-text" name="' . $this->option_name . '_endpoint_namespace' . '" id="' . $this->option_name . '_endpoint_namespace' . '" value="' . $key . '"> ';
	}
	/**
	 * Render input text field for Webhook endpoint base.
	 *
	 * @since    1.0.0
	 */
	public function drte_endpoint_restbase_cb() {
		$key = (get_option( $this->option_name . '_endpoint_restbase' ) === null) ? "confirmation" : get_option( $this->option_name . '_endpoint_restbase' );
		echo '<input type="text" class="regular-text" name="' . $this->option_name . '_endpoint_restbase' . '" id="' . $this->option_name . '_endpoint_restbase' . '" value="' . $key . '"> ';
	}
	/**
	 * Render input text field for Webhook endpoint base.
	 *
	 * @since    1.0.0
	 */
	public function drte_complete_endpoint_cb() {
		$restbase = (get_option( $this->option_name . '_endpoint_restbase' ) === null) ? "confirmation" : get_option( $this->option_name . '_endpoint_restbase' );
		$namespace = (get_option( $this->option_name . '_endpoint_namespace' ) === null) ? "drwebhook/v1" : get_option( $this->option_name . '_endpoint_namespace' );
		echo '<div><span>'.home_url().'/</span><span class="output_namespace">'.$namespace.'</span>/<span class="output_restbase">'.$restbase.'</span></div>';
	}
}
