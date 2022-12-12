<?php

namespace km_message_filter;

// Create users table
$users = new Migration( 'messages' );
$users->id();
$users->string( 'contact_form' );
$users->string( 'form_id' );
$users->text( 'message' );
$users->timestamps();


