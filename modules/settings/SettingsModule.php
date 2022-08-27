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

		$settings_page->add_tab( 'basic', __( 'Basic', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'basic' ) );

		$settings_page->add_tab( 'messages', __( 'Error Messages', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'messages' ) );

		$settings_page->add_tab( 'advanced', __( 'Advanced', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'advanced' ) );

		$settings_page->add_tab( 'extensions', __( 'Extensions', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'extensions' ) );

		$settings_page->add_tab( 'filters', __( 'Filters', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'filters' ) );

		$settings_page->add_tab( 'plugins', __( 'More Plugins', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'plugins' ) );

		$settings_page = apply_filters( 'kmcf7_settings_tab', $settings_page );


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
			case 'messages':
				$this->renderContent( 'messages' );
				break;
			case 'extensions':
				$this->renderContent( 'extensions' );
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
		// basic settings
		$link_to_filters = admin_url( 'admin.php' ) . '?page=kmcf7-message-filter-options&tab=filters';
		$settings        = new KMSetting( 'kmcf7-message-filter-options&tab=basic' );
		$settings->add_section( 'kmcfmf_message_filter_basic' );
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_filter_toggle',
				'label' => __( 'Enable Message Filter?: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_restricted_words',
				'input_class' => 'select2',
				'label'       => __( 'Restricted Words: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => "<a href='$link_to_filters'>" . __( "Click here to view list of filters", KMCF7MS_TEXT_DOMAIN ) . "</a>",
				'placeholder' => 'eg john, doe, baby, man, [link], [russian]'
			)
		);
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_email_filter_toggle',
				'label' => __( 'Enable Email Filter?: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_restricted_emails',
				'label'       => __( 'Restricted Emails: ', KMCF7MS_TEXT_DOMAIN ),
				'input_class' => 'select2',
				'tip'         => 'Eg. ( john@gmail.com, john@yahoo.com, john@hotmail.com, etc... )',
				'placeholder' => 'eg john@doe.com, mary@doman.tk,'
			)
		);
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_tags_by_name_filter_toggle',
				'label' => __( 'Enable Filter on single line Text Fields by Name?: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_tags_by_name',
				'input_class' => 'select2',
				'label'       => __( 'Analyze single line Text Fields with these names for restricted word, also: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => 'Note: your-subject, your-address, your-lastname, etc.',
				'placeholder' => ''
			)
		);

		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_filter_reset',
				'label' => __( 'Reset Filter Count?: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);

		$settings = apply_filters( 'kmcf7_basic_settings', $settings );

		$settings->save();

		// Error messages settings
		$settings = new KMSetting( 'kmcf7-message-filter-options&tab=messages' );
		$settings->add_section( 'kmcfmf_message_filter_messages' );
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_spam_word_error',
				'label'       => __( 'Error Message For Restricted Words: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => '',
				'placeholder' => __( 'You have entered a word marked as spam', 'contact-form-7' )
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_spam_email_error',
				'label'       => __( 'Error Message For Restricted Emails: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => '',
				'placeholder' => __( 'The e-mail address entered is invalid.', 'contact-form-7' ),
			)
		);

		$settings = apply_filters( 'kmcf7_message_settings', $settings );

		$settings->save();

		// Advanced settings
		$settings = new KMSetting( 'kmcf7-message-filter-options&tab=advanced' );
		$settings->add_section( 'kmcfmf_message_filter_advanced' );
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_storage_toggle',
				'label' => __( 'Disable file storage: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => __( "<span class='text-danger' style='color:red;'>Note: This is an experimental feature.</span><br/>Blocked messages are currently stored in a file. <br/>If you are unable to view blocked messages, enable this option. <br/> <b>Note: </b> Auto delete will be activated if it's currently not enabled", KMCF7MS_TEXT_DOMAIN )
			)
		);
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_db_storage_toggle',
				'label' => __( 'Enable database storage: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => __( "<span class='text-danger' style='color:red;'>Note: This is an experimental feature.</span><br/>Blocked messages are currently stored in a file. <br/>If you are unable to view blocked messages, enable this option. <br/> <b>Note: </b>", KMCF7MS_TEXT_DOMAIN )
			)
		);
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_auto_delete_toggle',
				'label' => __( 'Auto delete messages: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'  => 'number',
				'id'    => 'kmcfmf_message_auto_delete_duration',
				'label' => __( 'Number of days: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => '',
				'min'   => 1,
				'max'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'    => 'select',
				'id'      => 'kmcfmf_message_auto_delete_duration',
				'label'   => __( 'Number of days: ', KMCF7MS_TEXT_DOMAIN ),
				'options' => array(
					__( '1 Month', KMCF7MS_TEXT_DOMAIN ) => '30',
					__( '1 Day', KMCF7MS_TEXT_DOMAIN )   => '1',
					__( '3 Days', KMCF7MS_TEXT_DOMAIN )  => '3',
					__( '1 Week', KMCF7MS_TEXT_DOMAIN )  => '7',
					__( '2 Weeks', KMCF7MS_TEXT_DOMAIN ) => '14',
				),
				// 'default_option' => ''
			)
		);
		$settings->add_field(
			array(
				'type'    => 'select',
				'id'      => 'kmcfmf_message_auto_delete_amount',
				'label'   => __( 'Number of messages to delete: ', KMCF7MS_TEXT_DOMAIN ),
				'options' => array(
					__( '10 Messages', KMCF7MS_TEXT_DOMAIN ) => '10',
					__( '20 Messages', KMCF7MS_TEXT_DOMAIN ) => '20',
					__( '40 Messages', KMCF7MS_TEXT_DOMAIN ) => '40',
					__( '80 Messages', KMCF7MS_TEXT_DOMAIN ) => '80',
				),
				// 'default_option' => ''
			)
		);

		$settings = apply_filters( 'kmcf7_advanced_settings', $settings );

		$settings->save();
	}
}