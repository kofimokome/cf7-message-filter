<?php

namespace km_message_filter;

$km_wp_forms_spam_status = false;

class WpFormsModule extends Module {
	private $count_updated = false;
	private $spam_word_error;
	private $spam_email_error;

	public function __construct() {
		parent::__construct();
		$this->getErrorMessages();
	}

	/**
	 * Retrieves custom error messages
	 * @since v1.4.0
	 */
	private function getErrorMessages() {
		if ( KMCFMFs()->is_plan_or_trial__premium_only( 'pro' ) ) {
			$this->spam_word_error  = get_option( 'kmcfmf_spam_word_error', false ) ? get_option( 'kmcfmf_spam_word_error' ) : __( "One or more fields have an error. Please check and try again.", 'contact-form-7' );
			$this->spam_email_error = get_option( 'kmcfmf_spam_email_error', false ) ? get_option( 'kmcfmf_spam_email_error' ) : __( 'The e-mail address entered is invalid.', KMCF7MS_TEXT_DOMAIN );
		} else {
			$this->spam_word_error  = __( "One or more fields have an error. Please check and try again.", 'contact-form-7' );
			$this->spam_email_error = __( 'The e-mail address entered is invalid.', KMCF7MS_TEXT_DOMAIN );
		}
	}

	/**
	 * Filters text from form text elements from elems_names List
	 * @since 1.4.0
	 */
	function textValidationFilter( $errors, $form_data ) {

		$fields         = $_POST['wpforms']['fields'];
		$form_fields    = $form_data['fields'];
		$names          = explode( ',', get_option( 'kmcfmf_wp_forms_text_fields' ) );
		$invalid_fields = empty( $errors[ $_POST['wpforms']['id'] ] ) ? array() : $errors[ $_POST['wpforms']['id'] ];

		if ( in_array( '*', $names ) ) {
			foreach ( $form_fields as $field ) {
				if ( $field['type'] == 'name' ) {
					if ( $this->validateTextField( $fields[ $field['id'] ] ) ) {
						$invalid_fields[ $field['id'] ]    = $this->spam_email_error;
						$errors[ $_POST['wpforms']['id'] ] = $invalid_fields;

						return $errors;
					}
				}
			}
		} else {
			foreach ( $form_fields as $field ) {
				if ( $field['type'] == 'name' && in_array( $field['label'], $names ) ) {
					if ( $this->validateTextField( $fields[ $field['id'] ] ) ) {
						$invalid_fields[ $field['id'] ]    = $this->spam_word_error;
						$errors[ $_POST['wpforms']['id'] ] = $invalid_fields;

						return $errors;
					}
				}
			}
		}

		return $errors;

	}

	/**
	 * Runs spam filter on text fields and text area fields
	 * @since 1.4.0
	 */
	private function validateTextField( $message ) {
		global $km_wp_forms_spam_status;
		if ( is_array( $message ) ) {
			$message = implode( ' ', $message );
		}

		$filter    = new Filter();
		$spam_word = $filter->validateTextField( $message );
		$return    = false;
		// Spam word is recognized
		if ( $spam_word ) {
			$invalidate_field = apply_filters( 'km_wp_forms_invalidate_text_field', true );
			if ( $invalidate_field ) {
				$return = true;
			} else {
				$km_wp_forms_spam_status = true;
			}
			if ( ! $this->count_updated ) {
				MessagesModule::updateDatabase( $spam_word, 'wp_forms' );
				$this->count_updated = true;
			}
			do_action( 'km_wp_forms_after_invalidate_text_field' );
		}

		return $return;
	}

	/**
	 * Filters text from textarea
	 * @since 1.4.0
	 */
	function textareaValidationFilter( $errors, $form_data ) {

		$fields         = $_POST['wpforms']['fields'];
		$form_fields    = $form_data['fields'];
		$names          = explode( ',', get_option( 'kmcfmf_wp_forms_textarea_fields' ) );
		$invalid_fields = empty( $errors[ $_POST['wpforms']['id'] ] ) ? array() : $errors[ $_POST['wpforms']['id'] ];

		if ( in_array( '*', $names ) ) {
			foreach ( $form_fields as $field ) {
				if ( $field['type'] == 'textarea' ) {
					if ( $this->validateTextField( $fields[ $field['id'] ] ) ) {
						$invalid_fields[ $field['id'] ]    = $this->spam_email_error;
						$errors[ $_POST['wpforms']['id'] ] = $invalid_fields;

						return $errors;
					}
				}
			}
		} else {
			foreach ( $form_fields as $field ) {
				if ( $field['type'] == 'textarea' && in_array( $field['label'], $names ) ) {
					if ( $this->validateTextField( $fields[ $field['id'] ] ) ) {
						$invalid_fields[ $field['id'] ]    = $this->spam_word_error;
						$errors[ $_POST['wpforms']['id'] ] = $invalid_fields;

						return $errors;
					}
				}
			}
		}

		return $errors;

	}

