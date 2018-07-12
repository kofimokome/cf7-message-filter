<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.kofimokome.ml
 * @since      1.0.0
 *
 * @package    Contact_form_message_filter
 * @subpackage Contact_form_message_filter/includes
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
 * @package    Contact_form_message_filter
 * @subpackage Contact_form_message_filter/includes
 * @author     Kofi Mokome <kofimokome10@gmail.com>
 */
class Contact_form_message_filter {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Contact_form_message_filter_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
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
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'contact_form_message_filter';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Contact_form_message_filter_Loader. Orchestrates the hooks of the plugin.
	 * - Contact_form_message_filter_i18n. Defines internationalization functionality.
	 * - Contact_form_message_filter_Admin. Defines all hooks for the admin area.
	 * - Contact_form_message_filter_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-contact_form_message_filter-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-contact_form_message_filter-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-contact_form_message_filter-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-contact_form_message_filter-public.php';

		$this->loader = new Contact_form_message_filter_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Contact_form_message_filter_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Contact_form_message_filter_i18n();

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

		//
		$enable_message_filter = get_option( 'kmcfmf_message_filter_toggle' ) == 'on' ? true : false;
		$enable_email_filter = get_option( 'kmcfmf_email_filter_toggle' ) == 'on' ? true : false;
		$reset_message_filter_counter = get_option( 'kmcfmf_message_filter_reset' ) == 'on' ? true : false;

		$option_names = array(
			'kmcfmf_messages_blocked',
			'kmcfmf_emails_blocked',
			'kmcfmf_last_email_blocked',
			'kmcfmf_last_message_blocked',
			'kmcfmf_message_filter_reset'
		);

		foreach ( $option_names as $option_name ) {
			if ( get_option( $option_name ) == false ) {
				// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
				$deprecated = null;
				$autoload   = 'no';
				add_option( $option_name, 0, $deprecated, $autoload );
			}

			if ( $reset_message_filter_counter ) {
				update_option( $option_name, 0 );
			}

		}

		$plugin_admin = new Contact_form_message_filter_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'kmcfmf_add_main_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'kmcfmf_add_options_submenu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'kmcfmf_register_settings_init' );

		if ( $enable_message_filter ) {
			$this->loader->add_filter( 'wpcf7_validate_textarea', $plugin_admin, 'kmcfmf_textarea_validation_filter', 12, 2 );
			$this->loader->add_filter( 'wpcf7_validate_textarea*', $plugin_admin, 'kmcfmf_textarea_validation_filter', 12, 2 );
		}
		if ( $enable_email_filter ) {
			$this->loader->add_filter( 'wpcf7_validate_email', $plugin_admin, 'kmcfmf_text_validation_filter', 12, 2 );
			$this->loader->add_filter( 'wpcf7_validate_email*', $plugin_admin, 'kmcfmf_text_validation_filter', 12, 2 );
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Contact_form_message_filter_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

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
	 * @return    Contact_form_message_filter_Loader    Orchestrates the hooks of the plugin.
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
