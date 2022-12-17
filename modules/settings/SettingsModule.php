<?php

namespace km_message_filter;

use KMSetting;
use KMSubMenuPage;

class SettingsModule extends Module {
	public function __construct() {
		parent::__construct();
		$this->addSettings();
		$this->checkWildcardInSettingFields();
	}

	/**
	 * @since v1.2.5
	 */
	public function addSettings() {

		// Check documentation here https://github.com/kofimokome/WordPress-Tools
		// Plugin settings
		$settings = new KMSetting( 'kmcf7-message-filter-options&tab=basic' );
		$settings->add_section( 'kmcfmf_basic' );
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_filter_toggle',
				'label' => __( 'Enable spam words filter?: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_restricted_words',
				'input_class' => 'select2',
				'label'       => __( 'Spam words: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => "<a href='#' id='km-show-filters'>" . __( "Click here to view list of filters", KMCF7MS_TEXT_DOMAIN ) . "</a>",
				'placeholder' => 'eg john, doe, baby, man, [link], [russian]'
			)
		);
		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_email_filter_toggle',
				'label' => __( 'Enable spam email filter?: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_restricted_emails',
				'label'       => __( 'Spam emails: ', KMCF7MS_TEXT_DOMAIN ),
				'input_class' => 'select2',
				'tip'         => 'Eg. ( john@gmail.com, john@yahoo.com, john@hotmail.com, etc... )',
				'placeholder' => 'eg john@doe.com, mary@doman.tk,'
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

		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_filter_reset',
				'label' => __( 'Reset plugin: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);

		$settings = apply_filters( 'kmcfmf_basic_settings', $settings );

		$settings->save();

		// Error messages settings

		$settings = new KMSetting( 'kmcf7-message-filter-options&tab=messages' );
		$settings->add_section( 'kmcfmf_messages' );

		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_spam_word_error',
				'label'       => __( 'Error Message For Spam Words: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => '',
				'placeholder' => __( 'You have entered a word marked as spam', 'contact-form-7' )
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_spam_email_error',
				'label'       => __( 'Error Message For Spam Emails: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => '',
				'placeholder' => __( 'The e-mail address entered is invalid.', 'contact-form-7' ),
			)
		);
		$settings->add_field(
			array(
				'type'      => 'checkbox',
				'id'        => 'kmcfmf_hide_error_message',
				'label'     => __( 'Hide error messages: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'       => __( "Show a success message instead of an error message if a spam is found", KMCF7MS_TEXT_DOMAIN )
			)
		);


		$settings = apply_filters( 'kmcfmf_messages_settings', $settings );
		$settings->save();

		// Contact Form 7 settings
		$settings = new KMSetting( 'kmcf7-message-filter-options&tab=contactform7' );
		$settings->add_section( 'kmcfmf_contact_form_7' );

		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_enable_contact_form_7_toggle',
				'label' => __( 'Enable Contact Form 7 filter: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_tags_by_name',
				'input_class' => 'select2',
				'label'       => __( 'Text fields to analyse: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => 'Only the fields with the [text] or [text*] tag on your form. Eg: your-subject, your-address, your-lastname, etc.',
				'placeholder' => ''
			)
		);

		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_contact_form_7_textarea_fields',
				'input_class' => 'select2',
				'label'       => __( 'Text area fields to analyse: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => 'Only the fields with the [textarea] or [textarea*] tag on your form. Eg: your-message, etc.',
				'placeholder' => ''
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_contact_form_7_email_fields',
				'input_class' => 'select2',
				'label'       => __( 'Email fields to analyse: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => 'Only the fields with the [email] or [email*] tag on your form. eg: your-email  etc.',
				'placeholder' => ''
			)
		);

		$settings = apply_filters( 'kmcfmf_contact_form_7_settings', $settings );
		$settings->save();

		// WP Forms settings
		$settings = new KMSetting( 'kmcf7-message-filter-options&tab=wpforms' );
		$settings->add_section( 'kmcfmf_wp_forms' );

		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_enable_wp_forms_toggle',
				'label' => __( 'Enable WP Forms filter: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_wp_forms_text_fields',
				'input_class' => 'select2',
				'label'       => __( 'Text fields to analyse: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => 'Eg: Name, Subject etc.',
				'placeholder' => ''
			)
		);

		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_wp_forms_textarea_fields',
				'input_class' => 'select2',
				'label'       => __( 'Text area fields to analyse: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => 'Eg: Comment or Message, etc.',
				'placeholder' => ''
			)
		);
		$settings->add_field(
			array(
				'type'        => 'textarea',
				'id'          => 'kmcfmf_wp_forms_email_fields',
				'input_class' => 'select2',
				'label'       => __( 'Email fields to analyse: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => 'Eg: Email  etc.',
				'placeholder' => ''
			)
		);

		$settings = apply_filters( 'kmcf7_wp_forms_settings', $settings );

		$settings->save();
	}

	/**
	 * Deletes duplicate data in fields having the * wildcard.
	 * @since v1.4.0
	 */
	private function checkWildcardInSettingFields() {
		$options = array(
			'kmcfmf_tags_by_name',
			'kmcfmf_contact_form_7_textarea_fields',
			'kmcfmf_contact_form_7_email_fields',
			'kmcfmf_wp_forms_text_fields',
			'kmcfmf_wp_forms_textarea_fields',
			'kmcfmf_wp_forms_email_fields',
		);
		foreach ( $options as $option ) {
			$names = explode( ',', get_option( $option ) );
			if ( in_array( '*', $names ) && sizeof( $names ) > 1 ) {
				update_option( $option, '*' );
			}
		}
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

		$settings_page->add_tab( 'settings', __( 'Settings', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'settings' ) );

		$settings_page->add_tab( 'messages', __( 'Error Messages', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'messages' ) );


		$settings_page->add_tab( 'contactform7', __( 'Contact Form 7', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'contactform7' ) );

		$settings_page->add_tab( 'wpforms', __( 'WP Forms', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'wpforms' ) );
		/*
				$settings_page->add_tab( 'extensions', __( 'Extensions', KMCF7MS_TEXT_DOMAIN ), array(
					$this,
					'statusTabView'
				), array( 'tab' => 'extensions' ) );*/

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
			case 'contactform7':
				$this->renderContent( 'contactform7' );
				break;
			case 'messages':
				$this->renderContent( 'messages' );
				break;
			case 'extensions':
				$this->renderContent( 'extensions' );
				break;
			case 'wpforms':
				$this->renderContent( 'wpforms' );
				break;
			case 'upgrade':
				$this->renderContent( 'upgrade' );
				break;
			default:
				$this->renderContent( 'settings' );
				break;
		}
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