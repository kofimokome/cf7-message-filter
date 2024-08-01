<?php

namespace km_message_filter;

use Emoji;

class Filter {

	/**
	 * Runs spam filter on text fields and text area fields
	 * @since 1.4.0
	 */
	public function validateTextField( $message ) {
		$found = false;

		$spam_words_to_check = explode( ',', get_option( 'kmcfmf_restricted_words' ) );

		// we separate words with spaces and single words and treat them different.
		$check_words_with_spaces = array_filter( $spam_words_to_check, function ( $word ) {
			return preg_match( "/\s+/", $word );
		} );
		$spam_words_to_check     = array_values( array_diff( $spam_words_to_check, $check_words_with_spaces ) );


		// UnderWordPressue: make all lowercase - safe is safe
		$message = strtolower( $message );

		foreach ( $check_words_with_spaces as $spam_word_to_check ) {
			try {
				if ( $this->validateCustomFilters( $spam_word_to_check, $message ) ) {
					return $spam_word_to_check;
				}
			} catch ( \Exception $e ) {
				if ( $this->validateFilterModifiers( $spam_word_to_check, $message ) ) {
					return $spam_word_to_check;
				}
			}
		}
		// still not found a spam?, we continue with the check for single words
		// UnderWordPressue: Change explode(" ", $values) to preg_split([white-space]) -  reason: whole whitespace range are valid separators
		//                   and rewrite the foreach loops
		$words = preg_split( '/\s+/', $message );
		foreach ( $words as $word ) {
			$word = trim( $word );
			foreach ( $spam_words_to_check as $spam_word_to_check ) {

				/*if (preg_match("/^\.\w+/miu", $word) > 0) {
					$found = true;
				}else if (preg_match("/\b" . $spam_word_to_check . "\b/miu", $word) > 0) {
					$found = true;
				}*/

				$spam_word_to_check         = trim( $spam_word_to_check );
				$spam_word_to_check_lowered = strtolower( $spam_word_to_check );
				$found                      = false;
				try {
					$found = $this->validateCustomFilters( $spam_word_to_check_lowered, $word );
				} catch ( \Exception $e ) {
					$like_start = ( preg_match( '/^\*/', $spam_word_to_check_lowered ) );
					$like_end   = ( preg_match( '/\*$/', $spam_word_to_check_lowered ) );

					# Remove leading and trailing asterisks from $spam_word_to_check
					$regex_pattern = preg_quote( trim( $spam_word_to_check_lowered, '*' ), '/' );

					if ( $like_start ) {
						$regex_pattern = '.*' . $regex_pattern;
					}
					if ( $like_end ) {
						$regex_pattern = $regex_pattern . '+.*';
					}
					if ( $like_end || $like_start ) {
						$found = preg_match( '/^' . $regex_pattern . '$/miu', $word );
					} else {
						if ( $this->hasEmoji( $spam_word_to_check ) ) { // if the check word is an emoji
							$emoji           = $spam_word_to_check;
							$advanced_filter = explode( ":", $spam_word_to_check );
							if ( sizeof( $advanced_filter ) > 1 ) {
								$emoji = $advanced_filter[1];
							}
							$found = strpos( $message, $emoji ) !== false;
						} else {
//								$found = preg_match( '/\b' . $regex_pattern . '\b/miu', $word );
							$found = $this->validateFilterModifiers( $spam_word_to_check, $word );
						}
					}
				}

				if ( $found ) {
					return $spam_word_to_check;
				}
			} // end of foreach($checkwords)
		}// end of foreach($values...)


		#####################
		# Final evaluation. #
		#####################
		return false;
	}

