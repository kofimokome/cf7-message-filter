<?php

namespace kmcf7_message_filter;

/**
 * Add migration files to be included
 */


add_filter( 'kmcf7_includes_filter', function ( $includes ) {
	$migrations = [
		KMCF7MS_MIGRATIONS_DIR . '/messages.php',//
	];

	return array_merge( $includes, $migrations );
} );