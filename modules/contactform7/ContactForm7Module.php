<?php

namespace km_message_filter;

use WPCF7_ContactForm;
use WPCF7_Submission;

$kmcf7_spam_status = false;

class ContactForm7Module extends Module {
	private $prevent_default_validation;
	private $count_updated = false;
	private $spam_word_error;
	private $spam_email_error;

	public function __construct() {
		parent::__construct();
		$this->prevent_default_validation = get_option( 'kmcfmf_hide_error_message' ) == 'on' ? true : false;
		$this->getErrorMessages();
	}

	/**
	 * Retrieves custom error messages
	 * @since v1.4.0
	 */
	private function getErrorMessages() {
		$this->spam_word_error  = get_option( 'kmcfmf_spam_word_error', false ) ? get_option( 'kmcfmf_spam_word_error' ) : __( "One or more fields have an error. Please check and try again.", 'contact-form-7' );
		$this->spam_email_error = get_option( 'kmcfmf_spam_email_error', false ) ? get_option( 'kmcfmf_spam_email_error' ) : __( 'The e-mail address entered is invalid.', KMCF7MS_TEXT_DOMAIN );
	}

	/**
	 * @since v1.4.0
	 * Gets and group all tags from contact form 7 forms into three categories: email, text and textarea
	 * @returns  array
	 */
	public static function getTags() {
		$email    = array( array( 'text' => '*', 'value' => '*' ) );
		$text     = array( array( 'text' => '*', 'value' => '*' ) );
		$textarea = array( array( 'text' => '*', 'value' => '*' ) );


		if ( class_exists( 'WPCF7_ContactForm' ) ) {
			$forms = WPCF7_ContactForm::find();
			foreach ( $forms as $form ) {
				$contact_form = WPCF7_ContactForm::get_instance( $form->id() );
				$rows         = $contact_form->scan_form_tags();
				foreach ( $rows as $row ) {
					switch ( $row->basetype ) {
						case 'email':
							array_push( $email, array( 'text' => $row->name, 'value' => $row->name ) );
							break;
						case 'text':
							array_push( $text, array( 'text' => $row->name, 'value' => $row->name ) );
							break;
						case 'textarea':
							array_push( $textarea, array( 'text' => $row->name, 'value' => $row->name ) );
							break;
					}
				}
			}
		}
		$tags = array(
			'text'     => array_unique( $text, SORT_REGULAR ),
			'textarea' => array_unique( $textarea, SORT_REGULAR ),
			'email'    => array_unique( $email, SORT_REGULAR )
		);

		return $tags;
	}


	/**
	 * @since v1.4.8
	 * Gets all registered contact form 7 forms
	 * @returns  array
	 */
	public static function getForms() {
		$forms = array();

		if ( class_exists( 'WPCF7_ContactForm' ) ) {
			$cf7_forms = WPCF7_ContactForm::find();
			foreach ( $cf7_forms as $form ) {
				$contact_form = WPCF7_ContactForm::get_instance( $form->id() );
				array_push( $forms, array( 'text' => $contact_form->title(), 'value' => $contact_form->id() ) );
			}
		}

		return $forms;
	}


	/**
	 * Filters text from form text elements from elems_names List
	 * @author: UnderWordPressure
	 * @since 1.2.3
	 */
	function textValidationFilter( $result, $tag ) {

		$submission = WPCF7_Submission::get_instance();

		// fixes for cf7-conditional-fields plugin
		if ( is_null( $submission ) ) {
			if ( isset( $_POST['_wpcf7'] ) ) {
				$id           = (int) $_POST['_wpcf7'];
				$contact_form = wpcf7_contact_form( $id );
			}
		} else {
			$contact_form = $submission->get_contact_form();
		}
		$is_exempted = $this->isFormExempted( $contact_form );

		if ( ! KMCFMessageFilter::skipValidation() && ! $is_exempted ) {
			$name  = $tag->name;
			$names = explode( ',', get_option( 'kmcfmf_tags_by_name' ) );
			if ( in_array( '*', $names ) ) {
				$result = $this->validateTextField( $result, $tag );
			} else if ( in_array( $name, $names ) ) {
				$result = $this->validateTextField( $result, $tag );
			}
		}

		return $result;

	}