	/**
	 *  Checks if a spam word is a custom filter
	 * @throws \Exception
	 * @since v1.6.0
	 */
	private function validateCustomFilters( string $filter, string $message ): bool {
		$found = false;

		// check if this is a custom filter
		if ( preg_match( '/^\[([^\[\]]*)\]$/u', $filter, $matches ) ) {
			$short_code = trim( $matches[1] );

			// extract the filter form the shortcode
			$filter = explode( " ", $short_code )[0];

			// check if the filter has parameters
			$parameters = [];
			preg_match_all( '/\w+\s*=\s*(' . "'" . '|")?\w+(' . "'" . '|")?/', $short_code, $parameters );
			if ( sizeof( $parameters ) > 0 ) {
				$parameters     = $parameters[0];
				$new_parameters = [];
				foreach ( $parameters as $parameter ) {
					$parameter                         = explode( "=", $parameter );
					$parameter_name                    = trim( $parameter[0] );
					$parameter_value                   = str_replace( '"', "", $parameter[1] );
					$parameter_value                   = str_replace( "'", "", $parameter_value );
					$parameter_value                   = trim( $parameter_value );
					$new_parameters[ $parameter_name ] = $parameter_value;
				}
				$parameters = $new_parameters;
			} else {
				$parameters = [];
			}

			switch ( $filter ) {
				case '':
					break;
				case 'russian':
					$found = preg_match( '/[а-яА-Я]/miu', $message );
					break;
				case 'hiragana':
					$character_sets = array(
						'hiragana'
					);
					$found          = $this->checkJapanese( $message, $character_sets );
					break;
				case 'katakana':
					$character_sets = array(
						'katakana',
						'katakana_punctuation',
					);
					$found          = $this->checkJapanese( $message, $character_sets );
					break;
				case 'kanji':
					$character_sets = array(
						'kanji',
						'kanji_radicals',
					);
					$found          = $this->checkJapanese( $message, $character_sets );
					break;
				case 'japanese':
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
					$found          = $this->checkJapanese( $message, $character_sets );

					break;
				case 'link':
					$pattern = '/((ftp|http|https):\/\/\w+)|(www\.\w+\.\w+)/ium'; // filters http://google.com and http://www.google.com and www.google.com
					$found   = preg_match( $pattern, $message );
					break;
				case 'emoji':
					$found = $this->hasEmoji( $message );
					break;
				default:
					// find the filter in the database
					$filter = MyFilter::where( 'short_code', '=', $filter )->first();

					if ( $filter ) {
						$expression = $filter->expression;

						// replace the variables in the expression with the parameters
						foreach ( $parameters as $parameter => $value ) {
							$expression = preg_replace( '/{{\s*' . $parameter . '\s*}}/miu', $value, $expression );
//							$expression = str_replace( "{{" . $parameter . "}}", $value, $expression );
						}

						$found = preg_match( '/' . $expression . '/miu', $message );
					} else {
						throw new \Exception( 'Not a valid filter' );
					}

					break;
			}

			return $found;
		}
		throw new \Exception( 'Not a valid filter' );
	}

	/**
	 */
	private function checkJapanese( $word, $character_sets = array() ) {
		$found = false;

		foreach ( $character_sets as $character_set ) {

			switch ( $character_set ) {
				case 'hiragana':
					$found = preg_match( '/[\x{3041}-\x{3096}]/ium', $word );
					break;

				case 'katakana':
					$found = preg_match( '/[\x{30A0}-\x{30FF}]/ium', $word );
					break;

				case 'kanji':
					$found = preg_match( '/[\x{3400}-\x{4DB5}\x{4E00}-\x{9FCB}\x{F900}-\x{FA6A}]/ium', $word );
					break;

				case 'kanji_radicals':
					$found = preg_match( '/[\x{2E80}-\x{2FD5}]/ium', $word );
					break;

				case 'katakana_punctuation':
					$found = preg_match( '/[\x{FF5F}-\x{FF9F}]/ium', $word );
					break;

				case 'symbols_punctuations':
					$found = preg_match( '/[\x{3000}-\x{303F}]/ium', $word );
					break;

				case 'others':
					$found = preg_match( '/[\x{31F0}-\x{31FF}\x{3220}-\x{3243}\x{3280}-\x{337F}]/ium', $word );
					break;
			}

			if ( $found ) {
				break 1;
			}
		}

		return $found;
	}

