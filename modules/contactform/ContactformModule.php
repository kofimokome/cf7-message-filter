<?php

namespace kmcf7_message_filter;

use WPCF7_Submission;

class ContactformModule extends Module {
	private $count_updated = false;

	public function __construct() {
		parent::__construct();
	}

	private function checkJapanese( $value, $character_sets = array() ) {
		$found = false;

		foreach ( $character_sets as $character_set ) {

			switch ( $character_set ) {
				case 'hiragana':
					$found = preg_match( '/[\x{3041}-\x{3096}]/ium', $value );
					break;

				case 'katakana':
					$found = preg_match( '/[\x{30A0}-\x{30FF}]/ium', $value );
					break;

				case 'kanji':
					$found = preg_match( '/[\x{3400}-\x{4DB5}\x{4E00}-\x{9FCB}\x{F900}-\x{FA6A}]/ium', $value );
					break;

				case 'kanji_radicals':
					$found = preg_match( '/[\x{2E80}-\x{2FD5}]/ium', $value );
					break;

				case 'katakana_punctuation':
					$found = preg_match( '/[\x{FF5F}-\x{FF9F}]/ium', $value );
					break;

				case 'symbols_punctuations':
					$found = preg_match( '/[\x{3000}-\x{303F}]/ium', $value );
					break;

				case 'others':
					$found = preg_match( '/[\x{31F0}-\x{31FF}\x{3220}-\x{3243}\x{3280}-\x{337F}]/ium', $value );
					break;
			}

			if ( $found ) {
				break 1;
			}
		}

		return $found;
	}

	protected function addFilters() {
		parent::addFilters();

		add_filter( 'wpcf7_messages', array( $this, 'addCustomMessages' ), 10, 1 );

		$enable_message_filter       = get_option( 'kmcfmf_message_filter_toggle' ) == 'on' ? true : false;
		$enable_email_filter         = get_option( 'kmcfmf_email_filter_toggle' ) == 'on' ? true : false;
		$enable_tags_by_names_filter = get_option( 'kmcfmf_tags_by_name_filter_toggle' ) == 'on' ? true : false;

		if ( $enable_email_filter ) {
			add_filter( 'wpcf7_validate_email', array( $this, 'textValidationFilter' ), 12, 2 );
			add_filter( 'wpcf7_validate_email*', array( $this, 'textValidationFilter' ), 12, 2 );
		}

		if ( $enable_message_filter ) {
			add_filter( 'wpcf7_validate_textarea', array( $this, 'textareaValidationFilter' ), 12, 2 );
			add_filter( 'wpcf7_validate_textarea*', array( $this, 'textareaValidationFilter' ), 12, 2 );
		}

		if ( $enable_tags_by_names_filter ) {
			add_filter( 'wpcf7_validate_text', array( $this, 'textTagsByNameValidationFilter' ), 12, 2 );
			add_filter( 'wpcf7_validate_text*', array( $this, 'textTagsByNameValidationFilter' ), 12, 2 );
		}
	}

	protected function addActions() {
		parent::addActions();
		// add_action('wpcf7_submit', array($this, 'onWpcf7Submit'),10, 2);
	}

	public function onWpcf7Submit( $contact_form, $result ) {
		$logs_root  = wp_upload_dir()['basedir'] . '/kmcf7mf_logs/';
		$submission = WPCF7_Submission::get_instance();
		file_put_contents( $logs_root . 'test.txt', json_encode( $submission->get_posted_data() ) );

	}