	/**
	 * Filters text from email fields
	 * @since 1.4.0
	 */
	function emailValidationFilter( $errors, $form_data ) {
		$fields         = $_POST['wpforms']['fields'];
		$form_fields    = $form_data['fields'];
		$names          = explode( ',', get_option( 'kmcfmf_wp_forms_email_fields' ) );
		$invalid_fields = empty( $errors[ $_POST['wpforms']['id'] ] ) ? array() : $errors[ $_POST['wpforms']['id'] ];

		if ( in_array( '*', $names ) ) {
			foreach ( $form_fields as $field ) {
				if ( $field['type'] == 'email' ) {
					if ( $this->validateEmailField( $fields[ $field['id'] ] ) ) {
						$invalid_fields[ $field['id'] ]    = $this->spam_email_error;
						$errors[ $_POST['wpforms']['id'] ] = $invalid_fields;

						return $errors;
					}
				}
			}
		} else {
			foreach ( $form_fields as $field ) {
				if ( $field['type'] == 'email' && in_array( $field['label'], $names ) ) {
					if ( $this->validateEmailField( $fields[ $field['id'] ] ) ) {
						$invalid_fields[ $field['id'] ]    = $this->spam_email_error;
						$errors[ $_POST['wpforms']['id'] ] = $invalid_fields;

						return $errors;
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Runs spam filter on email fields
	 * @return bool
	 * @since v1.4.0
	 */
	private function validateEmailField( $value ) {
		global $km_wp_forms_spam_status;
		if ( is_array( $value ) ) {
			$value = implode( ' ', $value );
		}

		$filter = new Filter();
		$spam   = $filter->validateEmail( $value );
		$return = false;
		if ( $spam ) {
			$invalidate_field = apply_filters( 'km_wp_forms_invalidate_email_field', true );
			if ( $invalidate_field ) {
				$return = true;
			} else {
				$km_wp_forms_spam_status = true;
			}
			if ( ! $this->count_updated ) {
				MessagesModule::updateDatabase( '', 'wp_forms' );
				$this->count_updated = true;
			}
			do_action( 'km_wp_forms_after_invalidate_email_field' );
		}

		return $return;
	}

	/**
	 * @since v1.4.0
	 * Adds validation for wp forms
	 */
	public function emailValidationFilterc( $errors, $form_data ) {
//		print_r( $_POST['wpforms'] );
//		print_r( $form_data['fields'] );
		$test    = array();
		$test[0] = 'see';
//		$test['footer']='footere error';
		$errors[ $_POST['wpforms']['id'] ] = $test;


//print_r($errors);
		return $errors;
	}


	protected function addFilters() {
		parent::addFilters();

		$enable_message_filter = get_option( 'kmcfmf_message_filter_toggle' ) == 'on' ? true : false;
		$enable_email_filter   = get_option( 'kmcfmf_email_filter_toggle' ) == 'on' ? true : false;
		$enable_wp_form_filter = get_option( 'kmcfmf_enable_wp_forms_toggle' ) == 'on' ? true : false;

		if ( $enable_email_filter && $enable_wp_form_filter ) {
			add_filter( 'wpforms_process_initial_errors', array( $this, 'emailValidationFilter' ), 999, 2 );
//			add_filter( 'wpforms_process_initial_errors', array( $this, 'emailValidationFilterc' ), 999, 2 );
		}

		if ( $enable_message_filter && $enable_wp_form_filter ) {
			add_filter( 'wpforms_process_initial_errors', array( $this, 'textValidationFilter' ), 999, 2 );
			add_filter( 'wpforms_process_initial_errors', array( $this, 'textareaValidationFilter' ), 999, 2 );

		}


	}

	protected function addActions() {
		parent::addActions();
		// add_action('wpcf7_submit', array($this, 'onWpcf7Submit'),10, 2);
	}

}
