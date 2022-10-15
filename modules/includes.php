<?php

namespace km_message_filter;

/**
 * Add files/classes included on the admin module
 */

add_filter( 'kmcf7_includes_filter', function ( $includes ) {
	$modules  = Module::getModules( KMCF7MS_MODULE_DIR );

	return array_merge( $includes, $modules );
} );
