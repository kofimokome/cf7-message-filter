<?php

namespace km_message_filter;

$km_wp_forms_spam_status = false;

class WpFormsModule extends Module {
	private $count_updated = false;
	private $spam_word_error;
	private $spam_email_error;
	private $form_fields = array();
	private $fields = array();
	private $form_id;
	private $prevent_default_validation;

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
		if ( KMCFMFs()->is_plan_or_trial__premium_only( 'pro' ) ) {
			$this->spam_word_error  = get_option( 'kmcfmf_spam_word_error', false ) ? get_option( 'kmcfmf_spam_word_error' ) : __( "One or more fields have an error. Please check and try again.", 'contact-form-7' );
			$this->spam_email_error = get_option( 'kmcfmf_spam_email_error', false ) ? get_option( 'kmcfmf_spam_email_error' ) : __( 'The e-mail address entered is invalid.', KMCF7MS_TEXT_DOMAIN );
		} else {
			$this->spam_word_error  = __( "One or more fields have an error. Please check and try again.", 'contact-form-7' );
			$this->spam_email_error = __( 'The e-mail address entered is invalid.', KMCF7MS_TEXT_DOMAIN );
		}
	}

	/**
	 * @since v1.4.0
	 * Gets and group all tags from wp forma forms into three categories: email, text and textarea
	 * @returns  array
	 */
	public static function getTags() {

		$email    = array( array( 'text' => '*', 'value' => '*' ) );
		$text     = array( array( 'text' => '*', 'value' => '*' ) );
		$textarea = array( array( 'text' => '*', 'value' => '*' ) );

		if ( function_exists( 'wpforms' ) ) {
			$args['post_status'] = 'publish';
			$forms               = wpforms()->get( 'form' )->get( '', $args );
			foreach ( $forms as $form ) {
				$content = json_decode( $form->post_content, true );
				$fields  = $content['fields'];

				foreach ( $fields as $field ) {
					switch ( $field['type'] ) {
						case 'email':
							array_push( $email, array( 'text' => $field['label'], 'value' => $field['label'] ) );
							break;
						case 'name':
							array_push( $text, array( 'text' => $field['label'], 'value' => $field['label'] ) );
							break;
						case 'textarea':
							array_push( $textarea, array( 'text' => $field['label'], 'value' => $field['label'] ) );
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
	 * Filters text from form text elements from elems_names List
	 * @since 1.4.0
	 */
	function textValidationFilter( $errors, $form_data ) {

		$fields            = $_POST['wpforms']['fields'];
		$form_fields       = $form_data['fields'];
		$this->form_fields = $form_fields;
		$this->fields      = $fields;
		$this->form_id     = sanitize_text_field( $_POST['wpforms']['id'] );
		$names             = explode( ',', get_option( 'kmcfmf_wp_forms_text_fields' ) );
		$invalid_fields    = empty( $errors[ $_POST['wpforms']['id'] ] ) ? array() : $errors[ $_POST['wpforms']['id'] ];

		if ( in_array( '*', $names ) ) {
			foreach ( $form_fields as $field ) {

				if ( $field['type'] == 'name' ) {
					if ( $this->validateTextField( $fields[ $field['id'] ] ) ) {
						$invalid_fields[ $field['id'] ]    = $this->spam_word_error;
						$errors[ $_POST['wpforms']['id'] ] = $invalid_fields;
//						return $errors;
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
			$invalidate_field = $this->preventDefaultValidation();
			if ( $invalidate_field ) {
				$return = true;
			} else {
				$km_wp_forms_spam_status = true;
			}
			if ( ! $this->count_updated ) {
				$data = array(
					'spam'    => $spam_word,
					'form'    => 'wpforms',
					'message' => json_encode( $this->getPostedData() ),
					'form_id' => $this->form_id
				);
				MessagesModule::updateDatabase( $data );
				$this->count_updated = true;
			}
			do_action( 'km_wp_forms_after_invalidate_text_field' );
		}

		return $return;
	}

	/**
	 * Prevent default validation if a spam is found
	 *
	 * @return  bool
	 *
	 * @since v1.4.0
	 */
	private function preventDefaultValidation() {
		if ( $this->prevent_default_validation ) {
			if ( KMCFMFs()->is_plan_or_trial__premium_only( 'pro' ) ) {
				$this->removeActions__premium_only();
			}

			return false;
		}

		return true;
	}

	/**
	 * Removes wp forms submit actions
	 * @since v1.4.0
	 */
	private function removeActions__premium_only() {
		if ( KMCFMFs()->is_plan_or_trial( 'pro' ) ) {
			remove_all_actions( 'wpforms_process_entry_save' );
			remove_all_actions( 'wpforms_process_entry_saved' );
		}
	}

	/**
	 * @since v1.4.0
	 * Matches submitted data to their field names
	 * @returns array
	 */
	private function getPostedData() {
		$postedData = array();
		foreach ( $this->form_fields as $field ) {
			if ( is_array( $this->fields[ $field['id'] ] ) ) {
				$postedData[ $field['label'] ] = implode( ' ', $this->fields[ $field['id'] ] );
			} else {
				$postedData[ $field['label'] ] = $this->fields[ $field['id'] ];
			}
		}

		return $postedData;
	}

	/**
	 * Filters text from textarea
	 * @since 1.4.0
	 */
	function textareaValidationFilter( $errors, $form_data ) {

		$fields            = $_POST['wpforms']['fields'];
		$form_fields       = $form_data['fields'];
		$this->form_fields = $form_fields;
		$this->fields      = $fields;
		$this->form_id     = sanitize_text_field( $_POST['wpforms']['id'] );
		$names             = explode( ',', get_option( 'kmcfmf_wp_forms_textarea_fields' ) );
		$invalid_fields    = empty( $errors[ $_POST['wpforms']['id'] ] ) ? array() : $errors[ $_POST['wpforms']['id'] ];

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
		$fields            = $_POST['wpforms']['fields'];
		$form_fields       = $form_data['fields'];
		$this->form_fields = $form_fields;
		$this->fields      = $fields;
		$this->form_id     = sanitize_text_field( $_POST['wpforms']['id'] );
		$names             = explode( ',', get_option( 'kmcfmf_wp_forms_email_fields' ) );
		$invalid_fields    = empty( $errors[ $_POST['wpforms']['id'] ] ) ? array() : $errors[ $_POST['wpforms']['id'] ];

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
			$invalidate_field = $this->preventDefaultValidation();
			if ( $invalidate_field ) {
				$return = true;
			} else {
				$km_wp_forms_spam_status = true;
			}
			if ( ! $this->count_updated ) {
				$data = array(
					'spam'    => '',
					'form'    => 'wpforms',
					'message' => json_encode( $this->getPostedData() ),
					'form_id' => $this->form_id
				);
				MessagesModule::updateDatabase( $data );
				$this->count_updated = true;
			}
			do_action( 'km_wp_forms_after_invalidate_email_field' );
		}

		return $return;
	}

	/**
	 * Skips sending of mails on submission if a spam word is found
	 * @return bool
	 * @since v1.4.0
	 */
	public function skipMail__premium_only( $value, $object ) {
		global $km_wp_forms_spam_status;
		if ( KMCFMFs()->is_plan_or_trial( 'pro' ) ) {
			if ( $km_wp_forms_spam_status && $this->prevent_default_validation ) {
				return true;
			}
		}

		return $value;
	}

	/**
	 * Prevents sending of mails if skipMail() fails prevent sending of mails
	 * @return array
	 * @since v1.4.0
	 */
	public function removeEmailAddress__premium_only( $email, $fields, $entry, $form_data, $notification_id ) {
		global $km_wp_forms_spam_status;
		if ( KMCFMFs()->is_plan_or_trial( 'pro' ) ) {
			if ( $km_wp_forms_spam_status && $this->prevent_default_validation ) {
				$email['address'] = array();
			}
		}

		return $email;
	}

	protected function addFilters() {
		parent::addFilters();

		$enable_message_filter = get_option( 'kmcfmf_message_filter_toggle' ) == 'on' ? true : false;
		$enable_email_filter   = get_option( 'kmcfmf_email_filter_toggle' ) == 'on' ? true : false;
		$enable_wp_form_filter = get_option( 'kmcfmf_enable_wp_forms_toggle' ) == 'on' ? true : false;

		if ( $enable_email_filter && $enable_wp_form_filter ) {
			add_filter( 'wpforms_process_initial_errors', array( $this, 'emailValidationFilter' ), 999, 2 );
		}

		if ( $enable_message_filter && $enable_wp_form_filter ) {
			add_filter( 'wpforms_process_initial_errors', array( $this, 'textValidationFilter' ), 999, 2 );
			add_filter( 'wpforms_process_initial_errors', array( $this, 'textareaValidationFilter' ), 999, 2 );

		}

	}

	protected function addActions() {
		parent::addActions();
		if ( KMCFMFs()->is_plan_or_trial__premium_only( 'pro' ) ) {
			add_action( 'wpforms_disable_all_emails', array( $this, 'skipMail__premium_only' ), 999, 2 );
			add_action( 'wpforms_entry_email_atts', array( $this, 'removeEmailAddress__premium_only' ), 999, 5 );
		}
	}

}
