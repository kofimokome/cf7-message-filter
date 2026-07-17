<?php

namespace km_message_filter;

use KMSubMenuPage;
use KMValidator;
use WPCF7_ContactForm;
use WPCF7_Submission;
use WPTools;

class MessagesModule extends Module {
	private static $instance;
	private $wp_tools;

	public function __construct() {
		parent::__construct();
		$this->clearMessages();
		$this->transferOldData();
//		$this->module = 'packages';
		self::$instance = $this;
		$this->wp_tools = WPTools::getInstance( __FILE__ );
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
				$now  = strtotime( gmdate( "d F Y" ) );
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
			//todo: remove the else

			update_option( 'kmcfmf_messages', 0 );

			// now update to the latest version
			$this->transferOldData();
		}
	}

	/**
	 * @return MessagesModule
	 * @since v1.4.9
	 * @author kofimokome
	 */
	public static function getInstance(): MessagesModule {
		return self::$instance;
	}

	/**
	 * Logs messages blocked to the database
	 * @since 1.4.0
	 */
	public function updateDatabase( $data ) {

		$defaults              = [ 'word' => '', 'email' => '' ];
		$data                  = array_merge( $defaults, $data );
		$message               = new Message();
		$message->contact_form = $data['form'];
		$message->form_id      = $data['form_id'];
		$message->message      = $data['message'];
		$message->save();

		update_option( 'kmcfmf_messages_blocked', get_option( 'kmcfmf_messages_blocked' ) + 1 );
		update_option( "kmcfmf_messages_blocked_today", get_option( "kmcfmf_messages_blocked_today" ) + 1 );
		update_option( "kmcfmf_messages_blocked_today_tmp", get_option( "kmcfmf_messages_blocked_today_tmp" ) + 1 );

		$today                      = gmdate( 'N' );
		$weekly_stats               = json_decode( get_option( 'kmcfmf_weekly_stats' ), true );
		$weekly_stats[ $today - 1 ] = get_option( "kmcfmf_messages_blocked_today" );
		update_option( 'kmcfmf_weekly_stats', wp_json_encode( $weekly_stats ) );

		if ( trim( $data['word'] ) !== '' ) {
			$word_stats                  = json_decode( get_option( 'kmcfmf_word_stats' ), true );
			$word_stats[ $data['word'] ] = isset( $word_stats[ $data['word'] ] ) ? ( (int) $word_stats[ $data['word'] ] ) + 1 : 1;
			update_option( 'kmcfmf_word_stats', wp_json_encode( $word_stats ) );
		}

		if ( trim( $data['email'] ) !== '' ) {
			$email_stats                   = json_decode( get_option( 'kmcfmf_email_stats' ), true );
			$email_stats[ $data['email'] ] = isset( $email_stats[ $data['email'] ] ) ? ( (int) $email_stats[ $data['email'] ] ) + 1 : 1;
			update_option( 'kmcfmf_email_stats', wp_json_encode( $email_stats ) );
		}
	}

	/**
	 * Gets all forms ids and titles
	 * @since 1.2.5.2
	 */
	public function getForms() {
		$result = array();
		if ( class_exists( 'WPCF7_ContactForm' ) ) {
			$forms     = array();
			$cf7_forms = WPCF7_ContactForm::find();
			foreach ( $cf7_forms as $form ) {
				array_push( $forms, array( "name" => $form->title(), "id" => $form->id() ) );
			}
			$result['cf7'] = array( 'name' => "Contact Form 7", "forms" => $forms );
		}
		if ( function_exists( 'wpforms' ) ) {
			$forms               = array();
			$args['post_status'] = 'publish';
			$wp_forms            = wpforms()->obj( 'form' )->get( '', $args );
			if ( $wp_forms ) {
				foreach ( $wp_forms as $form ) {
					array_push( $forms, array( "name" => $form->post_title, "id" => $form->ID ) );
				}
			}
			$result['wpforms'] = array( "name" => "WP Forms", "forms" => $forms );
		}

		return $result;
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
		if ( isset( $_GET['message_id'] ) ) {
			$this->wp_tools->renderView( 'messages.message' );
		} else {
			$this->wp_tools->renderView( 'messages.list' );
		}
	}

	/**
	 * @since 1.4.0
	 * Gets messages from the database
	 */
	public function serverMessages() {
		try {
			if ( ! current_user_can( 'manage_options' ) ) {
				throw new \Exception( __( 'You do not have permission to perform this action', KMCFMF_TEXT_DOMAIN ) );
			}
			$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'kmcfmf_can_get_blocked_messages' ) ) {
				throw new \Exception( __( 'Invalid nonce', KMCFMF_TEXT_DOMAIN ) );
			}
			$link_to_messages = admin_url( 'admin.php' ) . '?page=kmcf7-filtered-messages';
			$form_id          = isset( $_REQUEST['form_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['form_id'] ) ) : '';
			$contact_form     = isset( $_REQUEST['contact_form'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['contact_form'] ) ) : '';
			$draw             = isset( $_REQUEST['draw'] ) ? intval( sanitize_text_field( wp_unslash( $_REQUEST['draw'] ) ) ) : '';
			$length           = isset( $_REQUEST['length'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['length'] ) ) : '';
			$start            = isset( $_REQUEST['start'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start'] ) ) : '';
			$search           = isset( $_REQUEST['search'] ) ? rest_sanitize_array( wp_unslash( $_REQUEST['search'] ) ) : [];
			$search_value     = sanitize_text_field( wp_unslash( $search[0] ) );
			$search_value     = trim( $search_value );
			$current_page     = ( $start / $length ) + 1;
			$form_columns     = sanitize_text_field( wp_unslash( $_REQUEST['form_columns'] ) );
			$results          = Message::where( 'message', 'LIKE', "%{$search_value}%" )->orderBy( 'id', 'desc' )->paginate( $length, $current_page );
			if ( $contact_form == 'all' ) {
				$form_id = 'all';
			} else {
				$results = $results->andWhere( 'contact_form', '=', $contact_form );
			}
			if ( $form_id != 'all' ) {
				$results = $results->andWhere( 'form_id', '=', $form_id );
			}
			$results  = $results->get();
			$size     = $results['totalItems'];
			$results  = $results['data'];
			$messages = array();

			// todo: Investigate why this function returns two different results for some contact forms on the frontend and here
//			$rows = $this->getColumns2( $form_id, $contact_form );
			$rows = json_decode( $form_columns );
			foreach ( $results as $result ) {
				$decoded_message = json_decode( $result->message );
				$message         = array(
					"",
					"<a href='{$link_to_messages}&message_id={$result->id}' class='btn btn-sm btn-primary'>" . __( "View", KMCFMF_TEXT_DOMAIN ) . "</a> <button class='btn btn-sm btn-primary' onclick='showResubmitModal({$result->id})'>" . __( "Restore", KMCFMF_TEXT_DOMAIN ) . "</button>",
					intval( $result->id )
				);
				if ( $contact_form == 'all' || $form_id == 'all' ) {
					$message[] = $this->getFormName( $result->form_id, $result->contact_form );
				}
				foreach ( $rows as $row ) {
					if ( $row == 'Contact Form' ) {
						continue;
					}
					if ( property_exists( $decoded_message, $row ) ) {
						$content   = esc_html( self::decodeUnicodeVars( $decoded_message->$row ) );
						$ellipses  = strlen( $content ) > 50 ? "..." : '.';
						$message[] = substr( $content, 0, 50 ) . $ellipses;
					} else {
						$message[] = " ";
					}

				}

				$messages[] = $message;
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
		} catch ( \Throwable $e ) {
			wp_send_json_error( $e->getMessage(), 500 );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage(), 500 );
		}
		wp_die();
	}

	/**
	 * @since 1.6.3
	 * Get the name of a form from its ID
	 */
	private function getFormName( $form_id, $contact_form ) {
		switch ( $contact_form ) {
			case 'cf7':
				if ( class_exists( 'WPCF7_ContactForm' ) ) {
					$form = WPCF7_ContactForm::get_instance( $form_id );

					return $form->title();
				}

				return "";
			case 'wpforms':
				if ( function_exists( 'wpforms' ) ) {
					$form = wpforms()->get( 'form' )->get( $form_id );

					return $form->post_title;
				}

				return "";
			default:
				return '';
		}
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

		$validator = KMValidator::make(
			array(
				'message_ids' => 'required',
				"_wpnonce"    => 'required',
			),
			$_REQUEST
		);
		if ( current_user_can( 'manage_options' ) ) {
			if ( $validated_data = $validator->validate() ) {
				$nonce = sanitize_text_field( wp_unslash( $validated_data['_wpnonce'] ) );
				if ( wp_verify_nonce( $nonce, 'kmcfmf_can_delete_messages' ) ) {
					$message_ids = sanitize_text_field( wp_unslash( $validated_data['message_ids'] ) );
					$message_ids = explode( ',', $message_ids );
					foreach ( $message_ids as $message_id ) {
						$message_id = intval( $message_id );
						$message    = Message::find( $message_id );
						if ( $message ) {
							if ( ! $message->delete() ) {
								wp_send_json_error( __( "We could not delete this message", KMCFMF_TEXT_DOMAIN ), 500 );
							}
						} else {
							wp_send_json_error( __( "We could not find this message", KMCFMF_TEXT_DOMAIN ), 400 );
						}

					}
					wp_send_json_success( __( "Message(s) deleted", KMCFMF_TEXT_DOMAIN ) );

				} else {
					wp_send_json_error( __( "Invalid nonce", KMCFMF_TEXT_DOMAIN ), 400 );
				}

			}
		} else {
			wp_send_json_error( __( "You do not have permission to perform this action", KMCFMF_TEXT_DOMAIN ), 400 );
		}

		wp_die();
	}

	/**
	 * @since 1.6.3.7
	 * Delete all messages in a contact form from the database
	 */
	public function deleteAllMessages() {

		$validator = KMValidator::make(
			array(
				'form_id'  => 'required',
				"_wpnonce" => 'required',
			),
			$_REQUEST
		);
		if ( current_user_can( 'manage_options' ) ) {
			if ( $validated_data = $validator->validate() ) {
				$nonce = sanitize_text_field( wp_unslash( $validated_data['_wpnonce'] ) );
				if ( wp_verify_nonce( $nonce, 'kmcfmf_can_delete_messages' ) ) {
					if( $validated_data['form_id'] == 'all' ){
						wp_send_json_error( __( "Please select a contact form first", KMCFMF_TEXT_DOMAIN ), 400 );
					}

					$form_id            = sanitize_text_field( wp_unslash( $validated_data['form_id'] ) );
					$messages_to_delete = Message::where( 'form_id', '=', $form_id )->get();
					foreach ( $messages_to_delete as $message_to_delete ) {
						if ( ! $message_to_delete->delete() ) {
							wp_send_json_error( __( "We could not delete this message", KMCFMF_TEXT_DOMAIN ), 500 );
						}
					}
					wp_send_json_success( __( "Message(s) deleted", KMCFMF_TEXT_DOMAIN ) );

				} else {
					wp_send_json_error( __( "Invalid nonce", KMCFMF_TEXT_DOMAIN ), 400 );
				}

			}
		} else {
			wp_send_json_error( __( "You do not have permission to perform this action", KMCFMF_TEXT_DOMAIN ), 400 );
		}

		wp_die();
	}

	/**
	 * @since v1.4.4
	 * Resubmits a blocked message
	 */
	public
	function resubmitMessage() {
		$validator = KMValidator::make(
			array(
				'message_ids' => 'required',
				'_wpnonce'    => 'required'
			),
			$_REQUEST
		);

		if ( current_user_can( 'manage_options' ) ) {
			if ( $validated_data = $validator->validate() ) {
				$nonce = sanitize_text_field( wp_unslash( $validated_data['_wpnonce'] ) );
				if ( wp_verify_nonce( $nonce, 'kmcfmf_can_resubmit_messages' ) ) {
					$message_ids = sanitize_text_field( $validated_data['message_ids'] );
					$message_ids = explode( ',', $message_ids );
					KMCFMessageFilter::getInstance()->skipValidation( true );

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
									wp_send_json_error( $result, KMCFMF_TEXT_DOMAIN, 400 );
								}
								$message_id = intval( $message_id );
								$message    = Message::find( $message_id );
								$message->delete();

							} else {
								wp_send_json_error( __( "Feature only available for Contact Form 7", KMCFMF_TEXT_DOMAIN ), 400 );
							}
						} else {
							wp_send_json_error( __( "We could not find this message", KMCFMF_TEXT_DOMAIN ), 400 );
						}
					}
					wp_send_json_success( __( "Message(s) resubmitted successfully", KMCFMF_TEXT_DOMAIN ), 200 );
				} else {
					wp_send_json_error( __( "Invalid nonce", KMCFMF_TEXT_DOMAIN ), 400 );
				}
			}
		} else {
			wp_send_json_error( __( "You do not have permission to perform this action", KMCFMF_TEXT_DOMAIN ), 400 );
		}

		wp_die();
	}

	/**
	 * @since v1.5.5
	 * Resubmits a blocked message
	 */
	function saveVisibleColumns() {
		$validator = KMValidator::make(
			array(
				'form'    => 'required',
				'columns' => 'required'
			),
			$_REQUEST
		);
		if ( current_user_can( 'manage_options' ) ) {
			if ( $validated_data = $validator->validate() ) {
				$nonce = sanitize_text_field( wp_unslash( $validated_data['_wpnonce'] ) );
				if ( wp_verify_nonce( $nonce, 'kmcfmf_can_save_visible_columns' ) ) {
					$form    = sanitize_text_field( $validated_data['form'] );
					$columns = sanitize_text_field( $validated_data['columns'] );
					update_option( 'kmcfmf_visible_columns_' . $form, $columns );

					wp_send_json_success( __( "Visible columns saved", KMCFMF_TEXT_DOMAIN ), 200 );
				} else {
					wp_send_json_error( __( "Invalid nonce", KMCFMF_TEXT_DOMAIN ), 400 );
				}
			} else {
				wp_send_json_error( __( "You do not have permission to perform this action", KMCFMF_TEXT_DOMAIN ), 400 );
			}
		}
		wp_die();
	}

	/**
	 * @since v1.6.3.3
	 * @author: kofimokome
	 */
	public function downloadCSV() {
		$validator = KMValidator::make(
			array(
				'form_id'      => 'required',
				'contact_form' => 'required',
				'_wpnonce'     => 'required'
			),
			$_REQUEST
		);
		if ( current_user_can( 'manage_options' ) ) {
			if ( $validated_data = $validator->validate() ) {
				$nonce = sanitize_text_field( wp_unslash( $validated_data['_wpnonce'] ) );
				if ( wp_verify_nonce( $nonce, 'kmcfmf_can_download_csv' ) ) {

					$form_id      = sanitize_text_field( $validated_data['form_id'] );
					$contact_form = sanitize_text_field( $validated_data['contact_form'] );
					$columns      = $this->getColumns2( $form_id, $contact_form );
					$columns[]    = 'date-blocked';
					$messages     = Message::where( 'contact_form', '=', $contact_form );

					if ( $form_id != 'all' ) {
						$messages = $messages->andWhere( 'form_id', '=', $form_id );
					}

					$messages = $messages->orderBy( 'id', 'desc' )->get();

					$filename = 'blocked_messages_' . date( 'Y-m-d' ) . '.csv';
					$fp       = fopen( 'php://output', 'w' );
					header( 'Content-type: application/csv' );
					header( 'Content-Disposition: attachment; filename=' . $filename );
					fputcsv( $fp, $columns );
					foreach ( $messages as $message ) {
						$decoded_message = json_decode( $message->message );

						$row = [];
						if ( $form_id == 'all' ) {
							$row[] = $this->getFormName( $message->form_id, $message->contact_form );
						}
						foreach ( $columns as $column ) {
							if ( $column == 'date-blocked' ) {
								$row[] = $message->created_at;
							} else {
								if ( property_exists( $decoded_message, $column ) ) {
									$row[] = esc_html( self::decodeUnicodeVars( $decoded_message->$column ) );
								} else {
									$row[] = " ";
								}
							}
						}
						fputcsv( $fp, $row );
					}
					fclose( $fp );
					exit();
				} else {
					wp_send_json_error( __( "Invalid nonce", KMCFMF_TEXT_DOMAIN ), 400 );
				}
			} else {
				wp_send_json_error( __( "You do not have permission to perform this action", KMCFMF_TEXT_DOMAIN ), 400 );
			}
		} else {
			throw new \Exception( __( 'You do not have permission to perform this action', KMCFMF_TEXT_DOMAIN ) );
		}
		wp_die();
	}

	/**
	 * New version of getRows()
	 * @since 1.4.0
	 */
	public function getColumns2( $form_id, $contact_form ) {
		$rows = array();
		switch ( $contact_form ) {
			case 'cf7':
				if ( class_exists( 'WPCF7_ContactForm' ) ) {
					if ( $form_id == 'all' ) {
						$rows = [ 'Contact Form' ];
						foreach (
							WPCF7_ContactForm::find( [
								'post_status' => 'publish',
							] ) as $cf7_form
						) {
							// todo: get only published forms

							$rows = array_merge( $rows, $this->scanCf7Rows( $cf7_form->id() ) );
						}
					} else {
						$rows = $this->scanCf7Rows( $form_id );
					}
				}
				break;
			case 'wpforms':
				if ( function_exists( 'wpforms' ) ) {
					if ( $form_id == 'all' ) {
						$rows = [ 'Contact Form' ];
						foreach ( wpforms()->obj( 'form' )->get() as $wp_form ) {
							// get only published forms
							if ( $wp_form->post_status == 'publish' ) {
								$rows = array_merge( $rows, $this->scanWPFormRows( $wp_form->ID ) );
							}
						}
					} else {
						$rows = $this->scanWPFormRows( $form_id );
					}
				}
				break;
			default:
				$rows = [ 'Contact Form' ];
				if ( class_exists( 'WPCF7_ContactForm' ) ) {
					foreach (
						WPCF7_ContactForm::find( [
							'post_status' => 'publish',
						] ) as $cf7_form
					) {
						$rows = array_merge( $rows, $this->scanCf7Rows( $cf7_form->id() ) );
					}
				}
				if ( function_exists( 'wpforms' ) ) {
					foreach ( wpforms()->obj( 'form' )->get() as $wp_form ) {
						// get only published forms
						if ( $wp_form->post_status == 'publish' ) {
							$rows = array_merge( $rows, $this->scanWPFormRows( $wp_form->ID ) );
						}
					}
				}
				break;

		}

		return array_unique( $rows, SORT_REGULAR );
	}

	/**
	 * @since 1.6.3
	 * Scans a contact form 7 form for rows
	 */
	private function scanCf7Rows( $form_id ) {
		$rows = array();

		$form = WPCF7_ContactForm::get_instance( $form_id );
		$tags = $form->scan_form_tags();
		foreach ( $tags as $tag ) {
			array_push( $rows, $tag->name );
		}

		return $rows;
	}

	/**
	 * @since 1.6.3
	 * Scans a wp form for rows
	 */
	private function scanWPFormRows( $form_id ) {
		$rows = array();

		$form = wpforms()->get( 'form' )->get( $form_id );

		$content = json_decode( $form->post_content, true );
		$fields  = $content['fields'];

		foreach ( $fields as $field ) {
			array_push( $rows, $field['label'] );
		}

		return $rows;
	}

	/**
	 * @since v1.3.4
	 */
	protected
	function addFilters() {
		parent::addFilters();
		add_filter( 'kmcf7_sub_menu_pages_filter', [ $this, 'addSubMenuPage' ] );
		// add actions here
	}

	protected
	function addActions() {
		parent::addActions();
		add_action( 'wp_ajax_kmcf7_download_csv', [ $this, 'downloadCSV' ] );
		add_action( 'wp_ajax_kmcf7_messages', [ $this, 'serverMessages' ] );
		add_action( 'wp_ajax_kmcf7_delete_message', [ $this, 'deleteMessage' ] );
		add_action( 'wp_ajax_kmcf7_delete_all_messages', [ $this, 'deleteAllMessages' ] );
		add_action( 'wp_ajax_kmcf7_resubmit_message', [ $this, 'resubmitMessage' ] );
		add_action( 'wp_ajax_kmcf7_save_visible_columns', [ $this, 'saveVisibleColumns' ] );
	}

}