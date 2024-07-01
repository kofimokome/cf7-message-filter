<?php

/**
 * Add libraries to be included
 */


add_filter( 'kmcf7_requires_filter', function ( $includes ) {
	$plugin_path = plugin_dir_path( __FILE__ );

	$files = [
		$plugin_path . 'KMCFMessageFilter.php', //
		$plugin_path . 'Module.php', //
		$plugin_path . 'Filter.php', //
	];

	return array_merge( $includes, $files );
} );