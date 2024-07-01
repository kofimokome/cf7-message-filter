<?php

namespace km_message_filter;

// Create statistics table
use KMBlueprint;
use KMMigration;

class CreateStatisticsTable extends KMMigration {
	protected  $table_name = 'statistics';

	public function up( KMBlueprint $blueprint ) {
		$blueprint->id();
		$blueprint->date( 'date' );
		$blueprint->integer( 'emails_blocked' )->default( 0 );
		$blueprint->integer( 'messages_blocked' )->default( 0 );
	}

	public function down( KMBlueprint $blueprint ) {
		$blueprint->drop();
	}
}