	/**
	 * @since 1.4.8
	 * Checks if the form is exempted from validation
	 */
	private function isFormExempted( $contact_form ) {
		// 1 Check if a white list or black has been activated
		$filter_type  = get_option( 'kmcfmf_contact_form_7_filter_type', '' );
		$filter_forms = get_option( 'kmcfmf_contact_form_7_filter_forms', '' );
		$filter_forms = explode( ',', $filter_forms );
		$id           = $contact_form->id();
		switch ( $filter_type ) {
			case 'all_forms_except':
				// we have a white list
				if ( in_array( $id, $filter_forms ) ) {
					return true;
				}
				break;
			case 'only_these_forms':
				// we have a black list
				if ( ! in_array( $id, $filter_forms ) ) {
					return true;
				}
				break;
		}

		return false;
	}

	/**
	 * Runs spam filter on text fields and text area fields
	 * @since 1.4.0
	 */
	private function validateTextField( $result, $tag ) {
		global $kmcf7_spam_status;

		$name = $tag->name;

		$message   = isset( $_POST[ $name ] ) ? trim( (string) $_POST[ $name ] ) : '';
		$filter    = new Filter();
		$spam_word = $filter->validateTextField( $message );


		// Spam word is recognized
		if ( $spam_word ) {
			$invalidate_field = $this->preventDefaultValidation();
			if ( $invalidate_field ) {
				$result->invalidate( $tag, $this->spam_word_error );
			} else {
				$kmcf7_spam_status = true;
			}
			if ( ! $this->count_updated ) {
				$submission = WPCF7_Submission::get_instance();

				// fixes for cf7-conditional-fields plugin
				if ( is_null( $submission ) ) {
					if ( isset( $_POST['_wpcf7'] ) ) {
						$id = (int) $_POST['_wpcf7'];

						$contact_form = wpcf7_contact_form( $id );
						$submission   = WPCF7_Submission::get_instance( $contact_form );
					}
				} else {
					$contact_form = $submission->get_contact_form();
				}

				$data = array(
					'spam'    => $spam_word,
					'form'    => 'cf7',
					'message' => json_encode( $submission->get_posted_data() ),
					'form_id' => $contact_form->id()
				);
				MessagesModule::updateDatabase( $data );
				$this->count_updated = true;
			}
			do_action( 'kmcf7_after_invalidate_text_field' );
		}

		return $result;
	}

	/**
	 * Prevent default validation if a spam is found
	 *
	 * @return  bool
	 *
	 * @since v1.3.6
	 */
	private
	function preventDefaultValidation() {
		if ( $this->prevent_default_validation ) {
			$this->removeActions();

			return false;
		}

		return true;
	}

	/**
	 * Removes contact form 7 submit actions
	 * @since v1.3.6
	 */
	private
	function removeActions() {
		remove_all_actions( 'wpcf7_mail_sent' );
		remove_all_actions( 'wpcf7_before_send_mail' );
	}

	/**
	 * Filters text from textarea
	 * @since 1.0.0
	 */
	function textareaValidationFilter( $result, $tag ) {
		$submission = WPCF7_Submission::get_instance();

		// fixes for cf7-conditional-fields plugin
		if ( is_null( $submission ) ) {
			if ( isset( $_POST['_wpcf7'] ) ) {
				$id           = (int) $_POST['_wpcf7'];
				$contact_form = wpcf7_contact_form( $id );
			}
		} else {
			$contact_form = $submission->get_contact_form();
		}

		$is_exempted = $this->isFormExempted( $contact_form );

		if ( ! KMCFMessageFilter::skipValidation() && ! $is_exempted ) {
			$name = $tag->name;

			$names = explode( ',', get_option( 'kmcfmf_contact_form_7_textarea_fields' ) );
			if ( in_array( '*', $names ) ) {
				$result = $this->validateTextField( $result, $tag );

			} else if ( in_array( $name, $names ) ) {
				$result = $this->validateTextField( $result, $tag );
			}
		}

		return $result;
	}

	/**
	 * Filters text from email fields
	 * @since 1.0.0
	 */
	function emailValidationFilter( $result, $tag ) {
		$submission = WPCF7_Submission::get_instance();

		// fixes for cf7-conditional-fields plugin
		if ( is_null( $submission ) ) {
			if ( isset( $_POST['_wpcf7'] ) ) {
				$id           = (int) $_POST['_wpcf7'];
				$contact_form = wpcf7_contact_form( $id );
			}
		} else {
			$contact_form = $submission->get_contact_form();
		}

		$is_exempted = $this->isFormExempted( $contact_form );

		if ( ! KMCFMessageFilter::skipValidation() && ! $is_exempted ) {
			$name = $tag->name;

			$names = explode( ',', get_option( 'kmcfmf_contact_form_7_email_fields' ) );
			if ( in_array( '*', $names ) ) {
				$result = $this->validateEmailField( $result, $tag );

			} else if ( in_array( $name, $names ) ) {
				$result = $this->validateEmailField( $result, $tag );
			}
		}

		return $result;
	}

