<?php

namespace km_message_filter;

use KMSubMenuPage;
use WPCF7_ContactForm;
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
	 * Creates a directory in WordPress upload folder if it does not exist
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
					$messages = Message::paginate( $to_delete )->get();
					foreach ( $messages['data'] as $message ) {
						$message->delete();
					}
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

			// from <=v1.3.0 to >= v1.4.0
			if ( get_option( 'kmcfmf_updated_to_1_4_0', 'no' ) == 'no' ) {
				update_option( 'kmcfmf_contact_form_7_email_fields', '*' );
				update_option( 'kmcfmf_tags_by_name', '*' );
				update_option( 'kmcfmf_contact_form_7_textarea_fields', '*' );
				update_option( 'kmcfmf_enable_contact_form_7_toggle', 'on' );
				delete_option( 'kmcfmf_tags_by_name_filter_toggle' );
				update_option( 'kmcfmf_updated_to_1_4_0', 'yes' );
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
	 * Logs messages blocked to the database
	 * @since 1.4.0
	 */
	public static function updateDatabase( $data ) {


		$message               = new Message();
		$message->contact_form = $data['form'];
		$message->form_id      = $data['form_id'];
		$message->message      = $data['message'];
		$message->save();

		update_option( 'kmcfmf_messages_blocked', get_option( 'kmcfmf_messages_blocked' ) + 1 );
		update_option( "kmcfmf_messages_blocked_today", get_option( "kmcfmf_messages_blocked_today" ) + 1 );
		$today                      = date( 'N' );
		$weekly_stats               = json_decode( get_option( 'kmcfmf_weekly_stats' ) );
		$weekly_stats[ $today - 1 ] = get_option( "kmcfmf_messages_blocked_today" );
		update_option( 'kmcfmf_weekly_stats', json_encode( $weekly_stats ) );

		if ( trim( $data['spam'] ) !== '' ) {
			$word_stats                  = json_decode( get_option( 'kmcfmf_word_stats' ), true );
			$word_stats[ $data['spam'] ] = isset( $word_stats[ $data['spam'] ] ) ? ( (int) $word_stats[ $data['spam'] ] ) + 1 : 1;
			update_option( 'kmcfmf_word_stats', json_encode( $word_stats ) );
		}
	}

	/**
	 * Returns the location to the log file
	 * @since v1.3.4
	 */
	public static function getLogFile() {
		return self::$log_file;
	}

	/**
	 * Gets all forms ids and titles
	 * @since 1.2.5.2
	 */
	public static function getForms() {
		$result = array();
		if ( class_exists( 'WPCF7_ContactForm' ) ) {
			$cf7_forms = WPCF7_ContactForm::find();
			foreach ( $cf7_forms as $form ) {
				array_push( $result, array( '[CF7] ' . $form->title(), 'cf7-' . $form->id() ) );
			}
		}
		if ( function_exists( 'wpforms' ) ) {
			$args['post_status'] = 'publish';
			$wp_forms            = wpforms()->get( 'form' )->get( '', $args );
			foreach ( $wp_forms as $form ) {
				array_push( $result, array( '[WPForms] ' . $form->post_title, 'wpforms-' . $form->ID ) );
			}
		}

		return $result;

	}

	/**
	 * Gets all rows for a particular form
	 * @since 1.2.5.2
	 * @deprecated
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
	 * Returns the path to the log file
	 * @since 1.2.5.2
	 */
	public static function getLogFilePath() {
		return self::$log_file;
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
				'position'   => 1,
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
		if ( isset( $_GET['old'] ) ) {
			$this->renderContent( 'old' );
		} else if ( isset( $_GET['message_id'] ) ) {
			$this->renderContent( 'message' );
		} else {
			$this->renderContent( 'new' );
		}
	}

	/**
	 * @since 1.4.0
	 * Gets messages from the database
	 */
	public function serverMessages() {
		$link_to_messages = admin_url( 'admin.php' ) . '?page=kmcf7-filtered-messages';
		$form_id          = sanitize_text_field( $_REQUEST['form_id'] );
		$contact_form     = sanitize_text_field( $_REQUEST['contact_form'] );
		$draw             = intval( sanitize_text_field( $_REQUEST['draw'] ) );
		$length           = sanitize_text_field( $_REQUEST['length'] );
		$start            = sanitize_text_field( $_REQUEST['start'] );
		$search           = $_REQUEST['search'];
		$search_value     = sanitize_text_field( $search['value'] );
		$search_value     = trim( $search_value );
		$current_page     = ( $start / $length ) + 1;
		$results          = Message::where( 'contact_form', '=', $contact_form )->andWhere( 'message', 'LIKE', "%{$search_value}%" )->andWhere( 'form_id', '=', $form_id )->orderBy( 'id', 'desc' )->paginate( $length, $current_page )->get();;
		$size     = $results['totalItems'];
		$results  = $results['data'];
		$messages = array();
		$rows     = self::getRows2( $form_id, $contact_form );
		foreach ( $results as $result ) {
			$decoded_message = json_decode( $result->message );
			$message         = array(
				"",
				"<a href='{$link_to_messages}&message_id={$result->id}' class='btn btn-sm btn-primary'>View</a> <button class='btn btn-sm btn-primary' onclick='showResubmitModal({$result->id})'>Restore</button>",
				intval( $result->id )
			);
			foreach ( $rows as $row ) {
				if ( property_exists( $decoded_message, $row ) ) {
					$content  = esc_html( self::decodeUnicodeVars( $decoded_message->$row ) );
					$ellipses = strlen( $content ) > 50 ? "..." : '.';
					array_push( $message, substr( $content, 0, 50 ) . $ellipses );
				} else {
					array_push( $message, " " );
				}
			}

			array_push( $messages, $message );
		}
		$data = [
			"draw"            => $draw,
			"recordsTotal"    => $size,
			"recordsFiltered" => $size,
			"data"            => $messages,
			"defaultContent"  => '',
			"orderable"       => false,
			"className"       => 'select-checkbox'
		];

		wp_send_json( $data );

		wp_die();
	}

	/**
	 * New version of getRows()
	 * @since 1.4.0
	 */
	public static function getRows2( $form_id, $contact_form ) {
		$rows = array();
		switch ( $contact_form ) {
			case 'cf7':
				if ( class_exists( 'WPCF7_ContactForm' ) ) {
					$form = WPCF7_ContactForm::get_instance( $form_id );
					$tags = $form->scan_form_tags();
					foreach ( $tags as $tag ) {
						array_push( $rows, $tag->name );
					}
				}
				break;
			case 'wpforms':
				if ( function_exists( 'wpforms' ) ) {
					$form = wpforms()->get( 'form' )->get( $form_id );

					$content = json_decode( $form->post_content, true );
					$fields  = $content['fields'];

					foreach ( $fields as $field ) {
						array_push( $rows, $field['label'] );
					}
				}
				break;
		}

		return array_unique( $rows, SORT_REGULAR );
	}

	/**
	 * @since 1.4.0
	 * Decode unicode variables in string
	 */
	static function decodeUnicodeVars( $message ) {
		$message = is_array( $message ) ? implode( " ", $message ) : $message;

		return mb_convert_encoding( $message, 'UTF-8',
			mb_detect_encoding( $message, 'UTF-8, ISO-8859-1', true ) );
	}

	/**
	 * @since 1.4.0
	 * Deletes a message from the database
	 */
	public function deleteMessage() {

		$validator = Validator::make(
			array(
				'message_ids' => 'required'
			),
			$_POST
		);

		if ( $validator->validate() ) {
			$message_ids = sanitize_text_field( $_POST['message_ids'] );
			$message_ids = explode( ',', $message_ids );
			foreach ( $message_ids as $message_id ) {
				$message_id = intval( $message_id );
				$message    = Message::find( $message_id );
				if ( ! $message->delete() ) {
					wp_send_json_error( __( "We could not find this message", KMCF7MS_TEXT_DOMAIN ), 400 );
				}
			}
			wp_send_json_success( __( "Message(s) deleted", KMCF7MS_TEXT_DOMAIN ) );

		} else {
			wp_send_json_error( __( "An error occurred. Please try again", KMCF7MS_TEXT_DOMAIN ), 400 );
		}
	}

	/**
	 * @since v1.4.4
	 * Resubmits a blocked message
	 */
	public function resubmitMessage() {
		$validator = Validator::make(
			array(
				'message_ids' => 'required'
			),
			$_POST
		);

		if ( $validator->validate() ) {
			$message_ids = sanitize_text_field( $_POST['message_ids'] );
			$message_ids = explode( ',', $message_ids );
			KMCFMessageFilter::skipValidation( true );

			foreach ( $message_ids as $message_id ) {
				$message = Message::find( $message_id );
				if ( $message ) {
					$contact_form = $message->contact_form;
					if ( $contact_form == 'cf7' ) {
						$decoded_message = json_decode( $message->message );
						foreach ( $decoded_message as $key => $value ) {
							$_POST[ $key ] = $value;
						}

						$contact_form = WPCF7_ContactForm::get_instance( $message->form_id );
						$args         = array(
							'skip_mail' =>
								( $contact_form->in_demo_mode()
								  || $contact_form->is_true( 'skip_mail' )
								  || ! empty( $contact_form->skip_mail ) ),
						);
						$submission   = WPCF7_Submission::get_instance( $contact_form, $args );
						$result       = $submission->get_result();
//					$contact_form->submit();
						if ( $result['status'] != 'mail_sent' ) {
							wp_send_json_error( __( $result, KMCF7MS_TEXT_DOMAIN ), 400 );
						}
						$message_id = intval( $message_id );
						$message    = Message::find( $message_id );
						$message->delete();

					} else {
						wp_send_json_error( __( "Feature only available for Contact Form 7", KMCF7MS_TEXT_DOMAIN ), 400 );
					}
				} else {
					wp_send_json_error( __( "We could not find this message", KMCF7MS_TEXT_DOMAIN ), 400 );
				}
			}
			wp_send_json_success( __( "Message(s) resubmitted successfully", KMCF7MS_TEXT_DOMAIN ), 200 );
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

	protected function addActions() {
		parent::addActions();
		add_action( 'wp_ajax_kmcf7_messages', [ $this, 'serverMessages' ] );
		add_action( 'wp_ajax_kmcf7_delete_message', [ $this, 'deleteMessage' ] );
		add_action( 'wp_ajax_kmcf7_resubmit_message', [ $this, 'resubmitMessage' ] );
	}

}