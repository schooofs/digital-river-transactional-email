<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.digitalriver.com/company/
 * @since      1.0.0
 *
 * @package    Digital_River_Transactional_Email
 * @subpackage Digital_River_Transactional_Email/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Digital_River_Transactional_Email
 * @subpackage Digital_River_Transactional_Email/includes
 * @author     Digital River <schan@digitalriver.com>
 */
class DRTE_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'digital-river-transactional-email',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
