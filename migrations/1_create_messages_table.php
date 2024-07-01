<?php

namespace km_message_filter;

// Create users table
use KMBlueprint;
use KMMigration;

class
CreateMessagesTable
	extends
	KMMigration {
	protected  $table_name = 'messages';

	public function up( KMBlueprint $blueprint ) {
		$blueprint->id();
		$blueprint->string( 'contact_form' );
		$blueprint->string( 'form_id' );
		$blueprint->text( 'message' );
		$blueprint->timestamps();
	}

	public function down( KMBlueprint $blueprint ) {
		$blueprint->drop();
	}
}