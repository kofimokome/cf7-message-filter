<?php

namespace km_message_filter;

use KMValidator;

class FiltersModule extends Module {
	private static $instance;

	private array $default_filters = [ 'katakana', "hiragana", "kanji", "japanese", "russian", "emoji", "link" ];

	public function __construct() {
		parent::__construct();
		self::$instance = $this;

	}

	/**
	 * @since v1.6.0
	 * @author kofimokome
	 */
	public static function getInstance(): FiltersModule {
		return self::$instance;
	}


	/**
	 * @since v1.6.0
	 * Checks if a filter has variables and updates the shortcode with the variables.
	 */
	public function buildShortCode( MyFilter $filter, $append_example_value = false ): string {
		$short_code    = $filter->short_code;
		$expression    = $filter->expression;
		$example_value = $append_example_value ? "xxx " : " ";
		$variables     = [];
		preg_match_all( '/{{.*?}}/', $expression, $variables );
		if ( sizeof( $variables ) > 0 ) {
			return trim( $short_code . array_reduce( $variables[0], function ( $carry, $variable ) use ( $example_value ) {
					$variable = str_replace( "{{", "", $variable );
					$variable = str_replace( "}}", "", $variable );

					return $carry . $variable . "=" . $example_value;
				}, ' ' ) );
		}

		return $short_code;
	}

	/**
	 * @since 1.6.0
	 * Deletes a filter from the database
	 */
	public function deleteFilter() {

		$validator = KMValidator::make(
			array(
				'id' => 'required'
			),
			$_POST
		);

		if ( $validator->validate() ) {
			$id        = sanitize_text_field( $_POST['id'] );
			$my_filter = MyFilter::find( $id );
			if ( $my_filter ) {
				$my_filter->delete();
			} else {
				wp_send_json_error( __( "We could not find this filter", KMCF7MS_TEXT_DOMAIN ), 400 );
			}
			wp_send_json_success( __( "Filter deleted", KMCF7MS_TEXT_DOMAIN ), 200 );

		}
		wp_die();
	}

	/**
	 * @since v1.6.0
	 * Saves a filter in the database
	 */
	public function saveFilter() {
		$validator = KMValidator::make(
			array(
				'name'       => 'required',
				'expression' => 'required'
			),
			$_POST
		);

		if ( $validator->validate() ) {
			$name        = sanitize_text_field( $_POST['name'] );
			$description = sanitize_text_field( $_POST['description'] );
			$expression  = sanitize_text_field( $_POST['expression'] );

			$short_code = trim( strtolower( $name ) );
			$short_code = str_replace( " ", "-", $short_code );

			if ( in_array( $short_code, $this->default_filters ) ) {
				wp_send_json_error( __( "The filter name  already exists", KMCF7MS_TEXT_DOMAIN ), 400 );
			}

			$exists = MyFilter::where( 'short_code', '=', $short_code )->get();
			if ( $exists ) {
				wp_send_json_error( __( "A filter with this name  already exists", KMCF7MS_TEXT_DOMAIN ), 400 );
			}
			$new_filter              = new MyFilter();
			$new_filter->name        = $name;
			$new_filter->description = $description;
			$new_filter->expression  = $this->removeRegexStartAndEnd( $expression );
			$new_filter->short_code  = $short_code;
			$new_filter->save();

			wp_send_json_success( __( "Filter saved", KMCF7MS_TEXT_DOMAIN ), 200 );
		}
		wp_die();
	}

	/**
	 * @since v1.6.0
	 * Remove the first and last characters in a string if they are '/'
	 * */
	private function removeRegexStartAndEnd( $expression ): string {
		if ( preg_match( "/^\/.*\//", $expression, $matches ) ) {
			// remove the first and last characters in $matches[0]
			$expression = substr( $matches[0], 1, - 1 );
		}

		// wordpress sanitization escapes \
		return str_replace( "\\\\", "\\", $expression );
	}

	/**
	 * @since v1.6.0
	 * Updates a filter in the database
	 */
	public function updateFilter() {
		$validator = KMValidator::make(
			array(
				'name'       => 'required',
				'expression' => 'required',
				"id"         => 'required'
			),
			$_POST
		);

		if ( $validator->validate() ) {
			$name        = sanitize_text_field( $_POST['name'] );
			$description = sanitize_text_field( $_POST['description'] );
			$expression  = sanitize_text_field( $_POST['expression'] );
			$id          = sanitize_text_field( $_POST['id'] );

			$short_code = trim( strtolower( $name ) );
			$short_code = str_replace( " ", "-", $short_code );

			if ( in_array( $short_code, $this->default_filters ) ) {
				wp_send_json_error( __( "The filter name  already exists", KMCF7MS_TEXT_DOMAIN ), 400 );
			}
			$exists = MyFilter::where( 'short_code', '=', $short_code )->first();
			if ( $exists && $exists->id != intval( $id ) ) {
				wp_send_json_error( __( "A filter with this name  already exists", KMCF7MS_TEXT_DOMAIN ), 400 );
			} else {
				$my_filter = MyFilter::find( $id );
				if ( $my_filter ) {
					$my_filter->name        = $name;
					$my_filter->description = $description;
					$my_filter->expression  = $this->removeRegexStartAndEnd( $expression );
					$my_filter->short_code  = $short_code;
					$my_filter->save();
				} else {
					wp_send_json_error( __( "We could not find this filter", KMCF7MS_TEXT_DOMAIN ), 400 );
				}
			}

			wp_send_json_success( __( "Filter saved", KMCF7MS_TEXT_DOMAIN ), 200 );
		}
		wp_die();
	}

	protected function addActions() {
		parent::addActions();
		add_action( 'wp_ajax_kmcf7_delete_filter', [ $this, 'deleteFilter' ] );
		add_action( 'wp_ajax_kmcf7_update_filter', [ $this, 'updateFilter' ] );
		add_action( 'wp_ajax_kmcf7_save_filter', [ $this, 'saveFilter' ] );
	}

}