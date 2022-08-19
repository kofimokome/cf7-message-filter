<?php

namespace kmcf7_message_filter;

/**
 * Add models to be included
 */

function addModels( $includes ) {
	$models = [
		KMCF7MS_MODELS_DIR . '/Message.php',//
	];

	return array_merge( $includes, $models );
}

add_filter( 'kmcf7_includes_filter', 'kmcf7_message_filter\\addModels' );