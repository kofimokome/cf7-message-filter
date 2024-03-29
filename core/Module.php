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
	protected function renderContent( $template = '', $echo = true ) {

		$parent_module_folder = KMCF7MS_MODULE_DIR;
		$template     = str_replace( '.', '/', $template );

		// Start output buffering.
		ob_start();
		ob_implicit_flush( 0 );
		try {
			include $parent_module_folder . '/' . $this->module . '/templates/' . $template . '.php';
		} catch ( Exception $e ) {
			ob_end_clean();
			throw $e;
		}

		if ( $echo ) {
			echo ob_get_clean();
		} else {
			return ob_get_clean();
		}
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