	/**
	 * Checks if text has an emoji
	 * @since v1.3.6
	 */
	private function hasEmoji( $emoji ) {
		$result = Emoji\detect_emoji( $emoji );

		if ( sizeof( $result ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/*
	 * @param $spam_word_to_check
	 * @param $word
	 *
	 * @since 1.6.0
	 * Checks if the spam word has a modifier before validating against the word
	 */

	private function validateFilterModifiers( $spam_word_to_check, $word ) {
		// UnderWordPressue: make all lowercase - safe is safe
		$word = strtolower( $word );
//		$word               = str_replace( "\'", "'", $word );
//		$word               = str_replace( '\"', '"', $word );
		$word      = stripslashes( $word );
		$modifiers = explode( ":", $spam_word_to_check );
		if ( sizeof( $modifiers ) > 1 ) {
			switch ( $modifiers[0] ) {
				case 'startsWith':
					// check for the word that starts with the spam word
					$spam_word_to_check = strtolower( trim( $modifiers[1] ) );
					//todo: revise this to use word boundaries
					$match = preg_match( "/^" . $spam_word_to_check . "/mui", $word );

					break;
				case 'startsWithExcluding':
					// check for the word that starts with the spam word, excluding the spam word
					$spam_word_to_check = strtolower( trim( $modifiers[1] ) );
					// todo: revise this to use word bondaries
					$match = preg_match( "/^" . $spam_word_to_check . "\S+/mui", $word );

					break;
				case 'endsWith':
					// check for the word that ends with the spam word
					$spam_word_to_check = strtolower( trim( $modifiers[1] ) );
					// todo: Revise this to use word boundaries
					$match = preg_match( "/" . $spam_word_to_check . "$/miu", $word );

					break;
				case 'endsWithExcluding':
					// check for the word that ends with the spam word, excluding the spam word
					$spam_word_to_check = strtolower( trim( $modifiers[1] ) );
					$match              = preg_match( "/\S+" . $spam_word_to_check . "$/miu", $word );

					break;
				case 'contains':
					// check for the word that contains the spam word
					$spam_word_to_check = strtolower( trim( $modifiers[1] ) );
					$match              = preg_match( "/" . $spam_word_to_check . "/miu", $word );
					// $match              = preg_match( "/\b\w*" . $spam_word_to_check . "\w*\b/miu", $word );
					break;
				case 'containsExcluding':
					// check for the word that contains the spam word, excluding the spam word
					$spam_word_to_check = strtolower( trim( $modifiers[1] ) );
					$match              = preg_match( "/(\b\w*" . $spam_word_to_check . "\w+\b)|(\b\w+" . $spam_word_to_check . "\w*\b)/miu", $word );
					break;
				case 'containsExcludingEnd':
					// check for the word that contains the spam word, excluding cases where the word ends with the spam word
					$spam_word_to_check = strtolower( trim( $modifiers[1] ) );
					$match              = preg_match( "/\b\w*" . $spam_word_to_check . "\w+\b/miu", $word );

					break;
				case 'containsExcludingStart':
					// check for the word that contains the spam word, excluding cases where the word starts with the spam word
					$spam_word_to_check = strtolower( trim( $modifiers[1] ) );
					$match              = preg_match( "/\b\w+" . $spam_word_to_check . "\w*\b/miu", $word );
					break;
				default:
					// no advanced filter used, check spam word as is
					$spam_word_to_check = preg_quote( strtolower( trim( $spam_word_to_check ) ) );
					$match              = preg_match( "/\b" . $spam_word_to_check . "\b/mui", $word );
					break;
			}
		} else {
			$spam_word_to_check = preg_quote( strtolower( trim( $spam_word_to_check ) ) );
			$match              = preg_match( "/\b" . $spam_word_to_check . "\b/mui", $word );
		}


		return $match;

	}

	/**
	 * Runs spam filter on email fields
	 * @since v1.4.0
	 */
	public function validateEmail( $word ) {
		$spam_words_to_check = strlen( trim( get_option( 'kmcfmf_restricted_emails' ) ) ) > 0 ? explode( ",", get_option( 'kmcfmf_restricted_emails' ) ) : [];

		foreach ( $spam_words_to_check as $spam_word_to_check ) {
			try {
				if ( $this->validateCustomFilters( $spam_word_to_check, $word ) ) {
					return $spam_word_to_check;
				}
			} catch ( \Exception $e ) {
				if ( $this->validateFilterModifiers( $spam_word_to_check, $word ) ) {
					return $spam_word_to_check;
				}
			}
		}

		return false;
	}
}
