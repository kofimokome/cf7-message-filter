<?php

namespace km_message_filter;

use KMMenuPage;

class KMCFMessageFilter {
	private static $version;
	private static $skip_validation = false;
	private $blocked;

	public function __construct() {
		// do something here
		self::$version = '1.4.6';
		$this->blocked = get_option( "kmcfmf_messages_blocked_today" );
	}

	/**
	 * @since v1.3.4
	 * Returns the version number of the plugin
	 */
	public static function getVersion() {
		return self::$version;
	}

	/**
	 *
	 * @return KMCFMessageFilter
	 * Returns this instance
	 * @since v1.4.4
	 */
	public static function getInstance() {
		return new static();
	}

	/**
	 * @param null|boolean $value
	 *
	 * @return boolean
	 * Skips the validation
	 * @since v1.4.4
	 */
	public static function skipValidation( $value = null ) {
		if ( ! is_null( $value ) ) {
			self::$skip_validation = $value;
		}

		return self::$skip_validation;
	}

	/**
	 * @since v1.3.4
	 * Starts the plugin
	 */
	public function run() {
		// runs the plugin
		$this->addActions();
		$this->initModules();
		$this->addOptions();
		$this->addMenuPage();
	}

	/**
	 * @since v1.3.4
	 * Adds actions
	 */
	public function addActions() {
		add_action( 'admin_enqueue_scripts', [ $this, 'addAdminScripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'addScripts' ] );
	}

	/**
	 * @since v1.3.4
	 * Initialises class modules
	 */
	public function initModules() {
		foreach ( Module::getModules( KMCF7MS_MODULE_DIR, false ) as $dir ) {
			$module = 'km_message_filter\\' . rtrim( $dir, ".php " );
			new $module();
		}
	}

	/**
	 * Todo: Add Description
	 * @since    1.0.0
	 * @access   public
	 */
	private function addOptions() {

		//
		$reset_message_filter_counter = get_option( 'kmcfmf_message_filter_reset' ) == 'on' ? true : false;

		$option_names = array(
			'kmcfmf_messages_blocked',
			'kmcfmf_last_message_blocked',
			'kmcfmf_message_filter_reset',
			'kmcfmf_date_of_today',
			'kmcfmf_messages_blocked_today',
			'kmcfmf_messages', // todo: used to upgrade from v1.1 to v1.3. Now using kmcfmf_blocked_messages variable
			'kmcfmf_blocked_messages',
			'kmcfmf_weekly_stats',
			'kmcfmf_weekend',
			'kmcfmf_word_stats',
			'kmcfmf_last_cleared_date',
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
		if ( $reset_message_filter_counter || file_get_contents( MessagesModule::getLogFile() ) == '' ) {
			$content = "{}";
			file_put_contents( MessagesModule::getLogFile(), $content );
		}

		if ( $reset_message_filter_counter || get_option( 'kmcfmf_blocked_messages' ) == '' || get_option( 'kmcfmf_blocked_messages' ) == '0' ) {
			$content = "{}";
			update_option( 'kmcfmf_blocked_messages', $content );
		}

		update_option( 'kmcfmf_message_filter_reset', 'off' );
		update_option( 'kmcfmf_weekly_stats', get_option( 'kmcfmf_weekly_stats' ) == '0' ? '[0,0,0,0,0,0,0]' : get_option( 'kmcfmf_weekly_stats' ) );
		update_option( 'kmcfmf_word_stats', get_option( 'kmcfmf_word_stats' ) == '0' ? '[]' : get_option( 'kmcfmf_word_stats' ) );
		update_option( 'kmcfmf_last_cleared_date', get_option( 'kmcfmf_last_cleared_date' ) == '0' ? strtotime( Date( "d F Y" ) ) : get_option( 'kmcfmf_last_cleared_date' ) );

		$date  = get_option( 'kmcfmf_date_of_today' );
		$now   = strtotime( Date( "d F Y" ) );
		$today = date( "N", $now );
		//todo: Check this graph again for it's not working as expected
		if ( (int) get_option( 'kmcfmf_weekend' ) == 0 || (int) get_option( 'kmcfmf_weekend' ) < (int) $now ) {
			$sunday = strtotime( "+" . ( 7 - $today ) . "day" );
			update_option( 'kmcfmf_weekend', $sunday );
			update_option( 'kmcfmf_weekly_stats', '[0,0,0,0,0,0,0]' );
		}
		if ( (int) $date < (int) $now ) {
			$weekly_stats                           = json_decode( get_option( 'kmcfmf_weekly_stats' ) );
			$weekly_stats[ date( 'N', $date ) - 1 ] = get_option( "kmcfmf_messages_blocked_today" );
			update_option( 'kmcfmf_weekly_stats', json_encode( $weekly_stats ) );
			update_option( "kmcfmf_date_of_today", $now );
			update_option( "kmcfmf_messages_blocked_today", 0 );
			update_option( "kmcfmf_emails_blocked_today", 0 );
		}

		if ( get_option( 'kmcfmf_message_storage_toggle' ) === 'on' ) {
			// die('we die here');
			update_option( 'kmcfmf_message_auto_delete_toggle', 'on' );
		}
	}

	/**
	 * @since v1.3.4
	 * Adds the admin menu page
	 */
	public function addMenuPage() {
		$menu_title = 'CF7 Form Filter';
		if ( $this->blocked > 0 ) {
			$menu_title .= " <span class='update-plugins count-1'><span class='update-count'>$this->blocked </span></span>";
		}

		$menu_page      = new KMMenuPage( array(
			'page_title' => 'CF7 Form Filter',
			'menu_title' => $menu_title,
			'capability' => 'read',
			'menu_slug'  => 'kmcf7-message-filter',
			'icon_url'   => 'dashicons-filter',
			'position'   => 100,
			'function'   => null
		) );
		$sub_menu_pages = apply_filters( 'kmcf7_sub_menu_pages_filter', array() );
		foreach ( $sub_menu_pages as $sub_menu_page ) {
			$menu_page->add_sub_menu_page( $sub_menu_page );
		}
		$menu_page->run();
	}

	/**
	 * @since v1.3.4
	 * Adds stylesheets and scripts on the client side
	 */
	public function addScripts() {
	}

	/**
	 * @since v1.3.4
	 * Adds stylesheets and scripts on the admin side
	 */
	public function addAdminScripts( $hook ) {

		global $wp;
		$url = add_query_arg( array( $_GET ), $wp->request );
		$url = substr( $url, 0, 29 );
		// echo "<script> alert('$url');</script>";
		//wp_enqueue_style( 'style-name', get_stylesheet_uri() );
		wp_enqueue_script( 'selectize', plugins_url( 'assets/js/selectize.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '0.12.4', true );
		wp_enqueue_style( 'selectize', plugins_url( '/assets/css/selectize.default.css', dirname( __FILE__ ) ), '', '0.12.4' );

		wp_enqueue_script( 'swal', plugins_url( 'assets/js/swal.min.js', dirname( __FILE__ ) ), array(), '11.7.1', true );
		wp_enqueue_style( 'swal', plugins_url( '/assets/css/swal.min.css', dirname( __FILE__ ) ), '', '11.7.1' );

		wp_enqueue_style( 'cf7-message-filter', plugins_url( '/assets/css/styles.css', dirname( __FILE__ ) ), '', '1.4.0' );

		if ( $hook == 'toplevel_page_kmcf7-message-filter' || $url == '?page=kmcf7-filtered-messages' ) {

			wp_enqueue_script( 'vendor', plugins_url( 'assets/js/vendor.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'moment', plugins_url( 'assets/libs/moment/moment.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'apex', plugins_url( 'assets/libs/apexcharts/apexcharts.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', false );
			wp_enqueue_script( 'flat', plugins_url( 'assets/libs/flatpickr/flatpickr.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'dash', plugins_url( 'assets/js/pages/dashboard.init.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'app', plugins_url( 'assets/js/app.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0.0', true );
			wp_enqueue_script( 'bootstrap', plugins_url( 'assets/js/bootstrap.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '4.3.1', false );
			wp_enqueue_script( 'datatable', plugins_url( 'assets/libs/datatables/datatables.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.12.1', false );
			wp_enqueue_script( 'datatable-select', plugins_url( 'assets/libs/datatables/select.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.4.0', false );
			wp_enqueue_script( 'datatable-buttons', plugins_url( 'assets/libs/datatables/buttons.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '2.2.3', false );
			wp_enqueue_script( 'datatable-pdfmake', plugins_url( 'assets/libs/datatables/pdfmake.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '0.1.53', false );
			wp_enqueue_script( 'datatable-buttons-html5', plugins_url( 'assets/libs/datatables/buttons.html5.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '2.2.3', false );
			wp_enqueue_script( 'datatable-jszip', plugins_url( 'assets/libs/datatables/jszio.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '3.1.3', false );
			wp_enqueue_script( 'datatable-colvis', plugins_url( 'assets/libs/datatables/colvis.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '2.3.2', false );


			wp_enqueue_style( 'datatable', plugins_url( '/assets/libs/datatables/datatables.min.css', dirname( __FILE__ ) ), '', '1.12.1' );
			wp_enqueue_style( 'datatable-select', plugins_url( '/assets/libs/datatables/select.min.css', dirname( __FILE__ ) ), '', '1.4.0' );
			wp_enqueue_style( 'datatable-buttons', plugins_url( '/assets/libs/datatables/buttons.min.css', dirname( __FILE__ ) ), '', '2.2.3' );
			wp_enqueue_style( 'bootstrap', plugins_url( '/assets/css/bootstrap.min.css', dirname( __FILE__ ) ), '', '4.3.1' );
			wp_enqueue_style( 'app', plugins_url( '/assets/css/app.min.css', dirname( __FILE__ ) ), '', '4.3.1' );
			wp_enqueue_style( 'icons', plugins_url( '/assets/css/icons.min.css', dirname( __FILE__ ) ), '', '4.3.1' );
		}
	}


}