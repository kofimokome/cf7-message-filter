<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       www.kofimokome.ml
 * @since      1.0.0
 *
 * @package    Contact_form_message_filter
 * @subpackage Contact_form_message_filter/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Contact_form_message_filter
 * @subpackage Contact_form_message_filter/includes
 * @author     Kofi Mokome <kofimokome10@gmail.com>
 */
class Contact_form_message_filter_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'contact_form_message_filter',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
