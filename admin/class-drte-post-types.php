<?php
/**
 * Register post types and taxonomies of the plugin.
 *
 * @link       https://www.digitalriver.com
 * @since      1.0.0
 *
 * @package    Digital_River_Transactional_Email
 * @subpackage Digital_River_Transactional_Email/admin
 */

class DRTE_Post_Types {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
	}

	/**
	 * Register post types.
	 *
	 * @since    1.0.0
	 */
	public function register_post_types() {
		register_post_type(
			'dr_client',
			array(
				'labels' => array(
					'name' => __( 'Clients', 'digital-river-global-commerce' ),
					'singular_name' => __( 'Client', 'digital-river-global-commerce' ),
					'all_items' => __( 'Clients', 'digital-river-global-commerce' ),
					'menu_name' => __( 'Digital River', 'digital-river-global-commerce' ),
					'add_new' => __( 'Add New', 'digital-river-global-commerce' ),
					'add_new_item' => __( 'Add new client', 'digital-river-global-commerce' ),
					'edit' => __( 'Edit', 'digital-river-global-commerce' ),
					'edit_item' => __( 'Edit client', 'digital-river-global-commerce' ),
					'new_item' => __( 'New client', 'digital-river-global-commerce' ),
					'view_item' => __( 'View client', 'digital-river-global-commerce' ),
					'view_items' => __( 'View clients', 'digital-river-global-commerce' ),
					'search_items' => __( 'Search clients', 'digital-river-global-commerce' ),
					'not_found' => __( 'No clients found', 'digital-river-global-commerce' ),
					'not_found_in_trash' => __( 'No clients found in trash', 'digital-river-global-commerce' )
				),
				'public' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_nav_menus' => true,
				'show_in_rest' => true,
				'rest_base' => 'dr_clients',
				'menu_position' => 6,
				'publicly_queryable' => false,// true,
				'exclude_from_search' => false,
				'hierarchical' => false,
				'query_var' => true,
				'has_archive' => false,
				'menu_icon' => 'dashicons-store',
				'capability_type' => 'post',
				'map_meta_cap' => true,
				'supports' => array('title'),
				'rewrite' => false
			)
		);
	}
}
