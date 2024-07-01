<?php

namespace km_message_filter;

use KMSetting;
use KMSubMenuPage;
use WordPressTools;

class SettingsModule extends Module {
	private $is_free;
	private $wp_tools;

	public function __construct() {
		parent::__construct();
		$this->is_free = ( ! kmcf7ms_fs()->is_premium() || ! kmcf7ms_fs()->is_plan_or_trial( 'pro' ) );
		$this->addSettings();
		$this->checkWildcardInSettingFields();
		$this->wp_tools = WordPressTools::getInstance( __FILE__ );

		$is_using_old_tag_ui = get_option( 'kmcfmf_use_old_tag_ui', 'deleted' );

		if ( $is_using_old_tag_ui != 'deleted' ) {
			if ( $is_using_old_tag_ui == 'on' ) {
				update_option( 'kmcfmf_tag_ui', 'old_ui' );
			} else {
				update_option( 'kmcfmf_tag_ui', 'new_ui' );
			}

			delete_option( 'kmcfmf_use_old_tag_ui' );
		}


	}

	/**
	 * @since v1.2.5
	 */
	public function addSettings() {
		$max_words_text = $this->is_free ? __( "Note: You can add up of 40 words in the free version. Upgrade to add unlimited words.", KMCF7MS_TEXT_DOMAIN ) : '';

		// Check documentation here https://github.com/kofimokome/WordPress-Tools
		// Plugin settings
		$settings = new KMSetting( 'kmcf7-message-filter-options&tab=basic' );
		$settings->add_section( 'kmcfmf_basic' );
		/*$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_use_old_tag_ui',
				'label' => __( 'Use old tag UI?: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => 'We have added a new tag UI to the plugin. If you are having issues with the new tag UI, you can switch to the old tag UI.'
			)
		);*/
		$settings->add_field(
			array(
				'type'           => 'select',
				'id'             => 'kmcfmf_tag_ui',
				'label'          => __( 'Tag UI: ', KMCF7MS_TEXT_DOMAIN ),
				'options'        => array(
					'new_ui' => __( 'New UI', KMCF7MS_TEXT_DOMAIN ),
					'old_ui' => __( 'Old UI', KMCF7MS_TEXT_DOMAIN ),
					'none'   => __( 'None', KMCF7MS_TEXT_DOMAIN ),
				),
				'tip'            => 'If you are having issues with the new UI, you can switch to the old UI.',
				'default_option' => 'new_ui'
			)
		);
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
				'tip'         => "Eg.  spam, word, word2, etc...  <br/><a href='#' id='km-show-filters'>" . __( "Click here to insert my filters", KMCF7MS_TEXT_DOMAIN ) . "</a> " . $max_words_text,
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
				'tip'         => 'Eg. ( john@gmail.com, john@yahoo.com, john@hotmail.com, etc... ) ' . $max_words_text,
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
				'type'    => 'select',
				'id'      => 'kmcfmf_message_auto_delete_duration',
				'label'   => __( 'Number of days: ', KMCF7MS_TEXT_DOMAIN ),
				'options' => array(
					'30' => __( '1 Month', KMCF7MS_TEXT_DOMAIN ),
					'1'  => __( '1 Day', KMCF7MS_TEXT_DOMAIN ),
					'3'  => __( '3 Days', KMCF7MS_TEXT_DOMAIN ),
					'7'  => __( '1 Week', KMCF7MS_TEXT_DOMAIN ),
					'14' => __( '2 Weeks', KMCF7MS_TEXT_DOMAIN ),
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
					'10' => __( '10 Messages', KMCF7MS_TEXT_DOMAIN ),
					'20' => __( '20 Messages', KMCF7MS_TEXT_DOMAIN ),
					'40' => __( '40 Messages', KMCF7MS_TEXT_DOMAIN ),
					'80' => __( '80 Messages', KMCF7MS_TEXT_DOMAIN ),
				),
				// 'default_option' => ''
			)
		);

		$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_delete_data',
				'label' => __( 'Delete my data when uninstalling this plugin: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);

		/*$settings->add_field(
			array(
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_message_filter_reset',
				'label' => __( 'Reset plugin: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => ''
			)
		);*/

		$settings = apply_filters( 'kmcfmf_basic_settings', $settings );

		$settings->save();

		// Data collection settings

		$settings = new KMSetting( 'kmcf7-message-filter-options&tab=data_collection' );
		$settings->add_section( 'kmcfmf_data_collection' );
		$settings->add_field(
			array(
				'tip'   => '',
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_enable_collection',
				'label' => __( 'Enable data collection: ', KMCF7MS_TEXT_DOMAIN ),
			)
		);

		$settings->add_field(
			array(
				'read_only' => $this->is_free,
				'disabled'  => $this->is_free,
				'tip'       => $this->is_free ? __( 'This feature is only available in the premium version', KMCF7MS_TEXT_DOMAIN ) : __( 'Email reports are processed and sent from your website', KMCF7MS_TEXT_DOMAIN ),
				'type'      => 'checkbox',
				'id'        => 'kmcfmf_disable_email_reports',
				'label'     => $this->is_free ? __( 'Enable email reports: ', KMCF7MS_TEXT_DOMAIN ) : __( 'Disable email reports: ', KMCF7MS_TEXT_DOMAIN ),
			)
		);
		$settings->add_field(
			array(
				'read_only'   => $this->is_free,
				'disabled'    => $this->is_free,
				'placeholder' => 'admin@yoursite.com',
				'tip'         => $this->is_free ? __( 'This feature is only available in the premium version', KMCF7MS_TEXT_DOMAIN ) : '',
				'type'        => 'text',
				'id'          => 'kmcfmf_report_email',
				'label'       => __( 'Send Email Report To: ', KMCF7MS_TEXT_DOMAIN ),
			)
		);
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
				'type'  => 'checkbox',
				'id'    => 'kmcfmf_hide_error_message',
				'label' => __( 'Hide error messages: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'   => __( "Show a success message instead of an error message if a spam is found", KMCF7MS_TEXT_DOMAIN )
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
		$settings->add_field(
			array(
				'read_only' => $this->is_free,
				'disabled'  => $this->is_free,
				'tip'       => $this->is_free ? __( 'This feature is only available in the premium version', KMCF7MS_TEXT_DOMAIN ) : '',
				'type'      => 'select',
				'id'        => 'kmcfmf_contact_form_7_filter_type',
				'label'     => __( 'Apply filter to: ', KMCF7MS_TEXT_DOMAIN ),
				'options'   => array(
					''                 => __( 'All forms', KMCF7MS_TEXT_DOMAIN ),
					'all_forms_except' => __( 'All forms except', KMCF7MS_TEXT_DOMAIN ),
					'only_these_forms' => __( 'Only these forms', KMCF7MS_TEXT_DOMAIN ),
				),
				// 'default_option' => ''
			)
		);

		$settings->add_field(
			array(

				'read_only'   => $this->is_free,
				'disabled'    => $this->is_free,
				'type'        => 'textarea',
				'id'          => 'kmcfmf_contact_form_7_filter_forms',
				'input_class' => 'select2',
				'label'       => __( 'Select forms: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => $this->is_free ? __( 'This feature is only available in the premium version', KMCF7MS_TEXT_DOMAIN ) : __( "This will not apply if the 'Apply filter to' field is set to '<b>All Forms</b>'", KMCF7MS_TEXT_DOMAIN ),
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
		$settings->add_field(
			array(
				'read_only' => $this->is_free,
				'disabled'  => $this->is_free,
				'type'      => 'select',
				'tip'       => $this->is_free ? __( 'This feature is only available in the premium version', KMCF7MS_TEXT_DOMAIN ) : '',
				'id'        => 'kmcfmf_wp_forms_filter_type',
				'label'     => __( 'Apply filter to: ', KMCF7MS_TEXT_DOMAIN ),
				'options'   => array(
					''                 => __( 'All forms', KMCF7MS_TEXT_DOMAIN ),
					'all_forms_except' => __( 'All forms except', KMCF7MS_TEXT_DOMAIN ),
					'only_these_forms' => __( 'Only these forms', KMCF7MS_TEXT_DOMAIN ),
				),
				// 'default_option' => ''
			)
		);

		$settings->add_field(
			array(
				'read_only'   => $this->is_free,
				'disabled'    => $this->is_free,
				'type'        => 'textarea',
				'id'          => 'kmcfmf_wp_forms_filter_forms',
				'input_class' => 'select2',
				'label'       => __( 'Select forms: ', KMCF7MS_TEXT_DOMAIN ),
				'tip'         => $this->is_free ? __( 'This feature is only available in the premium version', KMCF7MS_TEXT_DOMAIN ) : __( "This will not apply if the 'Apply filter to' field is set to '<b>All Forms</b>'", KMCF7MS_TEXT_DOMAIN ),
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
				'position'   => 2,
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

		$settings_page->add_tab( 'my_filters', __( 'My Filters', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'my_filters' ) );

		$settings_page->add_tab( 'data_collection', __( 'Email & Data Collection', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'data_collection' ) );

		if ( kmcf7ms_fs()->can_use_premium_code() && ! kmcf7ms_fs()->is_premium() ) {
			$settings_page->add_tab( 'upgrade', __( 'Upgrade', KMCF7MS_TEXT_DOMAIN ), array(
				$this,
				'statusTabView'
			), array( 'tab' => 'upgrade' ) );
		}

		$settings_page->add_tab( 'debug', __( 'Debug Info', KMCF7MS_TEXT_DOMAIN ), array(
			$this,
			'statusTabView'
		), array( 'tab' => 'debug' ) );

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
		$this->wp_tools->renderView( 'settings.index' );
	}

	/**
	 * Displays settings page
	 * @since 1.2.5
	 */
	public function statusTabView( $args ) {
		switch ( $args['tab'] ) {
			case 'plugins':
				$this->wp_tools->renderView( 'settings.plugins' );
				break;
			case 'contactform7':
				$this->wp_tools->renderView( 'settings.contactform7' );
				break;
			case 'messages':
				$this->wp_tools->renderView( 'settings.messages' );
				break;
			case 'extensions':
				$this->wp_tools->renderView( 'settings.extensions' );
				break;
			case 'wpforms':
				$this->wp_tools->renderView( 'settings.wpforms' );
				break;
			case 'upgrade':
				$this->wp_tools->renderView( 'settings.upgrade' );
				break;
			case 'data_collection':
				$this->wp_tools->renderView( 'settings.data_collection' );
				break;
			case 'my_filters':
				$this->wp_tools->renderView( 'settings.my_filters' );
				break;
			case 'debug':
				$this->wp_tools->renderView( 'settings.debug' );
				break;
			default:
				$this->wp_tools->renderView( 'settings.settings' );
				break;
		}
	}

	/**
	 * @since v1.5.5
	 * @author kofimokome
	 */
	public function clearSuggestedSpamWords() {
		update_option( 'kmcfmf_suggested_words', '{}' );
		wp_send_json( [ 'message' => 'Suggested spam words cleared' ] );
		wp_die();
	}

	/**
	 * @since v1.3.4
	 */
	protected function addFilters() {
		parent::addFilters();
		add_filter( 'kmcf7_sub_menu_pages_filter', [ $this, 'addSubMenuPage' ] );
		// add actions here
	}

	protected function addActions() {
		parent::addActions();
		add_action( 'wp_ajax_kmcf7_clear_suggested_spam_words', [ $this, 'clearSuggestedSpamWords' ] );
	}
}