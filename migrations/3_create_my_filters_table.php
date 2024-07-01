<?php

namespace km_message_filter;

// Create statistics table
use KMBlueprint;
use KMMigration;

class CreateFiltersTable extends KMMigration {
	protected $table_name = 'my_filters';

	public function up( KMBlueprint $blueprint ) {
		$blueprint->id();
		$blueprint->string( 'name' );
		$blueprint->text( 'description' )->nullable();
		$blueprint->text( 'expression' );
		$blueprint->string( 'short_code' );
		$blueprint->timestamps();
	}

	public function down( KMBlueprint $blueprint ) {
		$blueprint->drop();
	}
}

