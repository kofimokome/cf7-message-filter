<?php
/**
 * Created by PhpStorm.
 * User: kofi
 * Date: 6/5/19
 * Time: 12:41 PM
 */

namespace kmcf7_message_filter;

class BlockedMessage {

	/**
	 * Gets blocked messages from log file
	 * @since 1.2.5.2
	 */
	private static function get_messages() {
		$log_file = CF7MessageFilter::get_log_file_path();
		if ( get_option( 'kmcfmf_message_storage_toggle' ) == 'on' ) {
			$messages = (array) json_decode( get_option( 'kmcfmf_blocked_messages', '{}' ) );
		} else {
			$messages = (array) json_decode( file_get_contents( $log_file ) );
		}

		return $messages;
	}

	/**
	 * Gets all forms ids and titles
	 * @since 1.2.5.2
	 */
	public static function get_forms() {
		$messages = self::get_messages();
		$forms    = array();
		foreach ( $messages as $message ) {
			if ( property_exists( $message, 'id' ) ) {
				if ( sizeof( $form = \WPCF7_ContactForm::find( array( 'p' => $message->id ) ) ) > 0 ) {
					array_push( $forms, [ $form[0]->title(), $message->id ] );
				} else {
					array_push( $forms, [ $message->title, $message->id ] );
				}
			} else {
				array_push( $forms, [ 'uncategorized', 0 ] );
			}
		}

		return array_unique( $forms, SORT_REGULAR );

	}

	/**
	 * Gets all rows for a particular form
	 * @since 1.2.5.2
	 */
	public static function get_rows( $form_id = 0 ) {
		$messages = self::get_messages();
		$rows     = array();

		$messages = array_filter( $messages, function ( $val ) use ( $form_id ) {
			if ( property_exists( $val, 'id' ) ) {
				return $val->id == $form_id;
			}

			return $form_id == 0;
		} );
		foreach ( $messages as $message ) {
			$rows = array_merge( $rows, array_keys( get_object_vars( $form_id == 0 ? $message : $message->data ) ) );
		}

		return array_unique( $rows, SORT_REGULAR );
	}

	/**
	 * Gets all columns for a particular form
	 * @since 1.2.5.2
	 */
	public static function get_columns( $form_id = 0 ) {
		$messages = self::get_messages();
		$columns  = array();
		$messages = array_filter( $messages, function ( $val ) use ( $form_id ) {
			if ( property_exists( $val, 'id' ) ) {
				return $val->id == $form_id;
			}

			return $form_id == 0;
		} );
		foreach ( $messages as $message ) {
			array_push( $columns, $form_id == 0 ? $message : $message->data );
		}

		return $columns;
	}

}