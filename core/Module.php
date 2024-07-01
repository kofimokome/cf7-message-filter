<?php

namespace km_message_filter;

use Exception;


class Module {
	/** Render a template file. */
	protected $module;

	public function __construct() {
		$module       = get_called_class();
		$module       = str_replace( 'Module', '', $module );
		$module       = str_replace( 'km_message_filter\\', '', $module );
		$this->module = strtolower( $module );
		$this->addActions();
		$this->addFilters();
		$this->addShortcodes();
	}

	/**
	 * @since v1.3.4
	 */
	protected function addActions() {

	}

	/**
	 * @since v1.3.4
	 */
	protected function addFilters() {

	}

	/**
	 * @since v1.3.4
	 */
	protected function addShortcodes() {

	}

	/**
	 * @since v1.3.4
	 */
	public static function getModules( $dir, $show_folder_name = true ) {
		$ffs   = scandir( $dir );
		$files = array();
		unset( $ffs[ array_search( '.', $ffs, true ) ] );
		unset( $ffs[ array_search( '..', $ffs, true ) ] );

		// prevent empty ordered elements


		foreach ( $ffs as $ff ) {
			if ( is_dir( $dir . '/' . $ff ) ) {
				$files = array_merge( $files, self::getModules( $dir . '/' . $ff, $show_folder_name ) );
			} else {
				if ( strpos( $ff, 'Module' ) > 0 ) {
					if ( $show_folder_name ) {
						array_push( $files, $dir . '/' . $ff );
					} else {
						array_push( $files, $ff );
					}
				}
			}
		}

		return $files;
	}
}