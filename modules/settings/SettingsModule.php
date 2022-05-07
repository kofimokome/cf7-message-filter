<?php

namespace kmcf7_message_filter;

use KMSetting;
use KMSubMenuPage;

class SettingsModule extends Module {
	public function __construct() {
		parent::__construct();
		$this->addSettings();
//		$this->module = 'packages';
	}

	/**
	 * @since v1.3.4
	 */
	protected function addFilters() {
		parent::addFilters();
		add_filter( 'kmcf7_sub_menu_pages_filter', [ $this, 'addSubMenuPage' ] );
		// add actions here
	}

	/**
	 * @since v1.3.4
	 * Adds settings submenu page
	 */
	function addSubMenuPage( $sub_menu_pages ) {
		$settings_page = new KMSubMenuPage(
			array(
				'page_title' => 'Settings',
				'menu_title' => 'Settings',
				'capability' => 'manage_options',
				'menu_slug'  => 'kmcf7-message-filter-options',
				'function'   => array(
					$this,
					'settingsPageContent'
				),
				'use_tabs'   => true
			) );

		$settings_page->add_tab( 'basic', 'Basic Settings', array(
			$this,
			'statusTabView'
		), array( 'tab' => 'basic' ) );

		$settings_page->add_tab( 'advanced', 'Advanced Settings', array(
			$this,
			'statusTabView'
		), array( 'tab' => 'advanced' ) );

		$settings_page->add_tab( 'filters', 'Filters', array(
			$this,
			'statusTabView'
		), array( 'tab' => 'filters' ) );

		$settings_page->add_tab( 'plugins', 'More Plugins', array(
			$this,
			'statusTabView'
		), array( 'tab' => 'plugins' ) );


		array_push( $sub_menu_pages, $settings_page );

		return $sub_menu_pages;
	}

	/**
	 * @since v1.3.4
	 * Displays content on dashboard sub menu page
	 */
	function settingsPageContent() {
		$this->renderContent( 'index' );
	}

	/**
	 * Displays settings page
	 * @since 1.2.5
	 */
	public function statusTabView( $args ) {
		switch ( $args['tab'] ) {
			case 'plugins':
				$this->renderContent( 'plugins' );
				break;
			case 'advanced':
				$this->renderContent( 'advanced' );
				break;
			case 'filters':
				$this->renderContent( 'filters' );
				break;
			default:
				$this->renderContent( 'basic' );
				break;
		}
	}

	/**
	 * @since v1.2.5
	 */
	public function addSettings() {

		// Check documentation here https://github.com/kofimokome/WordPress-Tools

		$link_to_filters = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-options&tab=filters';
		$settings        = new KMSetting( 'kmcf7-message-filter-options&tab=basic' );
		$settings->add_section( 'kmcfmf_message_filter_basic' );
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_restricted_words',
				'input_class' => 'select2',
				'label'       => 'Restricted Words: ',
				'tip'         => "<a href='$link_to_filters'>Click here to view list of filters</a>",
				'placeholder' => 'eg john, doe, baby, man, [link], [russian]'
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_restricted_emails',
				'label'       => 'Restricted Emails: ',
				'input_class' => 'select2',
				'tip'         => 'Eg. ( john@gmail.com, john@yahoo.com, john@hotmail.com, etc... )',
				'placeholder' => 'eg john@doe.com, mary@doman.tk,'
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_tags_by_name',
				'input_class' => 'select2',
				'label'       => 'Analyze single line Text Fields with these names for restricted word, also: ',
				'tip'         => 'Note: your-subject, your-address, your-lastname, etc.',
				'placeholder' => ''
			)
		);

		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_spam_word_error',
				'label'       => 'Error Message For Restricted Words: ',
				'tip'         => '',
				'placeholder' => 'You have entered a word marked as spam'
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_spam_email_error',
				'label'       => 'Error Message For Restricted Emails: ',
				'tip'         => '',
				'placeholder' => 'The e-mail address entered is invalid.',
			)
		);

		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_filter_toggle',
				'label' => 'Enable Message Filter?: ',
				'tip'   => ''
			)
		);

		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_email_filter_toggle',
				'label' => 'Enable Email Filter?: ',
				'tip'   => ''
			)
		);

		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_tags_by_name_filter_toggle',
				'label' => 'Enable Filter on single line Text Fields by Name?: ',
				'tip'   => ''
			)
		);

		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_filter_reset',
				'label' => 'Reset Filter Count?: ',
				'tip'   => ''
			)
		);

		$settings->save();


		$settings = new KMSetting( 'kmcf7-message-filter-options&tab=advanced' );
		$settings->add_section( 'kmcfmf_message_filter_advanced' );
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_storage_toggle',
				'label' => 'Disable file storage: ',
				'tip'   => "<span class='text-danger' style='color:red;'>Note: This is an experimental feature.</span><br/>Blocked messages are currently stored in a file. <br/>If you are unable to view blocked messages, disable this option. <br/> <b>Note: </b> Auto delete will be activated if it's currently not enabled"
			)
		);
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_auto_delete_toggle',
				'label' => 'Auto delete messages: ',
				'tip'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'  => 'number',
				'id'    => 'kmcfmf_message_auto_delete_duration',
				'label' => 'Number of days: ',
				'tip'   => '',
				'min'   => 1,
				'max'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'    => 'select',
				'id'      => 'kmcfmf_message_auto_delete_duration',
				'label'   => 'Number of days: ',
				'options' => array(
					'1 Month' => '30',
					'1 Day'   => '1',
					'3 Days'  => '3',
					'1 Week'  => '7',
					'2 Weeks' => '14',
				),
				// 'default_option' => ''
			)
		);
		$settings->add_field(
			array(
				'type'    => 'select',
				'id'      => 'kmcfmf_message_auto_delete_amount',
				'label'   => 'Number of messages to delete: ',
				'options' => array(
					'10 Messages' => '10',
					'20 Messages' => '20',
					'40 Messages' => '40',
					'80 Messages' => '80',
				),
				// 'default_option' => ''
			)
		);
		$settings->save();
	}
}