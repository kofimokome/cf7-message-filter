<?php

namespace km_message_filter;

/**
 * Add models to be included
 */

function addModels( $includes ) {
	$models = [
		KMCFMF_MODELS_DIR . '/Message.php',//
		KMCFMF_MODELS_DIR . '/Statistic.php',//
		KMCFMF_MODELS_DIR . '/MyFilter.php',//
	];

	return array_merge( $includes, $models );
}

add_filter( 'kmcf7_includes_filter', 'km_message_filter\\addModels' );