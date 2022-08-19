<?php

namespace kmcf7_message_filter;

use KMSubMenuPage;
use WPCF7_Submission;

class MessagesModule extends Module {
	private static $log_file;

	public function __construct() {
		parent::__construct();
		$this->initUploadDir();
		$this->clearMessages();
		$this->transferOldData();
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
	 * Creates a directory in wordpress upload folder if it does not exist
	 * @since 1.2.5
	 */
	private function initUploadDir() {
		$logs_root = wp_upload_dir()['basedir'] . '/kmcf7mf_logs/';
		if ( ! is_dir( $logs_root ) ) {
			mkdir( $logs_root, 0700 );
		}
		self::$log_file = $logs_root . 'messages.txt';
		if ( ! is_file( self::$log_file ) ) {
			file_put_contents( self::$log_file, '{}' );
		}
	}


	/**
	 * Clears saved blocked messages
	 * @since 1.2.5.1
	 */
	private function clearMessages() {
		$clear_messages = get_option( 'kmcfmf_message_auto_delete_toggle' ) == 'on' ? true : false;
		if ( $clear_messages ) {
			$last_cleared_date = get_option( 'kmcfmf_last_cleared_date' );
			$frequency         = get_option( 'kmcfmf_message_auto_delete_duration' );
			$to_delete         = get_option( 'kmcfmf_message_auto_delete_amount' );
			if ( $last_cleared_date != '0' ) {
				$now  = strtotime( Date( "d F Y" ) );
				$diff = $now - $last_cleared_date;
				$diff = round( $diff / ( 60 * 60 * 24 ) );
				if ( $diff >= $frequency ) {
					// clear messages
					$log_messages = (array) json_decode( file_get_contents( self::$log_file ) );
					$log_messages = array_slice( $log_messages, $to_delete );
					$log_messages = json_encode( (object) $log_messages );
					file_put_contents( self::$log_file, $log_messages );
					update_option( 'kmcfmf_last_cleared_date', $now );
				}
			}
		}
	}


	/**
	 * Transfer data in old format to new format, when plugin is updated to from an older version to this version
	 * @since 1.2.0
	 */
	private function transferOldData() {
		if ( get_option( 'kmcfmf_messages' ) == '0' ) {
			// for those migrating from =<v1.2.4 to >=v1.2.5
			$old_logs_root = plugin_dir_path( dirname( __FILE__ ) ) . 'logs/';
			$old_logs_file = $old_logs_root . 'messages.txt';
			if ( is_file( $old_logs_file ) ) {
				rename( $old_logs_file, self::$log_file );
			}
			// from v1.2.5 to >= v1.3.0
			if ( get_option( 'kmcfmf_updated_to_1_3_0', 'no' ) == 'no' ) {
				$options_to_update = [ 'kmcfmf_restricted_words', 'kmcfmf_restricted_emails', 'kmcfmf_tags_by_name' ];
				foreach ( $options_to_update as $option ) {
					$words = get_option( $option );
					$words = trim( $words );
					$words = preg_replace( "/\s+/", ",", $words );
					$words = preg_replace( "/,+/", ",", $words );
					update_option( $option, $words );
				}
				update_option( 'kmcfmf_updated_to_1_3_0', 'yes' );
			}
		} else {
			// for those migrating from v1.1.x to >=v1.2.0
			$messages     = explode( "]kmcfmf_message[", get_option( 'kmcfmf_messages' ) );
			$log_messages = [];
			for ( $i = 0; $i < sizeof( $messages ); $i ++ ) {
				$data = explode( "kmcfmf_data=", $messages[ $i ] );
				if ( $data[1] != '' && $data[2] != '' && $data[3] != '' ) {
					$log_message = [ 'message' => $data[1], 'date' => $data[3], 'email' => $data[2] ];
					array_push( $log_messages, $log_message );

				}
			}
			$log_messages = json_encode( (object) $log_messages );
			file_put_contents( self::$log_file, $log_messages );

			update_option( 'kmcfmf_messages', 0 );

			// now update to the latest version
			$this->transferOldData();
		}
	}

	/**
	 * Gets blocked messages from log file
	 * @since 1.2.5.2
	 */
	private static function getMessages() {
		$log_file = self::getLogFilePath();
		if ( get_option( 'kmcfmf_message_storage_toggle' ) == 'on' ) {
			$messages = (array) json_decode( get_option( 'kmcfmf_blocked_messages', '{}' ) );
		} else {
			$messages = (array) json_decode( file_get_contents( $log_file ) );
		}

		return $messages;
	}

	/**
	 * @since v1.3.4
	 * Adds settings submenu page
	 */
	function addSubMenuPage( $sub_menu_pages ) {
		$dashboard_page = new KMSubMenuPage(
			array(
				'page_title' => 'Blocked Messages',
				'menu_title' => 'Blocked Messages',
				'capability' => 'manage_options',
				'menu_slug'  => 'kmcf7-filtered-messages',
				'function'   => array(
					$this,
					'messagesPageContent'
				)
			) );

		array_push( $sub_menu_pages, $dashboard_page );

		return $sub_menu_pages;
	}

	/**
	 * @since v1.3.4
	 * Displays content on dashboard sub menu page
	 */
	function messagesPageContent() {
		$this->renderContent( 'index' );
	}

	/**
	 * Logs messages blocked to the log file
	 * @since 1.2.0
	 */
	public static function updateLog( $spam ) {
		$submission   = WPCF7_Submission::get_instance();
		$contact_form = $submission->get_contact_form();
		// update_option('kmcfmf_last_message_blocked', '<td>' . Date('d-m-y h:ia') . ' </td><td>' . $email . '</td><td>' . $message . ' </td>');
		if ( get_option( 'kmcfmf_message_storage_toggle' ) == 'on' ) {
			$log_messages = (array) json_decode( get_option( 'kmcfmf_blocked_messages', '{}' ) );
			$log_message  = [
				'id'    => $contact_form->id(),
				'name'  => $contact_form->name(),
				'title' => $contact_form->f,
				'data'  => array_merge( $submission->get_posted_data(), array( 'date' => Date( 'd-m-y  h:ia' ) ) )
			];
			array_push( $log_messages, $log_message );

			$log_messages = json_encode( (object) $log_messages );
			update_option( 'kmcfmf_blocked_messages', $log_messages );
		} else {
			$log_messages = (array) json_decode( file_get_contents( self::$log_file ) );
			$log_message  = [
				'id'    => $contact_form->id(),
				'name'  => $contact_form->name(),
				'title' => $contact_form->title(),
				'data'  => array_merge( $submission->get_posted_data(), array( 'date' => Date( 'd-m-y  h:ia' ) ) )
			];
			array_push( $log_messages, $log_message );

			$log_messages = json_encode( (object) $log_messages );
			file_put_contents( self::$log_file, $log_messages );
		}
		update_option( 'kmcfmf_messages_blocked', get_option( 'kmcfmf_messages_blocked' ) + 1 );
		update_option( "kmcfmf_messages_blocked_today", get_option( "kmcfmf_messages_blocked_today" ) + 1 );
		$today                      = date( 'N' );
		$weekly_stats               = json_decode( get_option( 'kmcfmf_weekly_stats' ) );
		$weekly_stats[ $today - 1 ] = get_option( "kmcfmf_messages_blocked_today" );
		update_option( 'kmcfmf_weekly_stats', json_encode( $weekly_stats ) );

		if ( trim( $spam ) !== '' ) {
			$word_stats          = json_decode( get_option( 'kmcfmf_word_stats' ), true );
			$word_stats[ $spam ] = isset( $word_stats[ $spam ] ) ? ( (int) $word_stats[ $spam ] ) + 1 : 1;
			update_option( 'kmcfmf_word_stats', json_encode( $word_stats ) );
		}

		// debug purpose
		//$logs_root = wp_upload_dir()['basedir'] . '/kmcf7mf_logs/';
		//$submission = WPCF7_Submission::get_instance();
		//file_put_contents($logs_root . 'test.txt', json_encode($submission->get_posted_data()));
	}


	/**
	 * Returns the location to the log file
	 * @since v1.3.4
	 */
	public static function getLogFile() {
		return self::$log_file;
	}

	/**
	 * Returns the path to the log file
	 * @since 1.2.5.2
	 */
	public static function getLogFilePath() {
		return self::$log_file;
	}


	/**
	 * Gets all forms ids and titles
	 * @since 1.2.5.2
	 */
	public static function getForms() {
		$messages = self::getMessages();
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
	public static function getRows( $form_id = 0 ) {
		$messages = self::getMessages();
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
	public static function getColumns( $form_id = 0 ) {
		$messages = self::getMessages();
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