<?php

namespace km_message_filter;

use KMSubMenuPage;

class DashboardModule extends Module {
	private $blocked;

	public function __construct() {
		parent::__construct();
		$this->blocked = get_option( "kmcfmf_messages_blocked_today" );

//		$this->module = 'packages';
	}

	/**
	 * @since v1.3.4
	 * Adds settings submenu page
	 */
	function addSubMenuPage( $sub_menu_pages ) {
		$menu_title = 'CF7 Form Filter';
		if ( $this->blocked > 0 ) {
			$menu_title .= " <span class='update-plugins count-1'><span class='update-count'>$this->blocked </span></span>";
		}

		$dashboard_page = new KMSubMenuPage(
			array(
				'page_title' => $menu_title,
				'menu_title' => $menu_title,
				'capability' => 'manage_options',
				'menu_slug'  => 'kmcf7-message-filter',
				'function'   => array(
					$this,
					'dashboardPageContent'
				)
			) );

		array_push( $sub_menu_pages, $dashboard_page );

		return $sub_menu_pages;
	}

	/**
	 * @since v1.3.4
	 * Displays content on dashboard sub menu page
	 */
	function dashboardPageContent() {
		$this->renderContent( 'index' );
	}

	/**
	 * @since v1.3.4
	 */
	protected function addFilters() {
		parent::addFilters();
		add_filter( 'kmcf7_sub_menu_pages_filter', [ $this, 'addSubMenuPage' ] );
		// add actions here
	}
}