	/**
	 * Adds a custom message for messages flagged as spam
	 * @since 1.2.2
	 */
	public function addCustomMessages( $messages ) {
		$spam_word_eror   = get_option( 'kmcfmf_spam_word_error' ) ? get_option( 'kmcfmf_spam_word_error' ) : 'One or more fields have an error. Please check and try again.';
		$spam_email_error = get_option( 'kmcfmf_spam_email_error' ) ? get_option( 'kmcfmf_spam_email_error' ) : 'The e-mail address entered is invalid.';
		$messages         = array_merge( $messages, array(
			'spam_word_error'  => array(
				'description' =>
					__( "Message contains a word marked as spam", 'contact-form-7' ),
				'default'     =>
					__( $spam_word_eror, 'contact-form-7' ),
			),
			'spam_email_error' => array(
				'description' =>
					__( "Email is an email marked as spam", 'contact-form-7' ),
				'default'     =>
					__( $spam_email_error, 'contact-form-7' ),
			),
		) );

		return $messages;
	}

	/**
	 * Filters text from form text elements from elems_names List
	 * @author: UnderWordPressure
	 * @since 1.2.3
	 */
	function textTagsByNameValidationFilter( $result, $tag ) {

		$name  = $tag->name;
		$names = preg_split( '/[\s,]+/', get_option( 'kmcfmf_tags_by_name' ) );
		if ( in_array( $name, $names ) ) {
			$result = $this->textareaValidationFilter( $result, $tag );
		}

		return $result;

	}

