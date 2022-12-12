<?php

namespace km_message_filter;

class Validator {
	private static $rules = array();
	private static $data = array();

	/**
	 * @return Validator
	 * Creates a validator request
	 * Rules: required, bool, int, numeric, pdf
	 * @since  v1.0.0
	 */
	public static function make( $rules, $data ) {
		self::$rules = $rules;
		self::$data  = $data;

		return new static();
	}

	/**
	 * @return bool
	 * Validates request
	 * Rules: required, bool, int, numeric, pdf
	 * @since  v1.4.0
	 */
	public static function validate( $rules = array(), $data = array() ) {

		if ( sizeof( $rules ) == 0 ) {
			$rules = self::$rules;
		}
		if ( sizeof( $data ) == 0 ) {
			$data = self::$data;
		}
		foreach ( $rules as $field => $rule ) {
			$rules_to_check = explode( '|', $rule );
			$rules_to_check = array_filter( $rules_to_check, function ( $a ) {
				return strlen( $a ) > 0;
			} );
			foreach ( $rules_to_check as $rule_to_check ) {
				switch ( $rule_to_check ) {
					case 'required':
						if ( ! isset( $data[ $field ] ) || ( isset( $data[ $field ] ) && ( ( ! is_array( $data[ $field ] ) && trim( $data[ $field ] ) == '' ) || ( is_array( $data[ $field ] ) && sizeof( $data[ $field ] ) == 0 ) ) ) ) {
							wp_send_json_error( $field . ' is required', 400 );

							return false;
						}
//						$data[ $field ] = trim( $data[ $field ] );
						break;
					case 'int':
						if ( isset( $data[ $field ] ) && ! is_integer( $data[ $field ] ) ) {
							wp_send_json_error( $field . ' must be an integer', 400 );

							return false;
						}
//						$data[ $field ] = intval( $data[ $field ] );
						break;
					case 'numeric':
						if ( isset( $data[ $field ] ) && ! is_numeric( $data[ $field ] ) ) {
							wp_send_json_error( $field . ' must be a numeric value', 400 );

							return false;
						}

						break;

					case 'bool':
						if ( isset( $data[ $field ] ) && ( ! is_bool( $data[ $field ] ) && ( $data[ $field ] != 'true' && $data[ $field ] != 'false' ) ) ) {
							wp_send_json_error( $field . ' must be a boolean value', 400 );

							return false;
						}
						/*if ( $data[ $field ] == 'true' ) {
							$data[ $field ] = 1;
						} elseif ( $data[ $field ] == 'false' ) {
							$data[ $field ] = 0;
						} else {
							$data[ $field ] = (bool) $data[ $field ];
						}*/
						break;

					case 'pdf':
						if ( isset( $data[ $field ] ) ) {
							if ( self::isFile( $data[ $field ] ) ) {
								if ( $data[ $field ]['type'] != 'application/pdf' ) {
									wp_send_json_error( $field . ' must be a pdf file', 400 );

									return false;
								}
							} else {
								wp_send_json_error( $field . ' must be a file', 400 );

								return false;
							}
						}
						break;
				}
			}
		}

//		return $data;
		self::$rules = array();
		self::$data  = array();

		return true;
	}

	private static function isFile( $data ) {
		if ( is_array( $data ) && isset( $data['type'] ) && isset( $data['name'] ) && isset( $data['size'] ) && isset( $data['tmp_name'] ) ) {
			return true;
		}

		return false;
	}
}