	/**
	 * Runs spam filter on email fields
	 * @since v1.4.0
	 */
	private
	function validateEmailField(
		$result, $tag
	) {
		global $kmcf7_spam_status;
		$name = $tag->name;

		$value  = isset( $_POST[ $name ] )
			? trim( wp_unslash( strtr( (string) $_POST[ $name ], "\n", " " ) ) )
			: '';
		$filter = new Filter();
		$spam   = $filter->validateEmail( $value );

		if ( $spam ) {
			$invalidate_field = $this->preventDefaultValidation();
			if ( $invalidate_field ) {
				$result->invalidate( $tag, $this->spam_email_error );
			} else {
				$kmcf7_spam_status = true;
			}
			if ( ! $this->count_updated ) {
				$submission   = WPCF7_Submission::get_instance();
				$contact_form = $submission->get_contact_form();
				$data         = array(
					'spam'    => '',
					'form'    => 'cf7',
					'message' => json_encode( $submission->get_posted_data() ),
					'form_id' => $contact_form->id()
				);
				MessagesModule::updateDatabase( $data );
				$this->count_updated = true;
			}
			do_action( 'kmcf7_after_invalidate_email_field' );
		}


		return $result;
	}

	/**
	 * Skips sending of contact form mail
	 * @since v1.3.6
	 */
	function skipMail( $skip_mail, $contact_form ) {
		global $kmcf7_spam_status;
		if ( $this->prevent_default_validation && $kmcf7_spam_status ) {
			return true;
		}

		return $skip_mail;
	}

	/**
	 * @since 1.4.8
	 * Called when a contact form is submitted
	 */
	/*public function onSubmit( $contact_form, $result ) {
		if ( $contact_form->in_demo_mode() ) {
			return;
		}

		// 1 Check if a white list or black has been activated
		$filter_type  = get_option( 'kmcfmf_contact_form_7_filter_type', '' );
		$filter_forms = get_option( 'kmcfmf_contact_form_7_filter_forms', '' );
		$filter_forms = explode( ',', $filter_forms );
		switch ( $filter_type ) {
			case 'all_forms_except':
				// we have a white list
				$id = $contact_form->id();
				if ( in_array( $id, $filter_forms ) ) {
					KMCFMessageFilter::skipValidation( true );
					echo 'submit '.(KMCFMessageFilter::skipValidation()? 'true' : 'false');

				}
				break;
			case 'only_these_forms':
				// we have a black list
				$id = $contact_form->id();
				if ( ! in_array( $id, $filter_forms ) ) {
					KMCFMessageFilter::skipValidation( true );
				}
				break;
		}
	}*/
	protected
	function addFilters() {
		parent::addFilters();
		add_filter( 'wpcf7_skip_mail', array( $this, 'skipMail' ), 999, 2 );

		$enable_message_filter        = get_option( 'kmcfmf_message_filter_toggle' ) == 'on' ? true : false;
		$enable_email_filter          = get_option( 'kmcfmf_email_filter_toggle' ) == 'on' ? true : false;
		$enable_contact_form_7_filter = get_option( 'kmcfmf_enable_contact_form_7_toggle' ) == 'on' ? true : false;

		if ( $enable_email_filter && $enable_contact_form_7_filter ) {
			add_filter( 'wpcf7_validate_email', array( $this, 'emailValidationFilter' ), 10, 2 );
			add_filter( 'wpcf7_validate_email*', array( $this, 'emailValidationFilter' ), 10, 2 );

		}

		if ( $enable_message_filter && $enable_contact_form_7_filter ) {
			add_filter( 'wpcf7_validate_textarea', array( $this, 'textareaValidationFilter' ), 10, 2 );
			add_filter( 'wpcf7_validate_textarea*', array( $this, 'textareaValidationFilter' ), 10, 2 );
			add_filter( 'wpcf7_validate_text', array( $this, 'textValidationFilter' ), 999, 2 );
			add_filter( 'wpcf7_validate_text*', array(
				$this,
				'textValidationFilter'
			), 999, 2 );

		}
	}


	protected function addActions() {
		parent::addActions();
//		add_action( 'wpcf7_submit', array( $this, 'onSubmit' ), 99, 2 );
	}
}