	/**
	 * Filters text from textarea
	 * @since 1.0.0
	 */
	function textareaValidationFilter( $result, $tag ) {
		$name = $tag->name;

		$found     = false;
		$spam_word = '';

		$check_words = explode( ',', get_option( 'kmcfmf_restricted_words' ) );

		// we separate words with spaces and single words and treat them different.
		$check_words_with_spaces = array_filter( $check_words, function ( $word ) {
			return preg_match( "/\s+/", $word );
		} );
		$check_words             = array_values( array_diff( $check_words, $check_words_with_spaces ) );

		$message = isset( $_POST[ $name ] ) ? trim( (string) $_POST[ $name ] ) : '';

		// UnderWordPressue: make all lowercase - safe is safe
		$message = strtolower( $message );
		//$value = '';

		foreach ( $check_words_with_spaces as $check_word ) {
			if ( preg_match( "/\b" . $check_word . "\b/", $message ) ) {
				$found     = true;
				$spam_word = $check_word;
				break;
			}
		}
		// still not found a spam?, we continue with the check for single words
		if ( ! $found ) {
			// UnderWordPressue: Change explode(" ", $values) to preg_split([white-space]) -  reason: whole whitespace range are valid separators
			//                   and rewrite the foreach loops
			$values = preg_split( '/\s+/', $message );
			foreach ( $values as $value ) {
				$value = trim( $value );
				foreach ( $check_words as $check_word ) {

					/*if (preg_match("/^\.\w+/miu", $value) > 0) {
						$found = true;
					}else if (preg_match("/\b" . $check_word . "\b/miu", $value) > 0) {
						$found = true;
					}*/

					$check_word = strtolower( trim( $check_word ) );

					switch ( $check_word ) {
						case '':
							break;
						case '[russian]':
							$found = preg_match( '/[а-яА-Я]/miu', $value );
							break;
						case '[japanese]':
							// this blog post http://www.localizingjapan.com/blog/2012/01/20/regular-expressions-for-japanese-text/
							$character_sets = array(
								'hiragana',
								'katakana',
								'kanji',
								'kanji_radicals',
								'katakana_punctuation',
								'symbols_punctuations',
								'others'
							);
							$found          = $this->checkJapanese( $value, $character_sets );
							break;
						case '[link]':
							$pattern = '/((ftp|http|https):\/\/\w+)|(www\.\w+\.\w+)/ium'; // filters http://google.com and http://www.google.com and www.google.com
							$found   = preg_match( $pattern, $value );
							break;
						default:

							$like_start = ( preg_match( '/^\*/', $check_word ) );
							$like_end   = ( preg_match( '/\*$/', $check_word ) );

							# Remove leading and trailing asterisks from $check_word
							$regex_pattern = preg_quote( trim( $check_word, '*' ), '/' );

							if ( $like_start ) {
								$regex_pattern = '.*' . $regex_pattern;
							}
							if ( $like_end ) {
								$regex_pattern = $regex_pattern . '+.*';
							}
							if ( $like_end || $like_start ) {
								$found = preg_match( '/^' . $regex_pattern . '$/miu', $value );
							} else {
								$found = preg_match( '/\b' . $regex_pattern . '\b/miu', $value );
							}
							break;
					}


					if ( $found ) {
						$spam_word = $check_word;
						break 2; // stops the first foreach loop since we have already identified a spam word
					}
				}
			}
		} // end of foreach($values...)


		#####################
		# Final evaluation. #
		#####################

		// Spam word is recognized
		if ( $found ) {
			$result->invalidate( $tag, wpcf7_get_message( 'spam_word_error' ) );

			if ( ! $this->count_updated ) {
				MessagesModule::updateLog( $spam_word );
				$this->count_updated = true;
			}
		} else {

			// Check additional conditions on $message
			if ( empty( $message ) ) {
				// No content ($message) in a required Tag
				if ( $tag->is_required() ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
				}
			} else {

				$maxlength = $tag->get_maxlength_option();
				$minlength = $tag->get_minlength_option();

				if ( $maxlength && $minlength && $maxlength < $minlength ) {
					$maxlength = $minlength = null;
				}

				$code_units = wpcf7_count_code_units( stripslashes( $message ) );

				if ( $code_units ) {
					if ( $maxlength && $maxlength < $code_units ) {
						$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_long' ) );
					} elseif ( $minlength && $code_units < $minlength ) {
						$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_short' ) );
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Filters text from text input fields
	 * @since 1.0.0
	 */
	function textValidationFilter( $result, $tag ) {
		$name        = $tag->name;
		$check_words = strlen( trim( get_option( 'kmcfmf_restricted_emails' ) ) ) > 0 ? explode( ",", get_option( 'kmcfmf_restricted_emails' ) ) : [];

		$value = isset( $_POST[ $name ] )
			? trim( wp_unslash( strtr( (string) $_POST[ $name ], "\n", " " ) ) )
			: '';

		if ( 'text' == $tag->basetype ) {
			if ( $tag->is_required() && '' == $value ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
			}
		}

		if ( 'email' == $tag->basetype ) {
			if ( $tag->is_required() && '' == $value ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
			} elseif ( '' != $value && ! wpcf7_is_email( $value ) ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_email' ) );
			} else {
				foreach ( $check_words as $check_word ) {
					if ( preg_match( "/\b" . $check_word . "\b/", $value ) ) {
//                    if (strpos($value, $check_word) !== false) {
						$result->invalidate( $tag, wpcf7_get_message( 'spam_email_error' ) );

						if ( ! $this->count_updated ) {
							MessagesModule::updateLog( '' );
							$this->count_updated = true;
						}
					}
				}
			}
		}

		if ( 'url' == $tag->basetype ) {
			if ( $tag->is_required() and '' === $value ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
			} elseif ( '' !== $value and ! wpcf7_is_url( $value ) ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_url' ) );
			}
		}

		if ( 'tel' == $tag->basetype ) {
			if ( $tag->is_required() and '' === $value ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
			} elseif ( '' !== $value and ! wpcf7_is_tel( $value ) ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_tel' ) );
			}
		}

		if ( '' !== $value ) {
			$maxlength = $tag->get_maxlength_option();
			$minlength = $tag->get_minlength_option();

			if ( $maxlength && $minlength && $maxlength < $minlength ) {
				$maxlength = $minlength = null;
			}

			$code_units = wpcf7_count_code_units( stripslashes( $value ) );

			if ( false !== $code_units ) {
				if ( $maxlength && $maxlength < $code_units ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_long' ) );
				} elseif ( $minlength && $code_units < $minlength ) {
					$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_short' ) );
				}
			}
		}

		return $result;
	}
}
