<?php
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );


function get_conversation() {
	if ( ! API_valid_fields( $_POST['username'], $_POST['password'], $_POST['recipient'], $_POST['last_seen'] ) ) {
		echo API_error( 'Invalid parametrs' );

		return;
	}

	$user = auth_user_login( $_POST['username'], $_POST['password'] );
	if ( false === $user ) {
		echo API_error( 'Invalid credentials' );

		return;
	}

	$user->load();

	$messages = $user->get_conversation_with( intval( $_POST['recipient'] ), intval( $_POST['last_seen'] ) );

	if ( false === $messages ) {
		echo API_error( 'Could not load messages' );
		return;
	}

	echo API_success( array( 'messages' => $messages ) );
}

get_conversation();